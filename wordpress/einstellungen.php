<?php
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
    add_settings_field( 
        'bot-smtp',                      // ID used to identify the field throughout the theme
        'Bot-SMTP-Server',                           // The label to the left of the option interface element
        'dienste_einstellungen_botsmtp_anzeigen',   // The name of the function responsible for rendering the option interface
        'general',                          // The page on which this option will be displayed
        'dienste_einstellungen'         // The name of the section to which this field belongs
        
    );
    add_settings_field( 
        'bot-email',                      // ID used to identify the field throughout the theme
        'Bot-Email',                           // The label to the left of the option interface element
        'dienste_einstellungen_botemail_anzeigen',   // The name of the function responsible for rendering the option interface
        'general',                          // The page on which this option will be displayed
        'dienste_einstellungen'         // The name of the section to which this field belongs
        
    );
    add_settings_field( 
        'bot-passwort',                      // ID used to identify the field throughout the theme
        'Bot-Passwort',                           // The label to the left of the option interface element
        'dienste_einstellungen_botpasswort_anzeigen',   // The name of the function responsible for rendering the option interface
        'general',                          // The page on which this option will be displayed
        'dienste_einstellungen'         // The name of the section to which this field belongs
        
    );
     
    // Finally, we register the fields with WordPress
    register_setting(
        'general',
        'vereinsname'
    );

    register_setting(
        'general',
        'bot-smtp'
    );

    register_setting(
        'general',
        'bot-email'
    );
    
    register_setting(
        'general',
        'bot-passwort'
    );
     
} 
 
function dienste_einstellungen_beschreibung() {
    echo '<p>Einstellungen für das Dienste-Plugin</p>';
}
 
function dienste_einstellungen_vereinsname_anzeigen($args) {
    echo '<input type="text" id="vereinsname" name="vereinsname" value="'.get_option('vereinsname').'" />';
}
function dienste_einstellungen_botsmtp_anzeigen($args) {
    echo '<input type="text" id="bot-smtp" name="bot-smtp" value="'.get_option('bot-smtp').'" />';
}
function dienste_einstellungen_botemail_anzeigen($args) {
    echo '<input type="text" id="bot-email" name="bot-email" value="'.get_option('bot-email').'" />';
}
function dienste_einstellungen_botpasswort_anzeigen($args) {
    echo '<input type="text" id="bot-passwort" name="bot-passwort" value="'.get_option('bot-passwort').'" />';
}
?>