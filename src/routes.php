<?php

use Slim\Http\Request;
use Slim\Http\Response;

use App\TxPayload;

// Routes

$app->post('/login/', function (Request $request, Response $response, array $args) {

    $data = $request->getParsedBody();

    if(!isset($data['name']) || !isset($data['password']))
    {
        return $response->withStatus(422);
    }

    $name = $data['name'];
    $password = $data['password'];

    if(!$this->db->checkUser($name, $password))
    {
        return $response->withStatus(401);
    }

    $outputPayload = array('token' => $this->settings['auth_token']);

    return $response->withJson(['data' => $outputPayload]);
});


$app->post('/rx/', function (Request $request, Response $response, array $args) {

    $data = $request->getParsedBody();
    if(!$data || !isset($data['data']))
    {
        $this->logger->info(var_export($data, true));
        return $response->withStatus(422);
    }

    $payload = $this->tx->input($data['data']);

    if($payload->flag == TxPayload::FLAG_REQUEST_STATE)
    {
        $rrdData = array(
            'luminosite' => $payload->lux,
            'ouvert' => $payload->ouvert,
            'ferme' => $payload->ferme,
            'voltage' => $payload->supplyV,
            'intensity' => $payload->sig
        );
        $rrd = $this->rrd;
        $rrd->update($rrdData);
    }
    else if($payload->flag == TxPayload::FLAG_REQUEST_CONF)
    {
        $data = array(
            'lux_fermeture' => $payload->luxFermeture,
            'lux_ouverture' => $payload->luxOuverture,
            'tours' => $payload->tours,
            'retry' => $payload->retry,
            'sleep' => $payload->sleep,
            'fermeture_delay' => $payload->fermetureDelay,
        );
        $this->json->write($data);
        $this->db->deleteCommand('config');
    }
    else if($payload->flag == TxPayload::FLAG_ACK)
    {
        $this->db->deleteCommand('porte');
    }

    list($command, $params) = $this->db->getCommand();
    $outputPayload = $command ? $this->tx->command($payload->sonde, $command, $params) : null;
    $outputPayload = $outputPayload ?: new TxPayload($payload->sonde, TxPayload::FLAG_ACK);

    return $response->withJson(['data' => $this->tx->output($outputPayload)]);
})->add($app->getContainer()->token_guard);

$app->get('/info/', function (Request $request, Response $response, array $args) {

    $info = $this->rrd->info();

    return $response->withJson(['data' => $info]);
})->add($app->getContainer()->token_guard);

$app->get('/configuration/', function (Request $request, Response $response, array $args) {

    $config = $this->json->read();

    $this->db->pushCommand('config');

    return $response->withJson(['data' => $config]);
})->add($app->getContainer()->token_guard);

$app->get('/graph/{probe}/{periode}/', function (Request $request, Response $response, array $args) {

    $probe = $args['probe'];
    $periode = $args['periode'];
    $width = $request->getQueryParam('width');
    $height = $request->getQueryParam('height');

    if(!$width || !$height)
    {
        $this->logger->info("No dimensions given to graph");
        return $response->withStatus(422);
    }

    $graph = $this->rrd->graph($probe, $periode, $width, $height);

    return $response->withJson(['data' => ['mediatype' => 'image/png', 'data' => base64_encode($graph)]]);
})->add($app->getContainer()->token_guard);

$app->post('/ouvrir/', function (Request $request, Response $response, array $args) {

    $this->db->pushCommand('porte', ['direction' => 'ouvrir']);

    return $response->withJson(['data' => null]);
})->add($app->getContainer()->token_guard);

$app->post('/fermer/', function (Request $request, Response $response, array $args) {

    $this->db->pushCommand('porte', ['direction' => 'fermer']);

    return $response->withJson(['data' => null]);
})->add($app->getContainer()->token_guard);

$app->post('/lumiere/', function (Request $request, Response $response, array $args) {

    $this->db->pushCommand('lumiere');

    return $response->withJson(['data' => null]);
})->add($app->getContainer()->token_guard);

$app->post('/configurer/', function (Request $request, Response $response, array $args) {

    $data = $request->getParsedBody();
    if(!$data)
    {
        $this->logger->info(var_export($data, true));
        return $response->withStatus(422);
    }

    $config = $data;

    $oldConfig = $this->json->read();
    foreach($oldConfig as $key => $value)
    {
	if(!isset($config[$key])) $config[$key] = $value;
    }

    $this->db->pushCommand('config', $config);

    return $response->withJson(['data' => null]);
})->add($app->getContainer()->token_guard);
