<?php
require_once __DIR__."/dao/mannschaft.php";
require_once __DIR__."/dao/gegner.php";
require_once __DIR__."/dao/spiel.php";
require_once __DIR__."/dao/dienst.php";

class SpielAenderung{
    public Spiel $alt;
    public NuLigaSpiel $neu;
    
    public function __construct(Spiel $alt, NuLigaSpiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }
}

class DienstAenderungsPlan{
    private DienstDAO $dao;
    private array $mannschaften;
    private $geanderteDienste = array();
    private $geanderteSpiele = array();

    public function __construct(array $mannschaften){
        $this->dao = new DienstDAO();
        $this->mannschaften = $mannschaften;
        
        foreach($mannschaften as $mannschaft){
            $this->geanderteDienste[$mannschaft->getID()] = array();
        }
    }

    public function registerSpielAenderung(Spiel $alt, NuLigaSpiel $neu){ 
        $this->geanderteSpiele[$alt->getID()] = new SpielAenderung($alt, $neu);
        $bisherigeDienste = $this->dao->loadAllDienste("spiel=".$alt->getID());
        foreach($bisherigeDienste as $dienst){
            array_push($this->geanderteDienste[$dienst->getMannschaft()], $dienst);
        }
    }

    public function simulateEmails(): string{
        $messages = array();
        foreach($this->mannschaften as $mannschaft){
            if($this->hatKeineGeandertenDienste($mannschaft)){
                continue;
            }
            $messages[$mannschaft->getID()] = $this->createMessageForMannschaft($mannschaft);
        }
        return "<pre>".implode("\n\n--------------\n\n", $messages)."</pre>";
    }

    private function hatKeineGeandertenDienste(Mannschaft $mannschaft): bool{
        return count($this->geanderteDienste[$mannschaft->getID()]) == 0;
    }

    private function createMessageForMannschaft(Mannschaft $mannschaft): string{
        $simulatedMessage = 
            "Hallo ".$mannschaft->getName()."\n"
            ."\n"
            ."Es haben sich Spiele geändert, bei denen ihr Dienste übernehmt:\n";
        
        return $simulatedMessage;
    }
}

function importSpieleFromNuliga(): string{
    require_once __DIR__."/grabber/SpieleGrabber.php";
    
    $mannschaften = loadMannschaften();
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();

    $dienstAenderungsPlan = new DienstAenderungsPlan($mannschaften);

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
            $spiel = findSpiel ($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel);
            if(isset($spiel)){
                $dienstAenderungsPlan->registerSpielAenderung($spiel, $nuLigaSpiel);
                updateSpiel($spiel->getID(), $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                // Hier mit wp_mail 
                $spieleAktualisiert ++;
            } else {
                insertSpiel($nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                $spieleImportiert ++;
            }
        }
        $resultMessage .= "<b>".$mannschaft->getName()."</b>: $spieleImportiert Spiele importiert, $spieleAktualisiert aktualisiert<br>\n";
    }

    $resultMessage .= "<br><b>Folgende Emails würden versendet werden:</b><br>".$dienstAenderungsPlan->simulateEmails();

    return $resultMessage;
}

?>