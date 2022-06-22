<?php
require_once __DIR__."/meisterschaft.php";

const GESCHLECHT_M = "m";
const GESCHLECHT_W = "w";

class Mannschaft {
    private $assoc_array;

    private $meisterschaften = array();

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getName(): string {
        if(!empty($this->getJugendklasse())){
            return $this->getGeschlecht().$this->getJugendklasse().$this->getNummer();
        }
        if($this->getGeschlecht() === GESCHLECHT_M){
            return "Herren ".$this->getNummer();
        }
        if($this->getGeschlecht() === GESCHLECHT_W){
            return "Damen ".$this->getNummer();
        }
        
        return "Andersgeschlechtlich ".$this->getNummer();
    }

    public function getKurzname(): string {
        if(!empty($this->getJugendklasse())){
            return $this->getGeschlecht().$this->getJugendklasse().$this->getNummer();
        }
        if($this->getGeschlecht() === GESCHLECHT_W){
            return "D".$this->getNummer();
        }
        return "H".$this->getNummer();
    }
    
    public function getNummer(): int {
        return $this->assoc_array["nummer"];
    }
    
    public function getGeschlecht(): string {
        return $this->assoc_array["geschlecht"];
    }
    public function getJugendklasse(): ?string {
        $jugendklasse = $this->assoc_array["jugendklasse"];
        if(trim($jugendklasse) == "") return null;
        return $jugendklasse;
    }
    public function getEmail(): ?string {
        return $this->assoc_array["email"];
    }
    
    public function getMeisterschaft(): string {
        return $this->assoc_array["meisterschaft"];
    }
    
    public function getLiga(): string {
        return $this->assoc_array["liga"];
    }
    
    public function getNuligaLigaID(): int {
        return $this->assoc_array["nuliga_liga_id"];
    }
    
    public function getNuligaTeamID(): int {
        return $this->assoc_array["nuliga_team_id"];
    }

    public function hasSpieler(): bool {
        return count($this->spieler) > 0;
    }

    public function getRandomSpielerID(): int {
        return array_rand($this->spieler);
    }

    public function addMeisterschaft(Meisterschaft $meisterschaft){
        $this->meisterschaften[$meisterschaft->getID()] = $meisterschaft;
    }

    public function getMeisterschaften(): array{
        return $this->meisterschaften;
    }
    
    public function getDebugOutput(): string {
        return 
            "<div>".
            $this->getID().". <b>".$this->getName()."</b>: ".$this->getLiga()."<br>".
            $this->getSpielerListeDebugOutput().
            "</div>";
    }

    private function getSpielerListeDebugOutput(): string {
        $spielerListe = "";
        foreach($this->spieler as $spieler) {
            $spielerListe .= "<li>".$spieler->getName()."</li>\n";
        }
        return "<ul>".$spielerListe."</ul>";
    }

    public static function getIDs(array $mannschaften): array {
        $ids = array();
        foreach($mannschaften as $mannschaft){
            $ids[] = $mannschaft->getID();
        }
        return $ids;
    }
    
}
?>