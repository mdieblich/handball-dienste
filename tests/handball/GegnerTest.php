<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Gegner.php";
require_once __DIR__."/../../handball/Mannschaft.php";

final class GegnerTest extends TestCase {

    private function hornets(int $nummer): Gegner{
        $gegner = new Gegner();
        $gegner->verein = "Pulheimer SC";
        $gegner->nummer = $nummer;
        return $gegner;
    }
    
    // ##########################################
    // getName()
    // ##########################################
    public function test_Name_von_Hornets_1(){
        $this->assertEquals("Pulheimer SC", $this->hornets(1)->getName());
    }
    public function test_Name_von_Hornets_2(){
        $this->assertEquals("Pulheimer SC II", $this->hornets(2)->getName());
    }
    public function test_Name_von_Hornets_4(){
        $this->assertEquals("Pulheimer SC IV", $this->hornets(4)->getName());
    }
    public function test_Name_von_Hornets_5(){
        $this->assertEquals("Pulheimer SC V", $this->hornets(5)->getName());
    }
    public function test_Name_von_Hornets_9(){
        $this->assertEquals("Pulheimer SC 9", $this->hornets(9)->getName());
    }

    // ##########################################
    // fromName()
    // ##########################################
    public function test_erstelle_Hornets(){
        $this->assertEquals($this->hornets(1), Gegner::fromName("Pulheimer SC"));
    }
    public function test_erstelle_Hornets_1(){
        $this->assertEquals($this->hornets(1), Gegner::fromName("Pulheimer SC I"));
    }
    public function test_erstelle_Hornets_2(){
        $this->assertEquals($this->hornets(2), Gegner::fromName("Pulheimer SC II"));
    }
    public function test_erstelle_Hornets_4(){
        $this->assertEquals($this->hornets(4), Gegner::fromName("Pulheimer SC IV"));
    }

    // ##########################################
    // verschiedene Methoden
    // ##########################################
    public function test_Geschlecht_über_Mannschaftsmeldung_ableiten(){
        $damen1 = new Mannschaft();
        $damen1->geschlecht = GESCHLECHT_W;
        $meldung_verbandsliga = new MannschaftsMeldung();
        $meldung_verbandsliga->mannschaft = $damen1;
        $gegner = new Gegner();
        $gegner->zugehoerigeMeldung = $meldung_verbandsliga;

        $this->assertEquals(GESCHLECHT_W, $gegner->getGeschlecht());
    }

    public function test_Liga_über_Mannschaftsmeldung_ableiten(){
        $meldung_verbandsliga = new MannschaftsMeldung();
        $meldung_verbandsliga->liga = "Verbandsliga";
        $gegner = new Gegner();
        $gegner->zugehoerigeMeldung = $meldung_verbandsliga;

        $this->assertEquals("Verbandsliga", $gegner->getLiga());

    }

}
?>