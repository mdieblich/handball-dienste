<?php
require_once WP_PLUGIN_DIR."/dienstedienst/menue_mannschaft.php";
require_once WP_PLUGIN_DIR."/dienstedienst/menue_import.php";

function addDiensteMenueeintraege() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    add_menu_page(  'Dienste', 'Dienste', 'administrator', 'dienste', 'displayDiensteDashboard', 'dashicons-schedule' );
    
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    addDiensteMannschaftsKonfiguration();
    addDiensteSpieleImportKonfiguration();
    add_submenu_page( 'dienste', 'Dienste - Gegner einrichten', 'Gegner', 'administrator', 'dienste-gegner', 'displayDiensteGegner');
    add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
}


function displayDiensteDashboard(){}
function displayDiensteGegner(){}
function displayDiensteZuweisen(){}

?>