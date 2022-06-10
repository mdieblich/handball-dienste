<?php
require_once __DIR__."/dao/mannschaft.php";
require_once __DIR__."/dao/gegner.php";
require_once __DIR__."/dao/spiel.php";

function findOrInsertGegner(array $alleGegner, string $name, string $geschlecht, string $liga): Gegner{
    
    foreach($alleGegner as $gegner){
        if($gegner->getName() === $name && $gegner->getGeschlecht() === $geschlecht){
            return $gegner;
        }
    }
    // Nix gefunden - einfügen!
    $gegner = insertGegner($name, $geschlecht, $liga);
    $alleGegner[$gegner->getID()] = $gegner;
    return $gegner;
}

function importSpieleFromNuliga(): string{
    require_once __DIR__."/grabber/SpieleGrabber.php";
    
    $mannschaften = loadMannschaften();
    $alleGegner = loadGegner();

    $spieleNeu = 0;
    $spieleAktualisiert = 0;

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
                $isHeimspiel = 1;
                $gegner_id = findOrInsertGegner($alleGegner, 
                    $spiel->getGastmannschaft(), 
                    $mannschaft->getGeschlecht(), 
                    $mannschaft->getLiga()
                )->getID();
            } else {
                $isHeimspiel = 0;
                $gegner_id = findOrInsertGegner($alleGegner, 
                    $spiel->getHeimmannschaft(), 
                    $mannschaft->getGeschlecht(), 
                    $mannschaft->getLiga()
                )->getID();
            }
            if(spielExistiert($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel)){
                updateSpiel($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $spiel->getHalle(), $spiel->getAnwurf());
                $spieleAktualisiert ++;
            } else {
                insertSpiel($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $spiel->getHalle(), $spiel->getAnwurf());
                $spieleNeu ++;
            }
        }
    }
    return "importiert: $spieleNeu<br>aktualisiert: $spieleAktualisiert";
}

?>