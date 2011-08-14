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
	
	delete_option( 'reservation_overview_showgeneraly' );
	delete_option( 'reservation_form_nights' );
	delete_option( 'reservation_form_address' );
	delete_option( 'reservation_form_special' );
	delete_option( 'reservation_form_phone' );

	delete_option( 'reservations_price_per_persons' );
	delete_option( 'reservations_on_page' );
	delete_option( 'reservations_room_category' );
	delete_option( 'reservations_special_offer_cat' );
	delete_option( 'reservations_support_mail' );

	delete_option( 'reservations_currency' );
	delete_option( 'reservation_season1' );
	delete_option( 'reservation_season2' );
	delete_option( 'reservation_season3' );
	delete_option( 'reservation_season4' );
	delete_option( 'reservation_season5' );

	$table_name = $wpdb->prefix . "reservations";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

easyreservation_delete_plugin();

?>