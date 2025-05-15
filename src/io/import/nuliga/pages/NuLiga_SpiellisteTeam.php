<?php
require_once __DIR__."/../../Webpage.php";
require_once __DIR__."/../entities/NuLigaSpiel.php";

class NuLiga_SpiellisteTeam extends Webpage{

    public function __construct(string $meisterschaft, int $gruppe, int $team_id, Log $logfile){
        parent::__construct("https://hnr-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?"
            ."teamtable=".$team_id
            ."&pageState=vorrunde"
            ."&championship=".urlencode($meisterschaft)
            ."&group=".$gruppe,
            $logfile);
    }

    public function getNuLigaSpiele() : array {
        $spiele  = array();

        $tabelle = $this->findSpielTabelle();
        if(isset($tabelle)){
            $tabellenZeilen = $this->extractTabellenZeilen($tabelle);
            $spielZeilen = array_slice($tabellenZeilen, 1);
            $vorherigesSpiel = null;
            foreach ($spielZeilen as $childNode){
                $spiel = $this->extractSpiel($childNode, $vorherigesSpiel);
                if(isset($spiel)){
                    $vorherigesSpiel = $spiel;
                    $spiele[] = $spiel;
                }
            }
            
        }

        return $spiele;
    }

    private function findSpielTabelle(): ?DOMElement {
        $spielTerminUeberschrift = $this->findSpielTerminUeberschrift();
        if(!isset($spielTerminUeberschrift)){
            return null;
        }
        $nextelement = $this->query("following-sibling::*[1]", $spielTerminUeberschrift)->item(0);
        if($nextelement->nodeName !== "table"){
            return null;
        }
        return $nextelement;
    }

    private function findSpielTerminUeberschrift(): ?DOMElement {
        foreach($this->getElementsByTagName("h2") as $h2){
            if(strtolower(trim($h2->textContent)) == "spieltermine"){
                return $h2;
            }
        }
        return null;
    }

    private function extractSpiel(DOMElement $tabellenZeile, ?NuLigaSpiel $vorherigesSpiel): ?NuLigaSpiel {
        $zellen = $this->extractTabellenZellen($tabellenZeile);
        $spiel = new NuLigaSpiel();
        $spiel->wochentag = $this->extractTrimmedContent($zellen[0]);
        $spiel->terminOffen = ($spiel->wochentag == "Termin offen");
        if($spiel->terminOffen){
            $spiel->halle = $this->extractTrimmedContent($zellen[2]);
            $spiel->spielNr = $this->extractTrimmedContent($zellen[3]);
            $spiel->heimmannschaft = $this->extractTrimmedContent($zellen[4]);
            $spiel->gastmannschaft = $this->extractTrimmedContent($zellen[5]);
            $spiel->ergebnisOderSchiris = $this->extractTrimmedContent($zellen[6]);
            $spiel->spielbericht = $this->extractTrimmedContent($zellen[7]);
            $spiel->spielberichtsGenehmigung = $this->extractTrimmedContent($zellen[8]);
        }else {
            $datum = $this->extractTrimmedContent($zellen[1]);
            if(empty($datum)){  // Manchmal sind mehrere Zeilen untereinander, dann wird das Datum weggelassen, siehe https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?teamtable=1797044&pageState=vorrunde&championship=KR+Q+22%2F23&group=297486
                $spiel->datum = $vorherigesSpiel->datum;
            }else{
                $spiel->datum = $datum;
            }
            $spiel->uhrzeit = substr($this->extractTrimmedContent($zellen[2]),0,5);   // format: 19:00, substring, damit etwaige "v" (für "Verlegt") abgeschnitten werden 
            $spiel->halle = $this->extractTrimmedContent($zellen[3]);
            $spiel->spielNr = $this->extractTrimmedContent($zellen[4]);
            $spiel->heimmannschaft = $this->extractTrimmedContent($zellen[5]);
            $spiel->gastmannschaft = $this->extractTrimmedContent($zellen[6]);
            $spiel->ergebnisOderSchiris = $this->extractTrimmedContent($zellen[7]);
            $spiel->spielbericht = $this->extractTrimmedContent($zellen[8]);
            $spiel->spielberichtsGenehmigung = $this->extractTrimmedContent($zellen[9]);
        }
        // leere Zelle: $zellen[10]
        return $spiel;
    }
    
    private function extractTrimmedContent(DOMElement $zelle): string {
        return $this->sanitizeContent($zelle->textContent);
    }
}

?>