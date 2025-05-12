<?php
require_once WP_PLUGIN_DIR."/dienstedienst/menues/mannschaft.php";
require_once WP_PLUGIN_DIR."/dienstedienst/menues/import.php";
require_once WP_PLUGIN_DIR."/dienstedienst/menues/gegner.php";
require_once WP_PLUGIN_DIR."/dienstedienst/menues/zuweisen.php";

function addDiensteMenueeintraege() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    add_menu_page(  'Dienste-Plugin', 'Dienste', 'administrator', 'dienste', 'displayDiensteDashboard', 'dashicons-schedule' );
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    add_submenu_page( 'dienste', 'Anleitung für Dienste-Plugin', 'Anleitung', 'administrator', 'dienste', 'displayDiensteDashboard');
    
    addDiensteMannschaftsKonfiguration();
    addDiensteSpieleImportKonfiguration();
    addDiensteGegnerKonfiguration();
    addDiensteZuweisenKonfiguration();
}

function displayDiensteDashboard(){
    include __DIR__."/anleitung.php";
}

?>