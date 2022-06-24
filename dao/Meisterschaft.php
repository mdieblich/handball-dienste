<?php
require_once __DIR__."/../entity/Meisterschaft.php";

function loadMeisterschaften(string $where = "1=1", string $orderby = "id"): array{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $sql = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby";
    $result = $wpdb->get_results($sql, ARRAY_A);

    $meisterschaften = array();
    if (count($result) > 0) {
        foreach($result as $meisterschaft) {
            $meisterschaftObj = new Meisterschaft($meisterschaft);
            $meisterschaften[$meisterschaftObj->getKuerzel()] = $meisterschaftObj;
        }
    }
    return $meisterschaften;
}

function upsertMeisterschaft(string $kuerzel, string $name){
    global $wpdb;
    
    $values = array(
        'kuerzel' => $kuerzel,
        'name' => $name
    );
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $wpdb->replace($table_name, $values);
}
?>