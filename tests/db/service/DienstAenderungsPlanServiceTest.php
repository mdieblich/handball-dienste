<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/db/service/DienstAenderungsPlanService.php";

require_once __DIR__."/../MemoryDB.php";

final class DienstAenderungsPlanServiceTest extends TestCase {

    private MemoryDB $db;

    public function setUp(): void {
        $this->db = new MemoryDB();
    }

    public function test_load_laedtDienstAenderung() {
        $this->fail("Not implemented yet");
    }
    public function test_load_laedtNeueDienste() {
        $this->fail("Not implemented yet");
    }
    public function test_load_laedtEntfalleneDienste() {
        $this->fail("Not implemented yet");
    }
}