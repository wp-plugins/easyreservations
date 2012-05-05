<?php
/*
Plugin Name: Module Core
Plugin URI: http://www.easyreservations.oef/module/
Description: The core contains the modules overview, the module installation and the module update notifier
Version: 1.2
Author: Feryaz Beer
License:GPL2
*/

	if(is_admin()){
		add_action('er_set_tab_add', 'easyreservations_core_add_settings_tab');

		function easyreservations_core_add_settings_tab(){ 

			if(isset($_GET['site']) && $_GET['site'] == "plugins") $current = 'current'; else $current = '';
			$tab = '<li ><a href="admin.php?page=reservation-settings&site=plugins" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_IMAGES_DIR.'/plugin.png"> '. __( 'Modules' , 'easyReservations' ).'</a></li>';

			echo $tab;

		}

		add_action('er_set_save', 'easyreservations_core_save_settings');

		function easyreservations_core_save_settings(){

			if(isset($_GET['site']) && $_GET['site'] == "plugins"){
				if(isset($_GET['check'])){
					easyreservations_latest_modules_versions(0);
				} elseif(isset($_GET['activate'])){
					echo '<div class="updated"><p>'.sprintf(__( 'Module %s activated' , 'easyReservations' ), '<b>'.$_GET['activate'].'</b>').'</p></div>';
				} elseif(isset($_GET['deactivate'])){
					echo '<div class="updated"><p>'.sprintf(__( 'Module %s deactivated' , 'easyReservations' ), '<b>'.$_GET['deactivate'].'</b>').'</p></div>';
				}

				if(isset($_FILES['reservation_core_upload_file']) || isset($_GET['file_name'])){
					if(isset($_FILES['reservation_core_upload_file'])) $file_name = $_FILES['reservation_core_upload_file']['name']; else $file_name = $_GET['file_name'];
					$file_tmp_name = $_FILES['reservation_core_upload_file']['tmp_name'];
					if(isset($_FILES['reservation_core_upload_file']))  $file_type = $_FILES['reservation_core_upload_file']['type']; else  $file_type = 'application/x-zip' ;
					$file_size = $_FILES['size'];
					$plugin_dir = WP_PLUGIN_DIR.'/easyreservations/';
					$uploads = wp_upload_dir();
					$saved_file_location = $uploads['basedir'].'/'. $file_name;

					if(preg_match("/(PayPal|Import|datepicker|GuestContact|Calendar|extendedCalendar|Search|)/i", $file_name) && ($file_type == 'application/zip'  || $file_type == 'application/x-zip' || $file_type == 'application/x-zip-compressed'  || isset($_GET['file_name']))){
						if(move_uploaded_file($file_tmp_name, $saved_file_location) || isset($_GET['file_name'])) {
							$url = 'admin.php?page=reservation-settings&site=plugins&file_name='.$file_name;
							if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false) ) ) {
								$error = 1;
							} elseif ( ! WP_Filesystem($creds) ) {
								request_filesystem_credentials($url, 'ftp', true, false);
							} else {
								global $wp_filesystem;

										if(class_exists('ZipArchive')){
											$zip = new ZipArchive();  
											$x = $zip->open($saved_file_location);  
											if($x === true){  
													$zip->extractTo($plugin_dir);  
													$zip->close();                
											} else {
												WP_Filesystem();
												$my_dirs = ''; //What should this be? I'm already passing he $target directory
												unzip_file($saved_file_location, $plugin_dir);
											}
										} 
										unlink($saved_file_location);
										echo '<br><div class="updated"><p>'.sprintf(__( 'Module %s installed successfully' , 'easyReservations' ), '<b>'.str_replace('.zip', '', str_replace( '-', ' ', $file_name)).'</b>').'</p></div>';
									
							}
						} else echo '<br><div class="error"><p>'.__( 'Upload failed' , 'easyReservations' ).'</p></div>';
					} else echo '<br><div class="error"><p>'.__( 'Wrong file' , 'easyReservations' ).'</p></div>';
				}
			}
		}

		add_action('er_set_add', 'easyreservations_core_add_settings');

		function easyreservations_core_add_settings(){
			$core_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/core/core.php', false);

			if(isset($_GET['site']) && $_GET['site'] == "plugins"){
				if(get_option('easyreservations-notifier-cache')) $xml = easyreservations_latest_modules_versions(86400);
				else {
					$xml = new stdClass();
					$xml->latestc = '1.1.1'; //Calendar
					$xml->latestd = '1.0.1'; //Chat
					$xml->latestp = '1.0'; //PayPal
					$xml->latestlang = '1.0'; //language
					$xml->latests = '1.1'; //searchFrom
				}

				$import_avail_version = "1.1";
				$datepicker_avail_version = "1.0";
				$chat_current_version = "1.0.1";
				$lang_current_version = "1.0";
				$multical_current_version = "1.1.1";
				$paypal_current_version = "1.0";
				$search_current_version = "1.1";
				$deprecated = 0; ?>
					<input type="hidden" name="action" value="reservation_core_settings">
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="width:99%;">
						<thead>
							<tr>
								<th style="width:10px"></th>
								<th><?php printf ( __( 'Name' , 'easyReservations' ));?></th>
								<th style="width:50%"><?php printf ( __( 'Description' , 'easyReservations' ));?></th>
								<th style="text-align:center"><?php printf ( __( 'Installed' , 'easyReservations' ));?></th>
								<th style="text-align:center"><?php printf ( __( 'Actual' , 'easyReservations' ));?></th>
								<th style="text-align:center;width:80px;"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
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
								if(function_exists('easyreservations_generate_import')){
									$import = 2;
									$import_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php', false);
									$import_current_version = $import_data['Version'];
									if(version_compare($import_current_version, $import_avail_version) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="import"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
								} else{
									if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php')){
										$import = 1;
										$import_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php', false);
										$import_current_version = $import_data['Version'];
										if(version_compare($import_current_version, $import_avail_version) == -1) $color = 'color:#FF3B38';
										$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="import"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
									} else $import = 0;
								}
							?>
							<tr <?php if($import > 0 && version_compare($import_current_version, $import_avail_version)){ echo 'class="deprecated"'; $deprecated++; } elseif($import != 2) echo 'class="inactive"'; ?>>
								<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/import.png"></td>
								<td><b><a href="http://easyreservations.org/module/import/" target="_blank"><?php printf ( __( 'Import Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
								<td><?php printf ( __( 'Import reservations from .XML backup files.' , 'easyReservations' ));?></td>
								<td style="font-weight:bold;text-align:center"><?php if($import > 0) echo '<a style="color:#118D18">'.$import_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
								<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $import_avail_version; ?></td>
								<td style="font-weight:bold;text-align:center"><b>free</b></td>
								<td style="font-weight:bold;text-align:right"><?php if($import > 0) echo  '<a href="http://easyreservations.org/module/import/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/import/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; ?></td>
							</tr>
							<?php
								$color = '';$action ='';
								if(function_exists('easyreservations_register_datepicker_style')){
									$datepicker = 2;
									$datepicker_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/datepicker/datepicker.php', false);
									$datepicker_current_version = $datepicker_data['Version'];
									if(version_compare($datepicker_current_version, $datepicker_avail_version) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="datepicker"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
								} else{
									if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/datepicker/datepicker.php')){
										$datepicker = 1;
										$datepicker_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/datepicker/datepicker.php', false);
										$datepicker_current_version = $datepicker_data['Version'];
										if(version_compare($datepicker_current_version, $datepicker_avail_version) == -1) $color = 'color:#FF3B38';
										$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="datepicker"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
									} else $datepicker = 0;
								}
								if($datepicker > 0){
							?>
							<tr <?php if($datepicker != 2) echo 'class="inactive"'; ?>>
								<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/to.png"></td>
								<td><b><a href="http://easyreservations.org/module/datepicker/" target="_blank"><?php printf ( __( 'Datepicker Styles' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
								<td><?php printf ( __( 'Choose from two new styles for the datepickers in forms and admin.' , 'easyReservations' ));?></td>
								<td style="font-weight:bold;text-align:center"><?php if($datepicker > 0) echo '<a style="color:#118D18">'.$datepicker_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
								<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $datepicker_avail_version; ?></td>
								<td style="font-weight:bold;text-align:center"><b><?php echo '3,00 &euro;'; ?></b></td>
								<td style="font-weight:bold;text-align:right"><?php if($datepicker > 0) echo  '<a href="http://easyreservations.org/module/datepicker/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/datepicker/" target="_blank">'.__( 'More info' , 'easyReservations' ).'</a>'; ?></td>
							</tr>
							<?php
								}
								$action= ''; $color = ''; 
								if(function_exists('easyreservations_translate_content')){
									$lang = 2;
									$lang_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/lang/lang.php', false);
									$lang_current_version = $lang_data['Version'];
									if(version_compare($lang_data['Version'], $xml->latestlang) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="lang"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
								} else {
									if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/lang/lang.php')){
										$lang = 1;
										$lang_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/lang/lang.php', false);
										$lang_current_version = $lang_data['Version'];
										if(version_compare($lang_data['Version'], $xml->latestlang) == -1) $color = 'color:#FF3B38';
										$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="lang"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
									} else $lang = 0;
								} if($lang > 0){ ?>
							<tr <?php if($lang != 2) echo 'class="inactive"'; ?>>
								<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"></td>
								<td><b><a href="http://easyreservations.org/module/lang/" target="_blank"><?php printf ( __( 'Translation Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
								<td><?php printf ( __( 'Function to make texts in forms and emails translatable.' , 'easyReservations' ));?></td>
								<td style="font-weight:bold;text-align:center"><?php if($lang) echo '<a style="color:#118D18">'.$lang_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
								<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestlang; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo '5,00 &euro;'; ?></td>
								<td style="font-weight:bold;text-align:right"><?php if($lang) echo '<a href="http://easyreservations.org/module/lang/">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/lang/" target="_blank">'.__( 'More info' , 'easyReservations' ).'</a>'; ?></td>
							</tr>
							<?php }
								$action= ''; $color = ''; 
								if(function_exists('easyreservations_generate_chat')){
									$chat = 2;
									$chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false);
									$chat_current_version = $chat_data['Version'];
									if(version_compare($chat_data['Version'], $xml->latestd) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="chat"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
								} else {
									if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php')){
										$chat = 1;
										$chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false);
										$chat_current_version = $chat_data['Version'];
										if(version_compare($chat_data['Version'], $xml->latestd) == -1) $color = 'color:#FF3B38';
										$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="chat"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
									} else $chat = 0;
								} ?>
							<tr <?php if($chat != 2) echo 'class="inactive"'; ?>>
								<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/chat.png"></td>
								<td><b><a href="http://easyreservations.org/module/chat/" target="_blank"><?php printf ( __( 'GuestContact Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
								<td><?php printf ( __( 'Be in contact with your guest. Provides a chat-like feature to user-edit and admin. New messages in table, dummy message at start, admin notices, avatars and fully AJAX driven.' , 'easyReservations' ));?></td>
								<td style="font-weight:bold;text-align:center"><?php if($chat) echo '<a style="color:#118D18">'.$chat_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
								<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestd; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo '7,50 &euro;'; ?></td>
								<td style="font-weight:bold;text-align:right"><?php if($chat) echo '<a href="http://easyreservations.org/module/chat/">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/chat/" target="_blank">'.__( 'More info' , 'easyReservations' ).'</a>'; ?></td>
							</tr>
							<?php
								$color = ''; $action = '';
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
								<td><b><a href="http://easyreservations.org/module/multical/" target="_blank"><?php printf ( __( 'ExtentedCalendar Module' , 'easyReservations' ));?></a></b><?php echo $action; ?></td>
								<td><?php printf ( __( 'Extend the calendar shortcode to show multiple months by an flexible grid (x*y) at once. Includes a new boxed calendar style.' , 'easyReservations' ));?></td>
								<td style="font-weight:bold;text-align:center"><?php if($multical > 0) echo '<a style="color:#118D18">'.$multical_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
								<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestc; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo '10,00 &euro;'; ?></td>
								<td style="font-weight:bold;text-align:right"><?php if($multical > 0) echo '<a href="http://easyreservations.org/module/multical/">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/multical/" target="_blank">'.__( 'More info' , 'easyReservations' ).'</a>'; ?></td>
							</tr>
							<?php
								$action =''; $color = '';
								if(function_exists('easyreservations_validate_payment')){
									$paypal = 2;
									$paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false);
									$paypal_current_version = $paypal_data['Version'];
									if(version_compare($paypal_data['Version'], $xml->latestp) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="paypal"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
								} elseif(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php')){
									$paypal = 1;
									$paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false);
									$paypal_current_version = $paypal_data['Version'];
									if(version_compare($paypal_data['Version'], $xml->latestp) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="paypal"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
								} else $paypal = 0;
								if($paypal > 0){
								?>
								<tr <?php if($paypal != 2) echo 'class="inactive"'; ?>>
									<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/paypal.png"></td>
									<td><b><a href="http://easyreservations.org/module/paypal/" target="_blank"><?php printf ( __( 'PayPal Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
									<td><?php printf ( __( 'Your guest can pay their reservations directly through PayPal! Adds the PayPal Buy Now Button after form submits and to userCP if not paid. Automatically approve new reservations and/or paid reservations. Payment verification by IPN.' , 'easyReservations' ));?></td>
									<td style="font-weight:bold;text-align:center"><?php if($paypal > 0) echo '<a style="color:#118D18">'.$paypal_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>';; ?></td>
									<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latestp; ?></td>
									<td style="font-weight:bold;text-align:center"><?php echo '15,00 &euro;'; ?></td>
									<td style="font-weight:bold;text-align:right"><?php if($paypal > 0) echo '<a href="admin.php?page=reservation-settings&site=pay">'.__( 'Settings' , 'easyReservations' ).'</a> | <a href="http://easyreservations.org/module/paypal/">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/paypal/" target="_blank">'.__( 'More info' , 'easyReservations' ).'</a>'; ?></td>
								</tr>
							<?php
								}
								$action =''; $color = '';
								if(function_exists('easyreservations_search_add_tinymce')){
									$search = 2;
									$search_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php', false);
									$search_current_version = $search_data['Version'];
									if(version_compare($search_data['Version'], $xml->latests) == -1) $color = 'color:#FF3B38';
									$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="deactivate" value="search"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
								} else{
									if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php')){
										$search = 1;
										$search_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php', false);
										$search_current_version = $search_data['Version'];
										if(version_compare($search_data['Version'], $xml->latests) == -1) $color = 'color:#FF3B38';
										$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/core/activate.php" method="post"><input type="hidden" name="activate" value="search"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
									} else $search = 0;
								} ?>
								<tr <?php if($search != 2) echo 'class="inactive"'; ?>>
									<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/search.png"></td>
									<td><b><a href="http://easyreservations.org/module/search/" target="_blank"><?php printf ( __( 'Search Module' , 'easyReservations' ));?></a></b><br><?php echo $action; ?></td>
									<td><?php printf ( __( 'New shortcode to let your guests search for available resources. No reload for searching, compatible to calendar, show price, show unavailable resources too, link to form with automatically selection. Each resource can have a small one-column calendar to show when its availble.' , 'easyReservations' ));?></td>
									<td style="font-weight:bold;text-align:center"><?php if($search) echo '<a style="color:#118D18">'.$search_current_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>';; ?></td>
									<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $xml->latests; ?></td>
									<td style="font-weight:bold;text-align:center"><?php echo '25,00 &euro;'; ?></td>
									<td style="font-weight:bold;text-align:right"><?php if($search) echo '<a href="http://easyreservations.org/module/search/">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/search/" target="_blank">'.__( 'More info' , 'easyReservations' ).'</a>'; ?></td>
								</tr>
						</tbody>
					</table>
					<?php if($deprecated > 0){
						echo '<p><div class="error"><p>'.$deprecated.' '. _n('Module is', 'Modules are', $deprecated, 'easyReservations').' '.__('deprecated and wont work anymore. Please update  from', 'easyReservations' ).' <a href="http://easyreservations.org/module/">easyreservations.org</a>!</p></div></p>';
					} ?>
				<div style="float:right;text-align:right;margin:7px;padding:5px">
					<a class="button" href="admin.php?page=reservation-settings&site=plugins&check"><?php if(get_option('easyreservations-notifier-cache')) echo __( 'Check For Updates' , 'easyReservations' ); else echo  __( 'Turn on update notifier' , 'easyReservations' );  ?></a><br>
					<?php if(get_option('easyreservations-notifier-cache')){ ?><br><?php printf ( __( 'Last check' , 'easyReservations' ));?>: <?php echo date(RESERVATIONS_DATE_FORMAT." H:i", get_option( 'easyreservations-notifier-last-updated')); ?><?php } ?>
				</div>
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="width:200px;margin-top:5px">
						<thead>
							<tr>
								<th><?php echo __( 'Install or Update Module' , 'easyReservations' );?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<form enctype="multipart/form-data"  action="admin.php?page=reservation-settings&site=plugins" name="reservation_core_upload" id="reservation_core_upload" method="post">
										<p>
											<?php do_action('er_mod_inst'); ?>
										</p>
										<input type="hidden" name="action" value="reservation_core_upload_plugin">
										<input type="hidden" name="max_file_size" value="100000">
										<input name="reservation_core_upload_file" type="file" size="50" maxlength="100000" accept="text/*"><br>
										<input type="button" value="<?php printf ( __( 'Install' , 'easyReservations' ));?>" onclick="document.getElementById('reservation_core_upload').submit(); return false;" style="margin-top:7px;" class="easySubmitButton-primary" >
									</form>
								</td>
							</tr>
						</tbody>
					</table>
				<?php
			}
		}

		function easyreservations_update_notifier_menu(){

			if(function_exists('easyreservations_validate_payment')){
				$paypal = true; $paypal_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false, false);
			} else $paypal = false;

			if(function_exists('easyreservations_generate_chat')){ 
				$chat = true; $chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false, false);
			} else $chat = false;

			if(function_exists('easyreservations_generate_multical')){
				$multical = true; $multical_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false, false);
			} else $multical = false;

			if(function_exists('easyreservations_search_add_tinymce')){ 
				$search = true; $search_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false, false);
			} else $search = false;

			if($paypal === true || $chat === true || $multical === true || $search === true){
				$xml = get_option('easyreservations-notifier-cache');
				if($xml){ 
					$xml = simplexml_load_string($xml);
					if(($paypal == true && version_compare($paypal_data['Version'], $xml->latestp) == -1) || ($chat == true && version_compare($chat_data['Version'], $xml->latestd) == -1) ||  ($multical == true && version_compare($multical_data['Version'], $xml->latestc) == -1) ||  ($search == true && version_compare($search_data['Version'], $xml->latests) == -1)) {
						easyreservations_update_notifier();
					}
				}
			}
		}

		add_action('admin_notices', 'easyreservations_update_notifier_menu');

		function easyreservations_update_notifier() {
			if(get_option('easyreservations-notifier-cache')) $xml = easyreservations_latest_modules_versions(86400);

			if(easyreservation_is_paypal()){
				$data[]  = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false);
				$plugin[] = 'paypal';
			}
			if(easyreservation_is_chat()){ 
				$data[] = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/chat/chat.php', false);
				$plugin[] = 'chat';
			} 
			if(easyreservation_is_multical()){ 
				$data[] = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false);
				$plugin[] = 'multical';
			}
			if(easyreservation_is_search()){
				$data[] = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php', false);
				$plugin[] = 'search';
			}
			$nr = 0; $message = '';
			foreach($data as $key => $upd){
				if($plugin[$key] == 'paypal'){
					$number = $xml->latestp;
					$name = $xml->namep;
				} elseif($plugin[$key] == 'chat'){
					$number = $xml->latestd;
					$name = $xml->named;
				} elseif($plugin[$key] == 'multical'){
					$number = $xml->latestc;
					$name = $xml->namec;
				} elseif($plugin[$key] == 'search'){
					$number = $xml->latests;
					$name = $xml->names;
				}  elseif($plugin[$key] == 'lang'){
					$number = $xml->latestlang;
					$name = $xml->namelang;
				}

				if(version_compare($upd['Version'], $number) == -1){
					$link = 'http://www.easyreservations.org/module/'.$plugin[$key].'/';
					$message .= ' <a href="'.$link.'">'.$name.'</a> '.$number.',';
					$nr++;
				}
			}
			if($nr == 1)  $update_message = __('New Update is available:', 'easyResrvations').substr($message, 0, -1);
			elseif($nr > 1) $update_message = __('New Updates are available:', 'easyResrvations').substr($message, 0, -1);

			if($nr > 0) echo '<div class="update-nag">'.$update_message.'! Please update now.</div>';
		}

		// This function retrieves a remote xml file on my server to see if there's a new update
		// For performance reasons this function caches the xml content in the database for XX seconds ($interval variable)
		function easyreservations_latest_modules_versions($interval) {
			// remote xml file location
			$notifier_file_url = 'http://easyreservations.org/notifier.xml';

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

				if ($cache){
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

	function easyreservation_is_search(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php') && is_array($active) && in_array('search', $active)) return true;
		else return false;
	}

	function easyreservation_is_import(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/import/import.php') && is_array($active) && in_array('import', $active)) return true;
		else return false;
	}

	function easyreservation_is_datepicker(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/datepicker/datepicker.php') && is_array($active) && in_array('datepicker', $active)) return true;
		else return false;
	}

	function easyreservation_is_language(){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/lang/lang.php') && is_array($active) && in_array('lang', $active)) return true;
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