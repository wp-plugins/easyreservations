<?php
function reservation_settings_page() { //Set Settings
			global $wpdb;

			$countcleans= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() AND approve != 'yes' "));

			if(isset($_GET["form"])){
				$formnameget = $_GET['form'];
				$reservations_form=get_option("reservations_form_".$formnameget.""); $howload="reservations ".$formnameget.""; 
			} else {
				$formnameget='';
				$reservations_form=get_option("reservations_form"); $howload="reservations"; 
			}

			if(isset($_GET["deleteform"])){
				$namtetodelete = $_GET['deleteform'];
			} 

			if(isset($_POST["action"])){ 
				$action = $_POST['action'];
			}

			if(isset($_GET["site"])){
				$settingpage = $_GET['site'];
			} else {
				$settingpage="general"; $ifgeneralcurrent='class="current"';
			}

			if($settingpage=="about") { $settingpage="about"; $ifaboutcurrent='class="current"'; }

			if(isset($action) AND $action == "reservation_clean_database") {
				$promt='cleaned';
				$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() AND approve != 'yes' ") ); 
			}

		if(isset($action) AND $action == "reservation_settingss"){
			//Set Reservation settings 
			$regguests = $_POST["regular_guests"];
			$easyReservationSyle = $_POST["reservations_style"];
			$reservations_price_per_persons = $_POST["reservations_price_per_persons"];
			$reservationss_support_mail = $_POST["reservations_support_mail"];
			$offer_cat = $_POST["offer_cat"];
			$room_category2 = $_POST["room_category"];
			$reservations_edit_url = $_POST["reservations_edit_url"];
			$reservations_edit_text = stripslashes($_POST["reservations_edit_text"]);
			update_option("reservations_style",$easyReservationSyle);
			update_option("reservations_regular_guests",$regguests);
			update_option("reservations_price_per_persons",$reservations_price_per_persons);
			update_option("reservations_room_category",$room_category2);
			update_option("reservations_support_mail",$reservationss_support_mail);
			update_option("reservations_special_offer_cat",$offer_cat);	
			update_option("reservations_edit_url",$reservations_edit_url);
			update_option("reservations_edit_text",$reservations_edit_text);	
			

			//Set Currency
			$reservations_currency = $_POST["reservations_currency"];
			update_option("reservations_currency",$reservations_currency);
		}

		if(isset($action) AND $action == "reservations_email_settings"){//Set Reservation Mails
			$reservations_email_sendmail_msg = $_POST["reservations_email_sendmail_msg"];
			$reservations_email_sendmail_subj = $_POST["reservations_email_sendmail_subj"];
			update_option("reservations_email_sendmail_msg",$reservations_email_sendmail_msg);
			update_option("reservations_email_sendmail_subj",$reservations_email_sendmail_subj);

			$reservations_email_to_admin_msg = $_POST["reservations_email_to_admin_msg"];
			$reservations_email_to_admin_subj = $_POST["reservations_email_to_admin_subj"];
			update_option("reservations_email_to_admin_msg",$reservations_email_to_admin_msg);
			update_option("reservations_email_to_admin_subj",$reservations_email_to_admin_subj);

			$reservations_email_to_userapp_msg = $_POST["reservations_email_to_userapp_msg"];
			$reservations_email_to_userapp_subj = $_POST["reservations_email_to_userapp_subj"];
			update_option("reservations_email_to_userapp_msg",$reservations_email_to_userapp_msg);
			update_option("reservations_email_to_userapp_subj",$reservations_email_to_userapp_subj);

			$reservations_email_to_userdel_msg = $_POST["reservations_email_to_userdel_msg"];
			$reservations_email_to_userdel_subj = $_POST["reservations_email_to_userdel_subj"];
			update_option("reservations_email_to_userdel_msg",$reservations_email_to_userdel_msg);
			update_option("reservations_email_to_userdel_subj",$reservations_email_to_userdel_subj);

			$reservations_email_to_user_msg = $_POST["reservations_email_to_user_msg"];
			$reservations_email_to_userdel_subj = $_POST["reservations_email_to_userdel_subj"];
			update_option("reservations_email_to_user_msg",$reservations_email_to_user_msg);
			update_option("reservations_email_to_userdel_subj",$reservations_email_to_userdel_subj);

			$reservations_email_to_user_edited_msg = $_POST["reservations_email_to_user_edited_msg"];
			$reservations_email_to_user_edited_subj = $_POST["reservations_email_to_user_edited_subj"];
			update_option("reservations_email_to_user_edited_msg",$reservations_email_to_user_edited_msg);
			update_option("reservations_email_to_user_edited_subj",$reservations_email_to_user_edited_subj);

			$reservations_email_to_admin_edited_msg = $_POST["reservations_email_to_admin_edited_msg"];
			$reservations_email_to_admin_edited_subj = $_POST["reservations_email_to_admin_edited_subj"];
			update_option("reservations_email_to_admin_edited_msg",$reservations_email_to_admin_edited_msg);
			update_option("reservations_email_to_admin_edited_subj",$reservations_email_to_admin_edited_subj);
			}

		if(isset($action) AND $action  == "reservations_form_settings"){ // Change a Form
			// Set Form
			$reservations_form_value = $_POST["reservations_formvalue"];
			$formnamesgets = $_POST["formnamesgets"];
			if($formnamesgets==""){
				update_option("reservations_form", $reservations_form_value);
			} else {
				update_option('reservations_form_'.$formnamesgets.'', $reservations_form_value);
			}
			$reservations_form = $_POST["reservations_formvalue"];
		}

		if(isset($action) AND $action == "reservation_change_permissions"){ // Change a Form
			$permissionselect = $_POST["permissionselect"];
			update_option('reservations_main_permission', $permissionselect);
		}

		if(isset($namtetodelete)){
			delete_option('reservations_form_'.$namtetodelete.'');
		}

		if(isset($action) AND $action == "reservations_form_add"){// Add Form after check twice for stupid Users :D
			if($_POST["formname"]!=""){

				$formname0='reservations_form_'.$_POST["formname"];
				$formname1='reservations_form_'.$_POST["formname"].'_1';
				$formname2='reservations_form_'.$_POST["formname"].'_2';
				
				if(get_option($formname0)==""){
					add_option(''.$formname0.'', ' ', '', 'yes' );
				} elseif(get_option($formname1)==""){
					add_option(''.$formname1.'', ' ', '', 'yes');
				} else { add_option(''.$formname2.'', ' ', '', 'yes'); }
			}
		} 
		if($settingpage=="form"){//Get current Form Options
			$forms = '';
			$ifformcurrent='class="current"';
			//Form Options
			$form = "SELECT option_name, option_value FROM ".$wpdb->prefix ."options WHERE option_name like 'reservations_form_%' "; // Get User made Forms
			$formresult = $wpdb->get_results($form);
				foreach( $formresult as $result )	{
					$formcutedname=str_replace('reservations_form_', '', $result->option_name);
					if($formcutedname!=""){
						if($formcutedname == $formnameget) $formbigcutedname='<b style="color:#000">'.$formcutedname.'</b>'; else $formbigcutedname = $formcutedname;
						$forms.=' | <a href="admin.php?page=settings&site=form&form='.$formcutedname.'">'.$formbigcutedname.'</a> <a href="admin.php?page=settings&site=form&deleteform='.$formcutedname.'"><img style="vertical-align:textbottom;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a>';
					}
				}
		}
		
		if($settingpage=="email"){
			$ifemailcurrent='class="current"';
			$reservations_email_sendmail_msg=get_option("reservations_email_sendmail_msg");
			$reservations_email_sendmail_subj=get_option("reservations_email_sendmail_subj");
			$reservations_email_to_admin_msg=get_option("reservations_email_to_admin_msg");
			$reservations_email_to_admin_subj=get_option("reservations_email_to_admin_subj");
			$reservations_email_to_user_msg=get_option("reservations_email_to_user_msg");
			$reservations_email_to_user_subj=get_option("reservations_email_to_user_subj");
			$reservations_email_to_user_edited_subj=get_option("reservations_email_to_user_edited_subj");
			$reservations_email_to_user_edited_msg=get_option("reservations_email_to_user_edited_msg");
			$reservations_email_to_admin_edited_subj=get_option("reservations_email_to_admin_edited_subj");
			$reservations_email_to_admin_edited_msg=get_option("reservations_email_to_admin_edited_msg");
			$reservations_email_to_userapp_msg=get_option("reservations_email_to_userapp_msg");
			$reservations_email_to_userapp_subj=get_option("reservations_email_to_userapp_subj");
			$reservations_email_to_userdel_msg=get_option("reservations_email_to_userdel_msg");
			$reservations_email_to_userdel_subj=get_option("reservations_email_to_userdel_subj");
		}

		$offer_cat = get_option("reservations_special_offer_cat");
		$room_category = get_option('reservations_room_category');
?>
<script>
function addtext() {
	var newtext = document.reservations_form_settings.inputstandart.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
function addtextforRoom() {
	var newtext = document.reservations_form_settings.inputstandartroom.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
function addtextforOffer() {
	var newtext = document.reservations_form_settings.inputstandartoffer.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
function addtextforemail0() {
	var newtext = document.reservations_email_settings.inputemail0.value;
	document.reservations_email_settings.reservations_email_sendmail_msg.value = newtext;
}
function addtextforemail1() {
	var newtext = document.reservations_email_settings.inputemail1.value;
	document.reservations_email_settings.reservations_email_to_admin_msg.value = newtext;
}
function addtextforemail2() {
	var newtext = document.reservations_email_settings.inputemail2.value;
	document.reservations_email_settings.reservations_email_to_userapp_msg.value = newtext;
}
function addtextforemail3() {
	var newtext = document.reservations_email_settings.inputemail3.value;
	document.reservations_email_settings.reservations_email_to_userdel_msg.value = newtext;
}
function addtextforemail4() {
	var newtext = document.reservations_email_settings.inputemail4.value;
	document.reservations_email_settings.reservations_email_to_user_msg.value = newtext;
}
function addtextforemail5() {
	var newtext = document.reservations_email_settings.inputemail5.value;
	document.reservations_email_settings.reservations_email_to_user_edited_msg.value = newtext;
}
function addtextforemail6() {
	var newtext = document.reservations_email_settings.inputemail6.value;
	document.reservations_email_settings.reservations_email_to_admin_edited_msg.value = newtext;
}
function resteText() {
	var newtext = document.reservations_form_settings.resetforrm.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
</script>
<div id="icon-options-general" class="icon32"><br></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;"><?php printf ( __( 'Settings' , 'easyReservations' ));?></h2>
<div id="wrap">

<div class="tabs-box widefat" style="margin-bottom:10px">
	<ul class="tabs">
		<li><a <?php if(isset($ifgeneralcurrent)) echo $ifgeneralcurrent; ?> href="admin.php?page=settings"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/pref.png"> <?php printf ( __( 'General' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifformcurrent)) echo $ifformcurrent; ?> href="admin.php?page=settings&site=form"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/form.png"> <?php printf ( __( 'Form' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifemailcurrent)) echo $ifemailcurrent; ?> href="admin.php?page=settings&site=email"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMails' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifaboutcurrent)) echo $ifaboutcurrent; ?> href="admin.php?page=settings&site=about"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'About' , 'easyReservations' ));?></a></li>
	</ul>
</div>

<?php if($settingpage=="general"){
		//Get current Options
		$reservations_price_per_persons = get_option("reservations_price_per_persons");
		$reservations_currency = get_option("reservations_currency");
		$reservation_support_mail = get_option("reservations_support_mail");
		$reservations_regular_guests = get_option('reservations_regular_guests');
		$reservations_main_permission=get_option("reservations_main_permission");
		$reservations_edit_url=get_option("reservations_edit_url");
		$reservations_edit_text=get_option("reservations_edit_text");
?>
<table cellspacing="0" style="width:99%">
	<tr cellspacing="0">
		<td style="width:70%;" valign="top" >
	<form method="post" action="admin.php?page=settings"  id="reservation_settingss">
		<input type="hidden" name="action" value="reservation_settingss">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;">
				<thead>
					<tr>
						<th style="width:45%;"> <?php printf ( __( 'Reservation Settings' , 'easyReservations' ));?> </th>
						<th style="width:55%;"> </th>
					</tr>
				</thead>
				<tbody style="border:0px">
					<tr valign="top" style="border:0px">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'Reservation Support Mail' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Mail for Reservations' , 'easyReservations' ));?>" name="reservations_support_mail" value="<?php echo $reservation_support_mail;?>" style="width:50%"></td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Rooms Category' , 'easyReservations' ));?></td>
						<td><select  title="<?php printf ( __( 'Choose the Post-Category of Rooms' , 'easyReservations' ));?>" name="room_category" ><option  value="<?php echo $room_category ?>"><?php echo get_cat_name($room_category);?></a></option>
						<?php
							$argss = array( 'type' => 'post', 'hide_empty' => 0 );
							$roomcategories = get_categories( $argss );
							foreach( $roomcategories as $roomcategorie ){
								$id=$roomcategorie->term_id;
								if($id!=$room_category) {
									echo '<option value="'.$id.'">'.__($roomcategorie->name).'</option>';
								}
							} ?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Special Offers Category' , 'easyReservations' ));?></td>
						<td><select title="<?php printf ( __( 'Choose the Post-Category of Offers' , 'easyReservations' ));?>" name="offer_cat" ><option value="<?php echo $offer_cat ?>" select="selected"><?php echo get_cat_name($offer_cat);?></a></option>
						<?php
								$args = array( 'type' => 'post', 'hide_empty' => 0 );
								$categories = get_categories( $args );
								foreach( $categories as $categorie ){
								$idx=$categorie->term_id;
									if($idx!=$offer_cat){
										echo '<option value="'.$idx.'">'.__($categorie->name).'</option>'; 
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/calc.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td><select name="reservations_price_per_persons" title="<?php printf ( __( 'Select type of Price calculation' , 'easyReservations' ));?>"><?php if($reservations_price_per_persons == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><?php } ?><?php if($reservations_price_per_persons == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/dollar.png"> <?php printf ( __( 'Currency' , 'easyReservations' ));?></td>
						<td><select name="reservations_currency" title="<?php printf ( __( 'Select currency' , 'easyReservations' ));?>"><?php if($reservations_currency=='euro'){ ?><option select="selected"  value="euro"><?php printf ( __( 'Euro' , 'easyReservations' ));?> &euro;</option><?php }  elseif($reservations_currency=='fnof'){ ?><option select="selected"  value="fnof"><?php printf ( __( 'Florin' , 'easyReservations' ));?> &fnof;</option><?php } elseif($reservations_currency=='dollar'){ ?><option select="selected"  value="dollar"><?php printf ( __( 'Dollar' , 'easyReservations' ));?> &dollar;</option><?php } elseif($reservations_currency == 'pound'){ ?><option select="selected"  value="pound"><?php printf ( __( 'Pound' , 'easyReservations' ));?> &pound;</option><?php } ?><?php if($reservations_currency == 'yen'){ ?><option select="selected"  value="yen"><?php printf ( __( 'Yen' , 'easyReservations' ));?> &yen;</option><?php } ?><option value="euro"><?php printf ( __( 'Euro' , 'easyReservations' ));?> &euro;</option><option value="dollar"><?php printf ( __( 'Dollar' , 'easyReservations' ));?> &dollar;</option><option  value="pound"><?php printf ( __( 'Pound' , 'easyReservations' ));?> &pound;</option><option  value="yen"><?php printf ( __( 'Yen' , 'easyReservations' ));?> &yen;</option><option  value="fnof"><?php printf ( __( 'Florin' , 'easyReservations' ));?> &fnof;</option></td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/background.png"> <?php printf ( __( 'Style' , 'easyReservations' ));?></td>
						<td>
							<select name="reservations_style" title="<?php printf ( __( 'Select style of Admin Panel' , 'easyReservations' ));?>">
								<option value="widefat" <?php if($easyReservationSyle=='widefat' OR RESERVATIONS_STYLE=='widefat') echo 'selected'; ?>><?php printf ( __( 'Wordpress' , 'easyReservations' ));?></option>
								<option value="greyfat" <?php if($easyReservationSyle=='greyfat' OR RESERVATIONS_STYLE=='greyfat') echo 'selected'; ?>><?php printf ( __( 'Grey' , 'easyReservations' ));?></option>
								<!--<option value="bluefat" <?php// if($easyReservationSyle=='bluefat' OR RESERVATIONS_STYLE=='bluefat') echo 'selected'; ?>><?php //printf ( __( 'Blue' , 'easyReservations' ));?></option>-->
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px">
				<thead>
					<tr>
						<th> <?php printf ( __( 'User-Edit Settings' , 'easyReservations' ));?> </th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'To let Users edit theyre Reservations on your Site add a Page or Post with Shortcode' , 'easyReservations' ));?>:</i> <code>[editreservation]</code>
						</td>
					</tr>
					<tr class="alternate">
						<td>
							&nbsp;<b><?php printf ( __( 'URL to edit Page' , 'easyReservations' ));?></b>: <input type="text" title="<?php printf ( __( 'URL to the edit Page' , 'easyReservations' ));?>" name="reservations_edit_url" value="<?php echo $reservations_edit_url;?>" style="width:50%">
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'This text should explain your Guest the process of editing his reservation' , 'easyReservations' ));?>:</i>
							<textarea name="reservations_edit_text" style="width:100%;height:80px;margin-top:4px"><?php echo $reservations_edit_text; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px">
				<thead>
					<tr>
						<th> <?php printf ( __( 'Important Guests' , 'easyReservations' ));?> </th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'Enter eMails of important guests; seperated by comma. Reservations with this eMail will be highlighted.' , 'easyReservations' ));?></i>
							<textarea name="regular_guests" style="width:100%;height:80px;margin-top:5px;"><?php echo $reservations_regular_guests; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>" onclick="document.getElementById('reservation_settingss').submit(); return false;" style="margin-top:7px;" class="easySubmitButton-primary" >
			</form>
			</td><td style="width:1%;" valign="top">
			</td><td style="width:29%;" valign="top">
				<form method="post" action="admin.php?page=settings" id="reservation_clean_database">
				<input type="hidden" name="action" value="reservation_clean_database" id="reservation_clean_database">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Clean Database' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td >
										<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/database.png"> <?php printf ( __( 'Delete all unapproved Old Reservations' , 'easyReservations' ));?>  (<?php echo $countcleans;?>)
										<input type="button" onclick="document.getElementById('reservation_clean_database').submit(); return false;" style="float:right;" title="<?php printf ( __( 'Delete all unapproved, rejected or trashed Old Reservations' , 'easyReservations' ));?>" class="easySubmitButton-secondary" value="<?php printf ( __( 'Clean' , 'easyReservations' ));?>">
									</td>
								</tr>
						</tbody>
					</table>
				</form>
				<form method="post" action="admin.php?page=settings" id="reservation_change_permissions">
				<input type="hidden" name="action" value="reservation_change_permissions" id="reservation_change_permissions">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Change Permissions' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td><select title="<?php printf ( __( 'Select needed permission for Reservations Admin Panel' , 'easyReservations' ));?>" name="permissionselect">
										<option value="<?php echo $reservations_main_permission; ?>"><?php if($reservations_main_permission=='edit_posts') echo 'Author'; elseif($reservations_main_permission=='manage_categories') echo 'Editor'; elseif($reservations_main_permission=='manage_options') echo 'Administrator'; elseif($reservations_main_permission=='manage_network') echo 'Super Admin'; ?></option>
										<?php
										$permissionarrays=array('edit_posts','manage_categories', 'manage_options', 'manage_network');
										foreach($permissionarrays as $permissionarray){
											if($permissionarray!=$reservations_main_permission){
												if($permissionarray=='edit_posts') $permissionname='Author'; elseif($permissionarray=='manage_categories') $permissionname='Editor'; elseif($permissionarray=='manage_options') $permissionname='Administrator'; elseif($permissionarray=='manage_network') $permissionname='Super Admin';
												echo '<option value="'.$permissionarray.'">'.$permissionname.'</option>';
											}
										}
										?>
									</select><input type="button" onclick="document.getElementById('reservation_change_permissions').submit(); return false;" class="easySubmitButton-secondary" style="margin-left:auto;margin-rigth:auto;" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>">
									</td>
								</tr>
						</tbody>
					</table>
				</form>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px;">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Informations' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td colspan="2" style="vertical-align:middle;" coldspan="2"><b><?php printf ( __( 'Room IDs' , 'easyReservations' ));?>:</b><br><?php $termin=reservations_get_room_ids();
									if($termin != ""){
										$nums=0;
										foreach ($termin as $nmbr => $inhalt){
											echo __($termin[$nums][1]).': <b>'.$termin[$nums][0].'</b><br>';
											$nums++;
										}
									} else {
										echo __( 'add Post to Room Category to add a Room' , 'easyReservations' ).'<br>';
									} ?><br>
									<b><?php printf ( __( 'Offer IDs' , 'easyReservations' ));?>:</b><br><?php $termin=reservations_get_offer_ids();
									if($termin != ""){
										$nums=0;
										foreach ($termin as $nmbr => $inhalt){
											echo __($termin[$nums][1]).': <b>'.$termin[$nums][0].'</b><br>';
											$nums++;
										} 
									} else {
										echo __( 'add Post to Offer Category to add an Offer' , 'easyReservations' ).'<br>';
									}
									?><br><b><?php printf ( __( 'Support Mail' , 'easyReservations' ));?>:</b><br> feryazbeer@googemail.com</td>
								</tr>
						</tbody>
					</table>
		</td></tr></table><br>
<?php } elseif($settingpage=="form"){ 
	$formstandart="
	[error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons Select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[custom text Phone]</p>

	<p>Address:<br>[custom text Address]</p>

	<p>Room: [rooms]</p>

	<p>Offer: [offers select]</p>	

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";
		$roomsoptions = reservations_get_room_options();
		$offeroptions =  reservations_get_offer_options();
		$personsoptions = '';
		for($counts=1; $counts < 100; $counts++){
			$personsoptions .= '<option value="'.$counts.'">'.$counts.'</option>';
		}?> 
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;">
			<thead>
				<tr>
					<th style="width:45%;"> <?php printf ( __( 'Reservation Form' , 'easyReservations' ));?></th>
					<th style="width:55%;"></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top" class="alternate">
					<td style="width:60%;line-height: 2;" colspan="2"><?php if($formnameget==""){ ?><a href="admin.php?page=settings&site=form"><b style="color:#000;"><?php printf ( __( 'Standard' , 'easyReservations' ));?></b></a><?php } else { ?><a href="admin.php?page=settings&site=form"><?php printf ( __( 'Standard' , 'easyReservations' ));?></a><?php } ?><?php echo $forms; ?><div style="float:right"><form method="post" action="admin.php?page=settings&site=form"  id="reservations_form_add"><input type="hidden" name="action" value="reservations_form_add"/><input name="formname" type="text"><input type="button" onclick="document.getElementById('reservations_form_add').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Add' , 'easyReservations' ));?>"></form></div> </td>
				</tr>
				<tr valign="top">
					<td style="width:60%;line-height: 2;">
					<form id="form1" name="form1">
						<div style="float: left;">
							<select name="jumpmenu" id="jumpmenu" onChange="jumpto(document.form1.jumpmenu.options[document.form1.jumpmenu.options.selectedIndex].value)">
								<option>Add Field</option>
								<option value="error">Display Errors</option>
								<option value="date-from">Arrival Date</option>
								<option value="date-to">Departure Date</option>
								<option value="persons">Persons</option>
								<option value="thename">Name</option>
								<option value="email">eMail</option>
								<option value="message">Message</option>
								<option value="rooms">Room</option>
								<option value="offers">Offer</option>
								<option value="custom">Custom Field</option>
								<option value="price">Price Field</option>
								<option value="hidden">Hidden Field</option>
								<option value="submit">Submit Button</option>
							</select>
						</div>
						<div id="Text" style="float: left;"></div>
						<div id="Text2" style="float: left;"></div>
						<div id="Text3" style="float: left;"></div>
						<div id="Text4" style="float: left;"></div> 
						&nbsp;<a href="javascript:resetform();" style="vertical-align:text-bottom;"><?php printf ( __( 'Reset' , 'easyReservations' ));?></a>
					</form>
					<form method="post" action="admin.php?page=settings&site=form<?php if($formnameget!=""){ echo '&form='.$formnameget; } ?>"  id="reservations_form_settings" name="reservations_form_settings">
						<input type="hidden" name="action" value="reservations_form_settings"/>
						<input type="hidden" name="formnamesgets" value="<?php echo $formnameget; ?>"/>
						<input type="hidden" value="<?php echo $formstandart; ?>" name="inputstandart">
						<input type='hidden' value='<?php echo str_replace('\"', '"', $reservations_form); ?>' name="resetforrm">
						<textarea style="width:100%; height: 588px;" title="<?php printf ( __( 'The ID of the Special Offer Category' , 'easyReservations' ));?>" name="reservations_formvalue" id="reservations_formvalue"><?php echo stripslashes($reservations_form); ?></textarea><br>
						<div style="margin-top:3px;">
							<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>"onclick="document.getElementById('reservations_form_settings').submit(); return false;" class="easySubmitButton-primary" >
							<input type="button" value="<?php printf ( __( 'Default Form' , 'easyReservations' ));?>" onClick="addtext();" class="easySubmitButton-secondary" >
							<input type="button" value="<?php printf ( __( 'Reset Form' , 'easyReservations' ));?>" onClick="resteText();" class="easySubmitButton-secondary" >
						</div>
					</form>
					</td>
					<td style="width:40%;">		
					<div style="text-align:center;vertical-align:middle; height:29px; font-weight:bold;line-height: 2;"><?php printf ( __( 'Include to Page or Post with' , 'easyReservations' ));?> <code class="codecolor">[<?php echo $howload; ?>]</code></div>
						<div id="Helper"></div>
						<div class="explainbox">
							<p><code class="codecolor">[error]</code> <i><?php printf ( __( 'wrong inputs & unavailable dates' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[date-from]</code> <i><?php printf ( __( 'day of arrival with datepicker' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[date-to]</code> <i><?php printf ( __( 'day of departure with datepicker' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[persons x]</code> <i><?php printf ( __( 'number of guests' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[thename]</code> <i><?php printf ( __( 'name of guest' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email of guest' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[rooms]</code> <i><?php printf ( __( 'select of rooms' , 'easyReservations' ));?></i>*</p>
							<p><code class="codecolor">[offers x]</code> <i><?php printf ( __( 'offers as select or box' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[hidden type x]</code> <i><?php printf ( __( 'for fix a room/offer to a form' , 'easyReservations' ));?> </i></p>
							<p><code class="codecolor">[custom type x]</code> <i><?php printf ( __( 'add custom fields as needed' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[message]</code> <i><?php printf ( __( 'message from guest' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[submit x]</code> <i><?php printf ( __( 'submit button' , 'easyReservations' ));?></i> *</p>
						</div><br>
						<?php
							$couerrors=0;
							$gute=0;
							$formgood='';
							if(preg_match('/\[date-from]/', $reservations_form)) $gute++; else { 
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[date-from]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>';}
							if(preg_match('/\[date-to]/', $reservations_form) OR preg_match('/\[nights/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[date-to]</code> '.__( 'or' , 'easyReservations' ).' <code class="codecolor">[nights x]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[date-to]/', $reservations_form) AND preg_match('/\[nights/', $reservations_form)){
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[date-to]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[nights x]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[rooms]/', $reservations_form) OR preg_match('/\[hidden room/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[rooms]</code> '.__( 'or' , 'easyReservations' ).' <code class="codecolor">[hidden room roomID]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[rooms]/', $reservations_form) AND preg_match('/\[hidden room/', $reservations_form)){
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[rooms]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[hidden room roomID]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[hidden room/', $reservations_form)){
							$cougoods++; $formgood .= '<b>'.$cougoods.'.</b> '.__( 'Check ' , 'easyReservations' ).' <code class="codecolor">[hidden room roomID]</code> '.__( '\'s roomID' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[offers select]/', $reservations_form) OR preg_match('/\[offers box]/', $reservations_form) OR preg_match('/\[hidden offer/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[offers select]</code>, <code class="codecolor">[offers box]</code> '.__( 'or' , 'easyReservations' ).' <code class="codecolor">[hidden offer offerID]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[offers box]/', $reservations_form) AND preg_match('/\[hidden offer/', $reservations_form)){
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[offers box]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[hidden offer offerID]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[offers select]/', $reservations_form) AND preg_match('/\[hidden offer/', $reservations_form)){
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[offers select]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[hidden offer offerID]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[offers select]/', $reservations_form) AND preg_match('/\[offers box]/', $reservations_form)){
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[offers select]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[offers box]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[hidden offer/', $reservations_form)){
							$cougoods++; $formgood .= '<b>'.$cougoods.'.</b> '.__( 'Check ' , 'easyReservations' ).' <code class="codecolor">[hidden offer offerID]</code> '.__( '\'s offerID' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[email]/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[email]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[thename]/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[thename]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[persons/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[persons x]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }

							if(preg_match('/\[error]/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[error]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[submit/', $reservations_form)) $gute++; else {
							$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[submit x]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							$coutall=$gute+$couerrors;
							if($couerrors > 0){ ?>
							<div class="explainbox" style="background:#FCEAEA; border-color:#FF4242;box-shadow: 0 0 2px #F99F9F;">
								<?php echo __( 'This form is not valid' , 'easyReservations' ).' '.$gute.'/'.$coutall.' P.<br>'; echo $formerror; ?>
							</div><?php } else { ?>
							<div class="explainbox" style="background:#E8F9E8; border-color:#68FF42;box-shadow: 0 0 2px #9EF7A1;">
								<?php echo __( 'This form is valid' , 'easyReservations' ).' '.$gute.'/'.$coutall.' P.<br>'; echo $formgood; ?>
							</div><?php } ?>
					</td>
				</tr>
			</tbody>
		</table>
<script language="javascript" type="text/javascript" >

function AddOne(){ // Add field to the Form
	document.getElementById("reservations_formvalue").value =
    document.getElementById("reservations_formvalue").value +
    '['+document.getElementById("jumpmenu").value+']';
}
function AddTwo(){ // Add field to the Form
	document.getElementById("reservations_formvalue").value =
    document.getElementById("reservations_formvalue").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+']';
}
function AddThree(){ // Add field to the Form
	document.getElementById("reservations_formvalue").value =
    document.getElementById("reservations_formvalue").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+' '+
    document.getElementById("zwei").value+']';
}
function AddFour(){ // Add field to the Form
	document.getElementById("reservations_formvalue").value =
    document.getElementById("reservations_formvalue").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+' '+
    document.getElementById("zwei").value+' '+
    document.getElementById("drei").value+']';
}
function AddFours(){ // Add field to the Form
	document.getElementById("reservations_formvalue").value =
    document.getElementById("reservations_formvalue").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+' '+
    document.getElementById("zwei").value+' "'+
    document.getElementById("drei").value+'"]';
}

var thetext1 = false;
var thetext2 = false;
var thetext3 = false;
var thetext4 = false;

function resetform(){ // Reset fields in Form
	var Nichts = '';
	document.form1.reset();
	document.form1.jumpmenu.disabled=false;
	document.getElementById("Text").innerHTML = Nichts;
	document.getElementById("Text2").innerHTML = Nichts;
	document.getElementById("Text3").innerHTML = Nichts;
	document.getElementById("Text4").innerHTML = Nichts;
	document.getElementById("Helper").innerHTML = Nichts;
	thetext1 = false;
	thetext2 = false;
	thetext3 = false;
	thetext4 = false;
}

function jumpto(x){ // Chained inputs;

	var click = 0;
	var end = 0;
	var first = document.form1.jumpmenu.options[document.form1.jumpmenu.options.selectedIndex].value;

	if(thetext1 == false){
		if (x == "custom") {
			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="text">Text</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="radio">Radio</option><option value="check">Checkbox</option></select>';
			document.getElementById("Text").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select type of Input you want to add' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "price") {
			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="select">Select</option><option value="radio">Radio</option><option value="checkbox">Checkbox</option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select Type of custom Price Input' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Select' , 'easyReservations' ); ?></b> <?php echo __( 'Add a Drop-Down Select Input with effect on the Price to the Form' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Radio' , 'easyReservations' ); ?></b> <?php echo __( 'Add a Radio Select Input with effect on the Price to the Form' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Checkbox' , 'easyReservations' ); ?></b> <?php echo __( 'Add a Checkbox Input with effect on the Price to the Form' , 'easyReservations' ); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "error" || x == "date-from" || x == "date-to" || x == "email" || x == "rooms" || x == "message" || x == "thename"){
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<a href="javascript:AddOne()"><b>Add</b></a>';
				if(x == "error"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add the Output of wrong Inputs or Unavailable Days to the end of the Form' , 'easyReservations' ); ?></div><br>';
				} else if(x == "date-from"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add a Input for the Arrival Date with the Date-Picker to the end of the Form' , 'easyReservations' ); ?></div><br>';
				} else if(x == "email"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add a Input for the eMail to the end of the Form' , 'easyReservations' ); ?></div><br>';
				} else if(x == "date-to"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add a Input for the Departure Date with the Date-Picker to the end of the Form' , 'easyReservations' ); ?></div><br>';
				} else if(x == "rooms"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add a Select of all Rooms to the end of the Form' , 'easyReservations' ); ?></div><br>';
				} else if(x == "message"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add a Input for a Message to the end of the Form' , 'easyReservations' ); ?></div><br>';
				} else if(x == "thename"){
					var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add a Input for the Name to the end of the Form' , 'easyReservations' ); ?></div><br>';
				}
			document.getElementById("Helper").innerHTML = Help;

			document.getElementById("Text4").innerHTML += Output;

		} else if (x == "submit"){

			var Output  = '<input type="text" name="eins" id="eins" value="Name">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Type in Value of Submit button' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<a href="javascript:AddTwo()"><b>Add</b></a>';
			document.getElementById("Text4").innerHTML += Output;

		} else if (x == "persons"){

			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="Select">Select</option><option value="Text">Text</option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Select Type of Person Input' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

		} else if (x == "hidden") {

			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="room">Room</option><option value="offer">Offer</option><option value="from">Arrival Date</option><option value="to">Departure Date</option><option value="persons">Persons</option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select Type of Hidden Input' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for fixing an Information to the form & hide it from Guest' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Room</b> <?php echo __( 'Fix a Room to the Form; dont use it with [rooms] in the same Form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Offer</b> <?php echo __( 'Fix an Offer to the Form; dont use it with [offers] in the same Form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Arrival Date</b> <?php echo __( 'Fix an Arrival Date to the Form; dont use it with [date-from] in the same Form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Departure Date</b> <?php echo __( 'Fix a Departure Date to the Form; dont use it with [date-to] in the same Form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Persons</b> <?php echo __( 'Fix an amount of Persons to the Form; dont use it with [persons] in the same Form' , 'easyReservations' ); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

		} else if (x == "offers") {

			var Output  = '<select id="eins" name="eins">';
			Output += '<option value="select">Select</option><option value="box">Box</option></select>';
			document.getElementById("Text").innerHTML += Output;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select Type of Offer' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b>Select</b> <?php echo __( 'A Drop-Down Select of all Offers' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><b>Box</b> <?php echo __( 'A Box as promt if the Guest was redirected by an Offer Post' , 'easyReservations' ); ?></i>';
				Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Offer Input to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			var Output  = '&nbsp;<a href="javascript:AddTwo()"><b>Add</b></a>';
			document.getElementById("Text4").innerHTML += Output;

		}
	} else if(thetext2 == false){
		if (x == "textarea" || x == "text" || x == "check"){
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name">';
			document.getElementById("Text2").innerHTML += Output;
		
			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Type in a Name for the' , 'easyReservations' ); ?> <span style="text-transform:capitalize">' + x + '</span> <?php echo __( 'Input you want to add' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "checkbox"){
			end = 4;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name"><input type="text" name="drei" id="drei" value="Value">';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a Name for the Checkbox' , 'easyReservations' ); ?></b>';
			Help += '<br><b>2. <?php echo __( 'Type in a Value for the Checkbox' , 'easyReservations' ); ?></b>',
			Help += '<br> &emsp; <?php echo __( 'The Value has to match ' , 'easyReservations' ); ?><b><span style="color:#FF0000">selectName</span>:<span style="color:#16A039">Price</span></b>';
			Help += '<br> &emsp; <i><span style="color:#FF0000">select Name</span>:<span style="color:#16A039">10</span> // <?php echo __( 'if Guest checks the Checkbox the Price will increase by' , 'easyReservations' ); echo ' '.reservations_format_money(10).' &'.get_option("reservations_currency"); ?>;</i>'
			Help += '<br> &emsp; <i><span style="color:#FF0000">want Breakfast</span>:<span style="color:#16A039">25.24</span> // <?php echo __( 'if Guest checks the Checkbox the Price will increase by' , 'easyReservations' ); echo ' '.reservations_format_money(25.24).' &'.get_option("reservations_currency"); ?>;</i>';
			Help += '<br> &emsp; <i><span style="color:#FF0000">no Laundry</span>:<span style="color:#16A039">-30.36</span> // <?php echo __( 'if Guest checks the Checkbox the Price will decrease by' , 'easyReservations' ); echo ' '.reservations_format_money(-30.36).' &'.get_option("reservations_currency"); ?>;</i>';
			Help += '<br><b>3. <?php echo __( 'Click on "Add" to add the custom Price Checkbox to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;
			
			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "select" || x == "radio") {
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name" onClick="jumpto(document.form1.zwei.value);">';
			document.getElementById("Text2").innerHTML += Output;
			if(first == "price"){
				var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a Name for the Drop-Down Select' , 'easyReservations' ); ?></b>';
				Help += '<br><b>2. <?php echo __( 'Type in the Options Field for the' , 'easyReservations' ); ?>  ' + x + ' Input</b>',
				Help += '<br> &emsp; <?php echo __( 'The Options Field has to match ' , 'easyReservations' ); ?><b><span style="color:#FF0000">selectName</span>:<span style="color:#16A039">Price</span>,<span style="color:#FF0000">selectName2</span>:<span style="color:#16A039">Price2</span>,<span style="color:#FF0000">selectName3</span>:<span style="color:#16A039">Price3</span> ...</b>';
				Help += '<br> &emsp; <i><span style="color:#FF0000">select Name</span>:<span style="color:#16A039">10</span> <?php echo __( 'if Guest selects "selectname" the Price will increase by' , 'easyReservations' ); echo ' '.reservations_format_money(10).' &'.get_option("reservations_currency"); ?>;</i>'
				Help += '<br> &emsp; <i><span style="color:#FF0000">no Breakfast</span>:<span style="color:#16A039">-30.36</span>,<span style="color:#FF0000">wantBreakfast</span>:<span style="color:#16A039">25.24</span> <?php echo __( 'if Guest selects "noBreakfast" the Price will decrease by' , 'easyReservations' ); echo ' '.reservations_format_money(-30.36).' &'.get_option("reservations_currency"); ?>;</i>';
				Help += '<br> &emsp; <i><span style="color:#FF0000">no Laundry</span>:<span style="color:#16A039">0</span>,<span style="color:#FF0000">yes Laundry</span>:<span style="color:#16A039">10</span>,<span style="color:#FF0000">bestLaundry</span>:<span style="color:#16A039">20</span> <?php echo __( 'if Guest selects "noLaundry" the Price wont change' , 'easyReservations' );?></i>';
				Help += '<br><b>3. <?php echo __( 'Click on "Add" to add the custom Price' , 'easyReservations' ); ?> ' + x + ' <?php echo __( 'Input to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			} else if(first == "custom"){
				var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a Name for the' , 'easyReservations' ); ?> ' + x + ' Input</b>';
				Help += '<br><b>2. <?php echo __( 'Type in the Options Field the Drop-Down Select' , 'easyReservations' ); ?></b>',
				Help += '<br> &emsp; <?php echo __( 'The Options Field has to match ' , 'easyReservations' ); ?><b><span style="color:#FF0000">select Name</span>,<span style="color:#FF0000">select Name 2</span>,<span style="color:#FF0000">selectName 3</span> ...</b>';
				Help += '<br> &emsp; <i><span style="color:#FF0000">selectName</span> <?php echo __( 'selected Option will be saved' , 'easyReservations' ); ?>;</i>'
				Help += '<br> &emsp; <i><span style="color:#FF0000">Yes Sir!</span>,<span style="color:#FF0000">No</span> <?php echo __( 'selected Option will be saved' , 'easyReservations' ); ?>;</i>';
				Help += '<br> &emsp; <i><span style="color:#FF0000">Yes of course</span>,<span style="color:#FF0000">No</span>,<span style="color:#FF0000">Maybe Later</span> <?php echo __( 'selected Option will be saved' , 'easyReservations' );?></i>';
				Help += '<br><b>3. <?php echo __( 'Click on "Add" to add the custom ' , 'easyReservations' ); ?> ' + x + ' <?php echo __( 'Input to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			}

			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "room") {
			end = 3;
			var Output  = '<select id="zwei" name="zwei"><?php echo $roomsoptions; ?></select>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select a Room' , 'easyReservations' ); ?></b>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the hidden Room Field to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "offer") {
			end = 3;
			var Output  = '<select id="zwei" name="zwei"><?php echo $offeroptions; ?></select>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select an Offer' , 'easyReservations' ); ?></b>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the hidden Offer Field to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "Select") {
			end = 3;
			var Output  = '<select name="zwei" id="zwei"><?php echo $personsoptions; ?></select>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select maximum Number of Selects' , 'easyReservations' ); ?></b>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Persons Input to the end of the Form' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "Text") {
			end = 2;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Click on "Add" to add the Person Input as Text Field to the end of the Form' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "persons") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Amount">';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold">1. <?php echo __( 'Fill in the Amount of Persons you want to fix' , 'easyReservations' ); ?>';
			Help += '<br>2. <?php echo __( 'Click on "Add" to add the Persons as Hidden Field to the end of the Form' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "from") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="dd.mm.yyyy">';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold">1. <?php echo __( 'Fill in the Date of the Arrival Date you want to fix' , 'easyReservations' ); ?>';
			Help += '<br>2. <?php echo __( 'Click on "Add" to add the Arrival Date as Hidden Field to the end of the Form' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "to") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="dd.mm.yyyy">';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold">1. <?php echo __( 'Fill in the Date of the Departure Date you want to fix' , 'easyReservations' ); ?>';
			Help += '<br>2. <?php echo __( 'Click on "Add" to add the Departure Date as Hidden Field to the end of the Form' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		}
	}else if(thetext3 == false){
		if (x == "Name") {
			end = 5;
			var Output  = '<input type="text" name="drei" id="drei" value="Options">';
			document.getElementById("Text3").innerHTML += Output;
			thetext3 = true;
		}
	}

	if (end == 1) {
		var Output  = '&nbsp;<a href="javascript:AddOne()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 2) {
		var Output  = '&nbsp;<a href="javascript:AddTwo()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 3) {
		var Output  = '&nbsp;<a href="javascript:AddThree()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 4) {
		var Output  = '&nbsp;<a href="javascript:AddFour()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 5) {
		var Output  = '&nbsp;<a href="javascript:AddFours()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
}
</script>
<?php } elseif($settingpage=="email"){

$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";

$emailstandart1="New Reservation on Blogname from<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";

$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";

$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";

$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
Reservation ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
Reservation ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
$emailstandart6="Reservation got edited by Guest.<br><br>
New Reservation Details:<br>
Reservation ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";

?>
	<form method="post" action="admin.php?page=settings&site=email"  id="reservations_email_settings" name="reservations_email_settings">
		<input type="hidden" name="action" value="reservations_email_settings"/>
		<input type="hidden" value="<?php echo $emailstandart0; ?>" name="inputemail0">
		<input type="hidden" value="<?php echo $emailstandart1; ?>" name="inputemail1">
		<input type="hidden" value="<?php echo $emailstandart2; ?>" name="inputemail2">
		<input type="hidden" value="<?php echo $emailstandart3; ?>" name="inputemail3">
		<input type="hidden" value="<?php echo $emailstandart4; ?>" name="inputemail4">
		<input type="hidden" value="<?php echo $emailstandart5; ?>" name="inputemail5">
		<input type="hidden" value="<?php echo $emailstandart6; ?>" name="inputemail6">
		<table style="width:99%;" cellspacing="0">
			<tr style="width:60%;" cellspacing="0">
				<td valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Standard Sendmail' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail0();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_sendmail_subj" style="width:60%;" value="<?php echo $reservations_email_sendmail_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_sendmail_msg" style="width:99%;height:120px;"><?php echo $reservations_email_sendmail_msg; ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on new Reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Admin' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail1();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_admin_subj" style="width:60%;" value="<?php echo $reservations_email_to_admin_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_admin_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_admin_msg; ?></textarea></td>
				</tr>	
				<tr valign="top">
					<td><div class="fakehr"></td>
				</tr>	
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail4();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_subj" style="width:60%;" value="<?php echo $reservations_email_to_user_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_user_msg; ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on approve' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail2();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_userapp_subj" style="width:60%;" value="<?php echo $reservations_email_to_userapp_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userapp_msg"  id="reservations_email_to_userapp_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_userapp_msg; ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on reject' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail3();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_userdel_subj" style="width:60%;" value="<?php echo $reservations_email_to_userdel_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userdel_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_userdel_msg; ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on user-edit' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Admin' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail6();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_admin_edited_subj" style="width:60%;" value="<?php echo $reservations_email_to_admin_edited_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_admin_edited_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_admin_edited_msg; ?></textarea></td>
				</tr>	
				<tr valign="top">
					<td><div class="fakehr"></td>
				</tr>	
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to Guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail5();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_edited_subj" style="width:60%;" value="<?php echo $reservations_email_to_user_edited_subj; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_edited_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_user_edited_msg; ?></textarea></td>
				</tr>	
			</tbody>
		</table>
			<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>">
		</td>
		<td  style="width:1%;"></td>
		<td  style="width:39%;"  valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Shortcodes' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;">
						<div class="explainbox">
							<p><code class="codecolor">&lt;br&gt;</code> <i><?php printf ( __( 'wordwrap' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[adminmessage]</code> <i><?php printf ( __( 'message from admin on approve/reject/sendmail' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[ID]</code> <i><?php printf ( __( 'ID' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[thename]</code> <i><?php printf ( __( 'name' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[arrivaldate]</code> <i><?php printf ( __( 'arrival date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[departuredate]</code> <i><?php printf ( __( 'departure date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[nights]</code> <i><?php printf ( __( 'nights to stay' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[persons]</code> <i><?php printf ( __( 'number of guests' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[rooms]</code> <i><?php printf ( __( 'choosen room' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[offers]</code> <i><?php printf ( __( 'choosen offer' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[note]</code> <i><?php printf ( __( 'message from guest/admin note' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[price]</code> <i><?php printf ( __( 'shows price' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[customs]</code> <i><?php printf ( __( 'custom fields' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[editlink]</code> <i><?php printf ( __( 'link to user edit | set URL to edit Page first' , 'easyReservations' ));?></i></p>								
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		</td></tr></table>
		</form>
<?php } 
	if($settingpage=="about"){ ?>
	<table style="width:99%;" cellspacing="0"><tr><td style="width:60%;" style="width:49%;"  valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" >
			<thead>
				<tr>
					<th> <?php printf ( __( 'Changelog' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
						<div class="changebox"  align="left">
						<p><b style="font-size:13px">easyReservations Version 1.2</b><br>
								<b>* NEW FUNCTION</b> <i>New Overview</i><br>
								<b>* NEW FUNCTION</b> <i>Overview Cells are clickable on add/edit to select room/date with effect</i><br>
								<b>* NEW FUNCTION</b> <i>Regular Guests, set emails of guests and the reservations get highlighted in the reservations table</i><br>
								<b>* NEW FUNCTION</b> <i>Admin Theme System</i><br>
								<b>* NEW FUNCTION</b> <i>Sendmail to Guet beside of approve/reject</i><br>
								<b>* ADDED</b> <i>Hide/Show Rooms in Overview</i><br>
								<b>* ADDED</b> <i>Datepicker to select Startdate Overview</i><br>
								<b>* ADDED</b> <i>Validate Prices</i><br>
								<b>* ADDED</b> <i>Statistics to Reservation Frontdesk</i><br>
								<b>* ADDED</b> <i>The first Theme "Grey"</i><br>
								<b>* ADDED</b> <i>Check Rooms availability on add, edit, approve. no more double reservations in same time and room.</i><br>
								<b>* ADDED</b> <i>Add Reservation to Reservation Dashboard</i><br>
								<b>* ADDED</b> <i>Paid changed from yes/no in the amount of paid money. If paid money = price the reservation is "paid"</i><br>
								<b>* ADDED</b> <i>Set/Fix Price and Paid amount at add/edit</i><br>
								<b>* ADDED</b> <i>Reservation time information for reservations</i><br>
								<b>* REVAMP</b> <i>Rewrote of the price calculation</i><br>
								<b>* FIXED</b> <i>old reservations count</i><br>
								<b>* FIXED</b> <i>Overview is visible without Reservations now</i><br>
								<b>* FIXED</b> <i>Bugs in Orderby and Filter at Reservations Table</i><br>
								<b>* FIXED</b> <i>private Posts can be Room/Offer too</i><br>
								<b>* FIXED</b> <i>allow spaces in custom &amp; price field options</i></p>
								<div class="fakehr"></div>
						<p><b style="font-size:13px">easyReservations Version 1.1.4</b><br>
								<b>* FIXED</b> <i>Installation</i><br>
								<b>* FIXED</b> <i>more then five Rooms/Offers</i><br>
								<b>* FIXED</b> <i>Resource thumbnail bug for some themes</i></p>
								<div class="fakehr"></div>
						<p><b style="font-size:13px">easyReservations Version 1.1.3</b><br>
								<b>* NEW FUNCTION</b> <i>Price Fields in Form, view and edit; Very similar to custom Fields but with impact on the Price</i><br>
								<b>* NEW FUNCTION</b> <i>Price Filters can have Units of Time as Condition now; for example January, Friday, Weekend, CW 23, 2012, Quarter 1</i><br>
								<b>* NEW FUNCTION</b> <i>Add Rooms or Offers directly from Resources</i><br>
								<b>* REVAMP</b> <i>fully rewrote of the Overview; just one querie per row left</i><br>
								<b>* REVAMP</b> <i>statistic querys</i><br>
								<b>* ADDED</b> <i>Custom &amp; Price Fields can now have Spaces in the Options Name.</i><br>
								<b>* ADDED</b> <i>Priority System for Price Filters with Units of Time as Condition</i><br>
								<b>* ADDED</b> <i>Help for Form Field and Room/Offer Filter adding</i><br>
								<b>* ADDED</b> <i>Custom fields &amp; error preventing to "add Reservation"</i><br>
								<b>* ADDED</b> <i>Roomcount and "Offer Box Informations" to Resource editing</i><br>
								<b>* ADDED</b> <i>Hidden fields can be date-from, date-to or persons too</i><br>
								<b>* STYLE</b> <i>add Reservation</i><br>
								<b>* FIXED</b> <i>many speed optimizations and clean-ups</i><br>
								<b>* FIXED</b> <i>Overview is visible without Reservations now</i><br>
								<b>* FIXED</b> <i>Bugs in Orderby and Filter at Reservations Table</i><br>
								<b>* FIXED</b> <i>private Posts can be Room/Offer too</i><br>
								<b>* FIXED</b> <i>allow spaces in custom &amp; price field options</i></p>
								<div class="fakehr"></div>
						<p><b style="font-size:13px">easyReservations Version 1.1.2</b><br>
								<b>* NEW FUNCTION</b> <i>Custom Fields in Form, view and edit; Can be different for each Reservation; delete-, edit-  &amp;  addable for each Reservation</i><br>
								<b>* NEW FUNCTION</b> <i>Detaile Price Calculation on view, edit, reject and approve</i><br>
								<b>* NEW FUNCTION</b> <i>Resources Page to display Rooms and Offers; better Grounprice &amp; Filter add script</i><br>
								<b>* ADDED</b> <i>On approve the Prices of Reservations get set and are directly editable.</i><br>
								<b>* ADDED</b> <i>Mark approved Reservations as paid.</i><br>
								<b>* ADDED</b> <i>Click on Reservations in Overview links to edit them now; Only on Main Site, reject and view. </i><br>
								<b>* ADDED</b> <i>New Filter [pers] to change Price if more persons reserve</i><br>
								<b>* ADDED</b> <i>[price] and [customs] to emails</i><br>
								<b>* ADDED</b> <i>Price formatting</i><br>
								<b>* ADDED</b> <i>Form Validator</i><br>
								<b>* ADDED</b> <i>All Reservations Group in Table</i><br>
								<b>* ADDED</b> <i>Florin Currency</i><br>
								<b>* STYLE</b> <i>Errors in Form at reservating looks better now</i><br>
								<b>* STYLE</b> <i>Detailed Statistics</i><br>
								<b>* FIXED</b> <i>Overview is visible without Reservations now</i><br>
								<b>* FIXED</b> <i>General Settings again</i><br>
								<b>* FIXED</b> <i>Name Bug in edit</i><br>
								<b>* FIXED</b> <i>Price Calculation</i><br>
								<b>* FIXED</b> <i>Stay Filter</i><br>
								<b>* FIXED</b> <i>Current Form is Big in Settings</i><br>
								<b>* FIXED</b> <i>Newest Reservation is first on Pending's</i><br>
								<b>* FIXED</b> <i>Filter Pagination Bug on Reservation Table</i><br>
								<b>* FIXED</b> <i>empty Categories selectable for Room/Offer Category</i><br>
								<b>* FIXED</b> <i>Address, Message and Phone fields can be deleted from Forms without getting error on reservating; Other types of fields are necesarry</i><br>
								<b>* DELETED</b> <i>Address and Phone from mySQL Database, use custom text fields insted. All Datas from old Reservations will be save.</i></p>
								<div class="fakehr"></div>
						<p><b style="font-size:13px">easyReservations Version 1.1.1</b><br>
								<b>* ADDED</b> <i>Hidden Field from Form works for Offers and Rooms now</i><br>
								<b>* ADDED</b> <i>Select needed Permissions for Reservations Admin</i><br>
								<b>* FIXED</b> <i>mouseOver in Overview</i><br>
								<b>* FIXED</b> <i>Datepicker in Edit</i><br>
								<b>* FIXED</b> <i>Upgrade Script. Everythink should be fine now.</i><br>
								<b>* FIXED</b> <i>General Settings</i></p>
								<div class="fakehr"></div>
						<p><b style="font-size:13px">easyReservations Version 1.1</b><br>
								<b>* NEW FUNCTION</b> <i>Filters! Each Room or Offer can now have unlimeted Filters for more flexiblity. Price, Availibility, and Discount for longer Stays or recoming Guests.</i><br>
								<b>* NEW FUNCTION</b> <i>The Form is very customizable now! Can have unlimited Forms, Forms for just one Room and edit the Style of them very easy.</i><br>
								<b>* NEW FUNCTION</b> <i>eMails are customizable now!</i><br>
								<b>* NEW FUNCTION</b> <i>Statistis! Starts with four Charts, more to come.</i><br>
								<b>* ADDED</b> <i>Overview is Clickable when approve or edit! 1x Click on roomname for change the room; Doubleclick for reset; [edit] click on date to change them fast (no visual response)</i><br>
								<b>* ADDED</b> <i>Settings Tabs</i><br>
								<b>* ADDED</b> <i>Checking availibility from Room/Offer Avail Filters and if Room is empty</i><br>
								<b>* REVAMP</b> <i>Rewrote the Edit Part and added it to Main Site</i><br>
								<b>* REVAMP</b> <i>Settings</i><br>
								<b>* FIXED</b> <i>Order by Date in Reservation Table</i><br>
								<b>* FIXED</b> <i>Search Reservations</i><br>
								<b>* FIXED</b> <i>many other minor bugs</i><br>
								<b>* DELETED</b> <i>Seasons; unnecessary because of  new Filter System</i><br>
								<b>* DELETED</b> <i>Form Options; unnecessary because of new Form System</i></p>
								<div class="fakehr"></div>
						<p><b style="font-size:13px">easyReservations Version 1.0.1</b><br>
								<b>* ADDED</b> <i>function easyreservations_price_calculation($id) to calculate Price from Reservation ID.</i><br>
								<b>* REVAMP</b> <i>the Overview now uses 95% less mySQL Queries! Nice speed boost for Administration.</i><br>
								<b>* FIXED</b> <i>Box Style of Offers in Reservation Form will now work on every Permalink where the id or the slug is at the end. Thats on almost every Site.</i><br>
								<b>* FIXED</b> <i>Box Style of Offers in Reservation Form should display right on the most Themes now. If not, please sent Screenshot and Themename.</i><br>
								<b>* FIXED</b> <i>Room/Offer in Approve/Reject Reservation Mail to User is now translatable</i><br>
								<b>* FIXED</b> <i>German Language is working now</i></p>
						</div>
					</td>
				</tr>	
			</tbody>
		</table>
		</td><td style="width:1%;"></td><td style="width:39%;" style="width:49%;"  valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" >
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Links' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
						<div class="changebox"><p><a href="http://www.feryaz.de/dokumentation/"><?php printf ( __( 'Documentation' , 'easyReservations' ));?></a></p> <div class="fakehr"></div><p><a href="http://www.feryaz.de/suggestions/"><?php printf ( __( 'Suggest Ideas & Report Bugs' , 'easyReservations' ));?></a></p><div class="fakehr"> </div><p><a href="http://wordpress.org/extend/plugins/easyreservations/"><?php printf ( __( 'Wordpress Repostry' , 'easyReservations' ));?></a></p><div>
					</td>
				</tr>	
			</tbody>
		</table><br>
		<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Latest News' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;">
						<?php // import rss feed
						if(function_exists('fetch_feed')) {
						// fetch feed items
						$rss = fetch_feed('http://feryaz.de/feed/rss/');
						if(!is_wp_error($rss)) : // error check
							$maxitems = $rss->get_item_quantity(5); // number of items
							$rss_items = $rss->get_items(0, $maxitems);
						endif;
						// display feed items ?>
						<dl>
						<?php if($maxitems == 0) echo '<dt>Feed not available.</dt>'; // if empty
						else foreach ($rss_items as $item) : ?>

							<dt>
								<a href="<?php echo $item->get_permalink(); ?>" 
								title="<?php echo $item->get_date('j F Y @ g:i a'); ?>">
								<?php echo $item->get_title(); ?>
								</a>
							</dt>
							<dd>
								<?php echo $item->get_description(); ?>
							</dd>

						<?php endforeach; ?>
						</dl>
						<?php } ?>
					</td>
				</tr>	
			</tbody>
		</table><br><table class="<?php echo RESERVATIONS_STYLE; ?>" >
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Donate' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="EZGXTQHU6JSUL">
							<input type="image" src="https://www.paypalobjects.com/en_US/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
						</form>
					</td>
				</tr>	
			</tbody>
		</table><br>
	</tr>
</table>
<?php } ?>
</div>
<?php }  
?>