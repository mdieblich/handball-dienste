<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__.'/dao/MannschaftDAO.php';
require_once __DIR__.'/dao/GegnerDAO.php';
require_once __DIR__.'/dao/MeisterschaftDAO.php';
require_once __DIR__.'/dao/MannschaftsMeldungDAO.php';
require_once __DIR__.'/dao/SpielDAO.php';
require_once __DIR__.'/dao/DienstDAO.php';

function dienste_datenbank_cleanup($dbhandle = null) {
    if(empty($dbhandle)){
        global $wpdb;
        $dbhandle = $wpdb;
    }
    
    dienste_nuliga_import_cleanup($dbhandle);
    dienste_entities_cleanup($dbhandle);
}

function dienste_nuliga_import_cleanup($dbhandle){
    drop($dbhandle, $dbhandle->prefix .'import_status');
    drop($dbhandle, $dbhandle->prefix .'nuliga_mannschaftseinteilung');
    drop($dbhandle, $dbhandle->prefix .'nuliga_meisterschaft');
}

function drop($dbhandle, string $table_name){
    $sql = "DROP TABLE $table_name";
    $dbhandle->query($sql);
}
function dienste_entities_cleanup($dbhandle){
    drop($dbhandle, DienstDAO::tableName($dbhandle));
    drop($dbhandle, SpielDAO::tableName($dbhandle));
    drop($dbhandle, GegnerDAO::tableName($dbhandle));
    drop($dbhandle, MannschaftsMeldungDAO::tableName($dbhandle));
    drop($dbhandle, MeisterschaftDAO::tableName($dbhandle));
    drop($dbhandle, MannschaftDAO::tableName($dbhandle));
}

?>