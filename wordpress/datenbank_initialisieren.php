<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

global $dienste_db_version;
$dienste_db_version = '1.7';

function dienste_datenbank_initialisieren() {
    global $dienste_db_version;

    $previous_version = get_option('dienste_db_version');
    if($previous_version && $previous_version < '1.6'){
        dienste_mannschaft_aktualisiern();
    }
    
    dienste_mannschaft_initialisieren();
    dienste_meisterschaft_initialisieren();
    dienste_mannschaftsMeldung_initialisieren();
    dienste_gegner_initialisieren();
    dienste_spiele_initialisieren();
    dienste_zuweisungen_initialisieren();

    dienste_nuliga_import_initialisieren();

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
        kuerzel VARCHAR(256) NOT NULL,
        name VARCHAR(1024) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate, ENGINE = InnoDB;";

    $result = dbDelta( $sql );
    $message = var_export($result, true);
    $myFile = fopen("Testdatei.txt", "w");
    fwrite($myFile, $message);
}

function dienste_mannschaftsMeldung_initialisieren(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'mannschaftsMeldung';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        meisterschaft INT NOT NULL,
        mannschaft INT NOT NULL , 
        liga VARCHAR(256) NULL , 
        aktiv TINYINT NOT NULL DEFAULT '1' , 
        nuliga_liga_id INT NULL , 
        nuliga_team_id INT NULL , 
        PRIMARY KEY (id), 
        FOREIGN KEY (meisterschaft) REFERENCES ".$wpdb->prefix."meisterschaft(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft) REFERENCES ".$wpdb->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
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
        mannschaftsmeldung INT NOT NULL,
        spielnr INT NOT NULL , 
        mannschaft INT NOT NULL , 
        gegner INT NOT NULL , 
        heimspiel TINYINT NOT NULL DEFAULT '0' , 
        halle int NOT NULL , 
        anwurf DATETIME NULL , 
        PRIMARY KEY (id), 
        KEY index_anwurf (anwurf),
        FOREIGN KEY (mannschaftsmeldung) REFERENCES ".$wpdb->prefix."mannschaftsmeldung(id) ON DELETE CASCADE ON UPDATE CASCADE,
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

function dienste_nuliga_import_initialisieren(){
    
    dienste_nuliga_import_meisterschaft_initialisieren();
    dienste_nuliga_import_mannschaftseinteilung_initialisieren();
    dienste_nuliga_import_status_initialisieren();
}

function dienste_nuliga_import_meisterschaft_initialisieren(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'nuliga_meisterschaft';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        name VARCHAR(1024) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_nuliga_import_mannschaftseinteilung_initialisieren(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'nuliga_mannschaftseinteilung';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        nuliga_meisterschaft INT NOT NULL,
        mannschaftsBezeichnung VARCHAR(1024) NOT NULL,
        mannschaft INT NULL,
        meisterschaftsKuerzel VARCHAR(256) NOT NULL,
        liga VARCHAR(256) NOT NULL,
        liga_id INT NOT NULL,
        team_id INT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (nuliga_meisterschaft) REFERENCES ".$wpdb->prefix."nuliga_meisterschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_nuliga_import_status_initialisieren(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'import_status';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        schritt VARCHAR(256) NOT NULL,
        start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
        ende DATETIME NULL , 
        PRIMARY KEY (id)
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}
?>