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

    public static function fromTabellenZellen(array $zellen): NuLigaSpiel {
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
            $spiel->datum = self::extractTrimmedContent($zellen[1]); // format: 28.08.2021
            $spiel->uhrzeit = substr(self::extractTrimmedContent($zellen[2]),0,5);   // format: 19:00, substring, damit etwaige "v" (für "Verlegt") abgeschnitten werden 
            $spiel->halle = self::extractTrimmedContent($zellen[3]);
            $spiel->spielNr = self::extractTrimmedContent($zellen[4]);
            $spiel->heimmannschaft = self::extractTrimmedContent($zellen[5]);
            $spiel->gastmannschaft = self::extractTrimmedContent($zellen[6]);
            $spiel->ergebnisOderSchiris = self::extractTrimmedContent($zellen[7]);
            $spiel->spielbericht = self::extractTrimmedContent($zellen[8]);
            $spiel->spielberichtsGenehmigung = self::extractTrimmedContent($zellen[9]);
        }
        // leere Zelle: $zellen[10]
        return $spiel;
    }

    private static function extractTrimmedContent(DOMElement $zelle): string {
        $content = $zelle->textContent;
        $content = preg_replace('/\s+/', ' ',$content);
        $evilSpace = hex2bin("c2a0"); // das ist ein utf-16 Zeichen. Die Ottos von nuliga geben das falsche Encoding an!
        $content = str_replace($evilSpace, " ", $content);
        $content = trim($content);
        return $content;
    }
}
?>