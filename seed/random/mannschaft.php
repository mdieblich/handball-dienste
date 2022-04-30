<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$anzahlTeams = 10;

$insert_mannschaft = $mysqli->prepare(
    "INSERT INTO mannschaft (name, liga) ".
    "VALUES (?, ?)");

$mannschaft_name = "";
$mannschaft_liga = "";
$insert_mannschaft->bind_param("ss", $mannschaft_name, $mannschaft_liga);


for($i=0; $i<$anzahlTeams; $i++){
    $mannschaft_name = "Testmannschaft ".$i;
    $mannschaft_liga = "Testliga ".$i;
    $insert_mannschaft->execute();
}

$mysqli->close();

echo "Fertig.";

?>