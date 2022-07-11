<?php

require_once __dir__."/../handball/MannschaftsListe.php";
require_once __dir__."/../dao/MannschaftDAO.php";
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";

class MannschaftService{
    private MannschaftDAO $mannschaftDAO;
    private MannschaftsMeldungDAO $meldungDAO;
    private MeisterschaftDAO $meisterschaftDAO;

    public function __construct($dbhandle=null){
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
        $this->meisterschaftDAO = new MeisterschaftDAO($dbhandle);
    }
    
    public function loadMannschaftenMitMeldungen(): MannschaftsListe{
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();

        if(count($mannschaftsListe->mannschaften) === 0){
            return $mannschaftsListe;
        }
        
        $meisterschaften = $this->meisterschaftDAO->loadMeisterschaften();

        $mannschaftIDs = $mannschaftsListe->getIDs();
        $filter = "mannschaft_id in (".implode(", ", $mannschaftIDs).")";
        $meldungen = $this->meldungDAO->loadMannschaftsMeldungen($filter);

        foreach($meldungen as $meldung){
            // Mannschaft zuweisen
            $mannschaft = $mannschaftsListe->mannschaften[$meldung->mannschaft_id];
            $mannschaft->meldungen[$meldung->id] = $meldung;
            $meldung->mannschaft = $mannschaft;
            unset($meldung->mannschaft_id);

            // Meisterschaft zuweisen
            $meisterschaft = $meisterschaften[$meldung->meisterschaft_id];
            $meldung->meisterschaft = $meisterschaft;
            unset($meldung->meisterschaft_id);
        }

        return $mannschaftsListe;
    }
}

?>