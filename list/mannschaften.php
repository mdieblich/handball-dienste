<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/mannschaft.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";

$mannschaften = loadMannschaftenDeep();

foreach($mannschaften as $mannschaft){
  echo $mannschaft->getDebugOutput();
}

$mysqli->close();
?>