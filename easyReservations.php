<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.feryaz.com
Description: easyReservation is the a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Version: 1.0.1
Author: Feryaz Beer
Author URI: http://www.feryaz.com/easyreservations/
*/

add_action('admin_menu', 'reservation_add_pages');

require_once(dirname(__FILE__)."/easyReservations_administration.php");

require_once(dirname(__FILE__)."/easyReservations_post_admin_widget.php");

require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");


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

if($page == 'reservations' OR $page== 'settings' OR $page== 'statistics'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'plugin_load_style');
}

function plugin_load_checkbox() {  //  Load Scripts and Styles
        $ScriptFile1 = WP_PLUGIN_URL . '/easyreservations/js/checkbox.js';
        $ScriptFile3 = WP_PLUGIN_URL . '/easyReservations/js/jquery.tools.min.js';

		wp_register_script('checkbox', $ScriptFile1);
		wp_register_script('jquerytools', $ScriptFile3);

		wp_enqueue_script( 'checkbox');
		wp_enqueue_script('jquerytools');

}

if($page == 'reservations'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'plugin_load_checkbox');
}

function plugin_load_colorpick() {  //  Load Scripts and Styles
        $ScriptFile4 = WP_PLUGIN_URL . '/easyreservations/js/mColorPicker_min.js';
		$ScriptFile3 = WP_PLUGIN_URL . '/easyReservations/js/jquery.tools.min.js';

		wp_register_script('jquerytools', $ScriptFile3);
		wp_register_script('colorpick', $ScriptFile4);
		
		wp_enqueue_script('colorpick');
		wp_enqueue_script('jquerytools');
}

if($page == 'settings'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'plugin_load_colorpick');
}

function stylechained_load() {  //  Load Scripts and Styles for datepicker
        $myStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';
        $ScriptFile6 = WP_PLUGIN_URL . '/easyreservations/js/jquery-ui.min.js';
        $ScriptFile7 = WP_PLUGIN_URL . '/easyreservations/js/jquery.min.js';

		wp_register_style('jquery-ui-css', $myStyleUrl);
		wp_register_script('jquery-ui-mini', $ScriptFile6);
		wp_register_script('jquery-mi-fulli', $ScriptFile7);

		wp_enqueue_style( 'jquery-ui-css');
		wp_enqueue_script( 'jquery-ui-mini');
		wp_enqueue_script( 'jquery-mi-fulli');
}

if($page == 'add-reservation'){  //  Only load Styles and Scripts on add Reservation 
add_action('admin_init', 'stylechained_load');
}

function reservation_add_pages() {  //  Add Pages Admincenter and Order them

    add_menu_page(__('Reservation','menu-test'), __('Reservations','menu-test'), 'manage_options', 'reservations', 'reservation_main_page' );

	add_submenu_page('reservations', __('Add Reservation','menu-test'), __('Add Reservation','menu-test'), 'manage_options', 'add-reservation', 'reservation_add_reservaton');

	add_submenu_page('reservations', __('Settings','menu-test'), __('Settings','menu-test'), 'manage_options', 'settings', 'reservation_settings_page');
}

register_activation_hook(__FILE__, 'easyreservation_install');
global $wpdb;

function easyreservation_install() { // Install Plugin Database
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
		if(!get_option('reservation_overview_showgeneraly') OR !get_option('reservation_form_phone')){
			add_option( 'reservation_overview_showgeneraly', '1', '', 'yes' );
			add_option( 'reservation_form_address', '0', '', 'yes' );
			add_option( 'reservation_form_nights', '0', '', 'yes' );
			add_option( 'reservation_form_special', '1', '', 'yes' );
			add_option( 'reservation_form_phone', '1', '', 'yes' );
		}
		if(!get_option('reservations_price_per_persons') OR !get_option('reservations_special_offer_cat')){
			add_option( 'reservations_price_per_persons', '0', '', 'yes' ); 
			add_option( 'reservations_on_page', '10', '', 'yes' ); 
			add_option( 'reservations_room_category', '', '', 'yes' ); 
			add_option( 'reservations_special_offer_cat', '', '', 'yes' ); 
		}
		if(!get_option('reservations_support_mail') OR !get_option('reservation_season2')){
			add_option( 'reservations_currency', '&euro;', '', 'yes' );
			add_option( 'reservations_support_mail', '', '', 'yes' ); 
			add_option( 'reservation_season1', '', '', 'yes' ); 
			add_option( 'reservation_season2', '', '', 'yes' ); 
			add_option( 'reservation_season3', '', '', 'yes' ); 
			add_option( 'reservation_season4', '', '', 'yes' ); 
			add_option( 'reservation_season5', '', '', 'yes' ); 	
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
		
		 function easyreservations_price_calculation($id) {
			global $wpdb;
			$reservation = "SELECT id, room, special, arrivalDate, nights FROM ".$wpdb->prefix ."reservations WHERE id='$id'";
			$res = $wpdb->get_results( $reservation );
			$price=0;
			$countpriceadd=0;
			$numberoffilter=0;
			
			// Calculate Price for Rooms for each day 

				if($res[0]->special=="0") { 
				
				$roomprice=explode("-", get_post_meta($res[0]->room, 'price', true)); 
				$season1 = explode("-", get_option("reservation_season1"));
				$season2 = explode("-", get_option("reservation_season2"));
				$season3 = explode("-", get_option("reservation_season3"));
				$season4 = explode("-", get_option("reservation_season4"));
				$season5 = explode("-", get_option("reservation_season5"));
				$arivaldatte=strtotime($res[0]->arrivalDate);
				
				for($count = 0; $count < $res[0]->nights; $count++){
				$arivaldatte=$arivaldatte+86400;
				
					if($arivaldatte >= strtotime($season1[0]) AND $arivaldatte <= strtotime($season1[1])) { $price+=$roomprice[0]; }
					if($arivaldatte >= strtotime($season2[0]) AND $arivaldatte <= strtotime($season2[1])) { $price+=$roomprice[1]; }
					if($arivaldatte >= strtotime($season3[0]) AND $arivaldatte <= strtotime($season3[1])) { $price+=$roomprice[2]; }
					if($arivaldatte >= strtotime($season4[0]) AND $arivaldatte <= strtotime($season4[1])) { $price+=$roomprice[3]; }
					if($arivaldatte >= strtotime($season5[0]) AND $arivaldatte <= strtotime($season5[1])) { $price+=$roomprice[4]; }
					
				}
				
			} else { // Calculate Price for Special Offer 

				$specialprices=explode("-", get_post_meta($res[0]->special, 'price', true)); 
					foreach($specialprices as $specialprice) {
						$specialp=explode(":", $specialprice);
							if($res[0]->room==$specialp[0]) $price = $specialp[1] * $res[0]->nights;
						}

			}
			if(get_option('reservations_price_per_persons') == '1') { $price=$price*$res[0]->person; } // Calculate Price when choosing "Calculate per person" 
			if($price=="0") $price="Set Price<br>or Season";
			return $price;
		 }
?>