<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/dienst.php";

$dienste = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/dienste.php";
foreach($dienste as $dienst){
  echo $dienst->getDebugOutput();
}

$mysqli->close();
?>