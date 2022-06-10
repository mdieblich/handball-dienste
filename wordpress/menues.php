<?php

function addDiensteMenueeintraege() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    add_menu_page(  'Dienste', 'Dienste', 'administrator', 'dienste', 'displayDiensteDashboard', 'dashicons-schedule' );
    
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    addDiensteMannschaftsKonfiguration();
    add_submenu_page( 'dienste', 'Dienste - Spiele importieren', 'Import', 'administrator', 'dienste-import', 'displayDiensteImport');
    add_submenu_page( 'dienste', 'Dienste - Gegner einrichten', 'Gegner', 'administrator', 'dienste-gegner', 'displayDiensteGegner');
    add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
}

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
        'displayMannschaftAendern',
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

    add_action( 'load-' . $hook_mannschaften, 'diensteMannschaftenSubmit' );
}

function displayDiensteDashboard(){}
function displayDiensteMannschaften(){
    
    require_once __DIR__."/dao/mannschaft.php";
    $mannschaften = loadMannschaften();

    ?>
    <div class="wrap">
        <h1>Mannschaften einrichten</h1>
        <table border="1">
            <tr>
                <th> Nr. </th>
                <th> w/m </th>
                <th> Meisterschaft </th>
                <th> Liga </th>
                <th> nuLiga: ID Liga </th>
                <th> nuLiga: ID Team </th>
                <th> <!-- Spalte für Aktionen //--> </th>
            </tr>
        <?php foreach($mannschaften as $mannschaft){ ?>
            <tr><form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                <input type="hidden" name="mannschafts-id" value="<?php echo $mannschaft->getID(); ?>">
                <td> <input type="number" name="mannschafts-nummer" value="<?php echo $mannschaft->getNummer(); ?>" min="1"> </td>
                <td> 
                    <select name="mannschafts-geschlecht"> 
                        <option value="w" <?php if($mannschaft->getGeschlecht()==GESCHLECHT_W) echo "selected"; ?>>Damen</option>
                        <option value="m" <?php if($mannschaft->getGeschlecht()==GESCHLECHT_M) echo "selected"; ?>>Herren</option>
                    </select> 
                </td>
                <td> <input type="text" name="mannschafts-meisterschaft" value="<?php echo $mannschaft->getMeisterschaft(); ?>"> </td>
                <td> <input type="text" name="mannschafts-liga" value="<?php echo $mannschaft->getLiga(); ?>"> </td>
                <td> <input type="number" name="mannschafts-nuliga-liga-id" value="<?php echo $mannschaft->getNuligaLigaID(); ?>"> </td>
                <td> <input type="number" name="mannschafts-nuliga-team-id" value="<?php echo $mannschaft->getNuligaTeamID(); ?>"> </td>
                <td> 
                    <?php
                    settings_fields( 'alte_mannschaft' );
                    do_settings_sections( 'dienste_mannschaft_aendern' );
                    wp_nonce_field('dienste_mannschaft_aendern_'.$mannschaft->getID());
                    submit_button( __( 'Ändern', 'textdomain' ) );
                    ?>
                </td>
            </form></tr> 
        <?php } ?>
            <tr><form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                <td> <input type="number" name="mannschafts-nummer" value="1" min="1"> </td>
                <td> 
                    <select name="mannschafts-geschlecht"> 
                        <option value="w">Damen</option>
                        <option value="m">Herren</option>
                    </select> 
                </td>
                <td> <input type="text" name="mannschafts-meisterschaft"> </td>
                <td> <input type="text" name="mannschafts-liga"> </td>
                <td> <input type="number" name="mannschafts-nuliga-liga-id"> </td>
                <td> <input type="number" name="mannschafts-nuliga-team-id"> </td>
                <td> 
                    <?php
                    settings_fields( 'neue_mannschaft' );
                    do_settings_sections( 'dienste_mannschaft_hinzufuegen' );
                    wp_nonce_field('dienste-mannschaft-hinzufuegen_neu');
                    submit_button( __( 'Anlegen', 'textdomain' ) );
                    ?>
                </td>
            </form></tr>
        </table>
    </div>
    <?php
}

function dummy_function(){}

function diensteMannschaftenSubmit(){
    ?>
    <div style="margin-left: 200px; background-color:#ffffff99">
    <?php var_dump($_POST); ?>
    </div><?php
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

function handleAlteMannschaft(){}

function isGueltigeMannschaftUebertragen(){
    $expectedKeys = array(
        'mannschafts-nummer', 
        'mannschafts-geschlecht',
        'mannschafts-meisterschaft',
        'mannschafts-liga',
        'mannschafts-nuliga-liga-id',
        'mannschafts-nuliga-team-id'
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
    
    if(!is_numeric($_POST['mannschafts-nuliga-liga-id'])){
        return false;
    }
    
    if(!is_numeric($_POST['mannschafts-nuliga-team-id'])){
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
        'meisterschaft' => $_POST['mannschafts-meisterschaft'],
        'liga' => $_POST['mannschafts-liga'],
        'nuliga_liga_id' => $_POST['mannschafts-nuliga-liga-id'],
        'nuliga_team_id' => $_POST['mannschafts-nuliga-team-id']
        ));
}
function displayDiensteImport(){}
function displayDiensteGegner(){}
function displayDiensteZuweisen(){}

?>