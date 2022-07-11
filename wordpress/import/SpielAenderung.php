<?php
require_once __DIR__."/NuLigaSpiel.php";
require_once __DIR__."/../handball/Spiel.php";

class SpielAenderung{
    public Spiel $alt;
    public NuLigaSpiel $neu;
    
    public function __construct(Spiel $alt, NuLigaSpiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }

    public function getBegegnungsbezeichnung(): string{
        $message = "";
        $mannschaftsName = $this->alt->mannschaft->getName();
        $gegnerName = $this->alt->gegner->getName();
        
        if($this->alt->heimspiel){
            return "HEIM $mannschaftsName vs. $gegnerName";
        } else{
            return "AUSWÄRTS $gegnerName vs. $mannschaftsName";
        }   
    }

    public function getAenderung(): string{
        $message = "";
        if($this->alt->anwurf != $this->neu->anwurf){

            $message .= "Anwurf "
                ."von [".$this->alt->anwurf->format("d.m.Y H:i")."]"
                ." zu [".$this->neu->anwurf->format("d.m.Y H:i")."]";
        }
        if($this->alt->halle != $this->neu->halle){
            $message .= " Halle "
                ."von [".$this->alt->halle."]"
                ." zu [".$this->neu->halle."]";
        }
        return $message;
    }
}
?>