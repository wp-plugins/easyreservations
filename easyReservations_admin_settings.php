<?php
function reservation_settings_page() { //Set Settings
		global $wpdb;

		if(isset($_GET["form"])){
			$formnameget = $_GET['form'];
			$reservations_form=get_option("reservations_form_".$formnameget.""); $howload="easy_form ".$formnameget.""; 
		} else {
			$formnameget='';
			$reservations_form=get_option("reservations_form"); $howload="easy_form"; 
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

		if(isset($action) AND $action == "reservation_clean_database"){
			$promt='cleaned';
			$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < DATE(NOW()) AND approve != 'yes' ") );
			$prompt = '<div class="update"><p>'.__( 'Database cleaned' , 'easyReservations' ).'</p></div>';
		}

		if(isset($action) AND $action == "er_main_set"){
			//Set Reservation settings 
			$regguests = $_POST["regular_guests"];
			$easyReservationSyle = $_POST["reservations_style"];
			$reservations_price_per_persons = $_POST["reservations_price_per_persons"];
			$reservationss_support_mail = $_POST["reservations_support_mail"];
			$reservations_edit_url = $_POST["reservations_edit_url"];
			$reservations_edit_text = stripslashes($_POST["reservations_edit_text"]);
			if(isset($_POST["reservations_uninstall"])) $reservations_uninstall = 1; else $reservations_uninstall = 0;
			update_option("reservations_uninstall", $reservations_uninstall);
			update_option("reservations_style",$easyReservationSyle);
			update_option("reservations_regular_guests",$regguests);
			update_option("reservations_price_per_persons",$reservations_price_per_persons);
			update_option("reservations_support_mail",$reservationss_support_mail);
			update_option("reservations_edit_url",$reservations_edit_url);
			update_option("reservations_edit_text",$reservations_edit_text);	
			do_action( 'er_set_main_save' );

			//Set Currency
			$reservations_currency = $_POST["reservations_currency"];
			update_option("reservations_currency",$reservations_currency);
			$prompt = '<div class="updated"><p>'.__( 'General settings saved' , 'easyReservations' ).'</p></div>';
		}

		if(isset($action) AND $action == "reservations_email_settings"){//Set Reservation Mails

			if(isset($_POST["reservations_email_sendmail_check"])) $reservations_email_sendmail_check = 1; else $reservations_email_sendmail_check = 0;
			$reservations_email_sendmail = array(
				'msg' => stripslashes($_POST["reservations_email_sendmail_msg"]),
				'subj' => $_POST["reservations_email_sendmail_subj"],
				'active' => $reservations_email_sendmail_check
			);
			update_option("reservations_email_sendmail",$reservations_email_sendmail);

			if(isset($_POST["reservations_email_to_admin_check"])) $reservations_email_to_admin_check = 1; else $reservations_email_to_admin_check = 0;
			$reservations_email_to_admin = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_msg"]),
				'subj' => $_POST["reservations_email_to_admin_subj"],
				'active' => $reservations_email_to_admin_check
			);
			update_option("reservations_email_to_admin",$reservations_email_to_admin);

			if(isset($_POST["reservations_email_to_userapp_check"])) $reservations_email_to_userapp_check = 1; else $reservations_email_to_userapp_check = 0;
			$reservations_email_to_userapp = array( 
				'msg' => stripslashes($_POST["reservations_email_to_userapp_msg"]),
				'subj' => $_POST["reservations_email_to_userapp_subj"],
				'active' => $reservations_email_to_userapp_check
			);
			update_option("reservations_email_to_userapp",$reservations_email_to_userapp);

			if(isset($_POST["reservations_email_to_userdel_check"])) $reservations_email_to_userdel_check = 1; else $reservations_email_to_userdel_check = 0;
			$reservations_email_to_userdel = array(
				'msg' => stripslashes($_POST["reservations_email_to_userdel_msg"]), 
				'subj' => $_POST["reservations_email_to_userdel_subj"],
				'active' => $reservations_email_to_userdel_check
			);
			update_option("reservations_email_to_userdel",$reservations_email_to_userdel);
			
			if(isset($_POST["reservations_email_to_user_check"])) $reservations_email_to_user_check = 1; else $reservations_email_to_user_check = 0;
			$reservations_email_to_user = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_msg"]),
				'subj' => $_POST["reservations_email_to_user_subj"],
				'active' => $reservations_email_to_user_check
			);
			update_option("reservations_email_to_user",$reservations_email_to_user);

			if(isset($_POST["reservations_email_to_user_edited_check"])) $reservations_email_to_user_edited_check = 1; else $reservations_email_to_user_edited_check = 0;
			$reservations_email_to_user_edited = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_edited_msg"]),
				'subj' => $_POST["reservations_email_to_user_edited_subj"],
				'active' => $reservations_email_to_user_edited_check
			);
			update_option("reservations_email_to_user_edited",$reservations_email_to_user_edited);

			if(isset($_POST["reservations_email_to_admin_edited_check"])) $reservations_email_to_admin_edited_check = 1; else $reservations_email_to_admin_edited_check = 0;
			$reservations_email_to_admin_edited = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_edited_msg"]),
				'subj' => $_POST["reservations_email_to_admin_edited_subj"],
				'active' => $reservations_email_to_admin_edited_check
			);
			update_option("reservations_email_to_admin_edited",$reservations_email_to_admin_edited);

			if(isset($_POST["reservations_email_to_user_admin_edited_check"])) $reservations_email_to_user_admin_edited_check = 1; else $reservations_email_to_user_admin_edited_check = 0;
			$reservations_email_to_user_admin_edited = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_admin_edited_msg"]),
				'subj' => $_POST["reservations_email_to_user_admin_edited_subj"],
				'active' => $reservations_email_to_user_admin_edited_check
			);
			update_option("reservations_email_to_user_admin_edited",$reservations_email_to_user_admin_edited);

			$prompt = '<div class="updated"><p>'.__( 'eMail settings saved' , 'easyReservations' ).'</p></div>';
		}

		if(isset($action) AND $action  == "reservations_form_settings"){ // Change a form
			// Set form
			$reservations_form_value =$_POST["reservations_formvalue"];
			$formnamesgets = $_POST["formnamesgets"];
			if($formnamesgets==""){
				update_option("reservations_form", $reservations_form_value);
			} else {
				update_option('reservations_form_'.$formnamesgets.'', $reservations_form_value);
			}
			$prompt = '<div class="updated"><p>'.sprintf(__( 'Form%ssaved' , 'easyReservations' ), '<b> '.$formnamesgets.' </b>' ).'</p></div>';
			$reservations_form = $_POST["reservations_formvalue"];
		}

		if(isset($action) AND $action == "reservation_change_permissions"){ // Change a form
			if(current_user_can('manage_options')){
				$permissions = array('dashboard' => $_POST["easy-permission-dashboard"], 'resources' => $_POST["easy-permission-resources"], 'statistics' => $_POST["easy-permission-statistics"], 'settings' => $_POST["easy-permission-settings"]);
				update_option('reservations_main_permission', $permissions);
				$prompt = '<div class="updated"><p>'.__( 'Permissions changed' , 'easyReservations' ).'</p></div>';
			} else $prompt = '<div class="error"><p>'.__( 'Only admins can change the permissions for easyReservations' , 'easyReservations' ).'</p></div>';
		}

		if(isset($namtetodelete)){
			delete_option('reservations_form_'.$namtetodelete.'');
			$prompt = '<div class="updated"><p>'.sprintf(__( 'Form %s has been deleted' , 'easyReservations' ), '<b>'.$namtetodelete.'</b>' ).'</p></div>';
		}

		if(isset($action) AND $action == "reservations_form_add"){// Add form after check twice for stupid Users :D
			if($_POST["formname"]!=""){

				$formname0='reservations_form_'.$_POST["formname"];
				$formname1='reservations_form_'.$_POST["formname"].'_1';
				$formname2='reservations_form_'.$_POST["formname"].'_2';
				
				if(get_option($formname0)=="")add_option(''.$formname0.'', ' ', '', 'no' );
				elseif(get_option($formname1)=="") add_option(''.$formname1.'', ' ', '', 'no');
				else add_option(''.$formname2.'', ' ', '', 'no');
				$prompt = '<div class="updated"><p>'.sprintf(__( 'Form %s has been added' , 'easyReservations' ), '<b>'.$_POST["formname"].'</b>' ).'</p></div>';
			} else $prompt = '<div class="error"><p>'.__( 'Please enter a name for the form' , 'easyReservations' ).'</p></div>';
		}

		if($settingpage=="form"){//Get current form Options
			$forms = '';
			$ifformcurrent='class="current"';
			//form Options
		//$items3 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen $selectors $searchstr", '%' . like_escape($search) . '%')); // number of total rows in the database

			$formresult = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM ".$wpdb->prefix ."options WHERE option_name like '%1\$s' ", like_escape("reservations_form_") . '%'));
			foreach( $formresult as $result ){
				$formcutedname=str_replace('reservations_form_', '', $result->option_name);
				if($formcutedname!=""){
					if($formcutedname == $formnameget) $formbigcutedname='<b style="color:#000">'.$formcutedname.'</b>'; else $formbigcutedname = $formcutedname;
					$forms.=' | <a href="admin.php?page=reservation-settings&site=form&form='.$formcutedname.'">'.$formbigcutedname.'</a> <a href="admin.php?page=reservation-settings&site=form&deleteform='.$formcutedname.'"><img style="vertical-align:textbottom;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a>';
				}
			}
		}

		do_action( 'er_set_save' );

		if($settingpage=="email"){
			$ifemailcurrent='class="current"';
			$reservations_email_sendmail=get_option("reservations_email_sendmail");
			$reservations_email_to_admin=get_option("reservations_email_to_admin");
			$reservations_email_to_user=get_option("reservations_email_to_user");
			$reservations_email_to_user_edited=get_option("reservations_email_to_user_edited");
			$reservations_email_to_user_admin_edited=get_option("reservations_email_to_user_admin_edited");
			$reservations_email_to_admin_edited=get_option("reservations_email_to_admin_edited");
			$reservations_email_to_userapp=get_option("reservations_email_to_userapp");
			$reservations_email_to_userdel=get_option("reservations_email_to_userdel");
		}

