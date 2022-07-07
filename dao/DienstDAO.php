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
    public function deleteDienst(int $spiel, string $dienstart, int $mannschaft){
        $params = array(
            'spiel' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft' => $mannschaft
        );
        $this->delete($params);
    }
}
?>