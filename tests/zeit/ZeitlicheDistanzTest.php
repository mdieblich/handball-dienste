<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../zeit/ZeitlicheDistanz.php";

final class ZeitlicheDistanzTest extends TestCase {

    private function zeitraum(string $start, string $ende): ZeitRaum{
        return new ZeitRaum(
            DateTime::createFromFormat("d.m.Y H:i", $start),
            DateTime::createFromFormat("d.m.Y H:i", $ende)
        );
    }

    // ##########################################
    // Definitionen
    // ##########################################
    public function test_vorher_ist_negativ() {
        $distanz = new ZeitlicheDistanz(-1);
        $this->assertTrue($distanz->isVorher());
        $this->assertFalse($distanz->isUeberlappend());
        $this->assertFalse($distanz->isNachher());
    }
    public function test_ueberlappend_ist_0() {
        $distanz = new ZeitlicheDistanz(0);
        $this->assertFalse($distanz->isVorher());
        $this->assertTrue($distanz->isUeberlappend());
        $this->assertFalse($distanz->isNachher());
    }
    public function test_nachher_ist_positiv() {
        $distanz = new ZeitlicheDistanz(1);
        $this->assertFalse($distanz->isVorher());
        $this->assertFalse($distanz->isUeberlappend());
        $this->assertTrue($distanz->isNachher());
    }

    // ##########################################
    // from_a_to_b(...)
    // ##########################################
    public function test_from_a_to_b_vorher_ist_negativ(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 20:00");
        $b = $this->zeitraum("11.08.2022 17:00", "11.08.2022 18:00");

        $distanz = ZeitlicheDistanz::from_a_to_b($a, $b);

        $this->assertEquals(new ZeitlicheDistanz(-3600), $distanz);
    }

    public function test_from_a_to_b_nachher_ist_positiv(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 20:00");
        $b = $this->zeitraum("11.08.2022 21:00", "11.08.2022 22:00");

        $distanz = ZeitlicheDistanz::from_a_to_b($a, $b);
        
        $this->assertEquals(new ZeitlicheDistanz(3600), $distanz);
    }
    public function test_from_a_to_b_überlappend_vorher_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 18:00", "11.08.2022 20:00");
        
        $distanz = ZeitlicheDistanz::from_a_to_b($a, $b);
        
        $this->assertEquals(new ZeitlicheDistanz(0), $distanz);
    }
    public function test_from_a_to_b_überlappend_nachher_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 20:00", "11.08.2022 22:00");
        
        $distanz = ZeitlicheDistanz::from_a_to_b($a, $b);
        
        $this->assertEquals(new ZeitlicheDistanz(0), $distanz);
    }
    public function test_from_a_to_b_überlappend_kleiner_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 19:30", "11.08.2022 20:30");
        
        $distanz = ZeitlicheDistanz::from_a_to_b($a, $b);
        
        $this->assertEquals(new ZeitlicheDistanz(0), $distanz);
    }
    public function test_from_a_to_b_überlappend_größer_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 18:00", "11.08.2022 22:00");
        
        $distanz = ZeitlicheDistanz::from_a_to_b($a, $b);
        
        $this->assertEquals(new ZeitlicheDistanz(0), $distanz);
    }
    
}
?>