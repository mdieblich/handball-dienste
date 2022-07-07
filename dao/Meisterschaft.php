<?php
require_once __DIR__."/../entity/Meisterschaft.php";
require_once __DIR__."/DAO.php";

class MeisterschaftDAO extends DAO {

    public function loadMeisterschaften(string $where = "1=1", string $orderby = "id"): array{
        $result = $this->fetchAll($where, $orderBy);
        
        $meisterschaften = array();
        foreach($result as $meisterschaft) {
            $meisterschaftObj = new Meisterschaft($meisterschaft);
            $meisterschaften[$meisterschaftObj->getID()] = $meisterschaftObj;
        }
        return $meisterschaften;
    }
}


function upsertMeisterschaft(string $kuerzel, string $name): int{
    $meisterschaft = findMeisterschaft($kuerzel, $name);
    if(isset($meisterschaft)){
        updateMeisterschaft($meisterschaft->getID(), $kuerzel, $name);
    } else {
        $meisterschaft = insertMeisterschaft($kuerzel, $name);
    }

    return $meisterschaft->getID();
}


function findMeisterschaft(string $kuerzel, string $name): ?Meisterschaft{
    global $wpdb;
    $table_name = $wpdb->prefix . 'meisterschaft';
    $result = $wpdb->get_row("SELECT * FROM $table_name WHERE kuerzel=\"$kuerzel\" AND name=\"$name\"", ARRAY_A);
    if(empty($result)){
      return null;
    }
    return new Meisterschaft($result);
}

function insertMeisterschaft(string $kuerzel, string $name): Meisterschaft{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $values = array(
        'kuerzel' => $kuerzel, 
        'name' => $name
    );
    $wpdb->insert($table_name, $values);
    $values['id'] = $wpdb->insert_id;
    return new Meisterschaft($values);
}
function updateMeisterschaft(int $id, string $kuerzel, string $name){
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $wpdb->update($table_name, 
        array(
          'kuerzel' => $kuerzel,
          'name' => $name
        ), array(
          'id' => $id
        )
    );
}
?>