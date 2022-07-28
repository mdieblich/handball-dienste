<?php

require_once __DIR__."/Mannschaft.php";
require_once __DIR__."/MannschaftsMeldung.php";
require_once __DIR__."/Gegner.php";
require_once __DIR__."/Dienst.php";
require_once __DIR__."/../zeit/ZeitRaum.php";
require_once __DIR__."/../zeit/ZeitlicheDistanz.php";

class Spiel{

    const VORBEREITUNG_VOR_ANWURF = "PT60M";
    const SPIELDAUER              = "PT90M";
    const NACHBEREITUNG_NACH_ENDE = "PT60M";
    const FAHRTZEIT_AUSWAERTS     = "PT60M";

    public int $id;
    
    public int $spielNr;
    public MannschaftsMeldung $mannschaftsMeldung;
    
    // TODO Referenz auf Mannschaft enfternen: Redundant über die Meldung
    public Mannschaft $mannschaft;
    public Gegner $gegner;

    public ?DateTime $anwurf;
    public int $halle;
    public bool $heimspiel;
    
    // TODO prüfen: kann auf diese Referenz verzichtet werden?
    public array $dienste = array();
    
    // Zuweisung von Diensten
    public function getDienst(string $dienstart): ?Dienst{
        if(array_key_exists($dienstart, $this->dienste)){
            return $this->dienste[$dienstart];
        }
        return null;
    }

    // Zeitfunktionen 
    public function getSpielEnde(): ?DateTime {
        if(empty($this->anwurf)){
            return null;
        }
        $anwurfCopy = clone $this->anwurf;
        return $anwurfCopy->add(new DateInterval(self::SPIELDAUER));
    }
    
    public function getSpielzeit(): ?ZeitRaum {
        if(empty($this->anwurf)){
            return null;
        }
        return new Zeitraum($this->anwurf, $this->getSpielEnde());
    }
    
    public function getAbfahrt(): ?DateTime {
        if(empty($this->anwurf)){
            return null;
        }
        $anwurfCopy = clone $this->anwurf;
        $abfahrt = $anwurfCopy->sub(new DateInterval(self::VORBEREITUNG_VOR_ANWURF));
        if(!$this->heimspiel){
            $abfahrt->sub(new DateInterval(self::FAHRTZEIT_AUSWAERTS));
        }
        return $abfahrt;
    }

    public function getRueckkehr(): ?DateTime {
        if(empty($this->anwurf)){
            return null;
        }
        $rueckkehr = $this->getSpielEnde()->add(new DateInterval(self::NACHBEREITUNG_NACH_ENDE));
        if(!$this->heimspiel){
            $rueckkehr = $rueckkehr->add(new DateInterval(self::FAHRTZEIT_AUSWAERTS));
        }
        return $rueckkehr;
    }

    public function getAbwesenheitsZeitraum(): ?ZeitRaum {
        if(empty($this->anwurf)){
            return null;
        }
        return new ZeitRaum($this->getAbfahrt(), $this->getRueckkehr());
    }
    
    public function getZeitlicheDistanz(Spiel $spiel): ?ZeitlicheDistanz {

        $eigenesSpiel = $this->getAbwesenheitsZeitraum();
        $anderesSpiel = $spiel->getAbwesenheitsZeitraum();

        if(empty($eigenesSpiel)||empty($anderesSpiel)){
            return null;
        }
        
        $gleicheHalle = ($this->halle == $spiel->halle);
        if($gleicheHalle){
            $eigenesSpiel = $this->getSpielzeit();
            $anderesSpiel = $spiel->getSpielzeit();
        }

        return $eigenesSpiel->getZeitlicheDistanz($anderesSpiel);
    }

    public function isAmGleichenTag(?Spiel $other): bool {
        if(empty($other)){
            return false;
        }
        if(empty($this->anwurf) || empty($other->anwurf)){
            return false;
        }
        return $this->anwurf->format("Y-m-d") == $other->anwurf->format("Y-m-d");
    }

}

?>