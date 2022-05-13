<?php

class ZeitlicheDistanz {
    public bool $ueberlappend;
    public bool $vorher;
    public DateInterval $abstand;

    public static function fromZeitraeumen(
        DateTime $start_a, DateTime $ende_a, 
        DateTime $start_b, DateTime $ende_b): ZeitlicheDistanz{
        
        $distanz = new ZeitlicheDistanz();
        $distanz->ueberlappend = $ende_a > $start_b && $ende_b > $start_a;
        $distanz->vorher = $start_b < $start_a;
        if($distanz->vorher){
            // anderes Spiel ist vorher
            $distanz->abstand = $start_a->diff($ende_b);
        } else { 
            // anderes Spiel ist nachher
            $distanz->abstand = $ende_a->diff($start_b);
        }
        return $distanz;
    }

    public function getDebugOutput(): string {
        $debugOutput = ($this->vorher?"Vorher":"Nachher").", ";
        if($this->ueberlappend){
            $debugOutput .= "(überlappend) ";
        }
        $debugOutput .= $this->abstand->format("%r%Y.%M.%D %H:%I");
        return $debugOutput;
    }
}
?>