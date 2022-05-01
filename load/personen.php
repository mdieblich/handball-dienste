<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/person.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

function loadPersonen(string $whereClause="1=1", string $orderBy="id") {
  global $mysqli;
  
  $sql = "SELECT * FROM person WHERE $whereClause ORDER BY $orderBy";
  $result = $mysqli->query($sql);

  $personen = array();
  if ($result->num_rows > 0) {
    while($person = $result->fetch_assoc()) {
      $personObj = new Person($person);
      $personen[$personObj->getID()] = $personObj;
    }
  }
  return $spiele;
}
?>