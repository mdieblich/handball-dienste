<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../src/handball/NahgelegeneSpiele.php";
require_once __DIR__."/../../src/handball/Spiel.php";

final class NahgelegeneSpieleTest extends TestCase {

    private int $nextID = 1;

    private function createHeimspiel(string $anwurf): Spiel {
        $spiel = new Spiel();
        $spiel->id = $this->nextID++;
        $spiel->anwurf = DateTime::createFromFormat("d.m.Y H:i", $anwurf);
        $spiel->heimspiel = true;
        $spiel->halle = "3182"; // Nippeser Tälchen
        return $spiel;
    }
     
    // ##########################################
    // updateWith()
    // ##########################################
    public function test_updateWith_gleichzeitigem_Spiel(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        
        $spielKurzDavor = $this->createHeimspiel("10.08.2022 19:00");
        $nahgelegeneSpiele->updateWith($spielKurzDavor);
        
        $this->assertEquals($spielKurzDavor, $nahgelegeneSpiele->gleichzeitig);
    }
    
    public function test_updateWith_vorherigem_Spiel(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        
        $spielGestern = $this->createHeimspiel("09.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielGestern);
        
        $this->assertEquals($spielGestern, $nahgelegeneSpiele->vorher);
    }
    public function test_updateWith_näherem_Spiel_davor_überschreibt_vorheriges_Spiel(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        $spielVorgestern = $this->createHeimspiel("08.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielVorgestern);
        
        $spielGestern = $this->createHeimspiel("09.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielGestern);
        
        
        $this->assertEquals($spielGestern, $nahgelegeneSpiele->vorher);
    }
    public function test_updateWith_früherem_Spiel_überschreibt_nichts(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
         
        $spielGestern = $this->createHeimspiel("09.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielGestern);
        
        $spielVorgestern = $this->createHeimspiel("08.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielVorgestern);
        
        $this->assertEquals($spielGestern, $nahgelegeneSpiele->vorher);
    }
    
    public function test_updateWith_späterem_Spiel(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        
        $spielMorgen = $this->createHeimspiel("11.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielMorgen);
        
        $this->assertEquals($spielMorgen, $nahgelegeneSpiele->nachher);
    }
    public function test_updateWith_näherem_Spiel_danach_überschreibt_vorheriges_Spiel(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        $spielÜbermorgen = $this->createHeimspiel("12.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielÜbermorgen);
        
        $spielMorgen = $this->createHeimspiel("11.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielMorgen);
        
        $this->assertEquals($spielMorgen, $nahgelegeneSpiele->nachher);
    }
    public function test_updateWith_späterem_Spiel_überschreibt_nichts(){
        $spielHeute = $this->createHeimspiel("10.08.2022 20:00");
        $nahgelegeneSpiele = new NahgelegeneSpiele($spielHeute);
        $spielMorgen = $this->createHeimspiel("11.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielMorgen);
         
        $spielÜbermorgen = $this->createHeimspiel("12.08.2022 20:00");
        $nahgelegeneSpiele->updateWith($spielÜbermorgen); 
        
        $this->assertEquals($spielMorgen, $nahgelegeneSpiele->nachher);
    }
}
?>