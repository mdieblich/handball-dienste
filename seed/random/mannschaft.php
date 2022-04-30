<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$anzahlTeams = 10;

$insert_mannschaft = $mysqli->prepare(
    "INSERT INTO mannschaft (name, liga) ".
    "VALUES (?, ?)");

$name = "";
$liga = "";
$insert_mannschaft->bind_param("ss", $name, $liga);

$isDamen = false;
$damenCount = 0;
$herrenCount = 0;
for($i=0; $i<$anzahlTeams; $i++){
    if($isDamen){
        $name = "Damen ".($damenCount+1);
        $liga = "Frauenliga ".($damenCount+1);
        $damenCount++;
    }
    else{
        $name = "Herren ".($herrenCount+1);
        $liga = "Männerliga ".($herrenCount+1);
        $herrenCount++;
    }
    echo $name." - ".$liga."<br>";
    $insert_mannschaft->execute();
    $isDamen = !$isDamen;
}

$mysqli->close();

echo "Fertig.";

?>