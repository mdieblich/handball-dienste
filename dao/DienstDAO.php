<?php
require_once __DIR__."/DAO.php";
require_once __DIR__."/../entity/Dienst.php";

class DienstDAO extends DAO{

    public function loadAllDienste(string $where=null, string $orderBy=null): array{
        return $this->fetchAll($where, $orderBy);
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