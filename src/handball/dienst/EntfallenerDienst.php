<?php

require_once __DIR__."/../Spiel.php";
require_once __DIR__."/../Mannschaft.php";
require_once __DIR__."/../Dienst.php";

class EntfallenerDienst {
    public int $id;
    public Dienst $dienst;          public int $dienst_id;
    public Spiel $spiel;            public int $spiel_id;
    public string $dienstart;
    public ?Mannschaft $mannschaft; public ?int $mannschaft_id;
    public string $grund;

    public function __construct(Dienst $dienst, string $grund){
        $this->dienst = $dienst;
        $this->dienst_id = $dienst->id;

        $this->spiel = $dienst->spiel;
        $this->spiel_id = $dienst->spiel_id;
        
        $this->mannschaft = $dienst->mannschaft;
        $this->mannschaft_id = $dienst->mannschaft_id;

        $this->grund = $grund;
    }
}