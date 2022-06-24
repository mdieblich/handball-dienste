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
    public int $gesamt = 0;
    public int $neu = 0;
    public int $aktualisiert = 0;

    public function toReadableString(): string{
        return $this->gesamt." geprüft, davon ".$this->neu." neu importiert und ".$this->aktualisiert." aktualisiert";
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
                $importErgebnis->gesamt ++;
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

function importMeisterschaftenFromNuliga(): array{
    require_once __DIR__."/../dao/meisterschaft.php";
    require_once __DIR__."/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";
    
    $mannschaften = loadMannschaften();
    $nuligaBezeichnungen = createNuLigaMannschaftsBezeichnungen($mannschaften);
    
    $ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(get_option('nuliga-clubid'));
    $nuliga_meisterschaften = $ligeneinteilung->getMeisterschaften(get_option('vereinsname'));

    $ergebnis = array();
    foreach($mannschaften as $mannschaft){
        $ergebnis[$mannschaft->getName()] = new ImportErgebnis();
    }
    foreach($nuliga_meisterschaften as $nuliga_meisterschaft){
        foreach($nuliga_meisterschaft->mannschaftsEinteilungen as $mannschaftsEinteilung){
            $mannschaft = $nuligaBezeichnungen[$mannschaftsEinteilung->mannschaftsBezeichnung];
            if(isset($mannschaft)){
                $ergebnis[$mannschaft->getName()]->gesamt++;

                $meisterschaft = findMeisterschaft($mannschaft->getID(), $mannschaftsEinteilung->meisterschaftsKuerzel, $mannschaftsEinteilung->liga);
                if(isset($meisterschaft)){
                    $namensAenderung = $meisterschaft->getName() !== $nuliga_meisterschaft->name;
                    $liga_idAenderung = $meisterschaft->getNuligaLigaID() !==  $mannschaftsEinteilung->liga_id;
                    $team_idAenderung = $meisterschaft->getNuligaTeamID() !==  $mannschaftsEinteilung->team_id;
                    if($namensAenderung || $liga_idAenderung || $team_idAenderung){
                        updateMeisterschaft($meisterschaft->getID(), $nuliga_meisterschaft->name, $mannschaftsEinteilung->liga_id, $mannschaftsEinteilung->team_id);
                        $ergebnis[$mannschaft->getName()]->aktualisiert++;
                    }
                } else{
                    
                    insertMeisterschaft($mannschaft->getID(), 
                        $nuliga_meisterschaft->name, $mannschaftsEinteilung->meisterschaftsKuerzel, 
                        $mannschaftsEinteilung->liga, $mannschaftsEinteilung->liga_id,
                        $mannschaftsEinteilung->team_id
                    );
                    $ergebnis[$mannschaft->getName()]->neu++;
                }
            }
        }
    }
    return $ergebnis;
}

function createNuLigaMannschaftsBezeichnungen(array $mannschaften): array{
    $nuligaBezeichnungen = array();
    foreach($mannschaften as $mannschaft){
        $bezeichnung = "";
        $jugendKlasse = $mannschaft->getJugendklasse();
        if(empty($jugendKlasse)){
            switch($mannschaft->getGeschlecht()){
                case GESCHLECHT_W: $bezeichnung = "Frauen"; break;
                case GESCHLECHT_M: $bezeichnung = "Männer"; break;
            }
        }else {
            switch($mannschaft->getGeschlecht()){
                case GESCHLECHT_W: $bezeichnung = "weibliche"; break;
                case GESCHLECHT_M: $bezeichnung = "männliche"; break;
            }
            $bezeichnung .= " Jugend ".strtoupper($jugendKlasse);
        }
        if($mannschaft->getNummer() > 1){
            $bezeichnung .= " ";
            for($i=0; $i<$mannschaft->getNummer(); $i++){
                $bezeichnung .= "I";
            }
        }
        $nuligaBezeichnungen[$bezeichnung] = $mannschaft;
    }
    return $nuligaBezeichnungen;
}

?>