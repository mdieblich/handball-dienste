<?php

require_once __DIR__."/../../handball/MannschaftsListe.php";

require_once __dir__."/MannschaftsMeldungService.php";
require_once __dir__."/../dao/MannschaftDAO.php";

class MannschaftService{
    private MannschaftDAO $mannschaftDAO;
    private MannschaftsMeldungService $meldungService;

    public function __construct($dbhandle=null){
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->meldungService = new MannschaftsMeldungService($dbhandle);
    }
    
    public function loadMannschaftenMitMeldungen(): MannschaftsListe{
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();

        if(count($mannschaftsListe->mannschaften) === 0){
            return $mannschaftsListe;
        }
        
        $mannschaftIDs = $mannschaftsListe->getIDs();
        $filter = "mannschaft_id in (".implode(", ", $mannschaftIDs).")";
        $meldungen = $this->meldungService->loadMannschaftsMeldungenMitMeisterschaften($filter);

        foreach($meldungen as $meldung){
            // Mannschaft zuweisen
            $mannschaft = $mannschaftsListe->mannschaften[$meldung->mannschaft_id];
            $mannschaft->meldungen[$meldung->id] = $meldung;
            $meldung->mannschaft = $mannschaft;
            unset($meldung->mannschaft_id);
        }

        return $mannschaftsListe;
    }
}

?>