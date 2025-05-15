<?php
 /*
 Plugin Name: Dienstedienst
 Description: Plugin zum Verwalten zusätzlicher Dienste (Zeitnehmer, Catering...) beim Handball
 Version: 1.19.22
 Author: Martin Fritz
 Author URI: https://www.turnerkreisnippes.de
 */
require_once plugin_dir_path( __FILE__ ) . 'db/datenbank_initialisieren.php';
require_once plugin_dir_path( __FILE__ ) . 'register_settings.php';
require_once plugin_dir_path( __FILE__ ) . 'dienste_anzeigen.php';
require_once plugin_dir_path( __FILE__ ) . '/ui/menues/menues.php';

register_activation_hook( __FILE__, 'dienste_datenbank_initialisieren' );
add_action('admin_init', 'register_settings');
add_action('admin_menu', 'addDiensteMenueeintraege');
add_filter('the_content', 'dienst_tabelle_einblenden');

// add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );

add_action( 'rest_api_init', function () {
    // erreichbar unter /wp-json/dienste/updateFromNuliga
    register_rest_route( 'dienste', '/updateFromNuliga', array(
        'methods' => 'GET',
        'callback' => 'updateFromNuliga',    
        'permission_callback' => '__return_true'
    ));
    // erreichbar unter /wp-json/dienste/spielerPlusExport
    register_rest_route( 'dienste', '/spielerPlusExport', array(
        'methods' => 'GET',
        'callback' => 'downloadSpielerPlusExport',    
        'permission_callback' => '__return_true'
    ));
});

function enqueue_scripts() {
    wp_enqueue_style( 'bootstrap', plugin_dir_url(__FILE__).'ui/bootstrap/bootstrap.min.css', array(), '5.2.0beta');
    wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__).'ui/bootstrap/bootstrap.min.js' , array( 'jquery' ), '5.2.0beta', true);
}

function updateFromNuliga(): WP_REST_Response{
    require_once __DIR__."/io/nuligaImport/importer.php";

    $problems = Importer::$SPIELE_IMPORTIEREN->run();
    $response = new WP_REST_Response( $problems );
    $response->set_status( 200 );
    if(!empty($problems)){
        $response->set_status( 500 );
    }
    return $response;
}

function downloadSpielerPlusExport(){
    require_once __DIR__."/io/spielerplusExport/exporter.php";
    exportSpielerPlus( $_GET['mannschaft'] );
}
?>