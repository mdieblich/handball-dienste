<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/mannschaft.php";

function loadMannschaften(): array{
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'mannschaft';
  $sql = "SELECT * FROM $table_name ORDER BY nummer, geschlecht";
  $result = $wpdb->get_results($sql);

  $mannschaften = array();
  if (count($result) > 0) {
    foreach($result as $mannschaft) {
      $mannschaftObj = new Mannschaft((array)$mannschaft);
      $mannschaften[$mannschaftObj->getID()] = $mannschaftObj;
    }
  }
  return $mannschaften;
}
?>