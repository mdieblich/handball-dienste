<?php

require_once "email.php";

/*
* Change the value of $password if you have set a password on the root userid
* Change NULL to port number to use DBMS other than the default using port 3306
*
*/
$user = 'root';
$password = ''; //To be completed if you have set a password to root
$database = 'dienstedienst'; //To be completed to connect to a database. The database must exist.
$port = NULL; //Default must be NULL to use default port
$mysqli = new mysqli('127.0.0.1', $user, $password, $database, $port);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$sql = "SELECT * FROM email_inbox";
$result = $mysqli->query($sql);

function getLineWithString($inhalt, $str): ?string {
  $lines = preg_split("/\r\n|\n|\r/", $inhalt);
    foreach ($lines as $lineNumber => $line) {
        if (strpos($line, $str) !== false) {
            return $line;
        }
    }
    return null;
} 

if ($result->num_rows > 0) {
  // output data of each row
  while($email = $result->fetch_assoc()) {
    $inhalt = $email["inhalt"];

    $emailObj = new Email($email["inhalt"]);

    $bisher_zeile = getLineWithString($inhalt, "BISHER");
    
    echo "<div>";
    echo "<b>Spielnummer:</b> ".$emailObj->getSpielNummer();
    echo "<pre style='padding-left:1em; font-style:italic'>".$bisher_zeile."</pre>\n";
    echo "</div>";
  }
} else {
  echo "0 results";
}

$mysqli->close();
?>