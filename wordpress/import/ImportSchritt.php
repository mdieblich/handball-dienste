<?php

require_once __DIR__."/../log/Log.php";

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
    public function run(): ?array{
        $this->initImportStatus();
        
        $logfile = new Log("Import_".$this->schritt);
        $logfile->log("=================================================");
        $logfile->log("START ".$this->beschreibung);
        $logfile->log(date("d.m.y H:i:s"));
        $logfile->log("=================================================");
        try{
            $problems = call_user_func($this->method, $logfile);
        } catch (Exception $e){
            $logfile->log("FEHLER - Unvorhergesehener Fehler (".get_class($e).") ist aufgetreten:");
            $logfile->log($e->getMessage());
        }
        $logfile->log("=================================================");
        $logfile->log("ENDE ".$this->beschreibung);
        $logfile->log(date("d.m.y H:i:s"));
        $logfile->log("=================================================");
        $this->finishImportStatus();
        return $problems;
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

    public function logFiles(): array {
        $files = Log::findFiles("Import_".$this->schritt);
        krsort($files);
        return $files;
    }
}
?>