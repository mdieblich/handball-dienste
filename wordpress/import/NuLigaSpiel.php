<?php
class NuLigaSpiel {
    
    private $wochentag;
    private $terminOffen;
    private $datum;
    private $uhrzeit;
    private $halle;
    private $spielNr;
    private $heimmannschaft;
    private $gastmannschaft;
    private $ergebnisOderSchiris;
    private $spielbericht;
    private $spielberichtsGenehmigung;

    // privat, damit man die Fabrik-Methode nutzt
    private function __construct(){}

    public function getSpielNr(): int{
        return $this->spielNr;
    }

    public function getHeimmannschaft(): string {
        return $this->heimmannschaft;
    }

    public function getGastmannschaft(): string {
        return $this->gastmannschaft;
    }

    public function getHalle(): int {
        return $this->halle;
    }

    public function isTerminOffen(): bool{
        return $this->terminOffen;
    }

    public function getAnwurf(): ?DateTime {
        if($this->terminOffen){
            return null;
        }
        $datum_und_zeit = $this->datum." ".$this->uhrzeit;
        return DateTime::createFromFormat('d.m.Y H:i', $datum_und_zeit);
    }
    public function getDebugOutput(): string {
        return "#".$this->spielNr.", ".$this->heimmannschaft." vs. ".$this->gastmannschaft.": Halle ".$this->halle;
    }

    public static function fromTabellenZellen(array $zellen, ?NuLigaSpiel $vorherigesSpiel): ?NuLigaSpiel {
        $spiel = new NuLigaSpiel();
        $spiel->wochentag = self::extractTrimmedContent($zellen[0]);
        $spiel->terminOffen = ($spiel->wochentag == "Termin offen");
        if($spiel->terminOffen){
            $spiel->halle = self::extractTrimmedContent($zellen[2]);
            $spiel->spielNr = self::extractTrimmedContent($zellen[3]);
            $spiel->heimmannschaft = self::extractTrimmedContent($zellen[4]);
            $spiel->gastmannschaft = self::extractTrimmedContent($zellen[5]);
            $spiel->ergebnisOderSchiris = self::extractTrimmedContent($zellen[6]);
            $spiel->spielbericht = self::extractTrimmedContent($zellen[7]);
            $spiel->spielberichtsGenehmigung = self::extractTrimmedContent($zellen[8]);
        }else {
            $datum = self::extractTrimmedContent($zellen[1]);
            if(empty($datum)){  // Manchmal sind mehrere Zeilen untereinander, dann wird das Datum weggelassen, siehe https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?teamtable=1797044&pageState=vorrunde&championship=KR+Q+22%2F23&group=297486
                $spiel->datum = $vorherigesSpiel->datum;
            }else{
                $spiel->datum = $datum;
            }
            $spiel->uhrzeit = substr(self::extractTrimmedContent($zellen[2]),0,5);   // format: 19:00, substring, damit etwaige "v" (für "Verlegt") abgeschnitten werden 
            $spiel->halle = self::extractTrimmedContent($zellen[3]);
            $spiel->spielNr = self::extractTrimmedContent($zellen[4]);
            $spiel->heimmannschaft = self::extractTrimmedContent($zellen[5]);
            $spiel->gastmannschaft = self::extractTrimmedContent($zellen[6]);
            $spiel->ergebnisOderSchiris = self::extractTrimmedContent($zellen[7]);
            $spiel->spielbericht = self::extractTrimmedContent($zellen[8]);
            $spiel->spielberichtsGenehmigung = self::extractTrimmedContent($zellen[9]);
        }
        if(empty($spiel->spielNr) || empty($spiel->halle)){
            // ungültiges Spiel, vermutlich spielfrei
            return null;
        }
        // leere Zelle: $zellen[10]
        return $spiel;
    }

    private static function extractTrimmedContent(DOMElement $zelle): string {
        return sanitizeContent($zelle->textContent);
    }

}
?>