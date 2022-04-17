<?php
class Person {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getName(): string {
        return $this->assoc_array["Name"];
    }
    
    public function getEmail(): ?string {
        return $this->assoc_array["Email"];
    }
    
    public function getHauptmannschaft(): string {
        return $this->assoc_array["Hauptmannschaft"];
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getID().". <b>".$this->getName()."</b> (".$this->getEmail()."): ".$this->getHauptmannschaft().
            "</div>";
    }
}
?>