<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/dienst.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/ZeitlicheDistanz.php";

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
    public function getNuligaID(): int {
        return $this->assoc_array["nuliga_id"];
    }

    public function getMannschaft(): int {
        return $this->assoc_array["mannschaft"];
    }
    
    public function getGegner(): string {
        return $this->assoc_array["gegner"];
    }
    
    public function isHeimspiel(): bool {
        return $this->assoc_array["heimspiel"] != "0";
    }
    
    public function getHalle(): int {
        return $this->assoc_array["halle"];        
    }

    public function getAnwurf(): DateTime {
        return DateTime::createFromFormat('Y-m-d H:i:s',  $this->assoc_array["anwurf"]);
    }
    public function getSpielEnde(): DateTime {
        return  $this->getAnwurf()->add(new DateInterval(self::SPIELDAUER));
    }

    public function getAbfahrt(): DateTime {
        $abfahrt = $this->getAnwurf()->sub(new DateInterval(self::VORBEREITUNG_VOR_ANWURF));
        if(!$this->isHeimspiel()){
            $abfahrt = $abfahrt->sub(new DateInterval(self::FAHRTZEIT_AUSWAERTS));
        }
        return $abfahrt;
    }

    public function getRueckkehr(): DateTime {
        $rueckkehr = $this->getSpielEnde()->add(new DateInterval(self::NACHBEREITUNG_NACH_ENDE));
        if(!$this->isHeimspiel()){
            $rueckkehr = $rueckkehr->add(new DateInterval(self::FAHRTZEIT_AUSWAERTS));
        }
        return $rueckkehr;
    }
    
    public function getZeitlicheDistanz(Spiel $spiel): ZeitlicheDistanz {
        $distanz = new ZeitlicheDistanz();
        $gleicheHalle = $this->getHalle() == $spiel->getHalle();
        if($gleicheHalle){
            $eigenerAnwurf = $this->getAnwurf();
            $eigenesEnde   = $this->getSpielEnde();
            $andererAnwurf = $spiel->getAnwurf();
            $anderesEnde   = $spiel->getSpielEnde();
            
            $distanz->ueberlappend = $eigenesEnde > $andererAnwurf && $anderesEnde > $eigenerAnwurf;
            $distanz->vorher = $andererAnwurf < $eigenerAnwurf;
            if($distanz->vorher){
                // anderes Spiel ist vorher
                $distanz->abstand = $eigenerAnwurf->diff($anderesEnde);
            } else { 
                // anderes Spiel ist nachher
                $distanz->abstand = $eigenesEnde->diff($andererAnwurf);
            }
        } else{
            $eigeneAbfahrt   = $this->getAbfahrt();
            $eigeneRueckkehr = $this->getRueckkehr();
            $andereAbfahrt   = $spiel->getAbfahrt();
            $andereRueckkehr = $spiel->getRueckkehr();
            
            $distanz->ueberlappend = $eigeneRueckkehr > $andereAbfahrt && $andereRueckkehr > $eigeneAbfahrt;
            $distanz->vorher = $andereAbfahrt < $andereAbfahrt;
            if($distanz->vorher){
                $distanz->abstand = $andereRueckkehr->diff($eigeneAbfahrt);
            } else { // anderes Spiel ist nachher
                $distanz->abstand = $eigeneRueckkehr->diff($andereAbfahrt);
            }
        }
        return $distanz;
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
            $this->getNuligaID().". <b>".$this->getSpielnameDebugOutput()."</b>".
            "</div>";
    }

    private function getSpielnameDebugOutput(): string {
        if($this->isHeimspiel()){
            return $this->getMannschaft()." gegen ".$this->getGegner();
        } 
        return $this->getGegner()." gegen ".$this->getMannschaft();

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
}
?>