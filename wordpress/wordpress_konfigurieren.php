<?php
function dienste_add_posttype_dienst(){

    register_meta('page', 'mannschaft', array(
        'type' => 'integer',
        'description' => 'ID einer Mannschaft'
    ));
}

?>