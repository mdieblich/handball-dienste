<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertArrayHasKey;

require_once __DIR__."/../../../src/db/service/DienstAenderungsPlanService.php";

require_once __DIR__."/../MemoryDB.php";
require_once __DIR__."/../DBBuilder.php";

final class DienstAenderungsPlanServiceTest extends TestCase {

    private MemoryDB $db;
    private DBBuilder $builder;
    private DienstAenderungsPlanService $planService;

    public function setUp(): void {
        $this->db = new MemoryDB();
        $this->builder = new DBBuilder($this->db);
        $this->planService = new DienstAenderungsPlanService($this->db);
    }

    public function test_load_laedtMannschaften() {
        // arrange
        $team1 = $this->builder->createMannschaft(3);
        $team2 = $this->builder->createMannschaft(2);

        // act
        $plan = $this->planService->loadFromDB();

        // assert
        $this->assertArrayHasKey($team1->id, $plan->mannschaften);
        $this->assertArrayHasKey($team2->id, $plan->mannschaften);
    }
    public function test_load_laedtDienstAenderung() {
        // 
        $meisterschaft = $this->builder->createMeisterschaft("EGAL");
        $mannschaft_spiel = $this->builder->createMannschaft(3);
        $mannschaft_dienst = $this->builder->createMannschaft(2);
        $meldung = $this->builder->createMannschaftsMeldung($mannschaft_spiel, $meisterschaft, 0, 0);
        $gegner = $this->builder->createGegner("Pulheimer SC", 3, $meldung);
        $spiel = $this->builder->createSpiel(703, $meldung, $gegner, new DateTime('2023-09-15 17:30:00'), '4711', true, $mannschaft_spiel);
        $dienst_id = $this->builder->createDienst($spiel->id, Dienstart::ZEITNEHMER, $mannschaft_dienst->id);
        $this->db->insert('wp_dienstaenderung', [
            'id' => 3, 
            'dienst_id' => $dienst_id,
            'mannschaft_id' => $mannschaft_dienst->id,
            'anwurfVorher' => '2023-10-01 10:00:00',
            'halleVorher' => '0815'
        ]);

        // act
        $plan = $this->planService->loadFromDB();

        // assert
        $this->assertCount(1,$plan->geaenderteDienste[$mannschaft_dienst->id]);
        $dienstAenderung = $plan->geaenderteDienste[$mannschaft_dienst->id][0];
        // $this->
        $this->fail("Not implemented yet");
    }
    public function test_load_laedtNeueDienste() {
        $this->fail("Not implemented yet");
    }
    public function test_load_laedtEntfalleneDienste() {
        $this->fail("Not implemented yet");
    }
    public function test_load_laedtSpiele() {
        $this->fail("Not implemented yet");
    }
}