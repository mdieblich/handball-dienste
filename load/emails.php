<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

$sql = "SELECT * FROM email_inbox";
$result = $mysqli->query($sql);

$emails = array();
if ($result->num_rows > 0) {
  // output data of each row
  while($email = $result->fetch_assoc()) {
    array_push($emails, new Email($email));
  }
}

return $emails;
?>