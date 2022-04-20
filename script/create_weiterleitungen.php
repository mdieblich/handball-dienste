<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";

$emails = loadAllEmails();

$insert_weiterleitung = $mysqli->prepare(
    "INSERT INTO weiterleitung (email, person) ".
    "SELECT DISTINCT ?, person ".
    "FROM dienst LEFT JOIN spiel ON dienst.spiel=spiel.id ".
    "WHERE spiel.nuliga_id=? ".
    "AND NOT EXISTS (SELECT * FROM weiterleitung WHERE email=?)");

$email_id = 0;
$nuliga_id = 0;
$insert_weiterleitung->bind_param("iii", $email_id, $nuliga_id, $email_id);

$update_email = $mysqli->prepare(
    "UPDATE email_inbox ".
    "SET verarbeitungsdatum = CURRENT_TIMESTAMP, ".
        "verarbeitungsergebnis = ? ".
    "WHERE id=?");

$verarbeitungsergebnis = "ignoriert";
$update_email->bind_param("si", $verarbeitungsergebnis, $email_id);

foreach($emails as $email){
    $email_id = $email->getID();
    echo $email->getBetreff().": ";
    if($email->isSpielaenderung()){
        $verarbeitungsergebnis = "weiterleiten";
        $nuliga_id = $email->getSpielNummer();
        $mysqli->begin_transaction();
        $insertSuccessful = $insert_weiterleitung->execute();
        $updateSuccessful = $update_email->execute();
        if($insert_weiterleitung && $updateSuccessful) {
            $mysqli->commit();
        } else {
            $mysqli->rollback();
        }
    } else {
        $verarbeitungsergebnis = "ignoriert";
        $update_email->execute();
    }
    echo $verarbeitungsergebnis."<br>\n";
}

$insert_weiterleitung->close();
$update_email->close();
$mysqli->close();
?>