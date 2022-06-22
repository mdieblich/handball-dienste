<?php

function addDiensteMannschaftsKonfiguration(){
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    $hook_mannschaften = add_submenu_page( 'dienste', 'Dienste - Mannschaften einrichten', 'Mannschaften', 'administrator', 'dienste-mannschaften', 'displayDiensteMannschaften');
    add_settings_section(
        'dienste_mannschaft_hinzufuegen',
        'Mannschaft hinzufügen',
        'displayMannschaftHinzufuegen',
        'dienste-mannschaften'
    );

    add_settings_field( 
        'neue_mannschaft',                      // ID used to identify the field throughout the theme
        '',                           // The label to the left of the option interface element
        'dummy_function',   // The name of the function responsible for rendering the option interface
        'dienste-mannschaften',                          // The page on which this option will be displayed
        'dienste_mannschaft_hinzufuegen'         // The name of the section to which this field belongs
        
    );
    register_setting('dienste', 'neue_mannschaft');

    add_settings_section(
        'dienste_mannschaft_aendern',
        'Mannschaft ändern',
        'dummy_function',
        'dienste-mannschaften'
    );

    add_settings_field( 
        'alte_mannschaft',                      // ID used to identify the field throughout the theme
        '',                           // The label to the left of the option interface element
        'dummy_function',   // The name of the function responsible for rendering the option interface
        'dienste-mannschaften',                          // The page on which this option will be displayed
        'dienste_mannschaft_aendern'         // The name of the section to which this field belongs
        
    );
    register_setting('dienste', 'alte_mannschaft');

    add_settings_section(
        'dienste_mannschaft_loeschen',
        'Mannschaft löschen',
        'dummy_function',
        'dienste-mannschaften'
    );

    add_settings_field( 
        'loesche_mannschaft',                      // ID used to identify the field throughout the theme
        '',                           // The label to the left of the option interface element
        'dummy_function',   // The name of the function responsible for rendering the option interface
        'dienste-mannschaften',                          // The page on which this option will be displayed
        'dienste_mannschaft_loeschen'         // The name of the section to which this field belongs
        
    );
    register_setting('dienste', 'loesche_mannschaft');

    add_action( 'load-' . $hook_mannschaften, 'diensteMannschaftenSubmit' );
}

