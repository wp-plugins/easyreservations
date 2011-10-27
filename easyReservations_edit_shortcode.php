<?php
function reservations_edit_shortcode($atts) {
?><link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css"/>
 <script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery.min.js"></script>
 <script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
<script language="JavaScript" id="urlPrice" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/send_price.js"></script>
<script language="JavaScript" id="urlValidate" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/send_validate.js"></script>
<link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/forms/form_none.css" rel="stylesheet" type="text/css"/>
<style>
.warning {
	background: #fff6bf url(<?php echo RESERVATIONS_IMAGES_DIR;?>/exclamation.png) center no-repeat;
	background-position: 15px 50%; /* x-pos y-pos */
	text-align: left;
	padding: 5px 20px 5px 45px;
	border-top: 2px solid #ffd324;
	border-bottom: 2px solid #ffd324;
	font-family :Arial;
	width:250px;
}
.showPrice {
	padding:6px;
	width:170px;
	font-family :Verdana;
	white-space: nowrap; 
	background: #FFF9AD;
	margin: 10px 0px;
}
.showError {
	padding:6px;
	width:400px;
	font-family :Verdana;
	font-size:12px;
	font-weight:bold;
	white-space: nowrap;
	color:#E80000;
	//background: #DD3737;
	margin: 10px 0px;
}
</style><script>
	$(document).ready(function() {
		$("#datepicker").datepicker( { dateFormat: 'dd.mm.yy', style: 'font-size:1em' });
		$("#datepicker2").datepicker( { dateFormat: 'dd.mm.yy' });
	});
</script>
<?php
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

		if(isset($_POST['thename'])){

			if ($_POST['thename'] != '' AND strlen($_POST['thename']) < 20 AND strlen($_POST['thename']) > 3){
			$name_form=$_POST['thename'];}
			else { $error.=  '<b>'.__( 'Please enter correct Name' , 'easyReservations' ).'</b><br>'; }

			if ($_POST['from'] != '' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['from']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['from']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['from']))) {
			$arrivaldate_form=date("Y-m-d", strtotime($_POST['from']));
			$arrivaldate_form2=date("d.m.Y", strtotime($_POST['from']));
			$month_form=date("Y-m",strtotime($_POST['from']));}
			else { $error.=  '<b>'.__( 'Please enter correct arrival Date' , 'easyReservations' ).'</b><br>'; }

			if ($_POST['to'] AND strtotime($_POST['to'])-time() > '0' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['to']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['to']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['to']))) {
			$dayofdeparture=strtotime($_POST['from']);
			$daysbetween=strtotime($_POST['to'])-strtotime($_POST['from']);
			$nights_form=$daysbetween/24/60/60; }
			else { $error.=  '<b>'.__( 'Please enter correct destination Date' , 'easyReservations' ).'</b><br>'; }

			$pattern_mail = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,5}$";
			if ($_POST['email'] != '' AND strlen($_POST['email']) < 40 AND strlen($_POST['email']) > 5 AND eregi($pattern_mail, $_POST['email'])){
			$email_form=$_POST['email'];}
			else { $error.=  '<b>'.__( 'Please enter correct eMail' , 'easyReservations' ).'</b><br>'; }

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

			if($_POST['specialoffer'] != '0'){
			$specialoffer_form=$_POST['specialoffer'];}
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

			for($i=1; $i < 15; $i++){
				if(isset($_POST["custom_value_".$i.""])){
					$customfields .= $_POST["custom_title_".$i.""].'&:&'.$_POST["custom_value_".$i.""].'&;&';
				}
			}

			if($error!='') $error='<p class="warning">'.$error.'</p>';
		}

		if(isset($_POST['thename']) AND !$error) { //When Check gives no error Insert into Database and send mail
			$checkSQLedit = "SELECT email, name, arrivalDate, nights, number, childs, country, room, special, approve, notes, custom, price FROM ".$wpdb->prefix ."reservations WHERE id='$theID' AND email='$theMail' ";
			$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

			$beforeArray = array( 'arrivalDate' => $checkQuerry[0]->arrivalDate, 'nights' => $checkQuerry[0]->nights, 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'offer' => $checkQuerry[0]->special, 'message' => $checkQuerry[0]->notes, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country );
			$afterArray = array( 'arrivalDate' => $arrivaldate_form, 'nights' => $nights_form, 'email' => $email_form, 'name' => $name_form, 'persons' => $persons_form, 'childs' => $childs_form, 'room' =>  $room_form, 'offer' => $specialoffer_form, 'message' => $message_form, 'custom' => $customfields, 'country' => $country_form );

			$changelog = easyreservations_generate_res_Changelog($beforeArray, $afterArray);
			
			if($checkQuerry[0]->nights != $nights_form OR $checkQuerry[0]->arrivalDate != $arrivaldate_form OR $checkQuerry[0]->number != $persons_form OR $checkQuerry[0]->room != $room_form OR $checkQuerry[0]->special != $specialoffer_form){
				$explodePrice = explode(";", $checkQuerry[0]->price);
				$newPrice = "price='".$explodePrice[1]."',";
			} else $newPrice = '';

			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$arrivaldate_form', nights='$nights_form', name='$name_form', email='$email_form', notes='$message_form', room='$room_form', number='$persons_form', childs='$childs_form', special='$specialoffer_form', dat='$month_form', country='$country_form', custom='$customfields', ".$newPrice." approve='' WHERE id='$theID' ")) or trigger_error('mySQL-Fehler in Query "'.$sql.'": '.mysql_error(), E_USER_ERROR);

			echo '<b>'.__( 'Your Reservation was edited' , 'easyReservations' ).'</b><br><br>';

			$newID = mysql_insert_id();
			$thePrice = easyreservations_get_price($newID);

			if($specialoffer_form != "0"){
				$post_id_7 = get_post($specialoffer_form);
				$specialoffer = __($post_id_7->post_title);
			}
			if($specialoffer_form == "0") $specialoffer =  __( 'None' , 'easyReservations' );

			$post_id8 = get_post($room_form);
			$roomtitle = __($post_id8->post_title);

			$emailformation=get_option('reservations_email_to_admin_edited_msg');
			$subj=get_option("reservations_email_to_admin_edited_subj");
			$emailformation2=get_option('reservations_email_to_user_edited_msg');
			$subj2=get_option("reservations_email_to_user_edited_subj");
			$reservation_support_mail = get_option("reservations_support_mail");
			
			if($checkQuerry[0]->email == $email_form){
				easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
				easyreservations_send_mail($emailformation2, $email_form, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $roomtitle, $specialoffer, $country_form, $thePrice, $message_form, $changelog);
			} else {
				easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
				easyreservations_send_mail($emailformation2, $email_form, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
				easyreservations_send_mail($emailformation2, $checkQuerry[0]->email, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $childs_form, $country_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form, $changelog);
			}
		}

		$SQLeditq = "SELECT email, name, arrivalDate, nights, number, childs, room, special, approve, country, notes, custom FROM ".$wpdb->prefix ."reservations WHERE id='$theID' AND email='$theMail' ";
		$editQuerry = $wpdb->get_results($SQLeditq ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

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

		//echo $_POST['invoice'];
		//echo $_POST['amount'];

		if($special=="0") $specials="None";

		if(isset($error)) echo $error;
		
		if(strtotime($editQuerry[0]->arrivalDate)+(86400*$editQuerry[0]->nights) < time()){
			$resPast = 1;
			$pastError = __( 'Your Reservation is past' , 'easyReservations' ).'<br>';
		} else {
			$resPast = 0;
			$pastError = '';
		}
		echo get_option("reservations_edit_text");
		
		echo '<div style="background:#f9f9f9; padding:5px;margin:5px;text-align:center;"><b style="font-weight:bold;">'.__( 'Status' , 'easyReservations' ).': '.reservations_status_output($approve).' | '.__( 'Price' , 'easyReservations' ).': '.easyreservations_get_price($theID).' | '.__( 'Left' , 'easyReservations' ).': '.reservations_format_money(reservations_check_pay_status($theID)).' &'.get_option('reservations_currency').';</b></div>';
		?>
			<div id="showError" class="showError" style="margin-left:auto;margin-right:auto;"><?php echo $pastError; ?></div>
			<form method="post" id="easyFrontendFormular" name="easyFrontendFormular" style="width:400px;margin-left:auto;margin-right:auto;">
				<input name="editID" type="hidden" value="<?php echo $_POST['editID'];?>">
				<label><?php echo __( 'Name' , 'easyReservations' );?><span class="small"><?php echo __( 'Your name' , 'easyReservations' );?></span></label><input type="text" name="thename" onchange="easyRes_sendReq_Validate();" value="<?php echo $editQuerry[0]->name; ?>">
				<label><?php echo __( 'eMail' , 'easyReservations' );?><span class="small"><?php echo __( 'Your email' , 'easyReservations' );?></span></label><input type="text" name="email" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();" value="<?php echo $editQuerry[0]->email; ?>">
				<label><?php echo __( 'From' , 'easyReservations' );?><span class="small"><?php echo __( 'The arrival date' , 'easyReservations' );?></span></label><input type="text" name="from" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();" id="datepicker" value="<?php echo date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)); ?>">
				<label><?php echo __( 'To' , 'easyReservations' );?><span class="small"><?php echo __( 'The departure date' , 'easyReservations' );?></span></label><input type="text" name="to" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();" id="datepicker2" value="<?php  echo date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)+(86400*$editQuerry[0]->nights));?>">
				<label><?php echo __( 'Persons' , 'easyReservations' );?><span class="small"><?php echo __( 'The amount of persons' , 'easyReservations' );?></span></label><select name="persons" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();"><option value="<?php echo $persons;?>" select><?php echo $persons;?></option> <?php
						for($countpersons=1; $countpersons < 100; $countpersons++){
							echo '<option value="'.$countpersons.'">'.$countpersons.'</option>';
						}
					?></select>
				<?php if(isset($childs) AND $childs != ""){ ?><label><?php echo __( 'Childs' , 'easyReservations' );?><span class="small"><?php echo __( 'The amount of childs' , 'easyReservations' );?></span></label><select name="childs" onchange="easyRes_sendReq_Price();"><option value="<?php echo $childs;?>" select><?php echo $childs;?></option> <?php
						for($countpersons=0; $countpersons < 50; $countpersons++){
							echo '<option value="'.$countpersons.'">'.$countpersons.'</option>';
						}
					?></select>
				<?php } ?>
				<label><?php echo __( 'Country' , 'easyReservations' );?><span class="small"><?php echo __( 'The departure date' , 'easyReservations' );?></span></label><select name="country"><?php echo easyReservations_country_select($country); ?></select>
				<label><?php echo __( 'Room' , 'easyReservations' );?><span class="small"><?php echo __( 'Choose the room' , 'easyReservations' );?></span></label><select  name="room" id="room" onChange="document.formular.room.value=this.value;easyRes_sendReq_Calendar();easyRes_sendReq_Price();easyRes_sendReq_Validate();"><option value="<?php echo $editQuerry[0]->room;?>"><?php echo __(get_the_title($editQuerry[0]->room));?></option>
					<?php	
						$argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1);
						$roomcategories = get_posts( $argss );
						foreach( $roomcategories as $roomcategorie ){
							if($roomcategorie->ID != $editQuerry[0]->room){
								echo '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
							}
						} 
					?></select>
				<label><?php echo __( 'Offer' , 'easyReservations' );?><span class="small"><?php echo __( 'Choose the offer' , 'easyReservations' );?></span></label><select  name="specialoffer" id="specialoffer" onchange="easyRes_sendReq_Price();easyRes_sendReq_Validate();"><?php if($special!=0){ ?><option  value="<?php echo $special;?>" selected><?php echo __($specials);?><?php }?><option  value="0">None</option>
					<?php	
						$argss = array( 'type' => 'post', 'category' => $offer_cat, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1);
						$specialcategories = get_posts( $argss );
						foreach( $specialcategories as $specialcategorie ){
							if($specialcategorie->ID!=$special){
								echo '<option value="'.$specialcategorie->ID.'">'.__($specialcategorie->post_title).'</option>';
							}
						} ?></select>

				<?php
					$explodecustoms=explode("&;&", $editQuerry[0]->custom);
					$thenumber=0;
					$customsmerge=array_values(array_filter($explodecustoms));
					foreach($customsmerge as $custom){
						$customexp=explode("&:&", $custom);
						$thenumber++;
						echo '<label>'.__($customexp[0]).'<span class="small">'.__( "Type in information" , "easyReservations" ).'</span></label><input type="hidden" name="custom_title_'.$thenumber.'" value="'.$customexp[0].'"><input type="text" name="custom_value_'.$thenumber.'" value="'.$customexp[1].'">';
					} ?>
				<label><?php echo __( 'Message' , 'easyReservations' );?><span class="small"><?php echo __( 'Type in message' , 'easyReservations' );?></span></label><textarea name="editMessage" style="width:170px;"><?php echo $editQuerry[0]->notes; ?></textarea>
				
				<div style="text-align:center"><?php if($resPast == 0){ ?><input type="submit" onclick="document.getElementById('easyFrontendFormular').submit(); return false;" value="<?php printf ( __( 'Submit' , 'easyReservations' ));?>"><?php } ?><span class="showPrice" style="margin-left:10px"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &<?php echo get_option("reservations_currency"); ?>;</span></div>
			</form><?php
		} else {
			echo __( 'Wrong ID and/or eMail' , 'easyReservations' ).' <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>';
		}
	} else {
?>
<form method="post" id="edit_reservation">
	<?php printf ( __( 'ID' , 'easyReservations' ));?>: <input name="editID" type="text">
	<?php printf ( __( 'eMail' , 'easyReservations' ));?>: <input name="email" type="text">
	<input type="button" onclick="document.getElementById('edit_reservation').submit(); return false;" class="button-primary" value="<?php printf ( __( 'Submit' , 'easyReservations' ));?>">
</form><?php
    }
}	?>