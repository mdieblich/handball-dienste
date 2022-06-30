<?php
require_once __DIR__."/../import/importer.php";

function addDiensteSpieleImportKonfiguration(){
    $hook_import = add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displaySpieleImport');
}
add_action( 'wp_ajax_alles_importieren', 'alles_importieren' );
add_action( 'wp_ajax_meisterschaften_importieren', 'meisterschaften_importieren' );
add_action( 'wp_ajax_teamIDs_importieren', 'teamIDs_importieren' );
add_action( 'wp_ajax_mannschaften_zuordnen', 'mannschaften_zuordnen' );
add_action( 'wp_ajax_meisterschaften_aktualisieren', 'meisterschaften_aktualisieren' );
add_action( 'wp_ajax_meldungen_aktualisieren', 'meldungen_aktualisieren' );
add_action( 'wp_ajax_spiele_importieren', 'spiele_importieren' );
add_action( 'wp_ajax_import_cache_leeren', 'import_cache_leeren' );

add_action( 'wp_ajax_status_lesen', 'status_lesen' );

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
function startImportAlles(){
    jQuery(function($){
        $("#importModalLabel").html("Importiere Alles...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'alles_importieren'};

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

function startMannschaftenZuordnen(){
    jQuery(function($){
        $("#importModalLabel").html("Ordne die Mannschaften zu");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'mannschaften_zuordnen'};

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
function startCacheLeeren(){
    jQuery(function($){
        $("#importModalLabel").html("Leere Cache...");
        $("#import-result").hide();
        $("#loading-spinner").show(500);
    });

    var data = {'action': 'import_cache_leeren'};

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

<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startImportAlles()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Alles importieren
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startImportMeisterschaften()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Meisterschaften importieren
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startImportTeamIDs()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Team-IDs importieren
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startMannschaftenZuordnen()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Mannschaften zuordnen
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
<button class="btn btn-primary" 
        onclick="startImportSpiele()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Spiele importieren
</button>
<button class="btn btn-primary psition-relative bottom-0 start-50 " 
        onclick="startCacheLeeren()" 
        data-bs-toggle="modal" data-bs-target="#exampleModal">
    Cache leeren
</button>
<br><br>
<hr>
Fortschritt
<div class="container">
    <div class="row" id="step-"
</div>
<div class="progress">
  <div class="progress-bar" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">aa</div>
  <div class="progress-bar bg-success" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
  <div class="progress-bar bg-info" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<br>
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

function alles_importieren(){
    echo "Start\n";

    echo "Meisterschaften ...";
    Importer::$NULIGA_MEISTERSCHAFTEN_LESEN->run();
    echo "importiert!\n";
    
    echo "Team-IDs ...";
    Importer::$NULIGA_TEAM_IDS_LESEN->run();
    echo "importiert!\n";
    
    echo "Mannschaften ...";
    Importer::$MANNSCHAFTEN_ZUORDNEN->run();
    echo "zugeordnet!\n";
    
    echo "Meisterschaften ...";
    Importer::$MEISTERSCHAFTEN_AKTUALISIEREN->run();
    echo "aktualisiert!\n";
    
    echo "Meldungen ...";
    Importer::$MELDUNGEN_AKTUALISIEREN->run();
    echo "aktualisiert !\n";
    
    echo "Spiele ...";
    Importer::$SPIELE_IMPORTIEREN->run();
    echo "importiert!\n";
    
    echo "Cache ...";
    Importer::$CACHE_LEEREN->run();
    echo "geleert!\n";

    echo "Erfolgreich abgeschlossen";
    exit;
}

function meisterschaften_importieren(){
    Importer::$NULIGA_MEISTERSCHAFTEN_LESEN->run();
    echo "Erfolg";
    exit;
}

function teamIDs_importieren(){
    Importer::$NULIGA_TEAM_IDS_LESEN->run();
    echo "Erfolg\n";
    exit;
}

function mannschaften_zuordnen(){
    Importer::$MANNSCHAFTEN_ZUORDNEN->run();
    echo "Erfolg\n";
    exit;
}

function meisterschaften_aktualisieren(){
    Importer::$MEISTERSCHAFTEN_AKTUALISIEREN->run();
    echo "Erfolg\n";
    exit;
}

function meldungen_aktualisieren(){
    Importer::$MELDUNGEN_AKTUALISIEREN->run();
    echo "Erfolg\n";
    exit;
}

function spiele_importieren(){
    Importer::$SPIELE_IMPORTIEREN->run();
    exit;
}
function import_cache_leeren(){
    Importer::$CACHE_LEEREN->run();
    echo "Erfolg";
    exit;
}

function status_lesen(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'import_status';
    echo json_encode($wpdb->get_Results("SELECT * FROM $table_name ORDER BY start"));
    exit;
}

?>