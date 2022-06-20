<?php
 /*
 Plugin Name: Dienstedienst
 Description: Plugin zum Verwalten zusätzlicher Dienste (Zeitnehmer, Catering...) beim Handball
 Version: 1.5
 Author: Martin Dieblich
 Author URI: https://www.turnerkreisnippes.de
 */
require_once plugin_dir_path( __FILE__ ) . 'datenbank_initialisieren.php';
require_once plugin_dir_path( __FILE__ ) . 'einstellungen.php';
require_once plugin_dir_path( __FILE__ ) . 'dienste_anzeigen.php';
require_once plugin_dir_path( __FILE__ ) . 'menues.php';

register_activation_hook( __FILE__, 'dienste_datenbank_initialisieren' );
add_action('admin_init', 'dienste_einstellungen_initialisieren');
add_action('admin_menu', 'addDiensteMenueeintraege');
add_filter('the_content', 'dienst_tabelle_einblenden');

add_action( 'rest_api_init', function () {
    // erreichbar unter /wp-json/dienste/updateFromNuliga
    register_rest_route( 'dienste', '/updateFromNuliga', array(
        'methods' => 'GET',
        'callback' => 'updateFromNuliga',    
        'permission_callback' => '__return_true'
    ));
});

function updateFromNuliga(){
    require_once __DIR__."/import/importer.php";
    $resultMessage = importSpieleFromNuliga();
    return $resultMessage;
}

?>