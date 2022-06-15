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
        foreach($spielGrabber->getNuLigaSpiele() as $nuLigaSpiel){
            if($nuLigaSpiel->getHeimmannschaft() === $teamName){
                $isHeimspiel = 1;
                $gegnerName = $nuLigaSpiel->getGastmannschaft();
            } else {
                $isHeimspiel = 0;
                $gegnerName = $nuLigaSpiel->getHeimmannschaft();
            }
            $gegner_id = $gegnerDAO->findOrInsertGegner( 
                $gegnerName, 
                $mannschaft->getGeschlecht(), 
                $mannschaft->getLiga()
            )->getID();
            $spielID = findSpielID ($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel);
            if(isset($spielID)){
                updateSpiel($spielID, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                // Hier mit wp_mail 
                $spieleAktualisiert ++;
            } else {
                insertSpiel($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                $spieleImportiert ++;
            }
        }
        $resultMessage .= "<b>".$mannschaft->getName()."</b>: $spieleImportiert Spiele importiert, $spieleAktualisiert aktualisiert<br>\n";
    }
    
    return $resultMessage;
}

?>