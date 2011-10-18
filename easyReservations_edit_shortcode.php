<?php
function reservations_edit_shortcode($atts) {
?><link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery.min.js"></script>
<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
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
</style><script>
	$(document).ready(function() {
		$("#datepicker").datepicker( { dateFormat: 'dd.mm.yy', style: 'font-size:1em' });
		$("#datepicker2").datepicker( { dateFormat: 'dd.mm.yy' });
	});
</script>
<?php
	if(isset($_POST['editEmail']) AND isset($_POST['editID'])){
		$theMail = $_POST['editEmail'];
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

		if(isset($_POST['editName'])){

				if ($_POST['editName'] != '' AND strlen($_POST['editName']) < 20 AND strlen($_POST['editName']) > 3){
				$name_form=$_POST['editName'];}
				else { $error.=  '<b>'.__( 'Please enter correct Name' , 'easyReservations' ).'</b><br>'; }

				if ($_POST['editFrom'] != '' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['editFrom']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['editFrom']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['editFrom']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['editFrom']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['editFrom']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['editFrom']))) {
				$arrivaldate_form=date("Y-m-d", strtotime($_POST['editFrom']));
				$arrivaldate_form2=date("d.m.Y", strtotime($_POST['editFrom']));
				$month_form=date("Y-m",strtotime($_POST['editFrom']));}
				else { $error.=  '<b>'.__( 'Please enter correct arrival Date' , 'easyReservations' ).'</b><br>'; }

				if ($_POST['editTo'] AND strtotime($_POST['editTo'])-time() > '0' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['editTo']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['editTo']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['editTo']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['editTo']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['editTo']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['editTo']))) {
				$dayofdeparture=strtotime($_POST['editFrom']);
				$daysbetween=strtotime($_POST['editTo'])-strtotime($_POST['editFrom']);
				$nights_form=$daysbetween/24/60/60; }
				else { $error.=  '<b>'.__( 'Please enter correct destination Date' , 'easyReservations' ).'</b><br>'; }

				$pattern_mail = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,5}$";
				if ($_POST['editEmail'] != '' AND strlen($_POST['editEmail']) < 40 AND strlen($_POST['editEmail']) > 5 AND eregi($pattern_mail, $_POST['editEmail'])){
				$email_form=$_POST['editEmail'];}
				else { $error.=  '<b>'.__( 'Please enter correct eMail' , 'easyReservations' ).'</b><br>'; }

				if ($_POST['editPersons'] != '' AND strlen($_POST['editPersons']) < 4 AND is_numeric($_POST['editPersons'])){
				$persons_form=$_POST['editPersons']; }
				else { $error.=  '<b>'.__( 'Please enter correct amount of Persons' , 'easyReservations' ).'</b><br>'; }

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

			if(isset($_POST['editName']) AND !$error) { //When Check gives no error Insert into Database and send mail

				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$arrivaldate_form', nights='$nights_form', name='$name_form', email='$email_form', notes='$message_form', room='$room_form', number='$persons_form', special='$specialoffer_form', dat='$month_form', custom='$customfields', approve='' WHERE id='$theID' ")) or trigger_error('mySQL-Fehler in Query "'.$sql.'": '.mysql_error(), E_USER_ERROR);

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

				easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['editTo']), $name_form, $email_form, $nights_form, $persons_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form);
				easyreservations_send_mail($emailformation2, $email_form, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['editTo']), $name_form, $email_form, $nights_form, $persons_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form);
			}

		$SQLeditq = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id='$theID' AND email='$theMail' ";
		$editQuerry = $wpdb->get_results($SQLeditq ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));
		
		if(isset($editQuerry[0])){
		$offer_cat = get_option("reservations_special_offer_cat");
		$room_category =get_option("reservations_room_category");

		$special = $editQuerry[0]->special;
		$specialgetpost=get_post($special);
		$specials=$specialgetpost->post_title;
		$persons=$editQuerry[0]->number;

		if($special=="0") $specials="None";

		if(isset($error)) echo $error;
		
		echo get_option("reservations_edit_text");
		?><br><br>
			<form method="post" id="edit_the_reservation">
				<input name="editID" type="hidden" value="<?php echo $_POST['editID'];?>">
				<p><?php printf ( __( 'Name' , 'easyReservations' ));?>: <input type="text" name="editName" value="<?php echo $editQuerry[0]->name; ?>"></p>
				<p><?php printf ( __( 'eMail' , 'easyReservations' ));?>: <input type="text" name="editEmail" value="<?php echo $editQuerry[0]->email; ?>"></p>
				<p><?php printf ( __( 'From' , 'easyReservations' ));?>: <input type="text" name="editFrom" id="datepicker" value="<?php echo date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)); ?>"></p>
				<p><?php printf ( __( 'To' , 'easyReservations' ));?>: <input type="text" name="editTo" id="datepicker2" value="<?php  echo date("d.m.Y", strtotime($editQuerry[0]->arrivalDate)+(86400*$editQuerry[0]->nights));?>"></p>
				<p><?php printf ( __( 'Persons' , 'easyReservations' ));?>: <select name="editPersons"><option value="<?php echo $persons;?>" select><?php echo $persons;?></option> <?php
						for($countpersons=1; $countpersons < 100; $countpersons++){
							echo '<option value="'.$countpersons.'">'.$countpersons.'</option>';
						}
					?></select>
				</p>
				<p><?php printf ( __( 'Room' , 'easyReservations' ));?>: <select  name="room" id="room"><option value="<?php echo $editQuerry[0]->room;?>"><?php echo __(get_the_title($editQuerry[0]->room));?></option>
					<?php	
						$argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1);
						$roomcategories = get_posts( $argss );
						foreach( $roomcategories as $roomcategorie ){
							if($roomcategorie->ID!=$editQuerry[0]->room){
								echo '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
								}
							} 
					?></select>
				</p>
				<p><?php printf ( __( 'Offer' , 'easyReservations' ));?>: <select  name="specialoffer" id="specialoffer"><?php if($special!=0){ ?><option  value="<?php echo $special;?>" selected><?php echo __($specials);?><?php }?><option  value="0">None</option>
					<?php	
						$argss = array( 'type' => 'post', 'category' => $offer_cat, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1);
						$specialcategories = get_posts( $argss );
						foreach( $specialcategories as $specialcategorie ){
							if($specialcategorie->ID!=$special){
								echo '<option value="'.$specialcategorie->ID.'">'.__($specialcategorie->post_title).'</option>';
							}
						} ?></select>
				</p>
				<?php
					$explodecustoms=explode("&;&", $editQuerry[0]->custom);
					$thenumber=0;
					$customsmerge=array_values(array_filter($explodecustoms));
					foreach($customsmerge as $custom){
						$customexp=explode("&:&", $custom);
						$thenumber++;
						echo '<p>'.__($customexp[0]).': <input type="hidden" name="custom_title_'.$thenumber.'" value="'.$customexp[0].'"><input type="text" name="custom_value_'.$thenumber.'" value="'.$customexp[1].'"></p>';
					} ?>
				<p><?php printf ( __( 'Message' , 'easyReservations' ));?>: <textarea name="editMessage"><?php echo $editQuerry[0]->notes; ?></textarea></p>
				<input type="button" onclick="document.getElementById('edit_the_reservation').submit(); return false;" class="button-secondary" value="<?php printf ( __( 'Submit' , 'easyReservations' ));?>">
			</form>
		<?php
		} else {
			echo __( 'Wrong ID and/or eMail' , 'easyReservations' ).' <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>';
		}
	} else {
?>
<form method="post" id="edit_reservation">
	<?php printf ( __( 'ID' , 'easyReservations' ));?>: <input name="editID" type="text">
	<?php printf ( __( 'eMail' , 'easyReservations' ));?>: <input name="editEmail" type="text">
	<input type="button" onclick="document.getElementById('edit_reservation').submit(); return false;" class="button-primary" value="<?php printf ( __( 'Submit' , 'easyReservations' ));?>">
</form><?php
    }
}	?>