<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/gegner.php";

// Konfigurationen des Skriptes
$spieleProTeam = 10;

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

$mannschaften = loadMannschaften();
$anzahlMannschaften = sizeof($mannschaften);
$alleGegner = loadGegner();
$gegnerProMannschaft = array();
foreach($mannschaften as $mannschaft){
    $gegnerProMannschaft[$mannschaft->getID()] = array();
    foreach($alleGegner as $gegner){
        if($gegner->getLiga() == $mannschaft->getLiga()){
            array_push($gegnerProMannschaft[$mannschaft->getID()], $gegner);
        }
    }
}

// INSERT vorbereiten
$insert_spiel = $mysqli->prepare(
    "INSERT INTO spiel (spielnr, mannschaft, gegner, heimspiel, halle, anwurf) ".
    "VALUES (?, ?, ?, ?, ?, ?)");
    
$spielnr = 0;
$mannschaft_id = 0;
$gegner_id = 0;
$isHeimspiel = 1;
$halle = 1000; // Heimspiel-Halle
$anwurf = "";
$insert_spiel->bind_param("iiiiis", $spielnr, $mannschaft_id, $gegner_id, $isHeimspiel, $halle, $anwurf);
    
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
        <th>Spiel-Nr.</th>
        <th>Anwurf</th>
        <th>Gegner</th>
        <th>Heimspiel?</th>
        <th>Halle</th>
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
    
    foreach($mannschaften as $mannschaft){
        $name = $mannschaft->getName();
        $mannschaft_id = $mannschaft->getID();
        $spielnr = 10000 * $mannschaft->getID() + $spieltagCount;
        $gegner = $gegnerProMannschaft[$mannschaft->getID()][$spieltagCount];
        $gegner_id = $gegner->getID();
        $isHeimspiel = rand(0,1);
        if($isHeimspiel){
            $halle = 1000;
            if(count($freieHeimspielSlots) == 0){
                die("Nicht genügend Zeitslots für Heimspiele");
            }
            // TODO: Warnen, wenn keine Heimspiel-Zeitslots mehr übrig sind
            $zeitSlotID = array_rand($freieHeimspielSlots);
            $anwurf = $freieHeimspielSlots[$zeitSlotID]->format('Y-m-d H:i:s');
            unset($freieHeimspielSlots[$zeitSlotID]);   // Löschen, da keine Spiele gleichzeitig stattfinden können
        } else {
            $halle = rand(2000,3000);
            $zeitSlotID = array_rand($auswaertsspielZeitSlots);
            $anwurf = $verfuegbareAuswaertsspielZeitSlots[$zeitSlotID]->format('Y-m-d H:i:s');
        }
        echo "<td>$name</td>";
        echo "<td>$spielnr</td>";
        echo "<td>$anwurf</td>";
        echo "<td>".$gegner->getName()." ($gegner_id)</td>";
        echo "<td>$isHeimspiel</td>";
        echo "<td>$halle</td>";
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