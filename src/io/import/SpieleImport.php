<?php


require_once __DIR__."/../../log/Log.php";

require_once __DIR__."/ImportSchritt.php";
require_once __DIR__."/../../handball/dienst/DienstAenderungsPlan.php";
require_once __DIR__."/nuliga/entities/NuLigaSpiel.php";

require_once __DIR__."/nuliga/pages/NuLiga_SpiellisteTeam.php";
require_once __DIR__."/nuliga/pages//NuLiga_Ligatabelle.php";

require_once __DIR__."/../../db/dao/MannschaftDAO.php";
require_once __DIR__."/../../db/dao/MannschaftsMeldungDAO.php";
require_once __DIR__."/../../db/dao/MeisterschaftDAO.php";
require_once __DIR__."/../../db/dao/SpielDAO.php";
require_once __DIR__."/../../db/dao/nuliga/NuLigaSpielDAO.php";
require_once __DIR__."/../../db/dao/DienstDAO.php";

require_once __DIR__."/../../db/service/MannschaftService.php";
require_once __DIR__."/../../db/service/GegnerService.php";

class SpieleImport {
    private $dbhandle;
    private Log $logfile;
    private HttpClient $httpClient;

    public function __construct($dbhandle, Log $logfile=null, HttpClient $httpClient=null) {
        $this->dbhandle = $dbhandle;
        $this->logfile = $logfile ?? new NoLog();
        $this->httpClient = $httpClient ?? new CurlHttpClient($this->logfile);
    }
    public function fetchAllNuligaSpielelisten(): array{
        $mannschaftService = new MannschaftService($this->dbhandle);
        $mannschaftsListe = $mannschaftService->loadMannschaftenMitMeldungen();
        $nuligaPages = array();
        foreach ($mannschaftsListe->mannschaften as $mannschaft) {
            foreach($mannschaft->meldungen as $mannschaftsMeldung) {
                $nuligaPage = new NuLiga_SpiellisteTeam(
                    $mannschaftsMeldung->meisterschaft->kuerzel, 
                    $mannschaftsMeldung->nuligaLigaID, 
                    $mannschaftsMeldung->nuligaTeamID,
                    $this->logfile,
                    $this->httpClient
                );
                $nuligaPages[] = $nuligaPage->saveLocally();
            }
        }
        return $nuligaPages;
    }

    public function extractNuligaSpiele(): void{
        $nuligaSpielDAO = new NuligaSpielDAO($this->dbhandle);
        $cachedPages = NuLiga_SpiellisteTeam::getAllCachedPages($this->logfile, $this->httpClient);
        foreach ($cachedPages as $nuligaPage) {
            $this->logfile->log("Lese NuLiga-Spiele von $nuligaPage->url");
            $nuligaSpiele = $nuligaPage->getNuLigaSpiele();
            foreach ($nuligaSpiele as $nuligaSpiel) {
                $this->logfile->log("Speichere extrahiertes Spiel in DB: {$nuligaSpiel->getLogOutput()}");
                $nuligaSpielDAO->insert($nuligaSpiel);
            }
            $nuligaPage->deleteLocally();
        }
    }
}
