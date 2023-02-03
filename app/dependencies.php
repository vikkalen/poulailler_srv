<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Core\Tx;
use App\Core\RRD;
use App\Core\TokenMiddleware;
use App\Core\DB;
use App\Core\JSON;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        // Transciever
       'tx' => function (ContainerInterface $c) {
            $tx = new Tx();
            return $tx;
        },
        
        // RRD
        'rrd' => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class)->get('rrd');
            $rrd = new RRD($settings['path']);
            return $rrd;
        },
        
        // Token guard
        'token_guard' => function (ContainerInterface $c) {
            $token = $c->get(SettingsInterface::class)->get('auth_token');
            $tokenGuard = new TokenMiddleware($token);
            return $tokenGuard;
        },
        
        // DB
        'db' => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class)->get('db');
            $db = new DB($settings['path']);
            return $db;
        },
        
        // JSON
        'json' => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class)->get('json');
            $json = new JSON($settings['path']);
            return $json;
	},
    ]);
};

