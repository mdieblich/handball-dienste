<?php

require_once __DIR__."/Meisterschaft.php";
require_once __DIR__."/Liga.php";
require_once __DIR__."/Mannschaft.php";

class MannschaftsMeldung{
    
    public int $id;
    public Mannschaft $mannschaft;
    public Meisterschaft $meisterschaft;
    public bool $aktiv = true;
    public Liga $liga;
    // TODO entfernen
    public int $nuligaLigaID;
    public int $nuligaTeamID;
}
?>