<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Domain\Mqtt\PublishDiscoveryAction;
use App\Domain\Mqtt\PublishStateAction;
use App\Domain\Mqtt\CommandAction;
use DI\ContainerBuilder;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();


$config = $container->get(SettingsInterface::class)->get('mqtt');
$logger = $container->get(LoggerInterface::class);

try {
    $client = new MqttClient($config['host'], (int)$config['port'], $config['client_id'], MqttClient::MQTT_3_1_1, null, $logger);

    pcntl_async_signals(true);
    pcntl_signal(SIGINT, function () use ($client, $logger) {
        $logger->info('Received SIGINT signal, interrupting the client for a graceful shutdown...');

        $client->interrupt();
    });

    $connectionSettings = (new ConnectionSettings)
	->setUsername($config['username'])
	->setPassword($config['password']);

    $client->connect($connectionSettings, true);

    $lastPublish = 0;
    $client->registerLoopEventHandler(function (MqttClient $client, float $elapsedTime) use ($container, &$lastPublish) {
        if ($lastPublish + 60 > time()) {
            return;
	}

	$container->get(PublishStateAction::class)->execute($client);

	$lastPublish = time();
    });

    $container->get(PublishDiscoveryAction::class)->execute($client);
    $client->subscribe($config['discovery_topic'] . "/status", function(string $topic, string $message, bool $retained) use($client, $container) {
	if($message == 'online') $container->get(PublishDiscoveryAction::class)->execute($client);
    }, MqttClient::QOS_AT_MOST_ONCE);

    $client->subscribe($config['command_topic'], function(string $topic, string $message, bool $retained) use($container) {
	$container->get(CommandAction::class)->execute($message);
    }, MqttClient::QOS_AT_MOST_ONCE);

    $client->loop(true);
    $client->disconnect();
} catch (MqttClientException $e) {
    $logger->error('Running the loop with a loop event handler failed. An exception occurred.', ['exception' => $e]);
}


