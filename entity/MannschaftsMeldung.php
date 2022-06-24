<?php

class MannschaftsMeldung {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }
    
    public function getKuerzel(): string {
        return $this->assoc_array["kuerzel"];
    }

    public function getMannschaft(): int {
        return $this->assoc_array["mannschaft"];
    }

    public function isAktiv(): bool {
        return $this->assoc_array["aktiv"] != "0";
    }
    
    public function getLiga(): string {
        return $this->assoc_array["liga"];
    }
    
    public function getNuligaLigaID(): int {
        return $this->assoc_array["nuliga_liga_id"];
    }
    
    public function getNuligaTeamID(): int {
        return $this->assoc_array["nuliga_team_id"];
    }
}
?>