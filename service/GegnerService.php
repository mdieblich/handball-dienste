<?php
require_once __dir__."/../dao/GegnerDAO.php";
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";
require_once __dir__."/../dao/MannschaftDAO.php";

class GegnerService {
    private GegnerDAO $gegnerDAO;
    private MannschaftsMeldungDAO $meldungDAO;
    private MannschaftDAO $mannschaftDAO;

    public function __construct($dbhandle=null){
        $this->gegnerDAO = new GegnerDAO($dbhandle);
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
    }

    public function loadAlleGegner(): array{
        $alleGegner = $this->gegnerDAO->loadGegner();
        if(empty($alleGegner)){
            return $alleGegner;
        }
        
        $meldungen = $this->loadMeldungen($alleGegner);
        foreach($alleGegner as $gegner){
            $gegner->zugehoerigeMeldung = $meldungen[$gegner->zugehoerigeMeldung_id];
            unset($gegner->zugehoerigeMeldung_id);
        }

        return $alleGegner;
    }

    private function loadMeldungen(array $alleGegner): array{
        $meldungen = $this->meldungDAO->fetchAllByIds($this->meldungIDs($alleGegner));

        $mannschaften = $this->mannschaftDAO->fetchAllByIds($this->mannschaftIDs($meldungen));
        foreach($meldungen as $meldung){
            $meldung->mannschaft = $mannschaften[$meldung->mannschaft_id];
            unset($meldung->mannschaft_id);
        }
        
        return $meldungen;
    }

    private function meldungIDs(array $alleGegner): array{
        $ids = array();
        foreach($alleGegner as $gegner){
            $ids[$gegner->zugehoerigeMeldung_id] = true;
        }
        return array_keys($ids);
    }

    private function mannschaftIDs(array $meldungen){
        $ids = array();
        foreach($meldungen as $meldung){
            $ids[$meldung->mannschaft_id] = true;
        }
        return array_keys($ids);
    }

    public function loadAktiveGegner(): array{
        $alleGegner = $this->loadAlleGegner();

        foreach($alleGegner as $id => $gegner){
            if(empty($gegner->zugehoerigeMeldung)){
                unset($alleGegner[$id]);
                continue;
            }

            if(!$gegner->zugehoerigeMeldung->aktiv){
                unset($alleGegner[$id]);
                continue;
            }
        }

        return $alleGegner;
    }
}
?>