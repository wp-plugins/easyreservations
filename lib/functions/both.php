<?php
	/**
	* 	@functions for admin and frontend 
	*/

	function easyreservation_resource_init() {
		$labels = array(
			'name' => _x('Resources', 'easyReservations'),
			'singular_name' => _x('Resource', 'easyReservations'),
			'add_new' => _x('Add Resource', 'easyReservations'),
			'add_new_item' => __('Add New Resource', 'easyReservations'),
			'edit_item' => __('Edit Resource', 'easyReservations'),
			'new_item' => __('New Resource', 'easyReservations'),
			'all_items' => __('All Resources', 'easyReservations'),
			'view_item' => __('View Resource', 'easyReservations'),
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => array('slug' => 'resource'), 
			'show_in_menu' => false, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => false, 
			'hierarchical' => true,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'categorys', 'page-attributes' )
		); 
		register_post_type('easy-rooms',$args);
		register_post_type('easy-offers');
	}
	add_action( 'init', 'easyreservation_resource_init' );

	function easy_init_sessions() {
		if (!session_id()) {
			session_start();
		}
	}
	add_action('init', 'easy_init_sessions');

	function easyreservations_admin_bar() {
		global $wp_admin_bar;

		$pending_reservations_cnt = easyreservations_get_pending();
		if($pending_reservations_cnt != 0) $pending = '<span class="ab-label">'.$pending_reservations_cnt.'</span>';
		else $pending = '';

		$wp_admin_bar->add_node( array(
			'id' => 'reservations',
			'title' => '<span class="er-adminbar-icon"></span>'.$pending,
			'href' => admin_url( 'admin.php?page=reservations#pending'),
			'meta' => array('class' => 'er-adminbar-item')
		) );
		$wp_admin_bar->add_node( array(
			'parent' => 'reservations',
			'id' => 'reservations-new',
			'title' => 'New',
			'href' => admin_url( 'admin.php?page=reservations&add'),
		) );
		$wp_admin_bar->add_node( array(
			'parent' => 'reservations',
			'id' => 'reservations-pending',
			'title' => 'Pending',
			'href' => admin_url( 'admin.php?page=reservations#pending'),
		) );
		$wp_admin_bar->add_node( array(
			'parent' => 'reservations',
			'id' => 'reservations-nurrent',
			'title' => 'Current',
			'href' => admin_url( 'admin.php?page=reservations#current'),
		) );
	}

	add_action( 'admin_bar_menu', 'easyreservations_admin_bar', 999 );

	/**
	* Format string into money
	*
	* @since 1.3
	*
	* @param int $amout amount of money to format
	* @param int 1 = currency sign | 0 = without
	* @return string formated money
	*/

	function easyreservations_format_money($amount, $mode=0, $dig = 2){
		if($amount == '') $amount = 0;

		if(RESERVATIONS_CURRENCY == "#8364") $separator = true;
		else $separator = false;

		if($amount < 0 || substr($amount,0,1) == '-'){
			$amount = substr($amount, 1);
			$add = '-';
		} else $add = '';

		$simple=false;
		$money =
		(true===$separator?
			(false===$simple?
				number_format($amount,$dig,',','.'):
				str_replace(',00','',money($amount))
			):
			(false===$simple?
				number_format($amount,$dig,'.','.'):
				str_replace(',00','',money($amount,false))
			)
		);

		$money = $add.$money;

		if($mode == 1){
			if(RESERVATIONS_CURRENCY == "#8364") $money = $money.'&'.RESERVATIONS_CURRENCY.';';
			else  $money = '&'.RESERVATIONS_CURRENCY.';'.$money;
		}

		return $money;
	}

	/**
	*	Repair incorrect input, checks if string can be a price (money) -> returns the price or error
	*
	*	$price = a string to check
	*/

	function easyreservations_check_price($price){
		$newPrice = str_replace(",", ".", $price);
		return ( preg_match("/^[\-]{0,1}[0-9]+[\.]?[0-9]*$/", $newPrice)) ? $newPrice : false;
	}

	function easyreservations_get_rooms($content=false, $check=false, $user = false){
		global $wpdb;
		if($content) $con = ", post_content"; else $con = "";

		$rooms = $wpdb->get_results("SELECT ID, post_title, menu_order $con FROM ".$wpdb->prefix ."posts WHERE post_type='easy-rooms' AND post_status!='auto-draft' ORDER BY menu_order ASC");

		foreach($rooms as $key => $room){
			$rooms[$room->ID] = $room;
			unset($rooms[$key]);
			if($check){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if(!empty($get_role) && ((!$user && !current_user_can($get_role)) || ($user && !user_can($user, $get_role))) ) unset($rooms[$room->ID]);
			}
		}

		return $rooms;
	}

	$the_rooms_array = easyreservations_get_rooms();

	function easyreservations_resource_options($selected='', $check=0, $exclude= ''){
		$rooms = easyreservations_get_rooms(0, $check);
		$rooms_options='';
		foreach( $rooms as $room ){
			if(empty($exclude) || !in_array($room->ID, $exclude)){
				if(!empty($selected) && $selected == $room->ID) $select = ' selected="selected"'; else $select = "";
				$rooms_options .= '<option value="'.$room->ID.'"'.$select.'>'.__($room->post_title).'</option>';
			}
		}
		return $rooms_options;
	}

	function easyreservations_get_rooms_intervals(){
		global $the_rooms_array;
		$rooms = $the_rooms_array;
		$room_intervals = '';
		if(!empty($rooms)){
			foreach( $rooms as $room ){
				$meta = get_post_meta($room->ID, 'easy-resource-interval', TRUE);
				if($meta) $room_intervals[$room->ID] = $meta;
				else $room_intervals[$room->ID] = 86400;
			}
		}
		return $room_intervals;
	}
	
	$the_rooms_intervals_array = easyreservations_get_rooms_intervals();

	function easyreservations_get_the_title($id, $resources=false){
		if($id > 0){
			if(!$resources){
				global $the_rooms_array;
				$resources = $the_rooms_array;
			}
			if(isset($resources[$id]))	return __($resources[$id]->post_title);
			else return false;
		}
	}

	function easyreservations_interval_infos($interval= 0, $mode = 0, $singular = 0){
		if($interval == 3600){
			$string = _n('hour', 'hours', $singular, 'easyReservations');
		} elseif($interval == 86400){
			$string = _n('day', 'days', $singular, 'easyReservations');
		} elseif($interval == 604800){
			$string = _n('week', 'weeks', $singular, 'easyReservations');
		} else $string = _n('time', 'times', $singular, 'easyReservations');

		return $string;
	}

	function easyreservations_get_interval($interval = 0, $resourceID = 0, $mode = 0){
		if($interval == 0) $interval = get_post_meta($resourceID, 'easy-resource-interval', TRUE);
		if($mode == 0) return $interval;
		else{
			if($interval == 3600) return 3600;
			else return 86400;
		}
	}

	function easyreservations_get_nights($interval, $arrival, $departure, $mode = 1){
		$number = ($departure-$arrival) / easyreservations_get_interval($interval, 0,  $mode);
		$significance = 0.01;
		return ( is_numeric($number)) ? (ceil(ceil($number/$significance)*$significance)) : false;
	}
	/**
	*	Returns changelog
	*
	*	$beforeArray = array of reservation before editation
	*	$afterArray = array of reservation after editation
	*/

	function easyreservations_generate_res_changelog($beforeArray, $afterArray){		
		$changelog = '';

		if($beforeArray['arrival'] != $afterArray['arrival']){
			$changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT, strtotime($beforeArray['arrival'])).' => '.date(RESERVATIONS_DATE_FORMAT, strtotime($afterArray['arrival'])).'<br>';
		}

		if($beforeArray['departure'] != $afterArray['departure']){
			$changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT_SHOW, strtotime($beforeArray['departure'])).' => '.date(RESERVATIONS_DATE_FORMAT_SHOW, strtotime($afterArray['departure'])).'<br>';
		}

		if($beforeArray['name'] != $afterArray['name']){
			$changelog .= __('The name was edited' , 'easyReservations' ).': '.$beforeArray['name'].' => '.$afterArray['name'].'<br>';
		}

		if($beforeArray['email'] != $afterArray['email']){
			$changelog .= __('The email was edited' , 'easyReservations' ).': '.$beforeArray['email'].' => '.$afterArray['email'].'<br>';
		}

		if($beforeArray['persons'] != $afterArray['persons']){
			$changelog .= __('The amount of persons was edited' , 'easyReservations' ).': '.$beforeArray['persons'].' => '.$afterArray['persons'].'<br>';
		}

		if($beforeArray['childs'] != $afterArray['childs']){
			$changelog .= __('The amount of childs was edited' , 'easyReservations' ).': '.$beforeArray['childs'].' => '.$afterArray['childs'].'<br>';
		}

		if($beforeArray['country'] != $afterArray['country']){
			$changelog .= __('The country was edited' , 'easyReservations' ).': '.$beforeArray['country'].' => '.$afterArray['country'].'<br>';
		}

		if($beforeArray['room'] != $afterArray['room']){
			$changelog .= __('The room was edited' , 'easyReservations' ).': '.__(easyreservations_get_roomname($beforeArray['room'])).' => '.__(easyreservations_get_roomname($afterArray['room'])).'<br>';
		}

		if($beforeArray['message'] != $afterArray['message']){
			$changelog .= __('The message was edited' , 'easyReservations' ).'<br>';
		}

		if($beforeArray['custom'] != $afterArray['custom']){
			$changelog .= __('Custom fields got edited', 'easyReservations' ).'<br>';
		}

		if(isset($beforeArray['customp']) && $beforeArray['customp'] != $afterArray['customp']){
			$changelog .= __('Prices  got edited' , 'easyReservations' ).'<br>';
		}

		return $changelog;
	}

	/**
	*	Returns an array of all countrys
	*
	*/
	function easyReservations_country_array(){

		return array( 'AF'=>'Afghanistan', 'AL'=>'Albania', 'DZ'=>'Algeria', 'AS'=>'American Samoa', 'AD'=>'Andorra', 'AO'=>'Angola', 'AI'=>'Anguilla', 'AQ'=>'Antarctica', 'AG'=>'Antigua And Barbuda', 'AR'=>'Argentina', 'AM'=>'Armenia', 'AW'=>'Aruba', 'AU'=>'Australia', 'AT'=>'Austria', 'AZ'=>'Azerbaijan', 'BS'=>'Bahamas', 'BH'=>'Bahrain', 'BD'=>'Bangladesh', 'BB'=>'Barbados', 'BY'=>'Belarus', 'BE'=>'Belgium', 'BZ'=>'Belize', 'BJ'=>'Benin', 'BM'=>'Bermuda', 'BT'=>'Bhutan', 'BO'=>'Bolivia', 'BA'=>'Bosnia And Herzegovina', 'BW'=>'Botswana', 'BV'=>'Bouvet Island', 'BR'=>'Brazil', 'IO'=>'British Indian Ocean Territory', 'BN'=>'Brunei', 'BG'=>'Bulgaria', 'BF'=>'Burkina Faso', 'BI'=>'Burundi', 'KH'=>'Cambodia', 'CM'=>'Cameroon', 'CA'=>'Canada', 'CV'=>'Cape Verde', 'KY'=>'Cayman Islands', 'CF'=>'Central African Republic', 'TD'=>'Chad', 'CL'=>'Chile', 'CN'=>'China', 'CX'=>'Christmas Island', 'CC'=>'Cocos (Keeling) Islands', 'CO'=>'Columbia', 'KM'=>'Comoros', 'CG'=>'Congo', 'CK'=>'Cook Islands', 'CR'=>'Costa Rica', 'CI'=>'Cote D\'Ivorie (Ivory Coast)', 'HR'=>'Croatia (Hrvatska)', 'CU'=>'Cuba', 'CY'=>'Cyprus', 'CZ'=>'Czech Republic', 'CD'=>'Democratic Republic Of Congo (Zaire)', 'DK'=>'Denmark', 'DJ'=>'Djibouti', 'DM'=>'Dominica', 'DO'=>'Dominican Republic', 'TP'=>'East Timor', 'EC'=>'Ecuador', 'EG'=>'Egypt', 'SV'=>'El Salvador', 'GQ'=>'Equatorial Guinea', 'ER'=>'Eritrea', 'EE'=>'Estonia', 'ET'=>'Ethiopia', 'FK'=>'Falkland Islands (Malvinas)', 'FO'=>'Faroe Islands', 'FJ'=>'Fiji', 'FI'=>'Finland', 'FR'=>'France', 'FX'=>'France, Metropolitan', 'GF'=>'French Guinea', 'PF'=>'French Polynesia', 'TF'=>'French Southern Territories', 'GA'=>'Gabon', 'GM'=>'Gambia', 'GE'=>'Georgia', 'DE'=>'Germany', 'GH'=>'Ghana', 'GI'=>'Gibraltar', 'GR'=>'Greece', 'GL'=>'Greenland', 'GD'=>'Grenada', 'GP'=>'Guadeloupe', 'GU'=>'Guam', 'GT'=>'Guatemala', 'GN'=>'Guinea', 'GW'=>'Guinea-Bissau', 'GY'=>'Guyana', 'HT'=>'Haiti', 'HM'=>'Heard And McDonald Islands', 'HN'=>'Honduras', 'HK'=>'Hong Kong', 'HU'=>'Hungary', 'IS'=>'Iceland', 'IN'=>'India', 'ID'=>'Indonesia', 'IR'=>'Iran', 'IQ'=>'Iraq', 'IE'=>'Ireland', 'IL'=>'Israel', 'IT'=>'Italy', 'JM'=>'Jamaica', 'JP'=>'Japan', 'JO'=>'Jordan', 'KZ'=>'Kazakhstan', 'KE'=>'Kenya', 'KI'=>'Kiribati', 'KW'=>'Kuwait', 'KG'=>'Kyrgyzstan', 'LA'=>'Laos', 'LV'=>'Latvia', 'LB'=>'Lebanon', 'LS'=>'Lesotho', 'LR'=>'Liberia', 'LY'=>'Libya', 'LI'=>'Liechtenstein', 'LT'=>'Lithuania', 'LU'=>'Luxembourg', 'MO'=>'Macau', 'MK'=>'Macedonia', 'MG'=>'Madagascar', 'MW'=>'Malawi', 'MY'=>'Malaysia', 'MV'=>'Maldives', 'ML'=>'Mali', 'MT'=>'Malta', 'MH'=>'Marshall Islands', 'MQ'=>'Martinique', 'MR'=>'Mauritania', 'MU'=>'Mauritius', 'YT'=>'Mayotte', 'MX'=>'Mexico', 'FM'=>'Micronesia', 'MD'=>'Moldova', 'MC'=>'Monaco', 'MN'=>'Mongolia', 'MS'=>'Montserrat', 'MA'=>'Morocco', 'MZ'=>'Mozambique', 'MM'=>'Myanmar (Burma)', 'NA'=>'Namibia', 'NR'=>'Nauru', 'NP'=>'Nepal', 'NL'=>'Netherlands', 'AN'=>'Netherlands Antilles', 'NC'=>'New Caledonia', 'NZ'=>'New Zealand', 'NI'=>'Nicaragua', 'NE'=>'Niger', 'NG'=>'Nigeria', 'NU'=>'Niue', 'NF'=>'Norfolk Island', 'KP'=>'North Korea', 'MP'=>'Northern Mariana Islands', 'NO'=>'Norway', 'OM'=>'Oman', 'PK'=>'Pakistan', 'PW'=>'Palau', 'PA'=>'Panama', 'PG'=>'Papua New Guinea', 'PY'=>'Paraguay', 'PE'=>'Peru', 'PH'=>'Philippines', 'PN'=>'Pitcairn', 'PL'=>'Poland', 'PT'=>'Portugal', 'PR'=>'Puerto Rico', 'QA'=>'Qatar', 'RE'=>'Reunion', 'RO'=>'Romania', 'RU'=>'Russia', 'RW'=>'Rwanda', 'SH'=>'Saint Helena', 'KN'=>'Saint Kitts And Nevis', 'LC'=>'Saint Lucia', 'PM'=>'Saint Pierre And Miquelon', 'VC'=>'Saint Vincent And The Grenadines', 'SM'=>'San Marino', 'ST'=>'Sao Tome And Principe', 'SA'=>'Saudi Arabia', 'SN'=>'Senegal', 'SC'=>'Seychelles', 'SL'=>'Sierra Leone', 'SG'=>'Singapore', 'SK'=>'Slovak Republic', 'SI'=>'Slovenia', 'SB'=>'Solomon Islands', 'SO'=>'Somalia', 'ZA'=>'South Africa', 'GS'=>'South Georgia And South Sandwich Islands', 'KR'=>'South Korea', 'ES'=>'Spain', 'LK'=>'Sri Lanka', 'SD'=>'Sudan', 'SR'=>'Suriname', 'SJ'=>'Svalbard And Jan Mayen', 'SZ'=>'Swaziland', 'SE'=>'Sweden', 'CH'=>'Switzerland', 'SY'=>'Syria', 'TW'=>'Taiwan', 'TJ'=>'Tajikistan', 'TZ'=>'Tanzania', 'TH'=>'Thailand', 'TG'=>'Togo', 'TK'=>'Tokelau', 'TO'=>'Tonga', 'TT'=>'Trinidad And Tobago', 'TN'=>'Tunisia', 'TR'=>'Turkey', 'TM'=>'Turkmenistan', 'TC'=>'Turks And Caicos Islands', 'TV'=>'Tuvalu', 'UG'=>'Uganda', 'UA'=>'Ukraine', 'AE'=>'United Arab Emirates', 'UK'=>'United Kingdom', 'US'=>'United States', 'UM'=>'United States Minor Outlying Islands', 'UY'=>'Uruguay', 'UZ'=>'Uzbekistan', 'VU'=>'Vanuatu', 'VA'=>'Vatican City (Holy See)', 'VE'=>'Venezuela', 'VN'=>'Vietnam', 'VG'=>'Virgin Islands (British)', 'VI'=>'Virgin Islands (US)', 'WF'=>'Wallis And Futuna Islands', 'EH'=>'Western Sahara', 'WS'=>'Western Samoa', 'YE'=>'Yemen', 'YU'=>'Yugoslavia', 'ZM'=>'Zambia', 'ZW'=>'Zimbabwe' );

	}
	/**
	*	Returns options for a country select
	*
	*	$sel = (optional) selected country
	*/
	function easyreservations_country_options($sel = ''){

		$countryArray = easyReservations_country_array();
		$country_options = '';
		foreach($countryArray as $short => $country){
			if($short == $sel){ $select = ' selected'; }
			else $select = "";
			$country_options .= '<option value="'.$short.'"'.$select.'>'.htmlentities($country,ENT_QUOTES).'</options>';
		}

		return $country_options;
	}

	/**
	*	Returns full name of a country
	*
	*	$country = Index of country
	*/

	function easyreservations_country_name($country){

		if(!empty($country)){
			$countryArray = easyReservations_country_array();
			return $countryArray[$country];
		}
	}

	/**
	* Return numbered options for selects
	*
	*	$start = first number of options
	*	$end = last number of options
	*	$sel = (optional) selected option
	*/

    function easyreservations_num_options($start,$end,$sel=''){

		$return = '';

		for($num = (int) $start; $num <= $end; $num++){
			
			$numdisplay = $num;
			if(!empty($sel) && $num == $sel ) $isel = 'selected="selected"'; else $isel = '';
			if(strlen($start) == strlen($end) && $start < 10 && $end > 9 && $num < 10){
				$numdisplay = '0'.$num;
			}

			$return .= '<option value="'.$num.'" '.$isel.'>'.$numdisplay.'</option>';

		}

		return $return;
	}
	
	function easyreservations_shortcode_parser($content, $usepattern = false, $define = false){
		if($usepattern){
			$pattern = '\\[';						 // Opening bracket
			if($define){
				$pattern.= '(\\[?)'					 // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
					.	'('.$define.')';					 // 2: Shortcode name
			}
			$pattern .= '\\b'                        // Word boundary
					. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
					.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
					.     '(?:'
					.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
					.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
					.     ')*?'
					. ')'
					. '(?:'
					.     '(\\/)'                        // 4: Self closing tag ...
					.     '\\]'                          // ... and closing bracket
					. '|'
					.     '\\]'                          // Closing bracket
					.     '(?:'
					.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
					.             '[^\\[]*+'             // Not an opening bracket
					.             '(?:'
					.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
					.                 '[^\\[]*+'         // Not an opening bracket
					.             ')*+'
					.         ')'
					.         '\\[\\/\\2\\]'             // Closing shortcode tag
					.     ')?'
					. ')'
					. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
			preg_match_all( '/'. $pattern .'/s', $content, $match);
			if($define) $return = $match[3];
			else $return = $match[1];
			$return = array_merge($return, array());
		} else {
			preg_match_all( '/\[.*\]/U', $content, $match);
			$return = $match[0];
		}
		$return = str_replace(array('[',']'), '', $return);

		return $return;
	}

	function easyreservations_check_val(){
		if(has_action( 'er_mod_inst', 'easyreservations_add_module_notice') && strlen((string) easyreservations_add_module_notice(true)) == 280) return true;
		else return false;
	}

	function easyreservations_send_calendar_callback(){
		global $the_rooms_intervals_array;
		check_ajax_referer( 'easy-calendar', 'security' );

		$explodeSize = explode(",", $_POST['size']);
		if(isset($explodeSize[0]) && $explodeSize[0] != '') $width = $explodeSize[0];
		if(isset($explodeSize[1]) && $explodeSize[1] != '') $price = $explodeSize[1];
		else $price = 0;
		if(isset($explodeSize[2]) && $explodeSize[2] != '') $interval = $explodeSize[2];
		else $interval = 1;
		if(isset($explodeSize[3]) && $explodeSize[3] != '') $header = $explodeSize[3];
		else $header = 0;
		
		$pers = 1; $child = 0; $resev = 0;
		if(isset($_POST['persons'])) $pers = $_POST['persons'];
		if(isset($_POST['childs'])) $child = $_POST['childs'];
		if(isset($_POST['reservated'])) $resev = $_POST['reservated'];
		
		$room_count = get_post_meta($_POST['room'], 'roomcount', true);
		$month_names = easyreservations_get_date_name(1);
		$day_names = easyreservations_get_date_name(0,2);
		if($width == 0 || empty($width)) $width=300;
		if($price == 1) $currency = '&'.RESERVATIONS_CURRENCY.';';
		if(isset($_POST['where']) && $_POST['where'] == "widget"){
			$onClick = "easyreservations_send_calendar('widget');";
			$formular = "widget_formular";
			$where = 'widget';
		} else {
			$onClick = "easyreservations_send_calendar('shortcode');";
			$formular = "CalendarFormular";
			$where = 'shortcode';
		}
		$divider = 1;
		$monthes = 1;

		if(isset($_POST['monthes']) && $where == 'shortcode' && preg_match('/^[0-9]+x{1}[0-9]+$/i', $_POST['monthes'])){
			$explode_monthes = explode('x', $_POST['monthes']);
			$monthes = $explode_monthes[0] * $explode_monthes[1];
			$divider = $explode_monthes[0];
		}

		if(function_exists('easyreservations_generate_multical') && $where == 'shortcode' && $monthes != 1) $timenows = easyreservations_generate_multical($_POST['date'], $monthes);
		else $timenows=array(time()+($_POST['date']*86400*30));

		if(!isset($timenows[1])) $month = $month_names[date("n", $timenows[0])-1].' '.date("Y", $timenows[0]);
		else {
			$anf =  $timenows[0];
			$end = $timenows[count($timenows)-1];
			if(date("Y", $anf) == date("Y", $end) ){
				$month=$month_names[date("n", $anf)-1].' - '.$month_names[date("n", $end)-1].' '.date("Y", $anf);
			} else {
				$month=$month_names[date("n", $anf)-1].' '.date("y", $anf).' - '.$month_names[date("n", $end)-1].' '.date("y", $end);
			}
		}

		echo '<table class="calendar-table" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr class="calendarheader">';
					echo '<th class="calendar-header-month-prev" onClick="easyClick = 0;document.'.$formular.'.date.value='.($_POST['date']-$interval).';'.$onClick.'">'.__('prev', 'easyReservations').'</th>';
					echo '<th colspan="5" class="calendar-header-show-month">'.$month.'</th>';
					echo '<th class="calendar-header-month-next" onClick="document.'.$formular.'.date.value='.($_POST['date']+$interval).';'.$onClick.'">'.__('next', 'easyReservations').'</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody style="text-align:center;white-space:nowrap;padding:0px">';
					echo '<tr>';
					echo '<td colspan="7" style="white-space:nowrap;padding:0px;margin:0px">';

		if(count($timenows) > 1){
			$width = $width / $divider;
			$percent = 100 / $divider;
		} else $percent = 100;
		$rand = rand(1,999);

		$month_count=0;
		foreach($timenows as $timenow){
			$month_count++;
			$diff=1;
			$setet=0;
			$yearnow=date("Y", $timenow);
			$monthnow=date("m", $timenow);
			$key = $yearnow.$monthnow;
			$num = cal_days_in_month(CAL_GREGORIAN, $monthnow, $yearnow); // 31

			if($monthnow-1 <= 0){
				$monthnowFix=13;
				$yearnowFix=$yearnow-1;
			} else {
				$monthnowFix=$monthnow;
				$yearnowFix=$yearnow;
			}

			$num2 = cal_days_in_month(CAL_GREGORIAN, $monthnowFix-1, $yearnowFix); // 31
			if($divider % 2 != 0) $thewidth = ($width-0.33).'px';
			else $thewidth = $percent.'%';
			if($month_count % $divider == 0) $float = '';
			else $float = 'float:left';
			echo '<table class="calendar-direct-table '.str_replace(':left', '', $float).'" style="width:'.$thewidth.';margin:0px;'.$float.'">';
				echo '<thead>';
				if($header == 1){
					echo '<tr>';
						echo '<th class="calendar-header-month" colspan="7">'.$month_names[date("n", $timenow)-1].'</th>';
					echo '</tr>';
				}
					echo '<tr>';
						echo '<th class="calendar-header-cell">'.$day_names[0].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[1].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[2].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[3].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[4].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[5].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[6].'</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody style="text-align:center;padding;0px;margin:0px">';

			$rowcount=0;
			while($diff <= $num){

				$dateofeachday=strtotime($diff.'.'.$monthnow.'.'.$yearnow);
				$dayindex=date("N", $dateofeachday);
				if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28 || $setet==35){ echo '<tr style="text-align:center">'; $rowcount++; }
				if($setet==0 && $diff==1 && $dayindex != "1"){ 
					echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2).'</span></td>'; $setet++; 
					if($setet==1 && $diff==1 && $dayindex != "2"){ 
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
						if($setet==2 && $diff==1 && $dayindex != "3"){ 
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
							if($setet==3 && $diff==1 && $dayindex != "4"){ 
							echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
								if($setet==4 && $diff==1 && $dayindex != "5"){ 
								echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
									if($setet==5 && $diff==1 && $dayindex != "6"){
									echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
										if($setet==6 && $diff==1 && $dayindex != "7"){
										echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
										}
									}
								}
							}
						}
					}
				}

				$res = new Reservation(false, array('email' => 'mail@test.com', 'arrival' => $dateofeachday+43200, 'departure' =>  $dateofeachday+easyreservations_get_interval($the_rooms_intervals_array[$_POST['room']], 0, 1)+43200,'resource' => (int) $_POST['room'], 'adults' => $pers, 'childs' => $child,'reservated' => time()-($resev*86400)), false);
				try {
					if($price > 0){
						$res->Calculate();

						if($price == 1 || $price == 2){ $explode = explode('.', $res->price); $res->price = $explode[0]; }
						if($price == 1) $formated_price = $res->price.$currency;
						elseif($price == 2) $formated_price = $res->price;
						elseif($price == 3) $formated_price = easyreservations_format_money($res->price, 1);
						elseif($price == 4) $formated_price = easyreservations_format_money($res->price);

						$final_price = '<span class="calendar-cell-price">'.$formated_price.'</b>';
					} else $final_price = '';

					if(date("d.m.Y", $dateofeachday) == date("d.m.Y", time())) $todayClass=" today";
					else $todayClass="";

					$avail = floor($res->checkAvailability(3));

					if($avail >= $room_count){
						$backgroundtd=" calendar-cell-full";
					} elseif($avail > 0){
						$backgroundtd=" calendar-cell-occupied";
					} else {
						$backgroundtd=" calendar-cell-empty";
					}
					if($dateofeachday > time()) $onclick = 'onclick="easyreservations_click_calendar(this,\''.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'\', \''.$rand.'\', \''.$key.'\')"'; else $onclick ='style="cursor:default"';
					echo '<td class="calendar-cell'.$todayClass.$backgroundtd.'" '.$onclick.' id="easy-cal-'.$rand.'-'.$diff.'-'.$key.'" axis="'.$diff.'">'.$diff.''.$final_price.'</td>'; $setet++; $diff++;
					if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28) echo '</tr>';
					$res->destroy();
				} catch(easyException $e){
					return false;
				}
			}

			if(!empty($final_price)) $final_price =  '<span class="calendar-cell-price">&nbsp;</b>';

			if(($diff-1==$num && $setet/7 != $rowcount) || $setet < 36){
				if($divider == 1) $calc=($rowcount*7)-($setet+1);
				else $calc=42-($setet+1);
				for($countits=0; $countits < $calc+1; $countits++){
					if($countits==0) $fix = " calendar-cell-lastfixer"; else $fix ="";
					if($setet+$countits==35){ echo '</tr><tr>'; $setet++; }
					echo '<td class="calendar-cell calendar-cell-last'.$fix.'"><div>&nbsp;</div><span>'.($countits+1).'</span>'.$final_price.'</td>';
				}
			}

			echo '</tr></tbody></table>';
		}

		echo '</td></tr></tbody></table>';
		exit;
	}

	/**
	 *	Callback for the price calculation (here it fakes a reservation and send it to calculation)
	 *
	*/

	function easyreservations_send_price_callback(){
		check_ajax_referer( 'easy-price', 'security' );

		global $the_rooms_intervals_array;
		$room = $_POST['room'];
		$val_from = strtotime($_POST['from']) + (int) $_POST['fromplus'] ;
		if(!empty($_POST['to'])){
			$val_to = strtotime($_POST['to']) + (int) $_POST['toplus'] ;
		} else {
			$val_to = $val_from + ($_POST['nights'] * $the_rooms_intervals_array[$room]);
		}
		if(!empty($_POST['email'])) $email = $_POST['email'];
		else $email = "test@test.de";
		if(!empty($_POST['persons'])) $persons = $_POST['persons'];
		else $persons = 1;

		if(isset($_POST['customp'])){
			$customp = str_replace("!", "&", $_POST['customp']);
		} else $customp = '';

		if(isset($_POST['childs']) && !empty($_POST['childs'])) $childs = $_POST['childs'];
		else $childs = 0;

		$res = new Reservation(false, array('name' => 'abv', 'email' => $email, 'arrival' => $val_from,'departure' => $val_to,'resource' => (int) $room, 'adults' => (int) $persons, 'childs' => $childs,'reservated' => time(),'status' => '', 'prices' => (float) $customp, 'coupon' => $_POST['coupon']), false);
		try {
			echo easyreservations_format_money($res->Calculate());
		} catch(easyException $e){
			echo 'Error:'. $e->getMessage();
		}

		exit;
	}

	/**
	 *	Callback for the ajax validation (here it checks the values)
	 *
	*/

	function easyreservations_send_validate_callback(){

		check_ajax_referer( 'easy-price', 'security' );
		$mode = $_POST['mode'];
		global $the_rooms_intervals_array;
		
		$val_room = $_POST['room'];
		$val_from = strtotime($_POST['from']) + (int) $_POST['fromplus'] ;
		if(!empty($_POST['to'])){
			$val_to = strtotime($_POST['to']) + (int) $_POST['toplus'] ;
			$field = 'easy-form-to';
		} else {
			$val_to = $val_from + ($_POST['nights'] * $the_rooms_intervals_array[$val_room]);
			$field = 'easy-form-units';
		}
		if(isset($_POST['id'])) $id = $_POST['id'];
		else $id = false;
		$error = "";

		$res = new Reservation($id, array('name' =>  $_POST['thename'], 'email' => $_POST['email'], 'arrival' => $val_from,'departure' => $val_to,'resource' => (int) $_POST['room'], 'adults' => (int) $_POST['persons'], 'childs' => (int) $_POST['childs'],'reservated' => time(),'status' => ''), false);
		try {
			$error = $res->Validate($mode);
		} catch(easyException $e){
			$error[] = '';
			$error[] = $e->getMessage();
		}
		
		if($mode == 'send'){
			$explode_customs = explode(',', substr($_POST['customs'],0,-1));
			foreach($explode_customs as $cstm){
				if(!empty($cstm)){
					$error[] = $cstm;
					$error[] =  sprintf(__( '%1$s is required' , 'easyReservations' ), ucfirst(str_replace('easy-custom-req-', '', $cstm)));
				}
			}
			if($_POST['captcha'] != 'x!'){
				if(empty($_POST['captcha'])){
					$error[] = 'easy-form-captcha';
					$error[] =  __( 'Captcha is required' , 'easyReservations' );
				} elseif(strlen($_POST['captcha']) != 4){
					$error[] = 'easy-form-captcha';
					$error[] =  __( 'Enter correct captcha' , 'easyReservations' );
				}
			}
		}

		if( $error != '' ){
			header( "Content-Type: application/json" );
			echo json_encode($error);
		} else echo true;

		exit;
	}

	function easyreservations_register_scripts(){
		wp_register_script('easyreservations_send_calendar', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_calendar.js' , array( "jquery" ));
		wp_register_script('easyreservations_send_price', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_price.js' , array( "jquery" ));
		wp_register_script('easyreservations_send_validate', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_validate.js' , array( "jquery" ));
		wp_register_script('easy-form-js', WP_PLUGIN_URL . '/easyreservations/js/form.js');

		global $the_rooms_intervals_array;

		wp_localize_script( 'easyreservations_send_calendar', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easyreservations_send_price', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easyreservations_send_validate', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easy-form-js', 'easyDate', array( 'easydateformat' => RESERVATIONS_DATE_FORMAT, 'interval' => json_encode($the_rooms_intervals_array) ) );
		
		// register form styles
		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/form.css'))wp_register_style('easy-form-custom', WP_PLUGIN_URL . '/easyreservations/css/custom/form.css'); // custom form style override
		wp_register_style('easy-form-little', WP_PLUGIN_URL . '/easyreservations/css/forms/form_little.css'); // widget form style
		wp_register_style('easy-form-none', WP_PLUGIN_URL . '/easyreservations/css/forms/form_none.css'); // default (white) form style
		wp_register_style('easy-form-blue', WP_PLUGIN_URL . '/easyreservations/css/forms/form_blue.css'); // blue form style

		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/calendar.css'))wp_register_style('easy-cal-custom', WP_PLUGIN_URL . '/easyreservations/css/custom/calendar.css'); // custom form style override
		wp_register_style('easy-cal-1', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_1.css');
		wp_register_style('easy-cal-2', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_2.css');

		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/datepicker.css')) $form1 = 'custom/datepicker.css'; else $form1 = 'jquery-ui.css';
		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/'.$form1);
	}

	add_action('wp_enqueue_scripts', 'easyreservations_register_scripts');
	add_action('wp_ajax_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
	add_action('wp_ajax_easyreservations_send_price', 'easyreservations_send_price_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_price', 'easyreservations_send_price_callback');
	add_action('wp_ajax_easyreservations_send_validate', 'easyreservations_send_validate_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_validate', 'easyreservations_send_validate_callback');
 
	function easyreservtions_send_price_script(){
		echo '<script>easyreservations_send_price(); </script>';
	}

	function easyreservations_get_roomname($number, $room, $roomnames = ''){
		$number = $number - 1;
		if(empty($number) && $number < 0) return $number;
		if(empty($roomnames)) $roomnames = get_post_meta($room, 'easy-resource-roomnames', TRUE);
		if(isset($roomnames[$number]) && !empty($roomnames[$number])) return $roomnames[$number];
		else return $number+1;
	}

	/**
	 * Get day or month names
	 * 
	 * @since 1.8
	 * 
	 * @param int $interval 0 for days - 1 for monthes
	 * @param int $substr number of characters to display 0=full
	 * @param int $date number of day/or month to retutn just that string
	 * @return array/string with name of date
	 */

	function easyreservations_get_date_name($interval = 0, $substr = 0, $date = false){
		$name = '';
		if($interval == 0){
			$name[] = __( 'Monday' , 'easyReservations' );
			$name[] = __( 'Tuesday' , 'easyReservations' );
			$name[] = __( 'Wednesday' , 'easyReservations' );
			$name[] = __( 'Thursday' , 'easyReservations' );
			$name[] = __( 'Friday' , 'easyReservations' );
			$name[] = __( 'Saturday' , 'easyReservations' );
			$name[] = __( 'Sunday' , 'easyReservations' );
		} else {
			$name[] = __( 'January' , 'easyReservations' );
			$name[] = __( 'February' , 'easyReservations' );
			$name[] = __( 'March' , 'easyReservations' );
			$name[] = __( 'April' , 'easyReservations' );
			$name[] = __( 'May' , 'easyReservations' );
			$name[] = __( 'June' , 'easyReservations' );
			$name[] = __( 'July' , 'easyReservations' );
			$name[] = __( 'August' , 'easyReservations' );
			$name[] = __( 'September' , 'easyReservations' );
			$name[] = __( 'October' , 'easyReservations' );
			$name[] = __( 'November' , 'easyReservations' );
			$name[] = __( 'December' , 'easyReservations' );
		}

		if($substr > 0){
			foreach($name as $key => $day){
				$name[$key] = substr($day, 0, $substr);
			}
		}

		if($date !== false) return $name[$date];
		else return $name;
	}
	
	function easyreservations_add_icons_stylesheet() {
		if(is_user_logged_in()){?><style type="text/css">
			.er-adminbar-item .er-adminbar-icon {background-image: url('<?php echo RESERVATIONS_URL; ?>images/toolbar.png');background-repeat: no-repeat;float: left;height: 16px !important;margin-top: 6px !important;margin-right: 1px !important;position: absolute; width: 16px !important;}
			.hover .er-adminbar-icon {background-image: url('<?php echo RESERVATIONS_URL; ?>images/toolbar_hover.png'); }
	    </style><?php }
	}

	add_action('wp_print_styles', 'easyreservations_add_icons_stylesheet');
	add_action('admin_print_styles', 'easyreservations_add_icons_stylesheet');

?>