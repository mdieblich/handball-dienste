<?php

class ZeitRaum {
    public DateTime $start;
    public DateTime $ende;

    public function __construct(DateTime $start, DateTime $ende) {
        $this->start = $start;
        $this->ende = $ende;
    }

    public function getZeitlicheDistanz(Zeitraum $other): ZeitlicheDistanz{
        $distanz = new ZeitlicheDistanz();
        $distanz->ueberlappend = $this->ende > $other->start && $other->ende > $this->start;
        $distanz->vorher = $other->start < $this->start;
        if($distanz->vorher){
            // anderes Spiel ist vorher
            $distanz->abstand = $this->start->diff($other->ende);
        } else { 
            // anderes Spiel ist nachher
            $distanz->abstand = $this->ende->diff($other->start);
        }
        return $distanz;
    }
}

class ZeitlicheDistanz {
    public bool $ueberlappend;
    public bool $vorher;
    public DateInterval $abstand;

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