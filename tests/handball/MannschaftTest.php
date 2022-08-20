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
    public function test_Name_von_Herren_1() {
        $this->assertEquals("Herren 1", $this->herren1()->getName());
    }
    public function test_Name_von_Damen_3() {
        $this->assertEquals("Damen 3", $this->damen3()->getName());
    }
    public function test_Name_von_Jugend_mC2() {
        $this->assertEquals("männliche C2", $this->mC2()->getName());
    }
    public function test_Name_von_Jugend_wB1() {
        $this->assertEquals("weibliche B1", $this->wB1()->getName());
    }

    // ##########################################
    // getKurzname()
    // ##########################################
    public function test_Kurzname_von_Herren_1() {
        $this->assertEquals("H1", $this->herren1()->getKurzname());
    }
    public function test_Kurzname_von_Damen_3() {
        $this->assertEquals("D3", $this->damen3()->getKurzname());
    }
    public function test_Kurzname_von_Jugend_mC2() {
        $this->assertEquals("mC2", $this->mC2()->getKurzname());
    }
    public function test_Kurzname_von_Jugen_wB1() {
        $this->assertEquals("wB1", $this->wB1()->getKurzname());
    }

    // ##########################################
    // createNuLigaMannschaftsBezeichnung()
    // ##########################################
    public function test_nuLiga_Bezeichnung_von_Herren_1() {
        $this->assertEquals("Männer", $this->herren1()->createNuLigaMannschaftsBezeichnung());
    }
    public function test_nuLiga_Bezeichnung_von_Damen3() {
        $this->assertEquals("Frauen III", $this->damen3()->createNuLigaMannschaftsBezeichnung());
    }
    public function test_nuLiga_Bezeichnung_von_Jugend_mC2() {
        $this->assertEquals("männliche Jugend C II", $this->mC2()->createNuLigaMannschaftsBezeichnung());
    }
    public function test_nuLiga_Bezeichnung_von_Jugend_wB1() {
        $this->assertEquals("weibliche Jugend B", $this->wB1()->createNuLigaMannschaftsBezeichnung());
    }
    
    // ##########################################
    // getMeldungenFuerMeisterschaft()
    // ##########################################
    public function test_Meldungen_für_Meisterschaft_ist_leer_bei_keinen_Meldungen(){
        $mannschaft = new Mannschaft();
        $meisterschaft = new Meisterschaft();

        $this->assertEmpty($mannschaft->getMeldungenFuerMeisterschaft($meisterschaft));
    }
    public function test_Meldungen_für_Meisterschaft_ist_leer_falls_keine_Meldung_zur_Meisterschaft_passt(){
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
    public function test_Meldungen_für_Meisterschaft_enthält_passende_Meldungen(){
        $mannschaft = new Mannschaft();
        $hvm22_23 = new Meisterschaft();
        $hvm22_23->id = 1;
        $meldung_freundschaftsspiele = new MannschaftsMeldung();
        $meldung_freundschaftsspiele->meisterschaft = $hvm22_23;
        $mannschaft->meldungen[] = $meldung_freundschaftsspiele;

        $meldung_verbandsliga = new MannschaftsMeldung();
        $meldung_verbandsliga->meisterschaft = $hvm22_23;
        $mannschaft->meldungen[] = $meldung_verbandsliga;

        $hkkr22_23 = new Meisterschaft();
        $hkkr22_23->id = 2;
        $meldung_kreisliga = new MannschaftsMeldung();
        $meldung_kreisliga->meisterschaft = $hkkr22_23;
        $mannschaft->meldungen[] = $meldung_kreisliga;

        $this->assertEquals([$meldung_freundschaftsspiele, $meldung_verbandsliga], $mannschaft->getMeldungenFuerMeisterschaft($hvm22_23));
    }
    
    // ##########################################
    // getGeschlechtFormKurzname()
    // ##########################################
    public function test_getGeschlechtFormKurzname_H(){
        $this->assertEquals(GESCHLECHT_M, Mannschaft::getGeschlechtFormKurzname("H1"));
    }
    public function test_getGeschlechtFormKurzname_m(){
        $this->assertEquals(GESCHLECHT_M, Mannschaft::getGeschlechtFormKurzname("mC2"));
    }
    public function test_getGeschlechtFormKurzname_D(){
        $this->assertEquals(GESCHLECHT_W, Mannschaft::getGeschlechtFormKurzname("D2"));
    }
    public function test_getGeschlechtFormKurzname_w(){
        $this->assertEquals(GESCHLECHT_W, Mannschaft::getGeschlechtFormKurzname("wA1"));
    }
    // ##########################################
    // getJugendKlasseFromKurzname()
    // ##########################################
    public function test_getJugendKlasseFromKurzname_H1(){
        $this->assertNull(Mannschaft::getJugendKlasseFromKurzname("H1"));
    }
    public function test_getJugendKlasseFromKurzname_mC2(){
        $this->assertEquals("C", Mannschaft::getJugendKlasseFromKurzname("mC2"));
    }
    public function test_getJugendKlasseFromKurzname_D2(){
        $this->assertNull(Mannschaft::getJugendKlasseFromKurzname("D2"));
    }
    public function test_getJugendKlasseFromKurzname_wA1(){
        $this->assertEquals("A", Mannschaft::getJugendKlasseFromKurzname("wA1"));
    }
    // ##########################################
    // getNummerFromKurzname()
    // ##########################################
    public function test_getNummerFromKurzname_H1(){
        $this->assertEquals(1, Mannschaft::getNummerFromKurzname("H1"));
    }
    public function test_getNummerFromKurzname_mC2(){
        $this->assertEquals(2, Mannschaft::getNummerFromKurzname("mC2"));
    }
    public function test_getNummerFromKurzname_D3(){
        $this->assertEquals(3, Mannschaft::getNummerFromKurzname("D3"));
    }
    public function test_getNummerFromKurzname_wA1(){
        $this->assertEquals(1, Mannschaft::getNummerFromKurzname("wA1"));
    }
}
?>