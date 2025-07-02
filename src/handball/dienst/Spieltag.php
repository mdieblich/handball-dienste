<?php
require_once __DIR__."/../Spiel.php";

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
            $this->loescheAufUndAbbau();
            return;
        }
        $this->organisiereAufbau();
        $this->organisiereAbbau();
    }
    private function loescheAufUndAbbau(): void {
        foreach($this->spiele as $spiel){
            $this->loesche($spiel, Dienstart::AUFBAU);
            $this->loesche($spiel, Dienstart::ABBAU);
        }
    }
    
    hier weiter
}