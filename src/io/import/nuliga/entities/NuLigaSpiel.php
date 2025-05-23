<?php

require_once __DIR__."/../../../../handball/Mannschaft.php";
require_once __DIR__."/../../../../handball/MannschaftsMeldung.php";
require_once __DIR__."/../../../../handball/Spiel.php";
require_once __DIR__."/../../../../handball/Gegner.php";

class NuLigaSpiel implements \JsonSerializable {
    
    public $wochentag;
    public $terminOffen;
    public $datum;
    public $uhrzeit;
    public $halle;
    public $spielNr;
    public $heimmannschaft;
    public $gastmannschaft;
    public $ergebnisOderSchiris;
    public $spielbericht;
    public $spielberichtsGenehmigung;


    public function isSpielFrei(): bool {
        return 
            $this->gastmannschaft === "spielfrei"
         || $this->heimmannschaft === "spielfrei";
    }

    public function isUngueltig(): bool {
        return 
            empty($this->spielNr) 
         || empty($this->halle);
    }

    public function getAnwurf(): ?DateTime {
        if($this->terminOffen){
            return null;
        }
        $datum_und_zeit = $this->datum." ".$this->uhrzeit;
        $anwurf = DateTime::createFromFormat('d.m.Y H:i', $datum_und_zeit);
        if($anwurf){
            return $anwurf;
        }
        return null;
    }
    public function getLogOutput(): string {
        $termin = "Termin offen";
        if(!$this->terminOffen){
            $termin = $this->datum." ".$this->uhrzeit;
        }
        return $termin.": ".$this->heimmannschaft." vs. ".$this->gastmannschaft.": Halle ".$this->halle;
    } 
    
    public function extractSpiel(MannschaftsMeldung $meldung, string $teamName): Spiel{
        $spiel = new Spiel();
        $spiel->spielNr = $this->spielNr;
        $spiel->mannschaftsMeldung = $meldung;
        
        $spiel->mannschaft = $meldung->mannschaft;
        $spiel->anwurf = $this->getAnwurf();
        $spiel->halle = $this->halle;
        
        $heim = $this->sanitizeTeamname($this->heimmannschaft);
        $gast = $this->sanitizeTeamname($this->gastmannschaft);

        if( ($heim === $teamName) || 
        // In manchen Gruppen (z.B. Turnieren) wird die erste Mannschaft hinten mit "1" ergänzt, was hier abgefangen werden soll
        ($heim === $teamName." 1") ){
            $spiel->heimspiel = true;
            $spiel->gegner = Gegner::fromName($gast);
        } else {
            $spiel->heimspiel = false;
            $spiel->gegner = Gegner::fromName($heim);
        }
        $spiel->gegner->zugehoerigeMeldung = $meldung;
        
        return $spiel;
    }
    
    private function sanitizeTeamname(string $teamName): string {
        // entfernen von "(a.K.)", welches für "außer Konkurrenz" steht
        $ohneAK = str_replace("(a.K.)", "", $teamName);
        $getrimmt = trim($ohneAK);
        return $getrimmt;
    }

    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}
?>