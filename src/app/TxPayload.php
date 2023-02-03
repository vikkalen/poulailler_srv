<?php
namespace App;

class TxPayload
{
    const FLAG_ACK = "0";
    const FLAG_REQUEST_STATE = "1";
    const FLAG_REQUEST_CONF = "2";
    const FLAG_CONFIGURE = "3";
    const FLAG_OUVRIR = "4";
    const FLAG_FERMER = "5";
    const FLAG_LUMIERE = "6";

    public $sonde;
    public $flag;
    public $ouvert;
    public $ferme;
    public $lux;
    public $supplyV;
    public $sig;

    public $luxFermeture;
    public $luxOuverture;
    public $tours;
    public $retry;
    public $sleep;
    public $fermetureDelay;

    public function __construct($sonde, $flag)
    {
        $this->sonde = $sonde;
        $this->flag = $flag;
    }
}
