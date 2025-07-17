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
        $team1_id = $this->builder->createMannschaft(3);
        $team2_id = $this->builder->createMannschaft(2);

        // act
        $plan = $this->planService->loadFromDB();

        // assert
        $this->assertArrayHasKey($team1_id, $plan->mannschaften);
        $this->assertArrayHasKey($team2_id, $plan->mannschaften);
    }
    public function test_load_laedtDienstAenderung() {
        // 
        $mannschaft_spiel_id = $this->builder->createMannschaft(3);
        $mannschaft_dienst_id = $this->builder->createMannschaft(2);
        $meldung_id = $this->builder->createMannschaftsMeldung($mannschaft_spiel_id, 0, 0, 0);
        $spiel_id = $this->builder->createSpiel(703, $meldung_id, 0, new DateTime('2023-09-15 17:30:00'), '4711', true, $mannschaft_spiel_id);
        $dienst_id = $this->builder->createDienst($spiel_id, Dienstart::ZEITNEHMER, $mannschaft_dienst_id);
        $this->db->insert('wp_dienstaenderung', [
            'id' => 3, 
            'dienst_id' => $dienst_id,
            'mannschaft_id' => $mannschaft_dienst_id,
            'anwurfVorher' => '2023-10-01 10:00:00',
            'halleVorher' => '0815'
        ]);

        // act
        $plan = $this->planService->loadFromDB();

        // assert
        $this->assertCount(1,$plan->geaenderteDienste[$mannschaft_dienst_id]);
        $dienstAenderung = $plan->geaenderteDienste[$mannschaft_dienst_id][0];
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