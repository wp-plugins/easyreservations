<?php
if(isset($_GET['page'])){
	$page=$_GET['page'];
	if(isset($_GET['tutorial_histoy'])){
		if(!function_exists('wp_get_current_user')) include(ABSPATH . "wp-includes/pluggable.php"); 
		set_user_setting( 'easy_tutorial', '' );
	}

	function easyreservations_load_mainstyle() {  //  Load Scripts and Styles

		wp_register_style('myStyleSheets', WP_PLUGIN_URL . '/easyreservations/css/style.css');
		if(RESERVATIONS_STYLE == 'premium') wp_register_style('chosenStyle', WP_PLUGIN_URL . '/easyreservations/lib/modules/styles/admin/style_premium.css');
		else wp_register_style('chosenStyle', WP_PLUGIN_URL . '/easyreservations/css/style_'.RESERVATIONS_STYLE.'.css');

		wp_enqueue_style( 'myStyleSheets');
		wp_enqueue_style( 'chosenStyle');
	}


	if($page == 'reservations' || $page== 'reservation-settings' || $page== 'reservation-statistics' ||  $page=='reservation-resources'){  //  Only load Styles and Scripts on Reservation Admin Page 
		add_action('admin_init', 'easyreservations_load_mainstyle');
	}

	function easyreservations_statistics_load() {  //  Load Scripts and Styles
		wp_register_script('jquery-flot', RESERVATIONS_URL . 'js/flot/jquery.flot.min.js' );
		wp_register_script('jquery-flot-stack', RESERVATIONS_URL . 'js/flot/jquery.flot.stack.min.js' );
		wp_register_script('jquery-flot-pie', RESERVATIONS_URL . 'js/flot/jquery.flot.pie.min.js' );
		wp_register_script('jquery-flot-crosshair', RESERVATIONS_URL . 'js/flot/jquery.flot.crosshair.min.js' );
		wp_register_script('jquery-flot-resize', RESERVATIONS_URL . 'js/flot/jquery.flot.resize.min.js' );
	}

	if($page == 'reservation-statistics' || $page == 'reservations'){  //  Only load Styles and Scripts on Statistics Page
		add_action('admin_init', 'easyreservations_statistics_load');
	}

	function easyreservations_scripts_resources_load() {  //  Load Scripts and Styles
		if(RESERVATIONS_STYLE == 'premium') wp_enqueue_style('easy-cal-premium', WP_PLUGIN_URL . '/easyreservations/lib/modules/styles/calendar/calendar_premium.css');
		else wp_enqueue_style('easy-cal-2',WP_PLUGIN_URL . '/easyreservations/css/calendar/style_1.css');
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
	function easyreservations_detailed_price($priceforarray, $resource = 0){
		$date_pat = RESERVATIONS_DATE_FORMAT;
		if($resource > 0){
			global $the_rooms_intervals_array;
			if($the_rooms_intervals_array[$resource] == 3600) $date_pat .= ' H:i';
		}
		if(count($priceforarray) > 0){
			$arraycount=count($priceforarray);
			$pricetable='<table class="'.RESERVATIONS_STYLE.'"><thead><tr><th colspan="4" style="border-right:1px">'.__('Price calculation', 'easyReservations').'</th></tr></thead><tr style="background:#fff;"><td><b>'.__('Date', 'easyReservations').'</b></td><td><b>'.__('Description', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Price', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Total', 'easyReservations').'</b></td></tr>';
			$count=0;
			$pricetotal=0;

			foreach( $priceforarray as $pricefor){
				$count++;
				if(is_int($count/2)) $class=' class="alternate"'; else $class='';
				$date=$pricefor['date'];
				$pricetotal+=$pricefor['priceday'];
				if($count == $arraycount) $onlastprice=' style="border-bottom: double 3px #000000;"';  else $onlastprice='';
				if($pricefor['type'] == 'customp_p'){
					$dateposted = '';
					$type = __(sprintf('Custom price %s', $pricefor['amount'].'%'),'easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'customp_n'){
					$dateposted = '';
					$type = __('Custom price','easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'stay'){
					$dateposted = '';
					$type = __('Stay filter','easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'loyal'){
					$dateposted = '';
					$type = __('Loyal filter','easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'pers'){
					$dateposted = '';
					$type = __('Person filter','easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'early'){
					$dateposted = '';
					$type = __('Earlybird filter','easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'persons'){
					$dateposted = '';
					$type = __('Price per Person','easyReservations').' x'.$pricefor['name'];
				} elseif($pricefor['type'] == 'coupon'){
					$dateposted = '';
					$type = __('Coupon','easyReservations').' '.$pricefor['name'];
				} elseif($pricefor['type'] == 'childs'){
					$dateposted = '';
					$type = __('Price per Children','easyReservations').' x'.$pricefor['name'];
				} elseif($pricefor['type'] == 'tax'){
					$dateposted = '';
					$type = __('Tax','easyReservations').' '.$pricefor['name']. ' ('.$pricefor['amount'].'%)';
				} elseif($pricefor['type'] == 'pricefilter'){
					$dateposted=date($date_pat, $date);
					$type = __('Price filter','easyReservations').' '.$pricefor['name'];
				} else {
					$dateposted=date($date_pat, $date);
					$type = __('Groundprice','easyReservations');
				}
				$pricetable.= '<tr'.$class.'><td nowrap>'.$dateposted.'</td><td nowrap>'.$type.'</td><td style="text-align:right;" nowrap>'.easyreservations_format_money($pricefor['priceday'], 1).'</td><td style="text-align:right;" nowrap><b'.$onlastprice.'>'.easyreservations_format_money($pricetotal, 1).'</b></td></tr>';
				unset($priceforarray[$count-1]);
			}

			$pricetable.='</table>';
		} else $pricetable = 'Critical Error #1023462';

		return $pricetable;
	}

	/**
	*	Returns info box
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function easyreservations_reservation_info_box($res, $where, $status){

		if($res->paid > 0){
			$percent = round(100/$res->price*$res->paid, 2);
			if($percent == 100) $color = '#1FB512';
			elseif($percent > 100) $color = '#ab2ad6	';
			else $color = '#F7B500';
		} else {
			$percent = '0';
			$color = '#BC0B0B';
		}

		if(time() >= $res->arrival && time() <= $res->departure){
			$text = __( 'active' , 'easyReservations' );
			$text_color = '#1FB512';
			$time = round(($res->departure-time())/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">+'.$time.'</small>';
		} elseif(time() < $res->arrival){
			$text = __( 'future' , 'easyReservations' );
			$text_color = '#BC0B0B';
			$time = round(($res->arrival - time())/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">+'.$time.'</small>';
		} else {
			$text = __( 'past' , 'easyReservations' );
			$text_color = '#2A78D8';
			$time = round((time()-$res->departure)/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">-'.$time.'</small>';
		}

		$box = '<div class="explainbox">';
			$box .= '<span>';
				$box .= __( 'price' , 'easyReservations' );
				if(isset($res->fixed)) $box .= ' <img style="vertical-align:text-bottom;display:inline-block !Important;" src="'.RESERVATIONS_URL.'/images/lock.png">';
				$box .= '<b>'.$res->formatPrice().'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __( 'paid' , 'easyReservations' );
				$box .= '<b><span style="color:'.$color.'">'.easyreservations_format_money($res->paid, true).'</span> <small>'.$percent.'%</small></b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __( 'time' , 'easyReservations' );
				$box .= '<b style="color:'.$text_color.'">'.$text.'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __( 'status' , 'easyReservations' );
				$box .= '<b>'.$res->getStatus(true).'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= reservations_get_administration_links($res->id, $where, $status);
			$box .= '</span>';
		$box .= '</div>';
	
		return $box;
	}

	/**
	*	Get administration links
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function reservations_get_administration_links($id, $where, $status){

		$countits=0;
		$administration_links = "";
		if($where != "approve" && $status != 'yes') { $administration_links.='<a href="admin.php?page=reservations&approve='.$id.'">'.__( 'Approve' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' ';}
		if($where != "reject" && $status !='no') { $administration_links.='<a href="admin.php?page=reservations&delete='.$id.'">'.__( 'Reject' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.='DASD';}
		if($where != "edit") { $administration_links.='<a href="admin.php?page=reservations&edit='.$id.'">'.__( 'Edit' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' '; }
		$administration_links.='<a href="admin.php?page=reservations&sendmail='.$id.'">'.__( 'Mail' , 'easyReservations' ).'</a>'; $countits++;
		if($countits > 3) $administration_links = str_replace('DASD', '</span><span style="padding-left:0px;">', $administration_links);
		else $administration_links = str_replace('DASD', '', $administration_links);
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
				if(isset($_POST['show_statistics'])) $show_statistics = 1; else $show_statistics = 0;
				if(isset($_POST['show_welcome'])) $show_welcome = 1; else $show_welcome = 0;

				$showhide = array( 'show_overview' => $show_overview, 'show_table' => $show_table, 'show_upcoming' => $show_upcoming, 'show_new' => $show_new, 'show_export' => $show_export, 'show_today' => $show_today, 'show_welcome' => $show_welcome, 'show_statistics' => $show_statistics  );

				if(isset($_POST['table_color'])) $table_color = 1; else $table_color = 0;
				if(isset($_POST['table_id'])) $table_id = 1; else $table_id = 0;
				if(isset($_POST['table_name'])) $table_name = 1; else $table_name = 0;
				if(isset($_POST['table_from'])) $table_from = 1; else $table_from = 0;
				if(isset($_POST['table_email'])) $table_email = 1; else $table_email = 0;
				if(isset($_POST['table_room'])) $table_room = 1; else $table_room = 0;
				if(isset($_POST['table_exactly'])) $table_exactly = 1; else $table_exactly = 0;
				if(isset($_POST['table_reservated'])) $table_reservated = 1; else $table_reservated = 0;
				if(isset($_POST['table_persons'])) $table_persons = 1; else $table_persons = 0;
				if(isset($_POST['table_status'])) $table_status = 1; else $table_status = 0;
				if(isset($_POST['table_country'])) $table_country = 1; else $table_country = 0;
				if(isset($_POST['table_custom'])) $table_custom = 1; else $table_custom = 0;
				if(isset($_POST['table_customp'])) $table_customp = 1; else $table_customp = 0;
				if(isset($_POST['table_price'])) $table_price = 1; else $table_price = 0;
				if(isset($_POST['table_filter_month'])) $table_filter_month = 1; else $table_filter_month = 0;
				if(isset($_POST['table_filter_room'])) $table_filter_room = 1; else $table_filter_room = 0;
				if(isset($_POST['table_filter_offer'])) $table_filter_offer = 1; else $table_filter_offer = 0;
				if(isset($_POST['table_filter_days'])) $table_filter_days = 1; else $table_filter_days = 0;
				if(isset($_POST['table_search'])) $table_search = 1; else $table_search = 0;
				if(isset($_POST['table_bulk'])) $table_bulk = 1; else $table_bulk = 0;
				if(isset($_POST['table_fav'])) $table_fav = 1; else $table_fav = 0;
				if(isset($_POST['table_onmouseover'])) $table_onmouseover = 1; else $table_onmouseover = 0;
				
				$table = array( 'table_color' => $table_color, 'table_id' => $table_id, 'table_name' => $table_name, 'table_from' => $table_from, 'table_fav' => $table_fav, 'table_email' => $table_email, 'table_room' => $table_room, 'table_exactly' => $table_exactly, 'table_persons' => $table_persons, 'table_country' => $table_country, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_price' => $table_price, 'table_filter_month' => $table_filter_month, 'table_filter_room' => $table_filter_room, 'table_filter_offer' => $table_filter_offer, 'table_filter_days' => $table_filter_days, 'table_search' => $table_search, 'table_bulk' => $table_bulk, 'table_onmouseover' => $table_onmouseover, 'table_reservated' => $table_reservated, 'table_status' => $table_status );

				if(isset($_POST['overview_onmouseover'])) $overview_onmouseover = 1; else $overview_onmouseover = 0;
				if(isset($_POST['overview_autoselect'])) $overview_autoselect = 1; else $overview_autoselect = 0;
				if(isset($_POST['overview_show_days'])) $overview_show_days = $_POST['overview_show_days']; else $overview_show_days = 30;
				if(isset($_POST['overview_show_rooms'])) $overview_show_rooms = implode(",", $_POST['overview_show_rooms']); else $overview_show_rooms = '';
				if(isset($_POST['overview_show_avail'])) $overview_show_avail = 1; else $overview_show_avail = 0;
				if(isset($_POST['overview_hourly_stand'])) $overview_hourly_stand = 1; else $overview_hourly_stand = 0;

				$overview = array( 'overview_onmouseover' => $overview_onmouseover, 'overview_autoselect' => $overview_autoselect, 'overview_show_days' => $overview_show_days, 'overview_show_rooms' => $overview_show_rooms, 'overview_show_avail' => $overview_show_avail, 'overview_hourly_stand' => $overview_hourly_stand );

				update_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ));
				if(isset($_POST['daybutton'])) update_option("reservations_show_days",$_POST['daybutton']);
				global $easy_errors;
				$easy_errors[] = array( 'updated', __( 'Reservations dashboard settings saved' , 'easyReservations' ));
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
					if(function_exists('easyreservations_statistics_mini')) $current .= '<label><input type="checkbox" name="show_statistics" value="1" '.checked($show['show_statistics'], 1, false).'> '.__( 'Statistics' , 'easyReservations').'</label><br>';
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
						$current .= '<label><input type="checkbox" name="table_from" value="1" '.checked($table['table_from'], 1, false).'> '.__( 'Date  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_reservated" value="1" '.checked($table['table_reservated'], 1, false).'> '.__( 'Reserved ' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<label><input type="checkbox" name="table_email" value="1" '.checked($table['table_email'], 1, false).'> '.__( 'eMail' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_room" value="1" '.checked($table['table_room'], 1, false).'> '.__( 'Resource' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_exactly" value="1" '.checked($table['table_exactly'], 1, false).'> '.__( 'Resource number' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_persons" value="1" '.checked($table['table_persons'], 1, false).'> '.__( 'Persons' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_status" value="1" '.checked($table['table_status'], 1, false).'> '.__( 'Status' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;">';
						$current .= '<label><input type="checkbox" name="table_country" value="1" '.checked($table['table_country'], 1, false).'> '.__( 'Country' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_custom" value="1" '.checked($table['table_custom'], 1, false).'> '.__( 'Custom fields' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_customp" value="1" '.checked($table['table_customp'], 1, false).'> '.__( 'Custom prices' , 'easyReservations').'</label><br>';
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
					$current .= '<b><u>'.__( 'Overview' , 'easyReservations').'</u></b><br>';
					$current .= '<label><input type="checkbox" name="overview_onmouseover" value="1" '.checked($overview['overview_onmouseover'], 1, false).'> '.__( 'Overview onMouseOver Date & Select animation' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_autoselect" value="1" '.checked($overview['overview_autoselect'], 1, false).'> '.__( 'Overview autoselect with inputs on add/edit' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_show_avail" value="1" '.checked($overview['overview_show_avail'], 1, false).'> '.__( 'Show empty space for each room and day (+20% load)' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_hourly_stand" value="1" '.checked($overview['overview_hourly_stand'], 1, false).'> '.__( 'Hourly mode as standard' , 'easyReservations').'</label><br>';
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

	function easyreservations_dashboard_message(){
		global $easy_errors;
		if(!empty($easy_errors)){
			foreach($easy_errors as $error){
				if(!is_array($error)) $error = array('error', $error);
				elseif(!isset($error[1])){ $error[1] = $error[0]; $error[0] = 'error'; }
				echo '<div class="'.$error[0].'" style="margin-top:-5px !important"><p>'.$error[1].'</p></div>';
			}
		}
	}
}
	/* *
	*	Table ajax request
	*/

	function easyreservations_send_table(){
		$nonce = wp_create_nonce( 'easy-table' );
		?><script type="text/javascript" >	
			function easyreservation_send_table(typ, paging, order, orderby){
				if(document.getElementById('easy-table-refreshimg')) document.getElementById('easy-table-refreshimg').src = '<?php echo RESERVATIONS_URL; ?>images/loading1.gif'
				
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
		global $wpdb,$the_rooms_array; // this is how you get access to the database
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
			if($favourite && !empty($favourite) && is_array($favourite)) $favourite_sql = 'id in('.implode(",", $favourite).')'; 
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
 			if(preg_match('/^[0-9]+$/i', $search)) $searchstr = "AND id in($search)";
			else{
				$room_ids == "";
				foreach($the_rooms_array as $room){
					if(strpos($room->post_title, $search) !== false) $room_ids .= $room->ID.', ';
				}
				if(!empty($room_ids)) $roomsearch = ' OR room in ('.substr($room_ids,0,-2).')';
				else $roomsearch = '';
				$searchstr = "AND (name like '%1\$s' OR id like '%1\$s' OR email like '%1\$s' OR arrival like '%1\$s' OR custom like '%1\$s'$roomsearch)";
			}
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
		if($orderby=="persons") $ordersby="number+(childs*0.5)";
		if($orderby=="status") $ordersby="approve";
		elseif($orderby=="name") $ordersby="name";
		elseif($orderby=="room"){
			$ordersby="room";
			$orders.=", roomnumber ".$orders;
		}
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
			$p->first(1);
			$p->last(1);
			$p->numbers(0);
			$p->field(array(1, __('of', 'easyReservations')));
			$p->parameterName('paging');
			$p->adjacents(1); //No. of page away from the current page

			if(isset($_POST['paging'])) $pagei = $_POST['paging']; else $pagei = 1;

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
				<td style="text-align:center; font-size:12px;" id="idstatusbar" nowrap><!-- Begin of Filter //--> 
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
						<select name="roomselector" id="easy-table-roomselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all Resources' , 'easyReservations' ));?></option><?php echo easyreservations_resource_options($roomselector); ?></select>
					<?php } if($table_options['table_filter_days'] == 1){ ?><input size="1px" type="text" id="easy-table-perpage-field" name="perpage" value="<?php echo $perpage; ?>" maxlength="3" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"></input>
					<img src=" <?php echo RESERVATIONS_URL; ?>images/list.png" style="vertical-align:text-bottom;cursor:pointer" onclick="easyreservation_send_table('all', 1)">
					<?php } ?>
				</td>
				<td style="width:33%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
					<img id="easy-table-refreshimg" src="<?php echo RESERVATIONS_URL; ?>images/refresh.png" style="vertical-align:text-bottom" onclick="resetTableValues()">
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
					<?php if($table_options['table_bulk'] == 1){ $countrows++; ?>
						<th style="text-align:center"><input type="hidden" name="page" value="reservations"><input type="checkbox" name="themainbulk" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')" style="margin-top:2px"></th>
					<?php } if($table_options['table_from'] == 1){ $countrows++; ?>
						<th colspan="2"><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
						<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ $countrows++; ?>
						<th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
						<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ $countrows++; ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )">
						<?php } elseif($order=="DESC" and $orderby=="reservated") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reservated' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )"><?php } ?><?php printf ( __( 'Reserved' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_status'] == 1){ $countrows++; ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="status") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )">
						<?php } elseif($order=="DESC" and $orderby=="status") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'status' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )"><?php } ?><?php printf ( __( 'Status' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_email'] == 1){ $countrows++; ?>
						<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<?php } if($table_options['table_persons'] == 1){ $countrows++; ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="persons") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )">
						<?php } elseif($order=="DESC" and $orderby=="persons") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'persons' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )"><?php } ?><?php printf ( __( 'Persons' , 'easyReservations' ));?></a></th>
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
					<?php }  if($table_options['table_price'] == 1){ $countrows++; ?>
						<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?php if($table_options['table_bulk'] == 1){ ?>
						<th style="text-align:center"><input type="hidden" name="page" value="reservations" style="text-align:center"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
					<?php } if($table_options['table_from'] == 1){ ?>
						<th colspan="2"><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
						<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
						<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )">
						<?php } elseif($order=="DESC" and $orderby=="reservated") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reservated' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )"><?php } ?><?php printf ( __( 'Reserved' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_status'] == 1){ ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="status") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )">
						<?php } elseif($order=="DESC" and $orderby=="status") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'status' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )"><?php } ?><?php printf ( __( 'Status' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_email'] == 1){ ?>
						<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<?php } if($table_options['table_persons'] == 1){ ?>
						<th style="text-align:center"><?php if($order=="ASC" and $orderby=="persons") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )">
						<?php } elseif($order=="DESC" and $orderby=="persons") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'persons' )">
						<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )"><?php } ?><?php printf ( __( 'Persons' , 'easyReservations' ));?></a></th>
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

					foreach($result as $res){
						$res = new Reservation($res->id, (array) $res);
						$res->Calculate();

						if($nr%2==0) $class="alternate"; else $class="";
						$nr++;

						if(in_array($res->email, $regular_guest_array)) $highlightClass='highlighter';
						else $highlightClass='';
						$export_ids .= $res->id.', ';

						if(time() - $res->arrival > 0 && time() - $res->departure > 0) $sta = "er_res_old";
						elseif(time() - $res->arrival > 0 && time() - $res->departure <= 0) $sta = "er_res_now";
						else $sta = "er_res_future";
						if(isset($favourite)){
							if(in_array($res->id, $favourite)){
								$favclass = ' easy-fav';
								$favid = 'fav-'.$res->id;
								if($typ != 'favourite')$highlightClass = 'highlighter';
							} else {
								$favclass = ' easy-unfav';
								$favid = 'unfav-'.$res->id;
							}
						} ?>
				<tr class="<?php echo $class.' '.$highlightClass; ?>" height="47px"><!-- Main Table Body //-->
					<?php if($table_options['table_bulk'] == 1 || isset($favourite)){ ?>
						<td width="2%" style="text-align:center;vertical-align:middle;">
							<?php if($table_options['table_bulk'] == 1){ ?><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $res->id;?>"><?php } ?>
							<?php if(isset($favourite)){ ?><div class="easy-favourite <?php echo $favclass; ?>" id="<?php echo $favid; ?>" onclick="easyreservations_send_fav(this)"> </div><?php } ?>
						</td>
					<?php } if($table_options['table_from'] == 1){
						if(date('Y', $res->arrival) != date('Y') || date('Y', $res->departure) != date('Y')) $year = true; else $year = false; ?>
						<td class="<?php echo $sta; ?>" style="width:24px;text-align: right;">
							<div style="margin-bottom:5px;">
								<span style="font-weight: bold;font-size: 11px;text-align:right;"><?php $round = round(($res->arrival-time())/86400, 0); if($round > 0) $round = '+'.$round; if($round == 0) echo ' 0'; else echo $round;?></span>
							</div>
							<div>
								<span style="font-weight: bold;font-size: 11px;text-align:right;"><?php $round = round(($res->departure-time())/86400, 0); if($round > 0) $round = '+'.$round; if($round == 0) echo ' 0'; else echo $round; ?></span>
							</div>
						</td>
						<td class="<?php echo $sta; ?>" style="padding-left:0px;padding-right:0px;width:70px;white-space: nowrap;">
							<div style="margin-bottom:5px;">
								<span style="color:#444;font-weight: bold;font-size:15px;"><?php echo date('d', $res->arrival); ?></span>
								<span style="color:#777;font-weight: bold;font-size: 13px;"><?php echo date('M', $res->arrival); ?></span>
								<?php if($year){ ?><span style="color:#777;font-weight: bold;font-size: 14px;"><?php echo date('Y', $res->arrival); ?></span><?php } ?>
								<?php if(RESERVATIONS_USE_TIME == 1){ ?><span style="color:#999;font-weight: bold;font-size: 11px;"><?php echo date('H:i', $res->arrival); ?></span><?php } ?>
							</div>
							<div>
								<span style="color:#444;font-weight: bold;font-size: 15px;"><?php echo date('d', $res->departure); ?></span>
								<span style="color:#777;font-weight: bold;font-size: 13px;"><?php echo date('M', $res->departure); ?></span>
								<?php if($year){ ?><span style="color:#777;font-weight: bold;font-size: 14px;"><?php echo date('Y', $res->departure); ?></span><?php } ?>
								<?php if(RESERVATIONS_USE_TIME == 1){ ?><span style="color:#999;font-weight: bold;font-size: 11px;"><?php echo date('H:i', $res->departure); ?></span><?php } ?>
							</div>
						</td>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<td  valign="top" class="row-title test" valign="top" nowrap>
								<b style="font-weight: bold">
								<?php if($table_options['table_name'] == 1){ ?>
									<a href="admin.php?page=reservations&view=<?php echo $res->id;?>"><?php echo $res->name;?></a>
								<?php } if($table_options['table_id'] == 1) echo ' (#'.$res->id.')'; ?>
								</b>
								<?php do_action('er_table_name_custom', $res); ?>
								<div class="test2" style="margin:5px 0 0px 0;">
									<a href="admin.php?page=reservations&edit=<?php echo $res->id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a> 
									<?php if(isset($typ) && ($typ=="deleted" || $typ=="pending")) { ?>| <a style="color:#28a70e;" href="admin.php?page=reservations&approve=<?php echo $res->id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a>
									<?php } if(!isset($typ) || (isset($typ) && ($typ=="active" || $typ=="pending"))) { ?> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&delete=<?php echo $res->id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a>
									<?php } if(isset($typ) && $typ=="trash") { ?>| <a href="admin.php?page=reservations&bulkArr[]=<?php echo $res->id;?>&bulk=2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&easy-main-bulk=&bulkArr[]=<?php echo $res->id;?>&bulk=3&easy-main-bulk=<?php echo wp_create_nonce('easy-main-bulk'); ?>"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a><?php } ?> | <a href="admin.php?page=reservations&sendmail=<?php echo $res->id;?>"><?php echo __( 'Mail' , 'easyReservations' );?></a>
									<?php if(function_exists('easyreservations_generate_invoice_form')) echo easyreservations_generate_invoice_form($res, '', false); ?>
								</div>
						</td>
					<?php } if($table_options['table_reservated'] == 1){ ?>
						<td style="text-align:center"><?php echo human_time_diff( $res->reservated ).' '.__('ago', 'easyReservations');?></td>
					<?php } if($table_options['table_status'] == 1){ ?>
						<td style="text-align:center;vertical-align: middle"><span class="table-status-<?php echo $res->status; ?>"><?php echo $res->getStatus(false); ?></span></td>
					<?php } if($table_options['table_email'] == 1){ ?>
						<td><a href="admin.php?page=reservations&sendmail=<?php echo $res->id; ?>"><?php echo $res->email;?></a></td>
					<?php } if($table_options['table_persons'] == 1){ ?>
						<td style="text-align:center;color:#777;font-weight: bold !important;font-size:14px"><?php echo $res->adults; ?> +<?php echo $res->childs; ?></td>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
						<td nowrap><?php if($table_options['table_room'] == 1) echo '<a href="admin.php?page=reservation-resources&room='.$res->resource.'">'.__($the_rooms_array[$res->resource]->post_title).'</a> '; if($table_options['table_exactly'] == 1 && isset($res->resourcenumber)) echo '<b>'.easyreservations_get_roomname($res->resourcenumber, $res->resource).'</b>'; ?></td>
					<?php }  if($table_options['table_country'] == 1){  ?>
						<td nowrap><?php echo easyreservations_country_name( $res->country); ?></td>
					<?php }  if($table_options['table_custom'] == 1){ ?>
						<td><?php $customs = $res->getCustoms($res->custom, 'cstm');
								if(!empty($customs)){
									foreach($customs as $custom){
										echo '<b>'.$custom['title'].':</b> '.$custom['value'].'<br>';
									}
								}?></td>
					<?php }  if($table_options['table_customp'] == 1){ ?>
						<td><?php $customs = $res->getCustoms($res->prices, 'cstm');
								if(!empty($customs)){
									foreach($customs as $custom){
										echo '<b>'.$custom['title'].':</b> '.$custom['value'].' - '.easyreservations_format_money($custom['amount'], 1).'<br>';
									}
								}?></td>
					<?php }  if($table_options['table_price'] == 1){ ?>
						<td nowrap style="text-align:right">
							<div style="margin-bottom:6px;">
								<span style="font-weight: bold;font-size:12px;color:#555;;"><?php echo $res->formatPrice(true, 1); ?></span>
							</div>
							<div>
								<span style="font-weight: bold !important;font-size:12px;"><?php if($res->price == 0) echo 0; else echo round(100/$res->price*$res->paid, 0); ?>% Paid</span>
							</div>
						</td>
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
					<?php if($items > 0) { ?><div class="tablenav" style="text-align:center; margin:0 115px 4px 0;"><div class='tablenav-pages'><?php echo $p->show(); ?></div></div><?php } ?>
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

	add_action('er_add_settings_top', 'easyreserations_prem_box_set', 10, 0);

	function easyreserations_prem_box_set(){ ?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-bottom: 7px">
				<thead>
					<tr>
						<th colspan="2"><?php printf ( __( 'easyReservations Premium' , 'easyReservations' ));?> </th>
					</tr>
				</thead>
				<tbody style="border:0px">
					<tr valign="top">
						<td style="font-weight:bold;background-image:url('<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/lifetime_slide.png');height: 200px;width:230px;border-right: 1px solid #CCCCCC"></td>
						<td class="s" style="font-family: Helvetica Neue-Light,Helvetica Neue Light,Helvetica Neue,sans-serif; font-size: 20px; font-weight: normal;   line-height: 1.6em;vertical-align:top;padding:20px;">
							Improve your reservations system and get support by upgrading to <b><a href="http://easyreservations.org/premium/">easyReservations Premium</a></b>!<br>
							<span class="premiumcontent" style="font-size:18px">
								With over <b>twenty</b> additional functions like <a href="http://easyreservations.org/module/paypal/">PayPal integration</a>, <a href="http://easyreservations.org/module/invoice/">automatically Invoice generation</a>, <a href="http://easyreservations.org/module/htmlmails/">HTML eMails with templates</a>, <a href="http://easyreservations.org/module/search/">Search for available Resources</a>, <a href="http://easyreservations.org/modules/hourlycal/">hourly Calendars</a>, <a href="http://easyreservations.org/module/import/">Export (xls/xml/csv) &amp; Import Reservations</a>, <a href="http://easyreservations.org/module/lang/">Multilingual Form &amp; Email Content</a>,
								<a href="http://easyreservations.org/module/useredit/">Reservation editing for guests &amp; a communication system</a>, <a href="http://easyreservations.org/module/coupons/">a Coupon Code system</a>, <a href="http://easyreservations.org/module/multical/">Multiple months by grid for Calendar</a>, <a href="http://easyreservations.org/module/statistics/">Statistics</a> and <a href="http://easyreservations.org/module/styles/">more Form, Admin, Calendar and Datepicker Styles</a>.
							</span>
							<br>
							<a href="http://easyreservations.org/premium/" style="text-decoration:underline">Check out all Features now!</a>
						</td>
					</tr>
				</tbody>
			</table>
		<style>.premiumcontent a { background:#EAEAEA;} </style>
		<?php
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
			$the_condtion = sprintf(__( 'If the %2$s to calculate is %1$s else' , 'easyReservations' ), '<b>'.date(str_replace(':i', ':00', RESERVATIONS_DATE_FORMAT_SHOW), $filtertype['date']).'</b>', easyreservations_interval_infos($interval),  0 ,1 ).' <b style="font-size:17px">&#8595;</b>';
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
				var loading = '<img style="vertical-align:text-bottom" src="<?php echo RESERVATIONS_URL; ?>images/loading.gif">';
				jQuery("#showPrice").html(loading);
				
				var customPrices = ''; var coupons = '';

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

				if(document.getElementsByName('allcoupon')){
					var couponfield = document.getElementsByName('allcoupon[]');
					for(var i=0; i < couponfield.length;i++){
						coupons += couponfield[i].value + ',';
					}
				}

				var data = {
					action: 'easyreservations_send_price',
					security:'<?php echo $nonce; ?>',
					from:from,
					fromplus:fromplus,
					to:to,
					coupon:coupons,
					toplus:toplus,
					childs:childs,
					persons:persons,
					room: room,
					email:email,
					customp:customPrices
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					response = JSON.parse(response);
					jQuery("#showPrice").html(response[0]);
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
	
	function easyreservations_get_allowed_rooms($rooms=0, $user = false){
		if($rooms == 0) $rooms = easyreservations_get_rooms();
		if(current_user_can('manage_options')) $final_rooms = $rooms;
		else {
			foreach($rooms as $room){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if((!$user && current_user_can($get_role)) || user_can($user, $get_role)) $final_rooms[] = $room;
			}
		}
		if(isset($final_rooms)) return $final_rooms;
	}
	
	function easyreservations_get_allowed_rooms_mysql($rooms = 0, $user = false){
		if($rooms == 0) $rooms = easyreservations_get_allowed_rooms(0, $user);
		else $rooms = easyreservations_get_allowed_rooms($rooms, $user);
		$mysql = "";

		if(count($rooms) > 0){
			$mysql .= '( ';
			foreach($rooms as $room){
				$mysql .= " '$room->ID', ";
			}
			$mysql = substr( $mysql,0,-2).' )';
		}

		return $mysql;
	}


	function easyreservations_get_color($round){
		if($round >= 200) return '#ab2ad6';
		if($round >= 1) return '#52d646';
		elseif($round < 0) return '#BC0B0B';
		else return '#ffcb49';
	}
?>