<?php

require_once __DIR__."/../../log/Log.php";
require_once __DIR__."/../../log/Problem.php";

require_once __DIR__."/ImportSchritt.php";
require_once __DIR__."/../../handball/dienst/DienstAenderungsPlan.php";
require_once __DIR__."/nuliga/entities/NuLigaSpiel.php";

require_once __DIR__."/nuliga/pages/NuLiga_SpiellisteTeam.php";
require_once __DIR__."/nuliga/pages//NuLiga_Ligatabelle.php";
require_once __DIR__."/nuliga/pages/NuLiga_MannschaftsUndLigenEinteilung.php";

require_once __DIR__."/../../db/dao/MannschaftDAO.php";
require_once __DIR__."/../../db/dao/MannschaftsMeldungDAO.php";
require_once __DIR__."/../../db/dao/MeisterschaftDAO.php";
require_once __DIR__."/../../db/dao/SpielDAO.php";
require_once __DIR__."/../../db/dao/DienstDAO.php";

require_once __DIR__."/../../db/service/MannschaftService.php";
require_once __DIR__."/../../db/service/GegnerService.php";

class Importer{
    public static $NULIGA_VEREINSSEITE_LADEN;
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
            self::$NULIGA_VEREINSSEITE_LADEN,
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
        global $wpdb;
        ImportSchritt::initAlleSchritte();
        foreach(self::alleSchritte() as $schritt){
            $schritt->run($wpdb);
        }
    }

    public static function alleDatenBereinigen(){
        global $wpdb;

        // NuLiga-Tabellen leeren
        $table_nuliga_meisterschaft = $wpdb->prefix . 'nuliga_meisterschaft';
        $table_nuliga_mannschaftseinteilung = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
        $wpdb->query("DELETE FROM $table_nuliga_mannschaftseinteilung");
        $wpdb->query("DELETE FROM $table_nuliga_meisterschaft");

        // Entitäten löschen
        $table_dienst = DienstDAO::tableName($wpdb);
        $wpdb->query("DELETE FROM $table_dienst");

        $table_spiel = SpielDAO::tableName($wpdb);
        $wpdb->query("DELETE FROM $table_spiel");

        $table_gegner = GegnerDAO::tableName($wpdb);
        $wpdb->query("DELETE FROM $table_gegner");
        
        $table_mannschaftsMeldung = MannschaftsMeldungDAO::tableName($wpdb);
        $wpdb->query("DELETE FROM $table_mannschaftsMeldung");

        $table_meisterschaften = MeisterschaftDAO::tableName($wpdb);
        $wpdb->query("DELETE FROM $table_meisterschaften");

        // Import-Schritt-Daten
        $table_importSchritte = ImportSchritt::tableName($wpdb);
        $wpdb->query("DELETE FROM $table_importSchritte");

    }
}

Importer::$NULIGA_VEREINSSEITE_LADEN = new ImportSchritt(1, "Vereinsseite von nuLiga laden", function ($dbhandle, Log $logfile){
    $ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(get_option('nuliga-clubid'), $logfile);
    $ligeneinteilung->saveLocally();
});

Importer::$NULIGA_MEISTERSCHAFTEN_LESEN = new ImportSchritt(2, "Mannschaften und Ligazuordnungen von nuLiga-Vereinsseite lesen", function ($dbhandle, Log $logfile){
    $ligeneinteilung = new NuLiga_MannschaftsUndLigenEinteilung(get_option('nuliga-clubid'), $logfile);
    $nuliga_meisterschaften = $ligeneinteilung->getMeisterschaften();

    $table_nuliga_meisterschaft = $dbhandle->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';

    foreach($nuliga_meisterschaften as $nuliga_meisterschaft){
        $meisterschaft_id = $dbhandle->get_var("SELECT id FROM $table_nuliga_meisterschaft WHERE name=\"".$nuliga_meisterschaft->name."\"");
        if(empty($meisterschaft_id)){
            $values_meisterschaft = array(
                'name' => $nuliga_meisterschaft->name
            );
            $dbhandle->insert($table_nuliga_meisterschaft, $values_meisterschaft);
            $meisterschaft_id = $dbhandle->insert_id;
        }

        foreach($nuliga_meisterschaft->mannschaftsEinteilungen as $mannschaftsEinteilung){
            $mannschaftsEinteilung_id = $dbhandle->get_var(
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
                $dbhandle->update($table_nuliga_mannschaftseinteilung, $values_einteilung, array('id' => $mannschaftsEinteilung_id));
            } else{
                $dbhandle->insert($table_nuliga_mannschaftseinteilung, $values_einteilung);
            }
        }
    }
});

