<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/weiterleitung.php";

$offene_weiterleitungen = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/offene_weiterleitungen.php";
foreach($offene_weiterleitungen as $weiterleitung){
  echo $weiterleitung->getDebugOutput();
}

$mysqli->close();
?>