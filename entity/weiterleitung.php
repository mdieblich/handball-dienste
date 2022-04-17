<?php
class Weiterleitung {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }
    
    public function getEmail(): int {
        return $this->assoc_array["email"];
    }
    
    public function getPerson(): int {
        return $this->assoc_array["person"];
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getID().". Email ".$this->getEmail()." an ".$this->getPerson().
            "</div>";
    }
}
?>