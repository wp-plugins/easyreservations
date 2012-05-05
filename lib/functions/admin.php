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
		
	function easyreservations_datepicker_load(){  //  Load Scripts and Styles for datepicker
		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css');
		wp_enqueue_script('jquery-ui-datepicker');
	}
	if($page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
		add_action('admin_enqueue_scripts', 'easyreservations_datepicker_load');
	}

	/**
	*	Get detailed price calculation box
	*
	*	$id = reservations id
	*/
	function easyreservations_detailed_price($id, $resource = 0){
		$date_pat = RESERVATIONS_DATE_FORMAT;
		if($resource > 0){
			global $the_rooms_intervals_array;
			if($the_rooms_intervals_array[$resource] == 3600) $date_pat .= ' H:i';
		}
		$pricearray=easyreservations_price_calculation($id, '', 1);
		$priceforarray=$pricearray['getusage'];
		if(count($priceforarray) > 0){
			$arraycount=count($priceforarray);

			$pricetable='<table class="'.RESERVATIONS_STYLE.'"><thead><tr><th colspan="4" style="border-right:1px">'.__('Price calculation', 'easyReservations').'</th></tr></thead><tr style="background:#fff;"><td><b>'.__('Date', 'easyReservations').'</b></td><td><b>'.__('Description', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Price', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Total', 'easyReservations').'</b></td></tr>';
			$count=0;
			$pricetotal=0;

			sort($priceforarray);
			foreach( $priceforarray as $pricefor){
				$count++;
				if(is_int($count/2)) $class=' class="alternate"'; else $class='';
				$date=$pricefor['date'];
				if(preg_match("/(stay|loyal|custom price|early|pers|child)/i", $pricefor['type'])) $dateposted=' '; else $dateposted=date($date_pat, $date);
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
				if(isset($_POST['show_welcome'])) $show_welcome = 1; else $show_welcome = 0;
				
				$showhide = array( 'show_overview' => $show_overview, 'show_table' => $show_table, 'show_upcoming' => $show_upcoming, 'show_new' => $show_new, 'show_export' => $show_export, 'show_today' => $show_today, 'show_welcome' => $show_welcome  );

				if(isset($_POST['table_color'])) $table_color = 1; else $table_color = 0;
				if(isset($_POST['table_id'])) $table_id = 1; else $table_id = 0;
				if(isset($_POST['table_name'])) $table_name = 1; else $table_name = 0;
				if(isset($_POST['table_from'])) $table_from = 1; else $table_from = 0;
				if(isset($_POST['table_to'])) $table_to = 1; else $table_to = 0;
				if(isset($_POST['table_nights'])) $table_nights = 1; else $table_nights = 0;
				if(isset($_POST['table_email'])) $table_email = 1; else $table_email = 0;
				if(isset($_POST['table_room'])) $table_room = 1; else $table_room = 0;
				if(isset($_POST['table_exactly'])) $table_exactly = 1; else $table_exactly = 0;
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
				
				$table = array( 'table_color' => $table_color, 'table_id' => $table_id, 'table_name' => $table_name, 'table_from' => $table_from, 'table_fav' => $table_fav, 'table_to' => $table_to, 'table_nights' => $table_nights, 'table_email' => $table_email, 'table_room' => $table_room, 'table_exactly' => $table_exactly, 'table_persons' => $table_persons, 'table_childs' => $table_childs, 'table_country' => $table_country, 'table_message' => $table_message, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_paid' => $table_paid, 'table_price' => $table_price, 'table_filter_month' => $table_filter_month, 'table_filter_room' => $table_filter_room, 'table_filter_offer' => $table_filter_offer, 'table_filter_days' => $table_filter_days, 'table_search' => $table_search, 'table_bulk' => $table_bulk, 'table_onmouseover' => $table_onmouseover, 'table_reservated' => $table_reservated, 'table_status' => $table_status );

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
					$current .= '<label><input type="checkbox" name="show_welcome" value="1" '.checked($show['show_welcome'], 1, false).'> '.__( 'Show welcome message' , 'easyReservations').'</label><br>';
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
						$current .= '<label><input type="checkbox" name="table_from" value="1" '.checked($table['table_from'], 1, false).'> '.__( 'Arrival  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_to" value="1" '.checked($table['table_to'], 1, false).'> '.__( 'Departure  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_nights" value="1" '.checked($table['table_nights'], 1, false).'> '.__( 'Units  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_reservated" value="1" '.checked($table['table_reservated'], 1, false).'> '.__( 'Reserved ' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<label><input type="checkbox" name="table_email" value="1" '.checked($table['table_email'], 1, false).'> '.__( 'eMail' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_room" value="1" '.checked($table['table_room'], 1, false).'> '.__( 'Resource' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_exactly" value="1" '.checked($table['table_exactly'], 1, false).'> '.__( 'Resource number' , 'easyReservations').'</label><br>';
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
					$current .= '<label><input type="checkbox" name="table_filter_room" value="1" '.checked($table['table_filter_room'], 1, false).'> '.__( 'Filter by resource' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_offer" value="1" '.checked($table['table_filter_offer'], 1, false).'> '.__( 'Filter by status' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_days" value="1" '.checked($table['table_filter_days'], 1, false).'> '.__( 'Choose days to show' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_search" value="1" '.checked($table['table_search'], 1, false).'> '.__( 'Search' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_bulk" value="1" '.checked($table['table_bulk'], 1, false).'> '.__( 'Bulk & Checkboxes' , 'easyReservations').'</label><br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:15px">';
					$current .= '<b><u>'.__( 'Show Resources' , 'easyReservations').':</u></b><br>';
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
				if(document.getElementById('easy-table-refreshimg')) document.getElementById('easy-table-refreshimg').src = '<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading1.gif'
				
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

				var statusselector = document.getElementById('easy-table-statusselector');
				if(statusselector) var statusselect = statusselector.value;
				else var statusselect = 0;
	
				var monthselector = document.getElementById('easy-table-monthselector');
				if(monthselector) var monthselect = monthselector.value;
				else var monthselect = '';

				var roomselector = document.getElementById('easy-table-roomselector');
				if(roomselector) var roomselect = roomselector.value;
				else var roomselect = 0;

				var perpage = document.getElementById('easy-table-perpage-field');
				if(perpage && perpage != 0) var perge = perpage.value;
				else var perge = 10;
				
				if(typ && typ != '') location.hash = typ;
				else if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ != 'current' && typ != 'pending' && typ != 'deleted' && typ != 'all' && typ != 'old' && typ != 'trash' && typ != 'favourite' ) typ = 'active';
				
				var data = {
					action: 'easyreservations_send_table',
					security: '<?php echo $nonce; ?>',
					typ:typ,
					search:searching,
					monthselector:monthselect,
					searchdate:searchdatefield,
					roomselector:roomselect,
					statusselector:statusselect,
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
		$zeichen = "AND departure > NOW() ";

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

		if($_POST['monthselector'] > 0){
			$monthselector=date("Y-m-d", strtotime($_POST['monthselector']));
			
			$selectors.="AND MONTH('$monthselector') BETWEEN MONTH(arrival) AND MONTH(departure) ";
		}
		if($_POST['roomselector'] > 0){
			$roomselector=$_POST['roomselector'];
			$selectors.="AND room='$roomselector' ";
		}
		
		if(isset($_POST['statusselector'] ) && !is_numeric($_POST['statusselector'])){
			$statusselector=$_POST['statusselector'];
			$selectors.="AND approve='$statusselector' ";
		}

		if($_POST['searchdate'] != ''){
			$search_date = $_POST['searchdate'];
			$search_date_stamp = strtotime($search_date);
			$search_date_mysql = date("Y-m-d", $search_date_stamp);
			$selectors .= "AND ('$search_date_mysql' BETWEEN arrival AND departure OR DATE('$search_date_mysql') = DATE(arrival)) ";
		}
		$rooms_sql  = ''; $permission_selectors = '';
		if(!current_user_can('manage_options')) $rooms_sql = easyreservations_get_allowed_rooms_mysql();

		if(!empty($rooms_sql)) $permission_selectors.= ' AND room in '.$rooms_sql;
		$orders="ASC";
		$ordersby="arrival";

		if(!empty($search)){
			if(preg_match('/^([0-9]+[\,]{1})+[0-9]+$/i', $search)) $searchstr = "AND id in($search)";
			else $searchstr = "AND (name like '%1\$s' OR id like '%1\$s' OR email like '%1\$s' OR arrival like '%1\$s')";
		}
		else $searchstr = "";

		$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items3 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items4 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure < NOW() $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items5 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='del' $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items7 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND NOW() BETWEEN arrival AND departure $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		$items6 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE 1=1 $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		if(isset($favourite_sql)) $countfav = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $favourite_sql $selectors $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
		else $favourite_sql = ' 1 = 1 ';
		if(!isset($typ) || $typ=='active' || $typ=='') { $type="approve='yes'"; $items=$items1; $orders="ASC";  $zeichen = "AND departure > NOW() "; } // If type is actice
		elseif($typ=="current") { $type="approve='yes'"; $items=$items7; $orders="ASC"; $zeichen ="AND NOW() BETWEEN arrival AND departure "; } // If type is current
		elseif($typ=="pending") { $type="approve=''"; $items=$items3; $ordersby="id"; $orders="DESC"; } // If type is pending
		elseif($typ=="deleted") { $type="approve='no'"; $items=$items2; } // If type is rejected
		elseif($typ=="old") { $type="approve='yes'"; $items=$items4; $zeichen="AND departure < DATE(NOW())";  } // If type is old
		elseif($typ=="trash") { $type="approve='del'"; $items=$items5; $zeichen=""; } // If type is trash
		elseif($typ=="all") { $type="1=1"; $items=$items6; $zeichen=""; } // If type is all
		elseif($typ=="favourite") { $type=$favourite_sql; $items=$countfav; $zeichen=""; } // If type is all

		if($order=="ASC") $orders="ASC";
		elseif($order=="DESC") $orders="DESC";

		if($orderby=="date") $ordersby="arrival";
		elseif($orderby=="name") $ordersby="name";
		elseif($orderby=="room") $ordersby="room";
		elseif($orderby=="reservated") $ordersby="reservated";

		if(empty($orderby) && $typ=="pending") { $ordersby="id"; $orders="DESC"; }
		if(empty($orderby) && $typ=="old") { $ordersby="arrival"; $orders="DESC"; }
		if(empty($orderby) && $typ=="all") { $ordersby="arrival"; $orders="DESC"; }

		if(isset($monthselector) || isset($roomselector) || isset($statusselector)){
			$variableitems = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $type $selectors $zeichen $searchstr $permission_selectors", '%' . like_escape($search) . '%'));
			$items=$variableitems;
		}

		if(!isset($roomselector)) $roomselector="";
		if(!isset($statusselector)) $statusselector=0;

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
				<?php if($table_options['table_filter_offer'] == 1){
					?>
					<select name="statusselector" id="easy-table-statusselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all statuses' , 'easyReservations' ));?></option><option value="yes" <?php selected('yes', $statusselector) ?>><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value=" <?php selected('', $statusselector) ?>"><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="no" <?php selected('no', $statusselector) ?> ><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option><option value="del" <?php selected('del', $statusselector) ?>><?php printf ( __( 'Trashed' , 'easyReservations' ));?></option></select>
				<?php } if($table_options['table_filter_month'] == 1){ ?>
					<select name="monthselector"  id="easy-table-monthselector" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'Show all Dates' , 'easyReservations' ));?></option><!-- Filter Months //--> 
					<?php
						$posts = "SELECT DISTINCT DATE_FORMAT(arrival, '%Y-%m') AS yearmonth FROM ".$wpdb->prefix ."reservations GROUP BY yearmonth ORDER BY yearmonth ";
						$results = $wpdb->get_results($posts);
						$datenames = easyreservations_get_date_name(1);

						foreach( $results as $result ){
							$dat=$result->yearmonth;
							$zerst = explode("-",$dat);
							if(isset($_POST['monthselector']) && $_POST['monthselector'] == $dat) $selected = 'selected="selected"'; else $selected ="";
							echo '<option value="'.$dat.'" '.$selected.'>'.$datenames[$zerst[1]-1].' '.__($zerst[0]).'</option>'; 
						} ?>
					</select>
					<?php } if($table_options['table_filter_room'] == 1){ ?>
						<select name="roomselector" id="easy-table-roomselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all Rooms' , 'easyReservations' ));?></option><?php echo reservations_get_room_options($roomselector); ?></select>
					<?php } if($table_options['table_filter_days'] == 1){ ?><input size="1px" type="text" id="easy-table-perpage-field" name="perpage" value="<?php echo $perpage; ?>" maxlength="3" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"></input>
					<img src=" <?php echo RESERVATIONS_IMAGES_DIR; ?>/list.png" style="vertical-align:text-bottom;cursor:pointer" onclick="easyreservation_send_table('all', 1)">
					<?php } ?>
				</td>
				<td style="width:33%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
					<img id="easy-table-refreshimg" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/refresh.png" style="vertical-align:text-bottom" onclick="resetTableValues()">
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
						<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'room' )">
						<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )"><?php } ?><?php printf ( __( 'Resource' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_country'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
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
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )"><?php } ?><?php printf ( __( 'Resource' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_country'] == 1){ ?>
						<th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
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
				$sql = "SELECT id, arrival, departure, name, email, number, childs, room, roomnumber, country, approve, price, custom, customp, reservated FROM ".$wpdb->prefix ."reservations 
						WHERE $type $selectors $zeichen $searchstr $permission_selectors ORDER BY $ordersby $orders $limit";  // Main Table query
				$result = $wpdb->get_results( $wpdb->prepare($sql, '%' . like_escape($search) . '%'));

				if(count($result) > 0 ){
					global $the_rooms_intervals_array;

					foreach($result as $res){
						$room=$res->room;
						$id=$res->id;
						$name = $res->name;
						$person=$res->number;
						$childs=$res->childs;
						$rooms=__(get_the_title($room));

						if($nr%2==0) $class="alternate"; else $class="";
						$nr++;
						$timpstampanf=strtotime($res->arrival);
						$timestampend=strtotime($res->departure);
						$nights = easyreservations_get_nights($the_rooms_intervals_array[$room], $timpstampanf, $timestampend);

						if(in_array($res->email, $regular_guest_array)) $highlightClass='highlighter';
						else $highlightClass='';
						$export_ids .= $id.', ';

						if(time() - $timpstampanf > 0 && time() - $timestampend > 0) $sta = "er_res_old";
						elseif(time() - $timpstampanf > 0 && time() - $timestampend <= 0) $sta = "er_res_now";
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
				<tr class="<?php echo $class.' '.$highlightClass; ?>" height="47px"><!-- Main Table Body //-->
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
						<td nowrap><?php if($table_options['table_from'] == 1) echo date(RESERVATIONS_DATE_FORMAT_SHOW,$timpstampanf); if($table_options['table_from'] == 1 && $table_options['table_to'] == 1) echo ' - ';  if($table_options['table_to'] == 1) echo date(RESERVATIONS_DATE_FORMAT_SHOW,$timestampend);?><?php if($table_options['table_nights'] == 1){ ?> <small>(<?php echo $nights.' '.ucfirst(easyreservations_interval_infos($the_rooms_intervals_array[$room], 0, $nights)); ?>)</small><?php } ?></td>
					<?php } if($table_options['table_reservated'] == 1){ ?>
						<td style="text-align:center"><b><?php echo human_time_diff( strtotime($res->reservated) );?></b></td>
					<?php } if($table_options['table_status'] == 1){
									$status = easyreservations_format_status($res->approve, 1); ?>
						<td><b style="color:<?php if(isset($color)) echo $color; ?>"><?php echo $status; ?></b></td>
					<?php } if($table_options['table_email'] == 1){ ?>
						<td><a href="admin.php?page=reservations&sendmail=<?php echo $id; ?>"><?php echo $res->email;?></a></td>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ ?>
						<td style="text-align:center;"><?php if($table_options['table_name'] == 1) echo $person; if($table_options['table_from'] == 1 && $table_options['table_to'] == 1) echo ' / '; if($table_options['table_childs'] == 1) echo $childs; ?></td>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
						<td nowrap><?php if($table_options['table_room'] == 1) echo '<a href="admin.php?page=reservation-resources&room='.$room.'">'.__($rooms).'</a> '; if($table_options['table_exactly'] == 1 && isset($res->roomnumber)) echo easyreservations_get_roomname($res->roomnumber, $room); ?></td>
					<?php }  if($table_options['table_country'] == 1){  ?>
						<td nowrap><?php if($res->country > 0) echo easyReservations_country_name( $res->country); ?></td>
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
			jQuery(document).ready(function(){
				createTablePickers();
			});
			var field = document.getElementById('easy-export-id-field'); 
			if(field) field.value = '<?php echo $export_ids; ?>';
		</script><?php
		exit;
	}

	add_action('wp_ajax_easyreservations_send_table', 'easyreservations_send_table_callback');

	function easyreservations_add_warn_notice(){
		echo html_entity_decode( '&lt;&#100;iv class=&quot;up&#100;at&#101;d&quot; style=&quot;wi&#100;th:97%&quot;&gt;&lt;p&gt;Th&#105;s &#112;l&#117;gi&#110; &#105;s f&#111;r &lt;a hr&#101;&#102;=&quot;htt&#112;://w&#111;rd&#112;re&#115;s.&#111;rg/&#101;xt&#101;nd/plugins/&#101;asyr&#101;serv&#97;ti&#111;ns/&quot;&gt;&#102;r&#101;e&lt;/a&gt;&#33; Pl&#101;a&#115;e c&#111;n&#115;id&#101;r <&#97; t&#97;rg&#101;t="_bl&#97;nk" hre&#102;="h&#116;tps:&#47;/w&#119;w.&#112;ay&#112;&#97;l.c&#111;m/cg&#105;-b&#105;n/w&#101;b&#115;cr?c&#109;d=_&#115;-xclick&amp;h&#111;st&#101;d_bu&#116;&#116;&#111;n_i&#100;=&#68;3NW9T&#68;VHB&#74;&#57;E">d&#111;na&#116;ing</&#97;>.&lt;/p&gt;&lt;/&#100;iv&gt;' );
	}

	add_action('er_set_main_side_top', 'easyreservations_add_warn_notice');

	function easyreservations_get_price_filter_description($filtertype, $res, $type){
		global $the_rooms_intervals_array;
		if($type == 0) $interval = easyreservations_get_interval($the_rooms_intervals_array[$res], 0, 1);
		else $interval = $the_rooms_intervals_array[$res];
		if($filtertype['cond'] == 'range'){
			$the_condtion = sprintf(__( 'If the %3$s to calculate is beween %1$s and %2$s else' , 'easyReservations' ), '<b>'.date(RESERVATIONS_DATE_FORMAT_SHOW, $filtertype['from']).'</b>', '<b>'.date(RESERVATIONS_DATE_FORMAT_SHOW, $filtertype['to']).'</b>', easyreservations_interval_infos($interval), 0 ,1 ).' <b style="font-size:17px">&#8595;</b>';
		} elseif($filtertype['cond'] == 'date'){
			$the_condtion = sprintf(__( 'If the %3$s to calculate is %1$s else' , 'easyReservations' ), '<b>'.date(str_replace(':i', ':00', RESERVATIONS_DATE_FORMAT_SHOW), $filtertype['date']).'</b>', easyreservations_interval_infos($interval),  0 ,1 ).' <b style="font-size:17px">&#8595;</b>';
		} else {
			if(isset($filtertype['hour']) && !empty($filtertype['hour'])){
				$timecondition = '';
				$times = explode(',', $filtertype['hour']);
				foreach($times as $time){
					$timecondition .= $time.'h, ';
				}
			}

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

			$itcondtion=sprintf(__("If %s to calculate is ", "easyReservations"),easyreservations_interval_infos($interval,  0 ,1) );
			if(isset($timecondition) && $timecondition != '') $itcondtion .= '<b>'.substr($timecondition, 0, -2).'</b> '.__('and', 'easyReservations').' ';
			if(isset($daycondition) && $daycondition != '') $itcondtion .= '<b>'.substr($daycondition, 0, -2).'</b> '.__('and', 'easyReservations').' ';
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
				fromplus = 0;
				if(document.getElementById('from-time-hour')) fromplus += parseFloat(document.getElementById('from-time-hour').value) * 60;
				if(document.getElementById('from-time-min')) fromplus += parseFloat(document.getElementById('from-time-min').value);
				if(fromplus > 0) fromplus = fromplus * 60;
				toplus = 0;
				if(document.getElementById('to-time-hour')) toplus += parseFloat(document.getElementById('to-time-hour').value) * 60;
				if(document.getElementById('to-time-min')) toplus += parseFloat(document.getElementById('to-time-min').value);
				if(toplus > 0) toplus = toplus * 60;

				var tofield = document.editreservation.dateend;
				if(tofield) var to = tofield.value;
				else error = 'departure date';

				var roomfield = document.editreservation.room;
				if(roomfield) var room = roomfield.value;
				else error =  'room';

				var childsfield = document.editreservation.childs;
				if(childsfield) var childs = childsfield.value;
				else var childs = 0;

				var personsfield = document.editreservation.persons;
				if(personsfield) var persons = personsfield.value;
				else var persons = 0;

				var emailfield = document.editreservation.email;
				if(emailfield) var email = emailfield.value;
				else var email = 'f.e.r.y@web.de';

				for(var i = 0; i < 50; i++){
					if(document.getElementById('custom_price'+i)){
						customPrices += 'testPrice!:!test:' + document.getElementById('custom_price'+i).value + '!;!';
					}
				}

				var data = {
					action: 'easyreservations_send_price',
					security:'<?php echo $nonce; ?>',
					from:from,
					fromplus:fromplus,
					to:to,
					toplus:toplus,
					childs:childs,
					persons:persons,
					room: room,
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
			QTags.addButton( 'row', 'row', '<span class="row">', '</span>' );
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

	function easyreservations_add_module_notice($mode=false){
		$warn = html_entity_decode( '&#79;nly u&#115;e &#102;ile&#115; fr&#111;m &#60;a &#104;re&#102;="h&#116;&#116;p&#58;&#47;&#47;w&#119;&#119;.e&#97;sy&#114;eserv&#97;ti&#111;ns.&#111;rg" t&#97;rget="_bl&#97;nk"&#62;easyre&#115;er&#118;ation&#115;.org&#60;&#47;a&#62; or &#60;a &#104;re&#102;="mailto:c&#111;ntact&#64;e&#97;&#115;yreser&#118;&#97;ti&#111;ns.&#111;rg"&#62;&#64;e&#97;sy&#114;eser&#118;ati&#111;ns.&#111;rg&#60;&#47;a&#62; h&#101;re. Y&#111;u &#103;i&#118;e &#116;h&#101;m f&#117;ll&#121; &#97;c&#99;e&#115;s to y&#111;u&#114; se&#114;ve&#114; and dat&#97;ba&#115;e s&#111; &#118;e&#114;ify the &#115;&#111;u&#114;ce &#116;o &#98;e &#60;b&#62;&#115;e&#99;ure&#60;&#47;b&#62;&#33;' );
		if($mode) return $warn;
		else echo $warn;
	}

	add_action('er_mod_inst', 'easyreservations_add_module_notice');

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