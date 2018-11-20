<?php
// DIC configuration

use App\Tx;
use App\RRD;
use App\TokenMiddleware;
use App\DB;
use App\JSON;

$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Transciever
$container['tx'] = function ($c) {
    $tx = new Tx();
    return $tx;
};

// RRD
$container['rrd'] = function ($c) {
    $settings = $c->get('settings')['rrd'];
    $rrd = new RRD($settings['path']);
    return $rrd;
};

// Token guard
$container['token_guard'] = function ($c) {
    $token = $c->get('settings')['auth_token'];
    $tokenGuard = new TokenMiddleware($token);
    return $tokenGuard;
};

// DB
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $db = new DB($settings['path']);
    return $db;
};

// JSON
$container['json'] = function ($c) {
    $settings = $c->get('settings')['json'];
    $json = new JSON($settings['path']);
    return $json;
};
