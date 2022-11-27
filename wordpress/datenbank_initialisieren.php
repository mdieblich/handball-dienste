<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__.'/dao/VereinDAO.php';
require_once __DIR__.'/dao/MannschaftDAO.php';
require_once __DIR__.'/dao/GegnerDAO.php';
require_once __DIR__.'/dao/MeisterschaftDAO.php';
require_once __DIR__.'/dao/MannschaftsMeldungDAO.php';
require_once __DIR__.'/dao/SpielDAO.php';
require_once __DIR__.'/dao/DienstDAO.php';
require_once __DIR__.'/service/GegnerService.php';

global $dienste_db_version;
$dienste_db_version = '1.8';

function dienste_datenbank_initialisieren() {
    global $dienste_db_version;
    global $wpdb;

    $previous_version = get_option('dienste_db_version', '0.0');
    if($previous_version === '0.0'){
        dienste_datenbank_neu($wpdb);
    } else if($previous_version && $previous_version < '1.8'){
        dienste_vereine_nachruesten($wpdb);
    }

    update_option( 'dienste_db_version', $dienste_db_version );
}

function dienste_datenbank_neu($dbhandle){
    dienste_vereine_initialisieren($dbhandle);
    dienste_mannschaft_initialisieren($dbhandle);
    dienste_meisterschaft_initialisieren($dbhandle);
    dienste_mannschaftsMeldung_initialisieren($dbhandle);
    dienste_gegner_initialisieren($dbhandle);
    dienste_spiele_initialisieren($dbhandle);
    dienste_dienste_initialisieren($dbhandle);

    dienste_nuliga_import_initialisieren($dbhandle);

    dienste_heimverein_anlegen($dbhandle);
}

function dienste_vereine_initialisieren($dbhandle){
    $sql = VereinDAO::tableCreation($dbhandle);
    $dbhandle->query($sql);
}
function dienste_mannschaft_initialisieren($dbhandle){
    $sql = MannschaftDAO::tableCreation($dbhandle);
    $dbhandle->query( $sql );
}

function dienste_meisterschaft_initialisieren($dbhandle){
    $sql = MeisterschaftDAO::tableCreation($dbhandle);
    $dbhandle->query( $sql );
}

function dienste_mannschaftsMeldung_initialisieren($dbhandle){
    $sql = MannschaftsMeldungDAO::tableCreation($dbhandle);
    $dbhandle->query( $sql );
}

function dienste_gegner_initialisieren($dbhandle){
    $sql = GegnerDAO::tableCreation($dbhandle);
    $dbhandle->query( $sql );
}

function dienste_spiele_initialisieren($dbhandle){
    $sql = SpielDAO::tableCreation($dbhandle);
    $dbhandle->query( $sql );
}

function dienste_dienste_initialisieren($dbhandle){
    $sql = DienstDAO::tableCreation($dbhandle);
    $dbhandle->query( $sql );
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

    $dbhandle->query( $sql );
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

    $dbhandle->query( $sql );
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

    $dbhandle->query( $sql );
}

function dienste_vereine_nachruesten($dbhandle){
    dienste_vereine_initialisieren($dbhandle);
    dienste_heimverein_anlegen($dbhandle);

    dienste_mannschaft_verein_ergaenzen($dbhandle);
    dienste_verein_der_heimmannschaften_setzen($dbhandle);
    dienste_mannschaft_verein_foreign_key_ergaenzen($dbhandle);

    dienste_gegner_in_mannschaften_importieren($dbhandle);
    // Gegner löschen
}

function dienste_heimverein_anlegen($dbhandle){
    $vereinDAO = new VereinDAO($dbhandle);
    
    $heimVerein = new Verein();
    $heimVerein->id=1;
    $heimVerein->name=get_option('vereinsname');
    $heimVerein->nuligaClubId=get_option('nuliga-clubid', null);
    
    $vereinDAO->insert($heimVerein);
}

function dienste_mannschaft_verein_ergaenzen($dbhandle){
    $sql = "ALTER TABLE ".MannschaftDAO::tableName($dbhandle)
    ." ADD COLUMN verein_id INT NOT NULL";
    $dbhandle->query($sql);
}
function dienste_verein_der_heimmannschaften_setzen($dbhandle){
    $table_name = MannschaftDAO::tableName($dbhandle);
    $sql = "UPDATE $table_name SET verein_id=1";

    $dbhandle->query($sql);
}
function dienste_mannschaft_verein_foreign_key_ergaenzen($dbhandle){
    $sql = "ALTER TABLE ".MannschaftDAO::tableName($dbhandle)
    ." ADD FOREIGN KEY (verein_id) REFERENCES wp_verein(id) ON DELETE CASCADE ON UPDATE CASCADE";
    $dbhandle->query($sql);
}

function dienste_gegner_in_mannschaften_importieren($dbhandle){
    $gegnerService = new GegnerService($dbhandle);
    $mannschaftDAO = new MannschaftDAO($dbhandle);
    $vereinDAO = new VereinDAO($dbhandle);

    $alleGegner = $gegnerService->loadAlleGegner();

    foreach($alleGegner as $gegner){
        $verein = $vereinDAO->findOrInsertName($gegner->verein);

        $mannschaft = new Mannschaft();
        $mannschaft->verein = $verein;
        $mannschaft->nummer = $gegner->nummer;
        $mannschaft->geschlecht = $gegner->getGeschlecht();
        $mannschaft->jugendklasse = $gegner->getJugendklasse();
        $mannschaftDAO->insert($mannschaft);
        // TODO Mannschaftsmeldungen fehlen noch!
    }
}
?>