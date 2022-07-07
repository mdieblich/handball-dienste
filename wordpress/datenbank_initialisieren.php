<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__.'/dao/MannschaftsMeldungDAO.php';
require_once __DIR__.'/dao/SpielDAO.php';
require_once __DIR__.'/dao/DienstDAO.php';

global $dienste_db_version;
$dienste_db_version = '1.7';

function dienste_datenbank_initialisieren() {
    global $dienste_db_version;
    global $wpdb;

    $previous_version = get_option('dienste_db_version');
    if($previous_version && $previous_version < '1.6'){
        dienste_mannschaft_aktualisiern($wpdb);
    }
    
    dienste_mannschaft_initialisieren($wpdb);
    dienste_meisterschaft_initialisieren($wpdb);
    dienste_mannschaftsMeldung_initialisieren($wpdb);
    dienste_gegner_initialisieren($wpdb);
    dienste_spiele_initialisieren($wpdb);
    dienste_dienste_initialisieren($wpdb);

    dienste_nuliga_import_initialisieren($wpdb);

    update_option( 'dienste_db_version', $dienste_db_version );
}

function dienste_mannschaft_initialisieren($dbhandle){
    $table_name = $dbhandle->prefix . 'mannschaft';
    $charset_collate = $dbhandle->get_charset_collate();

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

function dienste_meisterschaft_initialisieren($dbhandle){
    $table_name = $dbhandle->prefix . 'meisterschaft';
    $charset_collate = $dbhandle->get_charset_collate();
    
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

function dienste_mannschaftsMeldung_initialisieren($dbhandle){
    $table_name = MannschaftsMeldungDAO::tableName($dbhandle);
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        meisterschaft INT NOT NULL,
        mannschaft INT NOT NULL , 
        liga VARCHAR(256) NULL , 
        aktiv TINYINT NOT NULL DEFAULT '1' , 
        nuliga_liga_id INT NULL , 
        nuliga_team_id INT NULL , 
        PRIMARY KEY (id), 
        FOREIGN KEY (meisterschaft) REFERENCES ".$dbhandle->prefix."meisterschaft(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft) REFERENCES ".$dbhandle->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_mannschaft_aktualisiern($dbhandle){
    $table_name    = $dbhandle->prefix . 'mannschaft';
    $sql = "ALTER TABLE $table_name 
        DROP meisterschaft, 
        DROP liga, 
        DROP nuliga_liga_id, 
        DROP nuliga_team_id";

    $dbhandle->query($sql);
}

function dienste_gegner_initialisieren($dbhandle){
    $table_name = $dbhandle->prefix . 'gegner';
    $charset_collate = $dbhandle->get_charset_collate();
    
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

function dienste_spiele_initialisieren($dbhandle){
    $table_name = SpielDAO::tableName($dbhandle);
    $table_name_meldung = MannschaftsMeldungDAO::tableName($dbhandle);
    $charset_collate = $dbhandle->get_charset_collate();

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
        FOREIGN KEY (mannschaftsmeldung) REFERENCES $table_name_meldung(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft) REFERENCES ".$dbhandle->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (gegner) REFERENCES ".$dbhandle->prefix."gegner(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_dienste_initialisieren($dbhandle){
    $table_name = DienstDAO::tableName($dbhandle);
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        spiel INT NOT NULL , 
        dienstart VARCHAR(256) NOT NULL , 
        mannschaft INT NULL , 
        PRIMARY KEY (id),
        FOREIGN KEY (spiel) REFERENCES ".SpielDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft) REFERENCES ".$dbhandle->prefix."mannschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_nuliga_import_initialisieren($dbhandle){
    dienste_nuliga_import_meisterschaft_initialisieren($dbhandle);
    dienste_nuliga_import_mannschaftseinteilung_initialisieren($dbhandle);
    dienste_nuliga_import_status_initialisieren($dbhandle);
}

function dienste_nuliga_import_meisterschaft_initialisieren($dbhandle){
    $table_name = $dbhandle->prefix . 'nuliga_meisterschaft';
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        name VARCHAR(1024) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_nuliga_import_mannschaftseinteilung_initialisieren($dbhandle){
    $table_name = $dbhandle->prefix . 'nuliga_mannschaftseinteilung';
    $charset_collate = $dbhandle->get_charset_collate();

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
        FOREIGN KEY (nuliga_meisterschaft) REFERENCES ".$dbhandle->prefix."nuliga_meisterschaft(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_nuliga_import_status_initialisieren($dbhandle){
    $table_name = $dbhandle->prefix . 'import_status';
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        schritt INT NOT NULL,
        beschreibung VARCHAR(1024) NOT NULL,
        start DATETIME NULL DEFAULT CURRENT_TIMESTAMP, 
        ende DATETIME NULL , 
        PRIMARY KEY (schritt)
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}
?>