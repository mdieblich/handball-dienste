<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/spiel.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/dienste.php";

function loadSpiele(string $whereClause="1=1", string $orderBy="id") {
  global $mysqli;
  
  $sql = "SELECT * FROM spiel WHERE $whereClause ORDER BY $orderBy";
  $result = $mysqli->query($sql);

  $spiele = array();
  if ($result->num_rows > 0) {
    while($spiel = $result->fetch_assoc()) {
      $spielObj = new Spiel($spiel);
      $spiele[$spielObj->getID()] = $spielObj;
    }
  }
  return $spiele;
}

function loadSpieleDeep(string $whereClause="1=1", string $orderBy="id"){
  $spiele = loadSpiele($whereClause, $orderBy);
  foreach( loadAllDienste() as $dienst){
    $spiele[$dienst->getSpiel()]->addDienst($dienst);
  } 
  return $spiele;
}

?>