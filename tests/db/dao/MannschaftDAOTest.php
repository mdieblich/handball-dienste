<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../src/db/dao/MannschaftDAO.php";
require_once __DIR__."/../MemoryDB.php";

final class MannschaftDAOTest extends TestCase {

    private $dbMock;
    private MannschaftDAO $mannschaftDAO;

    public function setUp(): void {
        $this->dbMock = $this->createMock(MemoryDB::class);
        $this->dbMock->prefix = "mock";
        $this->mannschaftDAO = new MannschaftDAO($this->dbMock);
    }

    public function test_getAllWithGlobalCache_singleDAO() {
        // arrange
        $herren1 = new Mannschaft();
        $herren1->id = 12;
        $herren1->nummer = 1;
        $herren1->geschlecht = GESCHLECHT_M;
        
        $herren2 = new Mannschaft();
        $herren2->id = 89;
        $herren2->nummer = 2;
        $herren2->geschlecht = GESCHLECHT_M;
        
        $this->dbMock
            ->expects($this->once())    // wichtig: Darf nur einmal aufgerufen werden!
            ->method("get_results")
            ->with("SELECT * FROM mockmannschaft ORDER BY jugendklasse, nummer, geschlecht")
            ->willReturn([
                ['id'=>$herren1->id, 'nummer' => $herren1->nummer, 'geschlecht' => $herren1->geschlecht],
                ['id'=>$herren2->id, 'nummer' => $herren2->nummer, 'geschlecht' => $herren2->geschlecht],
            ]);
        
        // act
        $mannschaftsListe1 =  $this->mannschaftDAO->getAllWithGlobalCache();
        $mannschaftsListe2 =  $this->mannschaftDAO->getAllWithGlobalCache();

        // assert
        $expectedMannschaften = [
            $herren1->id => $herren1, 
            $herren2->id => $herren2,
        ];
        $this->assertEquals($expectedMannschaften, $mannschaftsListe1->mannschaften);
        $this->assertEquals($expectedMannschaften, $mannschaftsListe2->mannschaften);
    }

}