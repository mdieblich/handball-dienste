<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/dienst.php";

class DienstDAO{

    public function loadAllDienste(string $whereClause="1=1", string $orderBy="id ASC"): array{
        global $wpdb;
  
        $table_name = $wpdb->prefix . 'dienst';
        $sql = "SELECT * FROM $table_name WHERE $whereClause ORDER BY $orderBy";
        $result = $wpdb->get_results($sql, ARRAY_A);
      
        $dienste = array();
        if (count($result) > 0) {
            foreach($result as $dienst) {
                $dienstObj = new Dienst($dienst);
                $dienste[$dienstObj->getID()] = $dienstObj;
            }
        }
        return $dienste;
    }

    public function insert(int $spiel, string $dienstart, int $mannschaft){
        
        global $wpdb;
            
        $table_name = $wpdb->prefix . 'dienst';

        $params = array(
            'spiel' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft' => $mannschaft
        );

        $wpdb->insert($table_name, $params);
  
    }
    public function delete(int $spiel, string $dienstart, int $mannschaft){
        
        global $wpdb;
            
        $table_name = $wpdb->prefix . 'dienst';

        $params = array(
            'spiel' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft' => $mannschaft
        );

        $wpdb->delete($table_name, $params);
  
    }
}

?>