<?php
require_once __DIR__."/../handball/Spiel.php";
require_once __DIR__."/../handball/SpieleListe.php";

require_once __DIR__."/DAO.php";
require_once __DIR__."/MannschaftsMeldungDAO.php";

class SpielDAO extends DAO{
    // TODO function zum erstellen der DB-Tabelle
    // TODO spaltennamen als Klassenkonstanten

    public function findSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, int $isHeimspiel): ?Spiel{
        return $this->fetch("mannschaftsmeldung_id=$mannschaftsmeldung AND spielnr=$spielnr AND mannschaft_id=$mannschaft_id AND gegner_id=$gegner_id AND heimspiel=$isHeimspiel");
    }

    public function loadSpiele(
            string $where="anwurf > subdate(current_date, 1)", 
            string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft_id"
        ): SpieleListe{
        
        $where .= " AND mannschaftsmeldung_id in (SELECT id FROM ".MannschaftsMeldungDAO::tableName($this->dbhandle)." WHERE aktiv=1)";
        $spiele = $this->fetchAll($where, $orderBy);
        return new SpieleListe($spiele);
    }

    public function countSpiele(int $mannschaftsmeldung, int $mannschaftsID): int {
        return $this->count("mannschaftsmeldung_id=$mannschaftsmeldung AND mannschaft_id=$mannschaftsID");
    }
    
}
?>