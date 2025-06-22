<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../src/handball/MannschaftsListe.php";

final class MannschaftsListeTest extends TestCase {

    public function test_findMeldungByNuligaIDs(){
        // arrange
        $herren1 = new Mannschaft();
        $herren1->id = 99;

        $meldung1 = new MannschaftsMeldung();
        $meldung1->nuligaLigaID = 123;
        $meldung1->nuligaTeamID = 456;
        $meldung1->mannschaft = $herren1;
        $herren1->meldungen[] = $meldung1;

        $herren2 = new Mannschaft();
        $herren2->id = 5000;

        $meldung2 = new MannschaftsMeldung();
        $meldung2->nuligaLigaID = 789;
        $meldung2->nuligaTeamID = 666;
        $meldung2->mannschaft = $herren2;
        $herren2->meldungen[] = $meldung2;

        $liste = new MannschaftsListe([$herren1, $herren2]);

        // act
        $foundMannschaft = $liste->findMeldungByNuligaIDs(123, 456);

        // assert
        $this->assertEquals($herren1->id,  $foundMannschaft->id);
    }
}