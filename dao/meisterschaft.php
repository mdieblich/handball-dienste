<?php
require_once __DIR__."/../entity/meisterschaft.php";

function loadMeisterschaften(string $where = "1=1", string $orderby = "id"): array{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $sql = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby";
    $result = $wpdb->get_results($sql, ARRAY_A);

    $meisterschaften = array();
    if (count($result) > 0) {
        foreach($result as $meisterschaft) {
            $meisterschaftObj = new Meisterschaft($meisterschaft);
            $meisterschaften[$meisterschaftObj->getID()] = $meisterschaftObj;
        }
    }
    return $meisterschaften;
}

function findMeisterschaft(int $mannschaft, string $kuerzel, string $liga): ?Meisterschaft {
  global $wpdb;

  $table_name = $wpdb->prefix . 'meisterschaft';
  $sql = "SELECT * FROM $table_name WHERE mannschaft=$mannschaft AND kuerzel=\"$kuerzel\" AND liga=\"$liga\"";
  $result = $wpdb->get_row($sql, ARRAY_A);
  if(empty($result)){
    return null;
  }

  return new Meisterschaft($result);
}
function updateMeisterschaft(int $id, string $name, int $nuliga_liga_id, int $nuliga_team_id){
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'meisterschaft';
  $wpdb->update($table_name, 
    array(
      'name' => $name, 
      'nuliga_liga_id' => $nuliga_liga_id, 
      'nuliga_team_id' => $nuliga_team_id
    ), array(
      'id' => $id
    ));
}

// function insertMeisterschaft(int $mannschaft, string $name, string $kuerzel, string $liga, int $nuliga_liga_id, int $nuliga_team_id){
function insertMeisterschaft(int $mannschaft, string $name, string $kuerzel, string $liga, int $nuliga_liga_id, int $nuliga_team_id){
    global $wpdb;
    
    $values = array(
        'mannschaft' => $mannschaft, 
        'name' => $name, 
        'kuerzel' => $kuerzel, 
        'liga' => $liga, 
        'nuliga_liga_id' => $nuliga_liga_id, 
        'nuliga_team_id' => $nuliga_team_id
    );
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $wpdb->insert($table_name, $values);
}
?>