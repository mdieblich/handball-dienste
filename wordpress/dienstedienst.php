<?php
 /*
 Plugin Name: Dienstedienst
 Description: Plugin zum Verwalten zusätzlicher Dienste (Zeitnehmer, Catering...) beim Handball
 Version: 1.0
 Author: Martin Dieblich
 Author URI: https://www.turnerkreisnippes.de
 */

add_action('admin_menu', 'addDiensteMenueeintraege');    

function addDiensteMenueeintraege() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    add_menu_page(  'Dienste', 'Dienste', 'administrator', 'dienste', 'displayDiensteDashboard', 'dashicons-schedule' );
    
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    add_submenu_page( 'dienste', 'Dienste - Mannschaften einreichten', 'Mannschaften', 'administrator', 'dienste-mannschaften', 'displayDiensteMannschaften');
    add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
    add_submenu_page( 'dienste', 'Dienste - Einstellungen', 'Einstellungen', 'administrator', 'dienste-einstellungen', 'displayDiensteEinstellungen');
}

function displayDiensteDashboard(){}
function displayDiensteMannschaften(){}
function displayDiensteZuweisen(){}
function displayDiensteEinstellungen(){}
?>