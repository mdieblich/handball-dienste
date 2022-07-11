<?php

require_once __DIR__."/Dienstart.php";
require_once __DIR__."/Spiel.php";
require_once __DIR__."/Mannschaft.php";

class Dienst {
    public int $id;
    public Spiel $spiel;
    public string $dienstart;
    public Mannschaft $mannschaft;
}
?>