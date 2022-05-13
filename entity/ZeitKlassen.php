<?php

class ZeitRaum {
    public DateTime $start;
    public DateTime $ende;

    public function __construct(DateTime $start, DateTime $ende) {
        $this->start = $start;
        $this->ende = $ende;
    }
}

class ZeitlicheDistanz {
    public bool $ueberlappend;
    public bool $vorher;
    public DateInterval $abstand;

    public static function fromZeitraeumen(
        DateTime $start_a, DateTime $ende_a, 
        DateTime $start_b, DateTime $ende_b): ZeitlicheDistanz{

        $a = new ZeitRaum($start_a, $ende_a);
        $b = new ZeitRaum($start_b, $ende_b);
        return self::fromZeitRaeumen2($a, $b);
    }
    
    public static function fromZeitRaeumen2(ZeitRaum $a, ZeitRaum $b): ZeitlicheDistanz{
        
        $distanz = new ZeitlicheDistanz();
        $distanz->ueberlappend = $a->ende > $b->start && $b->ende > $a->start;
        $distanz->vorher = $b->start < $a->start;
        if($distanz->vorher){
            // anderes Spiel ist vorher
            $distanz->abstand = $a->start->diff($b->ende);
        } else { 
            // anderes Spiel ist nachher
            $distanz->abstand = $a->ende->diff($b->start);
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