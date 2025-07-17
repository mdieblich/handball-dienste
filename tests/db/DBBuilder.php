<?php

require_once __DIR__."/../../src/handball/Meisterschaft.php";
require_once __DIR__."/../../src/handball/Mannschaft.php";

class DBBuilder{    
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createMeisterschaft(string $kuerzel): Meisterschaft {
        $this->db->insert("wp_meisterschaft", [
            "kuerzel" => $kuerzel
        ]);
        $meisterschaft = new Meisterschaft();
        $meisterschaft->id = $this->db->insert_id;
        $meisterschaft->kuerzel = $kuerzel;
        return $meisterschaft;
    }
    public function createMannschaft(int $nummer, string $geschlecht = "m"): Mannschaft {
        $this->db->insert("wp_mannschaft", [
            "nummer" => $nummer,
            "geschlecht" => $geschlecht
        ]);
        $mannschaft = new Mannschaft();
        $mannschaft->nummer = $nummer;
        $mannschaft->geschlecht = $geschlecht;
        $mannschaft->id = $this->db->insert_id;
        return $mannschaft;
    }
    public function createMannschaftsMeldung(int $mannschaft_id, int $meisterschaft_id, int $nuligaLigaID, int $nuligaTeamID): int {
        $this->db->insert("wp_mannschaftsmeldung", [
            "aktiv" => 1,
            "mannschaft_id" => $mannschaft_id,
            "meisterschaft_id" => $meisterschaft_id,
            "nuligaLigaID" => $nuligaLigaID,
            "nuligaTeamID" => $nuligaTeamID
        ]);
        return $this->db->insert_id;
    }
    public function createGegner(string $verein, int $nummer, int $meldung_id, bool $stelltSekretaer = false): int {
        $this->db->insert("wp_gegner", [
            "verein" => $verein,
            "nummer" => $nummer,
            "zugehoerigeMeldung_id" => $meldung_id,
            "stelltSekretaerBeiHeimspiel" => $stelltSekretaer?1:0 // Defaultwert, kann später angepasst werden
        ]);
        return $this->db->insert_id;
    }

    public function createSpiel(int $spielNr, int $meldung_id, int $gegner_id, ?DateTime $anwurf, string $halle, bool $heimspiel, ?int $mannschaft_id = null): int {
        $this->db->insert("wp_spiel", [
            "spielNr" => $spielNr,
            "mannschaftsMeldung_id" => $meldung_id,
            "mannschaft_id" => $mannschaft_id,
            "gegner_id" => $gegner_id,
            "anwurf" => $anwurf?->format('Y-m-d H:i:s'),
            "halle" => $halle,
            "heimspiel" => $heimspiel
        ]);
        return $this->db->insert_id;
    }

    public function createDienst(int $spiel_id, string $dienstart, ?int $mannschaft_id = null): int {
        $this->db->insert("wp_dienst", [
            "spiel_id" => $spiel_id,
            "dienstart" => $dienstart,
            "mannschaft_id" => $mannschaft_id
        ]);
        return $this->db->insert_id;
    }
}