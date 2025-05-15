<?php

require_once __DIR__."/../../Webpage.php";
require_once __DIR__."/../entities/NuLiga_Meisterschaft.php";

class NuLiga_MannschaftsUndLigenEinteilung extends Webpage{

    public function __construct(int $club_id, Log $logfile){
        parent::__construct("https://hnr-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubTeams?"
            ."club=".$club_id, $logfile);
    }

    public function getMeisterschaften(): array{
        $meisterschaften = array();

        $contentDiv = $this->dom->getElementById("content-row1");
        $tabelle = $contentDiv->getElementsByTagName("table")[0];
        $tabellenZeilen = $this->extractTabellenZeilen($tabelle);
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
                $currentMeisterschaft->mannschaftsEinteilungen[] = $this->MannschaftsEinteilungfromTabellenzeile($zellen);
            }
        }

        return $meisterschaften;
    }

    private function MannschaftsEinteilungfromTabellenzeile(array $zellen): NuLiga_MannschaftsEinteilung {
        $einteilung = new NuLiga_MannschaftsEinteilung();
        $einteilung->mannschaftsBezeichnung = $this->sanitizeContent($zellen[0]->textContent);
        $einteilung->liga = $this->sanitizeContent($zellen[1]->textContent);


        $linkElement = $this->extractChildrenByTags($zellen[1], "a")[0];
        $url = $linkElement->attributes->getNamedItem("href")->value;
        preg_match('/championship=(.*)&/', $url, $championShipMatches);
        preg_match('/group=(.*)/', $url, $groupMatches);
        $einteilung->meisterschaftsKuerzel = urldecode($championShipMatches[1]);
        $einteilung->liga_id = $groupMatches[1];

        return $einteilung;
    }

    private function isMeisterschaftsZeile($zellen): bool{
        return count($zellen) == 1;
    }
    private function isKopfZeile($zellen): bool{
        return count($zellen) == 1;
    }
}

?>