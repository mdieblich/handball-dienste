<?php

require_once __DIR__."/Meisterschaft.php";

class Liga{
    
    public int $id;
    public Meisterschaft $meisterschaft;
    public string $name;
    
    public function getLigaKurz(): string {
        $liga = $this->liga;
        $liga = str_replace(array(" Männer", " Frauen", "Mittelrhein ", "männliche ", "weibliche "), "", $liga);
        return trim($liga);
    }
}
?>