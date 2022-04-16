<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/person.php";

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

    public function hasSpieler(): bool {
        return count($this->spieler) > 0;
    }

    public function getRandomSpielerID(): int {
        return array_rand($this->spieler);
    }

    public function getSpieler(?int $id): ?Person {
        if(array_key_exists($id, $this->spieler)){
            return $this->spieler[$id];
        }
        return null;
    }

    public function addSpieler(Person $spieler){
        $this->spieler[$spieler->getID()] = $spieler;
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getID().". <b>".$this->getName()."</b>: ".$this->getLiga()."<br>".
            $this->getSpielerListeDebugOutput().
            "</div>";
    }

    private function getSpielerListeDebugOutput(): string {
        $spielerListe = "";
        foreach($this->spieler as $spieler) {
            $spielerListe .= "<li>".$spieler->getName()."</li>\n";
        }
        return "<ul>".$spielerListe."</ul>";
    }
}
?>