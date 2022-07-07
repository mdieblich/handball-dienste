<?php
require_once __dir__."/../entity/Mannschaft.php";
require_once __DIR__."/DAO.php";
require_once __dir__."/MannschaftsMeldungDAO.php";

class MannschaftDAO extends DAO{
    public function loadMannschaften(): array{
        $result = $this->fetchAll("1=1", "jugendklasse, nummer, geschlecht");
    
        $mannschaften = array();
        foreach($result as $mannschaft) {
            $mannschaftObj = new Mannschaft($mannschaft);
            $mannschaften[$mannschaftObj->getID()] = $mannschaftObj;
        }
        return $mannschaften;
    }
}

?>