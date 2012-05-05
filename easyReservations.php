<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.easyreservations.org
Description: This powerfull property and reservation management plugin allows you to receive, schedule and handle your bookings easily!
Version: 2.0.1
Author: Feryaz Beer
Author URI: http://www.feryaz.de
License:GPL2
*/

	add_action('admin_menu', 'easyReservations_add_pages');

	function easyReservations_add_pages(){  //  Add Pages Admincenter and Order them
		$reservation_main_permission=get_option("reservations_main_permission");
		if($reservation_main_permission && is_array($reservation_main_permission)){
			if(isset($reservation_main_permission['dashboard']) && !empty($reservation_main_permission['dashboard'])) $dashboard = $reservation_main_permission['dashboard'];
			else $dashboard == 'edit_posts';
			if(isset($reservation_main_permission['resources']) && !empty($reservation_main_permission['resources'])) $resources = $reservation_main_permission['resources'];
			else $resources == 'edit_posts';
			if(isset($reservation_main_permission['statistics']) && !empty($reservation_main_permission['statistics'])) $statistics = $reservation_main_permission['statistics'];
			else $statistics == 'edit_posts';
			if(isset($reservation_main_permission['settings']) && !empty($reservation_main_permission['settings'])) $settings = $reservation_main_permission['settings'];
			else $settings == 'edit_posts';
		} else {
			$settings = 'edit_posts'; $statistics = 'edit_posts'; $resources = 'edit_posts'; $dashboard = 'edit_posts';
		}

		$count = easyreservations_get_pending();
		if($count != 0) $pending = '<span class="update-plugins count-'.$count.'"><span class="plugin-count">'.$count.'</span></span>';
		else $pending = '';

		if(current_user_can($dashboard)) add_action('wp_dashboard_setup', 'easyreservations_add_dashboard_widgets' );

		add_menu_page(__('easyReservation','easyReservations'), __('Reservation','easyReservations').' '.$pending, $dashboard, 'reservations', 'reservation_main_page', RESERVATIONS_IMAGES_DIR.'/logo.png' );
		add_submenu_page('reservations', __('Dashboard','easyReservations'), __('Dashboard','easyReservations'), $dashboard, 'reservations', 'reservation_main_page');
		add_submenu_page('reservations', __('Resources','easyReservations'), __('Resources','easyReservations'), $resources, 'reservation-resources', 'reservation_resources_page');
		add_submenu_page('reservations', __('Statistics','easyReservations'), __('Statistics','easyReservations'), $statistics, 'reservation-statistics', 'reservation_statistics_page');
		add_submenu_page('reservations', __('Settings','easyReservations'), __('Settings','easyReservations'), $settings, 'reservation-settings', 'reservation_settings_page');
	}

	/**
	* 	Hook languages to admin & frontend
	*/
	function easyreservations_get_pending(){

		global $wpdb;

		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) as Num FROM ".$wpdb->prefix ."reservations WHERE approve='' AND arrival > NOW()"));
		return $count;
	}

	/**
	*	Install script
	*
	*/

	register_activation_hook(__FILE__, 'easyreservations_install');
	register_activation_hook(__FILE__, 'save_error');
	function save_error(){

		update_option('plugin_error',  ob_get_contents());

	}

	function easyreservations_install(){ // Install Plugin Database

		$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
		$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]";
		$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
		$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>[customs]<br>edit your reservation on [editlink]";
		$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
		$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
		$emailstandart6="Reservation got edited by Guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]<br><br>[changelog]";
		$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";

		$formstandart = '[error]
<h1>Reserve now!<span style="float:right;margin:10px">[show_price]</span></h1>
<p>General informations</p>

<label>Arrival Date
<span class="small">When do you come?</span>
</label><span class="row">[date-from style="width:75px"] [date-from-hour style="width:50px" value="12"]:[date-from-min style="width:50px"]</span>

<label>Departure Date
<span class="small">When do you go?</span>
</label><span class="row">[date-to style="width:75px"] [date-to-hour style="width:50px" value="12"]:[date-to-min style="width:50px"]</span>

<label>Resource
<span class="small">What do you want?</span>
</label>[resources]

<label>Adults
<span class="small">How many guests?</span>
</label>[adults 1 10]

<label>Children&rsquo;s	
<span class="small">With children&rsquo;s?</span>
</label>[childs 0 10]

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
</label>[custom textarea Message]

