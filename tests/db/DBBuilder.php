<?php

require_once __DIR__."/../../src/handball/Meisterschaft.php";
require_once __DIR__."/../../src/handball/Mannschaft.php";
require_once __DIR__."/../../src/handball/MannschaftsMeldung.php";
require_once __DIR__."/../../src/handball/Gegner.php";
require_once __DIR__."/../../src/handball/Spiel.php";
require_once __DIR__."/../../src/handball/Dienst.php";

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
    public function createGegner(string $verein, int $nummer, MannschaftsMeldung $meldung, bool $stelltSekretaer = false): Gegner {
        $this->db->insert("wp_gegner", [
            "verein" => $verein,
            "nummer" => $nummer,
            "zugehoerigeMeldung_id" => $meldung->id,
            "stelltSekretaerBeiHeimspiel" => $stelltSekretaer?1:0 // Defaultwert, kann später angepasst werden
        ]);
        $gegner = new Gegner();
        $gegner->id = $this->db->insert_id;
        $gegner->verein = $verein;
        $gegner->nummer = $nummer;
        $gegner->zugehoerigeMeldung = $meldung;
        $gegner->stelltSekretaerBeiHeimspiel = $stelltSekretaer;
        return $gegner;
    }

    public function createSpiel(int $spielNr, MannschaftsMeldung $meldung, Gegner $gegner, ?DateTime $anwurf, string $halle, bool $heimspiel, ?Mannschaft $mannschaft = null): Spiel {
        $this->db->insert("wp_spiel", [
            "spielNr" => $spielNr,
            "mannschaftsMeldung_id" => $meldung->id,
            "mannschaft_id" => $mannschaft?->id,
            "gegner_id" => $gegner->id,
            "anwurf" => $anwurf?->format('Y-m-d H:i:s'),
            "halle" => $halle,
            "heimspiel" => $heimspiel?1:0
        ]);
        $spiel = new Spiel();
        $spiel->id = $this->db->insert_id;
        $spiel->spielNr = $spielNr;
        $spiel->mannschaftsMeldung = $meldung;
        if(isset($mannschaft)){
            $spiel->mannschaft = $mannschaft;
        }
        $spiel->gegner = $gegner;
        $spiel->anwurf = $anwurf;
        $spiel->halle = $halle;
        $spiel->heimspiel = $heimspiel;
        return $spiel;
    }

    public function createDienst(Spiel $spiel, string $dienstart, ?Mannschaft $mannschaft = null): Dienst {
        $this->db->insert("wp_dienst", [
            "spiel_id" => $spiel->id,
            "dienstart" => $dienstart,
            "mannschaft_id" => $mannschaft?->id
        ]);
        $dienst = new Dienst();
        $dienst->id = $this->db->insert_id;
        $dienst->spiel = $spiel;
        $dienst->dienstart = $dienstart;
        if(isset($mannschaft)){
            $dienst->mannschaft = $mannschaft;
        }
        return $dienst;
    }
}