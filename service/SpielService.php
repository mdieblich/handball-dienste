<?php

require_once __dir__."/../dao/SpielDAO.php";
require_once __dir__."/../dao/DienstDAO.php";

class SpielService{
    private SpielDAO $spielDAO;
    private DienstDAO $dienstDAO;

    public function __construct($dbhandle=null){
        $this->spielDAO = new SpielDAO($dbhandle);
        $this->dienstDAO = new DienstDAO($dbhandle);
    }
    
    public function loadSpieleMitDiensten(
        string $whereClause="anwurf > subdate(current_date, 1)", 
        string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft"
    ){
        $spiele = $this->spielDAO->loadSpiele($whereClause, $orderBy);
        if(count($spiele) == 0){
            return $spiele;
        }
        
        $spielIDs = Spiel::getIDs($spiele);
        $filter = "spiel in (".implode(", ", $spielIDs).")";

        foreach($this->dienstDAO->loadAllDienste($filter) as $dienst){
            $spiele[$dienst->getSpiel()]->addDienst($dienst);
        } 
        return $spiele;
    }
}

?>