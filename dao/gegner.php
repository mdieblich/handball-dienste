<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/gegner.php";

function loadGegner(): array{
  global $wpdb;
  
  $table_name = $wpdb->prefix . 'gegner';
  $sql = "SELECT * FROM $table_name";
  $result = $wpdb->get_results($sql);

  $alleGegner = array();
  if (count($result) > 0) {
    foreach($result as $gegner) {
      $gegnerObj = new Gegner((array)$gegner);
      $alleGegner[$gegnerObj->getID()] = $gegnerObj;
    }
  }
  return $alleGegner;
}

function insertGegner(string $name, string $liga): Gegner{
  global $wpdb;
      
	$table_name = $wpdb->prefix . 'gegner';

  $wpdb->insert($table_name, array(
      'name' => $name,
      'liga' => $liga
      ));

  return new Gegner(array(
    "id" => $wpdb->insert_id,
    "name" => $name,
    "liga" => $liga
    ));
}
?>