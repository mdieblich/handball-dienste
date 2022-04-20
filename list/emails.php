<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/load/emails.php";

$emails = loadAllEmails(); 
foreach($emails as $email){
  echo $email->getDebugOutput();
}

$mysqli->close();
?>