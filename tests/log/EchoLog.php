<?php


require_once __DIR__."/../../src/log/Log.php";

class EchoLog extends Log {
    public function __construct() {}
    public function __destruct() {}
    public function log(string $message){
        echo $message."\n";
    }
    public function log_withoutNewline(string $message){
        echo $message;
    }
}