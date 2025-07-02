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
}