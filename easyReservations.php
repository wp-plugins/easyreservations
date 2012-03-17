<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.feryaz.com
Description: easyReservation is a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Version: 1.5
Author: Feryaz Beer
Author URI: http://www.feryaz.com
License:GPL2
*/

	add_action('admin_menu', 'easyReservations_add_pages');

	function easyReservations_add_pages(){  //  Add Pages Admincenter and Order them
		$reservation_main_permission=get_option("reservations_main_permission");
		if(isset($reservation_main_permission['dashboard']) && !empty($reservation_main_permission['dashboard'])) $dashboard2 = $reservation_main_permission['dashboard'];
		else $dashboard2 == 'edit_posts';
		if(isset($reservation_main_permission['resources']) && !empty($reservation_main_permission['resources'])) $resources2 = $reservation_main_permission['resources'];
		else $resources2 == 'edit_posts';
		if(isset($reservation_main_permission['statistics']) && !empty($reservation_main_permission['statistics'])) $statistics2 = $reservation_main_permission['statistics'];
		else $statistics2 == 'edit_posts';
		if(isset($reservation_main_permission['settings']) && !empty($reservation_main_permission['settings'])) $settings2 = $reservation_main_permission['settings'];
		else $settings2 == 'edit_posts';

		$count = easyreservations_get_pending();
		if($count != 0) $pending = '<span class="update-plugins count-'.$count.'"><span class="plugin-count">'.$count.'</span></span>';
		else $pending = '';
		
		add_menu_page(__('easyReservation','easyReservations'), __('Reservation','easyReservations').' '.$pending, $dashboard2, 'reservations', 'reservation_main_page', RESERVATIONS_IMAGES_DIR.'/logo.png' );
		add_submenu_page('reservations', __('Dashboard','easyReservations'), __('Dashboard','easyReservations'), $dashboard2, 'reservations', 'reservation_main_page');

		add_submenu_page('reservations', __('Resources','easyReservations'), __('Resources','easyReservations'), $resources2, 'reservation-resources', 'reservation_resources_page');

		add_submenu_page('reservations', __('Statistics','easyReservations'), __('Statistics','easyReservations'), $statistics2, 'reservation-statistics', 'reservation_statistics_page');
		
		add_submenu_page('reservations', __('Settings','easyReservations'), __('Settings','easyReservations'), $settings2, 'reservation-settings', 'reservation_settings_page');

	}

	/**
	* 	Hook languages to admin & frontend 
	*/
	function easyreservations_get_pending(){

		global $wpdb;

		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) as Num FROM ".$wpdb->prefix ."reservations WHERE approve='' AND arrivalDate > NOW()"));
		return $count;
	}

	/**
	*	Install script
	*
	*/
	register_activation_hook(__FILE__, 'easyreservations_install');
	//register_activation_hook(__FILE__, 'save_error');
	function save_error(){
	
		update_option('plugin_error',  ob_get_contents());
	
	}
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

		$formstandart = '[error]
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

<label>Children&rsquo;s
<span class="small">With children&rsquo;s?</span>
</label>[childs Select 10]

