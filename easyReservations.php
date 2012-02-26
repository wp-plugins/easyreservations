<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.feryaz.com
Description: easyReservation is a Reservations or Booking Plugin for Websites with rentable content. It grants you a fast, structured and detailed overview of your Reservations. For help read the Dokumentation on the Pulgin Page.
Version: 1.2
Author: Feryaz Beer
Author URI: http://www.feryaz.com
License:GPL2
*/

add_action('admin_menu', 'easyReservations_add_pages');
/*

	get files


*/
require_once(dirname(__FILE__)."/easyReservations_admin_main.php");

require_once(dirname(__FILE__)."/easyReservations_admin_resources.php");

require_once(dirname(__FILE__)."/easyReservations_admin_statistics.php");

require_once(dirname(__FILE__)."/easyReservations_admin_settings.php");

require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");

require_once(dirname(__FILE__)."/lib/widgets/form_widget.php");

require_once(dirname(__FILE__)."/easyReservations_edit_shortcode.php");

require_once(dirname(__FILE__)."/easyReservations_calendar_shortcode.php");

if(file_exists(dirname(__FILE__).'/lib/plugins/paypal/paypal.php')){
	require_once(dirname(__FILE__)."/lib/plugins/paypal/paypal.php");
}

/*

	add shortcodes


*/
add_shortcode('easy_calendar', 'reservations_calendar_shortcode');
add_shortcode('easy_edit', 'reservations_edit_shortcode');
add_shortcode('easy_form', 'reservations_form_shortcode');


/*

	get files


*/
define('RESERVATIONS_IMAGES_DIR', WP_PLUGIN_URL.'/easyreservations/images');
define('RESERVATIONS_LIB_DIR', WP_PLUGIN_URL.'/easyreservations/lib/');
define('RESERVATIONS_JS_DIR', WP_PLUGIN_URL.'/easyreservations/js');
define('RESERVATIONS_STYLE', get_option("reservations_style"));
//add_filter('widget_text', 'do_shortcode'); //enable shortcodes in widgets

function easyReservations_init() {
	load_plugin_textdomain('easyReservations', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
}
add_action('init','easyReservations_init');
add_action('admin_init','easyReservations_init');

function easyReservations_admin_bar() {
	global $wp_admin_bar, $wpdb;

	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) as Num FROM ".$wpdb->prefix ."reservations WHERE approve=''"));		

	if($count!=0) $c="<span id=\"ab-awaiting-mod\" class=\"pending-count\">".$count."</span>"; else $c ="";
	$wp_admin_bar->add_menu( array(
		'id' => 'reservations',
		'title' => __('Reservations '.$c.''),
		'href' => admin_url( 'admin.php?page=reservations&typ=pending')
	) );
}

add_action( 'wp_before_admin_bar_render', 'easyReservations_admin_bar' );
function easyReservations_add_help_tabs () {
    $screen = get_current_screen();
    if ( $screen->id != 'toplevel_page_reservations' )
        return;

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
        'id'	=> 'easyReservations_help_1',
        'title'	=> __('Overview', 'easyReservations'  ),
        'content'	=> '<p><b><u>'.__('Reservation Overview', 'easyReservations').'</u></b><br>'.__('The overview is the visual output of the availability of all reservations in a clean, informative and inuitive way. It shows the availabilty of your rooms in a flexible time-period - the rooms and number of days to show are selectable. With a click on the date-icon in the header you can select the start-date of the overview in a datepicker. The overview cells have different backgrounds: White for normal day, yellow for weekend, blue for today and red for unavail. In addition to this the past days have a pattern over the background. Over this the reservations are shown in different colors to see directly if its a past reservation (blue), a current one (green) or a futre one (red). If you interact with a reservation it will be yellow. For each day and room there is the number of free spaces in the room-seperator, so you know directly if theres enough room or not. If you go over the cells the room and date cells get highlighted and the full date is shown in the header. Beside of this the overview is interactiv. That means you can click on it to add or edit reservations date and room very inuitive. On approve or edit you can select or change the room directly by clicking on the releated first cell (that one with the room number in it), so the date wont get changed.', 'easyReservations').'</p><p><b><u>'.__('Reservation Table', 'easyReservations').'</u></b><br>'.__('The reservations table is an detailed, flexible and ordered list of your reservations. Its divided in different types: Active for approved and current or future reservations, pending for unapproved ones, rejected, old, trashed or all resservatons. The table is filterable by month, room and offer. It has a pagination function and you can select how many results should be shown per page. Further it has a search function to search for name, email, arrival Date or note. It has the bulk actions move-to-trash, restore or delete-permanently. It shows the informations name, date, nights, eMail, persons, room, offer, note, price and link to admin actions like edit or approve. The price will be red for unpaid, orange for partiatially paid and green for fully paid.', 'easyReservations').'</p><p><b><u>'.__('Statistics and Export', 'easyReservations').'</u></b><br>'.__('The statistics display the reservations of the next few days and how much reservations happend in the past few days. With the export function you can get a .csv or. xls file with all the informations from the reservations. You can select if only the reservations from the table, all reservations or a collection of reservations by time-period and/or type gets in it. Further you can select which of the informations come in.', 'easyReservations' ).'</p>',
    ) );
    $screen->add_help_tab( array(
        'id'	=> 'easyReservations_help_2',
        'title'	=> __('Resources', 'easyReservations'  ),
        'content'	=> '<p><b><u>'.__('Resources', 'easyReservations').'</u></b><br>'.__('A resource can be a room or an offer. A reservation can have none offer but a room is necessary. If an offer gets choosen only the offers price for the room goes into calculation. Resources get saved as private posts either in the rooms or the offers category.', 'easyReservations').'</p><p><b><u>'.__('Rooms', 'easyReservations').'</u></b><br>'.__( 'A room is more a "type of room". So you dont have to add each room itself, you just add the rooms with different price-settings and set the room-count.', 'easyReservations' ).'</p><p><b><u>'.__('Offers', 'easyReservations').'</u></b><br>'.__( 'Like said offers arnt necesarry and you could just dont use them at all. To do this just delete the [offer *] from the forms and replace it with a [hidden offer 0] field. An offer can have a different price for each room. Usually the offers are selectable in forms like rooms, but you can set the display-style to "box" too. This is a bit complicated but can have a great effort. You have to set the offers post to public, descripe your offer in the post-content and add a link to a form with the [offer box] field in it. Just if the guest come througth this link to the form-page he sees the offer in a box above the form and can only deselect it. In this way the Guests dont get attention of other offers (or offers at all) and you may get more money.', 'easyReservations' ).'</p><p><b><u>'.__('Filters', 'easyReservations').'</u></b><br>'.__( 'Filters are the most complicated and powerfull thing in this plugin. Theyre to change the resources price, add an special discount or set a resource unavailable for a time-period.', 'easyReservations' ).' <a target="_blank" href="http://www.feryaz.de/dokumentation/filters/">read more</a></p>',
    ) );
    $screen->add_help_tab( array(
        'id'	=> 'easyReservations_help_3',
        'title'	=> __('Forms', 'easyReservations'  ),
        'content'	=> '<p><b><u>'.__('Form shortcode', 'easyReservations').'</u></b><br>'.__('The forms have the function to get the reservations from your guests. Theyre unlimited and very customizable throught HTML and tags. Its recomment to add the calendar shortcode to the same page or post as the form.', 'easyReservations').'</p><p>'.__('This tags can be deleted from forms, the others are required:', 'easyReservations').' <code>[error]</code>,  <code>[show_price]</code>, <code>[country]</code>, <code>[message]</code>.</p><p><b><u>'.__('Post form in a resource post', 'easyReservations').'</u></b><br>'.__('To include a form directly to a room/offer post you will need to remove the [room]/[offer] from the Form and add a hidden room/offer field.', 'easyReservations').'</p><p><b><u>'.__('Hidden fields', 'easyReservations').'</u></b><br>'.__('With hidden fields you can fix a information, like the depature date or the room, to a form. In order to get this work you have to delete the normal tag of the information ( e.g. [rooms] for a room) and every reservation who comes throught whis form will have it selected. Perfectly if you want to have a form for just a special weekend for example.', 'easyReservations').'</p><p><b><u>'.__('Custom and price fields', 'easyReservations').'</u></b><br>'.__('With custom fields you can add your own form elements to forms and gather all informations you need. They work as text fields, text areas, checkboxes, radio buttons and selects. Price fields are much the same but have an impact on the price of the reservation.', 'easyReservations').' <a target="_blank" href="http://www.feryaz.de/dokumentation/custom-fields/">read more</a></p>',
    ) );
    $screen->add_help_tab( array(
        'id'	=> 'easyReservations_help_4',
        'title'	=> __('Shortcodes', 'easyReservations'  ),
        'content'	=> '<p><b><u>'.__('Shortcode adding Tool - tinyMCE button', 'easyReservations').'</u></b><br>'.__('When add or editing a post or page you\'ll see a button with the easyReservations logo in the header of the editor. After clicking on it a dialog box will open and let you add each of the three shortcodes (form, user-edit and calendar) very easily.', 'easyReservations').'</p><p><b><u>'.__('Calendar', 'easyReservations').'</u></b><br>'.__('Everyone wanted, her it is: A fully flexible ajax calendar to show the availabilty of your rooms on the frontpage. It can have different styles and the price for the night can be shown in it. On start it shows the availibility of the pre-selected room. If its in the same page, post or widget like a room select it changes on select.', 'easyReservations').'</p><p><b><u>'.__('User edit', 'easyReservations').'</u></b><br>'.__('To let users edit their reservations afterwards you have to add a page with the shortcode [easy_edit]. Only add this shortcode one page. In the settings you have to enter a text that describes your guests the procedure of editing his reservation and the link to the page with the shortcode. Its recomment to add the calendar shortcode to the same page as the edit-shortcode.', 'easyReservations').'</p><p>'.__('The Guest have to enter his ID and email to see and change his reservation. I think this is secure enoought, because the user and the admin both get an email after edit. If the email changes, the old one will get a mail too.', 'easyReservations').'</p><p>'.__('The guest can edit his reservation only if the arrival date isn\'t past. After editing the reservation will reset to pending. Custom fields can be changed in a text-field, custom price fields can just get deselected.', 'easyReservations').'</p>',
    ) );

    $screen->add_help_tab( array(
        'id'	=> 'easyReservations_help_5',
        'title'	=> __('eMails', 'easyReservations'  ),
        'content'	=> '<p><b><u>'.__('eMails settings', 'easyReservations').'</u></b><br>'.__('First you have to enter the support email in the main-settings. All mails to admin will be sent to this email and all emails to guest will have it as sender.', 'easyReservations').'</p><p>'.__('Under Settings -> eMails you can view and change the value of the mails.', 'easyReservations').'</p><p><b><u>'.__('Valid Tags', 'easyReservations').'</u></b><br>'.__('Valid in all emails', 'easyReservations').': <code>&lt;br&gt;</code>, <code>[ID]</code>, <code>[thename]</code>, <code>[email]</code>, <code>[arrivaldate]</code>, <code>[departuredate]</code>, <code>[nights]</code>, <code>[persons]</code>, <code>[childs]</code>, <code>[country]</code>, <code>[rooms]</code>, <code>[offers]</code>, <code>[note]</code>, <code>[price]</code>, <code>[customs]</code></p><p>'.__('The <code>[adminmessage]</code> '.__('tag is only working on normal sendmail, approve, reject and admin-edit emails. The', 'easyReservations').' <code>[changlog]</code> '.__('tag is working after all kinds of edit', 'easyReservations').'.', 'easyReservations'  ),
    ) );
    $screen->add_help_tab( array(
        'id'	=> 'easyReservations_help_6',
        'title'	=> __('Widget', 'easyReservations'  ),
        'content'	=> '<p><b><u>'.__('Calendar and Form Widget', 'easyReservations').'</u></b><br>'.__('The new form Widget is a very nice addition to the plugin. You can choose to either show the calendar, a form or both in your themes widgetized areas. The calendar has the same options as tje shortcode, so you can determine the width, the default room or if the prices should get displayed. The Widget-form is almost as customizable as the form shortcode and the working tags are displayd and clickable-to-add in the widget options. At last you have to enter a link to a post or a page with a form with the same or more tags. The selected values will be transfered to the real form.', 'easyReservations').'</p>',
    ) );
	$screen->set_help_sidebar('<p><b>'.__('Help to improve the Plugin', 'easyReservations').':</b><br>'.__('You can', 'easyReservations').' <a target="_blank" href="http://feryaz.square7.ch/bugs/bug_report_page.php"> '.__('report bugs', 'easyReservations').'</a>, <a target="_blank" href="http://feryaz.square7.ch/bugs/bug_report_page.php"> '.__('make a suggestion', 'easyReservations').'</a> or <a target="_blank" href="http://www.feryaz.de/translate/"> '.__('translate the plugin', 'easyReservations').'</a>!</p><p>'.__('Even', 'easyReservations').' <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=D3NW9TDVHBJ9E"> '.__('donating', 'easyReservations').'</a> '.__('for the hundreds of hours i\'ve spent isn\'t impossible', 'easyReservations').' :)</p><p><strong> '.__('For more information', 'easyReservations').':</strong><br><a target="_blank" href="http://www.feryaz.de/dokumentation/">Documentation</a><br><a target="_blank" href="http://www.feryaz.de">Plugin Website</a><br><a target="_blank" href="http://wordpress.org/extend/plugins/easyreservations/">Wordpress Plugin Directory</a></p>');
}

