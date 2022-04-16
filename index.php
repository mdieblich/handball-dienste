<?php
require_once "email.php";

$mysqli = require_once "db_connect.php";

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