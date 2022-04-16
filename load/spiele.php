<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/spiel.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM spiel";
$result = $mysqli->query($sql);

$spiele = array();
if ($result->num_rows > 0) {
  while($spiel = $result->fetch_assoc()) {
    $spielObj = new Spiel($spiel);
    $spiele[$spielObj->getNuligaID()] = $spielObj;
  }
}

return $spiele;
?>