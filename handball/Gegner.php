<?php

class Gegner {
    public int $id;
    public string $verein;
    public int $nummer;
    public string $geschlecht;
    public string $liga;
    public bool $stelltSekretaerBeiHeimspiel;

    public function getName(): string {
        if($this->nummer === 1){
            return $this->verein;
        }else{
            switch($this->nummer){
                case 1:  return $this->verein." I";
                case 2:  return $this->verein." II";
                case 3:  return $this->verein." III";
                case 4:  return $this->verein." IV";
                case 5:  return $this->verein." V";
                default: return $this->verein." ".$this->getNummer();
            }
        }
    }
}

?>