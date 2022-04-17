<?php
// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/src/Exception.php";
require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/src/PHPMailer.php";
require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/src/SMTP.php";

// Datenbank-Zugriff
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.ionos.de';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'dienstebot@turnerkreisnippes.de';
    $mail->Password   = '9nUgcLpcRMz3fLF';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('dienstebot@turnerkreisnippes.de', 'Nippes Bot');
    $mail->addAddress('martin.dieblich@gmx.de', 'Martin Dieblich');
    // $mail->addBCC('bcc@example.com');
    $mail->addReplyTo('mdieblich@gmail.com', 'Martin GOOGLE Dieblich');

    //Content
    $mail->isHTML(true);
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