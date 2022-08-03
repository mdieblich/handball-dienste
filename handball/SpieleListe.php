<?php

require_once __DIR__."/Spiel.php";
require_once __DIR__."/Dienstart.php";
require_once __DIR__."/Dienst.php";
require_once __DIR__."/NahgelegeneSpiele.php";

class SpieleListe{

    public array $spiele;

    public function __construct(array $spiele = array()){
        $this->spiele = $spiele;
    }
    
    // TODO ersetzen durch getDiensteProMannschaft
    public function zaehleDienste(Mannschaft $mannschaft): int{
        $anzahl = 0;
        foreach($this->spiele as $spiel){
            foreach($spiel->dienste as $dienst){
                if(isset($dienst->mannschaft) && $dienst->mannschaft->id == $mannschaft->id){
                    $anzahl++;
                }
            }
        }
        return $anzahl;
    }
    
    function findNahgelegeneSpiele(Spiel $zuPruefendesSpiel, Mannschaft $mannschaft): NahgelegeneSpiele {

        $nahgelegeneSpiele = new NahgelegeneSpiele();
        $distanzVorher = null;
        $distanzNachher = null;
        foreach($this->spiele as $spiel){
            // TODO das geht definitiv einfacher: Alle Spiele als Array in Mannschaft
            if($spiel->mannschaft->id != $mannschaft->id){
                continue;
            }
            $zeitlicheDistanz = $spiel->getZeitlicheDistanz($zuPruefendesSpiel);
            if(empty($zeitlicheDistanz)){
                continue;
            }
            if($zeitlicheDistanz->ueberlappend){
                $nahgelegeneSpiele->gleichzeitig = $spiel;
            } else {
                if($zeitlicheDistanz->isVorher()){
                    if($zeitlicheDistanz->isNaeher($distanzVorher)){
                        $distanzVorher = $zeitlicheDistanz;
                        $nahgelegeneSpiele->vorher = $spiel;
                    }
                } else {
                    if($zeitlicheDistanz->isNaeher($distanzNachher)){
                        $distanzNachher = $zeitlicheDistanz;
                        $nahgelegeneSpiele->nachher = $spiel;
                    }
                }
            }
        }
        return $nahgelegeneSpiele;
    }
    
    public function getIDs(): array {
        $ids = array();
        foreach($this->spiele as $spiel){
            $ids[] = $spiel->id;
        }
        return $ids;
    }

    public function hasEntries(): bool {
        return count($this->spiele) > 0;
    }

    public function getDienste(string $dienstart): array{
        $dienste = array();
        foreach($this->spiele as $spiel){
            $dienst = $spiel->getDienst($dienstart);
            if(isset($dienst)){
                $dienste[$dienst->id] = $dienst;
            }
        }
        return $dienste;
    }

    public function getMannschaften(): array {
        $mannschaften = array();
        foreach($this->spiele as $spiel){
            $mannschaft = $spiel->mannschaft;
            $mannschaften[$mannschaft->id] = $mannschaft;
            foreach($spiel->dienste as $dienst){
                if (empty($dienst->mannschaft)){
                    continue;
                }
                $mannschaft = $dienst->mannschaft;
                $mannschaften[$mannschaft->id] = $mannschaft;
            }
        }

        return $mannschaften;
    }

    public function groupBySpielTag(): array{
        $spieleProTag = array();
        foreach($this->spiele as $spiel){
            $spieltag = $spiel->anwurf->format("d.m.Y");
            if(!array_key_exists($spieltag, $spieleProTag)){
                $spieleProTag[$spieltag] = new SpieleListe();
            }
            $spieleProTag[$spieltag]->spiele = $spiel;
        }

        return $spieleProTag;
    }

}

?>