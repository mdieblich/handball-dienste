<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/dienst.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM dienst ORDER BY spiel, dienstart";
$result = $mysqli->query($sql);

$dienste = array();
if ($result->num_rows > 0) {
  while($dienst = $result->fetch_assoc()) {
    $dienstObj = new Dienst($dienst);
    $dienste[$dienstObj->getID()] = $dienstObj;
  }
}

return $dienste;
?>