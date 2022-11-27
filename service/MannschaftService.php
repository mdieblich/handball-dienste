<?php

require_once __dir__."/../handball/MannschaftsListe.php";
require_once __dir__."/../dao/VereinDAO.php";
require_once __dir__."/../dao/MannschaftDAO.php";
require_once __dir__."/MannschaftsMeldungService.php";

class MannschaftService{
    private VereinDAO $vereinDAO;
    private MannschaftDAO $mannschaftDAO;
    private MannschaftsMeldungService $meldungService;

    public function __construct($dbhandle=null){
        $this->vereinDAO = new VereinDAO($dbhandle);
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->meldungService = new MannschaftsMeldungService($dbhandle);
    }
    
    public function loadMannschaftenMitMeldungen(): MannschaftsListe{
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();

        if(count($mannschaftsListe->mannschaften) === 0){
            return $mannschaftsListe;
        }

        // Vereine setzen
        $vereine = $this->vereinDAO->fetchAll();
        foreach($mannschaftsListe->mannschaften as $mannschaft){
            $mannschaft->verein = $vereine[$mannschaft->verein_id];
            unset($mannschaft->verein_id);
        }

        $mannschaftIDs = $mannschaftsListe->getIDs();
        $filter_mannschaften = "mannschaft_id in (".implode(", ", $mannschaftIDs).")";
        $meldungen = $this->meldungService->load($filter_mannschaften);

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