<?php

function addDiensteZuweisenKonfiguration(){
    $hook_zuweisen = add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
    
    add_action( 'load-' . $hook_zuweisen, 'diensteZuweisenSubmit' );
}

function displayDiensteZuweisen(){
    require_once __DIR__."/../dao/mannschaft.php";
    require_once __DIR__."/../dao/spiel.php";
    $mannschaften = loadMannschaften();
 ?>
<div class="wrap">

    <h1>Dienste zuweisen</h1>
</div>
 <?php
}

function diensteZuweisenSubmit(){
    if('POST' !== $_SERVER['REQUEST_METHOD']){
        return;
    }
    if(empty($_POST['submit'])){
        return;
    }
    if("Importieren" !== $_POST['submit']){
        return;
    }
    if(!check_admin_referer('dienste-spiele-importieren')){
        return;
    }
    require_once __DIR__."/../importer.php";
    $resultMessage = importSpieleFromNuliga();
    echo "<div style='margin-left:200px;'>$resultMessage</div>";
}
?>