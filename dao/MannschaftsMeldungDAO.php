<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../handball/MannschaftsMeldung.php";

class MannschaftsMeldungDAO extends DAO{

    public function loadMannschaftsMeldungen(string $where = null, string $orderBy = null): array{
        return $this->fetchAll2($where, $orderBy);
    }
    
    public function findMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga): ?MannschaftsMeldung {
        return $this->fetch2("meisterschaft_id=$meisterschaft AND mannschaft_id=$mannschaft AND liga=\"$liga\"");
    }

    public function meldungAktivieren(int $id, bool $aktiv){
        $this->update($id, array('aktiv' => $aktiv ? 1 : 0));
    }

    public function updateMannschaftsMeldung(int $id, int $nuligaLigaID, int $nuligaTeamID){
        $this->update($id, array(
            'nuligaLigaID' => $nuligaLigaID, 
            'nuligaTeamID' => $nuligaTeamID
        ));
    }

    public function insertMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga, int $nuligaLigaID, int $nuligaTeamID){
        $values = array(
            'meisterschaft_id' => $meisterschaft, 
            'mannschaft_id' => $mannschaft, 
            'liga' => $liga, 
            'nuligaLigaID' => $nuligaLigaID, 
            'nuligaTeamID' => $nuligaTeamID
        );
        $this->insert($values);
    }
}
?>