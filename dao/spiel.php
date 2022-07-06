<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/spiel.php";
require_once WP_PLUGIN_DIR."/dienstedienst/dao/dienst.php";

class SpielDAO{
    private $dbhandle;
    private string $table_name;
    // TODO function zum erstellen der DB-Tabelle
    // TODO spaltennamen als Klassenkonstanten

    public function __construct(){
        global $wpdb;
        $this->dbhandle = $wpdb;
        $this->table_name = $wpdb->prefix."spiel";
    }

    public function getTableName(): string{
        return $this->table_name;
    }

    public function findSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, int $isHeimspiel): ?Spiel{
        $sql = "SELECT * FROM ".$this->getTableName()." "
                ."WHERE mannschaftsmeldung=$mannschaftsmeldung AND spielnr=$spielnr AND mannschaft=$mannschaft_id AND gegner=$gegner_id AND heimspiel=$isHeimspiel";
        $result = $this->dbhandle->get_row($sql, ARRAY_A);
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
        $tables = $this->getTableName()." LEFT JOIN $table_meldung ON ".$this->getTableName().".mannschaftsmeldung=$table_meldung.id";
        $sql = "SELECT ".$this->getTableName().".*, $table_meldung.aktiv FROM $tables WHERE $whereClause ORDER BY $orderBy";
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
}

function countSpiele(int $mannschaftsmeldung, int $mannschaftsID): int {
    global $wpdb;
    $table_name = $wpdb->prefix . 'spiel';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE mannschaftsmeldung=$mannschaftsmeldung AND mannschaft=$mannschaftsID");
}

function insertSpiel(int $mannschaftsmeldung, int $spielnr, int $mannschaft_id, int $gegner_id, bool $isHeimspiel, int $halle, ?DateTime $anwurf){
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'spiel';
    $wpdb->insert($table_name, array(
        'mannschaftsmeldung' => $mannschaftsmeldung, 
        'spielnr' => $spielnr, 
        'mannschaft' => $mannschaft_id, 
        'gegner' => $gegner_id, 
        'heimspiel' => $isHeimspiel, 
        'halle' => $halle, 
        'anwurf' => isset($anwurf) ? $anwurf->format('Y-m-d H:i:s') : null
    ));
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