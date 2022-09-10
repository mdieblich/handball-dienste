<?php
class ImportSchritt{
    public int $schritt;
    public string $beschreibung;
    private Closure $method;
    
    public function __construct(int $schritt, string $beschreibung, Closure $method){
        $this->schritt = $schritt;
        $this->beschreibung = $beschreibung;
        $this->method = $method;
    }
    
    // TODO dbHandle als parameter hereinreichen
    public function run(){
        $this->initImportStatus();
        echo "=================================================\n";
        echo "START ".$this->beschreibung."\n";
        echo "=================================================\n";
        call_user_func($this->method);
        echo "=================================================\n";
        echo "ENDE ".$this->beschreibung."\n";
        echo "=================================================\n";
        $this->finishImportStatus();
    }

    private function initImportStatus(){
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'import_status';
        $eintrag_vorhanden = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE schritt = ".$this->schritt);
        if($eintrag_vorhanden > 0){
            $wpdb->update(
                $table_name, 
                array(
                    'beschreibung' => $this->beschreibung,
                    'start' => current_time('mysql'), 
                    'ende' => null
                ), 
                array('schritt' => $this->schritt)
            );
        }else{
            $wpdb->insert(
                $table_name, 
                array(
                    'schritt' => $this->schritt,
                    'beschreibung' => $this->beschreibung
                )
            );
        }
    }
    
    private function finishImportStatus(){
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'import_status';
        $wpdb->update($table_name, 
            array('ende' => current_time('mysql')), 
            array('schritt' => $this->schritt)
        );
        return $wpdb->insert_id;
    }

    public static function initAlleSchritte(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'import_status';
        $wpdb->query("UPDATE $table_name set start=null, ende=null");
    }
}
?>