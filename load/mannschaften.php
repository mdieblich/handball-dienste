<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/mannschaft.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM mannschaft";
$result = $mysqli->query($sql);

$mannschaften = array();
if ($result->num_rows > 0) {
  while($mannschaft = $result->fetch_assoc()) {
    $mannschaftObj = new Mannschaft($mannschaft);
    $mannschaften[$mannschaftObj->getID()] = $mannschaftObj;
  }
}

$personen = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/personen.php";
foreach($personen as $person){
  $mannschaften[$person->getHauptmannschaft()]->addSpieler($person);
}

return $mannschaften;
?>