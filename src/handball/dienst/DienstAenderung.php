<?php

require_once __DIR__."/../Dienst.php";
require_once __DIR__."/../Spiel.php";
require_once __DIR__."/../Mannschaft.php";

class DienstAenderung {
    public int $id;
    public Dienst $dienst; public int $dienst_id;
    public Mannschaft $mannschaft; public int $mannschaft_id; 

    public ?DateTime $anwurfVorher = null;
    public ?string $halleVorher = null;

    public function __construct(Dienst $dienst, Spiel $spiel_vorher){
        $this->dienst = $dienst;
        $this->mannschaft = $dienst->mannschaft;
        $this->anwurfVorher = $spiel_vorher->anwurf;
        $this->halleVorher = $spiel_vorher->halle;
    }
}