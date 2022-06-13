<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/dienst.php";

class DienstDAO{

    public function loadAllDienste(): array{
        global $wpdb;
  
        $table_name = $wpdb->prefix . 'dienst';
        $sql = "SELECT * FROM $table_name WHERE $whereClause ORDER BY $orderBy";
        $result = $wpdb->get_results($sql);
      
        $dienste = array();
        if (count($result) > 0) {
            foreach($result as $dienst) {
                $dienstObj = new Dienst((array)$dienst);
                $dienste[$dienstObj->getID()] = $dienstObj;
            }
        }
        return $dienste;
    }
}

?>