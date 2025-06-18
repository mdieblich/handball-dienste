<?php

class Log {

    private $fileHandle;
    private $indentation = "";
    public function __construct(string $purpose) {
        $filename = self::LOG_DIRECTORY().date("Y.m.d_H.i.s")."-".$purpose.".txt";
        $this->fileHandle = fopen($filename, "a");
    }

    public function __destruct(){
        fclose($this->fileHandle);
    }

    public function log(string $message){
        fwrite($this->fileHandle, $this->indentation.$message."\n");
    }
    public function setIndentation(string $indentation){
        $this->indentation = $indentation;
    }
    public function resetIndentation(){
        $this->indentation = "";
    }

    public function log_withoutNewline(string $message){
        fwrite($this->fileHandle, $message);
    }

    public static function findFiles(string $purpose) : array {
        $fileNames = array();
        $path = plugin_dir_path(__FILE__);
        $path_length = strlen($path);
        foreach(glob("$path*-".$purpose.".txt") as $fileName){
            $date = DateTime::createFromFormat("Y.m.d_H.i.s", substr($fileName, $path_length, 19));
            $fileNames[$date->getTimestamp()] =  $fileName;
        }
        return $fileNames;
    }

    public static function LOG_DIRECTORY(): string{
        return plugin_dir_path(__FILE__);
    }
}

class NoLog extends Log {
    public function __construct() {}
    public function __destruct() {}
    public function log(string $message){}
    public function log_withoutNewline(string $message){}
}

?>