<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/tool/grabber/PageGrabber.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/tool/grabber/NuLigaSpiel.php";

class SpieleGrabber {
    public DomDocument $dom;
    private DOMXPath $xpath;

    public function __construct(string $meisterschaft, int $gruppe, int $team_id){
        $url = "https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?"
            ."teamtable=".$team_id
            ."&pageState=vorrunde"
            ."&championship=".$meisterschaft
            ."&group=".$gruppe;
            echo "URL = $url<br>";
        $this->dom = getDOMFromSite($url);
        $this->xpath = new DOMXPath($this->dom);
    }

    public function getSpiele() : array {
        $spiele  = array();

        $tabelle = $this->findSpielTabelle();
        if(isset($tabelle)){
            $tabellenZeilen = extractTabellenZeilen($tabelle);
            $spielZeilen = array_slice($tabellenZeilen, 1);
            foreach ($spielZeilen as $childNode){
                $spiele[] = $this->extractSpiel($childNode);
            }
            
        }

        return $spiele;
    }

    private function findSpielTabelle(): ?DOMElement {
        $spielTerminUeberschrift = $this->findSpielTerminUeberschrift();
        if(!isset($spielTerminUeberschrift)){
            return null;
        }
        $nextelement = $this->xpath->query("following-sibling::*[1]", $spielTerminUeberschrift)->item(0);
        if($nextelement->nodeName !== "table"){
            return null;
        }
        return $nextelement;
    }

    private function findSpielTerminUeberschrift(): ?DOMElement {
        foreach($this->dom->getElementsByTagName("h2") as $h2){
            if(strtolower(trim($h2->textContent)) == "spieltermine"){
                return $h2;
            }
        }
        return null;
    }

    private function extractTabellenZeilen($tabelle): array {
        $tabellenZeile = array();
        foreach ($tabelle->childNodes as $childNode){
            if($childNode->nodeName === "tr"){
                $tabellenZeile[] = $childNode;
            }
        }
        return $tabellenZeile;
    }

    private function extractSpiel(DOMElement $tabellenZeile): NuLigaSpiel {
        $zellen = $this->extractTabellenZellen($tabellenZeile);
        return NuLigaSpiel::fromTabellenZellen($zellen);
    }

    private function extractTabellenZellen(DOMElement $zeile): array {
        $zellen = array();
        foreach ($zeile->childNodes as $childNode) {
            if($childNode->nodeName === "td"){
                $zellen[] = $childNode;
            }
        }
        return $zellen;
    }
}

?>