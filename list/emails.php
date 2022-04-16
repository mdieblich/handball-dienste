<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/class/email.php";

$emails = require $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";
foreach($emails as $email){
  echo $email->getDebugOutput();
}

$mysqli->close();
?>