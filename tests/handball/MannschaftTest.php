<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Mannschaft.php";
require_once __DIR__."/../../handball/Meisterschaft.php";

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
    
    // ##########################################
    // getMeldungenFuerMeisterschaft()
    // ##########################################
    public function testMeldungenFuerMeisterschaftLeer(){
        $mannschaft = new Mannschaft();
        $meisterschaft = new Meisterschaft();

        $this->assertEmpty($mannschaft->getMeldungenFuerMeisterschaft($meisterschaft));
    }
    public function testMeldungenFuerMeisterschaftLeerFallsKeineMeldungInMeisterschaft(){
        $mannschaft = new Mannschaft();
        $hvm22_23 = new Meisterschaft();
        $hvm22_23->id = 1;
        $meldung = new MannschaftsMeldung();
        $meldung->meisterschaft = $hvm22_23;
        $mannschaft->meldungen[] = $meldung;

        $hkkr22_23 = new Meisterschaft();
        $hkkr22_23->id = 2;

        $this->assertEmpty($mannschaft->getMeldungenFuerMeisterschaft($hkkr22_23));
    }
    public function testMeldungenFuerMeisterschaftEnthaeltMeldungen(){
        $mannschaft = new Mannschaft();
        $hvm22_23 = new Meisterschaft();
        $hvm22_23->id = 1;
        $meldung_freundschaftsspiele = new MannschaftsMeldung();
        $meldung_freundschaftsspiele->meisterschaft = $hvm22_23;
        $mannschaft->meldungen[] = $meldung_freundschaftsspiele;

        $meldung_verbandsliga = new MannschaftsMeldung();
        $meldung_verbandsliga->meisterschaft = $hvm22_23;
        $mannschaft->meldungen[] = $meldung_verbandsliga;

        $this->assertEquals([$meldung_freundschaftsspiele, $meldung_verbandsliga], $mannschaft->getMeldungenFuerMeisterschaft($hvm22_23));
    }
}
?>