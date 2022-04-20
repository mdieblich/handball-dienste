<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";

$emails = loadAllEmails();

$insert_stmt = $mysqli->prepare(
    "INSERT INTO weiterleitung (email, person) ".
    "SELECT DISTINCT ?, person ".
    "FROM dienst LEFT JOIN spiel ON dienst.spiel=spiel.id ".
    "WHERE spiel.nuliga_id=? ".
    "AND NOT EXISTS (SELECT * FROM weiterleitung WHERE email=?)");

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