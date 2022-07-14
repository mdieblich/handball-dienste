<?php
require_once __DIR__."/../handball/Spiel.php";

class SpielAenderung{
    public Spiel $alt;
    public Spiel $neu;
    
    public function __construct(Spiel $alt, Spiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }

    public function getBegegnungsbezeichnung(): string{
        $anwurf = $this->neu->anwurf->format("d.m.Y H:i");
        $mannschaftsName = $this->alt->mannschaft->getName();
        $gegnerName = $this->alt->gegner->getName();
        
        if($this->alt->heimspiel){
            return "$anwurf HEIM (".$this->neu->halle.") $mannschaftsName vs. $gegnerName";
        } else{
            return "$anwurf AUSWÄRTS (".$this->neu->halle.") $gegnerName vs. $mannschaftsName";
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