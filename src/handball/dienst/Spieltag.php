<?php
require_once __DIR__."/../Spiel.php";
require_once __DIR__."/EntfallenerDienst.php";

class Spieltag {
    private string $tag;
    private array $spiele = [];

    private ?array $neueDienste = null;
    private ?array $entfalleneDienste = null;

    public function __construct(string $tag) {
        $this->tag = $tag;
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
        return $this->neueDienste;
    }
    private function organisiereDienste(): void {
        if($this->tag === ""){
            $this->loescheAufUndAbbau("Das Spiel ist keinem Tag zugeordnet.");
            return;
        }
        $this->organisiereAufbau();
        $this->organisiereAbbau();
    }
    private function loescheAufUndAbbau(string $grund): void {
        foreach($this->spiele as $spiel){
            $this->loesche($spiel, Dienstart::AUFBAU, $grund);
            $this->loesche($spiel, Dienstart::ABBAU, $grund);
        }
    }
    private function loesche(Spiel $spiel, string $dienstart, string $grund): void {
        $dienst = $spiel->getDienst($dienstart);
        if(!isset($dienst)) return;
        $entfallenerDienst = new EntfallenerDienst($dienst, $grund);
    }
    // Hier weiter
}