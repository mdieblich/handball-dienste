<?php
require_once __DIR__."/../entity/MannschaftsMeldung.php";

function loadMannschaftsMeldungen(string $where = "1=1", string $orderby = "id"): array{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mannschaftsMeldung';
    $sql = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby";
    $result = $wpdb->get_results($sql, ARRAY_A);

    $meldungen = array();
    if (count($result) > 0) {
        foreach($result as $meldung) {
            $meldungObj = new MannschaftsMeldung($meldung);
            $meldungen[$meldungObj->getID()] = $meldungObj;
        }
    }
    return $meldungen;
}

function findMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga): ?MannschaftsMeldung {
  global $wpdb;

  $table_name = $wpdb->prefix . 'mannschaftsMeldung';
  $sql = "SELECT * FROM $table_name WHERE meisterschaft=$meisterschaft AND mannschaft=$mannschaft AND liga=\"$liga\"";
  $result = $wpdb->get_row($sql, ARRAY_A);
  if(empty($result)){
    return null;
  }

  return new MannschaftsMeldung($result);
}
function updateMannschaftsMeldung(int $id, int $nuliga_liga_id, int $nuliga_team_id){
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'mannschaftsMeldung';
  $wpdb->update($table_name, 
    array(
      'nuliga_liga_id' => $nuliga_liga_id, 
      'nuliga_team_id' => $nuliga_team_id
    ), array(
      'id' => $id
    ));
}

function insertMannschaftsMeldung(int $meisterschaft, int $mannschaft, string $liga, int $nuliga_liga_id, int $nuliga_team_id){
    global $wpdb;
    
    $values = array(
        'meisterschaft' => $meisterschaft, 
        'mannschaft' => $mannschaft, 
        'liga' => $liga, 
        'nuliga_liga_id' => $nuliga_liga_id, 
        'nuliga_team_id' => $nuliga_team_id
    );
    
    $table_name = $wpdb->prefix . 'mannschaftsMeldung';
    $wpdb->insert($table_name, $values);
}
?>