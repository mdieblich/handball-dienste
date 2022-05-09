<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/person.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/mannschaft.php";

class Dienstart{
    const ZEITNEHMER = "Zeitnehmer";
    const SEKRETAER = "Sekretär";
    const values = array(self::ZEITNEHMER, self::SEKRETAER);
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

    public function getPerson(): string {
        return $this->assoc_array["person"];
    }

    public function getDebugOutput($mannschaften = array()): string {
        return 
            "<div>".
            "<b>".$this->getSpiel().". </b>".$this->getDienstart().": ".
            $this->getAnsetzungDebugOutput($mannschaften).
            "</div>";
    }

    private function getAnsetzungDebugOutput($mannschaften): string {

        $mannschaftDebugOutput = $this->getMannschaft();
        $spielerDebugOuput = $this->getPerson();

        if(array_key_exists($this->getMannschaft(), $mannschaften)){
            $mannschaft = $mannschaften[$this->getMannschaft()];
            $mannschaftDebugOutput = $mannschaft->getName();
            $spieler = $mannschaft->getSpieler($this->getPerson());
            if(!empty($spieler)){
                return $spielerDebugOuput = $spieler->getName();
            }
        }
        return 
            "Mannschaft ".$mannschaftDebugOutput.", ".
            "Person ".$spielerDebugOuput;

    }

}
?>