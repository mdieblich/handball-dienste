<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/io/import/SpieleImport.php";

require_once __DIR__."/../../db/MemoryDB.php";
require_once __DIR__."/FakeHttpClient.php";

final class SpieleImportTest extends TestCase {

    private MemoryDB $db;
    private FakeHttpClient $httpClient;
    private Log $logfile;
    private SpieleImport $import;

    public function setUp(): void {
        $this->db = new MemoryDB();
        $this->httpClient = new FakeHttpClient();
        $this->logfile = new NoLog();
        $this->import = new SpieleImport($this->db, $this->logfile, $this->httpClient);
    }

    public function test_fetchAllNuligaSpielelisten_laedtEineSeite() {
        // arrange
        $meisterschaft = "KR 24/25"; // Köln/Rheinberg 2024/25
        $gruppe = 363515;   // Regionsliga Männer
        $team_id = 1986866; // Turnerkreis Nippes 2 (Herren)

        $this->db->insert("wp_meisterschaft", [
            "id" => 1,
            "kuerzel" => $meisterschaft
        ]);
        $this->db->insert("wp_mannschaft", [
            "id" => 1,
            "nummer" => 2,
            "geschlecht" => "m"
        ]);

        $this->db->insert("wp_mannschaftsmeldung", [
            "id" => 1,
            "mannschaft_id" => 1,
            "meisterschaft_id" => 1,
            "aktiv" => 1,
            "nuligaLigaId" => $gruppe,
            "nuligaTeamId" => $team_id
        ]);

        // act
        $files = $this->import->fetchAllNuligaSpielelisten();

        //
        $this->assertCount(1, $files);
    }

    // TODO: Test für mehrere Meldungen einer Mannschaft
    // TODO: Test für mehrere Mannschaften
}