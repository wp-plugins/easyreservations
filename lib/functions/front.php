<?php
/**
* 	@functions for frontend only
*/

	/**
	*	Returns url of current page before wp can do it
	*/
	function easyreservations_current_page() {
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
	 *	Check frontend inputs (from a form or user-edit), returns errors or add to DB and send mails
	 *
	 *	$res = array with reservations informations
	 *	$where = 'user-add'/'user-edit'
	*/

	function easyreservations_check_reservation($res, $where) {

		$val_from = strtotime($res['from']);
		$val_fromdate = date("d.m.Y", $val_from);
		$val_fromdate_sql = date("Y-m-d", $val_from);
		$val_fromdat = date("Y-m", $val_from);
		if(!empty($res['to'])){
			$val_to = strtotime($res['to']);
			$val_nights = ( $val_to - $val_from ) / 86400;
		} elseif(!empty($res['nights'])){
			$val_nights = $res['nights'];
			$val_to = $val_from + ($val_nights * 86400 );
		}

		if(!empty($res['offer'])) $val_offer = $res['offer'];
		else $val_offer = 0;

		$val_room = $res['room'];
		$val_name = $res['thename'];
		$val_email = $res['email'];
		$val_country = $res['country'];
		$val_persons = $res['persons'];
		$val_childs = $res['childs'];
		$val_message = $res['message'];
		$val_custom = $res['custom'];
		$val_customp = $res['customp'];
		if(isset($res['old_email'])) $val_oldemail = $res['old_email'];
		$error = "";
		
		if(isset($res['id'])) $val_id = $res['id'];
		
		if(isset($res['captcha']) && !empty($res['captcha'])){
		
			$captcha = $res['captcha'];

			require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
			$prefix = $captcha['captcha_prefix'];
			$the_answer_from_respondent = $captcha['captcha_value'];
			$captcha_instance = new ReallySimpleCaptcha();
			$correct = $captcha_instance->check($prefix, $the_answer_from_respondent);
			$chaptchaFileAdded = 1;
			$captcha_instance->remove($prefix);
			$captcha_instance->cleanup(); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?

			if($correct != 1)	$error.=  __( 'Please enter the correct captcha' , 'easyReservations' ).'</b><br>';
		}

		if((strlen($val_name) > 30 OR strlen($val_name) <= 3) OR $val_name == ""){ /* check name */
			$error.=  __( 'Please enter a correct name' , 'easyReservations' ).'<br>';
		}

		if($val_from < time()){ /* check arrival Date */
			$error.=  __( 'The arrival date has to be in future' , 'easyReservations' ).'<br>';
		}

		if($val_to < time()){ /* check departure Date */
			$error.=  __( 'The depature date has to be in future' , 'easyReservations' ).'<br>';
		}

		if($val_to <= $val_from){ /* check difference between arrival and departure date */
			$error.=  __( 'The depature date has to be after the arrival date' , 'easyReservations' ).'<br>';
		}

		$pattern_mail = "/^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+\.[a-zA-Z]{2,4}$/";
		if(!preg_match($pattern_mail, $val_email) OR $val_email == ""){ /* check email */
			$error.=  __( 'Please enter a correct eMail' , 'easyReservations' ).'<br>'; 
		}

		if (!is_numeric($val_persons) OR $val_persons == '' ){ /* check persons */
			$error.=  __( 'Persons has to be a number' , 'easyReservations' ).'<br>';
		}
		
		$numbererrors=easyreservations_check_avail($val_room, $val_from, 0, $val_nights, $val_offer, 1 ); /* check rooms availability */

		if($numbererrors != '' || $numbererrors > 0){
			$error.= __( 'Isn\'t available at' , 'easyReservations' ).' '.$numbererrors.'<br>';
		}

		$reservation_support_mail = get_option("reservations_support_mail");

		if($error == ""){
			global $wpdb;

			if($where == "user-add"){

				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(name,  email, notes, nights, arrivalDate, dat, room, number, childs, country, special, custom, customp, reservated ) 
				VALUES ('$val_name', '$val_email', '$val_message', '$val_nights', '$val_fromdate_sql', '$val_fromdat', '$val_room', '$val_persons', '$val_childs', '$val_country', '$val_offer', '$val_custom', '$val_customp', NOW() )" ) );

				$newID = mysql_insert_id();
				$error = $newID;
				$priceFunction = easyreservations_price_calculation($newID,'');
				$getThePrice = $priceFunction['price'];
				$thePrice = reservations_format_money($getThePrice);

				if($val_offer != "0"){
					$specialoffer =get_the_title($val_offer); 
				}
				if($val_offer == "0") $specialoffer =  __( 'None' , 'easyReservations' );

				$roomtitle = __(get_the_title($val_room));

				$emailformation=get_option('reservations_email_to_admin');
				$emailformation2=get_option('reservations_email_to_user');

				if($emailformation['active'] == 1) easyreservations_send_mail($emailformation['msg'], $reservation_support_mail, $emailformation['subj'], '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, '');
				if($emailformation2['active'] == 1) easyreservations_send_mail($emailformation2['msg'], $val_email, $emailformation2['subj'], '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, '');

			} elseif($where == "user-edit"){
			
				$checkSQLedit = "SELECT email, name, arrivalDate, nights, number, childs, country, room, special, approve, notes, custom, customp, price FROM ".$wpdb->prefix ."reservations WHERE id='$val_id' AND email='$val_oldemail' ";
				$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

				$beforeArray = array( 'arrivalDate' => $checkQuerry[0]->arrivalDate, 'nights' => $checkQuerry[0]->nights, 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'offer' => $checkQuerry[0]->special, 'message' => $checkQuerry[0]->notes, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country, 'customp' => $checkQuerry[0]->customp );
				$afterArray = array( 'arrivalDate' => $val_fromdate_sql, 'nights' => $val_nights, 'email' => $val_email, 'name' => $val_name, 'persons' => $val_persons, 'childs' => $val_childs, 'room' =>  $val_room, 'offer' => $val_offer, 'message' => $val_message, 'custom' => $val_custom, 'country' => $val_country, 'customp' => $val_customp );

				$changelog = easyreservations_generate_res_changelog($beforeArray, $afterArray);
				
				if($checkQuerry[0]->nights != $val_nights OR $checkQuerry[0]->arrivalDate != $val_fromdate_sql OR $checkQuerry[0]->number != $val_persons OR $checkQuerry[0]->room != $val_room OR $checkQuerry[0]->special != $val_offer){
					if($checkQuerry[0]->price)
					$explodePrice = explode(";", $checkQuerry[0]->price);
					$newPrice = " price='".$explodePrice[1]."',";
				} else $newPrice = '';

				if(!empty($val_custom))		$customfields =		easyreservations_edit_custom($val_custom,	$val_id, 0, 1, false, 0, 'cstm', 'edit');
				if(!empty($val_customp)) 	$custompfields =	easyreservations_edit_custom($val_customp,	$val_id, 0, 1, false, 1, 'cstm', 'edit');

				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$val_fromdate_sql', nights='$val_nights', name='$val_name', email='$val_email', notes='$val_message', room='$val_room', number='$val_persons', childs='$val_childs', special='$val_offer', dat='$val_fromdat', custom='$customfields', customp='$custompfields', country='$val_country', ".$newPrice." approve='' WHERE id='$val_id' ")) or trigger_error('mySQL-Fehler: '.mysql_error(), E_USER_ERROR);
				$thePrice = easyreservations_get_price($val_id);
				
				if($val_offer != 0) $specialoffer = get_the_title($val_offer);
				else $specialoffer =  __( 'None' , 'easyReservations' );

				$roomtitle = get_the_title($val_room);

				$emailformation=get_option('reservations_email_to_admin_edited');
				$emailformation2=get_option('reservations_email_to_user_edited');
				
				if($checkQuerry[0]->email == $val_email){
					if($emailformation['active'] == 1)		easyreservations_send_mail($emailformation['msg'],		$reservation_support_mail,	$emailformation['subj'],		'', $val_id, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
					if($emailformation2['active'] == 1)	easyreservations_send_mail($emailformation2['msg'],	$val_email,								$emailformation2['subj'],	'', $val_id, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
				} else {
					if($emailformation['active'] == 1) 		easyreservations_send_mail($emailformation['msg'],		$reservation_support_mail,	$emailformation['subj'],		'', $val_id, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
					if($emailformation2['active'] == 1) 	easyreservations_send_mail($emailformation2['msg'],	$val_email,								$emailformation2['subj'],	'', $val_id, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
					if($emailformation2['active'] == 1) 	easyreservations_send_mail($emailformation2['msg'],	$checkQuerry[0]->email,		$emailformation2['subj'],	'', $val_id, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
				}
			}
		}
		
		return $error;
	}
?>