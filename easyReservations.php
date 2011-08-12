<?php
/*
Plugin Name: easyReservation
Plugin URI: http://www.feryaz.com/easyReservations/
Description: easyReservation is the a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Author: Feryaz Beer
Author URI: http://www.feryaz.com
*/

add_action('admin_menu', 'reservation_add_pages');

require_once(dirname(__FILE__)."/easyReservations_post_admin_widget.php");

require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");

require_once(dirname(__FILE__)."/easyReservations_administration.php");

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

function plugin_load() {  //  Load Scripts and Styles
        $myStyleUrl = WP_PLUGIN_URL . '/easyReservations/css/style.css';
        $ScriptFile1 = WP_PLUGIN_URL . '/easyReservations/js/checkbox.js';
        $ScriptFile2 = WP_PLUGIN_URL . '/easyReservations/js/others.js';
        $ScriptFile3 = WP_PLUGIN_URL . '/easyReservations/js/jquery.tools.min.js';
        $ScriptFile4 = WP_PLUGIN_URL . '/easyReservations/js/mColorPicker_min.js';
		
		wp_register_style('myStyleSheets', $myStyleUrl);
		wp_register_script('checkbox', $ScriptFile1);
		wp_register_script('color', $ScriptFile2);
		wp_register_script('jquerytools', $ScriptFile3);
		wp_register_script('colorpick', $ScriptFile4);

		wp_enqueue_style( 'myStyleSheets');
		wp_enqueue_script( 'checkbox');
		wp_enqueue_script( 'color');
		wp_enqueue_script('jquerytools');
		wp_enqueue_script('colorpick');
}

if(isset($_GET['page'])) { $page=$_GET['page'] ; } else $page='';

if($page == 'reservations' OR $page == 'overview' OR $page== 'settings'){  //  Only load Styles and Scripts on Reservation Admin Page 
add_action('admin_init', 'plugin_load');
}

function stylechained_load() {  //  Load Scripts and Styles for datepicker
        $myStyleUrl = WP_PLUGIN_URL . '/easyReservations/css/jquery-ui.css';
        $ScriptFile6 = WP_PLUGIN_URL . '/easyReservations/js/jquery-ui.min.js';
        $ScriptFile7 = WP_PLUGIN_URL . '/easyReservations/js/jquery.min.js';

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

    add_menu_page(__('Reservation','menu-test'), __('Reservation','menu-test'), 'manage_options', 'reservations', 'reservation_main_page' );

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
			add_option( 'reservations_support_mail', '', 'yes' ); 
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
?>