<?php

require_once __DIR__."/DAO.php";
require_once __DIR__."/../../handball/Dienst.php";
require_once __DIR__."/../../handball/Spiel.php";

class DienstDAO extends DAO{

    public function loadAllDienste(string $where=null, string $orderBy=null): array{
        return $this->fetchAll($where, $orderBy);
    }

    public function deleteDienst(int $spiel, string $dienstart, int $mannschaft){
        $params = array(
            'spiel_id' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft_id' => $mannschaft
        );
        $this->delete($params);
    }

    public function assign($id, $mannschaft_id){
        $this->updateFields( $id, array('mannschaft_id' => $mannschaft_id));
    }

    public function unassign($id){
        $this->updateFields( $id, array('mannschaft_id' => null));
    }
}
?>