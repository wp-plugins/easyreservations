<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function easyreservation_delete_plugin() {
	global $wpdb;

	delete_option( 'reservations_show_days' );
	delete_option( 'reservations_backgroundiffull' );
	delete_option( 'reservations_border_bottom' );
	delete_option( 'reservations_border_side' );
	delete_option( 'reservations_colorbackgroundfree' );
	delete_option( 'reservations_fontcoloriffull' );
	delete_option( 'reservations_backgroundiffull' );
	delete_option( 'reservations_colorborder' );
	delete_option( 'reservations_overview_size' );
	
	delete_option( 'reservations_price_per_persons' );
	delete_option( 'reservations_on_page' );
	delete_option( 'reservations_room_category' );
	delete_option( 'reservations_special_offer_cat' );
	delete_option( 'reservations_support_mail' );

	delete_option( 'reservations_email_to_userapp_subj' );
	delete_option( 'reservations_email_to_userapp_msg' );
	delete_option( 'reservations_email_to_userdel_subj' );
	delete_option( 'reservations_email_to_userdel_msg' );
	delete_option( 'reservations_email_to_admin_subj' );
	delete_option( 'reservations_email_to_admin_msg' );
	delete_option( 'reservations_email_to_user_subj' );
	delete_option( 'reservations_email_to_user_msg' );
	delete_option( 'reservations_email_to_user_edited_subj' );
	delete_option( 'reservations_email_to_user_edited_msg' );
	delete_option( 'reservations_email_to_admin_edited_subj' );
	delete_option( 'reservations_email_to_admin_edited_msg' );
	delete_option( 'reservations_email_to_user_admin_edited_subj' );
	delete_option( 'reservations_email_to_user_admin_edited_msg' );
	delete_option( 'reservations_email_sendmail_subj' );
	delete_option( 'reservations_email_sendmail_msg' );
	delete_option( 'reservations_form' );
	delete_option( 'reservations_main_permission' );
	delete_option( 'reservations_currency' );

	$table_name = $wpdb->prefix . "reservations";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

easyreservation_delete_plugin();

?>