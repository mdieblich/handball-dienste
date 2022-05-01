<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/person.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/personen.php";

$personen = loadPersonen();
foreach($personen as $person){
  echo $person->getDebugOutput();
}

$mysqli->close();
?>