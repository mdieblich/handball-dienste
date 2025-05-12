<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../../handball/MannschaftsMeldung.php";

class MannschaftsMeldungDAO extends DAO{
    
    public function findMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga): ?MannschaftsMeldung {
        return $this->fetch("meisterschaft_id=$meisterschaft AND mannschaft_id=$mannschaft AND liga=\"$liga\"");
    }

    public function meldungAktivieren(int $id, bool $aktiv){
        $this->updateFields($id, array('aktiv' => $aktiv ? 1 : 0));
    }

    public function updateMannschaftsMeldung(int $id, int $nuligaLigaID, int $nuligaTeamID){
        $this->updateFields($id, array(
            'nuligaLigaID' => $nuligaLigaID, 
            'nuligaTeamID' => $nuligaTeamID
        ));
    }
}
?>