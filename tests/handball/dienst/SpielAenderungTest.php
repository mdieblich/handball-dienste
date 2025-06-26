<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/handball/dienst/SpielAenderung.php";

final class SpielAenderungTest extends TestCase {

    private function auswaertsspiel(string $anwurf, string $halle = "3117"): Spiel {
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
        $spielAlt = $this->auswaertsspiel("12.03.2023 23:18", "1234");
        $spielNeu = $this->auswaertsspiel("12.03.2023 23:18", "1235");

        $spielAenderung = new SpielAenderung($spielAlt, $spielNeu);
        
        $this->assertEquals("Halle von [1234] zu [1235]", $spielAenderung->getAenderung());
    }
    public function test_getAenderung_anwurf_und_halle_geandert(){
        $spielAlt = $this->auswaertsspiel("12.03.2023 23:18", "1234");
        $spielNeu = $this->auswaertsspiel("13.03.2023 23:19", "1235");

        $spielAenderung = new SpielAenderung($spielAlt, $spielNeu);
        
        $this->assertEquals("Anwurf von [12.03.2023 23:18] zu [13.03.2023 23:19] und Halle von [1234] zu [1235]", $spielAenderung->getAenderung());
    }
    public function test_getAenderung_anwurf_alt_fehlt(){
        $spielAlt = $this->auswaertsspiel("12.03.2023 23:18");
        $spielAlt->anwurf = null;
        $spielNeu = $this->auswaertsspiel("12.03.2023 23:18");

        $spielAenderung = new SpielAenderung($spielAlt, $spielNeu);
        
        $this->assertEquals("Anwurf [fehlte] und ist jetzt [12.03.2023 23:18]", $spielAenderung->getAenderung());
    }
    public function test_getAenderung_anwurf_neu_fehlt(){
        $spielAlt = $this->auswaertsspiel("12.03.2023 23:18");
        $spielNeu = $this->auswaertsspiel("12.03.2023 23:18");
        $spielNeu->anwurf = null;

        $spielAenderung = new SpielAenderung($spielAlt, $spielNeu);
        
        $this->assertEquals("Anwurf war [12.03.2023 23:18] und ist jetzt [noch nicht festgelegt]", $spielAenderung->getAenderung());
    }
}
?>