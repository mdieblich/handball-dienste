<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/gegner.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/tool/grabber/SpieleGrabber.php";
    // Vereinssuche
    // https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubInfoDisplay?club=74851
    //
    // Mannschaften und Ligeneinteilung
    // https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/clubTeams?club=74851

$alleGegner = loadGegner();

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
$mannschaft_id = 0;
$gegner_id = 0;
$isHeimspiel = 1;
$halle = 0; // Heimspiel-Halle
$anwurf = "";
$insert_spiel->bind_param("iiiiis", $spielnr, $mannschaft_id, $gegner_id, $isHeimspiel, $halle, $anwurf);
    
$mannschaften = loadMannschaften();

foreach($mannschaften as $mannschaft){
    $mannschaft_id = $mannschaft->getID();
    echo "Importiere ".$mannschaft->getName()."<br>";
    $teamName = "Turnerkreis Nippes ";
    for($i=0; $i<$mannschaft->getNummer(); $i++){
        $teamName .= "I";
    }
    $teamName = trim($teamName);
    $spielGrabber = new SpieleGrabber(
        $mannschaft->getMeisterschaft(), 
        $mannschaft->getNuligaLigaID(), 
        $mannschaft->getNuligaTeamID()
    );
    foreach($spielGrabber->getSpiele() as $spiel){
        $spielnr = $spiel->getSpielNr();
        if($spiel->getHeimmannschaft() === $teamName){
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
}

    ?>