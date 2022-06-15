<?php
function dienste_add_posttype_dienst(){
	// $labels = array(
    //     'name'               => 'Dienste',
    //     'singular_name'      => 'Dienst',
    //     'menu_name'          => 'Dienst',
    //     'name_admin_bar'     => 'Dienst',
    //     'add_new'            => 'Dienstseite hinzufügen',
    //     'add_new_item'       => 'Dienstseite hinzufügen',
    //     'new_item'           => 'Neue Dienstseite',
    //     'edit_item'          => 'Dienstseite editieren',
    //     'view_item'          => 'Dienstseite ansehen',
    //     'all_items'          => 'Alle Dienstseiten',
    //     'search_items'       => 'Suche in Dienstseiten',
    //     'parent_item_colon'  => 'Eltern-Dienstseiten',
    //     'not_found'          => 'Keine Dientseiten gefunden',
    //     'not_found_in_trash' => 'Keine Dienstseiten im Papierkorb gefunden'
    // );

    // $args = array(
    //     'labels'             => $labels,
    //     'public'             => true,
    //     'publicly_queryable' => true,
    //     'show_ui'            => true,
    //     'show_in_menu'       => true,
    //     'query_var'          => 'dienst',
    //     'rewrite'            => array( 'slug' => 'dienste' ),
    //     'capability_type'    => 'post',
    //     'has_archive'        => true,
    //     'hierarchical'       => false
    // );

    // register_post_type( 'dienst', $args );

    register_meta('post', 'mannschaft', array(
        // 'object_subtype' => 'dienst',
        'type' => 'integer',
        'description' => 'ID einer Mannschaft'
    ));
}

?>