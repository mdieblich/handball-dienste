<?php

require_once __DIR__."/../../log/Log.php";

require_once __DIR__."/../PageGrabber.php";
require_once __DIR__."/NuLiga_Meisterschaft.php";

class NuLiga_MannschaftsUndLigenEinteilung {
    public string $url;
    public DomDocument $dom;
    private DOMXPath $xpath;

    public function __construct(int $club_id, Log $logfile){
        $this->url = "https://hnr-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubTeams?"
            ."club=".$club_id;
        $logfile->log("Lade Daten von ".$this->url);
        $this->dom = getDOMFromSite($this->url);
        $this->xpath = new DOMXPath($this->dom);
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
            $zellen = extractTabellenZellen($tabellenZeile);
            if($this->isMeisterschaftsZeile($zellen)){
                $currentMeisterschaft = new NuLiga_Meisterschaft();
                $currentMeisterschaft->name = sanitizeContent($zellen[0]->textContent);
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
}

?>