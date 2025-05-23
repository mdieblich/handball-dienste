<?php
require_once __DIR__."/../../handball/Mannschaft.php";   // Für GESCHLECHT_W und GESCHLECHT_M
require_once __DIR__."/../../handball/dienst/DienstAenderungsPlan.php";
require_once __DIR__."/../../db/service/GegnerService.php";
require_once __DIR__."/../../db/dao/SpielDAO.php";
require_once __DIR__."/../../db/dao/DienstDAO.php";

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
    
    $gegnerService = new GegnerService();
    $alleGegner = $gegnerService->loadAktiveGegner();

    ?>
    <div class="wrap">
        <h1>Gegner einrichten</h1>
        Hier kann eingestellt werden, ob die gegnerische Mannschaft bei ihren Spielen einen Sekretär stellt.<br>
        Dies bedeutet, dass bei Auswärtsspielen kein Sekretär-Dienst übernommen werden muss, aber bei Heimspielen der Sekretär-Dienst zusätzlich übernommen werden muss.<br>
        <b><i>Wichtig:</i></b> Werden hier Einstellungen geändert, kann es dazu kommen, dass bereits zugewiesene Dienste entfallen (<i>d.h. gelöscht werden</i>). Dann wird eine Email an die entsprechende Mannschaft versendet.
        <form action="<?php menu_page_url( 'dienste-gegner' ) ?>" method="post">
        <?php
            settings_fields( 'gegner_aendern' );
            do_settings_sections( 'dienste_gegner_aendern' );
            wp_nonce_field('dienste-gegner-aendern_alle');
            submit_button( 'Speichern', 'primary' , 'submit-change', false);
        ?>
        <table cellpadding="5">
            <tr>
                <th> Name </th>
                <th> w/m </th>
                <th> Meisterschaft </th>
                <th> Liga </th>
                <th> Stellt Sekretär </th>
            </tr>
        <?php foreach($alleGegner as $gegner){ ?>
            <tr>
                <td> <?php echo $gegner->getDisplayName(); ?> </td>
                <td style="text-align:center"> 
                    <select name="gegner-geschlecht" disabled> 
                        <option value="w" <?php if($gegner->getGeschlecht()==GESCHLECHT_W) echo "selected"; ?>>Damen</option>
                        <option value="m" <?php if($gegner->getGeschlecht()==GESCHLECHT_M) echo "selected"; ?>>Herren</option>
                    </select> 
                </td>
                <td style="text-align:center"> <?php echo $gegner->getMeisterschaft(); ?> </td>
                <td style="text-align:center"> <?php echo $gegner->getLiga(); ?> </td>
                <td style="text-align:center"> <input type="checkbox" name="gegner-id[]" value="<?php echo $gegner->id; ?>" <?php if($gegner->stelltSekretaerBeiHeimspiel){echo "checked";} ?>></td>
            </tr> 
        <?php } ?>
        </table>
        <small>* stellt Sekretär bei Heimspielen</small><br>
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
    $gegner_table_name = GegnerDAO::tableName($wpdb);
    $gegnerDAO = new GegnerDAO($wpdb);

    if(isset($_POST['gegner-id'])){
        $zuAenderndeGegner_idListe = "(" . implode(",", $_POST['gegner-id']) . ")";
        $zuAenderndeGegner_filter = "stelltSekretaerBeiHeimspiel != (id in $zuAenderndeGegner_idListe)";
        $gegnerUpdate = "stelltSekretaerBeiHeimspiel = (id in $zuAenderndeGegner_idListe)";
    } else {
        $zuAenderndeGegner_filter = "stelltSekretaerBeiHeimspiel = 1";
        $gegnerUpdate = "stelltSekretaerBeiHeimspiel = 0";
    }
    $zuAenderndeGegner = $gegnerDAO->fetchAll($zuAenderndeGegner_filter);

    // Gegner aktualisieren
    $wpdb->query("UPDATE $gegner_table_name SET $gegnerUpdate");

    // Dienste aktualisieren
    $gegnerDieAbJetztSekretaerStellen = array();
    $gegnerDieNichtMehrSekretaerStellen = array();
    foreach($zuAenderndeGegner as $gegner){
        if($gegner->stelltSekretaerBeiHeimspiel){
            $gegnerDieNichtMehrSekretaerStellen[$gegner->id] = $gegner;
        } else {
            $gegnerDieAbJetztSekretaerStellen[$gegner->id] = $gegner;
        }
    }

    updateDiensteFuerGegner($wpdb, $gegnerDieAbJetztSekretaerStellen, $gegnerDieNichtMehrSekretaerStellen);
}

