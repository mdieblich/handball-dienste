<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/email.php";

foreach(require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php" as $email){
  echo $email->getDebugOutput();
}

$mysqli->close();
?>