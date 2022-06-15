<?php
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../entity/spiel.php";
require_once __DIR__."/../dao/gegner.php";

class SpielAenderung{
    public Spiel $alt;
    public NuLigaSpiel $neu;
    
    public function __construct(Spiel $alt, NuLigaSpiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }

    public function getBegegnungsbezeichnung(array $alleMannschaften, GegnerDAO $gegnerDAO): string{
        $message = "";
        $mannschaft = $alleMannschaften[$this->alt->getMannschaft()]->getName();
        $gegner = $gegnerDAO->fetch($this->alt->getGegner())->getName();
        
        if($this->alt->isHeimspiel()){
            return "HEIM $mannschaft vs. $gegner";
        } else{
            return "AUSWÄRTS $gegner vs. $mannschaft";
        }   
    }

    public function getAenderung(): string{
        $message = "";
        if($this->alt->getAnwurf() != $this->neu->getAnwurf()){

            $message .= "Anwurf "
                ."von [".$this->alt->getAnwurf()->format("d.m.Y H:i")."]"
                ." zu [".$this->neu->getAnwurf()->format("d.m.Y H:i")."]";
        }
        if($this->alt->getHalle() != $this->neu->getHalle()){
            $message .= " Halle "
                ."von [".$this->alt->getHalle()."]"
                ." zu [".$this->neu->getHalle()."]";
        }
        return $message;
    }
}
?>