<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/spiele.php";

$spiele = loadSpiele(); 

function mannschaftsmatrix($mannschaften): array{
    $matrix = array();
    foreach($mannschaften as $mannschaft){
        $id = $mannschaft->getID();
        $andereMannschaften = $mannschaften;
        unset($andereMannschaften[$id]);
        $matrix[$id] = $andereMannschaften;
    }
    return $matrix;
}

$mannschaften = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";
$matrix = mannschaftsmatrix($mannschaften);

foreach($spiele as $spiel){
    $andereMannschaften = $matrix[$spiel->getMannschaft()];
    $valuesZeitnehmer = createVALUESForDienstart($spiel, "Zeitnehmer", $andereMannschaften);
    $valuesSekretaer = createVALUESForDienstart($spiel, "Sekretär", $andereMannschaften);
    $valuesCorona0 = createVALUESForDienstart($spiel, "Corona", $andereMannschaften);
    $valuesCorona1 = createVALUESForDienstart($spiel, "Corona", $andereMannschaften);
    $sqlInsert = "INSERT INTO dienst (spiel, dienstart, mannschaft, person) VALUES ".
        $valuesZeitnehmer.", ".
        $valuesSekretaer.", ".
        $valuesCorona0.", ".
        $valuesCorona1
    ;
    $mysqli->query($sqlInsert);
}

function createVALUESForDienstart(Spiel $spiel, string $dienstArt, array $mannschaften){
    $dienstMannschaftID = array_rand($mannschaften);
    $dienstMannschaft = $mannschaften[$dienstMannschaftID];
    $dienstID = "NULL";
    if($dienstMannschaft->hasSpieler()){
        $dienstID = $dienstMannschaft->getRandomSpielerID();
    }
    return "(".$spiel->getID().", '".$dienstArt."', ".$dienstMannschaftID.", ".$dienstID.")";
}

$mysqli->close();
?>