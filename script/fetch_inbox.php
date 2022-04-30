<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$connection = "{imap.ionos.de:993/imap/ssl/novalidate-cert}";
// OUT      smtp.ionos.de   587
$username = "dienstebot@turnerkreisnippes.de";
$password = "9nUgcLpcRMz3fLF";

$inbox = imap_open($connection, $username, $password) or die('Cannot connect to email: ' . imap_last_error());
$ordnerVERARBEITETStatus = imap_status($inbox, $connection."VERARBEITET", SA_ALL);
if(!$ordnerVERARBEITETStatus){
    imap_createmailbox($inbox, $connection."VERARBEITET");
}


 hier weiter in Klasse EmailInbox verschieben

$emails = imap_search($inbox, 'ALL');
if(!$emails){
    imap_close($inbox);
    die("Keine Emails!");
}

$insert_stmt = $mysqli->prepare("INSERT INTO email_inbox (absender, empfang, betreff, inhalt) VALUES (?,?,?,?)");
$absender = "";
$empfang = "CURRENT_TIMESTAMP";
$betreff = "";
$inhalt = "";
$insert_stmt->bind_param("ssss", $absender, $empfang, $betreff, $inhalt);
foreach($emails as $msg_number) 
 {
    $header = imap_headerinfo($inbox, $msg_number);
    $absender = $header->senderaddress;
    $empfang = gmdate("Y-m-d H:i:s", $header->udate+2*3600);    // 2 h Offest vom Server
    $betreff = quoted_printable_decode($header->subject);

    $s = imap_fetchstructure($inbox, $msg_number);
    $inhalt = getpart($inbox, $msg_number, $s, 0);
    echo $betreff.":";
    if($insert_stmt->execute()){    
        echo "eingefügt!<br>\n";
        imap_mail_move($inbox, $msg_number, "VERARBEITET");
    } else {    
        echo $insert_stmt->error."<br>\n";
    }
 }
 imap_expunge($inbox);
 imap_close($inbox);
 $insert_stmt->close();

function getpart($mbox, $mid, $p, $part_n) {
    $data = imap_body($mbox, $mid);
    $plain_msg = "";

    // Decode
    if ($p->encoding == 4) {
        $data = quoted_printable_decode($data);
    } else if ($p->encoding == 3) {
        $data = base64_decode($data);
    }

    // Text Messaage
    if ($p->type == 0 && !empty($data)) {
        if (strtolower($p->subtype) == 'plain') {
            $plain_msg .= trim($data) ."\n\n";
        }
    } else if ($p->type == 2 && !empty($data)) {
        $plain_msg .= $data. "\n\n";
    }

    // Subparts Recursion
    if (!empty($p->parts)) {
        foreach ($p->parts as $part_n2 => $p2) {
            $plain_msg .= getpart($mbox, $mid, $p2, $part_n . '.' . ($part_n2 + 1));
        }
    }

    return $plain_msg;
}

$mysqli->close();
?>