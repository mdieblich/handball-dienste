<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/gegner.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/tool/grabber/SpieleGrabber.php";
    // Vereinssuche
    // https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubInfoDisplay?club=74851
    //
    // Mannschaften und Ligeneinteilung
    // https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubTeams?club=74851
    //
    // Herren 1 Hinrunde
    // https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?teamtable=1744276&pageState=vorrunde&championship=MR+21%2F22&group=274529

$meisterschaft = "MR+21%2F22";
$liga = "Mittelrhein Oberliga Männer Abstiegsrunde";
$liga_id = 274529;
$team = "Turnerkreis Nippes";
$team_id = 1744276;
$herren1Spiele = new SpieleGrabber($meisterschaft, $liga_id, $team_id);

$alleGegner = loadGegner("liga='$liga'");



// INSERT vorbereiten
$insert_gegner = $mysqli->prepare("INSERT INTO gegner (name, liga) VALUES (?, ?)");
    
$gegner_name = "";
$insert_gegner->bind_param("ss", $gegner_name, $liga);
    
    function findOrInsertGegner(string $gegnerName): Gegner{
        global $alleGegner, $liga;
        
        foreach($alleGegner as $gegner){
            if($gegner->getName() === $gegnerName){
                return $gegner;
            }
        }
        // Nix gefunden - einfügen!
        $gegner_id = insertGegner($gegnerName);
        $gegner = new Gegner(array(
            "id" => $gegner_id,
            "name" => $gegnerName,
            "liga" => $liga
        ));
        $alleGegner[$gegner_id] = $gegner;
        return $gegner;
    }

    function insertGegner(string $gegnerName): int {
        global $mysqli;
        global $insert_gegner, $gegner_name;
        
        $gegner_name = $gegnerName;
        $insert_gegner->execute();
        return $mysqli->insert_id;
    }

    $insert_spiel = $mysqli->prepare(
    "INSERT INTO spiel (spielnr, mannschaft, gegner, heimspiel, halle, anwurf) ".
    "VALUES (?, ?, ?, ?, ?, ?)");
    $spielnr = 0;
    $mannschaft_id = 1;
    $gegner_id = 0;
    $isHeimspiel = 1;
    $halle = 0; // Heimspiel-Halle
    $anwurf = "";
    $insert_spiel->bind_param("iiiiis", $spielnr, $mannschaft_id, $gegner_id, $isHeimspiel, $halle, $anwurf);
    
    foreach($herren1Spiele->getSpiele() as $spiel){
        $spielnr = $spiel->getSpielNr();
        if($spiel->getHeimmannschaft() === $team){
            $isHeimspiel = true;
            $gegner_id = findOrInsertGegner($spiel->getGastmannschaft())->getID();
        } else {
            $isHeimspiel = false;
            $gegner_id = findOrInsertGegner($spiel->getHeimmannschaft())->getID();
        }
        $halle = $spiel->getHalle();
        if($spiel->isTerminOffen()){
            $anwurf = null;
        }else {
            $anwurf = $spiel->getAnwurf()->format('Y-m-d H:i:s');
        }
        $insert_spiel->execute();
    }
    ?>