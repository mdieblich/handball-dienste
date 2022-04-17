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
        return $this->assoc_array["name"];
    }
    
    public function getEmail(): ?string {
        return $this->assoc_array["email"];
    }
    
    public function getHauptmannschaft(): string {
        return $this->assoc_array["hauptmannschaft"];
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getID().". <b>".$this->getName()."</b> (".$this->getEmail()."): ".$this->getHauptmannschaft().
            "</div>";
    }
}
?>