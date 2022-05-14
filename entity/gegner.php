<?php
class Gegner {
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
    
    public function getLiga(): string {
        return $this->assoc_array["liga"];
    }
    
    public function stelltSekretearBeiHeimspiel(): bool {
        return $this->assoc_array["steltSekretaerBeiHeimspiel"];
    }
}
?>