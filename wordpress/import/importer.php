<?php

require_once __DIR__."/../log/Log.php";
require_once __DIR__."/../log/Problem.php";

require_once __DIR__."/ImportSchritt.php";
require_once __DIR__."/SpieleGrabber.php";
require_once __DIR__."/../handball/dienst/DienstAenderungsPlan.php";
require_once __DIR__."/NuLigaSpiel.php";

require_once __DIR__."/meisterschaft/NuLiga_Ligatabelle.php";
require_once __DIR__."/meisterschaft/NuLiga_MannschaftsUndLigenEinteilung.php";

require_once __DIR__."/../dao/MannschaftDAO.php";
require_once __DIR__."/../dao/MannschaftsMeldungDAO.php";
require_once __DIR__."/../dao/MeisterschaftDAO.php";
require_once __DIR__."/../dao/SpielDAO.php";
require_once __DIR__."/../dao/DienstDAO.php";


require_once __DIR__."/../service/MannschaftService.php";
require_once __DIR__."/../service/GegnerService.php";

require_once __DIR__."/../NippesMailer.php";

class Importer{
    public static $NULIGA_MEISTERSCHAFTEN_LESEN;
    public static $MANNSCHAFTEN_ZUORDNEN;
    public static $NULIGA_TEAM_IDS_LESEN;
    public static $MEISTERSCHAFTEN_AKTUALISIEREN;
    public static $MELDUNGEN_AKTUALISIEREN;
    public static $GEGNER_IMPORTIEREN;
    public static $SPIELE_IMPORTIEREN;
    public static $CACHE_LEEREN;

