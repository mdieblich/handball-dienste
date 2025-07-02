<?php
require_once __DIR__."/../Spiel.php";

class Spieltag {
    private string $tag;
    private array $spiele = [];

    public function __construct(string $tag) {
        $this->tag = $tag;
    }

    public function addSpiel(Spiel $spiel): void {
        $this->spiele[] = $spiel;
    }
}