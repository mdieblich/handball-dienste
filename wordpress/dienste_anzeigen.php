<?php

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

    require_once __DIR__."/dao/mannschaft.php";
    $mannschaften = loadMannschaften();

    preg_match_all("/(\w*)=\"([\w\d\s\.]*)\"/", $attributString, $attributeArray);
    $attributeKeys = $attributeArray[1];
    $attributeValues = $attributeArray[2];
    $vonMannschaft = null;
    $fuerMannschaft = null;
    $seit = null;
    for($i=0; $i<count($attributeKeys); $i++){
        switch($attributeKeys[$i]){
            case "von" : $vonMannschaft  = getMannschaftFromName($mannschaften, $attributeValues[$i]); break;
            case "fuer": $fuerMannschaft = getMannschaftFromName($mannschaften, $attributeValues[$i]); break;
            case "seit": $seit = getDateFromString($attributeValues[$i]); break;
        }
    }
    require_once __DIR__."/entity/dienst.php";
    require_once __DIR__."/dao/gegner.php";
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();
    $alleGegner = $gegnerDAO->getAlleGegner();

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

    require_once __DIR__."/dao/SpielDAO.php";
    global $wpdb;
    $table_name_spiel = $wpdb->prefix."spiel";
    $table_name_dienst = $wpdb->prefix."dienst";
    $filter = array();
    if(isset($seit)){
        $filter[] = "anwurf > \"".$seit->format("Y-m-d")."\""; // nur aktuelle Spiele
    }else{
        $filter[] = "anwurf > subdate(current_date, 1)"; // nur aktuelle Spiele
    }
    if(isset($fuerMannschaft)){
        $filter[] = "$table_name_spiel.mannschaft=".$fuerMannschaft->getID();
    }
    if(isset($vonMannschaft)){
        $filter[] = "$table_name_spiel.id IN (SELECT spiel FROM ". $wpdb->prefix ."dienst WHERE $table_name_dienst.mannschaft=".$vonMannschaft->getID().")";
    }
    $spielDAO = new SpielDAO();
    $spiele = $spielDAO->loadSpieleDeep(implode(" AND ", $filter)); 

    $tabellenkoerper = "";
    foreach($spiele as $spiel){
        $anwurfDatum = $spiel->getAnwurf()->format("d.m.Y");
        $anwurfZeit = $spiel->getAnwurf()->format("H:i");
        if($anwurfZeit === "00:00"){
            $anwurfZeit = "<span style='color:red'>$anwurfZeit</span>";
        }
        $halle = $spiel->getHalle();
        $mannschaft = $mannschaften[$spiel->getMannschaft()]->getName();
        $gegner = $alleGegner[$spiel->getGegner()]->getName();
        $heim = $mannschaft;
        $gast = $gegner;
        if(!$spiel->isHeimspiel()){
            $heim = $gegner;
            $gast = $mannschaft;
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
                $spielzeile .= $mannschaften[$dienst->getMannschaft()]->getKurzname();
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