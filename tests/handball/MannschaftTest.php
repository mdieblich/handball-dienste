<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Mannschaft.php";

final class MannschaftTest extends TestCase {

    // ##########################################
    // getName()
    // ##########################################
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

    public function testNameJugendmC2() {

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

    
    // ##########################################
    // getKurzname()
    // ##########################################
    public function testKurznameHerren1() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_M;
        $mannschaft->nummer = 1;

        $this->assertEquals("H1", $mannschaft->getKurzname());
    }
    
    public function testKurznameDamen3() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_W;
        $mannschaft->nummer = 3;

        $this->assertEquals("D3", $mannschaft->getKurzname());
    }

    public function testKurznameJugendmC2() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_M;
        $mannschaft->nummer = 2;
        $mannschaft->jugendklasse = "C";

        $this->assertEquals("mC2", $mannschaft->getKurzname());
    }

    public function testKurznameJugendwb1() {

        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_W;
        $mannschaft->nummer = 1;
        $mannschaft->jugendklasse = "B";

        $this->assertEquals("wB1", $mannschaft->getKurzname());
    }
}
?>