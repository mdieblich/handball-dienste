<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Mannschaft.php";

final class MannschaftTest extends TestCase {

    public function testNameHerren1() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_M;
        $mannschaft->nummer = 1;

        $this->assertEquals("Herren 1", $mannschaft->getName());
    }
    
    public function testNameDamen3() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_W;
        $mannschaft->nummer = 3;

        $this->assertEquals("Damen 3", $mannschaft->getName());
    }

    public function testNameJugendMC2() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_M;
        $mannschaft->nummer = 2;
        $mannschaft->jugendklasse = "C";

        $this->assertEquals("männliche C2", $mannschaft->getName());
    }

    public function testNameJugendwb1() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_W;
        $mannschaft->nummer = 1;
        $mannschaft->jugendklasse = "B";

        $this->assertEquals("weibliche B1", $mannschaft->getName());
    }
}
?>