<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

global $dienste_db_version;
$dienste_db_version = '1.0';

function dienste_datenbank_initialisieren() {
	global $dienste_db_version;

	dienste_mannschaft_initialisieren();
    dienste_gegner_initialisieren();
    dienste_spiele_initialisieren();
    dienste_zuweisungen_initialisieren();

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

function dienste_gegner_initialisieren(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'gegner';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        name VARCHAR(256) NOT NULL ,
        geschlecht enum('m','w') NOT NULL, 
        liga VARCHAR(256) NULL , 
        stelltSekretaerBeiHeimspiel TINYINT NOT NULL DEFAULT '0' , 
        PRIMARY KEY (id)
	) $charset_collate, ENGINE = InnoDB;";

	dbDelta( $sql );
}

function dienste_spiele_initialisieren(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'spiel';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        spielnr INT NOT NULL , 
        mannschaft INT NOT NULL , 
        gegner INT NOT NULL , 
        heimspiel TINYINT NOT NULL DEFAULT '0' , 
        halle int NOT NULL , 
        anwurf DATETIME NULL , 
        PRIMARY KEY (id), 
        KEY index_anwurf (anwurf),
        FOREIGN KEY (mannschaft) REFERENCES ".$wpdb->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (gegner) REFERENCES ".$wpdb->prefix."gegner(id) ON DELETE CASCADE ON UPDATE CASCADE
	) $charset_collate, ENGINE = InnoDB;";

	dbDelta( $sql );
}

function dienste_zuweisungen_initialisieren(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'dienst';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        spiel INT NOT NULL , 
        dienstart VARCHAR(256) NOT NULL , 
        mannschaft INT NOT NULL , 
        PRIMARY KEY (id),
        FOREIGN KEY (spiel) REFERENCES ".$wpdb->prefix."spiel(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft) REFERENCES ".$wpdb->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
	) $charset_collate, ENGINE = InnoDB;";

	dbDelta( $sql );
}

?>