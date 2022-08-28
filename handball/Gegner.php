<?php

require_once __DIR__."/MannschaftsMeldung.php";

class Gegner {
    public int $id;
    public string $verein;
    public int $nummer;

    public MannschaftsMeldung $zugehoerigeMeldung;
    public bool $stelltSekretaerBeiHeimspiel = false;

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

    public function getGeschlecht(): string{
        return $this->zugehoerigeMeldung->mannschaft->geschlecht;
    }
    
    public function getLiga(): string{
        return $this->zugehoerigeMeldung->liga;
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