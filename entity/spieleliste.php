<?php
require_once __DIR__."/../handball/Mannschaft.php";
require_once __DIR__."/Spiel.php";
require_once __DIR__."/Dienst.php";
require_once __DIR__."/Nahgelegenespiele.php";

class SpieleListe {
    private array $spiele;

    public function __construct(array $spiele){
        $this->spiele = $spiele;
    }

    public function getSpiele(): array{
        return $this->spiele;
    }

    public function zaehleDienste(Mannschaft $mannschaft): array{
        $anzahl = array();
        foreach(Dienstart::values as $dienstart){
            $anzahl[$dienstart] = 0;
        }
        foreach($this->spiele as $spiel){
            foreach($spiel->getDienste() as $dienst){
                if($dienst->getMannschaft() == $mannschaft->id){
                    $anzahl[$dienst->getDienstart()]++;
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
            if($spiel->getMannschaft() != $mannschaft->id){
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

}
?>