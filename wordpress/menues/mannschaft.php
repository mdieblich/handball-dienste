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
    $mannschaften = loadMannschaften();

    ?>
    <div class="wrap">
        <h1>Mannschaften einrichten</h1>
        <ol>
            <li>
                Mit <b>Nummer</b> ist die fortlaufende Nummer gemeint, unter der eine Mannschaft gemeldet ist, also z.B. 1. Herren, 2. Herren, 3. Herren usw..
            </li>
            <li>
                <b>Jugend</b> sollte mit "A", "B"  usw. befüllt werden, wenn es sich um eine Jugend-Mannschaft handelt. Auch "Minis" ist möglich. Für Senioren-Mannschaften dies leer lassen.
            </li>
            <li>
                An die <b>E-Mail-Adresse</b><i>(optional)</i> werden Updates geschickt, wenn sich nach einem Import an den Spielen etwas ändert, bei denen diese Mannschaft Dienste hat. 
            </li>
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
        <table cellspacing="1">
            <tr>
                <th> Nr. </th>
                <th> w/m </th>
                <th> Jugend </th>
                <th> Email </th>
                <th> Meisterschaft </th>
                <th> Liga </th>
                <th> nuLiga: ID Liga </th>
                <th> nuLiga: ID Team </th>
                <th colspan="2"> <!-- Spalte für Aktionen //--> </th>
            </tr>
        <?php foreach($mannschaften as $mannschaft){ ?>
            <tr><form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                <input type="hidden" name="mannschafts-id" value="<?php echo $mannschaft->getID(); ?>">
                <td style="text-align:center"> <input type="number" name="mannschafts-nummer" value="<?php echo $mannschaft->getNummer(); ?>" min="1" style="width:50px"> </td>
                <td style="text-align:center"> 
                    <select name="mannschafts-geschlecht"> 
                        <option value="w" <?php if($mannschaft->getGeschlecht()==GESCHLECHT_W) echo "selected"; ?>>Damen</option>
                        <option value="m" <?php if($mannschaft->getGeschlecht()==GESCHLECHT_M) echo "selected"; ?>>Herren</option>
                    </select> 
                </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-jugendklasse" value="<?php echo $mannschaft->getJugendklasse(); ?>" style="width:50px"> </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-email" value="<?php echo $mannschaft->getEmail(); ?>" style="width:150px"> </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-meisterschaft" value="<?php echo $mannschaft->getMeisterschaft(); ?>" style="width:100px"> </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-liga" value="<?php echo $mannschaft->getLiga(); ?>"> </td>
                <td style="text-align:center"> <input type="number" name="mannschafts-nuliga-liga-id" value="<?php echo $mannschaft->getNuligaLigaID(); ?>" style="width:100px"> </td>
                <td style="text-align:center"> <input type="number" name="mannschafts-nuliga-team-id" value="<?php echo $mannschaft->getNuligaTeamID(); ?>" style="width:100px"> </td>
                <td style="text-align:left"> 
                    <?php
                    settings_fields( 'alte_mannschaft' );
                    do_settings_sections( 'dienste_mannschaft_aendern' );
                    wp_nonce_field('dienste-mannschaft-aendern_'.$mannschaft->getID());
                    submit_button( 'Ändern', 'primary' , 'submit-change', false);
                    ?>
                    </form>
        </td><td style="text-align:left">
                    <form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                <input type="hidden" name="mannschafts-id" value="<?php echo $mannschaft->getID(); ?>">
                    <?php
                    settings_fields( 'loesche_mannschaft' );
                    do_settings_sections( 'dienste_mannschaft_loeschen' );
                    wp_nonce_field('dienste-mannschaft-loeschen_'.$mannschaft->getID());
                    submit_button( 'Löschen', 'delete', 'submit-delete', false );
                    ?>
                    </form>
                </td>
            </tr> 
        <?php } ?>
            <tr><form action="<?php menu_page_url( 'dienste-mannschaften' ) ?>" method="post">
                <td style="text-align:center"> <input type="number" name="mannschafts-nummer" min="1" style="width:50px"> </td>
                <td style="text-align:center"> 
                    <select name="mannschafts-geschlecht"> 
                        <option value="w">Damen</option>
                        <option value="m">Herren</option>
                    </select> 
                </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-jugendklasse" style="width:50px"> </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-email" style="width:150px"> </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-meisterschaft" style="width:100px"> </td>
                <td style="text-align:center"> <input type="text" name="mannschafts-liga"> </td>
                <td style="text-align:center"> <input type="number" name="mannschafts-nuliga-liga-id" style="width:100px"> </td>
                <td style="text-align:center"> <input type="number" name="mannschafts-nuliga-team-id" style="width:100px"> </td>
                <td colspan="2" style="text-align:left"> 
                    <?php
                    settings_fields( 'neue_mannschaft' );
                    do_settings_sections( 'dienste_mannschaft_hinzufuegen' );
                    wp_nonce_field('dienste-mannschaft-hinzufuegen_neu');
                    submit_button('Anlegen', 'primary', 'submit-new', false);
                    ?>
                </td>
            </form></tr>
        </table>
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
    
    if (!empty($_POST['mannschafts-email']) && !filter_var($_POST['mannschafts-email'], FILTER_VALIDATE_EMAIL)) {
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
        'jugendklasse' => $_POST['mannschafts-jugendklasse'],
        'email' => $_POST['mannschafts-email'],
        'meisterschaft' => $_POST['mannschafts-meisterschaft'],
        'liga' => $_POST['mannschafts-liga'],
        'nuliga_liga_id' => $_POST['mannschafts-nuliga-liga-id'],
        'nuliga_team_id' => $_POST['mannschafts-nuliga-team-id']
        ));

    $mannschaftsID = $wpdb->insert_id;
}

function updateMannschaftFrom_POST(){
    global $wpdb;
    
	$table_name = $wpdb->prefix . 'mannschaft';

    $wpdb->update($table_name, array(
        'nummer' => $_POST['mannschafts-nummer'],
        'geschlecht' => $_POST['mannschafts-geschlecht'],
        'jugendklasse' => $_POST['mannschafts-jugendklasse'],
        'email' => $_POST['mannschafts-email'],
        'meisterschaft' => $_POST['mannschafts-meisterschaft'],
        'liga' => $_POST['mannschafts-liga'],
        'nuliga_liga_id' => $_POST['mannschafts-nuliga-liga-id'],
        'nuliga_team_id' => $_POST['mannschafts-nuliga-team-id']
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