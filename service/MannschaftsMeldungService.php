<?php

require_once __dir__."/../dao/MannschaftsMeldungDAO.php";
require_once __dir__."/../dao/MeisterschaftDAO.php";
require_once __dir__."/../dao/LigaDAO.php";

class MannschaftsMeldungService{
    private MannschaftsMeldungDAO $meldungDAO;
    private MeisterschaftDAO $meisterschaftDAO;
    private LigaDAO $ligaDAO;

    public function __construct($dbhandle=null){
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
        $this->meisterschaftDAO = new MeisterschaftDAO($dbhandle);
        $this->ligaDAO = new LigaDAO($dbhandle);
    }
    
    public function load(string $where = null): array{
        
        $meisterschaften = $this->meisterschaftDAO->loadMeisterschaften();
        $ligen = $this->ligaDAO->fetchAll();
        $meldungen = $this->meldungDAO->fetchAll($where);

        foreach($meldungen as $meldung){
            // Meisterschaft zuweisen
            $meisterschaft = $meisterschaften[$meldung->meisterschaft_id];
            $meldung->meisterschaft = $meisterschaft;
            unset($meldung->meisterschaft_id);

            // Liga zuweisen
            $liga = $ligen[$meldung->liga_id];
            $meldung->liga = $liga;
            unset($meldung->liga_id);
        }

        return $meldungen;
    }
}

?>