<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../handball/Dienst.php";

class DienstDAO extends DAO{

    public function loadAllDienste(string $where=null, string $orderBy=null): array{
        return $this->fetchAll($where, $orderBy);
    }

    public function insertDienst(int $spiel, string $dienstart, int $mannschaft){
        $params = array(
            'spiel_id' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft_id' => $mannschaft
        );
        $this->insert($params);
    }
    public function deleteDienst(int $spiel, string $dienstart, int $mannschaft){
        $params = array(
            'spiel_id' => $spiel,
            'dienstart' => $dienstart,
            'mannschaft_id' => $mannschaft
        );
        $this->delete($params);
    }
}
?>