<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$anzahlTeams = 10;

$insert_mannschaft = $mysqli->prepare(
    "INSERT INTO mannschaft (name, liga) ".
    "VALUES (?, ?)");

$mannschaft_name = "";
$mannschaft_liga = "";
$insert_mannschaft->bind_param("ss", $mannschaft_name, $mannschaft_liga);

$damen = false;
$damenCount = 0;
$herrenCount = 0;
for($i=0; $i<$anzahlTeams; $i++){
    if($damen){
        $mannschaft_name = "Damen ".($damenCount+1);
        $mannschaft_liga = "Frauenliga ".($damenCount+1);
        $damenCount++;
    }
    else{
        $mannschaft_name = "Herren ".($herrenCount+1);
        $mannschaft_liga = "Männerliga ".($herrenCount+1);
        $herrenCount++;
    }
    echo $mannschaft_name." - ".$mannschaft_liga."<br>";
    $insert_mannschaft->execute();
    $damen = !$damen;
}

$mysqli->close();

echo "Fertig.";

?>