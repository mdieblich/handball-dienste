<?php
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../entity/Spiel.php";
require_once __DIR__."/../dao/GegnerDAO.php";

class SpielAenderung{
    public Spiel $alt;
    public NuLigaSpiel $neu;
    
    public function __construct(Spiel $alt, NuLigaSpiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }

    public function getBegegnungsbezeichnung(array $alleMannschaften, GegnerDAO $gegnerDAO): string{
        $message = "";
        $mannschaftsName = $alleMannschaften[$this->alt->getMannschaft()]->getName();
        $gegnerName = $gegnerDAO->findGegner($this->alt->getGegner())->name();
        
        if($this->alt->isHeimspiel()){
            return "HEIM $mannschaftsName vs. $gegnerName";
        } else{
            return "AUSWÄRTS $gegnerName vs. $mannschaftsName";
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