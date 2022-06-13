<?php
require_once __DIR__."/spiel.php";
require_once __DIR__."/nahgelegenespiele.php";
require_once __DIR__."/dienst.php";

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
                if($dienst->getMannschaft() == $mannschaft->getID()){
                    $anzahl[$dienst->getDienstart()]++;
                }
            }
        }
        return $anzahl;
    }
}
?>