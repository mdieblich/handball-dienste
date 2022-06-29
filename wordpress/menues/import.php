<?php

function addDiensteSpieleImportKonfiguration(){
    $hook_import = add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displaySpieleImport');
}
add_action( 'wp_ajax_meisterschaften_importieren', 'meisterschaften_importieren' );
add_action( 'wp_ajax_meisterschaften_importieren_new', 'meisterschaften_importieren_new' );
add_action( 'wp_ajax_teamIDs_importieren', 'teamIDs_importieren' );
add_action( 'wp_ajax_meisterschaften_aktualisieren', 'meisterschaften_aktualisieren' );
add_action( 'wp_ajax_meldungen_aktualisieren', 'meldungen_aktualisieren' );
add_action( 'wp_ajax_spiele_importieren', 'spiele_importieren' );
add_action( 'wp_ajax_meldung_aktivieren', 'meldung_aktivieren' );

function meldung_aktivieren(){
    require_once __DIR__."/../dao/MannschaftsMeldung.php";

    $meldung_id = filter_var($_POST['meldung'], FILTER_VALIDATE_INT);
    $aktiv = filter_var($_POST['aktiv'], FILTER_VALIDATE_BOOLEAN);

    meldungAktivieren($meldung_id, $aktiv);
    http_response_code(200);
    wp_die();
}

function displaySpieleImport(){
    require_once __DIR__."/../dao/mannschaft.php";
    require_once __DIR__."/../dao/meisterschaft.php";
    require_once __DIR__."/../dao/spiel.php";
    $mannschaften = loadMannschaftenMitMeldungen();
    $meisterschaften = loadMeisterschaften();
?>
<script>
function startImportMeisterschaften(){
    jQuery(function($){
        $("#importModalLabel").html("Importiere Meisterschaften...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'meisterschaften_importieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                importErgebnisse = JSON.parse(response);
                importErgebnisse.forEach(function(importErgebnis){
                    neueErgebnisZeile = $("#import-result-zeile").clone();
                    neueErgebnisZeile.appendTo("#import-result");
                    neueErgebnisZeile.find(".antwort").html(importErgebnis.mannschaft);
                    neueErgebnisZeile.find(".text-bg-success").html(importErgebnis.gesamt);
                    neueErgebnisZeile.find(".text-bg-warning").html(importErgebnis.aktualisiert);
                    neueErgebnisZeile.find(".text-bg-info").html(importErgebnis.neu);
                    neueErgebnisZeile.show();
                });
                
                $("#loading-spinner").hide(500, function(){
                    $("#import-result").show(500);
                });
            });
        });
}
function startImportMeisterschaftenNew(){
    jQuery(function($){
        $("#importModalLabel").html("Importiere Meisterschaften...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'meisterschaften_importieren_new'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                $("#loading-spinner").hide(500, function(){
                    $("#import-result")
                        .html("<pre>"+response+"</pre>")
                        .show(500);
                });
            });
        });
}

function startImportTeamIDs(){
    jQuery(function($){
        $("#importModalLabel").html("Importiere TeamIDs...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'teamIDs_importieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                $("#loading-spinner").hide(500, function(){
                    $("#import-result")
                        .html("<pre>"+response+"</pre>")
                        .show(500);
                });
            });
        });
}

function startUpdateMeisterschaften(){
    jQuery(function($){
        $("#importModalLabel").html("Aktualisiere Meisterschaften...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'meisterschaften_aktualisieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                $("#loading-spinner").hide(500, function(){
                    $("#import-result")
                        .html("<pre>"+response+"</pre>")
                        .show(500);
                });
            });
        });
}

