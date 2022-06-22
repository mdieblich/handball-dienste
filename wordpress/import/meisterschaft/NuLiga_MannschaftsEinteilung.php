<?php

require_once __DIR__."/../PageGrabber.php";
require_once __DIR__."/NuLiga_Ligatabelle.php";

class NuLiga_MannschaftsEinteilung{
    public string $mannschaftsBezeichnung;
    public string $meisterschaftsKuerzel;
    public string $liga;
    public int $liga_id;
    public int $team_id;

    public static function fromTabellenzeile(array $zellen, string $vereinsname): NuLiga_MannschaftsEinteilung {
        $einteilung = new NuLiga_MannschaftsEinteilung();
        $einteilung->mannschaftsBezeichnung = sanitizeContent($zellen[0]->textContent);
        $einteilung->liga = sanitizeContent($zellen[1]->textContent);


        $linkElement = extractChildrenByTags($zellen[1], "a")[0];
        $url = $linkElement->attributes->getNamedItem("href")->value;
        preg_match('/championship=(.*)&/', $url, $championShipMatches);
        preg_match('/group=(.*)/', $url, $groupMatches);
        $einteilung->meisterschaftsKuerzel = urldecode($championShipMatches[1]);
        $einteilung->liga_id = $groupMatches[1];

        $ligaTabellenSeite = new NuLiga_Ligatabelle($url);
        $einteilung->team_id = $ligaTabellenSeite->extractTeamID($vereinsname);

        return $einteilung;
    }
}

?>