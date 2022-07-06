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
                schritt.start = "Noch nicht";
            } else if(schritt.ende === null){
                hinweistext = "";
                jQuery("#spinner-schritt-"+schritt.schritt).show();
            } else{
                hinweistext = "fertig";
                jQuery("#spinner-schritt-"+schritt.schritt).hide();

            }
            jQuery("#letzter-start-schritt-"+schritt.schritt).html(schritt.start);
            jQuery("#hinweis-schritt-"+schritt.schritt).html(hinweistext);
        });
    });
}, 500);

</script>

<div class="wrap">

<div class="card-group">
    <div class="bootstrap-card">
        <h5 class="card-header">Import</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <p class="card-text">
                    In der Regel reicht es einmalig alles zu importieren. <br>
                    Der Import dauert recht lange, da viele Seiten von nuLiga gescannt werden.
                </p>
                <p class="card-text">
                    Es werden nur Meisterschaften und Spiele importiert von 
                    <a href="<?php echo get_admin_url() ;?>admin.php?page=dienste-mannschaften">Mannschaft, die zuvor konfiguriert wurden</a>.
                </p>
            </li>
            <li class="list-group-item">
                <a class="card-link" href="javascript:startImportAlles()">Alles importieren</a>
                <i>(inklusive Spiele)</i>
            </li>
            <li class="list-group-item">
                <p class="card-text">
                    Der Spiele-Import kann zusätzlich manuell gestartet werden.<br>
                    Über den Endpunkt <code><?php echo get_site_url(); ?>/wp-json/dienste/updateFromNuliga</code> wird er jede Nacht gestartet.
                </p>
                <ul style="font-size:13px">
                    <li type="disc">Bei vorhandenen Spiele werden Datum, Uhrzeit & Halle aktualisiert</li>
                    <li type="disc">Zugewiesene Dienste bleiben erhalten</li>
                    <li type="disc">Gegnerische Mannschaften werden automatisch mitimportiert</li>
                    <li type="disc">Sollte sich ein Spiel ändern (Datum, Uhrzeit oder Halle), bei dem eine Mannschaft schon Dienste zugewiesen bekommen hat, dann bekommt diese eine Email.</li>
                    <li type="disc">Auch bei mehreren sich ändernden Spielen bekommt eine Mannschaft pro Import immer nur genau <u>eine</u> Email. <i>(Ich hasse zu viele Emails!)</i></li>
                </ul>
            </li>
            <li class="list-group-item">
                <i>(nur)</i> <a class="card-link" href="javascript:startImportSchritt(<?php echo Importer::$SPIELE_IMPORTIEREN->schritt;?>)">Spiele importieren</a>
            </li>
        </ul>
    </div> <!-- bootstrap-card Import -->
    <div class="bootstrap-card">
        <h5 class="card-header">Manueller Import</h5>
        <div class="card-body">
            <p class="card-text">
                Sollte es Schwierigkeiten beim Import geben kann dieser hier überwacht und Schritte können einzeln gestartet werden.
            </p>
            <table class="table">
                <tr>
                    <th>Beschreibung</th>
                    <th>Start</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                <?php foreach(Importer::alleSchritte() as $importSchritt){ ?>
                    <tr>
                        <td>
                            <?php echo $importSchritt->beschreibung;?>
                        </td>
                        <td id="letzter-start-schritt-<?php echo $importSchritt->schritt;?>" style="width:200px">
                            Noch nicht
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
    </div> <!-- bootstrap-card -->
</div> <!-- card-group -->

<hr>
<h1> Meisterschaften aktivieren & deaktivieren</h1>
<p>
    Importierte Mannschaftsmeldungen können einzeln aktiviert & deaktiviert werden. Dazugehörige Spiele werden zwar noch aktualisiert, aber die Spiele werden nicht mehr in der Liste der Dienste dargestellt.
</p>
<?php
    require_once __DIR__."/../dao/mannschaft.php";
    require_once __DIR__."/../dao/meisterschaft.php";
    require_once __DIR__."/../dao/spiel.php";
    $mannschaften = loadMannschaftenMitMeldungen();
    
    $spielDAO = new SpielDAO();
    global $wpdb;
    $meisterschaften = loadMeisterschaften("id in (SELECT meisterschaft FROM ".$wpdb->prefix."mannschaftsMeldung)");
    if(count($meisterschaften) === 0){
        echo "<div class='card'><i>Keine Meisterschaften gefunden</i></div>";
    }
?>
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
                            <th>Meldung für Liga</th>
                            <th>Spiele</th>
                            <th>aktiv</th>
                        </tr>            
                        <?php foreach($mannschaften as $mannschaft){  
                            $meisterschaftsMeldungen = $mannschaft->getMeldungenFuerMeisterschaft($meisterschaft->getID());
                            if(count($meisterschaftsMeldungen) === 0){
                                continue;
                            }
                            foreach ($meisterschaftsMeldungen as $meldung) {
                                $anzahlSpiele = $spielDAO->countSpiele($meldung->getID(), $mannschaft->getID());
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