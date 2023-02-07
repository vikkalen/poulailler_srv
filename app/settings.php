<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stderr' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::INFO,
                ],
                'rrd' => [
                    'path' => __DIR__ . '/../storage/poulailler.rrd' ,
                ],

                'db' => [
                    'path' => __DIR__ . '/../storage/poulailler.db' ,
                ],

                'json' => [
                    'path' => __DIR__ . '/../storage/poulailler.json' ,
		],

		'mqtt' => [
		    'host' => $_ENV['MQTT_HOST'] ?? null,
		    'port' => $_ENV['MQTT_PORT'] ?? null,
		    'client_id' => $_ENV['MQTT_CLIENT_ID'] ?? null,
		    'username' => $_ENV['MQTT_USERNAME'] ?? null,
		    'password' => $_ENV['MQTT_PASSWORD'] ?? null,
		    'discovery_topic' => $_ENV['MQTT_DISCOVERY_TOPIC'] ?? null,
		    'state_topic' => $_ENV['MQTT_STATE_TOPIC'] ?? null,
		    'command_topic' => $_ENV['MQTT_COMMAND_TOPIC'] ?? null,
		],

                'tz' => isset($_ENV['TZ']) ? $_ENV['TZ'] : 'UTC',

                'auth_token' => isset($_ENV['POULAILLER_TOKEN']) ? $_ENV['POULAILLER_TOKEN'] : '',
            ]);
        }
    ]);
};
