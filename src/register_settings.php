<?php

function register_settings(){
    register_settings_import();
    register_settings_email();
}

function register_settings_import(){
    register_setting('dienste-settings', 'vereinsname');
    register_setting('dienste-settings', 'nuliga-clubid');

    add_settings_section(
        'dienste_import',         // ID used to identify this section and with which to register options
        'Import-Einstellungen',                  // Title to be displayed on the administration page
        'dienste_settings_import_callback', // Callback used to render the description of the section
        'dienste-settings'                           // Page on which to add this section of options
    );

    // Next, we will introduce the fields for toggling the visibility of content elements.
    add_settings_field( 
        'vereinsname',                      // ID used to identify the field throughout the theme
        'Vereinsname',                           // The label to the left of the option interface element
        'dienste_settings_import_vereinsname_anzeigen',   // The name of the function responsible for rendering the option interface
        'dienste-settings',                          // The page on which this option will be displayed
        'dienste_import'         // The name of the section to which this field belongs
        
    );
     
    // Next, we will introduce the fields for toggling the visibility of content elements.
    add_settings_field( 
        'nuliga-clubid',                      // ID used to identify the field throughout the theme
        'Nuliga-Vereins-ID',                           // The label to the left of the option interface element
        'dienste_settings_import_nuligaclubid_anzeigen',   // The name of the function responsible for rendering the option interface
        'dienste-settings',                          // The page on which this option will be displayed
        'dienste_import'         // The name of the section to which this field belongs
        
    );
    
}
function register_settings_email(){
    add_settings_section(
        'dienste_email',         // ID used to identify this section and with which to register options
        'Email-Einstellungen',                  // Title to be displayed on the administration page
        'dienste_settings_email_callback', // Callback used to render the description of the section
        'dienste-settings'                           // Page on which to add this section of options
    );
    register_setting('dienste-settings', 'bot-smtp');
    register_setting('dienste-settings', 'bot-email');
    register_setting('dienste-settings', 'bot-passwort');
    
    add_settings_field( 
        'bot-smtp',                      // ID used to identify the field throughout the theme
        'Bot-SMTP-Server',                           // The label to the left of the option interface element
        'dienste_settings_email_botsmtp_anzeigen',   // The name of the function responsible for rendering the option interface
        'dienste-settings',                          // The page on which this option will be displayed
        'dienste_email'         // The name of the section to which this field belongs
        
    );
    add_settings_field( 
        'bot-email',                      // ID used to identify the field throughout the theme
        'Bot-Email',                           // The label to the left of the option interface element
        'dienste_settings_email_botemail_anzeigen',   // The name of the function responsible for rendering the option interface
        'dienste-settings',                          // The page on which this option will be displayed
        'dienste_email'         // The name of the section to which this field belongs
        
    );
    add_settings_field( 
        'bot-passwort',                      // ID used to identify the field throughout the theme
        'Bot-Passwort',                           // The label to the left of the option interface element
        'dienste_settings_email_botpasswort_anzeigen',   // The name of the function responsible for rendering the option interface
        'dienste-settings',                          // The page on which this option will be displayed
        'dienste_email'         // The name of the section to which this field belongs
    );

} 
 
function dienste_settings_import_callback() {
    echo '<p>Einstellungen für das Importieren von nuLiga</p>';
}
function dienste_settings_email_callback() {
    echo '<p>Einstellungen für das Versenden von Emails mit Spiel-Updates (Verschiebungen / Dienste)</p>';
}
 
function dienste_settings_import_vereinsname_anzeigen($args) {
    echo '<input type="text" id="vereinsname" name="vereinsname" value="'.get_option('vereinsname').'" />';
}
function dienste_settings_import_nuligaclubid_anzeigen($args) {
    echo '<input type="text" id="nuliga-clubid" name="nuliga-clubid" value="'.get_option('nuliga-clubid').'" />';
}
function dienste_settings_email_botsmtp_anzeigen($args) {
    echo '<input type="text" id="bot-smtp" name="bot-smtp" value="'.get_option('bot-smtp').'" />';
}
function dienste_settings_email_botemail_anzeigen($args) {
    echo '<input type="text" id="bot-email" name="bot-email" value="'.get_option('bot-email').'" />';
}
function dienste_settings_email_botpasswort_anzeigen($args) {
    echo '<input type="text" id="bot-passwort" name="bot-passwort" value="'.get_option('bot-passwort').'" />';
}
?>