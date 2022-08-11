<?php

require_once __DIR__."/ZeitlicheDistanz.php";

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
        if($distanz->ueberlappend){
            $distanz->seconds = 0;
            return $distanz;
        }
        
        $vorher = $other->start < $this->start;
        if($vorher){
            $distanz->seconds = $other->ende->getTimestamp() - $this->start->getTimestamp();
        } else { // anderes Spiel ist nachher
            $distanz->seconds = $other->start->getTimestamp() - $this->ende->getTimestamp();
        }
        return $distanz;
    }
}
?>