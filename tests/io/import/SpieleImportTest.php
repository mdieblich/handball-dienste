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
}