    public static function alleSchritte(): array{
        $unsortierteSchritte = array(
            self::$NULIGA_MEISTERSCHAFTEN_LESEN,
            self::$MANNSCHAFTEN_ZUORDNEN,
            self::$NULIGA_TEAM_IDS_LESEN,
            self::$MEISTERSCHAFTEN_AKTUALISIEREN,
            self::$MELDUNGEN_AKTUALISIEREN,
            self::$GEGNER_IMPORTIEREN,
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


Importer::$MANNSCHAFTEN_ZUORDNEN = new ImportSchritt(2, "Mannschaften zuordnen", function (){
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

Importer::$NULIGA_TEAM_IDS_LESEN = new ImportSchritt(3, "Team-IDs aus nuLiga auslesen", function (){
    global $wpdb;
    
    $vereinsname = get_option('vereinsname');
    $mannschaftDAO = new MannschaftDAO();
    $mannschaftsListe = $mannschaftDAO->loadMannschaften();

    $table_name = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE mannschaft IS NOT NULL AND team_id IS NULL", ARRAY_A);
    foreach ($results as $nuliga_mannschaftseinteilung) {
        $mannschaft = $mannschaftsListe->mannschaften[$nuliga_mannschaftseinteilung['mannschaft']];
        $ligaTabellenSeite = new NuLiga_Ligatabelle(
            $nuliga_mannschaftseinteilung['meisterschaftsKuerzel'], 
            $nuliga_mannschaftseinteilung['liga_id']
        );
        
        $team_id = $ligaTabellenSeite->extractTeamID($vereinsname, $mannschaft->nummer);
        $updated = $wpdb->update($table_name, 
            array('team_id' => $team_id), 
            array('id' => $nuliga_mannschaftseinteilung['id'])
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

Importer::$MELDUNGEN_AKTUALISIEREN = new ImportSchritt(5, "Meldungen pro Mannschaft aktualisieren", function (Log $logfile){
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
        echo print_r($newMeldung, true)."\n";
        $oldMeldung = $meldungDAO->findMannschaftsMeldung($newMeldung->meisterschaft_id, $newMeldung->mannschaft_id, $newMeldung->liga);
        // TODO Transaktionsstart
        if(isset($oldMeldung)){
            $logfile->log("\tAktualisiere alte Meldung: ".print_r($oldMeldung, true)."");
            $meldungDAO->updateMannschaftsMeldung($oldMeldung->id, $newMeldung->nuligaLigaID, $newMeldung->nuligaTeamID);
        } else{
            $logfile->log("\Wird eingefügt");
            $meldungDAO->insert($newMeldung);
        }
        // Löschen der Einteilung in der nuliga-Import-Tabelle
        $wpdb->delete($table_nuliga_mannschaftseinteilung, array('id' => $newMeldung->id));
        // TODO Transaktionsende
    }
});
Importer::$GEGNER_IMPORTIEREN = new ImportSchritt(6, "Gegner importieren", function (Log $logfile){
    $vereinsname = get_option('vereinsname');

    $mannschaftService = new MannschaftService();
    $gegnerDAO = new GegnerDAO();
    
    $mannschaftsListe = $mannschaftService->loadMannschaftenMitMeldungen();
    foreach($mannschaftsListe->mannschaften as $mannschaft){
        $logfile->log($mannschaft->getName());
        foreach($mannschaft->meldungen as $mannschaftsMeldung) {
            $logfile->log("\t".$mannschaftsMeldung->liga);
            $nuliga_tabelle = new NuLiga_Ligatabelle(
                $mannschaftsMeldung->meisterschaft->kuerzel, 
                $mannschaftsMeldung->nuligaLigaID);
            $gegnerNamen = $nuliga_tabelle->extractGegnerNamen($vereinsname);
            foreach($gegnerNamen as $gegnerName){
                $logmessage = "$gegnerName: ";
                $logSymbol = "";
                $gegnerNeu = Gegner::fromName($gegnerName);
                $gegnerNeu->zugehoerigeMeldung = $mannschaftsMeldung;
                $gegnerAlt = $gegnerDAO->findSimilar( $gegnerNeu);
                if(isset($gegnerAlt)){
                    $logmessage .= "Bereits vorhanden";
                } else {
                    $gegnerDAO->insert($gegnerNeu);
                    $logmessage .= "Neu importiert mit ID: ".$gegnerNeu->id;
                    $logSymbol = "+";
                }
                $logfile->log("\t$logSymbol\t$logmessage");
            }
        }
    }
});

Importer::$SPIELE_IMPORTIEREN = new ImportSchritt(7, "Spiele importieren", function (Log $logfile){
    $problems = array();
    $mannschaftService = new MannschaftService();
    $gegnerService = new GegnerService();
    $spielDAO = new SpielDAO();
    $dienstDAO = new DienstDAO();
    $spielService = new SpielService();
    $mannschaftsListe = $mannschaftService->loadMannschaftenMitMeldungen();
    $alleGegner = $gegnerService->loadAlleGegner();

    $dienstAenderungsPlan = new DienstAenderungsPlan($mannschaftsListe->mannschaften);

    foreach($mannschaftsListe->mannschaften as $mannschaft){
        $logfile->log($mannschaft->getName().": Starte Import\n");
        
        $teamName = get_option('vereinsname');
        if($mannschaft->nummer >= 2){
            $teamName .= " ";
            for($i=0; $i<$mannschaft->nummer; $i++){
                $teamName .= "I";
            }
        }
        
        foreach($mannschaft->meldungen as $mannschaftsMeldung) {
            $logfile->log("\t".$mannschaftsMeldung->liga."");
            $spielGrabber = new SpieleGrabber(
                $mannschaftsMeldung->meisterschaft->kuerzel, 
                $mannschaftsMeldung->nuligaLigaID, 
                $mannschaftsMeldung->nuligaTeamID
            );
            foreach($spielGrabber->getNuLigaSpiele() as $nuLigaSpiel){
                if($nuLigaSpiel->isSpielfrei()){
                    $logfile->log("\t.\tSpiel wird übersprungen: spielfrei ".$nuLigaSpiel->getLogOutput());
                    continue;
                }
                if($nuLigaSpiel->isUngueltig()){
                    $problems[] = new Problem(
                        $mannschaft->getName()." - ".$mannschaftsMeldung->liga." - ".$nuLigaSpiel->getLogOutput(),
                        "Ungültiges Spiel",
                        $nuLigaSpiel//->getLogOutput()
                    );
                    $logfile->log("\t!\tUngültiges Spiel: ".$nuLigaSpiel->getLogOutput());
                    continue;
                }
                $logmessage = $nuLigaSpiel->getLogOutput().": ";
                $logSymbol = "";
                // TODO nur spiele in der Zukunft importieren
                $spielNeu = $nuLigaSpiel->extractSpiel($mannschaftsMeldung, $teamName);
                
                $gegnerGefunden = false;
                foreach($alleGegner as $gegner){
                    if($gegner->isSimilarTo($spielNeu->gegner)){
                        $spielNeu->gegner = $gegner;
                        $gegnerGefunden = true;
                        break;
                    }
                }
                if(!$gegnerGefunden){
                    $problems[] = new Problem(
                        $mannschaft->getName()." - ".$mannschaftsMeldung->liga." - ".$nuLigaSpiel->getLogOutput(),
                        "Der Gegner wurde nicht gefunden. Spiel wird ignoriert",
                        $nuLigaSpiel
                    );
                    $logmessage .= "Gegner wurde nicht gefunden. Spiel wird ignoriert";
                    $logSymbol = "!";
                    $logfile->log("\t$logSymbol\t$logmessage");
                    continue;
                }

                $spielAlt = $spielService->findOriginalSpiel ($spielNeu);
                
                if(isset($spielAlt)){
                    // ein bereits vorhandenes Spiel
                    $hallenAenderung = ($spielAlt->halle != $spielNeu->halle);
                    $anwurfAenderung = ($spielAlt->anwurf != $spielNeu->anwurf);
                    if($hallenAenderung || $anwurfAenderung){
                        $logmessage .= "Spiel muss aktualisiert werden.";
                        $logSymbol = "*";
                        $dienstAenderungsPlan->registerSpielAenderung($spielAlt, $spielNeu);
                        $spielDAO->update($spielAlt->id, $spielNeu);
                    } else {
                        $logmessage .= "Spiel bereits vorhanden.";
                    }
                    // TODO Spiel in eine Liste aufnehmen um zu prüfen, ob Hallenaufbau oder -abbau neu vergeben werden muss
                } else {
                    // ein neues Spiel
                    $logmessage .= "Spiel ist neu und wird importiert.";
                    $logSymbol = "+";
                    $spielDAO->insert($spielNeu);
                    $spielNeu->createDienste();
                    // TODO das Insert sollte über den SpielService laufen. Dabei wird auch der Gegner eingefügt, falls nicht vorhanden
                    // In der Folge wird oben bei extractSpiel ein neuer Gegner erstellt und der dann nur eingefügt, wenn er noch nicht existiert
                    $dienstDAO->insertAll($spielNeu->dienste);
                }
                $logfile->log("\t$logSymbol\t$logmessage");
            }
        }
    }

    // Auf- und Abbau organisieren
    $heimSpieleProHalle = $spielService->fetchSpieleProHalle("heimspiel = 1");
    foreach($heimSpieleProHalle as $halle => $spieleInDerHalle){
        $spieleProTag = $spieleInDerHalle->groupBySpielTag();
        foreach($spieleProTag as $spieltag => $spieleAmSpielTag){

            // TODO in Klasse "Spieltag" auslagern
            $erstesSpiel = $spieleAmSpielTag->getErstesSpiel();
            $aufbau = $erstesSpiel->getDienst(Dienstart::AUFBAU);
            if(!isset($aufbau)){
                $aufbau = $erstesSpiel->createDienst(Dienstart::AUFBAU);
                $aufbau->mannschaft = $erstesSpiel->mannschaft;
                $dienstDAO->insert($aufbau);
                $dienstAenderungsPlan->registerNeuenDienst($aufbau);
            }
            
            // TODO in Klasse "Spieltag" auslagern
            $letztesSpiel = $spieleAmSpielTag->getLetztesSpiel();
            $abbau = $letztesSpiel->getDienst(Dienstart::ABBAU);
            if(!isset($abbau)){
                $abbau = $letztesSpiel->createDienst(Dienstart::ABBAU);
                $abbau->mannschaft = $letztesSpiel->mannschaft;
                $dienstDAO->insert($abbau);
                $dienstAenderungsPlan->registerNeuenDienst($abbau);
            }
            
            // TODO in Klasse "Spieltag" auslagern
            foreach($spieleAmSpielTag->spiele as $spiel){
                if($spiel === $erstesSpiel || $spiel === $letztesSpiel){
                    continue;
                }
                $unnoetigerAufbau = $spiel->getDienst(Dienstart::AUFBAU);
                if(isset($unnoetigerAufbau)){
                    $dienstDAO->delete(array("id" => $unnoetigerAufbau->id));
                    $dienstAenderungsPlan->registerEntfallenenDienst($unnoetigerAufbau);
                }
                $unnoetigerAbbau = $spiel->getDienst(Dienstart::ABBAU);
                if(isset($unnoetigerAbbau)){
                    $dienstDAO->delete(array("id" => $unnoetigerAbbau->id));
                    $dienstAenderungsPlan->registerEntfallenenDienst($unnoetigerAbbau);
                }
            }
            
            // Notiz an Martin: ausprobieren! 
            // Werden Auf- und Abbau korrekt angelegt?
            // Werden bestehende Dienste (Auf/abbau) gelöscht?
            // Werden Emails für die Aktionen versandt?
        }
    }

    $dienstAenderungsPlan->sendEmails();
    return $problems;
});

Importer::$CACHE_LEEREN = new ImportSchritt(8, "Cache leeren", function (){
    global $wpdb;
    $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $wpdb->query("DELETE FROM $table_nuliga_mannschaftseinteilung");
    $wpdb->query("DELETE FROM $table_nuliga_meisterschaft");
});

?>