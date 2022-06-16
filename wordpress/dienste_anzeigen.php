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
    $attribute = $matches[1];
    $innerHTML = $matches[2];

    require_once __DIR__."/entity/dienst.php";
    require_once __DIR__."/dao/mannschaft.php";
    require_once __DIR__."/dao/gegner.php";
    $mannschaften = loadMannschaften();
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();
    $alleGegner = $gegnerDAO->getAlleGegner();

    $kopfzeile = 
        "<tr style=\"background-color:#ddddff;\">"
        ."<th style=\"width:200px;\">Datum</th>"
        ."<th>Halle</th>"
        ."<th>Heim</th>"
        ."<th>Gast</th>";
    foreach(Dienstart::values as $dienstart){
        $kopfzeile .= "<td>$dienstart</td>";
    }
    $kopfzeile .= "</tr>";

    require_once __DIR__."/dao/spiel.php";
    $spiele = loadSpieleDeep("1=1", "-date(anwurf) DESC, heimspiel desc, anwurf, mannschaft"); 

    $tabellenkoerper = "";
    foreach($spiele as $spiel){
        $anwurf = $spiel->getAnwurf()->format("d.m.Y H:i");
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
            ."<td>$anwurf</td>"
            ."<td>$halle</td>"
            ."<td>$heim</td>"
            ."<td>$gast</td>";
        foreach(Dienstart::values as $dienstart){
            $spielzeile .= "<td>";
            $dienst = $spiel->getDienst($dienstart);
            if(isset($dienst)){
                $spielzeile .= $mannschaften[$dienst->getMannschaft()]->getName();
            }
            $spielzeile .= "</td>";
        }
        $spielzeile .= "</tr>";
        $tabellenkoerper .= $spielzeile;
    }
    $tabelle = "<table>$kopfzeile $tabellenkoerper</table>";
    return $tabelle;
}
?>