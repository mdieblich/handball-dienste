<?php

require_once __dir__."/../dao/SpielDAO.php";
require_once __dir__."/../dao/DienstDAO.php";
require_once __dir__."/../dao/MannschaftDAO.php";
require_once __dir__."/../dao/GegnerDAO.php";

class SpielService{
    private SpielDAO $spielDAO;
    private DienstDAO $dienstDAO;
    private MannschaftDAO $mannschaftDAO;
    private GegnerDAO $gegnerDAO;

    public function __construct($dbhandle=null){
        $this->spielDAO = new SpielDAO($dbhandle);
        $this->dienstDAO = new DienstDAO($dbhandle);
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->gegnerDAO = new GegnerDAO($dbhandle);
    }
    
    public function loadSpieleMitDiensten(
        string $whereClause="anwurf > subdate(current_date, 1)", 
        string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft_id"
    ): SpieleListe{
        $spieleListe = $this->spielDAO->loadSpiele($whereClause, $orderBy);
        if(count($spieleListe->spiele) == 0){
            return $spielListe;
        }
        
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();
        $this->appendMannschaften($spieleListe, $mannschaftsListe);
        $this->appendGegner($spieleListe);
        $this->appendDienste($spieleListe, $mannschaftsListe);
        return $spieleListe;
    }

    private function appendMannschaften(SpieleListe $spieleListe, MannschaftsListe $mannschaftsListe){
        foreach($spieleListe->spiele as $spiel){
            $mannschaft = $mannschaftsListe->mannschaften[$spiel->mannschaft_id];
            $spiel->mannschaft = $mannschaft;
            unset($spiel->mannschaft_id);
        }
    }
    
    private function appendGegner(SpieleListe $spieleListe){
        $alleGegner = $this->gegnerDAO->loadGegner();
        foreach($spieleListe->spiele as $spiel){
            $gegner = $alleGegner[$spiel->gegner_id];
            $spiel->gegner = $gegner;
            unset($spiel->gegner_id);
        }
    }
    private function appendDienste(SpieleListe $spieleListe, MannschaftsListe $mannschaftsListe){
        $spielIDs = $spieleListe->getIDs();
        $filter = "spiel_id in (".implode(", ", $spielIDs).")";

        $dienste = $this->dienstDAO->loadAllDienste($filter);
        foreach($dienste as $dienst){
            $spiel = $spieleListe->spiele[$dienst->spiel_id];
            $spiel->dienste[$dienst->dienstart] = $dienst;
            $dienst->spiel = $spiel;
            unset($dienst->spiel_id);

            $mannschaft = $mannschaftsListe->mannschaften[$dienst->mannschaft_id];
            $dienst->mannschaft = $mannschaft;
            unset($dienst->mannschaft_id);
        } 
    }
}

?>