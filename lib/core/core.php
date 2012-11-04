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

			if(isset($_GET['site']) && $_GET['site'] == "plugins" && current_user_can('activate_plugins')){
				if(isset($_GET['check'])){
					easyreservations_latest_modules_versions(0,false,true);
					echo '<div class="updated"><p>'.__( 'Checked for updates' , 'easyReservations' ).'</p></div>';
				} elseif(isset($_POST['prem_login'])){
					update_option('reservations_login', $_POST['prem_login'].'$%!$&'.$_POST['prem_pw']);
					echo '<div class="updated"><p>'.__( 'Logging in' , 'easyReservations' ).'</p></div>';
				} elseif(isset($_GET['activate'])){
					echo '<div class="updated"><p>'.sprintf(__( 'Module %s activated' , 'easyReservations' ), '<b>'.$_GET['activate'].'</b>').'</p></div>';
				} elseif(isset($_GET['logout'])){
					delete_option('reservations_login');
					echo '<div class="updated"><p>'.__( 'Logged out' , 'easyReservations' ).'</p></div>';
				} elseif(isset($_GET['activate_all'])){
					echo '<div class="updated"><p>'.__( 'All installed modules activated' , 'easyReservations' ).'</p></div>';
				} elseif(isset($_GET['deactivate_all'])){
					echo '<div class="updated"><p>'.__( 'All installed modules deactivated' , 'easyReservations' ).'</p></div>';
				} elseif(isset($_GET['changelog'])){
					echo '<div id="the_modules_changelog">'.easyreservations_latest_modules_versions(0,false,true,false,$_GET['changelog']).'</div>';
					exit;
				} elseif(isset($_GET['update']) || isset($_GET['install'])){
					if(isset($_GET['update'])) $updatestr = __( 'updated' , 'easyReservations' );
					else $updatestr = __( 'installed' , 'easyReservations' );
					if(isset($_GET['install'])) $_GET['update'] = $_GET['install'];
					$update = easyreservations_latest_modules_versions(86400, false, true, $_GET['update']);
					if($update === false) echo '<div class="error"><p>'.sprintf(__( 'Failure at updating module %1$s - %2$s' , 'easyReservations' ), '<b>'.$_GET['update'].'</b>', '<a href="http://easyreservations.org/module/'.$_GET['update'].'">update manually</a>').'</p></div>';
					elseif($update === 808) echo '<div class="error"><p>'.sprintf(__( 'Failure at copying module %1$s - %2$s' , 'easyReservations' ), '<b>'.$_GET['update'].'</b>', '<a href="http://easyreservations.org/module/'.$_GET['update'].'">update manually</a>').'</p></div>';
					elseif($update != 'creds') echo '<div class="updated"><p>'.sprintf(__( 'Module %1$s %2$s' , 'easyReservations' ), '<b>'.$_GET['update'].'</b>', $updatestr).'</p></div>';
				} elseif(isset($_GET['deactivate'])){
					echo '<div class="updated"><p>'.sprintf(__( 'Module %s deactivated' , 'easyReservations' ), '<b>'.$_GET['deactivate'].'</b>').'</p></div>';
				} elseif(isset($_GET['delete'])){
					$url = 'admin.php?page=reservation-settings&site=plugins&delete='.$_GET['delete'];
					if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false) ) ) {
						$error = 1;
					} elseif ( ! WP_Filesystem($creds) ) {
						request_filesystem_credentials($url, 'ftp', true, false);
					} else {
						global $wp_filesystem;
						$dir = WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$_GET['delete'].'/';
						foreach (scandir($dir) as $item) {
							if ($item == '.' || $item == '..') continue;
							unlink($dir.DIRECTORY_SEPARATOR.$item);
						}
						rmdir($dir);
						echo '<div class="updated"><p>'.sprintf(__( 'Module %s deleted' , 'easyReservations' ), '<b>'.$_GET['deactivate'].'</b>').'</p></div>';
					}
				}

				if(isset($_FILES['reservation_core_upload_file']) || isset($_GET['file_name'])){
					if(isset($_FILES['reservation_core_upload_file'])) $file_name = $_FILES['reservation_core_upload_file']['name']; else $file_name = $_GET['file_name'];
					$file_tmp_name = $_FILES['reservation_core_upload_file']['tmp_name'];
					if(isset($_FILES['reservation_core_upload_file'])) $file_type = $_FILES['reservation_core_upload_file']['type']; else $file_type = 'application/x-zip' ;
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
		
		function easyreservations_load_modules_array(){
				return array(
					'invoice' => array(
							'slug' => 'invoice',
							'title' => __( 'Invoice Module' , 'easyReservations' ),
							'content' => __( 'Generate totally customazible Invoices automatically from predefined templates. Including an editor for admins, invoices as email attachments and correct A4 Letter formats.' , 'easyReservations' ),
							'function' => 'easyreservations_generate_invoice',
							'least' => '1.0.4',
							'vers' => '1.0.6',
							'image' => 'invoice',
							'beta' => 0
					),
					'htmlmails' => array(
							'slug' => 'htmlmails',
							'title' => __( 'htmlMails Module' , 'easyReservations' ),
							'content' => __( 'Style your eMails with HTML to increase the appereance of your hospitality.' , 'easyReservations' ),
							'function' => 'easyreservations_send_multipart_mail',
							'least' => '1.1.2',
							'vers' => '1.1.2',
							'image' => 'email',
					),
					'paypal' => array(
							'slug' => 'paypal',
							'title' => __( 'Payment Module' , 'easyReservations' ),
							'content' => __( 'Your guest can pay their reservations directly through PayPal or leave their credit card details for manually booking. Automatically approve new reservations and/or paid reservations. Payment verification by IPN.' , 'easyReservations' ),
							'function' => 'easyreservations_validate_payment',
							'least' => '1.4',
							'vers' => '1.4',
							'image' => 'paypal',
					),
					'search' => array(
							'slug' => 'search',
							'title' => __( 'searchForm Module' , 'easyReservations' ),
							'content' => __( 'New shortcode to let your guests search for available resources. No reload for searching, compatible to calendar, show price, show unavailable resources too, link to form with automatically selection. Each resource can have a small one-column calendar to show when its availble.' , 'easyReservations' ),
							'function' => 'easyreservations_search_add_tinymce',
							'least' => '1.1.12',
							'vers' => '1.1.13',
							'image' => 'search',
					),
					'hourlycal' => array(
							'slug' => 'hourlycal',
							'title' => __( 'hourlyCalendar Module' , 'easyReservations' ),
							'content' => __( 'Show your guests the availability on a hourly basis.' , 'easyReservations' ),
							'function' => 'easyreservations_send_hourlycal_callback',
							'least' => '1.0.3',
							'vers' => '1.0.3',
							'image' => 'time',
					),
					'import' => array(
							'slug' => 'import',
							'title' => __( 'Export &amp; Import Module' , 'easyReservations' ),
							'content' => __( 'Export selectable reservations informations by time, selection or all as .xls, .csv or .least and Import them from back from the .least files.' , 'easyReservations' ),
							'function' => 'easyreservations_generate_import',
							'least' => '1.2.2',
							'vers' => '1.2.3',
							'image' => 'import',
					),
					'lang' => array(
							'slug' => 'lang',
							'title' => __( 'Multilingual Module' , 'easyReservations' ),
							'content' => __( 'Function to make texts in forms and emails translatable.' , 'easyReservations' ),
							'function' => 'easyreservations_translate_content',
							'least' => '1.1.1',
							'vers' => '1.1.1',
							'image' => 'country',
					),
					'useredit' => array(
							'slug' => 'useredit',
							'title' => __( 'Guest Editing Module' , 'easyReservations' ),
							'content' => __( ' Let your guests login with their reservations ID and email to edit their reservation afterwards. They can switch between their reservations in a table. In addition it provides a chat-like feature to user-edit and admin. New messages in table, dummy message at start, admin notices, avatars and fully AJAX driven.' , 'easyReservations' ),
							'function' => 'easyreservations_generate_chat',
							'least' => '1.2.6',
							'vers' => '1.2.6',
							'image' => 'chat',
					),
					'statistics' => array(
							'slug' => 'statistics',
							'title' => __( 'Statistics Module' , 'easyReservations' ),
							'content' => __( 'Detailed statistics, charts, resources usage and a dashboards widget.' , 'easyReservations' ),
							'function' => 'easyreservations_add_statistics_submenu',
							'least' => '1.1',
							'vers' => '1.1.3',
							'image' => 'statistics',
					),
					'stream' => array(
							'slug' => 'stream',
							'title' => __( 'Stream Module' , 'easyReservations' ),
							'content' => __( 'Text.' , 'easyReservations' ),
							'function' => 'easyreservations_install_stream',
							'least' => '1.0',
							'vers' => '1.0',
							'image' => 'stream',
							'beta' => 1,
					),
					'styles' => array(
							'slug' => 'styles',
							'title' => __( 'Styles' , 'easyReservations' ),
							'content' => __( 'New Admin, Calendar and Form stlye. In addition it changes your datepickers style and disable unavailble dates in it.' , 'easyReservations' ),
							'least' => '1.2',
							'function' => 'easyreservations_register_datepicker_style',
							'vers' => '1.2.5',
							'image' => 'to',
					),
					'coupons' => array(
							'slug' => 'coupons',
							'title' => __( 'Coupon Module' , 'easyReservations' ),
							'content' => __( 'Let your guests enter coupon codes for discounts.' , 'easyReservations' ),
							'least' => '1.0.7',
							'function' => 'easyreservations_calculate_coupon',
							'vers' => '1.0.7',
							'image' => 'money',
					),
					'multical' => array(
							'slug' => 'multical',
							'title' => __( 'extentedCalendar Module' , 'easyReservations' ),
							'content' => __( 'Extend the calendar shortcode to show multiple months by an flexible grid (x*y) at once. Includes a new boxed calendar style.' , 'easyReservations' ),
							'least' => '1.1',
							'function' => 'easyreservations_generate_multical',
							'vers' => '1.1.5',
							'image' => 'day',
					)
			);
		}

		function easyreservations_core_add_settings(){
			$core_data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/core/core.php', false);

			if(isset($_GET['site']) && $_GET['site'] == "plugins"){
				$login = false;
				$the_modules = easyreservations_load_modules_array();
				if($data = get_option('reservations_login')) $xml = easyreservations_latest_modules_versions(86400,$the_modules, true);
				else $login = true;
				if(isset($xml) && $xml && is_array($xml)) $the_modules = $xml;
				elseif(isset($xml) && $xml && is_string($xml)) $login_error = $xml; ?>
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
								<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/plugin.png"></td>
								<td><b><?php echo $core_data['Name']; ?></b></td>
								<td><?php echo $core_data['Description']; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version']; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version']; ?></td>
								<td style="font-weight:bold;text-align:right"></td>
							</tr>
							<?php 
								foreach($the_modules as $module){
									$status = 0;
									$newupdate = false;
									$deprecated = false;
									if(function_exists($module['function'])) $status = 2;
									elseif(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$module['slug'].'/'.$module['slug'].'.php')) $status = 1;
									$actual_version = $module['vers'];
									if($status > 0){
										$data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$module['slug'].'/'.$module['slug'].'.php', false);
										$installed_version = $data['Version'];
										if(version_compare($installed_version, $actual_version) == -1) $color = 'color:#FF3B38';
										else $color = '';
										if(version_compare($data['Description'], RESERVATIONS_VERSION) == +1) $deprecated = array(true,$data['Description']);
										elseif(version_compare($data['Version'], $module['least']) == -1) $deprecated = array(false, $module['least']);

										if($status == 1){
											$action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/core/activate.php" method="post" style="display:inline-block;"><input type="hidden" name="activate" value="'.$module['slug'].'"><a onclick="this.parentNode.submit();" href="#">'.__( 'Activate' , 'easyReservations' ).'</a></form>';
											$action .= ' <a href="admin.php?page=reservation-settings&site=plugins&delete='.$module['slug'].'" style="color:#ff5954">'.__( 'Delete' , 'easyReservations' ).'</a>';
										} else $action = '<form action="'.WP_PLUGIN_URL.'/easyreservations/lib/core/activate.php" method="post" style="display:inline-block;"><input type="hidden" name="deactivate" value="'.$module['slug'].'"><a onclick="javascript:this.parentNode.submit()" href="#">'.__( 'Deactivate' , 'easyReservations' ).'</a></form>';
										if($login === false){
											$action .= ' <a href="javascript:" onclick="get_changelog(\''.$module['slug'].'\');return true;" style="color:#1fa856">'.__( 'Changelog' , 'easyReservations' ).'</a>';
											if(isset($module['updated'])) $newupdate = true;
										}
									}
									if(!isset($module['beta']) || $status > 0){
								?>
									<tr class="<?php if($status != 2) echo 'inactive '; echo 'module_row_'.$module['slug']; ?>">
										<td style="text-align:center"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL.'/images/'.$module['image']; ?>.png"></td>
										<td><b><a href="http://easyreservations.org/module/<?php echo $module['slug']; ?>/" target="_blank" style="font-size: 12px;font-weight: bold;"><?php echo $module['title'];?></a></b><br><?php echo $action; ?></td>
										<td><?php 
											if($deprecated){
												if($deprecated[0]) $message = sprintf( __('Incompatibility - Update easyReservations to at least %s','easyReservations'), $deprecated[1]);
												else $message = sprintf( __('Incompatibility - This version of easyReservations needs at least version %s of this module','easyReservations'), $deprecated[1]);
												echo '<b style="color:#FF3B38">'.$message.'</b>';
											} else echo $module['content']; ?></td>
										<td style="font-weight:bold;text-align:center"><?php if($status > 0) echo '<a style="color:#118D18">'.$installed_version.'</a>'; else echo '<a style="color:#FF3B38">'.__( 'None' , 'easyReservations' ).'</a>'; ?></td>
										<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $actual_version; ?></td>
										<td style="font-weight:bold;text-align:right"><?php
											if($login === false){
												if($newupdate) echo '<a href="admin.php?page=reservation-settings&site=plugins&update='.$module['slug'].'" class="easySubmitButton-secondary">'.__( 'Update' , 'easyReservations' ).'</a>';
												elseif($status !== 0) echo '<a href="admin.php?page=reservation-settings&site=plugins&install='.$module['slug'].'" class="easySubmitButton-secondary">'.__( 'Reinstall' , 'easyReservations' ).'</a>';
												else echo '<a class="button-secondary" href="admin.php?page=reservation-settings&site=plugins&install='.$module['slug'].'" class="easySubmitButton-secondary">'. __( 'Install' , 'easyReservations' ).'</a>';
											} else{
												if($status > 0) echo  '<a href="http://easyreservations.org/module/'.$module['slug'].'/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>'; else echo '<a href="http://easyreservations.org/module/'.$module['slug'].'/" target="_blank">'.__( 'Download' , 'easyReservations' ).'</a>';
											} ?>
										</td>
									</tr><?php
									}
								}
							echo '</table>';
							?>
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="min-width:300px;width:300px;margin-top:5px;margin-right:5px;float:left;text-align:left;">
						<thead>
							<tr>
								<th><?php if($login) echo __( 'Premium Login' , 'easyReservations' ); else echo __( 'Premium Features' , 'easyReservations' ); ?>
									<?php if($login){?><input type="button" value="<?php printf ( __( 'Login' , 'easyReservations' ));?>" onclick="document.getElementById('reservation_prem_login').submit(); return false;" style="padding:4px 6px;float:right" class="easySubmitButton-primary" ><?php } ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="text-align:center">
									<?php if($login){
										if(isset($login_error)) echo '<b style="color:#FF3B38">'.$login_error.'</b>'; ?>
										<form enctype="multipart/form-data"  action="admin.php?page=reservation-settings&site=plugins" name="reservation_prem_login" id="reservation_prem_login" method="post" style="text-align:left">
											<p>
												<label for="prem_login"><?php printf ( __( 'Username' , 'easyReservations' ));?></label>
												<input type="text" name="prem_login" id="prem_login" style="float:right;margin-bottom:4px;">
											</p>
											<p>
												<br><label for="prem_pw" style="float:left"><?php printf ( __( 'Password' , 'easyReservations' ));?></label>
												<input type="password" name="prem_pw" id="prem_pw" style="float:right;padding:6px;width:168px">
											</p>
										</form>
										<span style="display:inline-block;text-align:left;margin-top:10px;">
											After login with your premium account you can easily install and update your modules automatically on this page. You'll get informed about new updates and will be able to read changelogs of them.<br>
											For all of these functions the script calls easyreservations.org's API to get the informations, but isn't collecting any datas or personal informations.
										</span>
									<?php } else { ?>
										<p><a href="admin.php?page=reservation-settings&site=plugins&install=all" class="easySubmitButton-secondary"><?php echo __( 'Install all modules' , 'easyReservations' ); ?></a></p>
										<p>
											<form action="<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/core/activate.php" method="post" style="display:inline-block;">
												<input type="hidden" name="activate_all" value="bla">
												<a href="#" onclick="this.parentNode.submit();" class="easySubmitButton-secondary"><?php echo __( 'Activate All' , 'easyReservations' ); ?></a>
											</form>
											<form action="<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/core/activate.php" method="post" style="display:inline-block;">
												<input type="hidden" name="deactivate_all" value="bla">
												<a href="#" onclick="this.parentNode.submit();" class="easySubmitButton-secondary"><?php echo __( 'Deactivate All' , 'easyReservations' ); ?></a>
											</form>
										</p>
										<p><a href="admin.php?page=reservation-settings&site=plugins&check" class="easySubmitButton-secondary"><?php echo __( 'Check for updates' , 'easyReservations' ); ?></a></p>
										<p><a href="admin.php?page=reservation-settings&site=plugins&logout" class="easySubmitButton-secondary"><?php echo __( 'Turn of update notifier' , 'easyReservations' ); ?></a></p>
										<p><?php echo __( 'Last check' , 'easyReservations' );?>: <?php echo date(RESERVATIONS_DATE_FORMAT." H:i", (int) get_option( 'easyreservations-notifier-last-updated')); ?></p>
									<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="width:200px;margin-top:5px">
						<thead>
							<tr>
								<th><?php echo __( 'Install or Update Module manually' , 'easyReservations' );?></th>
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
					<style>
						#changlog_td { border-top:0px;}
						#changlog_td ul {
							line-height: 1.2em;
							list-style: disc !important;
							padding-left: 30px;
						} #changlog_td li {
							list-style-type: circle;
						}
					</style>
				<?php if(!$login){ ?>
						<script>
							is_changelog = false;

							function get_changelog(module){
								if(is_changelog){
									jQuery('#changlog_tr').remove();
									if(module == is_changelog){
										jQuery('.module_row_'+is_changelog+' td').css('border-bottom', '0px');
										is_changelog = false;
										return true;
									}
									is_changelog = false;
								}
								is_changelog = module;
								jQuery('.module_row_'+module+' td').css('border-bottom', '0px');
								jQuery('<tr id="changlog_tr"><td></td><td colspan="5" id="changlog_td"><img src="<?php echo RESERVATIONS_URL; ?>images/loading.gif"></td></tr>').insertAfter('.module_row_'+module);
								var req = jQuery.ajax({
									url: 'admin.php?page=reservation-settings&site=plugins&changelog='+module,
									success: function(data){
										changelog = jQuery(data).find('#the_modules_changelog').html();
										is_changelog = module;
										jQuery('#changlog_td').html(changelog);
									}
								});
								req.error(function(error,textStatus, errorThrown) {
									jQuery('.module_row_'+module+' td').css('border-bottom', '1px');
									jQuery('#changlog_tr').remove();
									is_changelog = false;
									alert(errorThrown);
								});
							}
						</script>
				<?php
				}
			}
		}

		function easyreservations_update_notifier_menu(){
			$changes = easyreservations_latest_modules_versions(86400);
			if($changes){
				$message = '';
				foreach($changes as $key => $module){
					$message .= ' <a target="_blank" href="http://www.easyreservations.org/module/'.$key.'/">'.$module[0].'</a> v'.$module[1].',';
				}
				if(count($changes) > 1) $update_message = __('New Updates are available:', 'easyResrvations').substr($message, 0, -1);
				else $update_message = __('New Update is available:', 'easyResrvations').substr($message, 0, -1);
				echo '<div class="update-nag">'.$update_message.'! Please update now.</div>';
				return true;
			}
			return false;
		}

		add_action('admin_notices', 'easyreservations_update_notifier_menu');

		function easyreservations_latest_modules_versions($interval, $modules = false, $onload = false, $update = false, $changelog = false){
			if(!$modules) $modules = easyreservations_load_modules_array();
			$login = get_option('reservations_login');
			$error = '';
			if($login !== false && !empty($login)){
				$notifier_file_url = 'http://easyreservations.org/req/modules/';
				$db_cache_field = 'easyreservations-notifier-cache';
				$db_cache_field_last_updated = 'easyreservations-notifier-last-updated';
				$last = get_option( $db_cache_field_last_updated );
				if($update || !$last || (( time() - $last ) > $interval)){
					$explode= explode('$%!$&', $login);
					if( function_exists('curl_init')){ // if cURL is available, use it...
						if($update){
							$notifier_file_url =  'http://easyreservations.org/req/down/'.$update;
						} elseif($changelog){
							$notifier_file_url =  'http://easyreservations.org/req/change/'.$changelog;
						}
						$ch = curl_init($notifier_file_url);
						curl_setopt($ch, CURLOPT_URL, $notifier_file_url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Accept: application/json', 'Content-Type: json'));
						curl_setopt($ch, CURLOPT_TIMEOUT, 10);
						curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ); //CURLAUTH_DIGEST
						curl_setopt($ch, CURLOPT_USERPWD, $explode[0]. ':' .$explode[1]);
						$cache = curl_exec($ch);
						$responseInfo	= curl_getinfo($ch);
						if($responseInfo['http_code'] == "401") $error = __('Wrong login data', 'easyReservations');
						elseif($responseInfo['http_code'] == "401") $error = sprintf(__('No premium account - %s','easyReservations'), '<a target="_blank" href="http://easyreservations.org/remium/">order here</a>');
					} else {
						$error = __('cURL isnt installed on your server, please contact your host', 'easyReservations');
					}
					if($update && empty($error)){
						$newfile = WP_PLUGIN_DIR.'/easyreservations/tmp_file_dasd.zip';
						$url = 'admin.php?page=reservation-settings&site=plugins&update='.$update;
						if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false))){
							$error = 'creds';
						} elseif ( ! WP_Filesystem($creds) ) {
							request_filesystem_credentials($url, 'ftp', true, false);
							$error = 'creds';
						} else {			
							if(!$das = file_put_contents($newfile, $cache)) return 808;
							if(class_exists('ZipArchive')){
								$zip = new ZipArchive();  
								$x = $zip->open($newfile);
								if($x === true){
									$zip->extractTo(WP_PLUGIN_DIR.'/easyreservations/');
									$zip->close();
									unlink($newfile);
									return true;
								} else {
									$zip = unzip_file($newfile, WP_PLUGIN_DIR.'/easyreservations/');
									unlink($newfile);
									return $zip;
								}
							} else {
								$zip = unzip_file($newfile, WP_PLUGIN_DIR.'/easyreservations/');
								unlink($newfile);
								return $zip;
							}
							return false;
						}
					} elseif($changelog){
						return $cache;
					} elseif ($cache){
						update_option( $db_cache_field, $cache );
						update_option( $db_cache_field_last_updated, time());
					}
			}
			if($onload && !empty($error)) return $error;
			elseif(!$onload && !empty($error)) return false;
			$notifier_data = get_option( $db_cache_field );

			if($notifier_data){
				$xml = json_decode($notifier_data);
				$changes = '';
				if(!empty($xml)){
					foreach($xml as $module){
						if($module->name == 'chat') $module->name = 'useredit';
						if(file_exists(WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$module->name.'/'.$module->name.'.php')) {
							$modules[$module->name]['vers'] = $module->version;
							$modules[$module->name]['update'] = $module->update;
							$data = get_plugin_data(WP_PLUGIN_DIR.'/easyreservations/lib/modules/'.$module->name.'/'.$module->name.'.php', false);
							if(version_compare($data['Version'], $module->version) == -1){
								$modules[$module->name]['updated'] = $module->update;
								$changes[$module->name] = array($module->name, $module->version);
							}
						}
					}
				}

				if($onload){
					return $modules;
				} else {
					if(!empty($changes)){
						return $changes;
					} else return false;
				}
			} else return false;
		}
		return false;
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