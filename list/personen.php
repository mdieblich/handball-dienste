<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/person.php";

$personen = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/personen.php";
foreach($personen as $person){
  echo $person->getDebugOutput();
}

$mysqli->close();
?>