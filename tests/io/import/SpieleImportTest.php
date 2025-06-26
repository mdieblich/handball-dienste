<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/io/import/SpieleImport.php";
require_once __DIR__."/../../../src/io/import/importer.php"; // for the function deleteAll()

require_once __DIR__."/../../db/MemoryDB.php";
require_once __DIR__."/../../db/DBBuilder.php";
require_once __DIR__."/FakeHttpClient.php";

final class SpieleImportTest extends TestCase {

    private MemoryDB $db;
    private DBBuilder $builder;
    private FakeHttpClient $httpClient;
    private Log $logfile;
    private SpieleImport $import;

    public function setUp(): void {

        deleteAll(Webpage::CACHEFILE_BASE_DIRECTORY());

        $this->db = new MemoryDB();
        $this->builder = new DBBuilder($this->db);
        $this->httpClient = new FakeHttpClient();
        $this->logfile = new NoLog();
        $this->import = new SpieleImport($this->db, $this->logfile, $this->httpClient);
    }

    public function test_fetchAllNuligaSpielelisten_laedtEineSeite() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe = 363515;   // Regionsliga Männer
        $team_id = 1986866; // Turnerkreis Nippes 2 (Herren)

        $meisterschaft_id = $this->builder->createMeisterschaft($meisterschaft);
        $mannschaft_id = $this->builder->createMannschaft(2);
        $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            $gruppe,
            $team_id
        );

        $this->httpClient->set(
            NuLiga_SpiellisteTeam::$BASE_URL
                ."teamtable=$team_id&"
                ."pageState=vorrunde&"
                ."championship=".urlencode($meisterschaft)."&"
                ."group=$gruppe",
            "<html>Example-HTML</html>"
        );

        // act
        $files = $this->import->fetchAllNuligaSpielelisten();

        // assert
        $this->assertCount(1, $files);
        $this->assertEquals("<html>Example-HTML</html>", file_get_contents($files[0]), "Die gespeicherte HTML-Datei stimmt nicht mit der erwarteten überein.");
    }
    
    public function test_fetchAllNuligaSpielelisten_laedtMehrereMeldungen() {
        // arrange
        $meisterschaft1 = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe1 = 363515;   // Regionsliga Männer
        $team_id1 = 1986866; // Turnerkreis Nippes 2 (Herren)
        
        $meisterschaft2 = "KR 25/26"; // Köln/Rheinberg 2025/26
        $gruppe2 = 424075;   // Regionsliga Männer
        $team_id2 = 2095123; // Turnerkreis Nippes 2 (Herren)
        
        $meisterschaft_id1 = $this->builder->createMeisterschaft($meisterschaft1);
        $mannschaft_id = $this->builder->createMannschaft(2);
        $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id1,
            $gruppe1,
            $team_id1
        );
        
        $this->httpClient->set(
            NuLiga_SpiellisteTeam::$BASE_URL
            ."teamtable=$team_id1&"
            ."pageState=vorrunde&"
            ."championship=".urlencode($meisterschaft1)."&"
            ."group=$gruppe1",
            "<html>Example-HTML 1</html>"
        );
        
        $meisterschaft_id2 = $this->builder->createMeisterschaft($meisterschaft2);
        $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id2,
            $gruppe2,
            $team_id2
        );
        
        $this->httpClient->set(
            NuLiga_SpiellisteTeam::$BASE_URL
            ."teamtable=$team_id2&"
            ."pageState=vorrunde&"
            ."championship=".urlencode($meisterschaft2)."&"
            ."group=$gruppe2",
            "<html>Example-HTML 2</html>"
        );
        
        // act
        $files = $this->import->fetchAllNuligaSpielelisten();
        
        // assert
        $this->assertCount(2, $files);
        $this->assertEquals("<html>Example-HTML 1</html>", file_get_contents($files[0]), "Die gespeicherte HTML-Datei stimmt nicht mit der erwarteten überein.");
        $this->assertEquals("<html>Example-HTML 2</html>", file_get_contents($files[1]), "Die gespeicherte HTML-Datei stimmt nicht mit der erwarteten überein.");
    }

    public function test_fetchAllNuligaSpielelisten_laedtMehrereMannschaften() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe1 = 363515;   // Regionsliga Männer
        $team_id1 = 1986866; // Turnerkreis Nippes 2 (Herren)

        $gruppe2 = 363729;   // Regionsklasse Männer
        $team_id2 = 1986887; // Turnerkreis Nippes 3 (Herren)

        $meisterschaft_id = $this->builder->createMeisterschaft($meisterschaft);
        $mannschaft_id1 = $this->builder->createMannschaft(2);
        $this->builder->createMannschaftsMeldung(
            $mannschaft_id1,
            $meisterschaft_id,
            $gruppe1,
            $team_id1
        );

        $this->httpClient->set(
            NuLiga_SpiellisteTeam::$BASE_URL
                ."teamtable=$team_id1&"
                ."pageState=vorrunde&"
                ."championship=".urlencode($meisterschaft)."&"
                ."group=$gruppe1",
            "<html>Example-HTML 1</html>"
        );

        $mannschaft_id2 = $this->builder->createMannschaft(3);
        $this->builder->createMannschaftsMeldung(
            $mannschaft_id2,
            $meisterschaft_id,
            $gruppe2,
            $team_id2
        );

        $this->httpClient->set(
            NuLiga_SpiellisteTeam::$BASE_URL
                ."teamtable=$team_id2&"
                ."pageState=vorrunde&"
                ."championship=".urlencode($meisterschaft)."&"
                ."group=$gruppe2",
            "<html>Example-HTML 2</html>"
        );


        // act
        $files = $this->import->fetchAllNuligaSpielelisten();

        // assert
        $this->assertCount(2, $files);
        $this->assertEquals("<html>Example-HTML 1</html>", file_get_contents($files[0]), "Die gespeicherte HTML-Datei stimmt nicht mit der erwarteten überein.");
        $this->assertEquals("<html>Example-HTML 2</html>", file_get_contents($files[1]), "Die gespeicherte HTML-Datei stimmt nicht mit der erwarteten überein.");
    }
    public function test_extractNuligaSpiele_speichertEinSpielKorrektInDB() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe = 363515;   // Regionsliga Männer
        $team_id = 1986866; // Turnerkreis Nippes 2 (Herren)
        $pageGrabber = new NuLiga_SpiellisteTeam(
            $meisterschaft, 
            $gruppe, 
            $team_id,
            $this->logfile,
            $this->httpClient
        );
        $exampleFile = __DIR__."/fixtures/teamtable=$team_id&pageState=vorrunde&championship=".urlencode($meisterschaft)."&group=$gruppe.html";
        $cachedFile = $pageGrabber->getCacheDirectory()."/spiel_1.html";
        copy($exampleFile, $cachedFile);
        $this->assertFileExists( $cachedFile);
        
        // act
        $this->import->extractNuligaSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id", ARRAY_A);
        $this->assertNotEmpty( $rows, "Es wurde kein Spiel in der DB gespeichert.");
        $spiel = $rows[0];
        $this->assertEquals($team_id, $spiel['nuligaTeamID'], "Die nuliga TeamID stimmt nicht überein.");
        $this->assertEquals($gruppe, $spiel['nuligaLigaID'], "Die nuliga LigaID stimmt nicht überein.");
        $this->assertEquals("Sa.", $spiel['wochentag'], "Der Wochentag stimmt nicht überein.");
        $this->assertEquals("Sa.", $spiel['wochentag'], "Der Wochentag stimmt nicht überein.");
        $this->assertEquals("07.09.2024", $spiel['datum'], "Das Datum stimmt nicht überein.");
        $this->assertEquals("17:00", $spiel['uhrzeit'], "Die Uhrzeit stimmt nicht überein.");
        $this->assertEquals("06057", $spiel['halle'], "Die Halle stimmt nicht überein.");
        $this->assertEquals("703", $spiel['spielNr'], "Die SpielNr stimmt nicht überein.");
        $this->assertEquals("TuS 82 Opladen III", $spiel['heimmannschaft'], "Die Heimmannschaft stimmt nicht überein.");
        $this->assertEquals("Turnerkreis Nippes II", $spiel['gastmannschaft'], "Die Gastmannschaft stimmt nicht überein.");
        // restliche Felder wie "ErgebnisOderSchiris" sind egal
    }

    public function test_extractNuligaSpiele_speichertAlleSpiele() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe = 363515;   // Regionsliga Männer
        $team_id = 1986866; // Turnerkreis Nippes 2 (Herren)
        $pageGrabber = new NuLiga_SpiellisteTeam(
            $meisterschaft, 
            $gruppe, 
            $team_id,
            $this->logfile,
            $this->httpClient
        );
        $exampleFile = __DIR__."/fixtures/teamtable=$team_id&pageState=vorrunde&championship=".urlencode($meisterschaft)."&group=$gruppe.html";
        $cachedFile = $pageGrabber->getCacheDirectory()."/spiel_1.html";
        copy($exampleFile, $cachedFile);
        $this->assertFileExists( $cachedFile);
        
        // act
        $this->import->extractNuligaSpiele();

        // assert
        // Es sind 26 Spiele auf der Seite, davon zwei "Spielfrei"
        $rows = $this->db->get_results("SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id", ARRAY_A);
        $this->assertCount( 26, $rows, "Es wurden nicht genug Spiele extrahiert.");
    }
    
    public function test_extractNuligaSpiele_loeschtDateiAusCache() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe = 363515;   // Regionsliga Männer
        $team_id = 1986866; // Turnerkreis Nippes 2 (Herren)
        $pageGrabber = new NuLiga_SpiellisteTeam(
            $meisterschaft, 
            $gruppe, 
            $team_id,
            $this->logfile,
            $this->httpClient
        );
        $exampleFile = __DIR__."/fixtures/teamtable=$team_id&pageState=vorrunde&championship=".urlencode($meisterschaft)."&group=$gruppe.html";
        $cachedFile = $pageGrabber->getCacheDirectory()."/spiel_1.html";
        copy($exampleFile, $cachedFile);
        $this->assertFileExists( $cachedFile);
        
        // act
        $this->import->extractNuligaSpiele();

        // 
        $this->assertFileDoesNotExist( $cachedFile);
    }
    
    public function test_extractNuligaSpiele_speichertSpieleAllerMannschaften() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe1 = 363515;   // Regionsliga Männer
        $team_id1 = 1986866; // Turnerkreis Nippes 2 (Herren)
        $pageGrabber = new NuLiga_SpiellisteTeam(
            $meisterschaft, 
            $gruppe1, 
            $team_id1,
            $this->logfile,
            $this->httpClient
        );
        $exampleFile1 = __DIR__."/fixtures/teamtable=$team_id1&pageState=vorrunde&championship=".urlencode($meisterschaft)."&group=$gruppe1.html";
        $cachedFile1 = $pageGrabber->getCacheDirectory()."/spiel_1.html";
        copy($exampleFile1, $cachedFile1);
        $this->assertFileExists( $cachedFile1);
        
        $gruppe2 = 363729;   // Regionsklasse Männer
        $team_id2 = 1986887; // Turnerkreis Nippes 3 (Herren)
        $pageGrabber = new NuLiga_SpiellisteTeam(
            $meisterschaft, 
            $gruppe2, 
            $team_id2,
            $this->logfile,
            $this->httpClient
        );
        $exampleFile2 = __DIR__."/fixtures/teamtable=$team_id2&pageState=vorrunde&championship=".urlencode($meisterschaft)."&group=$gruppe2.html";
        $cachedFile2 = $pageGrabber->getCacheDirectory()."/spiel_1.html";
        copy($exampleFile2, $cachedFile2);
        $this->assertFileExists( $cachedFile2);
        
        // act
        $this->import->extractNuligaSpiele();

        // assert
        // Es sind 26 Spiele auf der Seite, davon zwei "Spielfrei"
        $rows = $this->db->get_results("SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id1", ARRAY_A);
        $this->assertCount( 26, $rows, "Es wurden nicht genug Spiele für Team 1 extrahiert.");

        // Auch hier: Es sind 26 Spiele auf der Seite, davon zwei "Spielfrei"
        $rows = $this->db->get_results("SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id2", ARRAY_A);
        $this->assertCount( 26, $rows, "Es wurden nicht genug Spiele für Team 2 extrahiert.");
    }

    public function test_convertSpiele_konvertiertEinSpiel(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );

        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Sa.";
        $nuligaSpiel->datum = "07.09.2024";
        $nuligaSpiel->uhrzeit = "17:00";
        $nuligaSpiel->halle = "06057";  
        $nuligaSpiel->spielNr = "703";
        $nuligaSpiel->heimmannschaft = "TuS 82 Opladen III";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE spielNr = 703", ARRAY_A);
        $this->assertNotEmpty($rows, "Es wurde kein Spiel in der DB gespeichert.");
        
        $this->assertNotNull($rows[0]['importDatum'], "Das Importdatum ist nicht gesetzt.");
        $this->assertEquals(703, $rows[0]['spielNr'], "Die SpielNr stimmt nicht überein.");
        $this->assertEquals($meldung_id, $rows[0]['meldung_id'], "Die Meldung-ID stimmt nicht überein.");
        $this->assertEquals("TuS 82 Opladen III", $rows[0]['gegnerName'], "Der Gegner stimmt nicht überein.");
        $this->assertEquals("2024-09-07 17:00:00", $rows[0]['anwurf'], "Der Anwurf stimmt nicht überein.");
        $this->assertEquals("06057", $rows[0]['halle'], "Die Halle stimmt nicht überein.");
        $this->assertFalse($rows[0]['heimspiel'], "Das Spiel ist kein Heimspiel, aber es wurde als solches markiert.");
    }

    public function test_convertSpiele_konvertiertZweiSpiele(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
    
        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Sa.";
        $nuligaSpiel->datum = "07.09.2024";
        $nuligaSpiel->uhrzeit = "17:00";
        $nuligaSpiel->halle = "06057";  
        $nuligaSpiel->spielNr = "703";
        $nuligaSpiel->heimmannschaft = "TuS 82 Opladen III";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);
        
        // Ein zweites Spiel, das konvertiert werden soll
        $nuligaSpiel->datum = "14.09.2024";
        $nuligaSpiel->spielNr = "704";
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE meldung_id = $meldung_id", ARRAY_A);
        $this->assertCount(2, $rows);
    }

    public function test_convertSpiele_ignoriertSpielfrei(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );

        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Sa.";
        $nuligaSpiel->datum = "07.09.2024";
        $nuligaSpiel->uhrzeit = "17:00";
        $nuligaSpiel->halle = "06057";  
        $nuligaSpiel->spielNr = "703";
        $nuligaSpiel->heimmannschaft = "spielfrei";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported", ARRAY_A);
        $this->assertEmpty($rows, "Es hätte kein Spiel angelegt werden dürfen.");
    }
    public function test_convertSpiele_ignoriertOhneHalle(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );

        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Sa.";
        $nuligaSpiel->datum = "07.09.2024";
        $nuligaSpiel->uhrzeit = "17:00";
        $nuligaSpiel->halle = "";   // bewusst keine Halle gesetzt  
        $nuligaSpiel->spielNr = "703";
        $nuligaSpiel->heimmannschaft = "TuS 82 Opladen III";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported", ARRAY_A);
        $this->assertEmpty($rows, "Es hätte kein Spiel angelegt werden dürfen.");
    }
    public function test_convertSpiele_ignoriertOhneSpielNr(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        
        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Sa.";
        $nuligaSpiel->datum = "07.09.2024";
        $nuligaSpiel->uhrzeit = "17:00";
        $nuligaSpiel->halle = "06057";  
        $nuligaSpiel->spielNr = ""; // bewusst keine SpielNr gesetzt
        $nuligaSpiel->heimmannschaft = "TuS 82 Opladen III";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported", ARRAY_A);
        $this->assertEmpty($rows, "Es hätte kein Spiel angelegt werden dürfen.");
    }
    
    public function test_convertSpiele_konvertiertOhneAnwurf(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        
        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Termin offen";
        $nuligaSpiel->datum = "";
        $nuligaSpiel->uhrzeit = "";
        $nuligaSpiel->halle = "06057";  
        $nuligaSpiel->spielNr = "703";
        $nuligaSpiel->heimmannschaft = "TuS 82 Opladen III";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported", ARRAY_A);
        $this->assertNotEmpty($rows, "Es wurde kein Spiel in der DB gespeichert.");
        $this->assertEmpty($rows[0]['anwurf'],  "Der Anwurf sollte leer sein, da es sich um einen Termin offen handelt.");
    }

    public function test_convertSpiele_loeschtNuligaSpiele(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );

        $nuligaSpiel = new NuLigaSpiel();
        $nuligaSpiel->nuligaTeamID = 1986866;
        $nuligaSpiel->nuligaLigaID = 363515;
        $nuligaSpiel->wochentag = "Sa.";
        $nuligaSpiel->datum = "07.09.2024";
        $nuligaSpiel->uhrzeit = "17:00";
        $nuligaSpiel->halle = "06057";  
        $nuligaSpiel->spielNr = "703";
        $nuligaSpiel->heimmannschaft = "TuS 82 Opladen III";
        $nuligaSpiel->gastmannschaft = "Turnerkreis Nippes II";
        $nuligaSpielDao = new NuligaSpielDAO($this->db);
        $nuligaSpielDao->insert($nuligaSpiel);

        // act
        $this->import->convertSpiele("Turnerkreis Nippes");

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_nuligaspiel", ARRAY_A);
        $this->assertEmpty($rows);
    }
    
    public function test_sucheGegner_findetEinenGegner(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id      );

        $spiel = new Spiel_toBeImported();
        $spiel-> spielNr = 703;
        $spiel->meldung_id = $meldung_id;
        $spiel->gegnerName = "TuS 82 Opladen III";
        $spiel->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel->halle = "06057";
        $spiel->heimspiel = false; // Es ist kein Heimspiel
        $spielDAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_id = $spielDAO->insert($spiel);

        // act
        $this->import->sucheGegner();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id", ARRAY_A);
        $this->assertEquals($gegner_id, $rows[0]['gegner_id'], "Der Gegner wurde nicht korrekt gefunden.");
    }
    
    public function test_sucheGegner_findetGegnerFuerMehrereSpiele(){
        // arrange
        $spielDAO = new Spiel_toBeImportedDAO($this->db);

        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );

        $gegner_id1 = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id      );
        $spiel1 = new Spiel_toBeImported();
        $spiel1-> spielNr = 703;
        $spiel1->meldung_id = $meldung_id;
        $spiel1->gegnerName = "TuS 82 Opladen III";
        $spiel1->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel1->halle = "06057";
        $spiel1->heimspiel = false;
        $spiel_id1 = $spielDAO->insert($spiel1);

        $gegner_id2 = $this->builder->createGegner("1. FSV Köln 1899",1,$meldung_id      );
        $spiel2 = new Spiel_toBeImported();
        $spiel2->spielNr = 710;
        $spiel2->meldung_id = $meldung_id;
        $spiel2->gegnerName = "1. FSV Köln 1899";
        $spiel2->anwurf = new DateTime("2024-09-14 15:30:00");
        $spiel2->halle = "06078";
        $spiel2->heimspiel = true;
        $spiel_id2 = $spielDAO->insert($spiel2);

        // act
        $this->import->sucheGegner();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id1", ARRAY_A);
        $this->assertEquals($gegner_id1, $rows[0]['gegner_id'], "Der 1. Gegner wurde nicht korrekt gefunden.");

        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id2", ARRAY_A);
        $this->assertEquals($gegner_id2, $rows[0]['gegner_id'], "Der 2. Gegner wurde nicht korrekt gefunden.");
    }

    
    public function test_sucheGegner_gleicherGegnernameUnterschiedlicheLigen(){
        // arrange
        $spielDAO = new Spiel_toBeImportedDAO($this->db);

        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id1 = $this->builder->createMannschaft(2);
        $meldung_id1 = $this->builder->createMannschaftsMeldung(
            $mannschaft_id1,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );

        $spiel1 = new Spiel_toBeImported();
        $spiel1-> spielNr = 703;
        $spiel1->meldung_id = $meldung_id1;
        $spiel1->gegnerName = "TuS 82 Opladen III";
        $spiel1->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel1->halle = "06057";
        $spiel1->heimspiel = false;
        $spiel_id1 = $spielDAO->insert($spiel1);
        
        $mannschaft_id2 = $this->builder->createMannschaft(2, 'w');
        $meldung_id2 = $this->builder->createMannschaftsMeldung(
            $mannschaft_id2,
            $meisterschaft_id,
            333333, // irgendwas anderes
            1919191 // irgendwas anderes
        );
        
        $spiel2 = new Spiel_toBeImported();
        $spiel2->spielNr = 710;
        $spiel2->meldung_id = $meldung_id2;
        $spiel2->gegnerName = "TuS 82 Opladen III";
        $spiel2->anwurf = new DateTime("2024-09-14 15:30:00");
        $spiel2->halle = "06078";
        $spiel2->heimspiel = true;
        $spiel_id2 = $spielDAO->insert($spiel2);
        
        // Die Gegner werden in "falsch" Reihenfolge erstellt, damit im Test nicht zufällig der richtige Geggner gewählt wird.
        $gegner_id2 = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id2);
        $gegner_id1 = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id1);
        // act
        $this->import->sucheGegner();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id1", ARRAY_A);
        $this->assertEquals($gegner_id1, $rows[0]['gegner_id'], "Der 1. Gegner wurde nicht korrekt gefunden.");

        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id2", ARRAY_A);
        $this->assertEquals($gegner_id2, $rows[0]['gegner_id'], "Der 2. Gegner wurde nicht korrekt gefunden.");
    }
    
    public function test_sucheGegner_loeschtSpieleOhneGegner(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        // Es wird kein Gegner erstellt, damit das Spiel keinen Gegner findet
        //$gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id      );

        $spiel = new Spiel_toBeImported();
        $spiel-> spielNr = 703;
        $spiel->meldung_id = $meldung_id;
        $spiel->gegnerName = "TuS 82 Opladen III";
        $spiel->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel->halle = "06057";
        $spiel->heimspiel = false;
        $spielDAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_id = $spielDAO->insert($spiel);

        // act
        $this->import->sucheGegner();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id", ARRAY_A);
        $this->assertEmpty($rows);
    }

    public function test_findExistingSpiele_findetIdentischesSpiel() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel_toBeImported->halle = "06057";
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->findExistingSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertEquals($spiel_id, $rows[0]['spielID_alt'], "Die Spiel-ID sollte mit der ID des bereits existierenden Spiels übereinstimmen.");
        $this->assertFalse($rows[0]['istNeuesSpiel'], "Das Spiel sollte als bereits existierend markiert sein.");
    }
    public function test_findExistingSpiele_findetSpielmitAnderemDatum() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00"); // anderes Datum+Uhrzeit
        $spiel_toBeImported->halle = "06057";
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->findExistingSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertEquals($spiel_id, $rows[0]['spielID_alt'], "Die Spiel-ID sollte mit der ID des bereits existierenden Spiels übereinstimmen.");
        $this->assertFalse($rows[0]['istNeuesSpiel'], "Das Spiel sollte als bereits existierend markiert sein.");
    }
    public function test_findExistingSpiele_findetSpielmitAndererHalle() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel_toBeImported->halle = "12345"; // andere Halle
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->findExistingSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertEquals($spiel_id, $rows[0]['spielID_alt'], "Die Spiel-ID sollte mit der ID des bereits existierenden Spiels übereinstimmen.");
        $this->assertFalse($rows[0]['istNeuesSpiel'], "Das Spiel sollte als bereits existierend markiert sein.");
    }

    public function test_findExistingSpiele_findetSpielmitAndererHalleUndTauschHeimrecht() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel_toBeImported->halle = "12345"; // andere Halle
        $spiel_toBeImported->heimspiel = true;  // die andere Halle ist auch noch eine Heimhalle
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->findExistingSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertEquals($spiel_id, $rows[0]['spielID_alt'], "Die Spiel-ID sollte mit der ID des bereits existierenden Spiels übereinstimmen.");
        $this->assertFalse($rows[0]['istNeuesSpiel'], "Das Spiel sollte als bereits existierend markiert sein.");
    }
    public function test_findExistingSpiele_findetSpielUnterMehreren() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        {
            // die folgenden Spiele sollten nicht gefunden werden
            $this->builder->createSpiel(
                705, // andere SpielNr
                $meldung_id, 
                $gegner_id, 
                new DateTime("2024-09-07 17:00:00"), 
                "06057",
                false,
            );
            $this->builder->createSpiel(
                705, 
                $meldung_id, 
                $gegner_id+3, // anderer Gegner
                new DateTime("2024-09-07 17:00:00"), 
                "06057",
                false,
            );
            $this->builder->createSpiel(
                703, 
                $meldung_id+1, // andere Liga
                $gegner_id, 
                new DateTime("2024-09-07 17:00:00"), 
                "06057",
                false,
            );
        }

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel_toBeImported->halle = "06057";
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->findExistingSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertEquals($spiel_id, $rows[0]['spielID_alt'], "Die Spiel-ID sollte mit der ID des bereits existierenden Spiels übereinstimmen.");
        $this->assertFalse($rows[0]['istNeuesSpiel'], "Das Spiel sollte als bereits existierend markiert sein.");
    }
    public function test_findExistingSpiele_markiertNeueSpiele() {
        
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        // Das eigentliche Spiel gibt es noch nicht in der DB

        {
            // die folgenden Spiele sollten nicht gefunden werden
            $this->builder->createSpiel(
                705, // andere SpielNr
                $meldung_id, 
                $gegner_id, 
                new DateTime("2024-09-07 17:00:00"), 
                "06057",
                false,
            );
            $this->builder->createSpiel(
                705, 
                $meldung_id, 
                $gegner_id+3, // anderer Gegner
                new DateTime("2024-09-07 17:00:00"), 
                "06057",
                false,
            );
            $this->builder->createSpiel(
                703, 
                $meldung_id+1, // andere Liga
                $gegner_id, 
                new DateTime("2024-09-07 17:00:00"), 
                "06057",
                false,
            );
        }

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-07 17:00:00");
        $spiel_toBeImported->halle = "06057";
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->findExistingSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertNull($rows[0]['spielID_alt'], "Es sollte keine alte Spiel-ID geben, da das Spiel noch nicht existiert.");
        $this->assertTrue($rows[0]['istNeuesSpiel'], "Das Spiel sollte als bereits existierend markiert sein.");
    }

    public function test_updateSpiele_aktualisiertSpiele(){   
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00"); // anderes Datum+Uhrzeit
        $spiel_toBeImported->halle = "06058";   // andere Halle
        $spiel_toBeImported->heimspiel = true;  // ab jetzt Heimspiel
        $spiel_toBeImported->istNeuesSpiel = false;
        $spiel_toBeImported->spielID_alt = $spiel_id;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->updateSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel WHERE id = $spiel_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das Spiel ist nicht mehr in der Datenbank...?");
        $this->assertEquals("2024-09-08 20:00:00", $rows[0]['anwurf'], "Der Anwurf sollte aktualisiert worden sein.");
        $this->assertEquals("06058", $rows[0]['halle'], "Die Halle sollte aktualisiert worden sein.");
        $this->assertEquals(true, $rows[0]['heimspiel'], "Das Heimrecht sollte aktualisiert worden sein.");
    }
    public function test_updateSpiele_setztDienstaenderungsplan(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );
        $dienst1 = $this->builder->createDienst($spiel_id,Dienstart::ZEITNEHMER);
        $dienst2 = $this->builder->createDienst($spiel_id,Dienstart::SEKRETAER);
        $dienst3 = $this->builder->createDienst($spiel_id,Dienstart::CATERING);

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00"); // anderes Datum+Uhrzeit
        $spiel_toBeImported->halle = "06058";   // andere Halle
        $spiel_toBeImported->heimspiel = true;  // ab jetzt Heimspiel
        $spiel_toBeImported->istNeuesSpiel = false;
        $spiel_toBeImported->spielID_alt = $spiel_id;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->updateSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_dienstaenderung WHERE id in ($dienst1, $dienst2, $dienst3)", ARRAY_A);
        $this->assertCount(3, $rows, "Es sollten 3 Dienständerungen für das Spiel existieren.");
        foreach ($rows as $row) {
            $this->assertEquals(false, $row['istNeu'], "Die Dienständerung sollte nicht als neuer Dienst markiert sein.");
            $this->assertEquals(false, $row['entfaellt'], "Die Dienständerung sollte nicht als entfernter Dienst markiert sein.");    
            $this->assertEquals("2024-09-07 17:00:00", $row['anwurfVorher'], "Der vorherige Anwurf sollte gespeichert sein.");
            $this->assertEquals("06057", $row['halleVorher'], "Die vorherige Halle sollte gespeichert sein.");
        }
    }
    public function test_updateSpiele_raeumtAuf(){
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id1 = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id);
        $spiel_id = $this->builder->createSpiel(
            703, 
            $meldung_id, 
            $gegner_id1, 
            new DateTime("2024-09-07 17:00:00"), 
            "06057",
            false,
        );

        $spiel_toBeImported_asUpdate = new Spiel_toBeImported();
        $spiel_toBeImported_asUpdate->spielNr = 703;
        $spiel_toBeImported_asUpdate->meldung_id = $meldung_id;
        $spiel_toBeImported_asUpdate->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported_asUpdate->gegner_id = $gegner_id1;
        $spiel_toBeImported_asUpdate->anwurf = new DateTime("2024-09-08 20:00:00");
        $spiel_toBeImported_asUpdate->halle = "06058"; 
        $spiel_toBeImported_asUpdate->heimspiel = true;
        $spiel_toBeImported_asUpdate->istNeuesSpiel = false;
        $spiel_toBeImported_asUpdate->spielID_alt = $spiel_id;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $updateSpiel_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported_asUpdate);

        // Ein zweites Spiel, welches komplett neu ist
        $gegner_id2 = $this->builder->createGegner("TuS 82 Opladen",1,$meldung_id);
        $spiel_toBeImported_asNewOne = new Spiel_toBeImported();
        $spiel_toBeImported_asNewOne->spielNr = 709;
        $spiel_toBeImported_asNewOne->meldung_id = $meldung_id;
        $spiel_toBeImported_asNewOne->gegnerName = "TuS 82 Opladen I";
        $spiel_toBeImported_asNewOne->gegner_id = $gegner_id2;
        $spiel_toBeImported_asNewOne->anwurf = new DateTime("2024-09-20 20:00:00");
        $spiel_toBeImported_asNewOne->halle = "666666";   
        $spiel_toBeImported_asNewOne->heimspiel = true; 
        $spiel_toBeImported_asNewOne->istNeuesSpiel = true; // NEUES Spiel
        // $spiel_toBeImported_asNewOne->spielID_alt = null;  // KEIN Spiel welches schon existierte
        $newSpiel_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported_asUpdate);

        // act
        $this->import->updateSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $updateSpiel_id", ARRAY_A);
        $this->assertEmpty($rows, "Das Import-Spiel hätte gelöscht werden sollen");
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE id = $newSpiel_id", ARRAY_A);
        $this->assertNotEmpty($rows, "Das neue Import-Spiel hätte nicht gelöscht werden dürfen");    
    }
}