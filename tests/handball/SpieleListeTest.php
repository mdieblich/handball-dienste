<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../handball/SpieleListe.php";

final class SpieleListeTest extends TestCase {

    private int $mannschaft_count = 0;
    private function createMannschaft(): Mannschaft{
        $mannschaft = new Mannschaft();
        $mannschaft->id = $this->mannschaft_count++;
        return $mannschaft;
    }

    private int $spiel_count = 0;
    private function createSpiele(Mannschaft $mannschaft, int $anzahl_spiele, array $dienstarten = []): array{
        $spiele = array();
        for($i = 0; $i<$anzahl_spiele; $i++){
            $spiel = new Spiel();
            $spiel->id = $this->spiel_count++;
            $spiel->mannschaft = $mannschaft;
            
            foreach($dienstarten as $dienstart){
                $spiel->createDienst($dienstart);
            }

            $spiele[] = $spiel;
        }
        return $spiele;
    }

    private function assignDienste(array $spiele, Mannschaft $mannschaft){
        foreach($spiele as $spiel){
            foreach($spiel->dienste as $dienst){
                $dienst->mannschaft = $mannschaft;
            }
        }
    }

    private function getDienste(array $spiele): array {
        $dienste = array();
        foreach($spiele as $spiel){
            foreach($spiel->dienste as $dienst){
                $dienste[] = $dienst;
            }
        }
        return $dienste;
    }
    
    // ##########################################
    // getDiensteProMannschaft()
    // ##########################################
    public function test_getDiensteProMannschaft_leere_Liste(){
        $spieleListe = new SpieleListe();

        $diensteProMannschaft = $spieleListe->getDiensteProMannschaft();

        $this->assertEmpty($diensteProMannschaft);
    }
    public function test_getDiensteProMannschaft_liste_ohne_Dienste(){
        $h1 = $this->createMannschaft();
        $d1 = $this->createMannschaft();
        $spieleH1 = $this->createSpiele($h1, 5);
        $spieleD1 = $this->createSpiele($d1, 4);
        $spieleListe = new SpieleListe(array_merge($spieleH1, $spieleD1));

        $diensteProMannschaft = $spieleListe->getDiensteProMannschaft();

        $this->assertEmpty($diensteProMannschaft);
    }
    public function test_getDiensteProMannschaft_liste_ohne_zugewiesene_Dienste(){
        $h1 = $this->createMannschaft();
        $d1 = $this->createMannschaft();
        $spieleH1 = $this->createSpiele($h1, 5, ["Aufbau", "Aufbau", "Abbau"]);
        $spieleD1 = $this->createSpiele($d1, 4, ["Aufbau", "Abbau"]);
        $spieleListe = new SpieleListe(array_merge($spieleH1, $spieleD1));

        $diensteProMannschaft = $spieleListe->getDiensteProMannschaft();

        $this->assertEmpty($diensteProMannschaft);
    }
    public function test_getDiensteProMannschaft_zugewiesene_Dienste(){
        $h1 = $this->createMannschaft();
        $d1 = $this->createMannschaft();
        $spieleH1 = $this->createSpiele($h1, 5, ["Aufbau", "Abbau"]);
        $spieleD1 = $this->createSpiele($d1, 4, ["Catering"]);
        $this->assignDienste($spieleH1, $d1);
        $this->assignDienste($spieleD1, $h1);
        $spieleListe = new SpieleListe(array_merge($spieleH1, $spieleD1));

        $diensteProMannschaft = $spieleListe->getDiensteProMannschaft();

        $expectedMap = array();
        $expectedMap[$h1->id] = $this->getDienste($spieleD1);
        $expectedMap[$d1->id] = $this->getDienste($spieleH1);
        $this->assertEquals($expectedMap, $diensteProMannschaft);
    }

}
?>