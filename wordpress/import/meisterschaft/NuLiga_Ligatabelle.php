<?php
require_once __DIR__."/../PageGrabber.php";

class NuLiga_Ligatabelle {
    public string $url;
    public DomDocument $dom;
    private DOMXPath $xpath;

    public function __construct(string $meisterschaft, int $nuliga_liga_id){
        $this->url = "https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/groupPage?championship=".urlencode($meisterschaft)."&group=".$nuliga_liga_id;
        $this->dom = getDOMFromSite($this->url);
        $this->xpath = new DOMXPath($this->dom);
    }

    public function extractTeamID(string $vereinsname, int $mannschaftsNummer): ?int {
        $gesuchteMannschaft = $vereinsname;
        switch($mannschaftsNummer) {
            case 1: break;
            case 2: $gesuchteMannschaft .= " II"; break;
            case 3: $gesuchteMannschaft .= " III"; break;
            case 4: $gesuchteMannschaft .= " IV"; break;
            case 5: $gesuchteMannschaft .= " V"; break;
            default: $gesuchteMannschaft .= " ".$mannschaftsNummer; break;
        }
        foreach($this->extractMannschaftsZellen() as $mannschaftsZelle){
            $mannschaftsName = sanitizeContent($mannschaftsZelle->textContent);
            if($mannschaftsName === $gesuchteMannschaft){
                return $this->extractTeamIDFromZelle($mannschaftsZelle);
            }
        }
        return null;
    }

    private function extractMannschaftsZellen(): array {
        $mannschaftsZellen = array();
        foreach($this->extractMannschaftsZeilen() as $tabellenZeile){
            $zellen = extractTabellenZellen($tabellenZeile);
            $mannschaftsZellen[] = $zellen[2];
        }
        return $mannschaftsZellen;
    }

    private function extractMannschaftsZeilen(): array{
        $contentDiv = $this->dom->getElementById("content-row2");
        if(empty($contentDiv)){
            return array();
        }
        $tabelle = $contentDiv->getElementsByTagName("table")[0];
        return extractTabellenZeilen($tabelle);
    }

    private function extractTeamIDFromZelle($mannschaftsZelle): int{
        $linkElement = extractChildrenByTags($mannschaftsZelle, "a")[0];
        $url = $linkElement->attributes->getNamedItem("href")->value;
        preg_match('/teamtable=(\d*)/', $url, $teamtableMatches);
        return $teamtableMatches[1];
    }

    public function extractGegnerNamen(string $vereinsname): array {
        $gegner = array();

        foreach($this->extractMannschaftsZellen() as $mannschaftsZelle){
            $mannschaftsName = sanitizeContent($mannschaftsZelle->textContent);
            if(strpos($mannschaftsName, $vereinsname) === 0){
                continue;
            }
            if(strpos($mannschaftsName, "Mannschaft") === 0){
                continue;
            }
            $gegner[] = $mannschaftsName;
        }
        return $gegner;
    } 

}

?>