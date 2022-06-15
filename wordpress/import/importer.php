<?php
require_once __DIR__."/../dao/mannschaft.php";
require_once __DIR__."/../dao/gegner.php";
require_once __DIR__."/../dao/spiel.php";
require_once __DIR__."/../dao/dienst.php";

class SpielAenderung{
    public Spiel $alt;
    public NuLigaSpiel $neu;
    
    public function __construct(Spiel $alt, NuLigaSpiel $neu){
        $this->alt = $alt;
        $this->neu = $neu;
    }

    public function getBegegnungsbezeichnung(array $alleMannschaften, GegnerDAO $gegnerDAO): string{
        $message = "";
        $mannschaft = $alleMannschaften[$this->alt->getMannschaft()]->getName();
        $gegner = $gegnerDAO->fetch($this->alt->getGegner())->getName();
        
        if($this->alt->isHeimspiel()){
            return "HEIM $mannschaft vs. $gegner";
        } else{
            return "AUSWÄRTS $gegner vs. $mannschaft";
        }   
    }

    public function getAenderung(): string{
        $message = "";
        if($this->alt->getAnwurf() != $this->neu->getAnwurf()){

            $message .= "Anwurf "
                ."von [".$this->alt->getAnwurf()->format("d.m.Y H:i")."]"
                ." zu [".$this->neu->getAnwurf()->format("d.m.Y H:i")."]";
        }
        if($this->alt->getHalle() != $this->neu->getHalle()){
            $message .= " Halle "
                ."von [".$this->alt->getHalle()."]"
                ." zu [".$this->neu->getHalle()."]";
        }
        return $message;
    }
}

class DienstAenderungsPlan{
    private DienstDAO $dao;
    private array $mannschaften;
    private GegnerDAO $gegnerDAO;
    private $geanderteDienste = array();
    private $geanderteSpiele = array();

    public function __construct(array $mannschaften, GegnerDAO $gegnerDAO){
        $this->dao = new DienstDAO();
        $this->mannschaften = $mannschaften;
        $this->gegnerDAO = $gegnerDAO;
        
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
        $message = 
            "Hallo ".$mannschaft->getName().",\n"
            ."\n"
            ."es haben sich Spiele geändert, bei denen ihr Dienste übernehmt:\n"
            ."\n";
        
        $spieleUndDienste = $this->getGeanderteSpieleUndDienste($mannschaft);

        foreach($spieleUndDienste as $spielID => $dienstarten){
            $spielAenderung = $this->geanderteSpiele[$spielID];
            $message .= "  ".$spielAenderung->getBegegnungsbezeichnung($this->mannschaften, $this->gegnerDAO)."\n";
            $message .= "    ÄNDERUNG: ".$spielAenderung->getAenderung()."\n";
            $message .= "    EURE DIENSTE: ".implode(", ", $dienstarten)."\n";
        }

        $message .= "\n"
            ."Viele Grüße,\n"
            ."Euer Nippesbot";

        return $message;
    }

    private function getGeanderteSpieleUndDienste(Mannschaft $mannschaft): array{
        
        $spieleUndDienste = array();
        foreach($this->geanderteDienste[$mannschaft->getID()] as $dienst){
            $spielID = $dienst->getSpiel();
            if(empty($spieleUndDienste[$spielID])){
                $spieleUndDienste[$spielID] = array();
            }
            array_push($spieleUndDienste[$spielID], $dienst->getDienstart());
        }
        return $spieleUndDienste;
    }
}

function importSpieleFromNuliga(): string{
    require_once __DIR__."/../grabber/SpieleGrabber.php";
    
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

    $resultMessage .= "<br><b>Folgende Emails würden versendet werden:</b><br>".$dienstAenderungsPlan->simulateEmails();

    return $resultMessage;
}

?>