<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/gegner.php";

class GegnerDAO{
    private $alleGegner = array();

    public function loadGegner($where = "1=1", $orderBy = "verein ASC, nummer ASC"){
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gegner';
        $sql = "SELECT * FROM $table_name WHERE $where ORDER BY $orderBy";
        $result = $wpdb->get_results($sql);

        if (count($result) > 0) {
            foreach($result as $gegner) {
                $gegnerObj = new Gegner((array)$gegner);
                $this->alleGegner[$gegnerObj->getID()] = $gegnerObj;
            }
        }
    }

    public function getAlleGegner(): array{
        return $this->alleGegner;
    }

    public function insertGegner(string $name, string $geschlecht, string $liga): Gegner{
        global $wpdb;
            
        $table_name = $wpdb->prefix . 'gegner';

        $verein = $name;
        $nummer = 1;

        if(str_ends_with($name, " V")){
            $nummer = 5;
            $verein = substr($name, 0, strlen($name)-2);
        } else if(str_ends_with($name, " IV")){
            $nummer = 4;
            $verein = substr($name, 0, strlen($name)-3);
        } else if(str_ends_with($name, " III")){
            $nummer = 3;
            $verein = substr($name, 0, strlen($name)-4);
        } else if(str_ends_with($name, " II")){
            $nummer = 2;
            $verein = substr($name, 0, strlen($name)-3);
        } else if(str_ends_with($name, " I")){
            $nummer = 1;
            $verein = substr($name, 0, strlen($name)-2);
        }
        $verein = trim($verein);

        $params = array(
            'verein' => $verein,
            'nummer' => $nummer,
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