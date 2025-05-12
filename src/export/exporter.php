<?php

require_once __DIR__."/../service/SpielService.php";
require_once __DIR__."/SpielerPlusFile.php";

function exportSpielerPlus(string $mannschaft_kurzbezeichnung){
    global $wpdb;

    $spielService = new SpielService($wpdb);
    $spieleListe = $spielService->loadSpieleFuerMannschaft(
        Mannschaft::getNummerFromKurzname($mannschaft_kurzbezeichnung),
        Mannschaft::getGeschlechtFormKurzname($mannschaft_kurzbezeichnung),
        Mannschaft::getJugendklasseFromKurzname($mannschaft_kurzbezeichnung)
    );

    if(count($spieleListe->spiele) == 0){
        echo "Fehler: Keine Spiele für $mannschaft_kurzbezeichnung gefunden";
        die();
    }
    
    $spf = new SpielerPlusFile($spieleListe->spiele);
    $spf->provideDownload();
}

?>