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
    add_submenu_page( 'dienste', 'Dienste - Mannschaften einrichten', 'Mannschaften', 'administrator', 'dienste-mannschaften', 'displayDiensteMannschaften');
    add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
}

function displayDiensteDashboard(){}
function displayDiensteMannschaften(){}
function displayDiensteZuweisen(){}

add_action('admin_init', 'dienste_einstellungen_initialisieren');
function dienste_einstellungen_initialisieren() {
 
    // First, we register a section. This is necessary since all future options must belong to one. 
    add_settings_section(
        'dienste_einstellungen',         // ID used to identify this section and with which to register options
        'Dienste Einstellungen',                  // Title to be displayed on the administration page
        'dienste_einstellungen_beschreibung', // Callback used to render the description of the section
        'general'                           // Page on which to add this section of options
    );
     
    // Next, we will introduce the fields for toggling the visibility of content elements.
    add_settings_field( 
        'vereinsname',                      // ID used to identify the field throughout the theme
        'Vereinsname',                           // The label to the left of the option interface element
        'dienste_einstellungen_vereinsname_anzeigen',   // The name of the function responsible for rendering the option interface
        'general',                          // The page on which this option will be displayed
        'dienste_einstellungen'         // The name of the section to which this field belongs
        
    );
     
    // Finally, we register the fields with WordPress
    register_setting(
        'general',
        'vereinsname'
    );
     
} 
 
function dienste_einstellungen_beschreibung() {
    echo '<p>Einstellungen für das Dienste-Plugin</p>';
}
 
/* ------------------------------------------------------------------------ *
 * Field Callbacks
 * ------------------------------------------------------------------------ */
 
/**
 * This function renders the interface elements for toggling the visibility of the header element.
 * 
 * It accepts an array of arguments and expects the first element in the array to be the description
 * to be displayed next to the checkbox.
 */
function dienste_einstellungen_vereinsname_anzeigen($args) {
     
    // Note the ID and the name attribute of the element match that of the ID in the call to add_settings_field
    $html = '<input type="text" id="vereinsname" name="vereinsname" value="'.get_option('vereinsname').'" />'; 
     
    echo $html;
     
}
?>