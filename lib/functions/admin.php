<?php
if(isset($_GET['page'])){
		$page=$_GET['page'];

	function easyreservations_load_mainstyle() {  //  Load Scripts and Styles

		wp_register_style('myStyleSheets', WP_PLUGIN_URL . '/easyreservations/css/style.css');
		wp_register_style('chosenStyle', WP_PLUGIN_URL . '/easyreservations/css/style_'.RESERVATIONS_STYLE.'.css');

		wp_enqueue_style( 'myStyleSheets');
		wp_enqueue_style( 'chosenStyle');
	}


	if($page == 'reservations' || $page== 'reservation-settings' || $page== 'reservation-statistics' ||  $page=='reservation-resources'){  //  Only load Styles and Scripts on Reservation Admin Page 
		add_action('admin_init', 'easyreservations_load_mainstyle');
	}

	function easyreservations_statistics_load() {  //  Load Scripts and Styles
		wp_register_style('jqplot_style', RESERVATIONS_JS_DIR . '/jQplot/jquery.jqplot.min.css' );
		wp_register_script('jqplot', RESERVATIONS_JS_DIR . '/jQplot/jquery.jqplot.min.js');
		wp_register_script('jqplot_plugin_pieRenderer', RESERVATIONS_JS_DIR . '/jQplot/plugins/jqplot.pieRenderer.min.js' );
		wp_register_script('jqplot_plugin_barRenderer', RESERVATIONS_JS_DIR . '/jQplot/plugins/jqplot.barRenderer.min.js' );
		wp_register_script('jqplot_plugin_highlighter', RESERVATIONS_JS_DIR . '/jQplot/plugins/jqplot.highlighter.min.js' );
		wp_register_script('jqplot_plugin_dateAxisRenderer', RESERVATIONS_JS_DIR . '/jQplot/plugins/jqplot.dateAxisRenderer.min.js' );
		wp_register_script('jqplot_plugin_categoryAxisRenderer', RESERVATIONS_JS_DIR . '/jQplot/plugins/jqplot.categoryAxisRenderer.min.js' );
	}

	if($page == 'reservation-statistics' || $page == 'reservations'){  //  Only load Styles and Scripts on Statistics Page
		add_action('admin_init', 'easyreservations_statistics_load');
	}

	function easyreservations_scripts_resources_load() {  //  Load Scripts and Styles
		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css' );
		wp_register_style('easy-cal-2', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_2.css');

		wp_enqueue_style('datestyle');
		wp_enqueue_style('easy-cal-2');
		wp_enqueue_script('jquery-ui-datepicker');

		wp_enqueue_style('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
	}

	if($page == 'reservation-resources'){  //  Only load Styles and Scripts on Resources Page
		add_action('admin_init', 'easyreservations_scripts_resources_load');
		add_action('admin_head', 'easyreservations_send_cal_admin');
		add_action('wp_ajax_easyreservations_send_cal_admin', 'easyreservations_send_calendar_callback');

	}
		
	function easyreservations_datepicker_load() {  //  Load Scripts and Styles for datepicker
		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css');
		wp_enqueue_style( 'datestyle');
		wp_enqueue_script('jquery-ui-datepicker');
	}
	if($page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
		add_action('admin_enqueue_scripts', 'easyreservations_datepicker_load');
	}

	/**
	*	Add help to settings
	*
	*/
	if(isset($page) && $page == 'reservation-settings'){
		add_filter('contextual_help', 'easyReservations_custom_help', 10, 3);
	}

	function easyReservations_custom_help($contextual_help, $screen_id, $screen) {
		$contextual_help = easyReservations_add_help_tabs();
		return $contextual_help;
	}

	function easyReservations_add_help_tabs(){
		$screen = get_current_screen();

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
			'content'	=> '<p><b><u>'.__('Shortcode adding Tool - tinyMCE button', 'easyReservations').'</u></b><br>'.__('When add or editing a post or page you\'ll see a button with the easyReservations logo in the header of the editor. After clicking on it a dialog box will open and let you add each of the three shortcodes (Form, User ControlPanel and Calendar) very easily.', 'easyReservations').'</p><p><b><u>'.__('Calendar', 'easyReservations').'</u></b><br>'.__('Everyone wanted, her it is: A fully flexible ajax calendar to show the availabilty of your rooms on the frontpage. It can have different styles and the price for the night can be shown in it. On start it shows the availibility of the pre-selected room. If its in the same page, post or widget like a room select it changes on select.', 'easyReservations').'</p><p><b><u>'.__('User edit', 'easyReservations').'</u></b><br>'.__('To let users edit their reservations afterwards you have to add a page with the shortcode [easy_edit]. Only add this shortcode one page. In the settings you have to enter a text that describes your guests the procedure of editing his reservation and the link to the page with the shortcode. Its recomment to add the calendar shortcode to the same page as the edit-shortcode.', 'easyReservations').'</p><p>'.__('The Guest have to enter his ID and email to see and change his reservation. I think this is secure enoought, because the user and the admin both get an email after edit. If the email changes, the old one will get a mail too.', 'easyReservations').'</p><p>'.__('The guest can edit his reservation only if the arrival date isn\'t past. After editing the reservation will reset to pending. Custom fields can be changed in a text-field, custom price fields can just get deselected.', 'easyReservations').'</p>',
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
			$pricetotal=0;

			sort($priceforarray);
			foreach( $priceforarray as $pricefor){
				$count++;
				if(is_int($count/2)) $class=' class="alternate"'; else $class='';
				$date=$pricefor['date'];
				if(preg_match("/(stay|loyal|custom price|early|pers|child)/i", $pricefor['type'])) $dateposted=' '; else $dateposted=date(RESERVATIONS_DATE_FORMAT, $date);
				$pricetotal+=$pricefor['priceday'];
				if($count == $arraycount) $onlastprice=' style="border-bottom: double 3px #000000;"';  else $onlastprice='';
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

		$res = $wpdb->get_results( $wpdb->prepare("SELECT meta_value FROM ".$wpdb->prefix ."postmeta WHERE meta_key='roomcount' AND meta_value > 0 ORDER BY meta_value DESC LIMIT 1")); // Get Higest Roomcount
		return $res[0]->meta_value;

	}

	/**
	*	Returns info box
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function easyreservations_reservation_info_box($id, $where, $status){
		$payStatus = reservations_check_pay_status($id);
		if($payStatus == 0) $paid = ' - <b style="text-transform: capitalize;color:#1FB512;">'. __( 'paid' , 'easyReservations' ).'</b>';
		else $paid = ' - <b style="text-transform: capitalize;color:#FF3B38;">'. __( 'unpaid' , 'easyReservations' ).'</b>';

		$infoBox = '<div class="explainbox" style="width:96%; margin-bottom:2px;"><div id="left" style=""><b><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.easyreservations_get_price($id).'</b></div><div id="right"><span style="float:right">'.reservations_get_administration_links($id, $where, $status).'</span></div><div id="center">'.easyreservations_format_status($status,1).' '.$paid.'</div></div>';

		return $infoBox;
	}

	/**
	*	Get administration links
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function reservations_get_administration_links($id, $where, $status){ //Get Links for approve, edit, trash, delete, view...

		$countits=0;
		$checkID = easyreservations_format_status($status);
		$administration_links = "";
		if($where != "approve" && $checkID != __("approved")) { $administration_links.='<a href="admin.php?page=reservations&approve='.$id.'">'.__( 'Approve' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' | '; $countits=0; }
		if($where != "reject" && $checkID != __("rejected")) { $administration_links.='<a href="admin.php?page=reservations&delete='.$id.'">'.__( 'Reject' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' | '; $countits=0; }
		if($where != "edit") { $administration_links.='<a href="admin.php?page=reservations&edit='.$id.'">'.__( 'Edit' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' | '; $countits=0; }
		$administration_links.='<a href="admin.php?page=reservations&sendmail='.$id.'">'.__( 'Mail' , 'easyReservations' ).'</a>'; $countits++;
		//if($countits > 0){ $administration_links.=' | '; $countits=0; }
		//if($where != "trash" AND $checkID != "trashed") { $administration_links.='<a href="admin.php?page=reservations&bulkArr[]='.$id.'&bulk=1">'.__( 'Trash' , 'easyReservations' ).'</a>'; $countits++; }

		return $administration_links;
	}

	function easyreservations_add_warn_notice(){
		echo html_entity_decode( '&lt;&#100;iv class=&quot;up&#100;at&#101;d&quot; style=&quot;wi&#100;th:97%&quot;&gt;&lt;p&gt;Th&#105;s &#112;l&#117;gi&#110; &#105;s f&#111;r &lt;a hr&#101;&#102;=&quot;htt&#112;://w&#111;rd&#112;re&#115;s.&#111;rg/&#101;xt&#101;nd/plugins/&#101;asyr&#101;serv&#97;ti&#111;ns/&quot;&gt;&#102;r&#101;e&lt;/a&gt;&#33; Pl&#101;a&#115;e c&#111;n&#115;id&#101;r <&#97; t&#97;rg&#101;t="_bl&#97;nk" hre&#102;="h&#116;tps:&#47;/w&#119;w.&#112;ay&#112;&#97;l.c&#111;m/cg&#105;-b&#105;n/w&#101;b&#115;cr?c&#109;d=_&#115;-xclick&amp;h&#111;st&#101;d_bu&#116;&#116;&#111;n_i&#100;=&#68;3NW9T&#68;VHB&#74;&#57;E">d&#111;na&#116;ing</&#97;>.&lt;/p&gt;&lt;/&#100;iv&gt;' );
	}

	add_action('er_set_main_side_top', 'easyreservations_add_warn_notice');

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
				if(isset($_POST['table_reservated'])) $table_reservated = 1; else $table_reservated = 0;
				if(isset($_POST['table_persons'])) $table_persons = 1; else $table_persons = 0;
				if(isset($_POST['table_childs'])) $table_childs = 1; else $table_childs = 0;
				if(isset($_POST['table_status'])) $table_status = 1; else $table_status = 0;
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
				if(isset($_POST['table_fav'])) $table_fav = 1; else $table_fav = 0;
				if(isset($_POST['table_onmouseover'])) $table_onmouseover = 1; else $table_onmouseover = 0;
				
					$table = array( 'table_color' => $table_color, 'table_id' => $table_id, 'table_name' => $table_name, 'table_from' => $table_from, 'table_fav' => $table_fav, 'table_to' => $table_to, 'table_nights' => $table_nights, 'table_email' => $table_email, 'table_room' => $table_room, 'table_exactly' => $table_exactly, 'table_offer' => $table_offer, 'table_persons' => $table_persons, 'table_childs' => $table_childs, 'table_country' => $table_country, 'table_message' => $table_message, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_paid' => $table_paid, 'table_price' => $table_price, 'table_filter_month' => $table_filter_month, 'table_filter_room' => $table_filter_room, 'table_filter_offer' => $table_filter_offer, 'table_filter_days' => $table_filter_days, 'table_search' => $table_search, 'table_bulk' => $table_bulk, 'table_onmouseover' => $table_onmouseover, 'table_reservated' => $table_reservated, 'table_status' => $table_status );

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
					$current .= '<label><input type="checkbox" name="show_overview" value="1" '.checked($show['show_overview'], 1, false).'> '.__( 'Overview' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_table" value="1" '.checked($show['show_table'], 1, false).'> '.__( 'Table' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_upcoming" value="1" '.checked($show['show_upcoming'], 1, false).'> '.__( 'Upcoming reservations' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_new" value="1" '.checked($show['show_new'], 1, false).'> '.__( 'New reservations' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_export" value="1" '.checked($show['show_export'], 1, false).'> '.__( 'Export' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_today" value="1" '.checked($show['show_today'], 1, false).'> '.__( 'What happen today' , 'easyReservations').'</label><br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Table informations' , 'easyReservations').'</u></b><br>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<label><input type="checkbox" name="table_color" value="1" '.checked($table['table_color'], 1, false).'> '.__( 'Color' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_id" value="1" '.checked($table['table_id'], 1, false).'> '.__( 'ID' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_name" value="1" '.checked($table['table_name'], 1, false).'> '.__( 'Name' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_from" value="1" '.checked($table['table_from'], 1, false).'> '.__( 'Arrival date  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_to" value="1" '.checked($table['table_to'], 1, false).'> '.__( 'Depature date  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_nights" value="1" '.checked($table['table_nights'], 1, false).'> '.__( 'Nights ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_reservated" value="1" '.checked($table['table_reservated'], 1, false).'> '.__( 'Reserved ' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<label><input type="checkbox" name="table_email" value="1" '.checked($table['table_email'], 1, false).'> '.__( 'eMail' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_room" value="1" '.checked($table['table_room'], 1, false).'> '.__( 'Room' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_exactly" value="1" '.checked($table['table_exactly'], 1, false).'> '.__( 'Room number' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_offer" value="1" '.checked($table['table_offer'], 1, false).'> '.__( 'Offer' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_persons" value="1" '.checked($table['table_persons'], 1, false).'> '.__( 'Adults' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_childs" value="1" '.checked($table['table_childs'], 1, false).'> '.__( 'Children' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_status" value="1" '.checked($table['table_status'], 1, false).'> '.__( 'Status' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;">';
						$current .= '<label><input type="checkbox" name="table_country" value="1" '.checked($table['table_country'], 1, false).'> '.__( 'Country' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_message" value="1" '.checked($table['table_message'], 1, false).'> '.__( 'Note' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_custom" value="1" '.checked($table['table_custom'], 1, false).'> '.__( 'Custom fields' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_customp" value="1" '.checked($table['table_customp'], 1, false).'> '.__( 'Custom prices' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_paid" value="1" '.checked($table['table_paid'], 1, false).'> '.__( 'Paid' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_price" value="1" '.checked($table['table_price'], 1, false).'> '.__( 'Price' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_fav" value="1" '.checked($table['table_fav'], 1, false).'> '.__( 'Favourites' , 'easyReservations').'</label><br>';
					$current .= '</span>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Table actions' , 'easyReservations').'</u></b><br>';
					$current .= '<label><input type="checkbox" name="table_filter_month" value="1" '.checked($table['table_filter_month'], 1, false).'> '.__( 'Filter by month' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_room" value="1" '.checked($table['table_filter_room'], 1, false).'> '.__( 'Filter by room' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_offer" value="1" '.checked($table['table_filter_offer'], 1, false).'> '.__( 'Filter by offer' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_days" value="1" '.checked($table['table_filter_days'], 1, false).'> '.__( 'Choose days to show' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_search" value="1" '.checked($table['table_search'], 1, false).'> '.__( 'Search' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_bulk" value="1" '.checked($table['table_bulk'], 1, false).'> '.__( 'Bulk & Checkboxes' , 'easyReservations').'</label><br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:15px">';
					$current .= '<b><u>'.__( 'Show Rooms' , 'easyReservations').':</u></b><br>';
					$reservations_show_rooms = $overview['overview_show_rooms'];
					$roomArray = easyreservations_get_rooms();
					foreach($roomArray as $theNumber => $raum){
						if($reservations_show_rooms == '') $check="checked";
						elseif( substr_count($reservations_show_rooms, $raum->ID) > 0) $check="checked";
						else $check="";
						$current.='<label><input type="checkbox" name="overview_show_rooms['.$theNumber.']" value="'.$raum->ID.'" '.$check.'> '.__($raum->post_title).'</label><br>';
					}
				$current .= '</p>';
				$current .= '<p style="float:left;">';
					$current .= '<b><u>'.__( 'Overview effects' , 'easyReservations').'</u></b><br>';
					$current .= '<label><input type="checkbox" name="table_onmouseover" value="1" '.checked($table['table_onmouseover'], 1, false).'> '.__( 'Highlight in overview at table hover' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_onmouseover" value="1" '.checked($overview['overview_onmouseover'], 1, false).'> '.__( 'Overview onMouseOver Date & Select animation' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_autoselect" value="1" '.checked($overview['overview_autoselect'], 1, false).'> '.__( 'Overview autoselect with inputs on add/edit' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_show_avail" value="1" '.checked($overview['overview_show_avail'], 1, false).'> '.__( 'Show empty space for each room and day (+20% load)' , 'easyReservations').'</label><br>';
					$current.='<b><u>'.__( 'Show Days' , 'easyReservations' ).':</u></b><br>';
					$current.='<input type="text" name="overview_show_days" style="width:50px" value="'.$overview['overview_show_days'].'"> '.__( 'Days' , 'easyReservations' );
				$current .= '</p>';
				$current .= '<input type="submit" value="Save Changes" class="button-primary" style="float:right;margin-top:120px !important">';
			$current .= '</form>';
		}
		return $current;
	}

	add_filter('screen_settings', 'easyreservations_screen_settings', 10, 2);

	function easyreservations_get_user_options($sel = 0){

		$blogusers = get_users();
		$options = '';

		foreach ($blogusers as $usr){
			if($sel == $usr->ID) $selected = 'selected="selected"'; else $selected = '';
			$options.='<option value='.$usr->ID.' '.$selected.'>'.$usr->display_name.'</option>';
		}
		return $options;
	}

	if(isset($page) && $page == 'reservations'){
		if(isset($_GET['edit']) || isset($_GET['add'])){
			add_action('admin_head', 'easyreservations_send_price_admin');
			add_action('wp_ajax_easyreservations_send_price_admin', 'easyreservations_send_price_callback');
		} else {
			add_action('admin_head', 'easyreservations_send_table');
			add_action('admin_head', 'easyreservations_send_fav');
		}
	}
	
	function easyreservations_get_roomname_options($number, $max, $room, $roomnames = ''){
		if(empty($roomnames)) $roomnames = get_post_meta($room, 'easy-resource-roomnames', TRUE);
		$options = '';
		for($i=0; $i < $max; $i++){
			if(isset($roomnames[$i]) && !empty($roomnames[$i])) $name = $roomnames[$i];
			else $name = $i+1;
			if($number == $i+1) $selected='selected="selected"'; else $selected='';
			$options .= '<option value="'.($i+1).'">'.$name.'</option>';
		}
		return $options;
	}

}
	/* *
	*	Table ajax request
	*/

	function easyreservations_send_table(){
		$nonce = wp_create_nonce( 'easy-table' );
		?><script type="text/javascript" >	
			function easyreservation_send_table(typ, paging, order, orderby){
				var loading = '<img style="margin-right:7px;margin-top:7px" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading1.gif">';
				jQuery("#er-table-loading").html(loading);
				
				if(!order){
					var orderfield = document.getElementById('easy-table-order');
					if(orderfield) var order = orderfield.value;
					else var order = '';
				}
				if(!orderby){
					var orderbyfield = document.getElementById('easy-table-orderby');
					if(orderbyfield) var orderby = orderbyfield.value;
					else var orderby = '';
				}

				var searchfield = document.getElementById('easy-table-search-field');
				if(searchfield) var searching = searchfield.value;
				else var searching = '';

				var searchdatefield = document.getElementById('easy-table-search-date');
				if(searchdatefield) var searchdatefield = searchdatefield.value;
				else var searchdatefield = '';

				var specialselector = document.getElementById('easy-table-specialselector');
				if(specialselector) var specialselect = specialselector.value;
				else var specialselect = '';

				var monthselector = document.getElementById('easy-table-monthselector');
				if(monthselector) var monthselect = monthselector.value;
				else var monthselect = '';

				var roomselector = document.getElementById('easy-table-roomselector');
				if(roomselector) var roomselect = roomselector.value;
				else var roomselect = '';

				var perpage = document.getElementById('easy-table-perpage-field');
				if(perpage) var perge = perpage.value;
				else var perge = '';
				
				if(typ && typ != '') location.hash = typ;
				else if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ != 'current' && typ != 'pending' && typ != 'deleted' && typ != 'all' && typ != 'old' && typ != 'trash' && typ != 'favourite' ) typ = 'active';
				
				var data = {
					action: 'easyreservations_send_table',
					security: '<?php echo $nonce; ?>',
					typ:typ,
					search:searching,
					specialselector:specialselect,
					monthselector:monthselect,
					searchdate:searchdatefield,
					roomselector:roomselect,
					perpage:perge,
					order:order,
					orderby:orderby,
					paging:paging
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {

					jQuery("#easy-table-div").html(response);
					return false;
				});
			}

			jQuery(window).bind('hashchange', function() {
				if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ == 'active' || typ == 'current' || typ == 'pending' || typ == 'deleted' || typ == 'all' || typ == 'old' || typ == 'trash' || typ == 'favourite' ) easyreservation_send_table(typ, 1);
			});
		</script><?php
	}

	/**
	*
	*	Table ajax callback
	*
	*/

	function easyreservations_send_table_callback() {
		global $wpdb; // this is how you get access to the database
		check_ajax_referer( 'easy-table', 'security' );

		if(isset($_POST['typ'])) $typ=$_POST['typ'];
		else $typ = 'active';
		$orderby = ''; $order = ''; $search = '';

		if($_POST['search'] != '') $search = $_POST['search'];
		if($_POST['order'] != '') $order = $_POST['order'];
		if($_POST['orderby'] != '') $orderby = $_POST['orderby'];
		if($_POST['perpage'] != '') $perpage = $_POST['perpage'];
		else $perpage = get_option("reservations_on_page");

		$main_options = get_option("reservations_main_options");

		$table_options =  $main_options['table'];
		$regular_guest_explodes = explode(",", str_replace(" ", "", get_option("reservations_regular_guests")));
		foreach( $regular_guest_explodes as $regular_guest) $regular_guest_array[]=$regular_guest;

		$selectors='';
		if(!isset($table_options['table_fav']) || $table_options['table_fav'] == 1){
			global $current_user;
			$current_user = wp_get_current_user();
			$user = $current_user->ID;
			$favourite = get_user_meta($user, 'reservations-fav', true);
			if(!empty($favourite) && is_array($favourite)) $favourite_sql = 'id in('.implode(",", $favourite).')'; 
			else $favourite = array();
		}

		if($_POST['specialselector'] > 0){
			$specialselector=$_POST['specialselector'];
			$selectors.="AND special='$specialselector' ";
		}
		if($_POST['monthselector'] > 0){
			$monthselector=$_POST['monthselector'];
			$selectors.="AND dat='$monthselector' ";
		}
		if($_POST['roomselector'] > 0){
			$roomselector=$_POST['roomselector'];
			$selectors.="AND room='$roomselector' ";
		}

		if($_POST['searchdate'] != ''){
			$search_date = $_POST['searchdate'];
			$search_date_stamp = strtotime($search_date);
			$search_date_mysql = date("Y-m-d", $search_date_stamp);
			$selectors .= "AND '$search_date_mysql' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)";
		}
		$rooms_sql  = ''; $offers_sql =''; $permission_selectors = '';
		if(!current_user_can('manage_options')) $rooms_sql = easyreservations_get_allowed_rooms_mysql();
		if(!current_user_can('manage_options')) $offers_sql = easyreservations_get_allowed_offers_mysql();

		if(!empty($rooms_sql)) $permission_selectors.= ' AND room in '.$rooms_sql;
		if(!empty($offers_sql)) $permission_selectors.= ' AND special in '.$offers_sql;

		$zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) > DATE(NOW())";
		$orders="ASC";
		$ordersby="arrivalDate";

		if(!empty($search)) $searchstr = "AND (name like '%1\$s' OR id like '%1\$s' OR email like '%1\$s' OR notes like '%1\$s' OR arrivalDate like '%1\$s')";
		else $searchstr = "";

		$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items3 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items4 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) < DATE(NOW()) $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items5 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='del' $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items7 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(NOW()) BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items6 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE 1=1 $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		if(isset($favourite_sql)) $countfav = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $favourite_sql $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		else $favourite_sql = ' 1 = 1 ';
		if(!isset($typ) || $typ=='active' || $typ=='') { $type="approve='yes'"; $items=$items1; $orders="ASC";  } // If type is actice
		elseif($typ=="current") { $type="approve='yes'"; $items=$items7; $orders="ASC"; $zeichen ="AND DATE(NOW()) BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY "; } // If type is current
		elseif($typ=="pending") { $type="approve=''"; $items=$items3; $ordersby="id"; $orders="DESC"; } // If type is pending
		elseif($typ=="deleted") { $type="approve='no'"; $items=$items2; } // If type is rejected
		elseif($typ=="old") { $type="approve='yes'"; $items=$items4; $zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) < DATE(NOW())";  } // If type is old
		elseif($typ=="trash") { $type="approve='del'"; $items=$items5; $zeichen=""; } // If type is trash
		elseif($typ=="all") { $type="1=1"; $items=$items6; $zeichen=""; } // If type is all
		elseif($typ=="favourite") { $type=$favourite_sql; $items=$countfav; $zeichen=""; } // If type is all

		if($order=="ASC") $orders="ASC";
		elseif($order=="DESC") $orders="DESC";

		if($orderby=="date") $ordersby="arrivalDate";
		elseif($orderby=="name") $ordersby="name";
		elseif($orderby=="room") $ordersby="room";
		elseif($orderby=="special") $ordersby="special";
		elseif($orderby=="nights") $ordersby="nights";
		elseif($orderby=="reservated") $ordersby="reservated";

		if(empty($orderby) && $typ=="pending") { $ordersby="id"; $orders="DESC"; }
		if(empty($orderby) && $typ=="old") { $ordersby="arrivalDate"; $orders="DESC"; }
		if(empty($orderby) && $typ=="all") { $ordersby="arrivalDate"; $orders="DESC"; }

		if(isset($specialselector) || isset($monthselector) || isset($roomselector)){
			$variableitems = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $type $selectors $zeichen $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
			$items=$variableitems;
		}

		if(!isset($specialselector)) $specialselector="";
		if(!isset($roomselector)) $roomselector="";

		$pagei = 1;

		if(isset($items) && $items > 0) {

			$p = new easy_pagination;
			$p->items($items);
			$p->limit($perpage); // Limit entries per page
			$p->target($typ);
			$pagination = 0;
			$p->currentPage($pagination); // Gets and validates the current page
			$p->calculate(); // Calculates what to show
			$p->parameterName('paging');
			$p->adjacents(1); //No. of page away from the current page

			if(isset($_POST['paging'])) {
				$pagei = $_POST['paging'];
			} else {
				$pagei = 1;
			}

			$p->page = $pagei;

			$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
		} else $limit = 'LIMIT 0'; ?>
		<input type="hidden" id="easy-table-order" value="<?php echo $order;?>"><input type="hidden" id="easy-table-orderby" value="<?php echo $orderby;?>">
		<table style="width:99%;">
			<tr> <!-- Type Chooser //--> 
				<td style="white-space:nowrap;width:auto" class="no-select" nowrap>
					<ul id="easy-table-navi" class="subsubsub" style="float:left;white-space:nowrap">
						<li><a onclick="easyreservation_send_table('active', 1)" <?php if(!isset($typ) || (isset($typ) && $typ == 'active')) echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
						<li><a onclick="easyreservation_send_table('current', 1)" <?php if(isset($typ) && $typ == 'current') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Current' , 'easyReservations' ));?><span class="count"> (<?php echo $items7; ?>)</span></a> |</li>
						<li><a onclick="easyreservation_send_table('pending', 1)" <?php if(isset($typ) && $typ == 'pending') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
						<li><a onclick="easyreservation_send_table('deleted', 1)" <?php if(isset($typ) && $typ == 'deleted') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
						<li><a onclick="easyreservation_send_table('all', 1)" <?php if(isset($typ) && $typ == 'all') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
						<li><a onclick="easyreservation_send_table('old', 1)" <?php if(isset($typ) && $typ == 'old') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
						<?php if( $items5 > 0 ){ ?>| <li><a onclick="easyreservation_send_table('trash', <?php echo $pagei; ?>)" <?php if(isset($typ) && $typ == 'trash') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
						<?php if( isset($countfav) && $countfav > 0 ){ ?><li>| <a onclick="easyreservation_send_table('favourite', <?php echo $pagei; ?>)" style="cursor:pointer"><img style="vertical-align:text-bottom" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/css/images/star_full<?php if(isset($typ) && $typ == 'favourite') echo '_hover'; ?>.png"><span class="count"> (<span  id="fav-count"><?php echo $countfav; ?></span>)</span></a></li><?php } ?>
					</ul>
				</td>
				<td style="width:22px"><span style="float:left;" id="er-table-loading"></span></td>
				<td style="text-align:center; font-size:12px;" nowrap><!-- Begin of Filter //--> 
				<?php if($table_options['table_filter_month'] == 1){ ?>
					<select name="monthselector"  id="easy-table-monthselector" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'Show all Dates' , 'easyReservations' ));?></option><!-- Filter Months //--> 
					<?php
						$posts = "SELECT DISTINCT dat FROM ".$wpdb->prefix ."reservations GROUP BY dat ORDER BY dat ";
						$results = $wpdb->get_results($posts);

						foreach( $results as $result ){
							$dat=$result->dat;
							$zerst = explode("-",$dat);
							if($zerst[1]=="01") $month=__( 'January' , 'easyReservations' ); elseif($zerst[1]=="02") $month=__( 'February' , 'easyReservations' ); elseif($zerst[1]=="03") $month=__( 'March' , 'easyReservations' ); elseif($zerst[1]=="04") $month=__( 'April' , 'easyReservations' ); elseif($zerst[1]=="05") $month=__( 'May' , 'easyReservations' ); elseif($zerst[1]=="06") $month=__( 'June' , 'easyReservations' ); elseif($zerst[1]=="07") $month=__( 'July' , 'easyReservations' ); elseif($zerst[1]=="08") $month=__( 'August' , 'easyReservations' ); elseif($zerst[1]=="09") $month=__( 'September' , 'easyReservations' ); elseif($zerst[1]=="10") $month=__( 'October' , 'easyReservations' ); elseif($zerst[1]=="11") $month=__( 'November' , 'easyReservations' ); elseif($zerst[1]=="12") $month=__( 'December' , 'easyReservations' );
							if(isset($monthselector) && $monthselector == $dat) $selected = 'selected="selected"'; else $selected ="";
							echo '<option value="'.$dat.'" '.$selected.'>'.$month.' '.__($zerst[0]).'</option>'; 
						} ?>
					</select>
					<?php } ?>
					<?php if($table_options['table_filter_room'] == 1){ ?>
						<select name="roomselector" id="easy-table-roomselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all Rooms' , 'easyReservations' ));?></option><?php echo reservations_get_room_options($roomselector); ?></select>
					<?php } if($table_options['table_filter_offer'] == 1){ ?>
						<select name="specialselector" id="easy-table-specialselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all Offers ' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options($specialselector); ?></select>
					<?php } if($table_options['table_filter_days'] == 1){ ?><input size="1px" type="text" id="easy-table-perpage-field" name="perpage" value="<?php echo $perpage; ?>" maxlength="3" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"></input><input class="easySubmitButton-secondary" type="submit" value="<?php  printf ( __( 'Filter' , 'easyReservations' )); ?>">
					<?php } ?>
				</td>
				<td style="width:3%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
					<img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/refresh.png" style="vertical-align:text-bottom" onclick="resetTableValues()">
					<?php if($table_options['table_search'] == 1){ ?>
						<input type="text" onchange="easyreservation_send_table('all', 1)" style="width:77px;text-align:center" id="easy-table-search-date" value="<?php if(isset($search_date)) echo $search_date; ?>">
						<input type="text" onchange="easyreservation_send_table('all', 1)" style="width:130px;" id="easy-table-search-field" name="search" value="<?php if(isset($search)) echo $search;?>" class="all-options"></input>
						<input class="easySubmitButton-secondary" type="submit" value="<?php  printf ( __( 'Search' , 'easyReservations' )); ?>" onclick="easyreservation_send_table('all', 1)">
					<?php } ?>
				</td>
			</tr>
		</table>
		<form action="admin.php?page=reservations" method="get" name="frmAdd" id="frmAdd"><?php wp_nonce_field('easy-main-bulk','easy-main-bulk'); ?>
		<table  class="reservationTable <?php echo RESERVATIONS_STYLE; ?>" style="width:99%;"> <!-- Main Table //-->
			<thead> <!-- Main Table Header //-->
				<tr><?php $countrows = 0; ?>
					<?php if($table_options['table_color'] == 1){ $countrows++; ?>
						<th style="max-width:4px;padding:0px;"></th>
					<?php } if($table_options['table_bulk'] == 1){ $countrows++; ?>
						<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')" style="margin-top:2px"></th>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ $countrows++; ?>
						<th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
						<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_from'] == 1 || $table_options['table_to'] == 1 || $table_options['table_nights'] == 1){ $countrows++; ?>
						<th><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
						<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ $countrows++; ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )">
						<?php } elseif($order=="DESC" and $orderby=="reservated") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reservated' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )"><?php } ?><?php printf ( __( 'Reserved' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_status'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'Status' , 'easyReservations' )); ?></th>
					<?php } if($table_options['table_email'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ $countrows++; ?>
						<th style="text-align:center"><?php if($table_options['table_persons'] == 1 && $table_options['table_childs'] == 1) printf ( __( 'Persons' , 'easyReservations' )); elseif($table_options['table_persons'] == 1) echo __( 'Adults' , 'easyReservations' ); else echo __( 'Children\'s' , 'easyReservations' );?></th>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){ $countrows++; ?>
						<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )">
						<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_offer'] == 1){ $countrows++; ?>
						<th><?php if($order=="ASC" and $orderby=="special") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'special' )">
						<?php } elseif($order=="DESC" and $orderby=="special") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'special' )">
						<?php } else { ?><a class="stand2"   onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'special' )"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_country'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_message'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_custom'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'Custom fields' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_customp'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'Custom prices' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_paid'] == 1){ $countrows++; ?>
						<th style="text-align:right"><?php printf ( __( 'Paid' , 'easyReservations' ));?></th>
					<?php }  if($table_options['table_price'] == 1){ $countrows++; ?>
						<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?php if($table_options['table_color'] == 1){ ?>
						<th style="max-width:4px;padding:0px;"></th>
					<?php } if($table_options['table_bulk'] == 1){ ?>
						<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
						<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_from'] == 1 || $table_options['table_to'] == 1 || $table_options['table_nights'] == 1){ ?>
						<th><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
						<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )">
						<?php } elseif($order=="DESC" and $orderby=="reservated") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reservated' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )"><?php } ?><?php printf ( __( 'Reserved' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_status'] == 1){ ?>
						<th><?php printf ( __( 'Status' , 'easyReservations' )); ?></th>
					<?php } if($table_options['table_email'] == 1){ ?>
						<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ ?>
						<th style="text-align:center"><?php if($table_options['table_persons'] == 1 && $table_options['table_childs'] == 1) printf ( __( 'Persons' , 'easyReservations' )); elseif($table_options['table_persons'] == 1) echo __( 'Adults' , 'easyReservations' ); else echo __( 'Children\'s' , 'easyReservations' );?></th>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){ ?>
						<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'room' )">
						<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_offer'] == 1){ ?>
						<th><?php if($order=="ASC" and $orderby=="special") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'special' )">
						<?php } elseif($order=="DESC" and $orderby=="special") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'special' )">
						<?php } else { ?><a class="stand2"   onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'special' )"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_country'] == 1){ ?>
						<th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_message'] == 1){ ?>
						<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_custom'] == 1){ ?>
						<th><?php printf ( __( 'Custom fields' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_customp'] == 1){ ?>
						<th><?php printf ( __( 'Custom prices' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_paid'] == 1){ ?>
						<th style="text-align:right"><?php printf ( __( 'Paid' , 'easyReservations' ));?></th>
					<?php }  if($table_options['table_price'] == 1){ ?>
						<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
					<?php } ?>
				</tr>
			</tfoot>
			<tbody>
			<?php
				$nr=0;
				$export_ids = '';
				$time = strtotime(date("d.m.Y", time()));
				$sql = "SELECT id, arrivalDate, name, email, number, childs, nights, notes, room, roomnumber, country, special, approve, price, custom, customp, reservated FROM ".$wpdb->prefix ."reservations 
						WHERE $type $selectors $zeichen $searchstr $permission_selectors ORDER BY $ordersby $orders $limit";  // Main Table query
				$result = $wpdb->get_results( $wpdb->prepare($sql, '%' . like_escape($search) . '%'));

				if(count($result) > 0 ){

					foreach($result as $res){
						$room=$res->room;
						$id=$res->id;
						$name = $res->name;
						$nights=$res->nights;
						$person=$res->number;
						$childs=$res->childs;
						$special=$res->special;
						$rooms=__(get_the_title($room));

						if($nr%2==0) $class="alternate"; else $class="";
						$nr++;
						$timpstampanf=strtotime($res->arrivalDate);
						$timestampend=(86400*$nights)+$timpstampanf;

						if(in_array($res->email, $regular_guest_array)) $highlightClass='highlighter';
						else $highlightClass='';
						$export_ids .= $id.', ';

						if($time - $timpstampanf > 0 AND $time+86400 - $timestampend > 0) $sta = "er_res_old";
						elseif($time+86400 - $timpstampanf > 0 AND $time - $timestampend <= 0) $sta = "er_res_now";
						else $sta = "er_res_future";
						if(isset($favourite)){
							if(in_array($id, $favourite)){
								$favclass = ' easy-fav';
								$favid = 'fav-'.$id;
								if($typ != 'favourite')$highlightClass = 'highlighter';
							} else {
								$favclass = ' easy-unfav';
								$favid = 'unfav-'.$id;
							}
						} ?>
				<tr class="<?php echo $class.' '.$highlightClass; ?>" height="47px" <?php if($table_options['table_onmouseover'] == 1 && $res->approve == "yes" && !empty($res->roomnumber)){ ?>onmouseover="fakeClick('<?php echo $timpstampanf; ?>', '<?php echo $timestampend; ?>', '<?php echo $res->room; ?>', '<?php echo $res->roomnumber; ?>', 'yellow');" onmouseout="changer()"<?php } ?>><!-- Main Table Body //-->
					<?php if($table_options['table_color'] == 1){ ?>
						<td class="<?php echo $sta; ?>" style="max-width:4px !important;padding:0px !important;"></td>
					<?php } if($table_options['table_bulk'] == 1 || isset($favourite)){ ?>
						<td width="2%" style="text-align:center;vertical-align:middle;">
							<?php if($table_options['table_bulk'] == 1){ ?><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $id;?>"><?php } ?>
							<?php if(isset($favourite)){ ?><div class="easy-favourite <?php echo $favclass; ?>" id="<?php echo $favid; ?>" onclick="easyreservations_send_fav(this)"></div><?php } ?>
						</td>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<td  valign="top" class="row-title" valign="top" nowrap>
							<div class="test">
								<?php if($table_options['table_name'] == 1){ ?>
									<a href="admin.php?page=reservations&view=<?php echo $id;?>"><?php echo $name;?></a>
								<?php } if($table_options['table_id'] == 1) echo ' (#'.$id.')'; ?>
								<?php do_action('er_table_name_custom', $res->custom, $id); ?>
								<div class="test2" style="margin:5px 0 0px 0;">
									<a href="admin.php?page=reservations&edit=<?php echo $id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a> 
									<?php if(isset($typ) && ($typ=="deleted" || $typ=="pending")) { ?>| <a style="color:#28a70e;" href="admin.php?page=reservations&approve=<?php echo $id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a>
									<?php } if(!isset($typ) || (isset($typ) && ($typ=="active" || $typ=="pending"))) { ?> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&delete=<?php echo $id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a>
									<?php } if(isset($typ) && $typ=="trash") { ?>| <a href="admin.php?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&easy-main-bulk=&bulkArr[]=<?php echo $id;?>&bulk=3&easy-main-bulk=<?php echo wp_create_nonce('easy-main-bulk'); ?>"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a><?php } ?> | <a href="admin.php?page=reservations&sendmail=<?php echo $id;?>"><?php echo __( 'Mail' , 'easyReservations' );?></a>
								</div>
							</div>
						</td>
					<?php } if($table_options['table_from'] == 1 || $table_options['table_to'] == 1 || $table_options['table_nights'] == 1){ ?>
						<td nowrap><?php if($table_options['table_from'] == 1) echo date(RESERVATIONS_DATE_FORMAT,$timpstampanf); if($table_options['table_from'] == 1 && $table_options['table_to'] == 1) echo '-';  if($table_options['table_to'] == 1) echo date(RESERVATIONS_DATE_FORMAT,$timestampend);?><?php if($table_options['table_nights'] == 1){ ?> <small>(<?php echo $nights; ?> <?php printf ( __( 'Nights' , 'easyReservations' ));?>)</small><?php } ?></td>
					<?php } if($table_options['table_reservated'] == 1){ ?>
						<td style="text-align:center"><b><?php echo human_time_diff( strtotime($res->reservated) );?></b></td>
					<?php } if($table_options['table_status'] == 1){ 
									$status = easyreservations_format_status($res->approve, 1); ?>
						<td><b style="color:<?php echo $color; ?>"><?php echo $status; ?></b></td>
					<?php } if($table_options['table_email'] == 1){ ?>
						<td><a href="admin.php?page=reservations&sendmail=<?php echo $id; ?>"><?php echo $res->email;?></a></td>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ ?>
						<td style="text-align:center;"><?php if($table_options['table_name'] == 1) echo $person; if($table_options['table_from'] == 1 && $table_options['table_to'] == 1) echo ' / '; if($table_options['table_childs'] == 1) echo $childs; ?></td>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
						<td nowrap><?php if($table_options['table_room'] == 1) echo '<a href="admin.php?page=reservation-resources&room='.$room.'">'.__($rooms).'</a> '; if($table_options['table_exactly'] == 1 && isset($res->roomnumber)) echo easyreservations_get_roomname($res->roomnumber, $room); ?></td>
					<?php }  if($table_options['table_offer'] == 1){  ?>
						<td nowrap><?php if($special > 0) echo '<a href="admin.php?page=reservation-resources&room='.$special.'">'.__(get_the_title($special)).'</a>'; else echo __( 'None' , 'easyReservations' ); ?></td>
					<?php }  if($table_options['table_country'] == 1){  ?>
						<td nowrap><?php if($special > 0) echo easyReservations_country_name( $res->country); ?></td>
					<?php }  if($table_options['table_message'] == 1){ ?>
						<td><?php echo substr($res->notes, 0, 36); ?></td>
					<?php }  if($table_options['table_custom'] == 1){ ?>
						<td><?php $customs = easyreservations_get_customs($res->custom, 0, 'cstm');
								if(!empty($customs)){
									foreach($customs as $custom){
										echo '<b>'.$custom['title'].':</b> '.$custom['value'].'<br>';
									}
								}?></td>
					<?php }  if($table_options['table_customp'] == 1){ ?>
						<td><?php $customs = easyreservations_get_customs($res->customp, 0, 'cstm');
								if(!empty($customs)){
									foreach($customs as $custom){
										echo '<b>'.$custom['title'].':</b> '.$custom['value'].' - '.reservations_format_money($custom['amount'], 1).'<br>';
									}
								}?></td>
					<?php } if($table_options['table_paid'] == 1){  ?>
						<td nowrap style="text-align:right"><?php $theExplode = explode(";", $res->price); if(isset($theExplode[1]) && $theExplode[1] > 0) echo reservations_format_money( $theExplode[1], 1); else echo reservations_format_money( '0', 1); ?></td>
					<?php }  if($table_options['table_price'] == 1){  ?>
						<td nowrap style="text-align:right"><?php echo easyreservations_get_price($id, 1); ?></td>
					<?php } ?>
				</tr>
			<?php }
			} else { ?> <!-- if no results form main quary !-->
					<tr>
						<td colspan="<?php echo $countrows; ?>"><b><?php printf ( __( 'No Reservations found!' , 'easyReservations' ));?></b></td> <!-- Mail Table Body if empty //-->
					<tr>
			<?php } ?>
			</tbody>
		</table>
		<table  style="width:99%;"> 
			<tr>
				<td style="width:33%;"><!-- Bulk Options //-->
					<?php if($table_options['table_bulk'] == 1){ ?><select name="bulk" id="bulk"><option select="selected" value="0"><?php echo __( 'Bulk Actions' ); ?></option><?php if((isset($typ) AND $typ!="trash") OR !isset($typ)) { ?><option value="1"><?php printf ( __( 'Move to Trash' , 'easyReservations' ));?></option><?php }  if(isset($typ) AND $typ=="trash") { ?><option value="2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></option><option value="3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></option><?php } ;?></select>  <input class="easySubmitButton-secondary" type="submit" value="<?php printf ( __( 'Apply' , 'easyReservations' ));?>" /></form><?php } ?>
				</td>
				<td style="width:33%;" nowrap> <!-- Pagination  //-->
					<?php if($items > 0) { ?><div class="tablenav" style="text-align:center; margin:0 115px 4px 0;"><div style="background:#ffffff;" class='tablenav-pages'><?php echo $p->show(); ?></div></div><?php } ?>
				</td>
				<td style="width:33%;margin-left: auto; margin-right: 0pt; text-align: right;"> <!-- Num Elements //-->
					<span class="displaying-nums"><?php echo $nr;?> <?php printf ( __( 'Elements' , 'easyReservations' ));?></span>
				</td>
			</tr>
		</table>
		</form>
		<script>
			createTablePickers();
			var field = document.getElementById('easy-export-id-field'); 
			if(field) field.value = '<?php echo $export_ids; ?>';
		</script><?php
		exit;
	}

	add_action('wp_ajax_easyreservations_send_table', 'easyreservations_send_table_callback');

	function easyreservations_get_price_filter_description($filtertype){
		if($filtertype['cond'] == 'range'){
			$the_condtion = sprintf(__( 'If the day to calculate is beween %1$s and %2$s else' , 'easyReservations' ), '<b>'.$filtertype['from'].'</b>', '<b>'.$filtertype['to'].'</b>' ).' <b style="font-size:17px">&#8595;</b>';
		} elseif($filtertype['cond'] == 'date'){
			$the_condtion = sprintf(__( 'If the day to calculate is %1$s else' , 'easyReservations' ), '<b>'.$filtertype['date'].'</b>' ).' <b style="font-size:17px">&#8595;</b>';
		} else {
			if(!empty($filtertype['day'])){
				$daycondition = '';
				$days = explode(',', $filtertype['day']);
				$daynames= easyreservations_get_date_name(0, 3);
				foreach($days as $day){
					$daycondition .= $daynames[$day-1].', ';
				}
			}

			if(!empty($filtertype['cw'])){
				$cwcondition = $filtertype['cw'];
			}

			if(!empty($filtertype['month'])){
				$monthcondition = '';
				$monthes = explode(',', $filtertype['month']);
				$monthesnames= easyreservations_get_date_name(1, 3);
				foreach($monthes as $month){
					$monthcondition .=  $monthesnames[$month-1].', ';
				}
			}

			if(!empty($filtertype['quarter'])){
				$qcondition = $filtertype['quarter'];
			}

			if(!empty($filtertype['year'])){
				$ycondition = $filtertype['year'];
			}

			$itcondtion=__("If day to calculate is ", "easyReservations");
			if(isset($daycondition) && $daycondition != '') $itcondtion .= __('a calendar week', 'easyReservations')." <b>".substr($daycondition, 0, -2).'</b> '.__('and', 'easyReservations').' ';
			if(isset($cwcondition) && $cwcondition != '') $itcondtion .= __('in calendar week', 'easyReservations')." <b>".$cwcondition.'</b> '.__('and', 'easyReservations').' ';
			if(isset($monthcondition) && $monthcondition != '') $itcondtion .= __('in', 'easyReservations')." <b>".substr($monthcondition, 0, -2).'</b> '.__('and', 'easyReservations').' ';
			if(isset($qcondition) && $qcondition != '') $itcondtion .= __('in quarter', 'easyReservations')." <b>".$qcondition.'</b> '.__('and', 'easyReservations').' ';
			if(isset($ycondition) && $ycondition != '') $itcondtion .= __('in', 'easyReservations')." <b>".$ycondition.'</b> '.__('and', 'easyReservations').' ';
			$the_condtion = substr($itcondtion, 0, -4).' '.__('else', 'easyReservations').' <b style="font-size:17px">&#8595;</b>';
		}

		return $the_condtion;
	}
	
	function easyreservations_send_price_admin(){
		$nonce = wp_create_nonce( 'easy-price' );
		?><script type="text/javascript" >	
			function easyreservations_send_price_admin(){
				var loading = '<img style="vertical-align:text-bottom" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading.gif">';
				jQuery("#showPrice").html(loading);
				
				var customPrices = '';

				var fromfield = document.editreservation.date;
				if(fromfield) var from = fromfield.value;
				else error = 'arrival date';

				var tofield = document.editreservation.dateend;
				if(tofield) var to = tofield.value;
				else error = 'depature date';

				var roomfield = document.editreservation.room;
				if(roomfield) var room = roomfield.value;
				else error =  'room';

				var offerfield = document.editreservation.offer;
				if(offerfield) var offer = offerfield.value;
				else var offer = 0;

				var childsfield = document.editreservation.offer;
				if(childsfield) var childs = childsfield.value;
				else var childs = 0;

				var personsfield = document.editreservation.persons;
				if(personsfield) var persons = personsfield.value;
				else var persons = 0;

				var emailfield = document.editreservation.email;
				if(emailfield) var email = emailfield.value;
				else var email = 'f.e.r.y@web.de';

				for(var i = 0; i < 16; i++){
					if(document.getElementById('custom_price'+i)){
						var Element = document.getElementById('custom_price'+i);
						customPrices += 'testPrice!:!test:' + Element.value + '!;!';
					}
				}				

				var data = {
					action: 'easyreservations_send_price',
					security:'<?php echo $nonce; ?>',
					from:from,
					to:to,
					childs:childs,
					persons:persons,
					room: room,
					offer: offer,
					email:email,
					customp:customPrices
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					jQuery("#showPrice").html(response);
					return false;
				});
			}
		</script><?php
	}

	function easyreservations_send_cal_admin(){
		$nonce = wp_create_nonce( 'easy-calendar' );
		?><script type="text/javascript" >	
			function easyreservations_send_calendar(){

				var persons = document.CalendarFormular.persons.value;
				var reservated = document.CalendarFormular.reservated.value;
				var childs = document.CalendarFormular.childs.value;
				var room = document.CalendarFormular.room.value;
				var offer = document.CalendarFormular.offer.value;
				var sizefield = document.CalendarFormular.size;
				if(sizefield) var size = sizefield.value;
				else var size = '300,260,0,1';
				var datefield = document.CalendarFormular.date;
				if(datefield) var date = datefield.value;
				else var date = '0';

				var data = {
					action: 'easyreservations_send_calendar',
					security:'<?php echo $nonce; ?>',
					room: room,
					offer: offer,
					size: size,
					date: date,
					persons:persons,
					childs:childs,
					reservated:reservated,
					monthes:'1x1'
				};

				jQuery.post(ajaxurl , data, function(response) {
					//jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>' , data, function(response) {
					jQuery("#showCalender").html(response);
					return false;
				});
			}
		</script><?php
	}

	function easyreservations_send_fav(){
		$nonce = wp_create_nonce( 'easy-favourite' );
		?><script type="text/javascript" >	
			function easyreservations_send_fav(t){

				var the_id = t.id;
				if(the_id){
					var explodeID = the_id.split("-")
					var id = explodeID[1];
					var now = explodeID[0];
					
				
					if(now == 'unfav'){
						var mode = 'add';
						jQuery(t.parentNode.parentNode).addClass('highlighter');
						jQuery(t).removeClass('easy-unfav');
						jQuery(t).addClass('easy-fav');
						t.id = 'fav-' + id;
					} else {
						mode = 'del';
						jQuery(t.parentNode.parentNode).removeClass('highlighter');
						jQuery(t).addClass('easy-unfav');
						jQuery(t).removeClass('easy-fav');
						t.id = 'unfav-' + id;
					}
					var count = document.getElementById('fav-count');
					
					if(count){
						var the_count = count.innerHTML;
						if(mode == 'add') var new_count = 1 + parseInt(the_count);
						else var new_count = (-1) + parseInt(the_count);
						if(new_count < 1) {
							var the_li = count.parentNode.parentNode.parentNode;
							var the_li_parent = the_li.parentNode;
							the_li_parent.removeChild(the_li);
							//var the_il_innerhtml = the_li_parent.innerHTML;
							//the_li_parent.innerHTML = the_il_innerhtml.substr(0,the_il_innerhtml.length - 5);
						} else count.innerHTML = new_count;
					} else if(mode == 'add'){
						document.getElementById('easy-table-navi').innerHTML += '<li>| <a style="cursor:pointer" onclick="easyreservation_send_table(\'favourite\', 1)"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/css/images/star_full.png" style="vertical-align:text-bottom"> <span class="count">(<span id="fav-count">1</span>)</span></a></li>';
					}

					var data = {
						action: 'easyreservations_send_fav',
						security:'<?php echo $nonce; ?>',
						id: id,
						mode: mode
					};

					jQuery.post(ajaxurl , data, function(response) {
						//jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>' , data, function(response) {
						jQuery("#showError").html(response);
						return false;
					});
				}
			}
		</script><?php
	}
	add_action('wp_ajax_easyreservations_send_fav', 'easyreservations_send_fav_callback');

	function easyreservations_send_fav_callback(){
		check_ajax_referer( 'easy-favourite', 'security' );
		if(isset( $_POST['id'])){
			global $current_user;
			$current_user = wp_get_current_user();
			$user = $current_user->ID;

			$favourites = get_user_meta($user, 'reservations-fav', true);
			$save = $favourites;

			$id = $_POST['id'];
			$mode = $_POST['mode'];
			if(is_array($favourites) && $mode == 'add' && !in_array($id, $favourites)){
				$favourites[] = $id;
			} elseif(is_array($favourites) && $mode == 'del' && in_array($id, $favourites)){
				$key = array_search($id, $favourites);
				unset($favourites[$key]);
			}
	
			if(!is_array($favourites)) $favourites[] = $id;

			update_user_meta($user, 'reservations-fav', $favourites, $save);
		}
		die();
	}

	function easy_add_my_quicktags(){ ?>
		<script type="text/javascript">
			QTags.addButton( 'label', 'label', '<label>', '</label>' );
			QTags.addButton( 'p', 'p', '<p>', '</p>' );
			QTags.addButton( 'div', 'div', '<div>', '</div>' );
			QTags.addButton( 'span', 'span', '<span>', '</span>' );
			QTags.addButton( 'h1', 'h1', '<h1>', '</h1>' );
			QTags.addButton( 'h2', 'h2', '<h2>', '</h2>' );
			QTags.addButton( 'small', 'small', '<span class="small">', '</span>' );
			QTags.addButton( 'custom', 'custom', '<label>Name\n<span class="small">Description</span>\n</label><div class="formblock">\n', '</div>' );
			
		</script>
	<?php }

	function easyreservations_get_roles_options($sel=''){
		$roles = get_editable_roles();
		$the_options = '';

		foreach($roles as $key => $role){
			$da = key($role['capabilities']);

			if(is_numeric($da)) $value = $role['capabilities'][0];
			else $value = $da;
			if($sel == $value ) $selected = 'selected="selected"';
			else $selected = '';

			$the_options .= '<option value="'.$value.'" '.$selected.'>'.ucfirst($key).'</option>';
		}
		
		return $the_options;

	}

	function easyreservations_get_allowed_rooms($rooms=0){
		if($rooms == 0) $rooms = easyreservations_get_rooms();
		if(current_user_can('manage_options')) $final_rooms = $rooms;
		else {
			foreach($rooms as $room){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if(current_user_can($get_role)) $final_rooms[] = $room;
			}
		}
		if(isset($final_rooms)) return $final_rooms;
	}
	
	function easyreservations_get_allowed_rooms_mysql($rooms=0){
		if($rooms == 0) $rooms = easyreservations_get_allowed_rooms();
		else $rooms = easyreservations_get_allowed_rooms($rooms);
		
		if(count($rooms) > 0){
			$mysql = '( ';
			foreach($rooms as $room){
				$mysql .= " '$room->ID', ";
			}
			$mysql = substr( $mysql,0,-2).' )';
		} else {
			$mysql = "";
		}
		return $mysql;
	}

	function easyreservations_get_allowed_offers($offers=0){
		if(current_user_can('manage_options')){
			return '';
		} else {
			if($offers == 0) $offers = easyreservations_get_offers();
			if(current_user_can('manage_options')) $final_offers = $offers;
			else {
				foreach($offers as $offer){
					$get_role = get_post_meta($offer->ID, 'easy-resource-permission', true);
					if(current_user_can($get_role)) $final_offers[] = $offer;
				}
			}
		}
		if(isset($final_offers)) return $final_offers;
	}
	
	function easyreservations_get_allowed_offers_mysql($offers=0){
		if(current_user_can('manage_options')){
			return '';
		} else {
			if($offers == 0) $offers = easyreservations_get_allowed_offers();
			else $offers = easyreservations_get_allowed_offers($offers);
						
			if(count($offers) > 0){
				$mysql = '( ';
				foreach($offers as $offer){
					$mysql .= " '$offer->ID', ";
				}
				$mysql .= " ''; ";
				$mysql = substr( $mysql,0,-2).' )';
			} else {
				$mysql = "";
			}
			return $mysql;
		}
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

?>