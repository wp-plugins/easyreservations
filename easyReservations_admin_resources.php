<?php
function reservation_resources_page(){ 

if(isset($_GET['delete']) && check_admin_referer( 'easy-resource-delete')){
	wp_delete_post($_GET['delete']);
	$prompt='<div class="updated"><p>'.__( 'Resource deleted' , 'easyReservations' ).'</p></div>';
}
if(isset($_GET['room'])){
	$resourceID=$_GET['room'];
	$site='rooms';
}
if(isset($_POST['thecontent']) && check_admin_referer( 'easy-resource-add', 'easy-resource-add' )){
	$filename  = $_POST['upload_image'];

	// Insert the post into the database
	if(!empty($_POST['thetitle'])){
		$add_roomoroffer = array(
			'post_title' => $_POST['thetitle'],
			'post_content' => $_POST['thecontent'],
			'post_status' => 'private',
			'post_type' => 'easy-rooms'
		);

		$thenewid = wp_insert_post( $add_roomoroffer );
		add_post_meta($thenewid, 'roomcount', '1', TRUE);
		add_post_meta($thenewid, 'reservations_groundprice', 0, TRUE);
		add_post_meta($thenewid, 'easy-resource-interval', 86400, TRUE);

		if($filename != ''){
			$wp_filetype = wp_check_filetype(basename($filename), null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $filename, $thenewid );
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			add_post_meta($thenewid, '_thumbnail_id', $attach_id, TRUE);
		}
		?><meta http-equiv="refresh" content="0; url=admin.php?page=reservation-resources&room=<?php echo $thenewid; ?>"><?php
		$prompt='<div class="updated"><p>'.__( 'Resource added' , 'easyReservations' ).'</p></div>';
	} else $prompt='<div class="error"><p>'.__( 'Please enter a Title' , 'easyReservations' ).'</p></div>';
}

if(isset($_GET['delete_filter']) && check_admin_referer( 'easy-resource-delete-filter' )){
	$filters = get_post_meta($resourceID, 'easy_res_filter', true);
	unset($filters[$_GET['delete_filter']]);
	update_post_meta($resourceID,'easy_res_filter', $filters);
}

if(isset($_POST['filter_form_name_field'])){
	$type = $_POST['filter_type'];
	
	$filter=array();

	if($type == 'price'){
		$imp = $_POST['price_filter_imp'];
		$cond = $_POST['price_filter_cond'];

		$filter['imp'] = $imp;
		$filter['type'] = 'price';
	} elseif($type == 'discount'){
		$typ = $_POST['filter_form_discount_type'];
		$cond = $_POST['filter_form_discount_cond'];
		$modus = $_POST['filter_form_discount_mode'];

		$filter['cond'] = $cond;
		$filter['type'] = $typ;
		$filter['modus'] = $modus;
	} elseif($type == 'unavail'){
		$filter['type'] = 'unavail';
		$cond = $_POST['price_filter_cond'];
	}

	if(isset($_POST['filter_form_name_field']) && $_POST['filter_form_name_field'] != ''){
		$filter['name'] = $_POST['filter_form_name_field'];
	} else $prompt='<div class="error"><p>'.__( 'Enter a name for the filter' , 'easyReservations' ).'</p></div>';

	if($type == 'price' || $type == 'unavail' ){

		if(isset($_POST['price_filter_cond'])){
			if($cond == 'date'){
				$filter['cond'] = 'date';
				if(isset($_POST['price_filter_date'])){
					$date = strtotime($_POST['price_filter_date']) + (((int) $_POST['price_filter_date_h']*60) + (int) $_POST['price_filter_date_m'])*60;
					$filter['date'] = $date;
				} else $prompt='<div class="error"><p>'.__( 'Enter a date' , 'easyReservations' ).'</p></div>';
			} elseif($cond == 'range'){
				$filter['cond'] = 'range';
				if(isset($_POST['price_filter_range_from'])){
					$from = strtotime($_POST['price_filter_range_from']) + (((int) $_POST['price_filter_range_from_h']*60) + (int) $_POST['price_filter_range_from_m'])*60;
					$filter['from'] = $from;
				} else $prompt='<div class="error"><p>'.__( 'Enter a starting date' , 'easyReservations' ).'</p></div>';
				if(isset($_POST['price_filter_range_to'])){
					$to = strtotime($_POST['price_filter_range_to']) + (((int) $_POST['price_filter_range_to_h']*60) + (int) $_POST['price_filter_range_to_m'])*60;
					$filter['to'] = $to;
				} else $prompt='<div class="error"><p>'.__( 'Enter an ending date' , 'easyReservations' ).'</p></div>';
			} else {
				$filter['cond'] = 'unit';

				if(isset($_POST['price_filter_unit_year'])){
					$filter['year'] = implode(',', $_POST['price_filter_unit_year']);
				} else $filter['year'] ='';

				if(isset($_POST['price_filter_unit_quarter'])){
					$filter['quarter'] = implode(',', $_POST['price_filter_unit_quarter']);
				} else $filter['quarter'] ='';

				if(isset($_POST['price_filter_unit_month'])){
					$filter['month'] = implode(',', $_POST['price_filter_unit_month']);
				} else $filter['month'] ='';

				if(isset($_POST['price_filter_unit_cw'])){
					$filter['cw'] = implode(',', $_POST['price_filter_unit_cw']);
				} else $filter['cw'] ='';

				if(isset($_POST['price_filter_unit_days'])){
					$filter['day'] = implode(',', $_POST['price_filter_unit_days']);
				} else $filter['day'] ='';

				if(isset($_POST['price_filter_unit_hour'])){
					$filter['hour'] = implode(',', $_POST['price_filter_unit_hour']);
				} else $filter['hour'] ='';
			}
		} else $prompt='<div class="error"><p>'.__( 'Select a condition' , 'easyReservations' ).'</p></div>';
	}

	if($type == 'price' || $type == 'discount'){
		if(isset($_POST['filter-price-field'])){
			$filter['price'] = $_POST['filter-price-field'];
		}
	}

	$filters = get_post_meta($resourceID, 'easy_res_filter', true);
	if(!isset($filters) || empty($filters) || $filters == false) $filters = array();
	
	if(isset($_POST['price_filter_edit'])){
		unset($filters[$_POST['price_filter_edit']]);
		$filters[] = $filter;
	} else {
		$filters[] = $filter;
	}

    foreach($filters as $key => $filter) {
		if($filter['type'] == 'price'){
			$pfilters[] = $filter;
			$psortArray[$key] = $filter['imp'];
		} elseif($filter['type'] == 'unavail'){
			$ufilters[] = $filter;
			$ufiltersSort[] = $filter['cond'];
		} else {
			$dfilters[] = $filter;
			$dsortArray[$key] = $filter['cond'];
			$dtsortArray[$key] = $filter['type'];
		}
    }

    if(isset($psortArray)) array_multisort($psortArray, SORT_ASC, SORT_NUMERIC, $pfilters); 
	if(isset($dtsortArray)) array_multisort($dtsortArray, SORT_ASC, $dsortArray, SORT_DESC, SORT_NUMERIC, $dfilters); 
	if(isset($ufiltersSort)) array_multisort($ufiltersSort, SORT_ASC, $ufilters); 

	if(!isset($pfilters)) $pfilters = array();
	if(!isset($ufilters)) $ufilters = array();
	if(!isset($dfilters)) $dfilters = array();

	$filters = array_merge_recursive($pfilters, $dfilters, $ufilters);
	if(!isset($prompt)) update_post_meta($resourceID, 'easy_res_filter', $filters);
}

if(isset($_GET['addresource'])){
	$addresource=$_GET['addresource'];
	$site='addresource';
}

if(isset($_GET['site'])){
	$site=$_GET['site'];
}

$offers = get_posts(array('post_type' => 'easy-offers'));
$offerlink = '';
if(isset($offers[0])) $offerlink = '<a class="add-new-h2" href="edit.php?post_type=easy-rooms">View old offers</a>';
?>
<h2>
	<?php echo __( 'Reservations Resources' , 'easyReservations' );?><a class="add-new-h2" href="admin.php?page=reservation-resources&addresource=room"><?php echo __( 'Add New' , 'easyReservations' );?></a><a class="add-new-h2" href="edit.php?post_type=easy-rooms"><?php echo __( 'Post View' , 'easyReservations' );?></a><?php echo $offerlink; ?>
</h2>
<?php
if(!isset($site) || $site=='' || $site =='main'){
	global $wpdb;

	if(isset($prompt)) echo $prompt; ?>

			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-bottom:5px;">
				<thead>
					<tr>
						<?php if(function_exists('get_the_post_thumbnail')){ ?><th></th><?php } ?>
						<th nowrap><?php echo __( 'Title' , 'easyReservations' );?></th>
						<th nowrap><?php echo __( 'ID' , 'easyReservations' );?></th>
						<th style="text-align:center;" nowrap><?php echo __( 'Quantity' , 'easyReservations' ); ?></th>
						<th style="text-align:right" nowrap><?php echo __( 'Base Price' , 'easyReservations' ); ?></th>
						<th nowrap><?php echo __( 'Reservations' , 'easyReservations' ); ?></th>
						<th style="text-align:center;" nowrap><?php echo __( 'Filter' , 'easyReservations' ); ?></th>
						<th nowrap><?php echo __( 'Status' , 'easyReservations' ); ?></th>
						<th nowrap><?php echo __( 'Excerpt' , 'easyReservations' ); ?></th>
						<th nowrap></th>
					</tr>
				</thead>
				<tbody><?php
			$allrooms = easyreservations_get_rooms(1,1);
			$countresource=0;

			foreach( $allrooms as $allroom ){
				$countresource++;
				if($countresource%2==0) $class="alternate"; else $class="";
				$getfilters = get_post_meta($allroom->ID, 'easy_res_filter', true);
				$meta_gp = get_post_meta($allroom->ID, 'reservations_groundprice', true);

				$checkAvail = easyreservations_check_avail($allroom->ID, time());
				$theRoomCount = get_post_meta($allroom->ID, 'roomcount', true);
				if($checkAvail >=  $theRoomCount) $status='Full ('.$checkAvail.'/'.$theRoomCount.')'; 
				else $status='Empty ('.$checkAvail.'/'.$theRoomCount.')'; 
				$countallrooms = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room=%d ", $allroom->ID));	?>
				<tr class="<?php echo $class; ?>"><?php
					if(function_exists('get_the_post_thumbnail')){ 
						if(($img=get_the_post_thumbnail($allroom->ID, array(25,25))) != '' ){ ?>
							<td style="text-align:left; vertical-align:middle;max-width:25px;width:25px;"><a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><?php echo $img; ?></a></td><?php
						} else echo '<td></td>';
					}?>
					<td><a href="admin.php?page=reservation-resources&room=<?php echo $allroom->ID;?>" title="<?php echo __( 'edit ' , 'easyReservations' ).' '.$allroom->post_title; ?>"><?php echo '<b>'.__($allroom->post_title).'</b>'; ?></a></td>
					<td style="text-align:center"><?php echo '<b>'.$allroom->ID.'</b>'; ?></td>
					<td style="text-align:center;"><?php echo $theRoomCount; ?></td>
					<td style="text-align:right;width:100px" nowrap><?php echo reservations_format_money($meta_gp, 1);?></td>
					<td style="text-align:center;width:85px" nowrap><?php echo $countallrooms; ?></td>
					<td style="text-align:center" nowrap><?php if(empty($getfilters)) echo 0; else echo count($getfilters); ?></td>
					<td nowrap><?php echo $status; ?></td>
					<td><?php echo substr($allroom->post_content, 0, 36); ?></td>
					<td style="text-align:right;width:100px">
						<a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit post' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a>
						<a href="admin.php?page=reservation-resources&room=<?php echo $allroom->ID;?>" title="<?php echo __( 'edit resource' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"></a>
						<a href="<?php echo get_permalink( $allroom->ID ); ?>" target="_blank" title="<?php echo __( 'view post' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/page_white_link.png"></a>
						<a href="<?php echo wp_nonce_url('admin.php?page=reservation-resources&delete='.($allroom->ID).'', 'easy-resource-delete'); ?>" title="<?php echo __( 'trash' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/trash.png"></a>
					</td>
				</tr><?php
			}

		echo '</tbody>';
		echo '</table>';

	} elseif($site=='rooms'){
		wp_enqueue_style('datestyle');
		$get_role = get_post_meta($resourceID, 'easy-resource-permission', true);
		if(!empty($get_role) && !current_user_can($get_role)) die('You havnt the rights to view this resource');
		$right = '';
		$error = '';

		if(isset($_POST['action'])) {
			$action=$_POST['action'];
		} else $action = "";

		$allrooms = get_post($resourceID);

		if(isset($_POST['room_names'])){
			update_post_meta($resourceID, 'easy-resource-roomnames', $_POST['room_names']);
			$right.=__( 'Resource\'s count names changed' , 'easyReservations' ).', ';
		} elseif($action=='set_groundprice'){ /* SET GROUND PRICE */

			$reservations_current_req = get_post_meta($resourceID, 'easy-resource-req', TRUE);
			$req = array( 'nights-min' => $_POST['easy-resource-min-nights'], 'nights-max' => $_POST['easy-resource-max-nights'], 'pers-min' => $_POST['easy-resource-min-pers'], 'pers-max' => $_POST['easy-resource-max-pers'] );
			if($reservations_current_req != $req){
				update_post_meta($resourceID, 'easy-resource-req', $req);
				$right.=__( 'Requirements edited' , 'easyReservations' ).', ';
			}

			$gpricepost=easyreservations_check_price($_POST['groundprice']);
			$reservations_current_groundprice = get_post_meta($resourceID, 'reservations_groundprice', TRUE);
			if($reservations_current_groundprice !== $gpricepost){
				if($gpricepost != 'error'){
					update_post_meta($resourceID,'reservations_groundprice', $gpricepost);
					$right.=__( 'Groundprice edited' , 'easyReservations' ).', ';
				} else $error.=__( 'Insert right money format' , 'easyReservations' ).', ';
			}

			$reservations_room_count=$_POST['roomcount'];
			$reservations_current_roomcount = get_post_meta($resourceID, 'roomcount', TRUE);
			if($reservations_current_roomcount != $reservations_room_count){
				if(is_numeric($_POST['roomcount'])){
					update_post_meta($resourceID,'roomcount',$reservations_room_count);
					$right.=__( 'Count of resource space edited' , 'easyReservations' ).', ';
				} else $error.='Count of resource space has to be a number, ';
			}

			$reservations_current_price_set = get_post_meta($resourceID, 'easy-resource-price', TRUE);
			if(isset($_POST['easy-resource-price'])) $reservations_res_price_set = $_POST['easy-resource-price'];
			else $reservations_res_price_set = 0;
			if($reservations_current_price_set != $reservations_res_price_set){/* SET PRICE SETTINGS */
				if(is_numeric($reservations_res_price_set)){
					update_post_meta($resourceID, 'easy-resource-price', $reservations_res_price_set);
					$right.=__( 'Price setting changed' , 'easyReservations' ).', ';
				} else $error.='Price setting has to be a number, ';
			}

			$reservations_current_perm = get_post_meta($resourceID, 'easy-resource-permission', TRUE);
			$reservations_res_perm =$_POST['easy-resource-permission'];
			if($reservations_current_perm != $reservations_res_perm){/* SET PRICE SETTINGS */
				if(current_user_can('manage_options')){
					update_post_meta($resourceID, 'easy-resource-permission', $reservations_res_perm);
					$right.=__( 'Resource permission edited' , 'easyReservations' ).', ';
				} else $error.=__( 'Only admins can change the permissions for' , 'easyReservations' ).' '.__( 'resource' , 'easyReservations' ).' , ';
			}

			$reservations_current_int = get_post_meta($resourceID, 'easy-resource-interval', TRUE);
			$reservations_res_interval =$_POST['easy-resource-interval'];
			if($reservations_current_int != $reservations_res_interval){/* SET PRICE SETTINGS */
				update_post_meta($resourceID, 'easy-resource-interval', $reservations_res_interval);
				$right.=__( 'Resource interval edited' , 'easyReservations' ).', ';
			}

			$cpricepost=easyreservations_check_price($_POST['child_price']);
			$reservations_current_childprice = get_post_meta($resourceID, 'reservations_child_price', TRUE);
			if($reservations_current_childprice != $cpricepost){
				if($cpricepost != 'error'){
					update_post_meta($resourceID,'reservations_child_price', $cpricepost);
					$right.=__( 'Children\'s discount edited' , 'easyReservations' ).', ';
				} else $error.=__( 'Insert right money format' , 'easyReservations' ).', ';
			}

			if(isset($_POST['res_tax_names']) && !empty($_POST['res_tax_names']) && isset($_POST['res_tax_amounts']) && !empty($_POST['res_tax_amounts'])){
				$array = array_merge($_POST['res_tax_names'], $_POST['res_tax_amounts']);
				$taxes = '';
				foreach($_POST['res_tax_names'] as $key => $tax){
					if(is_numeric($_POST['res_tax_amounts'][$key])){
						$taxes[] = array($_POST['res_tax_names'][$key],$_POST['res_tax_amounts'][$key]);
					} else {
						$error.=__( 'Tax percentage has to be numeric' , 'easyReservations' ).', ';
						$taxes = 'error';
						break;
					}
				}
				if(!empty($taxes)  && $taxes != 'error') update_post_meta($resourceID, 'easy-resource-taxes', $taxes);
				elseif($taxes != 'error') update_post_meta($resourceID, 'easy-resource-taxes', array());
			}
			do_action('er_res_main_save', $resourceID);
		}

		$counroooms=0;

		$gp = get_post_meta($resourceID, 'reservations_groundprice', true);
		$reservations_current_room_count = get_post_meta($resourceID, 'roomcount', TRUE);
		$reservations_current_room_names = get_post_meta($resourceID, 'easy-resource-roomnames', TRUE);
		$reservations_current_child_price = get_post_meta($resourceID, 'reservations_child_price', TRUE);
		$reservations_current_price_set = get_post_meta($resourceID, 'easy-resource-price', TRUE);
		$reservations_current_tax = get_post_meta($resourceID, 'easy-resource-taxes', TRUE);
		$reservations_current_int = get_post_meta($resourceID, 'easy-resource-interval', TRUE);
		$reservations_current_req = get_post_meta($resourceID, 'easy-resource-req', TRUE);
		if(!$reservations_current_req || !is_array($reservations_current_req)){
			$reservations_current_req = array('nights-min' => 1, 'nights-max' => 30, 'pers-min' => 1, 'pers-max' => 0);
		}

		if(!empty($error)) echo '<div class="error"><p>'.substr($error,0,-2).'</p></div>';
		if(!empty($right)) echo '<div class="updated"><p>'.substr($right,0,-2).'</p></div>';
  ?><table style="width:99%">
			<tr>
				<td valign="top" style="width:64%">
					<table class="<?php echo RESERVATIONS_STYLE; ?>">
							<thead>
								<tr>
									<th colspan="2"><?php echo __(get_the_title($resourceID)); ?><div style="float:right"><a href="post.php?post=<?php echo $resourceID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a></div></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="width:90px;" valign="top"><?php if(function_exists('get_the_post_thumbnail')){ $pic = get_the_post_thumbnail($resourceID, array(90,90)); if(!empty($pic)) echo $pic.'<br>'; } ?><?php echo __( 'Status' , 'easyReservations' ).': <b>'; echo __($allrooms->post_status).'</b><br>'; echo __( 'Comments' , 'easyReservations' ).': <b>'; echo __($allrooms->comment_count).'</b>'; ?></td>
									<td style="background:#fff;border-left:1px solid #BABABA;vertical-align: top;"><?php echo htmlentities(__($allrooms->post_content)); ?></td>
								</tr>
							</tbody>
					</table>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
					<thead>
						<tr>
							<th><?php echo __( 'Filter' , 'easyReservations' ); ?></th>
							<th style="text-align:center;"><?php echo __( 'Priority' , 'easyReservations' ); ?></th>
							<th><?php echo __( 'Time' , 'easyReservations' ); ?></th>
							<th><?php echo __( 'Price' , 'easyReservations' ); ?></th>
							<th> </th>
						</tr>
					</thead>
					<tbody id="sortable">
					<script>var filter = new Array();</script>
					<?php
							$theFilters = get_post_meta($resourceID, 'easy_res_filter', true);
							if(!empty($theFilters) && is_array($theFilters)) $count_all_filters=count($theFilters); else $count_all_filters=0; // count the filter-array element
							$numberoffilter = 0;
							if($count_all_filters > 0){
								foreach($theFilters as $nummer => $filter){ //foreach filter array
									if($filter['type'] == 'price') {
										$numberoffilter++; //count filters
										if($numberoffilter%2==0) $class="alternate"; else $class=""; ?>
										<tr class="<?php echo $class; ?>">
											<script>filter[<?php echo $nummer; ?>] = new Object(); filter[<?php echo $nummer; ?>] = <?php echo json_encode($filter); ?>;</script>
											<td class="resourceType"><code  style="color:#30B24A;font-weight:bold;display:inline-block">[Time]</code> <?php echo $filter['name']; ?></td>
											<td style="vertical-align:middle;text-align:center;width:40px"><?php echo $filter['imp']; ?></td>
											<td>
												<?php echo easyreservations_get_price_filter_description($filter, $resourceID, 1); ?>
											</td>
												<td>
													<?php if(isset($filter['price']) && $filter['price'] > 0){ ?>
														<?php echo reservations_format_money($filter['price'], 1); ?>
													<?php } else { ?>
														<?php echo reservations_format_money('0', 1); ?>
													<?php } ?>
												</td>
											<td style="vertical-align:middle;text-align:center">
												<a href="javascript:filter_edit(<?php echo $nummer; ?>);"><img style="vertical-align:text-middle;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/edit.png'; ?>"></a>
												<a href="<?php echo wp_nonce_url('admin.php?page=reservation-resources&room='.$resourceID.'&delete_filter='.$nummer, 'easy-resource-delete-filter'); ?>"><img style="vertical-align:text-middle;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/delete.png'; ?>"></a>
											</td>
										</tr>
										<?php
										unset($theFilters[$nummer]);
									}
								}
							}
							if($numberoffilter == 0)  echo '<td colspan="5">'.__( 'No price filter set' , 'easyReservations' ).'</td>';
							?>
							</tbody>
							<thead>
								<tr class="tmiddle">
									<th class="tmiddle"><?php echo __( 'Filter' , 'easyReservations' ); ?></td>
									<th class="tmiddle" colspan="2"><?php echo __( 'Condition' , 'easyReservations' ); ?></td>
									<th class="tmiddle"><?php echo __( 'Price' , 'easyReservations' ); ?></td>
									<th class="tmiddle"></td>
								</tr>
							</thead>
							<tbody>
							<?php
							$numberoffilter2 = 0;
							if(!empty($theFilters)) $countfilter=count($theFilters); else $countfilter=0; // count the filter-array element
							if($countfilter > 0){
								foreach($theFilters as $nummer => $filter){ //foreach filter array
									$numberoffilter++; //count filters
									$numberoffilter2++;
									if($numberoffilter%2==0) $class="alternate"; else $class="";

									if($filter['type']=="stay"){
										$bgcolor='#1CA0E1';
										$condition_string = sprintf(__('If guest stays %s days or more the price changes by','easyReservations'), '<b>'.$filter['cond'].'</b>');
									} elseif($filter['type'] =="unavail"){
										$bgcolor='#D8211E';
										$condition_string =str_replace(__("calculate", 'easyReservations'), __("check", 'easyReservations'),substr(easyreservations_get_price_filter_description($filter, $resourceID, 0),0,-42)).' '.__('resource is unavailable','easyReservations');
									} elseif($filter['type'] =="pers"){
										$bgcolor='#3059C1';
										$condition_string = sprintf(__('If %s or more persons reservating the price changes by','easyReservations'), '<b>'.$filter['cond'].'</b>');
									} elseif($filter['type'] =="loyal"){
										$bgcolor='#A823A8';
										if($filter['cond'] == 1) $end = 'st';
										elseif($filter['cond'] == 2) $end = 'nd';
										elseif($filter['cond'] == 3) $end = 'rd';
										else $end = 'th';
										$condition_string = sprintf(__('If guest comes the %1$s%2$s time the price changes by','easyReservations'), '<b>'.$filter['cond'].'</b>', $end);
									} elseif($filter['type']=="early"){
										$bgcolor='#F4AA33';
										$condition_string = sprintf(__('If the guest reservates %s days before his arrival the price changes by','easyReservations'), '<b>'.$filter['cond'].'</b>');
									} ?>
									<tr class="<?php echo $class; ?>" name="notsort">
										<script>filter[<?php echo $nummer; ?>] = new Object(); filter[<?php echo $nummer; ?>] = <?php echo json_encode($filter); ?>;</script>
										<td class="resourceType"><code  style="color:<?php echo $bgcolor; ?>;font-weight:bold;display:inline-block">[<?php echo $filter['type']; ?>]</code> <?php echo $filter['name']; ?></td>
										<td colspan="<?php if($filter['type'] == "unavail") echo 3; else echo 2; ?>"><?php echo $condition_string; ?></td>
										<?php if($filter['type'] != "unavail") { ?>
											<td>
											<?php if(isset($filter['price'])){ ?>

													<?php if($filter['modus']=='%') echo $filter['price'].' %';
													elseif($filter['modus']=='price_res') echo reservations_format_money($filter['price'], 1).'/'.__('Reservation','easyReservations');
													elseif($filter['modus']=='price_day') echo reservations_format_money($filter['price'], 1).'/'.__('Day','easyReservations');
													elseif($filter['modus']=='price_pers') echo reservations_format_money($filter['price'], 1).'/'.__('Person','easyReservations');
													else echo reservations_format_money($filter['price'], 1); ?>
												<?php } else { ?>
													<?php echo reservations_format_money('0', 1); ?>
												<?php } ?>
											</td>
										<?php } ?>
										<td style="vertical-align:middle;text-align:center">
											<a href="javascript:filter_edit(<?php echo $nummer; ?>);"><img style="vertical-align:text-middle;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/edit.png'; ?>"></a>
											<a href="<?php echo wp_nonce_url('admin.php?page=reservation-resources&room='.$resourceID.'&delete_filter='.$nummer, 'easy-resource-delete-filter'); ?>"><img style="vertical-align:text-middle;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/delete.png'; ?>"></a>
										</td>
									</tr>
								<?php
								}
							} else echo '<tr><td colspan="5">'.__( 'No filter set' , 'easyReservations' ).'</td></tr>';  ?>
						</tbody>
					</table>
					<div id="showCalender" style="margin:6px 6px 6px 0;float:left"></div>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px;width:auto;">
						<thead>
							<tr>
								<th><?php echo __( 'Price simulator' , 'easyReservations' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<form name="CalendarFormular" id="CalendarFormular">
										<input type="hidden" name="room" onChange="easyreservations_send_calendar()" value="<?php echo $resourceID; ?>">
										<b><?php echo __( 'Persons' , 'easyReservations' ); ?></b>: <select name="persons" onChange="easyreservations_send_calendar()" style="margin-top:5px;width:80px;"><?php echo easyReservations_num_options(1, 10); ?></select> 
										<b><?php echo __( 'Childs' , 'easyReservations' ); ?></b>: <select name="childs" onChange="easyreservations_send_calendar()" style="margin-top:5px;width:80px;"><?php echo easyReservations_num_options(0, 10); ?></select><br>
										<b><?php echo sprintf("Reserved %s days ago", '</b><select name="reservated" onChange="easyreservations_send_calendar()" style="margin-top:5px;">'.easyReservations_num_options(0, 150).'</select><b>');  ?></b>
										<input type="hidden" name="date" onChange="easyreservations_send_calendar()" value="0">
										<input type="hidden" name="size" value="350,4">
									</form>
								</td>
							</tr>
						</tbody>
					</table>
					<script>easyreservations_send_calendar();</script>
				</td>
				<td></td>
				<td style="width:35%" valign="top">
					<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_groundprice" name="set_groundprice">
					<input type="hidden" name="action" id="action" value="set_groundprice"><input type="hidden" name="countrooms" id="countrooms" value="<?php echo $counroooms; ?>">
					<table class="<?php echo RESERVATIONS_STYLE; ?>">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Resource settings' , 'easyReservations' ));?>	<input type="button" style="float:right;" onclick="document.getElementById('set_groundprice').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
							<tr class="alternate">
								<td><b style="<?php if(!is_numeric($reservations_current_room_count) || $reservations_current_room_count < 1)  echo 'color:#F20909;'; ?>"><?php printf ( __( 'Count of resource' , 'easyReservations' ));?>:</b></td>
								<td style="text-align:right;"><input type="text" name="roomcount" style="width:30px;margin: 2px;<?php if(!is_numeric($reservations_current_room_count) || $reservations_current_room_count < 1)  echo 'border-color:#F20909;'; ?>" value="<?php echo $reservations_current_room_count; ?>" style=""></td>
							</tr>
							<tr>
								<td><b><?php echo  __( 'Base price' , 'easyReservations' ); ?>:</b></td>
								<td style="text-align:right;"><input type="text" value="<?php echo $gp; ?>" style="width:60px;text-align:right" name="groundprice"> &<?php echo RESERVATIONS_CURRENCY; ?>;</td>
							</tr>
							<tr class="alternate">
								<td><b><?php printf ( __( 'Child discount' , 'easyReservations' ));?>:</b></td>
								<td style="text-align:right;"><input type="text" name="child_price" style="width:60px;text-align:right" value="<?php echo $reservations_current_child_price; ?>" style="margin: 2px;"> <?php echo '&'.RESERVATIONS_CURRENCY.';<br>'; ?></td>
							</tr>
							<tr>
								<td><b><?php printf ( __( 'Price' , 'easyReservations' ));?>:</b></td>
								<td style="text-align:right">
									<select name="easy-resource-interval" style="margin-bottom:3px;">
										<option value="3600" <?php selected($reservations_current_int, 3600); ?>><?php  echo __( 'Hourly billing' , 'easyReservations' );?></option>
										<option value="86400" <?php selected($reservations_current_int, 86400); ?>><?php  echo __( 'Daily billing' , 'easyReservations' );?></option>
										<option value="604800" <?php selected($reservations_current_int, 604800); ?>><?php  echo __( 'Weekly billing' , 'easyReservations' );?></option>
									</select><br>
									<?php echo  __( 'Price per person' , 'easyReservations' );?> <input type="checkbox" name="easy-resource-price" value="1" <?php checked($reservations_current_price_set, 1); ?>>
								</td>
							</tr>
							<tr class="alternate">
								<td style="vertical-align:top;padding-top:7px;"><b><?php printf ( __( 'Taxes' , 'easyReservations' ));?></b> <a onClick="easy_add_tax(1, this)" style="cursor: pointer">Add</a></td>
								<td style="text-align:right">&nbsp;<?php if($reservations_current_tax && !empty($reservations_current_tax)){ $nr = 0;
									foreach($reservations_current_tax as $tax){
										echo '<span><img style="vertical-align:text-middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png" onClick="easy_add_tax(2, this);"> ';
										echo '<input type="text" name="res_tax_names[]" value="'.$tax[0].'" style="width:150px;margin-bottom:3px">';
										echo '<input type="text" name="res_tax_amounts[]" value="'.$tax[1].'" style="width:30px;margin-bottom:3px">%<br></span>';
										$nr++;
									}
								} ?><a class="placeholder"></a></td>
							<tr>
								<td><b><?php	echo ucfirst(easyreservations_interval_infos($reservations_current_int,0,2));?>:</b></td>
								<td style="text-align:right">
									<?php echo __( 'Min' , 'easyReservations');?>: <select name="easy-resource-min-nights"><?php echo easyReservations_num_options(1, 99, $reservations_current_req['nights-min']); ?></select><br><?php echo  __( 'Max' , 'easyReservations' );?>: <select name="easy-resource-max-nights"><option value="0" <?php echo selected($reservations_current_req['nights-max'], 0); ?>>&infin;</option><?php echo easyReservations_num_options(1,99, $reservations_current_req['nights-max']); ?></select>
								</td>	
							</tr>
							<tr class="alternate">
								<td><b><?php printf ( __( 'Persons' , 'easyReservations' ));?>:</b></td>
								<td style="text-align:right">
									<?php	 echo  __( 'Min' , 'easyReservations' );?>: <select name="easy-resource-min-pers"><?php echo easyReservations_num_options(1, 99, $reservations_current_req['pers-min']); ?></select><br><?php echo __( 'Max' , 'easyReservations' );?>: <select name="easy-resource-max-pers"><option value="0" <?php echo selected($reservations_current_req['pers-max'], 0); ?>>&infin;</option><?php echo easyReservations_num_options(1,99,$reservations_current_req['pers-max']); ?></select>
								</td>
							</tr>
							<tr>
								<td><b><?php echo __( 'Required  permission' , 'easyReservations' );?>:</b></td>
								<td style="text-align:right">
									<select name="easy-resource-permission"><?php echo easyreservations_get_roles_options(get_post_meta($resourceID, 'easy-resource-permission', true));?></select>
								</td>
							</tr>
							<?php do_action('er_add_res_main_table_row', $resourceID); ?>
						</tbody>
					</table>
					</form>
					<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_roomsnames" name="set_roomsnames">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Resources Numbers/Names' , 'easyReservations' ));?><input type="button" style="float:right;" onclick="document.getElementById('set_roomsnames').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
							<?php for($i=0; $i < $reservations_current_room_count; $i++){
									if(isset($reservations_current_room_names[$i])  && !empty($reservations_current_room_names[$i])) $name = $reservations_current_room_names[$i];
									else $name = $i+1;
									if($i%2==0) $class=""; else $class="alternate";?>
								<tr class="<?php echo $class; ?>">
									<td> #<?php echo $i+1; ?></td>
									<td style="text-align:right;width:70%"><input type="text" name="room_names[]" value="<?php echo $name; ?>" style="width:99%"></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					</form>
					<form method="post" id="filter_form" name="filter_form">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px" id="filter-table">
						<tbody>
							<tr>
								<td>
									<div style="margin:2px;padding:2px"><b><?php echo __( 'Add' , 'easyReservations' ); ?></b> <a id="show_add_price_link" href="javascript:show_add_price();document.filter_form.reset();"><?php echo __( 'Time' , 'easyReservations' ); ?></a> | <a id="show_add_discount_link" href="javascript:show_add_discount();document.filter_form.reset();"><?php echo __( 'Conditional' , 'easyReservations' ); ?></a> | <a id="show_add_avail_link" href="javascript:show_add_avail();document.filter_form.reset();"><?php echo __( 'Unavailability' , 'easyReservations' ); ?></a> <b>Filter</b> <a href="javascript:reset_filter_form()" style="float:right;margin-right:3px">&#10005;</a></div>
										<input type="hidden" name="filter_type" id="filter_type">
										<div id="filter_form_name" class="hide-it">
												<div class="fakehr"></div>
												<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Name' , 'easyReservations' ); ?>:</b> <input type="text" name="filter_form_name_field" id="filter_form_name_field"> <i><?php echo __( 'Give your filter a name' , 'easyReservations' ); ?></i><br>
										</div>
										<div id="filter_form_importance" class="hide-it">
												<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Priority' , 'easyReservations' ); ?>:</b> <select name="price_filter_imp" id="price_filter_imp"><?php echo easyReservations_num_options(1,99); ?></select><br>
										</div>
										<div id="filter_form_time_cond" class="hide-it">
											<div class="fakehr"></div>
											<span class="easy-h3"><?php echo __( 'Condition' , 'easyReservations' ); ?></span>
											<div class="fakehr"></div>
											<input type="radio" name="price_filter_cond" id="price_filter_cond_date" value="date"> <b class=""><?php echo __( 'Date' , 'easyReservations' ); ?></b><br>
											<div class="fakehr"></div>

											<span style="padding:2px 0px 2px 18px;">
												<label for="price_filter_cond_date" id="price_filter_cond_date"><?php echo __( 'At' , 'easyReservations' ); ?> <input type="text" id="price_filter_date" name="price_filter_date" style="width:71px"></label> <select id="price_filter_date_h" name="price_filter_date_h"><?php echo easyReservations_num_options("00", 23, 12); ?></select> : <select id="price_filter_date_m" name="price_filter_date_m"><?php echo easyReservations_num_options("00", 59); ?></select>
											</span><br>
											<div class="fakehr"></div>
											<input type="radio" name="price_filter_cond" id="price_filter_cond_range" value="range"> <b><?php echo __( 'Date range' , 'easyReservations' ); ?></b><br>
											<div class="fakehr"></div>
											<span style="padding:2px 0px 2px 18px;">
												<label for="price_filter_cond_range" id="price_filter_cond_range"><?php echo __( 'Between' , 'easyReservations' ); ?> <input type="text" id="price_filter_range_from" name="price_filter_range_from" style="width:71px"></label><select id="price_filter_range_from_h" name="price_filter_range_from_h"><?php echo easyReservations_num_options("00", 23, 12); ?></select>:<select id="price_filter_range_from_m" name="price_filter_range_from_m"><?php echo easyReservations_num_options("00", 59); ?></select> - <input type="text" id="price_filter_range_to" name="price_filter_range_to" style="width:71px"><select id="price_filter_range_to_h" name="price_filter_range_to_h"><?php echo easyReservations_num_options("00", 23, 12); ?></select>:<select id="price_filter_range_to_m" name="price_filter_range_to_m"><?php echo easyReservations_num_options("00", 59); ?></select>
											</span><br>
											<div class="fakehr"></div>

											<input type="radio" name="price_filter_cond" id="price_filter_cond_unit" value="unit"> <b><?php echo __( 'Unit' , 'easyReservations' ); ?></b><br>
											<div class="fakehr"></div>
											<label for="price_filter_cond_unit" id="price_filter_cond_unit">
										
											<span style="padding:2px 0px 2px 18px;margin-top:5px;float:none"><b><u><?php echo __( 'Hours' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price/availability for entire' , 'easyReservations' ).' '.__( 'day' , 'easyReservations' );?></i></span><br>
											<span style="min-width:99%;display:block;float:left">
												<div style="padding:0px 0px 0px 18px;margin:3px;width:52px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="0"> 00:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="1"> 01:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="2"> 02:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="3"> 03:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="4"> 04:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="5"> 05:00</label>
												</div>
												<div style="margin:3px;width:52px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="6"> 06:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="7"> 07:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="8"> 08:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="9"> 09:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="10"> 10:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="11"> 11:00</label>
												</div>
												<div style="margin:3px;width:52px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="12"> 12:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="13"> 13:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="14"> 14:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="15"> 15:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="16"> 16:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="17"> 17:00</label>
												</div>
												<div style="margin:3px;width:52px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="18"> 18:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="19"> 19:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="20"> 20:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="21"> 21:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="22"> 22:00</label>
													<label><input type="checkbox" name="price_filter_unit_hour[]" value="23"> 23:00</label>
												</div>
											</span>
											<span style="padding:2px 0px 2px 18px;"><b><u><?php echo __( 'Days' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price/availability for entire' , 'easyReservations' ).' '.__( 'calendar week' , 'easyReservations' );
											$days = easyreservations_get_date_name(); ?></i></span><br>
											<span style="min-width:99%;display:block;float:left">
												<div style="padding:0px 0px 0px 18px;margin:3px;width:90px;float:left;">
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="1"> <?php echo $days[0]; ?></label><br>
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="2"> <?php echo $days[1]; ?></label><br>
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="3"> <?php echo $days[2]; ?></label><br>
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="4"> <?php echo $days[3]; ?></label>
												</div>
												<div style="margin:3px;width:90px;float:left;">
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="5"> <?php echo $days[4]; ?></label><br>
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="6"> <?php echo $days[5]; ?></label><br>
													<label style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="7"> <?php echo $days[6]; ?></label><br>
												</div>
											</span>

											<span style="padding:2px 0px 2px 18px;margin-top:5px;float:none"><b><u><?php echo __( 'Calendar Week' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price/availability for entire' , 'easyReservations' ).' '.__( 'month' , 'easyReservations' ); ?></i></span><br>
											<span style="min-width:99%;display:block;float:left">
												<div style="padding:0px 0px 0px 18px;margin:3px;width:36px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="1"> 1</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="2"> 2</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="3"> 3</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="4"> 4</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="5"> 5</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="6"> 6</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="7"> 7</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="8"> 8</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="9"> 9</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="10"> 10</label>
												</div>
												<div style="margin:3px;width:36px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="11"> 11</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="12"> 12</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="13"> 13</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="14"> 14</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="15"> 15</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="16"> 16</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="17"> 17</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="18"> 18</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="19"> 19</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="20"> 20</label>
												</div>
												<div style="margin:3px;width:36px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="21"> 21</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="22"> 22</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="23"> 23</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="24"> 24</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="25"> 25</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="26"> 26</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="27"> 27</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="28"> 28</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="29"> 29</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="30"> 30</label>
												</div>
												<div style="margin:3px;width:36px;float:left;">
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="31"> 31</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="32"> 32</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="33"> 33</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="34"> 34</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="35"> 35</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="36"> 36</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="37"> 37</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="38"> 38</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="39"> 39</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="40"> 40</label>
												</div>
												<div style="margin:3px;width:36px;float:left">
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="41"> 41</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="42"> 42</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="43"> 43</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="44"> 44</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="45"> 45</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="46"> 46</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="47"> 47</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="48"> 48</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="49"> 49</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="50"> 50</label>
												</div>
												<div style="margin:3px;width:36px;float:left">
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="51"> 51</label>
													<label><input type="checkbox" name="price_filter_unit_cw[]" value="52"> 52</label>
												</div>
											</span>

											<span style="padding:2px 0px 2px 18px;margin-top:3px;float:none"><b><u><?php echo __( 'Monthes' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price/availability for entire' , 'easyReservations' ).' '.__( 'quarter' , 'easyReservations' ); 
											$monthes = easyreservations_get_date_name(1); ?></i></span><br>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="1"> <?php echo $monthes[0]; ?></label>
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="2"> <?php echo $monthes[1]; ?></label>
												<label style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="3"> <?php echo $monthes[2]; ?></label>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="4"> <?php echo $monthes[3]; ?></label>
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="5"> <?php echo $monthes[4]; ?></label>
												<label style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="6"> <?php echo $monthes[5]; ?></label>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="7"> <?php echo $monthes[6]; ?></label>
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="8"> <?php echo $monthes[7]; ?></label>
												<label style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="9"> <?php echo $monthes[8]; ?></label>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="10"> <?php echo $monthes[9]; ?></label>
												<label style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="11"> <?php echo $monthes[10]; ?></label>
												<label style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="12"> <?php echo $monthes[11]; ?></label>
											</div>

											<span style="padding:2px 0px 2px 18px;margin-top:3px"><b><u><?php echo __( 'Quarter' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price/availability for entire' , 'easyReservations' ).' '.__( 'year' , 'easyReservations' );  ?></i></span><br>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="1"> 1</label>
												<label style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="2"> 2</label>
												<label style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="3"> 3</label>
												<label style="width:40px;"><input type="checkbox" name="price_filter_unit_quarter[]" value="4"> 4</label>
											</div>

											<span style="padding:2px 0px 2px 18px;margin-top:3px"><b><u><?php echo __( 'Year' , 'easyReservations' ); ?></u></b></span><br>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2010"> 2010</label>
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2011"> 2011</label>
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2012"> 2012</label>
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2013"> 2013</label>
												<label style="width:50px;"><input type="checkbox" name="price_filter_unit_year[]" value="2014"> 2014</label>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2015"> 2015</label>
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2016"> 2016</label>
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2017"> 2017</label>
												<label style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2018"> 2018</label>
												<label style="width:50px;"><input type="checkbox" name="price_filter_unit_year[]" value="2019"> 2019</label>
											</div>
											</label>
										</div>
										<div id="filter_form_discount" class="hide-it">
											<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Type' , 'easyReservations' ); ?>:</b> <select name="filter_form_discount_type" id="filter_form_discount_type" onchange="setWord(this.value)"><option value="early"><?php echo ucfirst(easyreservations_interval_infos($reservations_current_int, 0)).' '.__( 'between reservation and arrival' , 'easyReservations' ); ?></option><option value="loyal"><?php echo __( 'Recurring guests' , 'easyReservations' ); ?></option><option value="stay"><?php echo __( 'Amount of' , 'easyReservations' ).' '.ucfirst(easyreservations_interval_infos($reservations_current_int));; ?></option><option value="pers"><?php echo __( 'Amount of' , 'easyReservations' ).' '.__( 'Persons' , 'easyReservations' ); ?></option></select><br>
											<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Condition' , 'easyReservations' ); ?>:</b> <select name="filter_form_discount_cond" id="filter_form_discount_cond"><?php echo easyReservations_num_options(1,99); ?></select> <span id="filter_form_discount_cond_verb">Days</span><br>
											<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Mode' , 'easyReservations' ); ?>:</b> 
												<select name="filter_form_discount_mode" id="filter_form_discount_mode">
													<option value="price_res"><?php echo __( 'Price per Reservation' , 'easyReservations' ); ?></option>
													<option value="price_day"><?php echo __( 'Price per' , 'easyReservations' ).' '.ucfirst(easyreservations_interval_infos($reservations_current_int, 1, 1));; ?></option>
													<option value="price_pers"><?php echo __( 'Price per Person' , 'easyReservations' ); ?></option>
													<option value="price_both"><?php echo __( sprintf('Price per %s and Person', ucfirst(easyreservations_interval_infos($reservations_current_int, 1, 1))) , 'easyReservations' ); ?></option>
													<option value="%"><?php echo __( 'Percent' , 'easyReservations' ); ?></option>
												</select><br>
											<i><?php echo __( 'If you add more than one discount of the same type only the first condition match from high to low will be given' , 'easyReservations' ); ?></i>
										</div>
										<div id="filter_form_price" class="hide-it">
											<div class="fakehr"></div>
											<span class="easy-h3"><?php echo __( 'Price' , 'easyReservations' ); ?></span>
											<div class="fakehr"></div>
											<b><?php echo __(get_the_title($resourceID)); ?></b>: <input type="text" name="filter-price-field" id="filter-price-field" value="-100"><select onchange="easy_change_amount(this);" name="filter-price-mode" class="hide-it"><option value="discount">Discount</option><option value="charge">Extra Charge</option></select>
										</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div id="filter_form_button" class="hide-it">
						<input class="easySubmitButton-primary" id="filter_form_button_input" type="button" value="<?php echo __( 'Add filter' , 'easyReservations' ); ?>" onclick="document.getElementById('filter_form').submit(); return false;" style="float:right;margin-top:3px">
					</div><div id="filter_form_hidden"></div>
					</form>
				</td>
			</tr>
		</table>
<script language="javascript" type="text/javascript" >
	function is_int(value){
		if((parseFloat(value) == parseInt(value)) && !isNaN(value)) return true;
		else return false;
	}
	function filter_edit(i){
		document.filter_form.reset();
		var type = filter[i]['type'];
		document.getElementById('filter_form_button_input').value = '<?php echo __( 'Edit filter' , 'easyReservations' ); ?>';
		document.getElementById('filter_form_hidden').innerHTML = '<input type="hidden" id="price_filter_edit" name="price_filter_edit" value="'+i+'">';
		document.getElementById('filter_form_name_field').value = filter[i]['name'];

		if(type == 'price' || type == 'unavail'){
			document.getElementsByName('price_filter_cond')[0].checked = true;
			var cond = filter[i]['cond'];
			if(cond == 'date' ){
				document.getElementsByName('price_filter_cond')[0].checked = true;
				var timestamp_date = filter[i]['date'];
				if(is_int(timestamp_date)){
					timestamp_date = parseFloat(timestamp_date + '000');
					var date_date = new Date (timestamp_date);
					document.getElementById('price_filter_date_h').selectedIndex = date_date.getHours() - 2;
					document.getElementById('price_filter_date_m').selectedIndex = date_date.getMinutes();
					document.getElementById('price_filter_date').value = (( date_date.getDate() < 10) ? '0'+ date_date.getDate() : date_date.getDate()) + '.' +(( (date_date.getMonth()+1) < 10) ? '0'+ (date_date.getMonth()+1) : (date_date.getMonth()+1)) + '.' + (( date_date.getYear() < 999) ? date_date.getYear() + 1900 : date_date.getYear());
				} else document.getElementById('price_filter_date').value = filter[i]['date'];
			} else if(cond == 'range'){
				document.getElementsByName('price_filter_cond')[1].checked = true;
				var timestamp_from = filter[i]['from'];
				if(is_int(timestamp_from)){
					timestamp_from = parseFloat(timestamp_from + '000');
					var date_from = new Date (timestamp_from);
					document.getElementById('price_filter_range_from_h').selectedIndex = date_from.getHours() - 2;
					document.getElementById('price_filter_range_from_m').selectedIndex = date_from.getMinutes();
					document.getElementById('price_filter_range_from').value = (( date_from.getDate() < 10) ? '0'+ date_from.getDate() : date_from.getDate()) + '.' +(( (date_from.getMonth()+1) < 10) ? '0'+ (date_from.getMonth()+1) : (date_from.getMonth()+1)) + '.' + (( date_from.getYear() < 999) ? date_from.getYear() + 1900 : date_from.getYear());
				} else document.getElementById('price_filter_range_from').value = filter[i]['date'];

				var timestamp_to = filter[i]['to'];
				if(is_int(timestamp_to)){
					timestamp_to = parseFloat(timestamp_to*1000);
					var date_to = new Date (timestamp_to);
					document.getElementById('price_filter_range_to_h').selectedIndex = date_to.getHours() - 2;
					document.getElementById('price_filter_range_to_m').selectedIndex = date_to.getMinutes();
					document.getElementById('price_filter_range_to').value = (( date_to.getDate() < 10) ? '0'+ date_to.getDate() : date_to.getDate()) + '.' + (((date_to.getMonth()+1) < 10) ? '0'+ (date_to.getMonth()+1) : (date_to.getMonth()+1)) + '.' + (( date_to.getYear() < 999) ? date_to.getYear() + 1900 : date_to.getYear());
				} else document.getElementById('price_filter_range_to').value = filter[i]['date'];
			} else {
				document.getElementsByName('price_filter_cond')[2].checked = true;
				var hour_checkboxes = document.getElementsByName('price_filter_unit_hour[]');
				if(hour_checkboxes && filter[i]['hour'] != ''){
					var hours =  filter[i]['hour'];
					var explode_hours = hours.split(",");
					for(var x = 0; x < explode_hours.length; x++){
						var nr = explode_hours[x];
						hour_checkboxes[nr].checked = true;
					}
				}
				var day_checkboxes = document.getElementsByName('price_filter_unit_days[]');
				if(day_checkboxes && filter[i]['day'] != ''){
					var days =  filter[i]['day'];
					var explode_days = days.split(",");
					for(var x = 0; x < explode_days.length; x++){
						var nr = explode_days[x];
						day_checkboxes[nr-1].checked = true;
					}
				}
				var cw_checkboxes = document.getElementsByName('price_filter_unit_cw[]');
				if(filter[i]['cw'] != ''){
					var cws =  filter[i]['cw'];
					var explode_cws = cws.split(",");
					for(var x = 0; x < explode_cws.length; x++){
						var nr = explode_cws[x];
						cw_checkboxes[nr-1].checked = true;
					}
				}
				var month_checkboxes = document.getElementsByName('price_filter_unit_month[]');
				if(filter[i]['month'] != ''){
					var month =  filter[i]['month'];
					var explode_month = month.split(",");
					for(var x = 0; x < explode_month.length; x++){
						var nr = explode_month[x];
						month_checkboxes[nr-1].checked = true;
					}
				}
				var q_checkboxes = document.getElementsByName('price_filter_unit_quarter[]');
				if(filter[i]['quarter'] != ''){
					var quarters =  filter[i]['quarter'];
					var explode_quarters = quarters.split(",");
					for(var x = 0; x < explode_quarters.length; x++){
						var nr = explode_quarters[x];
						q_checkboxes[nr-1].checked = true;
					}
				}
				var year_checkboxes = document.getElementsByName('price_filter_unit_year[]');
				if(filter[i]['year'] != ''){
					var years =  filter[i]['year'];
					var explode_years = years.split(",");
					for(var x = 0; x < explode_years.length; x++){
						var nr = explode_years[x] - 2009;
						year_checkboxes[nr-1].checked = true;
					}
				}
			}
		}
		if(type == 'price' || type == 'loyal' || type == 'early' || type == 'pers' || type == 'stay' ){
			var price = filter[i]['price'];
			var pricefield = document.getElementById('filter-price-field');
			var pricemodus = document.getElementsByName('filter-price-mode');

			pricefield.value = price;
			if(price > 0) pricemodus[0].selectedIndex = 1;
			else pricemodus[0].selectedIndex = 0;
		}
		if(type == 'price'){
			document.getElementById('price_filter_imp').selectedIndex = filter[i]['imp'] - 1;
			show_add_price();
		} else if(type == 'loyal' || type == 'early' || type == 'pers' || type == 'stay'){
			var discount_type = document.getElementById('filter_form_discount_type')
			if(type == 'early') discount_type.selectedIndex = 0;
			else if(type == 'loyal') discount_type.selectedIndex = 1;
			else if(type == 'stay') discount_type.selectedIndex =  2;
			else if(type == 'pers') discount_type.selectedIndex =  3;
			
			document.getElementById('filter_form_discount_cond').selectedIndex = filter[i]['cond']-1;
			
			if(filter[i]['modus'] == 'price_res') document.getElementById('filter_form_discount_mode').selectedIndex = 0;
			else if(filter[i]['modus'] == 'price_day') document.getElementById('filter_form_discount_mode').selectedIndex = 1;
			else if(filter[i]['modus'] == 'price_both') document.getElementById('filter_form_discount_mode').selectedIndex = 3;
			else if(filter[i]['modus'] == '%') document.getElementById('filter_form_discount_mode').selectedIndex = 4;
			else document.getElementById('filter_form_discount_mode').selectedIndex =  2;
			show_add_discount();
		}
		if(type == 'unavail'){
			show_add_avail();
		}
	}

	function show_add_price(){
		document.getElementById('filter_form_name').className = '';
		document.getElementById('filter_form_importance').className = '';
		document.getElementById('filter_form_time_cond').className = '';
		document.getElementById('filter_form_price').className = '';
		document.getElementById('filter_form_button').className = '';

		document.getElementById('filter_form_discount').className = 'hidden';
		var pricemodes = document.getElementsByName('filter-price-mode');
		for(var index = 0; index < pricemodes.length; index++) pricemodes[index].className = 'hidden';
		document.getElementById('filter_type').value="price";
	}
	function show_add_discount(){
		document.getElementById('filter_form_name').className = '';
		document.getElementById('filter_form_discount').className = '';
		document.getElementById('filter_form_price').className = '';
		document.getElementById('filter_form_button').className = '';
		var pricemodes = document.getElementsByName('filter-price-mode');

		for(var index = 0; index < pricemodes.length; index++) pricemodes[index].className = '';
		document.getElementById('filter_form_importance').className = 'hidden';
		document.getElementById('filter_form_time_cond').className = 'hidden';

		document.getElementById('filter_type').value="discount";
	}
	function show_add_avail(){		
		document.getElementById('filter_form_name').className = '';
		document.getElementById('filter_form_time_cond').className = '';
		document.getElementById('filter_form_button').className = '';

		document.getElementById('filter_form_discount').className = 'hidden';
		document.getElementById('filter_form_price').className = 'hidden';
		document.getElementById('filter_form_importance').className = 'hidden';

		document.getElementById('filter_type').value="unavail";
	}
	function reset_filter_form(){
		document.filter_form.reset();
		document.getElementById('filter_form_name').className = 'hidden';
		document.getElementById('filter_form_time_cond').className = 'hidden';
		document.getElementById('filter_form_button').className = 'hidden';
		document.getElementById('filter_form_discount').className = 'hidden';
		document.getElementById('filter_form_price').className = 'hidden';
		document.getElementById('filter_form_importance').className = 'hidden';
		document.getElementById('filter_type').value="";
		document.getElementById('filter_form_hidden').innerHTML = '';
		document.getElementById('filter_form_button_input').value = '<?php echo __( 'Add filter' , 'easyReservations' ); ?>';
	}
	function setWord(v){
		if(v == 'early' || v=='stay') var verb = '<?php echo easyreservations_interval_infos($reservations_current_int, 0, 0); ?>';
		if(v == 'loyal') var verb = '<?php echo __( 'visits' , 'easyReservations' ); ?>';
		if(v == 'pers') var verb = '<?php echo __( 'persons' , 'easyReservations' ); ?>';
		document.getElementById('filter_form_discount_cond_verb').innerHTML = verb;
	}
	jQuery(document).ready(function() {
		jQuery("#price_filter_date, #price_filter_range_from, #price_filter_range_to").datepicker({
			changeMonth: true,
			changeYear: true,
			showOn: 'both',
			buttonText: '<?php echo __( 'choose date' , 'easyReservations' ); ?>',
			buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png',
			buttonImageOnly: true,
			dateFormat: 'dd.mm.yy'
		});
	});
	function easy_change_amount(t){
		var fieldbefore = t.previousSibling;
		if(t){
			if(t.value == 'discount'){
				if(fieldbefore.value[0] == '-') var end = fieldbefore.value;
				else var end = '-' + fieldbefore.value;
			} else {
				if(fieldbefore.value[0] == '-') var end = fieldbefore.value.substr(1);
				else var end = fieldbefore.value;
			}
			fieldbefore.value = end;
		}
	}
	function easy_add_tax(x,y){
		if(x == 1) jQuery('.placeholder').before( '<span><img style="vertical-align:text-middle;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png" onClick="easy_add_tax(2, this);"> <input type="text" name="res_tax_names[]" value="Name" style="width:150px;margin-bottom:3px"><input type="text" name="res_tax_amounts[]" value="20" style="width:30px;margin-bottom:3px">%<br></span>');
		else {
			jQuery(y.parentNode).remove();
			jQuery(y).remove();
		}
	}
</script><style> .ui-datepicker-trigger { }</style>
<?php
	} elseif($site=='addresource'){
		if(isset($prompt)) echo $prompt;
 ?><form method="post" action="" name="addresource" id="addresource"><?php wp_nonce_field('easy-resource-add','easy-resource-add'); ?>
<input type="hidden" name="roomoroffer" value="<?php echo $addresource; ?>">
	<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:340px;">
		<thead>
			<tr>
				<th colspan="2"><?php echo __( 'Add resource' , 'easyReservations' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="alternate">
				<td colspan="2"><small><?php echo __( 'This will add a custom post that\'s private and only visible in forms and admin' , 'easyReservations' ); ?></small></td>
			</tr>
			<tr>
				<td nowrap><?php echo __( 'Resource\'s Title' , 'easyReservations' ); ?></td>
				<td><input type="text" size="32" name="thetitle"></td>
			</tr>
			<tr class="alternate">
				<td nowrap><?php echo __( 'Resource\'s Content' , 'easyReservations' ); ?></td>
				<td><textarea name="thecontent" rows="5" cols="23"></textarea></td>
			</tr>
			<tr>
				<td nowrap><?php echo __( 'Resource Image' , 'easyReservations' ); ?></td>
				<td>
					<label for="upload_image">
						<input id="upload_image" type="text" size="32" name="upload_image" value="" /> 
						<a id="upload_image_button" style="vertical-align:t"><img src="<?php echo admin_url().'images/media-button-image.gif'; ?>"></a>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="button" onclick="document.getElementById('addresource').submit(); return false;" style="margin-top:4px;" class="easySubmitButton-primary" value="<?php echo __( 'Add' , 'easyReservations' ); ?>">
</form>
<script>
	jQuery(document).ready(function() {
		jQuery('#upload_image_button').click(function() {
			formfield = jQuery('#upload_image').attr('name');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});

		window.send_to_editor = function(html) {
			imgurl = jQuery('img',html).attr('src');
			jQuery('#upload_image').val(imgurl);
			tb_remove();
		}
	});
</script><?php }

} 
function easyreservations_send_calendar_res(){
	echo '<script>easyreservations_send_calendar();</script>';
}
?>