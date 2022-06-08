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

add_action('admin_init', 'sandbox_initialize_theme_options');
function sandbox_initialize_theme_options() {
 
    // First, we register a section. This is necessary since all future options must belong to one. 
    add_settings_section(
        'general_settings_section',         // ID used to identify this section and with which to register options
        'Sandbox Options',                  // Title to be displayed on the administration page
        'sandbox_general_options_callback', // Callback used to render the description of the section
        'general'                           // Page on which to add this section of options
    );
     
    // Next, we will introduce the fields for toggling the visibility of content elements.
    add_settings_field( 
        'show_header',                      // ID used to identify the field throughout the theme
        'Header',                           // The label to the left of the option interface element
        'sandbox_toggle_header_callback',   // The name of the function responsible for rendering the option interface
        'general',                          // The page on which this option will be displayed
        'general_settings_section',         // The name of the section to which this field belongs
        array(                              // The array of arguments to pass to the callback. In this case, just a description.
            'Activate this setting to display the header.'
        )
    );
     
    add_settings_field( 
        'show_content',                     
        'Content',              
        'sandbox_toggle_content_callback',  
        'general',                          
        'general_settings_section',         
        array(                              
            'Activate this setting to display the content.'
        )
    );
     
    add_settings_field( 
        'show_footer',                      
        'Footer',               
        'sandbox_toggle_footer_callback',   
        'general',                          
        'general_settings_section',         
        array(                              
            'Activate this setting to display the footer.'
        )
    );
     
    // Finally, we register the fields with WordPress
    register_setting(
        'general',
        'show_header'
    );
     
    register_setting(
        'general',
        'show_content'
    );
     
    register_setting(
        'general',
        'show_footer'
    );
     
} // end sandbox_initialize_theme_options
 
/* ------------------------------------------------------------------------ *
 * Section Callbacks
 * ------------------------------------------------------------------------ */
 
/**
 * This function provides a simple description for the General Options page. 
 *
 * It is called from the 'sandbox_initialize_theme_options' function by being passed as a parameter
 * in the add_settings_section function.
 */
function sandbox_general_options_callback() {
    echo '<p>Select which areas of content you wish to display.</p>';
} // end sandbox_general_options_callback
 
/* ------------------------------------------------------------------------ *
 * Field Callbacks
 * ------------------------------------------------------------------------ */
 
/**
 * This function renders the interface elements for toggling the visibility of the header element.
 * 
 * It accepts an array of arguments and expects the first element in the array to be the description
 * to be displayed next to the checkbox.
 */
function sandbox_toggle_header_callback($args) {
     
    // Note the ID and the name attribute of the element match that of the ID in the call to add_settings_field
    $html = '<input type="checkbox" id="show_header" name="show_header" value="1" ' . checked(1, get_option('show_header'), false) . '/>'; 
     
    // Here, we will take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="show_header"> '  . $args[0] . '</label>'; 
     
    echo $html;
     
} // end sandbox_toggle_header_callback
 
function sandbox_toggle_content_callback($args) {
 
    $html = '<input type="checkbox" id="show_content" name="show_content" value="1" ' . checked(1, get_option('show_content'), false) . '/>'; 
    $html .= '<label for="show_content"> '  . $args[0] . '</label>'; 
     
    echo $html;
     
} // end sandbox_toggle_content_callback
 
function sandbox_toggle_footer_callback($args) {
     
    $html = '<input type="checkbox" id="show_footer" name="show_footer" value="1" ' . checked(1, get_option('show_footer'), false) . '/>'; 
    $html .= '<label for="show_footer"> '  . $args[0] . '</label>'; 
     
    echo $html;
     
} // end sandbox_toggl
?>