<?php
// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/NippesMailer.php";

// Datenbank-Zugriff
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = 
    "SELECT weiterleitung.id, person.name, person.email, email_inbox.absender, email_inbox.empfang, email_inbox.betreff, email_inbox.inhalt ".
    "FROM weiterleitung ".
    "LEFT JOIN email_inbox ON weiterleitung.email=email_inbox.id ".
    "LEFT JOIN person ON weiterleitung.person=person.id ".
    "WHERE sendezeit IS NULL";
$result = $mysqli->query($sql);

$weiterleitungen = array();
if ($result->num_rows > 0) {
    while($weiterleitung = $result->fetch_assoc()) {
        try {
            $mail = init_nippes_mailer();

            //Recipients
            $mail->addAddress($weiterleitung['email'], $weiterleitung['name']);

            //Inhalt
            $mail->Subject = $weiterleitung['betreff'];
            $mail->Body = 
                "Empfangen: ".$weiterleitung['empfang']."\n".
                "Von: ".$weiterleitung['absender']."\n".
                "-- Weitergeleitet durch den Nippes-Bot --\n".
                "\n".
                $weiterleitung['inhalt'];

            $mail->send();

            // Senden in Datenbank vermerken
            $mysqli->query("UPDATE weiterleitung SET sendezeit = CURRENT_TIMESTAMP WHERE id=".$weiterleitung['id']);

        } catch (Exception $e) {
            echo "Fehler beim Senden: ".$e;
        }
    }
}

$mysqli->close();
?>