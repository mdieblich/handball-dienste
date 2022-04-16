<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/mannschaft.php";

$mannschaften = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";

foreach($mannschaften as $mannschaft){
  echo $mannschaft->getDebugOutput();
}

$mysqli->close();
?>