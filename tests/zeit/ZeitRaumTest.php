<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../zeit/ZeitRaum.php";

final class ZeitRaumTest extends TestCase {

    private function zeitraum(string $start, string $ende): ZeitRaum{
        return new ZeitRaum(
            DateTime::createFromFormat("d.m.Y H:i", $start),
            DateTime::createFromFormat("d.m.Y H:i", $ende)
        );
    }

    private function distanz(int $seconds, bool $ueberlappend = false): ZeitlicheDistanz {
        $distanz = new ZeitlicheDistanz();
        $distanz->ueberlappend = $ueberlappend;
        $distanz->seconds = $seconds;
        return $distanz;
    }

    // ##########################################
    // getZeitlicheDistanz()
    // ##########################################
    public function test_getZeitlicheDistanz_vorher_ist_negativ(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 20:00");
        $b = $this->zeitraum("11.08.2022 17:00", "11.08.2022 18:00");

        $distanz = $a->getZeitlicheDistanz($b);

        $this->assertEquals($this->distanz(-3600), $distanz);
    }

    public function test_getZeitlicheDistanz_nachher_ist_positiv(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 20:00");
        $b = $this->zeitraum("11.08.2022 21:00", "11.08.2022 22:00");

        $distanz = $a->getZeitlicheDistanz($b);

        $this->assertEquals($this->distanz(3600), $distanz);
    }
    public function test_getZeitlicheDistanz_überlappend_vorher_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 18:00", "11.08.2022 20:00");

        $distanz = $a->getZeitlicheDistanz($b);

        $this->assertEquals($this->distanz(0, true), $distanz);
    }
    public function test_getZeitlicheDistanz_überlappend_nachher_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 20:00", "11.08.2022 22:00");

        $distanz = $a->getZeitlicheDistanz($b);

        $this->assertEquals($this->distanz(0, true), $distanz);
    }
    public function test_getZeitlicheDistanz_überlappend_kleiner_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 19:30", "11.08.2022 20:30");

        $distanz = $a->getZeitlicheDistanz($b);

        $this->assertEquals($this->distanz(0, true), $distanz);
    }
    public function test_getZeitlicheDistanz_überlappend_größer_ist_null(){
        $a = $this->zeitraum("11.08.2022 19:00", "11.08.2022 21:00");
        $b = $this->zeitraum("11.08.2022 18:00", "11.08.2022 22:00");

        $distanz = $a->getZeitlicheDistanz($b);

        $this->assertEquals($this->distanz(0, true), $distanz);
    }
    
}
?>