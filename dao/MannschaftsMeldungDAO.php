<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../entity/MannschaftsMeldung.php";

class MannschaftsMeldungDAO extends DAO{
    public function loadMannschaftsMeldungen(string $where = "1=1", string $orderby = "id"): array{
        $result = $this->fetchAll($where, $orderBy);
    
        $meldungen = array();
        foreach($result as $meldung) {
            $meldungObj = new MannschaftsMeldung($meldung);
            $meldungen[$meldungObj->getID()] = $meldungObj;
        }
        return $meldungen;
    }
    
    public function findMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga): ?MannschaftsMeldung {
        $result = $this->fetch("meisterschaft=$meisterschaft AND mannschaft=$mannschaft AND liga=\"$liga\"");
        if(empty($result)){
            return null;
        }

        return new MannschaftsMeldung($result);
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