<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../handball/dienst/SpielAenderung.php";

final class SpielAenderungTest extends TestCase {

    private function auswaertsspiel(string $anwurf, int $halle = 3117): Spiel {
        // Pulheimer Hornets
        $spiel = new Spiel();
        $spiel->heimspiel = false;
        $spiel->halle = $halle; 
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);

        return $spiel;
    }
    
    // ##########################################
    // getAenderung()
    // ##########################################
    public function test_getAenderung_anwurf_geandert(){
        $spielAlt = $this->auswaertsspiel("12.03.2023 23:18");
        $spielNeu = $this->auswaertsspiel("13.03.2023 23:19");

        $spielAenderung = new SpielAenderung($spielAlt, $spielNeu);
        
        $this->assertEquals("Anwurf von [12.03.2023 23:18] zu [13.03.2023 23:19]", $spielAenderung->getAenderung());
    }
    public function test_getAenderung_halle_geandert(){
        $spielA = $this->auswaertsspiel("12.03.2023 23:18", 1234);
        $spielB = $this->auswaertsspiel("12.03.2023 23:18", 1235);

        $spielAenderung = new SpielAenderung($spielA, $spielB);
        
        $this->assertEquals("Halle von [1234] zu [1235]", $spielAenderung->getAenderung());
    }
    public function test_getAenderung_anwurf_und_halle_geandert(){
        $spielA = $this->auswaertsspiel("12.03.2023 23:18", 1234);
        $spielB = $this->auswaertsspiel("13.03.2023 23:19", 1235);

        $spielAenderung = new SpielAenderung($spielA, $spielB);
        
        $this->assertEquals("Anwurf von [12.03.2023 23:18] zu [13.03.2023 23:19] und Halle von [1234] zu [1235]", $spielAenderung->getAenderung());
    }
}
?>