function displayDiensteMannschaften(){
    
    require_once __DIR__."/../dao/mannschaft.php";
    $mannschaften = loadMannschaftenMitMeisterschaften();

    ?>
    <script>
function updateGeschlecht(jugendklasse, id){
    bezeichnungW = jugendklasse ? "Mädchen" : "Damen";
    bezeichnungM = jugendklasse ? "Jungen"  : "Herren";
    document.getElementById("option-w-"+id).innerHTML=bezeichnungW;
    document.getElementById("option-m-"+id).innerHTML=bezeichnungM;
}
    </script>
    <div class="wrap">
        <h1>Mannschaften einrichten</h1>
        <ol>
            <li>
                <b>Meisterschaft</b>, <b>nuLiga: ID Liga</b>, <b>nuLiga: ID Team</b> sind Werte, die aus nuliga stammen. Über diese Werte sucht der Importer die Tabelle aller Spiele. <br>
                Am besten findet man diese Werte, wenn man in einer Liga auf die jeweilige Mannschaft klickt und sich dann die URL anschaut. 
                Hier werden die Werte <i>championship</i>, <i>group</i> und <i>teamtable</i> benötigt. <br>
                <br>
                Bsp: <code>https://hvmittelrhein-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?teamtable=<span style="color:#37bbbf">1744276</span>&pageState=vorrunde&championship=<span style="color:red">MR+21%2F22</span>&group=<span style="color:blue">274529</span></code><br>
                wird zu <code>Meisterschaft = <span style="color:red">MR 21/22</span></code>, <code>nuLiga: ID Liga = <span style="color:blue">274529</span></code> und <code>nuLiga: ID Team = <span style="color:#37bbbf">1744276</span></code>
            </li>
            <li>
                Der Name der <b>Liga</b> wird dazu genutzt, um dies bei importierten Gegnern zu hinterlegen.<br>
                Für zukünftige Verwendungen sollte er so sein, wie er in nuLiga steht.
            </li>
        </ol>
        
        <div class="accordion" id="accordionMannschaften">
            <?php foreach ($mannschaften as $mannschaft) { 
            $bezeichnungW = ($mannschaft->getJugendklasse() !== null) ? "Mädchen" : "Damen";
            $bezeichnungM = ($mannschaft->getJugendklasse() !== null) ? "Jungen"  : "Herren";
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $mannschaft->getID(); ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $mannschaft->getID(); ?>" aria-expanded="true" aria-controls="collapse<?php echo $mannschaft->getID(); ?>">
                            <?php echo $mannschaft->getName(); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $mannschaft->getID(); ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $mannschaft->getID(); ?>" data-bs-parent="#accordionMannschaften">
                        <div class="accordion-body">    
                            <form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                                <input type="hidden" name="mannschafts-id" value="<?php echo $mannschaft->getID(); ?>">
                                <div class="row">
                                    <div class="col-2"><div class="form-floating">
                                        <input title="Die fortlaufende Nummer, unter der eine Mannschaft gemeldet ist, also z.B. 1. Herren, 2. Herren, 3. Herren usw.." type="number" class="form-control" placeholder="XX" name="mannschafts-nummer" value="<?php echo $mannschaft->getNummer(); ?>" min="1" id="mannschafts-nummer-<?php echo $mannschaft->getID(); ?>">
                                        <label for="mannschafts-nummer-<?php echo $mannschaft->getID(); ?>">Nummer</label>
                                    </div></div>
                                    <div class="col-2"><div class="form-floating">
                                        <select name="mannschafts-geschlecht" class="form-select" id="mannschafts-geschlecht-<?php echo $mannschaft->getID(); ?>"> 
                                            <option value="w" <?php if($mannschaft->getGeschlecht()==GESCHLECHT_W) echo "selected"; ?> id="option-w-<?php echo $mannschaft->getID(); ?>"><?php echo $bezeichnungW; ?></option>
                                            <option value="m" <?php if($mannschaft->getGeschlecht()==GESCHLECHT_M) echo "selected"; ?> id="option-m-<?php echo $mannschaft->getID(); ?>"><?php echo $bezeichnungM; ?></option>
                                        </select> 
                                        <label for="mannschafts-geschlecht-<?php echo $mannschaft->getID(); ?>">Geschlecht</label>
                                    </div></div>
                                    <div class="col-2"><div class="form-floating">
                                        <input type="text" class="form-control" title='Sollte mit "A", "B"  usw. befüllt werden, wenn es sich um eine Jugend-Mannschaft handelt. Auch "Minis" ist möglich. Für Senioren-Mannschaften dies leer lassen.' placeholder="Mädels" name="mannschafts-jugendklasse" value="<?php echo $mannschaft->getJugendklasse(); ?>" onchange="updateGeschlecht(this.value, '<?php echo $mannschaft->getID(); ?>')" id="mannschafts-jugendklasse-<?php echo $mannschaft->getID(); ?>">
                                        <label for="mannschafts-jugendklasse-<?php echo $mannschaft->getID(); ?>">Jugendklasse (optional)</label>
                                    </div></div>
                                    <div class="col-2"><div class="form-floating">
                                        <input type="text" class="form-control" title='An diese Adresse werden Updates geschickt, wenn sich nach einem Import an den Spielen etwas ändert, bei denen diese Mannschaft Dienste hat.' placeholder="example@turnerkreisnippes.de" name="mannschafts-email" value="<?php echo $mannschaft->getEmail(); ?>" id="mannschafts-email-<?php echo $mannschaft->getID(); ?>">
                                        <label for="mannschafts-email-<?php echo $mannschaft->getID(); ?>">E-Mail (optional)</label>
                                    </div></div>
                                    <div class="col">
                                    <?php
                                        settings_fields( 'alte_mannschaft' );
                                        do_settings_sections( 'dienste_mannschaft_aendern' );
                                        wp_nonce_field('dienste-mannschaft-aendern_'.$mannschaft->getID());
                                        submit_button( 'Ändern', 'btn btn-primary btn-lg' , 'submit-change', false);
                                    ?><!--input type="submit" class="btn btn-primary btn-lg" value="Ändern" //-->
                                    </form>
                                    <form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                                        <input type="hidden" name="mannschafts-id" value="<?php echo $mannschaft->getID(); ?>">
                                        <?php
                                        settings_fields( 'loesche_mannschaft' );
                                        do_settings_sections( 'dienste_mannschaft_loeschen' );
                                        wp_nonce_field('dienste-mannschaft-loeschen_'.$mannschaft->getID());
                                        submit_button( 'Löschen', 'delete', 'submit-delete', false );
                                        ?>
                                        </form>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingNeu">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNeu" aria-expanded="true" aria-controls="collapseNeu">
                        Neue Mannschaft anlegen...
                    </button>
                </h2>
                <div id="collapseNeu" class="accordion-collapse collapse" aria-labelledby="headingNeu" data-bs-parent="#accordionMannschaften">
                    <div class="accordion-body">
                        <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function dummy_function(){}

function diensteMannschaftenSubmit(){
    // TODO check auf korrekte Berechtigung
    // 1. Berechtigung anlegen https://wordpress.org/support/article/roles-and-capabilities/
    // 2. hier prüfen mit: if ( current_user_can( 'edit_others_posts' ) ) {
    // siehe auch https://developer.wordpress.org/plugins/security/checking-user-capabilities/#restricted-to-a-specific-capability
    if(!somethingWasSentWithPOST()){
        return;
    }
    switch($_POST['option_page']){
        case 'neue_mannschaft': handleNeueMannschaft(); break;
        case 'alte_mannschaft': handleAlteMannschaft(); break;
        case 'loesche_mannschaft': handleMannschaftLoeschen(); break;
    }
}

function somethingWasSentWithPOST(){
    return 'POST' === $_SERVER['REQUEST_METHOD'];
}

function handleNeueMannschaft(){
    if(!check_admin_referer('dienste-mannschaft-hinzufuegen_neu')){
        return;
    }
    if(!isGueltigeMannschaftUebertragen()){
        return;
    }
    insertNeueMannschaftFrom_POST();
}

function handleAlteMannschaft(){
    if(empty($_POST['mannschafts-id'])){
        return;
    }
    $id = $_POST['mannschafts-id'];
    if(!check_admin_referer('dienste-mannschaft-aendern_'.$id)){
        return;
    }
    if(!isGueltigeMannschaftUebertragen()){
        return;
    }
    updateMannschaftFrom_POST();
}
function handleMannschaftLoeschen(){
    if(empty($_POST['mannschafts-id'])){
        return;
    }
    $id = $_POST['mannschafts-id'];
    if(!check_admin_referer('dienste-mannschaft-loeschen_'.$id)){
        return;
    }
    deleteMannschaftFrom_POST();
}

function isGueltigeMannschaftUebertragen(){
    $expectedKeys = array(
        'mannschafts-nummer', 
        'mannschafts-geschlecht'
    );
    $missingKeys = array_diff($expectedKeys, array_keys($_POST));
    if(count($missingKeys) > 0){
        return false;
    }
    
    if(!is_numeric($_POST['mannschafts-nummer'])){
        return false;
    }
    
    if($_POST['mannschafts-geschlecht'] !== 'w' && $_POST['mannschafts-geschlecht'] !== 'm'){
        return false;
    }
    
    if (!empty($_POST['mannschafts-email']) && !filter_var($_POST['mannschafts-email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}

function insertNeueMannschaftFrom_POST(){
    global $wpdb;
    
	$table_name = $wpdb->prefix . 'mannschaft';

    $wpdb->insert($table_name, array(
        'nummer' => $_POST['mannschafts-nummer'],
        'geschlecht' => $_POST['mannschafts-geschlecht'],
        'jugendklasse' => $_POST['mannschafts-jugendklasse'],
        'email' => $_POST['mannschafts-email']
        ));

    $mannschaftsID = $wpdb->insert_id;
}

function updateMannschaftFrom_POST(){
    global $wpdb;
    
	$table_name = $wpdb->prefix . 'mannschaft';
    $jugendklasse = null;
    if(!empty($_POST['mannschafts-jugendklasse'])){
        $jugendklasse = $_POST['mannschafts-jugendklasse'];
    }

    $wpdb->update($table_name, array(
        'nummer' => $_POST['mannschafts-nummer'],
        'geschlecht' => $_POST['mannschafts-geschlecht'],
        'jugendklasse' => $jugendklasse,
        'email' => $_POST['mannschafts-email']
    ), array(
        'id' => $_POST['mannschafts-id']
    ));
}

function deleteMannschaftFrom_POST(){
    global $wpdb;
    
	$table_name = $wpdb->prefix . 'mannschaft';

    $wpdb->delete($table_name, array(
        'id' => $_POST['mannschafts-id']
    ));
}
?>