<?php

class Meisterschaft {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): string {
        return $this->assoc_array["id"];
    }
    
    public function getKuerzel(): string {
        return $this->assoc_array["kuerzel"];
    }
    
    public function getName(): string {
        return $this->assoc_array["name"];
    }

}
?>