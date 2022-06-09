<?php
require_once __DIR__."/person.php";

const GESCHLECHT_M = "m";
const GESCHLECHT_W = "w";

class Mannschaft {
    private $assoc_array;

    private $spieler = array();

    public function __construct(array $assoc_array){
        $this->assoc_array = $assoc_array;
    }

    public function getID(): int {
        return $this->assoc_array["id"];
    }

    public function getName(): string {
        if($this->getGeschlecht() === GESCHLECHT_M){
            return "Herren ".$this->getNummer();
        }
        if($this->getGeschlecht() === GESCHLECHT_W){
            return "Damen ".$this->getNummer();
        }
        
        return "Andersgeschlechtlich ".$this->getNummer();
    }
    
    public function getNummer(): int {
        return $this->assoc_array["nummer"];
    }
    
    public function getGeschlecht(): string {
        return $this->assoc_array["geschlecht"];
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

    public function getSpieler(?int $id): ?Person {
        if(array_key_exists($id, $this->spieler)){
            return $this->spieler[$id];
        }
        return null;
    }

    public function addSpieler(Person $spieler){
        $this->spieler[$spieler->getID()] = $spieler;
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
}
?>