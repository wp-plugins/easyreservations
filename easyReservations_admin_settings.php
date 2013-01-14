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
		$wpdb->query( "DELETE FROM ".$wpdb->prefix ."reservations WHERE departure < NOW() AND approve != 'yes' " );
		$prompt = '<div class="update"><p>'.__( 'Database cleaned' , 'easyReservations' ).'</p></div>';
	}

	if(isset($_POST['easy-set-main'])){ //Set Reservation settings
		if(!wp_verify_nonce($_POST['easy-set-main'], 'easy-set-main' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		if(isset($_POST["reservations_uninstall"])) $reservations_uninstall = 1; else $reservations_uninstall = 0;
		if(isset($_POST["reservations_time"])) $reservations_time = 1; else $reservations_time = 0;
		if(isset($_POST["reservations_tutorial"])) $tutorial = 1; else $tutorial = 0;
		if(isset($_POST['reservations_resourcemerge_box'])) $mergeres = $_POST['reservations_resourcemerge'];
		else $mergeres = 0;
		update_option("reservations_uninstall", $reservations_uninstall);
		if(isset($_POST['reservations_currency_whitespace'])) $white = 1;
		else $white = 0;
		$settings_array = array( 'style' => $_POST["reservations_style"], 'currency' => array('sign' => $_POST["reservations_currency"], 'whitespace' => $white, 'decimal' => $_POST["reservations_currency_decimal"], 'divider1' => $_POST["reservations_currency_divider1"], 'divider2' => $_POST["reservations_currency_divider2"], 'place' => $_POST['reservations_currency_place']), 'date_format' => $_POST["reservations_date_format"], 'time' => $reservations_time, 'tutorial' => $tutorial, 'mergeres' => array('merge' => $mergeres,'blockbefore'=>$_POST['blockbefore'], 'blockafter' => $_POST['blockafter']));
		update_option("reservations_settings", $settings_array);
		update_option("reservations_regular_guests", $_POST["regular_guests"]);
		update_option("reservations_support_mail", $_POST["reservations_support_mail"]);
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

		if(isset($_POST["reservations_email_to_admin_canceled_msg"])){
			if(isset($_POST["reservations_email_to_admin_canceled_check"])) $reservations_email_to_admin_canceled_check = 1; else $reservations_email_to_admin_canceled_check = 0;
			if(is_array($_POST["reservations_email_to_admin_canceled_msg"])) $_POST["reservations_email_to_admin_canceled_msg"] = implode($_POST["reservations_email_to_admin_canceled_msg"]);
			$reservations_email_to_admin_canceled = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_canceled_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_admin_canceled_subj"]),
				'active' => $reservations_email_to_admin_canceled_check
			);
			update_option("reservations_email_to_admin_canceled",$reservations_email_to_admin_canceled);
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
		$prompt = '<div class="updated"><p>'.__( 'Email settings saved' , 'easyReservations' ).'</p></div>';
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
				$forms.=' | <a href="admin.php?page=reservation-settings&site=form&form='.$formcutedname.'">'.$formbigcutedname.'</a> <a href="admin.php?page=reservation-settings&site=form&deleteform='.$formcutedname.'"><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'images/delete.png"></a>';
			}
		}
	}

	do_action( 'er_set_save' );

	if($settingpage=="email") $ifemailcurrent='class="current"'; ?>
<h2>
	<?php echo __( 'Reservations Settings' , 'easyReservations' );?>
</h2>
<?php if(isset($prompt)) echo $prompt; ?>
<div id="wrap">
<div class="tabs-box" style="width:99%">
	<ul class="tabs">
		<li><a <?php if(isset($ifgeneralcurrent)) echo $ifgeneralcurrent; ?> href="admin.php?page=reservation-settings"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/pref.png"> <?php printf ( __( 'General' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifformcurrent)) echo $ifformcurrent; ?> href="admin.php?page=reservation-settings&site=form"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/form.png"> <?php printf ( __( 'Form' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifemailcurrent)) echo $ifemailcurrent; ?> href="admin.php?page=reservation-settings&site=email"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/email.png"> <?php printf ( __( 'Emails' , 'easyReservations' ));?></a></li>
		<?php do_action( 'er_set_tab_add' ); ?>
		<li><a <?php if(isset($ifaboutcurrent)) echo $ifaboutcurrent; ?> href="admin.php?page=reservation-settings&site=about"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/logo.png"> <?php printf ( __( 'About' , 'easyReservations' ));?></a></li>
	</ul>
