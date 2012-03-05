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
	register_activation_hook(__FILE__, 'easyreservations_install');
	function easyreservations_install(){ // Install Plugin Database

		$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
		$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";
		$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
		$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
		$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
		$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
		$emailstandart6="Reservation got edited by Guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>[changelog]";
		$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";

		$formstandart .= '[error]
<h1>Reserve now!</h1>
<p>General informations</p>

<label>Arrival Date
<span class="small">When do you come?</span>
</label>[date-from]

<label>Depature Date
<span class="small">When do you go?</span>
</label>[date-to]

<label>Room
<span class="small">Where you want to sleep?</span>
</label>[rooms]

<label>Offer
<span class="small">Do you want an offer?</span>
</label>[offers select]

<label>Adults
<span class="small">How many guests?</span>
</label>[persons Select 10]

<label>Children
<span class="small">with childrens?</span>
</label>[childs Select 10]

<p>Personal informations</p>

<label>Name
<span class="small">Whats your name?</span>
</label>[thename]

<label>eMail
<span class="small">Whats your email?</span>
</label>[email]

<label>Phone
<span class="small">Your phone number?</span>
</label>[custom text Phone *]

<label>Street
<span class="small">Your street?</span>
</label>[custom text Street *]

<label>Postal code
<span class="small">Your postal code?</span>
</label>[custom text PostCode *]

<label>City
<span class="small">Your city?</span>
</label>[custom text City *]

<label>Country
<span class="small">Your country?</span>
</label>[country]

<label>Message
<span class="small">Any comments?</span>
</label>[message]

<label>Captcha
<span class="small">Type in code</span>
</label>[captcha]
[show_price]
<div style="text-align:center;">[submit Send]</div>';

		/*

			Add Options

		*/
			add_option('reservations_main_permission', 'edit_posts', '', 'yes' );
			add_option( 'reservations_email_to_userapp_subj', 'Your Reservation on '.get_option('blogname').' has been approved', '', 'no' );
			add_option( 'reservations_email_to_userapp_msg', $emailstandart2, '', 'no' );
			add_option( 'reservations_email_to_userdel_subj', 'Your Reservation on '.get_option('blogname').' has been rejected', '', 'no' );
			add_option( 'reservations_email_to_userdel_msg', $emailstandart3, '', 'no' );
			add_option( 'reservations_email_to_admin_subj', 'New Reservation at '.get_option('blogname'), '', 'no' );
			add_option( 'reservations_email_to_admin_msg', $emailstandart1, '', 'no' );
			add_option( 'reservations_email_to_user_subj', 'Your Reservation on '.get_option('blogname'), '', 'no' );
			add_option( 'reservations_email_to_user_msg', $emailstandart4, '', 'no' );
			add_option( 'reservations_email_to_user_edited_subj', 'Your Reservation on '.get_option('blogname').' got edited', '', 'no' );
			add_option( 'reservations_email_to_user_edited_msg', $emailstandart5, '', 'no' );
			add_option( 'reservations_email_to_admin_edited_subj', 'Reservation on '.get_option('blogname').' got edited by user', '', 'no' );
			add_option( 'reservations_email_to_admin_edited_msg', $emailstandart6, '', 'no' );
			add_option( 'reservations_email_to_user_admin_edited_subj', 'Reservation on '.get_option('blogname').' got edited by admin', '', 'no' );
			add_option( 'reservations_email_to_user_admin_edited_msg', $emailstandart7, '', 'no' );
			add_option( 'reservations_email_sendmail_subj', 'Message from '.get_option('blogname'), '', 'no' );
			add_option( 'reservations_email_sendmail_msg', $emailstandart0, '', 'no' );
			add_option( 'reservations_form', $formstandart, '', 'no' );
			add_option( 'reservations_regular_guests', '', '', 'no' );
			add_option( 'reservations_edit_url', '', '', 'no' );
			add_option( 'reservations_price_per_persons', '1', '', 'yes' );
			add_option( 'reservations_on_page', '10', '', 'no' );
			add_option( 'reservations_room_category', '', '', 'yes' );
			add_option( 'reservations_special_offer_cat', '', '', 'yes' ); 
			add_option( 'reservations_currency', 'dollar', '', 'yes' );
			add_option( 'reservations_support_mail', '', '', 'yes' );
			add_option( 'reservations_style', 'greyfat', '', 'yes' );
			add_option('reservations_db_version', '1.2', '', 'yes' );
			add_option( 'reservations_edit_text', 'After editing your reservations status will get back to pending. We\'ll check the new situation as soon as we can.', '', 'no' );
			$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
			$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => $table_message, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_paid' => $table_paid, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1 );
			$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
			add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');

		/*

			Add Reservations Table to DB

		*/

		global $wpdb;
		$table_name = $wpdb->prefix . "reservations";

		$sql = "CREATE TABLE $table_name(
		id int(10) NOT NULL AUTO_INCREMENT,
		arrivalDate date NOT NULL,
		name varchar(35) NOT NULL,
		email varchar(50) NOT NULL,
		notes text NOT NULL,
		nights varchar(5) NOT NULL,
		country varchar(4) NOT NULL,
		dat varchar(8) NOT NULL,
		approve varchar(3) NOT NULL,
		room varchar(8) DEFAULT NULL,
		roomnumber varchar(8) NOT NULL,
		number int(4) NOT NULL,
		childs int(4) NOT NULL,
		special varchar(8) NOT NULL,
		price varchar(20) NOT NULL,
		custom text NOT NULL,
		customp text NOT NULL,
		reservated DATETIME NOT NULL,
		UNIQUE KEY id (id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		/*

			Add sample offer/room cat and two rooms & one offer

		*/

		if(!is_category( 'Offers' )){
			if(get_option("reservations_special_offer_cat")==''){
				$offer_cat = array('cat_name' => 'Offers', 'category_description' => 'Sample offer category', 'category_nicename' => 'offers', 'category_parent' => '');
				$offer_cat_id = wp_insert_category($offer_cat);
				update_option("reservations_special_offer_cat", $offer_cat_id);
			}
		}
		if(!is_category( 'Rooms' )){
			if(get_option("reservations_room_category")==''){
				$room_cat = array('cat_name' => 'Rooms', 'category_description' => 'Sample room category', 'category_nicename' => 'rooms', 'category_parent' => '');
				$room_cat_id = wp_insert_category($room_cat);
				update_option("reservations_room_category", $room_cat_id);
			}
		}

		$room_args = array( 'post_status' => 'publish|private', 'category' => get_option("reservations_room_category"), 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => 1);
		$roomcategories = get_posts( $room_args );
		if(!$roomcategories){

			$roomOne = array(
				'post_title' => 'Sample Room One',
				'post_content' => 'This is a Sample Room.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_category' => array(get_option("reservations_room_category"))
			);

			$roomOne_id = wp_insert_post( $roomOne );
			add_post_meta($roomOne_id, 'roomcount', 4);
			add_post_meta($roomOne_id, 'reservations_groundprice', 120);
			add_post_meta($roomOne_id, 'reservations_child_price', 10);
			add_post_meta($roomOne_id, 'reservations_filter', '[price 1 mon;fri 70][price 2 jun;july;aug;sep 50][price 3 2012 30][early 30 5]');

			$roomTwo = array(
				'post_title' => 'Sample Room Two',
				'post_content' => 'This is a Sample Room.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_category' => array(get_option("reservations_room_category"))
			);

			$roomTwo_id = wp_insert_post( $roomTwo );
			add_post_meta($roomTwo_id, 'roomcount', 7);
			add_post_meta($roomTwo_id, 'reservations_groundprice', 250.57);
			add_post_meta($roomTwo_id, 'reservations_child_price', 20);
			add_post_meta($roomTwo_id, 'reservations_filter', '[price 1 tue;wed 80][price 2 feb;mar;apr;may 55.5][price 3 2012 42.7][loyal 3 30][pers 4 50]');
		}

		$offer_args=array( 'category' => get_option('reservations_special_offer_cat'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => 1 );
		$offerposts = get_posts( $offer_args );
		if(!$offerposts){
			$offerOne = array(
				'post_title' => 'Sample Offer',
				'post_content' => 'This is a Sample Offer.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_category' => array(get_option('reservations_special_offer_cat'))
			);

			$offerOne_id = wp_insert_post( $offerOne );
			$pricestring = $roomOne_id.':50-'.$roomTwo_id.':70';
			add_post_meta($offerOne_id, 'reservations_groundprice', $pricestring);
			add_post_meta($offerOne_id, 'reservations_child_price', 5);

		}
	}

	define('RESERVATIONS_STYLE', get_option("reservations_style"));
	define('RESERVATIONS_IMAGES_DIR', WP_PLUGIN_URL.'/easyreservations/images');
	define('RESERVATIONS_LIB_DIR', WP_PLUGIN_URL.'/easyreservations/lib/');
	define('RESERVATIONS_JS_DIR', WP_PLUGIN_URL.'/easyreservations/js');

	add_action('init','easyreservations_init_language');
	add_action('admin_init','easyreservations_init_language');

	function easyreservations_init_language() {
		load_plugin_textdomain('easyReservations', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
	}

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


?>