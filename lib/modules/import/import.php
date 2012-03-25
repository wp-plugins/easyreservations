<?php
/*
Plugin Name: Import Module
Plugin URI: http://www.feryaz.com
Description: Import reservations from .CSV files
Version: 1.0
Author: Feryaz Beer
Author URI: http://www.feryaz.com
License:GPL2
*/

	function easyreservations_generate_import(){ ?>
		<table class="greyfat" cellspacing="0" cellpadding="0" style="width:100%;margin-top:7px">
			<thead>
				<tr>
					<th><?php echo __( 'Import' , 'easyReservations' );?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="background:#fff">
						<i>Import reservations from .XML Backup files.<br><u>Caution</u>:<br>This will add the reservations regardless of current reservations. Double entrys cause problems at the overview, editation and in availability check.</i>
						<form enctype="multipart/form-data" action="<?php echo WP_PLUGIN_URL?>/easyreservations/lib/modules/import/send_import.php" method="post">
							<input type="hidden" value="<?php echo wp_create_nonce('easy-import'); ?>" name="reservation_import_nonce">
							<input type="file" accept="text/*" maxlength="100000" size="35" name="reservation_import_upload_file">
							<input class="easySubmitButton-primary" type="button" style="margin-top:7px;" onclick="this.parentNode.submit(); return false;" value="<?php echo __( 'Import' , 'easyReservations' );?>">
						</form>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
	add_action('er_set_main_side_out', 'easyreservations_generate_import' );

	function easyreservations_generate_import_message(){
		if(isset($_GET['import'])){
			$import = $_GET['import'];
			if($import == 'true'){
				echo '<div class="updated"><p>'.sprintf(__( '%s reservations imported' , 'easyReservations' ), '<b>'.$_GET["count"].'</b>' ).'</p></div>';
			} elseif($import == 'http'){  
				echo '<div class="error"><p>'.__( 'Error in acces server' , 'easyReservations' ).'</p></div>';
			} elseif($import == 'access'){
				echo '<div class="error"><p>'.__( 'Only admins can import reservations' , 'easyReservations' ).'</p></div>';
			} elseif($import == 'file'){
				echo '<div class="error"><p>'.__( 'Wrong file' , 'easyReservations' ).'</p></div>';
			}
		}
	}

	add_action('er_set_save', 'easyreservations_generate_import_message' );

?>