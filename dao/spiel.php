<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/spiel.php";

function loadSpiele(string $whereClause="1=1", string $orderBy="anwurf, halle"): array{
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'spiel';
  $sql = "SELECT * FROM $table_name WHERE $whereClause ORDER BY $orderBy";
  $result = $wpdb->get_results($sql, ARRAY_A);
  
  $spiele = array();
  if (count($result) > 0) {
    foreach($result as $spiel) {
      $spielObj = new Spiel($spiel);
      $spiele[$spielObj->getID()] = $spielObj;
    }
  }
  return $spiele;
}

function loadSpieleDeep(string $whereClause="1=1", string $orderBy="anwurf, halle"){
    $spiele = loadSpiele($whereClause, $orderBy);
    require_once WP_PLUGIN_DIR."/dienstedienst/dao/dienst.php";
    $dienstDAO = new DienstDAO();
    foreach( $dienstDAO->loadAllDienste() as $dienst){
        $spiele[$dienst->getSpiel()]->addDienst($dienst);
    } 
    return $spiele;
}

function countSpiele(int $mannschaftsID): int {
  global $wpdb;
  $table_name = $wpdb->prefix . 'spiel';
  return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE mannschaft=$mannschaftsID");
}

function findSpielID(int $spielnr, int $mannschaft_id, int $gegner_id, int $isHeimspiel): int{
  global $wpdb;
  $table_name = $wpdb->prefix . 'spiel';
  return $wpdb->get_var("SELECT id FROM $table_name WHERE spielnr=$spielnr AND mannschaft=$mannschaft_id AND gegner=$gegner_id AND heimspiel=$isHeimspiel");
}

// function findSpiel(int $spielnr, int $mannschaft_id, int $gegner_id, int $isHeimspiel): ?Spiel{
//   global $wpdb;
//   $table_name = $wpdb->prefix . 'spiel';
//   $result = $wpdb->get_row("SELECT * FROM $table_name WHERE spielnr=$spielnr AND mannschaft=$mannschaft_id AND gegner=$gegner_id AND heimspiel=$isHeimspiel");
//   if()
// }

function insertSpiel(int $spielnr, int $mannschaft_id, int $gegner_id, bool $isHeimspiel, int $halle, ?DateTime $anwurf){
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'spiel';
  $wpdb->insert($table_name, array(
    'spielnr' => $spielnr, 
    'mannschaft' => $mannschaft_id, 
    'gegner' => $gegner_id, 
    'heimspiel' => $isHeimspiel, 
    'halle' => $halle, 
    'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
  ));
}
function updateSpiel(int $id, int $halle, ?DateTime $anwurf){
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'spiel';
  $wpdb->update($table_name, 
    array(
      'halle' => $halle, 
      'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
    ), array(
      'id' => $id
    ));
}
?>