function startMeldungenAktualisieren(){
    jQuery(function($){
        $("#importModalLabel").html("Aktualisiere Meldungen...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'meldungen_aktualisieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){    
            jQuery(function($){
                $("#loading-spinner").hide(500, function(){
                    $("#import-result")
                        .html("<pre>"+response+"</pre>")
                        .show(500);
                });
            });
        });
}

function meldungAktivieren(meldung_id, aktiv){
    var data = {
        'action': 'meldung_aktivieren',
        'meldung': meldung_id,
        'aktiv': aktiv
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data);
}

function startImportSpiele(){
    jQuery(function($){
        $("#importModalLabel").html("Importiere Spiele...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'spiele_importieren'};

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data)
        .done(function(response){
            jQuery(function($){    
                importErgebnisse = JSON.parse(response);
                importErgebnisse.forEach(function(importErgebnis){
                    neueErgebnisZeile = $("#import-result-zeile").clone();
                    neueErgebnisZeile.appendTo("#import-result");
                    neueErgebnisZeile.find(".antwort").html(importErgebnis.mannschaft);
                    neueErgebnisZeile.find(".text-bg-success").html(importErgebnis.gesamt);
                    neueErgebnisZeile.find(".text-bg-warning").html(importErgebnis.aktualisiert);
                    neueErgebnisZeile.find(".text-bg-info").html(importErgebnis.neu);
                    neueErgebnisZeile.show();
                });
                $("#loading-spinner").hide(500, function(){
                    $("#import-result").show(500);
                });
                // $("#import-result").html("<pre>" + response + "</pre>");
            });
        });
}
</script>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Import läuft...</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div  id="loading-spinner" class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%"></div>
        </div>

        <div id="import-result" style="display:none" class="container">
            <div class="row" id="import-result-zeile">
                <div class="col"><span class="antwort"><i>Mannschaft</i></span></div>
                <div class="col text-center"><span class="badge text-bg-success">geprüft</span></div>
                <div class="col text-center"><span class="badge text-bg-warning">aktualisiert</span></div>
                <div class="col text-center"><span class="badge text-bg-info">importiert</span></div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
      </div>
    </div>
  </div>
</div>

<div class="wrap">

<h2>Meisterschaften importieren</h2>
<p>
Zuerst müssen Meisterschaften importiert werden, welche dann unten auftauchen. Für jede 
<a href="<?php echo get_permalink( get_page_by_path( 'dienste-mannschaften' ) );?>">Mannschaft, die zuvor konfiguriert wurde</a> werden dann Mannschaftsmeldungen geladen und hier dargestellt.<br>
Bestehende Meisterschaften und Meldungen bleiben bei jedem weiterem Import erhalten. Der Import dauert recht lange, da viele Seiten von nuLiga gescannt werden.
</p>
<p>
Importierte Mannschaftsmeldungen können einzeln aktiviert & deaktiviert werden. Dazugehörige Spiele werden zwar noch aktualisiert, aber die Spiele werden nicht mehr in der Liste der Dienste dargestellt.
</p>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startImportMeisterschaften()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Meisterschaften importieren (alt)
</button>

<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startImportMeisterschaftenNew()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Meisterschaften importieren (neu)
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startImportTeamIDs()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Team-IDs importieren
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startUpdateMeisterschaften()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Meisterschaften aktualisieren
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startMeldungenAktualisieren()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Meldungen aktualisieren
</button>

<hr>
<h2>Spiele importieren</h2>
<p>
Nachdem Meisterschaften importiert wurden, können dazugehörige Spiele importiert werden.
</p>
<ul style="font-size:13px">
    <li type="disc">Vorhandene Spiele werden aktualisiert</li>
    <li type="disc">bestehende Dienste bleiben erhalten</li>
    <li type="disc">Gegnerische Mannschaften werden automatisch mitimportiert</li>
    <li type="disc">Sollte sich ein Spiel ändern (Anwurf oder Halle), bei dem eine Mannschaft schon Dienste zugewiesen bekommen hat, dann bekommt diese eine Email.</li>
    <li type="disc">Auch bei mehreren sich ändernden Spielen bekommt eine Mannschaft pro Import immer nur genau <u>eine</u> Email. <i>(Ich hasse zu viele Emails!)</i></li>
    <li type="disc">Durch den Aufruf von <code><?php echo get_site_url(); ?>/wp-json/dienste/updateFromNuliga</code> kann der Import automatisiert werden.</li>
</ul>

<button class="btn btn-primary" 
        onclick="startImportSpiele()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Spiele importieren
</button>

<hr>
    <div class="accordion" id="accordionMeisterschaften">
        <?php foreach($meisterschaften as $meisterschaft){  ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $meisterschaft->getID(); ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $meisterschaft->getID(); ?>" aria-expanded="true" aria-controls="collapse<?php echo $meisterschaft->getID(); ?>">
                        <?php echo $meisterschaft->getName(); ?>
                    </button>
                </h2>
                <div id="collapse<?php echo $meisterschaft->getID(); ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $meisterschaft->getID(); ?>" data-bs-parent="#accordionMeisterschaften">
                    <div class="accordion-body">    
                        <table class="table">  
                            <tr>
                                <th>Mannschaft</th>
                                <th>Liga</th>
                                <th>Spiele</th>
                                <th>aktiv</th>
                            </tr>            
                            <?php foreach($mannschaften as $mannschaft){  
                                $meisterschaftsMeldungen = $mannschaft->getMeldungenFuerMeisterschaft($meisterschaft->getID());
                                if(count($meisterschaftsMeldungen) === 0){
                                    continue;
                                }
                                foreach ($meisterschaftsMeldungen as $meldung) {
                                    $anzahlSpiele = countSpiele($meldung->getID(), $mannschaft->getID());
                                    $input_id = "aktiv_".$meldung->getID();
                                    $checked = $meldung->isAktiv()?"checked":""; 
                                    echo "<tr>"
                                        ."<td>".$mannschaft->getName()."</td>"
                                        ."<td>".$meldung->getLiga()   ."</td>"
                                        ."<td>".$anzahlSpiele         ."</td>"
                                        ."<td>"
                                            ."<input type=\"checkbox\" id=\"$input_id\" $checked onClick=\"meldungAktivieren(".$meldung->getID().", this.checked)\">"
                                        ."</td>"
                                        ."</tr>";
                                }
                            } ?>   
                        </table>
                    </div>
                </div>
            </div>
        <?php  } ?>
    </div>
</div>
 <?php
}

function spiele_importieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = importSpieleFromNuliga();
    echo json_encode($importErgebnis, JSON_PRETTY_PRINT);
    exit;
}

function meisterschaften_importieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = importMeisterschaftenFromNuliga();
    echo json_encode($importErgebnis, JSON_PRETTY_PRINT);
    exit;
}
function meisterschaften_importieren_new(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = importMeisterschaftenFromNuliga_new();
    echo "Erfolg";
    exit;
}

function teamIDs_importieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = importTeamIDsFromNuLiga();
    echo "Erfolg\n";
    exit;
}

function meisterschaften_aktualisieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = updateMeisterschaften();
    echo "Erfolg\n";
    exit;
}

function meldungen_aktualisieren(){
    require_once __DIR__."/../import/importer.php";
    $importErgebnis = updateMannschaftsMeldungen();
    echo "Erfolg\n";
    exit;
}
?>