<?php

require_once __DIR__."/Meisterschaft.php";
require_once __DIR__."/Mannschaft.php";

class MannschaftsMeldung{
    
    public int $id;
    public Mannschaft $mannschaft;
    public Meisterschaft $meisterschaft;
    public bool $aktiv = true;
    public string $liga;
    public int $nuligaLigaID;
    public int $nuligaTeamID;
    
    public function getLigaKurz(): string {
        $liga = $this->liga;
        $liga = str_replace(array(" Männer", " Frauen", "Mittelrhein ", "männliche ", "weibliche "), "", $liga);
        return trim($liga);
    }
}
?>