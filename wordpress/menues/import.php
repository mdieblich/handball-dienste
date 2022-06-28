<?php

function addDiensteSpieleImportKonfiguration(){
    $hook_import = add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displaySpieleImport');
}
add_action( 'wp_ajax_meisterschaften_importieren', 'meisterschaften_importieren' );
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

<h2>Meisterschaften importieren</h2>
<p>
    Zuerst müssen Meisterschaften importiert werden, welche dann unten auftauchen. Für jede 
    <a href="<?php echo get_permalink( get_page_by_path( 'dienste-mannschaften' ) );?>">Mannschaft, die zuvor konfiguriert wurde</a> werden dann Mannschaftsmeldungen geladen und hier dargestellt.<br>
    Bestehende Meisterschaften und Meldungen bleiben bei jedem weiterem Import erhalten. Der Import dauert recht lange, da viele Seiten von nuLiga gescannt werden.
</p>
<p>
    Importierte Mannschaftsmeldungen können einzeln aktiviert & deaktiviert werden. Dazugehörige Spiele werden zwar noch aktualisiert, aber die Spiele werden nicht mehr in der Liste der Dienste dargestellt.
</p>
<button class="btn btn-primary" onclick="startImportMeisterschaften()">Meisterschaften importieren</button>
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

<button class="btn btn-primary" onclick="startImportSpiele()">Spiele importieren</button>

<div class="spinner-border" role="status" id="loading-spinner" style="display:none">
    <span class="visually-hidden">Loading...</span>
</div>

<div id="import-result" style="display:none">Ergebnisse!</div>

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
?>