<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/gegner.php";

class GegnerDAO{
  private $alleGegner = array();

  public function loadGegner(){
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'gegner';
    $sql = "SELECT * FROM $table_name";
    $result = $wpdb->get_results($sql);
  
    if (count($result) > 0) {
      foreach($result as $gegner) {
        $gegnerObj = new Gegner((array)$gegner);
        $this->alleGegner[$gegnerObj->getID()] = $gegnerObj;
      }
    }
  }
  
  public function insertGegner(string $name, string $geschlecht, string $liga): Gegner{
    global $wpdb;
        
    $table_name = $wpdb->prefix . 'gegner';

    $params = array(
      'name' => $name,
      'geschlecht' => $geschlecht,
      'liga' => $liga
    );

    $wpdb->insert($table_name, $params);
    $params["id"] = $wpdb->insert_id;

    $newGegner = new Gegner($params);
    $this->alleGegner[$newGegner->getID()] = $newGegner;
    return $newGegner;

  }
  
  function findOrInsertGegner(string $name, string $geschlecht, string $liga): Gegner{
    foreach($this->alleGegner as $gegner){
        if($gegner->getName() === $name && $gegner->getGeschlecht() === $geschlecht){
            return $gegner;
        }
    }
    // Nix gefunden - einfügen!
    return $this->insertGegner($name, $geschlecht, $liga);
  }
}
?>