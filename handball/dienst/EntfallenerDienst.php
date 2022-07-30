<?php

require_once __DIR__."/../Spiel.php";
require_once __DIR__."/../Dienst.php";

class EntfallenerDienst {
    public Spiel $spiel;
    public string $dienstart;

    public function __construct(Spiel $spiel, Dienst $dienst){
        $this->spiel = $spiel;
        $this->dienstart = $dienst->dienstart;
    }
}

?>