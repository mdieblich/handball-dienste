<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'register_settings.php';
deleteAllOptions();

require_once plugin_dir_path( __FILE__ ) . 'db/datenbank_cleanup.php';
dienste_datenbank_cleanup($wpdb);
?>