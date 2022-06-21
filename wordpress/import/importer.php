<?php
require_once __DIR__."/SpieleGrabber.php";
require_once __DIR__."/DienstAenderungsPlan.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../dao/mannschaft.php";
require_once __DIR__."/../dao/gegner.php";
require_once __DIR__."/../dao/spiel.php";
require_once __DIR__."/../dao/dienst.php";

require_once __DIR__."/../PHPMailer/NippesMailer.php";

class ImportErgebnis{
    public int $spiele = 0;
    public int $neu = 0;
    public int $aktualisiert = 0;

    public function toReadableString(): string{
        return $this->spiele." Spiele geprüft, davon ".$this->neu." neu importiert und ".$this->aktualisiert." aktualisiert";
    }
}

function importSpieleFromNuliga(): array{
    
    $mannschaften = loadMannschaftenMitMeisterschaften();
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();

    $dienstAenderungsPlan = new DienstAenderungsPlan($mannschaften, $gegnerDAO);

    $ergebnis = array();
    foreach($mannschaften as $mannschaft){
        $importErgebnis = new ImportErgebnis();
        
        $teamName = get_option('vereinsname');
        if($mannschaft->getNummer() >= 2){
            $teamName .= " ";
            for($i=0; $i<$mannschaft->getNummer(); $i++){
                $teamName .= "I";
            }
        }
        
        foreach($mannschaft->getMeisterschaften() as $meisterschaft) {
            $spielGrabber = new SpieleGrabber(
                $meisterschaft->getKuerzel(), 
                $meisterschaft->getNuligaLigaID(), 
                $meisterschaft->getNuligaTeamID()
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
                    $meisterschaft->getLiga()
                )->getID();
                $spiel = findSpiel ($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel);
                $importErgebnis->spiele ++;
                if(isset($spiel)){
                    $hallenAenderung = ($spiel->getHalle() != $nuLigaSpiel->getHalle());
                    $AnwurfAenderung = ($spiel->getAnwurf() != $nuLigaSpiel->getAnwurf());
                    if($hallenAenderung || $AnwurfAenderung){
                        $dienstAenderungsPlan->registerSpielAenderung($spiel, $nuLigaSpiel);
                        updateSpiel($spiel->getID(), $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                        $importErgebnis->aktualisiert ++;
                    }
                } else {
                    insertSpiel($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                    $importErgebnis->neu ++;
                }
            }
        }
        $ergebnis[$mannschaft->getName()]  = $importErgebnis;
    }

    $dienstAenderungsPlan->sendEmails();

    return $ergebnis;
}

?>