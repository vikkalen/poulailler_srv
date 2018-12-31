<?php
namespace App;
use \Exception;

class RRD
{
    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function update($data)
    {
        $this->open();

        $dataStr = 'N'
            . ':' . $data['luminosite']
            . ':' . $data['ouvert']
            . ':' . $data['ferme']
            . ':' . $data['voltage']
            . ':' . $data['intensity'];

         if(!rrd_update($this->filename, array($dataStr)))
         {
             throw new Exception(rrd_error());
         }
    }

    public function info()
    {
        $this->open();

        $info = rrd_info($this->filename);
        foreach($info as &$value)
        {
            if(is_float($value) && (is_nan($value) || is_infinite($value))) $value = null;
        }

        return $info;
    }

    public function graph($datasource, $period, $width, $height)
    {
        $this->open();

        $graphObj = new \RRDGraph('-');
        $graphObj->setOptions($this->getGraphOptions($datasource, $period, $width, $height));
        $data = $graphObj->saveVerbose();

        if($data === false)
        {
            throw new Exception(rrd_error());
        }

        return $data['image'];
    }

    protected function open()
    {
        if(!file_exists($this->filename))
        {
            if (!rrd_create($this->filename, $this->getCreateOptions()))
            {
                throw new Exception(rrd_error());
            }
        }
    }

    protected function getCreateOptions()
    {
        $options = array(
            "--step", "300",
            "DS:luminosite:GAUGE:600:0:U",
            "DS:ouvert:GAUGE:600:0:1",
            "DS:ferme:GAUGE:600:0:1",
            "DS:voltage:GAUGE:600:0:U",
            "DS:intensity:GAUGE:600:0:U",
            "RRA:AVERAGE:0.5:1:576",
            "RRA:AVERAGE:0.5:12:336",
            "RRA:AVERAGE:0.5:12:1440",
            "RRA:AVERAGE:0.5:288:730",
            "RRA:MIN:0.5:1:576",
            "RRA:MIN:0.5:12:336",
            "RRA:MIN:0.5:12:1440",
            "RRA:MIN:0.5:288:730",
            "RRA:MAX:0.5:1:576",
            "RRA:MAX:0.5:12:336",
            "RRA:MAX:0.5:12:1440",
            "RRA:MAX:0.5:288:730",
        );

        return $options;
    }

    protected function getGraphOptions($datasource, $period, $width, $height)
    {
        switch($period)
        {
            case 'daily':
                $start = 'end-25hours';
                break;
            case 'weekly':
                $start = 'end-8days';
                break;
            case 'monthly':
                $start = 'end-1month-1day';
                break;
            case 'yearly':
                $start = 'end-13months';
                break;
        }

	$res = $datasource == 'voltage' ? '3.2' : '3.1';
	
        $def = "DEF:val=" . $this->filename . ":" . $datasource . ":AVERAGE";
        $line = "LINE1:val#FFFFFF";
        $options =  array(
            "--width", $width,
            "--height", $height,
            "--full-size-mode",
            "--slope-mode",
            //"--only-graph",
            "--start", $start,
            "--end", "now",
            //"--x-grid", "none",
            //"--y-grid", "none",
            //"--no-legend",
            "--color", "BACK#00000000",
            "--color", "CANVAS#00000000",
            "--color", "SHADEA#00000000",
            "--color", "SHADEB#00000000",
            "--color", "ARROW#FFFFFF",
            "--color", "AXIS#FFFFFF",
            "--color", "FONT#FFFFFF",
            "--imgformat", "PNG",
            $def,
            "VDEF:min=val,MINIMUM",
            "VDEF:max=val,MAXIMUM",
            "VDEF:avg=val,AVERAGE",
            "VDEF:last=val,LAST",
            $line,
            "GPRINT:min:Min\: %${res}lf",
            "GPRINT:max:Max\: %${res}lf",
            "GPRINT:avg:Avg\: %${res}lf",
            "GPRINT:last:Cur\: %${res}lf",
        );

        if($datasource == 'luminosite')
        {
            $options[] = "--logarithmic";
        }

        return $options;

    }
}
