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
    
    public function loadMannschaftenMitMeldungen(): array{
        $mannschaften = $this->loadMannschaften();

        $mannschaftIDs = Mannschaft::getIDs($mannschaften);
        $filter = "mannschaft in (".implode(", ", $mannschaftIDs).")";
        $meldungDAO = new MannschaftsMeldungDAO($this->dbhandle);
        $meldungen = $meldungDAO->loadMannschaftsMeldungen($filter);

        foreach($meldungen as $meldung){
            $mannschaften[$meldung->getMannschaft()]->addMeldung($meldung);
        }

        return $mannschaften;
    }
}

?>