<?php
class Dienst {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getMannschaft(): int {
        return $this->assoc_array["Mannschaft"];
    }
    
    public function getGegner(): string {
        return $this->assoc_array["Gegner"];
    }

    public function isHeimspiel(): bool {
        return $this->assoc_array["Heimspiel"] != "0";
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getNuligaID().". <b>".$this->getSpielnameDebugOutput()."</b>".
            "</div>";
    }

    private function getSpielnameDebugOutput(): string {
        if($this->isHeimspiel()){
            return $this->getMannschaft()." gegen ".$this->getGegner();
        } 
        return $this->getGegner()." gegen ".$this->getMannschaft();

    }
}
?>