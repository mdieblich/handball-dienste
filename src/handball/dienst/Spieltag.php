<?php
require_once __DIR__."/../Spiel.php";
require_once __DIR__."/../../log/Log.php";
require_once __DIR__."/EntfallenerDienst.php";

class Spieltag {
    private string $tag;
    private array $spiele = [];

    private ?array $neueDienste = null;
    private ?array $entfalleneDienste = null;
    private Log $logfile;
    public function __construct(string $tag, Log $logfile = null) {
        $this->tag = $tag;
        $this->logfile = $logfile ?? new NoLog();
    }

    public function addSpiel(Spiel $spiel): void {
        $this->spiele[] = $spiel;
    }

    public function getNeueDienste(): array {
        if(empty($this->neueDienste)) {
            $this->organisiereDienste();
        }
        return $this->neueDienste;
    }
    public function getEntfalleneDienste(): array {
        if(empty($this->neueDienste)) {
            $this->organisiereDienste();
        }
        return $this->entfalleneDienste;
    }
    private function organisiereDienste(): void {
        $this->neueDienste = [];
        $this->entfalleneDienste = [];
        if($this->tag === ""){
            $this->logfile->log("Lösche Auf-und Abbau für Spiele, die kein festes Datum haben.");
            $this->loescheAufUndAbbau("Das Spiel ist keinem Tag zugeordnet.");
            return;
        }
        $this->logfile->log("Organisiere Auf-und Abbau für $this->tag");
        $this->organisiereAufbau();
        $this->organisiereAbbau();
    }
    private function loescheAufUndAbbau(string $grund): void {
        foreach($this->spiele as $spiel){
            $this->loescheDienst($spiel, Dienstart::AUFBAU, $grund);
            $this->loescheDienst($spiel, Dienstart::ABBAU, $grund);
        }
    }
    private function loescheDienst(Spiel $spiel, string $dienstart, string $grund): void {
        $dienst = $spiel->getDienst($dienstart);
        if(!isset($dienst)) return;
        $this->logfile->log("Lösche $dienstart von Spiel {$spiel->getBegegnungsbezeichnung()}, Grund: $grund");
        $spiel->deleteDienst($dienstart);
        $this->entfalleneDienste[] = new EntfallenerDienst($dienst, $grund);
    }
    private function organisiereAufbau(): void {
        $erstesSpiel = $this->spiele[0];
        $this->erstelleDienst($erstesSpiel, Dienstart::AUFBAU, "Erstes Spiel des Tages");
        for($i = 1; $i < count($this->spiele); $i++){
            $this->loescheDienst($this->spiele[$i], Dienstart::AUFBAU, "Nicht mehr erstes Spiel des Tages");
        }
    }
    private function erstelleDienst(Spiel $spiel, string $dienstart, string $grund): void {
        $dienst = $spiel->getDienst($dienstart);
        if(isset($dienst)) return;

        $this->logfile->log("Erstelle $dienstart von Spiel {$spiel->getBegegnungsbezeichnung()}, Grund: $grund");
        $dienst = $spiel->createDienst($dienstart);
        $this->neueDienste[] = new NeuerDienst($dienst, $grund);
    }
    
    private function organisiereAbbau(): void {
        $letztesSpiel = $this->spiele[count($this->spiele) -1];
        $this->erstelleDienst($letztesSpiel, Dienstart::ABBAU, "Letztes Spiel des Tages");
        for($i = 0; $i < count($this->spiele)-1; $i++){
            $this->loescheDienst($this->spiele[$i], Dienstart::ABBAU, "Nicht mehr letztes Spiel des Tages");
        }
    }
}