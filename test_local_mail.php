<?php

class Egal{
function connect(){
        

        $connection = "{127.0.0.1:143}INBOX";
        error_reporting(E_ALL);
        $inbox = imap_open($connection, "dienstebot@dieblich.test", "password");

        echo "Inbox:<br>";
        var_dump($inbox);

        echo "<br>IMAP_CHECK:<br>";
        $check = imap_check($inbox);
        var_dump($check);
        
}
}
$e = new Egal();
$e->connect();

        //  imap_createmailbox($inbox, $connection."VERARBEITET");

?>