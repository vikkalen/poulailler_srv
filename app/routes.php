<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Core\TxPayload;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->post('/login/', function (Request $request, Response $response, array $args) {

        $data = $request->getParsedBody();

        if(!isset($data['name']) || !isset($data['password']))
        {
            return $response->withStatus(422);
        }

        $name = $data['name'];
        $password = $data['password'];

        if(!$this->get('db')->checkUser($name, $password))
        {
            return $response->withStatus(401);
        }

        $outputPayload = array('token' => $this->get(SettingsInterface::class)->get('auth_token'));

        $response->getBody()->write(json_encode(['data' => $outputPayload]));
	
	return $response;
    });


    $app->post('/rx/', function (Request $request, Response $response, array $args) {

        $data = $request->getParsedBody();
        if(!$data || !isset($data['data']))
        {
            $this->get(LoggerInterface::class)->info(var_export($data, true));
            return $response->withStatus(422);
        }

        $payload = $this->get('tx')->input($data['data']);

        if($payload->flag == TxPayload::FLAG_REQUEST_STATE)
        {
            $rrdData = array(
                'luminosite' => $payload->lux,
                'ouvert' => $payload->ouvert,
                'ferme' => $payload->ferme,
                'voltage' => $payload->supplyV,
                'intensity' => $payload->sig
            );
            $rrd = $this->get('rrd');
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
            $this->get('json')->write($data);
            $this->get('db')->deleteCommand('config');
        }
        else if($payload->flag == TxPayload::FLAG_ACK)
        {
            $this->get('db')->deleteCommand('porte');
        }

        list($command, $params) = $this->get('db')->getCommand();
        $outputPayload = $command ? $this->get('tx')->command($payload->sonde, $command, $params) : null;
        $outputPayload = $outputPayload ?: new TxPayload($payload->sonde, TxPayload::FLAG_ACK);

        $response->getBody()->write(json_encode(['data' => $this->get('tx')->output($outputPayload)]));
   	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->get('/info/', function (Request $request, Response $response, array $args) {

        $info = $this->get('rrd')->info();

        $response->getBody()->write(json_encode(['data' => $info]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->get('/configuration/', function (Request $request, Response $response, array $args) {

        $config = $this->get('json')->read();

        $this->get('db')->pushCommand('config');

        $response->getBody()->write(json_encode(['data' => $config]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->get('/graph/{probe}/{periode}/', function (Request $request, Response $response, array $args) {

        $probe = $args['probe'];
        $periode = $args['periode'];
        $params = $request->getQueryParams();
        $width = $params['width'] ?? null;
        $height = $params['height'] ?? null;

        if(!$width || !$height)
        {
            $this->get(LoggerInteface::class)->info("No dimensions given to graph");
            return $response->withStatus(422);
        }

        $graph = $this->get('rrd')->graph($probe, $periode, $width, $height);

        $response->getBody()->write(json_encode(['data' => ['mediatype' => 'image/png', 'data' => base64_encode($graph)]]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->post('/ouvrir/', function (Request $request, Response $response, array $args) {

        $this->get('db')->pushCommand('porte', ['direction' => 'ouvrir']);

        $response->getBody()->write(json_encode(['data' => null]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->post('/fermer/', function (Request $request, Response $response, array $args) {

        $this->get('db')->pushCommand('porte', ['direction' => 'fermer']);

        $response->getBody()->write(json_encode(['data' => null]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->post('/lumiere/', function (Request $request, Response $response, array $args) {

        $this->get('db')->pushCommand('lumiere');

        $response->getBody()->write(json_encode(['data' => $null]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->post('/configurer/', function (Request $request, Response $response, array $args) {

        $data = $request->getParsedBody();
        if(!$data)
        {
            $this->get(LoggerInterface::class)->info(var_export($data, true));
            return $response->withStatus(422);
        }

        $config = $data;

        $oldConfig = $this->get('json')->read();
        foreach($oldConfig as $key => $value)
        {
    	if(!isset($config[$key])) $config[$key] = $value;
        }

        $this->get('db')->pushCommand('config', $config);

        $response->getBody()->write(json_encode(['data' => null]));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

};

