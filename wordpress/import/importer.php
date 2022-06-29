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

function importMeisterschaftenFromNuliga(){
    global $wpdb;
    require_once __DIR__."/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";

    $ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(get_option('nuliga-clubid'));
    $nuliga_meisterschaften = $ligeneinteilung->getMeisterschaften();

    $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';

    foreach($nuliga_meisterschaften as $nuliga_meisterschaft){
        $meisterschaft_id = $wpdb->get_var("SELECT id FROM $table_nuliga_meisterschaft WHERE name=\"".$nuliga_meisterschaft->name."\"");
        if(empty($meisterschaft_id)){
            $values_meisterschaft = array(
                'name' => $nuliga_meisterschaft->name
            );
            $wpdb->insert($table_nuliga_meisterschaft, $values_meisterschaft);
            $meisterschaft_id = $wpdb->insert_id;
        }

        foreach($nuliga_meisterschaft->mannschaftsEinteilungen as $mannschaftsEinteilung){
            $mannschaftsEinteilung_id = $wpdb->get_var(
                "SELECT id FROM $table_nuliga_mannschaftseinteilung "
                ."WHERE mannschaftsBezeichnung=\"".$mannschaftsEinteilung->mannschaftsBezeichnung."\" "
                ."AND liga_id=".$mannschaftsEinteilung->liga_id
            );
            
            $values_einteilung = array(
                'nuliga_meisterschaft' => $meisterschaft_id,
                'mannschaftsBezeichnung' => $mannschaftsEinteilung->mannschaftsBezeichnung,
                'meisterschaftsKuerzel' => $mannschaftsEinteilung->meisterschaftsKuerzel,
                'liga' => $mannschaftsEinteilung->liga,
                'liga_id' => $mannschaftsEinteilung->liga_id
            );

            if(isset($mannschaftsEinteilung_id)){
                $wpdb->update($table_nuliga_mannschaftseinteilung, $values_einteilung, array('id' => $mannschaftsEinteilung_id));
            } else{
                $wpdb->insert($table_nuliga_mannschaftseinteilung, $values_einteilung);
            }
        }
    }
}

function importTeamIDsFromNuLiga() {
    global $wpdb;
    require_once __DIR__."/meisterschaft/NuLiga_Ligatabelle.php";
    
    $vereinsname = get_option('vereinsname');

    $table_name = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE team_id IS NULL", ARRAY_A);
    foreach ($results as $nuliga_mannschaftseinteilung) {
        $ligaTabellenSeite = new NuLiga_Ligatabelle(
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

function mannschaftenZuordnen(){
    global $wpdb;

    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';

    $mannschaften = loadMannschaften();
    $nuligaBezeichnungen = createNuLigaMannschaftsBezeichnungen($mannschaften);

    $results = $wpdb->get_results("SELECT id, mannschaftsBezeichnung FROM $table_nuliga_mannschaftseinteilung WHERE mannschaft IS NULL", ARRAY_A);
    foreach($results as $nuliga_mannschaftsEinteilung){
        if(!array_key_exists($nuliga_mannschaftsEinteilung['mannschaftsBezeichnung'], $nuligaBezeichnungen)){
            continue;
        }
        $mannschaft = $nuligaBezeichnungen[$nuliga_mannschaftsEinteilung['mannschaftsBezeichnung']];
        $wpdb->update(
            $table_nuliga_mannschaftseinteilung, 
            array('mannschaft'=>$mannschaft->getID()), 
            array('id' => $nuliga_mannschaftsEinteilung['id'])
        );
    }
}

function updateMeisterschaften(){
    global $wpdb;

    $table_meisterschaft = $wpdb->prefix . 'meisterschaft';
    $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';

    $results = $wpdb->get_results(
        "SELECT name , (
            SELECT meisterschaftsKuerzel 
            FROM $table_nuliga_mannschaftseinteilung 
            WHERE nuliga_meisterschaft=$table_nuliga_meisterschaft.id 
            LIMIT 1
        ) as kuerzel FROM $table_nuliga_meisterschaft", ARRAY_A);
    foreach($results as $nuliga_meisterschaft){
        $sql = "SELECT id FROM $table_meisterschaft WHERE kuerzel=\"".$nuliga_meisterschaft['kuerzel']."\"";
        $meisterschaft_id = $wpdb->get_var($sql);
        if(isset($meisterschaft_id)){
            $wpdb->update($table_meisterschaft, $nuliga_meisterschaft, array('id' => $meisterschaft_id));
        }else{
            $wpdb->insert($table_meisterschaft, $nuliga_meisterschaft);
        }
    }
}

function updateMannschaftsMeldungen(){
    global $wpdb;

    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $table_meisterschaft = $wpdb->prefix . 'meisterschaft';

    $sql = "SELECT 
            $table_nuliga_mannschaftseinteilung.id,
            nuliga_meisterschaft,
            $table_meisterschaft.id as meisterschaft, 
            mannschaft, 
            liga, 
            liga_id as nuliga_liga_id, 
            team_id as nuliga_team_id 
        FROM $table_nuliga_mannschaftseinteilung
        LEFT JOIN $table_meisterschaft on $table_meisterschaft.kuerzel=$table_nuliga_mannschaftseinteilung.meisterschaftsKuerzel
        WHERE team_id IS NOT NULL
        AND mannschaft IS NOT NULL";
    $results = $wpdb->get_results($sql, ARRAY_A);
    
    foreach($results as $nuliga_mannschaftsEinteilung){
        $mannschaftsMeldung = findMannschaftsMeldung($nuliga_mannschaftsEinteilung['meisterschaft'], $nuliga_mannschaftsEinteilung['mannschaft'], $nuliga_mannschaftsEinteilung['liga']);
        // TODO Transaktionsstart
        if(isset($mannschaftsMeldung)){
            updateMannschaftsMeldung($mannschaftsMeldung->getID(), $nuliga_mannschaftsEinteilung['nuliga_liga_id'], $nuliga_mannschaftsEinteilung['nuliga_team_id']);
        } else{
            insertMannschaftsMeldung($nuliga_mannschaftsEinteilung['meisterschaft'], $nuliga_mannschaftsEinteilung['mannschaft'], 
                $nuliga_mannschaftsEinteilung['liga'], $nuliga_mannschaftsEinteilung['nuliga_liga_id'],
                $nuliga_mannschaftsEinteilung['nuliga_team_id']
            );
        }
        // Löschen der Einteilung in der nuliga-Import-Tabelle
        $wpdb->delete($table_nuliga_mannschaftseinteilung, array('id' => $nuliga_mannschaftsEinteilung['id']));
        // TODO Transaktionsende
    }
    // Löschen überflüssiger Meisterschaften in der nuliga-Import-Tabelle
    $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
    $wpdb->query("DELETE FROM $table_nuliga_meisterschaft WHERE id NOT IN (SELECT nuliga_meisterschaft FROM $table_nuliga_mannschaftseinteilung)");
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