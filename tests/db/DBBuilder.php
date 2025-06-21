<?php

class DBBuilder{    
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createMeisterschaft(string $kuerzel): int {
        $this->db->insert("wp_meisterschaft", [
            "kuerzel" => $kuerzel
        ]);
        return $this->db->insert_id;
    }
    public function createMannschaft(int $nummer, string $geschlecht = "m"): int {
        $this->db->insert("wp_mannschaft", [
            "nummer" => $nummer,
            "geschlecht" => $geschlecht
        ]);
        return $this->db->insert_id;
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
}