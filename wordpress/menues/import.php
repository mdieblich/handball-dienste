<?php

function addDiensteSpieleImportKonfiguration(){
    $hook_import = add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displaySpieleImport');
}
add_action( 'wp_ajax_meisterschaften_importieren', 'meisterschaften_importieren' );
add_action( 'wp_ajax_spiele_importieren', 'spiele_importieren' );

function displaySpieleImport(){
    require_once __DIR__."/../dao/mannschaft.php";
    require_once __DIR__."/../dao/spiel.php";
    $mannschaften = loadMannschaftenMitMeisterschaften();
?>
<script>
function startImportMeisterschaften(){
    jQuery(function($){
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'meisterschaften_importieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                $("#loading-spinner").hide(500, function(){
                    $("#import-result").show(500);
                });
                $("#import-result").html("<pre>" + response + "</pre>");
            });
        });
}
function startImportSpiele(){
    jQuery(function($){
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'spiele_importieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                $("#loading-spinner").hide(500, function(){
                    $("#import-result").show(500);
                });
                $("#import-result").html("<pre>" + response + "</pre>");
            });
        });
}
</script>
<div class="wrap">

    <div class="spinner-border" role="status" id="loading-spinner" style="display:none">
        <span class="visually-hidden">Loading...</span>
    </div>
    
    <div id="import-result" style="display:none">Ergebnisse!</div>


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
    <button class="btn btn-primary" onclick="startImportMeisterschaften()">Meisterschaften importieren</button>
    <button class="btn btn-primary" onclick="startImportSpiele()">Spiele importieren</button>
</div>
 <?php
}

function spiele_importieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = importSpieleFromNuliga();
    echo "Ergebnis des Imports der Spiele:\n";
    foreach($importErgebnis as $mannschaftsName => $ergebnis){
        echo $mannschaftsName.": ".$ergebnis->toReadableString()."\n";
    }
    exit;
}

function meisterschaften_importieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = importMeisterschaftenFromNuliga();
    echo "Ergebnis des Imports der Meisterschaften:\n";
    foreach($importErgebnis as $mannschaftsName => $ergebnis){
        echo $mannschaftsName.": ".$ergebnis->toReadableString()."\n";
    }
    exit;
}
?>