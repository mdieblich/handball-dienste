<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__."/../../../../../src/io/import/nuliga/pages/NuLiga_SpiellisteTeam.php";
require_once __DIR__."/../../../../../src/io/import/importer.php"; // for the function deleteAll()
require_once __DIR__."/../../FakeHttpClient.php";

final class NuLiga_SpiellisteTeamTest extends TestCase {
    private FakeHttpClient $httpClient;
    private Log $logfile;

    public function setUp(): void {
        deleteAll(Webpage::CACHEFILE_BASE_DIRECTORY());
        $this->httpClient = new FakeHttpClient();
        $this->logfile = new NoLog();
    }

    public function test_getAllCachedPages() {
        // arrange
        $webpage1 = new NuLiga_SpiellisteTeam("KR 24/25", 1, 2, $this->logfile, $this->httpClient);
        $webpage2 = new NuLiga_SpiellisteTeam("KR 25/26", 2, 3, $this->logfile, $this->httpClient);
        $this->httpClient->set($webpage1->url, "<html><body>Page 1</body></html>");
        $this->httpClient->set($webpage2->url, "<html><body>Page 2</body></html>");
        
        // act
        $webpage1->saveLocally();
        $webpage2->saveLocally();
        $cachedWebPages = NuLiga_SpiellisteTeam::getAllCachedPages($this->logfile, new FakeHttpClient());

        // assert
        $this->assertCount(2, $cachedWebPages);
        $this->assertEquals("<html><body>Page 1</body></html>", $cachedWebPages[0]->getHTML());
        $this->assertEquals("<html><body>Page 2</body></html>", $cachedWebPages[1]->getHTML());
    }
}