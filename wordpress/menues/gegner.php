<?php
require_once __DIR__."/../dao/gegner.php";
require_once __DIR__."/../entity/mannschaft.php";   // Für GESCHLECHT_W und GESCHLECHT_M

function addDiensteGegnerKonfiguration(){
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    $hook_gegner = add_submenu_page( 'dienste', 'Dienste - Gegner einrichten', 'Gegner', 'administrator', 'dienste-gegner', 'displayDiensteGegner');

    add_settings_section(
        'dienste_gegner_aendern',
        'Gegner ändern',
        'dummy_function',
        'dienste-gegner'
    );

    add_settings_field( 
        'gegner_aendern',                      // ID used to identify the field throughout the theme
        '',                           // The label to the left of the option interface element
        'dummy_function',   // The name of the function responsible for rendering the option interface
        'dienste-gegner',                          // The page on which this option will be displayed
        'dienste_gegner_aendern'         // The name of the section to which this field belongs
        
    );
    register_setting('dienste', 'gegner_aendern');

    add_action( 'load-' . $hook_gegner, 'diensteGegnerSubmit' );
}

function displayDiensteGegner(){
    
    $gegnerDAO = new GegnerDAO();
    $gegnerDAO->loadGegner();

    ?>
    <div class="wrap">
        <h1>Gegner einrichten</h1>
        Hier kann eingestellt werden, ob die gegnerische Mannschaft bei ihren Spielen einen Sekretär stellt.<br>
        Dies bedeutet, dass bei Auswärtsspielen kein Sekretär-Dienst übernommen werden muss, aber bei Heimspielen der Sekretär-Dienst zusätzlich übernommen werden muss.
        <form action="<?php menu_page_url( 'dienste-gegner' ) ?>" method="post">
        <?php
            settings_fields( 'gegner_aendern' );
            do_settings_sections( 'dienste_gegner_aendern' );
            wp_nonce_field('dienste-gegner-aendern_alle');
            submit_button( 'Speichern', 'primary' , 'submit-change', false);
        ?>
        <table cellspacing="1">
            <tr>
                <th> Name </th>
                <th> w/m </th>
                <th> Liga </th>
                <th> Stellt Sekretär </th>
            </tr>
        <?php foreach($gegnerDAO->getAlleGegner() as $gegner){ ?>
            <tr>
                <td> <?php echo $gegner->getName(); ?> </td>
                <td style="text-align:center"> 
                    <select name="gegner-geschlecht" disabled> 
                        <option value="w" <?php if($gegner->getGeschlecht()==GESCHLECHT_W) echo "selected"; ?>>Damen</option>
                        <option value="m" <?php if($gegner->getGeschlecht()==GESCHLECHT_M) echo "selected"; ?>>Herren</option>
                    </select> 
                </td>
                <td style="text-align:center"> <?php echo $gegner->getLiga(); ?> </td>
                <td style="text-align:center"> <input type="checkbox" name="gegner-id[]" value="<?php echo $gegner->getID(); ?>" <?php if($gegner->stelltSekretearBeiHeimspiel()){echo "checked";} ?>></td>
            </tr> 
        <?php } ?>
        </table>
        <?php submit_button( 'Speichern', 'primary' , 'submit-change', false); ?>    
        </form>
    </div>
    <?php
}

function diensteGegnerSubmit(){
    // TODO check auf korrekte Berechtigung
    // 1. Berechtigung anlegen https://wordpress.org/support/article/roles-and-capabilities/
    // 2. hier prüfen mit: if ( current_user_can( 'edit_others_posts' ) ) {
    // siehe auch https://developer.wordpress.org/plugins/security/checking-user-capabilities/#restricted-to-a-specific-capability
    if('POST' !== $_SERVER['REQUEST_METHOD']){
        return;
    }

    switch($_POST['option_page']){
        case 'gegner_aendern': handleGegnerAendern(); break;
    }
}

function handleGegnerAendern(){
    if(!check_admin_referer('dienste-gegner-aendern_alle')){
        return;
    }
    updateGegnerFrom_POST();
}

function updateGegnerFrom_POST(){
    global $wpdb;
    
	$table_name = $wpdb->prefix . 'gegner';

    $sql = "UPDATE $table_name SET stelltSekretaerBeiHeimspiel=(id in (".implode(",", $_POST['gegner-id'])."))";
    $result = $wpdb->query($sql);
}

?>