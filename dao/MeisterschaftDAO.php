<?php
require_once __DIR__."/../entity/Meisterschaft.php";
require_once __DIR__."/DAO.php";

class MeisterschaftDAO extends DAO {

    public function loadMeisterschaften(string $where = null, string $orderBy = null): array{
        return $this->fetchAll($where, $orderBy);
    }

    public function insertMeisterschaft(string $kuerzel, string $name): Meisterschaft{
        $values = array(
            'kuerzel' => $kuerzel, 
            'name' => $name
        );
        $values['id'] = $this->insert($values);
        return new Meisterschaft($values);
    }

    public function updateMeisterschaft(int $id, string $kuerzel, string $name){
        $this->update($id, array(
            'kuerzel' => $kuerzel,
            'name' => $name
        ));
    }

    public function findMeisterschaft(string $kuerzel, string $name): ?Meisterschaft{
        return $this->fetch("kuerzel=\"$kuerzel\" AND name=\"$name\"");
    }

    public function upsertMeisterschaft(string $kuerzel, string $name): int{
        $meisterschaft = $this->findMeisterschaft($kuerzel, $name);
        if(isset($meisterschaft)){
            $this->updateMeisterschaft($meisterschaft->getID(), $kuerzel, $name);
        } else {
            $meisterschaft = $this->insertMeisterschaft($kuerzel, $name);
        }
    
        return $meisterschaft->getID();
    }
}


?>