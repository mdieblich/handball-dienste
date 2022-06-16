<?php

function addDiensteSpieleImportKonfiguration(){
    $hook_import = add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displaySpieleImport');
    
    add_action( 'load-' . $hook_import, 'diensteImportSubmit' );
}

function displaySpieleImport(){
    require_once __DIR__."/../dao/mannschaft.php";
    require_once __DIR__."/../dao/spiel.php";
    $mannschaften = loadMannschaften();
 ?>
<div class="wrap">

    <h1>Spiele von nuLiga importieren</h1>
    Einfach auf <i>"Importieren"</i> klicken:
    <ol>
        <li>Vorhandene Spiele werden aktualisiert</li>
        <li>bestehende Dienste bleiben erhalten</li>
        <li>Sollte sich ein Spiel ändern (Anwurf oder Halle), bei dem eine Mannschaft schon Dienste zugewiesen bekommen hat, dann bekommt diese eine Email.</li>
        <li>Auch bei mehreren sich ändernden Spielen bekommt eine Mannschaft pro Import immer nur genau <u>eine</u> Email. <i>(Ich hasse zu viele Emails!)</i>
    </ol>
    <table>
        <tr>
            <th> Mannschaft </th>
            <th> Importierte Spiele </th>
        </tr>
        <?php foreach($mannschaften as $mannschaft){  ?>
        <tr>
            <td> <?php echo $mannschaft->getName(); ?> </td>
            <td style="text-align:center"> <?php echo countSpiele($mannschaft->getID()); ?> </td>
        </tr>
        <?php } ?>
    </table>
    <form action="<?php menu_page_url( 'dienste-import' ) ?>" method="post">
        <?php 
            wp_nonce_field('dienste-spiele-importieren');
            submit_button("Importieren"); 
        ?>
    </form>
</div>
 <?php
}

function diensteImportSubmit(){
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
    require_once __DIR__."/../import/importer.php";
    $resultMessage = importSpieleFromNuliga();
    echo "<div style='margin-left:200px;'>$resultMessage</div>";
}
?>