?>
<script>
function addtext() {
	var newtext = document.reservations_form_settings.inputstandart.value;
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
function addtextforemail7() {
	var newtext = document.reservations_email_settings.inputemail7.value;
	document.reservations_email_settings.reservations_email_to_user_admin_edited_msg.value = newtext;
}
function resteText() {
	var newtext = document.reservations_form_settings.resetforrm.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
</script>
<h2>
	<?php echo __( 'Reservations Settings' , 'easyReservations' );?>
</h2>
<?php if(isset($prompt)) echo $prompt; ?>
<div id="wrap">

<div class="tabs-box widefat" style="margin-bottom:10px">
	<ul class="tabs">
		<li><a <?php if(isset($ifgeneralcurrent)) echo $ifgeneralcurrent; ?> href="admin.php?page=reservation-settings"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/pref.png"> <?php printf ( __( 'General' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifformcurrent)) echo $ifformcurrent; ?> href="admin.php?page=reservation-settings&site=form"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/form.png"> <?php printf ( __( 'Form' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifemailcurrent)) echo $ifemailcurrent; ?> href="admin.php?page=reservation-settings&site=email"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMails' , 'easyReservations' ));?></a></li>
		<?php do_action( 'er_set_tab_add' ); ?>
		<li><a <?php if(isset($ifaboutcurrent)) echo $ifaboutcurrent; ?> href="admin.php?page=reservation-settings&site=about"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/logo.png"> <?php printf ( __( 'About' , 'easyReservations' ));?></a></li>
	</ul>
</div>

<?php if($settingpage=="general"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GENERAL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Get current Options
	$reservations_price_per_persons = get_option("reservations_price_per_persons");
	$reservations_currency = get_option("reservations_currency");
	$reservation_support_mail = get_option("reservations_support_mail");
	$reservations_regular_guests = get_option('reservations_regular_guests');
	$permission_options=get_option("reservations_main_permission");
	$reservations_edit_url=get_option("reservations_edit_url");
	$reservations_edit_text=get_option("reservations_edit_text");
	$easyReservationSyle=get_option("reservations_style");
	$reservations_uninstall=get_option("reservations_uninstall");

	?>

<table cellspacing="0" style="width:99%">
	<tr cellspacing="0">
		<td style="width:70%;" valign="top" >
	<form method="post" action="admin.php?page=reservation-settings"  id="er_main_set">
		<input type="hidden" name="action" value="er_main_set">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;">
				<thead>
					<tr>
						<th style="width:45%;"> <?php printf ( __( 'Reservation settings' , 'easyReservations' ));?> </th>
						<th style="width:55%;"> </th>
					</tr>
				</thead>
				<tbody style="border:0px">
					<tr valign="top" style="border:0px">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'Reservation support mail' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Mail for reservations' , 'easyReservations' ));?>" name="reservations_support_mail" value="<?php echo $reservation_support_mail;?>" style="width:50%"></td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/calc.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td><select name="reservations_price_per_persons" title="<?php printf ( __( 'select type of price calculation' , 'easyReservations' ));?>"><?php if($reservations_price_per_persons == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><?php } ?><?php if($reservations_price_per_persons == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/dollar.png"> <?php printf ( __( 'Currency sign' , 'easyReservations' ));?></td>
						<td><select name="reservations_currency" title="<?php printf ( __( 'Select currency' , 'easyReservations' ));?>"><?php
								$currencys = array( 
									array(__( 'Euro' , 'easyReservations' ), '#8364'), 
									array(__( 'Dollar' , 'easyReservations' ), '#36'), 
									array(__( 'Yen' , 'easyReservations' ), '#165'), 
									array(__( 'Cent' , 'easyReservations' ), '#162'), 
									array(__( 'Indian rupee' , 'easyReservations' ), '#8377'), 
									array(__( 'Florin' , 'easyReservations' ), '#402'), 
									array(__( 'Pound' , 'easyReservations' ), '#163'), 
									array(__( 'Hongkong Dollar' , 'easyReservations' ), '#20803') , 
									array(__( 'Tenge' , 'easyReservations' ), '#8376'),
									array(__( 'Kip' , 'easyReservations' ), '#8365') , 
									array(__( 'Colon' , 'easyReservations' ), '#8353'),
									array(__( 'Guarani ' , 'easyReservations' ), '#8370'),
									array(__( 'Bengali Rupee' , 'easyReservations' ), '#2547'),
									array(__( 'Gujarati  Rupee' , 'easyReservations' ), '#2801'),
									array(__( 'Tamil Rupee' , 'easyReservations' ), '#3065'),
									array(__( 'Thai Baht' , 'easyReservations' ), '#3647'),
									array(__( 'Khmer Riel' , 'easyReservations' ), '#6107'),
									array(__( 'Square Yuan' , 'easyReservations' ), '#13136'),
									array(__( 'China Yuan' , 'easyReservations' ), '#20803'),
									array(__( 'Austral' , 'easyReservations' ), '#8371'),
									array(__( 'Hryvnia' , 'easyReservations' ), '#8372'),
									array(__( 'Cedi' , 'easyReservations' ), '#8373'),
									array(__( 'Tugril' , 'easyReservations' ), '#8366'),
									array(__( 'Drachma' , 'easyReservations' ), '#8367'),
									array(__( 'Dong' , 'easyReservations' ), '#8363'),
									array(__( 'Naira' , 'easyReservations' ), '#8358'),
									array(__( 'Mill' , 'easyReservations' ), '#8357'),
									array(__( 'Cruzeiro' , 'easyReservations' ), '#8354'),
									array(__( 'Omani Rial' , 'easyReservations' ), '#65020'),
									array(__( 'Won' , 'easyReservations' ), '#65510'),
									array(__( 'Philippine Peso' , 'easyReservations' ), '#608'),
									array(__( 'Philippine Peso 2nd' , 'easyReservations' ), '#80;&#104;&#11'),
									array(__( 'Brazilian Real' , 'easyReservations' ), '#986'),
									array(__( 'Brazilian Real 2nd' , 'easyReservations' ), '#82;&#36'),
									array(__( 'Czech Koruna' , 'easyReservations' ), '#75;&#269'),
									array(__( 'Danish Krone' , 'easyReservations' ), '#107;&#114'),
									array(__( 'Israeli Sheqel' , 'easyReservations' ), '#122;&#322'),
									array(__( 'Panamanian Balboa' , 'easyReservations' ), '#66;&#47;&#46'),
									array(__( 'Egyptian Pound' , 'easyReservations' ), '#163'),
									array(__( 'Romanian Leu' , 'easyReservations' ), '#108;&#101;&#1'),
									array(__( 'Russian Rouble' , 'easyReservations' ), '#1088;&#1091'),
								); 

								foreach($currencys as $currenc){
									if($currenc[1] == $reservations_currency) $select = ' selected="selected" '; else $select = '';
									echo '<option '.$select.' value="'.$currenc[1].'">'.$currenc[0].' &'.$currenc[1].';</option>';										
								}?></select>
						</td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/background.png"> <?php printf ( __( 'Style' , 'easyReservations' ));?></td>
						<td>
							<select name="reservations_style" title="<?php printf ( __( 'Select style of admin panel' , 'easyReservations' ));?>">
								<option value="widefat" <?php if($easyReservationSyle=='widefat' OR RESERVATIONS_STYLE=='widefat') echo 'selected'; ?>><?php printf ( __( 'Wordpress' , 'easyReservations' ));?></option>
								<option value="greyfat" <?php if($easyReservationSyle=='greyfat' OR RESERVATIONS_STYLE=='greyfat') echo 'selected'; ?>><?php printf ( __( 'Grey' , 'easyReservations' ));?></option>
							</select>
						</td>
					<tr valign="top" style="border:0px">
						<td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/database.png"> <?php printf ( __( 'Uninstall' , 'easyReservations' ));?></td>
						<td><input type="checkbox" name="reservations_uninstall" value="1" <?php echo checked($reservations_uninstall, 1); ?>> <?php printf ( __( 'Delete settings, reservations and resources' , 'easyReservations' ));?></td>
					</tr>
					</tr>
				</tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px">
				<thead>
					<tr>
						<th> <?php printf ( __( 'User-edit settings' , 'easyReservations' ));?> </th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'To let users edit their reservations on your site add a page or post with the shortcode' , 'easyReservations' ));?>:</i> <code>[easy_edit]</code>
						</td>
					</tr>
					<tr class="alternate">
						<td>
							&nbsp;<b><?php printf ( __( 'URL to edit site' , 'easyReservations' ));?></b>: <input type="text" title="<?php printf ( __( 'URL to edit site' , 'easyReservations' ));?>" name="reservations_edit_url" value="<?php echo $reservations_edit_url;?>" style="width:50%">
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'This text should explain your guest the process of editing his reservation' , 'easyReservations' ));?>:</i>
							<textarea name="reservations_edit_text" style="width:100%;height:80px;margin-top:4px"><?php echo $reservations_edit_text; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<?php do_action( 'er_set_main_out' ); ?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px">
				<thead>
					<tr>
						<th> <?php printf ( __( 'Important Guests' , 'easyReservations' ));?> </th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'Enter emails of important guests; seperated by comma. Reservations with this email will be highlighted.' , 'easyReservations' ));?></i>
							<textarea name="regular_guests" style="width:100%;height:80px;margin-top:5px;"><?php echo $reservations_regular_guests; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>" onclick="document.getElementById('er_main_set').submit(); return false;" style="margin-top:7px;" class="easySubmitButton-primary" >
		</form>
			</td><td style="width:1%;" valign="top">
			</td><td style="width:29%;" valign="top">
				<form method="post" action="admin.php?page=reservation-settings" id="reservation_clean_database">
				<input type="hidden" name="action" value="reservation_clean_database" id="reservation_clean_database">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Clean Database' , 'easyReservations' ));?><input type="button" onclick="document.getElementById('reservation_clean_database').submit(); return false;" style="float:right;" title="" class="easySubmitButton-secondary" value="<?php printf ( __( 'Clean' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/database.png"> <?php printf ( __( 'Delete all unapproved Old Reservations' , 'easyReservations' ));?>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
				<form method="post" action="admin.php?page=reservation-settings" id="reservation_change_permissions">
				<input type="hidden" name="action" value="reservation_change_permissions" id="reservation_change_permissions">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th colspan="2"> <?php printf ( __( 'Change Permissions' , 'easyReservations' ));?><input type="button" onclick="document.getElementById('reservation_change_permissions').submit(); return false;" class="easySubmitButton-secondary" style="float:right;" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
								<tr class="alternate">
									<td><?php echo __( 'Dashboard' , 'easyReservations' );?></td>
									<td><select name="easy-permission-dashboard" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['dashboard']); ?></select></td>
								</tr>
								<tr>
									<td><?php printf ( __( 'Resources' , 'easyReservations' ));?></td>
									<td><select name="easy-permission-resources" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['resources']); ?></select></td>
								</tr>
								<tr class="alternate">
									<td><?php printf ( __( 'Statistics' , 'easyReservations' ));?></td>
									<td><select name="easy-permission-statistics" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['statistics']); ?></select></td>
								</tr>
								<tr valign="top">
									<td><?php printf ( __( 'Settings' , 'easyReservations' ));?></td>
									<td><select name="easy-permission-settings" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['settings']); ?></select></td>
								</tr>
						</tbody>
					</table>
				</form>
				<?php do_action( 'er_set_main_side_out' ); ?>
		</td>
	</tr>
