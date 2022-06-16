<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/mannschaft.php";

function loadMannschaften(): array{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mannschaft';
    $sql = "SELECT * FROM $table_name ORDER BY jugendklasse, nummer, geschlecht";
    $result = $wpdb->get_results($sql, ARRAY_A);

    $mannschaften = array();
    if (count($result) > 0) {
        foreach($result as $mannschaft) {
            $mannschaftObj = new Mannschaft($mannschaft);
            $mannschaften[$mannschaftObj->getID()] = $mannschaftObj;
        }
    }
    return $mannschaften;
}

function getMannschaftFromName(array $mannschaften, string $name): ?Mannschaft{
    foreach($mannschaften as $mannschaft){
        if($mannschaft->getName() === $name){
            return $mannschaft;
        }
    }
    return null;
}
?>