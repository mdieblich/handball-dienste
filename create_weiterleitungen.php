<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$emails = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";

echo "<pre>";
foreach($emails as $email){
    $nuligaID = $email->getSpielNummer();
    $sql = 
        "INSERT INTO weiterleitungen (email, person) ".
        "SELECT ".$email->getID().", person ".
        "FROM dienst LEFT JOIN spiel ON dienst.spiel=spiel.id ".
        "WHERE spiel.nuliga_id=".$nuligaID." ".
        "AND NOT EXISTS (SELECT * FROM weiterleitungen WHERE email=".$email->getID().")";
        echo $sql."\n";
    $result = $mysqli->query($sql);
}
echo "</pre>";

$mysqli->close();
?>