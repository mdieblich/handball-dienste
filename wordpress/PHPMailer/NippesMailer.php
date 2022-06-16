<?php
// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __dir__."/src/Exception.php";
require __dir__."/src/PHPMailer.php";
require __dir__."/src/SMTP.php";

function init_nippes_mailer(): PHPMailer {

    $mail = new PHPMailer(true);

    //Server settings
    $mail->isSMTP();
    $mail->Host       = get_option('bot-smtp');
    $mail->SMTPAuth   = true;
    $mail->Username   = get_option('bot-email');
    $mail->Password   = get_option('bot-passwort');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom(get_option('bot-email'), 'Nippesbot');
    $mail->addReplyTo('no-reply@turnerkreisnippes.de');

    $mail->CharSet = PHPMAILER::CHARSET_UTF8;

    return $mail;
}
?>