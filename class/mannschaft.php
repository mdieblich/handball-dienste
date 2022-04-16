<?php

require_once "person.php";

class Mannschaft {
    private $assoc_array;

    private $spieler = array();

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getName(): string {
        return $this->assoc_array["Name"];
    }
    
    public function getLiga(): string {
        return $this->assoc_array["Liga"];
    }

    public function addSpieler(Person $spieler){
        $this->spieler[$spieler->getID()] = $spieler;
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getID().". <b>".$this->getName()."</b>: ".$this->getLiga().
            "</div>";
    }
}
?>