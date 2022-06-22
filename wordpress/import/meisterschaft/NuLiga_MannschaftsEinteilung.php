<?php

require_once __DIR__."/../PageGrabber.php";

class NuLiga_MannschaftsEinteilung{
    public string $mannschaftsBezeichnung;
    public string $meiterschaftsKuerzel;
    public string $liga;
    public string $liga_id;
    // TODO: Rausfinden der Team-ID

    public static function fromTabellenzeile(array $zellen): NuLiga_MannschaftsEinteilung {
        $einteilung = new NuLiga_MannschaftsEinteilung();
        $einteilung->mannschaftsBezeichnung = sanitizeContent($zellen[0]->textContent);
        $einteilung->liga = sanitizeContent($zellen[1]->textContent);


        $linkElement = extractChildrenByTags($zellen[1], "a")[0];
        $url = $linkElement->attributes->getNamedItem("href")->value;
        preg_match('/championship=(.*)&/', $url, $championShipMatches);
        preg_match('/group=(.*)/', $url, $groupMatches);
        $einteilung->meiterschaftsKuerzel = urldecode($championShipMatches[1]);
        $einteilung->liga_id = $groupMatches[1];

        return $einteilung;
    }
}

?>