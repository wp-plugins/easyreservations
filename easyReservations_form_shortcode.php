<?php
function reservations_form_shortcode($atts){

	global $post;
	$finalform = "";
	$error = '';
	$infobox = false;
	if(isset($atts[0])) $theForm = stripslashes(get_option('reservations_form_'.$atts[0]));
	else $theForm = stripslashes (get_option("reservations_form"));
	if(empty($theForm)) $theForm = stripslashes(get_option("reservations_form"));

	$atts = shortcode_atts(array(
		'room' => 0,
		'resource' => 0,
		'price' => 1,
		'multiple' => 0,
		'resourcename' => __( 'Room' , 'easyReservations' ),
		'credit' => __( 'Your reservation is complete' , 'easyReservations' ),
		'submit' => __( 'Your reservation was sent' , 'easyReservations' ),
		'validate' =>__( 'Reservation was validated succesfully' , 'easyReservations' ),
		'subcredit' => '',
		'subsubmit' => '',
		'subvalidate' => '',
		'style' => 'none',
		'paypal' => '',
		'bg' => '#fff',
		'pers' => 0,
		'redirect' => ''
	), $atts);

	if($atts['room'] > 0) $atts['resource'] = $atts['room'];

	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle' , false, array(), false, 'all');
	wp_enqueue_style('easy-frontend' , false, array(), false, 'all');
	wp_enqueue_script('easyreservations_send_form');

	if(wp_style_is( 'easy-form-'.$atts['style'], 'registered')) wp_enqueue_style('easy-form-'.$atts['style'] , false, array(), false, 'all');
	else wp_enqueue_style('easy-form-none' , false, array(), false, 'all');	

	if(strpos($theForm, '[error') !== false){
		$validate_action = 'easyreservations_send_validate();';
		wp_enqueue_script( 'easyreservations_send_validate' );
	} else $validate_action = '';

	if(strpos($theForm, '[show_price') !== false){
		$price_action = "easyreservations_send_price();";
		wp_enqueue_script( 'easyreservations_send_price' );
		add_action('wp_print_footer_scripts', 'easyreservtions_send_price_script'); //get price directily after loading
	} else $price_action = '';

	if(strpos(get_the_content($post->ID), '[easy_calendar') !== false) $isCalendar = true;
	else $isCalendar = false;

	if(isset($_POST['easynonce'])){ // Check and Set the Form Inputs

		if (!wp_verify_nonce($_POST['easynonce'], 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		global $the_rooms_intervals_array, $current_user;

		if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
		else $captcha ="";
		if(isset($_POST['thename'])) $name_form=$_POST['thename'];
		else $name_form = "";
		if(isset($_POST['from'])) $arrival = strtotime($_POST['from']);
		else $arrival = time();
		if(isset($_POST['persons'])) $persons=$_POST['persons'];
		else $persons = 1;
		if(isset($_POST['email'])) $email=$_POST['email'];
		else $email = "";
		if(isset($_POST['childs'])) $childs=$_POST['childs'];
		else $childs = 0;
		if(isset($_POST['to'])) $departure = strtotime($_POST['to']);
		else $departure = $arrival + $the_rooms_intervals_array[$_POST['easyroom']];
		if(isset($_POST['nights'])) $departure = $arrival+((int) $_POST['nights'] * $the_rooms_intervals_array[$_POST['easyroom']]);
		if(isset($_POST['country'])) $country=$_POST['country'];
		else $country = "";
		if(isset($_POST['easyroom'])) $room = $_POST['easyroom'];
		else $room = false;

		$arrivalplus = 0;
		if(isset($_POST['date-from-hour'])) $arrivalplus += (int) $_POST['date-from-hour'] * 60;
		else $arrivalplus += 12*60;
		if(isset($_POST['date-from-min'])) $arrivalplus += (int) $_POST['date-from-min'];
		if($arrivalplus > 0) $arrivalplus = $arrivalplus * 60;
		$departureplus = 0;
		if(isset($_POST['date-to-hour'])) $departureplus += (int) $_POST['date-to-hour'] * 60;
		else $departureplus += 12*60;
		if(isset($_POST['date-to-min'])) $departureplus += (int) $_POST['date-to-min'];
		if($departureplus > 0) $departureplus = $departureplus*60;
		$arrival += $arrivalplus;
		$departure += $departureplus;
		$custom_form='';
		$custom_price='';	
		$tags = easyreservations_shortcode_parser($theForm, true);

		foreach($tags as $fields){
			$field=shortcode_parse_atts( $fields);
			if($field[0]=="custom"){
				if(isset($_POST['easy-custom-'.$field[2]]) && !empty($_POST['easy-custom-'.$field[2]])){
					$custom_form[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => $_POST['easy-custom-'.$field[2]]);
				} else {
					if(isset($field[count($field)-1]) && $field[count($field)-1] == "*") $error.= '<li>'.sprintf(__( '%s is required', 'easyReservations'), ucfirst($field[2])).'</li>'; 
				}
			}
			if($field[0]=="price"){
				if(isset($_POST[$field[2]])){
					$explodeprice = explode(":",$_POST[$field[2]]);
					if(isset($explodeprice[2]) && $explodeprice[2] == 1) $theprice = $explodeprice[1] * ($persons+$childs);
					elseif(isset($explodeprice[2]) && $explodeprice[2] == 2) $theprice = $explodeprice[1] * easyreservations_get_nights($the_rooms_intervals_array[$room], $arrival,$departure);
					elseif(isset($explodeprice[2]) && $explodeprice[2] == 3) $theprice = $explodeprice[1] * easyreservations_get_nights($the_rooms_intervals_array[$room], $arrival,$departure) * ($persons+$childs);
					else $theprice = $explodeprice[1];
					$custom_price[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => $explodeprice[0], 'amount' => $theprice );
				}
			}
		}

		$current_user = wp_get_current_user();

		$res = new Reservation(false, array('name' => $name_form, 'email' => $email, 'arrival' => $arrival,'departure' => $departure,'resource' => (int) $room,'resourcenumber' => 0,'country' => $country, 'adults' => $persons, 'custom' => maybe_unserialize($custom_form),'prices' => maybe_unserialize($custom_price),'childs' => $childs,'reservated' => date('Y-m-d H:i:s', time()),'status' => '','user' => $current_user->ID), false);
		try {
			$res->fake = false;
			$theID = $res->addReservation(array('reservations_email_to_admin', 'reservations_email_to_user'), array(false, $res->email));
			if($theID){
				foreach($theID as $key => $terror){
					if($key%2==0) $error.=  '<li><labe for="'.$terror.'">';
					else $error .= $terror.'</label></li>';
				}
			}
		} catch(easyException $e){
			$error.=  '<li><label>'.$e->getMessage().'</label></li>';
		}

		if(empty($error) && isset($arrival)){ //When Check gives no error Insert into Database and send mail
			if(!empty($atts['submit'])) $finalform.= '<div class="easy_form_success"><b class="easy_submit">'.$atts['submit'].'!</b>';
			if(!empty($atts['subsubmit'])) $finalform.= '<span class="easy_subsubmit">'.$atts['subsubmit'].'</span>';
			$res->Calculate(true);
			if($atts['price'] == 1) $finalform.= '<span class="easy_show_price_submit">'.__('Price','easyReservations').': <b>'.easyreservations_format_money($res->price, 1).'</b></span>';
			if(!empty($atts['paypal'])) $finalform .= '<span class="easy_show_paypal_text_submit">'.$atts['paypal'].'</span>';
			if(function_exists('easyreservations_generate_paypal_button')){
				$finalform .= easyreservation_deposit_function($res->price);
				$finalform .= easyreservations_generate_paypal_button($res, $theID);
			}
			if(function_exists('easyreservations_generate_creditcard_form')){
				$finalform .= easyreservations_generate_creditcard_form($res);
			}
			$finalform.='</div>';
			$final = $finalform;
		}
	}

	$theForm = stripslashes($theForm);
	$theForm = apply_filters( 'easy-form-content', $theForm);
	$roomfield = 0;
	$customPrices  = 0;

	$tags = easyreservations_shortcode_parser($theForm, true);
	foreach($tags as $fields){
		$field=shortcode_parse_atts( $fields);
		if(isset($field['value'])) $value = $field['value'];
		else $value='';
		if(isset($field['style'])) $style = $field['style'];
		else $style='';
		if(isset($field['title'])) $title = $field['title'];
		else $title='';
		if(isset($field['disabled'])) $disabled =  'readonly="readonly"';
		else $disabled='';
		if(isset($field['maxlength'])) $maxlength = $field['maxlength'];
		else $maxlength='';

		if($field[0]=="date-from"){
			if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, time()+86400);
			elseif(preg_match('/\+{1}[1-9]+/i', $value)){
				$cutplus = str_replace('+', '',$value);
				$value = date(RESERVATIONS_DATE_FORMAT, time()+($cutplus*86400));
			}
			$theForm=str_replace('['.$fields.']', '<input id="easy-form-from" type="text" name="from" value="'.$value.'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="date-to"){
			if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, time()+172800);
			elseif(preg_match('/\+{1}[1-9]+/i', $value)){
				$cutplus = str_replace('+', '',$value);
				$value = date(RESERVATIONS_DATE_FORMAT, time()+((int) $cutplus*86400));
			}
			$theForm=str_replace('['.$fields.']', '<input id="easy-form-to" type="text" name="to" value="'.$value.'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="date-from-hour" || $field[0]=="date-to-hour"){
			$theForm=str_replace('['.$fields.']', '<select id="'.$field[0].'" name="'.$field[0].'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options("00", 23, $value).'</select>', $theForm);
		} elseif($field[0]=="date-from-min" || $field[0]=="date-to-min"){
			$theForm=str_replace('['.$fields.']', '<select id="'.$field[0].'" name="'.$field[0].'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options("00", 59, $value).'</select>', $theForm);
		} elseif($field[0]=="units" || $field[0]=="nights" || $field[0]=="times"){
			if(isset($field[2])) $end = $field[2]; else $end = 6;
			if(isset($field[3])){ $start = $field[2]; $end = $field[3]; } else $start = 1;
			$theForm=str_replace('['.$fields.']', '<select id="easy-form-units" name="nights" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options($start, $end, $value).'</select>', $theForm);
		} elseif($field[0]=="persons" || $field[0]=="adults"){
			$start = 1;
			if(isset($field[2])) $end = $field[2]; else $end = 6;
			if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
			$theForm=preg_replace('/\['.$fields.'\]/', '<select id="easy-form-persons" name="persons" '.$disabled.' style="'.$style.'" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options($start,$end,$value).'</select>', $theForm);
		} elseif($field[0]=="childs"){
			$start = 0;
			if(isset($field[2])) $end = $field[2]; else $end = 6;
			if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
			$theForm=preg_replace('/\['.$fields.'\]/', '<select name="childs" '.$disabled.' style="'.$style.'" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options($start,$end,$value).'</select>', $theForm);
		} elseif($field[0]=="thename"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-form-thename" name="thename" '.$disabled.' value="'.$value.'" style="'.$style.'" title="'.$title.'" onchange="'.$validate_action.'">', $theForm);
		} elseif($field[0]=="error"){
			if(strlen($error) > 3){
				$form_error=$error;
				$class='';
			} else {
				$form_error = '';
				$class=' hide-it';
			}
			if(isset($field['error_title'])) $error_title = $field['error_title'];
			else $error_title='Errors found in the form';
			if(isset($field['error_message'])) $error_message = $field['error_message'];
			else $error_message='There is a problem with the form, please check and correct the following:';

			$theForm=preg_replace('/\['.$fields.'\]/', '<div class="easy-show-error-div'.$class.'" id="easy-show-error-div" style="'.$style.'"><h2>'.$error_title.'</h2>'.$error_message.'<ul id="easy-show-error">'.$form_error.'</ul></div>', $theForm);
		} elseif($field[0]=="infobox"){
			$resource_block = '<div id="resource_infobox" style=";"></div>';
			$theForm=preg_replace('/\['.$fields.'\]/', $resource_block, $theForm);
			$infobox = $field;
		} elseif($field[0]=="email"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-form-email" name="email" '.$disabled.' value="'.$value.'" title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="country"){
			$theForm=str_replace('['.$fields.']', '<select id="easy-form-country" '.$disabled.' title="'.$title.'" name="country">'.easyreservations_country_options($value).'</select>', $theForm);
		} elseif($field[0]=="show_price"){
			if(isset($field['before'])) $before = $field['before'];
			else $before ='';
			$theForm=preg_replace('/\['.$fields.'\]/', '<span class="easy-form-price" title="'.$title.'" style="'.$style.'">'.$before.'<span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &'.RESERVATIONS_CURRENCY.';</span>', $theForm);
		} elseif($field[0]=="captcha"){
			require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
			$captcha_instance = new easy_ReallySimpleCaptcha();
			$word = $captcha_instance->generate_random_word();
			$prefix = mt_rand();
			$url = $captcha_instance->generate_image($prefix, $word);

			$theForm=preg_replace('/\['.$fields.'\]/', '<span class="row"><input type="text" title="'.$title.'" name="captcha_value" id="easy-form-captcha" style="width:40px;'.$style.'" ><img id="easy-form-captcha-img"	style="vertical-align:middle;margin-top: -5px;" src="'.RESERVATIONS_URL.'/lib/captcha/tmp/'.$url.'"><input type="hidden" value="'.$prefix.'" name="captcha_prefix"></span>', $theForm);
		} elseif($field[0]=="hidden"){
			if($field[1]=="room" || $field[1]=="resource"){
				$roomfield=1;
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="easyroom" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="from"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="from" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="to"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" id="easy-form-units" name="nights" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="units" || $field[1]=="times"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="nights" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="persons" || $field[1]=="adults"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="persons" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="childs"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="childs" value="'.$field[2].'">', $theForm);
			} else {
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="'.$field[1].'" id="'.$field[1].'" value="'.$field[2].'">', $theForm);
			}
		} elseif($field[0]=="rooms" || $field[0]=="resources"){
			$roomfield=1;
			if(isset($field['exclude'])) $exclude = explode(',', $field['exclude']); else $exclude = '';
			if($isCalendar == true) $calendar_action = "document.CalendarFormular.easyroom.value=this.value;easyreservations_send_calendar('shortcode');"; else $calendar_action = '';
			$theForm=str_replace('['.$fields.']', '<select name="easyroom" id="form_room" '.$disabled.' onchange="'.$calendar_action.$price_action.$validate_action.'">'.easyreservations_resource_options($value, 0, $exclude).'</select>', $theForm);
		} elseif($field[0]=="custom"){
			if(isset($field[3])) $valuefield=str_replace('"', '', $field[3]);
			if(end($field) == "*") $req = 'req'; else $req = '';
			if($field[1]=="text"){
				$theForm=str_replace('['.$fields.']', '<input title="'.$title.'" style="'.$style.'" '.$disabled.' type="text" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'">', $theForm);
			} elseif($field[1]=="textarea"){
				$theForm=str_replace('['.$fields.']', '<textarea title="'.$title.'" style="'.$style.'" '.$disabled.' name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'"></textarea>', $theForm);
			} elseif($field[1]=="check" || $field[1]=="checkbox"){
				if(isset($field['checked'])) $checked = ' checked="'.$field['checked'].'"'; else $checked = '';
				if(!empty($disabled)) $theForm=str_replace('['.$fields.']', '<input type="hidden" title="'.$title.'" '.$checked.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">', $theForm);
				else $theForm=str_replace('['.$fields.']', '<input type="checkbox" title="'.$title.'" '.$checked.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">', $theForm);
			} elseif($field[1]=="radio"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$theForm=str_replace('['.$fields.']', '<span class="radio"><input type="radio" title="'.$title.'" '.$disabled.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$valuefield.'"> '.$valuefield.'</span>', $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9_ \\,\\t]+$/", $valuefield)){
					$valueexplodes=explode(",", $valuefield);
					$custom_radio='';
					foreach($valueexplodes as $value){
						if($value != '') $custom_radio .= '<span class="radio"><input type="radio" title="'.$title.'" '.$disabled.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'"> '.$value.'</span>';
					}
					$theForm=str_replace($fields, $custom_radio, $theForm);
				}
			} elseif($field[1]=="select"){
				if(preg_match("/^[0-9]+$/", $valuefield)){
					$theForm=preg_replace('/\['.$fields.'\]/', '<select title="'.$title.'" style="'.$style.'" '.$disabled.'  name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">'.easyreservations_num_options(1,$valuefield).'</select>', $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$theForm=preg_replace('/\['.$fields.'\]/', '<select title="'.$title.'" style="'.$style.'" '.$disabled.'  name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'"><option value="'.$valuefield.'">'.$field[3].'</option></select>', $theForm);
				} elseif(strstr($valuefield,",")) {
					$valueexplodes=explode(",", $valuefield);
					$custom_select='';
					foreach($valueexplodes as $value){
						if($value != '') $custom_select .= '<option value="'.$value.'">'.$value.'</option>';
					}
					$theForm=str_replace($fields, '<select title="'.$title.'" style="'.$style.'" '.$disabled.' name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">'.$custom_select.'</select>', $theForm);
				}
			}
		} elseif($field[0]=="price"){
			$valuefield=str_replace('"', '', $field[3]);
			if(isset($field[4]) && $field[4] == 'pp' ){
				$personfield = 'class="'.$field[4].'"';
			} elseif(isset($field[4]) && $field[4] == 'pn'){
				$personfield = 'class="'.$field[4].'"';
			} elseif(isset($field[4]) && $field[4] == 'pb'){
				$personfield = 'class="'.$field[4].'"';
			} else {
				$personfield = '';
			}
			if($field[1]=="check" || $field[1]=="checkbox"){
				if(isset($field['checked'])) $checked = 'checked="'.$field['checked'].'"'; else $checked = '';
				if(!empty($disabled)) $theForm=preg_replace('/\['.$fields.'\]/', '<input title="'.$title.'" style="'.$style.'" id="custom_price'.$customPrices.'" '.$personfield.' type="hidden" onchange="'.$price_action.'" name="'.$field[2].'" value="'.$valuefield.'">', $theForm);
				else $theForm=preg_replace('/\['.$fields.'\]/', '<input title="'.$title.'" style="'.$style.'" id="custom_price'.$customPrices.'" '.$personfield.' type="checkbox" '.$checked.' onchange="'.$price_action.'" name="'.$field[2].'" value="'.$valuefield.'">', $theForm);
			} elseif($field[1]=="radio"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$explodeprice=explode(":", $valuefield);
					$theForm=preg_replace('/\['.$fields.'\]/', '<span class="radio"><input title="'.$title.'" style="'.$style.'" '.$disabled.' id="custom_price'.$customPrices.'" '.$personfield.' type="radio" onchange="'.$price_action.'" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].'"> '.$explodeprice[0].': '.easyreservations_format_money($explodeprice[1], 1).'</span>', $theForm);
				} elseif(strstr($valuefield,",")) {
					$valueexplodes=explode(",", $valuefield);
					$custom_radio = '<pre>';
					foreach($valueexplodes as $value){
						$explodeprice=explode(":", $value);
						if($value != '') $custom_radio .= '<span class="radio"><input id="custom_price'.$customPrices.'" '.$disabled.' title="'.$title.'" style="'.$style.'" type="radio" '.$personfield.' name="'.$field[2].'" onchange="'.$price_action.'" value="'.$explodeprice[0].':'.$explodeprice[1].'"> '.$explodeprice[0].': '.easyreservations_format_money($explodeprice[1], 1).'</span>';
						$customPrices++;
					}
					$theForm=preg_replace('/\['.$fields.'\]/', $custom_radio.'</pre>', $theForm);
				}
			} elseif($field[1]=="select"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$explodeprice=explode(":", $valuefield);
					$theForm=preg_replace('/\['.$fields.'\]/', '<select id="custom_price'.$customPrices.'" '.$personfield.' '.$disabled.' name="'.$field[2].'" title="'.$title.'" style="'.$style.'" onchange="'.$price_action.'"><option value="'.$explodeprice[0].':'.$explodeprice[1].'">'.$explodeprice[0].': '.easyreservations_format_money($explodeprice[1], 1).'</option></select>', $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9].+$/", $valuefield)){
					$valueexplodes=explode(",", $valuefield);
					$custom_select='';
					foreach($valueexplodes as $value){
						$explodeprice=explode(":", $value);
						if($value != '') $custom_select .= '<option value="'.$explodeprice[0].':'.$explodeprice[1].'">'.$explodeprice[0].': '.easyreservations_format_money($explodeprice[1], 1).'</option>';
					}
					$theForm=str_replace($fields, '<select  '.$personfield.' style="'.$style.'" title="'.$title.'" id="custom_price'.$customPrices.'" '.$disabled.' onchange="'.$price_action.'" name="'.$field[2].'">'.$custom_select.'</select>', $theForm);
				}
			}
			$customPrices++;
		} elseif($field[0]=="submit"){
			if(isset($field[1])) $value=$field[1];
			if(!empty($validate_action)) $action = 'easyreservations_send_validate(\'send\'); ';
			else $action = 'document.getElementById(\'easyFrontendFormular\').submit();';
			$theForm = preg_replace('/\['.$fields.'\]/', '<input type="submit" title="'.$title.'" style="'.$style.'" class="easy-button" onclick="'.$action.'" value="'.$value.'" '.$disabled.'>', $theForm);
		} else {
			$theForm = apply_filters('easy-form-tag', $theForm, $fields);
		}
	}

	if($roomfield == 0 && isset($atts['resource']) && $atts['resource'] > 0) $theForm .= '<input type="hidden" name="easyroom" value="'.$atts['resource'].'">';
	elseif($roomfield == 0 && isset($_POST['easyroom'])) $theForm .= '<input type="hidden" name="easyroom" value="'.$_POST['easyroom'].'">';

	$finalformedgesremoved = str_replace(array('[', ']'), '', $theForm);
	if(isset($atts[0])) $finalformedgesremoved.='<input type="hidden" name="formname" value="'.$atts[0].'">';

	if($finalform == '') $finalform.='<div class="easyFrontendFormular"><form onsubmit="'.$action.' return false;" method="post" id="easyFrontendFormular" name="easyFrontendFormular"><input name="easynonce" type="hidden" value="'.wp_create_nonce('easy-user-add').'"><input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">'.$finalformedgesremoved.'<!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org --></form></div>';

	if(isset($_POST['from'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.from.value="'.$_POST['from'].'";document.easyFrontendFormular.to.value="'.$_POST['to'].'"; var theCustomField = \'\';</script>'; 
		foreach($_POST as $key => $val){
			if (strpos($key, 'easy-custom-') === 0){
				$finalform .= '<script>theCustomField = document.getElementsByName(\''.$key.'\'); if(theCustomField[0]) theCustomField[0].value = \''.$val.'\';</script>'; 
			}
		}
	}
	if(isset($_POST['thename'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.thename.value="'.$_POST['thename'].'";</script>';
	if(isset($_POST['message'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.message.value="'.$_POST['message'].'";</script>';
	if(isset($_POST['date-from-hour']) && is_numeric($_POST['date-from-hour'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-from-hour\'); if(datefromfield) datefromfield.value="'.$_POST['date-from-hour'].'";</script>';
	if(isset($_POST['date-from-min']) && is_numeric($_POST['date-from-min'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-from-min\'); if(datefromfield) datefromfield.value="'.$_POST['date-from-min'].'";</script>';
	if(isset($_POST['date-to-hour']) && is_numeric($_POST['date-to-hour'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-to-hour\'); if(datefromfield) datefromfield.value="'.$_POST['date-to-hour'].'";</script>';
	if(isset($_POST['date-to-min']) && is_numeric($_POST['date-to-min'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-to-min\'); if(datefromfield) datefromfield.value="'.$_POST['date-to-min'].'";</script>';
	if(isset($_POST['email'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.email.value="'.$_POST['email'].'";</script>';
	if(isset($_POST['persons']))	$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.persons.selectedIndex='.($_POST['persons']-1).';</script>';
	if(isset($_POST['childs'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.childs.selectedIndex='.$_POST['childs'].';</script>';
	if(isset($_POST['country'])) $finalform .= '<script>function setCountry(country) {var x = document.easyFrontendFormular.country;if(x){for (var i = 0; i < x.options.length; i++) {if (x.options[i].value == country){x.options[i].selected = true;break;}}}}setCountry("'.$_POST['country'].'");</script>';
	if(isset($_POST['easyroom'])) $finalform .= '<script>function setRoom(roomid){var x=document.easyFrontendFormular.easyroom;if(x){for (var i = 0; i < x.options.length; i++){if(x.options[i].value == roomid){x.options[i].selected = true;break;}}}}setRoom('.$_POST['easyroom'].');</script>';
	$popuptemplate = '<span class="easy_validate_message">'.$atts['validate'].'</span>';
	if(!empty($atts['subvalidate'])) $popuptemplate.= '<\\span class="easy_validate_message_sub">'.$atts['subvalidate'].'</span>';
	$popuptemplate.= '<\\table id="easy_overlay_table"><\\thead><\\tr>';
	$popuptemplate.= '<\\th>'.__('Time', 'easyReservations').'</th>';
	$popuptemplate.= '<\\th>'.__($atts['resourcename']).'</th>';
	$popuptemplate.= '<\\th>'.__('Persons', 'easyReservations').'</th>';
	$popuptemplate.= '<\\th>'.__('Price', 'easyReservations').'</th>';
	$popuptemplate.= '<\\th></th></tr></thead><\\tbody id="easy_overlay_tbody"></tbody></table>';
	$popuptemplate.= '<\\input type="button" onclick="easyAddAnother();" value="'.__('Add another reservation', 'easyReservations').'">';
	$popuptemplate.= '<\\input class="easy_overlay_submit"  type="button" onclick="easyFormSubmit(1);" value="'.__('Submit all reservations', 'easyReservations').'">';
	$popuptemplate = json_encode(array(str_replace(array("\n","\r"), '', trim($popuptemplate))));
	$atts_encode = json_encode($atts);
	$popuptemplate = str_replace(array("\n","\r"), '', trim('<script type="text/javascript">var easyReservationAtts='.$atts_encode.';var easyInnerlayTemplate='.$popuptemplate.';</script>'));
	if(isset($final)) return $final.$popuptemplate;
	$finalform.= $popuptemplate;
	add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');
	if($infobox !== false){
		$all_rooms = easyreservations_get_rooms(true);
		if(isset($infobox['img']) && ($infobox['img'] == "yes" || $infobox['img'] == "true")){
			if(isset($infobox['img_y']) && is_numeric($infobox['img_y'])) $height = $infobox['img_y'];
			if(isset($infobox['img_x']) && is_numeric($infobox['img_x'])) $width = $infobox['img_y'];
			if(isset($width) && isset($height)) $size = array($width,$height);
			else $size = 'post-thumbnail';
			$image = 1;
		} else $image = 0;

		if(isset($infobox['title']) && ($infobox['title'] == "yes" || $infobox['title'] == "true")) $title = 1;
		else $title = 0;

		if(isset($infobox['content']) && $infobox['content'] != "off" && $infobox['content'] > 0) $content = 1;
		else $content = 0;

		if(isset($infobox['excerpt']) && is_numeric($infobox['excerpt'])) $excerpt = 1;
		else $excerpt = 0;

		if($excerpt == 0 && $title == 0 && $content == 0){
			$class = ' only-image';
			$only = 1;
		} else {
			$class = '';
			$only = 0;
		}

		if(isset($infobox['theme'])){
			$class.= ' '.$infobox['theme'];
			$theme = $infobox['theme'];
		} else $theme = 'medium';
		if(isset($infobox['style'])) $style = $infobox['style'];

		foreach($all_rooms as $key => $resource){
			$all_rooms[$key] = (array) $resource;
			if($image == 1) $all_rooms[$key]['thumb'] =  get_the_post_thumbnail($resource->ID, $size,array('class' => 'easy_infobox_thumb'));
			if($content == 1 && is_numeric($infobox['content'])) $all_rooms[$key]['post_content'] = stripslashes(html_entity_decode(strip_tags(substr( $all_rooms[$key]['post_content'], 0 , $infobox['content']))));
			if($excerpt == 1){
				$content_post = get_post($resource->ID);
				$all_rooms[$key]['excerpt'] = $content_post->post_excerpt;	
			}
		}
		$all_rooms = json_encode($all_rooms);
		$finalform.= <<<JAVASCRIPT
		<script>
		var all_resoures_array = $all_rooms;
		function easy_load_infobox(){
			var res = jQuery("*[name=easyroom]").val();
			var res_info = all_resoures_array[res];
			var start = '<\div class="easy_infobox_border$class" style="$style">';
			var content = "";
			if($only == 1) jQuery("#resource_infobox").addClass("only-image");
			if($title == 1) content += '<span class="easy_infobox_title">'+res_info["post_title"]+'</span>';
			if(res_info["thumb"] && "$theme" == "big") content += res_info["thumb"];
			else if(res_info["thumb"]) content = res_info["thumb"]+content;
			if(res_info["excerpt"]) content += '<span class="easy_infobox_excerpt">'+res_info["excerpt"]+'</span>';
			if($content == 1) content += '<span class="easy_infobox_content">'+res_info["post_content"]+'</span>';
			content+='</\div>';
			jQuery("#resource_infobox").css("display","none");
			jQuery("#resource_infobox").html(start+content);
			jQuery("#resource_infobox").fadeIn("slow");
		}
		</script>
JAVASCRIPT;
		add_action('wp_print_footer_scripts', 'easyreservations_make_resource_infobox');
	} else {
		global $the_rooms_array;
		$rooms = '';
		foreach($the_rooms_array as $key => $resource){
			$rooms[$key] = array( 'post_title' => __($resource->post_title));
		}
		$finalform .= '<script>var all_resoures_array = '.json_encode($rooms).';</script>';
	}
	return $finalform;
}

function easyreservations_make_datepicker(){
	easyreservations_build_datepicker(0, array('easy-form-from', 'easy-form-to'));
}

function easyreservations_make_resource_infobox(){
	echo '<script type="text/javascript">
	jQuery(document).ready(function(){
		easy_load_infobox();
		jQuery("*[name=easyroom]").bind("change", false, function(){ easy_load_infobox(); });
	});</script>';
}
?>