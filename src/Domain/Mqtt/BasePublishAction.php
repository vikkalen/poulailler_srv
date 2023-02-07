<?php
namespace App\Domain\Mqtt;

use App\Application\Settings\SettingsInterface;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;

class BasePublishAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function settings()
    {
        return $this->container->get(SettingsInterface::class)->get('mqtt');
    }

    protected function publish(MqttClient $client, $topic, $data)
    {
        $client->publish($topic, json_encode($data, JSON_UNESCAPED_SLASHES), MqttClient::QOS_AT_MOST_ONCE); 
    }
}
