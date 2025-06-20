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
    function fetchAllNuligaSpielelisten(): array{
        // TODO implement
        return array();
    }
}
