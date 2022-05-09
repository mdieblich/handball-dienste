<?php
require_once $_SERVER['DOCUMENT_ROOT']."/dienstedienst/db_connect.php";
header('Content-Type: application/json');
$dienst = json_decode(file_get_contents('php://input'));

switch($_SERVER['REQUEST_METHOD']){
    case "PUT":{
        $insert_dienst = $mysqli->prepare(
            "INSERT INTO dienst (spiel, dienstart, mannschaft) VALUES (?,?,?)");
        
        $insert_dienst->bind_param("isi", $dienst->spiel, $dienst->dienstart, $dienst->mannschaft);
        if($insert_dienst->execute()){
            http_response_code(200);
        } else {
            http_response_code(500);
        };
        break;
    }
    case "DELETE":{
        $delete_dienst = $mysqli->prepare(
            "DELETE FROM dienst WHERE spiel=? AND dienstart=? AND mannschaft=?");
        echo $mysqli->error;
        $delete_dienst->bind_param("isi", $dienst->spiel, $dienst->dienstart, $dienst->mannschaft);
        if($delete_dienst->execute()){
            http_response_code(200);
        } else {
            http_response_code(500);
        };
        break;
    }
}

?>