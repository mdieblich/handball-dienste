<?php
require_once __dir__."/../entity/Mannschaft.php";
require_once __DIR__."/DAO.php";
require_once __dir__."/MannschaftsMeldungDAO.php";

class MannschaftDAO extends DAO{
    public function loadMannschaften(): array{
        return $this->fetchAllObjects(null, "jugendklasse, nummer, geschlecht");
    }
}

?>