function updateDiensteFuerGegner($wpdb, $gegnerDieAbJetztSekretaerStellen, $gegnerDieNichtMehrSekretaerStellen){
    createNewDienste($wpdb, $gegnerDieAbJetztSekretaerStellen, $gegnerDieNichtMehrSekretaerStellen);
    deleteNeedlessDienste($wpdb, $gegnerDieAbJetztSekretaerStellen, $gegnerDieNichtMehrSekretaerStellen);
}

function createNewDienste($wpdb, $gegnerDieAbJetztSekretaerStellen, $gegnerDieNichtMehrSekretaerStellen){
    if(!empty($gegnerDieAbJetztSekretaerStellen)){
        $heimSpieleDieDiensteBrauchen = "heimspiel=1 AND gegner_id in (".implode(",", array_keys($gegnerDieAbJetztSekretaerStellen)).")";
    } else {
        $heimSpieleDieDiensteBrauchen = "false";
    }
    
    if(!empty($gegnerDieNichtMehrSekretaerStellen)){
        $auswaertsSpieleDieDiensteBrauchen = "heimspiel=0 AND gegner_id in (".implode(",", array_keys($gegnerDieNichtMehrSekretaerStellen)).")";
    } else {
        $auswaertsSpieleDieDiensteBrauchen = "false";
    }
    
    $filterFuerSpieleDieDiensteBrauchen = "($heimSpieleDieDiensteBrauchen) OR ($auswaertsSpieleDieDiensteBrauchen)";

    $spielDAO = new SpielDAO($wpdb);
    $spieleDieDiensteBrauchen = $spielDAO->loadSpiele("($filterFuerSpieleDieDiensteBrauchen) AND anwurf > CURRENT_TIMESTAMP");
    
    if($spieleDieDiensteBrauchen->hasEntries()){
        $dienste_table_name = DienstDAO::tableName($wpdb);
        $insertDienste = "INSERT INTO $dienste_table_name (spiel_id, dienstart) VALUES (".implode(", '".Dienstart::SEKRETAER."'),(", $spieleDieDiensteBrauchen->getIDs()).", '".Dienstart::SEKRETAER."')";
        $wpdb->query($insertDienste);
    }
}

function deleteNeedlessDienste($wpdb, $gegnerDieAbJetztSekretaerStellen, $gegnerDieNichtMehrSekretaerStellen){
    if(!empty($gegnerDieAbJetztSekretaerStellen)){
        $auswaertsSpieleDieKeineDiensteMehrBrauchen = "heimspiel=0 AND gegner_id in (".implode(",", array_keys($gegnerDieAbJetztSekretaerStellen)).")";
    } else {
        $auswaertsSpieleDieKeineDiensteMehrBrauchen = "false";
    }
    
    if(!empty($gegnerDieNichtMehrSekretaerStellen)){
        $heimSpieleDieKeineDiensteMehrBrauchen = "heimspiel=1 AND gegner_id in (".implode(",", array_keys($gegnerDieNichtMehrSekretaerStellen)).")";
    } else {
        $heimSpieleDieKeineDiensteMehrBrauchen = "false";
    }

    $filterFuerSpieleDieKeineDiensteMehrBrauchen = "($heimSpieleDieKeineDiensteMehrBrauchen) OR ($auswaertsSpieleDieKeineDiensteMehrBrauchen)";

    $spielService = new SpielService($wpdb);
    $spieleDieKeineDiensteMehrBrauchen = $spielService->loadSpieleMitDiensten("($filterFuerSpieleDieKeineDiensteMehrBrauchen) AND anwurf > CURRENT_TIMESTAMP");
    
    $zuLoeschendeDienste = $spieleDieKeineDiensteMehrBrauchen->getDienste(Dienstart::SEKRETAER);
    
    if(count($zuLoeschendeDienste)>0){
        // Email-Benachrichtigungen!
        $dienstAenderungsplan = new DienstAenderungsPlan($spieleDieKeineDiensteMehrBrauchen->getMannschaften());
        $dienstAenderungsplan->registerEntfalleneDienste($zuLoeschendeDienste);
        $dienstAenderungsplan->sendEmails();
        
        // Löschen
        $dienste_table_name = DienstDAO::tableName($wpdb);
        $deleteDienste = "DELETE FROM $dienste_table_name WHERE id IN (".implode(",", array_keys($zuLoeschendeDienste)).")";
        $wpdb->query($deleteDienste);
        
    }
}

?>