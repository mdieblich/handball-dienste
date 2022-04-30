<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

// Konfigurationen des Skriptes
$minimalePersonenProTeam = 10;
$maximalePersonenProTeam = 20;

// IDs der Mannschaften bestimmen
$result = $mysqli->query("SELECT id, name FROM mannschaft");
$anzahlMannschaften = $result->num_rows;
echo "Erstelle Spieler für ".$anzahlMannschaften." Mannschaften<br>";

$insert_person = $mysqli->prepare(
    "INSERT INTO person (name, email, hauptmannschaft) ".
    "VALUES (?, ?, ?)");

$name = "";
$email = "";
$hauptmannschaft = 0;
$insert_person->bind_param("ssi", $name, $email, $hauptmannschaft);

echo "<ol>";
while($mannschaft = $result->fetch_assoc()) {
    $hauptmannschaft = $mannschaft['id'];
    $spielerzahl = rand($minimalePersonenProTeam, $maximalePersonenProTeam);
    echo "<li>".$mannschaft['name'].": ".$spielerzahl." Personen";
    echo "<ul>";
    for($i=0; $i<$spielerzahl; $i++){
        $name = "Testperson ".($i+1)." von ".$mannschaft['name'];
        $email = "testperson_".($i+1).".".str_replace(array(" "), "_", strtolower($mannschaft['name']))."@tknippes.de";
        echo "<li>".$name." - ".$email."</li>";
        $insert_person->execute();
    }
    echo "</ul>";
    echo "</li>";
}

$mysqli->close();

echo "</ol>";
echo "Fertig.";

?>