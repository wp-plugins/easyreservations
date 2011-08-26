<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.feryaz.com
Description: easyReservation is a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Version: 1.1
Author: Feryaz Beer
Author URI: http://www.feryaz.com
*/

add_action('admin_menu', 'reservation_add_pages');

require_once(dirname(__FILE__)."/easyReservations_administration.php");

require_once(dirname(__FILE__)."/easyReservations_post_admin_widget.php");

require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");

require_once(dirname(__FILE__)."/easyReservations_statistics.php");

add_shortcode('reservations', 'reservations_shortcode');


function my_plugin_init() {
// Internationalization, first(!)
load_plugin_textdomain('easyReservations', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
// Other init stuff, be sure to it after load_plugins_textdomain if it involves translated text(!)
}
add_action('init','my_plugin_init');
add_action('admin_init','my_plugin_init');


function reservation_register_head() {
}
add_action('admin_head', 'reservation_register_head');

function reservation_admin_bar() {
        global $wp_admin_bar, $wpdb;

		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) as Num FROM ".$wpdb->prefix ."reservations WHERE approve=''"));		
		
		if($count!=0) $c="<span id=\"ab-awaiting-mod\" class=\"pending-count\">".$count."</span>";
        $wp_admin_bar->add_menu( array(

        'id' => 'reservations',
        'title' => __('Reservations '.$c.''),
        'href' => admin_url( 'admin.php?page=reservations&typ=pending')
    ) );
}

add_action( 'wp_before_admin_bar_render', 'reservation_admin_bar' );

function plugin_load_style() {  //  Load Scripts and Styles
        $myStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/style.css';
		
		wp_register_style('myStyleSheets', $myStyleUrl);

		wp_enqueue_style( 'myStyleSheets');
}

if(isset($_GET['page'])) { $page=$_GET['page'] ; } else $page='';

if($page == 'reservations' OR $page== 'settings' OR $page== 'statistics' OR $page== 'add-reservation'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'plugin_load_style');
}

function plugin_load_checkbox() {  //  Load Scripts and Styles
        $ScriptFile1 = WP_PLUGIN_URL . '/easyreservations/js/checkbox.js';

		wp_register_script('checkbox', $ScriptFile1);

		wp_enqueue_script( 'checkbox');
}

if($page == 'reservations'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'plugin_load_checkbox');
}

function plugin_load_colorpick() {  //  Load Scripts and Styles
        $ScriptFile4 = WP_PLUGIN_URL . '/easyreservations/js/mColorPicker_min.js';
		$ScriptFile3 = WP_PLUGIN_URL . '/easyreservations/js/jquery.tools.min.js';

		wp_register_script('jquerytools', $ScriptFile3);
		wp_register_script('colorpick', $ScriptFile4);

		wp_enqueue_script('jquerytools');
		wp_enqueue_script('colorpick');
}

if($page == 'settings'){  //  Only load Styles and Scripts on Reservation Admin Page
add_action('admin_init', 'plugin_load_colorpick');
}

function statistics_load() {  //  Load Scripts and Styles
        $highcharts = WP_PLUGIN_URL . '/easyreservations/js/highcharts.js';
        $exporting = WP_PLUGIN_URL . '/easyreservations/js/modules/exporting.js';

		wp_deregister_script('jquery');

		wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js', false, '1.6.1');
		wp_register_script('highcharts', $highcharts);
		wp_register_script('exporting', $exporting);

		wp_enqueue_script('highcharts');
		wp_enqueue_script('exporting');
		wp_enqueue_script('jquery');
}

if($page == 'statistics'){  //  Only load Styles and Scripts on Statistics Page
add_action('admin_init', 'statistics_load');
}

function reservations_datepicker_load() {  //  Load Scripts and Styles for datepicker
        $ScriptFile1 = WP_PLUGIN_URL . '/easyreservations/js/checkbox.js';
        $ScriptFile3 = WP_PLUGIN_URL . '/easyReservations/js/jquery.tools.min.js';

		wp_register_script('checkbox', $ScriptFile1);
		wp_register_script('jquerytools', $ScriptFile3);

		wp_enqueue_script( 'checkbox');
		wp_enqueue_script('jquerytools');
}

if($page == 'add-reservation' OR $page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
add_action('admin_init', 'reservations_datepicker_load');
}

