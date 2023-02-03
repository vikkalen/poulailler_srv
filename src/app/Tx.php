<?php
namespace App;
use App\TxPayload;

class Tx
{
    public function input($data)
    {
        $sonde = $data[0];
        $flag = $data[1];
        $txPayload = new TxPayload($sonde, $flag);
        switch ($flag)
        {
            case TxPayload::FLAG_ACK:
                break;
            case TxPayload::FLAG_REQUEST_STATE:
                $txPayload->ouvert = $data[2];
                $txPayload->ferme = $data[3];
                $txPayload->lux = $data[4];
                $txPayload->supplyV = 0.001 * $data[5];
                $txPayload->sig = $data[6];
                break;
            case TxPayload::FLAG_REQUEST_CONF:
                $txPayload->luxFermeture = $data[2];
                $txPayload->luxOuverture = $data[3];
                $txPayload->tours = $data[4];
                $txPayload->retry = $data[5];
                $txPayload->sleep = $data[6];
                $txPayload->fermetureDelay = $data[7];
                break;
        }

        return $txPayload;
    }

    public function output($txPayload)
    {
        $data = [];
        $data[0] = $txPayload->sonde;
        $data[1] = $txPayload->flag;
        switch($txPayload->flag)
        {
            case TxPayload::FLAG_ACK:
            case TxPayload::FLAG_REQUEST_STATE:
            case TxPayload::FLAG_REQUEST_CONF:
            case TxPayload::FLAG_OUVRIR:
            case TxPayload::FLAG_FERMER:
            case TxPayload::FLAG_LUMIERE:
                break;
            case TxPayload::FLAG_CONFIGURE:
                $data[2] = $txPayload->luxFermeture;
                $data[3] = $txPayload->luxOuverture;
                $data[4] = $txPayload->tours;
                $data[5] = $txPayload->retry;
                $data[6] = $txPayload->sleep;
                $data[7] = $txPayload->fermetureDelay;
                break;
        }

        return $data;
    }

    public function command($sonde, $command, $params)
    {
        $txPayload = null;
        switch($command)
        {
            case 'config':
                if($params)
                {
                    $txPayload = new TxPayload($sonde, TxPayload::FLAG_CONFIGURE);
                    $txPayload->luxFermeture = isset($params['lux_fermeture']) ? $params['lux_fermeture'] : null;
                    $txPayload->luxOuverture = isset($params['lux_ouverture']) ? $params['lux_ouverture'] : null;
                    $txPayload->tours = isset($params['tours']) ? $params['tours'] : null;
                    $txPayload->retry = isset($params['retry']) ? $params['retry'] : null;
                    $txPayload->sleep = isset($params['sleep']) ? $params['sleep'] : null;
                    $txPayload->fermetureDelay = isset($params['fermeture_delay']) ? $params['fermeture_delay'] : null;
                }
                else
                {
                    $txPayload = new TxPayload($sonde, TxPayload::FLAG_REQUEST_CONF);
                }
                break;
            case 'porte':
                $direction = isset($params['direction']) ? $params['direction'] : null;
                if($direction == 'ouvrir') $txPayload = new TxPayload($sonde, TxPayload::FLAG_OUVRIR);
                if($direction == 'fermer') $txPayload = new TxPayload($sonde, TxPayload::FLAG_FERMER);
		break;
           case 'lumiere':
                $txPayload = new TxPayload($sonde, TxPayload::FLAG_LUMIERE);
        }

        return $txPayload;
    }

}
