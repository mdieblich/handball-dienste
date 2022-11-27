<?php

require_once __dir__."/../handball/MannschaftsListe.php";
require_once __dir__."/../dao/VereinDAO.php";
require_once __dir__."/../dao/MannschaftDAO.php";
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";
require_once __dir__."/../dao/MeisterschaftDAO.php";
require_once __dir__."/../dao/LigaDAO.php";

class MannschaftService{
    private VereinDAO $vereinDAO;
    private MannschaftDAO $mannschaftDAO;
    private MannschaftsMeldungDAO $meldungDAO;
    private MeisterschaftDAO $meisterschaftDAO;
    private LigaDAO $ligaDAO;

    public function __construct($dbhandle=null){
        $this->vereinDAO = new VereinDAO($dbhandle);
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
        $this->meisterschaftDAO = new MeisterschaftDAO($dbhandle);
        $this->ligaDAO = new LigaDAO($dbhandle);
    }
    
    public function loadMannschaftenMitMeldungen(): MannschaftsListe{
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();

        if(count($mannschaftsListe->mannschaften) === 0){
            return $mannschaftsListe;
        }

        $mannschaftIDs = $mannschaftsListe->getIDs();
        $filter_mannschaften = "mannschaft_id in (".implode(", ", $mannschaftIDs).")";

        // Vereine setzen
        $vereine = $this->vereinDAO->fetchAll($filter_mannschaften);
        foreach($mannschaftsListe->mannschaften as $mannschaft){
            $mannschaft->verein = $vereine[$mannschaft->verein_id];
            unset($mannschaft->verein_id);
        }
        
        $meisterschaften = $this->meisterschaftDAO->loadMeisterschaften();
        $ligen = $this->ligaDAO->fetchAll();
        $meldungen = $this->meldungDAO->fetchAll($filter_mannschaften);

        foreach($meldungen as $meldung){
            // Mannschaft zuweisen
            $mannschaft = $mannschaftsListe->mannschaften[$meldung->mannschaft_id];
            $mannschaft->meldungen[$meldung->id] = $meldung;
            $meldung->mannschaft = $mannschaft;
            unset($meldung->mannschaft_id);

            // Liga zuweisen
            $liga = $ligen[$meldung->liga_id];
            $meldung->liga = $liga;
            unset($meldung->liga_id);

            // Meisterschaft zuweisen
            $meisterschaft = $meisterschaften[$meldung->meisterschaft_id];
            $meldung->meisterschaft = $meisterschaft;
            unset($meldung->meisterschaft_id);
        }

        return $mannschaftsListe;
    }
}

?>