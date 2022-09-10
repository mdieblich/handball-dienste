<?php

class Log {

    private $fileHandle;
    public function __construct(string $purpose) {
        $filename = plugin_dir_path(__FILE__).date("Y.m.d_H.i.s")."-".$purpose.".txt";
        $this->fileHandle = fopen($filename, "a");
    }

    public function __destruct(){
        fclose($this->fileHandle);
    }

    public function log(string $message){
        fwrite($this->fileHandle, $message."\n");
    }
}

?>