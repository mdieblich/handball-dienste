<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/entity/email.php";
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

function loadAllEmails(){
  return loadEmails();
}

function loadAllNewEmails(){
  return loadEmails("verarbeitungsdatum IS NULL");
}

function loadEmails(string $whereClause="1=1") {
  global $mysqli;
  
  $sql = "SELECT * FROM email_inbox WHERE ".$whereClause;
  $result = $mysqli->query($sql);
  
  $emails = array();
  if ($result->num_rows > 0) {
    // output data of each row
    while($email = $result->fetch_assoc()) {
      array_push($emails, new Email($email));
    }
  }
  
  return $emails;
}
?>