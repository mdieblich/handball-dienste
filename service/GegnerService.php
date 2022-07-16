<?php
require_once __dir__."/../dao/GegnerDAO.php";
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";

class GegnerService {
    private GegnerDAO $gegnerDAO;
    private MannschaftsMeldungDAO $meldungDAO;

    public function __construct($dbhandle=null){
        $this->gegnerDAO = new GegnerDAO($dbhandle);
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
    }

    public function loadAlleGegner(): array{
        $alleGegner = $$this->gegnerDAO->loadGegner();

        $alleMeldungen = $this->meldungDAO->fetchAll();
        foreach($alleGegner as $gegner){
            $gegner->zugehoerigeMeldung = $alleMeldungen[$gegner->zugehoerigeMeldung_id];
            unset($gegner->zugehoerigeMeldung_id);
        }

        return $alleGegner;
    }
}
?>