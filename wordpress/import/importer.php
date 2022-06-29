<?php
require_once __DIR__."/SpieleGrabber.php";
require_once __DIR__."/DienstAenderungsPlan.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../dao/mannschaft.php";
require_once __DIR__."/../dao/gegner.php";
require_once __DIR__."/../dao/meisterschaft.php";
require_once __DIR__."/../dao/spiel.php";
require_once __DIR__."/../dao/dienst.php";

require_once __DIR__."/../PHPMailer/NippesMailer.php";

class ImportErgebnisProMannschaft{
    public string $mannschaft;
    public int $gesamt = 0;
    public int $neu = 0;
    public int $aktualisiert = 0;

    public function __construct(Mannschaft $mannschaft){
        $this->mannschaft = $mannschaft->getName();
    }
}

function importSpieleFromNuliga(): array{
    
    $meisterschaften = loadMeisterschaften();
    $mannschaften = loadMannschaftenMitMeldungen();
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();

    $dienstAenderungsPlan = new DienstAenderungsPlan($mannschaften, $gegnerDAO);

    $ergebnis = array();
    foreach($mannschaften as $mannschaft){
        $importErgebnis = new ImportErgebnisProMannschaft($mannschaft);
        
        $teamName = get_option('vereinsname');
        if($mannschaft->getNummer() >= 2){
            $teamName .= " ";
            for($i=0; $i<$mannschaft->getNummer(); $i++){
                $teamName .= "I";
            }
        }
        
        foreach($mannschaft->getMeldungen() as $mannschaftsMeldung) {
            $meisterschaft = $meisterschaften[$mannschaftsMeldung->getMeisterschaft()];
            $spielGrabber = new SpieleGrabber(
                $meisterschaft->getKuerzel(), 
                $mannschaftsMeldung->getNuligaLigaID(), 
                $mannschaftsMeldung->getNuligaTeamID()
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
                    $mannschaftsMeldung->getLiga()
                )->getID();
                $spiel = findSpiel ($mannschaftsMeldung->getID(), $nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel);
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
                    insertSpiel($mannschaftsMeldung->getID(), $nuLigaSpiel->getSpielNr(), $mannschaft->getID(), $gegner_id, $isHeimspiel, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                    $importErgebnis->neu ++;
                }
            }
        }
        $ergebnis[$mannschaft->getName()]  = $importErgebnis;
    }

    $dienstAenderungsPlan->sendEmails();

    return array_values($ergebnis);
}

function importMeisterschaftenFromNuliga_new(){
    global $wpdb;
    require_once __DIR__."/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";

    $ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(get_option('nuliga-clubid'));
    $nuliga_meisterschaften = $ligeneinteilung->getMeisterschaften_new();

    $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';

    foreach($nuliga_meisterschaften as $nuliga_meisterschaft){
        $values_meisterschaft = array(
            'name' => $nuliga_meisterschaft->name
        );
        $wpdb->insert($table_nuliga_meisterschaft, $values_meisterschaft);
        $meisterschaft_id = $wpdb->insert_id;

        foreach($nuliga_meisterschaft->mannschaftsEinteilungen as $mannschaftsEinteilung){
            $values_einteilung = array(
                'nuliga_meisterschaft' => $meisterschaft_id,
                'mannschaftsBezeichnung' => $mannschaftsEinteilung->mannschaftsBezeichnung,
                'meisterschaftsKuerzel' => $mannschaftsEinteilung->meisterschaftsKuerzel,
                'liga' => $mannschaftsEinteilung->liga,
                'liga_id' => $mannschaftsEinteilung->liga_id
            );
            $wpdb->insert($table_nuliga_mannschaftseinteilung, $values_einteilung);
        }
    }
}

function importTeamIDsFromNuLiga() {
    global $wpdb;
    require_once __DIR__."/meisterschaft/NuLiga_Ligatabelle_new.php";
    
    $vereinsname = get_option('vereinsname');

    $table_name = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE team_id IS NULL", ARRAY_A);
    foreach ($results as $nuliga_mannschaftseinteilung) {
        $ligaTabellenSeite = new NuLiga_Ligatabelle_new(
            $nuliga_mannschaftseinteilung['meisterschaftsKuerzel'], 
            $nuliga_mannschaftseinteilung['liga_id']
        );
        $team_id = $ligaTabellenSeite->extractTeamID($vereinsname);
        $updated = $wpdb->update($table_name, 
            array('team_id' => $team_id), 
            array('id' => $nuliga_mannschaftseinteilung['id'])
        );
    }
}
    

function importMeisterschaftenFromNuliga(): array{
    require_once __DIR__."/../dao/MannschaftsMeldung.php";
    require_once __DIR__."/../dao/Meisterschaft.php";
    require_once __DIR__."/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";
    
    $mannschaften = loadMannschaften();
    $nuligaBezeichnungen = createNuLigaMannschaftsBezeichnungen($mannschaften);
    $ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(get_option('nuliga-clubid'));
    $nuliga_meisterschaften = $ligeneinteilung->getMeisterschaften(get_option('vereinsname'));

    $ergebnis = array();
    foreach($mannschaften as $mannschaft){
        $ergebnis[$mannschaft->getName()] = new ImportErgebnisProMannschaft($mannschaft);
    }
    foreach($nuliga_meisterschaften as $nuliga_meisterschaft){
        $meisterschaftNeedsUpsert = true;
        $meisterschaft_id = null;
        foreach($nuliga_meisterschaft->mannschaftsEinteilungen as $mannschaftsEinteilung){
            if($meisterschaftNeedsUpsert){
                // muss einmal zu Beginn passieren, da das Kürzel der Meisterschaft nur den Kind-Elementen bekannt ist.
                $meisterschaft_id = upsertMeisterschaft($mannschaftsEinteilung->meisterschaftsKuerzel, $nuliga_meisterschaft->name);
                $meisterschaftNeedsUpsert = false;
            }
            $mannschaft = $nuligaBezeichnungen[$mannschaftsEinteilung->mannschaftsBezeichnung];
            
            if(isset($mannschaft)){
                $ergebnis[$mannschaft->getName()]->gesamt++;

                $mannschaftsMeldung = findMannschaftsMeldung($meisterschaft_id, $mannschaft->getID(), $mannschaftsEinteilung->liga);
                if(isset($mannschaftsMeldung)){
                    $liga_idAenderung = $mannschaftsMeldung->getNuligaLigaID() !==  $mannschaftsEinteilung->liga_id;
                    $team_idAenderung = $mannschaftsMeldung->getNuligaTeamID() !==  $mannschaftsEinteilung->team_id;
                    if($namensAenderung || $liga_idAenderung || $team_idAenderung){
                        updateMannschaftsMeldung($mannschaftsMeldung->getID(), $mannschaftsEinteilung->liga_id, $mannschaftsEinteilung->team_id);
                        $ergebnis[$mannschaft->getName()]->aktualisiert++;
                    }
                } else{
                    insertMannschaftsMeldung($meisterschaft_id, $mannschaft->getID(), 
                        $mannschaftsEinteilung->liga, $mannschaftsEinteilung->liga_id,
                        $mannschaftsEinteilung->team_id
                    );
                    $ergebnis[$mannschaft->getName()]->neu++;
                }
            }
        }
    }
    return array_values($ergebnis);
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