<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/weiterleitung.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM weiterleitung WHERE sendezeit IS NULL";
$result = $mysqli->query($sql);

$weiterleitungen = array();
if ($result->num_rows > 0) {
  while($weiterleitung = $result->fetch_assoc()) {
    $weiterleitungObj = new Weiterleitung($weiterleitung);
    $weiterleitungen[$weiterleitungObj->getID()] = $weiterleitungObj;
  }
}

return $weiterleitungen;
?>