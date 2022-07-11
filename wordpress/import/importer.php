<?php
require_once __DIR__."/ImportSchritt.php";
require_once __DIR__."/SpieleGrabber.php";
require_once __DIR__."/DienstAenderungsPlan.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/../dao/MannschaftDAO.php";
require_once __DIR__."/../dao/GegnerDAO.php";
require_once __DIR__."/../dao/MeisterschaftDAO.php";
require_once __DIR__."/../dao/SpielDAO.php";
require_once __DIR__."/../dao/DienstDAO.php";

require_once __DIR__."/../service/MannschaftService.php";

require_once __DIR__."/../PHPMailer/NippesMailer.php";

class Importer{
    public static $NULIGA_MEISTERSCHAFTEN_LESEN;
    public static $NULIGA_TEAM_IDS_LESEN;
    public static $MANNSCHAFTEN_ZUORDNEN;
    public static $MEISTERSCHAFTEN_AKTUALISIEREN;
    public static $MELDUNGEN_AKTUALISIEREN;
    public static $SPIELE_IMPORTIEREN;
    public static $CACHE_LEEREN;

    public static function alleSchritte(): array{
        $unsortierteSchritte = array(
            self::$NULIGA_MEISTERSCHAFTEN_LESEN,
            self::$NULIGA_TEAM_IDS_LESEN,
            self::$MANNSCHAFTEN_ZUORDNEN,
            self::$MEISTERSCHAFTEN_AKTUALISIEREN,
            self::$MELDUNGEN_AKTUALISIEREN,
            self::$SPIELE_IMPORTIEREN,
            self::$CACHE_LEEREN
        );
        $sortierteSchritte = array();
        foreach($unsortierteSchritte as $schritt){
            $sortierteSchritte[$schritt->schritt] = $schritt;
        }
        return $sortierteSchritte;
    }

    public static function starteAlles(){
        ImportSchritt::initAlleSchritte();
        foreach(self::alleSchritte() as $schritt){
            $schritt->run();
        }
    }
}

Importer::$NULIGA_MEISTERSCHAFTEN_LESEN = new ImportSchritt(1, "Meisterschaften von nuLiga lesen", function (){
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
});

Importer::$NULIGA_TEAM_IDS_LESEN = new ImportSchritt(2, "Team-IDs aus nuLiga auslesen", function (){
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
});

Importer::$MANNSCHAFTEN_ZUORDNEN = new ImportSchritt(3, "Mannschaften zuordnen", function (){
    global $wpdb;

    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';

    $mannschaftDAO = new MannschaftDAO();
    $mannschaftsListe = $mannschaftDAO->loadMannschaften();
    $nuligaBezeichnungen = $mannschaftsListe->createNuLigaMannschaftsBezeichnungen();
    var_dump($nuligaBezeichnungen);

    $results = $wpdb->get_results("SELECT id, mannschaftsBezeichnung FROM $table_nuliga_mannschaftseinteilung WHERE mannschaft IS NULL", ARRAY_A);
    foreach($results as $nuliga_mannschaftsEinteilung){
        if(!array_key_exists($nuliga_mannschaftsEinteilung['mannschaftsBezeichnung'], $nuligaBezeichnungen)){
            continue;
        }
        $mannschaft = $nuligaBezeichnungen[$nuliga_mannschaftsEinteilung['mannschaftsBezeichnung']];
        $wpdb->update(
            $table_nuliga_mannschaftseinteilung, 
            array('mannschaft'=>$mannschaft->id), 
            array('id' => $nuliga_mannschaftsEinteilung['id'])
        );
    }
});

Importer::$MEISTERSCHAFTEN_AKTUALISIEREN = new ImportSchritt(4, "Meisterschaften aktualisieren", function (){
    global $wpdb;

    $table_meisterschaft = MeisterschaftDAO::tableName($wpdb);
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
});

