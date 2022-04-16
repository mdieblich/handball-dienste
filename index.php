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
$mysqli->set_charset("utf8");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$sql = "SELECT * FROM email_inbox";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($email = $result->fetch_assoc()) {
    $emailObj = new Email($email["inhalt"]);
    echo $emailObj->getDebugOutput();
  }
} else {
  echo "0 results";
}

$mysqli->close();
?>