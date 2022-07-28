<?php
require_once __DIR__."/ImportSchritt.php";
require_once __DIR__."/SpieleGrabber.php";
require_once __DIR__."/DienstAenderungsPlan.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/meisterschaft/NuLiga_Ligatabelle.php";
require_once __DIR__."/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";

require_once __DIR__."/../dao/MannschaftDAO.php";
require_once __DIR__."/../dao/MannschaftsMeldungDAO.php";
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
            $table_meisterschaft.id as meisterschaft_id, 
            mannschaft as mannschaft_id, 
            liga, 
            1 as aktiv,
            liga_id as nuligaLigaID, 
            team_id as nuligaTeamID 
        FROM $table_nuliga_mannschaftseinteilung
        LEFT JOIN $table_meisterschaft on $table_meisterschaft.kuerzel=$table_nuliga_mannschaftseinteilung.meisterschaftsKuerzel
        WHERE team_id IS NOT NULL
        AND mannschaft IS NOT NULL";
    $results = $wpdb->get_results($sql);
    
    $meldungDAO = new MannschaftsMeldungDAO($wpdb);
    foreach($results as $newMeldung){
        $oldMeldung = $meldungDAO->findMannschaftsMeldung($newMeldung->meisterschaft_id, $newMeldung->mannschaft_id, $newMeldung->liga);
        // TODO Transaktionsstart
        if(isset($oldMeldung)){
            $meldungDAO->updateMannschaftsMeldung($oldMeldung->id, $newMeldung->nuligaLigaID, $newMeldung->nuligaTeamID);
        } else{
            $meldungDAO->insert($newMeldung);
        }
        // Löschen der Einteilung in der nuliga-Import-Tabelle
        $wpdb->delete($table_nuliga_mannschaftseinteilung, array('id' => $newMeldung->id));
        // TODO Transaktionsende
    }
});

Importer::$SPIELE_IMPORTIEREN = new ImportSchritt(6, "Spiele importieren", function (){
    $mannschaftService = new MannschaftService();
    $gegnerDAO = new GegnerDAO();
    $spielDAO = new SpielDAO();
    $dienstDAO = new DienstDAO();
    $spielService = new SpielService();
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
            $spielGrabber = new SpieleGrabber(
                $mannschaftsMeldung->meisterschaft->kuerzel, 
                $mannschaftsMeldung->nuligaLigaID, 
                $mannschaftsMeldung->nuligaTeamID
            );
            foreach($spielGrabber->getNuLigaSpiele() as $nuLigaSpiel){
                $spielNeu = $nuLigaSpiel->extractSpiel($mannschaftsMeldung, $teamName, 
                    function (string $gegnerName, $mannschaftsMeldung) use ($gegnerDAO) {
                        return $gegnerDAO->findOrInsertGegner( $gegnerName, $mannschaftsMeldung);
                    }
                );
                $spielAlt = $spielService->findOriginalSpiel ($spielNeu);
                
                if(isset($spielAlt)){
                    // ein bereits vorhandenes Spiel
                    $hallenAenderung = ($spielAlt->halle != $spielNeu->halle);
                    $AnwurfAenderung = ($spielAlt->anwurf != $spielNeu->anwurf);
                    if($hallenAenderung || $AnwurfAenderung){
                        $dienstAenderungsPlan->registerSpielAenderung($spielAlt, $spielNeu);
                        $spielDAO->update($spielAlt->id, $spielNeu);
                    }
                    // TODO Spiel in eine Liste aufnehmen um zu prüfen, ob Hallenaufbau oder -abbau neu vergeben werden muss
                } else {
                    // ein neues Spiel
                    $spielDAO->insert($spielNeu);
                    $spielNeu->createDienste();
                    // TODO das Insert sollte über den SpielService laufen. Dabei wird auch der Gegner eingefügt, falls nicht vorhanden
                    // In der Folge wird oben bei extractSpiel ein neuer Gegner erstellt und der dann nur eingefügt, wenn er noch nicht existiert
                    $dienstDAO->insertAll($spielNeu->dienste);
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