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
require_once __DIR__."/../../db/dao/Spiel_toBeImportedDAO.php";
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

    public function convertSpiele(string $vereinsname=null): void {
        if($vereinsname === null){
            // Aus Wordpress-Optionen lesen
            $vereinsname = get_option('vereinsname');
        }

        $nuligaSpielDAO = new NuligaSpielDAO($this->dbhandle);
        $importedSpieleDAO = new Spiel_toBeImportedDAO($this->dbhandle);
        $mannschaftService = new MannschaftService($this->dbhandle);
        $mannschaftsListe = $mannschaftService->loadMannschaftenMitMeldungen();

        $nuligaSpiele = $nuligaSpielDAO->fetchAll();
        foreach ($nuligaSpiele as $nuligaSpiel) {
            $this->logfile->log("Konvertiere NuLiga-Spiel {$nuligaSpiel->getLogOutput()}");
            if($nuligaSpiel->isSpielfrei()){
                $this->logfile->log("Spiel {$nuligaSpiel->getLogOutput()} ist spielfrei, überspringe Konvertierung.");
                continue;
            }
            if($nuligaSpiel->isUngueltig()){
                $this->logfile->log("Spiel {$nuligaSpiel->getLogOutput()} ist ungültig, überspringe Konvertierung.");
                continue;
            }

            $meldung = $mannschaftsListe->findMeldungByNuligaIDs($nuligaSpiel->nuligaLigaID, $nuligaSpiel->nuligaTeamID);
            if(empty($meldung)){
                $this->logfile->log("Keine Mannschafts-Meldung für NuLiga-Spiel {$nuligaSpiel->getLogOutput()} gefunden, überspringe Konvertierung.");
                continue;
            }

            $spiel = $nuligaSpiel->extractSpielForImport($meldung, $vereinsname);
            
            if($spiel !== null) {
                $this->logfile->log("Speichere Spiel in DB");
                $importedSpieleDAO->insert($spiel);
            } else {
                $this->logfile->log("Konvertierung von NuLiga-Spiel {$nuligaSpiel->getLogOutput()} fehlgeschlagen.");
            }
            $nuligaSpielDAO->delete(array('id' => $nuligaSpiel->id));
        }
    }
    public function sucheGegner(): void{
        $importedSpieleDAO = new Spiel_toBeImportedDAO($this->dbhandle);
        $gegnerService = new GegnerService($this->dbhandle);
        $alleGegner = $gegnerService->loadAlleGegner();

        $alleSpiele = $importedSpieleDAO->fetchAll();
        foreach ($alleSpiele as $spiel) {
            foreach($alleGegner as $gegner){
                $this->logfile->log("Suche Gegner für Spiel {$spiel->spielNr} gegen {$spiel->gegnerName}.");
                $gegnerAusSpiel = Gegner::fromName($spiel->gegnerName);
                $gegnerAusSpiel->zugehoerigeMeldung_id = $spiel->meldung_id;
                if($gegner->isSimilarTo($gegnerAusSpiel)){
                    $spiel->gegner_id = $gegner->id;
                    $importedSpieleDAO->update($spiel->id, $spiel);
                    break;
                }
                $this->logfile->log(message: "WARNUNG: Kein passender Gegner für Spiel {$spiel->spielNr} gegen {$spiel->gegnerName} gefunden. Spiel wird aus Import-Warteschlange entfernt.");
                $importedSpieleDAO->delete(array('id' => $spiel->id));
            }
        }
    }
}
