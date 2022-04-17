<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/spiel.php";

$spiele = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/spiele.php";
foreach($spiele as $spiel){
  echo $spiel->getDebugOutput();
}

$mysqli->close();
?>