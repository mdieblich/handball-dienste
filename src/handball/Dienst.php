<?php

require_once __DIR__."/Dienstart.php";
require_once __DIR__."/Spiel.php";
require_once __DIR__."/Mannschaft.php";

class Dienst {
    public int $id;
    public Spiel $spiel;            public int $spiel_id;
    public string $dienstart;
    public ?Mannschaft $mannschaft; public ?int $mannschaft_id;

    public function getSpielID(): int {
        if(isset($this->spiel)){
            return $this->spiel->id;
        }
        return $this->spiel_id;
    }
    public function getMannschaftID(): int {
        if(isset($this->mannschaft)){
            return $this->mannschaft->id;
        }
        return $this->mannschaft_id;
    }
}