<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Spiel.php";

final class SpielTest extends TestCase {

    private function heimspiel(string $anwurf, int $halle = 3182): Spiel {
        // 3182 = Nippeser Tälchen
        $spiel = new Spiel();
        $spiel->heimspiel = true;
        $spiel->halle = $halle;
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);

        return $spiel;
    }

    private function auswaertsspiel(string $anwurf, int $halle = 3117): Spiel {
        // Pulheimer Hornets
        $spiel = new Spiel();
        $spiel->heimspiel = false;
        $spiel->halle = $halle; 
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);

        return $spiel;
    }

    private function zeitraum(string $von, string $bis): ZeitRaum {
        return new ZeitRaum(
            DateTime::createFromFormat("d.m.Y H:i", $von),
            DateTime::createFromFormat("d.m.Y H:i", $bis)
        );
    }
    
    // ##########################################
    // getDienst()
    // ##########################################
    public function test_getDienst_gibt_Dienst_zurück(){
        $spiel = new Spiel();
        $aufbau = new Dienst();
        $aufbau->dienstart = Dienstart::AUFBAU;;
        $spiel->dienste[Dienstart::AUFBAU] = $aufbau;
        
        $this->assertEquals($aufbau, $spiel->getDienst(Dienstart::AUFBAU));
    }
    public function test_getDienst_gibt_null_zurück_wenn_nicht_vorhanden(){
        $spiel = new Spiel();
        $aufbau = new Dienst();
        $aufbau->dienstart = Dienstart::AUFBAU;;
        $spiel->dienste[Dienstart::AUFBAU] = $aufbau;

        $this->assertNull($spiel->getDienst(Dienstart::CATERING));
    }

    // ##########################################
    // Spieldauer 
    // ##########################################
    public function test_SpielEnde_ist_nach_90_Minuten(){
        $spiel = new Spiel();
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 20:00");

        $this->assertEquals(
            DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 21:30"),
            $spiel->getSpielEnde()
        );
    }
    public function test_Spielzeit_ist_von_Anwurf_bis_90_Minuten_danach(){
        $spiel = $this->heimspiel("11.08.2022 20:00");

        $this->assertEquals(
            $this->zeitraum("11.08.2022 20:00", "11.08.2022 21:30"),
            $spiel->getSpielZeit()
        );
    }
    
    // ##########################################
    // Abfahrt und Rückfahrt 
    // ##########################################
    public function test_Abfahrt_ist_bei_Heimspielen_60_Minuten_vorher() {
        $heimspiel = $this->heimspiel("11.08.2022 20:00");
        
        $expectedAbfahrt = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 19:00");
        $this->assertEquals($expectedAbfahrt, $heimspiel->getAbfahrt());
    }
    public function test_Abfahrt_ist_bei_Auswärtsspielen_120_Minuten_vorher() {
        $auswaertsspiel = $this->auswaertsspiel("11.08.2022 20:00");
        
        $expectedAbfahrt = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 18:00");
        $this->assertEquals($expectedAbfahrt, $auswaertsspiel->getAbfahrt());
    }
    public function test_Rückkehr_ist_bei_Heimspielen_150_Minuten_später() {
        $heimspiel = $this->heimspiel("11.08.2022 20:00");
        
        $expectedRueckkehr = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 22:30");
        $this->assertEquals($expectedRueckkehr, $heimspiel->getRueckkehr());
    }
    public function test_Rückkehr_ist_bei_Auswärtsspielen_210_Minuten_später() {
        $auswaertsspiel = $this->auswaertsspiel("11.08.2022 20:00");
        
        $expectedRueckkehr = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 23:30");
        $this->assertEquals($expectedRueckkehr, $auswaertsspiel->getRueckkehr());
    }
    public function test_Abwesenheitszeitraum_eines_Heimspiels_ist_von_Abfahrt_bis_Rückfahrt() {
        $heimspiel = $this->heimspiel("11.08.2022 20:00");

        $this->assertEquals(
            $this->zeitraum("11.08.2022 19:00", "11.08.2022 22:30"),
            $heimspiel->getAbwesenheitsZeitraum()
        );
    }
    public function test_Abwesenheitszeitraum_eines_Auswärtsspiels_ist_von_Abfahrt_bis_Rückfahrt() {
        $auswaertsspiel = $this->auswaertsspiel("11.08.2022 20:00");

        $this->assertEquals(
            $this->zeitraum("11.08.2022 18:00", "11.08.2022 23:30"),
            $auswaertsspiel->getAbwesenheitsZeitraum()
        );
    }

    // ##########################################
    // calculate_distanz_to() 
    // ##########################################
    public function test_zeitliche_Distanz_zwischen_zwei_Spielen_ist_null_wenn_Anwurf_fehlt() {
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_a->anwurf = null;
        
        $spiel_b = $this->heimspiel("13.08.2022 20:00");

        $this->assertNull($spiel_a->calculate_distanz_to($spiel_b));
    }
    public function test_zeitliche_Distanz_zwischen_zwei_Spielen_ist_null_wenn_Anwurf_vom_anderen_Spiel_fehlt() {
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        
        $spiel_b = $this->heimspiel("13.08.2022 20:00");
        $spiel_b->anwurf = null;

        $this->assertNull($spiel_a->calculate_distanz_to($spiel_b));
    }
    public function test_zeitliche_Distanz_in_gleicher_halle_beruecksichtigt_nur_Spielzeit() {
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->heimspiel("13.08.2022 20:00");

        $abstand_in_h = 24; // Anwurf liegt 1 Tag auseinander
        $abstand_in_h -= 1.5; // abzüglich der Spielzeit von Spiel A
        $this->assertEquals(
            new ZeitlicheDistanz((int) ($abstand_in_h * 3600)), 
            $spiel_a->calculate_distanz_to($spiel_b)
        );
    }
    public function test_zeitliche_Distanz_in_unterschiedlichen_hallen_beruecksichtigt_gesamten_Abwesenheitszeitraum() {
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->auswaertsspiel("13.08.2022 20:00");

        $abstand_in_h  = 24.0; // Anwurf liegt 1 Tag auseinander
        $abstand_in_h -=  1.5; // abzüglich der Spielzeit von Spiel A
        $abstand_in_h -=  1.0; // abzüglich der Nachbereitung von Spiel A
        $abstand_in_h -=  1.0; // abzüglich der Anfahrt zu Spiel B
        $abstand_in_h -=  1.0; // abzüglich der Vorbereitung von Spiel B
        $this->assertEquals(
            new ZeitlicheDistanz((int) ($abstand_in_h * 3600)), 
            $spiel_a->calculate_distanz_to($spiel_b)
        );
    }
    public function test_zeitliche_Distanz_überlappender_Spiele_ist_0() {
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->heimspiel("12.08.2022 19:00");

        $this->assertEquals(
            new ZeitlicheDistanz(0), 
            $spiel_a->calculate_distanz_to($spiel_b)
        );
    }
    public function test_zeitliche_Distanz_in_gleicher_halle_ist_negativ_wenn_Spiel_vorher() {
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->heimspiel("11.08.2022 20:00");
        
        $abstand_in_h = 24; // Anwurf liegt 1 Tag auseinander
        $abstand_in_h -= 1.5; // abzüglich der Spielzeit von Spiel B
        $this->assertEquals(
            new ZeitlicheDistanz((int) (-$abstand_in_h * 3600)), 
            $spiel_a->calculate_distanz_to($spiel_b)
        );
    }

    // ##########################################
    // isAmGleichenTag 
    // ##########################################
    public function test_gleicher_Tag(){
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->heimspiel("12.08.2022 18:00");

        $this->assertTrue($spiel_a->isAmGleichenTag($spiel_b));
    }
    public function test_unterschiedlicher_Tag(){
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->heimspiel("13.08.2022 18:00");

        $this->assertFalse($spiel_a->isAmGleichenTag($spiel_b));
    }
    public function test_gleicher_Tag_ist_falsch_wenn_anderes_Spiel_fehlt(){
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = null;
        
        $this->assertFalse($spiel_a->isAmGleichenTag($spiel_b));
    }
    public function test_gleicher_Tag_ist_falsch_wenn_Anwurf_fehlt(){
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_a->anwurf = null;
        $spiel_b = $this->heimspiel("12.08.2022 18:00");

        $this->assertFalse($spiel_a->isAmGleichenTag($spiel_b));
    }
    public function test_gleicher_Tag_ist_falsch_wenn_Anwurf_des_anderen_Spiels_fehlt(){
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = $this->heimspiel("12.08.2022 18:00");
        $spiel_b->anwurf = null;

        $this->assertFalse($spiel_a->isAmGleichenTag($spiel_b));
    }

    // ##########################################
    // isAmGleichenTag 
    // ##########################################
    public function test_gleiche_Halle(){
        $spiel_a = $this->auswaertsspiel("12.08.2022 20:00", 4711);
        $spiel_b = $this->auswaertsspiel("12.08.2022 20:00", 4711);
        
        $this->assertTrue($spiel_a->isInGleicherHalle($spiel_b));
    }
    public function test_ungleiche_Halle(){
        $spiel_a = $this->auswaertsspiel("12.08.2022 20:00", 4711);
        $spiel_b = $this->auswaertsspiel("12.08.2022 20:00", 4712);
        
        $this->assertFalse($spiel_a->isInGleicherHalle($spiel_b));
    }
    public function test_gleiche_Halle_ist_falsch_wenn_anderes_Spiel_fehlt(){
        $spiel_a = $this->heimspiel("12.08.2022 20:00");
        $spiel_b = null;
        
        $this->assertFalse($spiel_a->isInGleicherHalle($spiel_b));
    }
    
    // ##########################################
    // Dienste erstellen 
    // ##########################################
    public function test_createDienst_weist_Spiel_und_Dienstart_zu() {
        $spiel = new Spiel();
        
        $spiel->createDienst(Dienstart::ZEITNEHMER);

        $this->assertArrayHasKey(Dienstart::ZEITNEHMER, $spiel->dienste);
        $this->assertCount(1, $spiel->dienste);
        $this->assertEquals(Dienstart::ZEITNEHMER, $spiel->dienste[Dienstart::ZEITNEHMER]->dienstart);
        $this->assertEquals($spiel, $spiel->dienste[Dienstart::ZEITNEHMER]->spiel);
    }
    public function test_createDienste_erstellt_Zeitnehmer_und_Catering_für_Heimspiele() {
        $gegner = new Gegner();
        $gegner->stelltSekretaerBeiHeimspiel = false;
        $heimspiel = $this->heimspiel("12.08.2022 20:00");
        $heimspiel->gegner = $gegner;
        
        $heimspiel->createDienste();
        
        $this->assertCount(2, $heimspiel->dienste);
        $this->assertArrayHasKey(Dienstart::ZEITNEHMER, $heimspiel->dienste);
        $this->assertArrayHasKey(Dienstart::CATERING, $heimspiel->dienste);
    }
    public function test_createDienste_erstellt_Sekretär_für_Auswärtsspiele() {
        $gegner = new Gegner();
        $gegner->stelltSekretaerBeiHeimspiel = false;
        $auswaertsspiel = $this->auswaertsspiel("12.08.2022 20:00");
        $auswaertsspiel->gegner = $gegner;
        
        $auswaertsspiel->createDienste();
        
        $this->assertCount(1, $auswaertsspiel->dienste);
        $this->assertArrayHasKey(Dienstart::SEKRETAER, $auswaertsspiel->dienste);
    }
    public function test_createDienste_erstellt_Sekretär_für_Heimspiele_wenn_Gegner_den_stellt() {
        $gegner = new Gegner();
        $gegner->stelltSekretaerBeiHeimspiel = true;
        $heimspiel = $this->heimspiel("12.08.2022 20:00");
        $heimspiel->gegner = $gegner;
        
        $heimspiel->createDienste();
        
        $this->assertArrayHasKey(Dienstart::SEKRETAER, $heimspiel->dienste);
    }
    public function test_createDienste_erstellt_keine_Dienste_für_Auswärtsspiele_wenn_Gegner_Sekretät_stellt() {
        $gegner = new Gegner();
        $gegner->stelltSekretaerBeiHeimspiel = true;
        $auswaertsspiel = $this->auswaertsspiel("12.08.2022 20:00");
        $auswaertsspiel->gegner = $gegner;
        
        $auswaertsspiel->createDienste();
        
        $this->assertCount(0, $auswaertsspiel->dienste);
    }
}
?>