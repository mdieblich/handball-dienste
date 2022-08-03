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
    
    // TODO umbenennen zu loadSpiele
    public function loadSpieleMitDiensten(
        string $whereClause="anwurf > subdate(current_date, 1)", 
        string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft_id"
    ): SpieleListe{
        $spieleListe = $this->spielDAO->loadSpiele($whereClause, $orderBy);
        if(count($spieleListe->spiele) == 0){
            return $spieleListe;
        }
        
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();
        $this->appendMannschaften($spieleListe, $mannschaftsListe);
        $this->appendGegner($spieleListe);
        $this->appendDienste($spieleListe, $mannschaftsListe);
        return $spieleListe;
    }

    public function findOriginalSpiel(Spiel $newSpiel): ?Spiel{
        $searchSpiel = clone $newSpiel;
        unset($searchSpiel->id);
        unset($searchSpiel->anwurf);
        unset($searchSpiel->halle);
        $oldSpiel = $this->spielDAO->findSimilar($searchSpiel);
        if(empty($oldSpiel)){
            return null;
        }
        
        $mannschaftsListe = $this->mannschaftDAO->loadMannschaften();
        $mannschaft = $mannschaftsListe->mannschaften[$oldSpiel->mannschaft_id];
        $oldSpiel->mannschaft = $mannschaft;
        unset($oldSpiel->mannschaft_id);

        $gegner = $this->gegnerDAO->fetch("id=".$oldSpiel->gegner_id);
        $oldSpiel->gegner = $gegner;
        unset($oldSpiel->gegner_id);

        $dienste = $this->dienstDAO->loadAllDienste("spiel_id=".$oldSpiel->id);
        foreach($dienste as $dienst){
            $oldSpiel->dienste[$dienst->dienstart] = $dienst;
            $dienst->spiel = $oldSpiel;
            unset($dienst->spiel_id);

            if(isset($dienst->mannschaft_id)){
                $mannschaft = $mannschaftsListe->mannschaften[$dienst->mannschaft_id];
                $dienst->mannschaft = $mannschaft;
                unset($dienst->mannschaft_id);
            }
        } 
        return $oldSpiel;
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
            
            if(isset($dienst->mannschaft_id)){
                $mannschaft = $mannschaftsListe->mannschaften[$dienst->mannschaft_id];
                $dienst->mannschaft = $mannschaft;
                unset($dienst->mannschaft_id);
            }
        } 
    }

    public function fetchSpieleProHalle(string $where = "anwurf > current_timestamp", string $orderBy = "anwurf"): array{
        $spieleProHalle = array();
        $spiele = $this->loadSpieleMitDiensten($where, "halle, ".$orderBy);
        foreach($spiele as $spiel){
            if(!array_key_exists($spiel->halle, $spieleProHalle)){
                $spieleProHalle[$spiel->halle] = new SpieleListe();
            }
            $spieleProHalle[$spiel->halle]->spiele[] = $spiel;
        }
        return $spieleProHalle;
    }
}

?>