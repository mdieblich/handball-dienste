<?php
require_once __DIR__."/mannschaft.php";

class Dienstart{
    const ZEITNEHMER = "Zeitnehmer";
    const SEKRETAER = "Sekretär";
    const CATERING = "Catering";
    const values = array(self::ZEITNEHMER, self::SEKRETAER, self::CATERING);
}

class Dienst {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getSpiel(): int {
        return $this->assoc_array["spiel"];
    }
    
    public function getDienstart(): string {
        return $this->assoc_array["dienstart"];
    }
    
    public function getMannschaft(): string {
        return $this->assoc_array["mannschaft"];
    }


    public function getDebugOutput($mannschaften = array()): string {
        return 
            "<div>".
            "<b>".$this->getSpiel().". </b>".$this->getDienstart().": ".
            $this->getAnsetzungDebugOutput($mannschaften).
            "</div>";
    }

    private function getAnsetzungDebugOutput($mannschaften): string {

        if(array_key_exists($this->getMannschaft(), $mannschaften)){
            $mannschaft = $mannschaften[$this->getMannschaft()];
            return $mannschaft->getName();
        }
        return $this->getMannschaft();
    }

}
?>