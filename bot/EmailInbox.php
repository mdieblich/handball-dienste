<?php
class EmailInbox {

    const SERVER = "imap.ionos.de";
    const PORT = 993;

    const EMAIL_USER = "dienstebot@turnerkreisnippes.de";
    const EMAIL_PASSWORD = "9nUgcLpcRMz3fLF";

    private IMAP\Connection $inbox;

// OUT      smtp.ionos.de   587
        
    public function __construct($server, $port, $user, $password){
        $connection = "{".self::SERVER.":".self::PORT."/imap/ssl/novalidate-cert}";
        $this->inbox = imap_open($connection, self::EMAIL_USER, self::EMAIL_PASSWORD) or die('Cannot connect to email: ' . imap_last_error());
    }

    public function __destruct(){
        imap_close($this->inbox);
    }

    public static function DiensteBot(){
        $server = "imap.ionos.de";
        $port = 993;

        $user = "dienstebot@turnerkreisnippes.de";
        $password = "9nUgcLpcRMz3fLF";

        return new EmailInbox($server, $port, $user, $password);
    }
}

?>