<p>Personal information&rsquo;s</p>

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
			$permission = array('dashboard' => 'edit_posts', 'statistics' => 'edit_posts', 'resources' => 'edit_posts', 'settings' => 'edit_posts');
			add_option('reservations_main_permission', $permission, '', 'yes' );
			add_option( 'reservations_email_to_user', array('msg' => $emailstandart4, 'subj' =>  'Your Reservation on '.get_option('blogname'), 'active' => 1), '', 'no');
			add_option( 'reservations_email_to_userapp', array('msg' => $emailstandart2, 'subj' => 'Your Reservation on '.get_option('blogname').' has been approved', 'active' => 1), '', 'no');
			add_option( 'reservations_email_to_userdel', array('msg' => $emailstandart3, 'subj' =>  'Your Reservation on '.get_option('blogname').' has been rejected', 'active' => 1), '', 'no');
			add_option( 'reservations_email_to_admin', array('msg' => $emailstandart1, 'subj' =>  'New Reservation at '.get_option('blogname'), 'active' => 1), '', 'no');
			add_option( 'reservations_email_to_user_edited', array('msg' => $emailstandart5, 'subj' =>  'Your Reservation on '.get_option('blogname').' got edited', 'active' => 1), '', 'no');
			add_option( 'reservations_email_to_admin_edited', array('msg' =>  $emailstandart6, 'subj' => 'Reservation on '.get_option('blogname').' got edited by user', 'active' => 1), '', 'no');
			add_option( 'reservations_email_to_user_admin_edited', array('msg' => $emailstandart7, 'subj' =>  'Reservation on '.get_option('blogname').' got edited by admin', 'active' => 1), '', 'no');
			add_option( 'reservations_email_sendmail', array('msg' => $emailstandart0, 'subj' => 'Message from '.get_option('blogname'), 'active' => 1), '', 'no');

			add_option( 'reservations_form', $formstandart, '', 'no' );
			add_option( 'reservations_regular_guests', '', '', 'no' );
			add_option( 'reservations_edit_url', '', '', 'yes' );
			add_option( 'reservations_price_per_persons', '1', '', 'yes' );
			add_option( 'reservations_on_page', '10', '', 'no' );
			add_option( 'reservations_room_category', '', '', 'yes' );
			add_option( 'reservations_special_offer_cat', '', '', 'yes' ); 
			add_option( 'reservations_currency', 'dollar', '', 'yes' );
			add_option( 'reservations_support_mail', '', '', 'yes' );
			add_option( 'reservations_style', 'greyfat', '', 'yes' );
			add_option('reservations_db_version', '1.4', '', 'yes' );
			add_option( 'reservations_edit_text', 'After editing your reservations status will get back to pending. We\'ll check the new situation as soon as we can.', '', 'no' );
			$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
			$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => 0, 'table_custom' => 0, 'table_customp' => 0, 'table_paid' => 0, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1 );
			$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
			add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');

		/*

			Add Reservations Table to DB

		*/

		global $wpdb;
		$table_name = $wpdb->prefix . "reservations";

		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
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

	/**
	*	Upgrade script
	*
	*/
	
	//delete_option('reservations_db_version' );
	add_action('admin_init','easyReservations_upgrade',1);

	function easyReservations_upgrade(){

		$easyReservations_active_ver=1.5;
		$easyReservations_installed_ver=get_option("reservations_db_version");

		if($easyReservations_installed_ver != $easyReservations_active_ver ){

			$reservtionsTable = 0;

			if(is_numeric($easyReservations_installed_ver) && $easyReservations_installed_ver < 1.2){
				global $wpdb;
				$table_name = $wpdb->prefix . "reservations";

					$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD reservated DATETIME NOT NULL"));
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET reservated=NOW()"));
					$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD childs int(4) NOT NULL"));
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET childs=0"));
					$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD country varchar(4) NOT NULL"));

					add_option( 'reservations_email_to_user_subj', 'Your Reservation on '.get_option('blogname'), '', 'yes' );
					add_option( 'reservations_email_to_user_msg', $emailstandart4, '', 'yes' );
					add_option( 'reservations_email_to_user_edited_subj', 'Your Reservation on '.get_option('blogname').' got edited', '', 'yes' );
					add_option( 'reservations_email_to_user_edited_msg', $emailstandart5, '', 'yes' );
					add_option( 'reservations_email_to_admin_edited_subj', 'Reservation on '.get_option('blogname').' got edited by user', '', 'yes' );
					add_option( 'reservations_email_to_admin_edited_msg', $emailstandart6, '', 'yes' );
					add_option( 'reservations_email_to_user_admin_edited_subj', 'Reservation on '.get_option('blogname').' got edited by admin', '', 'yes' );
					add_option( 'reservations_email_to_user_admin_edited_msg', $emailstandart7, '', 'yes' );
					add_option( 'reservations_email_sendmail_subj', 'Message from '.get_option('blogname'), '', 'yes' );
					add_option( 'reservations_email_sendmail_msg', $emailstandart0, '', 'yes' );
					add_option( 'reservations_regular_guests', '', '', 'yes' );
					add_option( 'reservations_style', 'greyfat', '', 'yes' );
					add_option( 'reservations_edit_url', '', '', 'yes' );
					add_option( 'reservations_edit_text', 'After editing your reservations status will get back to pending. We\'ll check the new situation as soon as we can.', '', 'yes' );

					$reservtionsTable = 1;

			}
			if($easyReservations_installed_ver == 1.2 || $easyReservations_installed_ver == 1.3 || $easyReservations_installed_ver == "1.3.1" || $easyReservations_installed_ver == "1.3.2"){

				$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
				$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => $table_message, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_paid' => $table_paid, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1 );
				$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
				add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');

				$reservtionsTable = 1;
			}
			if($easyReservations_installed_ver == 1.4 || $easyReservations_installed_ver == "1.4.5"){
				$permission = array('dashboard' => 'edit_posts', 'statistics' => 'edit_posts', 'resources' => 'edit_posts', 'settings' => 'edit_posts');
				update_option( 'reservations_main_permission', $permission );

				add_option( 'reservations_email_to_user', array('msg' => get_option( 'reservations_email_to_user_msg' ), 'subj' => get_option( 'reservations_email_to_user_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_to_userapp', array('msg' => get_option( 'reservations_email_to_userapp_msg' ), 'subj' => get_option( 'reservations_email_to_userapp_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_to_userdel', array('msg' => get_option( 'reservations_email_to_userdel_msg' ), 'subj' => get_option( 'reservations_email_to_userdel_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_to_admin', array('msg' => get_option( 'reservations_email_to_admin_msg' ), 'subj' => get_option( 'reservations_email_to_admin_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_to_user_edited', array('msg' => get_option( 'reservations_email_to_user_edited_msg' ), 'subj' => get_option( 'reservations_email_to_user_edited_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_to_admin_edited', array('msg' => get_option( 'reservations_email_to_admin_edited_msg' ), 'subj' => get_option( 'reservations_email_to_admin_edited_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_to_user_admin_edited', array('msg' => get_option( 'reservations_email_to_user_admin_edited_msg' ), 'subj' => get_option( 'reservations_email_to_user_admin_edited_subj' ), 'active' => 1), '', 'no');
				add_option( 'reservations_email_sendmail', array('msg' => get_option( 'reservations_email_sendmail_msg' ), 'subj' => get_option( 'reservations_email_sendmail_subj' ), 'active' => 1), '', 'no');

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
				global $wpdb;
				$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."postmeta WHERE meta_key = 'reservations_filter' "));

				$reservtionsTable = 1;
			}

			if($reservtionsTable == 1){
				update_option('reservations_db_version', "1.4.5");
				add_action('admin_notices', 'easyReservations_upgrade_notice');
			}
		}
	}
	
	function easyReservations_upgrade_notice(){
		echo '<div class="updated">
		   <p>Thanks for updating <b>easyReservations</b> to <b>1.5</b>!<br>View <a href="http://feryaz.de/changelog/" target="_blank">here</a> for a detailed Changelog!<br>
		   Hope you enjoy it!</p>
		</div>';
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
	require_once(dirname(__FILE__)."/lib/functions/both.php");

	if(is_admin()){

		require_once(dirname(__FILE__)."/lib/functions/admin.php");
	
		require_once(dirname(__FILE__)."/pagination.class.php");

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

	require_once(dirname(__FILE__)."/lib/widgets/form_widget.php");
	if(file_exists(dirname(__FILE__).'/lib/plugins/core/core.php')){
		require_once(dirname(__FILE__)."/lib/plugins/core/core.php");
	}

	if(file_exists(dirname(__FILE__).'/lib/plugins/paypal/paypal.php')){
		require_once(dirname(__FILE__)."/lib/plugins/paypal/paypal.php");
	}
	if(file_exists(dirname(__FILE__).'/lib/plugins/dummy/dummy.php')){
		require_once(dirname(__FILE__)."/lib/plugins/dummy/dummy.php");
	}
?>