<label>Captcha
<span class="small">Type in code</span>
</label>[captcha]

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

		add_option( 'reservations_uninstall', '1', '', 'no' );
		add_option( 'reservations_form', $formstandart, '', 'no' );
		add_option( 'reservations_regular_guests', '', '', 'no' );
		add_option( 'reservations_edit_url', '', '', 'yes' );
		add_option( 'reservations_price_per_persons', '1', '', 'yes' );
		add_option( 'reservations_on_page', '10', '', 'no' );
		add_option( 'reservations_support_mail', '', '', 'yes' );
		add_option('reservations_db_version', '2.0', '', 'yes' );
		$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1, 'show_welcome' => 2 );
		$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_fav' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => 0, 'table_custom' => 0, 'table_customp' => 0, 'table_paid' => 0, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1, 'table_reservated' => 0, 'table_status' => 1, 'table_fav' => 1 );
		$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
		add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');
		$edit_options = array( 'login_text' => '', 'edit_text' => '', 'submit_text' => 'Reservation successfully edited',  'table_infos' => array('date', 'status', 'price', 'room'), 'table_status' => array('','yes','no'), 'table_time' => array('past','current','future'), 'table_style' => 1, 'table_more' => 1 );
		add_option('reservations_edit_options', $edit_options, '', 'no');
		add_option('reservations_settings', array( 'style' => "greyfat", 'interval' => 86400, 'currency' => '#36', 'date_format' => 'd.m.Y', 'time' => 1 ), '', 'yes');

		/*

			Add Reservations Table to DB

		*/

		global $wpdb;
		$table_name = $wpdb->prefix . "reservations";

		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		id int(10) NOT NULL AUTO_INCREMENT,
		arrival DATETIME NOT NULL,
		departure DATETIME NOT NULL,
		user int(10) NOT NULL,
		name varchar(35) NOT NULL,
		email varchar(50) NOT NULL,
		country varchar(4) NOT NULL,
		approve varchar(3) NOT NULL,
		room varchar(8) DEFAULT NULL,
		roomnumber varchar(8) NOT NULL,
		number int(4) NOT NULL,
		childs int(4) NOT NULL,
		price varchar(20) NOT NULL,
		custom longtext NOT NULL,
		customp longtext NOT NULL,
		reservated DATETIME NOT NULL,
		UNIQUE KEY id (id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		/*

			Add sample two Resources

		*/

		$room_args = array( 'post_status' => 'publish|private', 'post_type' => 'easy-rooms', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => 1);
		$roomcategories = get_posts( $room_args );
		if(!$roomcategories){

			$roomOne = array(
				'post_title' => 'Sample Resource One',
				'post_content' => 'This is a Sample Resource.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_type' => 'easy-rooms'
			);

			$roomOne_id = wp_insert_post( $roomOne );
			add_post_meta($roomOne_id, 'roomcount', 4);
			add_post_meta($roomOne_id, 'reservations_groundprice', 120);
			add_post_meta($roomOne_id, 'reservations_child_price', 10);

			$roomTwo = array(
				'post_title' => 'Sample Resource Two',
				'post_content' => 'This is a Sample Resource.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_type' => 'easy-rooms'
			);

			$roomTwo_id = wp_insert_post( $roomTwo );
			add_post_meta($roomTwo_id, 'roomcount', 7);
			add_post_meta($roomTwo_id, 'reservations_groundprice', 250.57);
			add_post_meta($roomTwo_id, 'reservations_child_price', 20);
		}
	}

	/**
	*	Upgrade script
	*
	*/

	add_action('admin_init','easyReservations_upgrade',1);

	function easyReservations_upgrade(){

		$easyReservations_active_ver=2.0;
		$easyReservations_installed_ver = get_option("reservations_db_version");

		if($easyReservations_installed_ver != $easyReservations_active_ver ){

			if($easyReservations_installed_ver == 1.2 || $easyReservations_installed_ver == 1.3 || $easyReservations_installed_ver == "1.3.1" || $easyReservations_installed_ver == "1.3.2"){
				$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
				$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => 0, 'table_custom' => 0, 'table_customp' => 0, 'table_paid' => 0, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1, 'table_reservated' => 0, 'table_status' => 1, 'table_fav' => 1 );
				$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
				add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');

				$easyReservations_installed_ver = 1.4;
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

				$easyReservations_installed_ver = 1.5;
			}
			if($easyReservations_installed_ver == 1.5){

				global $wpdb;
				$room_category = get_option('reservations_room_category');
				if(isset($room_category) && !empty($room_category) && is_numeric($room_category)){
					$args=array( 'category' => $room_category, 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );
					$getids = get_posts($args);
					foreach($getids as $post){
						$id = $post->ID;
						$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."posts SET post_type='easy-rooms' WHERE ID='$id' "));
						$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."term_relationships WHERE object_id='$id'  ") );
					}
				}
				$offer_category = get_option('reservations_special_offer_cat');
				if(isset($offer_category) && !empty($offer_category) && is_numeric($room_category)){
					$args=array( 'category' => $offer_category, 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );
					$getids = get_posts($args);
					foreach($getids as $post){
						$id = $post->ID;
						$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."posts SET post_type='easy-offers' WHERE ID='$id' "));
						$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix ."term_relationships WHERE object_id='$id'"));
					}
				}

				delete_option('reservations_room_category');
				delete_option('reservations_special_offer_cat');
				$wpdb->query($wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations CHANGE custom custom longtext"));
				$wpdb->query($wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations CHANGE customp customp longtext"));

				$reservations = $wpdb->get_results($wpdb->prepare("SELECT id, custom, customp FROM ".$wpdb->prefix ."reservations"));
				foreach($reservations as $reservation){
					$id = $reservation->id;
					$customs = $reservation->custom;
					$explode_customs = explode('&;&', $customs);
					$new_customs='';
					if(isset($explode_customs[1])){
						foreach($explode_customs as $custom){
							if(!empty($custom)){
								$explode_the_custom = explode('&:&', $custom);
								$new_customs[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_custom[1] );
							}
						}
					} elseif(isset($explode_customs[0]) && strlen($explode_customs[0]) > 5){
						$explode_the_custom = explode('&:&', $explode_customs[0]);
						$new_customs[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_custom[1] );
					}

					$customsp = $reservation->customp;
					$explode_customp = explode('&;&', $customsp);
					$new_customp='';
					if(isset($explode_customp[1])){
						foreach($explode_customp as $customp){
							if(!empty($customp)){
								$explode_the_custom = explode('&:&', $customp);
								$explode_the_price = explode(':', $explode_the_custom[1]);
								$new_customp[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_price[0], 'amount' => $explode_the_price[1] );
							}
						}
					} elseif(isset($explode_customp[0]) && strlen($explode_customp[0]) > 5){
						$explode_the_custom = explode('&:&', $explode_customp[0]);
						$explode_the_price = explode(':', $explode_the_custom[1]);
						$new_customp[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_price[0], 'amount' => $explode_the_price[1] );
					}

					$save_custom = '';
					$save_customp = '';
					if(!empty($new_customs)) $save_custom = maybe_serialize(array($new_customs));
					if(!empty($new_customp)) $save_customp = maybe_serialize(array($new_customp));

					$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$save_custom', customp='$save_customp' WHERE id='$id' "));

					unset($new_custom);
					unset($new_customp);
				}

				$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD user int(10) NOT NULL"));
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET user='0'"));
				add_option( 'reservations_uninstall', '1', '', 'no' );

				$easyReservations_installed_ver = 1.6;
			}
			if($easyReservations_installed_ver == 1.6){
				$edit_text = get_option('reservations_edit_text');
				$edit_options = array( 'login_text' => stripslashes($edit_text), 'edit_text' => stripslashes($edit_text),  'table_infos' => array('date', 'status', 'price', 'room'), 'table_status' => array('','yes','no'), 'table_time' => array('past','current','future'), 'table_style' => 1, 'table_more' => 1 );
				add_option('reservations_edit_options', $edit_options, '', false);
				add_option('reservations_date_format', 'd.m.Y', '', true);

				delete_option( 'reservations_edit_text' );
				$easyReservations_installed_ver = 1.7;
			}

			if($easyReservations_installed_ver == 1.7){
				$easyReservations_installed_ver = 1.8;
			}

			if($easyReservations_installed_ver == 1.8){
				global $wpdb;
				$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD arrival datetime NOT NULL"));
				$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD departure datetime NOT NULL"));
				$reservations = $wpdb->get_results($wpdb->prepare("SELECT id, arrivalDate, nights, notes FROM ".$wpdb->prefix ."reservations"));
				foreach($reservations as $reservation){
					$id = $reservation->id;
					$arrivalDate = strtotime($reservation->arrivalDate);
					$notes = array( array( 'type' => 'cstm', 'mode' => 'edit', 'title' => 'Message', 'value' => $reservation->notes ) );
					easyreservations_edit_custom( $notes, $id, 0, 0, false, 0, 'cstm', 'edit', 1 );

					$nights = $reservation->nights;
					$arrival = date("Y-m-d H:i", $arrivalDate+43200);
					$departure = date("Y-m-d H:i", $arrivalDate+(86400*$nights)+43200);

					$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrival='$arrival', departure='$departure' WHERE id='$id' "));
				}

				$settings_array = array( 'style' => get_option("reservations_style"), 'interval' => 86400, 'currency' => get_option("reservations_currency"), 'date_format' => get_option("reservations_date_format"), 'time' => 1 );
				add_option("reservations_settings", $settings_array);
				delete_option("reservations_style");
				delete_option("reservations_interval");
				delete_option("reservations_currency");
				delete_option("reservations_date_format");
				$wpdb->query( $wpdb->prepare("DROP TABLE ".$wpdb->prefix ."reservations DROP arrivalDate, nights, special, dat, notes"));

				$easyReservations_installed_ver = 2.0;
			}

			update_option('reservations_db_version', $easyReservations_installed_ver);
			$main_options = get_option("reservations_main_options");
			$main_options['show']['show_welcome'] = 2;
			update_option('reservations_main_options', $main_options);
			save_error();
		}
	}

	$reservations_settings = get_option("reservations_settings");

	define('RESERVATIONS_STYLE', $reservations_settings['style']);
	define('RESERVATIONS_CURRENCY', $reservations_settings['currency']);
	define('RESERVATIONS_IMAGES_DIR', WP_PLUGIN_URL.'/easyreservations/images');
	define('RESERVATIONS_LIB_DIR', WP_PLUGIN_URL.'/easyreservations/lib/');
	define('RESERVATIONS_JS_DIR', WP_PLUGIN_URL.'/easyreservations/js');
	define('RESERVATIONS_DATE_FORMAT', $reservations_settings['date_format']);
	define('RESERVATIONS_USE_TIME', $reservations_settings['time']);
	if(RESERVATIONS_USE_TIME == 1) $usetime = ' H:i'; else $usetime = '';
	define('RESERVATIONS_DATE_FORMAT_SHOW', RESERVATIONS_DATE_FORMAT.$usetime);

	add_action('init','easyreservations_init_language');
	add_action('admin_init','easyreservations_init_language');

	function easyreservations_init_language() {
		load_plugin_textdomain('easyReservations', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
	}

	require_once(dirname(__FILE__)."/lib/functions/both.php");

	if(file_exists(dirname(__FILE__).'/lib/modules/core/core.php')){
		require_once(dirname(__FILE__)."/lib/modules/core/core.php");
	}

	if(is_admin()){
		require_once(dirname(__FILE__)."/pagination.class.php");
		require_once(dirname(__FILE__)."/lib/functions/admin.php");
		require_once(dirname(__FILE__)."/lib/widgets/dashboard.php");

		if(isset($_GET['page']) && $_GET['page'] == 'reservations') require_once(dirname(__FILE__)."/easyReservations_admin_main.php");

		if(isset($_GET['page']) && $_GET['page'] == 'reservation-resources') require_once(dirname(__FILE__)."/easyReservations_admin_resources.php");

		if(isset($_GET['page']) && $_GET['page'] == 'reservation-statistics') require_once(dirname(__FILE__)."/easyReservations_admin_statistics.php");

		if(isset($_GET['page']) && $_GET['page'] == 'reservation-settings') require_once(dirname(__FILE__)."/easyReservations_admin_settings.php");

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

	if(function_exists('easyreservation_is_paypal') && easyreservation_is_paypal()){
		include_once(dirname(__FILE__)."/lib/modules/paypal/paypal.php");
	}
	if(function_exists('easyreservation_is_chat') && easyreservation_is_chat()){
		include_once(dirname(__FILE__)."/lib/modules/chat/chat.php");
	}
	if(function_exists('easyreservation_is_import') && easyreservation_is_import()){
		include_once(dirname(__FILE__)."/lib/modules/import/import.php");
	}
	if(function_exists('easyreservation_is_multical') && easyreservation_is_multical()){
		include_once(dirname(__FILE__)."/lib/modules/multical/multical.php");
	}
	if(function_exists('easyreservation_is_search') && easyreservation_is_search()){
		include_once(dirname(__FILE__)."/lib/modules/search/search.php");
	}
	if(function_exists('easyreservation_is_language') && easyreservation_is_language()){
		include_once(dirname(__FILE__)."/lib/modules/lang/lang.php");
	}
	if(function_exists('easyreservation_is_datepicker') && easyreservation_is_datepicker()){
		include_once(dirname(__FILE__)."/lib/modules/datepicker/datepicker.php");
	}

?>