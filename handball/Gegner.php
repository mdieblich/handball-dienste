<?php

require_once __DIR__."/MannschaftsMeldung.php";

class Gegner {
    public int $id;
    public string $verein;
    public int $nummer;

    public MannschaftsMeldung $zugehoerigeMeldung;
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
                default: return $this->verein." ".$this->nummer;
            }
        }
    }

    public function getDisplayName(): string {
        if($this->stelltSekretaerBeiHeimspiel){
            return $this->getName(). "<sup>*</sup>";
        }
        return $this->getName();
    }

    public function getGeschlecht(): string{
        return $this->zugehoerigeMeldung->mannschaft->geschlecht;
    }
    
    public function getJugendklasse(): ?string{
        return $this->zugehoerigeMeldung->mannschaft->jugendklasse;
    }
    
    public function getLiga(): string{
        return $this->zugehoerigeMeldung->liga;
    }

    public function isSimilarTo(Gegner $other): bool {
        if(empty($other)){
            return false;
        }
        if( $this->verein !== $other->verein ||  $this->nummer !== $other->nummer ){
            return false;
        }
        $meldung_id = isset($this->zugehoerigeMeldung) ? $this->zugehoerigeMeldung->id : $this->zugehoerigeMeldung_id;
        $meldung_id_other = isset($other->zugehoerigeMeldung) ? $other->zugehoerigeMeldung->id : $other->zugehoerigeMeldung_id;
        return ($meldung_id === $meldung_id_other);
    }

    public static function fromName(string $name): Gegner {

        $gegner = new Gegner();
        $gegner->verein = $name;
        $gegner->nummer = 1;

        if(endsWith($name, " V")){
            $gegner->verein = substr($name, 0, strlen($name)-2);
            $gegner->nummer = 5;
        } else if(endsWith($name, " IV")){
            $gegner->verein = substr($name, 0, strlen($name)-3);
            $gegner->nummer = 4;
        } else if(endsWith($name, " III")){
            $gegner->verein = substr($name, 0, strlen($name)-4);
            $gegner->nummer = 3;
        } else if(endsWith($name, " II")){
            $gegner->verein = substr($name, 0, strlen($name)-3);
            $gegner->nummer = 2;
        } else if(endsWith($name, " I")){
            $gegner->verein = substr($name, 0, strlen($name)-2);
            $gegner->nummer = 1;
        }
        
        $gegner->verein = trim($gegner->verein);
        return $gegner;
    }

}

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}

?>