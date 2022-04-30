<?php
use PHPUnit\Framework\TestCase;

// require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/bot/EmailInbox.php";

final class EmailInboxTest extends TestCase {

    public function testVerbindungKannHergestelltWerden(){

        $connection = "{127.0.0.1:143}";
        $inbox = imap_open($connection."INBOX", "dienstebot@dieblich.test", "password");
        $text = var_export($inbox, true);
        $this->assertEquals("Hehe", $text);
    }

    // public function testErstelltVERARBEITETOrdnerFallsNichtExistent(){
    //     $connection = "{127.0.0.1:143/imap}";
    //     $inbox = imap_open($connection, "dienstebot@dieblich.test", "password") or die('Cannot connect to email: ' . imap_last_error());
    //     //imap_createmailbox($inbox, $connection."VERARBEITET");
    //     $this->assertIsObject($inbox);
    // }
}
?>