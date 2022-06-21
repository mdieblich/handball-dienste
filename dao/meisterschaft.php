<?php
require_once WP_PLUGIN_DIR."/dienstedienst/entity/meisterschaft.php";

function loadMeisterschaften(string $where = "1=1", string $orderby = "id"): array{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'meisterschaft';
    $sql = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby";
    $result = $wpdb->get_results($sql, ARRAY_A);

    $meisterschaften = array();
    if (count($result) > 0) {
        foreach($result as $meisterschaft) {
            $meisterschaftObj = new Meisterschaft($meisterschaft);
            $meisterschaften[$meisterschaftObj->getID()] = $meisterschaftObj;
        }
    }
    return $meisterschaften;
}
?>