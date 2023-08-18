<?php
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";
require_once __dir__."/../dao/MeisterschaftDAO.php";

class MannschaftsMeldungService {
    private MannschaftsMeldungDAO $meldungDAO;
    private MeisterschaftDAO $meisterschaftDAO;

    public function __construct($dbhandle=null){
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
        $this->meisterschaftDAO = new MeisterschaftDAO($dbhandle);
    }

    public function loadMannschaftsMeldungenMitMeisterschaften(string $where=null): array{
        $meldungen = $this->meldungDAO->fetchAll($where);
        if(empty($meldungen)){
            return $meldungen;
        }
        
        $meisterschaften = $this->meisterschaftDAO->loadMeisterschaften();
        foreach($meldungen as $meldung){
            $meisterschaft = $meisterschaften[$meldung->meisterschaft_id];
            $meldung->meisterschaft = $meisterschaft;
            unset($meldung->meisterschaft_id);
        }

        return $meldungen;
    }
}
?>