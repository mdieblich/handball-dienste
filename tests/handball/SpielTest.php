<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Spiel.php";

final class SpielTest extends TestCase {
    
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
    public function test_getSpielEnde_ist_nach_90_Minuten(){
        $spiel = new Spiel();
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 20:00");

        $this->assertEquals(
            DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 21:30"),
            $spiel->getSpielEnde()
        );
    }
    public function test_getSpielzeit_ist_Zeitraum_von_Anwurf_bis_90_Minuten_danach(){
        $anwurf = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 20:00");
        $spielEnde = DateTime::createFromFormat("d.m.Y H:i", "11.08.2022 21:30");
        $spiel = new Spiel();
        $spiel->anwurf = $anwurf;

        $this->assertEquals(
            new ZeitRaum($anwurf, $spielEnde),
            $spiel->getSpielZeit()
        );
    }

}
?>