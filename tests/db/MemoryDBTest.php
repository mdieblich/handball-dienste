<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/MemoryDB.php";

final class MemoryDBTest extends TestCase {

    private MemoryDB $db;

    public function setUp(): void {
        $this->db = new MemoryDB();
    }

    public function test_select_in() {
        // arrange
        $this->db->insert("mannschaft", [
            'name'       => 'Herren I',
            'nummer'     => 1,
            'geschlecht' => 'm'
        ]);
        $herren1_id = $this->db->insert_id;
        $this->db->insert("mannschaft", [
            'name'       => 'Herren II',
            'nummer'     => 2,
            'geschlecht' => 'm'
        ]);
        $herren2_id = $this->db->insert_id;
        $this->db->insert("mannschaft", [
            'name'       => 'Damen I',
            'nummer'     => 1,
            'geschlecht' => 'w'
        ]);
        $damen1_id = $this->db->insert_id;
        $this->db->insert("mannschaft", [
            'name'       => 'Damen II',
            'nummer'     => 2,
            'geschlecht' => 'w'
        ]);
        $damen2_id = $this->db->insert_id;
        // act
        $results = $this->db->get_results("SELECT * FROM mannschaft WHERE id IN ($herren1_id, $damen2_id)", ARRAY_A);

        // assert
        $this->assertCount(2, $results);
        $ligen = array_column($results, 'name');
        $this->assertContains( 'Herren I', $ligen);
        $this->assertContains( 'Damen II', $ligen);
    }
    public function test_subselect() {
        // arrange
        
        $this->db->insert("mannschaft", [
            'name'       => 'Herren I',
            'nummer'     => 1,
            'geschlecht' => 'm'
        ]);
        $herren1_id = $this->db->insert_id;
        $this->db->insert("mannschaft", [
            'name'       => 'Herren II',
            'nummer'     => 2,
            'geschlecht' => 'm'
        ]);
        $herren2_id = $this->db->insert_id;
        $this->db->insert("mannschaft", [
            'name'       => 'Damen I',
            'nummer'     => 1,
            'geschlecht' => 'w'
        ]);
        $damen1_id = $this->db->insert_id;
        $this->db->insert("mannschaft", [
            'name'       => 'Damen II',
            'nummer'     => 2,
            'geschlecht' => 'w'
        ]);
        $damen2_id = $this->db->insert_id;

        $this->db->insert('meldung', [
            'mannschaft' => $herren1_id,
            'liga'       => 'Oberliga Männer'
        ]);
        $this->db->insert('meldung', [
            'mannschaft' => $herren2_id,
            'liga'       => 'Kreisliga Männer'
        ]);
        $this->db->insert('meldung', [
            'mannschaft' => $damen1_id,
            'liga'       => 'Oberliga Frauen'
        ]);
        $this->db->insert('meldung', [
            'mannschaft' => $damen2_id,
            'liga'       => 'Kreisliga Frauen'
        ]);
        // act
        $results = $this->db->get_results("SELECT * FROM meldung WHERE mannschaft IN (SELECT id FROM mannschaft WHERE geschlecht='w')", ARRAY_A);

        // assert
        $this->assertCount(2, $results);
        $ligen = array_column($results, 'liga');
        $this->assertContains( 'Oberliga Frauen', $ligen);
        $this->assertContains( 'Kreisliga Frauen', $ligen);
    }

}