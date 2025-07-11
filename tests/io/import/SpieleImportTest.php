<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/io/import/SpieleImport.php";
require_once __DIR__."/../../../src/io/import/importer.php"; // for the function deleteAll()

require_once __DIR__."/../../db/MemoryDB.php";
require_once __DIR__."/../../db/DBBuilder.php";
require_once __DIR__."/../../log/EchoLog.php";
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
        $this->logfile = new EchoLog();
        $this->import = new SpieleImport($this->db, $this->logfile, $this->httpClient);
    }

    public function fetchOneWithAssert(string $query): array{      
        return $this->fetchAllWithAssert(1, $query)[0];
    }
    public function fetchAllWithAssert(int $count, string $query): array{        
        $rows = $this->db->get_results($query, ARRAY_A);
        $this->assertCount( $count, $rows, "Falsche Anzahl für $query");
        return $rows;
    }

    public const NOT_NULL = "SpieleImportTest.php NOT NULL";
    public const NULL = "SpieleImportTest.php NULL";

    public function assertObjectInDB(string $query, array $values): void {
        $objectInDB = $this->fetchOneWithAssert($query);
        foreach($values as $key => $value) {
            if($value === self::NOT_NULL) {
                $this->assertNotNull($objectInDB[$key],"$key hätte nicht null sein dürfen, Query: $query");
            } else if($value === self::NULL) {
                $this->assertNull($objectInDB[$key],"$key hätte nicht null sein dürfen. Query: $query");
            } else {
                $this->assertEquals($value, $objectInDB[$key],"$key ist falsch. Query: $query");
            }
        }
    }

    public function assertNotInDB(String $query): void {
        $rows = $this->db->get_results($query, ARRAY_A);
        $this->assertEmpty($rows, "Es hätte nix da sein dürfen für $query");
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
        $this->assertObjectInDB("SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id AND spielNr=703", [
            'nuligaTeamID'   => $team_id,
            'nuligaLigaID'   => $gruppe,
            'wochentag'      => "Sa.",
            'datum'          => "07.09.2024",
            'uhrzeit'        => "17:00",
            'halle'          => "06057",
            'spielNr'        => "703",
            'heimmannschaft' => "TuS 82 Opladen III",
            'gastmannschaft' => "Turnerkreis Nippes II"
        ]);
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
        $this->fetchAllWithAssert(26,"SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id");
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
        $this->fetchAllWithAssert(26, "SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id1");

        // Auch hier: Es sind 26 Spiele auf der Seite, davon zwei "Spielfrei"
        $this->fetchAllWithAssert(26, "SELECT * FROM wp_nuligaspiel WHERE nuligaTeamID = $team_id2");
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE spielNr = 703", [
            'importDatum' => self::NOT_NULL,
            'spielNr'     => 703,
            'meldung_id'  =>  $meldung_id,
            'gegnerName'  => "TuS 82 Opladen III",
            'anwurf'      => '2024-09-07 17:00:00',
            'halle'       => '06057',
            'heimspiel'   => false
        ]);
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
        $this->fetchAllWithAssert(2, "SELECT * FROM wp_spiel_tobeimported WHERE meldung_id = $meldung_id");
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
        $this->assertNotInDB("SELECT * FROM wp_spiel_tobeimported");
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
        $this->assertNotInDB("SELECT * FROM wp_spiel_tobeimported");
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
        $this->assertNotInDB("SELECT * FROM wp_spiel_tobeimported");
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported", [
            'anwurf' => self::NULL
        ]);
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
        $this->assertNotInDB("SELECT * FROM wp_nuligaspiel");
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
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id, true);

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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id",[
            'gegner_id'                         => $gegner_id,
            'gegnerStelltSekretaerBeiHeimspiel' => true
        ]);
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id1",[
            'gegner_id' => $gegner_id1
        ]);

        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id2",[
            'gegner_id' => $gegner_id2
        ]);
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id1",[
            'gegner_id' => $gegner_id1
        ]);

        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id2",[
            'gegner_id' => $gegner_id2
        ]);
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
        $this->assertNotInDB("SELECT * FROM wp_spiel_tobeimported WHERE id=$spiel_id");
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id",[
            'spielID_alt'   => $spiel_id,
            'istNeuesSpiel' => false
        ]);
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id",[
            'spielID_alt'   => $spiel_id,
            'istNeuesSpiel' => false
        ]);
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id",[
            'spielID_alt'   => $spiel_id,
            'istNeuesSpiel' => false
        ]);
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id",[
            'spielID_alt'   => $spiel_id,
            'istNeuesSpiel' => false
        ]);
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
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id",[
            'spielID_alt'   => $spiel_id,
            'istNeuesSpiel' => false
        ]);
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

        // 
        $this->assertObjectInDb("SELECT * FROM wp_spiel_tobeimported WHERE id = $spiel_toBeImported_id",[
            'spielID_alt'   => self::NULL,
            'istNeuesSpiel' => true
        ]);
    }
    public function test_createDienstAenderungen_setztDienstaenderungsplan(){
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
        $this->import->createDienstAenderungen();

        // assert
        $dienstAenderungen = $this->fetchAllWithAssert(3, "SELECT * FROM wp_dienstaenderung WHERE dienst_id in ($dienst1, $dienst2, $dienst3)");
        foreach ($dienstAenderungen as $dienstAenderung) {
            $this->assertEquals("2024-09-07 17:00:00", $dienstAenderung['anwurfVorher'], "Der vorherige Anwurf sollte gespeichert sein.");
            $this->assertEquals("06057", $dienstAenderung['halleVorher'], "Die vorherige Halle sollte gespeichert sein.");
        }
    }
    public function test_createDienstAenderungen_erstelltNixDoppelt(){
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
        $this->import->createDienstAenderungen();
        $this->import->createDienstAenderungen();   // Zweite Ausführung

        // assert
        $this->fetchAllWithAssert(3, "SELECT * FROM wp_dienstaenderung WHERE dienst_id in ($dienst1, $dienst2, $dienst3)");
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
        $spiel_toBeImported->dienstAenderungenErstellt = true;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->updateSpiele();

        // assert
        $this->assertObjectInDb("SELECT * FROM wp_spiel WHERE id = $spiel_id", [
            'anwurf'    => "2024-09-08 20:00:00",
            'halle'     => "06058",
            'heimspiel' => true
        ]);
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
        $spiel_toBeImported_asUpdate->dienstAenderungenErstellt = true;
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
        $newSpiel_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported_asNewOne);

        // act
        $this->import->updateSpiele();

        // assert
        $this->assertNotInDB("SELECT * FROM wp_spiel_tobeimported WHERE id = $updateSpiel_id");
        $this->fetchOneWithAssert("SELECT * FROM wp_spiel_tobeimported WHERE id = $newSpiel_id");    
    }
    public function test_createNeueSpiele_erstelltNeuesSpiel() {
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

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->gegnerStelltSekretaerBeiHeimspiel = false;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00");
        $spiel_toBeImported->halle = "06058";
        $spiel_toBeImported->heimspiel = true;
        $spiel_toBeImported->istNeuesSpiel = true;
        $spiel_toBeImported->spielID_alt = null;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->createNeueSpiele();

        // assert
        $this->assertObjectInDb("SELECT * FROM wp_spiel WHERE spielNr=703", [
            'spielNr'               => 703,
            'mannschaftsMeldung_id' => $meldung_id,
            'gegner_id'             => $gegner_id,
            'anwurf'                => "2024-09-08 20:00:00",
            'halle'                 => "06058",
            'heimspiel'             => true
        ]);
    }
    public function test_createNeueSpiele_erstelltDiensteFuerHeimspiel() {
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

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->gegnerStelltSekretaerBeiHeimspiel = false;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00");
        $spiel_toBeImported->halle = "06058";
        $spiel_toBeImported->heimspiel = true;
        $spiel_toBeImported->istNeuesSpiel = true;
        $spiel_toBeImported->spielID_alt = null;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->createNeueSpiele();

        // assert
        $spiel = $this->fetchOneWithAssert("SELECT * FROM wp_spiel WHERE spielNr=703");
        $spiel_id = $spiel['id'];
        // Die Dienste sind alphabetisch sortiert, also...:
        [$catering, $zeitnehmer] = $this->fetchAllWithAssert(2, "SELECT * FROM wp_dienst WHERE spiel_id=$spiel_id ORDER BY dienstart");
        $this->assertEquals(Dienstart::CATERING, $catering['dienstart'], "Erster Dienst sollte Catering sein");
        $this->assertEquals(Dienstart::ZEITNEHMER, $zeitnehmer['dienstart'], "Zweiter Dienst sollte Zeitnehmer sein");
    }
    public function test_createNeueSpiele_erstelltDiensteFuerAuswaertsspiel() {
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

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->gegnerStelltSekretaerBeiHeimspiel = false;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00");
        $spiel_toBeImported->halle = "06058";
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported->istNeuesSpiel = true;
        $spiel_toBeImported->spielID_alt = null;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->createNeueSpiele();

        // assert
        
        $spiel = $this->fetchOneWithAssert("SELECT * FROM wp_spiel WHERE spielNr=703");
        $spiel_id = $spiel['id'];
        $sekretaer = $this->fetchOneWithAssert( "SELECT * FROM wp_dienst WHERE spiel_id=$spiel_id ORDER BY dienstart");
        $this->assertEquals(Dienstart::SEKRETAER, $sekretaer['dienstart'], "Der Dienst sollte Sekretär sein");
    }  
    public function test_createNeueSpiele_erstelltDiensteFuerHeimspielMitSekretaer() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id, true);
        
        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->gegnerStelltSekretaerBeiHeimspiel = true;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00");
        $spiel_toBeImported->halle = "06058";
        $spiel_toBeImported->heimspiel = true;
        $spiel_toBeImported->istNeuesSpiel = true;
        $spiel_toBeImported->spielID_alt = null;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);
        
        // act
        $this->import->createNeueSpiele();
        
        // assert
        $spiel = $this->fetchOneWithAssert("SELECT * FROM wp_spiel WHERE spielNr=703");
        $spiel_id = $spiel['id'];
        // Die Dienste sind alphabetisch sortiert, also...:
        [$catering, $sekretaer, $zeitnehmer] = $this->fetchAllWithAssert(3, "SELECT * FROM wp_dienst WHERE spiel_id=$spiel_id ORDER BY dienstart");
        $this->assertEquals(Dienstart::CATERING, $catering['dienstart'], "Erster Dienst sollte Catering sein");
        $this->assertEquals(Dienstart::SEKRETAER, $sekretaer['dienstart'], "Zweiter Dienst sollte Sekretär sein");
        $this->assertEquals(Dienstart::ZEITNEHMER, $zeitnehmer['dienstart'], "Dritter Dienst sollte Zeitnehmer sein");
    }
    public function test_createNeueSpiele_erstelltKeineDiensteFuerAuswaertsspiel() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $gegner_id = $this->builder->createGegner("TuS 82 Opladen",3,$meldung_id, true);

        $spiel_toBeImported = new Spiel_toBeImported();
        $spiel_toBeImported->spielNr = 703;
        $spiel_toBeImported->meldung_id = $meldung_id;
        $spiel_toBeImported->gegnerName = "TuS 82 Opladen III";
        $spiel_toBeImported->gegner_id = $gegner_id;
        $spiel_toBeImported->gegnerStelltSekretaerBeiHeimspiel = true;
        $spiel_toBeImported->anwurf = new DateTime("2024-09-08 20:00:00");
        $spiel_toBeImported->halle = "06058";
        $spiel_toBeImported->heimspiel = false;
        $spiel_toBeImported->istNeuesSpiel = true;
        $spiel_toBeImported->spielID_alt = null;
        $spiel_toBeImported_DAO = new Spiel_toBeImportedDAO($this->db);
        $spiel_toBeImported_DAO->insert($spiel_toBeImported);

        // act
        $this->import->createNeueSpiele();

        // assert
        $spiel_id = $this->db->get_var("Select id from wp_spiel where spielNr=703");
        $rows = $this->db->get_results("SELECT * FROM wp_dienst WHERE spiel_id=$spiel_id ORDER BY dienstart", ARRAY_A);
        $this->assertCount(0, $rows, "Es sollten keine Dienst da sein");
    }
    public function test_createNeueSpiele_raeumtAuf() {
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
        $spiel_toBeImported_asUpdate->gegnerStelltSekretaerBeiHeimspiel = false;
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
        $spiel_toBeImported_asNewOne->gegnerStelltSekretaerBeiHeimspiel = false;
        $spiel_toBeImported_asNewOne->anwurf = new DateTime("2024-09-20 20:00:00");
        $spiel_toBeImported_asNewOne->halle = "666666";   
        $spiel_toBeImported_asNewOne->heimspiel = true; 
        $spiel_toBeImported_asNewOne->istNeuesSpiel = true; // NEUES Spiel
        // $spiel_toBeImported_asNewOne->spielID_alt = null;  // KEIN Spiel welches schon existierte
        $newSpiel_id = $spiel_toBeImported_DAO->insert($spiel_toBeImported_asNewOne);

        // act
        $this->import->createNeueSpiele();

        // assert
        $this->fetchOneWithAssert("SELECT * FROM wp_spiel_tobeimported WHERE id = $updateSpiel_id");
        $this->assertNotInDB("SELECT * FROM wp_spiel_tobeimported WHERE id = $newSpiel_id");   
    }
    public function test_organisiereAufUndAbbau_erstelltAufUndAbbauBeiNeuemTag() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $spieltag = "2024-09-07";
        $spiel_id = $this->builder->createSpiel(100, $meldung_id, 200, new DateTime("$spieltag 17:00:00"), "0815", true, $mannschaft_id );

        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        // alphabetisch sortierte Dienste
        [$abbau, $aufbau] = $this->fetchAllWithAssert(2, "SELECT * FROM wp_dienst WHERE spiel_id = $spiel_id ORDER BY dienstart");
        $this->assertEquals(Dienstart::AUFBAU, $aufbau['dienstart'], "Aufbau nicht gefunden");
        $this->assertEquals($mannschaft_id, $aufbau['mannschaft_id'], "Aufbau wurde nicht der entsprechenden Mannschaft zugewiesen");
        $this->assertEquals(Dienstart::ABBAU, $abbau['dienstart'], "Abbau nicht gefunden");
        $this->assertEquals($mannschaft_id, $abbau['mannschaft_id'], "Abbau wurde nicht der entsprechenden Mannschaft zugewiesen");
    }
    public function test_organisiereAufUndAbbau_erstelltAufUndAbbauFuerUnterschiedlicheSpiele() {
        // arrange
        $spieltag = "2024-09-07";
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id1 = $this->builder->createMannschaft(2);
        $meldung_id1 = $this->builder->createMannschaftsMeldung(
            $mannschaft_id1,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $anwurf1 = new DateTime("$spieltag 17:00:00");
        $spiel_id1 = $this->builder->createSpiel(100, $meldung_id1, 200, $anwurf1, "0815", true, $mannschaft_id1);
        
        $mannschaft_id2 = $this->builder->createMannschaft(3);
        $meldung_id2 = $this->builder->createMannschaftsMeldung(
            $mannschaft_id2,
            $meisterschaft_id,
            364515, // irgendwas anderes
            1987866 // irgendwas anderes
        );
        $anwurf2 = new DateTime("$spieltag 19:00:00");
        $spiel_id2 = $this->builder->createSpiel(100, $meldung_id2, 200, $anwurf2,  "0815", true, $mannschaft_id2);

        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        $this->assertObjectInDb("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_id1", [
            'dienstart'     => Dienstart::AUFBAU,
            'mannschaft_id' => $mannschaft_id1
        ]);
        $this->assertObjectInDb("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_id2", [
            'dienstart'     => Dienstart::ABBAU,
            'mannschaft_id' => $mannschaft_id2
        ]);
    }
    public function test_organisiereAufUndAbbau_keineAenderungWennDienstSchonVorhanden() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $spieltag = "2024-09-07";
        $spiel_id = $this->builder->createSpiel(100, $meldung_id, 200, new DateTime("$spieltag 17:00:00"), "0815", true);
        $aufbau_id = $this->builder->createDienst($spiel_id, Dienstart::AUFBAU, $mannschaft_id);
        $abbau_id = $this->builder->createDienst($spiel_id, Dienstart::ABBAU, $mannschaft_id);

        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        // alphabetisch sortierte Dienste
        [$abbau, $aufbau] = $this->fetchAllWithAssert(2, "SELECT * FROM wp_dienst WHERE spiel_id = $spiel_id ORDER BY dienstart");
        $this->assertEquals($aufbau_id, $aufbau['id'], "Aufbau-Dienst hätte gleich bleiben müssen.");
        $this->assertEquals($abbau_id, $abbau['id'], "Abbau-Dienst hätte gleich bleiben müssen.");
    }
    public function test_organisiereAufUndAbbau_erstelltNixFuerOffeneTermine() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $spiel_id = $this->builder->createSpiel(100, $meldung_id, 200, null, "0815", true);

        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        $this->assertNotInDB("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_id ORDER BY dienstart");
    }
    public function test_organisiereAufUndAbbau_loeschtAufUndAbbauFuerOffeneTermine() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $spiel_id = $this->builder->createSpiel(100, $meldung_id, 200, null, "0815", true, $mannschaft_id);
        $aufbau_id = $this->builder->createDienst($spiel_id, Dienstart::AUFBAU, $mannschaft_id);
        $abbau_id = $this->builder->createDienst($spiel_id, Dienstart::ABBAU, $mannschaft_id);

        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        $this->assertNotInDB("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_id ORDER BY dienstart");
    }
    public function test_organisiereAufUndAbbau_AufUndAbbauVerschoben() {
        // arrange
        $spieltag = "2024-09-07";
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_frueh_id = $this->builder->createMannschaft(2);
        $meldung_frueh_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_frueh_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $mannschaft_spaet_id = $this->builder->createMannschaft(3);
        $meldung_spaet_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_spaet_id,
            $meisterschaft_id,
            364515, // irgendwas anderes
            1987866 // irgendwas anderes
        );

        // Verkehrte Welt: Das frühere Spiel hat den Abbau...
        $anwurf_frueh = new DateTime("$spieltag 17:00:00");
        $spiel_frueh_id = $this->builder->createSpiel(100, $meldung_frueh_id, 200, $anwurf_frueh, "0815", true, $mannschaft_frueh_id);
        $abbau_vorher_id = $this->builder->createDienst($spiel_frueh_id, Dienstart::ABBAU, $mannschaft_frueh_id);
        // ... und das spätere Spiel den Aufbau.
        $anwurf_spaet = new DateTime("$spieltag 19:00:00");
        $spiel_spaet_id = $this->builder->createSpiel(100, $meldung_spaet_id, 200, $anwurf_spaet,  "0815", true, $mannschaft_spaet_id);
        $aufbau_vorher_id = $this->builder->createDienst($spiel_spaet_id, Dienstart::AUFBAU, $mannschaft_spaet_id);
        
        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        // Der frühere Spiel sollte jetzt den Aufbau haben
        $aufbau_nachher = $this->fetchOneWithAssert("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_frueh_id");
        $this->assertEquals(Dienstart::AUFBAU, $aufbau_nachher['dienstart'], "Aufbau nicht gefunden");
        $this->assertNotEquals($aufbau_vorher_id, $aufbau_nachher["id"],"der alte Dienst sollte entfallen");
        
        $abbau_nachher = $this->fetchOneWithAssert("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_spaet_id");
        $this->assertEquals(Dienstart::ABBAU, $abbau_nachher['dienstart'], "Abbau nicht gefunden");
        $this->assertNotEquals($abbau_vorher_id, $abbau_nachher["id"],"der alte Dienst sollte entfallen");
    }
    public function test_organisiereAufUndAbbau_NeuesErstesSpiel_DienstAenderungsplan() {
        // arrange
        $spieltag = "2024-09-07";
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_frueh_id = $this->builder->createMannschaft(2);
        $meldung_frueh_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_frueh_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $mannschaft_spaet_id = $this->builder->createMannschaft(3);
        $meldung_spaet_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_spaet_id,
            $meisterschaft_id,
            364515, // irgendwas anderes
            1987866 // irgendwas anderes
        );

        
        $anwurf_frueh = new DateTime("$spieltag 17:00:00");
        $spiel_frueh_id = $this->builder->createSpiel(100, $meldung_frueh_id, 200, $anwurf_frueh, "0815", true, $mannschaft_frueh_id);
        
        $anwurf_spaet = new DateTime("$spieltag 19:00:00");
        $spiel_spaet_id = $this->builder->createSpiel(100, $meldung_spaet_id, 200, $anwurf_spaet,  "0815", true, $mannschaft_spaet_id);
        $aufbau_vorher_id = $this->builder->createDienst($spiel_spaet_id, Dienstart::AUFBAU, $mannschaft_spaet_id);
        
        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        $aufbau_nachher = $this->fetchOneWithAssert("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_frueh_id");
        $this->assertEquals(Dienstart::AUFBAU, $aufbau_nachher['dienstart'], "Aufbau nicht gefunden");
        $this->assertObjectInDb("SELECT * FROM wp_neuerdienst WHERE dienst_id = {$aufbau_nachher['id']}", [
            'grund' => self::NOT_NULL
        ]);

        // Nun prüfen ob der Dienständerungsplan auch beim späten Spiel gesetzt ist:
        $this->assertObjectInDb("SELECT * FROM wp_entfallenerdienst WHERE spiel_id = $spiel_spaet_id", [
            'dienstart'     => Dienstart::AUFBAU,
            'mannschaft_id' => $mannschaft_spaet_id,
            'grund'         => self::NOT_NULL
        ]);
    }
    public function test_organisiereAufUndAbbau_AufUndAbbauVerschoben_DienstAenderungsplan() {
        // arrange
        $spieltag = "2024-09-07";
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_frueh_id = $this->builder->createMannschaft(2);
        $meldung_frueh_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_frueh_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $mannschaft_spaet_id = $this->builder->createMannschaft(3);
        $meldung_spaet_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_spaet_id,
            $meisterschaft_id,
            364515, // irgendwas anderes
            1987866 // irgendwas anderes
        );

        // Verkehrte Welt: Das frühere Spiel hat den Abbau...
        $anwurf_frueh = new DateTime("$spieltag 17:00:00");
        $spiel_frueh_id = $this->builder->createSpiel(100, $meldung_frueh_id, 200, $anwurf_frueh, "0815", true, $mannschaft_frueh_id);
        $abbau_vorher_id = $this->builder->createDienst($spiel_frueh_id, Dienstart::ABBAU, $mannschaft_frueh_id);
        // ... und das spätere Spiel den Aufbau.
        $anwurf_spaet = new DateTime("$spieltag 19:00:00");
        $spiel_spaet_id = $this->builder->createSpiel(100, $meldung_spaet_id, 200, $anwurf_spaet,  "0815", true, $mannschaft_spaet_id);
        $aufbau_vorher_id = $this->builder->createDienst($spiel_spaet_id, Dienstart::AUFBAU, $mannschaft_spaet_id);
        
        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        // Das frühere Spiel sollte den Abbau nicht mehr haben...
        $this->assertObjectInDb("SELECT * FROM wp_entfallenerdienst WHERE spiel_id = $spiel_frueh_id", [
            'dienstart' => Dienstart::ABBAU,
            'mannschaft_id' => $mannschaft_frueh_id,
            'grund' => self::NOT_NULL
        ]);
        // dafür aber den Aufbau als neuen Dienst
        $aufbau_nachher = $this->fetchOneWithAssert("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_frueh_id");
        $this->assertObjectInDb("SELECT * FROM wp_neuerdienst WHERE dienst_id = {$aufbau_nachher['id']}",[
            'grund' => self::NOT_NULL
        ]);
        
        // Beim späten Spiel genau anders herum:
        $this->assertObjectInDb("SELECT * FROM wp_entfallenerdienst WHERE spiel_id = $spiel_spaet_id", [
            'dienstart' => Dienstart::AUFBAU,
            'mannschaft_id' => $mannschaft_spaet_id,
            'grund' => self::NOT_NULL
        ]);
        $abbau_nachher = $this->fetchOneWithAssert("SELECT * FROM wp_dienst WHERE spiel_id = $spiel_spaet_id");
        $this->assertObjectInDb("SELECT * FROM wp_neuerdienst WHERE dienst_id = {$abbau_nachher['id']}",[
            'grund' => self::NOT_NULL
        ]);
    }

    public function test_organisiereAufUndAbbau_keinDienstBeiAuswaertsSpielen() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung(
            $mannschaft_id,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $spieltag = "2024-09-07";
        $spiel_id = $this->builder->createSpiel(100, $meldung_id, 200, new DateTime("$spieltag 17:00:00"), "0815", 
            heimspiel: false);

        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        // alphabetisch sortierte Dienste
        $this->assertNotInDB("SELECT * from wp_dienst WHERE spiel_id=$spiel_id");
    }
    public function test_organisiereAufUndAbbau_mehrereSpieltage() {
        // arrange
        $meisterschaft_id = $this->builder->createMeisterschaft("KR 24/25");
        $mannschaft_id1 = $this->builder->createMannschaft(2);
        $meldung_id1 = $this->builder->createMannschaftsMeldung(
            $mannschaft_id1,
            $meisterschaft_id,
            363515, // Regionsliga Männer
            1986866 // Turnerkreis Nippes II
        );
        $mannschaft_id2 = $this->builder->createMannschaft(3);
        $meldung_id2 = $this->builder->createMannschaftsMeldung(
            $mannschaft_id2,
            $meisterschaft_id,
            364515, // irgendwas anderes
            1987866 // irgendwas anderes
        );
        
        // Erst spielt Mannschaft 1, dann Mannschaft 2
        $tag1 = "2024-09-07";
        $tag1_anwurf_frueh = new DateTime("$tag1 17:00:00");
        $tag1_spiel_frueh_id = $this->builder->createSpiel(100, $meldung_id1, 200, $tag1_anwurf_frueh, "0815", true, $mannschaft_id1);
        $tag1_anwurf_spaet = new DateTime("$tag1 19:00:00");
        $tag1_spiel_spaet_id = $this->builder->createSpiel(100, $meldung_id2, 200, $tag1_anwurf_spaet,  "0815", true, $mannschaft_id2);
        
        // Und heute spielt zuerst Mannschaft 2, dann 1
        $tag2 = "2024-09-14";
        $tag2_anwurf_frueh = new DateTime("$tag2 17:00:00");
        $tag2_spiel_frueh_id = $this->builder->createSpiel(100, $meldung_id2, 200, $tag2_anwurf_frueh, "0815", true, $mannschaft_id2);
        $tag2_anwurf_spaet = new DateTime("$tag2 19:00:00");
        $tag2_spiel_spaet_id = $this->builder->createSpiel(100, $meldung_id1, 200, $tag2_anwurf_spaet,  "0815", true, $mannschaft_id1);
        
        // act
        $this->import->organisiereAufUndAbbau();

        // assert
        // Tag 1
        $this->assertObjectInDb("SELECT * from wp_dienst where spiel_id=$tag1_spiel_frueh_id", [
            'dienstart'     => Dienstart::AUFBAU,
            'mannschaft_id' => $mannschaft_id1
        ]);
        $this->assertObjectInDb("SELECT * from wp_dienst where spiel_id=$tag1_spiel_spaet_id", [
            'dienstart'     => Dienstart::ABBAU,
            'mannschaft_id' => $mannschaft_id2
        ]);
        // Tag 2
        $this->assertObjectInDb("SELECT * from wp_dienst where spiel_id=$tag2_spiel_frueh_id", [
            'dienstart'     => Dienstart::AUFBAU,
            'mannschaft_id' => $mannschaft_id2
        ]);
        $this->assertObjectInDb("SELECT * from wp_dienst where spiel_id=$tag2_spiel_spaet_id", [
            'dienstart'     => Dienstart::ABBAU,
            'mannschaft_id' => $mannschaft_id1
        ]);
    }
}