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

    private static function entityName(): string{
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

    public function fetch(string $where): ?object {
        $sql = "SELECT * FROM ".static::tableName($this->dbhandle);
        if(isset($where)){
            $sql .= " WHERE $where";
        }

        $array = $this->dbhandle->get_row($sql, ARRAY_A);
        if(empty($array)){
            return null;
        }

        return $this->createEntityBackedByArray($array);
    }

    private function createEntityBackedByArray(array $array): object{
        $entityName = static::entityName();
        require_once __dir__."/../entity/$entityName.php";
        return new $entityName($array);
    }
    
    public function fetch2(string $where): ?object {
        $sql = "SELECT * FROM ".static::tableName($this->dbhandle);
        if(isset($where)){
            $sql .= " WHERE $where";
        }

        $array = $this->dbhandle->get_row($sql, ARRAY_A);
        if(empty($array)){
            return null;
        }

        return $this->createEntity($array);
    }

    private function createEntity($array): object{
        $entityName = static::entityName();
        require_once __dir__."/../handball/$entityName.php";
        $entity = new $entityName();
        foreach($array as $key => $value){
            if($this->isBooleanProperty($entity, $key)){
                $entity->$key = ($value != 0);
            }else{
                $entity->$key = $value;
            }
        }
        return $entity;
    }
    private function isBooleanProperty($class, $property): bool{
        $rp = new ReflectionProperty($class, $property);
        return $rp->getType()->getName() === "boolean";
    }

    public function fetchAll(string $where = null, string $orderBy = null): array{
        $sql = "SELECT * FROM ".self::tableName($this->dbhandle);
        if(isset($where)){
            $sql .= " WHERE $where";
        } 
        if(isset($orderBy)){
            $sql .= " ORDER BY $orderBy";
        }

        $rows = $this->dbhandle->get_results($sql, ARRAY_A);    
        $objects = array();
        foreach($rows as $row) {
            $object = $this->createEntityFromArray($row);
            $objects[$object->getID()] = $object;
        }
        return $objects;
    }

    public function fetchAll2(string $where = null, string $orderBy = null): array{
        $sql = "SELECT * FROM ".self::tableName($this->dbhandle);
        if(isset($where)){
            $sql .= " WHERE $where";
        } 
        if(isset($orderBy)){
            $sql .= " ORDER BY $orderBy";
        }

        $rows = $this->dbhandle->get_results($sql, ARRAY_A);    
        $objects = array();
        foreach($rows as $row) {
            $object = $this->createEntity($row);
            $objects[$object->id] = $object;
        }
        return $objects;
    }

    protected function count(string $whereClause): int {
        return $this->dbhandle->get_var("SELECT COUNT(*) FROM ".self::tableName($this->dbhandle)." WHERE $whereClause");
    }

    protected function insert(array $entity): int{
        $this->dbhandle->insert(self::tableName($this->dbhandle), $entity);
        return $this->dbhandle->insert_id;
    }

    protected function update(int $id, array $values){
        $this->dbhandle->update(self::tableName($this->dbhandle), $values, array('id' => $id));
    }
    protected function delete(array $identifiers){
        $this->dbhandle->delete(self::tableName($this->dbhandle), $identifiers);
    }
}
?>