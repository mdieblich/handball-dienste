<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/db/service/SpielService.php";

require_once __DIR__."/../MemoryDB.php";
require_once __DIR__."/../DBBuilder.php";

final class SpielServiceTest extends TestCase {

    private MemoryDB $db;
    private DBBuilder $builder;
    private SpielService $spielService;
    private SpielDAO $spielDAO;

    public function setUp(): void {
        $this->db = new MemoryDB();
        $this->builder = new DBBuilder($this->db);
        $this->spielDAO = new SpielDAO($this->db);
        $this->spielService = new SpielService($this->db);
    }

    public function test_fetchCompletely() {
        // arrange
        $original_spiel = new Spiel();
        $meisterschaft = $this->builder->createMeisterschaft("Wacken 2016");
        $original_spiel->mannschaft = $this->builder->createMannschaft(1);
        $original_spiel->mannschaftsMeldung = $this->builder->createMannschaftsMeldung($original_spiel->mannschaft, $meisterschaft, 123, 456);
        $original_spiel->gegner = $this->builder->createGegner("Pulheimer SC", 3, $original_spiel->mannschaftsMeldung);        
        $this->spielDAO->insert($original_spiel);
        
        $mannschaft2 = $this->builder->createMannschaft(2);
        $this->builder->createDienst($original_spiel->id, Dienstart::ZEITNEHMER, $mannschaft2->id);
        $this->builder->createDienst($original_spiel->id, Dienstart::SEKRETAER, $mannschaft2->id);
        
        // act
        $foundSpiel = $this->spielService->fetchCompletely("id=$original_spiel->id");

        // assert
        $this->assertTrue(isset($foundSpiel->mannschaft));
        $this->assertTrue(isset($foundSpiel->mannschaftsMeldung));
        $this->assertTrue(isset($foundSpiel->gegner));
        $this->assertCount(2, $foundSpiel->dienste);
    }

}