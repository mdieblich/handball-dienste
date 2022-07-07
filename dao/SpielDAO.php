<?php
require_once __DIR__."/../entity/Spiel.php";

require_once __DIR__."/DAO.php";
require_once __DIR__."/MannschaftsMeldungDAO.php";
require_once __DIR__."/DienstDAO.php";

class SpielDAO extends DAO{
    // TODO function zum erstellen der DB-Tabelle
    // TODO spaltennamen als Klassenkonstanten

    private DienstDAO $dienstDAO;

    public function __construct($dbhandle = null){
        parent::__construct($dbhandle);
        $this->dienstDAO = new DienstDAO($dbhandle);
    }

    public function findSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, int $isHeimspiel): ?Spiel{
        return $this->fetch("mannschaftsmeldung=$mannschaftsmeldung AND spielnr=$spielnr AND mannschaft=$mannschaft_id AND gegner=$gegner_id AND heimspiel=$isHeimspiel");
    }

    public function loadSpiele(
            string $where="anwurf > subdate(current_date, 1)", 
            string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft"
        ): array{
        
        $where .= " AND mannschaftsmeldung in (SELECT id FROM ".MannschaftsMeldungDAO::tableName($this->dbhandle)." WHERE aktiv=1)";
        return $this->fetchAll($where, $orderBy);
    }

    public function countSpiele(int $mannschaftsmeldung, int $mannschaftsID): int {
        return $this->count("mannschaftsmeldung=$mannschaftsmeldung AND mannschaft=$mannschaftsID");
    }
    
    public function insertSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, bool $isHeimspiel, int $halle, ?DateTime $anwurf){
        $this->insert(array(
            'mannschaftsmeldung' => $mannschaftsmeldung, 
            'spielnr' => $spielnr, 
            'mannschaft' => $mannschaft_id, 
            'gegner' => $gegner_id, 
            'heimspiel' => $isHeimspiel, 
            'halle' => $halle, 
            'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
        ));
    }
    public function updateSpiel(int $id, int $halle, ?DateTime $anwurf){
        $this->update($id, array(
              'halle' => $halle, 
              'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
        ));
    }
}
?>