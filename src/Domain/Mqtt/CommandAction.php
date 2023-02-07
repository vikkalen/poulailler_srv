<?php
namespace App\Domain\Mqtt;

use Psr\Container\ContainerInterface;

class CommandAction
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute($message)
    {
        $payload = json_decode($message, true);
        if(!$payload) return;

        if($payload['porte'] ?? null == 'LOCK') {
            $this->container->get('db')->pushCommand('porte', ['direction' => 'fermer']);
        } else if ($payload['porte'] ?? null == 'UNLOCK') {
            $this->container->get('db')->pushCommand('porte', ['direction' => 'ouvrir']);
        }

        $config = $payload['config'] ?? [];
        if($config) {
            $json = $this->container->get('json');
            
            $config = array_merge($json->read(), $config);
            
            $json->write($config);
            $this->container->get('db')->pushCommand('config', $config);
        }
    }
}
