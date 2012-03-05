<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.feryaz.com
Description: easyReservation is a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Version: 1.3.2
Author: Feryaz Beer
Author URI: http://www.feryaz.com
License:GPL2
*/

	define('RESERVATIONS_STYLE', get_option("reservations_style"));
	define('RESERVATIONS_IMAGES_DIR', WP_PLUGIN_URL.'/easyreservations/images');
	define('RESERVATIONS_LIB_DIR', WP_PLUGIN_URL.'/easyreservations/lib/');
	define('RESERVATIONS_JS_DIR', WP_PLUGIN_URL.'/easyreservations/js');

	if(is_admin()){

		require_once(dirname(__FILE__)."/lib/functions/admin.php");
		
		require_once(dirname(__FILE__)."/easyReservations_admin_main.php");

		require_once(dirname(__FILE__)."/easyReservations_admin_resources.php");

		require_once(dirname(__FILE__)."/easyReservations_admin_statistics.php");

		require_once(dirname(__FILE__)."/easyReservations_admin_settings.php");

	} else {

		require_once(dirname(__FILE__)."/lib/functions/front.php");

		require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");

		require_once(dirname(__FILE__)."/easyReservations_edit_shortcode.php");

		require_once(dirname(__FILE__)."/easyReservations_calendar_shortcode.php");

		add_shortcode('easy_calendar', 'reservations_calendar_shortcode');
		add_shortcode('easy_edit', 'reservations_edit_shortcode');
		add_shortcode('easy_form', 'reservations_form_shortcode');

	}

	require_once(dirname(__FILE__)."/lib/functions/both.php");
	require_once(dirname(__FILE__)."/lib/widgets/form_widget.php");
	if(file_exists(dirname(__FILE__).'/lib/plugins/paypal/paypal.php')){
		require_once(dirname(__FILE__)."/lib/plugins/paypal/paypal.php");
	}

	register_activation_hook(__FILE__, 'easyreservations_install');

?>