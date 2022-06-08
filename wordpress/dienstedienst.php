<?php
 /*
 Plugin Name: Dienstedienst
 Description: Plugin zum Verwalten zusätzlicher Dienste (Zeitnehmer, Catering...) beim Handball
 Version: 1.0
 Author: Martin Dieblich
 Author URI: https://www.turnerkreisnippes.de
 */
require_once plugin_dir_path( __FILE__ ) . 'einstellungen.php';
require_once plugin_dir_path( __FILE__ ) . 'menues.php';

add_action('admin_init', 'dienste_einstellungen_initialisieren');
add_action('admin_menu', 'addDiensteMenueeintraege');
register_activation_hook( __FILE__, 'dienste_plugin_installieren' );

global $dienste_db_version;
$dienste_db_version = '1.0';

function dienste_plugin_installieren() {
	global $wpdb;
	global $dienste_db_version;

	$table_name = $wpdb->prefix . 'mannschaft';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        nummer INT NOT NULL , 
        geschlecht enum('m','w') NOT NULL, 
        meisterschaft VARCHAR(256) NULL , 
        liga VARCHAR(256) NULL , 
        nuliga_liga_id INT NULL , 
        nuliga_team_id INT NULL , 
        PRIMARY KEY (id)
	) $charset_collate, ENGINE = InnoDB;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	add_option( 'dienste_db_version', $dienste_db_version );
}
?>