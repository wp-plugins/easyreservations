<?php
	/**
	* 	@functions for admin only 
	*/

	/**
	*	Add pages to admin
	*
	*/

	add_action('admin_menu', 'easyReservations_add_pages');

	function easyReservations_add_pages(){  //  Add Pages Admincenter and Order them
		$reservation_main_permission=get_option("reservations_main_permission");

		add_menu_page(__('easyReservation','easyReservations'), __('Reservation','easyReservations'), $reservation_main_permission, 'reservations', 'reservation_main_page', RESERVATIONS_IMAGES_DIR.'/logo.png' );

		add_submenu_page('reservations', __('Resources','easyReservations'), __('Resources','easyReservations'), $reservation_main_permission, 'reservation-resources', 'reservation_resources_page');

		add_submenu_page('reservations', __('Statistics','easyReservations'), __('Statistics','easyReservations'), $reservation_main_permission, 'reservation-statistics', 'reservation_statistics_page');
		
		add_submenu_page('reservations', __('Settings','easyReservations'), __('Settings','easyReservations'), $reservation_main_permission, 'reservation-settings', 'reservation_settings_page');
	}
	
	/**
	*	Load scripts and styles
	*
	*/
	
	function easyreservations_load_mainstyle() {  //  Load Scripts and Styles
		$myStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/style.css';
		$chosenStyle = WP_PLUGIN_URL . '/easyreservations/css/style_'.RESERVATIONS_STYLE.'.css';

		wp_register_style('myStyleSheets', $myStyleUrl);
		wp_register_style('chosenStyle', $chosenStyle);

		wp_enqueue_style( 'myStyleSheets');
		wp_enqueue_style( 'chosenStyle');
	}

	if(isset($_GET['page'])) { $page=$_GET['page'] ; } else $page='';

	if($page == 'reservations' OR $page== 'reservation-settings' OR $page== 'reservation-statistics' OR  $page=='reservation-resources'){  //  Only load Styles and Scripts on Reservation Admin Page 
		add_action('admin_init', 'easyreservations_load_mainstyle');
	}


	function easyReservations_statistics_load() {  //  Load Scripts and Styles
		$highcharts = RESERVATIONS_JS_DIR . '/highcharts.js';
		$exporting = RESERVATIONS_JS_DIR . '/modules/exporting.js';


		wp_register_script('highcharts', $highcharts);
		wp_register_script('exporting', $exporting);

		wp_enqueue_script('highcharts');
		wp_enqueue_script('exporting');
	}

	if(isset($page) AND ($page == 'reservation-statistics' OR $page == 'reservations')){  //  Only load Styles and Scripts on Statistics Page
		add_action('admin_init', 'easyReservations_statistics_load');
	}

	function easyReservations_scripts_resources_load() {  //  Load Scripts and Styles
		$dateStyleUrl = WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css';

		wp_register_style('datestyle', $dateStyleUrl);
		wp_enqueue_style('datestyle');
		wp_enqueue_script('jquery-ui-datepicker');

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

		wp_enqueue_script('jquery-ui-datepicker');
	}
	if(isset($page) AND $page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
		add_action('admin_init', 'easyReservations_datepicker_load');
	}

	/**
	*	Install script
	*
	*/


	/**
	*	Upgrade script
	*
	*/
	
	//delete_option('reservations_db_version' );
	add_action('admin_init','easyReservations_upgrade',1);

	function easyReservations_upgrade(){

		$easyReservations_active_ver="1.3.2";
		$easyReservations_installed_ver=get_option("reservations_db_version");

		if($easyReservations_installed_ver != $easyReservations_active_ver ){

			$reservtionsTable = 0;

			if($easyReservations_installed_ver < 1.2){
				global $wpdb;
				$table_name = $wpdb->prefix . "reservations";
				if($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$table_name.";" ) ) > 0) { /* CHECK FOR DB TABLE */

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
			} elseif($easyReservations_installed_ver == 1.2 || $easyReservations_installed_ver == 1.3 || $easyReservations_installed_ver == "1.3.1"){

				$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
				$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => $table_message, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_paid' => $table_paid, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1 );
				$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
				add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');

				$reservtionsTable = 1;
			}

			if($reservtionsTable == 1){
				update_option('reservations_db_version', "1.3.2");
				add_action('admin_notices', 'easyReservations_upgrade_notice');
			}
		}
	}
	
	function easyReservations_upgrade_notice(){
		echo '<div class="updated">
		   <p>Thanks for updating <b>easyReservations</b> to <b>1.3</b>!<br>View <a href="http://feryaz.de/changelog/">here</a> fore a detailed Changelog!<br>
		   Sorry for the mess of updates, now everythink should be fine!</p>
		</div>';
	}
	
	/**
	*	Add help to settings
	*
	*/

	add_filter('contextual_help', 'easyReservations_custom_help', 10, 3);

	function easyReservations_custom_help($contextual_help, $screen_id, $screen) {
		if ($screen_id == 'reservation_page_reservation-settings') {
			easyReservations_add_help_tabs();
		}
		return $contextual_help;
	}

	function easyReservations_add_help_tabs(){
		$screen = get_current_screen();
		if ( $screen->id != 'reservation_page_reservation-settings' )
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
	
	/**
	*	Get detailed price calculation box
	*
	*	$id = reservations id
	*/

	function easyreservations_detailed_price($id){
		$pricearray=easyreservations_price_calculation($id, '', 1);
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
					$pricetable.= '<tr'.$class.'><td nowrap>'.$dateposted.'</td><td nowrap>'.$pricefor['type'].'</td><td style="text-align:right;" nowrap>'.reservations_format_money($pricefor['priceday'], 1).'</td><td style="text-align:right;" nowrap><b'.$onlastprice.'>'.reservations_format_money($pricetotal, 1).'</b></td></tr>';
					unset($priceforarray[$count-1]);
				}

			$pricetable.='</table>';
		} else $pricetable = 'Critical Error #1023462';

		return $pricetable;
	}

	/**
	*	Return ids of all rooms
	*
	*	$id = reservations id
	*/

	function easyreservations_get_highest_roomcount(){ //Get highest Count of Room
		global $wpdb;

		$gethighroomcount = "SELECT meta_value FROM ".$wpdb->prefix ."postmeta WHERE meta_key='roomcount' ORDER BY meta_value DESC LIMIT 1"; // Get Higest Roomcount
		$res = $wpdb->get_results( $gethighroomcount );
		return $res[0]->meta_value;
	}

	/**
	*	Return ids of all rooms
	*
	*	$id = reservations id
	*/

	function reservations_get_room_ids($mode=0){ //Get the IDs of the Room Posts in array for helping people to find it.
		global $wpdb;
		$getids = easyreservations_get_rooms();
		foreach($getids as $getid){
			if($mode==1) $theroomidsarray .= $getid->ID.',';
			else $theroomidsarray[] = array($getid->ID, $getid->post_title);
		}
		return $theroomidsarray;
	}

	/**
	*	Return ids of all offers
	*
	*	$id = reservations id
	*/

	function reservations_get_offer_ids(){ //Get the IDs of the Offer Posts in array for helping people to find it.
		global $wpdb;
		$getids = easyreservations_get_offers();
		foreach($getids as $getid){
			$theofferidsarray[] = array($getid->ID, $getid->post_title);
		}
		return $theofferidsarray;
	}

	/**
	*	Returns type of reservation
	*
	*	$id = reservations id
	*/

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

	/**
	*	Check if a post is a room
	*
	*	$id = rooms id
	*/

	function reservations_is_room($id){
		$category=get_the_category($id);
		$roomcategory=get_option('reservations_room_category');
		if($category[0]->cat_ID == $roomcategory) return true;
		else return false;
	}

	/**
	*	Returns info box
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

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

	/**
	*	Get administration links
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

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

	/**
	*	Load button and add it to tinyMCE
	*/

	add_filter('mce_external_plugins', 'easyreservations_tiny_register');
	add_filter('mce_buttons', 'easyreservations_tiny_add_button', 0);

	function easyreservations_tiny_add_button($buttons){
		array_push($buttons, "separator", "easyReservations");
		return $buttons;
	}

	function easyreservations_tiny_register($plugin_array){
		$url = WP_PLUGIN_URL . '/easyreservations/js/tinyMCE/tinyMCE_shortcode_add.js';

		$plugin_array['easyReservations'] = $url;
		return $plugin_array;
	}

	/**
	*	Add screen settings to reservations main screen
	*/
 
	function easyreservations_screen_settings($current, $screen){

		if($screen->id == "toplevel_page_reservations"){
			if(isset($_POST['main_settings'])){
				if(isset($_POST['show_overview'])) $show_overview = 1; else $show_overview = 0;
				if(isset($_POST['show_table'])) $show_table = 1; else $show_table = 0;
				if(isset($_POST['show_upcoming'])) $show_upcoming = 1; else $show_upcoming = 0;
				if(isset($_POST['show_new'])) $show_new = 1; else $show_new = 0;
				if(isset($_POST['show_export'])) $show_export = 1; else $show_export = 0;
				if(isset($_POST['show_today'])) $show_today = 1; else $show_today = 0;
				
				$showhide = array( 'show_overview' => $show_overview, 'show_table' => $show_table, 'show_upcoming' => $show_upcoming, 'show_new' => $show_new, 'show_export' => $show_export, 'show_today' => $show_today );

				if(isset($_POST['table_color'])) $table_color = 1; else $table_color = 0;
				if(isset($_POST['table_id'])) $table_id = 1; else $table_id = 0;
				if(isset($_POST['table_name'])) $table_name = 1; else $table_name = 0;
				if(isset($_POST['table_from'])) $table_from = 1; else $table_from = 0;
				if(isset($_POST['table_to'])) $table_to = 1; else $table_to = 0;
				if(isset($_POST['table_nights'])) $table_nights = 1; else $table_nights = 0;
				if(isset($_POST['table_email'])) $table_email = 1; else $table_email = 0;
				if(isset($_POST['table_room'])) $table_room = 1; else $table_room = 0;
				if(isset($_POST['table_exactly'])) $table_exactly = 1; else $table_exactly = 0;
				if(isset($_POST['table_offer'])) $table_offer = 1; else $table_offer = 0;
				if(isset($_POST['table_persons'])) $table_persons = 1; else $table_persons = 0;
				if(isset($_POST['table_childs'])) $table_childs = 1; else $table_childs = 0;
				if(isset($_POST['table_country'])) $table_country = 1; else $table_country = 0;
				if(isset($_POST['table_message'])) $table_message = 1; else $table_message = 0;
				if(isset($_POST['table_custom'])) $table_custom = 1; else $table_custom = 0;
				if(isset($_POST['table_customp'])) $table_customp = 1; else $table_customp = 0;
				if(isset($_POST['table_paid'])) $table_paid = 1; else $table_paid = 0;
				if(isset($_POST['table_price'])) $table_price = 1; else $table_price = 0;
				if(isset($_POST['table_filter_month'])) $table_filter_month = 1; else $table_filter_month = 0;
				if(isset($_POST['table_filter_room'])) $table_filter_room = 1; else $table_filter_room = 0;
				if(isset($_POST['table_filter_offer'])) $table_filter_offer = 1; else $table_filter_offer = 0;
				if(isset($_POST['table_filter_days'])) $table_filter_days = 1; else $table_filter_days = 0;
				if(isset($_POST['table_search'])) $table_search = 1; else $table_search = 0;
				if(isset($_POST['table_bulk'])) $table_bulk = 1; else $table_bulk = 0;
				if(isset($_POST['table_onmouseover'])) $table_onmouseover = 1; else $table_onmouseover = 0;
				
				$table = array( 'table_color' => $table_color, 'table_id' => $table_id, 'table_name' => $table_name, 'table_from' => $table_from, 'table_to' => $table_to, 'table_nights' => $table_nights, 'table_email' => $table_email, 'table_room' => $table_room, 'table_exactly' => $table_exactly, 'table_offer' => $table_offer, 'table_persons' => $table_persons, 'table_childs' => $table_childs, 'table_country' => $table_country, 'table_message' => $table_message, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_paid' => $table_paid, 'table_price' => $table_price, 'table_filter_month' => $table_filter_month, 'table_filter_room' => $table_filter_room, 'table_filter_offer' => $table_filter_offer, 'table_filter_days' => $table_filter_days, 'table_search' => $table_search, 'table_bulk' => $table_bulk, 'table_onmouseover' => $table_onmouseover );

				if(isset($_POST['overview_onmouseover'])) $overview_onmouseover = 1; else $overview_onmouseover = 0;
				if(isset($_POST['overview_autoselect'])) $overview_autoselect = 1; else $overview_autoselect = 0;
				if(isset($_POST['overview_show_days'])) $overview_show_days = $_POST['overview_show_days']; else $overview_show_days = 30;
				if(isset($_POST['overview_show_rooms'])) $overview_show_rooms = implode(",", $_POST['overview_show_rooms']); else $overview_show_rooms = 30;
				if(isset($_POST['overview_show_avail'])) $overview_show_avail = 1; else $overview_show_avail = 0;

				$overview = array( 'overview_onmouseover' => $overview_onmouseover, 'overview_autoselect' => $overview_autoselect, 'overview_show_days' => $overview_show_days, 'overview_show_rooms' => $overview_show_rooms, 'overview_show_avail' => $overview_show_avail );

				update_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ));
				if(isset($_POST['daybutton'])) update_option("reservations_show_days",$_POST['daybutton']);
			}
			
			$main_options = get_option("reservations_main_options");
			$show = $main_options['show'];
			$table = $main_options['table'];
			$overview = $main_options['overview'];
			
			$current .= '<form method="post" id="er-main-settings-form">';
				$current .= '<input type="hidden" name="main_settings" value="1">';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Show/Hide content' , 'easyReservations').'</u></b><br>';
					$current .= '<input type="checkbox" name="show_overview" value="1" '.checked($show['show_overview'], 1, false).'> '.__( 'Overview' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="show_table" value="1" '.checked($show['show_table'], 1, false).'> '.__( 'Table' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="show_upcoming" value="1" '.checked($show['show_upcoming'], 1, false).'> '.__( 'Upcoming reservations' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="show_new" value="1" '.checked($show['show_new'], 1, false).'> '.__( 'New reservations' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="show_export" value="1" '.checked($show['show_export'], 1, false).'> '.__( 'Export' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="show_today" value="1" '.checked($show['show_today'], 1, false).'> '.__( 'What happen today' , 'easyReservations').'<br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Table informations' , 'easyReservations').'</u></b><br>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<input type="checkbox" name="table_color" value="1" '.checked($table['table_color'], 1, false).'> '.__( 'Color' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_id" value="1" '.checked($table['table_id'], 1, false).'> '.__( 'ID' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_name" value="1" '.checked($table['table_name'], 1, false).'> '.__( 'Name' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_from" value="1" '.checked($table['table_from'], 1, false).'> '.__( 'Arrival date  ' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_to" value="1" '.checked($table['table_to'], 1, false).'> '.__( 'Depature date  ' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_nights" value="1" '.checked($table['table_nights'], 1, false).'> '.__( 'Nights ' , 'easyReservations').'<br>';
					$current .= '</span>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<input type="checkbox" name="table_email" value="1" '.checked($table['table_email'], 1, false).'> '.__( 'eMail' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_room" value="1" '.checked($table['table_room'], 1, false).'> '.__( 'Room' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_exactly" value="1" '.checked($table['table_exactly'], 1, false).'> '.__( 'Room number' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_offer" value="1" '.checked($table['table_offer'], 1, false).'> '.__( 'Offer' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_persons" value="1" '.checked($table['table_persons'], 1, false).'> '.__( 'Adults' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_childs" value="1" '.checked($table['table_childs'], 1, false).'> '.__( 'Children' , 'easyReservations').'<br>';
					$current .= '</span>';
					$current .= '<span style="float:left;">';
						$current .= '<input type="checkbox" name="table_country" value="1" '.checked($table['table_country'], 1, false).'> '.__( 'Country' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_message" value="1" '.checked($table['table_message'], 1, false).'> '.__( 'Note' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_custom" value="1" '.checked($table['table_custom'], 1, false).'> '.__( 'Custom fields' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_customp" value="1" '.checked($table['table_customp'], 1, false).'> '.__( 'Custom prices' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_paid" value="1" '.checked($table['table_paid'], 1, false).'> '.__( 'Paid' , 'easyReservations').'<br>';
						$current .= '<input type="checkbox" name="table_price" value="1" '.checked($table['table_price'], 1, false).'> '.__( 'Price' , 'easyReservations').'<br>';
					$current .= '</span>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Table actions' , 'easyReservations').'</u></b><br>';
					$current .= '<input type="checkbox" name="table_filter_month" value="1" '.checked($table['table_filter_month'], 1, false).'> '.__( 'Filter by month' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="table_filter_room" value="1" '.checked($table['table_filter_room'], 1, false).'> '.__( 'Filter by room' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="table_filter_offer" value="1" '.checked($table['table_filter_offer'], 1, false).'> '.__( 'Filter by offer' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="table_filter_days" value="1" '.checked($table['table_filter_days'], 1, false).'> '.__( 'Choose days to show' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="table_search" value="1" '.checked($table['table_search'], 1, false).'> '.__( 'Search' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="table_bulk" value="1" '.checked($table['table_bulk'], 1, false).'> '.__( 'Bulk & Checkboxes' , 'easyReservations').'<br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:15px">';
					$current .= '<b><u>'.__( 'Show Rooms' , 'easyReservations').':</u></b><br>';
					$reservations_show_rooms = $overview['overview_show_rooms'];
					$roomArray = reservations_get_room_ids();
					foreach($roomArray as $theNumber => $raum){
						if($reservations_show_rooms == '') $check="checked";
						elseif( substr_count($reservations_show_rooms, $raum[0]) > 0) $check="checked";
						else $check="";
						$current.='<input type="checkbox" name="overview_show_rooms['.$theNumber.']" value="'.$raum[0].'" '.$check.'> '.__($raum[1]).'<br>';
					}
				$current .= '</p>';
				$current .= '<p style="float:left;">';
					$current .= '<b><u>'.__( 'Overview effects' , 'easyReservations').'</u></b><br>';
					$current .= '<input type="checkbox" name="table_onmouseover" value="1" '.checked($table['table_onmouseover'], 1, false).'> '.__( 'Highlight in overview at table hover' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="overview_onmouseover" value="1" '.checked($overview['overview_onmouseover'], 1, false).'> '.__( 'Overview onMouseOver Date & Select animation' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="overview_autoselect" value="1" '.checked($overview['overview_autoselect'], 1, false).'> '.__( 'Overview autoselect with inputs on add/edit' , 'easyReservations').'<br>';
					$current .= '<input type="checkbox" name="overview_show_avail" value="1" '.checked($overview['overview_show_avail'], 1, false).'> '.__( 'Show empty space for each room and day (+20% load)' , 'easyReservations').'<br>';
					$current.='<b><u>'.__( 'Show Days' , 'easyReservations' ).':</u></b><br>';
					$current.='<input type="text" name="overview_show_days" style="width:50px" value="'.$overview['overview_show_days'].'"> '.__( 'Days' , 'easyReservations' );
				$current .= '</p>';
				$current .= '<input type="submit" value="Save Changes" class="button-primary" style="float:right;margin-top:120px !important">';
			$current .= '</form>';
			

			
		}
		return $current;
	}

	add_filter('screen_settings', 'easyreservations_screen_settings', 10, 2);

/**
 * Define dashboard widget 
*/
 
	function easyreservations_dashboard_widget_function() {
		echo '<style>#easyreservations_dashboard_widget .inside { margin:0px; padding:0px; } #er-dash-table thead th { background:#EAEAEA;border-top:1px solid #ccc;border-bottom:1px solid #ccc; padding:3px !important; } #er-dash-table tbody tr:nth-child(odd) { background:#fff } #er-dash-table tbody td { font-weight:normal !important; padding:3px !important; }</style>';?>
		<script>
			function navibackground(a){
				var e = document.getElementsByName('sendajax'); 
				for(var i=0;i<e.length;i++){ e[i].style.color = '#21759B';e[i].style.fontWeight='normal';} 
				a.style.color='#000';
				a.style.fontWeight='bold';
			}
		</script>
		<div id="er-dash-navi" style="width:100%;padding:4px;">
			<a id="current" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Current</a> | 
			<a id="leaving" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Leaving today</a> | 
			<a id="arrival" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Arrival today</a> | 
			<a id="pending" name="sendajax" style="cursor:pointer;font-size:12px;" onclick="navibackground(this)">Pending</a> | 
			<a id="future" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Future</a>
			<span id="er-loading" style="float:right;"></span>
		</div>
		<div id="easy-dashboard-div"></div><?php
	}

	function easyreservations_add_dashboard_widgets() {
		wp_add_dashboard_widget('easyreservations_dashboard_widget', 'easyReservations Dashboard Widget', 'easyreservations_dashboard_widget_function');	
	}

	add_action('wp_dashboard_setup', 'easyreservations_add_dashboard_widgets' );
	
/**
 * Dashboards ajax request
*/

	function easyreservations_send_dashboard(){
		$nonce = wp_create_nonce( 'easy-dashboard' );
		?><script type="text/javascript" >	
		jQuery(document).ready(function(jQuery) {
			jQuery('a[name|="sendajax"]').click(function() {
				var loading = '<img style="margin-right:7px" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading.gif">';
				jQuery("#er-loading").html(loading);
				var data = {
					action: 'easyreservations_send_dashboard',
					security: '<?php echo $nonce; ?>',
					mode: jQuery(this).attr('id')
				};
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					jQuery("#easy-dashboard-div").html(response);
					jQuery("#er-loading").html('');
					return false;
				});
			});
		});		</script><?php
	}

	add_action('admin_head-index.php', 'easyreservations_send_dashboard');

/**
 * Dashboards ajax callback
*/
	function easyreservations_send_dashboard_callback() {
		global $wpdb; // this is how you get access to the database
		check_ajax_referer( 'easy-dashboard', 'security' );

		$mode =  $_POST['mode'];
		$dateToday = date("Y-m-d", time());
	 
		// response output
		if($mode == "current"){
			$query = $wpdb->get_results("SELECT id, name, arrivalDate, nights, room, special, number, childs FROM ".$wpdb->prefix ."reservations WHERE '$dateToday' BETWEEN arrivalDate AND arrivalDate + INTERVAL nights DAY AND approve='yes'"); // Search query
		} elseif($mode == "leaving"){
			$query = $wpdb->get_results("SELECT id, name, arrivalDate, nights, room, special, number, childs FROM ".$wpdb->prefix ."reservations WHERE arrivalDate = '$dateToday'  AND approve='yes'"); // Search query 
		} elseif($mode == "pending"){
			$query = $wpdb->get_results("SELECT id, name, arrivalDate, nights, room, special, number, childs FROM ".$wpdb->prefix ."reservations WHERE arrivalDate > NOW() AND approve=''"); // Search query 
		} elseif($mode == "arrival"){
			$query = $wpdb->get_results("SELECT id, name, arrivalDate, nights, room, special, number, childs FROM ".$wpdb->prefix ."reservations WHERE arrivalDate + INTERVAL nights DAY = '$dateToday' AND approve='yes'"); // Search query 
		} elseif($mode == "future"){
			$query = $wpdb->get_results("SELECT id, name, arrivalDate, nights, room, special, number, childs FROM ".$wpdb->prefix ."reservations WHERE  arrivalDate > NOW() AND approve='yes'"); // Search query 
		}

		$table = '<table id="er-dash-table" style="width:100%;text-align:left;font-weight:normal;border-spacing:0px">';
			$table .= '<thead>';
				$table .= '<tr>';
					$table .= '<th>'.__( 'Name' , 'easyReservations').'</th>';
					$table .= '<th>'.__( 'Date' , 'easyReservations').'</th>';
					$table .= '<th>'.__( 'Room' , 'easyReservations').'</th>';
					$table .= '<th style="text-align:center">'.__( 'Persons' , 'easyReservations').'</th>';
					$table .= '<th style="text-align:right">'.__( 'Price' , 'easyReservations').'</th>';
				$table .= '</tr>';
			$table .= '</thead>';
			$table .= '<tbody>';

		foreach($query as $num => $res){
			$dateanf = strtotime($res->arrivalDate);
			$dateend = strtotime($res->arrivalDate) + ($res->nights * 86400);
			if($num % 2 == 0) $class="odd";
			else $class="even";
				$table .= '<tr class="'.$class.'">';
					$table .= '<td><a href="admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
					$table .= '<td>'.date("d.m.Y", $dateanf).' - '.date("d.m.Y", $dateend).' ('.$res->nights.')</td>';
					$table .= '<td>'.get_the_title($res->room).'</td>';
					$table .= '<td style="text-align:center;">'.$res->number.' ('.$res->childs.')</td>';
					$table .= '<td style="text-align:right">'.easyreservations_get_price($res->id,1).'</td>';
				$table .= '</tr>';
		}

			$table .= '</tbody>';
		$table .= '</table>';
		
		echo $table;

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	add_action('wp_ajax_easyreservations_send_dashboard', 'easyreservations_send_dashboard_callback');
	function easyreservations_get_custom_array($custom){
		$customs=array_values(array_filter(explode("&;&", $custom)));
		$customarray = "";
		foreach($customs as $customfield){
			$customaexp=explode("&:&", $customfield);
			$customarray[] = array( 'title' => $customaexp[0], 'value' => $customaexp[1]); 
		}
		
		return $customarray;
	}
	
	function easyreservations_get_custom_price_array($customp){
		$customs=array_values(array_filter(explode("&;&", $customp)));
		foreach($customs as $customfield){
			$customexp=explode("&:&", $customfield);
			$priceexp=explode(":", $customexp[1]);
			$customparray[] = array( 'title' => $customexp[0], 'value' => $priceexp[0], 'price' => $priceexp[1]); 
		}
		
		return $customparray;
	}
?>