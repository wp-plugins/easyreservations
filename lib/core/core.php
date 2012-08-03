<?php
/*
Plugin Name: Module Core
Plugin URI: http://www.easyreservations.oef/module/
Description: The core contains the modules overview, the module installation and the module update notifier
Version: 1.3
Author: Feryaz Beer
License:GPL2
*/

	if(is_admin()){
		add_action('er_set_tab_add', 'easyreservations_core_add_settings_tab');

		function easyreservations_core_add_settings_tab(){ 

			if(isset($_GET['site']) && $_GET['site'] == "plugins") $current = 'current'; else $current = '';
			$tab = '<li ><a href="admin.php?page=reservation-settings&site=plugins" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_URL.'/images/plugin.png"> '. __( 'Premium' , 'easyReservations' ).'</a></li>';

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

					if(preg_match("/(easyreservations|module|premium)/i", $file_name) && ($file_type == 'application/zip'  || $file_type == 'application/x-zip' || $file_type == 'application/x-zip-compressed' || $file_type == 'text/html' || $file_type == 'application/octet-stream' || isset($_GET['file_name']))){
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
			$core_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/core/core.php', false);

			if(isset($_GET['site']) && $_GET['site'] == "plugins"){
				if(get_option('easyreservations-notifier-cache')) $xml = easyreservations_latest_modules_versions(86400);
				else {
					$xml = new stdClass();
					$xml->latestc = '1.1.5'; //Calendar
					$xml->latestd = '1.2.3'; //Chat
					$xml->latestp = '1.2.4'; //PayPal
					$xml->latestlang = '1.1.1'; //language
					$xml->latests = '1.1.9'; //searchFrom
					$xml->latesthc = '1.0.2'; //hourlyCal
				}
				$the_modules = array(
						array(
								'slug' => 'invoice',
								'title' => __( 'Invoice Module' , 'easyReservations' ),
								'content' => __( 'Generate totally customazible Invoices automatically from predefined templates. Including an editor for admins, invoices as email attachments and correct A4 Letter formats.' , 'easyReservations' ),
								'xml' => '',
								'function' => 'easyreservations_generate_invoice',
								'vers' => '1.0.3',
								'image' => 'invoice',
								'beta' => 0
						),
						array(
								'slug' => 'htmlmails',
								'title' => __( 'htmlMails Module' , 'easyReservations' ),
								'content' => __( 'Style your eMails with HTML to increase the appereance of your hospitality.' , 'easyReservations' ),
								'xml' => '',
								'function' => 'easyreservations_send_multipart_mail',
								'vers' => '1.1.9',
								'image' => 'email',
								'beta' => 0
						),
						array(
								'slug' => 'paypal',
								'title' => __( 'PayPal Module' , 'easyReservations' ),
								'content' => __( 'Your guest can pay their reservations directly through PayPal! Adds the PayPal Buy Now Button after form submits and to userCP if not paid. Automatically approve new reservations and/or paid reservations. Payment verification by IPN.' , 'easyReservations' ),
								'xml' => 'latestp',
								'function' => 'easyreservations_validate_payment',
								'vers' => '1.2.4',
								'image' => 'paypal',
								'beta' => 0
						),
						array(
								'slug' => 'search',
								'title' => __( 'searchForm Module' , 'easyReservations' ),
								'content' => __( 'New shortcode to let your guests search for available resources. No reload for searching, compatible to calendar, show price, show unavailable resources too, link to form with automatically selection. Each resource can have a small one-column calendar to show when its availble.' , 'easyReservations' ),
								'xml' => 'latests',
								'function' => 'easyreservations_search_add_tinymce',
								'vers' => '1.1.8',
								'image' => 'search',
								'beta' => 0
						),
						array(
								'slug' => 'hourlycal',
								'title' => __( 'hourlyCalendar Module' , 'easyReservations' ),
								'content' => __( 'Show your guests the availability on a hourly basis.' , 'easyReservations' ),
								'xml' => 'latesthc',
								'function' => 'easyreservations_send_hourlycal_callback',
								'vers' => '1.0.2',
								'image' => 'time',
								'beta' => 0
						),
						array(
								'slug' => 'import',
								'title' => __( 'Import &amp; Export Module' , 'easyReservations' ),
								'content' => __( 'Export selectable reservations informations by time, selection or all as .xls, .csv or .xml and Import them from back from the .xml files.' , 'easyReservations' ),
								'xml' => '',
								'function' => 'easyreservations_generate_import',
								'vers' => '1.2.1',
								'image' => 'import',
								'beta' => 0
						),
						array(
								'slug' => 'lang',
								'title' => __( 'Multilingual Module' , 'easyReservations' ),
								'content' => __( 'Function to make texts in forms and emails translatable.' , 'easyReservations' ),
								'xml' => 'latestlang',
								'function' => 'easyreservations_translate_content',
								'vers' => '1.1.1',
								'image' => 'country',
								'beta' => 0
						),
						array(
								'slug' => 'useredit',
								'title' => __( 'Guest Editing Module' , 'easyReservations' ),
								'content' => __( ' Let your guests login with their reservations ID and email to edit their reservation afterwards. They can switch between their reservations in a table. In addition it provides a chat-like feature to user-edit and admin. New messages in table, dummy message at start, admin notices, avatars and fully AJAX driven.' , 'easyReservations' ),
								'xml' => 'latestd',
								'function' => 'easyreservations_generate_chat',
								'vers' => '1.2.3',
								'image' => 'chat',
								'beta' => 0
						),
						array(
								'slug' => 'statistics',
								'title' => __( 'Statistics Module' , 'easyReservations' ),
								'content' => __( 'Detailed statistics, charts, resources usage and a dashboards widget.' , 'easyReservations' ),
								'xml' => '',
								'function' => 'easyreservations_add_statistics_submenu',
								'vers' => '1.1.2',
								'image' => 'statistics',
								'beta' => 0
						),
						array(
								'slug' => 'styles',
								'title' => __( 'Styles' , 'easyReservations' ),
								'content' => __( 'New Admin, Calendar and Form stlye. In addition it changes your datepickers style and disable unavailble dates in it.' , 'easyReservations' ),
								'xml' => '',
								'function' => 'easyreservations_register_datepicker_style',
								'vers' => '1.2.1',
								'image' => 'to',
								'beta' => 0
						),
						array(
								'slug' => 'coupons',
								'title' => __( 'Coupon Module' , 'easyReservations' ),
								'content' => __( 'Let your guests enter coupon codes for discounts.' , 'easyReservations' ),
								'xml' => '',
								'function' => 'easyreservations_calculate_coupon',
								'vers' => '1.0.1',
								'image' => 'money',
								'beta' => 0
						),
						array(
								'slug' => 'multical',
								'title' => __( 'extentedCalendar Module' , 'easyReservations' ),
								'content' => __( 'Extend the calendar shortcode to show multiple months by an flexible grid (x*y) at once. Includes a new boxed calendar style.' , 'easyReservations' ),
								'xml' => 'latestc',
								'function' => 'easyreservations_generate_multical',
								'vers' => '1.1.5',
								'image' => 'day',
								'beta' => 0
						)
				);
				$deprecated = 0; ?>
					<input type="hidden" name="action" value="reservation_core_settings">
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="width:99%;">
						<thead>
							<tr>
								<th style="width:10px"></th>
								<th><?php echo __( 'Name' , 'easyReservations' );?></th>
								<th style="width:50%"><?php echo __( 'Description' , 'easyReservations' );?></th>
								<th style="text-align:center"><?php echo __( 'Installed' , 'easyReservations' );?></th>
								<th style="text-align:center"><?php echo __( 'Actual' , 'easyReservations' );?></th>
								<th style="text-align:right"><?php echo __( 'Link' , 'easyReservations' );?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>/images/plugin.png"></td>
								<td><b><?php echo $core_data['Name']; ?></b></td>
								<td><?php echo $core_data['Description']; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version']; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version']; ?></td>
								<td style="font-weight:bold;text-align:right"></td>
							</tr>
							<?php 
								foreach($the_modules as $module){
									$status = 0;
									if(function_exists($module['function'])) $status = 2;
									elseif(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$module['slug'].'/'.$module['slug'].'.php')) $status = 1;
									if(!empty($module['xml']) && isset($xml) && isset($xml->$module['xml'])) $actual_version = $xml->$module['xml'];
									else $actual_version = $module['vers'];
									if($status > 0){
										$data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$module['slug'].'/'.$module['slug'].'.php', false);
										$installed_version = $data['Version'];
										if(version_compare($installed_version, $actual_version) == -1) $color = 'color:#FF3B38';
										else $color = '';
										if($status == 1) $action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/core/activate.php" method="post"><input type="hidden" name="activate" value="'.$module['slug'].'"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
										else $action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/core/activate.php" method="post"><input type="hidden" name="deactivate" value="'.$module['slug'].'"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
									}
									if($module['beta'] == 0 || $status > 0){
									?>
									<tr <?php if($status != 2) echo 'class="inactive"'; ?>>
										<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL.'/images/'.$module['image']; ?>.png"></td>
										<td><b><a href="http://easyreservations.org/module/<?php echo $module['slug']; ?>/" target="_blank"><?php echo $module['title'];?></a></b><br><?php echo $action; ?></td>
										<td><?php echo $module['content'];?></td>
										<td style="font-weight:bold;text-align:center"><?php if($status > 0) echo '<a style="color:#118D18">'.$installed_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
										<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $actual_version; ?></td>
										<td style="font-weight:bold;text-align:right"><?php if($status > 0) echo  '<a href="http://easyreservations.org/module/'.$module['slug'].'/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/'.$module['slug'].'/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; ?></td>
									</tr><?php
									}
								}
							echo '</table>';

					if(isset($deprecated) && $deprecated > 0){
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
										<input type="hidden" name="max_file_size" value="1000000000">
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
				$chat = true; $chat_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/useredit/useredit.php', false, false);
			} else $chat = false;

			if(function_exists('easyreservations_generate_multical')){
				$multical = true; $multical_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false, false);
			} else $multical = false;

			if(function_exists('easyreservations_search_add_tinymce')){ 
				$search = true; $search_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php', false, false);
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

			if(easyreservations_is_module('paypal')){
				$data[]  = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/paypal/paypal.php', false);
				$plugin[] = 'paypal';
			}
			if(easyreservations_is_module('useredit')){ 
				$data[] = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/useredit/useredit.php', false);
				$plugin[] = 'chat';
			} 
			if(easyreservations_is_module('multical')){ 
				$data[] = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/multical/multical.php', false);
				$plugin[] = 'multical';
			}
			if(easyreservations_is_module('search')){
				$data[] = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/search/search.php', false);
				$plugin[] = 'search';
			}
			$nr = 0; $message = '';
			if(!empty($data)){
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

	function easyreservations_is_module($module){
		$active = get_option('reservations_active_modules');
		if(file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/$module/$module.php") && is_array($active) && in_array($module, $active)) return true;
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