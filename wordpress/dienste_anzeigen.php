<?php

require_once __DIR__."/entity/Dienst.php";

require_once __DIR__."/dao/MannschaftDAO.php";
require_once __DIR__."/dao/GegnerDAO.php";

require_once __DIR__."/service/SpielService.php";

function dienst_tabelle_einblenden($content){
    return preg_replace_callback(
        "/<dienste ?(.*)>(.*)<\/dienste>/", 
        'dienste_tabellen_ersetzen', 
        $content
    );
}

function dienste_tabellen_ersetzen(array $matches){
    $kompletterTreffer = $matches[0];
    $attributString = $matches[1];
    $innerHTML = $matches[2];

    $mannschaftDAO = new MannschaftDAO();
    $mannschaftsListe = $mannschaftDAO->loadMannschaften();

    preg_match_all("/(\w*)=\"([\w\d\s\.]*)\"/", $attributString, $attributeArray);
    $attributeKeys = $attributeArray[1];
    $attributeValues = $attributeArray[2];
    $vonMannschaft = null;
    $fuerMannschaft = null;
    $seit = null;
    for($i=0; $i<count($attributeKeys); $i++){
        switch($attributeKeys[$i]){
            case "von" : $vonMannschaft  = $mannschaftsListe->getMannschaftFromName($attributeValues[$i]); break;
            case "fuer": $fuerMannschaft = $mannschaftsListe->getMannschaftFromName($attributeValues[$i]); break;
            case "seit": $seit = getDateFromString($attributeValues[$i]); break;
        }
    }
    $gegnerDAO = new GegnerDAO();
    $alleGegner = $gegnerDAO->loadGegner();

    $kopfzeile = 
        "<tr style=\"background-color:#00407d; color:white\">"
        ."<th style=\"min-width:150px; padding: 3px\">Datum</th>"
        ."<th style=\"padding: 3px\">Halle</th>"
        ."<th style=\"padding: 3px\">Heim</th>"
        ."<th style=\"padding: 3px; border-right:2px solid #00407d\">Gast</th>";
    foreach(Dienstart::values as $dienstart){
        $kurzfrom = substr($dienstart, 0, 1);
        $kopfzeile .= "<th style=\"padding: 3px; text-align:center\">$kurzfrom</td>";
    }
    $kopfzeile .= "</tr>";

    global $wpdb;
    // TODO DAOs für Tabellennamen nutzen
    $table_name_spiel = $wpdb->prefix."spiel";
    $table_name_dienst = $wpdb->prefix."dienst";
    $filter = array();
    if(isset($seit)){
        $filter[] = "anwurf > \"".$seit->format("Y-m-d")."\""; // nur aktuelle Spiele
    }else{
        $filter[] = "anwurf > subdate(current_date, 1)"; // nur aktuelle Spiele
    }
    if(isset($fuerMannschaft)){
        $filter[] = "$table_name_spiel.mannschaft=".$fuerMannschaft->id;
    }
    if(isset($vonMannschaft)){
        $filter[] = "$table_name_spiel.id IN (SELECT spiel FROM ". $wpdb->prefix ."dienst WHERE $table_name_dienst.mannschaft=".$vonMannschaft->id.")";
    }
    $spielService = new SpielService();
    $spiele = $spielService->loadSpieleMitDiensten(implode(" AND ", $filter)); 

    $tabellenkoerper = "";
    foreach($spiele as $spiel){
        $anwurfDatum = $spiel->getAnwurf()->format("d.m.Y");
        $anwurfZeit = $spiel->getAnwurf()->format("H:i");
        if($anwurfZeit === "00:00"){
            $anwurfZeit = "<span style='color:red'>$anwurfZeit</span>";
        }
        $halle = $spiel->getHalle();
        $mannschaftsName = $mannschaftsListe->mannschaften[$spiel->getMannschaft()]->getName();
        $gegnerName = $alleGegner[$spiel->getGegner()]->getName();
        $heim = $mannschaftsName;
        $gast = $gegnerName;
        if(!$spiel->isHeimspiel()){
            $heim = $gegnerName;
            $gast = $mannschaftsName;
        }
        $spielzeile = 
            "<tr>"
            ."<td style=\"padding: 3px;\">$anwurfDatum $anwurfZeit</td>"
            ."<td style=\"padding: 3px;\">$halle</td>"
            ."<td style=\"padding: 3px;\">$heim</td>"
            ."<td style=\"padding: 3px; border-right:2px solid #00407d\">$gast</td>";
        foreach(Dienstart::values as $dienstart){
            $spielzeile .= "<td style=\"padding: 3px;\">";
            $dienst = $spiel->getDienst($dienstart);
            if(isset($dienst)){
                $spielzeile .= $mannschaftsListe->mannschaften[$dienst->getMannschaft()]->getKurzname();
            }
            $spielzeile .= "</td>";
        }
        $spielzeile .= "</tr>";
        $tabellenkoerper .= $spielzeile;
    }
    $tabelle = "<table cellpadding=\"3\" style=\"border-collapse:separate; border-spacing:0px\">$kopfzeile $tabellenkoerper</table>";
    return $tabelle;
}

function getDateFromString(string $string): ?DateTime{
    $date = DateTime::createFromFormat("d.m.Y", $string);
    if($date){
        return $date;
    }
    return null;
}

?>