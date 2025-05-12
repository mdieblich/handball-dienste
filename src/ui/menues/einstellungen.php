<?php
function addEinstellungenMenueEintrag(){
    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    $hook_mannschaften = add_submenu_page( 'dienste', 'Dienste - Einstellungen', 'Einstellungen', 'administrator', 'dienste-settings', 'displayEinstellungen');

}

function displayEinstellungen(){
    if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
    if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'dienste_messages', 'dienste_message', __( 'Einstellungen gespeichert', 'dienste' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'dienste_messages' );
    ?>
    <div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "dienste-settings"
			settings_fields( 'dienste-settings' );
			// output setting sections and their fields
			do_settings_sections( 'dienste-settings' );
			// output save settings button
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>

    <?php
}
?>