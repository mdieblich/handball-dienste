<?php

require_once __DIR__."/handball/Dienst.php";
require_once __DIR__."/dao/MannschaftDAO.php";
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

    $kopfzeile = 
        "<tr style=\"background-color:#00407d; color:white\">"
        ."<th style=\"min-width:150px; padding: 3px\">Datum</th>"
        ."<th style=\"padding: 3px\">Halle</th>"
        ."<th style=\"padding: 3px\">Heim</th>"
        ."<th style=\"padding: 3px; border-right:2px solid #00407d\">Gast</th>";
    foreach(Dienstart::values as $dienstart){
        $kurzfrom = substr($dienstart, 0, 1);
        $kopfzeile .= "<th style=\"padding: 3px; text-align:center\" title=\"$dienstart\">$kurzfrom</td>";
    }
    $kopfzeile .= "</tr>";

    global $wpdb;
    // TODO DAOs für Tabellennamen nutzen
    $table_name_spiel = SpielDAO::tableName($wpdb);
    $table_name_dienst = DienstDAO::tableName($wpdb);
    $filter = array();
    if(isset($seit)){
        $filter[] = "anwurf > \"".$seit->format("Y-m-d")."\""; // nur aktuelle Spiele
    }else{
        $filter[] = "anwurf > subdate(current_date, 1)"; // nur aktuelle Spiele
    }
    if(isset($fuerMannschaft)){
        $filter[] = "$table_name_spiel.mannschaft_id=".$fuerMannschaft->id;
    }
    if(isset($vonMannschaft)){
        $filter[] = "$table_name_spiel.id IN (SELECT spiel_id FROM ". $table_name_dienst ." WHERE $table_name_dienst.mannschaft_id=".$vonMannschaft->id.")";
    }
    $spielService = new SpielService();
    $spieleListe = $spielService->loadSpieleMitDiensten(implode(" AND ", $filter)); 

    $tabellenkoerper = "";
    foreach($spieleListe->spiele as $spiel){
        $anwurfDatum = $spiel->anwurf->format("d.m.Y");
        $anwurfZeit = $spiel->anwurf->format("H:i");
        if($anwurfZeit === "00:00"){
            $anwurfZeit = "<span style='color:red'>$anwurfZeit</span>";
        }
        $halle = $spiel->halle;
        $mannschaftsName = $spiel->mannschaft->getName();
        $gegnerName = $spiel->gegner->getName();
        $heim = $mannschaftsName;
        $gast = $gegnerName;
        if(!$spiel->heimspiel){
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
            $dienst = $spiel->getDienst($dienstart);
            $textHighlight = "";
            if(isset($vonMannschaft) && isset($dienst) && isset($dienst->mannschaft) && $dienst->mannschaft->id === $vonMannschaft->id){
                $textHighlight = "color:red;";
            }
            $spielzeile .= "<td style=\"padding: 3px; $textHighlight\">";
            if(isset($dienst)){
                if(isset($dienst->mannschaft)){
                    $spielzeile .= $dienst->mannschaft->getKurzname();
                } else {
                    $spielzeile .= "??";
                }
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