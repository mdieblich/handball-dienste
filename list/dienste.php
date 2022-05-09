<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/dienst.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/dienste.php";

$dienste = loadAllDienste(); 
foreach($dienste as $dienst){
  echo $dienst->getDebugOutput();
}

$mysqli->close();
?>