<?php
require_once __DIR__."/../../Webpage.php";

class NuLiga_Ligatabelle extends Webpage{

    private string $meisterschaft;
    private int $nuliga_liga_id;

    public function __construct(string $meisterschaft, int $nuliga_liga_id, Log $logfile){
        parent::__construct("https://hnr-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/groupPage?championship=".urlencode($meisterschaft)."&group=".$nuliga_liga_id, $logfile);
        $this->meisterschaft = $meisterschaft;
        $this->nuliga_liga_id = $nuliga_liga_id;
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
            $mannschaftsName = $this->sanitizeContent($mannschaftsZelle->textContent);
            if($mannschaftsName === $gesuchteMannschaft){
                return $this->extractTeamIDFromZelle($mannschaftsZelle);
            }
        }
        return null;
    }

    private function extractMannschaftsZellen(): array {
        $mannschaftsZellen = array();
        foreach($this->extractMannschaftsZeilen() as $tabellenZeile){
            $zellen = $this->extractTabellenZellen($tabellenZeile);
            $mannschaftsZellen[] = $zellen[2];
        }
        return $mannschaftsZellen;
    }

    private function extractMannschaftsZeilen(): array{
        $contentDiv = $this->getElementById("content-row2");
        if(empty($contentDiv)){
            return array();
        }
        $tabelle = $contentDiv->getElementsByTagName("table")[0];
        return $this->extractTabellenZeilen($tabelle);
    }

    private function extractTeamIDFromZelle($mannschaftsZelle): int{
        $linkElement = $this->extractChildrenByTags($mannschaftsZelle, "a")[0];
        $url = $linkElement->attributes->getNamedItem("href")->value;
        preg_match('/teamtable=(\d*)/', $url, $teamtableMatches);
        return $teamtableMatches[1];
    }

    public function extractGegnerNamen(string $vereinsname): array {
        $gegner = array();

        foreach($this->extractMannschaftsZellen() as $mannschaftsZelle){
            $mannschaftsName = $this->sanitizeContent($mannschaftsZelle->textContent);
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
    protected function getCacheFileIdentifier(): string {
        return "championship=".urlencode($this->meisterschaft)."&group=".$this->nuliga_liga_id;
    }
}
?>