function easyreservations_load_mainstyle() {  //  Load Scripts and Styles
	$myStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/style.css';
	$chosenStyle = WP_PLUGIN_URL . '/easyreservations/css/style_'.RESERVATIONS_STYLE.'.css';

	wp_register_style('myStyleSheets', $myStyleUrl);
	wp_register_style('chosenStyle', $chosenStyle);

	wp_enqueue_style( 'myStyleSheets');
	wp_enqueue_style( 'chosenStyle');
}

if(isset($_GET['page'])) { $page=$_GET['page'] ; } else $page='';

if($page == 'reservations' OR $page== 'settings' OR $page== 'statistics' OR  $page=='reservation-resources'){  //  Only load Styles and Scripts on Reservation Admin Page 
	add_action('admin_init', 'easyreservations_load_mainstyle');
}


function easyReservations_enqueue_Scripts(){
	global $post, $page;

    // See if the post content contains our shortcode
    if((isset( $post->post_content ) && (false !== strpos( $post->post_content, '[easy_edit' ) || false !== strpos( $post->post_content, '[easy_form' ))) || (isset( $page->post_content ) && (false !== strpos( $page->post_content, '[easy_edit' ) || false !== strpos( $page->post_content, '[easy_form' ))) OR is_home() OR is_category()){
		$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';
		$sendPrice = RESERVATIONS_JS_DIR . '/ajax/send_price.js';
		$sendValidate = RESERVATIONS_JS_DIR . '/ajax/send_validate.js';

		wp_register_style('datestyle', $dateStyleUrl);
		wp_register_script('sendPrice', $sendPrice);
		wp_register_script('sendValidate', $sendValidate);

		wp_enqueue_style( 'datestyle');
		wp_enqueue_script('sendPrice');
		wp_enqueue_script('sendValidate');
        wp_enqueue_script('jquery-ui-datepicker');
    }
	if((isset( $post->post_content ) && (false !== strpos( $post->post_content, '[easy_calendar' ))) || (isset( $page->post_content ) && (false !== strpos( $page->post_content, '[easy_calendar' ))) OR is_home() OR is_category()){
		$sendCalendar = RESERVATIONS_JS_DIR . '/ajax/send_calendar.js';
		wp_register_script('sendCalendar', $sendCalendar);
		wp_enqueue_script('sendCalendar');
	}
	if(is_active_widget(true, false, 'easyReservations_form_widget', true)){
		$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';
		wp_register_style('datestyle', $dateStyleUrl);
		wp_enqueue_style( 'datestyle');
        wp_enqueue_script('jquery-ui-datepicker');

		$littleformStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/forms/form_little.css';
		wp_register_style('littleForm', $littleformStyleUrl);
		wp_enqueue_style('littleForm');

		$sendCalendar = WP_PLUGIN_URL . '/easyreservations/lib/widgets/form_widget_calendar.js';
		wp_register_script('sendwidgetCalendar', $sendCalendar);
		wp_enqueue_script('sendwidgetCalendar');
	}
}

add_action( 'wp_enqueue_scripts', 'easyReservations_enqueue_Scripts' );

function easyReservations_custom_help($contextual_help, $screen_id, $screen) {
	if ($screen_id == 'toplevel_page_reservations') {
		easyReservations_add_help_tabs();
	}
	return $contextual_help;
}

add_filter('contextual_help', 'easyReservations_custom_help', 10, 3);


function easyReservations_statistics_load() {  //  Load Scripts and Styles
	$highcharts = RESERVATIONS_JS_DIR . '/highcharts.js';
	$exporting = RESERVATIONS_JS_DIR . '/modules/exporting.js';


	wp_register_script('highcharts', $highcharts);
	wp_register_script('exporting', $exporting);

	wp_enqueue_script('highcharts');
	wp_enqueue_script('exporting');
}

if(isset($page) AND ($page == 'statistics' OR $page == 'reservations')){  //  Only load Styles and Scripts on Statistics Page
	add_action('admin_init', 'easyReservations_statistics_load');
}

function easyReservations_scripts_resources_load() {  //  Load Scripts and Styles
	$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';
	wp_enqueue_script('jquery');

	wp_register_style('datestyle', $dateStyleUrl);
	wp_enqueue_style('datestyle');
	
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}

if(isset($page) AND $page == 'reservation-resources'){  //  Only load Styles and Scripts on Resources Page
add_action('admin_init', 'easyReservations_scripts_resources_load');
}

function easyReservations_datepicker_load() {  //  Load Scripts and Styles for datepicker
	$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';
	wp_register_style('datestyle', $dateStyleUrl);
	wp_enqueue_style( 'datestyle');

	wp_enqueue_style('thickbox');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery-ui-datepicker');

}
if(isset($page) AND $page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
	add_action('admin_init', 'easyReservations_datepicker_load');
}

