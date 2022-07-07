<?php

require_once __dir__."/../dao/MannschaftDAO.php";
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";

class MannschaftService{
    private MannschaftDAO $mannschaftDAO;
    private MannschaftsMeldungDAO $meldungDAO;

    public function __construct($dbhandle=null){
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
    }
    
    public function loadMannschaftenMitMeldungen(): array{
        $mannschaften = $this->mannschaftDAO->loadMannschaften();

        if(count($mannschaften) === 0){
            return $mannschaften;
        }

        $mannschaftIDs = Mannschaft::getIDs($mannschaften);
        $filter = "mannschaft in (".implode(", ", $mannschaftIDs).")";
        $meldungen = $this->meldungDAO->loadMannschaftsMeldungen($filter);

        foreach($meldungen as $meldung){
            $mannschaften[$meldung->getMannschaft()]->addMeldung($meldung);
        }

        return $mannschaften;
    }
}

?>