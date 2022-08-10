<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/NahgelegeneSpiele.php";
require_once __DIR__."/../../handball/Spiel.php";

final class NahgelegeneSpieleTest extends TestCase {

    private int $nextID = 1;

    private function createSpiel(string $anwurf): Spiel {
        $spiel = new Spiel();
        $spiel->id = $this->nextID++;
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);
        return $spiel;
    }
     
    // ##########################################
    // updateWith()
    // ##########################################
    public function test_updateWith_gleichzeitigem_Spiel(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        
        $spielKurzDavor = $this->createSpiel("10.08.2022 19:00");
        $nahgelegeneSpiele->updateWith($spielKurzDavor);
        
        $this->assertEquals($spielKurzDavor, $nahgelegeneSpiele->gleichzeitig);
    }
    
    public function test_updateWith_vorherigem_Spiel(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        
        $spielGestern = $this->createSpiel("09.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielGestern);
        
        $this->assertEquals($spielGestern, $nahgelegeneSpiele->vorher);
    }
    public function test_updateWith_näherem_Spiel_davor_überschreibt_vorheriges_Spiel(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        $spielVorgestern = $this->createSpiel("08.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielVorgestern);
        
        $spielGestern = $this->createSpiel("09.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielGestern);
        
        
        $this->assertEquals($spielGestern, $nahgelegeneSpiele->vorher);
    }
    public function test_updateWith_früherem_Spiel_überschreibt_nichts(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
         
        $spielGestern = $this->createSpiel("09.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielGestern);
        
        $spielVorgestern = $this->createSpiel("08.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielVorgestern);
        
        $this->assertEquals($spielGestern, $nahgelegeneSpiele->vorher);
    }
    
    public function test_updateWith_späterem_Spiel(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        
        $spielMorgen = $this->createSpiel("11.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielMorgen);
        
        $this->assertEquals($spielMorgen, $nahgelegeneSpiele->nachher);
    }
    public function test_updateWith_näherem_Spiel_danach_überschreibt_vorheriges_Spiel(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        $spielÜbermorgen = $this->createSpiel("12.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielÜbermorgen);
        
        $spielMorgen = $this->createSpiel("11.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielMorgen);
        
        $this->assertEquals($spielMorgen, $nahgelegeneSpiele->nachher);
    }
    public function test_updateWith_späterem_Spiel_überschreibt_nichts(){
        $spielHeute = $this->createSpiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        $spielMorgen = $this->createSpiel("11.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielMorgen);
         
        $spielÜbermorgen = $this->createSpiel("12.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielÜbermorgen); 
        
        $this->assertEquals($spielMorgen, $nahgelegeneSpiele->nachher);
    }
}
?>