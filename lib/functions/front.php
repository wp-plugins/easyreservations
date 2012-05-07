<?php
/**
* 	@functions for frontend only
*/

	/**
	*	Returns url of current page before wp can do it
	*/
	function easyreservations_current_page() {
		$pageURL = 'http';
		if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	/**
	*	Returns formated status
	*
	*	$status = status of reservtion
	*/

	function reservations_status_output($status){ //gives out colored and named stauts

		if($status=="yes") $theStatus= '<b style="color:#009B1C">'.__( 'approved' , 'easyReservations' ).'</b>';
		elseif($status=="no") $theStatus= '<b style="color:#E80000;">'.__( 'rejected' , 'easyReservations' ).'</b>';
		elseif($status=="del") $theStatus= '<b style="color:#E80000;">'.__( 'trashed' , 'easyReservations' ).'</b>';
		elseif($status=="") $theStatus= '<b style="color:#0072E5;">'.__( 'pending' , 'easyReservations' ).'</b>';

		return $theStatus;
	}

	/**
	 *	Check frontend inputs (from a form or User ControlPanel), returns errors or add to DB and send mails
	 *
	 *	$res = array with reservations informations
	 *	$where = 'user-add'/'user-edit'
	*/

	function easyreservations_check_reservation($res, $where) {

		global $the_rooms_intervals_array;
		$val_room = $res['room'];
		$val_from = strtotime($res['from']) + $res['fromplus'];
		$val_fromdate_sql = date("Y-m-d H:i:s", $val_from);
		if(!empty($res['to'])){
			$val_to = strtotime($res['to'])+ $res['toplus'];
			$val_nights = easyreservations_get_nights($the_rooms_intervals_array[$val_room], $val_from, $val_to);
		} elseif(!empty($res['nights'])){
			$val_nights = $res['nights'];
			$val_to = $val_from + ($val_nights * $the_rooms_intervals_array[$val_room] ) + $res['toplus'];
		} else $val_nights = 1;
		$val_todate_sql = date("Y-m-d H:i:s", $val_to);
		$val_name = $res['thename'];
		$val_email = $res['email'];
		$val_country = $res['country'];
		$val_persons = $res['persons'];
		$val_childs = $res['childs'];
		$val_custom = $res['custom'];
		$val_customp = $res['customp'];
		if(isset($res['old_email'])) $val_oldemail = $res['old_email'];
		$error = "";

		$resource_req = get_post_meta($val_room, 'easy-resource-req', TRUE);
		if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 0, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);

		$rooms = easyreservations_get_rooms();

		if($resource_req['pers-min'] > ($val_persons+$val_childs)){
			$error[] = 'easy-form-persons';
			$error[] =  sprintf(__( 'At least %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-min'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['pers-max'] != 0 && $resource_req['pers-max'] < ($val_persons+$val_childs)){
			$error[] = 'easy-form-persons';
			$error[] =  sprintf(__( 'Maximum %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-max'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['nights-min'] != 0 && $resource_req['nights-min'] > $val_nights){
			$error[] = 'date';
			$error[] =  sprintf(__( 'At least %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-min'], easyreservations_interval_infos($the_rooms_intervals_array[$val_room], 0, $resource_req['nights-min']), __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['nights-max'] != 0 && $resource_req['nights-max'] < $val_nights){
			$error[] = 'date';
			$error[] =  sprintf(__( 'Maximum %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-max'], easyreservations_interval_infos($the_rooms_intervals_array[$val_room], 0, $resource_req['nights-max']), __(easyreservations_get_the_title($val_room, $rooms)));
		}

		if(isset($res['id'])) $val_id = $res['id'];

		if(isset($res['captcha']) && !empty($res['captcha'])){

			$captcha = $res['captcha'];

			if(!class_exists('ReallySimpleCaptcha')) require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
			$prefix = $captcha['captcha_prefix'];
			$the_answer_from_respondent = $captcha['captcha_value'];
			$captcha_instance = new ReallySimpleCaptcha();
			$correct = $captcha_instance->check($prefix, $the_answer_from_respondent);
			$captcha_instance->remove($prefix);
			$captcha_instance->cleanup(); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?

			if($correct != 1)	$error.=  '<li><label for="easy-form-captcha">'.__( 'Please enter the correct captcha' , 'easyReservations' ).'</label></li>';
		}

		if((strlen($val_name) > 30 || strlen($val_name) <= 1) || $val_name == ""){ /* check name */
			$error.=  '<li><labe for="easy-form-thename">'.__( 'Please enter a correct name' , 'easyReservations' ).'<br>';
		}

		if($val_from < time()){ /* check arrival Date */
			$error.=  '<li><labe for="easy-form-from">'.__( 'The arrival date has to be in future' , 'easyReservations' ).'</label></li>';
		}

		if($val_to < time()){ /* check departure Date */
			$error.= '<li><labe for="easy-form-to">'. __( 'The departure date has to be in future' , 'easyReservations' ).'</label></li>';
		}

		if($val_to < $val_from){ /* check difference between arrival and departure date */
			$error.= '<li><labe for="easy-form-to">'. __( 'The departure date has to be after the arrival date' , 'easyReservations' ).'</label></li>';
		}

		$pattern_mail = "/^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+\.[a-zA-Z]{2,4}$/";
		if(!preg_match($pattern_mail, $val_email) || $val_email == ""){ /* check email */
			$error.=  '<li><labe for="easy-form-email">'.__( 'Please enter a correct eMail' , 'easyReservations' ).'</label></li>';
		}

		if (!is_numeric($val_persons) || $val_persons == '' ){ /* check persons */
			$error.= '<li><labe for="easy-form-persons">'. __( 'Persons has to be a number' , 'easyReservations' ).'</label></li>';
		}
		
		$numbererrors=easyreservations_check_avail($val_room, $val_from, 0, $val_to, 1 ); /* check rooms availability */

		if($numbererrors != '' || $numbererrors > 0){
			$error.= '<li><labe for="easy-form-to">'.__( 'Isn\'t available at' , 'easyReservations' ).' '.$numbererrors.'</label></li>';
		}

		$reservation_support_mail = get_option("reservations_support_mail");

		if($error == ""){
			global $wpdb;

			if($where == "user-add"){

				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(name, email, arrival, departure, room, number, childs, country, custom, customp, reservated ) 
				VALUES (%s, %s, %s, %s, %d, %d, %d, %s, %s, %s, NOW() )", $val_name, $val_email, $val_fromdate_sql, $val_todate_sql, $val_room, $val_persons, $val_childs, $val_country, $val_custom, $val_customp ) );

				$newID = mysql_insert_id();
				$error = $newID;
				do_action('easy-add-res', $newID, '1');

				$emailformation=get_option('reservations_email_to_admin');
				$emailformation2=get_option('reservations_email_to_user');

				if($emailformation['active'] == 1)	easyreservations_send_mail($emailformation['msg'], $reservation_support_mail, $emailformation['subj'], '', $newID, '');
				if($emailformation2['active'] == 1)	easyreservations_send_mail($emailformation2['msg'], $val_email, $emailformation2['subj'], '', $newID, '');

			} elseif($where == "user-edit"){

				$checkSQLedit = "SELECT email, name, arrival, departure, number, childs, country, room, roomnumber, approve, custom, customp, price FROM ".$wpdb->prefix ."reservations WHERE id='$val_id' AND email='$val_oldemail' ";
				$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

				$beforeArray = array( 'arrival' => $checkQuerry[0]->arrival, 'departure' => $checkQuerry[0]->departure, 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country, 'customp' => $checkQuerry[0]->customp );
				$afterArray = array( 'arrival' => $val_fromdate_sql, 'departure' => $val_todate_sql, 'email' => $val_email, 'name' => $val_name, 'persons' => $val_persons, 'childs' => $val_childs, 'room' =>  $val_room, 'custom' => $val_custom, 'country' => $val_country, 'customp' => $val_customp );

				$changelog = easyreservations_generate_res_changelog($beforeArray, $afterArray);
				
				if($checkQuerry[0]->departure != $val_to || $checkQuerry[0]->arrival != $val_fromdate_sql || $checkQuerry[0]->number != $val_persons || $checkQuerry[0]->room != $val_room){
					if($checkQuerry[0]->price)
					$explodePrice = explode(";", $checkQuerry[0]->price);
					$newPrice = " price='".$explodePrice[1]."',";
				} else $newPrice = '';

				if(!empty($val_custom))	$customfields =	easyreservations_edit_custom($val_custom,		$val_id, 0, 1, false, 0, 'cstm', 'edit');
				if(!empty($val_customp)) 	$custompfields =	easyreservations_edit_custom($val_customp,	$val_id, 0, 1, false, 1, 'cstm', 'edit');

				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrival='$val_fromdate_sql', departure='$val_todate_sql', name='$val_name', email='$val_email', room='$val_room', number='$val_persons', childs='$val_childs', custom='$customfields', customp='$custompfields', country='$val_country', ".$newPrice." approve='' WHERE id='$val_id' ")) or trigger_error('mySQL-Fehler: '.mysql_error(), E_USER_ERROR);

				$emailformation=get_option('reservations_email_to_admin');
				$emailformation2=get_option('reservations_email_to_user_edited');

				if($checkQuerry[0]->email == $val_email){
					if($emailformation['active'] == 1)	easyreservations_send_mail($emailformation['msg'],		$reservation_support_mail,	$emailformation['subj'],	'', $val_id, $changelog);
					if($emailformation2['active'] == 1)	easyreservations_send_mail($emailformation2['msg'],	$val_email,							$emailformation2['subj'],	'', $val_id, $changelog);
				} else {
					if($emailformation['active'] == 1) 	easyreservations_send_mail($emailformation['msg'],		$reservation_support_mail,	$emailformation['subj'],	'', $val_id, $changelog);
					if($emailformation2['active'] == 1)	easyreservations_send_mail($emailformation2['msg'],	$val_email,							$emailformation2['subj'],	'', $val_id, $changelog);
					if($emailformation2['active'] == 1)	easyreservations_send_mail($emailformation2['msg'],	$checkQuerry[0]->email,		$emailformation2['subj'],	'', $val_id, $changelog);
				}
			}
		}
		
		return $error;
	}

	function easyreservations_generate_form($theForm, $price_action, $validate_action, $isCalendar, $the_resource = 0, $error = 0, $local = false){
		$theForm = stripslashes($theForm);

		preg_match_all(' /\[.*\]/U', $theForm, $matches);
		$mergearray=array_merge($matches[0], array());
		$edgeoneremove=str_replace('[', '', $mergearray);
		$edgetworemoves=str_replace(']', '', $edgeoneremove);
		$customPrices = 0;
		$roomfield = 0;

		foreach($edgetworemoves as $fields){
			$field=shortcode_parse_atts( $fields);
			if(isset($field['value'])) $value = $field['value'];
			else $value='';
			if(isset($field['style'])) $style = $field['style'];
			else $style='';
			if(isset($field['title'])) $title = $field['title'];
			else $title='';
			if(isset($field['disabled'])) $disabled =  'disabled="disabled"';
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
				$theForm=str_replace('['.$fields.']', '<select id="'.$field[0].'" name="'.$field[0].'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyReservations_num_options("00", 23, $value).'</select>', $theForm);
			} elseif($field[0]=="date-from-min" || $field[0]=="date-to-min"){
				$theForm=str_replace('['.$fields.']', '<select id="'.$field[0].'" name="'.$field[0].'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyReservations_num_options("00", 59, $value).'</select>', $theForm);
			} elseif($field[0]=="units" || $field[0]=="nights"){
				if(isset($field[2])) $end = $field[2]; else $end = 6;
				if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
				$theForm=str_replace('['.$fields.']', '<select id="easy-form-units" name="nights" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyReservations_num_options($start, $end, $value).'</select>', $theForm);
			} elseif($field[0]=="persons" || $field[0]=="adults"){
				$start = 1;
				if(isset($field[2])) $end = $field[2]; else $end = 6;
				if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
				$theForm=preg_replace('/\['.$fields.'\]/', '<select id="easy-form-persons" name="persons" '.$disabled.' style="'.$style.'" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.easyReservations_num_options($start,$end,$value).'</select>', $theForm);
			} elseif($field[0]=="childs"){
				$start = 0;
				if(isset($field[2])) $end = $field[2]; else $end = 6;
				if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
				$theForm=preg_replace('/\['.$fields.'\]/', '<select name="childs" '.$disabled.' style="'.$style.'" title="'.$title.'" onchange="'.$price_action.'">'.easyReservations_num_options($start,$end,$value).'</select>', $theForm);
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
			} elseif($field[0]=="email"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-form-email" name="email" '.$disabled.' value="'.$value.'" title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
			} elseif($field[0]=="country"){
				$theForm=str_replace('['.$fields.']', '<select id="easy-form-country" '.$disabled.' title="'.$title.'" name="country">'.easyReservations_country_select($value).'</select>', $theForm);
			} elseif($field[0]=="show_price"){
				if(isset($field['before'])) $before = $field['before'];
				else $before ='';
				$theForm=preg_replace('/\['.$fields.'\]/', '<span class="easy-form-price" title="'.$title.'" style="'.$style.'">'.$before.'<span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &'.RESERVATIONS_CURRENCY.';</span>', $theForm);
			} elseif($field[0]=="captcha"){
				if(!isset($chaptchaFileAdded) && !class_exists('ReallySimpleCaptcha')) require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
				$captcha_instance = new ReallySimpleCaptcha();
				$word = $captcha_instance->generate_random_word();
				$prefix = mt_rand();
				$url = $captcha_instance->generate_image($prefix, $word);

				$theForm=preg_replace('/\['.$fields.'\]/', '<span class="row"><input type="text" title="'.$title.'" name="captcha_value" id="easy-form-captcha" style="width:40px;'.$style.'" ><img id="easy-form-captcha-img"	style="vertical-align:middle;margin-top: -5px;" src="'.RESERVATIONS_LIB_DIR.'/captcha/tmp/'.$url.'"><input type="hidden" value="'.$prefix.'" name="captcha_prefix"></span>', $theForm);
			} elseif($field[0]=="hidden"){
				if($field[1]=="room" || $field[1]=="resource"){
					$roomfield=1;
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="room" value="'.$field[2].'">', $theForm);
				} elseif($field[1]=="from"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="from" value="'.$field[2].'">', $theForm);
				} elseif($field[1]=="to"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="to" value="'.$field[2].'">', $theForm);
				} elseif($field[1]=="units"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="nights" value="'.$field[2].'">', $theForm);
				} elseif($field[1]=="persons" || $field[1]=="adults"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="persons" value="'.$field[2].'">', $theForm);
				} elseif($field[1]=="childs"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="childs" value="'.$field[2].'">', $theForm);
				}
			} elseif($field[0]=="rooms" || $field[0]=="resources"){
				$roomfield=1;
				if(isset($field['exclude'])) $exclude = explode(',', $field['exclude']); else $exclude = '';
				if($isCalendar == true) $calendar_action = "document.CalendarFormular.room.value=this.value;easyreservations_send_calendar('shortcode');"; else $calendar_action = '';
				$theForm=str_replace('['.$fields.']', '<select name="room" id="form_room" '.$disabled.' onChange="'.$calendar_action.$price_action.'">'.reservations_get_room_options($value, 0, $exclude).'</select>', $theForm);
			} elseif($field[0]=="custom"){
				if(isset($field[3])) $valuefield=str_replace('"', '', $field[3]);
				if($field[count($field)-1] == "*") $req = 'req'; else $req = '';
				if($field[1]=="text"){
					$theForm=str_replace('['.$fields.']', '<input title="'.$title.'" style="'.$style.'" '.$disabled.' type="text" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'">', $theForm);
				} elseif($field[1]=="textarea"){
					$theForm=str_replace('['.$fields.']', '<textarea title="'.$title.'" style="'.$style.'" '.$disabled.' name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'"></textarea>', $theForm);
				} elseif($field[1]=="check"){
					if(isset($field['checked'])) $checked = ' checked="'.$field['checked'].'"'; else $checked = '';
					$theForm=str_replace('['.$fields.']', '<input type="checkbox" title="'.$title.'" '.$disabled.$checked.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">', $theForm);
				} elseif($field[1]=="radio"){
					if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
						$theForm=str_replace('['.$fields.']', '<input type="radio" title="'.$title.'" '.$disabled.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$valuefield.'"> '.$valuefield, $theForm);
					} elseif(preg_match("/^[a-zA-Z0-9_ \\,\\t]+$/", $valuefield)){
						$valueexplodes=explode(",", $valuefield);
						$custom_radio='';
						foreach($valueexplodes as $value){
							if($value != '') $custom_radio .= '<input type="radio" title="'.$title.'" '.$disabled.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'"> '.$value.'<br>';
						}
						$theForm=str_replace($fields, $custom_radio, $theForm);
					}
				} elseif($field[1]=="select"){
					if(preg_match("/^[0-9]+$/", $valuefield)){
						$theForm=preg_replace('/\['.$fields.'\]/', '<select title="'.$title.'" style="'.$style.'" '.$disabled.'  name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">'.easyReservations_num_options(1,$valuefield).'</select>', $theForm);
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
					$personfields = ':1';
				} elseif(isset($field[4]) && $field[4] == 'pn'){
					$personfield = 'class="'.$field[4].'"';
					$personfields = ':2';
				} elseif(isset($field[4]) && $field[4] == 'pb'){
					$personfield = 'class="'.$field[4].'"';
					$personfields = ':3';
				} else {
					$personfield = '';
					$personfields = '';
				}
				if($field[1]=="checkbox"){
					if(isset($field['checked'])) $checked = 'checked="'.$field['checked'].'"'; else $checked = '';
					$theForm=preg_replace('/\['.$fields.'\]/', '<input title="'.$title.'" style="'.$style.'" '.$disabled.' id="custom_price'.$customPrices.'" '.$personfield.' type="checkbox" '.$checked.' onchange="'.$price_action.'" name="'.$field[2].'" value="'.$valuefield.$personfields.'">', $theForm);
				} elseif($field[1]=="radio"){
					if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
						$explodeprice=explode(":", $valuefield);
						$theForm=preg_replace('/\['.$fields.'\]/', '<input title="'.$title.'" style="'.$style.'" '.$disabled.' id="custom_price'.$customPrices.'" '.$personfield.' type="radio" onchange="'.$price_action.'" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'"> '.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1), $theForm);
					} elseif(strstr($valuefield,",")) {
						$valueexplodes=explode(",", $valuefield);
						$custom_radio = '<pre>';
						foreach($valueexplodes as $value){
							$explodeprice=explode(":", $value);
							if($value != '') $custom_radio .= '<input id="custom_price'.$customPrices.'" '.$disabled.' title="'.$title.'" style="'.$style.'" type="radio" '.$personfield.' name="'.$field[2].'" onchange="'.$price_action.'" value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'"> '.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1).'<br>';
						}
						$theForm=preg_replace('/\['.$fields.'\]/', $custom_radio.'</pre>', $theForm);
					}
				} elseif($field[1]=="select"){
					if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
						$explodeprice=explode(":", $valuefield);
						$theForm=preg_replace('/\['.$fields.'\]/', '<select id="custom_price'.$customPrices.'" '.$personfield.' '.$disabled.' name="'.$field[2].'" title="'.$title.'" style="'.$style.'" onchange="'.$price_action.'"><option value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'">'.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1).'</option></select>', $theForm);
					} elseif(preg_match("/^[a-zA-Z0-9].+$/", $valuefield)){
						$valueexplodes=explode(",", $valuefield);
						$custom_select='';
						foreach($valueexplodes as $value){
							$explodeprice=explode(":", $value);
							if($value != '') $custom_select .= '<option value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'">'.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1).'</option>';
						}
						$theForm=str_replace($fields, '<select  '.$personfield.' style="'.$style.'" title="'.$title.'" id="custom_price'.$customPrices.'" '.$disabled.' onchange="'.$price_action.'" name="'.$field[2].'">'.$custom_select.'</select>', $theForm);
					}
				}
				$customPrices++;
			} elseif($field[0]=="submit"){
				if(isset($field[1])) $value=$field[1];
				if(!empty($validate_action)) $action = 'easyreservations_send_validate(\'send\'); ';
				else $action = 'document.getElementById(\'easyFrontendFormular\').submit();';
				$theForm=preg_replace('/\['.$fields.'\]/', '<input title="'.$title.'" style="'.$style.'" type="button" class="easy-button" onclick="'.$action.'" '.$disabled.' value="'.$value.'">', $theForm);
			}
		}

		if($roomfield == 0 && $the_resource > 0) $theForm .= '<input type="hidden" name="room" value="'.$the_resource.'">';
		///$theForm = do_action('easy-form-content', $theForm);
		$theForm = apply_filters( 'easy-form-content', $theForm, $local);
		$finalformedgeremove1=str_replace('[', '', $theForm);
		$finalformedgesremoved=str_replace(']', '', $finalformedgeremove1);

		return $finalformedgesremoved;
	}
?>