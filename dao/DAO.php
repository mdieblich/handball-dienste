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

    public static function entityName(): string{
        $className = static::class;
        $entityName = substr($className, 0, -3);
        return $entityName;
    }

    public static function tableName($dbhandle = null): string{
        if(empty($dbhandle)){
            global $wpdb;
            $dbhandle = $wpdb;
        }
        $table_suffix = strtolower(static::entityName());
        return $dbhandle->prefix.$table_suffix;
    }

    public function fetch(string $whereClause): ?array {
        $sql = "SELECT * FROM ".static::tableName($this->dbhandle)." WHERE $whereClause";
        return $this->dbhandle->get_row($sql, ARRAY_A);
    }

    public function fetchAll(string $whereClause=null, string $orderBy=null): array {
        $sql = "SELECT * FROM ".self::tableName($this->dbhandle);
        if(isset($whereClause)){
            $sql .= " WHERE $whereClause";
        } 
        if(isset($orderBy)){
            $sql .= " ORDER BY $orderBy";
        }
        return $this->dbhandle->get_results($sql, ARRAY_A);
    }

    public function count(string $whereClause): int {
        return $this->dbhandle->get_var("SELECT COUNT(*) FROM ".self::tableName($this->dbhandle)." WHERE $whereClause");
    }

    public function insert(array $entity): int{
        $this->dbhandle->insert(self::tableName($this->dbhandle), $entity);
        return $this->dbhandle->insert_id;
    }

    public function update(int $id, array $values){
        $this->dbhandle->update(self::tableName($this->dbhandle), $values, array('id' => $id));
    }
    public function delete(array $identifiers){
        $this->dbhandle->delete(self::tableName($this->dbhandle), $identifiers);
    }
}
?>