function easyReservations_add_pages(){  //  Add Pages Admincenter and Order them
	$reservation_main_permission=get_option("reservations_main_permission");

    add_menu_page(__('easyReservation','easyReservations'), __('Reservation','easyReservations'), $reservation_main_permission, 'reservations', 'reservation_main_page', RESERVATIONS_IMAGES_DIR.'/logo.png' );

	add_submenu_page('reservations', __('Resources','easyReservations'), __('Resources','easyReservations'), $reservation_main_permission, 'reservation-resources', 'reservation_resources_page');

	add_submenu_page('reservations', __('Statistics','easyReservations'), __('Statistics','easyReservations'), $reservation_main_permission, 'statistics', 'reservation_statistics_page');
	
	add_submenu_page('reservations', __('Settings','easyReservations'), __('Settings','easyReservations'), $reservation_main_permission, 'settings', 'reservation_settings_page');
}

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

$formstandart.='[error]
<h1>Reserve now!</h1>
<p>General informations</p>

<label>From
<span class="small">When do you come?</span>
</label>[date-from]

<label>To
<span class="small">When do you go?</span>
</label>[date-to]

<label>Room
<span class="small">Where you want to sleep?</span>
</label>[rooms]

<label>Offer
<span class="small">Do you want an offer?</span>
</label>[offers select]

<label>Persons
<span class="small">How many guests?</span>
</label>[persons Select 10]

<label>Childs
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
		add_option( 'reservations_email_to_userapp_subj', 'Your Reservation on '.get_option('blogname').' has been approved', '', 'yes' );
		add_option( 'reservations_email_to_userapp_msg', $emailstandart2, '', 'yes' );
		add_option( 'reservations_email_to_userdel_subj', 'Your Reservation on '.get_option('blogname').' has been rejected', '', 'yes' );
		add_option( 'reservations_email_to_userdel_msg', $emailstandart3, '', 'yes' );
		add_option( 'reservations_email_to_admin_subj', 'New Reservation at '.get_option('blogname'), '', 'yes' );
		add_option( 'reservations_email_to_admin_msg', $emailstandart1, '', 'yes' );
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
		add_option( 'reservations_form', $formstandart, '', 'yes' );
		add_option( 'reservations_regular_guests', '', '', 'yes' );
		add_option( 'reservations_show_days', '30', '', 'yes' );
		add_option( 'reservations_show_rooms', '', '', 'yes' );
		add_option( 'reservations_edit_url', '', '', 'yes' );
		add_option( 'reservations_edit_text', '', '', 'yes' );
		add_option( 'reservations_price_per_persons', '1', '', 'yes' );
		add_option( 'reservations_on_page', '10', '', 'yes' );
		add_option( 'reservations_room_category', '', '', 'yes' );
		add_option( 'reservations_special_offer_cat', '', '', 'yes' ); 
		add_option( 'reservations_currency', 'dollar', '', 'yes' );
		add_option( 'reservations_support_mail', '', '', 'yes' );
		add_option( 'reservations_style', 'greyfat', '', 'yes' );

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
		UNIQUE KEY id (id));";
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

function easyReservations_upgrade_notice(){
    echo '<div class="updated">
       <p>Thanks for updating <b>easyReservations</b> to <b>1.2</b>!<br>View <a href="http://feryaz.de/changelog/">here</a> fore a detailed Changelog!</p>
    </div>';
}

/*

	Upgrade Script

*/
//delete_option('reservations_db_version' );
add_action('init','easyReservations_upgrade',1);

