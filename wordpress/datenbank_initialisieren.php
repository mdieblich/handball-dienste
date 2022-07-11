<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__.'/dao/MannschaftDAO.php';
require_once __DIR__.'/dao/GegnerDAO.php';
require_once __DIR__.'/dao/MeisterschaftDAO.php';
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
    $table_name = MannschaftDAO::tableName($dbhandle);
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
    $table_name = MeisterschaftDAO::tableName($dbhandle);
    $charset_collate = $dbhandle->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        kuerzel VARCHAR(256) NOT NULL,
        name VARCHAR(1024) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate, ENGINE = InnoDB;";

    $result = dbDelta( $sql );
}

function dienste_mannschaftsMeldung_initialisieren($dbhandle){
    $table_name = MannschaftsMeldungDAO::tableName($dbhandle);
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        meisterschaft_id INT NOT NULL,
        mannschaft_id INT NOT NULL , 
        liga VARCHAR(256) NULL , 
        aktiv TINYINT NOT NULL DEFAULT '1' , 
        nuligaLigaID INT NULL , 
        nuligaTeamID INT NULL , 
        PRIMARY KEY (id), 
        FOREIGN KEY (meisterschaft_id) REFERENCES ".MeisterschaftDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft_id) REFERENCES ".MannschaftDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_mannschaft_aktualisiern($dbhandle){
    $table_name = MannschaftDAO::tableName($dbhandle);
    $sql = "ALTER TABLE $table_name 
        DROP meisterschaft, 
        DROP liga, 
        DROP nuliga_liga_id, 
        DROP nuliga_team_id";

    $dbhandle->query($sql);
}

function dienste_gegner_initialisieren($dbhandle){
    $table_name = GegnerDAO::tableName($dbhandle);
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
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        mannschaftsmeldung_id INT NOT NULL,
        spielNr INT NOT NULL , 
        mannschaft_id INT NOT NULL , 
        gegner_id INT NOT NULL , 
        heimspiel TINYINT NOT NULL DEFAULT '0' , 
        halle int NOT NULL , 
        anwurf DATETIME NULL , 
        PRIMARY KEY (id), 
        KEY index_anwurf (anwurf),
        FOREIGN KEY (mannschaftsmeldung_id) REFERENCES ".MannschaftsMeldungDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft_id) REFERENCES ".MannschaftDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (gegner_id) REFERENCES ".GegnerDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate, ENGINE = InnoDB;";

    dbDelta( $sql );
}

function dienste_dienste_initialisieren($dbhandle){
    $table_name = DienstDAO::tableName($dbhandle);
    $charset_collate = $dbhandle->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT , 
        spiel_id INT NOT NULL , 
        dienstart VARCHAR(256) NOT NULL , 
        mannschaft_id INT NULL , 
        PRIMARY KEY (id),
        FOREIGN KEY (spiel_id) REFERENCES ".SpielDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (mannschaft_id) REFERENCES ".MannschaftDAO::tableName($dbhandle)."(id) ON DELETE CASCADE ON UPDATE CASCADE
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