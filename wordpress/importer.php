<?php
require_once __DIR__."/dao/mannschaft.php";
require_once __DIR__."/dao/gegner.php";
require_once __DIR__."/dao/spiel.php";

function findOrInsertGegner(array $alleGegner, string $name, $liga): Gegner{
    
    foreach($alleGegner as $gegner){
        if($gegner->getName() === $name){
            return $gegner;
        }
    }
    // Nix gefunden - einfügen!
    $gegner = insertGegner($name, $liga);
    $alleGegner[$gegner->getID()] = $gegner;
    return $gegner;
}

function importSpieleFromNuliga(){
    require_once __DIR__."/grabber/SpieleGrabber.php";
    
    $mannschaften = loadMannschaften();
    $alleGegner = loadGegner();

    foreach($mannschaften as $mannschaft){
        $teamName = get_option('vereinsname');
        if($mannschaft->getNummer() >= 2){
            $teamName .= " ";
            for($i=0; $i<$mannschaft->getNummer(); $i++){
                $teamName .= "I";
            }
        }
        $spielGrabber = new SpieleGrabber(
            $mannschaft->getMeisterschaft(), 
            $mannschaft->getNuligaLigaID(), 
            $mannschaft->getNuligaTeamID()
        );
        foreach($spielGrabber->getSpiele() as $spiel){
            if($spiel->getHeimmannschaft() === $teamName){
                $isHeimspiel = true;
                $gegner_id = findOrInsertGegner($alleGegner, $spiel->getGastmannschaft(), $mannschaft->getLiga())->getID();
            } else {
                $isHeimspiel = false;
                $gegner_id = findOrInsertGegner($alleGegner, $spiel->getHeimmannschaft(), $mannschaft->getLiga())->getID();
            }
            insertSpiel($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $spiel->getHalle(), $spiel->getAnwurf());
        }
    }
}

?>