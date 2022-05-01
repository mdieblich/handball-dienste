<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/spiel.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/spiele.php";

$spiele = loadSpiele(); 
foreach($spiele as $spiel){
  echo $spiel->getDebugOutput();
}

$mysqli->close();
?>