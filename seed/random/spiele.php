<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

// Konfigurationen des Skriptes
$spieleProTeam = 10;
// $spieleProTeam = 26;

$heimspielZeitSlots = array(
    "10:00",
    "12:00",
    "14:00",
    "16:00",
    "18:00",
    "20:00"
);

$auswaertsspielZeitSlots = array(
    "10:00",    "10:30",
    "11:00",    "11:30",
    "12:00",    "12:30",
    "13:00",    "13:30",
    "14:00",    "14:30",
    "15:00",    "15:30",
    "16:00",    "16:30",
    "17:00",    "17:30",
    "18:00",    "18:30",
    "19:00",    "19:30",
    "20:00",    "20:30"
);

// IDs der Mannschaften bestimmen
$result = $mysqli->query("SELECT id, name FROM mannschaft");
$anzahlMannschaften = $result->num_rows;
echo "Erstelle Spiele für ".$anzahlMannschaften." Mannschaften<br>";
$mannschaften = array();
while($mannschaftRow = $result->fetch_assoc()) {
    $mannschaften[$mannschaftRow['id']] = $mannschaftRow['name'];
}

// INSERT vorbereiten
$insert_spiel = $mysqli->prepare(
    "INSERT INTO spiel (nuliga_id, mannschaft, gegner, heimspiel, anwurf) ".
    "VALUES (?, ?, ?, ?, ?)");

$nuliga_id = 0;
$mannschaft_id = 0;
$gegner = "";
$isHeimspiel = 1;
$anwurf = "";
$insert_spiel->bind_param("iisis", $nuliga_id, $mannschaft, $gegner, $isHeimspiel, $anwurf);

// Spieltage generieren

function createSpielZeitSlots(array $uhrzeiten, DateTime $samstag): array{
    $sonntag = clone $samstag;
    $sonntag->modify('next sunday');
    
    $spielzeitSlots = array();
    foreach($uhrzeiten as $uhrzeit){
        $stunden = substr($uhrzeit, 0, 2);
        $minuten = substr($uhrzeit, 3, 2);
        array_push(
            $spielzeitSlots,
            clone $samstag->setTime($stunden, $minuten),
            clone $sonntag->setTime($stunden, $minuten)
        );
    }
    return $spielzeitSlots;
}

?>
<table border="1">
    <tr>
        <th>Spieltag</th>
        <th>Mannschaft</th>
        <th>nuLiga-ID</th>
        <th>Anwurf</th>
        <th>Gegner</th>
        <th>Heimspiel?</th>
        <th>Datenbank</th>
    </tr>
    <tr>
<?php
$spieltag = new DateTime();
$spieltag->setTime(0,0);
for($spieltagCount = 0; $spieltagCount<$spieleProTeam; $spieltagCount++){
    echo "<td rowspan=\"$anzahlMannschaften\">".($spieltagCount+1)."</td>";
    $spieltag->modify('next saturday');
    
    $freieHeimspielSlots = createSpielZeitSlots($heimspielZeitSlots, $spieltag);
    $verfuegbareAuswaertsspielZeitSlots = createSpielZeitSlots($auswaertsspielZeitSlots, $spieltag);

    foreach($mannschaften as $mannschaft => $name){
        $nuliga_id = 10000 * $mannschaft + $spieltagCount;
        $gegner = "Gegner von ".$name;
        $isHeimspiel = rand(0,1);
        if($isHeimspiel){
            if(count($freieHeimspielSlots) == 0){
                die("Nicht genügend Zeitslots für Heimspiele");
            }
            // TODO: Warnen, wenn keine Heimspiel-Zeitslots mehr übrig sind
            $zeitSlotID = array_rand($freieHeimspielSlots);
            $anwurf = $freieHeimspielSlots[$zeitSlotID]->format('Y-m-d H:i:s');
            unset($freieHeimspielSlots[$zeitSlotID]);   // Löschen, da keine Spiele gleichzeitig stattfinden können
        } else {
            $zeitSlotID = array_rand($auswaertsspielZeitSlots);
            $anwurf = $verfuegbareAuswaertsspielZeitSlots[$zeitSlotID]->format('Y-m-d H:i:s');
        }
        echo "<td>$name</td>";
        echo "<td>$nuliga_id</td>";
        echo "<td>$anwurf</td>";
        echo "<td>$gegner</td>";
        echo "<td>$isHeimspiel</td>";
        if($insert_spiel->execute()){
            echo "<td>Gespeichert</td>";
        }
        else{
            echo "<td>".$insert_spiel->error."</td>";
        }
        echo "</tr><tr>";
    }
}
echo "</table>";

$mysqli->close();

echo "Fertig.";

?>