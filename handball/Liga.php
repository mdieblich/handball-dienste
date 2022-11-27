<?php

require_once __DIR__."/Meisterschaft.php";

class Liga{
    
    public int $id;
    public Meisterschaft $meisterschaft;
    public string $name;
    public int $nuligaLigaID;
    
    // TODO prüfen: obsolet?
    public function getNameKurz(): string {
        $name = $this->name;
        $name_ohne_geschlecht = str_replace(array(" Männer", " Frauen", "Mittelrhein ", "männliche ", "weibliche "), "", $name);
        return trim($name_ohne_geschlecht);
    }
}
?>