function reservation_add_pages(){  //  Add Pages Admincenter and Order them

    add_menu_page(__('Reservation','menu-test'), __('Reservations','menu-test'), 'edit_posts', 'reservations', 'reservation_main_page' );

	add_submenu_page('reservations', __('Add Reservation','menu-test'), __('Add Reservation','menu-test'), 'edit_posts', 'add-reservation', 'reservation_add_reservaton');

	add_submenu_page('reservations', __('Statistics','menu-test'), __('Statistics','menu-test'), 'edit_posts', 'statistics', 'reservation_statistics_page');
	
	add_submenu_page('reservations', __('Settings','menu-test'), __('Settings','menu-test'), 'edit_posts', 'settings', 'reservation_settings_page');
}

add_option('reservations_db_version', '1.0.1', '', 'yes' );
$easyreservations_ver="1.1";
$installed_version=get_option("reservations_db_version");

if( $installed_ver != $easyreservations_ver ) {
	
$emailstandart1="New Reservation on Blogname from<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
$formstandart.="
	[error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[phone]</p>

	<p>Address:<br>[address]</p>

	<p>Room: [room]</p>

	<p>Offer: [offer select]</p>

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";

	add_option( 'reservations_email_to_userapp_subj', 'Your Reservation on Sitename has been approved', '', 'yes' );
	add_option( 'reservations_email_to_userapp_msg', $emailstandart2, '', 'yes' );
	add_option( 'reservations_email_to_userdel_subj', 'Your Reservation on Sitename has been rejected', '', 'yes' );
	add_option( 'reservations_email_to_userdel_msg', $emailstandart3, '', 'yes' );
	add_option( 'reservations_email_to_admin_subj', 'New Reservation at'.get_option('blogname'), '', 'yes' );
	add_option( 'reservations_email_to_admin_msg', $emailstandart1, '', 'yes' );
	add_option( 'reservations_form', $formstandart, '', 'yes' );

	delete_option( 'reservation_season1' );
	delete_option( 'reservation_season2' );
	delete_option( 'reservation_season3' );
	delete_option( 'reservation_season4' );
	delete_option( 'reservation_season5' );
	delete_option( 'reservation_overview_showgeneraly' );
	delete_option( 'reservation_form_nights' );
	delete_option( 'reservation_form_address' );
	delete_option( 'reservation_form_special' );
	delete_option( 'reservation_form_phone' );
	
	update_option('reservations_db_version', '1.1');
}
   
register_activation_hook(__FILE__, 'easyreservation_install');

function easyreservation_install(){ // Install Plugin Database

	$emailstandart1="New Reservation on Blogname from<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
	$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
	$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
	$formstandart.="
	[error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[phone]</p>

	<p>Address:<br>[address]</p>

	<p>Room: [room]</p>

	<p>Offer: [offer select]</p>	

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";

			add_option( 'reservations_email_to_userapp_subj', 'Your Reservation on demo has been approved', '', 'yes' );
			add_option( 'reservations_email_to_userapp_msg', $emailstandart2, '', 'yes' );
			add_option( 'reservations_email_to_userdel_subj', 'Your Reservation on demo has been rejected', '', 'yes' );
			add_option( 'reservations_email_to_userdel_msg', $emailstandart3, '', 'yes' );
			add_option( 'reservations_email_to_admin_subj', 'New Reservation at'.get_option('blogname'), '', 'yes' );
			add_option( 'reservations_email_to_admin_msg', $emailstandart1, '', 'yes' );
			add_option( 'reservations_form', $formstandart, '', 'yes' );

		if(!get_option('reservations_backgroundiffull') OR !get_option('reservations_colorborder')){
			add_option( 'reservations_show_days', '26', '', 'yes' );
			add_option( 'reservations_backgroundiffull', 'blue', '', 'yes' );
			add_option( 'reservations_border_bottom', '1', '', 'yes' );
			add_option( 'reservations_border_side', '1', '', 'yes' );
			add_option( 'reservations_colorbackgroundfree', '#ea1700', '', 'yes' );
			add_option( 'reservations_fontcoloriffull', '#ffffff', '', 'yes' );
			add_option( 'reservations_fontcolorifempty', '#dadada', '', 'yes' );
			add_option( 'reservations_colorborder', '#070200', '', 'yes' );
		}
		if(!get_option('reservations_price_per_persons') OR !get_option('reservations_special_offer_cat')){
			add_option( 'reservations_price_per_persons', '0', '', 'yes' ); 
			add_option( 'reservations_on_page', '10', '', 'yes' ); 
			add_option( 'reservations_room_category', '', '', 'yes' ); 
			add_option( 'reservations_special_offer_cat', '', '', 'yes' ); 
			add_option( 'reservations_currency', '&euro;', '', 'yes' );
			add_option( 'reservations_support_mail', '', '', 'yes' ); 
		}

		global $wpdb;
		$table_name = $wpdb->prefix . "reservations";
		if($wpdb->get_var($table_name) != $table_name) {
			$sql = "CREATE TABLE $table_name(
			id int(10) NOT NULL AUTO_INCREMENT,
			arrivalDate date NOT NULL,
			name varchar(35) NOT NULL,
			phone varchar(30) NOT NULL,
			email varchar(50) NOT NULL,
			notes text NOT NULL,
			nights varchar(5) NOT NULL,
			dat varchar(8) NOT NULL,
			approve varchar(3) NOT NULL,
			room varchar(8) DEFAULT NULL,
			roomnumber varchar(8) NOT NULL,
			number varchar(3) NOT NULL,
			special varchar(8) NOT NULL,
			UNIQUE KEY id (id));";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
}
////////////////////////////////////////////////////////////////// END OF MAIN FUNTIONS /////////////////////////////////////////////////////////////

		 function easyreservations_price_calculation($id) { //This is for calculate price just from the reservation ID
			global $wpdb;
			$reservation = "SELECT id, room, special, arrivalDate, nights, email, number FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1";
			$res = $wpdb->get_results( $reservation );
			$price=0; // This will be the Price
			$countpriceadd=0; // Count times (=days) a sum is add to price
			$numberoffilter=0; // Count of Filter
			// Calculate Price for Rooms for each day

				if($res[0]->special=="0" OR $res[0]->special==""){  $getfilters = spliti("\[|\] |\]", get_post_meta($res[0]->room, 'reservations_filter', true)); $roomoroffer=$res[0]->room; $roomoroffertext=__( 'Room' , 'easyReservations' ); }
				if($res[0]->special!="0" AND $res[0]->special!=""){  $getfilters = spliti("\[|\] |\]", get_post_meta($res[0]->special, 'reservations_filter', true)); $roomoroffer=$res[0]->special; $roomoroffertext=__( 'Offer' , 'easyReservations' ); }

				$filterouts=array_values(array_filter($getfilters)); //make array out of filters
				$countfilter=count($filterouts);// count the filter-array elements

				foreach($filterouts as $filterout){ //foreach filter array
				$numberoffilter++; //count filters
				$filtertype=explode(" ", $filterout);
					if($filtertype[0]=="price"){ //Check Price Filter

						if(preg_match("/^[0-9][0-9].[0-9][0-9].[0-9][0-9][0-9][0-9]-[0-9][0-9].[0-9][0-9].[0-9][0-9][0-9][0-9]$/", $filtertype[1])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition
								$explodedates=explode("-", $filtertype[1]);
								$arivaldattes=strtotime($res[0]->arrivalDate);
									for($count = 1; $count < $res[0]->nights; $count++){
										$specialexplodes=explode("-", $filtertype[2]);
										foreach($specialexplodes as $specialexplode){
											if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
												$priceroomexplode=explode(":", $specialexplode);
												if($priceroomexplode[0]==$res[0]->room){
													$arivaldattes+=86400;
													if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])){ $price+=$priceroomexplode[1]; $countpriceadd++;  }
												}
											}
										}
										if(preg_match("/^[0-9]+$/", $filtertype[2]) OR preg_match("/^[0-9]+.[0-9]+$/", $filtertype[2])){ //If Filter Value is XX
											$arivaldattes+=86400;
											if(date("d.m.Y", $arivaldattes) >= $explodedates[0] AND date("d.m.Y", $arivaldattes) <= $explodedates[1]){ $price+=$filtertype[2]; $countpriceadd++; }
										}
									}
									unset($filterouts[$numberoffilter-1]); //Remove Filter from Filter array to speed up later foreach
						}

						if(preg_match("/^[0-9][0-9].[0-9][0-9].[0-9][0-9][0-9][0-9]$/", $filtertype[1])){ // If Price filter with dd.mm.yyyy Condition
								$explodedates=explode("-", $filtertype[1]);
									$specialexplodes=explode("-", $filtertype[2]);
									foreach($specialexplodes as $specialexplode){
										if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
											$priceroomexplode=explode(":", $specialexplode);
											if($priceroomexplode[0]==$res[0]->room){
												$arivaldattes=strtotime($res[0]->arrivalDate);
												for($count = 0; $count < $res[0]->nights; $count++){
													$arivaldattes+=86400;
													if(date("d.m.Y", $arivaldattes) == date("d.m.Y", strtotime($explodedates[0]))){ $price+=$priceroomexplode[1]; $countpriceadd++; }
												}
											}
										}
									}
									if(preg_match("/^[0-9]+$/", $filtertype[2]) OR preg_match("/^[0-9]+.[0-9]+$/", $filtertype[2])){ //If Filter Value is XX
										$arivaldattes=strtotime($res[0]->arrivalDate);
										for($count = 1; $count < $res[0]->nights; $count++){
											$arivaldattes+=86400;
											if(date("d.m.Y", $arivaldattes) == $explodedates[0]){ $price+=$filtertype[2]; $countpriceadd++; }
										}
									}
									unset($filterouts[$numberoffilter-1]); //Remove Filter from Filter array to speed up later foreach
						}
					}
				}

				while($countpriceadd < $res[0]->nights){
					if(preg_match("/^[0-9]+$/", get_post_meta($roomoroffer, 'reservations_groundprice', true)) OR preg_match("/^[0-9]+.[0-9]+$/", get_post_meta($roomoroffer, 'reservations_groundprice', true))){
						$price+=get_post_meta($roomoroffer, 'reservations_groundprice', true); 
					} else {
						$specialexploder=explode("-", get_post_meta($roomoroffer, 'reservations_groundprice', true));
						foreach($specialexploder as $specialexplode){
							if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
								$specialroomexplode=explode(":", $specialexplode);
								if($res[0]->room == $specialroomexplode[0]){
									$price+=$specialroomexplode[1]; // Calculate price for permamently Price
									//$exactlyprice[] .= array('date'=>date("d.m.Y", strtotime($res[0]->arrivalDate)+($countpriceadd*86400)), 'priceday'=>$specialroomexplode[1], 'priceall'=>$specialroomexplode[1]);
								}
							}
						}
					}
					$countpriceadd++;
				}

				if(get_option('reservations_price_per_persons') == '1') { $price=$price*$res[0]->number; } // Calculate Price if  "Calculate per person"  was choosed

				if(count($filterouts) >= 1){  //IF Filter array has elemts left they should be Discount Filters or nonsense
					$numberoffilter++;
					$staywasfull=0;
					$loyalwasfull=0;
					arsort($filterouts); // Sort rest of array with high to low
					foreach($filterouts as $filterout){
					$filtertype=explode(" ", $filterout);

						if($filtertype[0]=="stay"){// Stay Filter
							if($staywasfull==0){
								if($filtertype[1] < $res[0]->nights){
									if(substr($filtertype[2], -1) == "%"){
										$percent=$price/100*str_replace("%", "", $filtertype[2]);
										$discount+=$percent; 
									} else {
										$discount+=$filtertype[2];
									}
								$staywasfull++;
								}
							}
						}

						if($filtertype[0]=="loyal"){// Loyal Filter
							$items1 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='".$res[0]->email."' AND arrivalDate + INTERVAL 1 DAY < NOW()")); //number of total rows in the database

							if($loyalwasfull==0){
								if($filtertype[1] <= $items1){
									if(substr($filtertype[2], -1) == "%"){
										$percent=$price/100*str_replace("%", "", $filtertype[2]);
										$discount+=$percent;
									} else {
										$discount+=$filtertype[2];
									}
								$loyalwasfull++;
								}
							}
						}
					}
				}
				$price-=$discount;
				$price=str_replace(".", ",", $price);
				if($price <= 0) $price="Check ".__(get_the_title($roomoroffer))."'s Price/Filter ";
				return $price;
				//return array('price'=>$price, 'getusage'=>$exactlyprice);
		 }

		function reservations_check_availibility($id, $arrivalDate, $nights, $room){ //Check if a Room or Offer is Avail or Full
			global $wpdb;
			$errox=0;
			if($id!=0){ $getfilters = spliti("\[|\] |\]", get_post_meta($id, 'reservations_filter', true));
			} else { $getfilters = spliti("\[|\] |\]", get_post_meta($room, 'reservations_filter', true)); }
			$filterouts=array_values(array_filter($getfilters));

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);
				if($filtertype[0]=='avail'){
					$explodedates=explode("-", $filtertype[1]);
					$arivaldattes=strtotime($arrivalDate);
					for($count = 0; $count < $nights; $count++){
						$arivaldattes+=86400;
						if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])){ $errox++; }
					}
				}
			}
			$arivaldattes2=strtotime($arrivalDate);
			for($counti = 0; $counti < $nights; $counti++){

			$arivaldattes3=date("Y-m-d", $arivaldattes2);
			$countroomondate = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE room='$room' AND approve='yes' AND  roomnumber != '' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ")); // number of total Reservations on day in Room in the database

			$arivaldattes2+=86400;
			if($countroomondate >= get_post_meta($room, 'roomcount', true)){ $errox++; }
			}
			
			return $errox;
		}

		function reservations_check_availibility_for_room($roomid, $date){ //Check if a Room or Offer is Avail or Full
			global $wpdb;
			$errox=0;
			$getfilters = spliti("\[|\] |\]", get_post_meta($roomid, 'reservations_filter', true));
			$filterouts=array_values(array_filter($getfilters));

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);
				if($filtertype[0]=='avail'){
					$explodedates=explode("-", $filtertype[1]);
					$arivaldattes=strtotime($date);
					if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])){ $errox++; }
				}
			}
			$arivaldattes2=strtotime($date);

			$arivaldattes3=date("Y-m-d", $arivaldattes2);
			$countroomondate = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE room='$roomid' AND approve='yes' AND  roomnumber != '' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - Interval 1 DAY ")); // number of total Reservations on day in Room in the database

			if($countroomondate >= get_post_meta($roomid, 'roomcount', true)){ $errox++; }

			
			return $errox;
		}

		function reservations_get_highest_roomcount(){ //Get highest Count of Room
			global $wpdb;

			$gethighroomcount = "SELECT meta_value FROM ".$wpdb->prefix ."postmeta WHERE meta_key='roomcount' ORDER BY meta_value DESC LIMIT 1"; // Get Higest Roomcount
			$res = $wpdb->get_results( $gethighroomcount );
			return $res[0]->meta_value;
		}

		function reservations_get_room_ids(){ //Get the IDs of the Rooms in XX, XX, XX for helping people to find it.
			global $wpdb;

			$args=array( 'category' => get_option('reservations_room_category'), 'post_type' => 'post', 'post_status' => 'publish', );

			$getids = get_posts($args);
			$num=0;
			foreach($getids as $getid){
				$num++;
				$theroomidsarray[] = array($getid->ID, $getid->post_title);
			}
			return $theroomidsarray;
		}
		
		function reservations_check_type($id){
			global $wpdb;

			$checktype = "SELECT approve FROM ".$wpdb->prefix ."reservations WHERE id='$id'"; // Get Higest Roomcount
			$res = $wpdb->get_results( $checktype );

			if($res[0]->approve=="yes") $istype=__( 'approved' , 'easyReservations' );
			if($res[0]->approve=="no") $istype=__( 'rejected' , 'easyReservations' );
			if($res[0]->approve=="del") $istype=__( 'trashed' , 'easyReservations' );
			if($res[0]->approve=="") $istype=__( 'pending' , 'easyReservations' );

			return $istype;
		}

		function reservations_get_administration_links($id, $where){ //Get Links for approve, edit, trash, delete, view...
			
			$countits=0;
			if($where != "approve" AND reservations_check_type($id) != __("approved")) { $administration_links='<a href="admin.php?page=reservations&approve='.$id.'">Approve</a>'; $countits++; }
			if($countits > 0){ $administration_links.=' | '; $countits=0; }
			if($where != "reject" AND reservations_check_type($id) != "rejected") { $administration_links.='<a href="admin.php?page=reservations&delete='.$id.'">Reject</a>'; $countits++; }
			if($countits > 0){ $administration_links.=' | '; $countits=0; }
			if($where != "edit") { $administration_links.='<a href="admin.php?page=reservations&edit='.$id.'">Edit</a>'; $countits++; }
			if($countits > 0){ $administration_links.=' | '; $countits=0; }
			if($where != "trash" AND reservations_check_type($id) != "trashed") { $administration_links.='<a href="admin.php?page=reservations&bulkArr[]='.$id.'&bulk=2">Trash</a>'; $countits++; }
		
			return $administration_links;
		}
?>