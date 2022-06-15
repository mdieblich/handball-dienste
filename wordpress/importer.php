<?php
require_once __DIR__."/dao/mannschaft.php";
require_once __DIR__."/dao/gegner.php";
require_once __DIR__."/dao/spiel.php";

function importSpieleFromNuliga(): string{
    require_once __DIR__."/grabber/SpieleGrabber.php";
    
    $mannschaften = loadMannschaften();
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();

    $resultMessage = "";
    foreach($mannschaften as $mannschaft){
        $spieleImportiert   = 0;
        $spieleAktualisiert = 0;

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
                $gegnerName = $spiel->getGastmannschaft();
            } else {
                $isHeimspiel = 0;
                $gegnerName = $spiel->getHeimmannschaft();
            }
            $gegner_id = $gegnerDAO->findOrInsertGegner( 
                $gegnerName, 
                $mannschaft->getGeschlecht(), 
                $mannschaft->getLiga()
            )->getID();
            $spielID = findSpielID ($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel);
            if(isset($spielID)){
                updateSpiel($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $spiel->getHalle(), $spiel->getAnwurf());
                // Hier mit wp_mail 
                $spieleAktualisiert ++;
            } else {
                insertSpiel($spiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $spiel->getHalle(), $spiel->getAnwurf());
                $spieleImportiert ++;
            }
        }
        $resultMessage .= "<b>".$mannschaft->getName()."</b>: $spieleImportiert Spiele importiert, $spieleAktualisiert aktualisiert<br>\n";
    }
    
    return $resultMessage;
}

?>