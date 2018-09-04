<?php
namespace libs;

class Log {

    private $fp;

    public function __construct($fileName){
        $this->fp = fopen(ROOT.'logs/'.$fileName.'.log','a');
    }

    public function log($content) {
        
        $date = date('Y-m-d H:i:s');
        $hh = $date."\r\n";
        $hh .= str_repeat('=',120)."\r\n";
        $hh .= $content ."\r\n\r\n";
        fwrite($this->fp,$hh);
    }   

}