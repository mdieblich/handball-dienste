<?php
class Spiel {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }
    public function getNuligaID(): int {
        return $this->assoc_array["nuliga_id"];
    }

    public function getMannschaft(): int {
        return $this->assoc_array["mannschaft"];
    }
    
    public function getGegner(): string {
        return $this->assoc_array["gegner"];
    }

    public function isHeimspiel(): bool {
        return $this->assoc_array["heimspiel"] != "0";
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