<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/person.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM person";
$result = $mysqli->query($sql);

$personen = array();
if ($result->num_rows > 0) {
  // output data of each row
  while($person = $result->fetch_assoc()) {
    array_push($personen, new Person($person));
  }
}


return $personen;
?>