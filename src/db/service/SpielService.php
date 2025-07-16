<?php

require_once __dir__."/../dao/SpielDAO.php";
require_once __dir__."/../dao/DienstDAO.php";
require_once __dir__."/../dao/MannschaftDAO.php";
require_once __dir__."/../dao/MannschaftsMeldungDAO.php";
require_once __dir__."/../dao/GegnerDAO.php";

class SpielService{
    private MannschaftDAO $mannschaftDAO;
    private MannschaftsMeldungDAO $meldungDAO;
    private GegnerDAO $gegnerDAO;
    private SpielDAO $spielDAO;
    private DienstDAO $dienstDAO;

    public function __construct($dbhandle=null){
        $this->mannschaftDAO = new MannschaftDAO($dbhandle);
        $this->meldungDAO = new MannschaftsMeldungDAO($dbhandle);
        $this->gegnerDAO = new GegnerDAO($dbhandle);
        $this->spielDAO = new SpielDAO($dbhandle);
        $this->dienstDAO = new DienstDAO($dbhandle);
    }

    public function fetchCompletely(string $where): Spiel{
        $spiel = $this->spielDAO->fetch($where);
        $alleMannschaften = $this->mannschaftDAO->fetchAll();
        $spiel->mannschaft = $alleMannschaften[$spiel->mannschaft_id];
        $spiel->mannschaftsMeldung = $this->meldungDAO->fetch("id=$spiel->mannschaftsMeldung_id");
        $spiel->gegner = $this->gegnerDAO->fetch("id=$spiel->gegner_id");
        $dienste = $this->dienstDAO->fetchAll("spiel_id=$spiel->id");
        foreach($dienste as $dienst){
            $spiel->dienste[] = $dienst;
            $dienst->spiel = $spiel;
            if(isset($dienst->mannschaft_id)){
                $dienst->mannschaft = $alleMannschaften[$dienst->mannschaft_id];
            }
        }
        return $spiel;
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
        
        $mannschaftsListe = $this->mannschaftDAO->getAllWithGlobalCache();
        $this->appendMannschaften($spieleListe, $mannschaftsListe);
        $this->appendGegner($spieleListe);
        $this->appendDienste($spieleListe, $mannschaftsListe);
        return $spieleListe;
    }

    public function loadSpieleFuerMannschaft(int $nummer, string $geschlecht, ?string $jugendklasse
    ): SpieleListe{
        $table_mannschaft = MannschaftDAO::tableName();
        $whereClause = "mannschaft_id=(SELECT id from $table_mannschaft where nummer=$nummer AND geschlecht='$geschlecht' AND jugendklasse";
        if(empty($jugendklasse)){
            $whereClause .= " IS NULL";
        } else {
            $whereClause .= "='$jugendklasse'";
        }
        $whereClause .= ") AND anwurf > subdate(current_date, 1)";
        return $this->loadSpieleMitDiensten($whereClause);
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
        
        $mannschaftsListe = $this->mannschaftDAO->getAllWithGlobalCache();
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
        $spieleProHalle = [];
        $spieleListe = $this->loadSpieleMitDiensten($where, "halle, $orderBy");
        foreach($spieleListe->spiele as $spiel){
            if(!array_key_exists($spiel->halle, $spieleProHalle)){
                $spieleProHalle[$spiel->halle] = new SpieleListe();
            }
            $spieleProHalle[$spiel->halle]->spiele[] = $spiel;
        }
        return $spieleProHalle;
    }

    public function fetchSpieleMitDienstenProHalle(string $where = "anwurf > current_timestamp", string $orderBy = "anwurf"): array{
        $spieleProHalle = [];
        
        
        $spieleListe = $this->spielDAO->loadSpiele($where, $orderBy);
        if(count($spieleListe->spiele) == 0){
            return [];
        }
        
        $mannschaftsListe = $this->mannschaftDAO->getAllWithGlobalCache();
        $this->appendDienste($spieleListe, $mannschaftsListe);

        foreach($spieleListe->spiele as $spiel){
            if(!array_key_exists($spiel->halle, $spieleProHalle)){
                $spieleProHalle[$spiel->halle] = new SpieleListe();
            }
            $spieleProHalle[$spiel->halle]->spiele[] = $spiel;
        }
        return $spieleProHalle;
    }
}