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
        $this->import->convertSpiele();

        // assert
        $rows = $this->db->get_results("SELECT * FROM wp_spiel_tobeimported WHERE spielNr = 703", ARRAY_A);
        $this->assertNotEmpty($rows, "Es wurde kein Spiel in der DB gespeichert.");
        
        $this->assertNotNull($rows[0]['importDatum'], "Das Importdatum ist nicht gesetzt.");
        $this->assertEquals(703, $rows[0]['spielNr'], "Die SpielNr stimmt nicht überein.");
        $this->assertEquals($meldung_id, $rows[0]['meldung_id'], "Die Meldung-ID stimmt nicht überein.");
        $this->assertEquals("TuS 82 Opladen III", $rows[0]['gegnerName'], "Der Gegner stimmt nicht überein.");
        $this->assertEquals(new DateTime('2024-09-07 17:00'), $rows[0]['anwurf'], "Der Wochentag stimmt nicht überein.");
        $this->assertEquals("06057", $rows[0]['halle'], "Die Halle stimmt nicht überein.");
        $this->assertFalse($rows[0]['heimspiel'], "Das Spiel ist kein Heimspiel, aber es wurde als solches markiert.");
    }

    // TODO Testen: konvertiert alle Spiele
    // TODO Umgang mit "Spielfrei"
    // TODO Umgang mit "Termin offen"
    // TODO Testen "ungültig" (z.B. kein Anwurf, keine Halle)
    // TODO TEsten: Löscht "verbrauchte" Nuliga-Spiele aus der DB
}