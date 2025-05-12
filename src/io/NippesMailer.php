<?php

function init_nippes_mailer(): PHPMailer\PHPMailer\PHPMailer {
    
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    //Server settings
    $mail->isSMTP();
    $mail->Host       = get_option('bot-smtp');
    $mail->SMTPAuth   = true;
    $mail->Username   = get_option('bot-email');
    $mail->Password   = get_option('bot-passwort');
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom(get_option('bot-email'), 'Nippesbot');
    $mail->addReplyTo('no-reply@turnerkreisnippes.de');

    $mail->CharSet = PHPMailer\PHPMailer\PHPMAILER::CHARSET_UTF8;

    return $mail;
}
?>