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

}
?>