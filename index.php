<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";

// $sql = "SELECT * FROM mannschaft";
// $result = $mysqli->query($sql);

// if ($result->num_rows > 0) {
//   // output data of each row
//   while($mannschaft = $result->fetch_assoc()) {
//     $mannschaftObj = new Mannschaft($mannschaft);
//     echo $mannschaftObj->getDebugOutput();
//   }
// } else {
//   echo "0 results";
// }
$mysqli->close();
?>