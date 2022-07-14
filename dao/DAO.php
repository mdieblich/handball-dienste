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
        return static::classToTableName(static::entityClassName(), $dbhandle);
    }

    private static function classToTableName(string $className, $dbhandle): string{
        $table_suffix = strtolower($className);
        return $dbhandle->prefix.$table_suffix;
    }

    public static function tableCreation($dbhandle = null): string{
        if(empty($dbhandle)){
            global $wpdb;
            $dbhandle = $wpdb;
        }

        $table_name = static::tableName($dbhandle);
        $charset_collate = $dbhandle->get_charset_collate();

        $rc = new ReflectionClass(static::entityClassName());
        $idProperties = array(
            "id INT NOT NULL AUTO_INCREMENT",
            "PRIMARY KEY (id)"
        );
        $sqlPropertyList = array();
        $foreignKeyList = array();
        foreach($rc->getProperties() as $property){
            if($property->name === "id"){
                // wird separat gehandhabt
                continue; 
            }
            if($property->getType()->getName() == "array"){
                // arrays werden in Services zusammengebaut
                continue; 
            }

            $columnName = $property->name;
            $sqlType = static::getSQLType($property->getType());
            $nullable = static::getNullability($property->getType());
            // Default wird nicht gesetzt, da das erst ab PHP 8 vernünftig läuft
            $foreignKey = static::getForeignKey($property, $dbhandle);

            if(isset($foreignKey)){
                $columnName .= "_id";
                $foreignKeyList[] = $foreignKey;
            }else{
            }
            $sqlPropertyList[] = "$columnName $sqlType $nullable";
        }

        $properties = array_merge($idProperties, $sqlPropertyList, $foreignKeyList);
        
        $sql = 
            "CREATE TABLE $table_name (\n"
                ."\t".implode(", \n\t", $properties)."\n"
            .") $charset_collate, ENGINE = InnoDB;";

        return $sql;
    }

    private static function getSQLType(ReflectionType $propertyType): string{
        switch($propertyType->getName()){
            case "int": return "INT";
            case "string": return "VARCHAR(1024)";
            case "bool": return "TINYINT";
            case "DateTime": return "DATETIME";
            default: return "INT";
        }
    }

    private static function getNullability(ReflectionType $propertyType): string{
        if($propertyType->allowsNull()){
            return "NULL";
        }
        return "NOT NULL";
    }

    private static function getForeignKey(ReflectionProperty $property, $dbhandle): ?string{
        $propertyType = $property->getType();
        if($propertyType->isBuiltin() || $propertyType->getName() === "DateTime" ){
            return null;
        }
        return "FOREIGN KEY (".$property->name."_id) REFERENCES ".static::classToTableName($propertyType->getName(), $dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE";
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

        return $this->createEntity($array);
    }

    public function findSimilar(object $entity): ?object {
        $comparisons = array();
        foreach($this->entityToArray($entity) as $key => $value){
            if($key === 'id'){ continue; }
            if($value === null){ continue; }
            $comparisons[] = "$key = '$value'";
        }
        $where = implode(" AND ", $comparisons);
        return $this->fetch($where);
    }

    private function createEntity($array): object{
        $entityClassName = static::entityClassName();
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
            $object = $this->createEntity($row);
            $objects[$object->id] = $object;
        }
        return $objects;
    }

    public function count(string $whereClause): int {
        return $this->dbhandle->get_var("SELECT COUNT(*) FROM ".self::tableName($this->dbhandle)." WHERE $whereClause");
    }

    // TODO umbenennen zu insert
    public function insert2(object $entity): int{
        $array = $this->entityToArray($entity);
        $this->dbhandle->insert(self::tableName($this->dbhandle), $array);
        $entity->id = $this->dbhandle->insert_id;
        return $this->dbhandle->insert_id;
    }

    private function entityToArray($entity): array{
        $rc = new ReflectionClass(static::entityClassName());
        $array = (array) $entity;    
        foreach($array as $key => $value){
            if($this->isIdFromOtherClass($key)){
                continue;
            }
            $propertyType = $rc->getProperty($key)->getType();
            if(!$propertyType->isBuiltin()){
                if($propertyType->getName() === "DateTime"){
                    $array[$key] = $value->format('Y-m-d H:i:s');
                }else{
                    unset($array[$key]);
                    $array[$key."_id"] = $value->id;
                }
            } else if($propertyType->getName() === "array"){
                // arrays werden nicht unterstützt
                unset($array[$key]);
            }
        }
        return $array;
    }

    private function isIdFromOtherClass($propertyName): bool{
        $rc = new ReflectionClass(static::entityClassName());
        if($rc->hasProperty($propertyName)){
            return false;
        }
        if(substr($propertyName, -3)!=="_id"){
            // muss auf _id enden
            return false;
        }
        $originalPropertyName = substr($propertyName, 0, strlen($propertyName)-3);
        return $rc->hasProperty($originalPropertyName);
    }

    protected function update(int $id, array $values){
        $this->dbhandle->update(self::tableName($this->dbhandle), $values, array('id' => $id));
    }
    public function update2(int $id, object $object){
        $values = $this->entityToArray($object);
        unset($values['id']);
        $this->dbhandle->update(self::tableName($this->dbhandle), $values, array('id' => $id));
    }
    protected function delete(array $identifiers){
        $this->dbhandle->delete(self::tableName($this->dbhandle), $identifiers);
    }
}
?>