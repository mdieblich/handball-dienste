<?php

require_once __DIR__."/NuLiga_Webpage.php";
require_once __DIR__."/NuLiga_Meisterschaft.php";

class NuLiga_MannschaftsUndLigenEinteilung extends NuLiga_Webpage{

    public function __construct(int $club_id, Log $logfile){
        parent::__construct("https://hnr-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubTeams?"
            ."club=".$club_id, $logfile);
    }

    public function getMeisterschaften(): array{
        $meisterschaften = array();

        $contentDiv = $this->dom->getElementById("content-row1");
        $tabelle = $contentDiv->getElementsByTagName("table")[0];
        $tabellenZeilen = extractTabellenZeilen($tabelle);
        $currentMeisterschaft = null;
        $skipZeile = false;
        foreach($tabellenZeilen as $tabellenZeile){
            if($skipZeile){
                $skipZeile = false;
                continue;
            }
            $zellen = $this->extractTabellenZellen($tabellenZeile);
            if($this->isMeisterschaftsZeile($zellen)){
                $currentMeisterschaft = new NuLiga_Meisterschaft();
                $currentMeisterschaft->name = $this->sanitizeContent($zellen[0]->textContent);
                $meisterschaften[] = $currentMeisterschaft;

                // die nächste Zeile ist eine Kopfzeile
                $skipZeile = true;
            } else {
                $currentMeisterschaft->mannschaftsEinteilungen[] = NuLiga_MannschaftsEinteilung::fromTabellenzeile($zellen);
            }
        }

        return $meisterschaften;
    }

    private function isMeisterschaftsZeile($zellen): bool{
        return count($zellen) == 1;
    }
    private function isKopfZeile($zellen): bool{
        return count($zellen) == 1;
    }

    // public function saveLocally(): string {
    //     $filename = self::CACHEFILE_DIRECTORY().date("Y.m.d_H.i.s").".html";
    //     $fileHandle = fopen($filename, "w");
    //     try{
    //         fwrite($this->fileHandle, $message);
    //         return $filename;
    //     } finally {
    //         fclose($this->fileHandle);
    //     }
    // }

    // public static function CACHEFILE_DIRECTORY(): string{
    //     return plugin_dir_path(__FILE__)."MannschaftenUndLigeneinteilungen/";
    // }
}

?>