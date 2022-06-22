<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

global $dienste_db_version;
$dienste_db_version = '1.6';

function dienste_datenbank_initialisieren() {
    global $dienste_db_version;

    $previous_version = get_option('dienste_db_version');
    if($previous_version && $previous_version < '1.6'){
        dienste_meisterschaft_initialisieren();
        dienste_migrate_meisterschaft();
        dienste_mannschaft_aktualisiern();
    }
    
    dienste_mannschaft_initialisieren();
    dienste_gegner_initialisieren();
    dienste_spiele_initialisieren();
    dienste_zuweisungen_initialisieren();

    update_option( 'dienste_db_version', $dienste_db_version );
}

function dienste_mannschaft_initialisieren(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'mannschaft';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        nummer INT NOT NULL , 
        geschlecht enum('m','w') NOT NULL, 
        jugendklasse VARCHAR(256) NULL, 
        email VARCHAR(1024) NULL ,
        PRIMARY KEY (id)
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_meisterschaft_initialisieren(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'meisterschaft';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        name VARCHAR(1024) NOT NULL,
        kuerzel VARCHAR(256) NOT NULL,
        mannschaft INT NOT NULL , 
        liga VARCHAR(256) NULL , 
        aktiv TINYINT NOT NULL DEFAULT '1' , 
        nuliga_liga_id INT NULL , 
        nuliga_team_id INT NULL , 
        PRIMARY KEY (id), 
        FOREIGN KEY (mannschaft) REFERENCES ".$wpdb->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_migrate_meisterschaft(){
    global $wpdb;

    $table_name_mannschaft    = $wpdb->prefix . 'mannschaft';
    $table_name_meisterschaft = $wpdb->prefix . 'meisterschaft';
    $sql = "INSERT INTO $table_name_meisterschaft 
        (name, kuerzel, mannschaft, liga, nuliga_liga_id, nuliga_team_id) 
        SELECT meisterschaft, meisterschaft, id, liga, nuliga_liga_id, nuliga_team_id 
        FROM $table_name_mannschaft";

    $wpdb->query($sql);
}

function dienste_mannschaft_aktualisiern(){
    global $wpdb;

    $table_name    = $wpdb->prefix . 'mannschaft';
    $sql = "ALTER TABLE $table_name 
        DROP meisterschaft, 
        DROP liga, 
        DROP nuliga_liga_id, 
        DROP nuliga_team_id";

    $wpdb->query($sql);
}

function dienste_gegner_initialisieren(){
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'gegner';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        verein VARCHAR(256) NOT NULL ,
        nummer INT NOT NULL ,
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