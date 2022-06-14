<?php
class Gegner {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getVerein(): string {
        return $this->assoc_array["verein"];
    }
    public function getNummer(): int {
        return $this->assoc_array["nummer"];
    }

    public function getName(): string {
        $name = $this->getVerein();
        if($this->getNummer() === 1){
            return $name;
        }else{
            switch($this->getNummer()){
                case 1: return $name." I";
                case 2: return $name." II";
                case 3: return $name." III";
                case 4: return $name." IV";
                case 5: return $name." V";
                default: return $name." ".$this->getNummer();
            }
        }
    }

    public function getGeschlecht(): string {
        return $this->assoc_array["geschlecht"];
    }
    
    public function getLiga(): string {
        return $this->assoc_array["liga"];
    }
    
    public function stelltSekretearBeiHeimspiel(): bool {
        return $this->assoc_array["stelltSekretaerBeiHeimspiel"];
    }
}
?>