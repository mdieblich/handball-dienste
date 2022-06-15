<?php
require_once __DIR__."/SpieleGrabber.php";
require_once __DIR__."/DienstAenderungsPlan.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../dao/mannschaft.php";
require_once __DIR__."/../dao/gegner.php";
require_once __DIR__."/../dao/spiel.php";
require_once __DIR__."/../dao/dienst.php";

require_once __DIR__."/../PHPMailer/NippesMailer.php";

function importSpieleFromNuliga(): string{
    
    $mannschaften = loadMannschaften();
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();

    $dienstAenderungsPlan = new DienstAenderungsPlan($mannschaften, $gegnerDAO);

    $resultMessage = "";
    foreach($mannschaften as $mannschaft){
        $spieleGeprueft     = 0;
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
            $spiel = findSpiel ($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel);
            $spieleGeprueft ++;
            if(isset($spiel)){
                $hallenAenderung = ($spiel->getHalle() != $nuLigaSpiel->getHalle());
                $AnwurfAenderung = ($spiel->getAnwurf() != $nuLigaSpiel->getAnwurf());
                if($hallenAenderung || $AnwurfAenderung){
                    $dienstAenderungsPlan->registerSpielAenderung($spiel, $nuLigaSpiel);
                    updateSpiel($spiel->getID(), $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                    $spieleAktualisiert ++;
                }
            } else {
                insertSpiel($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                $spieleImportiert ++;
            }
        }
        $resultMessage .= "<b>".$mannschaft->getName()."</b>: $spieleGeprueft Spiele geprüft, davon $spieleImportiert neu importiert und $spieleAktualisiert aktualisiert<br>\n";
    }

    $dienstAenderungsPlan->sendEmails();
    //$resultMessage .= "<br><b>Folgende Emails würden versendet werden:</b><br>".$dienstAenderungsPlan->simulateEmails();

    return $resultMessage;
}

?>