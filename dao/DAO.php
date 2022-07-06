<?php
abstract class DAO{
    protected $dbhandle;

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

    public function fetch(string $whereClause): ?array {
        $sql = "SELECT * FROM ".static::tableName($this->dbhandle)." WHERE $whereClause";
        return $this->dbhandle->get_row($sql, ARRAY_A);
    }

    public function count(string $whereClause): int {
        return $this->dbhandle->get_var("SELECT COUNT(*) FROM ".self::tableName()." WHERE $whereClause");
    }

    public function insert(array $entity) {
        $this->dbhandle->insert(self::tableName(), $entity);
    }
}
?>