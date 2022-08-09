<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Mannschaft.php";

final class MannschaftTest extends TestCase {

    private function herren1(): Mannschaft{
        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_M;
        $mannschaft->nummer = 1;
        return $mannschaft;
    }

    private function damen3(): Mannschaft{
        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_W;
        $mannschaft->nummer = 3;
        return $mannschaft;
    }

    private function mC2(): Mannschaft{
        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_M;
        $mannschaft->nummer = 2;
        $mannschaft->jugendklasse = "C";
        return $mannschaft;
    }

    private function wB1(): Mannschaft{
        $mannschaft = new Mannschaft();
        $mannschaft->geschlecht = GESCHLECHT_W;
        $mannschaft->nummer = 1;
        $mannschaft->jugendklasse = "B";
        return $mannschaft;
    }

    // ##########################################
    // getName()
    // ##########################################
    public function testNameHerren1() {
        $this->assertEquals("Herren 1", $this->herren1()->getName());
    }
    public function testNameDamen3() {
        $this->assertEquals("Damen 3", $this->damen3()->getName());
    }
    public function testNameJugendMC2() {
        $this->assertEquals("männliche C2", $this->mC2()->getName());
    }
    public function testNameJugendWB1() {
        $this->assertEquals("weibliche B1", $this->wB1()->getName());
    }

    // ##########################################
    // getKurzname()
    // ##########################################
    public function testKurznameHerren1() {
        $this->assertEquals("H1", $this->herren1()->getKurzname());
    }
    public function testKurznameDamen3() {
        $this->assertEquals("D3", $this->damen3()->getKurzname());
    }
    public function testKurznameJugendMC2() {
        $this->assertEquals("mC2", $this->mC2()->getKurzname());
    }
    public function testKurznameJugendWB1() {
        $this->assertEquals("wB1", $this->wB1()->getKurzname());
    }

    // ##########################################
    // createNuLigaMannschaftsBezeichnung()
    // ##########################################
    public function testNuligaBezeichnungHerren1() {
        $this->assertEquals("Männer", $this->herren1()->createNuLigaMannschaftsBezeichnung());
    }
    public function testNuligaBezeichnungDamen3() {
        $this->assertEquals("Frauen III", $this->damen3()->createNuLigaMannschaftsBezeichnung());
    }
    public function testNuligaBezeichnungJugendMC2() {
        $this->assertEquals("männliche Jugend C II", $this->mC2()->createNuLigaMannschaftsBezeichnung());
    }
    public function testNuligaBezeichnungJugendWB1() {
        $this->assertEquals("weibliche Jugend B", $this->wB1()->createNuLigaMannschaftsBezeichnung());
    }
}
?>