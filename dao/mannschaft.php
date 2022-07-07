<?php
require_once __dir__."/../entity/mannschaft.php";
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

function loadMannschaftenMitMeldungen(): array{
    $mannschaftDAO = new MannschaftDAO();
    $mannschaften = $mannschaftDAO->loadMannschaften();
    
    $mannschaftIDs = Mannschaft::getIDs($mannschaften);
    $filter = "mannschaft in (".implode(", ", $mannschaftIDs).")";

    $meldungDAO = new MannschaftsMeldungDAO();
    $meldungen = $meldungDAO->loadMannschaftsMeldungen($filter);
    foreach($meldungen as $meldung){
        $mannschaften[$meldung->getMannschaft()]->addMeldung($meldung);
    }

    return $mannschaften;
}

function getMannschaftFromName(array $mannschaften, string $name): ?Mannschaft{
    foreach($mannschaften as $mannschaft){
        if($mannschaft->getName() === $name){
            return $mannschaft;
        }
    }
    return null;
}
?>