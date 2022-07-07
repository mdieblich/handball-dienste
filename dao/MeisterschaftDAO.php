<?php
require_once __DIR__."/../handball/Meisterschaft.php";
require_once __DIR__."/DAO.php";

class MeisterschaftDAO extends DAO {

    public function loadMeisterschaften(string $where = null, string $orderBy = null): array{
        return $this->fetchAll2($where, $orderBy);
    }

    // TODO insertMeisterschaft muss ein Meisterschaft-Objekt erhalten
    public function insertMeisterschaft(string $kuerzel, string $name): Meisterschaft{
        $meisterschaft = new Meisterschaft();
        $meisterschaft->kuerzel = $kuerzel;
        $meisterschaft->name = $name;
        
        $this->insert2($meisterschaft);
        return new Meisterschaft($values);
    }
}

?>