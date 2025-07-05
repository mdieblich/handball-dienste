<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../db/MemoryDB.php";

final class MemoryDBTest extends TestCase {

    private MemoryDB $db;

    public function setUp(): void {
        $this->db = new MemoryDB();
    }

    public function test_subselect() {
        // arrange
        $herren1_id = $this->db->insert("mannschaft", [
            'name'       => 'Herren I',
            'nummer'     => 1,
            'geschlecht' => 'm'
        ]);
        $herren2_id = $this->db->insert("mannschaft", [
            'name'       => 'Herren II',
            'nummer'     => 2,
            'geschlecht' => 'm'
        ]);
        $damen1_id = $this->db->insert("mannschaft", [
            'name'       => 'Damen I',
            'nummer'     => 1,
            'geschlecht' => 'w'
        ]);
        $damen2_id = $this->db->insert("mannschaft", [
            'name'       => 'Damen II',
            'nummer'     => 2,
            'geschlecht' => 'w'
        ]);

        $meldung_h1_id = $this->db->insert('meldung', [
            'mannschaft' => $herren1_id,
            'liga'       => 'Oberliga Männer'
        ]);
        $meldung_h2_id = $this->db->insert('meldung', [
            'mannschaft' => $herren2_id,
            'liga'       => 'Kreisliga Männer'
        ]);
        $meldung_d1_id = $this->db->insert('meldung', [
            'mannschaft' => $damen1_id,
            'liga'       => 'Oberliga Frauen'
        ]);
        $meldung_d2_id = $this->db->insert('meldung', [
            'mannschaft' => $damen2_id,
            'liga'       => 'Kreisliga Frauen'
        ]);
        // act
        $results = $this->db->get_results("SELECT * FROM meldung WHERE mannschaft IN (SELECT id FROM mannschaft WHERE geschlecht='w')", ARRAY_A);

        // assert
        $this->assertCount(2, $results);
        $ligen = array_column($results, 'liga');
        $this->assertContains( 'Oberliga Männer', $ligen);
        $this->assertContains( 'Kreisliga Männer', $ligen);
    }

}