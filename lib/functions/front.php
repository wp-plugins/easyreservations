<?php
/**
* 	@functions for frontend only
*/

	/**
	*	Load scripts and styles
	*
	*/

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

	/**
	*	Returns url of current page
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
		$error = "";
		
		if(isset($res['id'])) $val_id = $res['id'];
		
		if(isset($res['captcha']) && !empty($res['captcha'])){
		
			$captcha = $res['captcha'];

			require_once(dirname(__FILE__).'/lib/captcha/captcha.php');
			$prefix = $captcha['captcha_prefix'];
			$the_answer_from_respondent = $captcha['captcha_value'];
			$captcha_instance = new ReallySimpleCaptcha();
			$correct = $captcha_instance->check($prefix, $the_answer_from_respondent);
			$chaptchaFileAdded = 1;
			$captcha_instance->remove($prefix);
			$captcha_instance->cleanup(); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?

			if($correct != 1)	$error.=  __( 'Please enter the correct captcha' , 'easyReservations' ).'</b><br>';
		}

		if((strlen($val_name) > 30 OR strlen($val_name) <= 3) AND $val_name != ""){ /* check name */
			$error.=  __( 'Please enter a correct name' , 'easyReservations' ).'<br>';
		}

		if( $where == "user-add" ||  $where == "user-edit"){
			if($val_from < $time){ /* check arrival Date */
				$error.=  __( 'The arrival date has to be in future' , 'easyReservations' ).'<br>';
			}

			if($val_to < time()){ /* check departure Date */
				$error.=  __( 'The depature date has to be in future' , 'easyReservations' ).'<br>';
			}
		}

		if($val_to <= $val_from){ /* check difference between arrival and departure date */
			$error.=  __( 'The depature date has to be after the arrival date' , 'easyReservations' ).'<br>';
		}

		$pattern_mail = "/^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+\.[a-zA-Z]{2,4}$/";
		if(!preg_match($pattern_mail, $val_email) AND $val_email != ""){ /* check email */
			$error.=  __( 'Please enter a correct eMail' , 'easyReservations' ).'<br>'; 
		}

		if (!is_numeric($val_persons)){ /* check persons */
			$error.=  __( 'Persons has to be a number' , 'easyReservations' ).'<br>';
		}
		
		if($val_offer > 0){  /* check offers & rooms availability */

			$numbererrors=reservations_check_availibility($val_offer, $val_fromdate, $val_nights, $val_room); /* check offers availability */
			if($numbererrors > 0){
				$error.= '('.$numbererrors.'x) '.__( 'The offer isn\'t available at' , 'easyReservations' ).' '.$val_fromdate.'<br>';
			}

			$numbererrors=reservations_check_availibility($val_room, $val_fromdate, $val_nights, $val_room);  /* check rooms availability */
			if($numbererrors > 0){
				$error.= '('.$numbererrors.'x) '.__( 'The room isn\'t available at' , 'easyReservations' ).' '.$val_fromdate.'<br>';
			}

		} else { /* check rooms availability */

			$numbererrors=reservations_check_availibility($val_room, $val_fromdate, $val_nights, $val_room); /* check rooms availability */
			if($numbererrors > 0){
				$error.= '('.$numbererrors.'x) '.__( 'The room isn\'t available at' , 'easyReservations' ).' '.$val_fromdate.'<br>';
			}

		}

		if($error == ""){
			global $wpdb;
			$reservation_support_mail = get_option("reservations_support_mail");

			if($where == "user-add"){

				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(name,  email, notes, nights, arrivalDate, dat, room, number, childs, country, special, custom, customp, reservated ) 
				VALUES ('$val_name', '$val_email', '$val_message', '$val_nights', '$val_fromdate_sql', '$val_fromdat', '$val_room', '$val_persons', '$val_childs', '$val_country', '$val_offer', '$val_custom', '$val_customp', NOW() )" ) );

				$newID = mysql_insert_id();
				$priceFunction = easyreservations_price_calculation($newID,'');
				$getThePrice = $priceFunction['price'];
				$thePrice = reservations_format_money($getThePrice);

				if($val_offer != "0"){
					$specialoffer =get_the_title($val_offer); 
				}
				if($val_offer == "0") $specialoffer =  __( 'None' , 'easyReservations' );

				$roomtitle = __(get_the_title($val_room));

				$emailformation=get_option('reservations_email_to_admin_msg');
				$subj=get_option("reservations_email_to_admin_subj");
				$emailformation2=get_option('reservations_email_to_user_msg');
				$subj2=get_option("reservations_email_to_user_subj");

				easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, '');
				easyreservations_send_mail($emailformation2, $reservation_support_mail, $subj2, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, '');
			}elseif($where == "user-edit"){
			
				$checkSQLedit = "SELECT email, name, arrivalDate, nights, number, childs, country, room, special, approve, notes, custom, customp, price FROM ".$wpdb->prefix ."reservations WHERE id='$val_id' AND email='$val_oldemail' ";
				$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

				$beforeArray = array( 'arrivalDate' => $checkQuerry[0]->arrivalDate, 'nights' => $checkQuerry[0]->nights, 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'offer' => $checkQuerry[0]->special, 'message' => $checkQuerry[0]->notes, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country, 'customp' => $checkQuerry[0]->customp );
				$afterArray = array( 'arrivalDate' => $val_fromdate_sql, 'nights' => $val_nights, 'email' => $val_email, 'name' => $val_name, 'persons' => $val_persons, 'childs' => $val_childs, 'room' =>  $val_room, 'offer' => $val_offer, 'message' => $val_message, 'custom' => $val_custom, 'country' => $val_country, 'customp' => $val_customp );

				$changelog = easyreservations_generate_res_Changelog($beforeArray, $afterArray);
				
				if($checkQuerry[0]->nights != $val_nights OR $checkQuerry[0]->arrivalDate != $val_fromdate_sql OR $checkQuerry[0]->number != $val_persons OR $checkQuerry[0]->room != $val_room OR $checkQuerry[0]->special != $val_offer){
					$explodePrice = explode(";", $checkQuerry[0]->price);
					$newPrice = " price='".$explodePrice[1]."',";
				} else $newPrice = '';

				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$val_fromdate_sql', nights='$val_nights', name='$val_name', email='$val_email', notes='$val_message', room='$val_room', number='$val_persons', childs='$val_childs', special='$val_offer', dat='$month_form', country='$val_country', custom='$val_custom', customp='$val_customp',".$newPrice." approve='' WHERE id='$val_id' ")) or trigger_error('mySQL-Fehler: '.mysql_error(), E_USER_ERROR);
				$thePrice = easyreservations_get_price($val_id);
				
				if($val_offer != 0) $specialoffer = get_the_title($val_offer);
				else $specialoffer =  __( 'None' , 'easyReservations' );

				$roomtitle = get_the_title($val_room);

				$emailformation=get_option('reservations_email_to_admin_edited_msg');
				$subj=get_option("reservations_email_to_admin_edited_subj");
				$emailformation2=get_option('reservations_email_to_user_edited_msg');
				$subj2=get_option("reservations_email_to_user_edited_subj");
				
				if($checkQuerry[0]->email == $val_email){
					easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
					easyreservations_send_mail($emailformation2, $val_email, $subj2, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
				} else {
					easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
					easyreservations_send_mail($emailformation2, $val_email, $subj2, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
					easyreservations_send_mail($emailformation2, $checkQuerry[0]->email, $subj2, '', $newID, $val_from, $val_to, $val_name, $val_email, $val_nights, $val_persons, $val_childs, $val_country, $roomtitle, $specialoffer, $val_custom, $thePrice, $val_message, $changelog);
				}
			}
		}
		
		return $error;
	}	

?>