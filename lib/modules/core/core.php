<?php
/*
Plugin Name: Module Core
Plugin URI: http://www.feryaz.de
Description: The core contains the modules overview, the module installation and the module update notifier
Version: 1.1
Author: Feryaz Beer
Author URI: http://www.feryaz.de
License:GPL2
*/
	
	add_action('er_set_tab_add', 'easyreservations_core_add_settings_tab');

	function easyreservations_core_add_settings_tab(){ 
		
		if(isset($_GET['site']) AND $_GET['site'] == "plugins") $current = 'current'; else $current = '';
		$tab = '<li ><a href="admin.php?page=reservation-settings&site=plugins" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_IMAGES_DIR.'/plugin.png"> '. __( 'Modules' , 'easyReservations' ).'</a></li>';

		echo $tab;

	}

	add_action('er_set_save', 'easyreservations_core_save_settings');

	function easyreservations_core_save_settings(){
	
		if(isset($_GET['site']) AND $_GET['site'] == "plugins"){
			if(isset($_GET['check'])){
				easyreservations_latest_modules_versions(0);
			} elseif(isset($_GET['activate'])){
				//easyreservation_activate_module($_GET['activate']);
				echo '<br><div class="updated"><p>'.sprintf(__( 'Module %s activated' , 'easyReservations' ), '<b>'.$_GET['activate'].'</b>').'</p></div>';
			} elseif(isset($_GET['deactivate'])){
				echo '<br><div class="updated"><p>'.sprintf(__( 'Module %s deactivated' , 'easyReservations' ), '<b>'.$_GET['deactivate'].'</b>').'</p></div>';
			}

			if(isset($_POST['action']) AND $_POST['action'] == "reservation_core_settings"){
				if (!wp_verify_nonce($_POST['easy-set-core'], 'easy-set-core' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
				$options = array( 'modus' => $_POST['er_pay_modus'], 'title' => $_POST['er_pay_title'], 'owner' => $_POST['er_pay_owner'], 'currency' => $_POST['er_pay_curency'], 'button' => $_POST['er_pay_button'], 'cancel_url' => $_POST['er_pay_cancel'], 'er_pay_ssl' => $er_pay_ssl, 'er_pay_return' => $er_pay_return );
				update_option('reservations_core_options', $options);
			}
			if((isset($_POST['action']) AND $_POST['action'] == "reservation_core_upload_plugin") || isset($_GET['file_name'])){

				if(isset($_FILES['reservation_core_upload_file']) || isset($_GET['file_name'])){
					if(isset($_FILES['reservation_core_upload_file'])) $file_name = $_FILES['reservation_core_upload_file']['name']; else $file_name = $_GET['file_name'];
					$file_tmp_name = $_FILES['reservation_core_upload_file']['tmp_name'];
					if(isset($_FILES['reservation_core_upload_file']))  $file_type = $_FILES['reservation_core_upload_file']['type']; else  $file_type = 'application/x-zip' ;
					$file_size = $_FILES['reservation_core_upload_file']['size'];
					$plugin_dir = WP_PLUGIN_DIR.'/easyreservations/';
					$uploads = wp_upload_dir();
					$saved_file_location = $uploads['basedir'].'/'. $file_name;

					if(preg_match("/(PayPal|Import|GuestContact)/i", $file_name) && ($file_type == 'application/zip'  || $file_type == 'application/x-zip' || $file_type == 'application/x-zip-compressed'  || isset($_GET['file_name']))){
						if(move_uploaded_file($file_tmp_name, $saved_file_location) || isset($_GET['file_name'])) {
							$url = 'admin.php?page=reservation-settings&site=plugins&file_name='.$file_name;
							$creds = $_POST;
							if ( ! WP_Filesystem($creds) ) {
								request_filesystem_credentials($url, 'ftp', false, false, '$form_fields');
							} else {
								global $wp_filesystem;
								if(get_filesystem_method($creds, $saved_file_location)){
									$zip = new ZipArchive;
									if ($zip->open( $saved_file_location ) === TRUE)
									{
										$zip->extractTo( $plugin_dir );
										$zip->close();
									}
									unlink($saved_file_location);
									echo '<br><div class="updated"><p>'.sprintf(__( 'Module %s installed successfully' , 'easyReservations' ), '<b>'.str_replace('.zip', '', str_replace( '-', ' ', $file_name)).'</b>').'</p></div>';
								} else echo '<br><div class="error"><p>'.__( 'Extract failure' , 'easyReservations' ).'</p></div>';
							}
						} else {
							echo '<br><div class="error"><p>'.__( 'Upload failed' , 'easyReservations' ).'</p></div>';
						} 				
					} else {
						echo '<br><div class="error"><p>'.__( 'Wrong file' , 'easyReservations' ).'</p></div>';
					}
				}
			}
		}
	}

	add_action('er_set_add', 'easyreservations_core_add_settings');

	function easyreservations_core_add_settings(){
		$core_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/core/core.php', false);

		if(isset($_GET['site']) AND $_GET['site'] == "plugins"){
			$options = get_option('reservations_core_options'); 
			if(get_option('easyreservations-notifier-cache')) $xml = easyreservations_latest_modules_versions(86400);
			else {
				$xml = new stdClass();
				$xml->latestc = '1.0';
				$xml->latestd = '1.0';
				$xml->latestp = '1.0';
			}
			if(empty($options)) $options = array('title' => '[room] for [nights] days | [arrivalDate] - [depatureDate]', 'owner' => 'feryaz_1319406050_biz@googlemail.com', 'modus' => 'off', 'currency' => 'USD', 'button' => 'https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif')?>
			<?php wp_nonce_field('easy-set-core','easy-set-core'); ?>
				<input type="hidden" name="action" value="reservation_core_settings">
				<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="width:99%;">
					<thead>
						<tr>
							<th style="width:10px"></th>
							<th><?php printf ( __( 'Name' , 'easyReservations' ));?></th>
							<th style="width:50%"><?php printf ( __( 'Description' , 'easyReservations' ));?></th>
							<th style="text-align:center"><?php printf ( __( 'Installed' , 'easyReservations' ));?></th>
							<th style="text-align:center"><?php printf ( __( 'Actual' , 'easyReservations' ));?></th>
							<th style="text-align:center"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
							<th style="text-align:right"><?php printf ( __( 'Link' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/plugin.png"></td>
							<td><b><?php echo $core_data['Name'] ?></b></td>
							<td><?php echo $core_data['Description'] ?></td>
							<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version'] ?></td>
							<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version'] ?></td>
							<td style="text-align:center"><b>free</b></td>
							<td style="font-weight:bold;text-align:right"></td>
						</tr>
						<?php
							$color = '';$action ='';
							$import_avail_version = "1.0";
							if(function_exists('easyreservations_generate_import')){
								$import = 2;
								$import_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php', false);
								$import_current_version = $import_data['Version'];
								if(version_compare($import_data['Version'], $import_avail_version) == -1) $color = 'color:#FF3B38';
								$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="import"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
							} else{
								if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php')){
									$import = 1;
									$import_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php', false);
									$import_current_version = $import_data['Version'];
									if(version_compare($import_data['Version'], $import_avail_version) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="import"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
								} else $import = 0;
							}
						?>
						<tr <?php if($import != 2) echo 'class="inactive"'; ?>>
							<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/import.png"></td>
							<td><b><a href="http://www.feryaz.de/import-module/" target="_blank"><?php printf ( __( 'Import Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
							<td><?php printf ( __( 'Import reservations from .XML backup files.' , 'easyReservations' ));?></td>
							<td style="font-weight:bold;text-align:center"><?php if($import > 0) echo '<a style="color:#118D18">'.$import_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
							<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $import_avail_version; ?></td>
							<td style="font-weight:bold;text-align:center"><b>free</b></td>
							<td style="font-weight:bold;text-align:right"><?php if($import > 0) echo '<a href="mailto:import@feryaz.de">'.__( 'Support' , 'easyReservations' ).'</a>'; else echo '<a href="http://www.feryaz.de/import-module/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; ?></td>
						</tr>
						<?php /*
							$multical_current_version = "1.0"; $color = ''; $action = '';
							if(function_exists('easyreservations_generate_multical')){
								$multical = 2;
								$multical_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false);
								$multical_current_version = $multical_data['Version'];
								if(version_compare($multical_data['Version'], $xml->latestc) == -1) $color = 'color:#FF3B38';
								$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="multical"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
							} else{
								if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php')){
									$multical = 1;
									$multical_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false);
									$multical_current_version = $multical_data['Version'];
									if(version_compare($multical_data['Version'], $xml->latestc) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="multical"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
								} else $multical = 0;
							}
						?>
						<tr <?php if($multical != 2) echo 'class="inactive"'; ?>>
							<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"></td>
							<td><b><a href="http://www.feryaz.de/multical-module/" target="_blank"><?php printf ( __( 'ExtentedCalendar Module' , 'easyReservations' ));?></a></b><?php echo $action; ?></td>
							<td><?php printf ( __( 'Extend the calendar shortcode to show multiple monthes at once.' , 'easyReservations' ));?></td>
							<td style="font-weight:bold;text-align:center"><?php if($multical) echo '<a style="color:#118D18">'.$multical_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
							<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestc; ?></td>
							<td style="font-weight:bold;text-align:center"><?php echo '7.50 &#36;<br>5,00 &euro;'; ?></td>
							<td style="font-weight:bold;text-align:right"><?php if($multical) echo '<a href="admin.php?page=reservation-settings&site=pay">'.__( 'Settings' , 'easyReservations' ).'</a> | <a href="mailto:multical@feryaz.de">'.__( 'Support' , 'easyReservations' ).'</a>'; else echo '<a href="http://www.feryaz.de/multical-module/" target="_blank">'.__( 'Buy now' , 'easyReservations' ).'</a>'; ?></td>
						</tr>
						<?php
							$chat_current_version = "1.0"; $action= ''; $color = ''; 
							if(function_exists('easyreservations_generate_chat')){
								$chat = 2;
								$chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false);
								$chat_current_version = $chat_data['Version'];
								if(version_compare($chat_data['Version'], $xml->latestd) == -1) $color = 'color:#FF3B38';
								$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="chat"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
							} else{
								if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php')){
									$chat = 1;
									$chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false);
									$chat_current_version = $chat_data['Version'];
									if(version_compare($chat_data['Version'], $xml->latestd) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="chat"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
								} else $chat = 0;
							}
						?>
						<tr <?php if($chat != 2) echo 'class="inactive"'; ?>>
							<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/chat.png"></td>
							<td><b><a href="http://www.feryaz.de/chat-module/" target="_blank"><?php printf ( __( 'GuestContact Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
							<td><?php printf ( __( 'Let your users pay their reserservations directly throug chat! Expands the form and the user editation by a chat buy now button. Payment verfication by IPN.' , 'easyReservations' ));?></td>
							<td style="font-weight:bold;text-align:center"><?php if($chat) echo '<a style="color:#118D18">'.$chat_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
							<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestd; ?></td>
							<td style="font-weight:bold;text-align:center"><?php echo '10.00 &#36;<br>7,50 &euro;'; ?></td>
							<td style="font-weight:bold;text-align:right"><?php if($chat) echo '<a href="admin.php?page=reservation-settings&site=pay">'.__( 'Settings' , 'easyReservations' ).'</a> | <a href="mailto:chat@feryaz.de">'.__( 'Support' , 'easyReservations' ).'</a>'; else echo '<a href="http://www.feryaz.de/chat-module/" target="_blank">'.__( 'Buy now' , 'easyReservations' ).'</a>'; ?></td>
						</tr>
						<?php
							$paypal_current_version = "1.0"; $action =''; $color = '';
							if(function_exists('easyreservations_generate_paypal_button')){
								$paypal = 2;
								$paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false);
								$paypal_current_version = $paypal_data['Version'];
								if(version_compare($paypal_data['Version'], $xml->latestp) == -1) $color = 'color:#FF3B38';
								$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="paypal"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
							} else{
								if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php')){
									$paypal = 1;
									$paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false);
									$paypal_current_version = $paypal_data['Version'];
									if(version_compare($paypal_data['Version'], $xml->latestp) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="paypal"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
								} else $paypal = 0;
							}
						?>
						<tr <?php if($paypal != 2) echo 'class="inactive"'; ?>>
							<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/paypal.png"></td>
							<td><b><a href="http://www.feryaz.de/paypal-module/" target="_blank"><?php printf ( __( 'PayPal Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
							<td><?php printf ( __( 'Let your users pay their reserservations directly throug PayPal! Expands the form and the user editation by a PayPal buy now button. Payment verfication by IPN.' , 'easyReservations' ));?></td>
							<td style="font-weight:bold;text-align:center"><?php if($paypal) echo '<a style="color:#118D18">'.$paypal_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>';; ?></td>
							<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestp; ?></td>
							<td style="font-weight:bold;text-align:center"><?php echo '17.50 &#36;12,50 &euro;'; ?></td>
							<td style="font-weight:bold;text-align:right"><?php if($paypal) echo '<a href="admin.php?page=reservation-settings&site=pay">'.__( 'Settings' , 'easyReservations' ).'</a> | <a href="mailto:paypal@feryaz.de">'.__( 'Support' , 'easyReservations' ).'</a>'; else echo '<a href="http://www.feryaz.de/paypal-module/" target="_blank">'.__( 'Buy now' , 'easyReservations' ).'</a>'; ?></td>
						</tr> <?php */ ?>
					</tbody>
				</table>
			<div style="float:right;text-align:right;margin:7px;padding:5px">
				<a class="button" href="admin.php?page=reservation-settings&site=plugins&check"><?php if(get_option('easyreservations-notifier-cache')) echo __( 'Check For Updates' , 'easyReservations' ); else echo  __( 'Turn on update notifier' , 'easyReservations' );  ?></a><br>
				<br><?php printf ( __( 'Last check' , 'easyReservations' ));?>: <?php echo date(RESERVATIONS_DATE_FORMAT." H:i", get_option( 'easyreservations-notifier-last-updated')); ?>
			</div>
			<h2><?php printf ( __( 'Install/Update Module' , 'easyReservations' ));?></h2>
			<form enctype="multipart/form-data"  id="reservation_core_upload" name="reservation_core_upload" method="post">
				<input type="hidden" name="action" value="reservation_core_upload_plugin">
				<input type="hidden" name="max_file_size" value="100000">
				<input name="reservation_core_upload_file" type="file" size="50" maxlength="100000" accept="text/*"><br>
				<input type="button" value="<?php printf ( __( 'Install' , 'easyReservations' ));?>" onclick="document.getElementById('reservation_core_upload').submit(); return false;" style="margin-top:7px;" class="easySubmitButton-primary" >
			</form>
			<?php
		}
	}

	function easyreservations_update_notifier_menu(){
	
		if(function_exists('easyreservations_generate_paypal_button')){
			$paypal = true;
			$paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false); // Get theme data from style.css (current version is what we want)
		} else $paypal = false;

		if(function_exists('easyreservations_generate_chat')){ 
			$chat = true;
			$chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false); // Get theme data from style.css (current version is what we want)
		} else $chat = false;

		if(function_exists('easyreservations_generate_multical')){ 
			$multical = true;
			$multical_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false); // Get theme data from style.css (current version is what we want)
		} else $multical = false;

		if($paypal === true || $chat === true || $multical === true){
			if(get_option('easyreservations-notifier-cache')) $xml = easyreservations_latest_modules_versions(86400);
			else {
				$xml = new stdClass();
				$xml->latestc = '1.0';
				$xml->latestd = '1.0';
				$xml->latestp = '1.0';
			}
			if(($paypal == true && version_compare($paypal_data['Version'], $xml->latestp) == -1) || ($chat == true && version_compare($chat_data['Version'], $xml->latestd) == -1) ||  ($chat == true && version_compare($multical_data['Version'], $xml->latestc) == -1)) {

				$reservation_main_permission=get_option("reservations_main_permission");
				if(isset($reservation_main_permission['settings'])) $cap = $reservation_main_permission['settings'];
				else $cap = 'edit_posts';

				add_submenu_page('reservations', __('New Module Update','easyReservations'), __('Update!','easyReservations') . '<span class="update-plugins count-1"><span class="update-count">1</span></span>', $cap, 'reservation-update', 'easyreservations_update_notifier');
			}
		}
	}

	add_action('admin_menu', 'easyreservations_update_notifier_menu');

	function easyreservations_update_notifier() {
			if(get_option('easyreservations-notifier-cache')) $xml = easyreservations_latest_modules_versions(86400);
			else {
				$xml = new stdClass();
				$xml->latestc = '1.0';
				$xml->latestd = '1.0';
				$xml->latestp = '1.0';
			}

		if(easyreservation_is_paypal()){
			$paypal = true;
			$paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false); // Get theme data from style.css (current version is what we want)
		} else $paypal = false;
		if(easyreservation_is_chat()){ 
			$chat = true;
			$chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false); // Get theme data from style.css (current version is what we want)
		} else $chat = false;
		if(easyreservation_is_multical()){ 
			$multical = true;
			$multical_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false); // Get theme data from style.css (current version is what we want)
		} else $multical = false;
		?><style>
			.update-nag {display: none;}
			#instructions {max-width: 800px;}
			h3.title {margin: 30px 0 0 0; padding: 30px 0 0 0; border-top: 1px solid #ddd;}
		</style>
<?php
		if($paypal == true && version_compare($paypal_data['Version'], $xml->latestp) == -1){ ?>
		<div class="wrap" style="float:none">
			<div id="icon-tools" class="icon32"></div>
			<h2><?php echo $paypal_data['Name']; ?> Theme Updates</h2>
			<div id="message" class="updated below-h2"><p><strong>There is a new version of the <?php echo $paypal_data['Name']; ?> theme available.</strong> You have version <?php echo $paypal_data['Version']; ?> installed. Update to version <?php echo $xml->latestp; ?>.</p></div>
			<img style="float: left; margin: 0 20px 20px 0; border: 1px solid #ddd;" src="<?php echo get_bloginfo( 'template_url' ) . '/screenshot.png'; ?>" />
			<?php //echo WP_PLUGIN_DIR.'/easyreservations/screenshot.png'; ?>
			<div id="instructions" style="min-height:250px;width: 400px;float:left">
				<h3>Update Download and Instructions</h3>
				<p><strong>Please note:</strong> make a <strong>backup</strong> of the Plugin inside your WordPress installation folder <strong>/wp-content/plugins/easyreservations/</strong></p>
				<p>To update the Module click <a href="http://www.feryaz.de/paypal-module/" target="_blank">here</a>, login to your account, and re-download the Module like you did when you bought it.</p>
				<p>To Install the Module head over to the modules settings, choose the .zip file and enter your FTP Datas or extract the .zip's content directly through FTP to <strong>/wp-content/plugins/easyreservations/</strong> and overwrite the old ones.
			</div>
			<div style="margin-left:30px;display:inline-block;float:none">
				<?php echo $xml->changelogp; ?>
			</div>
			<h3 class="title" style="border-bottom:1px solid #DDDDDD;border-top:0px;"></h3>
		</div>
	<?php } 
		if($chat == true && version_compare($chat_data['Version'], $xml->latestd) == -1){ ?>
		<div class="wrap" style="float:Inherit;width:99%">
			<div id="icon-tools" class="icon32"></div>
			<h2><?php echo $chat_data['Name']; ?> Update</h2>
			<div id="message" class="updated below-h2"><p><strong>There is a new version of the <?php echo $chat_data['Name']; ?> available.</strong> You have version <?php echo $chat_data['Version']; ?> installed. Update to version <?php echo $xml->latestd; ?>.</p></div>
			<img style="float:left; margin: 0 20px 20px 0; border: 1px solid #ddd;" src="<?php echo get_bloginfo( 'template_url' ) . '/screenshot.png'; ?>" />
			<?php //echo WP_PLUGIN_DIR.'/easyreservations/screenshot.png'; ?>
			<div id="instructions" style="min-height:250px;width: 400px;float:left">
				<h3>Update Download and Instructions</h3>
				<p><strong>Please note:</strong> make a <strong>backup</strong> of the Plugin inside your WordPress installation folder <strong>/wp-content/plugins/easyreservations/</strong></p>
				<p>To update the Module click <a href="http://www.feryaz.de/chat-module/" target="_blank">here</a>, login to your account, and re-download the Module like you did when you bought it.</p>
				<p>To Install the Module head over to the modules settings, choose the .zip file and enter your FTP Datas or extract the .zip's content directly through FTP to <strong>/wp-content/plugins/easyreservations/</strong> and overwrite the old ones.
			</div>
			<div style="margin-left:30px;display:inline-block">
				<?php echo $xml->changelogd; ?>
			</div>
			<h3 class="title" style="border-bottom:1px solid #DDDDDD;border-top:0px;"></h3>
		</div>
	<?php } 
		if($multical == true && version_compare($multical_data['Version'], $xml->latestd) == -1){ ?>
		<div class="wrap" style="float:Inherit;width:99%">
			<div id="icon-tools" class="icon32"></div>
			<h2><?php echo $multical_data['Name']; ?> Update</h2>
			<div id="message" class="updated below-h2"><p><strong>There is a new version of the <?php echo $multical_data['Name']; ?> available.</strong> You have version <?php echo $multical_data['Version']; ?> installed. Update to version <?php echo $xml->latestc; ?>.</p></div>
			<img style="float:left; margin: 0 20px 20px 0; border: 1px solid #ddd;" src="<?php echo get_bloginfo( 'template_url' ) . '/screenshot.png'; ?>" />
			<?php //echo WP_PLUGIN_DIR.'/easyreservations/screenshot.png'; ?>
			<div id="instructions" style="min-height:250px;width: 400px;float:left">
				<h3>Update Download and Instructions</h3>
				<p><strong>Please note:</strong> make a <strong>backup</strong> of the Plugin inside your WordPress installation folder <strong>/wp-content/plugins/easyreservations/</strong></p>
				<p>To update the Module click <a href="http://www.feryaz.de/chat-module/" target="_blank">here</a>, login to your account, and re-download the Module like you did when you bought it.</p>
				<p>To Install the Module head over to the modules settings, choose the .zip file and enter your FTP Datas or extract the .zip's content directly through FTP to <strong>/wp-content/plugins/easyreservations/</strong> and overwrite the old ones.
			</div>
			<div style="margin-left:30px;display:inline-block">
				<?php echo $xml->changelogc; ?>
			</div>
			<h3 class="title" style="border-bottom:1px solid #DDDDDD;border-top:0px;"></h3>
		</div>
	<?php } 
	}

	// This function retrieves a remote xml file on my server to see if there's a new update
	// For performance reasons this function caches the xml content in the database for XX seconds ($interval variable)
	function easyreservations_latest_modules_versions($interval) {
		// remote xml file location
		$notifier_file_url = 'http://feryaz.square7.ch/notifier.xml';

		$db_cache_field = 'easyreservations-notifier-cache';
		$db_cache_field_last_updated = 'easyreservations-notifier-last-updated';
		$last = get_option( $db_cache_field_last_updated );
		// check the cache
		if ( !$last || (( time() - $last ) > $interval) ) {
			// cache doesn't exist, or is old, so refresh it
			if( function_exists('curl_init') ) { // if cURL is available, use it...
				$ch = curl_init($notifier_file_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				$cache = curl_exec($ch);
				curl_close($ch);
			} else {
				$cache = file_get_contents($notifier_file_url); // ...if not, use the common file_get_contents()
			}

			if ($cache) {
				// we got good results
				update_option( $db_cache_field, $cache );
				update_option( $db_cache_field_last_updated, time() );
			}
			// read from the cache file
			$notifier_data = get_option( $db_cache_field );
		} else {
			// cache file is fresh enough, so read from it
			$notifier_data = get_option( $db_cache_field );
		}

		$xml = simplexml_load_string($notifier_data); 

		return $xml;
	}

	function easyreservation_is_paypal(){
		$active =get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php') && is_array($active) && in_array('paypal', $active)) return true;
		else return false;
	}

	function easyreservation_is_chat(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php') && is_array($active) && in_array('chat', $active)) return true;
		else return false;
	}

	function easyreservation_is_multical(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php') && is_array($active) && in_array('multical', $active)) return true;
		else return false;
	}

	function easyreservation_is_import(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php') && is_array($active) && in_array('import', $active)) return true;
		else return false;
	}

	function easyreservation_activate_module($module){
		$active = get_option('reservations_active_modules');
		$active[] = $module;
		update_option("reservations_active_modules",$active);
		
	}

	function easyreservation_deactivate_module($module){
		$active = get_option('reservations_active_modules');
		if(!empty($active)){
			foreach($active as $key => $mod){
				if($mod == $module){
					unset($active[$key]);
					break;
				}
			}
		}
		update_option("reservations_active_modules", $active);
	}
?>