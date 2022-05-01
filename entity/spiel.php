<?php
class Spiel {
    private $assoc_array;

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

    public function getAnwurf(): DateTime {
        return DateTime::createFromFormat('Y-m-d H:i:s',  $this->assoc_array["anwurf"]);
    }

    public function getAbfahrt(): DateTime {
        if($this->isHeimspiel()){
            return $this->getAnwurf()->sub(new DateInterval("PT1H"));
        }
        return $this->getAnwurf()->sub(new DateInterval("PT2H"));
    }

    public function getRueckkehr(): DateTime {
        if($this->isHeimspiel()){
            return $this->getAnwurf()->add(new DateInterval("PT2H30M"));
        }
        return $this->getAnwurf()->add(new DateInterval("PT3H30M"));
    }

    public function isGleichzeitig(Spiel $spiel): bool {
        $eigeneAbfahrt   = $this->getAbfahrt();
        $eigeneRueckkehr = $this->getRueckkehr();
        $andereAbfahrt   = $spiel->getAbfahrt();
        $andereRueckkehr = $spiel->getRueckkehr();

        if($eigeneRueckkehr > $andereAbfahrt && $andereRueckkehr > $eigeneAbfahrt){
            return true;
        }

        return false;
    }

    public function getSpielzeitDebugOutput(): string {
        return 
            $this->getAbfahrt()->format("H:i")." - ".
            $this->getAnwurf()->format("H:i")." - ".
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
}
?>