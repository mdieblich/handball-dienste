<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/bot/DBConnection.php";

class Dienstebot {

    private DBConnection dbConnection;
    
    public function __construct(){
        $this->dbConnection = new DBConnection();
    }

    public function fetchInbox(){

    }
}
?>