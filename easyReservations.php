<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.feryaz.com
Description: easyReservation is a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Version: 1.1.3
Author: Feryaz Beer
Author URI: http://www.feryaz.com
*/

add_action('admin_menu', 'reservation_add_pages');

require_once(dirname(__FILE__)."/easyReservations_admin_main.php");

require_once(dirname(__FILE__)."/easyReservations_admin_resources.php");

require_once(dirname(__FILE__)."/easyReservations_admin_statistics.php");

require_once(dirname(__FILE__)."/easyReservations_admin_add_reservations.php");

require_once(dirname(__FILE__)."/easyReservations_admin_settings.php");

require_once(dirname(__FILE__)."/easyReservations_admin_post_widget.php");

require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");

add_shortcode('reservations', 'reservations_shortcode');

define('RESERVATIONS_IMAGES_DIR', WP_PLUGIN_URL.'/easyreservations/images');

function my_plugin_init() {
	load_plugin_textdomain('easyReservations', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
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

function easyreservations_load_mainstyle() {  //  Load Scripts and Styles
	$myStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/style.css';
	
	wp_register_style('myStyleSheets', $myStyleUrl);

	wp_enqueue_style( 'myStyleSheets');
}

if(isset($_GET['page'])) { $page=$_GET['page'] ; } else $page='';

if($page == 'reservations' OR $page== 'settings' OR $page== 'statistics' OR $page== 'add-reservation' OR $page=='reservation-resources'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'easyreservations_load_mainstyle');
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

function reservations_scripts_resources_load() {  //  Load Scripts and Styles
	$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';

	$ScriptFilejqmin = WP_PLUGIN_URL . '/easyreservations/js/jquery.min.js';

	wp_register_script('jqmin', $ScriptFilejqmin);
	wp_enqueue_script( 'jqmin');

	wp_register_style('datestyle', $dateStyleUrl);
	wp_enqueue_style( 'datestyle');

}

if($page == 'reservation-resources'){  //  Only load Styles and Scripts on Statistics Page
add_action('admin_init', 'reservations_scripts_resources_load');
}
function my_admin_scripts() {
wp_enqueue_script('media-upload');
wp_enqueue_script('thickbox');
}

function my_admin_styles() {
wp_enqueue_style('thickbox');
}

if (isset($_GET['page']) && $_GET['page'] == 'reservation-resources') {
add_action('admin_print_scripts', 'my_admin_scripts');
add_action('admin_print_styles', 'my_admin_styles');
}

function reservations_datepicker_load() {  //  Load Scripts and Styles for datepicker
	$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';
	$ScriptFile1 = WP_PLUGIN_URL . '/easyreservations/js/checkbox.js';
	$ScriptFile3 = WP_PLUGIN_URL . '/easyreservations/js/jquery.tools.min.js';

	wp_register_script('checkbox', $ScriptFile1);
	wp_register_script('jquerytools', $ScriptFile3);
	wp_register_style('datestyle', $dateStyleUrl);

	wp_enqueue_script( 'checkbox');
	wp_enqueue_script('jquerytools');
	wp_enqueue_style( 'datestyle');
}

if($page == 'add-reservation' OR $page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
add_action('admin_init', 'reservations_datepicker_load');
}

function reservation_add_pages(){  //  Add Pages Admincenter and Order them
	$reservation_main_permission=get_option("reservations_main_permission");

    add_menu_page(__('Reservation','menu-reservations'), __('Reservations','menu-reservations'), $reservation_main_permission, 'reservations', 'reservation_main_page' );
	
	add_submenu_page('reservations', __('Resources','menu-reservations'), __('Resources','menu-reservations'), $reservation_main_permission, 'reservation-resources', 'reservation_resources_page');

	add_submenu_page('reservations', __('Add Reservation','menu-reservations'), __('Add Reservation','menu-reservations'), $reservation_main_permission, 'add-reservation', 'reservation_add_reservaton');

	add_submenu_page('reservations', __('Statistics','menu-reservations'), __('Statistics','menu-reservations'), $reservation_main_permission, 'statistics', 'reservation_statistics_page');
	
	add_submenu_page('reservations', __('Settings','menu-reservations'), __('Settings','menu-reservations'), $reservation_main_permission, 'settings', 'reservation_settings_page');
	
}
//delete_option('reservations_db_version' );
add_option('reservations_db_version', '1.1.1', '', 'yes' );
$easyreservations_ver="1.1.3";
$installed_ver=get_option("reservations_db_version");

if( $installed_ver != $easyreservations_ver ){
	global $wpdb;
		$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations CHANGE number number VARCHAR(4) NOT NULL"));
		$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD customp TEXT NOT NULL"));
		add_option( 'reservations_overview_size', 'small', '', 'yes' );
	//easyreservation_install();
	update_option('reservations_db_version', '1.1.3');
}

if('a' == 'a'){

$prompt = 'You should eat bananas';

}

register_activation_hook(__FILE__, 'easyreservation_install');

function easyreservation_install(){ // Install Plugin Database

	$emailstandart1="New Reservation on Blogname from<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [message]";
	$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [message]";
	$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [message]";
	$formstandart.="
	[error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons Select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[custom text Phone]</p>

	<p>Address:<br>[custom text Address]</p>

	<p>Room: [rooms]</p>

	<p>Offer: [offers select]</p>	

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";
	
		if(!get_option('reservations_backgroundiffull') OR !get_option('reservations_colorborder')){
			add_option('reservations_main_permission', 'edit_posts', '', 'yes' );
			add_option( 'reservations_email_to_userapp_subj', 'Your Reservation on demo has been approved', '', 'yes' );
			add_option( 'reservations_email_to_userapp_msg', $emailstandart2, '', 'yes' );
			add_option( 'reservations_email_to_userdel_subj', 'Your Reservation on demo has been rejected', '', 'yes' );
			add_option( 'reservations_email_to_userdel_msg', $emailstandart3, '', 'yes' );
			add_option( 'reservations_email_to_admin_subj', 'New Reservation at '.get_option('blogname'), '', 'yes' );
			add_option( 'reservations_email_to_admin_msg', $emailstandart1, '', 'yes' );
			add_option( 'reservations_form', $formstandart, '', 'yes' );

			add_option( 'reservations_show_days', '26', '', 'yes' );
			add_option( 'reservations_overview_size', 'small', '', 'yes' );
			add_option( 'reservations_backgroundiffull', 'blue', '', 'yes' );
			add_option( 'reservations_border_bottom', '1', '', 'yes' );
			add_option( 'reservations_border_side', '1', '', 'yes' );
			add_option( 'reservations_colorbackgroundfree', '#ea1700', '', 'yes' );
			add_option( 'reservations_fontcoloriffull', '#ffffff', '', 'yes' );
			add_option( 'reservations_fontcolorifempty', '#969696', '', 'yes' );
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
			email varchar(50) NOT NULL,
			notes text NOT NULL,
			nights varchar(5) NOT NULL,
			dat varchar(8) NOT NULL,
			approve varchar(3) NOT NULL,
			room varchar(8) DEFAULT NULL,
			roomnumber varchar(8) NOT NULL,
			number varchar(4) NOT NULL,
			special varchar(8) NOT NULL,
			price varchar(20) NOT NULL,
			custom text NOT NULL,
			UNIQUE KEY id (id));";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		} else {
			$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD price VARCHAR(20) NOT NULL"));
			$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD custom TEXT NOT NULL"));
			
			$getallres = "SELECT id, phone, notes FROM ".$wpdb->prefix ."reservations";
			$getall = $wpdb->get_results( $getallres );
			foreach($getall as $reserv){
				$noteexp=explode("*/*", $reserv->notes);
				$reservid=$reserv->id;
				$reservphone=$reserv->phone;
				$customfieldtoadd = 'Phone&:&'.$reservphone.'&;&Address&:&'.$noteexp[1].'&;&';

				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$customfieldtoadd' WHERE id=$reservid"  ) ); 
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET notes='$noteexp[0]' WHERE id=$reservid"  ) ); 
			}

			$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations DROP phone ") ); 

			$sql_setpricequerie = "SELECT id, price FROM ".$wpdb->prefix ."reservations WHERE approve='yes'";
			$setpricequeries = $wpdb->get_results($sql_setpricequerie );
			foreach($setpricequeries as $price){
				$pricearry = easyreservations_price_calculation($price->id);
				$priceset=$pricearry['price'].';0';
				if($price->price == '') $wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET price='$priceset' WHERE id='$price->id' ") );
			}
		}
}
////////////////////////////////////////////////////////////////// END OF MAIN FUNTIONS /////////////////////////////////////////////////////////////

		 function easyreservations_price_calculation($id){ //This is for calculate price just from the reservation ID
			global $wpdb;
			$reservation = "SELECT room, special, arrivalDate, nights, email, number, price, customp FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1";
			$res = $wpdb->get_results( $reservation );
			$price=0; // This will be the Price
			$discount=0; // This will be the Dicount
			$countpriceadd=0; // Count times (=days) a sum is add to price
			$countgroundpriceadd=0; // Count times (=days) a groundprice is add to price
			$numberoffilter=0; // Count of Filter
			// Calculate Price for Rooms for each day

				if($res[0]->special=="0" OR $res[0]->special==""){  $getfilters = spliti("\[|\] |\]", get_post_meta($res[0]->room, 'reservations_filter', true)); $roomoroffer=$res[0]->room; $roomoroffertext=__( 'Room' , 'easyReservations' ); }
				if($res[0]->special!="0" AND $res[0]->special!=""){  $getfilters = spliti("\[|\] |\]", get_post_meta($res[0]->special, 'reservations_filter', true)); $roomoroffer=$res[0]->special; $roomoroffertext=__( 'Offer' , 'easyReservations' ); }

				$filterouts=array_values(array_filter($getfilters)); //make array out of filters
				$countfilter=count($filterouts);// count the filter-array elements
				$datearray[]='';

				foreach($filterouts as $filterout){ //foreach filter array
				$numberoffilter++; //count filters
				$filtertype=explode(" ", $filterout);
					if($filtertype[0]=="price"){ //Check Price Filter

						if(preg_match("/^[0-9][0-9].[0-9][0-9].[0-9][0-9][0-9][0-9]-[0-9][0-9].[0-9][0-9].[0-9][0-9][0-9][0-9]$/", $filtertype[1])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition
								$explodedates=explode("-", $filtertype[1]);
								$arivaldattes=strtotime($res[0]->arrivalDate);
									for($count = 1; $count <= $res[0]->nights; $count++){
										$specialexplodes=explode("-", $filtertype[2]);
										foreach($specialexplodes as $specialexplode){
											if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
												$priceroomexplode=explode(":", $specialexplode);
												if($priceroomexplode[0]==$res[0]->room){
													if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1]) AND !in_array($arivaldattes, $datearray)){
														$price+=$priceroomexplode[1]; $countpriceadd++;
														$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
														$datearray[]=$arivaldattes;
													}
													$arivaldattes+=86400;
												}
											}
										}
										if(preg_match("/^[0-9]+$/", $filtertype[2]) OR preg_match("/^[0-9]+.[0-9]+$/", $filtertype[2])){ //If Filter Value is XX
											if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1]) AND !in_array($arivaldattes, $datearray)){
												$price+=$filtertype[2]; $countpriceadd++; 
												$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
												$datearray[]=$arivaldattes;
											}
											$arivaldattes+=86400;
										}
									}
									unset($filterouts[$numberoffilter-1]); //Remove Filter from Filter array to speed up later foreach
						}
						elseif(preg_match("/^[0-9][0-9].[0-9][0-9].[0-9][0-9][0-9][0-9]$/", $filtertype[1])){ // If Price filter with dd.mm.yyyy Condition
								$explodedates=explode("-", $filtertype[1]);
									$specialexplodes=explode("-", $filtertype[2]);
									foreach($specialexplodes as $specialexplode){
										if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
											$priceroomexplode=explode(":", $specialexplode);
											if($priceroomexplode[0]==$res[0]->room){
												$arivaldattes=strtotime($res[0]->arrivalDate);
												for($count = 0; $count <= $res[0]->nights; $count++){
													if(date("d.m.Y", $arivaldattes) == date("d.m.Y", strtotime($explodedates[0])) AND !in_array($arivaldattes, $datearray)){
													$price+=$priceroomexplode[1]; $countpriceadd++; 
													$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
													$datearray[]=$arivaldattes;
													}
													$arivaldattes+=86400;
												}
											}
										}
									}
									if(preg_match("/^[0-9]+$/", $filtertype[2]) OR preg_match("/^[0-9]+.[0-9]+$/", $filtertype[2])){ //If Filter Value is XX
										$arivaldattes=strtotime($res[0]->arrivalDate);
										for($count = 1; $count <= $res[0]->nights; $count++){
											if(date("d.m.Y", $arivaldattes) == $explodedates[0] AND !in_array($arivaldattes, $datearray)){
											$price+=$filtertype[2]; $countpriceadd++; 
											$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
											$datearray[]=$arivaldattes;
											}
											$arivaldattes+=86400;
										}
									}
									unset($filterouts[$numberoffilter-1]); //Remove Filter from Filter array to speed up later foreach
						}
					}
				}
				$dats = $filterouts;
				foreach($filterouts as $filterout){ //foreach filter array
					$filtertype=explode(" ", $filterout);
					if(!preg_match('/loyal/i', $filtertype[0]) AND !preg_match('/stay/i', $filtertype[0]) AND !preg_match('/pers/i', $filtertype[0])){
						if(!preg_match("/^[0-9]$/", $filtertype[1])){ 
							if(preg_match('/january/i', $filtertype[1]) OR preg_match('/february/i', $filtertype[1]) OR preg_match('/march/i', $filtertype[1]) OR preg_match('/april/i', $filtertype[1]) OR preg_match('/may/i', $filtertype[1]) OR preg_match('/june/i', $filtertype[1]) OR preg_match('/july/i', $filtertype[1]) OR preg_match('/august/i', $filtertype[1]) OR preg_match('/septembre/i', $filtertype[1]) OR preg_match('/octobre/i', $filtertype[1]) OR preg_match('/novembre/i', $filtertype[1]) OR preg_match('/decembre/i', $filtertype[1]) OR preg_match('/jan/i', $filtertype[1]) OR preg_match('/feb/i', $filtertype[1]) OR preg_match('/mar/i', $filtertype[1]) OR preg_match('/apr/i', $filtertype[1]) OR preg_match('/May/i', $filtertype[1]) OR preg_match('/jun/i', $filtertype[1]) OR preg_match('/jul/i', $filtertype[1]) OR preg_match('/aug/i', $filtertype[1]) OR preg_match('/sep/i', $filtertype[1]) OR preg_match('/oct/i', $filtertype[1]) OR preg_match('/nov/i', $filtertype[1]) OR preg_match('/dec/i', $filtertype[1])){
								 $dats = preg_replace('/'.$filtertype[1].'/', '3 '.$filtertype[1], $dats);
							} elseif( preg_match('/weekend/', $filtertype[1]) OR preg_match('/week/', $filtertype[1]) OR preg_match('/weekdays/', $filtertype[1]) OR preg_match('/mon/', $filtertype[1]) OR preg_match('/monday/', $filtertype[1]) OR preg_match('/tuesday/', $filtertype[1]) OR preg_match('/tue/', $filtertype[1]) OR preg_match('/wednesday/', $filtertype[1]) OR preg_match('/wed/', $filtertype[1]) OR preg_match('/thursday/', $filtertype[1]) OR preg_match('/thu/', $filtertype[1]) OR preg_match('/friday/', $filtertype[1]) OR preg_match('/fri/', $filtertype[1]) OR preg_match('/saturday/', $filtertype[1]) OR preg_match('/sat/', $filtertype[1]) OR preg_match('/sunday/', $filtertype[1]) OR preg_match('/sun/', $filtertype[1]) ){
								 $dats = preg_replace('/'.$filtertype[1].'/', '1 '.$filtertype[1], $dats);
							} elseif(preg_match("/^[0-9][0-9][0-9][0-9];$/", $filtertype[1]) OR preg_match("/^[0-9][0-9][0-9][0-9]$/", $filtertype[1])){ 
								 $dats = preg_replace('/'.$filtertype[1].'/', '5 '.$filtertype[1], $dats);
							} elseif(preg_match("/[0-9;]+/", $filtertype[1]) OR preg_match("/^[0-9][0-9]$/", $filtertype[1])){ 
								 $dats = preg_replace('/'.$filtertype[1].'/', '2 '.$filtertype[1], $dats);
							} elseif(preg_match('/q1/i', $filtertype[1]) OR preg_match('/q2/i', $filtertype[1]) OR preg_match('/q3/i', $filtertype[1]) OR preg_match('/q4/i', $filtertype[1]) OR preg_match('/quarter1/i', $filtertype[1]) OR preg_match('/quarter2/i', $filtertype[1]) OR preg_match('/quarter3/i', $filtertype[1]) OR preg_match('/quarter4/i', $filtertype[1])){ 
								 $dats = preg_replace('/'.$filtertype[1].'/', '4 '.$filtertype[1], $dats);
							}
						}
					}
				}

				asort($dats); //sort left filters for any not "date-range" price fields
				$countleftfilters=0;
				foreach($dats as $filteroust){ //foreach filter array
					$filtertype=explode(" ", $filteroust);
						if((preg_match("/^[a-zA-Z;]+$/", $filtertype[2]) OR preg_match("/^[a-zA-Z]+$/", $filtertype[2]) OR preg_match("/^[0-9][0-9][0-9][0-9];$/", $filtertype[2]) OR preg_match("/^[0-9][0-9][0-9][0-9]$/", $filtertype[2]) OR preg_match("/^[0-9][0-9]$/", $filtertype[2]) OR preg_match("/[0-9;]+/", $filtertype[2])) AND !preg_match('/loyal/i', $filtertype[0]) AND !preg_match('/stay/i', $filtertype[0]) AND !preg_match('/pers/i', $filtertype[0])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition

								if(preg_match("/^[a-zA-Z]+$/", $filtertype[2]) OR preg_match("/^[0-9][0-9][0-9][0-9]$/", $filtertype[2]) OR preg_match("/^[0-9][0-9]$/", $filtertype[2])){
									$conditionarrays[]=$filtertype[2];
								} else {
									$explodedaynames=explode(";", $filtertype[2]);
									foreach($explodedaynames as $explodedayname){
										if($explodedayname != ''){
											$conditionarrays[]=$explodedayname;
										}
									}
								}
								
								foreach($conditionarrays as $condition){
									$arivaldattes=strtotime($res[0]->arrivalDate);
									for($count = 1; $count <= $res[0]->nights; $count++){
										$derderder=0;
										
										if($condition == 'week' OR $condition == 'weekdays'){
											if((date("D", $arivaldattes) == "Mon" OR date("D", $arivaldattes) == "Tue" OR date("D", $arivaldattes) == "Wed" OR date("D", $arivaldattes) == "Thu" OR date("D", $arivaldattes) == "Sun") AND !in_array($arivaldattes, $datearray)){
												$derderder=1;
												$daystring='Weekdays';
											}
										} elseif($condition == 'weekend'){
											if((date("D", $arivaldattes) == "Sat" OR date("D", $arivaldattes) == "Fri") AND !in_array($arivaldattes, $datearray)){
												$derderder=1;
												$daystring='Weekend';
											}
										} elseif(($condition == 'monday' OR $condition == 'mon') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Mon"){
												$derderder=1;
												$daystring='Monday';
											}
										} elseif(($condition == 'tuesday' OR $condition == 'tue') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Tue"){
												$derderder=1;
												$daystring='Tuesday';
											}
										} elseif(($condition == 'wednesday' OR $condition == 'wed') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Wed"){
												$derderder=1;
												$daystring='Wednesday';
											}
										} elseif(($condition == 'thursday' OR $condition == 'thu') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Thu"){
												$derderder=1;
												$daystring='Thursday';
											}
										} elseif(($condition == 'friday' OR $condition == 'fri') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Fri"){
												$derderder=1;
												$daystring='Friday';
											}
										} elseif(($condition == 'saturday' OR $condition == 'sat') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Sat"){
												$derderder=1;
												$daystring='Saturday';
											}
										} elseif(($condition == 'sunday' OR $condition == 'sun') AND !in_array($arivaldattes, $datearray)){
											if(date("D", $arivaldattes) == "Sun"){
												$derderder=1;
												$daystring='Sunday';
											}
										} elseif(preg_match("/^[0-9][0-9];$/", $condition) OR preg_match("/^[0-9][0-9]+$/", $condition)){
											if(date("W", $arivaldattes) == $condition AND !in_array($arivaldattes, $datearray)){
												$derderder=1;
												$daystring='Calendar Week';
											}
										} elseif(($condition == 'january' OR $condition == 'jan') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "01"){
												$derderder=1;
												$daystring='January';
											}
										} elseif(($condition == 'february' OR $condition == 'feb') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "02"){
												$derderder=1;
												$daystring='February';
											}
										} elseif(($condition == 'march' OR $condition == 'mar') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "03"){
												$derderder=1;
												$daystring='March';
											}
										} elseif(($condition == 'april' OR $condition == 'apr') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "04"){
												$derderder=1;
												$daystring='April';
											}
										} elseif(($condition == 'may' OR $condition == 'May') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "05"){
												$derderder=1;
												$daystring='May';
											}
										} elseif(($condition == 'june' OR $condition == 'jun') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "06"){
												$derderder=1;
												$daystring='June';
											}
										} elseif(($condition == 'july' OR $condition == 'jul') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "08"){
												$derderder=1;
												$daystring='July';
											}
										} elseif(($condition == 'august' OR $condition == 'aug') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "08"){
												$derderder=1;
												$daystring='August';
											}
										} elseif(($condition == 'september' OR $condition == 'sep') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "09"){
												$derderder=1;
												$daystring='September';
											}
										} elseif(($condition == 'october' OR $condition == 'oct') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "10"){
												$derderder=1;
												$daystring='October';
											}
										} elseif(($condition == 'november' OR $condition == 'nov') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "11"){
												$derderder=1;
												$daystring='November';
											}
										} elseif(($condition == 'december' OR $condition == 'dec') AND !in_array($arivaldattes, $datearray)){
											if(date("m", $arivaldattes) == "12"){
												$derderder=1;
												$daystring='December';
											}
										} elseif(($condition == 'q1' OR $condition == 'quarter1') AND !in_array($arivaldattes, $datearray)){
											if(ceil(date("m", $arivaldattes) / 3) == 1){
												$derderder=1;
												$daystring='1. Quartar';
											}
										} elseif(($condition == 'q2' OR $condition == 'quarter2') AND !in_array($arivaldattes, $datearray)){
											if(ceil(date("m", $arivaldattes) / 3) == 2){
												$derderder=1;
												$daystring='2. Quartar';
											}
										} elseif(($condition == 'q3' OR $condition == 'quarter3') AND !in_array($arivaldattes, $datearray)){
											if(ceil(date("m", $arivaldattes) / 3) == 3){
												$derderder=1;
												$daystring='3. Quartar';
											}
										} elseif(($condition == 'q4' OR $condition == 'quarter4') AND !in_array($arivaldattes, $datearray)){
											if(ceil(date("m", $arivaldattes) / 3) == 4){
												$derderder=1;
												$daystring='4. Quartar';
											}
										} elseif(preg_match("/^[0-9][0-9][0-9][0-9];$/", $condition) OR preg_match("/^[0-9][0-9][0-9][0-9]+$/", $condition)){
											if(date("Y", $arivaldattes) == $condition AND !in_array($arivaldattes, $datearray)){
												$derderder=1;
												$daystring='Year';
											}
										}

										if($derderder==1){
											$specialexplodes=explode("-", $filtertype[3]);
											foreach($specialexplodes as $specialexplode){
												if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
													$priceroomexplode=explode(":", $specialexplode);
													if($priceroomexplode[0]==$res[0]->room){
														$price+=$priceroomexplode[1]; $countpriceadd++;
														$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.$daystring);
														$datearray[]=$arivaldattes;
													}
												}
											}
											if(preg_match("/^[0-9]+$/", $filtertype[5]) OR preg_match("/^[0-9]+.[0-9]+$/", $filtertype[3])){ //If Filter Value is XX
												$price+=$filtertype[3]; $countpriceadd++; 
												$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.$daystring);
												$datearray[]=$arivaldattes;
											}
										}
										$arivaldattes += 86400;
									}
								}
								unset($filterouts[$countleftfilters]); //Remove Filter from Filter array to speed up later foreach
								$conditionarrays= '';
					}
					$countleftfilters++;
				}

				while($countpriceadd < $res[0]->nights){
					if(preg_match("/^[0-9]+$/", get_post_meta($roomoroffer, 'reservations_groundprice', true)) OR preg_match("/^[0-9]+.[0-9]+$/", get_post_meta($roomoroffer, 'reservations_groundprice', true))){
						$price+=get_post_meta($roomoroffer, 'reservations_groundprice', true);		
						$ifDateHasToBeAdded=0;
						if(isset($datearray)){ $getrightday=0; 
							while($getrightday==0){
								if(in_array(strtotime($res[0]->arrivalDate)+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
									$ifDateHasToBeAdded++;
								} else {
									$getrightday++;
								}
							}
							$datearray[]=strtotime($res[0]->arrivalDate)+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
						}

						$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>get_post_meta($roomoroffer, 'reservations_groundprice', true), 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
						$countgroundpriceadd++;
					} else {
						$specialexploder=explode("-", get_post_meta($roomoroffer, 'reservations_groundprice', true));
						foreach($specialexploder as $specialexplode){
							if(preg_match("/^[0-9]+:[0-9]+$/", $specialexplode) OR preg_match("/^[0-9]+:[0-9]+.[0-9]+$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
								$specialroomexplode=explode(":", $specialexplode);
								if($res[0]->room == $specialroomexplode[0]){
									$price+=$specialroomexplode[1]; // Calculate price for permamently Price
									$ifDateHasToBeAdded=0;
									if(isset($datearray)){ $getrightday=0;
										while($getrightday==0){
											if(in_array(strtotime($res[0]->arrivalDate)+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
												$ifDateHasToBeAdded++;
											} else {
												$getrightday++;
											}
										}
										$datearray[]=strtotime($res[0]->arrivalDate)+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
									}
									$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$specialroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
									$countgroundpriceadd++;
								}
							}
						}
					}
					$countpriceadd++;
				}
				if(get_option('reservations_price_per_persons') == '1' and $res[0]->number > 1) {  // Calculate Price if  "Calculate per person"  was choosen
					$checkprice=$price;
					$price=$price*$res[0]->number; 
					$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>$price-$checkprice, 'type'=>__( 'Price per Person' , 'easyReservations' ).' x'.$res[0]->number);
					$countpriceadd++;
				}

				if(count($filterouts) >= 1){  //IF Filter array has elemts left they should be Discount Filters or nonsense
					$numberoffilter++;
					$staywasfull=0;
					$loyalwasfull=0;
					$perswasfull=0;
					arsort($filterouts); // Sort rest of array with high to low
					foreach($filterouts as $filterout){
					$filtertype=explode(" ", $filterout);

						if($filtertype[0]=="stay"){// Stay Filter
							if($staywasfull==0){
								if($filtertype[1] <= $res[0]->nights){
									if(substr($filtertype[2], -1) == "%"){
										$percent=$price/100*str_replace("%", "", $filtertype[2]);
										$discount+=$percent; 
										$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>'-'.$percent, 'type'=>get_the_title($roomoroffer).' '.__( ' Stay Filter' , 'easyReservations' ).' '.$filtertype[2]);
									} else {
										$discount+=$filtertype[2];
										$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Stay Filter' , 'easyReservations' ));
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
										$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>'-'.$percent, 'type'=>get_the_title($roomoroffer).' '.__( ' Loyal Filter' , 'easyReservations' ).' '.$filtertype[2]);
									} else {
										$discount+=$filtertype[2];
										$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Loyal Filter' , 'easyReservations' ));
									}
								$loyalwasfull++;
								}
							}
						}

						if($filtertype[0]=="pers"){// Loyal Filter
							if($perswasfull==0){
								if($filtertype[1] <= $res[0]->number){
									if(substr($filtertype[2], -1) == "%"){
										$percent=$price/100*str_replace("%", "", $filtertype[2]);
										$discount+=$percent;
										$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>'-'.$discount, 'type'=>get_the_title($roomoroffer).' '.__( ' Persons Filter' , 'easyReservations' ).' '.$filtertype[2]);
									} else {
										$discount+=$filtertype[2];
										$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Persons Filter' , 'easyReservations' ));
									}
								$perswasfull++;
								}
							}
						}
					}
				}

				$price-=$discount; //Price minus Discount

				if($res[0]->customp != ""){
					$explodecustomprices=explode("&;&", $res[0]->customp);
					foreach($explodecustomprices as $customprice){
						if($customprice != ""){
							$custompriceexp=explode("&:&", $customprice);
							$priceasexp=explode(":", $custompriceexp[1]);
							if(substr($priceasexp[1], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $priceasexp[1]);
								$customprices+=$percent;
								$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>$percent, 'type'=>__( 'Reservation Custom Price %' , 'easyReservations' ).' '.$custompriceexp[0]);
							} else {
								$customprices+=$priceasexp[1];
								$exactlyprice[] = array('date'=>strtotime($res[0]->arrivalDate)+($countpriceadd*86400), 'priceday'=>$priceasexp[1], 'type'=>__( 'Reservation Custom Price' , 'easyReservations' ).' '.$custompriceexp[0]);
							}
						}
					}
				}

				$price+=$customprices; //Price plus Custom prices
				$price=str_replace(".", ",", $price);

				if($res[0]->price != ''){
					$pricexpl=explode(";", $res[0]->price);
					if($pricexpl[0]!=0){
						$price=$pricexpl[0];
					}
				}

				//return $price;
				return array('price'=>$price, 'getusage'=>$exactlyprice);
		 }

		 function easyreservations_get_price($id){
			$getprice=easyreservations_price_calculation($id);
			if($getprice['price'] <= 0) $rightprice=__( 'Wrong Price/Filter' , 'easyReservations' );
			else {
			$geprice=str_replace(",", ".", $getprice['price']);
			$rightprice=reservations_format_money($geprice).' &'.get_option('reservations_currency').';';
			}
			return $rightprice;
		 }

		function easyreservations_detailed_price($id){
			$pricearray=easyreservations_price_calculation($id);
			$priceforarray=$pricearray['getusage'];
			if(count($priceforarray) > 0){
				$arraycount=count($priceforarray);

				$pricetable.='<table class="widefat"><thead><tr><th colspan="4" style="border-right:1px">'.__('Detailed Price', 'easyReservations').'</th></tr></thead><tr style="background:#fff;"><td><b>'.__('Date', 'easyReservations').'</b></td><td><b>'.__('Description', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Price of Day', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Total Price', 'easyReservations').'</b></td></tr>';
				$count=0;
				$count2=0;
				$countprices=0;

					sort($priceforarray);
					foreach( $priceforarray as $pricefor){
						$count++;
						if(is_int($count/2)) $class=' class="alternate"'; else $class='';
						$date=$pricefor['date'];
						if(preg_match("/Stay/i", $pricefor['type']) or preg_match("/loyal/i", $pricefor['type']) or preg_match("/custom price/i", $pricefor['type']) or preg_match("/pers/i", $pricefor['type'])) $dateposted=' '; else $dateposted=date("d.m.Y", $date); 
						$datearray.="".date("d.m.Y", $date)." ";
						$pricetotal+=$pricefor['priceday'];
						if($count==$arraycount) $onlastprice=' style="border-bottom: double 3px #000000;"';  else $onlastprice='';
						$pricetable.= '<tr'.$class.'><td nowrap>'.$dateposted.'</td><td nowrap>'.$pricefor['type'].'</td><td style="text-align:right;" nowrap>'.reservations_format_money($pricefor['priceday']).' &'.get_option('reservations_currency').';</td><td style="text-align:right;" nowrap><b'.$onlastprice.'>'.reservations_format_money($pricetotal).' &'.get_option('reservations_currency').';</b></td></tr>';
						unset($priceforarray[$count-1]);
					}

				$pricetable.='</table>';
			} else $pricetable = 'Critical Error #1023462';

			return $pricetable;
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
			$arivaldattes3=date("Y-m-d", strtotime($date));
			$countroomondate = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE room='$roomid' AND approve='yes' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - Interval 1 DAY ")); // number of total Reservations on day in Room in the database
			if($countroomondate > 0){ $errox++; }
			
			return $errox;
		}

		function reservations_get_highest_roomcount(){ //Get highest Count of Room
			global $wpdb;

			$gethighroomcount = "SELECT meta_value FROM ".$wpdb->prefix ."postmeta WHERE meta_key='roomcount' ORDER BY meta_value DESC LIMIT 1"; // Get Higest Roomcount
			$res = $wpdb->get_results( $gethighroomcount );
			return $res[0]->meta_value;
		}

		function reservations_get_room_ids(){ //Get the IDs of the Room Posts in array for helping people to find it.
			global $wpdb;
			$args=array( 'category' => get_option('reservations_room_category'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC' );


			$getids = get_posts($args);
			foreach($getids as $getid){
				$theroomidsarray[] = array($getid->ID, $getid->post_title);
			}
			return $theroomidsarray;
		}

		function reservations_get_offer_ids(){ //Get the IDs of the Offer Posts in array for helping people to find it.
			global $wpdb;

			$args=array( 'category' => get_option('reservations_special_offer_cat'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC' );

			$getids = get_posts($args);
			foreach($getids as $getid){
				$theofferidsarray[] = array($getid->ID, $getid->post_title);
			}
			return $theofferidsarray;
		}

		function reservations_check_type($id){
			global $wpdb;

			$checktype = "SELECT approve FROM ".$wpdb->prefix ."reservations WHERE id='$id'"; // Get Higest Roomcount
			$res = $wpdb->get_results( $checktype );

			if($res[0]->approve=="yes") $istype=__( 'approved' , 'easyReservations' );
			elseif($res[0]->approve=="no") $istype=__( 'rejected' , 'easyReservations' );
			elseif($res[0]->approve=="del") $istype=__( 'trashed' , 'easyReservations' );
			elseif($res[0]->approve=="") $istype=__( 'pending' , 'easyReservations' );

			return $istype;
		}

		function reservations_check_pay_status($id){
			global $wpdb;

			$checkpaid = "SELECT price FROM ".$wpdb->prefix ."reservations WHERE id='$id'"; // Get Higest Roomcount
			$res = $wpdb->get_results( $checkpaid  );
			$explodetheprice = explode(";", $res[0]->price);
			if($explodetheprice[1] == 1) $ispayed=__( 'paid' , 'easyReservations' );
			elseif($explodetheprice[1] == 0) $ispayed=__( 'not unpaid' , 'easyReservations' );

			return $ispayed;
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

		function reservations_format_money($amount,$separator=true,$simple=false){
			return
			(true===$separator?
				(false===$simple?
					number_format($amount,2,',','.'):
					str_replace(',00','',money($amount))
				):
				(false===$simple?
					number_format($amount,2,',',''):
					str_replace(',00','',money($amount,false))
				)
			);
		}

		function reservations_check_if_cat_is_child($id){
			$count=0;
			$breadcrumbs = explode('|',get_category_parents($id,true,'|'));
			if(isset($breadcrumbs[2])) $count++;
			return $count;
		}
		
		function reservations_is_room($id){
			$category=get_the_category($id);
			$roomcategory=get_option('reservations_room_category');
			if($category[0]->cat_ID == $roomcategory) return true;
			else return false;
		}

		function reservations_get_room_options(){
			$room_args = array( 'post_status' => 'publish|private', 'category' => get_option("reservations_room_category"), 'orderby' => 'post_title', 'order' => 'ASC');
			$roomcategories = get_posts( $room_args );
			foreach( $roomcategories as $roomcategorie ){
				$rooms_options .= '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
			}
			return $rooms_options;
		}

		function reservations_get_offer_options(){
			$room_args = array( 'post_status' => 'publish|private', 'category' => get_option("reservations_special_offer_cat"), 'orderby' => 'post_title', 'order' => 'ASC');
			$roomcategories = get_posts( $room_args );
			foreach( $roomcategories as $roomcategorie ){
				$rooms_options .= '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
			}
			return $rooms_options;
		}

		function reservations_get_category_count($input = ''){
			global $wpdb;
			if($input == ''){
				$category = get_the_category();
				return $category[0]->category_count;
			}
			elseif(is_numeric($input)){
				$SQL = "SELECT $wpdb->term_taxonomy.count FROM $wpdb->terms, $wpdb->term_taxonomy WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id AND $wpdb->term_taxonomy.term_id=$input";
				return $wpdb->get_var($SQL);
			}
			else	{
				$SQL = "SELECT $wpdb->term_taxonomy.count FROM $wpdb->terms, $wpdb->term_taxonomy WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id AND $wpdb->terms.slug='$input'";
				return $wpdb->get_var($SQL);
			}
		}
		
		add_filter('mce_external_plugins', "tinyplugin_register");
		add_filter('mce_buttons', 'tinyplugin_add_button', 0);

		function tinyplugin_add_button($buttons){
			array_push($buttons, "separator", "tinyplugin");
			return $buttons;
		}

		function tinyplugin_register($plugin_array){
			$url = WP_PLUGIN_URL . '/easyreservations/js/editor_plugin.js';

			$plugin_array['tinyplugin'] = $url;
			return $plugin_array;
		}

?>