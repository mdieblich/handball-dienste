<?php
// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/NippesMailer.php";

// Datenbank-Zugriff
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";


try {
    $mail = init_nippes_mailer();

    //Recipients
    $mail->addAddress('martin.dieblich@gmx.de', 'Martin Dieblich');
    // $mail->addBCC('bcc@example.com');

    //Content
    $mail->Subject = 'Spielverlegung bei deinem Zeitnehmer-Dienst';
    $mail->Body    = 'Das Spiel wurde verlegt! <b>Denk dran!</b>';
    $mail->AltBody = 'Das Spiel wurde verlegt! Denk dran!';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

$mysqli->close();
?>