Importer::$MELDUNGEN_AKTUALISIEREN = new ImportSchritt(5, "Meldungen pro Mannschaft aktualisieren", function (){
    global $wpdb;

    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $table_meisterschaft = MeisterschaftDAO::tableName($wpdb);

    $sql = "SELECT 
            $table_nuliga_mannschaftseinteilung.id,
            nuliga_meisterschaft,
            $table_meisterschaft.id as meisterschaft_id, 
            mannschaft as mannschaft_id, 
            liga, 
            liga_id as nuligaLigaID, 
            team_id as nuligaTeamID 
        FROM $table_nuliga_mannschaftseinteilung
        LEFT JOIN $table_meisterschaft on $table_meisterschaft.kuerzel=$table_nuliga_mannschaftseinteilung.meisterschaftsKuerzel
        WHERE team_id IS NOT NULL
        AND mannschaft IS NOT NULL";
    $results = $wpdb->get_results($sql, ARRAY_A);
    
    $meldungDAO = new MannschaftsMeldungDAO($wpdb);
    foreach($results as $nuliga_mannschaftsEinteilung){
        $mannschaftsMeldung = $meldungDAO->findMannschaftsMeldung($nuliga_mannschaftsEinteilung['meisterschaft_id'], $nuliga_mannschaftsEinteilung['mannschaft_id'], $nuliga_mannschaftsEinteilung['liga']);
        // TODO Transaktionsstart
        if(isset($mannschaftsMeldung)){
            $meldungDAO->updateMannschaftsMeldung($mannschaftsMeldung->id, $nuliga_mannschaftsEinteilung['nuligaLigaID'], $nuliga_mannschaftsEinteilung['nuligaTeamID']);
        } else{
            $meldungDAO->insertMannschaftsMeldung($nuliga_mannschaftsEinteilung['meisterschaft_id'], $nuliga_mannschaftsEinteilung['mannschaft_id'], 
                $nuliga_mannschaftsEinteilung['liga'], $nuliga_mannschaftsEinteilung['nuligaLigaID'],
                $nuliga_mannschaftsEinteilung['nuligaTeamID']
            );
        }
        // Löschen der Einteilung in der nuliga-Import-Tabelle
        $wpdb->delete($table_nuliga_mannschaftseinteilung, array('id' => $nuliga_mannschaftsEinteilung['id']));
        // TODO Transaktionsende
    }
});

Importer::$SPIELE_IMPORTIEREN = new ImportSchritt(6, "Spiele importieren", function (){
    $mannschaftService = new MannschaftService();
    $gegnerDAO = new GegnerDAO();
    $spielDAO = new SpielDAO();
    $mannschaftsListe = $mannschaftService->loadMannschaftenMitMeldungen();

    $dienstAenderungsPlan = new DienstAenderungsPlan($mannschaftsListe->mannschaften);

    foreach($mannschaftsListe->mannschaften as $mannschaft){
        
        $teamName = get_option('vereinsname');
        if($mannschaft->nummer >= 2){
            $teamName .= " ";
            for($i=0; $i<$mannschaft->nummer; $i++){
                $teamName .= "I";
            }
        }
        
        foreach($mannschaft->meldungen as $mannschaftsMeldung) {
            var_dump($mannschaftsMeldung);
            $spielGrabber = new SpieleGrabber(
                $mannschaftsMeldung->meisterschaft->kuerzel, 
                $mannschaftsMeldung->nuligaLigaID, 
                $mannschaftsMeldung->nuligaTeamID
            );
            foreach($spielGrabber->getNuLigaSpiele() as $nuLigaSpiel){
                if($nuLigaSpiel->getHeimmannschaft() === $teamName){
                    $isHeimspiel = 1;
                    $gegnerName = $nuLigaSpiel->getGastmannschaft();
                } else {
                    $isHeimspiel = 0;
                    $gegnerName = $nuLigaSpiel->getHeimmannschaft();
                }
                $gegner = $gegnerDAO->findOrInsertGegner( 
                    $gegnerName, 
                    $mannschaft->geschlecht, 
                    $mannschaftsMeldung->liga
                );
                $spiel = $spielDAO->findSpiel ($mannschaftsMeldung->id, $nuLigaSpiel->getSpielNr(), $mannschaft->id, $gegner->id, $isHeimspiel);
                
                if(isset($spiel)){
                    $hallenAenderung = ($spiel->halle != $nuLigaSpiel->getHalle());
                    $AnwurfAenderung = ($spiel->anwurf != $nuLigaSpiel->getAnwurf());
                    if($hallenAenderung || $AnwurfAenderung){
                        $dienstAenderungsPlan->registerSpielAenderung($spiel, $nuLigaSpiel);
                        $spielDAO->updateSpiel($spiel->id, $nuLigaSpiel->getHalle(), $nuLigaSpiel->anwurf);
                    }
                } else {
                    $spielDAO->insertSpiel($mannschaftsMeldung->id, $nuLigaSpiel->getSpielNr(), $mannschaft->id, $gegner->id, $isHeimspiel, $nuLigaSpiel->getHalle(), $nuLigaSpiel->getAnwurf());
                }
            }
        }
    }

    $dienstAenderungsPlan->sendEmails();
});

Importer::$CACHE_LEEREN = new ImportSchritt(7, "Cache leeren", function (){
    global $wpdb;
    $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $wpdb->query("DELETE FROM $table_nuliga_mannschaftseinteilung");
    $wpdb->query("DELETE FROM $table_nuliga_meisterschaft");
});

?>