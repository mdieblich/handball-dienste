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
    public MannschaftsMeldung $mannschaftsMeldung;  public int $mannschaftsMeldung_id;
    
    // TODO Referenz auf Mannschaft enfternen: Redundant über die Meldung
    public Mannschaft $mannschaft;                  public int $mannschaft_id;
    public Gegner $gegner;                          public int $gegner_id;

    public ?DateTime $anwurf = null;
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

    public function anwurfDiffers(Spiel $other): bool{
        if(null === $this->anwurf){
            return null !== $other->anwurf;
        }
        if (null === $other->anwurf){
            return true;
        }
        return ($this->anwurf != $other->anwurf);
    }

    public function halleDiffers(Spiel $other): bool{
        return !$this->isInGleicherHalle($other);
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

    public function calculate_distanz_to(Spiel $other): ?ZeitlicheDistanz {

        $eigenesSpiel = $this->getAbwesenheitsZeitraum();
        $anderesSpiel = $other->getAbwesenheitsZeitraum();

        if(empty($eigenesSpiel)||empty($anderesSpiel)){
            return null;
        }
        
        $gleicheHalle = ($this->halle == $other->halle);
        if($gleicheHalle){
            $eigenesSpiel = $this->getSpielzeit();
            $anderesSpiel = $other->getSpielzeit();
        }

        return ZeitlicheDistanz::from_a_to_b($eigenesSpiel, $anderesSpiel);
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

    public function isInGleicherHalle(?Spiel $other): bool {
        if(empty($other)){
            return false;
        }
        return $this->halle === $other->halle;
    }

    public function createDienste(Log $logfile=null): void{
        if(empty($logfile)){
            $logfile = new NoLog();
        }
        if($this->heimspiel){
            $logfile->log("Heimspiel: Lege Zeitnehmer und Cateringienst an.");
            $this->createDienst(Dienstart::ZEITNEHMER);
            $this->createDienst(Dienstart::CATERING);
            if($this->gegner->stelltSekretaerBeiHeimspiel){
                // wenn der Gegner beim Heimspiel den Sekretär stellt, so müssen wir das auch
                $logfile->log("Gegner stellt Sekretär: Lege Sekretärendienst an.");
                $this->createDienst(Dienstart::SEKRETAER);
            }
        } else {
            if(!$this->gegner->stelltSekretaerBeiHeimspiel){
                $logfile->log("Auswärtsspiel: Lege Sekretärdienst an.");
                $this->createDienst(Dienstart::SEKRETAER);
            }
        }
    }
    public function createDienst(string $dienstart): Dienst{
        $dienst = new Dienst();
        $dienst->spiel = $this;
        $dienst->dienstart = $dienstart;
        $this->dienste[$dienstart] = $dienst;
        return $dienst;
    }

    public function getBegegnungsbezeichnung(): string{
        $anwurf = $this->anwurf->format("d.m.Y H:i");
        $mannschaftsName = $this->mannschaft->getName();
        $gegnerName = $this->gegner->getName();
        
        if($this->heimspiel){
            return "$anwurf HEIM (".$this->halle.") $mannschaftsName vs. $gegnerName";
        } else{
            return "$anwurf AUSWÄRTS (".$this->halle.") $gegnerName vs. $mannschaftsName";
        }   
    }
}

?>