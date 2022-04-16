<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/mannschaft.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM mannschaft";
$result = $mysqli->query($sql);

$mannschaften = array();
if ($result->num_rows > 0) {
  // output data of each row
  while($mannschaft = $result->fetch_assoc()) {
    array_push($mannschaften, new Mannschaft($mannschaft));
  }
}


return $mannschaften;
?>