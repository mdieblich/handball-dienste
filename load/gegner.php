<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/gegner.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

function loadGegner(string $whereClause="1=1", string $orderBy="id") {
  global $mysqli;
  
  $sql = "SELECT * FROM gegner WHERE $whereClause ORDER BY $orderBy";
  $result = $mysqli->query($sql);

  $alleGegner = array();
  if ($result->num_rows > 0) {
    while($gegner = $result->fetch_assoc()) {
      $gegnerObj = new Gegner($gegner);
      $alleGegner[$gegnerObj->getID()] = $gegnerObj;
    }
  }
  return $alleGegner;
}
?>