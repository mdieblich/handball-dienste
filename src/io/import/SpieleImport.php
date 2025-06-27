<?php


require_once __DIR__."/../../log/Log.php";

require_once __DIR__."/ImportSchritt.php";
require_once __DIR__."/DienstAenderung.php";
require_once __DIR__."/../../handball/dienst/DienstAenderungsPlan.php";
require_once __DIR__."/nuliga/entities/NuLigaSpiel.php";

require_once __DIR__."/nuliga/pages/NuLiga_SpiellisteTeam.php";
require_once __DIR__."/nuliga/pages//NuLiga_Ligatabelle.php";

require_once __DIR__."/../../db/dao/MeisterschaftDAO.php";
require_once __DIR__."/../../db/dao/MannschaftDAO.php";
require_once __DIR__."/../../db/dao/MannschaftsMeldungDAO.php";
require_once __DIR__."/../../db/dao/SpielDAO.php";
require_once __DIR__."/../../db/dao/DienstDAO.php";
require_once __DIR__."/../../db/dao/import/Spiel_toBeImportedDAO.php";
require_once __DIR__."/../../db/dao/import/DienstAenderungDAO.php";
require_once __DIR__."/../../db/dao/import/nuliga/NuLigaSpielDAO.php";

require_once __DIR__."/../../db/service/MannschaftService.php";
require_once __DIR__."/../../db/service/GegnerService.php";

class SpieleImport {
    private $dbhandle;
    private Log $logfile;
    private HttpClient $httpClient;

    private MannschaftService $mannschaftService;
    private NuligaSpielDAO $nuligaSpielDAO;
    private Spiel_toBeImportedDAO $spiel_toBeImportedDAO;
    private GegnerDAO $gegnerDAO;
    private SpielDAO $spielDAO;
    private DienstDAO $dienstDAO;
    private DienstAenderungDAO $dienstAenderungDAO;

