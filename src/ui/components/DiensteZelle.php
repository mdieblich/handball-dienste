<?php

require_once __DIR__."/Component.php";
require_once __DIR__."/DienstCheckBox.php";

require_once __DIR__."/../../handball/Mannschaft.php";
require_once __DIR__."/../../handball/Spiel.php";
require_once __DIR__."/../../handball/NahgelegeneSpiele.php";

class DiensteZelle implements Component{

    const FARBE_NEUTRAL = "#bbf";
    const FARBE_GLEICHER_TAG = "#ffd";
    const FARBE_GLEICHER_TAG_UND_HALLE = "#dfd";
    const FARBE_VERHINDERT = "#fdd";
    
    private Spiel $spiel;
    private Mannschaft $mannschaft;
    private NahgelegeneSpiele $nahgelegeneSpiele;
    
    public function __construct(Spiel $spiel, Mannschaft $mannschaft, NahgelegeneSpiele $nahgelegeneSpiele){
        $this->spiel = $spiel;
        $this->mannschaft = $mannschaft;
        $this->nahgelegeneSpiele = $nahgelegeneSpiele;
    }

    public function toHTML(): string{
        $style = $this->getStyle();
        $highlightColorVorher = $this->getHighlightColor($this->nahgelegeneSpiele->vorher);
        $highlightColorNachher = $this->getHighlightColor($this->nahgelegeneSpiele->nachher);
        $tooltip = $this->getToolTip();
        $cellContent = $this->getCellContent();
        
        return "<td "
            ."mannschaft=\"".$this->mannschaft->id."\""
            ."style=\"$style\" "
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

    private function getStyle(): string{
        $backgroundColor = $this->getBackgroundColor();
        $textColor = $this->getTextColor();
        return 
            "background-color:$backgroundColor; ".
            "color:$textColor; ".
            "text-align:center";
    }

    private function getBackgroundColor(): string {
        if($this->isVerhindert()){
            return self::FARBE_VERHINDERT;
        }
        if($this->hatSpielAmGleichenTag()){
            if($this->hatSpielInGleicherHalle()){
                return self::FARBE_GLEICHER_TAG_UND_HALLE;
            }
            return self::FARBE_GLEICHER_TAG;
        }
        return "inherit";
    }

    private function isVerhindert(): bool {
        return $this->isEigenesSpiel() || $this->hatGleichZeitigesSpiel();
    }

    private function isEigenesSpiel(): bool {
        return $this->spiel->mannschaft->id == $this->mannschaft->id;
    }

    private function hatGleichZeitigesSpiel(): bool {
        return isset($this->nahgelegeneSpiele->gleichzeitig);
    }
    private function getTextColor(): string {
        if($this->isVerhindert()){
            return "silver";
        }
        return "black";
    }
    
    private function getHighlightColor(?Spiel $anderesSpiel): string {
        if(!isset($anderesSpiel)){
            return self::FARBE_NEUTRAL;
        }

        if($this->isVerhindert()){
            return self::FARBE_NEUTRAL;
        } 
        
        if($this->spiel->isAmGleichenTag($anderesSpiel)){
            if($this->spiel->halle == $anderesSpiel->halle){
                return self::FARBE_GLEICHER_TAG_UND_HALLE;
            }
            return self::FARBE_GLEICHER_TAG;
        }
        
        return self::FARBE_NEUTRAL;
    }

    private function getToolTip(): string {
        if($this->isEigenesSpiel()){
            return "Eigenes Spiel";
        } 
        if($this->hatGleichZeitigesSpiel()) {
            return "Gleichzeitiges Spiel";
        } 
        if($this->hatSpielAmGleichenTag()){
            if($this->hatSpielInGleicherHalle()){
                return "Spiel am gleichen Tag\nSpiel in gleicher Halle";
            }
            return "Spiel am gleichen Tag";
        }
        return "";
    }

    private function hatSpielAmGleichenTag(): bool {
        return
            $this->spiel->isAmGleichenTag($this->nahgelegeneSpiele->vorher) ||
            $this->spiel->isAmGleichenTag($this->nahgelegeneSpiele->nachher);
    }
        
    private function hatSpielInGleicherHalle(): bool {
        return
            $this->spiel->isInGleicherHalle($this->nahgelegeneSpiele->vorher) ||
            $this->spiel->isInGleicherHalle($this->nahgelegeneSpiele->nachher);
    }

    private function getCellContent(): string {
        $cellContent = "";
        foreach(Dienstart::values as $dienstart){
            $dienst = $this->spiel->getDienst($dienstart);
            if(isset($dienst)){
                $checkBox = new DienstCheckBox($dienst, $this->mannschaft);
                $cellContent .= $checkBox->toHTML()."<br>";
            }
        }
        return $cellContent;
    }
}

?>