<?php

require_once __DIR__."/../../src/handball/Meisterschaft.php";
require_once __DIR__."/../../src/handball/Mannschaft.php";
require_once __DIR__."/../../src/handball/MannschaftsMeldung.php";

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
    public function createMannschaftsMeldung(Mannschaft $mannschaft, Meisterschaft $meisterschaft, int $nuligaLigaID, int $nuligaTeamID): MannschaftsMeldung{
        $this->db->insert("wp_mannschaftsmeldung", [
            "aktiv" => 1,
            "mannschaft_id" => $mannschaft->id,
            "meisterschaft_id" => $meisterschaft->id,
            "nuligaLigaID" => $nuligaLigaID,
            "nuligaTeamID" => $nuligaTeamID
        ]);
        $meldung = new MannschaftsMeldung();
        $meldung->id = $this->db->insert_id;
        $meldung->aktiv = true;
        $meldung->mannschaft = $mannschaft;
        $meldung->meisterschaft = $meisterschaft;
        $meldung->nuligaLigaID = $nuligaLigaID;
        $meldung->nuligaTeamID = $nuligaTeamID;
        return $meldung;
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