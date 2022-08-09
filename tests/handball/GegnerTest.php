<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/Gegner.php";

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
}
?>