Importer::$MANNSCHAFTEN_ZUORDNEN = new ImportSchritt(3, "Mannschaften zuordnen", function ($dbhandle, Log $logfile){
    $table_nuliga_mannschaftseinteilung = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';

    $mannschaftDAO = new MannschaftDAO($dbhandle);
    $mannschaftsListe = $mannschaftDAO->loadMannschaften();
    $nuligaBezeichnungen = $mannschaftsListe->createNuLigaMannschaftsBezeichnungen();
    $logfile->log(print_r($nuligaBezeichnungen), true);

    $results = $dbhandle->get_results("SELECT id, mannschaftsBezeichnung FROM $table_nuliga_mannschaftseinteilung WHERE mannschaft IS NULL", ARRAY_A);
    foreach($results as $nuliga_mannschaftsEinteilung){
        if(!array_key_exists($nuliga_mannschaftsEinteilung['mannschaftsBezeichnung'], $nuligaBezeichnungen)){
            $logfile->log("Für die in nuLiga gelistete mannschaft '".$nuliga_mannschaftsEinteilung['mannschaftsBezeichnung']."' "
                ."wurde keine in diesem Plugin hinterlegte Mannschaft gefunden. Sie wird übersprungen.");
            continue;
        }
        $mannschaft = $nuligaBezeichnungen[$nuliga_mannschaftsEinteilung['mannschaftsBezeichnung']];
        $logfile->log("'".$nuliga_mannschaftsEinteilung['mannschaftsBezeichnung']."' gehört zur Mannschaft mit der ID ".$mannschaft->id);
        $dbhandle->update(
            $table_nuliga_mannschaftseinteilung, 
            array('mannschaft'=>$mannschaft->id), 
            array('id' => $nuliga_mannschaftsEinteilung['id'])
        );
    }
});

Importer::$NULIGA_TEAM_IDS_LESEN = new ImportSchritt(4, "Team-IDs aus nuLiga auslesen", function ($dbhandle, Log $logfile){
    $vereinsname = get_option('vereinsname');
    $mannschaftDAO = new MannschaftDAO($dbhandle);
    $mannschaftsListe = $mannschaftDAO->loadMannschaften();

    $table_name = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';
    $results = $dbhandle->get_results("SELECT * FROM $table_name WHERE mannschaft IS NOT NULL AND team_id IS NULL", ARRAY_A);
    foreach ($results as $nuliga_mannschaftseinteilung) {
        $ligaTabellenSeite = new NuLiga_Ligatabelle(
            $nuliga_mannschaftseinteilung['meisterschaftsKuerzel'], 
            $nuliga_mannschaftseinteilung['liga_id'],
            $logfile
        );
        
        $mannschaft = $mannschaftsListe->mannschaften[$nuliga_mannschaftseinteilung['mannschaft']];
        $team_id = $ligaTabellenSeite->extractTeamID($vereinsname, $mannschaft->nummer);
        $updated = $dbhandle->update($table_name, 
            array('team_id' => $team_id), 
            array('id' => $nuliga_mannschaftseinteilung['id'])
        );
    }
});

Importer::$MEISTERSCHAFTEN_AKTUALISIEREN = new ImportSchritt(5, "Meisterschaften aktualisieren", function ($dbhandle){
    $table_meisterschaft = MeisterschaftDAO::tableName($dbhandle);
    $table_nuliga_meisterschaft = $dbhandle->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';

    $results = $dbhandle->get_results(
        "SELECT name , (
            SELECT meisterschaftsKuerzel 
            FROM $table_nuliga_mannschaftseinteilung 
            WHERE nuliga_meisterschaft=$table_nuliga_meisterschaft.id 
            LIMIT 1
        ) as kuerzel FROM $table_nuliga_meisterschaft", ARRAY_A);
    foreach($results as $nuliga_meisterschaft){
        $sql = "SELECT id FROM $table_meisterschaft WHERE kuerzel=\"".$nuliga_meisterschaft['kuerzel']."\"";
        $meisterschaft_id = $dbhandle->get_var($sql);
        if(isset($meisterschaft_id)){
            $dbhandle->update($table_meisterschaft, $nuliga_meisterschaft, array('id' => $meisterschaft_id));
        }else{
            $dbhandle->insert($table_meisterschaft, $nuliga_meisterschaft);
        }
    }
});

