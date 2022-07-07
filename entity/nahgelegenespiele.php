<?php
require_once __DIR__."/Spiel.php";

class NahgelegeneSpiele {
    public ?Spiel $vorher = null;
    public ?Spiel $gleichzeitig = null;
    public ?Spiel $nachher = null;

    public function getVorherID(): ?string{
        return $this->getOptionalID($this->vorher);
    }
    public function getGleichzeitigID(): ?string{
        return $this->getOptionalID($this->gleichzeitig);
    }
    public function getNachherID(): ?string{
        return $this->getOptionalID($this->nachher);
    }

    private function getOptionalID(?Spiel $spiel): ?string{
        if(empty($spiel)) {
            return "null";
        }
        return $spiel->getID();
    }
}
?>