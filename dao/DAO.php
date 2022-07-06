<?php
abstract class DAO{
    private $dbhandle;

    public function __construct($dbhandle = null){
        if(isset($dbhandle)){
            $this->dbhandle = $dbhandle;
        } else{
            global $wpdb;
            $this->dbhandle = $wpdb;
        }
    }

    public static function tableName($dbhandle = null): string{
        if(empty($dbhandle)){
            global $wpdb;
            $dbhandle = $wpdb;
        }
        $className = static::class;
        $entityName = substr($className, 0, -3);
        $table_suffix = strtolower($entityName);
        return $dbhandle->prefix.$table_suffix;
    }
}
?>