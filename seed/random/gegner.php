<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

// Konfigurationen des Skriptes
$gegnerProLiga = 10;

// Anzahl der Ligen bestimmen
$result = $mysqli->query("SELECT distinct liga FROM mannschaft");
$anzahlLigen = $result->num_rows;
echo "Erstelle Spiele für ".$anzahlLigen." Ligen<br>";
$ligen = array();
while($ligaRow = $result->fetch_assoc()) {
    array_push($ligen, $ligaRow['liga']);
}

// INSERT vorbereiten
$insert_gegner = $mysqli->prepare(
    "INSERT INTO gegner (name, liga, stelltSekretaerBeiHeimspiel) ".
    "VALUES (?, ?, ?)");

$name = "";
$liga = "";
$stelltSekretaerBeiHeimspiel = 0;
$insert_gegner->bind_param("ssi", $name, $liga, $stelltSekretaerBeiHeimspiel);

// Ligen generieren

?>
<ul>

<?php
foreach($ligen as $liga){
    echo "<li>$liga<ol>";
    for($i=0; $i<$gegnerProLiga; $i++){
        $name = "$liga - Mannschaft $i";
        $stelltSekretaerBeiHeimspiel = rand(0,1);
        echo "<li>$name";
        if($stelltSekretaerBeiHeimspiel){
            echo " - stellt Sekretär";
        }
        echo ": ";
        if($insert_gegner->execute()){
            echo "<td>Gespeichert</td>";
        }
        else{
            echo "<td>".$insert_gegner->error."</td>";
        }
        echo "</li>";
    }
    echo "</ol></li>";
}
echo "</ul>";

$mysqli->close();

echo "Fertig.";

?>