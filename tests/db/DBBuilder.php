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
}