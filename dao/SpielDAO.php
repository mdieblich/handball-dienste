<?php
require_once __DIR__."/DAO.php";
require_once WP_PLUGIN_DIR."/dienstedienst/entity/spiel.php";
require_once WP_PLUGIN_DIR."/dienstedienst/dao/dienst.php";

class SpielDAO extends DAO{
    // TODO function zum erstellen der DB-Tabelle
    // TODO spaltennamen als Klassenkonstanten

    public function findSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, int $isHeimspiel): ?Spiel{
        $result = $this->fetch("mannschaftsmeldung=$mannschaftsmeldung AND spielnr=$spielnr AND mannschaft=$mannschaft_id AND gegner=$gegner_id AND heimspiel=$isHeimspiel");
        if(empty($result)){
          return null;
        }
        return new Spiel($result);
    }

    public function loadSpiele(
            string $whereClause="anwurf > subdate(current_date, 1) AND aktiv=1", 
            string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft"
        ): array{
        
        $table_meldung = $this->dbhandle->prefix . 'mannschaftsMeldung'; 
        $tables = self::tableName()." LEFT JOIN $table_meldung ON ".self::tableName().".mannschaftsmeldung=$table_meldung.id";
        $sql = "SELECT ".self::tableName().".*, $table_meldung.aktiv FROM $tables WHERE $whereClause ORDER BY $orderBy";
        $result = $this->dbhandle->get_results($sql, ARRAY_A);
        
        $spiele = array();
        if (count($result) > 0) {
            foreach($result as $spiel) {
                $spielObj = new Spiel($spiel);
                $spiele[$spielObj->getID()] = $spielObj;
            }
        }
        return $spiele;
    }
    
    public function loadSpieleDeep(
            string $whereClause="anwurf > subdate(current_date, 1) AND aktiv=1", 
            string $orderBy="-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft"
        ){
        $dienstDAO = new DienstDAO();

        $spiele = $this->loadSpiele($whereClause, $orderBy);
        $spielIDs = Spiel::getIDs($spiele);
        $filter = "spiel in (".implode(", ", $spielIDs).")";

        foreach($dienstDAO->loadAllDienste($filter) as $dienst){
            $spiele[$dienst->getSpiel()]->addDienst($dienst);
        } 
        return $spiele;
    }

    public function countSpiele(int $mannschaftsmeldung, int $mannschaftsID): int {
        return $this->dbhandle->get_var("SELECT COUNT(*) FROM ".self::tableName()." WHERE mannschaftsmeldung=$mannschaftsmeldung AND mannschaft=$mannschaftsID");
    }
    
    public function insertSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, bool $isHeimspiel, int $halle, ?DateTime $anwurf){
        
        $this->dbhandle->insert(self::tableName(), array(
            'mannschaftsmeldung' => $mannschaftsmeldung, 
            'spielnr' => $spielnr, 
            'mannschaft' => $mannschaft_id, 
            'gegner' => $gegner_id, 
            'heimspiel' => $isHeimspiel, 
            'halle' => $halle, 
            'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
        ));
    }
}



function updateSpiel(int $id, int $halle, ?DateTime $anwurf){
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'spiel';
    $wpdb->update($table_name, 
        array(
          'halle' => $halle, 
          'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
        ), array(
          'id' => $id
        )
    );
}
?>