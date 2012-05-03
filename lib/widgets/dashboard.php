<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


	/**
	*	Construct dashboard widget 
	*/
	 
	function easyreservations_dashboard_widget_function() {
		echo '<style>#easyreservations_dashboard_widget .inside { margin:0px; padding:0px; } #er-dash-table thead th { background:#EAEAEA;border-top:1px solid #ccc;border-bottom:1px solid #ccc; padding:3px !important; } #er-dash-table tbody tr:nth-child(odd) { background:#fff } #er-dash-table tbody td { font-weight:normal !important; padding:3px !important; }</style>';?>
		<script>
			function navibackground(a){
				var e = document.getElementsByName('sendajax'); 
				for(var i=0;i<e.length;i++){ e[i].style.color = '#21759B';e[i].style.fontWeight='normal';} 
				a.style.color='#000';
				a.style.fontWeight='bold';
			}
		</script>
		<div id="er-dash-navi" style="width:100%;padding:4px;">
			<a id="current" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Current</a> | 
			<a id="leaving" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Leaving today</a> | 
			<a id="arrival" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Arrival today</a> | 
			<a id="pending" name="sendajax" style="cursor:pointer;font-size:12px;" onclick="navibackground(this)">Pending</a> | 
			<a id="future" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Future</a>
			<span id="er-loading" style="float:right;"></span>
		</div>
		<div id="easy-dashboard-div"></div><?php
	}

	function easyreservations_add_dashboard_widgets() {
		wp_add_dashboard_widget('easyreservations_dashboard_widget', 'easyReservations Dashboard Widget', 'easyreservations_dashboard_widget_function');	
	}

	/* *
	*	Dashboards ajax request
	*/

	function easyreservations_send_dashboard(){
		$nonce = wp_create_nonce( 'easy-dashboard' );
		?><script type="text/javascript" >	
		jQuery(document).ready(function(jQuery) {
			jQuery('a[name|="sendajax"]').click(function() {
				var loading = '<img style="margin-right:7px" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading.gif">';
				jQuery("#er-loading").html(loading);
				var data = {
					action: 'easyreservations_send_dashboard',
					security: '<?php echo $nonce; ?>',
					mode: jQuery(this).attr('id')
				};
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					jQuery("#easy-dashboard-div").html(response);
					jQuery("#er-loading").html('');
					return false;
				});
			});
		});		</script><?php
	}

	add_action('admin_head-index.php', 'easyreservations_send_dashboard');

	/* *
	*	Dashboards ajax callback
	*/
	function easyreservations_send_dashboard_callback() {
		global $wpdb; // this is how you get access to the database
		check_ajax_referer( 'easy-dashboard', 'security' );

		$mode =  $_POST['mode'];
		$dateToday = date("Y-m-d", time());
	 
		// response output
		if($mode == "current"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE '$dateToday' BETWEEN arrival AND departure AND approve='yes'"); // Search query
		} elseif($mode == "leaving"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE DATE(arrival) = '$dateToday'  AND approve='yes'"); // Search query 
		} elseif($mode == "pending"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE arrival > NOW() AND approve=''"); // Search query 
		} elseif($mode == "arrival"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE DATE(departure) = '$dateToday' AND approve='yes'"); // Search query 
		} elseif($mode == "future"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE  arrival > NOW() AND approve='yes'"); // Search query 
		}

		$table = '<table id="er-dash-table" style="width:100%;text-align:left;font-weight:normal;border-spacing:0px">';
			$table .= '<thead>';
				$table .= '<tr>';
					$table .= '<th>'.__( 'Name' , 'easyReservations').'</th>';
					$table .= '<th>'.__( 'Date' , 'easyReservations').'</th>';
					$table .= '<th>'.__( 'Resource' , 'easyReservations').'</th>';
					$table .= '<th style="text-align:center">'.__( 'Persons' , 'easyReservations').'</th>';
					$table .= '<th style="text-align:right">'.__( 'Price' , 'easyReservations').'</th>';
				$table .= '</tr>';
			$table .= '</thead>';
			$table .= '<tbody>';

		foreach($query as $num => $res){
			$dateanf = strtotime($res->arrival);
			$dateend = strtotime($res->departure);
			if($num % 2 == 0) $class="odd";
			else $class="even";
				$table .= '<tr class="'.$class.'">';
					$table .= '<td><a href="admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
					$table .= '<td>'.date(RESERVATIONS_DATE_FORMAT, $dateanf).' - '.date(RESERVATIONS_DATE_FORMAT, $dateend).' ('.$res->nights.')</td>';
					$table .= '<td>'.get_the_title($res->room).'</td>';
					$table .= '<td style="text-align:center;">'.$res->number.' ('.$res->childs.')</td>';
					$table .= '<td style="text-align:right">'.easyreservations_get_price($res->id,1).'</td>';
				$table .= '</tr>';
		}

			$table .= '</tbody>';
		$table .= '</table>';
		
		echo $table;

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	add_action('wp_ajax_easyreservations_send_dashboard', 'easyreservations_send_dashboard_callback');

?>
