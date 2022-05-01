<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/mannschaft.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/personen.php";

function loadMannschaften(string $whereClause="1=1", string $orderBy="id") {
  global $mysqli;
  
  $sql = "SELECT * FROM mannschaft WHERE $whereClause ORDER BY $orderBy";
  $result = $mysqli->query($sql);

  $mannschaften = array();
  if ($result->num_rows > 0) {
    while($mannschaft = $result->fetch_assoc()) {
      $mannschaftObj = new Mannschaft($mannschaft);
      $mannschaften[$mannschaftObj->getID()] = $mannschaftObj;
    }
  }
  return $mannschaften;
}

function loadMannschaftenDeep(string $whereClause="1=1", string $orderBy="id"){
  $mannschaften = loadMannschaften($whereClause, $orderBy);

  $personen = loadPersonen();
  foreach($personen as $person){
    $mannschaften[$person->getHauptmannschaft()]->addSpieler($person);
  }
  
  return $mannschaften;
}
?>