<?php
require_once __DIR__."/../PageGrabber.php";

class NuLiga_Ligatabelle_new {
    public string $url;
    public DomDocument $dom;
    private DOMXPath $xpath;

    public function __construct(string $meisterschaft, int $nuliga_liga_id){
        $this->url = "https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/groupPage?championship=".urlencode($meisterschaft)."&group=".$nuliga_liga_id;
        $this->dom = getDOMFromSite($this->url);
        $this->xpath = new DOMXPath($this->dom);
    }

    public function extractTeamID(string $vereinsname): ?int {
        $contentDiv = $this->dom->getElementById("content-row2");
        if(empty($contentDiv)){
            return null;
        }
        $tabelle = $contentDiv->getElementsByTagName("table")[0];
        $tabellenZeilen = extractTabellenZeilen($tabelle);
        foreach($tabellenZeilen as $tabellenZeile){
            $zellen = extractTabellenZellen($tabellenZeile);
            $mannschaftsZelle = $zellen[2];
            $mannschaftsName = sanitizeContent($mannschaftsZelle->textContent);
            if(strpos($mannschaftsName, $vereinsname) === 0){
                return $this->extractTeamIDFromZelle($mannschaftsZelle);
            }
        }
    }

    private function extractTeamIDFromZelle($mannschaftsZelle): int{
        $linkElement = extractChildrenByTags($mannschaftsZelle, "a")[0];
        $url = $linkElement->attributes->getNamedItem("href")->value;
        preg_match('/teamtable=(\d*)/', $url, $teamtableMatches);
        return $teamtableMatches[1];
    }

}

?>