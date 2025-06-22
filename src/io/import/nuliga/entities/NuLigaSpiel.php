<?php

require_once __DIR__."/../../../../handball/Mannschaft.php";
require_once __DIR__."/../../../../handball/MannschaftsMeldung.php";
require_once __DIR__."/../../../../handball/Spiel.php";
require_once __DIR__."/../../../../handball/Spiel.php";
require_once __DIR__."/../../Spiel_toBeImported.php";

class NuLigaSpiel implements \JsonSerializable {

    public int $id; // Datenbank-ID
    public int $nuligaLigaID;   // Zur Nachverfolgung, in welcher Liga das Spiel ist
    public int $nuligaTeamID;   // Zur Nachverfolgung, für welches Team das Spiel ist
    public string $wochentag;
    public bool $terminOffen = false;
    public string $datum;
    public string $uhrzeit;
    public string $halle;
    public string $spielNr;
    public string $heimmannschaft;
    public string $gastmannschaft;
    public string $ergebnisOderSchiris;
    public string $spielbericht;
    public string $spielberichtsGenehmigung;


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
        $spiel->spielNr = (int) $this->spielNr;
        $spiel->mannschaftsMeldung = $meldung;
        
        $spiel->mannschaft = $meldung->mannschaft;
        $spiel->anwurf = $this->getAnwurf();
        $spiel->halle = (int) $this->halle;
        
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
    public function extractSpielForImport(MannschaftsMeldung $meldung, string $vereinsname): Spiel_toBeImported{
        $teamName = $vereinsname;
        if($meldung->mannschaft->nummer >= 2){
            $teamName .= " ";
            for($i=0; $i<$meldung->mannschaft->nummer; $i++){
                $teamName .= "I";
            }
        }
        
        $spiel = new Spiel_toBeImported();
        $spiel->spielNr = (int) $this->spielNr;
        $spiel->meldung_id = $meldung->id;
        
        $spiel->anwurf = $this->getAnwurf();
        $spiel->halle = (int) $this->halle;
        
        $heim = $this->sanitizeTeamname($this->heimmannschaft);
        $gast = $this->sanitizeTeamname($this->gastmannschaft);

        if( ($heim === $teamName) || 
        // In manchen Gruppen (z.B. Turnieren) wird die erste Mannschaft hinten mit "1" ergänzt, was hier abgefangen werden soll
        ($heim === $teamName." 1") ){
            $spiel->heimspiel = true;
            $spiel->gegnerName = $gast;
        } else {
            $spiel->heimspiel = false;
            $spiel->gegnerName = $heim;
        }
        
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