</div>
<?php do_action( 'er_add_settings_top' );
if($settingpage=="general"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GENERAL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Get current Options
	$reservations_settings = get_option("reservations_settings");
	$reservations_currency = $reservations_settings['currency'];
	if(!is_array($reservations_currency)) $reservations_currency = array('sign' => $reservations_currency, 'place' => 0, 'whitespace' => 1, 'divider1' => '.', 'divider2' => ',', 'decimal' => 1);
	$reservations_date_format = $reservations_settings['date_format'];
	$easyReservationSyle=$reservations_settings['style'];
	$reservation_support_mail = get_option("reservations_support_mail");
	$reservations_regular_guests = get_option('reservations_regular_guests');
	$permission_options=get_option("reservations_main_permission");
	$reservations_uninstall=get_option("reservations_uninstall");
	if(isset($reservations_settings['mergeres']) && is_array($reservations_settings['mergeres'])){
		$blockbefore = $reservations_settings['mergeres']['blockbefore'];
		$blockafter = $reservations_settings['mergeres']['blockafter'];
		$reservations_settings['mergeres'] = $reservations_settings['mergeres']['merge'];
	} else {
		$blockbefore = 0;
		$blockafter = 0;
	}
	if(!isset($reservations_settings['tutorial'])) $reservations_settings['tutorial'] = 1;?>
<table cellspacing="0" style="width:99%">
	<tr cellspacing="0">
		<td style="width:70%;" valign="top" >
		<?php
			$currencys = array('#8364' => 'Euro','#36' => 'Dollar','#165' => 'Yen','#162' => 'Cent','#402' => 'Florin','#163' => 'Pound','#8356' => 'Lire','#20803' => 'Hongkong Dollar','#8376' => 'Tenge','#8365' => 'Laos Kip','#8353' => 'Colon','#8370' => 'Guarani','#70;&#116' => 'Hungary Forint','#8369' => 'Uruguay Peso','#8360' => 'Indian Rupee','#8377' => 'Indian Rupee 2nd','#2547' => 'Bengali Rupee','#2801' => 'Gujarati  Rupee','#3065' => 'Tamil Rupee','#3647' => 'Thai Baht','#6107' => 'Khmer Riel','#66;&#90;&#36' => 'Belize Dollar','#36;&#98' => 'Bolivia Boliviano','#75;&#77' => 'Bosnia and Herzegovina Marka','#80' => 'Botswana Pula','#1083;&#1074' => 'Bulgaria Lev','#6107' => 'Cambodia Riel','#20803' => 'China Yuan','#8371' => 'Austral','#8372' => 'Hryvnia','#81' => 'Guatemala Quetzal','#8373' => 'Cedi','#8366' => 'Tugril','#84;&#76' => 'Turkish Lira','#8367' => 'Drachma','#76' => 'Honduras Lempira','#8363' => 'Vietnam Dong','#8358' => 'Naira','#1084;&#1072;&#1085' => 'Azerbaijan New Manat','#83;&#71;&#68' => 'Singapore Dollar',	'#1076;&#1077;&#1085' => 'Macedonia Denar','#8366' => 'Mongolia Tughrik','#1547' => 'Afghanistan Afghani','#8354' => 'Cruzeiro','#65020' => 'Omani Rial','#65510' => 'Won','#82&#112;&#46' => 'Indonesian rupiah sign','#73&#68;&#82' => 'Indonesian rupiah ISO','#608' => 'Philippine Peso','#80;&#104;&#11' => 'Philippine Peso 2nd','#986' => 'Brazilian Real','#76;&#115' => 'Brazilian Real 2nd','#77;&#84' => 'Nicaragua Cordoba','#82;&#77' => 'Malaysia Ringgit','#82;&#36' => 'Latvia Lat','#1083;&#1074' => 'Kazakhstan Tenge','#74;&#36' => 'Jamaica Dollar','#75;&#269' => 'Czech Koruna','#107;&#114' => 'Danish Krone','#107;&#110' => 'Croatia Kuna','#122;&#322' => 'Polish Zloty','#122;&#322' => 'Israeli Sheqel','#66;&#47;&#46' => 'Panamanian Balboa','#82;&#68;&#36' => 'Dominican Republic Peso','#78;&#79;&#75' => 'Norwegian Krone','#67;&#72;&#70' => 'Switzerland Franc','#108;&#101;&#1' => 'Romanian Leu','#1088;&#1091' => 'Russian Rouble','#82' => 'South African ZAR','#67;&#79;&#80' => 'Colombian peso','#66;&#115;&#46' => 'Venezuelan Bolivares',);
 			asort($currencys);
			$divider = array('.' => '.', ',' => ',', ' ' => 'whitespace', '' => '');
			$styles = array('widefat' =>__( 'Wordpress' , 'easyReservations' ),'greyfat' =>__( 'Grey' , 'easyReservations' ));
			if(file_exists(WP_PLUGIN_DIR . '/easyreservations/lib/modules/styles/admin/style_premium.css')) $styles['premium'] = __( 'Premium' , 'easyReservations' );
			$date_formats = array('Y/m/d' => date('Y/m/d', time()),'Y-m-d' => date('Y-m-d', time()),'m/d/Y' => date('m/d/Y', time()),'d-m-Y' => date('d-m-Y', time()),'d.m.Y' => date('d.m.Y', time()));
			if(isset($reservations_settings['mergeres']) && $reservations_settings['mergeres'] > 0) $thenum = $reservations_settings['mergeres'];
			else $thenum = 0;
			$timearray = array(0 => '0 '.__('minutes', 'easyReservations'),5 => '5 '.__('minutes', 'easyReservations'),10 =>'10 '. __('minutes', 'easyReservations'),15 => '15 '.__('minutes', 'easyReservations'),30 => '30'. __('minutes', 'easyReservations'),45 =>'45 '. __('minutes', 'easyReservations'),60=>'1 '.__('hour', 'easyReservations'),90=>'1.5 '.__('hours', 'easyReservations'),120=>'2 '.__('hours', 'easyReservations'),150=>'2.5 '.__('hours', 'easyReservations'),180=>'3 '.__('hours', 'easyReservations'),240=>'4 '.__('hours', 'easyReservations'),300=>'5 '.__('hours', 'easyReservations'),360=>'6 '.__('hours', 'easyReservations'),600=>'10 '.__('hours', 'easyReservations'),720=>'12 '.__('hours', 'easyReservations'),1080=>'18 '.__('hours', 'easyReservations'),1440=>'1 '.__('day', 'easyReservations'),2160=>'1.5 '.__('days', 'easyReservations'),2880=>'2 '.__('days', 'easyReservations'),4320=>'3 '.__('days', 'easyReservations'),5760=>'4 '.__('days', 'easyReservations'),7200=>'5 '.__('days', 'easyReservations'),8640=>'6 '.__('days', 'easyReservations'),10080=>'7 '.__('days', 'easyReservations'),20160=>'14 '.__('days', 'easyReservations'),40320=>'1 '.__('month', 'easyReservations'));

			$rows = array(
				'<img src="'.RESERVATIONS_URL.'images/email.png"> <b>'.__( 'Support email', 'easyReservations' ).'</b>' => '<input type="text" name="reservations_support_mail" value="'.$reservation_support_mail.'" style="width:50%">',
				'<img src="'.RESERVATIONS_URL.'images/dollar.png"> <b>'.__( 'Money format', 'easyReservations' ).'</b>' => array('currency_settings',easyreservations_generate_input_select('reservations_currency', $currencys, $reservations_currency['sign'], '', true).' '.easyreservations_generate_input_select('reservations_currency_place', array(__( 'after' , 'easyReservations' ),__( 'before' , 'easyReservations' )), $reservations_currency['place']).' '.__( 'Price' , 'easyReservations' ).' <input type="checkbox" name="reservations_currency_whitespace" '.checked($reservations_currency['whitespace'],1,false).'> '.__( 'Whitespace between price and currency sign' , 'easyReservations' ).'<br>'.__( 'Th. seperator' , 'easyReservations' ).': '.easyreservations_generate_input_select('reservations_currency_divider1', $divider, $reservations_currency['divider1']).' '.__( 'Dec. seperator' , 'easyReservations' ).': '.easyreservations_generate_input_select('reservations_currency_divider2', $divider, $reservations_currency['divider2']).' '.easyreservations_generate_input_select('reservations_currency_decimal',array('1' =>__('show decimals','easyReservations'), '0' =>  __( 'round' , 'easyReservations' )), $reservations_currency['decimal']).' '.__( 'Example' , 'easyReservations' ).':</strong> <span id="reservations_currency_example"></span>'),
				'<img src="'.RESERVATIONS_URL.'images/day.png"> <b>'.__( 'Date format', 'easyReservations' ).'</b>' => easyreservations_generate_input_select('reservations_date_format',$date_formats,$reservations_date_format),
				'<img src="'.RESERVATIONS_URL.'images/clock.png"> <b>'.__( 'Time reservations', 'easyReservations' ).'</b>' => '<input type="checkbox" name="reservations_time" '.checked($reservations_settings['time'],1,false).'> <i>'.__( 'Enable time for reservations ', 'easyReservations' ).'</i>',
				'<img src="'.RESERVATIONS_URL.'images/background.png"> <b>'.__( 'Admin Style', 'easyReservations' ).'</b>' => easyreservations_generate_input_select('reservations_style',$styles,$easyReservationSyle),
				'<img src="'.RESERVATIONS_URL.'images/house.png"> <b>'.__( 'Merge resources', 'easyReservations' ).'</b>' => '<input type="checkbox" id="checkmerge" name="reservations_resourcemerge_box" value="1" '.((isset($reservations_settings['mergeres']) && $reservations_settings['mergeres'] > 0) ? 'checked="checked"' : '').'> '.sprintf(__( 'Check availability over all resources with max %s reservations at the same time regardless of the resource' , 'easyReservations' ), '<select name="reservations_resourcemerge" onclick="document.getElementById(\'checkmerge\').checked = true;">'.easyreservations_num_options(0,99,$thenum).'</select>'),
				'<img src="'.RESERVATIONS_URL.'images/lock.png"> <b>'.__( 'Block time', 'easyReservations' ).'</b>' => __( 'Block' , 'easyReservations' ).' '.easyreservations_generate_input_select('blockbefore', $timearray, $blockbefore).' '.__( 'before and' , 'easyReservations' ).' '.easyreservations_generate_input_select('blockafter', $timearray, $blockafter).' '.__( 'after reservations' , 'easyReservations' ),
				'<img src="'.RESERVATIONS_URL.'images/help.png"> <b>'.__( 'Tutorial', 'easyReservations' ).'</b>' => '<input type="checkbox" name="reservations_tutorial" value="1" '.checked($reservations_settings['tutorial'],1,false).'> '.__( 'Enable tutorial mode' , 'easyReservations' ).' <a class="button" href="admin.php?page=reservation-settings&tutorial_histoy=0"> '.__( 'Reset' , 'easyReservations' ).'</a>',
				'<img src="'.RESERVATIONS_URL.'images/database.png"> <b>'.__( 'Uninstall', 'easyReservations' ).'</b>' => '<input type="checkbox" name="reservations_uninstall" value="1" '.checked($reservations_uninstall, 1,false).'> '.__( 'Delete settings, reservations and resources' , 'easyReservations' )
			);
			$rows = apply_filters('er_add_set_main_table_row', $rows);
			$table = easyreservations_generate_table('reservation_main_settings_table', __( 'General Settings', 'easyReservations' ).'<input type="submit" value="'. __( 'Save Changes' , 'easyReservations' ).'" onclick="document.getElementById(\'er_main_set\').submit(); return false;" class="easySubmitButton-primary" style="float:right" >', $rows);
			$rows = array(
				'col' => '&nbsp;<i'.__( 'Enter emails of important guests; seperated by comma. Reservations with this email will be highlighted.' , 'easyReservations' ).'</i><textarea name="regular_guests" style="width:100%;height:80px;margin-top:5px;">'.$reservations_regular_guests.'</textarea>'
			);
			$table.= easyreservations_generate_table('reservation_important_guests_table', __( 'Important Guests', 'easyReservations' ), $rows);
			echo easyreservations_generate_form('er_main_set', 'admin.php?page=reservation-settings', 'post', false, array('easy-set-main' => wp_create_nonce('easy-set-main')), $table);
			 do_action( 'er_set_main_out' ); ?>
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
							<td style="font-weight:bold;padding:10px;text-align:center"><span style="width:20%;display: inline-block">Version: <?php echo RESERVATIONS_VERSION; ?></span><span style="width:30%;display: inline-block">Last update: 12.12.2012</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
						</tr>
						<tr class="alternate">
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/" target="_blank" id="iddocumentation"><?php echo __( 'Documentation' , 'easyReservations' );?></a></td>
						</tr>
						<tr>
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/bug-reports/" target="_blank" id="idbugreport"><?php echo __( 'Report bug' , 'easyReservations' );?></a></td>
						</tr>
						<tr class="alternate"> 
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/premium/" target="_blank" id="idpremium"><?php echo __( 'Premium' , 'easyReservations' );?></a></td>
						</tr>
						<tr>
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/" target="_blank" id="idrate"><?php echo __( 'Rate the Plugin' , 'easyReservations' );?>, please!</a></td>
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
									<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/database.png"> <?php printf ( __( 'Delete all unapproved Old Reservations' , 'easyReservations' ));?>
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
								<th colspan="2" id="idpermission"> <?php printf ( __( 'Change Permissions' , 'easyReservations' ));?><input type="submit" onclick="document.getElementById('reservation_change_permissions').submit(); return false;" class="easySubmitButton-primary" style="float:right;" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
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
<script type="text/javascript">
	function easyreservations_currency_example(){
		var divider1 = jQuery('select[name=reservations_currency_divider1]').val();
		var divider2 = jQuery('select[name=reservations_currency_divider2]').val();
		var decimal = jQuery('select[name=reservations_currency_decimal]').val();
		var place = jQuery('select[name=reservations_currency_place]').val();
		var sign = jQuery('select[name=reservations_currency]').val();

		var price = 54+divider1;
		if(decimal == 1) price+= 847+divider2+99;
		else price += 848;
		if(place == 0){
			if(jQuery('input[name=reservations_currency_whitespace]').is(":checked")) price+= ' ';
			price += '&'+sign+';';
		} else {
			var white = '';
			if(jQuery('input[name=reservations_currency_whitespace]').is(":checked")) white = ' ';
			price = '&'+sign+';'+white+price;
		}
		jQuery('#reservations_currency_example').html(price);
	}
	jQuery('#currency_settings input,#currency_settings select').bind('change',function(){
		easyreservations_currency_example();
	});
	easyreservations_currency_example();
</script>
<?php } elseif($settingpage=="form"){ 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + FORM SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$roomsoptions = easyreservations_resource_options('', 0, '', true);
	add_action('admin_print_footer_scripts',  'easy_add_my_quicktags'); //add buttons to quicktag
	?><script>
			function setDefaultForm(){
				var Default = '[error]\n';
					Default += '<h1>Reserve now!<span style="float:right;">[show_price]</span></h1>\n';
					Default += '<h2>General informations</h2>\n\n';
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

					Default += '<h2>Personal informations</h2>\n\n';

					Default += '<label>Name\n';
					Default += '<span class="small">Whats your name?</span>\n';
					Default += '</label>[thename]\n\n';

					Default += '<label>Email\n';
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
					<td style="width:60%;line-height: 2;" colspan="2"><?php if($formnameget==""){ ?><a href="admin.php?page=reservation-settings&site=form"><b style="color:#000;"><?php printf ( __( 'Standard' , 'easyReservations' ));?></b></a><?php } else { ?><a href="admin.php?page=reservation-settings&site=form"><?php printf ( __( 'Standard' , 'easyReservations' ));?></a><?php } ?><?php echo $forms; ?><div style="float:right"><form method="post" action="admin.php?page=reservation-settings&site=form"  id="reservations_form_add"><input type="hidden" name="action" value="reservations_form_add"/><input name="formname" type="text" style="width:200px"><input type="button" onclick="document.getElementById('reservations_form_add').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Add' , 'easyReservations' ));?>"></form></div> </td>
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
								<option value="units"><?php printf ( __( 'Billing unit' , 'easyReservations' ));?> [units]</option>
								<option value="date-to-hour"><?php printf ( __( 'Departure Hour' , 'easyReservations' ));?> [date-from]</option>
								<option value="date-to-min"><?php printf ( __( 'Departure Minute' , 'easyReservations' ));?> [date-from]</option>
								<option value="resources"><?php printf ( __( 'Resources' , 'easyReservations' ));?> [resources]</option>
								<option value="adults"><?php printf ( __( 'Adults' , 'easyReservations' ));?> [adults]</option>
								<option value="childs"><?php printf ( __( 'Children\'s' , 'easyReservations' ));?> [childs]</option>
								<option value="thename"><?php printf ( __( 'Name' , 'easyReservations' ));?> [thename]</option>
								<option value="email"><?php printf ( __( 'Email' , 'easyReservations' ));?> [email]</option>
								<option value="country"><?php printf ( __( 'Country' , 'easyReservations' ));?> [country]</option>
								<?php do_action('easy-form-js-select'); ?>
								<option value="custom"><?php printf ( __( 'Custom field' , 'easyReservations' ));?> [custom]</option>
								<option value="price"><?php printf ( __( 'Price field' , 'easyReservations' ));?> [price]</option>
								<option value="hidden"><?php printf ( __( 'Hidden field' , 'easyReservations' ));?> [hidden]</option>
								<option value="infobox"><?php printf ( __( 'Infobox' , 'easyReservations' ));?> [infobox]</option>
								<option value="captcha"><?php printf ( __( 'Captcha' , 'easyReservations' ));?> [captcha]</option>
								<option value="show_price"><?php printf ( __( 'Display Price' , 'easyReservations' ));?> [show_price]</option>
								<option value="error"><?php printf ( __( 'Display Errors' , 'easyReservations' ));?> [error]</option>
								<option value="submit"><?php printf ( __( 'Submit Button' , 'easyReservations' ));?> [submit]</option>
							</select>
						</div>
						<div id="Text" style="float: left;"></div>
						<div id="Text2" style="float: left;"></div>
						<div id="Text3" style="float: left;"></div>
						<div id="Text4" style="float: left;"></div>
						<a href="javascript:resetform();" class="easySubmitButton-primary" style="margin:0px 1px 0px 1px"><?php printf ( __( 'Reset' , 'easyReservations' ));?></a>
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
						<table class="<?php echo RESERVATIONS_STYLE; ?>">
							<thead>
								<tr>
									<th><?php echo  __( 'Information' , 'easyReservations' ); ?></th>
									<th><?php echo  __( 'Tag' , 'easyReservations' ); ?></th>
									<th style="text-align:center;"><?php echo  __( 'If unused' , 'easyReservations' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr onclick="resetform();jQuery('#jumpmenu').val('date-from').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Arrival Date' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field with datepicker' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-from]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('date-from-hour').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Arrival Hour' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-23' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-from-hour]</code></td>
									<td style="text-align:center;">12:00</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('date-from-min').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Arrival Minute' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-59' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-from-min]</code></td>
									<td style="text-align:center;">0</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('date-to').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Departure Date' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field with datepicker' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-to]</code></td>
									<td style="text-align:center;">Arrival Date + 1 <?php echo ucfirst(easyreservations_interval_infos(0, 0, 1)); ?></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('units').change();" style="cursor:pointer;">
									<td><b>&#10132<?php echo  __( 'Billing units' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden units nr]</code><br><i id="idtimes"><?php echo  __( 'Select of definable numbers' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[units]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('date-to-hour').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Departure Hour' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-23' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-to-hour]</code></td>
									<td style="text-align:center;">12:00</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('date-to-min').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Departure Minute' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select from 00-59' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[date-to-min]</code></td>
									<td style="text-align:center;">0</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('resources').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Resources' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden resource id]</code><br><i><?php echo  __( 'Select of excludable resources' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[resources]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('adults').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Adults' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden adults nr]</code><br><i><?php echo  __( 'Select of definable numbers' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[adults]</code></td>
									<td style="text-align:center;">1</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('childs').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Children\'s' , 'easyReservations' ); ?></b> &#10132; <code class="codecolor">[hidden childs nr]</code><br><i><?php echo  __( 'Select of definable numbers' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[childs]</code></td>
									<td style="text-align:center;">0</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('thename').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Name' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[thename]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('email').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Email' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[email]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('country').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Country' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Select of countrys' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[country]</code></td>
									<td style="text-align:center;">unknown</td>
								</tr>
								<?php do_action('easy-add-forms-table-col'); ?>
								<tr onclick="resetform();jQuery('#jumpmenu').val('custom').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Custom' , 'easyReservations' ); ?></b><br><i id="idcustom"><?php echo  __( 'Custom field, area, select, radio or checkbox' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[custom]</code></td>
									<td></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('price').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Price Field' , 'easyReservations' ); ?></b><br><i id="idprices"><?php echo  __( 'Custom select, radio or checkbox with effect on price' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[price]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('infobox').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Infobox' , 'easyReservations' ); ?></b><br><i id="idprices"><?php echo  __( 'Show selected resources informations flexible' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[Infobox]</code></td>
									<td></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('hidden').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Hidden' , 'easyReservations' ); ?></b><br><i id="idhidden"><?php echo  __( 'Fix &amp; hide informations in form' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[hidden]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('captcha').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Captcha' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Text field and captcha image' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[captcha]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('show_price').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Display price' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Box with live price calculation' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[show_price]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('error').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Display Errors' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Box with errors' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[error]</code></td>
									<td style="text-align:center;"></td>
								</tr>
								<tr onclick="resetform();jQuery('#jumpmenu').val('submit').change();" style="cursor:pointer;">
									<td><b><?php echo  __( 'Submit' , 'easyReservations' ); ?></b><br><i><?php echo  __( 'Submit button with definable text' , 'easyReservations' ); ?></i></td>
									<td><code class="codecolor">[submit]</code></td>
									<td style="text-align:center;">&#10008;</td>
								</tr>
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
							if(preg_match('/\[date-to/', $reservations_form) || preg_match('/\[units/', $reservations_form) || preg_match('/\[hidden units/', $reservations_form)) $gute++; else {
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'No' , 'easyReservations' ).' <code class="codecolor">[date-to]</code> '.__( 'or' , 'easyReservations' ).' <code class="codecolor">[units]</code> '.__( 'Tag in Form' , 'easyReservations' ).'<br>'; }
							if(preg_match('/\[date-to/', $reservations_form) && preg_match('/\[units/', $reservations_form)){
								$couerrors++; $formerror .= '<b>'.$couerrors.'.</b> '.__( 'Dont use' , 'easyReservations' ).' <code class="codecolor">[date-to]</code> '.__( 'and' , 'easyReservations' ).' <code class="codecolor">[units]</code> '.__( 'in the same Form' , 'easyReservations' ).'<br>'; } else $gute++; 
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
							$formtags = easyreservations_shortcode_parser($reservations_form);
							$customarray = '';
							$customerror = '';
							$pricesarray = '';
							$priceserror = '';
							foreach($formtags as $formtag){
								$tags = shortcode_parse_atts($formtag);
								if($tags[0] == 'custom'){
									if(!is_array($customarray) || !in_array($tags[2],$customarray)) $customarray[] = $tags[2];
									else $customerror .= $tags[2].', ';
								} elseif($tags[0] == 'price'){
									if(!is_array($pricesarray) || !in_array($tags[2],$pricesarray)) $pricesarray[] = $tags[2];
									else $priceserror .= $tags[2].', ';
								}
							}
							if(empty($customerror)) $gute++; else {
								$couerrors++;$customerror = substr($customerror,0,-2);$formerror .= '<b>'.$couerrors.'.</b> '.__( 'Custom field name entered multiple times - must be unique. Name:' , 'easyReservations' ).' <code class="codecolor">'.$customerror.'</code><br>'; }
							if(empty($priceserror)) $gute++; else {
								$couerrors++;$priceserror = substr($priceserror,0,-2);$formerror .= '<b>'.$couerrors.'.</b> '.__( 'Price field name entered multiple times - must be unique. Name:' , 'easyReservations' ).' <code class="codecolor">'.$priceserror.'</code><br>'; }
							$coutall=$gute+$couerrors;
							if($couerrors > 0){ ?>
							<div id="formerror" class="explainbox" style="background:#FCEAEA; border-color:#FF4242;box-shadow: 0 0 2px #F99F9F;margin-top:5px">
								<?php echo __( 'This form is not valid' , 'easyReservations' ).' '.$gute.'/'.$coutall.' P.<br>'; echo $formerror; ?><input type="hidden" id="formerror"><script>window.location.hash = 'formerror';</script>
							</div><?php } else { ?>
							<div class="explainbox" style="background:#E8F9E8; border-color:#68FF42;box-shadow: 0 0 2px #9EF7A1;margin-top:5px">
								<?php echo __( 'This form is valid' , 'easyReservations' ).' '.$gute.'/'.$coutall.' P.<br>'; echo $formgood; ?>
							</div><?php } ?>
					</td>
				</tr>
			</tbody>
		</table>
