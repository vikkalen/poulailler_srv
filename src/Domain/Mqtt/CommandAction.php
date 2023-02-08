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
        if($message == 'LOCK') {
            $this->container->get('db')->pushCommand('porte', ['direction' => 'fermer']);
	    return;
	} else if ($message == 'UNLOCK') {
            $this->container->get('db')->pushCommand('porte', ['direction' => 'ouvrir']);
	    return;
	}

       $payload = json_decode($message, true);
        if(!$payload) return;

        $config = $payload['config'] ?? [];
        if($config) {
            $json = $this->container->get('json');
            
            $config = array_merge($json->read(), $config);
            
            $json->write($config);
            $this->container->get('db')->pushCommand('config', $config);
        }
    }
}
