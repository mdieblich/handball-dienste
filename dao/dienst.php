<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../entity/Dienst.php";

class DienstDAO extends DAO{

    public function loadAllDienste(string $whereClause="1=1", string $orderBy="id ASC"): array{
        $result = $this->fetchAll($whereClause, $orderBy);
      
        $dienste = array();
        foreach($result as $dienst) {
            $dienstObj = new Dienst($dienst);
            $dienste[$dienstObj->getID()] = $dienstObj;
        }
        return $dienste;
    }

    public function insertDienst(int $spiel, string $dienstart, int $mannschaft){
        $params = array(
            'spiel' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft' => $mannschaft
        );
        $this->insert($params);
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