<script language="javascript" type="text/javascript" >
	function resteText() {
		document.reservations_form_settings.reservations_formvalue.value = document.reservations_form_settings.resetforrm.value;
	}

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
		
		jQuery('*[class|=customattr]').each(function(){
			var explode = this.className.split('-');
			if(explode[2] && explode[2] == 'master'){
				if(this.checked === false) return true; 
			} else if(explode[2] && explode[2] == 'slave'){
				var checked = jQuery('*[class=customattr-'+explode[1]+'-master]').is(':checked');
				if(checked !== true) return true;
			}
			if(this.name && this.value) tag += ' ' + this.name + '="' + this.value +'"';
		});

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
	}

	var thetext1 = false;
	var thetext2 = false;
	var thetext3 = false;
	var thetext4 = false;

	function resetform(){ // Reset fields in Form
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
		if(typ == 'date-from' || typ == 'date-to' ) Settings += '<?php echo addslashes(__( 'Value' , 'easyReservations' )); ?>: <input type="text" id="form-value" class="datepicker" style="width:80px" value="+15"> ';
		else if(typ == 'date-from-hour' || typ == 'date-to-hour' ) Settings += '<?php echo addslashes(__( 'Selected' , 'easyReservations' )); ?>:  <select id="form-value"><?php echo easyreservations_num_options("00",23,12); ?></select>';
		else if(typ == 'date-from-min' || typ == 'date-to-min' ) Settings += '<?php echo addslashes(__( 'Selected' , 'easyReservations' )); ?>:  <select id="form-value"><?php echo easyreservations_num_options("00",59); ?></select>';
		else if(typ == 'email' || typ == 'message' || typ == 'thename'  || typ == 'input' || typ == 'captcha' || typ == 'submit') Settings += '<?php echo addslashes(__( 'Value' , 'easyReservations' )); ?>: <input type="text" id="form-value" style="width:80px"> ';
		else if( typ == "country" ) Settings += '<?php echo addslashes(__( 'Selected' , 'easyReservations' )); ?>: <select id="form-value" style="width:100px"><?php echo easyreservations_country_options(); ?></select> ';
		else if( typ == "rooms" ) Settings += '<?php echo addslashes(__( 'Selected' , 'easyReservations' )); ?>: <select id="form-value" style="width:100px"><?php echo $roomsoptions; ?></select> ';
		else if( typ == "amount" ) Settings += '<?php echo addslashes(__( 'Selected' , 'easyReservations' )); ?>: <select id="form-value"><?php echo easyreservations_num_options(1,100,50); ?></select> ';
		if(typ == 'date-from' || typ == 'date-to' || typ == 'email' || typ == 'message' || typ == 'thename' || typ == 'input') Settings += 'Maxlength: <select id="form-maxlength"><?php echo easyreservations_num_options(1,100,50); ?></select> ';

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
			
			var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Select type of Input you want to add' , 'easyReservations' )); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "price") {
			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="select">Select</option><option value="radio">Radio</option><option value="checkbox">Checkbox</option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Select Type of custom price field' , 'easyReservations' )); ?></b>';
				Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Select' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Add a dropdown select field with effect on the price to the form' , 'easyReservations' )); ?></i>';
				Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Radio' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Add a radio select field with effect on the price to the form' , 'easyReservations' )); ?></i>';
				Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Checkbox' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Add a checkbox input with effect on the price to the form' , 'easyReservations' )); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "error"){
			Output = ' <?php echo addslashes(__( 'Error title' , 'easyReservations' )); ?>: <input id="easy-error-title" type="text" value="Errors found in the form"> <?php echo addslashes(__( 'Error message' , 'easyReservations' )); ?>: <input id="easy-error-message" type="text" value="There is a problem with the form, please check and correct the following:"> ';
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
		} else if(x == "resources"){
			addformsettings(x);
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<?php echo addslashes(__( 'Exclude by ID' , 'easyReservations' )); ?> <input type="text" name="exclude" id="easy-other" value=""> ';
			document.getElementById("Text").innerHTML += Output;
		} else if(x == "show_price") {
			addformsettings(x);
			end = 1;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
			var Output  = '<?php echo addslashes(__( 'Title' , 'easyReservations' )); ?> <input type="text" name="before" id="easy-other" value="Price:">';
			document.getElementById("Text").innerHTML += Output;
		} else if(x == "infobox"){
			addformsettings(x);
			end = 1;
			var Output = '&nbsp;<select class="customattr" name="theme" style="width:60px"><option value="big">big</option><option value="medium">medium</option></select>';
			Output  += '&nbsp;<input type="checkbox" class="customattr-1-master" name="img" value="yes"> <?php echo addslashes(__( 'Image' , 'easyReservations' )); ?> <input type="text" name="img_y" class="customattr-1-slave" value="100" style="width:38px">px * <input type="text" name="img_x" class="customattr-1-slave" value="100" style="width:38px">px';
			Output += '&nbsp;<input type="checkbox" class="customattr-2-master" name="title" value="yes"> <?php echo addslashes(__( 'Title' , 'easyReservations' )); ?>';
			Output += '&nbsp;<input type="checkbox" class="customattr-3-master" name="" value=""> <?php echo addslashes(__( 'Content' , 'easyReservations' )); ?> <input type="text" class="customattr-3-slave" name="content" value="400" style="width:38px">';
			Output += '&nbsp;<input type="checkbox" class="customattr-4-master" name="" value=""> <?php echo addslashes(__( 'Excerpt' , 'easyReservations' )); ?> <input type="text" class="customattr-4-slave" name="excerpt" value="400" style="width:38px">';
			document.getElementById("Text").innerHTML += Output;
			document.form1.jumpmenu.disabled=true;
		} else if(x == "adults" || x == "childs" || x == "units" || x == "units"){
			end = 1;
			var Output  = '&nbsp;<b><?php echo addslashes(__( 'Min' , 'easyReservations' )); ?>:</b> <select name="zwei" id="zwei"><?php echo easyreservations_num_options(0,100,0); ?></select> <b><?php echo addslashes(__( 'Max' , 'easyReservations' )); ?>:</b> <select name="drei" id="drei"><?php echo easyreservations_num_options(0,100,10); ?></select>';
			document.getElementById("Text2").innerHTML += Output;
			addformsettings('amount');
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "submit"){

			var Output  = '<input type="text" name="eins" id="eins" value="Name">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo addslashes(__( 'Type in value of submit button' , 'easyReservations' )); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.form1.jumpmenu.disabled=true;
			var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="margin-top:2px" style="line-height:1;margin:2px 2px 0px 2px"><b><?php echo addslashes(__( 'Add' , 'easyReservations' )); ?></b></a>';

		} else if (x == "hidden") {
			var Output  = '<select id="eins" name="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)">';
			Output += '<option>Type</option><option value="resource"><?php echo addslashes(__( 'Resource' , 'easyReservations' )); ?></option><option value="from"><?php echo addslashes(__( 'Arrival Date' , 'easyReservations' )); ?></option><option value="date-from-hour"><?php echo __( 'Arrival Hour' , 'easyReservations' ); ?><option value="date-from-min"><?php echo __( 'Arrival Minute' , 'easyReservations' ); ?></option><option value="to"><?php echo __( 'Departure Date' , 'easyReservations' ); ?></option><option value="units"><?php echo __( 'Billing units' , 'easyReservations' ); ?></option><option value="date-to-hour"><?php echo __( 'Departure Hour' , 'easyReservations' ); ?><option value="date-to-min"><?php echo __( 'Departure Minute' , 'easyReservations' ); ?></option><option value="persons"><?php echo __( 'Persons' , 'easyReservations' ); ?></option><option value="childs"><?php echo __( 'Childrens' , 'easyReservations' ); ?></option></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Select type of hidden input' , 'easyReservations' )); ?></b>';
			Help += '<br> &emsp; <i><?php echo addslashes(__( 'to fix information\\\'s to the form and hide them from the guest' , 'easyReservations' )); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Resource' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Fix a resource to the form; dont use it with [resources] in the same form' , 'easyReservations' )); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Arrival Date' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Fix an arrival date to the form; dont use it with [date-from] in the same form' , 'easyReservations' )); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Departure Date' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Fix a departure date to the form; dont use it with [date-to] in the same form' , 'easyReservations' )); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Billing units' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Fix billing units to the form; dont use it with [date-to] or [units] in the same form' , 'easyReservations' )); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Adults' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Fix an amount of adults to the form; dont use it with [adults] in the same form' , 'easyReservations' )); ?></i>';
			Help += '<br> &emsp; <i><b><?php echo addslashes(__( 'Children\'s' , 'easyReservations' )); ?></b> <?php echo addslashes(__( 'Fix an amount of childrens to the form; dont use it with [childs] in the same form' , 'easyReservations' )); ?></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} <?php do_action('easy-form-js-1'); ?>
	} else if(thetext2 == false){
		if (x == "textarea" || x == "text" || x == "check"){
			var Output  = '<input type="text" name="customzwei" id="zwei" value="Name"> <input type="checkbox" id="req" name="req" value="*"> <?php echo addslashes(__( 'Required' , 'easyReservations' )); ?> ';
			document.getElementById("Text2").innerHTML += Output;
			addformsettings('input');

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo addslashes(__( 'Type in a name for the' , 'easyReservations' )); ?> <span style="text-transform:capitalize">' + x + '</span> <?php echo addslashes(__( 'input you want to add' , 'easyReservations' )); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			end = 1;
			document.form1.eins.disabled=true;
		} else if (x == "checkbox"){
			end = 1;

			var Output  = '<input type="text" name="customzwei" id="zwei" value="Name"><input type="text" name="drei" id="drei" value="Value">';
			Output += easy_price_checks();
			document.getElementById("Text2").innerHTML += Output;
			addformsettings('checkbox');

			jQuery('input[name="customzwei"]').keydown(function(e){if(e.keyCode == 32) e.preventDefault();});
			var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Type in a Name for the Checkbox' , 'easyReservations' )); ?></b>';
			Help += '<br><b>2. <?php echo addslashes(__( 'Type in a value for the checkbox' , 'easyReservations' )); ?></b>',
			Help += '<br> &emsp; <?php echo addslashes(__( 'The value has to match ' , 'easyReservations' )); ?><br>&emsp; <code>option:price</code><br> &emsp; <?php echo addslashes(sprintf( __( 'Price: negative for reduction %1$s  zero for no change %2$s positiv for increase %3$s ', 'easyReservations'), '<code>-30.75</code>', '<code>0</code>', '<code>20.2</code>' )); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "select" || x == "radio") {
			var Output  = '<input type="text" name="customzwei" id="zwei" value="Name" onClick="jumpto(document.form1.zwei.value);">';
			addformsettings('select');
			if(first == "price"){
				var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Type in a name for the dropdown select' , 'easyReservations' )); ?></b>';
				Help += '<br><b>2. <?php echo addslashes(__( 'Type in the options field for the' , 'easyReservations' )); ?>  ' + x + ' Input</b>',
				Help += '<br> &emsp;<?php echo addslashes(__( 'The options field has to match ' , 'easyReservations' )); ?><br>&emsp; <code>first option:first price<b>,</b>second option:second price [...]</code><br> &emsp; <?php echo addslashes(sprintf( __( 'Price: negative for reduction %1$s  zero for no change %2$s positiv for increase %3$s '), '<code>-20</code>', '<code>0</code>', '<code>50.89</code>' )); ?></div><br>';
			} else if(first == "custom"){
				var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Type in a Name for the' , 'easyReservations' )); ?> ' + x + ' field</b>';
				Help += '<br><b>2. <?php echo addslashes(__( 'Type in the options field' , 'easyReservations' )); ?></b>',
				Help += '<br> &emsp; <?php echo addslashes(__( 'The options field has to match ' , 'easyReservations' )); ?><br>&emsp; <code>first option<b>,</b>second option<b>,</b>third option [...]</code></div><br>';
			}
			document.getElementById("Text2").innerHTML += Output;
			document.getElementById("Helper").innerHTML = Help;
			jQuery('input[name="customzwei"]').keydown(function(e){if(e.keyCode == 32) e.preventDefault();});

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "resource") {
			end = 1;
			var Output  = '<select id="zwei" name="zwei"><?php echo $roomsoptions; ?></select>';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo addslashes(__( 'Select a resource' , 'easyReservations' )); ?></b></div><br>';
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

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo addslashes(__( 'Fill in the date of the arrival date you want to fix' , 'easyReservations' )); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "to") {
			end = 1;
			var Output  = '<input type="text" name="zwei" id="zwei" value="dd.mm.yyyy">';
			document.getElementById("Text3").innerHTML += Output;

			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo addslashes(__( 'Fill in the date of the departure date you want to fix' , 'easyReservations' )); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "units") {
			end = 1;
			var Output  = '<select name="zwei" id="zwei"><?php echo easyreservations_num_options(0,100,0); ?></select>';
			document.getElementById("Text3").innerHTML += Output;
			var Help = '<div class="explainbox" style="font-weight:bold"><?php echo addslashes(__( 'Select the amount of billing units you want to fix' , 'easyReservations' )); ?></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "date-from-hour" || x == "date-to-hour") {
			end = 1;
			var Output  = '<select name="zwei" id="zwei"><?php echo easyreservations_num_options(0,23,12); ?></select>';
			document.getElementById("Text3").innerHTML += Output;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "date-from-min" || x == "date-to-min") {
			end = 1;
			var Output  = '<select name="zwei" id="zwei"><?php echo easyreservations_num_options(0,59,0); ?></select>';
			document.getElementById("Text3").innerHTML += Output;

			thetext2 = true;
			document.form1.eins.disabled=true;
		}
		jQuery('input[name="customzwei"]').keydown(function(e){if(e.keyCode == 32) e.preventDefault();});
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
		var Output  = '<a href="javascript:easy_add_form_tag()" class="easySubmitButton-primary" style="margin:0px 2px 0px 2px"><b><?php echo addslashes(__( 'Add' , 'easyReservations' )); ?></b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
}