</table>
<br>

<?php } elseif($settingpage=="form"){ 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + FORM SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$roomsoptions = reservations_get_room_options();
	$offeroptions =  reservations_get_offer_options();
	add_action('admin_print_footer_scripts',  'easy_add_my_quicktags'); //add buttons to quicktag
	?><script>
			function setDefaultForm(){
				var Default = '[error]\n';
					Default += '<h1>Reserve now!</h1>\n';
					Default += '<p>General informations</p>\n\n';
					Default += '<label>From\n';
					Default += '<span class="small">When do you come?</span>\n';
					Default += '</label>[date-from]\n\n';

					Default += '<label>To\n';
					Default += '<span class="small">When do you go?</span>\n';
					Default += '</label>[date-to]\n\n';

					Default += '<label>Room\n';
					Default += '<span class="small">Where you want to sleep?</span>\n';
					Default += '</label>[rooms]\n\n';

					Default += '<label>Offer\n';
					Default += '<span class="small">Do you want an offer?</span>\n';
					Default += '</label>[offers select]\n\n';

					Default += '<label>Persons\n';
					Default += '<span class="small">How many guests?</span>\n';
					Default += '</label>[persons Select 1 10]\n\n';

					Default += '<label>Children&rsquo;s\n';
					Default += '<span class="small">With children&rsquo;s?</span>\n';
					Default += '</label>[childs Select 0 10]\n\n';

					Default += '<p>Personal informations</p>\n\n';

					Default += '<label>Name\n';
					Default += '<span class="small">Whats your name?</span>\n';
					Default += '</label>[thename]\n\n';

					Default += '<label>eMail\n';
					Default += '<span class="small">Whats your email?</span>\n';
					Default += '</label>[email]\n\n';

					Default += '<label>Phone\n';
					Default += '<span class="small">Your phone number?</span>\n';
					Default += '</label>[custom text Phone *]\n\n';

					Default += '<label>Street\n';
					Default += '<span class="small">Your street?</span>\n';
					Default += '</label>[custom text Street *]\n\n';

					Default += '<label>Postal code\n';
					Default += '<span class="small">Your postal code?</span>\n';
					Default += '</label>[custom text PostCode *]\n\n';

					Default += '<label>City\n';
					Default += '<span class="small">Your city?</span>\n';
					Default += '</label>[custom text City *]\n\n';

					Default += '<label>Country\n';
					Default += '<span class="small">Your country?</span>\n';
					Default += '</label>[country]\n\n';

					Default += '<label>Message\n';
					Default += '<span class="small">Any comments?</span>\n';
					Default += '</label>[message]\n\n';

					Default += '<label>Captcha\n';
					Default += '<span class="small">Type in code</span>\n';
					Default += '</label>[captcha]\n';
					Default += '[show_price]\n\n';

					Default += '<div style="text-align:center;">[submit Send]</div>';
				document.reservations_form_settings.reservations_formvalue.value = Default;
			}
		</script><style>input[type=text] { margin-top: 2px;}</style>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;">
			<thead>
				<tr>
					<th style="width:45%;"> <?php printf ( __( 'Reservation Form' , 'easyReservations' ));?></th>
					<th style="width:55%;"></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top" class="alternate">
					<td style="width:60%;line-height: 2;" colspan="2"><?php if($formnameget==""){ ?><a href="admin.php?page=reservation-settings&site=form"><b style="color:#000;"><?php printf ( __( 'Standard' , 'easyReservations' ));?></b></a><?php } else { ?><a href="admin.php?page=reservation-settings&site=form"><?php printf ( __( 'Standard' , 'easyReservations' ));?></a><?php } ?><?php echo $forms; ?><div style="float:right"><form method="post" action="admin.php?page=reservation-settings&site=form"  id="reservations_form_add"><input type="hidden" name="action" value="reservations_form_add"/><input name="formname" type="text"><input type="button" onclick="document.getElementById('reservations_form_add').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Add' , 'easyReservations' ));?>"></form></div> </td>
				</tr>
				<tr valign="top">
					<td style="width:60%;line-height: 2;vertical-align: top;text-align:left">
					<form id="form1" name="form1">
						<div style="float: left;">
							<select name="jumpmenu" id="jumpmenu" onChange="jumpto(document.form1.jumpmenu.options[document.form1.jumpmenu.options.selectedIndex].value)">
								<option><?php printf ( __( 'Add Field' , 'easyReservations' ));?></option>
								<option value="error"><?php printf ( __( 'Display Errors' , 'easyReservations' ));?> [error]</option>
								<option value="show_price"><?php printf ( __( 'Dispay Price' , 'easyReservations' ));?> [show_price]</option>
								<option value="date-from"><?php printf ( __( 'Arrival Date' , 'easyReservations' ));?> [date-from]</option>
								<option value="date-to"><?php printf ( __( 'Departure Date' , 'easyReservations' ));?> [date-to]</option>
								<option value="persons"><?php printf ( __( 'Persons' , 'easyReservations' ));?> [persons]</option>
								<option value="childs"><?php printf ( __( 'Childs' , 'easyReservations' ));?> [childs]</option>
								<option value="thename"><?php printf ( __( 'Name' , 'easyReservations' ));?> [thename]</option>
								<option value="email"><?php printf ( __( 'eMail' , 'easyReservations' ));?> [email]</option>
								<option value="message"><?php printf ( __( 'Message' , 'easyReservations' ));?> [message]</option>
								<option value="rooms"><?php printf ( __( 'Room' , 'easyReservations' ));?> [rooms]</option>
								<option value="offers"><?php printf ( __( 'Offer' , 'easyReservations' ));?> [offers]</option>
								<option value="country"><?php printf ( __( 'Country' , 'easyReservations' ));?> [country]</option>
								<option value="custom"><?php printf ( __( 'Custom Field' , 'easyReservations' ));?> [custom]</option>
								<option value="price"><?php printf ( __( 'Price Field' , 'easyReservations' ));?> [price]</option>
								<option value="hidden"><?php printf ( __( 'Hidden Field' , 'easyReservations' ));?> [hidden]</option>
								<option value="submit"><?php printf ( __( 'Submit Button' , 'easyReservations' ));?> [submit]</option>
							</select>
						</div>
						<div id="Text" style="float: left;"></div>
						<div id="Text2" style="float: left;"></div>
						<div id="Text3" style="float: left;"></div>
						<div id="Text4" style="float: left;"></div>
						<a href="javascript:resetform();" class="easySubmitButton-primary" style="line-height:1;margin:2px 2px 0px 2px"><?php printf ( __( 'Reset' , 'easyReservations' ));?></a>
					</form>
					<form method="post" action="admin.php?page=reservation-settings&site=form<?php if($formnameget!=""){ echo '&form='.$formnameget; } ?>"  id="reservations_form_settings" name="reservations_form_settings" style="margin-top:-2px">
						<input type="hidden" name="action" value="reservations_form_settings"/>
						<input type="hidden" name="formnamesgets" value="<?php echo $formnameget; ?>"/>
						<input type='hidden' value='<?php echo stripslashes($reservations_form); ?>' name="resetforrm">
							<?php wp_editor( stripslashes($reservations_form), 'reservations_formvalue', $settings = array( 'textarea_rows' => 35, 'wpautop' => false, 'tinymce' => false, 'media_buttons' => false, 'quicktags' => array('buttons' => 'strong,em,link,img,ul,ol,li' ) ) ); ?>
						<div style="margin:8px 1px;">
							<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>" onclick="document.getElementById('reservations_form_settings').submit(); return false;" class="easySubmitButton-primary" >
							<input type="button" value="<?php printf ( __( 'Default Form' , 'easyReservations' ));?>" onClick="setDefaultForm();" class="easySubmitButton-secondary" >
							<input type="button" value="<?php printf ( __( 'Reset Form' , 'easyReservations' ));?>" onClick="resteText();" class="easySubmitButton-secondary" >
						</div>
					</form>
					</td>
					<td style="width:40%;vertical-align: top;">		
					<div style="text-align:center;vertical-align:middle;height:30px;font-weight:bold;"><?php printf ( __( 'Include to Page or Post with' , 'easyReservations' ));?> <code class="codecolor">[<?php echo $howload; ?>]</code></div>
						<div id="Helper"></div>
						<div class="explainbox">
							<p><code class="codecolor">[error]</code> <i><?php printf ( __( 'live form validation' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[show_price]</code> <i><?php printf ( __( 'live price calculation' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[date-from]</code> <i><?php printf ( __( 'day of arrival with datepicker' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[date-to]</code> <i><?php printf ( __( 'day of departure with datepicker' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[persons x]</code> <i><?php printf ( __( 'number of guests' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[childs x]</code> <i><?php printf ( __( 'number of children\'s' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[thename]</code> <i><?php printf ( __( 'name of guest' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email of guest' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[rooms]</code> <i><?php printf ( __( 'select of rooms' , 'easyReservations' ));?></i>*</p>
							<p><code class="codecolor">[offers x]</code> <i><?php printf ( __( 'offers as select or box' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[country]</code> <i><?php printf ( __( 'countrys as select' , 'easyReservations' ));?></i> *</p>
							<p><code class="codecolor">[hidden type x]</code> <i><?php printf ( __( 'for fix a room/offer to a form' , 'easyReservations' ));?> </i></p>
							<p><code class="codecolor">[custom type x]</code> <i><?php printf ( __( 'add custom fields as needed' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[message]</code> <i><?php printf ( __( 'message from guest' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[submit x]</code> <i><?php printf ( __( 'submit button' , 'easyReservations' ));?></i> *</p>
						</div><br>
						<?php
							$couerrors=0;
							$gute=0;
							$formgood='';
							$formerror ='';
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
	function insertAtCaret(areaId,text) {
		var txtarea = document.getElementById(areaId);
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;

		var front = (txtarea.value).substring(0,strPos);  
		var back = (txtarea.value).substring(strPos,txtarea.value.length); 
		txtarea.value=front+text+back;
		strPos = strPos + text.length;
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			range.moveStart ('character', strPos);
			range.moveEnd ('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		txtarea.scrollTop = scrollPos;
	}

	function easy_add_form_tag(){
		var type = document.getElementById("jumpmenu");
		var tag = '[' + type.value;

		var eins = document.getElementById("eins");
		var zwei = document.getElementById("zwei");
		var drei = document.getElementById("drei");
		var vier = document.getElementById("vier");
		var req = document.getElementById("req");
		if((type.value == 'custom' && ( eins.value == 'select' || eins.value == 'radio' )) || type.value == 'price') var limit = '"';
		else var limit = '';

		if(eins) tag += ' '+eins.value;
		if(zwei) tag += ' '+zwei.value;
		if(drei) tag += ' '+limit+drei.value+limit;
		if(vier) tag += ' '+vier.value;
		if(req && req.checked != false ) tag += ' '+ req.value;
		tag += ']';
		var textareaelem = document.getElementById("reservations_formvalue");
		textareaelem.focus();

		insertAtCaret('reservations_formvalue', tag)
		//textareaelem.scrollTop = textareaelem.scrollHeight;
		//textareaelem.innerHTML += tag;
	}

	var thetext1 = false;
	var thetext2 = false;
	var thetext3 = false;
	var thetext4 = false;

	function resetform(){ // Reset fields in Form
		var Nichts = '';
		document.form1.reset();
		document.form1.jumpmenu.disabled=false;
		document.getElementById("Text").innerHTML = '';
		document.getElementById("Text2").innerHTML = '';
		document.getElementById("Text3").innerHTML = '';
		document.getElementById("Text4").innerHTML = '';
		document.getElementById("Helper").innerHTML = '';
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

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select Type of custom price field' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Select' , 'easyReservations' ); ?></b> <?php echo __( 'Add a dropdown select field with effect on the price to the form' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Radio' , 'easyReservations' ); ?></b> <?php echo __( 'Add a radio select field with effect on the price to the form' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Checkbox' , 'easyReservations' ); ?></b> <?php echo __( 'Add a checkbox input with effect on the price to the form' , 'easyReservations' ); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "error" || x == "date-from" || x == "date-to" || x == "email" || x == "rooms" || x == "message" || x == "thename" || x == "show_price" || x == "country" ){
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "submit"){

			var Output  = '<input type="text" name="eins" id="eins" value="Name">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Type in value of submit button' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.form1.jumpmenu.disabled=true;
			var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="line-height:1;margin:2px 2px 0px 2px"><b><?php echo __( 'Add' , 'easyReservations' ); ?></b></a>';

		} else if (x == "persons"){

			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="Select">Select</option><option value="Text">Text</option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Select type of person input' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

		} else if (x == "childs"){

			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="Select">Select</option><option value="Text">Text</option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Select type of childs input' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

		} else if (x == "hidden") {

			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="room"><?php echo __( 'room' , 'easyReservations' ); ?></option><option value="offer"><?php echo __( 'Offer' , 'easyReservations' ); ?></option><option value="from"><?php echo __( 'Arrival date' , 'easyReservations' ); ?></option><option value="to"><?php echo __( 'Departure date' , 'easyReservations' ); ?></option><option value="persons"><?php echo __( 'Persons' , 'easyReservations' ); ?></option><option value="childs"><?php echo __( 'Childrens' , 'easyReservations' ); ?></option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select type of hidden input' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for fixing an information to the form & hide it from guest' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Room</b> <?php echo __( 'Fix a room to the form; dont use it with [rooms] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Offer</b> <?php echo __( 'Fix an offer to the form; dont use it with [offers] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Arrival Date</b> <?php echo __( 'Fix an arrival date to the form; dont use it with [date-from] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Departure Date</b> <?php echo __( 'Fix a departure date to the form; dont use it with [date-to] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Persons</b> <?php echo __( 'Fix an amount of persons to the form; dont use it with [persons] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b>Childrens</b> <?php echo __( 'Fix an amount of childrens to the form; dont use it with [childs] in the same form' , 'easyReservations' ); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

		} else if (x == "offers") {

			var Output  = '<select id="eins" name="eins">';
			Output += '<option value="select">Select</option><option value="box">Box</option></select>';
			document.getElementById("Text").innerHTML += Output;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select type of offer' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b>Select</b> <?php echo __( 'A drop-down select of all offers' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><b>Box</b> <?php echo __( 'A box as prompt, if the guest was redirected by an offer post' , 'easyReservations' ); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="line-height:1;margin:2px 2px 0px 2px"><b><?php echo __( 'Add' , 'easyReservations' ); ?></b></a>';
			document.getElementById("Text4").innerHTML += Output;

		}
	} else if(thetext2 == false){
		if (x == "textarea" || x == "text" || x == "check"){
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name"> <input type="checkbox" id="req" name="req" value="*"> <?php echo __( 'Required' , 'easyReservations' ); ?> ';
			document.getElementById("Text2").innerHTML += Output;
		
			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Type in a name for the' , 'easyReservations' ); ?> <span style="text-transform:capitalize">' + x + '</span> <?php echo __( 'input you want to add' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			end = 1;
			document.form1.eins.disabled=true;
		} else if (x == "checkbox"){
			end = 1;

			var Output  = '<input type="text" name="zwei" id="zwei" value="Name"><input type="text" name="drei" id="drei" value="Value"><input type="checkbox" id="req" name="req" value="pp"> <?php echo __( 'price per person' , 'easyReservations' ); ?>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a Name for the Checkbox' , 'easyReservations' ); ?></b>';
			Help += '<br><b>2. <?php echo __( 'Type in a value for the checkbox' , 'easyReservations' ); ?></b>',
			Help += '<br> &emsp; <?php echo __( 'The value has to match ' , 'easyReservations' ); ?><br>&emsp; <code>option:price</code><br> &emsp; <?php printf( __( 'Price: negative for reduction %1$s  zero for no change %2$s positiv for increase %3$s '), '<code>-30.75</code>', '<code>0</code>', '<code>20.2</code>' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "select" || x == "radio") {
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name" onClick="jumpto(document.form1.zwei.value);">';
			if(first == "price"){
				var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a name for the dropdown select' , 'easyReservations' ); ?></b>';
				Help += '<br><b>2. <?php echo __( 'Type in the options field for the' , 'easyReservations' ); ?>  ' + x + ' Input</b>',
				Help += '<br> &emsp;<?php echo __( 'The options field has to match ' , 'easyReservations' ); ?><br>&emsp; <code>first option:first price<b>,</b>second option:second price [...]</code><br> &emsp; <?php printf( __( 'Price: negative for reduction %1$s  zero for no change %2$s positiv for increase %3$s '), '<code>-20</code>', '<code>0</code>', '<code>50.89</code>' ); ?></div><br>';
			} else if(first == "custom"){
				var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a Name for the' , 'easyReservations' ); ?> ' + x + ' field</b>';
				Help += '<br><b>2. <?php echo __( 'Type in the options field' , 'easyReservations' ); ?></b>',
				Help += '<br> &emsp; <?php echo __( 'The options field has to match ' , 'easyReservations' ); ?><br>&emsp; <code>first option<b>,</b>second option<b>,</b>third option [...]</code></div><br>';
			}
			document.getElementById("Text2").innerHTML += Output;

			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "room") {
			end = 1;
			var Output  = '<select id="zwei" name="zwei"><?php echo $roomsoptions; ?></select>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select a room' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "offer") {
			end = 1;
			var Output  = '<select id="zwei" name="zwei"><option value="0"><?php echo __( 'None' , 'easyReservations' ); ?></option><?php echo $offeroptions; ?></select>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select an Offer' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "Select") {
			end = 1;
			var Output  = '&nbsp;<b><?php echo __( 'Min:' , 'easyReservations' ); ?></b> <select name="zwei" id="zwei"><?php echo easyReservations_num_options(0,100,1); ?></select> <b><?php echo __( 'Max:' , 'easyReservations' ); ?></b> <select name="drei" id="drei"><?php echo easyReservations_num_options(0,100,10); ?></select>';
			document.getElementById("Text2").innerHTML += Output;
			
			if(document.getElementById("jumpmenu").value=="persons"){
				var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select minimum and maximum number of persons to select' , 'easyReservations' ); ?></b></div><br>';
				document.getElementById("Helper").innerHTML = Help;
			} else {
				var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select minimum and maximum number of childrens to select' , 'easyReservations' ); ?></b></div><br>';
				document.getElementById("Helper").innerHTML = Help;
			}

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "Text") {
			end = 1;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "persons" || x == "childs") {
			end = 1;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Amount">';
			document.getElementById("Text3").innerHTML += Output;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "from") {
			end = 1;
			var Output  = '<input type="text" name="zwei" id="zwei" value="dd.mm.yyyy">';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Fill in the date of the arrival date you want to fix' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "to") {
			end = 1;
			var Output  = '<input type="text" name="zwei" id="zwei" value="dd.mm.yyyy">';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Fill in the date of the departure date you want to fix' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		}
	} else if(thetext3 == false){
		if (x == "Name") {
			end = 1;
			var Output  = '<input type="text" name="drei" id="drei" value="Options">';
			if(first == "custom") Output += ' <input type="checkbox" id="req" name="req" value="*"> <?php echo __( 'Required' , 'easyReservations' ); ?> ';
			else Output += '<input type="checkbox" id="req" name="req" value="pp"> <?php echo __( 'price per person' , 'easyReservations' ); ?>';

			document.getElementById("Text3").innerHTML += Output;
			thetext3 = true;
		}
	}

	if (end == 1) {
		var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="line-height:1;margin:2px 2px 0px 2px"><b><?php echo __( 'Add' , 'easyReservations' ); ?></b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
}
</script>
<?php } elseif($settingpage=="email"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EMAIL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]";
$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
$emailstandart6="Reservation got edited by Guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>[changelog]";
$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [departuredate] <br>Persons: [persons] <br>Childs: [childs] <br>Room: [rooms] <br>Offer: [offers] <br>Message: [note]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";

?>
	<form method="post" action="admin.php?page=reservation-settings&site=email"  id="reservations_email_settings" name="reservations_email_settings">
		<input type="hidden" name="action" value="reservations_email_settings"/>
		<input type="hidden" value="<?php echo $emailstandart0; ?>" name="inputemail0">
		<input type="hidden" value="<?php echo $emailstandart1; ?>" name="inputemail1">
		<input type="hidden" value="<?php echo $emailstandart2; ?>" name="inputemail2">
		<input type="hidden" value="<?php echo $emailstandart3; ?>" name="inputemail3">
		<input type="hidden" value="<?php echo $emailstandart4; ?>" name="inputemail4">
		<input type="hidden" value="<?php echo $emailstandart5; ?>" name="inputemail5">
		<input type="hidden" value="<?php echo $emailstandart6; ?>" name="inputemail6">
		<input type="hidden" value="<?php echo $emailstandart7; ?>" name="inputemail7">
		<table style="width:99%;" cellspacing="0">
			<tr style="width:60%;" cellspacing="0">
				<td valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Standard Sendmail' , 'easyReservations' ));?><span style="float:right;margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_sendmail_check" <?php checked(1, $reservations_email_sendmail['active']); ?> style="margin-top:3px;margin-left:-1px;"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail0();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_sendmail_subj" style="width:60%;" value="<?php echo $reservations_email_sendmail['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_sendmail_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_sendmail['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on new reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to admin' , 'easyReservations' ); ?> </b><span style=";margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_admin_check" <?php checked(1, $reservations_email_to_admin['active']); ?> style="margin-top:3px;margin-left:-1px;"></span><input type="button" value="Default Mail" onClick="addtextforemail1();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_admin_subj" style="width:60%;" value="<?php echo $reservations_email_to_admin['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_admin_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_admin['msg']); ?></textarea></td>
				</tr>	
				<tr valign="top">
					<td><div class="fakehr"></td>
				</tr>	
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><span style="margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_user_check" <?php checked(1, $reservations_email_to_user['active']); ?> style="margin-top:3px;margin-left:-1px;"></span><input type="button" value="Default Mail" onClick="addtextforemail4();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_subj" style="width:60%;" value="<?php echo $reservations_email_to_user['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on approve' , 'easyReservations' ));?><span style="float:right;margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_userapp_check" <?php checked(1, $reservations_email_to_userapp['active']); ?> style="margin-top:3px;margin-left:-1px;"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail2();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_userapp_subj" style="width:60%;" value="<?php echo $reservations_email_to_userapp['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userapp_msg"  id="reservations_email_to_userapp_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_userapp['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on reject' , 'easyReservations' ));?><span style="float:right;margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_userdel_check" <?php checked(1, $reservations_email_to_userdel['active']); ?> style="margin-top:3px;margin-left:-1px;"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail3();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_userdel_subj" style="width:60%;" value="<?php echo $reservations_email_to_userdel['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userdel_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_userdel['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on admin-edit' , 'easyReservations' ));?><span style="float:right;margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_user_admin_edited_check" <?php checked(1, $reservations_email_to_user_admin_edited['active']); ?> style="margin-top:3px;margin-left:-1px;"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail7();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_admin_edited_subj" style="width:60%;" value="<?php echo $reservations_email_to_user_admin_edited['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_admin_edited_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user_admin_edited['msg']); ?></textarea></td>
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
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to admin' , 'easyReservations' ); ?></b><span style=";margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_admin_edited_check" <?php checked(1, $reservations_email_to_admin_edited['active']); ?> style="margin-top:3px;margin-left:-1px;"></span><input type="button" value="Default Mail" onClick="addtextforemail6();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_admin_edited_subj" style="width:60%;" value="<?php echo $reservations_email_to_admin_edited['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_admin_edited_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_admin_edited['msg']); ?></textarea></td>
				</tr>	
				<tr valign="top">
					<td><div class="fakehr"></td>
				</tr>	
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><span style=";margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_user_edited_check" <?php checked(1, $reservations_email_to_user_edited['active']); ?> style="margin-top:3px;margin-left:-1px;"></span><input type="button" value="Default Mail" onClick="addtextforemail5();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_edited_subj" style="width:60%;" value="<?php echo $reservations_email_to_user_edited['subj']; ?>"> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_edited_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user_edited['msg']); ?></textarea></td>
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
							<p><code class="codecolor">[adminmessage]</code> <i><?php printf ( __( 'message from admin' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[ID]</code> <i><?php printf ( __( 'ID' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[thename]</code> <i><?php printf ( __( 'name' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[arrivaldate]</code> <i><?php printf ( __( 'arrival date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[departuredate]</code> <i><?php printf ( __( 'departure date' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[nights]</code> <i><?php printf ( __( 'nights to stay' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[persons]</code> <i><?php printf ( __( 'number of guests' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[childs]</code> <i><?php printf ( __( 'number of childs' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[country]</code> <i><?php printf ( __( 'country of guest' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[rooms]</code> <i><?php printf ( __( 'choosen room' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[offers]</code> <i><?php printf ( __( 'choosen offer' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[note]</code> <i><?php printf ( __( 'message from guest/admin note' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[price]</code> <i><?php printf ( __( 'show price' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[customs]</code> <i><?php printf ( __( 'custom fields' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[changlog]</code> <i><?php printf ( __( 'show changes after edits' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[editlink]</code> <i><?php printf ( __( 'link to user edit' , 'easyReservations' ));?></i></p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		</td></tr>
	</table>
</form>
<?php } 
	if($settingpage=="about"){ ?>
	<table style="width:99%;" cellspacing="0"><tr><td style="width:60%;" style="width:49%;"  valign="top">
		<table id="changelog" class="<?php echo RESERVATIONS_STYLE; ?>" >
			<thead>
				<tr>
					<th> <?php printf ( __( 'Changelog' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
						<style> 
							#changelog ul {
								list-style: disc !important;
							} #changelog li {
								list-style-type: circle;
							} #changelog ul {
								padding-left: 30px;
							}
						</style> 
						<?php include('changlog.html');?>
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
						<div class="changebox">
							<p>
								<a href="http://www.feryaz.de/dokumentation/"><?php printf ( __( 'Documentation' , 'easyReservations' ));?></a>
							</p>
							<div class="fakehr"></div>
							<p>
								<a href="http://www.feryaz.de/suggestions/"><?php printf ( __( 'Suggest Ideas' , 'easyReservations' ));?></a>
							</p>
							<div class="fakehr"></div>
							<p>
								<a href="http://bugs.feryaz.de"><?php printf ( __( 'Report Bugs' , 'easyReservations' ));?></a>
							</p>
							<div class="fakehr"></div>
							<p>
								<a href="http://wordpress.org/extend/plugins/easyreservations/"><?php printf ( __( 'Wordpress Repository' , 'easyReservations' ));?></a>
							</p>
						<div>
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
						$rss = fetch_feed('http://feryaz.de/wp-rss2.php');
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
<?php } do_action( 'er_set_add' ); ?>
</div><?php
} ?>