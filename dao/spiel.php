<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/spiel.php";

function loadSpiele(): array{
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'spiel';
  $sql = "SELECT * FROM $table_name ORDER BY anwurf, halle";
  $result = $wpdb->get_results($sql);
  
  $spiele = array();
  if (count($result) > 0) {
    foreach($result as $spiel) {
      $spielObj = new Spiel((array)$spiel);
      $spiele[$spielObj->getID()] = $spielObj;
    }
  }
  return $spiele;
}

function countSpiele(int $mannschaftsID): int {
  global $wpdb;
  $table_name = $wpdb->prefix . 'spiel';
  return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE mannschaft=$mannschaftsID");
}

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
?>