function easy_price_checks(){
	Output = '<input type="checkbox" id="price1" name="req" value="pp"> <?php echo addslashes(__( 'price per person' , 'easyReservations' )); ?>';
	Output += '<input type="checkbox" id="price2" name="req" value="pn"> <?php echo addslashes(__( 'price per night' , 'easyReservations' )); ?>';
	return Output;
}
jQuery('input[name="formname"]').keydown(function(e){if(e.keyCode == 32) e.preventDefault();});
<?php do_action('easy-form-js-function'); ?>
</script>
<hr>
<?php } elseif($settingpage=="email"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EMAIL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource]<br>Price: [price]<br>[customs]";
$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource]<br><br>Price: [price]<br>[customs]<br>edit your reservation on [editlink]";
$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource]<br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]";
$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
$emailstandart6="Reservation got edited by guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>[changelog]";
$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
$emailstandart10="Reservation got canceled by guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Childrens: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>[customs]<br><br>[changelog]";
?><script type="text/javascript">
function addtextforemail(nr){
	if(nr == 0) document.reservations_email_settings.reservations_email_sendmail_msg.value = document.reservations_email_settings.inputemail0.value;
	else if(nr == 1) document.reservations_email_settings.reservations_email_to_admin_msg.value = document.reservations_email_settings.inputemail1.value;
	else if(nr == 2) document.reservations_email_settings.reservations_email_to_userapp_msg.value = document.reservations_email_settings.inputemail2.value;
	else if(nr == 3) document.reservations_email_settings.reservations_email_to_userdel_msg.value = document.reservations_email_settings.inputemail3.value;
	else if(nr == 4) document.reservations_email_settings.reservations_email_to_user_msg.value = document.reservations_email_settings.inputemail4.value;
	else if(nr == 5) document.reservations_email_settings.reservations_email_to_user_edited_msg.value = document.reservations_email_settings.inputemail5.value;
	else if(nr == 6) document.reservations_email_settings.reservations_email_to_admin_edited_msg.value = document.reservations_email_settings.inputemail6.value;
	else if(nr == 7) document.reservations_email_settings.reservations_email_to_user_admin_edited_msg.value = document.reservations_email_settings.inputemail7.value;
	else if(nr == 8) document.reservations_email_settings.reservations_email_to_admin_paypal_msg.value = document.reservations_email_settings.inputemail8.value;
	else if(nr == 9) document.reservations_email_settings.reservations_email_to_user_paypal_msg.value = document.reservations_email_settings.inputemail9.value;
	else if(nr == 10) document.reservations_email_settings.reservations_email_to_admin_canceled_msg.value = document.reservations_email_settings.inputemail10.value;
}
</script>
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
	<input type="hidden" value="<?php echo $emailstandart10; ?>" name="inputemail10">
	<?php if(!function_exists('easyreservations_generate_email_settings')){ 
		$reservations_email_sendmail=get_option("reservations_email_sendmail");
		$reservations_email_to_admin=get_option("reservations_email_to_admin");
		$reservations_email_to_user=get_option("reservations_email_to_user");
		$reservations_email_to_user_admin_edited=get_option("reservations_email_to_user_admin_edited");
		$reservations_email_to_userapp=get_option("reservations_email_to_userapp");
		$reservations_email_to_userdel=get_option("reservations_email_to_userdel"); ?>
	<table style="width:99%;" cellspacing="0">
		<tr style="width:60%;" cellspacing="0">
			<td valign="top">
		<?php do_action('er_set_emails_add_before'); ?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th> <?php echo __( 'Mail from admin in dashboard' , 'easyReservations' );?><span style="float:right"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" id="idactive" value="1" name="reservations_email_sendmail_check" <?php checked(1, $reservations_email_sendmail['active']); ?> style="margin-top:3px;margin-left:-1px;"> <input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b id="idemailhead" style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail(0);" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input id="idsubj" type="text" name="reservations_email_sendmail_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_sendmail['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea id="idmsg" name="reservations_email_sendmail_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_sendmail['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on new reservation' , 'easyReservations' ));?><input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" style="float:right" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>"></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to admin' , 'easyReservations' ); ?> </b><span style=";margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_admin_check" <?php checked(1, $reservations_email_to_admin['active']); ?> style="margin-top:3px;margin-left:-1px;"></span><input type="button" value="Default Mail" onClick="addtextforemail(1);" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_admin_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_admin['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_admin_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_admin['msg']); ?></textarea></td>
				</tr>
			<tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="border-top:0;">
			</tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><span style="margin-right:5px"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_user_check" <?php checked(1, $reservations_email_to_user['active']); ?> style="margin-top:3px;margin-left:-1px;"><input type="button" value="Default Mail" onClick="addtextforemail(4);" class="easySubmitButton-secondary" style="float:right;"></span></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_user['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>

		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on approvement' , 'easyReservations' ));?><span style="float:right"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_userapp_check" <?php checked(1, $reservations_email_to_userapp['active']); ?> style="margin-top:3px;margin-left:-1px;"> <input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail(2);" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_userapp_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_userapp['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userapp_msg"  id="reservations_email_to_userapp_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_userapp['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mail on rejection' , 'easyReservations' ));?><span style="float:right;"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_userdel_check" <?php checked(1, $reservations_email_to_userdel['active']); ?> style="margin-top:3px;margin-left:-1px;"> <input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail(3);" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_userdel_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_userdel['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_userdel_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_userdel['msg']); ?></textarea></td>
				</tr>
			</tbody>
		</table>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;">
			<thead>
				<tr>
					<th> <?php printf ( __( 'Mails on editing of admin' , 'easyReservations' ));?><span style="float:right;"><?php echo __( 'Active' , 'easyReservations' ); ?>: <input type="checkbox" value="1" name="reservations_email_to_user_admin_edited_check" <?php checked(1, $reservations_email_to_user_admin_edited['active']); ?> style="margin-top:3px;margin-left:-1px;"> <input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easySubmitButton-primary" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><b style="padding:5px;line-height:2;font-size:13px;text-decoration:underline;"><?php echo __( 'Mail to guest' , 'easyReservations' ); ?></b><input type="button" value="Default Mail" onClick="addtextforemail(7);" class="easySubmitButton-secondary" style="float:right;"></td>
				</tr>	
				<tr valign="top">
					<td><input type="text" name="reservations_email_to_user_admin_edited_subj" style="width:60%;" value='<?php echo stripslashes($reservations_email_to_user_admin_edited['subj']); ?>'> <?php echo __( 'Subject' , 'easyReservations' ); ?></td>
				</tr>	
				<tr valign="top">
					<td><textarea name="reservations_email_to_user_admin_edited_msg" style="width:99%;height:120px;"><?php echo stripslashes($reservations_email_to_user_admin_edited['msg']); ?></textarea></td>
				</tr>	
			</tbody>
		</table>
		<?php do_action('er_set_emails_add_after'); ?>
		</td>
		<td  style="width:1%;"></td>
		<td  style="width:39%;"  valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th id="idtags"> <?php printf ( __( 'Tags' , 'easyReservations' ));?></th>
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
							<p><code class="codecolor">[units]</code> <i><?php echo __( 'amount of billing units', 'easyReservations' )?></i></p>
							<p><code class="codecolor">[reserved]</code> <i><?php printf( __( 'amount of %s from date of reservation' , 'easyReservations' ), easyreservations_interval_infos());?></i></p>
							<p><code class="codecolor">[adults]</code> <i><?php printf ( __( 'amount of adults' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[childs]</code> <i><?php printf ( __( 'amount of childs' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[country]</code> <i><?php printf ( __( 'country of guest' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[resource]</code> <i><?php printf ( __( 'name of resource' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[resourcenumber]</code> <i><?php printf ( __( 'name of resource number' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[price]</code> <i><?php printf ( __( 'show price' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[paid]</code> <i><?php printf ( __( 'show paid amount' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[customs]</code> <i><?php printf ( __( 'custom fields' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor" id="idtagcustom">[prices]</code> <i><?php printf ( __( 'price fields' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[changlog]</code> <i><?php printf ( __( 'show changes after edits' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[editlink]</code> <i><?php printf ( __( 'link to user control panel' , 'easyReservations' ));?></i></p>
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
						<?php include('changelog.html');?>
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
						<td style="font-weight:bold;padding:10px;text-align:center"><span style="width:20%;display: inline-block">Version: <?php echo RESERVATIONS_VERSION; ?></span><span style="width:30%;display: inline-block">Last update: 12.12.2012</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
					</tr>
					<tr class="alternate">
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/" target="_blank" id="iddocumentation"><?php echo __( 'Documentation' , 'easyReservations' );?></a></td>
					</tr>
					<tr>
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/bug-reports/" target="_blank" id="idbugreport"><?php echo __( 'Report bug' , 'easyReservations' );?></a></td>
					</tr>
					<tr class="alternate"> 
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/premium/" target="_blank" id="idpremium"><?php echo __( 'Premium' , 'easyReservations' );?></a></td>
					</tr>
					<tr>
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/" target="_blank" id="idrate"><?php echo __( 'Rate the Plugin' , 'easyReservations' );?>, please!</a></td>
					</tr>
					<tr class="alternate">
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/chrome/" target="_blank" id="idrate"><b style="font-weight: bold !important; color:#ff0000;">*NEW*</b> <?php echo __( 'Chrome Extension' , 'easyReservations' );?> <b style="font-weight: bold !important; color:#ff0000;">*NEW*</b></a></td>
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
		</table>
	</tr>
</table>
<?php } elseif($settingpage=="chrome"){
	$countactive = 0;
	$countpending = 0;
	easyreservations_load_resources();
	global $the_rooms_array;
	echo '<table><tr><td id="idvers">'.RESERVATIONS_VERSION.'</td></tr></table><div id="idtable1">';
	$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '".date('Y-m-d H:i')."' >= arrival AND '".date('Y-m-d H:i')."' <= departure"); // Search query 
	if(!empty($queryDepartures)){
		echo '<table class="reservations"><thead><tr><th>Date</th><th>Name</th><th>Resource</th><th>Pers</th><th>Price</th><th></th></tr></thead><tbody>';
		foreach($queryDepartures as $id){
			$countactive++;
			$res = new Reservation($id->id);
			$res->Calculate();
			if($res->interval == 3600) $dateformat = 'd.m H:00';
			else $dateformat = 'd.m';
			echo '<tr>';
				echo '<td>'.date($dateformat, $res->arrival).' - '.date($dateformat, $res->departure).'</td><td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
				echo '<td>'.__($the_rooms_array[$res->resource]->post_title).' '.easyreservations_get_roomname($res->resourcenumber, $res->resource).'</td><td>'.$res->adults.'/'.$res->childs.'</td><td>'.$res->formatPrice(true, true, 0).'</td>';
				echo '<td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&approve='.$res->id.'">REJECTIMG</a> <a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&edit='.$res->id.'">EDITIMG</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div><div id="idtable2">';
	$queryPendings = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '".date('Y-m-d H:i')."' <= arrival"); // Search query 
	if(!empty($queryPendings)){
		echo '<table class="reservations"><thead><tr><th>Date</th><th>Name</th><th>Resource</th><th>Pers</th><th>Price</th><th></th></tr></thead><tbody>';
		foreach($queryPendings as $id){
			$countpending++;
			$res = new Reservation($id->id);
			$res->Calculate();
			if($res->interval == 3600) $dateformat = 'd.m H:00';
			else $dateformat = 'd.m';
			echo '<tr>';
				echo '<td>'.date($dateformat, $res->arrival).' - '.date($dateformat, $res->departure).'</td><td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
				echo '<td>'.__($the_rooms_array[$res->resource]->post_title).' '.easyreservations_get_roomname($res->resourcenumber, $res->resource).'</td><td>'.$res->adults.'/'.$res->childs.'</td><td>'.$res->formatPrice(true, true, 0).'</td>';
				echo '<td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&approve='.$res->id.'">APPRIMG</a> <a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&edit='.$res->id.'">EDITIMG</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div><div id="idactive">'.$countactive.'</div><div id="idpending">'.$countpending.'</div>';
}
do_action( 'er_set_add' );?>
</div>
<?php } ?>