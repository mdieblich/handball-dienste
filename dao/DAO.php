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

    private static function entityClassName(): string{
        $className = static::class;
        $entityClassName = substr($className, 0, -3);
        return $entityClassName;
    }

    public static function tableName($dbhandle = null): string{
        if(empty($dbhandle)){
            global $wpdb;
            $dbhandle = $wpdb;
        }
        $table_suffix = strtolower(static::entityClassName());
        return $dbhandle->prefix.$table_suffix;
    }

    // TODO ersetzen durch fetch2
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
        $entityClassName = static::entityClassName();
        require_once __dir__."/../entity/$entityClassName.php";
        return new $entityClassName($array);
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
        $entityClassName = static::entityClassName();
        require_once __dir__."/../handball/$entityClassName.php";
        $entity = new $entityClassName();
        foreach($array as $key => $value){
            if($this->isBooleanProperty($entityClassName, $key)){
                $entity->$key = ($value != 0);
            }else{
                $entity->$key = $value;
            }
        }
        return $entity;
    }
    private function isBooleanProperty($entityClassName, $property): bool{
        $rc = new ReflectionClass($entityClassName);
        if(!$rc->hasProperty($property)){
            // Relationen werden in der DB- als "XXX_id" abgebildet
            return false;
        }
        $rp = $rc->getProperty($property);
        return $rp->getType()->getName() === "boolean";
    }

    // TODO ersetzen durch fetchAll2
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
            $object = $this->createEntityBackedByArray($row);
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

    // TODO ersetzen durch insert2
    protected function insert(array $entity): int{
        $this->dbhandle->insert(self::tableName($this->dbhandle), $entity);
        return $this->dbhandle->insert_id;
    }
    protected function insert2(object $entity): int{
        $this->dbhandle->insert(self::tableName($this->dbhandle), (array) $entity);
        $entity->id = $this->dbhandle->insert_id;
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