function easyReservations_upgrade(){

	add_option('reservations_db_version', '1.1.4', '', 'yes' );
	$easyReservations_active_ver="1.2";
	$easyReservations_installed_ver=get_option("reservations_db_version");

	if($easyReservations_installed_ver != $easyReservations_active_ver ){
$formstandart.='[error]
<h1>Reserve now!</h1>
<p>General informations</p>

<label>From
<span class="small">When do you come?</span>
</label>[date-from]

<label>To
<span class="small">When do you go?</span>
</label>[date-to]

<label>Room
<span class="small">Where you want to sleep?</span>
</label>[rooms]

<label>Offer
<span class="small">Do you want an offer?</span>
</label>[offers select]

<label>Persons
<span class="small">How many guests?</span>
</label>[persons Select 10]

<label>Childs
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
<div style="text-align:center;width:99%;">[submit Send]</div>';
		$reservtionsTable = 0;

		global $wpdb;
		$table_name = $wpdb->prefix . "reservations";
		if($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$table_name.";" ) ) > 0) { /* CHECK FOR DB TABLE */

			$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD reservated DATETIME NOT NULL"));
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET reservated=NOW()"));
			$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD childs int(4) NOT NULL"));
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET childs=0"));
			$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD country varchar(4) NOT NULL"));
			$reservtionsTable = 1;

		}

		if($reservtionsTable == 1){
			update_option('reservations_db_version', '1.2');
			add_action('admin_notices', 'easyReservations_upgrade_notice');
			$oldStandardForm = get_option('reservations_form');
			add_option( 'reservations_form_StandardOld', $oldStandardForm, '', 'yes' );
			update_option('reservations_form', $new_form);

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
			add_option( 'reservations_show_rooms', '', '', 'yes' );
			delete_option( 'reservations_backgroundiffull' );
			delete_option( 'reservations_border_bottom' );
			delete_option( 'reservations_border_side' );
			delete_option( 'reservations_colorbackgroundfree' );
			delete_option( 'reservations_fontcoloriffull' );
			delete_option( 'reservations_backgroundiffull' );
			delete_option( 'reservations_colorborder' );
			delete_option( 'reservations_overview_size' );
		}
	}
}
////////////////////////////////////////////////////////////////// END OF MAIN FUNTIONS /////////////////////////////////////////////////////////////

	function easyreservations_price_calculation($id, $newRes){ //This is for calculate price just from the reservation ID
		global $wpdb;
		if(!isset($newRes) OR $newRes == ""){
			$reservation = "SELECT room, special, arrivalDate, nights, email, number, childs, price, customp, reservated FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1";
			$res = $wpdb->get_results( $reservation );
		} else {
			$res = $newRes; // newRes is an array with a db fake of a reservaton. for new reservations, testing purposes or the price in calendars | need to have theese format but you can enter fake emails ect: array(room => '', special => '', arrivalDate => '', nights => '', email => '', number => '', childs => '', price => '', customp => '', reservated => '');
		}
		$price=0; // This will be the Price
		$discount=0; // This will be the Dicount
		$countpriceadd=0; // Count times (=days) a sum gets added to price
		$countgroundpriceadd=0; // Count times (=days) a groundprice is added to price
		$numberoffilter=0; // Count of Filters
		/*

			Get Filters From Offer or from Room if Offer = 0

		*/
		if($res[0]->special=="0" OR $res[0]->special==""){ 
			preg_match_all("/[\[](.*?)[\]]/", get_post_meta($res[0]->room, 'reservations_filter', true), $getfilters); $roomoroffer=$res[0]->room; $roomoroffertext=__( 'Room' , 'easyReservations' );
		} else { 
			preg_match_all("/[\[](.*?)[\]]/", get_post_meta($res[0]->special, 'reservations_filter', true), $getfilters); $roomoroffer=$res[0]->special; $roomoroffertext=__( 'Offer' , 'easyReservations' );
		}

		$filterouts=array_filter($getfilters[1]); //make array out of filters
		$countfilter=count($filterouts);// count the filter-array elements
		$datearray[]='';

		/*

			Sort Price Filters by priorities if no priority was set

		*/
		$arrivalDateRes = strtotime($res[0]->arrivalDate);
		
		foreach($filterouts as $filterout){ //foreach filter array
			$filtertype=explode(" ", $filterout);
			if(!preg_match('/(loyal|stay|pers|avail|early)/i', $filtertype[0]) AND !preg_match("/^[0-9]$/", $filtertype[1])){
				if(preg_match('/(january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|septembre|sep|octobre|oct|novembre|nov|decembre|dec)/', $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 4 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match('/(week|weekdays|weekend|moneday|mon|tuesday|tue|wednesday|wed|thursday|thu|friday|fri|saturday|sat|sunday|sun)/', $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 2 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match("/(([0-9]{4}[\;])+|^[0-9]{4}$)/", $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 6 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match("/(([0-9]{1,2}[\;])+|^[0-9]{1,2}$)/", $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 3 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match('/(q1|quarter1|q2|quarter2|q3|quarter3|q4|quarter4)/', $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 5 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match("/[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}[\-][\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}/", $filtertype[1]) OR preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[1])){
					$filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 1 '.$filtertype[1].' ', $filterouts);
				}
			}
		}

		/*

			Apply Filters

		*/
		asort($filterouts); //sort left filters for any not "date-range" price fields
		$countleftfilters=0;
		foreach($filterouts as $filterout){ //foreach filter array
			$numberoffilter++;
			$filtertype=explode(" ", $filterout);
			if(!preg_match('/(loyal|stay|pers|avail|early)/i', $filtertype[0])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition
				if(preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}[\-][\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[2])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition
					$explodedates=explode("-", $filtertype[2]);
					$arivaldattes=$arrivalDateRes;
					for($count = 1; $count <= $res[0]->nights; $count++){
						if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filtertype[3])){
							$specialexplodes=explode("-", $filtertype[3]);
							foreach($specialexplodes as $specialexplode){
								$priceroomexplode=explode(":", $specialexplode);
								if($priceroomexplode[0]==$res[0]->room){
									if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1]) AND !in_array($arivaldattes, $datearray) AND !in_array($arivaldattes, $datearray)){
										$price+=$priceroomexplode[1]; $countpriceadd++;
										$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
										$datearray[]=$arivaldattes;
									}
									$arivaldattes+=86400;
								}
							}
						}
						elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filtertype[3])){ //If Filter Value is XX
							if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1]) AND !in_array($arivaldattes, $datearray) AND !in_array($arivaldattes, $datearray)){
								$price+=$filtertype[3]; $countpriceadd++;
								$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
								$datearray[]=$arivaldattes;
							}
							$arivaldattes+=86400;
						}
					}
				} elseif(preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[2])){ // If Price filter with dd.mm.yyyy Condition
					$arivaldattes=$arrivalDateRes;
					for($count = 1; $count <= $res[0]->nights; $count++){
						if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filtertype[3])){
							$specialexplodes=explode("-", $filtertype[3]);
							foreach($specialexplodes as $specialexplode){
								$priceroomexplode=explode(":", $specialexplode);
								if($priceroomexplode[0]==$res[0]->room){
									if(date("d.m.Y", $arivaldattes) == date("d.m.Y", strtotime($filtertype[2])) AND !in_array($arivaldattes, $datearray) AND !in_array($arivaldattes, $datearray)){
										$price+=$priceroomexplode[1]; $countpriceadd++;
										$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
										$datearray[]=$arivaldattes;
									}
									$arivaldattes+=86400;
								}
							}
						} elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filtertype[3])){ //If Filter Value is XX
							if(date("d.m.Y", $arivaldattes) == date("d.m.Y", strtotime($filtertype[2])) AND !in_array($arivaldattes, $datearray)){
								$price+=$filtertype[3]; $countpriceadd++;
								$exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
								$datearray[]=$arivaldattes;
							}
							$arivaldattes+=86400;
						}
					}
				} else {

					if(preg_match("/^[a-zA-Z]+$/", $filtertype[2]) OR preg_match("/^[0-9]{2,4}$/", $filtertype[2])){
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
						$arivaldaae=$arrivalDateRes;
						for($count = 1; $count <= $res[0]->nights; $count++){
							$derderder=0;

							if(!in_array($arivaldaae, $datearray)){
								if(preg_match('/(week|weekdays|weekend|moneday|mon|tuesday|tue|wednesday|wed|thursday|thu|friday|fri|saturday|sat|sunday|sun)/', $condition)){
									if($condition == 'week' OR $condition == 'weekdays'){
										if((date("D", $arivaldaae) == "Mon" OR date("D", $arivaldaae) == "Tue" OR date("D", $arivaldaae) == "Wed" OR date("D", $arivaldaae) == "Thu" OR date("D", $arivaldaae) == "Sun")){
											$derderder=1;
											$daystring='Weekdays';
										}
									} elseif($condition == 'weekend'){
										if(date("D", $arivaldaae) == "Sat" OR date("D", $arivaldaae) == "Fri"){
											$derderder=1;
											$daystring='Weekend';
										}
									} elseif(($condition == 'monday' OR $condition == 'mon')){
										if(date("D", $arivaldaae) == "Mon"){
											$derderder=1;
											$daystring='Monday';
										}
									} elseif(($condition == 'tuesday' OR $condition == 'tue')){
										if(date("D", $arivaldaae) == "Tue"){
											$derderder=1;
											$daystring='Tuesday';
										}
									} elseif(($condition == 'wednesday' OR $condition == 'wed')){
										if(date("D", $arivaldaae) == "Wed"){
											$derderder=1;
											$daystring='Wednesday';
										}
									} elseif(($condition == 'thursday' OR $condition == 'thu')){
										if(date("D", $arivaldaae) == "Thu"){
											$derderder=1;
											$daystring='Thursday';
										}
									} elseif(($condition == 'friday' OR $condition == 'fri')){
										if(date("D", $arivaldaae) == "Fri"){
											$derderder=1;
											$daystring='Friday';
										}
									} elseif(($condition == 'saturday' OR $condition == 'sat')){
										if(date("D", $arivaldaae) == "Sat"){
											$derderder=1;
											$daystring='Saturday';
										}
									} elseif(($condition == 'sunday' OR $condition == 'sun')){
										if(date("D", $arivaldaae) == "Sun"){
											$derderder=1;
											$daystring='Sunday';
										}
									}
								}  elseif(preg_match("/(([0-9]{1,2}[\;])+|^[0-9]{1,2}$)/", $condition)){
									if(date("W", $arivaldaae) == $condition){ 
										$derderder=1;
										$daystring='Calendar Week';
									}
								} elseif(preg_match('/(january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|septembre|sep|octobre|oct|novembre|nov|decembre|dec)/', $condition)){
									if(($condition == 'january' OR $condition == 'jan')){
										if(date("m", $arivaldaae) == "01"){
											$derderder=1;
											$daystring='January';
										}
									} elseif(($condition == 'february' OR $condition == 'feb')){
										if(date("m", $arivaldaae) == "02"){
											$derderder=1;
											$daystring='February';
										}
									} elseif(($condition == 'march' OR $condition == 'mar')){
										if(date("m", $arivaldaae) == "03"){
											$derderder=1;
											$daystring='March';
										}
									} elseif(($condition == 'april' OR $condition == 'apr')){
										if(date("m", $arivaldaae) == "04"){
											$derderder=1;
											$daystring='April';
										}
									} elseif(($condition == 'may' OR $condition == 'May')){
										if(date("m", $arivaldaae) == "05"){
											$derderder=1;
											$daystring='May';
										}
									} elseif(($condition == 'june' OR $condition == 'jun')){
										if(date("m", $arivaldaae) == "06"){
											$derderder=1;
											$daystring='June';
										}
									} elseif(($condition == 'july' OR $condition == 'jul')){
										if(date("m", $arivaldaae) == "07"){
											$derderder=1;
											$daystring='July';
										}
									} elseif(($condition == 'august' OR $condition == 'aug')){
										if(date("m", $arivaldaae) == "08"){
											$derderder=1;
											$daystring='August';
										}
									} elseif(($condition == 'september' OR $condition == 'sep')){
										if(date("m", $arivaldaae) == "09"){
											$derderder=1;
											$daystring='September';
										}
									} elseif(($condition == 'october' OR $condition == 'oct')){
										if(date("m", $arivaldaae) == "10"){
											$derderder=1;
											$daystring='October';
										}
									} elseif(($condition == 'november' OR $condition == 'nov')){
										if(date("m", $arivaldaae) == "11"){
											$derderder=1;
											$daystring='November';
										}
									} elseif(($condition == 'december' OR $condition == 'dec')){
										if(date("m", $arivaldaae) == "12"){
											$derderder=1;
											$daystring='December';
										}
									}
								} elseif(preg_match('/(q1|quarter1|q2|quarter2|q3|quarter3|q4|quarter4)/', $condition)){
									if($condition == 'q1' OR $condition == 'quarter1'){
										if(ceil(date("m", $arivaldaae) / 3) == 1){
											$derderder=1;
											$daystring='1. Quartar';
										}
									} elseif(($condition == 'q2' OR $condition == 'quarter2')){
										if(ceil(date("m", $arivaldaae) / 3) == 2){
											$derderder=1;
											$daystring='2. Quartar';
										}
									} elseif($condition == 'q3' OR $condition == 'quarter3'){
										if(ceil(date("m", $arivaldaae) / 3) == 3){
											$derderder=1;
											$daystring='3. Quartar';
										}
									} elseif($condition == 'q4' OR $condition == 'quarter4'){
										if(ceil(date("m", $arivaldaae) / 3) == 4){
											$derderder=1;
											$daystring='4. Quartar';
										}
									}
								} elseif(preg_match("/(([0-9]{4}[\;])+|^[0-9]{4}$)/", $condition)){
									if(date("Y", $arivaldaae) == $condition){
										$derderder=1;
										$daystring='Year';
									}
								}

								if($derderder==1){
									if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filtertype[3])){
										$specialexplodes=explode("-", $filtertype[3]);
										foreach($specialexplodes as $specialexplode){
											$priceroomexplode=explode(":", $specialexplode);
											if($priceroomexplode[0]==$res[0]->room){
												$price+=$priceroomexplode[1]; $countpriceadd++;
												$exactlyprice[] = array('date'=>$arivaldaae, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.$daystring);
												$datearray[]=$arivaldaae;
											}
										}
									} elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filtertype[3])){ //If Filter Value is XX
										$price+=$filtertype[3]; $countpriceadd++; 
										$exactlyprice[] = array('date'=>$arivaldaae, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.$daystring);
										$datearray[]=$arivaldaae;
									}
								}
							}
							$arivaldaae += 86400;
						}
					}
				}
				unset($filterouts[$countleftfilters]); //Remove Filter from Filter array to speed up later foreach
				$conditionarrays= '';
			}
			$countleftfilters++;
		}

		while($countpriceadd < $res[0]->nights){
			if(preg_match("/^[0-9]+[\.]?[0-9]+$/", get_post_meta($roomoroffer, 'reservations_groundprice', true))){
				$price+=get_post_meta($roomoroffer, 'reservations_groundprice', true);		
				$ifDateHasToBeAdded=0;
				if(isset($datearray)){ $getrightday=0; 
					while($getrightday==0){
						if(in_array($arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
							$ifDateHasToBeAdded++;
						} else {
							$getrightday++;
						}
					}
					$datearray[]=$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
				}

				$exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>get_post_meta($roomoroffer, 'reservations_groundprice', true), 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
				$countgroundpriceadd++;
			} else {
				$specialexploder=explode("-", get_post_meta($roomoroffer, 'reservations_groundprice', true));
				foreach($specialexploder as $specialexplode){
					if(preg_match("/^[0-9]+:[0-9]+[\.]?[0-9]$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
						$specialroomexplode=explode(":", $specialexplode);
						if($res[0]->room == $specialroomexplode[0]){
							$price+=$specialroomexplode[1]; // Calculate price for permamently Price
							$ifDateHasToBeAdded=0;
							if(isset($datearray)){ $getrightday=0;
								while($getrightday==0){
									if(in_array($arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
										$ifDateHasToBeAdded++;
									} else {
										$getrightday++;
									}
								}
								$datearray[]=$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
							}
							$exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$specialroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
							$countgroundpriceadd++;
						}
					}
				}
			}
			$countpriceadd++;
		}

		$checkprice=$price;
		$price_per_person = get_option('reservations_price_per_persons');
		
		if($price_per_person == '1' AND ($res[0]->number > 1 OR $res[0]->childs > 0)) {  // Calculate Price if  "Calculate per person"  was choosen
			
			if($res[0]->number > 1){
				$price=$price*$res[0]->number; 
				$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$price-$checkprice, 'type'=>__( 'Price for  persons' , 'easyReservations' ).' x'.$res[0]->number);
				$countpriceadd++;
			}

			if(!empty($res[0]->childs) AND $res[0]->childs != 0){
				$childprice = get_post_meta($roomoroffer, 'reservations_child_price', true);
				if(substr($childprice, -1) == "%"){
					$percent=$checkprice/100*(str_replace("%", "", $childprice)*$res[0]->nights);
					$childsPrice = ($checkprice - $percent);
				} else {
					$childsPrice = ($checkprice - $childprice*$res[0]->nights);
				}
				
				if($price_per_person == '1') $childsPrice = $childsPrice*$res[0]->childs;
				
				$price += $childsPrice;

				$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$childsPrice, 'type'=>__( 'Price per child' , 'easyReservations' ).' x'.$res[0]->childs);
				$countpriceadd++;
			}
		}

		if($res[0]->customp != ""){
			$explodecustomprices=explode("&;&", $res[0]->customp);
			$customprices = 0;
			foreach($explodecustomprices as $customprice){
				if($customprice != ""){
					$custompriceexp=explode("&:&", $customprice);
					$priceasexp=explode(":", $custompriceexp[1]);
					if(substr($priceasexp[1], -1) == "%"){
						$percent=$price/100*str_replace("%", "", $priceasexp[1]);
						$customprices+=$percent;
						$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$percent, 'type'=>__( 'Reservation custom price %' , 'easyReservations' ).' '.$custompriceexp[0]);
					} else {
						$customprices+=$priceasexp[1];
						$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$priceasexp[1], 'type'=>__( 'Reservation custom price' , 'easyReservations' ).' '.$custompriceexp[0]);
					}
				}
			}
			$price+=$customprices; //Price plus Custom prices
		}

		if(count($filterouts) > 0){  //IF Filter array has elemts left they should be Discount Filters or nonsense
			$numberoffilter++;
			$staywasfull=0; $loyalwasfull=0; $perswasfull=0; $earlywasfull=0;
			arsort($filterouts); // Sort rest of array with high to low

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);

				if($filtertype[0]=="stay"){// Stay Filter
					if($staywasfull==0){
						if($filtertype[1] <= $res[0]->nights){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent; 
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$percent, 'type'=>get_the_title($roomoroffer).' '.__( ' Stay filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Stay filter' , 'easyReservations' ));
							}
						$staywasfull++;
						}
					}
				}

				elseif($filtertype[0]=="loyal"){// Loyal Filter
					if($loyalwasfull==0){
						$items1 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='".$res[0]->email."' AND arrivalDate + INTERVAL 1 DAY < NOW()")); //number of total rows in the database
						if($filtertype[1] <= $items1){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent;
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$percent, 'type'=>get_the_title($roomoroffer).' '.__( ' Loyal filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Loyal filter' , 'easyReservations' ));
							}
						$loyalwasfull++;
						}
					}
				}

				elseif($filtertype[0]=="pers"){// Persons Filter
					if($perswasfull==0){
						if($filtertype[1] <= $res[0]->number){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent;
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$discount, 'type'=>get_the_title($roomoroffer).' '.__( ' Persons filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Persons filter' , 'easyReservations' ));
							}
						$perswasfull++;
						}
					}
				}
				
				elseif($filtertype[0]=="early"){// Early Bird Discount Filter
					if($earlywasfull==0){
						$dayBetween=round(($arrivalDateRes/86400)-(strtotime($res[0]->reservated)/86400)); // cals days between booking and arrival
						if($filtertype[1] <= $dayBetween){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent;
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$discount, 'type'=>get_the_title($roomoroffer).' '.__( ' Early Bird filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Early Bird filter' , 'easyReservations' ));
							}
						$earlywasfull++;
						}
					}
				}

			}
		}

		$price-=$discount; //Price minus Discount

		$price=str_replace(".", ",", $price);

		if($res[0]->price != ''){
			$pricexpl=explode(";", $res[0]->price);
			if($pricexpl[0]!=0 AND $pricexpl[0]!=''){
				$price=$pricexpl[0];
			}
		}

		//return $price;
		return array('price'=>$price, 'getusage'=>$exactlyprice);
	}

		function easyreservations_get_price($id){
			$getprice=easyreservations_price_calculation($id, '');
			if($getprice['price'] <= 0) $rightprice=__( 'Wrong Price/Filter' , 'easyReservations' );
			else {
				$geprice=str_replace(",", ".", $getprice['price']);
				$rightprice=reservations_format_money($geprice).' &'.get_option('reservations_currency').';';
			}
			return $rightprice;
		}

		function easyreservations_detailed_price($id){
			$pricearray=easyreservations_price_calculation($id, '');
			$priceforarray=$pricearray['getusage'];
			if(count($priceforarray) > 0){
				$arraycount=count($priceforarray);

				$pricetable='<table class="'.RESERVATIONS_STYLE.'"><thead><tr><th colspan="4" style="border-right:1px">'.__('Detailed Price', 'easyReservations').'</th></tr></thead><tr style="background:#fff;"><td><b>'.__('Date', 'easyReservations').'</b></td><td><b>'.__('Description', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Price of Day', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Total Price', 'easyReservations').'</b></td></tr>';
				$count=0;
				$count2=0;
				$countprices=0;
				$datearray  = "";
				$pricetotal=0;

					sort($priceforarray);
					foreach( $priceforarray as $pricefor){
						$count++;
						if(is_int($count/2)) $class=' class="alternate"'; else $class='';
						$date=$pricefor['date'];
						if(preg_match("/(stay|loyal|custom price|early|pers|child|benutzerdefinierter)/i", $pricefor['type'])) $dateposted=' '; else $dateposted=date("d.m.Y", $date); 
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
			if($id!=0) $filter = get_post_meta($id, 'reservations_filter', true);
			else  $filter = get_post_meta($room, 'reservations_filter', true);
			
			if(!empty($filter)){
				preg_match_all("/[\[](.*?)[\]]/", $filter, $getfilters);
				$filteroutsa=array_values(array_filter($getfilters)); //make array out of filters
				$filterouts = $filteroutsa[1];

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
			}

			$arivaldattes2=strtotime($arrivalDate);
			for($counti = 0; $counti < $nights; $counti++){
				$arivaldattes3=date("Y-m-d", $arivaldattes2);
				$countroomondate = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE room='$room' AND approve='yes' AND roomnumber != '' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY ")); // number of total Reservations on day in Room in the database
				$arivaldattes2+=86400;
				if($countroomondate >= get_post_meta($room, 'roomcount', true)){ $errox++; }
			}
			
			return $errox;
		}

		function reservations_check_room_availibility_exactly_all($arrivalDate, $nights, $room, $roomexactly, $id=''){ //Check if a Room or Offer is Avail or Full
			global $wpdb;
			$errox=0;
			$arrivalDateStmp=strtotime($arrivalDate);

			$filter = get_post_meta($roomid, 'reservations_filter', true);
			if(!empty($filter)){
				preg_match_all("/[\[](.*?)[\]]/", $filter, $getfilters);
				$filteroutsa=array_values(array_filter($getfilters)); //make array out of filters
				$filterouts = $filteroutsa[1];

				foreach($filterouts as $filterout){
					$filtertype=explode(" ", $filterout);
					if($filtertype[0]=='avail'){
						$explodedates=explode("-", $filtertype[1]);
						$arivaldattes=$arrivalDateStmp;
						for($count = 0; $count < $nights; $count++){
							$arivaldattes+=86400;
							if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])) $errox++;
						}
					}
				}
			}

			for($counti = 0; $counti < $nights-1; $counti++){
				$arivaldattes3=date("Y-m-d", $arrivalDateStmp+($counti*86400));
				$erros = mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE id!='$id' AND room='$room' AND approve='yes' AND roomnumber='$roomexactly' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"); // number of total Reservations on day in Room in the database
				$errox += mysql_num_rows($erros);
			}

			return $errox;
		}

	function reservations_check_availibility_for_room($roomid, $date){ //Check if a Room or Offer is Avail or Full
		global $wpdb;
		$errox=0;
		
		$filter = get_post_meta($roomid, 'reservations_filter', true);
		
		if(!empty($filter)){
			preg_match_all("/[\[](.*?)[\]]/", $filter, $getfilters);
			$filteroutsa=array_values(array_filter($getfilters)); //make array out of filters
			$filterouts = $filteroutsa[1];

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);
				if($filtertype[0]=='avail'){
					$explodedates=explode("-", $filtertype[1]);
					$arivaldattes=strtotime($date);
					if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])){ $errox+= get_post_meta($roomid, 'roomcount', true); }
				}
			}
		}
		$arivaldattes3=date("Y-m-d", strtotime($date));
		$countroomondate = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE room='$roomid' AND roomnumber != '' AND approve='yes' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - Interval 1 DAY ")); // number of total Reservations on day in Room in the database
		$errox+=$countroomondate;

		return $errox;
	}

	function reservations_check_availibility_for_room_exactly($roomid, $roomexactly, $date){ //Check if a Room or Offer is Avail or Full
		global $wpdb;
		$errox=0;
		$filter = get_post_meta($roomid, 'reservations_filter', true);
		
		if(!empty($filter)){
			preg_match_all("/[\[](.*?)[\]]/", $filter, $getfilters);
			$filteroutsa=array_values(array_filter($getfilters)); //make array out of filters
			$filterouts = $filteroutsa[1];

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);
				if($filtertype[0]=='avail'){
					$explodedates=explode("-", $filtertype[1]);
					$arivaldattes=strtotime($date);
					if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])){ $errox++; }
				}
			}
		}
		$arivaldattes3=date("Y-m-d", strtotime($date));
		$countroomondate = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE room='$roomid' AND roomnumber='$roomexactly' AND approve='yes' AND '$arivaldattes3' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - Interval 1 DAY ")); // number of total Reservations on day in Room in the database
		if($countroomondate > 0){ $errox++; }
		
		return $errox;
	}

	function reservations_check_availibility_for_room_filter($roomid, $date){ //Check if a Room or Offer is Avail or Full

		global $wpdb;
		$errox=0;

		$filter = get_post_meta($roomid, 'reservations_filter', true);
		
		if(!empty($filter)){
			preg_match_all("/[\[](.*?)[\]]/", $filter, $getfilters);
			$filteroutsa=array_values(array_filter($getfilters)); //make array out of filters
			$filterouts = $filteroutsa[1];

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);
				if($filtertype[0]=='avail'){
					$explodedates=explode("-", $filtertype[1]);
					$arivaldattes=strtotime($date);
					if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1])){ $errox++; }
				}
			}
		}
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
		$args=array( 'category' => get_option('reservations_room_category'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );

		$getids = get_posts($args);
		foreach($getids as $getid){
			$theroomidsarray[] = array($getid->ID, $getid->post_title);
		}
		return $theroomidsarray;
	}

	function reservations_get_offer_ids(){ //Get the IDs of the Offer Posts in array for helping people to find it.
		global $wpdb;
		$args=array( 'category' => get_option('reservations_special_offer_cat'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );

		$getids = get_posts($args);
		foreach($getids as $getid){
			$theofferidsarray[] = array($getid->ID, $getid->post_title);
		}
		return $theofferidsarray;
	}

	function reservations_check_type($id){
		global $wpdb;

		$checktype = "SELECT approve FROM ".$wpdb->prefix ."reservations WHERE id='$id'"; 
		$res = $wpdb->get_results( $checktype );

		if($res[0]->approve=="yes") $istype=__( 'approved' , 'easyReservations' );
		elseif($res[0]->approve=="no") $istype=__( 'rejected' , 'easyReservations' );
		elseif($res[0]->approve=="del") $istype=__( 'trashed' , 'easyReservations' );
		elseif($res[0]->approve=="") $istype=__( 'pending' , 'easyReservations' );

		return $istype;
	}
	
	function reservations_status_output($status){ //gives out colored and named stauts

		if($status=="yes") $theStatus= '<b style="color:#009B1C">'.__( 'approved' , 'easyReservations' ).'</b>';
		elseif($status=="no") $theStatus= '<b style="color:#E80000;">'.__( 'rejected' , 'easyReservations' ).'</b>';
		elseif($status=="del") $theStatus= '<b style="color:#E80000;">'.__( 'trashed' , 'easyReservations' ).'</b>';
		elseif($status=="") $theStatus= '<b style="color:#0072E5;">'.__( 'pending' , 'easyReservations' ).'</b>';

		return $theStatus;
	}

	function reservations_check_pay_status($id){
		global $wpdb;

		$checkpaid = "SELECT price FROM ".$wpdb->prefix ."reservations WHERE id='$id'";
		$res = $wpdb->get_results( $checkpaid  );
		$explodetheprice = explode(";", $res[0]->price);
		if(!isset($explodetheprice[1]) OR empty($explodetheprice[1])) $payed = 0;
		else $payed = $explodetheprice[1];

		if($explodetheprice[0] != '') $ispayed = $explodetheprice[0]-$payed;
		else {
			$thepriceArray = easyreservations_price_calculation($id, '');
			$thePricetoAdd = $thepriceArray['price'];
			$ispayed = easyreservations_check_price($thePricetoAdd)-$payed;
		}

		return $ispayed;
	}

	function reservations_get_administration_links($id, $where){ //Get Links for approve, edit, trash, delete, view...

		$countits=0;
		$checkID = reservations_check_type($id);
		$administration_links = "";
		if($where != "approve" AND $checkID != __("approved")) { $administration_links.='<a href="admin.php?page=reservations&approve='.$id.'">'.__( 'Approve' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' | '; $countits=0; }
		if($where != "reject" AND $checkID != "rejected") { $administration_links.='<a href="admin.php?page=reservations&delete='.$id.'">'.__( 'Reject' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' | '; $countits=0; }
		if($where != "edit") { $administration_links.='<a href="admin.php?page=reservations&edit='.$id.'">'.__( 'Edit' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' | '; $countits=0; }
		$administration_links.='<a href="admin.php?page=reservations&sendmail='.$id.'">'.__( 'Mail' , 'easyReservations' ).'</a>'; $countits++;
		//if($countits > 0){ $administration_links.=' | '; $countits=0; }
		//if($where != "trash" AND $checkID != "trashed") { $administration_links.='<a href="admin.php?page=reservations&bulkArr[]='.$id.'&bulk=1">'.__( 'Trash' , 'easyReservations' ).'</a>'; $countits++; }

		return $administration_links;
	}

	function reservations_format_money($amount,$separator=true,$simple=false){
		if($amount != ''){
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
	}

	function reservations_is_room($id){
		$category=get_the_category($id);
		$roomcategory=get_option('reservations_room_category');
		if($category[0]->cat_ID == $roomcategory) return true;
		else return false;
	}

	function reservations_get_room_options($selected=''){
		$room_args = array( 'post_status' => 'publish|private', 'category' => get_option("reservations_room_category"), 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1);
		$roomcategories = get_posts( $room_args );
		$rooms_options='';
		foreach( $roomcategories as $roomcategorie ){
			if(isset($selected) AND !empty($selected) AND $selected == $roomcategorie->ID) $select = ' selected="selected"'; else $select = "";
			$rooms_options .= '<option value="'.$roomcategorie->ID.'"'.$select.'>'.__($roomcategorie->post_title).'</option>';
		}
		return $rooms_options;
	}

	function reservations_get_offer_options($selected=''){
		$offer_args = array( 'post_status' => 'publish|private', 'category' => get_option("reservations_special_offer_cat"), 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1);
		$offercategories = get_posts( $offer_args );
		$offer_options='';
		foreach( $offercategories as $offercategorie ){
			if(isset($selected) AND !empty($selected) AND $selected == $offercategorie->ID) $select = ' selected="selected"'; $select = "";
			$offer_options .= '<option value="'.$offercategorie->ID.'"'.$select.'>'.__($offercategorie->post_title).'</option>';
		}
		return $offer_options;
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

	add_filter('mce_external_plugins', "reservations_tiny_register");
	add_filter('mce_buttons', 'reservations_tiny_add_button', 0);

	function reservations_tiny_add_button($buttons){
		array_push($buttons, "separator", "easyReservations");
		return $buttons;
	}

	function reservations_tiny_register($plugin_array){
		$url = WP_PLUGIN_URL . '/easyreservations/js/tinyMCE/tinyMCE_shortcode_add.js';

		$plugin_array['easyReservations'] = $url;
		return $plugin_array;
	}

	function easyreservations_send_mail($theForm, $mailTo, $mailSubj, $theMessage, $theID, $arrivalDate, $departureDate, $theName, $theEmail, $theNights, $thePersons, $theChilds, $theCountry, $theRoom, $theOffer, $theCustoms, $thePrice, $theNote, $theChangelog){ //Send formatted Mails from anywhere
		preg_match_all(' /\[.*\]/U', $theForm, $matchers); 
		$mergearrays=array_merge($matchers[0], array());
		$edgeoneremoave=str_replace('[', '', $mergearrays);
		$edgetworemovess=str_replace(']', '', $edgeoneremoave);

		foreach($edgetworemovess as $fieldsx){
			$field=explode(" ", $fieldsx);
			if($field[0]=="adminmessage"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theMessage, $theForm);
			}
			elseif($field[0]=="ID"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theID, $theForm);
			}
			elseif($field[0]=="thename"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theName, $theForm);
			}
			elseif($field[0]=="email"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theEmail, $theForm);
			}
			elseif($field[0]=="arrivaldate"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $arrivalDate).'', $theForm);
			}
			elseif($field[0]=="changelog"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theChangelog, $theForm);
			}
			elseif($field[0]=="departuredate"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $departureDate).'', $theForm);
			}
			elseif($field[0]=="nights"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$theNights.'', $theForm);
			}
			elseif($field[0]=="note"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$theNote.'', $theForm);
			}
			elseif($field[0]=="persons"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$thePersons.'', $theForm);
			}
			elseif($field[0]=="childs"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$theChilds.'', $theForm);
			}
			elseif($field[0]=="rooms"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.__($theRoom).'', $theForm);
			}
			elseif($field[0]=="country"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.__($theCountry).'', $theForm);
			}
			elseif($field[0]=="offers"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.__($theOffer).'', $theForm);
			}
			elseif($field[0]=="price"){
				$theForm=str_replace('[price]', str_replace("&", "", str_replace(";", "", $thePrice)), $theForm);
			}
			elseif($field[0]=="editlink"){
				$theForm=str_replace('[editlink]', get_option("reservations_edit_url").'?id='.$theID.'?email='.$theEmail, $theForm);
			}
			elseif($field[0]=="customs"){
				$explodecustoms=explode("&;&", $theCustoms);
				$customsmerge=array_values(array_filter($explodecustoms));
				$theCustominMail="";
				foreach($customsmerge as $custom){
					$customaexp=explode("&:&", $custom);
					$theCustominMail  .= $customaexp[0].': '.$customaexp[1].'<br>';
				}
				$theForm=str_replace('['.$field[0].']', $theCustominMail, $theForm);
			}
		}

		$finalemailedgeremove1=str_replace('[', '', $theForm);
		$finalemailedgesremoved=str_replace(']', '', $finalemailedgeremove1);
		$makebrtobreak=str_replace('<br>', "\n", $finalemailedgesremoved);
		$msg=$makebrtobreak;
		
		$reservation_support_mail = get_option("reservations_support_mail");
		$subj=$mailSubj;
		$eol="\n";
		$headers = "From: ".get_bloginfo('name')." <".$reservation_support_mail.">".$eol;
		$headers .= "Message-ID: <".time()."-".$reservation_support_mail.">".$eol;

		wp_mail($mailTo,$subj,$msg,$headers);
	}

	function easyreservations_check_price($price){
		$newPrice = str_replace(",", ".", $price);
		if(preg_match("/^[0-9]+[\.]?[0-9]*$/", $newPrice)){
			$finalPrice = $newPrice;
		} else {
			$finalPrice = 'error';
		}
		return $finalPrice;
	}
	
	function easyreservations_reservation_info_box($id, $where){
		$payStatus = reservations_check_pay_status($id);
		if($payStatus == 0) $paid = ' - <b style="text-transform: capitalize;color:#1FB512;">'. __( 'paid' , 'easyReservations' ).'</b>';
		else $paid = ' - <b style="text-transform: capitalize;color:#FF3B38;">'. __( 'unpaid' , 'easyReservations' ).'</b>';
		$status = reservations_check_type($id) ;

		if($status == __('approved', 'easyReservations' )) $color='#1FB512';
		elseif($status == __('pending' , 'easyReservations' )) $color='#3BB0E2';
		elseif($status == __('rejected' , 'easyReservations' )) $color='#D61111';
		elseif($status == __('trashed' , 'easyReservations' )) $color='#870A0A';

		$infoBox = '<div class="explainbox" style="width:96%; margin-bottom:2px;"><div id="left" style=""><b><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.easyreservations_get_price($id).'</b></div><div id="right"><span style="float:right">'.reservations_get_administration_links($id, $where).'</span></div><div id="center"><b style="color:'.$color.';text-transform: capitalize">'.$status.'</b> '.$paid.'</div></div>';
		return $infoBox;
	}

	class RemoveAdminHelpLinkButton { // Remove the WP Admin Help button on top
	  static function on_load() {
		add_filter('contextual_help',array(__CLASS__,'contextual_help'));
		add_action('admin_notices',array(__CLASS__,'admin_notices'));
	  }
	  static function contextual_help($contextual_help) {
		ob_start();
		return $contextual_help;
	  }
	  static function admin_notices() {
		echo preg_replace('#<div id="contextual-help-link-wrap".*>.*</div>#Us','',ob_get_clean());
	  }
	}

	function curPageURL() {
		$pageURL = 'http';
		if(isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	function easyreservations_generate_res_Changelog($beforeArray, $afterArray){		
		$changelog = '';

		if($beforeArray['arrivalDate'] != $afterArray['arrivalDate']){
			$changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date("d.m.Y", (strtotime($beforeArray['arrivalDate']))).' => '.date("d.m.Y", (strtotime($afterArray['arrivalDate']))).'<br>';
		}

		//if($beforeArray['arrivalDate'] != $afterArray['arrivalDate'] OR $beforeArray['nights'] != $afterArray['nights']){
		if((strtotime($beforeArray['arrivalDate'])+($beforeArray['nights']*86400)) != (strtotime($afterArray['arrivalDate'])+($afterArray['nights']*86400))){
			$changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date("d.m.Y", ((strtotime($beforeArray['arrivalDate'])+($beforeArray['nights']*86400)))).' => '.date("d.m.Y", ((strtotime($afterArray['arrivalDate'])+($afterArray['nights']*86400)))).'<br>';
		}

		if($beforeArray['name'] != $afterArray['name']){
			$changelog .= __('The name was edited' , 'easyReservations' ).': '.$beforeArray['name'].' => '.$afterArray['name'].'<br>';
		}

		if($beforeArray['email'] != $afterArray['email']){
			$changelog .= __('The email was edited' , 'easyReservations' ).': '.$beforeArray['email'].' => '.$afterArray['email'].'<br>';
		}

		if($beforeArray['persons'] != $afterArray['persons']){
			$changelog .= __('The amoun of persons was edited' , 'easyReservations' ).': '.$beforeArray['persons'].' => '.$afterArray['persons'].'<br>';
		}

		if($beforeArray['childs'] != $afterArray['childs']){
			$changelog .= __('The amoun of childs was edited' , 'easyReservations' ).': '.$beforeArray['childs'].' => '.$afterArray['childs'].'<br>';
		}

		if($beforeArray['country'] != $afterArray['country']){
			$changelog .= __('The country was edited' , 'easyReservations' ).': '.$beforeArray['country'].' => '.$afterArray['country'].'<br>';
		}

		if($beforeArray['room'] != $afterArray['room']){
			$changelog .= __('The room was edited' , 'easyReservations' ).': '.get_the_title($beforeArray['room']).' => '.get_the_title($afterArray['room']).'<br>';
		}

		if($beforeArray['offer'] != $afterArray['offer']){
			if($afterArray['offer'] == 0) $afterMailOffer = __( 'None' , 'easyReservations' );
			else $afterMailOffer = __(get_the_title($afterArray['offer']));
			if($beforeArray['offer'] == 0) $beforMailOffer = __( 'None' , 'easyReservations' );
			else $beforMailOffer = __(get_the_title($beforeArray['offer']));

			$changelog .= __('The offer was edited' , 'easyReservations' ).': '.$beforMailOffer.' => '.$afterMailOffer.'<br>';
		}

		if($beforeArray['message'] != $afterArray['message']){
			$changelog .= __('The message was edited' , 'easyReservations' ).'<br>';
		}

		if($beforeArray['custom'] != $afterArray['custom']){
			$changelog .= __('Custom fields was edited' , 'easyReservations' ).'<br>';
		}

		if(isset($beforeArray['customp']) AND $beforeArray['customp'] != $afterArray['customp']){
			$changelog .= __('Custom price was deleted' , 'easyReservations' ).'<br>';
		}

		return $changelog;
	}

	function easyReservations_country_array(){

		return array(
			'AF'=>'Afghanistan',
			'AL'=>'Albania',
			'DZ'=>'Algeria',
			'AS'=>'American Samoa',
			'AD'=>'Andorra',
			'AO'=>'Angola',
			'AI'=>'Anguilla',
			'AQ'=>'Antarctica',
			'AG'=>'Antigua And Barbuda',
			'AR'=>'Argentina',
			'AM'=>'Armenia',
			'AW'=>'Aruba',
			'AU'=>'Australia',
			'AT'=>'Austria',
			'AZ'=>'Azerbaijan',
			'BS'=>'Bahamas',
			'BH'=>'Bahrain',
			'BD'=>'Bangladesh',
			'BB'=>'Barbados',
			'BY'=>'Belarus',
			'BE'=>'Belgium',
			'BZ'=>'Belize',
			'BJ'=>'Benin',
			'BM'=>'Bermuda',
			'BT'=>'Bhutan',
			'BO'=>'Bolivia',
			'BA'=>'Bosnia And Herzegovina',
			'BW'=>'Botswana',
			'BV'=>'Bouvet Island',
			'BR'=>'Brazil',
			'IO'=>'British Indian Ocean Territory',
			'BN'=>'Brunei',
			'BG'=>'Bulgaria',
			'BF'=>'Burkina Faso',
			'BI'=>'Burundi',
			'KH'=>'Cambodia',
			'CM'=>'Cameroon',
			'CA'=>'Canada',
			'CV'=>'Cape Verde',
			'KY'=>'Cayman Islands',
			'CF'=>'Central African Republic',
			'TD'=>'Chad',
			'CL'=>'Chile',
			'CN'=>'China',
			'CX'=>'Christmas Island',
			'CC'=>'Cocos (Keeling) Islands',
			'CO'=>'Columbia',
			'KM'=>'Comoros',
			'CG'=>'Congo',
			'CK'=>'Cook Islands',
			'CR'=>'Costa Rica',
			'CI'=>'Cote D\'Ivorie (Ivory Coast)',
			'HR'=>'Croatia (Hrvatska)',
			'CU'=>'Cuba',
			'CY'=>'Cyprus',
			'CZ'=>'Czech Republic',
			'CD'=>'Democratic Republic Of Congo (Zaire)',
			'DK'=>'Denmark',
			'DJ'=>'Djibouti',
			'DM'=>'Dominica',
			'DO'=>'Dominican Republic',
			'TP'=>'East Timor',
			'EC'=>'Ecuador',
			'EG'=>'Egypt',
			'SV'=>'El Salvador',
			'GQ'=>'Equatorial Guinea',
			'ER'=>'Eritrea',
			'EE'=>'Estonia',
			'ET'=>'Ethiopia',
			'FK'=>'Falkland Islands (Malvinas)',
			'FO'=>'Faroe Islands',
			'FJ'=>'Fiji',
			'FI'=>'Finland',
			'FR'=>'France',
			'FX'=>'France, Metropolitan',
			'GF'=>'French Guinea',
			'PF'=>'French Polynesia',
			'TF'=>'French Southern Territories',
			'GA'=>'Gabon',
			'GM'=>'Gambia',
			'GE'=>'Georgia',
			'DE'=>'Germany',
			'GH'=>'Ghana',
			'GI'=>'Gibraltar',
			'GR'=>'Greece',
			'GL'=>'Greenland',
			'GD'=>'Grenada',
			'GP'=>'Guadeloupe',
			'GU'=>'Guam',
			'GT'=>'Guatemala',
			'GN'=>'Guinea',
			'GW'=>'Guinea-Bissau',
			'GY'=>'Guyana',
			'HT'=>'Haiti',
			'HM'=>'Heard And McDonald Islands',
			'HN'=>'Honduras',
			'HK'=>'Hong Kong',
			'HU'=>'Hungary',
			'IS'=>'Iceland',
			'IN'=>'India',
			'ID'=>'Indonesia',
			'IR'=>'Iran',
			'IQ'=>'Iraq',
			'IE'=>'Ireland',
			'IL'=>'Israel',
			'IT'=>'Italy',
			'JM'=>'Jamaica',
			'JP'=>'Japan',
			'JO'=>'Jordan',
			'KZ'=>'Kazakhstan',
			'KE'=>'Kenya',
			'KI'=>'Kiribati',
			'KW'=>'Kuwait',
			'KG'=>'Kyrgyzstan',
			'LA'=>'Laos',
			'LV'=>'Latvia',
			'LB'=>'Lebanon',
			'LS'=>'Lesotho',
			'LR'=>'Liberia',
			'LY'=>'Libya',
			'LI'=>'Liechtenstein',
			'LT'=>'Lithuania',
			'LU'=>'Luxembourg',
			'MO'=>'Macau',
			'MK'=>'Macedonia',
			'MG'=>'Madagascar',
			'MW'=>'Malawi',
			'MY'=>'Malaysia',
			'MV'=>'Maldives',
			'ML'=>'Mali',
			'MT'=>'Malta',
			'MH'=>'Marshall Islands',
			'MQ'=>'Martinique',
			'MR'=>'Mauritania',
			'MU'=>'Mauritius',
			'YT'=>'Mayotte',
			'MX'=>'Mexico',
			'FM'=>'Micronesia',
			'MD'=>'Moldova',
			'MC'=>'Monaco',
			'MN'=>'Mongolia',
			'MS'=>'Montserrat',
			'MA'=>'Morocco',
			'MZ'=>'Mozambique',
			'MM'=>'Myanmar (Burma)',
			'NA'=>'Namibia',
			'NR'=>'Nauru',
			'NP'=>'Nepal',
			'NL'=>'Netherlands',
			'AN'=>'Netherlands Antilles',
			'NC'=>'New Caledonia',
			'NZ'=>'New Zealand',
			'NI'=>'Nicaragua',
			'NE'=>'Niger',
			'NG'=>'Nigeria',
			'NU'=>'Niue',
			'NF'=>'Norfolk Island',
			'KP'=>'North Korea',
			'MP'=>'Northern Mariana Islands',
			'NO'=>'Norway',
			'OM'=>'Oman',
			'PK'=>'Pakistan',
			'PW'=>'Palau',
			'PA'=>'Panama',
			'PG'=>'Papua New Guinea',
			'PY'=>'Paraguay',
			'PE'=>'Peru',
			'PH'=>'Philippines',
			'PN'=>'Pitcairn',
			'PL'=>'Poland',
			'PT'=>'Portugal',
			'PR'=>'Puerto Rico',
			'QA'=>'Qatar',
			'RE'=>'Reunion',
			'RO'=>'Romania',
			'RU'=>'Russia',
			'RW'=>'Rwanda',
			'SH'=>'Saint Helena',
			'KN'=>'Saint Kitts And Nevis',
			'LC'=>'Saint Lucia',
			'PM'=>'Saint Pierre And Miquelon',
			'VC'=>'Saint Vincent And The Grenadines',
			'SM'=>'San Marino',
			'ST'=>'Sao Tome And Principe',
			'SA'=>'Saudi Arabia',
			'SN'=>'Senegal',
			'SC'=>'Seychelles',
			'SL'=>'Sierra Leone',
			'SG'=>'Singapore',
			'SK'=>'Slovak Republic',
			'SI'=>'Slovenia',
			'SB'=>'Solomon Islands',
			'SO'=>'Somalia',
			'ZA'=>'South Africa',
			'GS'=>'South Georgia And South Sandwich Islands',
			'KR'=>'South Korea',
			'ES'=>'Spain',
			'LK'=>'Sri Lanka',
			'SD'=>'Sudan',
			'SR'=>'Suriname',
			'SJ'=>'Svalbard And Jan Mayen',
			'SZ'=>'Swaziland',
			'SE'=>'Sweden',
			'CH'=>'Switzerland',
			'SY'=>'Syria',
			'TW'=>'Taiwan',
			'TJ'=>'Tajikistan',
			'TZ'=>'Tanzania',
			'TH'=>'Thailand',
			'TG'=>'Togo',
			'TK'=>'Tokelau',
			'TO'=>'Tonga',
			'TT'=>'Trinidad And Tobago',
			'TN'=>'Tunisia',
			'TR'=>'Turkey',
			'TM'=>'Turkmenistan',
			'TC'=>'Turks And Caicos Islands',
			'TV'=>'Tuvalu',
			'UG'=>'Uganda',
			'UA'=>'Ukraine',
			'AE'=>'United Arab Emirates',
			'UK'=>'United Kingdom',
			'US'=>'United States',
			'UM'=>'United States Minor Outlying Islands',
			'UY'=>'Uruguay',
			'UZ'=>'Uzbekistan',
			'VU'=>'Vanuatu',
			'VA'=>'Vatican City (Holy See)',
			'VE'=>'Venezuela',
			'VN'=>'Vietnam',
			'VG'=>'Virgin Islands (British)',
			'VI'=>'Virgin Islands (US)',
			'WF'=>'Wallis And Futuna Islands',
			'EH'=>'Western Sahara',
			'WS'=>'Western Samoa',
			'YE'=>'Yemen',
			'YU'=>'Yugoslavia',
			'ZM'=>'Zambia',
			'ZW'=>'Zimbabwe'
		);
	}

	function easyReservations_country_select($presentCountry){

		$countryArray = easyReservations_country_array();
		$country_options = '';
		foreach($countryArray as $short => $country){
			if($short == $presentCountry){ $select = ' selected'; }
			else $select = "";
			$country_options .= '<option value="'.$short.'"'.$select.'>'.$country.'</options>';
		}

		return $country_options;
	}

	function easyReservations_country_name($country){

		$countryArray = easyReservations_country_array();

		return $countryArray[$country];

	}

	function easyReservations_num_options($start,$end,$sel=''){

		$return = '';

		for($num = $start; $num <= $end; $num++){
		
			if(!empty($sel) AND $num == $sel ) $isel = 'selected="selected"'; else $isel = '';

			$return .= '<option value="'.$num.'"'.$isel.'>'.$num.'</option>';
		
		}
		
		return $return;

	}
	
	function easyReservations_res_box($anf, $end){
		
		$time = time();
		if($time - $anf > 0 AND $time - $end > 0) $sta = "er_res_old";
		elseif($time - $anf > 0 AND $time - $end <= 0) $sta = "er_res_now";
		else $sta = "er_res_future";
		
		return '<div class="er_res_box '.$sta.'"></div>';
	
	} ?>