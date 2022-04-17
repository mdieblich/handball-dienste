<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$emails = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";


$insert_stmt = $mysqli->prepare(
    "INSERT INTO weiterleitungen (email, person) ".
    "SELECT ?, person ".
    "FROM dienst LEFT JOIN spiel ON dienst.spiel=spiel.id ".
    "WHERE spiel.nuliga_id=? ".
    "AND NOT EXISTS (SELECT * FROM weiterleitungen WHERE email=?)");

$email_id = 0;
$nuliga_id = 0;
$insert_stmt->bind_param("iii", $email_id, $nuliga_id, $email_id);

foreach($emails as $email){
    $email_id = $email->getID();
    $nuliga_id = $email->getSpielNummer();
    $insert_stmt->execute();
}
$insert_stmt->close();
$mysqli->close();
?>