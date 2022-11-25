<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__.'/dao/VereinDAO.php';
require_once __DIR__.'/dao/MannschaftDAO.php';
require_once __DIR__.'/dao/GegnerDAO.php';
require_once __DIR__.'/dao/MeisterschaftDAO.php';
require_once __DIR__.'/dao/MannschaftsMeldungDAO.php';
require_once __DIR__.'/dao/SpielDAO.php';
require_once __DIR__.'/dao/DienstDAO.php';

global $dienste_db_version;
$dienste_db_version = '1.8';

function dienste_datenbank_initialisieren() {
    global $dienste_db_version;
    global $wpdb;

    $previous_version = get_option('dienste_db_version', '0.0');
    
    dienste_vereine_initialisieren($wpdb);
    if($previous_version && $previous_version < '1.7'){
        dienste_vereine_anlegen($wpdb);
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

function dienste_vereine_initialisieren($dbhandle){
    $sql = VereinDAO::tableCreation($dbhandle);
    dbDelta( $sql );
}
function dienste_mannschaft_initialisieren($dbhandle){
    $sql = MannschaftDAO::tableCreation($dbhandle);
    dbDelta( $sql );
}

function dienste_meisterschaft_initialisieren($dbhandle){
    $sql = MeisterschaftDAO::tableCreation($dbhandle);
    $result = dbDelta( $sql );
}

function dienste_mannschaftsMeldung_initialisieren($dbhandle){
    $sql = MannschaftsMeldungDAO::tableCreation($dbhandle);
    dbDelta( $sql );
}

function dienste_gegner_initialisieren($dbhandle){
    $sql = GegnerDAO::tableCreation($dbhandle);
    dbDelta( $sql );
}

function dienste_spiele_initialisieren($dbhandle){
    $sql = SpielDAO::tableCreation($dbhandle);
    dbDelta( $sql );
}

function dienste_dienste_initialisieren($dbhandle){
    $sql = DienstDAO::tableCreation($dbhandle);
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

function dienste_vereine_anlegen($dbhandle){
    dienste_heimverein_anlegen($dbhandle);
    dienste_verein_der_heimmannschaften_setzen($dbhandle);
    // Gegner umziehen
}

function dienste_heimverein_anlegen($dbhandle){
    $vereinDAO = new VereinDAO($dbhandle);
    
    $heimVerein = new Verein();
    $heimVerein->id=0;
    $heimVerein->name=get_option('vereinsname');
    $heimVerein->nuligaClubId=get_option('nuliga-clubid', null);
    
    $vereinDAO->insert($heimVerein);
}
function dienste_mannschaftstabelle_um_verein_erweitern($dbhandle){

    $sql = MannschaftDAO::tableCreation($dbhandle);
    dbDelta( $sql );
    
    $table_name = MannschaftDAO::tableName($dbhandle);
    $sql = "UPDATE $table_name SET verein=0";

    $dbhandle->query($sql);
}
?>