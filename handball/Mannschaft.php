<?php
require_once __DIR__."/MannschaftsMeldung.php";

const GESCHLECHT_M = "m";
const GESCHLECHT_W = "w";

class Mannschaft {
    public int $id;
    public int $nummer;
    public string $geschlecht;
    public ?string $jugendklasse;
    public ?string $email;

    public array $meldungen = array();
    
    public function getName(): string {
        if(!empty($this->jugendklasse)){
            return $this->geschlecht.$this->jugendklasse.$this->nummer;
        }
        if($this->geschlecht === GESCHLECHT_M){
            return "Herren ".$this->nummer;
        }
        if($this->geschlecht === GESCHLECHT_W){
            return "Damen ".$this->nummer;
        }
        
        return "Andersgeschlechtlich ".$this->getNummer();
    }

    public function getKurzname(): string {
        if(!empty($this->jugendklasse)){
            return $this->geschlecht.$this->jugendklasse.$this->nummer;
        }
        if($this->geschlecht === GESCHLECHT_W){
            return "D".$this->nummer;
        }
        return "H".$this->nummer;
    }
    
    function createNuLigaMannschaftsBezeichnung(): string{
        $bezeichnung = "";
        if(!empty($this->jugendklasse)){
            switch($this->geschlecht){
                case GESCHLECHT_W: $bezeichnung = "weibliche"; break;
                case GESCHLECHT_M: $bezeichnung = "männliche"; break;
            }
            $bezeichnung .= " Jugend ".strtoupper($this->jugendklasse);
        }else {
            switch($this->geschlecht){
                case GESCHLECHT_W: $bezeichnung = "Frauen"; break;
                case GESCHLECHT_M: $bezeichnung = "Männer"; break;
            }
        }
        if($this->nummer > 1){
            $bezeichnung .= " ";
            for($i=0; $i<$this->nummer; $i++){
                $bezeichnung .= "I";
            }
        }
        return $bezeichnung;
    }
    
    public function getMeldungenFuerMeisterschaft(Meisterschaft $meisterschaft): array{
        $meldungen = array();
        foreach ($this->meldungen as $meldung) {
            if($meldung->meisterschaft->id === $meisterschaft->id){
                $meldungen[] = $meldung;
            }
        }
        return $meldungen;
    }

}

?>