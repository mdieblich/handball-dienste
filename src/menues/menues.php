<?php
require_once __DIR__."/mannschaft.php";
require_once __DIR__."/import.php";
require_once __DIR__."/gegner.php";
require_once __DIR__."/zuweisen.php";

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
    include __DIR__."/menues/anleitung.php";
}

?>