<?php
class Dienst {
    private $assoc_array;

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getSpiel(): int {
        return $this->assoc_array["spiel"];
    }
    
    public function getDienstart(): string {
        return $this->assoc_array["dienstart"];
    }
    
    public function getMannschaft(): string {
        return $this->assoc_array["mannschaft"];
    }

    public function getPerson(): ?string {
        return $this->assoc_array["person"];
    }

    public function getDebugOutput(): string {
        return 
            "<div>".
            "<b>".$this->getSpiel().". </b>".$this->getDienstart().": ".
            "Mannschaft ".$this->getMannschaft().", Person ".$this->getPerson()."".
            "</div>";
    }
}
?>