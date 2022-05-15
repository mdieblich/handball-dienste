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
        $vorher = $other->start < $this->start;
        if($vorher){
            $distanz->seconds = $other->ende->getTimestamp() - $this->start->getTimestamp();
        } else { // anderes Spiel ist nachher
            $distanz->seconds = $other->start->getTimestamp() - $this->ende->getTimestamp();
        }
        return $distanz;
    }
}

class ZeitlicheDistanz {
    public bool $ueberlappend;
    public int $seconds;

    public function isNaeher(?ZeitlicheDistanz $other): bool{
        if(!isset($other)){
            return true;
        }
        return abs($this->seconds) < abs($other->seconds);
    }

    public function isVorher(): bool {
        return $this->seconds < 0;
    }

    public function getDebugOutput(): string {
        $debugOutput = "";
        if($this->ueberlappend){
            $debugOutput .= "(überlappend) ";
        }
        $debugOutput .= ($this->seconds<0)?"-":" ";
        $restSekunden = abs($this->seconds);
        $tage = intdiv($restSekunden, 3600*24);
        $restSekunden = $restSekunden % (3600*24);
        if($tage > 0) {
            $debugOutput .= "$tage d ";
        }
        $stunden = intdiv($restSekunden, 3600);
        $restSekunden %= 3600;
        $minuten = intdiv($restSekunden, 60);
        $restSekunden %= 60;
        $debugOutput .= "$stunden:$minuten:$restSekunden";
        return $debugOutput;
    }
}
?>