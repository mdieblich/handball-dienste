<?php

class DBConnection {

    const DB_ADDRESS = "127.0.0.1";
    const DB_USER = "root";
    const DB_PASSWORD = ""; //To be completed if you have set a password to root
    const DATABASE = "dienstedienst";
    const PORT = NULL; //Default must be NULL to use default port
    const CHARSET = "utf8";

    private $mysqli;
    
    public function __construct(){
        $this->mysqli = new mysqli(self::DB_ADDRESS, self::DB_USER, self::DB_PASSWORD, self::DATABASE, self::PORT);
        $this->mysqli->set_charset(self::CHARSET);
    }

    public function __destruct(){
        $this->mysqli->close();
    }

    public function getMysqli(): mysqli {
        return $this->mysqli;
    }
}
?>