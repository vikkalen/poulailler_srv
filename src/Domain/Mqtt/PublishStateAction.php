<?php
namespace App\Domain\Mqtt;

use App\Application\Settings\SettingsInterface;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;

class PublishStateAction extends BasePublishAction
{
    public function execute(MqttClient $client)
    {
        $info = $this->container->get('rrd')->info();
        $voltage = (float)$info['ds[voltage].last_ds'];
        $linkquality = (int)$info['ds[intensity].last_ds'];
        $luminosite = (int)$info['ds[luminosite].last_ds'];
        
	$ouvert = $info['ds[ouvert].last_ds'];
	$ferme = $info['ds[ferme].last_ds'];
	$porte = 'JAMMED';
	if($ouvert && !$ferme) $porte = 'UNLOCKED';
	else if(!$ouvert && $ferme) $porte = 'LOCKED';

        if($voltage > 4.2) $battery = 100;
        else if($voltage > 4.1) $battery = (int)(90 + ($voltage - 4.1) * 100);
        else if($voltage > 4.0) $battery = (int)(80 + ($voltage - 4.0) * 100);
        else if($voltage > 3.9) $battery = (int)(60 + ($voltage - 3.9) * 200);
        else if($voltage > 3.8) $battery = (int)(40 + ($voltage - 3.8) * 200);
        else if($voltage > 3.7) $battery = (int)(20 + ($voltage - 3.7) * 200);
        else if($voltage > 3.6) $battery = (int)(($voltage - 3.6) * 200);
        else $battery = 0;

        $config = $this->container->get('json')->read();
	
	$stateData = [
            "battery" => $battery,
            "voltage" => $voltage,
            "linkquality" => $linkquality,
	    "luminosite" => $luminosite,
	    "porte" => $porte,
	    "config" => $config,
        ];
        $topic = $this->settings()['state_topic'];
        $this->publish($client, $topic, $stateData);
    }
}
