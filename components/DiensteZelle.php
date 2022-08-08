<?php

require_once __DIR__."/Component.php";
require_once __DIR__."/DienstCheckBox.php";

require_once __DIR__."/../handball/Mannschaft.php";
require_once __DIR__."/../handball/Spiel.php";
require_once __DIR__."/../handball/NahgelegeneSpiele.php";

class DiensteZelle implements Component{
    
    private Spiel $spiel;
    private Mannschaft $mannschaft;
    private NahgelegeneSpiele $nahgelegeneSpiele;
    
    public function __construct(Spiel $spiel, Mannschaft $mannschaft, NahgelegeneSpiele $nahgelegeneSpiele){
        $this->spiel = $spiel;
        $this->mannschaft = $mannschaft;
        $this->nahgelegeneSpiele = $nahgelegeneSpiele;
    }

    public function toHTML(): string{
        $backgroundColor = "inherit";
        $highlightColorVorher = "#bbf";
        $highlightColorNachher = "#bbf";
        $textColor = "black";
        $tooltip = "";
        if($this->spiel->mannschaft->id == $this->mannschaft->id){
            // TODO Warnung wegen eigenem Spiel bei Anklicken
            $textColor = "silver";
            $tooltip = "Eigenes Spiel";
        } else if(isset($this->nahgelegeneSpiele->gleichzeitig)) {
            // TODO Warnung wegen gleichzeitigem Spiel
            $textColor = "silver";
            $tooltip = "Gleichzeitiges Spiel";
        } else {
            $hatSpielAmGleichenTag = false;
            $hatSpielinGleicherHalle = false;
            
            if(isset($this->nahgelegeneSpiele->vorher)){
                if($this->spiel->isAmGleichenTag($this->nahgelegeneSpiele->vorher)){
                    $highlightColorVorher = "#ffd";
                    $hatSpielAmGleichenTag = true;
                    if($this->spiel->halle == $this->nahgelegeneSpiele->vorher->halle){
                        $highlightColorVorher = "#dfd";
                        $hatSpielinGleicherHalle = true;
                    }
                }    
            }
            
            if(isset($this->nahgelegeneSpiele->nachher)){
                if($this->spiel->isAmGleichenTag($this->nahgelegeneSpiele->nachher)){
                    $highlightColorNachher = "#ffd";
                    $hatSpielAmGleichenTag = true;
                    if($this->spiel->halle == $this->nahgelegeneSpiele->nachher->halle){
                        $highlightColorNachher = "#dfd";
                        $hatSpielinGleicherHalle = true;
                    }
                }    
            }

            if($hatSpielAmGleichenTag){
                $tooltip = "Spiel am gleichen Tag";
                $backgroundColor = "#ffd";
                if($hatSpielinGleicherHalle){
                    $tooltip .= "\nSpiel in gleicher Halle";
                    $backgroundColor = "#dfd";
                }
            }
        }

        $cellContent = "";
        foreach(Dienstart::values as $dienstart){
            $dienst = $this->spiel->getDienst($dienstart);
            if(isset($dienst)){
                $checkBox = new DienstCheckBox($dienst, $this->mannschaft);
                $cellContent .= $checkBox->toHTML()."<br>";
            }
        }
        
        return "<td "
            ."mannschaft=\"".$this->mannschaft->id."\""
            ."style=\"background-color:$backgroundColor; color:$textColor; text-align:center\" "
            ."title=\"$tooltip\" "
            ."onmouseover=\"highlightGames("
                .$this->nahgelegeneSpiele->getVorherID().", '$highlightColorVorher', "
                .$this->nahgelegeneSpiele->getGleichzeitigID().", "
                .$this->nahgelegeneSpiele->getNachherID().", '$highlightColorNachher')\" "
            ."onmouseout=\"resetHighlight("
                .$this->nahgelegeneSpiele->getVorherID().","
                .$this->nahgelegeneSpiele->getGleichzeitigID().", "
                .$this->nahgelegeneSpiele->getNachherID().")\" "
            .">$cellContent</td>";
    }
}

?>