<?php
namespace App\Domain\Mqtt;

use App\Application\Settings\SettingsInterface;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;

class PublishDiscoveryAction extends BasePublishAction
{
    public function execute(MqttClient $client)
    {
        $topic = $this->settings()['discovery_topic'];
        
        $discoveryData = [
            "device" => [
                "identifiers" => ['poulailler_device'],
                "manufacturer" => "Michal Olexa",
                "model" => "Poulailler",
            ],
            "enabled_by_default" => true,
            "state_class" => "measurement",
            "state_topic" => $this->settings()['state_topic'],
        ];

        $batteryData = array_merge($discoveryData, [
            'device_class' => 'battery',
            'entity_category' => 'diagnostic',
            'name' => 'Poulailler battery',
            'unique_id' => 'poulailler_battery',
            'unit_of_measurement' => '%',
            'value_template' => '{{ value_json.battery }}'
        ]);
 
        $voltageData = array_merge($discoveryData, [
            'device_class' => 'voltage',
            'entity_category' => 'diagnostic',
            'name' => 'Poulailler voltage',
            'unique_id' => 'poulailler_voltage',
            'unit_of_measurement' => 'V',
            'value_template' => '{{ value_json.voltage }}'
        ]);

	$luminositeData = array_merge($discoveryData, [
            'device_class' => 'illuminance',
            'name' => 'Poulailler luminosite',
            'unique_id' => 'poulailler_luminosite',
            'unit_of_measurement' => 'lx',
            'value_template' => '{{ value_json.luminosite }}'
        ]);
 
        $linkqualityData = array_merge($discoveryData, [
            'enabled_by_default' => false,
            'icon' => 'mdi:signal',
            'entity_category' => 'diagnostic',
            'name' => 'Poulailler linkquality',
            'unique_id' => 'poulailler_linkquality',
            'unit_of_measurement' => 'lqi',
            'value_template' => '{{ value_json.linkquality }}'
        ]);
 
 	$porteData = array_merge($discoveryData, [
            'name' => 'Poulailler porte',
            'unique_id' => 'poulailler_porte',
	    'value_template' => '{{ value_json.porte }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "porte": "{{ value }}" }',
	]);
        
 	$luxOuvertureData = array_merge($discoveryData, [
            'name' => 'Poulailler lux ouverture',
	    'unique_id' => 'poulailler_lux_ouverture',
	    'entity_category' => 'config',
	    'min' => 1,
	    'max' => 20,
	    'mode' => 'box',
	    'value_template' => '{{ value_json.config.lux_ouverture }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "config": { "lux_ouverture": "{{ value }}" } }',
	]);

      	$luxFermetureData = array_merge($discoveryData, [
            'name' => 'Poulailler lux fermeture',
	    'unique_id' => 'poulailler_lux_fermeture',
	    'entity_category' => 'config',
	    'min' => 1,
	    'max' => 20,
	    'mode' => 'box',
	    'value_template' => '{{ value_json.config.lux_fermeture }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "config": { "lux_fermeture": "{{ value }}" } }',
	]);

	$toursData = array_merge($discoveryData, [
            'name' => 'Poulailler tours',
	    'unique_id' => 'poulailler_tours',
	    'entity_category' => 'config',
	    'min' => 0,
	    'max' => 20,
	    'mode' => 'box',
	    'value_template' => '{{ value_json.config.tours }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "config": { "tours": "{{ value }}" } }',
	]);

	$retryData = array_merge($discoveryData, [
            'name' => 'Poulailler retry',
	    'unique_id' => 'poulailler_retry',
	    'entity_category' => 'config',
	    'min' => 0,
	    'max' => 5,
	    'mode' => 'box',
	    'value_template' => '{{ value_json.config.retry }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "config": { "retry": "{{ value }}" } }',
	]);

	$sleepData = array_merge($discoveryData, [
            'name' => 'Poulailler sleep',
	    'unique_id' => 'poulailler_sleep',
	    'entity_category' => 'config',
	    'min' => 10,
	    'max' => 120,
	    'mode' => 'box',
	    'value_template' => '{{ value_json.config.sleep }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "config": { "sleep": "{{ value }}" } }',
	]);

	$fermetureDelayData = array_merge($discoveryData, [
            'name' => 'Poulailler fermeture delay',
	    'unique_id' => 'poulailler_fermeture_delay',
	    'entity_category' => 'config',
	    'min' => 0,
	    'max' => 30,
	    'mode' => 'box',
	    'value_template' => '{{ value_json.config.fermeture_delay }}',
            "command_topic" => $this->settings()['command_topic'],
            "command_template" => '{ "config": { "fermeture_delay": "{{ value }}" } }',
	]);


	$this->publish($client, "$topic/sensor/poulailler/battery/config", $batteryData);
	$this->publish($client, "$topic/sensor/poulailler/voltage/config", $voltageData);
	$this->publish($client, "$topic/sensor/poulailler/luminosite/config", $luminositeData);
	$this->publish($client, "$topic/sensor/poulailler/linkquality/config", $linkqualityData);
	$this->publish($client, "$topic/lock/poulailler/porte/config", $porteData);
	$this->publish($client, "$topic/number/poulailler/lux_ouverture/config", $luxOuvertureData);
	$this->publish($client, "$topic/number/poulailler/lux_fermeture/config", $luxFermetureData);
	$this->publish($client, "$topic/number/poulailler/tours/config", $toursData);
	$this->publish($client, "$topic/number/poulailler/retry/config", $retryData);
	$this->publish($client, "$topic/number/poulailler/sleep/config", $sleepData);
	$this->publish($client, "$topic/number/poulailler/fermeture_delay/config", $fermetureDelayData);
    }
}
