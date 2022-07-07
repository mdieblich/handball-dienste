<?php
require_once __DIR__."/Dienst.php";
require_once __DIR__."/ZeitKlassen.php";

class Spiel {

    const VORBEREITUNG_VOR_ANWURF = "PT60M";
    const SPIELDAUER              = "PT90M";
    const NACHBEREITUNG_NACH_ENDE = "PT60M";
    const FAHRTZEIT_AUSWAERTS     = "PT60M";
    
    private $assoc_array;

    private $dienste = array();

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }
    
    public function getMannschaftsMeldung(): int {
        return $this->assoc_array["mannschaftsmeldung"];
    }
    public function getSpielNr(): int {
        return $this->assoc_array["spielnr"];
    }

    public function getMannschaft(): int {
        return $this->assoc_array["mannschaft"];
    }
    
    public function getGegner(): int {
        return $this->assoc_array["gegner"];
    }
    
    public function isHeimspiel(): bool {
        return $this->assoc_array["heimspiel"] != "0";
    }
    
    public function getHalle(): int {
        return $this->assoc_array["halle"];        
    }

    public function getAnwurf(): ?DateTime {
        if(empty($this->assoc_array["anwurf"])){
            return null;
        }
        return DateTime::createFromFormat('Y-m-d H:i:s',  $this->assoc_array["anwurf"]);
    }
    public function getSpielEnde(): ?DateTime {
        $anwurf = $this->getAnwurf();
        if(empty($anwurf)){
            return null;
        }
        return  $anwurf->add(new DateInterval(self::SPIELDAUER));
    }

    public function getSpielzeit(): ?ZeitRaum {
        $anwurf = $this->getAnwurf();
        if(empty($anwurf)){
            return null;
        }
        return new Zeitraum($anwurf, $this->getSpielEnde());
    }

    public function getAbfahrt(): ?DateTime {
        $anwurf = $this->getAnwurf();
        if(empty($anwurf)){
            return null;
        }
        $abfahrt = $anwurf->sub(new DateInterval(self::VORBEREITUNG_VOR_ANWURF));
        if(!$this->isHeimspiel()){
            $abfahrt = $abfahrt->sub(new DateInterval(self::FAHRTZEIT_AUSWAERTS));
        }
        return $abfahrt;
    }

    public function getRueckkehr(): ?DateTime {
        $anwurf = $this->getAnwurf();
        if(empty($anwurf)){
            return null;
        }
        $rueckkehr = $this->getSpielEnde()->add(new DateInterval(self::NACHBEREITUNG_NACH_ENDE));
        if(!$this->isHeimspiel()){
            $rueckkehr = $rueckkehr->add(new DateInterval(self::FAHRTZEIT_AUSWAERTS));
        }
        return $rueckkehr;
    }

    public function getAbwesenheitsZeitraum(): ?ZeitRaum {
        $anwurf = $this->getAnwurf();
        if(empty($anwurf)){
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
        
        $gleicheHalle = $this->getHalle() == $spiel->getHalle();
        if($gleicheHalle){
            $eigenesSpiel = $this->getSpielzeit();
            $anderesSpiel = $spiel->getSpielzeit();
        }

        return $eigenesSpiel->getZeitlicheDistanz($anderesSpiel);
    }
    
    public function getSpielzeitDebugOutput(): string {
        return 
        $this->getAbfahrt()->format("H:i")." - ".
        $this->getAnwurf()->format("H:i")."-".
        $this->getSpielEnde()->format("H:i")." - ".
            $this->getRueckkehr()->format("H:i");
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getSpielNr().". <b>".$this->getSpielnameDebugOutput()."</b>".
            "</div>";
    }

    private function getSpielnameDebugOutput(): string {
        if($this->isHeimspiel()){
            return $this->getMannschaft()." gegen #".$this->getGegner();
        } 
        return "#".$this->getGegner()." gegen ".$this->getMannschaft();

    }

    public function addDienst(Dienst $dienst){
        $this->dienste[$dienst->getDienstart()] = $dienst;
    }

    public function getDienste(): array{
        return $this->dienste;
    }

    public function getDienst(string $dienstart): ?Dienst{
        if(array_key_exists($dienstart, $this->dienste)){
            return $this->dienste[$dienstart];
        }
        return null;
    }

    public function isAmGleichenTag(?Spiel $other): bool {
        if(empty($other)){
            return false;
        }
        $anwurfA = $this->getAnwurf();
        $anwurfB = $other->getAnwurf();
        if(empty($anwurfA) || empty($anwurfB)){
            return false;
        }
        return $anwurfA->format("Y-m-d") == $anwurfB->format("Y-m-d");
    }

    public static function getIDs(array $spiele): array {
        $ids = array();
        foreach($spiele as $spiel){
            $ids[] = $spiel->getID();
        }
        return $ids;
    }
    
}
?>