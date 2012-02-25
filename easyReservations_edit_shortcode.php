<?php
function reservations_edit_shortcode($atts) {
?><link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/forms/form_none.css" rel="stylesheet" type="text/css"/><input type="hidden" id="urlPrice" value="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_price.js"><input type="hidden" id="urlValidate" value="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_validate.js">
<script>
	jQuery(document).ready(function() {
		jQuery("#datepicker").datepicker( { dateFormat: 'dd.mm.yy', style: 'font-size:1em' });
		jQuery("#datepicker2").datepicker( { dateFormat: 'dd.mm.yy' });
	});
</script>
<?php
	if(strpos(get_the_content($post->ID), '[easy_calendar') !== false){
		$isCalendar = true;
	} else $isCalendar = false;

	if(isset($_POST['email']) AND isset($_POST['editID'])){
		$theMail = $_POST['email'];
		$theID = $_POST['editID'];
	} else {
		$explURI=explode("?", curPageURL());
		if(isset($explURI[2]) AND isset($explURI[3])){
			$urlIDexpl=explode("=", $explURI[2]);
			$urlMAILexpl=explode("=", $explURI[3]);
			$theMail = $urlMAILexpl[1];
			$theID = $urlIDexpl[1];
		}
	}

	if(isset($theMail) AND isset($theID)){
		global $wpdb;
		$error = "";

		if(isset($_POST['thename'])){

			if ($_POST['thename'] != '' AND strlen($_POST['thename']) < 20 AND strlen($_POST['thename']) > 3){
			$name_form=$_POST['thename'];}
			else { $error.=  '<b>'.__( 'Please enter correct and future Name' , 'easyReservations' ).'</b><br>'; }

		$stampfrom = strtotime($_POST['from']);
		$arrivaldate_form2 = "";

		if(is_numeric($stampfrom) AND $stampfrom > time()-86400) {
			$arrivaldate_form=date("Y-m-d", $stampfrom);
			$arrivaldate_form2=date("d.m.Y", $stampfrom);
			$month_form=date("Y-m",$stampfrom);}
		else $error.=  __( 'Please enter correct arrival date' , 'easyReservations' ).'</b><br>';

		$stampto = strtotime($_POST['to']);
		if(!empty($_POST['to']) AND is_numeric($stampto) AND $stampto > time()-86400)  {
			$dayofdeparture=$stampfrom;
			$daysbetween=$stampto-$stampfrom;
			$nights_form=$daysbetween/86400;
		} else $error.=  __( 'Please enter correct and future destination date' , 'easyReservations' ).'</b><br>';

		$pattern_mail = "/^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+\.[a-zA-Z]{2,4}$/";
		if ($_POST['email'] != '' AND strlen($_POST['email']) < 40 AND strlen($_POST['email']) > 5 AND preg_match($pattern_mail, $_POST['email'])) $email_form=$_POST['email'];
		else $error.=  '<b>'.__( 'Please enter correct eMail' , 'easyReservations' ).'</b><br>';

		if ($_POST['persons'] != '' AND strlen($_POST['persons']) < 4 AND is_numeric($_POST['persons'])){
		$persons_form=$_POST['persons']; }
		else { $error.=  '<b>'.__( 'Please enter correct amount of Persons' , 'easyReservations' ).'</b><br>'; }

		if(isset($_POST['country'])){
			if(!empty($_POST['country']) AND strlen($_POST['country']) < 4){
				$country_form = $_POST['country'];
			} else $error.=  __( 'Please select country' , 'easyReservations' ).'</b><br>';
		} else $country_form = '';

		if(isset($_POST['childs'])){
			if ($_POST['childs'] != '' AND strlen($_POST['childs']) < 4 AND is_numeric($_POST['childs'])){
				$childs_form=$_POST['childs'];
			} else {
				$error.=  '<b>'.__( 'Please enter correct amount of childs' , 'easyReservations' ).'</b><br>';
			}
		} else $childs_form = '';

		$room_form=$_POST['room'];

		if(isset($_POST['editMessage'])) $message_form=$_POST['editMessage'];

		if($_POST['offer'] != '0'){
		$specialoffer_form=$_POST['offer'];}
		else $specialoffer_form=0;

		if(!isset($specialoffer_form)) $specialoffer_form=0;

		if($specialoffer_form > 0){
			$numbererrors=reservations_check_availibility($specialoffer_form, $arrivaldate_form2, $nights_form, $room_form);
			if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Special Offer isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
			$numbererrors=reservations_check_availibility($room_form, $arrivaldate_form2, $nights_form, $room_form);
			if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Room isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
		} else {
			$numbererrors=reservations_check_availibility($room_form, $arrivaldate_form2, $nights_form, $room_form);
			if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Room isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
		}
		
		$customfields = '';

		for($i=1; $i < 15; $i++){
			if(isset($_POST["custom_value_".$i.""])){
				$customfields .= $_POST["custom_title_".$i.""].'&:&'.$_POST["custom_value_".$i.""].'&;&';
			}
		}

		$custompfields = '';

		for($i=1; $i < 15; $i++){
			if(isset($_POST["custom_price".$i.""])){
				$custompfields .= htmlspecialchars_decode($_POST["customp_value_".$i.""]).'&;&';
			}
		}
	}

	if(isset($_POST['thename']) AND !$error) { //When Check gives no error Insert into Database and send mail
		$checkSQLedit = "SELECT email, name, arrivalDate, nights, number, childs, country, room, special, approve, notes, custom, customp, price FROM ".$wpdb->prefix ."reservations WHERE id='$theID' AND email='$theMail' ";
		$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

		$beforeArray = array( 'arrivalDate' => $checkQuerry[0]->arrivalDate, 'nights' => $checkQuerry[0]->nights, 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'offer' => $checkQuerry[0]->special, 'message' => $checkQuerry[0]->notes, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country, 'customp' => $checkQuerry[0]->customp );
		$afterArray = array( 'arrivalDate' => $arrivaldate_form, 'nights' => $nights_form, 'email' => $email_form, 'name' => $name_form, 'persons' => $persons_form, 'childs' => $childs_form, 'room' =>  $room_form, 'offer' => $specialoffer_form, 'message' => $message_form, 'custom' => $customfields, 'country' => $country_form, 'customp' => $custompfields );

		$changelog = easyreservations_generate_res_Changelog($beforeArray, $afterArray);
		
		if($checkQuerry[0]->nights != $nights_form OR $checkQuerry[0]->arrivalDate != $arrivaldate_form OR $checkQuerry[0]->number != $persons_form OR $checkQuerry[0]->room != $room_form OR $checkQuerry[0]->special != $specialoffer_form){
			$explodePrice = explode(";", $checkQuerry[0]->price);
			$newPrice = " price='".$explodePrice[1]."',";
		} else $newPrice = '';

		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$arrivaldate_form', nights='$nights_form', name='$name_form', email='$email_form', notes='$message_form', room='$room_form', number='$persons_form', childs='$childs_form', special='$specialoffer_form', dat='$month_form', country='$country_form', custom='$customfields', customp='$custompfields',".$newPrice." approve='' WHERE id='$theID' ")) or trigger_error('mySQL-Fehler: '.mysql_error(), E_USER_ERROR);

		echo '<b>'.__( 'Your Reservation was edited' , 'easyReservations' ).'</b><br><br>';

		$thePrice = easyreservations_get_price($theID);
		
		if($specialoffer_form != 0){
			$post_id_7 = get_post($specialoffer_form);
			$specialoffer = __($post_id_7->post_title);
		}
		if($specialoffer_form == 0) $specialoffer =  __( 'None' , 'easyReservations' );

		$post_id8 = get_post($room_form);
		$roomtitle = __($post_id8->post_title);

		$emailformation=get_option('reservations_email_to_admin_edited_msg');
		$subj=get_option("reservations_email_to_admin_edited_subj");
		$emailformation2=get_option('reservations_email_to_user_edited_msg');
		$subj2=get_option("reservations_email_to_user_edited_subj");
		$reservation_support_mail = get_option("reservations_support_mail");
		
		if($checkQuerry[0]->email == $email_form){
			easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
			easyreservations_send_mail($emailformation2, $email_form, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer,  $custom_form, $thePrice, $message_form, $changelog);
		} else {
			easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
			easyreservations_send_mail($emailformation2, $email_form, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
			easyreservations_send_mail($emailformation2, $checkQuerry[0]->email, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
		}
	}

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
		exit(__(  'Please enter the correct captcha' , 'easyReservations' ).' <a href="#" onClick="history.go(-1)">Back</a>');
		}
	}

	if(isset($atts['daysbefore'])) $daysbeforearival = $atts['daysbefore'];
	else $daysbeforearival = 10;

	if(isset($editQuerry[0])){
	$offer_cat = get_option("reservations_special_offer_cat");
	$room_category =get_option("reservations_room_category");

	$special = $editQuerry[0]->special;
	$country = $editQuerry[0]->country;
	$specialgetpost=get_post($special);
	$specials=$specialgetpost->post_title;
	$persons=$editQuerry[0]->number;
	$childs=$editQuerry[0]->childs;
	$approve=$editQuerry[0]->approve;

	if($special==0) $specials="None";

	if(strtotime($editQuerry[0]->arrivalDate) < time()){
		$resPast = 1;
		$pastError = __( 'Your arrival date is past' , 'easyReservations' ).'<br>';
	} elseif(strtotime($editQuerry[0]->arrivalDate) > time()-(86400*$daysbeforearival)){
		$resPast = 1;
		$pastError = __( 'Please contact us to edit your reservation' , 'easyReservations' ).'<br>';
	} else {
		$resPast = 0;
		$pastError = '';
	}

	echo '<div style="margin-left:auto;margin-right:auto;margin: 0px 5px;padding: 0px 5px;">'.get_option("reservations_edit_text").'</div>';
	
	echo '<div style="background:#f9f9f9; padding:5px;margin:5px;text-align:center;"><b style="font-weight:bold;">'.__( 'Status' , 'easyReservations' ).': '.reservations_status_output($approve).' | '.__( 'Price' , 'easyReservations' ).': '.easyreservations_get_price($theID).' | '.__( 'Left' , 'easyReservations' ).': '.reservations_format_money(reservations_check_pay_status($theID)).' &'.get_option('reservations_currency').';</b></div>'; ?>
		<div id="showError" class="showError" style="margin-left:auto;margin-right:auto;"><?php echo $pastError.$error; ?></div>
		<form method="post" id="easyFrontendFormular" name="easyFrontendFormular" style="width:400px;margin-left:auto;margin-right:auto;">
			<input name="editID" type="hidden" value="<?php echo $_POST['editID'];?>">
			<label><?php echo __( 'Name' , 'easyReservations' );?><span class="small"><?php echo __( 'Your name' , 'easyReservations' );?></span></label><input type="text" name="thename" onchange="easyRes_sendReq_Validate();" value="<?php echo $editQuerry[0]->name; ?>">
			<label><?php echo __( 'eMail' , 'easyReservations' );?><span class="small"><?php echo __( 'Your email' , 'easyReservations' );?></span></label><input type="text" name="email" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();" value="<?php echo $editQuerry[0]->email; ?>">
			<label><?php echo __( 'From' , 'easyReservations' );?><span class="small"><?php echo __( 'The arrival date' , 'easyReservations' );?></span></label><input type="text" name="from" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();" id="datepicker" value="<?php echo date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)); ?>">
			<label><?php echo __( 'To' , 'easyReservations' );?><span class="small"><?php echo __( 'The departure date' , 'easyReservations' );?></span></label><input type="text" name="to" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();" id="datepicker2" value="<?php  echo date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)+(86400*$editQuerry[0]->nights));?>">
			<label><?php echo __( 'Persons' , 'easyReservations' );?><span class="small"><?php echo __( 'The amount of persons' , 'easyReservations' );?></span></label><select name="persons" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();"><?php echo easyReservations_num_options(1,50,$persons); ?></select>
			<?php if(isset($childs) AND $childs != ""){ ?><label><?php echo __( 'Childs' , 'easyReservations' );?><span class="small"><?php echo __( 'The amount of childs' , 'easyReservations' );?></span></label><select name="childs" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(0,50,$childs); ?></select><?php } ?>
			<label><?php echo __( 'Country' , 'easyReservations' );?><span class="small"><?php echo __( 'The departure date' , 'easyReservations' );?></span></label><select name="country"><?php echo easyReservations_country_select($country); ?></select>
			<label><?php echo __( 'Room' , 'easyReservations' );?><span class="small"><?php echo __( 'Choose the room' , 'easyReservations' );?></span></label><select  name="room" id="room" onChange="<?php if($isCalendar){ ?>document.CalendarFormular.room.value=this.value;easyRes_sendReq_Calendar();<?php } ?>easyRes_sendReq_Price();easyRes_sendReq_Validate();"><?php echo reservations_get_room_options($editQuerry[0]->room); ?></select>
			<label><?php echo __( 'Offer' , 'easyReservations' );?><span class="small"><?php echo __( 'Choose the offer' , 'easyReservations' );?></span></label><select  name="offer" id="offer" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();"><?php echo __($specials);?><option  value="0"><?php echo __( 'None' , 'easyReservations' );?></option><?php echo reservations_get_offer_options($special); ?></select>
			<?php
			if(!empty($editQuerry[0]->custom)){
				$explodecustoms=explode("&;&", $editQuerry[0]->custom);
				$thenumber=0;
				$customsmerge=array_values(array_filter($explodecustoms));
				foreach($customsmerge as $custom){
					$customexp=explode("&:&", $custom);
					$thenumber++;
					echo '<label>'.__($customexp[0]).'<span class="small">'.__( "Type in information" , "easyReservations" ).'</span></label><input type="hidden" name="custom_title_'.$thenumber.'" value="'.$customexp[0].'"><input type="text" name="custom_value_'.$thenumber.'" value="'.$customexp[1].'">';
				}
			}

			if(!empty($editQuerry[0]->customp)){
				$thenumber2=0;
				$explodecustomprices=explode("&;&", $editQuerry[0]->customp);
				$customsmerges=array_values(array_filter($explodecustomprices));
				foreach($customsmerges as $thenumber2 => $customprice){
					$thenumber2++;
					$custompriceexp=explode("&:&", $customprice);
					$pricexplode=explode(":", $custompriceexp[1]);
					echo '<label>'.__($custompriceexp[0]).'<span class="small">'.__( "Pay service" , "easyReservations" ).'</span></label><span class="formblock"><input type="hidden" name="customp_value_'.$thenumber2.'" value="'.$customprice.'"><b>'.$pricexplode[0].':</b> '.$pricexplode[1].' &'.get_option("reservations_currency").';<input type="checkbox" name="custom_price'.$thenumber2.'" id="custom_price'.$thenumber2.'" value="test:'.$pricexplode[1].'" onchange="easyRes_sendReq_Price();" checked ></span>';
				}
			}
			?>
				<label><?php echo __( 'Message' , 'easyReservations' );?><span class="small"><?php echo __( 'Type in message' , 'easyReservations' );?></span></label><textarea name="editMessage" style="width:170px;"><?php echo $editQuerry[0]->notes; ?></textarea>
			<div style="text-align:center"><?php if($resPast == 0){ ?><input type="submit" onclick="document.getElementById('easyFrontendFormular').submit(); return false;" value="<?php printf ( __( 'Submit' , 'easyReservations' ));?>"><?php } ?><span class="showPrice" style="margin-left:10px"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &<?php echo get_option("reservations_currency"); ?>;</span></div>
			</form><script>easyRes_sendReq_Price();</script><?php
		} else {
			echo __( 'Wrong ID and/or eMail' , 'easyReservations' ).' <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>';
		}
	} else {
		if(!isset($chaptchaFileAdded)) require_once(dirname(__FILE__).'/lib/captcha/captcha.php');
		$captcha_instance = new ReallySimpleCaptcha();
		$word = $captcha_instance->generate_random_word();
		$prefix = mt_rand();
		$url = $captcha_instance->generate_image($prefix, $word);
?><div>
<form method="post" id="easyFrontendFormular">
	<label><?php echo __( 'ID' , 'easyReservations' );?><span class="small"><?php echo __( 'ID of your reservation' , 'easyReservations' );?></span></label><input name="editID" type="text"><br>
	<label><?php echo __( 'eMail' , 'easyReservations' );?><span class="small"><?php echo __( 'Your email' , 'easyReservations' );?></span></label><input name="email" type="text"><br>
	<label><?php echo __( 'Captcha' , 'easyReservations' );?><span class="small"><?php echo __( 'Type in code' , 'easyReservations' );?></span></label><input type="text" name="captcha_value" style="width:40px;"><img style="vertical-align:middle;" src="<?php echo RESERVATIONS_LIB_DIR.'/captcha/tmp/'.$url; ?>"><input type="hidden" value="<?php echo $prefix; ?>" name="captcha_prefix">
	<input type="submit" onclick="document.getElementById('easyFrontendFormular').submit(); return false;" class="button-primary" value="<?php printf ( __( 'Submit' , 'easyReservations' ));?>">
</form></div><?php
    }
}	?>