<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/dienst.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

function loadAllDienste(){
  global $mysqli;
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
}
?>