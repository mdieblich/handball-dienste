<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

global $dienste_db_version;
$dienste_db_version = '1.0';

function dienste_datenbank_initialisieren() {
	global $dienste_db_version;

	dienste_mannschaft_initialisieren();

	add_option( 'dienste_db_version', $dienste_db_version );
}

function dienste_mannschaft_initialisieren(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'mannschaft';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        nummer INT NOT NULL , 
        geschlecht enum('m','w') NOT NULL, 
        meisterschaft VARCHAR(256) NULL , 
        liga VARCHAR(256) NULL , 
        nuliga_liga_id INT NULL , 
        nuliga_team_id INT NULL , 
        PRIMARY KEY (id)
	) $charset_collate, ENGINE = InnoDB;";

	dbDelta( $sql );
}

?>