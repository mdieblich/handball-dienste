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
            $this->assignValue($entity, $key, $value);
        }
        return $entity;
    }
    private function assignValue($entity, $key, $value){
        $rc = new ReflectionClass(static::entityClassName());
        if(!$rc->hasProperty($key)){
            // Relationen werden in der DB- als "XXX_id" abgebildet und sind somit nicht Teil der Klasse
            $entity->$key = $value;
            return;
        }
        $propertyType = $rc->getProperty($key)->getType();
        if($propertyType->isBuiltin()){
            if($propertyType->getName() === "boolean"){
                $entity->$key = ($value != 0);
            }else{
                $entity->$key = $value;
            }
        } else if($propertyType->getName() === "DateTime"){
            $entity->$key = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        }
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
        $array = $this->entityToArray($entity);
        $this->dbhandle->insert(self::tableName($this->dbhandle), $array);
        $entity->id = $this->dbhandle->insert_id;
        return $this->dbhandle->insert_id;
    }

    private function entityToArray($entity): array{
        $array = (array) $entity;    
        foreach($array as $key => $value){
            $rc = new ReflectionClass(static::entityClassName());
            if(!$rc->hasProperty($key)){
                unset($array[$key]);
            }
            $propertyType = $rc->getProperty($key)->getType();
            if(!$propertyType->isBuiltin()){
                if($propertyType->getName() === "DateTime"){
                    $array[$key] = $value->format('Y-m-d H:i:s');
                }else{
                    $array[$key."_id"] = $value->id;
                }
            }
        }
        return $array;
    }

    protected function update(int $id, array $values){
        $this->dbhandle->update(self::tableName($this->dbhandle), $values, array('id' => $id));
    }
    protected function delete(array $identifiers){
        $this->dbhandle->delete(self::tableName($this->dbhandle), $identifiers);
    }
}
?>