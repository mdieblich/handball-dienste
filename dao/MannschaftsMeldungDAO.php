<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../entity/MannschaftsMeldung.php";

class MannschaftsMeldungDAO extends DAO{

    public function loadMannschaftsMeldungen(string $where = null, string $orderBy = null): array{
        return $this->fetchAll($where, $orderBy);
    }
    
    public function findMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga): ?MannschaftsMeldung {
        return $this->fetch("meisterschaft=$meisterschaft AND mannschaft=$mannschaft AND liga=\"$liga\"");
    }

    public function meldungAktivieren(int $id, bool $aktiv){
        $this->update($id, array('aktiv' => $aktiv ? 1 : 0));
    }

    public function updateMannschaftsMeldung(int $id, int $nuliga_liga_id, int $nuliga_team_id){
        $this->update($id, array(
            'nuliga_liga_id' => $nuliga_liga_id, 
            'nuliga_team_id' => $nuliga_team_id
        ));
    }

    public function insertMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga, int $nuliga_liga_id, int $nuliga_team_id){
        $values = array(
            'meisterschaft' => $meisterschaft, 
            'mannschaft' => $mannschaft, 
            'liga' => $liga, 
            'nuliga_liga_id' => $nuliga_liga_id, 
            'nuliga_team_id' => $nuliga_team_id
        );
        $this->insert($values);
    }
}
?>