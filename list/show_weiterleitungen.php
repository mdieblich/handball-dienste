<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/dienst.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$emails = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";
$mannschaften = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/mannschaften.php";

foreach($emails as $email){
    echo "<b>".$email->getBisherZeile()."</b><br>";
    $nuligaID = $email->getSpielNummer();
    $sql = "SELECT * FROM dienst LEFT JOIN spiel ON dienst.spiel=spiel.id WHERE spiel.nuliga_id=".$nuligaID;
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        echo "<ul>";
        while($dienst = $result->fetch_assoc()) {
            $dienstObj = new Dienst($dienst);
            echo "<li>".$dienstObj->getDebugOutput($mannschaften)."</li>";
        }
        echo "</ul>";
    }
}

$mysqli->close();
?>