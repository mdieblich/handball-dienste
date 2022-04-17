<?php
// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/src/Exception.php";
require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/src/PHPMailer.php";
require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/lib/PHPMailer/src/SMTP.php";

function init_nippes_mailer(): PHPMailer {

    $mail = new PHPMailer(true);

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
    $mail->addReplyTo('no-reply@turnerkreisnippes.de');

    return $mail;
}
?>