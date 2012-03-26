<?php
function reservations_edit_shortcode($atts) {
	
$return = '<link href="'.WP_PLUGIN_URL.'/easyreservations/css/forms/form_none.css" rel="stylesheet" type="text/css"/>';
	$return .= '<script>function easyreservations_build_datepicker(){ jQuery("#easy-form-from, #easy-form-to").datepicker( { dateFormat: \'dd.mm.yy\' }); } </script>';

	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle');

	if(strpos(get_the_content(), '[easy_calendar') !== false){
		$isCalendar = true;
	} else $isCalendar = false;

	if(isset($_POST['email']) AND isset($_POST['editID'])){
		if (!wp_verify_nonce($_POST['easy-user-edit-login'], 'easy-user-edit-login' ) AND !wp_verify_nonce($_POST['easy-user-edit'], 'easy-user-edit' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		$theMail = $_POST['email'];
		$theID = $_POST['editID'];
	} elseif(isset($_GET['email']) AND isset($_GET['id'])) {
		$theMail = $_GET['email'];
		$theID = $_GET['id'];
		if (!wp_verify_nonce($_GET['nonce'], 'easy-user-edit-link' )) die(__('Link is only 24h valid', 'easyReservations').' <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
	}

	if(isset($theMail) AND isset($theID)){
		wp_enqueue_script( 'easyreservations_send_validate' );
		if(isset($atts['price'])){
			wp_enqueue_script( 'easyreservations_send_price' );
			add_action('wp_print_footer_scripts', 'easyreservtions_send_price_script'); //get price directily after loading
		}
		add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');

		global $wpdb;

		if(isset($_POST['thename'])){
			$error = "";
			if (!wp_verify_nonce($_POST['easy-user-edit'], 'easy-user-edit' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );

			if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
			else $captcha ="";

			if(isset($_POST['thename'])) $name_form=$_POST['thename'];
			else $name_form = "";

			if(isset($_POST['from'])) $from=$_POST['from'];
			else $from = "";

			if(isset($_POST['old_email'])) $old_email=$_POST['old_email'];
			else $old_email = "";
			
			if(isset($_POST['to'])) $to=$_POST['to'];
			else $to = "";

			if(isset($_POST['nights'])) $nights=$_POST['nights'];
			else $nights = "";

			if(isset($_POST['persons'])) $persons=$_POST['persons'];
			else $persons = "";

			if(isset($_POST['email'])) $email=$_POST['email'];
			else $email = "";

			if(isset($_POST['childs'])) $childs=$_POST['childs'];
			else $childs = "";

			if(isset($_POST['country'])) $country=$_POST['country'];
			else $country = "";

			if(isset($_POST['room'])) $room=$_POST['room'];
			else $room = "";

			if(isset($_POST['editMessage'])) $message=$_POST['editMessage'];
			else $message = "";

			if(isset($_POST['offer'])) $offer=$_POST['offer'];
			else $offer = "";

			$customfields="";
			$custompfields="";

			for($theCount = 0; $theCount < 50; $theCount++){
				if(isset($_POST["custom_value_".$theCount]) AND isset($_POST["custom_title_".$theCount])){
					$customfields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$theCount], 'title' => $_POST["custom_title_".$theCount], 'value' => $_POST["custom_value_".$theCount]);
				}
			}

			for($theCount = 0; $theCount < 50; $theCount++){
				if(isset($_POST["customPvalue".$theCount]) AND isset($_POST["customPtitle".$theCount])){
					if(easyreservations_check_price($_POST["customPprice".$theCount]) == 'error') $moneyerrors++;
					$custompfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$theCount], 'title' => $_POST["customPtitle".$theCount], 'value' => $_POST["customPvalue".$theCount], 'amount' => $_POST["customPprice".$theCount]);
				}
			}

			$error .= easyreservations_check_reservation( array( 'thename' => $name_form, 'from' => $from, 'to' => $to, 'nights' => $nights, 'email' => $email, 'persons' => $persons, 'childs' => $childs, 'country' => $country, 'room' => $room, 'message' => $message, 'offer' => $offer, 'custom' => $customfields, 'customp' => $custompfields, 'id' => $theID, 'old_email' => $old_email), 'user-edit');
		}

		if(isset($_POST['thename']) AND empty($error)){ //When Check gives no error Insert into Database and send mail

			$return .= '<div class="easy_form_success">'.__( 'Your Reservation was edited' , 'easyReservations' ).'</div>';

		}
		
		do_action( 'er_edit_add_action' );

		$SQLeditq = "SELECT email, name, arrivalDate, nights, number, childs, room, special, approve, country, notes, custom, customp FROM ".$wpdb->prefix ."reservations WHERE id='$theID' AND email='$theMail' ";
		$editQuerry = $wpdb->get_results($SQLeditq ) or exit(__( 'Wrong ID or eMail' , 'easyReservations' ).' <a href="#" onClick="history.go(-1)">Back</a>');

		if(isset($_POST['captcha_value'])){

			require_once(dirname(__FILE__).'/lib/captcha/captcha.php');
			$prefix = $_POST['captcha_prefix'];
			$the_answer_from_respondent = $_POST['captcha_value'];
			$captcha_instance = new ReallySimpleCaptcha();
			$correct = $captcha_instance->check($prefix, $the_answer_from_respondent);
			$chaptchaFileAdded = 1;
			$captcha_instance->remove($prefix);
			$captcha_instance->cleanup(); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?

			if($correct != 1){
			//	exit(__(  'Please enter the correct captcha code' , 'easyReservations' ).' <a href="#" onClick="history.go(-1)">Back</a>');
			}
		}

		if(isset($atts['daysbefore'])) $daysbeforearival = $atts['daysbefore'];
		else $daysbeforearival = 10;

		if(isset($editQuerry[0])){
		$special = $editQuerry[0]->special;
		$country = $editQuerry[0]->country;
		$specials=get_the_title($special);
		$persons=$editQuerry[0]->number;
		$childs=$editQuerry[0]->childs;
		$approve=$editQuerry[0]->approve;

		if($special==0) $specials="None";

		if(strtotime($editQuerry[0]->arrivalDate) < time()){
			$resPast = 1;
			$pastError = __( 'Your arrival date is past' , 'easyReservations' ).'<br>';
		} elseif(strtotime($editQuerry[0]->arrivalDate) < time()+(86400*$daysbeforearival)){
			$resPast = 1;
			$pastError = __( 'Please contact us to edit your reservation' , 'easyReservations' ).'<br>';
		} else {
			$resPast = 0;
			$pastError = '';
		}

		$left = reservations_check_pay_status($theID);
		if(function_exists('easyreservations_generate_paypal_button') && $left > 0){
			$paypal = easyreservations_generate_paypal_button($theID, strtotime($editQuerry[0]->arrivalDate), $editQuerry[0]->nights, $editQuerry[0]->room, $special, $editQuerry[0]->email, 0);
		} else $paypal = '';

		$return .= '<div style="margin-left:auto;text-align:center;margin-right:auto;margin: 0px 5px;padding:5px 5px;">'.get_option("reservations_edit_text").'</div>';
		
		if(isset($atts['price']) || isset($atts['status']) || !empty($paypal)){
			$return .= '<div class="easy-edit-status"><b>';
				if(isset($atts['status'])) $return .= __( 'Status' , 'easyReservations' ).': '.reservations_status_output($approve).'';
				if(isset($atts['price']) && isset($atts['status'])) $return .= ' | ';
				if(isset($atts['price'])) $return .= __( 'Price' , 'easyReservations' ).': '.easyreservations_get_price($theID,1).' | '.__( 'Left' , 'easyReservations' ).': '.reservations_format_money($left, 1);
			$return .= '</b>'.$paypal.'</div>';
		}

			$return .= '<form method="post" id="easyFrontendFormular" name="easyFrontendFormular" style="width:99%;margin-left:auto;margin-right:auto;">';
			if(function_exists('easyreservations_generate_chat')){
				$return .= easyreservations_generate_chat( $theID, 'edit' );
				 $return .= '<div style="width:400px;">';
			} else $return .= '<div style="width:400px;margin-left:auto;margin-right:auto">';
				if(isset($error)) $pastError = $pastError.$error;
				$return .= '<div id="showError" class="showError" style="margin-left:auto;margin-right:auto;padding-left:100px">'.$pastError.'</div>';

				$return .= '<input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">';
				$return .= '<input name="editID" id="editID" type="hidden" value="'.$theID.'">';
				$return .= '<input name="old_email" type="hidden" value="'.$editQuerry[0]->email.'">';
				$return .= '<input name="easy-user-edit" type="hidden" value="'.wp_create_nonce('easy-user-edit').'">';
				$return .= '<label>'.__( 'Name' , 'easyReservations' ).'<span class="small">'.__( 'Your name' , 'easyReservations' ).'</span></label><input type="text" name="thename" id="easy-form-thename" onchange="easyreservations_send_validate();" value="'.$editQuerry[0]->name.'">';
				$return .= '<label>'.__( 'eMail' , 'easyReservations' ).'<span class="small">'.__( 'Your email' , 'easyReservations' ).'</span></label><input type="text" name="email" id="easy-form-email" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();" value="'.$editQuerry[0]->email.'">';
				$return .= '<label>'.__( 'From' , 'easyReservations' ).'<span class="small">'.__( 'The arrival date' , 'easyReservations' ).'</span></label><input type="text" name="from" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();" id="easy-form-from" value="'.date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)).'">';
				$return .= '<label>'.__( 'To' , 'easyReservations' ).'<span class="small">'.__( 'The departure date' , 'easyReservations' ).'</span></label><input type="text" name="to" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();" id="easy-form-to" value="'.date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)+(86400*$editQuerry[0]->nights)).'">';
				$return .= '<label>'.__( 'Persons' , 'easyReservations' ).'<span class="small">'.__( 'The amount of persons' , 'easyReservations' ).'</span></label><select name="persons" id="easy-form-persons" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();">'.easyReservations_num_options(1,50,$persons).'</select>';
				if(isset($childs) AND $childs != "") $return .= '<label>'.__( 'Children\'s' , 'easyReservations' ).'<span class="small">'.__( 'The amount of children\'s' , 'easyReservations' ).'</span></label><select name="childs" onchange="easyreservations_send_price(\'front\');">'.easyReservations_num_options(0,50,$childs).'</select>';
				$return .= '<label>'.__( 'Country' , 'easyReservations' ).'<span class="small">'.__( 'The departure date' , 'easyReservations' ).'</span></label><select name="country">'.easyReservations_country_select($country).'</select>';
				if($isCalendar) $calendar_js = 'document.CalendarFormular.room.value=this.value;easyreservations_send_calendar(\'shortcode\');';
				$return .= '<label>'.__( 'Room' , 'easyReservations' ).'<span class="small">'.__( 'Choose the room' , 'easyReservations' ).'</span></label><select  name="room" id="room" onChange="'.$calendar_js.'easyreservations_send_price(\'front\');easyreservations_send_validate();">'.reservations_get_room_options($editQuerry[0]->room).'</select>';
				$return .= '<label>'.__( 'Offer' , 'easyReservations' ).'<span class="small">'.__( 'Choose the offer' , 'easyReservations' ).'</span></label><select  name="offer" id="offer" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();">'.__($specials).'<option  value="0">'.__( 'None' , 'easyReservations' ).'</option>'.reservations_get_offer_options($special).'</select>';
			
				if(!empty($editQuerry[0]->custom)){
					$customs=easyreservations_get_customs($editQuerry[0]->custom, 0, 'cstm', 'edit');
					if(!empty($customs)){
						foreach($customs as $key => $custom){
							if($custom['mode'] == 'visible' || $custom['mode'] == 'edit'){ 
								$return .= '<label>'.__($custom['title']).'<span class="small">'.__( "Type in information" , "easyReservations" ).'</span></label>';
								$return .= '<input type="hidden" name="custom_title_'.$key.'" value="'.$custom['title'].'">';
								if($custom['mode'] == 'edit') $return .= '<input type="text" name="custom_value_'.$key.'" value="'.$custom['value'].'"><input type="hidden" value="edit" name="custommodus'.$key.'">';
								else $return .= '<span style="display:inline-block;min-width:150px;min-height:40px;margin-left:10px">'.$custom['value'].'<input type="hidden" name="custommodus'.$key.'" value="visible"><input type="hidden" name="custom_value_'.$key.'" value="'.$custom['value'].'"></span>';
							}
						}
					}
				}
				if(!empty($editQuerry[0]->customp)){
					$customps=easyreservations_get_customs($editQuerry[0]->customp, 0, 'cstm', 'edit');
					if(!empty($customps)){
						foreach($customps as $thenumber2 => $customp){
							if($customp['mode'] == 'visible' || $customp['mode'] == 'edit'){ 
								$return .= '<label>'.__($customp['title']).'<span class="small">'.__( "Pay service" , "easyReservations" ).'</span></label><span class="formblock"><b>'.$customp['value'].':</b> '.$customp['amount'].' &'.get_option("reservations_currency").';';
								if($customp['mode'] == 'edit') $return .= '<input type="checkbox"  id="custom_price'.$thenumber2.'" value="test:'.$customp['amount'].'" onchange="easyreservations_send_price(\'front\');" checked ><input name="customPmodus'.$thenumber2.'" type="hidden" value="edit">'; 
								else $return .= '<input type="hidden" name="customPmodus'.$thenumber2.'" value="visible"><input type="hidden" id="custom_price'.$thenumber2.'" value="test:'.$customp['amount'].'">';
								$return .= '<input type="hidden" name="customPtitle'.$thenumber2.'" value="'.$customp['title'].'"><input type="hidden" name="customPvalue'.$thenumber2.'" value="'.$customp['value'].'"><input type="hidden" name="customPprice'.$thenumber2.'" value="'.$customp['amount'].'"></span>';
							}
						}
					}
				}
				$return .= '<label>'.__( 'Message' , 'easyReservations' ).'<span class="small">'.__( 'Type in message' , 'easyReservations' ).'</span></label><textarea name="editMessage" style="width:170px;">'.$editQuerry[0]->notes.'</textarea>';
				$return .= '<div style="text-align:center">';
					if($resPast == 0) $return .= '<input type="submit" onclick="document.getElementById(\'easyFrontendFormular\').submit(); return false;" value="'.__( 'Submit' , 'easyReservations' ).'">';
					if(isset($atts['price'])) $return .='<span class="showPrice" style="margin-left:10px">'.__( 'Price' , 'easyReservations' ).': <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &'.get_option("reservations_currency").';</span>';
				$return .= '</div>';
			$return .= '</div>';
			$return .= '</form>';

			return $return;
		} else return __( 'Wrong ID and/or eMail' , 'easyReservations' ).' <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>';
	} else {
		if(!isset($chaptchaFileAdded)) require_once(dirname(__FILE__).'/lib/captcha/captcha.php');
		$captcha_instance = new ReallySimpleCaptcha();
		$word = $captcha_instance->generate_random_word();
		$prefix = mt_rand();
		$url = $captcha_instance->generate_image($prefix, $word);

		$return .= '<form method="post" id="easyFrontendFormular" style="padding-left:106px">';
			$return .= '<input type="hidden" value="'.$prefix.'" name="captcha_prefix">';
			$return .= '<input name="easy-user-edit-login" type="hidden" value="'.wp_create_nonce('easy-user-edit-login').'">';
			$return .= '<label>'.__( 'ID' , 'easyReservations' ).'<span class="small">'.__( 'ID of your reservation' , 'easyReservations' ).'</span></label><input name="editID" type="text"><br>';
			$return .= '<label>'.__( 'eMail' , 'easyReservations' ).'<span class="small">'.__( 'Your email' , 'easyReservations' ).'</span></label><input name="email" type="text"><br>';
			$return .= '<label>'.__( 'Captcha' , 'easyReservations' ).'<span class="small">'.__( 'Type in code' , 'easyReservations' ).'</span></label><input type="text" name="captcha_value" style="width:40px;"><img style="vertical-align:middle;" src="'.RESERVATIONS_LIB_DIR.'/captcha/tmp/'.$url.'">';
			$return .= '<input type="submit" onclick="document.getElementById(\'easyFrontendFormular\').submit(); return false;" class="button-primary" value="'.__( 'Submit' , 'easyReservations' ).'">';
		$return .= '</form>';
		return $return;
    }
}
?>