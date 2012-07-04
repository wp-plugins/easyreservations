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

		if(isset($_GET["deleteform"])) $namtetodelete = $_GET['deleteform'];
		if(isset($_POST["action"])) $action = $_POST['action'];
		if(isset($_GET["site"])) $settingpage = $_GET['site'];
		else { $settingpage="general"; $ifgeneralcurrent='class="current"'; }

		if($settingpage=="about") $ifaboutcurrent='class="current"';

		if(isset($action) && $action == "reservation_clean_database"){
			$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE departure < NOW() AND approve != 'yes' ") );
			$prompt = '<div class="update"><p>'.__( 'Database cleaned' , 'easyReservations' ).'</p></div>';
		}

		if(isset($action) && $action == "er_main_set"){ //Set Reservation settings 
			if(isset($_POST["reservations_uninstall"])) $reservations_uninstall = 1; else $reservations_uninstall = 0;
			if(isset($_POST["reservations_time"])) $reservations_time = 1; else $reservations_time = 0;
			update_option("reservations_uninstall", $reservations_uninstall);
			$settings_array = array( 'style' => $_POST["reservations_style"], 'currency' => $_POST["reservations_currency"], 'date_format' => $_POST["reservations_date_format"], 'time' => $reservations_time );
			update_option("reservations_settings", $settings_array);
			update_option("reservations_regular_guests", $_POST["regular_guests"]);
			update_option("reservations_support_mail", $_POST["reservations_support_mail"]);
			update_option("reservations_edit_url", $_POST["reservations_edit_url"]);
			if(isset( $_POST["reservations_edit_table_infos"])) $table_infos = $_POST["reservations_edit_table_infos"]; else $table_infos = array();
			if(isset( $_POST["reservations_edit_table_status"])) $table_status = $_POST["reservations_edit_table_status"]; else $table_status = array();
			if(isset( $_POST["reservations_edit_table_time"])) $table_time = $_POST["reservations_edit_table_time"]; else $table_time = array();
			if(isset( $_POST["reservations_edit_table_style"])) $table_style = 1; else $table_style = 0;
			if(isset( $_POST["reservations_edit_table_more"])) $table_more = 1; else $table_more = 0;
			$edit_options = array( 'login_text' => stripslashes($_POST["reservations_edit_login_text"]), 'edit_text' => stripslashes($_POST["reservations_edit_text"]), 'submit_text' => stripslashes($_POST["reservations_submit_text"]), 'table_infos' => $table_infos, 'table_status' => $table_status, 'table_time' => $table_time, 'table_style' => $table_style, 'table_more' => $table_more );
			update_option('reservations_edit_options', $edit_options);
			do_action( 'er_set_main_save' );
			$prompt = '<div class="updated"><p>'.__( 'General settings saved' , 'easyReservations' ).'</p></div>';
		}

		if(isset($action) && $action == "reservations_email_settings"){//Set Reservation Mails
			do_action( 'er_set_email_save' );

			if(isset($_POST["reservations_email_sendmail_msg"])){
				if(isset($_POST["reservations_email_sendmail_check"])) $reservations_email_sendmail_check = 1; else $reservations_email_sendmail_check = 0;
				if(is_array($_POST["reservations_email_sendmail_msg"])) $_POST["reservations_email_sendmail_msg"] = implode($_POST["reservations_email_sendmail_msg"]);
				$reservations_email_sendmail = array(
					'msg' => stripslashes($_POST["reservations_email_sendmail_msg"]),
					'subj' => stripslashes($_POST["reservations_email_sendmail_subj"]),
					'active' => $reservations_email_sendmail_check
				);
				update_option("reservations_email_sendmail",$reservations_email_sendmail);
			}

			if(isset($_POST["reservations_email_to_admin_msg"])){
				if(isset($_POST["reservations_email_to_admin_check"])) $reservations_email_to_admin_check = 1; else $reservations_email_to_admin_check = 0;
				if(is_array($_POST["reservations_email_to_admin_msg"])) $_POST["reservations_email_to_admin_msg"] = implode($_POST["reservations_email_to_admin_msg"]);
				$reservations_email_to_admin = array(
					'msg' => stripslashes($_POST["reservations_email_to_admin_msg"]),
					'subj' => stripslashes($_POST["reservations_email_to_admin_subj"]),
					'active' => $reservations_email_to_admin_check
				);
				update_option("reservations_email_to_admin",$reservations_email_to_admin);
			}

			if(isset($_POST["reservations_email_to_userapp_msg"])){
				if(isset($_POST["reservations_email_to_userapp_check"])) $reservations_email_to_userapp_check = 1; else $reservations_email_to_userapp_check = 0;
				if(is_array($_POST["reservations_email_to_userapp_msg"])) $_POST["reservations_email_to_userapp_msg"] = implode($_POST["reservations_email_to_userapp_msg"]);
				$reservations_email_to_userapp = array( 
					'msg' => stripslashes($_POST["reservations_email_to_userapp_msg"]),
					'subj' => stripslashes($_POST["reservations_email_to_userapp_subj"]),
					'active' => $reservations_email_to_userapp_check
				);
				update_option("reservations_email_to_userapp",$reservations_email_to_userapp);
			}

			if(isset($_POST["reservations_email_to_userdel_msg"])){
				if(isset($_POST["reservations_email_to_userdel_check"])) $reservations_email_to_userdel_check = 1; else $reservations_email_to_userdel_check = 0;
				if(is_array($_POST["reservations_email_to_userdel_msg"])) $_POST["reservations_email_to_userdel_msg"] = implode($_POST["reservations_email_to_userdel_msg"]);
				$reservations_email_to_userdel = array(
					'msg' => stripslashes($_POST["reservations_email_to_userdel_msg"]), 
					'subj' => stripslashes($_POST["reservations_email_to_userdel_subj"]),
					'active' => $reservations_email_to_userdel_check
				);
				update_option("reservations_email_to_userdel",$reservations_email_to_userdel);
			}

			if(isset($_POST["reservations_email_to_user_msg"])){
				if(isset($_POST["reservations_email_to_user_check"])) $reservations_email_to_user_check = 1; else $reservations_email_to_user_check = 0;
				if(is_array($_POST["reservations_email_to_user_msg"])) $_POST["reservations_email_to_user_msg"] = implode($_POST["reservations_email_to_user_msg"]);
				$reservations_email_to_user = array(
					'msg' => stripslashes($_POST["reservations_email_to_user_msg"]),
					'subj' => stripslashes($_POST["reservations_email_to_user_subj"]),
					'active' => $reservations_email_to_user_check
				);
				update_option("reservations_email_to_user",$reservations_email_to_user);
			}

			if(isset($_POST["reservations_email_to_user_edited_msg"])){
				if(isset($_POST["reservations_email_to_user_edited_check"])) $reservations_email_to_user_edited_check = 1; else $reservations_email_to_user_edited_check = 0;
				if(is_array($_POST["reservations_email_to_user_edited_msg"])) $_POST["reservations_email_to_user_edited_msg"] = implode($_POST["reservations_email_to_user_edited_msg"]);
				$reservations_email_to_user_edited = array(
					'msg' => stripslashes($_POST["reservations_email_to_user_edited_msg"]),
					'subj' => stripslashes($_POST["reservations_email_to_user_edited_subj"]),
					'active' => $reservations_email_to_user_edited_check
				);
				update_option("reservations_email_to_user_edited",$reservations_email_to_user_edited);
			}

			if(isset($_POST["reservations_email_to_admin_edited_msg"])){
				if(isset($_POST["reservations_email_to_admin_edited_check"])) $reservations_email_to_admin_edited_check = 1; else $reservations_email_to_admin_edited_check = 0;
				if(is_array($_POST["reservations_email_to_admin_edited_msg"])) $_POST["reservations_email_to_admin_edited_msg"] = implode($_POST["reservations_email_to_admin_edited_msg"]);
				$reservations_email_to_admin_edited = array(
					'msg' => stripslashes($_POST["reservations_email_to_admin_edited_msg"]),
					'subj' => stripslashes($_POST["reservations_email_to_admin_edited_subj"]),
					'active' => $reservations_email_to_admin_edited_check
				);
				update_option("reservations_email_to_admin_edited",$reservations_email_to_admin_edited);
			}

			if(isset($_POST["reservations_email_to_user_admin_edited_msg"])){
				if(isset($_POST["reservations_email_to_user_admin_edited_check"])) $reservations_email_to_user_admin_edited_check = 1; else $reservations_email_to_user_admin_edited_check = 0;
				if(is_array($_POST["reservations_email_to_user_admin_edited_msg"])) $_POST["reservations_email_to_user_admin_edited_msg"] = implode($_POST["reservations_email_to_user_admin_edited_msg"]);
				$reservations_email_to_user_admin_edited = array(
					'msg' => stripslashes($_POST["reservations_email_to_user_admin_edited_msg"]),
					'subj' => stripslashes($_POST["reservations_email_to_user_admin_edited_subj"]),
					'active' => $reservations_email_to_user_admin_edited_check
				);
				update_option("reservations_email_to_user_admin_edited",$reservations_email_to_user_admin_edited);
			}
			$prompt = '<div class="updated"><p>'.__( 'eMail settings saved' , 'easyReservations' ).'</p></div>';
		}

		if(isset($action) && $action  == "reservations_form_settings"){ // Change a form
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

		if(isset($action) && $action == "reservation_change_permissions"){ // Change a form
			if(current_user_can('manage_options')){
				$permissions = array('dashboard' => $_POST["easy-permission-dashboard"], 'resources' => $_POST["easy-permission-resources"], 'statistics' => $_POST["easy-permission-statistics"], 'settings' => $_POST["easy-permission-settings"]);
				update_option('reservations_main_permission', $permissions);
				$prompt = '<div class="updated"><p>'.__( 'Permissions changed' , 'easyReservations' ).'</p></div>';
			} else $prompt = '<div class="error"><p>'.__( 'Only admins can change the permissions for' , 'easyReservations' ).' easyReservations</p></div>';
		}

		if(isset($namtetodelete)){
			delete_option('reservations_form_'.$namtetodelete);
			$prompt = '<div class="updated"><p>'.sprintf(__( 'Form %s has been deleted' , 'easyReservations' ), '<b>'.$namtetodelete.'</b>' ).'</p></div>';
		}

		if(isset($action) && $action == "reservations_form_add"){// Add form after check twice for stupid Users :D
			if($_POST["formname"]!=""){

				$formname0='reservations_form_'.strtolower(str_replace(' ', '', $_POST["formname"]));
				$formname1=$formname0.'_1';
				$formname2=$formname0.'_2';
				
				if(get_option($formname0)=="") add_option(''.$formname0.'', ' ', '', 'no' );
				elseif(get_option($formname1)=="") add_option(''.$formname1.'', ' ', '', 'no');
				else add_option(''.$formname2.'', ' ', '', 'no');
				$prompt = '<div class="updated"><p>'.sprintf(__( 'Form %s has been added' , 'easyReservations' ), '<b>'.$_POST["formname"].'</b>' ).'</p></div>';
			} else $prompt = '<div class="error"><p>'.__( 'Please enter a name for the form' , 'easyReservations' ).'</p></div>';
		}

		if($settingpage=="form"){//Get current form Options
			$forms = '';
			$ifformcurrent='class="current"';

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

		if($settingpage=="email") $ifemailcurrent='class="current"';
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
function addtextforemail8() {
	var newtext = document.reservations_email_settings.inputemail8.value;
	document.reservations_email_settings.reservations_email_to_admin_paypal_msg.value = newtext;
}
function addtextforemail9() {
	var newtext = document.reservations_email_settings.inputemail9.value;
	document.reservations_email_settings.reservations_email_to_user_paypal_msg.value = newtext;
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
<div class="tabs-box" style="margin-bottom:10px;width:99%">
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
	$reservations_settings = get_option("reservations_settings");
	$reservations_currency = $reservations_settings['currency'];
	$reservations_date_format = $reservations_settings['date_format'];
	$easyReservationSyle=$reservations_settings['style'];
	$reservation_support_mail = get_option("reservations_support_mail");
	$reservations_regular_guests = get_option('reservations_regular_guests');
	$permission_options=get_option("reservations_main_permission");
	$reservations_edit_url=get_option("reservations_edit_url");
	$reservations_edit_options=get_option("reservations_edit_options");
	if(!$reservations_edit_options) $reservations_edit_options = array();
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
					<tr valign="top">
						<td style="font-weight:bold"><img style="vertical-align:text-bottom;margin-right:2px;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'Support eMail' , 'easyReservations' ));?></td>
						<td><input type="text" name="reservations_support_mail" value="<?php echo $reservation_support_mail;?>" style="width:50%"></td>
					</tr>
						<tr valign="top" class="alternate" style="height:35px">
							<td style="font-weight:bold"><img style="vertical-align:text-bottom;margin-right:2px;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/clock.png"> <?php printf ( __( 'Time reservations' , 'easyReservations' ));?></td>
							<td><input type="checkbox" name="reservations_time" <?php checked($reservations_settings['time'],1); ?>> <i><?php printf ( __( 'Enable time for reservations ' , 'easyReservations' ));?></i></td>
						</tr>
					<tr valign="top">
						<td style="font-weight:bold"><img style="vertical-align:text-bottom;margin-right:2px;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/dollar.png"> <?php printf ( __( 'Currency sign' , 'easyReservations' ));?></td>
						<td>
							<select name="reservations_currency"><?php
								$currencys = array(
									array('Euro' , '#8364'),
									array('Dollar' , '#36'),
									array('Yen' , '#165'),
									array('Cent' , '#162'), 
									array('Florin' , '#402'), 
									array('Pound' , '#163'), 
									array('Lire' , '#8356'), 
									array('Hongkong Dollar' , '#20803'),
									array('Tenge' , '#8376'),
									array('Laos Kip' , '#8365') , 
									array('Colon' , '#8353'),
									array('Guarani ' , '#8370'),
									array('Hungary Forint' , '#70;&#116'),
									array('Uruguay Peso' , '#8369'),
									array('Indian Rupee' , '#8360'),
									array('Indian Rupee 2nd' , '#8377'), 
									array('Bengali Rupee' , '#2547'),
									array('Gujarati  Rupee' , '#2801'),
									array('Tamil Rupee' , '#3065'),
									array('Thai Baht' , '#3647'),
									array('Khmer Riel' , '#6107'),
									array('Belize Dollar' , '#66;&#90;&#36'),
									array('Bolivia Boliviano' , '#36;&#98'),
									array('Bosnia and Herzegovina Marka' , '#75;&#77'),
									array('Botswana Pula' , '#80'),
									array('Bulgaria Lev' , '#1083;&#1074'),
									array('Cambodia Riel' , '#6107'),
									array('China Yuan' , '#20803'),
									array('Austral' , '#8371'),
									array('Hryvnia' , '#8372'),
									array('Guatemala Quetzal' , '#81'),
									array('Cedi' , '#8373'),
									array('Tugril' , '#8366'),
									array('Turkish Lira' , '#84;&#76'),
									array('Drachma' , '#8367'),
									array('Honduras Lempira' , '#76'),
									array('Vietnam Dong' , '#8363'),
									array('Naira' , '#8358'),
									array('Azerbaijan New Manat' , '#1084;&#1072;&#1085'),
									array('Macedonia Denar' , '#1076;&#1077;&#1085'),
									array('Mongolia Tughrik' , '#8366'),
									array('Afghanistan Afghani' , '#1547'),
									array('Cruzeiro' , '#8354'),
									array('Omani Rial' , '#65020'),
									array('Won' , '#65510'),
									array('Philippine Peso' , '#608'),
									array('Philippine Peso 2nd' , '#80;&#104;&#11'),
									array('Brazilian Real' , '#986'),
									array('Brazilian Real 2nd' , '#76;&#115'),
									array('Nicaragua Cordoba' , '#67;&#36'),
									array('Mozambique Metical' , '#77;&#84'),
									array('Malaysia Ringgit' , '#82;&#77'),
									array('Latvia Lat' , '#82;&#36'),
									array('Kazakhstan Tenge' , '#1083;&#1074'),
									array('Jamaica Dollar' , '#74;&#36'),
									array('Czech Koruna' , '#75;&#269'),
									array('Danish Krone' , '#107;&#114'),
									array('Croatia Kuna' , '#107;&#110'),
									array('Polish Zloty' , '#122;&#322'),
									array('Israeli Sheqel' , '#122;&#322'),
									array('Panamanian Balboa' , '#66;&#47;&#46'),
									array('Dominican Republic Peso' , '#82;&#68;&#36'),
									array('Switzerland Franc' , '#67;&#72;&#70'),
									array('Egyptian Pound' , '#163'),
									array('Romanian Leu' , '#108;&#101;&#1'),
									array( 'Russian Rouble', '#1088;&#1091')
								);
								asort($currencys);

								foreach($currencys as $currenc){
									if($currenc[1] == $reservations_currency) $select = ' selected="selected" '; else $select = '';
									echo '<option value="'.htmlentities($currenc[1]).'" '.$select.'>'.$currenc[0].' &'.$currenc[1].';</option>';										
								} ?>
							</select>
						</td>
					</tr>
					<tr valign="top"  class="alternate">
						<td style="font-weight:bold"><img style="vertical-align:text-bottom;margin-right:2px;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/background.png"> <?php printf ( __( 'Style' , 'easyReservations' ));?></td>
						<td>
							<select name="reservations_style">
								<option value="widefat" <?php if($easyReservationSyle=='widefat' OR RESERVATIONS_STYLE=='widefat') echo 'selected'; ?>><?php printf ( __( 'Wordpress' , 'easyReservations' ));?></option>
								<option value="greyfat" <?php if($easyReservationSyle=='greyfat' OR RESERVATIONS_STYLE=='greyfat') echo 'selected'; ?>><?php printf ( __( 'Grey' , 'easyReservations' ));?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<td style="font-weight:bold"><img style="vertical-align:text-bottom;margin-right:2px" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date format' , 'easyReservations' ));?></td>
						<td>
							<select name="reservations_date_format"><?php
								$date_formats = array(
									array(__( date('Y/m/d', time()), 'easyReservations' ), 'Y/m/d'),
									array(__( date('Y-m-d', time()), 'easyReservations' ), 'Y-m-d'),
									array(__( date('m/d/Y', time()), 'easyReservations' ), 'm/d/Y'),
									array(__( date('d-m-Y', time()), 'easyReservations' ), 'd-m-Y'),
									array(__( date('d.m.Y', time()), 'easyReservations' ), 'd.m.Y')
								 );

								foreach($date_formats as $date_format){
									if($date_format[1] == $reservations_date_format) $select = ' selected="selected" '; else $select = '';
									echo '<option '.$select.' value="'.$date_format[1].'">'.date($date_format[1], strtotime($date_format[0])).'</option>';										
								}?>
							</select>
						</td>
					</tr>
					<tr valign="top"  class="alternate" style="height:35px">
						<td style="font-weight:bold"><img style="vertical-align:text-bottom;margin-right:2px;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/database.png"> <?php printf ( __( 'Uninstall' , 'easyReservations' ));?></td>
						<td><input type="checkbox" name="reservations_uninstall" value="1" <?php echo checked($reservations_uninstall, 1); ?>> <?php printf ( __( 'Delete settings, reservations and resources' , 'easyReservations' ));?></td>
					</tr>
					<?php do_action( 'er_add_set_main_table_row' ); ?>
					</tr>
				</tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px">
				<thead>
					<tr>
						<th> <?php printf ( __( 'User ControlPanel settings' , 'easyReservations' ));?> </th>
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
							&nbsp;<b><?php printf ( __( 'URL to edit page' , 'easyReservations' ));?></b>: <input type="text" name="reservations_edit_url" value="<?php echo $reservations_edit_url;?>" style="width:50%">
						</td>
					</tr>
					<tr class="alternate">
						<td>
							&nbsp;<b><?php printf ( __( 'Text after Submit' , 'easyReservations' ));?></b>: <input type="text" name="reservations_submit_text" value="<?php echo $reservations_edit_options['submit_text'];?>" style="width:50%">
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'Text over login area - optional' , 'easyReservations' ));?>:</i>
							<textarea name="reservations_edit_login_text" style="width:100%;height:80px;margin-top:4px"><?php echo $reservations_edit_options['login_text']; ?></textarea>
						</td>
					</tr>
					<tr class="alternate">
						<td>
							&nbsp;<i><?php echo __( 'Text over edit area - optional' , 'easyReservations' ); ?>:</i>
							<textarea name="reservations_edit_text" style="width:100%;height:80px;margin-top:4px"><?php echo $reservations_edit_options['edit_text']; ?></textarea>
						</td>
					</tr>
					<tr  id="easy-edit-table-cols">
						<td>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Table Settings' , 'easyReservations' ); ?></b><br>
								<i><?php echo __( 'Table of other reservations from the same email' , 'easyReservations' ); ?></i><br>
							</span>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Informations' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="id" <?php checked(in_array('id', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'ID' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="name" <?php checked(in_array('name', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Name' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="date" <?php checked(in_array('date', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Date' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="persons" <?php checked(in_array('persons', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Persons' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="reservated" <?php checked(in_array('reservated', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Reservated' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="status" <?php checked(in_array('status', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Status' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="room" <?php checked(in_array('room', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Resource' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="roomn" <?php checked(in_array('roomn', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Resource Number' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="price" <?php checked(in_array('price', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Price' , 'easyReservations' ); ?></label>
							</span>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Status' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="yes" <?php checked(in_array('yes', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'approved' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="" <?php checked(in_array('', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'pending' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="no" <?php checked(in_array('no', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'rejected' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="del" <?php checked(in_array('del', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'trashed' , 'easyReservations' ); ?></label><br>
								<b><?php echo __( 'Time' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_time[]" value="past" <?php checked(in_array('past', $reservations_edit_options['table_time']), true); ?>> <?php echo __( 'Past' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_time[]" value="current" <?php checked(in_array('current', $reservations_edit_options['table_time']), true); ?>> <?php echo __( 'Current' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_time[]" value="future" <?php checked(in_array('future', $reservations_edit_options['table_time']), true); ?>> <?php echo __( 'Future' , 'easyReservations' ); ?></label><br>
							</span>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Other' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_style" <?php checked($reservations_edit_options['table_style'], 1); ?>> <?php echo __( 'use style' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_more" value="" <?php checked($reservations_edit_options['table_more'], 1); ?>> <?php echo __( 'only show for guest with >1 reservations' , 'easyReservations' ); ?></label><br>
							</span>
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
		<input type="button" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>" onclick="document.getElementById('er_main_set').submit(); return false;" style="margin-top:7px;" class="easySubmitButton-primary" style="margin-top:4px" >
		</form>
			</td><td style="width:1%;" valign="top">
			</td><td style="width:29%;" valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-bottom:7px;" cellspacing="0" cellpadding="0" style="background:#fff;">
					<thead>
						<tr>
							<th colspan="2"> <?php printf ( __( 'Status' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="font-weight:bold;padding:10px;text-align:center"><span style="width:20%;display: inline-block">Version: 2.1.1</span><span style="width:30%;display: inline-block">Last update: 04.07.2012</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
						</tr>
						<tr class="alternate" style="">
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/" target="_blank"><?php echo __( 'Documentation' , 'easyReservations' );?></a></td>
						</tr>
						<tr>
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/general/" target="_blank"><?php echo __( 'Support forums' , 'easyReservations' );?></a></td>
						</tr>
						<tr class="alternate">
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/module/" target="_blank"><?php echo __( 'Modules' , 'easyReservations' );?></a></td>
						</tr>
						<tr>
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/" target="_blank"><?php echo __( 'Rate the Plugin' , 'easyReservations' );?>, please!</a></td>
						</tr>
					</tbody>
				</table>
				<?php do_action( 'er_set_main_side_top' ); ?>
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
	add_action('admin_print_footer_scripts',  'easy_add_my_quicktags'); //add buttons to quicktag
	?><script>
			function setDefaultForm(){
				var Default = '[error]\n';
					Default += '<h1>Reserve now!<span style="float:right;margin:10px">[show_price]</span></h1>\n';
					Default += '<p>General informations</p>\n\n';
					Default += '<label>Arrival Date\n';
					Default += '<span class="small">When do you come?</span>\n';
					Default += '</label><span class="row">[date-from style="width:75px"] [date-from-hour style="width:42px" value="12"]:[date-from-min style="width:42px"]</span>\n\n';

					Default += '<label>Departure Date\n';
					Default += '<span class="small">When do you go?</span>\n';
					Default += '</label><span class="row">[date-to style="width:75px"] [date-to-hour style="width:42px" value="12"]:[date-to-min style="width:42px"]</span>\n\n';

					Default += '<label>Resource\n';
					Default += '<span class="small">Where you want to sleep?</span>\n';
					Default += '</label>[resources]\n\n';

					Default += '<label>Adults\n';
					Default += '<span class="small">How many guests?</span>\n';
					Default += '</label>[adults 1 10]\n\n';

					Default += '<label>Children&rsquo;s\n';
					Default += '<span class="small">With children&rsquo;s?</span>\n';
					Default += '</label>[childs 0 10]\n\n';

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
					Default += '</label>[custom textarea Message]\n\n';

					Default += '<label>Captcha\n';
					Default += '<span class="small">Type in code</span>\n';
					Default += '</label>[captcha]\n\n';

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
					<form id="form1" name="form1" style="display:inline-block;">
						<div style="float: left;">
							<select name="jumpmenu" id="jumpmenu" onChange="jumpto(document.form1.jumpmenu.options[document.form1.jumpmenu.options.selectedIndex].value)">
								<option><?php printf ( __( 'Add Field' , 'easyReservations' ));?></option>
								<option value="date-from"><?php printf ( __( 'Arrival Date' , 'easyReservations' ));?> [date-from]</option>
								<option value="date-from-hour"><?php printf ( __( 'Arrival Hour' , 'easyReservations' ));?> [date-from-min]</option>
								<option value="date-from-min"><?php printf ( __( 'Arrival Minute' , 'easyReservations' ));?> [date-from-hour]</option>
								<option value="date-to"><?php printf ( __( 'Departure Date' , 'easyReservations' ));?> [date-to]</option>
								<option value="units"><?php printf ( __( 'Times of stay' , 'easyReservations' ));?> [times]</option>
								<option value="date-to-hour"><?php printf ( __( 'Departure Hour' , 'easyReservations' ));?> [date-from]</option>
								<option value="date-to-min"><?php printf ( __( 'Departure Minute' , 'easyReservations' ));?> [date-from]</option>
								<option value="rooms"><?php printf ( __( 'Resources' , 'easyReservations' ));?> [resources]</option>
								<option value="adults"><?php printf ( __( 'Adults' , 'easyReservations' ));?> [adults]</option>
								<option value="childs"><?php printf ( __( 'Childs' , 'easyReservations' ));?> [childs]</option>
								<option value="thename"><?php printf ( __( 'Name' , 'easyReservations' ));?> [thename]</option>
								<option value="email"><?php printf ( __( 'eMail' , 'easyReservations' ));?> [email]</option>
								<option value="country"><?php printf ( __( 'Country' , 'easyReservations' ));?> [country]</option>
								<option value="custom"><?php printf ( __( 'Custom' , 'easyReservations' ));?> [custom]</option>
								<option value="price"><?php printf ( __( 'Price' , 'easyReservations' ));?> [price]</option>
								<option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?> [hidden]</option>
								<option value="captcha"><?php printf ( __( 'Captcha' , 'easyReservations' ));?> [captcha]</option>
								<option value="show_price"><?php printf ( __( 'Display Price' , 'easyReservations' ));?> [show_price]</option>
								<option value="error"><?php printf ( __( 'Display Errors' , 'easyReservations' ));?> [error]</option>
								<?php do_action('easy-form-js-select'); ?>
								<option value="submit"><?php printf ( __( 'Submit Button' , 'easyReservations' ));?> [submit]</option>
							</select>
						</div>
						<div id="Text" style="float: left;"></div>
						<div id="Text2" style="float: left;"></div>
						<div id="Text3" style="float: left;"></div>
						<div id="Text4" style="float: left;"></div>
						<a href="javascript:resetform();" class="easySubmitButton-primary" style="margin:2px 1px 0px 1px"><?php printf ( __( 'Reset' , 'easyReservations' ));?></a>
						<div id="formsettings" style="margin-top:2px;"></div>
					</form>
					<form method="post" action="admin.php?page=reservation-settings&site=form<?php if($formnameget!=""){ echo '&form='.$formnameget; } ?>"  id="reservations_form_settings" name="reservations_form_settings" style="margin-top:-2px">
						<input type="hidden" name="action" value="reservations_form_settings"/>
						<input type="hidden" name="formnamesgets" value="<?php echo $formnameget; ?>"/>
						<input type='hidden' value='<?php echo stripslashes($reservations_form); ?>' name="resetforrm">
							<?php wp_editor( stripslashes($reservations_form), 'reservations_formvalue', array( 'textarea_rows' => 48, 'wpautop' => false, 'tinymce' => false, 'media_buttons' => false, 'quicktags' => array('buttons' => 'strong,em,link,img,ul,ol,li' ) ) ); ?>
						<div style="margin:8px 1px;">
							<input type="button" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>" onclick="document.getElementById('reservations_form_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" >
							<input type="button" value="<?php printf ( __( 'Default Form' , 'easyReservations' ));?>" onClick="setDefaultForm();" class="easySubmitButton-secondary" >
							<input type="button" value="<?php printf ( __( 'Reset Form' , 'easyReservations' ));?>" onClick="resteText();" class="easySubmitButton-secondary" >
						</div>
					</form>
					</td>
					<td style="width:40%;vertical-align: top;">		
					<div style="text-align:center;vertical-align:middle;height:30px;font-weight:bold;"><?php printf ( __( 'Include to Page or Post with' , 'easyReservations' ));?> <code class="codecolor">[<?php echo $howload; ?>]</code></div>
						<div id="Helper"></div>
						<table class="widefat">
							<thead>
								<tr>
									<th><?php echo  __( 'Information' , 'easyReservations' ); ?></th>
									<th><?php echo  __( 'Tag' , 'easyReservations' ); ?></th>
									<th style="text-align:center;"><?php echo  __( 'If unused' , 'easyReservations' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 1; jumpto(document.form1.jumpmenu.options[1].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Arrival Date' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field with datepicker' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-from]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 2; jumpto(document.form1.jumpmenu.options[2].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Arrival Hour' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-23' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-from-hour]</code></td>
									<td style="text-align:center;">12:00</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 3; jumpto(document.form1.jumpmenu.options[3].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Arrival Minute' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-59' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-from-min]</code></td>
									<td style="text-align:center;">0</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 4; jumpto(document.form1.jumpmenu.options[4].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Departure Date' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field with datepicker' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-to]</code></td>
									<td style="text-align:center;">Arrival Date + 1 <?php echo ucfirst(easyreservations_interval_infos(0, 0, 1)); ?></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 5; jumpto(document.form1.jumpmenu.options[5].value)" style="cursor:pointer;">
									<td><b>&#10132<?php echo  __( 'Times' , 'easyReservations' ).' ('.easyreservations_interval_infos(0, 0, 1).')'; ?></b> &#10132; <code class="codecolor">[hidden times nr]</code><br><i><?php echo  __( 'Select of definable numbers' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[times]</code></td>
									<td style="text-align:center;">1 <?php echo ucfirst(easyreservations_interval_infos(0, 0, 1)); ?></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 6; jumpto(document.form1.jumpmenu.options[6].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Departure Hour' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-23' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-to-hour]</code></td>
									<td style="text-align:center;">12:00</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 7; jumpto(document.form1.jumpmenu.options[7].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Departure Minute' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-59' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-to-min]</code></td>
									<td style="text-align:center;">0</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 8; jumpto(document.form1.jumpmenu.options[8].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Resources' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden resource id]</code><br><i><?php echo  __( 'Select of excludable resources' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[resources]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 9; jumpto(document.form1.jumpmenu.options[9].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Adults' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden adults nr]</code><br><i><?php echo  __( 'Select of definable numbers' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[adults]</code></td>
									<td style="text-align:center;">1</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 10; jumpto(document.form1.jumpmenu.options[10].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Children\'s' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden childs nr]</code><br><i><?php echo  __( 'Select of definable numbers' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[childs]</code></td>
									<td style="text-align:center;">0</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 11; jumpto(document.form1.jumpmenu.options[11].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Name' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[thename]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 12; jumpto(document.form1.jumpmenu.options[12].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'eMail' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[email]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 13; jumpto(document.form1.jumpmenu.options[13].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Country' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select of countrys' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[country]</code></td>
									<td style="text-align:center;">unknown</td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 14; jumpto(document.form1.jumpmenu.options[14].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Custom' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Custom field, area, select, radio or checkbox' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[custom]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 15; jumpto(document.form1.jumpmenu.options[15].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Price' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Custom select, radio or checkbox with effect on price' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[price]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 16; jumpto(document.form1.jumpmenu.options[16].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Hidden' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Fix &amp; hide resource, arrival, departure, persons or childs' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[hidden]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 17; jumpto(document.form1.jumpmenu.options[17].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Captcha' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field and captcha image' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[captcha]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 18; jumpto(document.form1.jumpmenu.options[18].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Display price' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Box with live price calculation' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[show_price]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 19; jumpto(document.form1.jumpmenu.options[19].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Display Errors' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Box with errors' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[error]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();document.form1.jumpmenu.selectedIndex = 20; jumpto(document.form1.jumpmenu.options[20].value)" style="cursor:pointer;">
									<td><b><?php echo  __( 'Submit' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Submit button with definable text' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[submit]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<?php do_action('easy-add-forms-table-col'); ?>
								<tr>
									<td colspan="3" style="text-align:center;">&#10132; = <b>alternative</b> &#10008; = <b>required</b></td>
								</tr>
							</tbody>
						</table>
						<?php
							$couerrors=0;
							$gute=0;
							$formgood='';
							$formerror ='';
							if(preg_match('/\[date-from/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[date-from]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>';}
							if(preg_match('/\[date-to/', $reservations_form) OR preg_match('/\[times/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[date-to]</code> '.__( 'or' , 'easyReservations' ).' <code class="codecolor">[times]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[date-to/', $reservations_form) && preg_match('/\[times/', $reservations_form)){
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[date-to]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[times]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[resources/', $reservations_form) || preg_match('/\[hidden resource/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[resources]</code> '.__( 'or' , 'easyReservations' ).' <code class="codecolor">[hidden resource resourceID]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[resources/', $reservations_form) && preg_match('/\[hidden resource/', $reservations_form)){
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[resources]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[hidden resources resourceID]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
							if(preg_match('/\[email/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[email]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[thename/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[thename]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[submit/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[submit x]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							$coutall=$gute+$couerrors;
							if($couerrors > 0){ ?>
							<div class="explainbox" style="background:#FCEAEA; border-color:#FF4242;box-shadow: 0 0 2px #F99F9F;margin-top:5px">
								<?php echo __( 'This form is not valid' , 'easyReservations' ).' '.$gute.'/'.$coutall.' P.<br>'; echo $formerror; ?>
							</div><?php } else { ?>
							<div class="explainbox" style="background:#E8F9E8; border-color:#68FF42;box-shadow: 0 0 2px #9EF7A1;margin-top:5px">
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

		var tvalue = document.getElementById("form-value");
		var maxlength = document.getElementById("form-maxlength");
		var style = document.getElementById("form-style");
		var title = document.getElementById("form-title");
		var disabled = document.getElementById("form-disabled");
		var checkd = document.getElementById("form-checked");
		var error_title = document.getElementById("easy-error-title");
		var error_message = document.getElementById("easy-error-message");
		var other = document.getElementById("easy-other");
		var price1 = document.getElementById("price1");
		var price2 = document.getElementById("price2");
		if(tvalue && tvalue.value != '') tag += ' value="'+tvalue.value+'"';
		if(maxlength && maxlength.value != '') tag += ' maxlength="'+maxlength.value+'"';
		if(style && style.value != '')  tag += ' style="'+style.value+'"';
		if(title && title.value != '')  tag += ' title="'+title.value+'"';
		if(other && other.value != '')  tag += ' ' + other.name + '="'+other.value+'"';
		if(disabled && disabled.checked != false )  tag += ' disabled="disabled"';
		if(checkd && checkd.checked != false )  tag += ' checked="checked"';
		if(error_title && error_title.value != '')  tag += ' error_title="'+error_title.value+'"';
		if(error_message && error_message.value != '')  tag += ' error_message="'+error_message.value+'"';
		if(price1 && price1.checked != false && price2.checked != false)  tag += ' pb';
			else if(price1 && price1.checked != false) tag += ' pp';
				else if(price2 && price2.checked != false) tag += ' pn';
				
		 <?php do_action('easy-form-js-add-func'); ?>

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
		document.getElementById("formsettings").innerHTML = '';
		thetext1 = false;
		thetext2 = false;
		thetext3 = false;
		thetext4 = false;
	}

	function addformsettings(typ){
		//var Settings = 'value style title  maxlength';
		var Settings = '';
		if(typ == 'date-from' || typ == 'date-to' ){
			Settings += '<?php echo __( 'Value' , 'easyReservations' ); ?>: <input type="text" id="form-value" class="datepicker" style="width:80px" value="+15"> ';
		} else if(typ == 'date-from-hour' || typ == 'date-to-hour' ){
			Settings += '<?php echo __( 'Selected' , 'easyReservations' ); ?>:  <select id="form-value"><?php echo easyReservations_num_options("00",23,12); ?></select>';
		} else if(typ == 'date-from-min' || typ == 'date-to-min' ){
			Settings += '<?php echo __( 'Selected' , 'easyReservations' ); ?>:  <select id="form-value"><?php echo easyReservations_num_options("00",59); ?></select>';
		} else if(typ == 'email' || typ == 'message' || typ == 'thename'  || typ == 'input' || typ == 'captcha' || typ == 'submit'){
			Settings += '<?php echo __( 'Value' , 'easyReservations' ); ?>: <input type="text" id="form-value" style="width:80px"> ';
		} else if( typ == "country" ){
			Settings += '<?php echo __( 'Selected' , 'easyReservations' ); ?>: <select id="form-value" style="width:100px"><?php echo easyReservations_country_select(); ?></select> ';
		} else if( typ == "rooms" ){
			Settings += '<?php echo __( 'Selected' , 'easyReservations' ); ?>: <select id="form-value" style="width:100px"><?php echo $roomsoptions; ?></select> ';
		} else if( typ == "amount" ){
			Settings += '<?php echo __( 'Selected' , 'easyReservations' ); ?>: <select id="form-value"><?php echo easyReservations_num_options(1,100,50); ?></select> ';
		}

		if(typ == 'date-from' || typ == 'date-to' || typ == 'email' || typ == 'message' || typ == 'thename' || typ == 'input'){
			Settings += 'Maxlength: <select id="form-maxlength"><?php echo easyReservations_num_options(1,100,50); ?></select> ';
		}

		Settings += 'Style: <input type="text" id="form-style"> ';
		Settings += 'Title: <input type="text" id="form-title"> ';
		if(typ != 'error' && typ != 'show_price') Settings += '<input type="checkbox" id="form-disabled"> Disabled ';
		if(typ == 'checkbox') Settings += '<input type="checkbox" id="form-checked"> Checked ';
		document.getElementById("formsettings").innerHTML = Settings;
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
		} else if (x == "error"){
			Output = ' <?php echo __( 'Error title' , 'easyReservations' ); ?>: <input id="easy-error-title" type="text" value="Errors found in the form"> <?php echo __( 'Error message' , 'easyReservations' ); ?>: <input id="easy-error-message" type="text" value="There is a problem with the form, please check and correct the following:"> ';
			document.getElementById("Text2").innerHTML += Output;
			addformsettings(x);
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if(x == "date-from" || x == "date-to" || x == "date-from-hour" || x == "date-to-hour" || x == "date-from-min" || x == "date-to-min" || x == "email" || x == "thename" || x == "country" || x == 'captcha' || x == 'submit' || x == 'coupon'){
			addformsettings(x);
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if(x == "rooms"){
			addformsettings(x);
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<?php echo __( 'Exclude by ID' , 'easyReservations' ); ?> <input type="text" name="exclude" id="easy-other" value=""> ';
			document.getElementById("Text").innerHTML += Output;
		} else if(x == "show_price") {
			addformsettings(x);
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
			var Output  = '<?php echo __( 'Exclude by ID' , 'easyReservations' ); ?> <input type="text" name="before" id="easy-other" value="Price:">';
			document.getElementById("Text").innerHTML += Output;
		} else if(x == "adults" || x == "childs" || x == "units"){
			end = 1;
			var Output  = '&nbsp;<b><?php echo __( 'Min' , 'easyReservations' ); ?>:</b> <select name="zwei" id="zwei"><?php echo easyReservations_num_options(0,100,0); ?></select> <b><?php echo __( 'Max' , 'easyReservations' ); ?>:</b> <select name="drei" id="drei"><?php echo easyReservations_num_options(0,100,10); ?></select>';
			document.getElementById("Text2").innerHTML += Output;
			addformsettings('amount');
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "submit"){

			var Output  = '<input type="text" name="eins" id="eins" value="Name">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Type in value of submit button' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.form1.jumpmenu.disabled=true;
			var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="margin-top:2px" style="line-height:1;margin:2px 2px 0px 2px"><b><?php echo __( 'Add' , 'easyReservations' ); ?></b></a>';

		} else if (x == "hidden") {
			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="room"><?php echo __( 'Resource' , 'easyReservations' ); ?></option><option value="from"><?php echo __( 'Arrival Date' , 'easyReservations' ); ?></option><option value="date-from-hour"><?php echo __( 'Arrival Hour' , 'easyReservations' ); ?><option value="date-from-min"><?php echo __( 'Arrival Minute' , 'easyReservations' ); ?></option><option value="to"><?php echo __( 'Departure Date' , 'easyReservations' ); ?></option><option value="units"><?php echo __( 'Times' , 'easyReservations' ); ?></option><option value="date-to-hour"><?php echo __( 'Departure Hour' , 'easyReservations' ); ?><option value="date-to-min"><?php echo __( 'Departure Minute' , 'easyReservations' ); ?></option><option value="persons"><?php echo __( 'Persons' , 'easyReservations' ); ?></option><option value="childs"><?php echo __( 'Childrens' , 'easyReservations' ); ?></option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select type of hidden input' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for fixing informations to the form & hide it from guest' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo __( 'Resource' , 'easyReservations' ); ?></b> <?php echo __( 'Fix a resource to the form; dont use it with [resources] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo __( 'Arrival Date' , 'easyReservations' ); ?></b> <?php echo __( 'Fix an arrival date to the form; dont use it with [date-from] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo __( 'Departure Date' , 'easyReservations' ); ?></b> <?php echo __( 'Fix a departure date to the form; dont use it with [date-to] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo __( 'Times' , 'easyReservations' ); ?></b> <?php echo __( 'Fix times to the form; dont use it with [date-to] or [times] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo __( 'Adults' , 'easyReservations' ); ?></b> <?php echo __( 'Fix an amount of adults to the form; dont use it with [adults] in the same form' , 'easyReservations' ); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo __( 'Childrens' , 'easyReservations' ); ?></b> <?php echo __( 'Fix an amount of childrens to the form; dont use it with [childs] in the same form' , 'easyReservations' ); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} <?php do_action('easy-form-js-1'); ?>
	} else if(thetext2 == false){
		if (x == "textarea" || x == "text" || x == "check"){
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name"> <input type="checkbox" id="req" name="req" value="*"> <?php echo __( 'Required' , 'easyReservations' ); ?> ';
			document.getElementById("Text2").innerHTML += Output;
			addformsettings('input');

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Type in a name for the' , 'easyReservations' ); ?> <span style="text-transform:capitalize">' + x + '</span> <?php echo __( 'input you want to add' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			end = 1;
			document.form1.eins.disabled=true;
		} else if (x == "checkbox"){
			end = 1;

			var Output  = '<input type="text" name="zwei" id="zwei" value="Name"><input type="text" name="drei" id="drei" value="Value">';
			Output += easy_price_checks();
			document.getElementById("Text2").innerHTML += Output;
			addformsettings('checkbox');

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in a Name for the Checkbox' , 'easyReservations' ); ?></b>';
			Help += '<br><b>2. <?php echo __( 'Type in a value for the checkbox' , 'easyReservations' ); ?></b>',
			Help += '<br> &emsp; <?php echo __( 'The value has to match ' , 'easyReservations' ); ?><br>&emsp; <code>option:price</code><br> &emsp; <?php printf( __( 'Price: negative for reduction %1$s  zero for no change %2$s positiv for increase %3$s '), '<code>-30.75</code>', '<code>0</code>', '<code>20.2</code>' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "select" || x == "radio") {
			var Output  = '<input type="text" name="zwei" id="zwei" value="Name" onClick="jumpto(document.form1.zwei.value);">';
			addformsettings('select');
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

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Select a resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

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
		} else if (x == "units") {
			end = 1;
			var Output  = '<select name="zwei" id="zwei"><?php echo easyReservations_num_options(0,100,0); ?></select>';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo __( 'Select the amount of times you want to fix' , 'easyReservations' ); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "date-from-hour" || x == "date-to-hour") {
			end = 1;
			var Output  = '<select name="zwei" id="zwei"><?php echo easyReservations_num_options(0,23,12); ?></select>';
			document.getElementById("Text3").innerHTML += Output;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "date-from-min" || x == "date-to-min") {
			end = 1;
			var Output  = '<select name="zwei" id="zwei"><?php echo easyReservations_num_options(0,59,0); ?></select>';
			document.getElementById("Text3").innerHTML += Output;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} 
	} else if(thetext3 == false){
		if (x == "Name") {
			end = 1;
			var Output  = '<input type="text" name="drei" id="drei" value="Options">';
			if(first == "custom") Output += ' <input type="checkbox" id="req" name="req" value="*"> <?php echo __( 'Required' , 'easyReservations' ); ?> ';
			else Output += easy_price_checks();

			document.getElementById("Text3").innerHTML += Output;
			thetext3 = true;
		}
	}
	if (end == 1) {
		var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="margin-top:2px" style="line-height:1;margin:2px 2px 0px 2px"><b><?php echo __( 'Add' , 'easyReservations' ); ?></b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
}

function easy_price_checks(){
	Output = '<input type="checkbox" id="price1" name="req" value="pp"> <?php echo __( 'price per person' , 'easyReservations' ); ?>';
	Output += '<input type="checkbox" id="price2" name="req" value="pn"> <?php echo __( 'price per night' , 'easyReservations' ); ?>';
	return Output;
}
<?php do_action('easy-form-js-function'); ?>
</script>
<?php } elseif($settingpage=="email"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EMAIL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource]<br>Price: [price]<br>[customs]";
$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource]<br><br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource]<br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
$emailstandart6="Reservation got edited by Guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>[changelog]";
$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>eMail: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Childs: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
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
		<?php if(!function_exists('easyreservations_generate_email_settings')){ 
			$reservations_email_sendmail=get_option("reservations_email_sendmail");
			$reservations_email_to_admin=get_option("reservations_email_to_admin");
			$reservations_email_to_user=get_option("reservations_email_to_user");
			$reservations_email_to_user_edited=get_option("reservations_email_to_user_edited");
			$reservations_email_to_user_admin_edited=get_option("reservations_email_to_user_admin_edited");
			$reservations_email_to_admin_edited=get_option("reservations_email_to_admin_edited");
			$reservations_email_to_userapp=get_option("reservations_email_to_userapp");
			$reservations_email_to_userdel=get_option("reservations_email_to_userdel"); ?>
		<table style="width:99%;" cellspacing="0">
			<tr style="width:60%;" cellspacing="0">
				<td valign="top">
		<?php do_action('er_set_emails_add_before'); ?>
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
					<td><input type="" name="reservations_email_sendmail_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_sendmail['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_sendmail_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_sendmail['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
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
					<td><input type="text" name="reservations_email_to_admin_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_admin['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
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
					<td><input type="text" name="reservations_email_to_user_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_user['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
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
					<td><input type="text" name="reservations_email_to_userapp_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_userapp['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userapp_msg"  id="reservations_email_to_userapp_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_userapp['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
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
					<td><input type="text" name="reservations_email_to_userdel_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_userdel['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userdel_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_userdel['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on edit from admin' , 'easyReservations' ));?><span style="float:right;margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_user_admin_edited_check" <?php checked(1, $reservations_email_to_user_admin_edited['active']); ?> style="margin-top:3px;margin-left:-1px;"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail7();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_admin_edited_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_user_admin_edited['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_admin_edited_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user_admin_edited['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on edit from user' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to admin' , 'easyReservations' ); ?></b><span style=";margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_admin_edited_check" <?php checked(1, $reservations_email_to_admin_edited['active']); ?> style="margin-top:3px;margin-left:-1px;"></span><input type="button" value="Default Mail" onClick="addtextforemail6();" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_admin_edited_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_admin_edited['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
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
					<td><input type="text" name="reservations_email_to_user_edited_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_user_edited['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_edited_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user_edited['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<?php do_action('er_set_emails_add_after'); ?>
		<input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
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
							<p><code class="codecolor">[arrival]</code> <i><?php printf ( __( 'arrival date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[departure]</code> <i><?php printf ( __( 'departure date' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[times]</code> <i><?php echo __( 'amount of', 'easyReservations' ).' '.easyreservations_interval_infos();?></i></p>
							<p><code class="codecolor">[reserved]</code> <i><?php printf( __( 'amount of %s from date of reservation' , 'easyReservations' ), easyreservations_interval_infos());?></i></p>
							<p><code class="codecolor">[adults]</code> <i><?php printf ( __( 'amount of adults' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[childs]</code> <i><?php printf ( __( 'amount of childs' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[country]</code> <i><?php printf ( __( 'country of guest' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[resource]</code> <i><?php printf ( __( 'name of resource' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[resourcenumber]</code> <i><?php printf ( __( 'name of resource number' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[price]</code> <i><?php printf ( __( 'show price' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[paid]</code> <i><?php printf ( __( 'show paid amount' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[customs]</code> <i><?php printf ( __( 'custom fields' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[prices]</code> <i><?php printf ( __( 'price fields' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[changlog]</code> <i><?php printf ( __( 'show changes after edits' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[editlink]</code> <i><?php printf ( __( 'link to user edit' , 'easyReservations' ));?></i></p>
							<?php do_action('easy-email-list'); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		</td></tr>
	</table>
	<?php } do_action('easy-email-settings'); ?>
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
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-bottom:7px;" cellspacing="0" cellpadding="0" style="background:#fff;">
				<thead>
					<tr>
						<th colspan="2"> <?php printf ( __( 'Status' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="font-weight:bold;padding:10px;text-align:center"><span style="width:20%;display: inline-block">Version: 2.1.1</span><span style="width:30%;display: inline-block">Last update: 04.07.2012</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
					</tr>
					<tr class="alternate" style="">
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/"><?php echo __( 'Documentation' , 'easyReservations' );?></a></td>
					</tr>
					<tr>
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/general/"><?php echo __( 'Support forums' , 'easyReservations' );?></a></td>
					</tr>
					<tr class="alternate">
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/module/"><?php echo __( 'Modules' , 'easyReservations' );?></a></td>
					</tr>
					<tr>
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/"><?php echo __( 'Rate Plugin' , 'easyReservations' );?></a></td>
					</tr>
				</tbody>
			</table>
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
						include_once(ABSPATH . WPINC . '/class-feed.php');

						if(function_exists('fetch_feed')) {
						// fetch feed items
						$rss = fetch_feed('http://easyreservations.org/feed');
						if(!is_wp_error($rss)) : // error check
							$maxitems = $rss->get_item_quantity(5); // number of items
							$rss_items = $rss->get_items(0, $maxitems);
						endif;
						// display feed items ?>
						<dl>
						<?php if($maxitems == 0) echo '<dt>Feed not available.</dt>'; // if empty
						else foreach ($rss_items as $item) : ?>
							<dt>
								<a href="<?php echo $item->get_permalink(); ?>" title="<?php echo $item->get_date('j F Y @ g:i a'); ?>">
								<?php echo $item->get_title(); ?>
								</a> <i><?php echo substr($item->get_description(),0,  50); ?></i>
							</dt>
							<dd></dd>
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
</div><?php }

?>