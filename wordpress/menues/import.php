<?php
require_once __DIR__."/../import/importer.php";

function addDiensteSpieleImportKonfiguration(){
    $hook_import = add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displaySpieleImport');
}
add_action( 'wp_ajax_alles_importieren', 'alles_importieren' );
add_action( 'wp_ajax_start_import_schritt', 'start_import_schritt' );
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
    jQuery.post(ajaxurl, {'action': 'alles_importieren'});
}

function startImportSchritt(schritt){
    var data = {
        'action': 'start_import_schritt', 
        'schritt': schritt
    };
    jQuery.post(ajaxurl, data);
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

setInterval(function(){
    jQuery.post(ajaxurl, {'action': 'status_lesen'}, function(response) {
        statusSchritte = JSON.parse(response);
        statusSchritte.forEach(function(schritt){
            if(schritt.start === null){
                hinweistext = "wartet";
                jQuery("#spinner-schritt-"+schritt.schritt).show();
            } else if(schritt.ende === null){
                hinweistext = "";
                jQuery("#spinner-schritt-"+schritt.schritt).show();
            } else{
                hinweistext = "fertig";
                jQuery("#spinner-schritt-"+schritt.schritt).hide();

            }
            jQuery("#hinweis-schritt-"+schritt.schritt).html(hinweistext);
        });
    });
}, 500);

</script>

<div class="wrap">
<h2>Meisterschaften importieren</h2>
<div class="accordion" id="accordionAnleitung">
    <div class="accordion-item">
        <h2 class="accordion-header" id="anleitungAufklappen">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnleitung" aria-expanded="false" aria-controls="collapseOne">
            Anleitung
        </button>
        </h2>
        <div id="collapseAnleitung" class="accordion-collapse collapse" aria-labelledby="anleitungAufklappen" data-bs-parent="#accordionAnleitung">
        <div class="accordion-body">
            <p>
                Zuerst müssen Meisterschaften importiert werden, welche dann unten auftauchen. Für jede 
                <a href="<?php echo get_permalink( get_page_by_path( 'dienste-mannschaften' ) );?>">Mannschaft, die zuvor konfiguriert wurde</a> werden dann Mannschaftsmeldungen geladen und hier dargestellt.<br>
                Bestehende Meisterschaften und Meldungen bleiben bei jedem weiterem Import erhalten. Der Import dauert recht lange, da viele Seiten von nuLiga gescannt werden.
            </p>
            <p>
                Importierte Mannschaftsmeldungen können einzeln aktiviert & deaktiviert werden. Dazugehörige Spiele werden zwar noch aktualisiert, aber die Spiele werden nicht mehr in der Liste der Dienste dargestellt.
            </p>

            <h3>Spiele importieren</h3>
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
        </div>
    </div>
</div>

<button class="btn btn-primary" 
    onclick="startImportAlles()" >
    Alles importieren
</button>
<button class="btn btn-primary" 
    onclick="startImportSchritt(<?php echo Importer::$SPIELE_IMPORTIEREN->schritt;?>)">
    (nur) Spiele importieren
</button>
<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManuellerImport" aria-expanded="false" aria-controls="collapseManuellerImport">
    Manueller Import...
</button>
<div class="collapse" id="collapseManuellerImport">
    <div class="card card-body">
        <table class="table">
            <tr>
                <th>Beschreibung</th>
                <th>Status</th>
                <th></th>
            </tr>
            <?php foreach(Importer::alleSchritte() as $importSchritt){ ?>
                <tr>
                    <td>
                        <?php echo $importSchritt->beschreibung;?>
                    </td>
                    <td>
                        <span id="spinner-schritt-<?php echo $importSchritt->schritt;?>" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span id="hinweis-schritt-<?php echo $importSchritt->schritt;?>"></span>                    
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-sm" 
                            onclick="startImportSchritt(<?php echo $importSchritt->schritt;?>)" >
                            starten
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

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
    Importer::starteAlles();
    exit;
}

function start_import_schritt(){
    Importer::alleSchritte()[$_POST['schritt']]->run();
    exit;
}

function status_lesen(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'import_status';
    echo json_encode($wpdb->get_Results("SELECT * FROM $table_name ORDER BY start"));
    exit;
}

?>