Importer::$MELDUNGEN_AKTUALISIEREN = new ImportSchritt(6, "Meldungen pro Mannschaft aktualisieren", function ($dbhandle, Log $logfile){
    $table_nuliga_mannschaftseinteilung = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';
    $table_meisterschaft = MeisterschaftDAO::tableName($dbhandle);

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
    $results = $dbhandle->get_results($sql);
    
    $meldungDAO = new MannschaftsMeldungDAO($dbhandle);
    foreach($results as $newMeldung){
        $logfile->log("Eintrag '".print_r($newMeldung, true)."' wird verarbeitet");
        $oldMeldung = $meldungDAO->findMannschaftsMeldung($newMeldung->meisterschaft_id, $newMeldung->mannschaft_id, $newMeldung->liga);
        // TODO Transaktionsstart
        if(isset($oldMeldung)){
            $logfile->log("Aktualisiere alte Meldung: ".print_r($oldMeldung, true));
            $meldungDAO->updateMannschaftsMeldung($oldMeldung->id, $newMeldung->nuligaLigaID, $newMeldung->nuligaTeamID);
        } else{
            $logfile->log("Wird eingefügt");
            $meldungDAO->insert($newMeldung);
        }
        // Löschen der Einteilung in der nuliga-Import-Tabelle
        $dbhandle->delete($table_nuliga_mannschaftseinteilung, array('id' => $newMeldung->id));
        // TODO Transaktionsende
    }
});
Importer::$GEGNER_IMPORTIEREN = new ImportSchritt(7, "Gegner importieren", function ($dbhandle, Log $logfile){
    $vereinsname = get_option('vereinsname');

    $mannschaftService = new MannschaftService($dbhandle);
    $gegnerDAO = new GegnerDAO($dbhandle);
    
    $mannschaftsListe = $mannschaftService->loadMannschaftenMitMeldungen();
    foreach($mannschaftsListe->mannschaften as $mannschaft){
        $logfile->log($mannschaft->getName());
        foreach($mannschaft->meldungen as $mannschaftsMeldung) {
            $logfile->log("\t".$mannschaftsMeldung->liga);
            $nuliga_tabelle = new NuLiga_Ligatabelle(
                $mannschaftsMeldung->meisterschaft->kuerzel, 
                $mannschaftsMeldung->nuligaLigaID,
                $logfile);
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

Importer::$SPIELE_IMPORTIEREN = new ImportSchritt(8, "Spiele importieren", function ($dbhandle, Log $logfile){
    $problems = array();
    $mannschaftService = new MannschaftService($dbhandle);
    $gegnerService = new GegnerService($dbhandle);
    $spielDAO = new SpielDAO($dbhandle);
    $dienstDAO = new DienstDAO($dbhandle);
    $spielService = new SpielService($dbhandle);
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
            $logfile->log("\t".$mannschaftsMeldung->liga);
            if(!$mannschaftsMeldung->aktiv){
                $logfile->log("\t.\tDiese Meldung ist inaktiv und wird übersprungen.");
                continue;
            }
            $spielGrabber = new NuLiga_SpiellisteTeam(
                $mannschaftsMeldung->meisterschaft->kuerzel, 
                $mannschaftsMeldung->nuligaLigaID, 
                $mannschaftsMeldung->nuligaTeamID,
                $logfile
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
                $logfile->log($nuLigaSpiel->getLogOutput());
                $logfile->setIndentation("\t");
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
                    $logfile->log("Gegner wurde nicht gefunden. Spiel wird ignoriert");
                    continue;
                }

                $spielAlt = $spielService->findOriginalSpiel ($spielNeu);
                
                if(isset($spielAlt)){
                    // ein bereits vorhandenes Spiel
                    $hallenAenderung = ($spielAlt->halle != $spielNeu->halle);
                    $anwurfAenderung = $spielAlt->anwurfDiffers($spielNeu);
                    if($hallenAenderung || $anwurfAenderung){
                        $logfile->log("Spiel muss aktualisiert werden.");
                        $dienstAenderungsPlan->registerSpielAenderung($spielAlt, $spielNeu);
                        $spielDAO->update($spielAlt->id, $spielNeu);
                    } else {
                        $logfile->log("Spiel bereits vorhanden.");
                    }
                    // TODO Spiel in eine Liste aufnehmen um zu prüfen, ob Hallenaufbau oder -abbau neu vergeben werden muss
                } else {
                    // ein neues Spiel
                    $logfile->log("Spiel ist neu und wird importiert.");
                    $spielDAO->insert($spielNeu);
                    $spielNeu->createDienste($logfile);
                    // TODO das Insert sollte über den SpielService laufen. Dabei wird auch der Gegner eingefügt, falls nicht vorhanden
                    // In der Folge wird oben bei extractSpiel ein neuer Gegner erstellt und der dann nur eingefügt, wenn er noch nicht existiert
                    $dienstDAO->insertAll($spielNeu->dienste);
                }
                $logfile->resetIndentation();
            }
        }
    }

    // Auf- und Abbau organisieren
    $heimSpieleProHalle = $spielService->fetchSpieleProHalle("heimspiel = 1");
    foreach($heimSpieleProHalle as $halle => $spieleInDerHalle){
        $spieleProTag = $spieleInDerHalle->groupBySpielTag();
        foreach($spieleProTag as $spieltag => $spieleAmSpielTag){
            if($spieltag === ""){
                continue; // Spieltag nicht gesetzt
                // TODO eigentlich muss dann Auf- und Abbau gelöscht werden, aber das dürfte auch passieren, sobald der Spieltag wieder zugewiesen wird
            }

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

Importer::$CACHE_LEEREN = new ImportSchritt(9, "Cache leeren", function ($dbhandle, Log $logfile){
    $logfile->log("Datenbank-cache leeren");
    $table_nuliga_meisterschaft = $dbhandle->prefix . 'nuliga_meisterschaft';
    $table_nuliga_mannschaftseinteilung = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';
    $dbhandle->query("DELETE FROM $table_nuliga_mannschaftseinteilung");
    $dbhandle->query("DELETE FROM $table_nuliga_meisterschaft");
    
    $logfile->log("========================================================");
    $logfile->log("Cache-Dateien löschen");
    deleteAll(Webpage::CACHEFILE_BASE_DIRECTORY(), $logfile);
});

// Function to delete all files
// and directories
function deleteAll($filename_or_dir, Log $logfile = null, $baseFolder = null) {
    If (is_null($logfile)){
        $logfile = new NoLog();
    }
    if($baseFolder != null){
        $whitespaces_for_foldername = preg_replace("/./", " ", $baseFolder);
        $whitespaces_with_3dots = substr($whitespaces_for_foldername, 0, -3)."...";
        $filename_only = substr($filename_or_dir, strlen($baseFolder));
        $filename_for_logging = $whitespaces_with_3dots.$filename_only;
    } else {
        $filename_for_logging = $filename_or_dir;
    }
    $logfile->log_withoutNewline("$filename_for_logging ");

    // Check for files
    if (is_file($filename_or_dir)) {

        // If it is file then remove by
        // using unlink function
        unlink($filename_or_dir);
        $logfile->log("gelöscht");
        return;
    }
    
    // If it is a directory.
    elseif (is_dir($filename_or_dir)) {
        $logfile->log("");
        // Get the list of the files in this
        // directory
        $scan = glob(rtrim($filename_or_dir, '/').'/*');

        // Loop through the list of files
        foreach($scan as $index=>$path) {
            
            // Call recursive function
            deleteAll($path, $logfile, $filename_or_dir);
        }
        
        // Remove the directory itself
        @rmdir($filename_or_dir);
        $logfile->log("$filename_for_logging gelöscht");
        return;
    }
}

?>