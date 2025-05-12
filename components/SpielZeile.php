<?php

require_once __DIR__."/Component.php";
require_once __DIR__."/DiensteZelle.php";

require_once __DIR__."/../handball/Spiel.php";
require_once __DIR__."/../handball/SpieleListe.php";

class SpielZeile implements Component{
    
    private Spiel $spiel;
    private string $backgroundColor;
    private array $nahgelegeneSpieleProMannschaft;
    private MannschaftsListe $mannschaftsListe;
    
    public function __construct(Spiel $spiel, string $backgroundColor, array $nahgelegeneSpieleProMannschaft, MannschaftsListe $mannschaftsListe){
        $this->spiel = $spiel;
        if(isset($spiel->anwurf)){
            $this->backgroundColor = $backgroundColor;
        } else {
            $this->backgroundColor = "#fff";
        }
        $this->nahgelegeneSpieleProMannschaft = $nahgelegeneSpieleProMannschaft;
        $this->mannschaftsListe = $mannschaftsListe;
    }

    
    public function toHTML(): string{
        $mannschaft = $this->spiel->mannschaft->id;
        return "<tr "
            ."style=\"background-color:$this->backgroundColor\" "
            ."mannschaft=\"$mannschaft\">"
            .$this->getZellen()
            ."</tr>";
    }

    private function getZellen(): string {
        $zelleSpielNr = "<td>".$this->spiel->spielNr."</td>";
        $zelleAnwurf = $this->getZelleAnwurf();
        $zelleHalle = "<td id=\"spiel-".$this->spiel->id."-halle\">".$this->spiel->halle."</td>";

        $zelleHeim = $this->getZelleHeim();
        $zelleGast = $this->getZelleGast();
        $dienstZellen = $this->getDienstZellen();

        return $zelleSpielNr
            .$zelleAnwurf
            .$zelleHalle
            .$zelleHeim
            .$zelleGast
            .$dienstZellen;
    }
    
    private function getZelleAnwurf(): string {
        $anwurfText = $this->getAnwurfText();
        return "<td id=\"spiel-".$this->spiel->id."-anwurf\">$anwurfText</td>";
    }
    
    private function getAnwurfText(): string {
        $anwurf = $this->spiel->anwurf;
        if(!isset($anwurf)){
            return "Termin offen";
        }
        
        $uhrzeit = $anwurf->format("H:i");
        $color = ($uhrzeit !== "00:00") ? "black" : "red";
        return $anwurf->format("d.m.Y ")."<span style='color:$color'>$uhrzeit</span>";
    }
    
    private function getZelleHeim(): string{
        if($this->spiel->heimspiel){
            return $this->getZelleMannschaft();
        }
        return $this->getZelleGegner();
    }
    
    private function getZelleGast(): string{
        if($this->spiel->heimspiel){
            return $this->getZelleGegner();
        }
        return $this->getZelleMannschaft();
    }
    private function getZelleMannschaft(): string{
        $id = "spiel-".$this->spiel->id."-mannschaft";
        $cellContent = $this->spiel->mannschaft->getName();
        return "<td "
            ."id=\"$id\""
            .">$cellContent</td>";
    }
    
    private function getZelleGegner(): string{
        $gegner = $this->spiel->gegner;
        
        $id = "spiel-".$this->spiel->id."-gegner";
        $title = $gegner->stelltSekretaerBeiHeimspiel ? "Stellt Sekretär in deren Halle" : "";
        $cellContent = $gegner->getDisplayName();
        return "<td "
            ."id=\"$id\" "
            ."title='$title'"
            .">$cellContent</td>";
    }

    private function getDienstZellen(): string {
        $dienstZellen = "";
        foreach($this->mannschaftsListe->mannschaften as $mannschafts_id => $mannschaft){
            if(array_key_exists($mannschafts_id, $this->nahgelegeneSpieleProMannschaft)){
                $nahgelegeneSpiele = $this->nahgelegeneSpieleProMannschaft[$mannschafts_id];
            } else {
                // Keine nahgelegenen Spiele? Dann leeres Objekt verwenden.
                $nahgelegeneSpiele = new NahgelegeneSpiele($this->spiel);
            }
            $diensteZelle = new DiensteZelle($this->spiel, $mannschaft, $nahgelegeneSpiele);
            $dienstZellen .= $diensteZelle->toHTML();
        }
        return $dienstZellen;
    }
}
?>