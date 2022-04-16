<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/person.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

foreach(require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/personen.php" as $person){
  echo $person->getDebugOutput();
}

$mysqli->close();
?>