<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Spiel.php";

final class SpielTest extends TestCase {

    private function heimspiel(string $anwurf): Spiel {
        $spiel = new Spiel();
        $spiel->heimspiel = true;
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);

        return $spiel;
    }

    private function auswaertsspiel(string $anwurf): Spiel {
        $spiel = new Spiel();
        $spiel->heimspiel = false;
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);

        return $spiel;
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
    public function test_Spielzeit_ist_Zeitraum_von_Anwurf_bis_90_Minuten_danach(){
        $anwurf = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 20:00");
        $spielEnde = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 21:30");
        $spiel = new Spiel();
        $spiel->anwurf = $anwurf;

        $this->assertEquals(
            new ZeitRaum($anwurf, $spielEnde),
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


     // TODO Abwesenheitszeitraum testen

}
?>