    public function __construct($dbhandle, Log $logfile=null, HttpClient $httpClient=null) {
        $this->dbhandle = $dbhandle;
        $this->logfile = $logfile ?? new NoLog();
        $this->httpClient = $httpClient ?? new CurlHttpClient($this->logfile);

        $this->mannschaftService = new MannschaftService($this->dbhandle);
        $this->nuligaSpielDAO = new NuligaSpielDAO($this->dbhandle);
        $this->spiel_toBeImportedDAO = new Spiel_toBeImportedDAO($this->dbhandle);
        $this->gegnerDAO = new GegnerDAO($this->dbhandle);
        $this->spielDAO = new SpielDAO($this->dbhandle);
        $this->dienstDAO = new DienstDAO($this->dbhandle);
        $this->dienstAenderungDAO = new DienstAenderungDAO($this->dbhandle);
    }
    public function fetchAllNuligaSpielelisten(): array{
        $mannschaftsListe = $this->mannschaftService->loadMannschaftenMitMeldungen();
        $nuligaPages = [];
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
        $cachedPages = NuLiga_SpiellisteTeam::getAllCachedPages($this->logfile, $this->httpClient);
        foreach ($cachedPages as $nuligaPage) {
            $this->logfile->log("Lese NuLiga-Spiele von $nuligaPage->url");
            $nuligaSpiele = $nuligaPage->getNuLigaSpiele();
            foreach ($nuligaSpiele as $nuligaSpiel) {
                $this->logfile->log("Speichere extrahiertes Spiel in DB: {$nuligaSpiel->getLogOutput()}");
                $this->nuligaSpielDAO->insert($nuligaSpiel);
            }
            $nuligaPage->deleteLocally();
        }
    }
    public function convertSpiele(string $vereinsname=null): void {
        if($vereinsname === null){
            // Aus Wordpress-Optionen lesen
            $vereinsname = get_option('vereinsname');
        }

        $mannschaftsListe = $this->mannschaftService->loadMannschaftenMitMeldungen();

        $nuligaSpiele = $this->nuligaSpielDAO->fetchAll();
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
                $this->spiel_toBeImportedDAO->insert($spiel);
            } else {
                $this->logfile->log("Konvertierung von NuLiga-Spiel {$nuligaSpiel->getLogOutput()} fehlgeschlagen.");
            }
            $this->nuligaSpielDAO->delete(array('id' => $nuligaSpiel->id));
        }
    }
    public function sucheGegner(): void{
        $alleGegner = $this->gegnerDAO->fetchAll();

        $alleSpiele = $this->spiel_toBeImportedDAO->fetchAll();
        foreach ($alleSpiele as $spiel) {
            $this->logfile->log("Suche Gegner für Spiel {$spiel->spielNr} gegen {$spiel->gegnerName}.");
            $gegnerAusSpiel = Gegner::fromName($spiel->gegnerName);
            $gegnerAusSpiel->zugehoerigeMeldung_id = $spiel->meldung_id;
            $gegnerGefunden = false;
            foreach($alleGegner as $gegner){
                if($gegner->isSimilarTo($gegnerAusSpiel)){
                    $spiel->gegner_id = $gegner->id;
                    $this->spiel_toBeImportedDAO->update($spiel->id, $spiel);
                    $gegnerGefunden = true;
                    break;
                }
            }
            if(!$gegnerGefunden){
                $this->logfile->log(message: "WARNUNG: Kein passender Gegner für Spiel {$spiel->spielNr} gegen {$spiel->gegnerName} gefunden. Spiel wird aus Import-Warteschlange entfernt.");
                $this->spiel_toBeImportedDAO->delete(array('id' => $spiel->id));
            }
        }
    }
    public function findExistingSpiele(): void {
        $allezuImportierendenSpiele = $this->spiel_toBeImportedDAO->fetchAll();
        $alleSpiele = $this->spielDAO->fetchAll();

        foreach ($allezuImportierendenSpiele as $spiel_toBeImported) {
            $this->logfile->log("Suche nach bestehendem Spiel für {$spiel_toBeImported->spielNr} gegen {$spiel_toBeImported->gegnerName}.");
            
            $oldSpiel = null;
            foreach ($alleSpiele as $existingSpiel) {
                if( $existingSpiel->spielNr === $spiel_toBeImported->spielNr && 
                $existingSpiel->mannschaftsMeldung_id === $spiel_toBeImported->meldung_id &&
                $existingSpiel->gegner_id === $spiel_toBeImported->gegner_id){
                    $this->logfile->log("Gefunden: Spiel mit id {$existingSpiel->id}.");
                    $oldSpiel = $existingSpiel;
                    break;
                }
            }

            if( $oldSpiel !== null ){
                $spiel_toBeImported->istNeuesSpiel = false;
                $spiel_toBeImported->spielID_alt = $oldSpiel->id;
            } else {
                $spiel_toBeImported->istNeuesSpiel = true;
            }

            $this->spiel_toBeImportedDAO->update($spiel_toBeImported->id, $spiel_toBeImported);
        }
    }

    public function createDienstAenderungen(): void {
        $spieleToBeImported = $this->spiel_toBeImportedDAO->fetchAllForDienstAenderungen();
        foreach($spieleToBeImported as $spielToBeImported){
            $this->logfile->log("Erstelle Dienständerungsplan für Import-Spiel mit ID $spielToBeImported->id");
            $spiel_vorher = $this->spielDAO->fetch("id=$spielToBeImported->spielID_alt");
            $dienste = $this->dienstDAO->fetchAll("spiel_id=$spiel_vorher->id AND id NOT IN (select dienstID from wp_dienstaenderung)");
            foreach($dienste as $dienst){
                $this->logfile->log("$dienst->dienstart ist von Spieländerugen betroffen");
                $aenderung = DienstAenderung::create($dienst->id, $spiel_vorher);
                $this->dienstAenderungDAO->insert($aenderung);
            }
            $spielToBeImported->dienstAenderungenErstellt = true;
            $this->spiel_toBeImportedDAO->update($spielToBeImported->id, $spielToBeImported);
        }
    }
    
    public function updateSpiele(): void {
        $spieleToBeImported = $this->spiel_toBeImportedDAO->fetchAllForUpdate();
        foreach($spieleToBeImported as $spielToBeImported){
            $spiel_vorher = $this->spielDAO->fetch("id=$spielToBeImported->spielID_alt");
            $spiel_nachher = $spielToBeImported->updateSpiel($spiel_vorher);
            $this->spielDAO->update($spiel_nachher->id, $spiel_nachher);
            $this->spiel_toBeImportedDAO->delete(["id"=>$spielToBeImported->id]);
        }
    }

    public function createNeueSpiele(): void {

    }
    // TODO Auf- und Abbau neu prüfen und im Dienständerungsplan hinterlegen
    // TODO  Dienständerungsplan als Emails versenden & aufräumen
}
