<?php
require_once __DIR__."/../Spiel.php";

class SpielAenderung{
    public Spiel $alt;
    public Spiel $neu;
    
    public function __construct(Spiel $alt, Spiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }

    public function getAenderung(): string{
        $message = "";
        $anwurfDiffers = $this->alt->anwurfDiffers($this->neu);
        $halleDiffers = $this->alt->halleDiffers($this->neu);

        if($anwurfDiffers){
            $message .= "Anwurf "
                ."von [".$this->alt->anwurf->format("d.m.Y H:i")."]"
                ." zu [".$this->neu->anwurf->format("d.m.Y H:i")."]";
        }
        if($anwurfDiffers && $halleDiffers){
            $message .= " und ";
        }
        if($halleDiffers){
            $message .= "Halle "
                ."von [".$this->alt->halle."]"
                ." zu [".$this->neu->halle."]";
        }
        return $message;
    }
}
?>