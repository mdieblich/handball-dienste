<?php
 /*
 Plugin Name: Dienstedienst
 Description: Plugin zum Verwalten zusätzlicher Dienste (Zeitnehmer, Catering...) beim Handball
 Version: 1.2
 Author: Martin Dieblich
 Author URI: https://www.turnerkreisnippes.de
 */
require_once plugin_dir_path( __FILE__ ) . 'datenbank_initialisieren.php';
require_once plugin_dir_path( __FILE__ ) . 'wordpress_konfigurieren.php';
require_once plugin_dir_path( __FILE__ ) . 'einstellungen.php';
require_once plugin_dir_path( __FILE__ ) . 'menues.php';

register_activation_hook( __FILE__, 'dienste_datenbank_initialisieren' );
register_activation_hook( __FILE__, 'dienste_add_posttype_dienst' );
add_action('admin_init', 'dienste_einstellungen_initialisieren');
add_action('admin_menu', 'addDiensteMenueeintraege');
?>