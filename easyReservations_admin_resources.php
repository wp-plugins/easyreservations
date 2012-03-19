<?php
function reservation_resources_page(){ 

$offer_cat = get_option("reservations_special_offer_cat");
$room_category = get_option('reservations_room_category');

if(isset($_GET['delete']) && check_admin_referer( 'easy-resource-delete')){
	wp_delete_post($_GET['delete']);
	$prompt='<div class="updated"><p>'.__( 'Resouce deleted' , 'easyReservations' ).'</p></div>';
}
if(isset($_GET['room'])){
	$resourceID=$_GET['room'];
	$site='rooms';
}
if(isset($_POST['thecontent']) && check_admin_referer( 'easy-resource-add', 'easy-resource-add' )){
// Create post object
	if($_POST['roomoroffer']=='room') $cat=$room_category;
	else $cat=$offer_cat;
	$filename  = $_POST['upload_image'];

	// Insert the post into the database
	if($_POST['thetitle'] != ''){
		$add_roomoroffer = array(
			'post_title' => $_POST['thetitle'],
			'post_content' => $_POST['thecontent'],
			'post_status' => 'private',
			'post_category' => array($cat)
		);
		$thenewid = wp_insert_post( $add_roomoroffer );

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
					$filter['date'] = $_POST['price_filter_date'];
				} else $prompt='<div class="error"><p>'.__( 'Enter a date' , 'easyReservations' ).'</p></div>';

			} elseif($cond == 'range'){
				$filter['cond'] = 'range';
				
				if(isset($_POST['price_filter_range_from'])){
					$filter['from'] = $_POST['price_filter_range_from'];
				} else $prompt='<div class="error"><p>'.__( 'Enter a starting date' , 'easyReservations' ).'</p></div>';
		
				if(isset($_POST['price_filter_range_to'])){
					$filter['to'] = $_POST['price_filter_range_to'];
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

			}
			
		} else $prompt='<div class="error"><p>'.__( 'Select a condition' , 'easyReservations' ).'</p></div>';
	}

	if($type == 'price' || $type == 'discount'){
		if(isset($_POST['filter-price-field'])){
			$filter['price'] = $_POST['filter-price-field'];
		} else {
			$prices = $_POST['filter-price-fields'];
			$rooms = $_POST['filter-price-field-room'];
			$price_string = '';
			foreach($prices as $num => $price){

				$price_string .=  $rooms[$num].':'.$prices[$num].'-';

			}
			$filter['price'] = substr($price_string, 0, -1);
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
	update_post_meta($resourceID, 'easy_res_filter', $filters);
}
if(isset($_GET['addresource'])){
	$addresource=$_GET['addresource'];
	$site='addresource';
}

if(isset($_GET['site'])){
	$site=$_GET['site'];
}
?>
<h2>
	<?php echo __( 'Reservations Resources' , 'easyReservations' );?>
</h2>
<?php
if(!isset($site) OR $site=='' OR $site=='main'){
	global $wpdb;

	if(isset($prompt)) echo $prompt;
	$categoryids = array($room_category, $offer_cat);

	if($room_category == 0 OR empty($room_category)) echo '<b style="color:#FF0000">'.__( 'Add and set room post-category', 'easyReservations' ).'</b><br>';
	if($offer_cat == 0 OR empty($offer_cat)) echo '<b style="color:#FF0000">'.__( 'Add and set offer post-category', 'easyReservations' ).'</b>';
	else{

	foreach($categoryids as $categoryid){

		if($categoryid == $room_category){ 
			$roomoroffer=__( 'Rooms' , 'easyReservations' );
			$roo = 'room'; ?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-bottom:5px;">
				<thead>
					<tr>
						<th style="width:100px"><?php echo $roomoroffer; ?> <a href="admin.php?page=reservation-resources&addresource=<?php echo $roo; ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png"></a></th>
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
		} else { 
			$roomoroffer=__( 'Offers' , 'easyReservations' ); 
			$roo = 'offer';?>
					<tr class="tmiddle">
						<td style="width:100px"><?php echo $roomoroffer; ?> <a href="admin.php?page=reservation-resources&addresource=<?php echo $roo; ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png"></a>
						<td nowrap><?php echo __( 'Title' , 'easyReservations' );?></td>
						<td style="text-align:center" nowrap><?php echo __( 'ID' , 'easyReservations' );?></td>
						<td style="text-align:right" nowrap><?php echo __( 'Base Price' , 'easyReservations' ); ?></td>
						<td nowrap><?php echo __( 'Reservations' , 'easyReservations' ); ?></td>
						<td style="text-align:center" nowrap><?php echo __( 'Filter' , 'easyReservations' ); ?></td>
						<td colspan="3" nowrap><?php echo __( 'Excerpt' , 'easyReservations' ); ?></td>
						<td nowrap></td>
					</tr><?php
				}

		$roomargs = array(  'post_status' => 'publish|private', 'type' => 'post', 'category' => $categoryid, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => -1);
		if( $roo == 'room' ) $allrooms = easyreservations_get_rooms(1,1);
		else $allrooms = easyreservations_get_offers(1,1);
		$countallrooms=count($allrooms);
		$countresource=0;

		foreach( $allrooms as $allroom ){
			$countresource++;
			if($countresource%2==0) $class="alternate"; else $class="";
			$getfilters = get_post_meta($allroom->ID, 'easy_res_filter', true);
			foreach($filterouts as $filterout){ //foreach filter array
				$num++;
			}

			if(reservations_is_room($allroom->ID)){
				$price=reservations_format_money(get_post_meta($allroom->ID, 'reservations_groundprice', true), 1);
			} else {
				if(!preg_match("/^[0-9]+.[0-9]{1,2}$/", get_post_meta($allroom->ID, 'reservations_groundprice', true))){
					$price='';
					$explprices=explode("-", get_post_meta($allroom->ID, 'reservations_groundprice', true));
					foreach($explprices as $explprice){
						$explidprice=explode(":", $explprice);
						$price.='<b>'.__(get_the_title($explidprice[0])).':</b> '.reservations_format_money($explidprice[1], 1).'<br>';
					}
				} else {
					$price=reservations_format_money(get_post_meta($allroom->ID, 'reservations_groundprice', true), 1);
				}
			}

			$checkAvail = easyreservations_check_avail($allroom->ID, time());
			$theRoomCount = get_post_meta($allroom->ID, 'roomcount', true);
			if($checkAvail >=  $theRoomCount) $status='Full ('.$checkAvail.'/'.$theRoomCount.')'; 
			else $status='Empty ('.$checkAvail.'/'.$theRoomCount.')'; 
			if(reservations_is_room($allroom->ID)) $countallrooms = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$allroom->ID'"));
			else $countallrooms = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='$allroom->ID'"));

			?><tr class="<?php echo $class; ?>">
					<td style="text-align:left; vertical-align:middle;">
						<?php if(function_exists('get_the_post_thumbnail')){ ?>
							<a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><?php echo get_the_post_thumbnail($allroom->ID, array(25,25)); ?></a>
						<?php } ?>
					</td>
					<td><a href="admin.php?page=reservation-resources&room=<?php echo $allroom->ID;?>" title="<?php echo __( 'edit ' , 'easyReservations' ).' '.$allroom->post_title; ?>s "><?php echo '<b>'.__($allroom->post_title).'</b>'; ?></a></td>
					<td style="text-align:center"><?php echo '<b>'.$allroom->ID.'</b>'; ?></td>
					<?php if(reservations_is_room($allroom->ID)){ ?><td style="text-align:center;"><?php echo $theRoomCount; ?></td><?php } ?>
					<td style="text-align:right;width:100px" nowrap><?php echo $price;?></td>
					<td style="text-align:center;width:85px" nowrap><?php echo $countallrooms; ?></td>
					<td style="text-align:center" nowrap><?php echo count($getfilters)-1; ?></td>
					<?php if(reservations_is_room($allroom->ID)){ ?><td nowrap><?php echo $status; ?></td><?php } ?>
					<td <?php if(!reservations_is_room($allroom->ID)){ ?>colspan="3"<?php } ?>><?php echo substr($allroom->post_content, 0, 36); ?></td>
					<td style="text-align:right;width:100px">
						<a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit post' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a>
						<a href="admin.php?page=reservation-resources&room=<?php echo $allroom->ID;?>" title="<?php echo __( 'edit' , 'easyReservations' ); if($categoryid == $room_category) echo ' '. __( 'Room' , 'easyReservations' ); else echo ' '. __( 'Offer' , 'easyReservations' );?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"></a>
						<a href="<?php echo get_permalink( $allroom->ID ); ?>" target="_blank" title="<?php echo __( 'view post' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/page_white_link.png"></a>
						<a href="<?php echo wp_nonce_url('admin.php?page=reservation-resources&delete='.($allroom->ID).'', 'easy-resource-delete'); ?>" title="<?php echo __( 'trash & delete' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/trash.png"></a>
					</td>
				</tr><?php
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}	echo '</tbody></table>';
	} elseif($site=='rooms'){
		$get_role = get_post_meta($resourceID, 'easy-resource-permission', true);
		if(!empty($get_role) && !current_user_can($get_role)) die('You havnt the rights to view this reservation');

		if(isset($_POST['action'])) {
			$action=$_POST['action'];
		} else $action = "";

		if(reservations_is_room($resourceID)) $roomoroffer='room'; else $roomoroffer='offer';

		if($action=='set_groundprice'){ /* SET GROUND PRICE */
			$error=0;

			if($roomoroffer == "room"){
				if(easyreservations_check_price($_POST['groundprice']) == 'error') $error++;
				$gpricepost=easyreservations_check_price($_POST['groundprice']);
			} elseif ($roomoroffer == "offer"){
				$gpricepost='';
				for($countits=1; $countits <=  $_POST['countrooms']; $countits++){
					if(easyreservations_check_price($_POST['gppriceroom'.$countits]) == 'error') $error++;
					$offersGPrice = easyreservations_check_price($_POST['gppriceroom'.$countits]);
					$gpricepost.= $_POST['idgproom'.$countits].':'.$offersGPrice;
					if($countits !=  $_POST['countrooms']) $gpricepost.='-';
				}
			}
			if($error == 0) update_post_meta($resourceID,'reservations_groundprice', $gpricepost);
			else $prompt='<div class="error"><p>&nbsp;'.__( 'Insert right Money format' , 'easyReservations' ).'</p></div>';

			if(isset($_POST['roomcount'])){/* SET ROOM COUNT */
				if(is_numeric($_POST['roomcount'])){
					$reservations_room_count=$_POST['roomcount'];
					$reservations_current_room_count = get_post_meta($resourceID, 'roomcount', TRUE);
					if ($reservations_current_room_count){
						if($reservations_room_count == "")  delete_post_meta($resourceID,'roomcount');
						else update_post_meta($resourceID,'roomcount',$reservations_room_count);
					} else {
						add_post_meta($resourceID, 'roomcount', $reservations_room_count, TRUE);
					}
					$prompt='<div class="updated"><p>'.__( 'Room count set' , 'easyReservations' ).'</p></div>';
				} else $prompt='<div class="error"><p>&nbsp;'.__( 'The roomcount has to be a number' , 'easyReservations' ).'</p></div>';
			}

			if(isset($_POST['easy-resource-permission'])){/* SET easy-resource-permission COUNT */
				$reservations_room_count=$_POST['easy-resource-permission'];
				$reservations_current_room_count = get_post_meta($resourceID, 'easy-resource-permission', TRUE);
				if ($reservations_current_room_count){
					if($reservations_room_count == "")  delete_post_meta($resourceID,'easy-resource-permission');
					else{
						update_post_meta($resourceID,'easy-resource-permission',$reservations_room_count);
						$prompt='<div class="updated"><p>'.__( 'Resources permission set' , 'easyReservations' ).'</p></div>';
					}
				} else {
					add_post_meta($resourceID, 'easy-resource-permission', $reservations_room_count, TRUE);
				}
			}
			
			if(easyreservations_check_price($_POST['child_price']) != 'error'){ /* SET PRICE FOR CHILDS */
				$reservations_child_price=$_POST['child_price'];
				$reservations_current_child_price = get_post_meta($resourceID, 'reservations_child_price', TRUE);

				if ($reservations_current_child_price){
					if($reservations_child_price == "")  delete_post_meta($resourceID,'reservations_child_price');
					else update_post_meta($resourceID,'reservations_child_price',$reservations_child_price);
				} elseif($reservations_child_price != ""){
					add_post_meta($resourceID,'reservations_child_price',$reservations_child_price,TRUE);
				}
			} else $prompt='<div class="error"><p>&nbsp;'.__( 'Insert right Money format' , 'easyReservations' ).'</p></div>';

		} elseif($action=='set_infobox'){ /* SET THE OFFER INFO BOX */
			$reservations_percent=$_POST['pricebox'];
			$reservations_from_to=$_POST['fromtobox'];
			$reservations_short=$_POST['descbox'];
			$reservations_current_value_fromto = get_post_meta($resourceID, 'reservations_fromto', TRUE);
			$reservations_current_value_percent = get_post_meta($resourceID, 'reservations_percent', TRUE);
			$reservations_current_value_short = get_post_meta($resourceID, 'reservations_short', TRUE);

			if ($reservations_current_value_percent){
				if($reservations_percent == "")  delete_post_meta($resourceID,'reservations_percent');
				else update_post_meta($resourceID,'reservations_percent',$reservations_percent);
			} elseif($reservations_percent != ""){
				add_post_meta($resourceID,'reservations_percent',$reservations_percent,TRUE);
			}
			if($reservations_current_value_fromto) {
				if($reservations_from_to == "") delete_post_meta($resourceID,'reservations_fromto');
				else update_post_meta($resourceID,'reservations_fromto',$reservations_from_to);
			}
			elseif($reservations_from_to != ""){
				 add_post_meta($resourceID,'reservations_fromto',$reservations_from_to,TRUE);
			}
			if ($reservations_current_value_short) {
				if($reservations_short == "") delete_post_meta($resourceID,'reservations_short');
				else update_post_meta($resourceID,'reservations_short',$reservations_short);
			}
			elseif($reservations_short != "") {
				add_post_meta($resourceID,'reservations_short',$reservations_short,TRUE);
			}
		}

		$roomcategories = easyreservations_get_rooms();
		$counroooms=0;
		$count=count($roomcategories);
		if($roomoroffer == "offer"){ 
			$roomjsarray="";
			$roomgpadd="";
			$roomsadd="";
			foreach( $roomcategories as $roomcategorie ){
				$wassetted=0;
				$counroooms++;
				$roomjsarray .= ' <b> '.__($roomcategorie->post_title).'</b>:<input type="hidden" name="idroom'.$counroooms.'" id="idroom'.$counroooms.'" value="'.$roomcategorie->ID.'"><input type="text" id="priceroom'.$counroooms.'" name="priceroom'.$counroooms.'" value="Price" style="width:55px;text-align:right">';

				$explgpprices=explode("-", get_post_meta($resourceID, 'reservations_groundprice', true));
				
				foreach($explgpprices as $num=>$explgpprice){
					$explidgpprice=explode(":", $explgpprice);
					if($explidgpprice[0] == $roomcategorie->ID){
						if(!is_numeric($explidgpprice[1]) || $explidgpprice[1] == ''  || !isset($explidgpprice[1])) $theStyle = 'border-color:#F20909'; else $theStyle = "";
						if(is_int(($num+1)/2)) $class='alternate'; else $class='';
						$roomgpadd .= '<tr class="'.$class.'"><td>'.__($roomcategorie->post_title).': <input type="hidden" name="idgproom'.$counroooms.'" id="idgproom'.$counroooms.'" value="'.$roomcategorie->ID.'"></td><td style="text-align:right;"><input type="text" id="gppriceroom'.$counroooms.'" name="gppriceroom'.$counroooms.'" value="'.$explidgpprice[1].'" style="width:60px;margin-top:-2px;text-align:right;'.$theStyle.'"> &'.get_option("reservations_currency").';</td></tr>';
						$wassetted++;
					}
				}

				if( $wassetted == 0) $roomgpadd .= '<tr><td>'.__($roomcategorie->post_title).': <input type="hidden" name="idgproom'.$counroooms.'" id="idgproom'.$counroooms.'" value="'.$roomcategorie->ID.'"></td><td style="text-align:right;"><input type="text" id="gppriceroom'.$counroooms.'" name="gppriceroom'.$counroooms.'" value="Price" style="width:60px"></td></tr>';
				$roomsadd .= "document.getElementById('idroom".$counroooms."').value+':'+document.getElementById('priceroom".$counroooms."').value+";
				if($counroooms!=$count) $roomsadd .= "'-'+";
			}
		}

		if($roomoroffer == "room"){ 
			$gp = get_post_meta($resourceID, 'reservations_groundprice', true);
			if(!isset($gp) || empty($gp)) $gp = 0;
			$groundpricefield='<tr><td><b>'.__( 'Base price' , 'easyReservations' ).':</b></td><td style="text-align:right;"><input type="text" value="'.$gp.'" style="width:60px;text-align:right" name="groundprice"> &'.get_option("reservations_currency").';</td></tr>';
		} else {
			$groundpricefield = $roomgpadd;
		}

		$reservations_current_value_fromto = get_post_meta($resourceID, 'reservations_fromto', TRUE);
		$reservations_current_room_count = get_post_meta($resourceID, 'roomcount', TRUE);
		$reservations_current_value_percent = get_post_meta($resourceID, 'reservations_percent', TRUE);
		$reservations_current_child_price = get_post_meta($resourceID, 'reservations_child_price', TRUE);
		$reservations_current_value_short = get_post_meta($resourceID, 'reservations_short', TRUE);

		$roomargs = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'id' => $resourceID); 
		$allrooms = get_post( $resourceID );
					
		if(isset($prompt)) echo $prompt;
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
									<td><?php echo htmlentities(__($allrooms->post_content)); ?></td>
								</tr>
							</tbody>
					</table>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
					<thead>
						<tr>
							<th><?php echo __( 'Type' , 'easyReservations' ); ?></th>
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
							if(!empty($theFilters)) $count_all_filters=count($theFilters); else $count_all_filters=0; // count the filter-array element
							$numberoffilter = 0;
							if($count_all_filters > 0){
							foreach($theFilters as $nummer => $filter){ //foreach filter array
								if($filter['type'] == 'price') {
									$numberoffilter++; //count filters
									if($numberoffilter%2==0) $class="alternate"; else $class=""; ?>
									<tr class="<?php echo $class; ?>">
										<script>filter[<?php echo $nummer; ?>] = new Object(); filter[<?php echo $nummer; ?>] = <?php echo json_encode($filter); ?>;</script>
										<td class="resourceType" style="background:#BF4848;border-left:0px;width:60px;cursor:pointer">Price</td>
										<td style="vertical-align:middle;text-align:center;width:40px"><?php echo $filter['imp']; ?></td>
										<td>
											<?php echo easyreservations_get_price_filter_description($filter); ?>
										</td>
										<?php if($roomoroffer == "offer"){
											$explprices=explode("-", $filter['price']);
											$i = 0;
											$offer_prices = '';

											foreach($roomcategories as $roomcategorie){
											//	print_r($explprices);
											//	$key = array_search($roomcategorie->ID.':', $explprices);
												$keys = preg_grep("/^[".$roomcategorie->ID."]+[:]{1}/i", $explprices);
												if(count($keys) > 0){
													$explidprice=explode(":", $keys[$i]);
													$offer_prices.= '<b>'.__(get_the_title($roomcategorie->ID)).':</b> '.reservations_format_money($explidprice[1], 1).'<br>';
													$i++;
												} else {
													$offer_prices.= '<b>'.__(get_the_title($roomcategorie->ID)).':</b> '.reservations_format_money('0', 1).'<br>';
													$i++;
												}
											} ?>
											<td><?php echo $offer_prices; ?></td>
										<?php } else { ?>
											<td>
												<?php if(isset($filter['price']) && $filter['price'] > 0){ ?>
													<?php echo reservations_format_money($filter['price'], 1); ?>
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
									unset($theFilters[$nummer]);
								}
							}
							} else echo '<td colspan="5">'.__( 'No price filter set' , 'easyReservations' ).'</td>';
							?>
							</tbody>
							<tbody>
								<tr class="tmiddle">
									<td><?php echo __( 'Type' , 'easyReservations' ); ?></td>
									<td colspan="2"><?php echo __( 'Condition' , 'easyReservations' ); ?></td>
									<td><?php echo __( 'Discount' , 'easyReservations' ); ?></td>
									<td></td>
								</tr>
							<?php
							$numberoffilter2 = 0;
							if(!empty($theFilters)) $countfilter=count($theFilters); else $countfilter=0; // count the filter-array element
							if($countfilter > 0){
								foreach($theFilters as $nummer => $filter){ //foreach filter array
									$numberoffilter++; //count filters
									$numberoffilter2++;
									if($numberoffilter%2==0) $class="alternate"; else $class="";

									if($filter['type']=="stay"){
										$bgcolor='#76AEFC';
										$condition_string = sprintf(__('If guest stays %s days or more he\'ll get an discount of','easyReservations'), '<b>'.$filter['cond'].'</b>');
									} elseif($filter['type'] =="unavail"){
										$bgcolor='#81FC76';
										$condition_string =str_replace(__("calculate", 'easyReservations'), __("check", 'easyReservations'),substr(easyreservations_get_price_filter_description($filter),0,-42)).' '.__('resouce is unavailable','easyReservations');
									} elseif($filter['type'] =="pers"){
										$bgcolor='#1CA0E1';
										$condition_string = sprintf(__('If %s or more persons reservating they\'ll get an discount of','easyReservations'), '<b>'.$filter['cond'].'</b>');
									} elseif($filter['type'] =="loyal"){
										$bgcolor='#FCF776';
										if($filter['cond'] == 1) $end = 'st';
										elseif($filter['cond'] == 2) $end = 'nd';
										elseif($filter['cond'] == 3) $end = 'rd';
										else $end = 'th';
										$condition_string = sprintf(__('If guest comes the %1$s%2$s time he\'ll get an discount of','easyReservations'), '<b>'.$filter['cond'].'</b>', $end);
									} elseif($filter['type']=="early"){
										$bgcolor='#FCF776';
										$condition_string = sprintf(__('If the guest reservates %s days before his arrival he\'ll get a discount of','easyReservations'), '<b>'.$filter['cond'].'</b>');
									}
									?>
									<tr class="<?php echo $class; ?>" name="notsort">
										<script>filter[<?php echo $nummer; ?>] = new Object(); filter[<?php echo $nummer; ?>] = <?php echo json_encode($filter); ?>;</script>
										<td class="resourceType" style="background:<?php echo $bgcolor; ?>"><?php echo $filter['type']; ?></td>
										<td colspan="<?php if($filter['type'] == "unavail") echo 3; else echo 2; ?>"><?php echo $condition_string; ?></td>
										<?php
										if($filter['type'] != "unavail"){
											if($roomoroffer == "offer"){
											$explprices=explode("-", $filter['price']);
											$i = 0;
											$offer_prices = '';

											foreach($roomcategories as $roomcategorie){
												$keys = preg_grep("/^[".$roomcategorie->ID."]+[:]{1}/i", $explprices);
												if(count($keys) > 0){
													$explidprice=explode(":", $keys[$i]);
													$offer_prices.= '<b>'.__(get_the_title($roomcategorie->ID)).':</b> '.reservations_format_money($explidprice[1], 1).'<br>';
													$i++;
												} else {
													$offer_prices.= '<b>'.__(get_the_title($roomcategorie->ID)).':</b> '.reservations_format_money('0', 1).'<br>';
													$i++;
												}
											} ?>
											<td><?php echo $offer_prices; ?></td>
										<?php } else { ?>
											<td>
												<?php if(isset($filter['price']) && $filter['price'] > 0){ ?>
													<?php echo reservations_format_money($filter['price'], 1); ?>
												<?php } else { ?>
													<?php echo reservations_format_money('0', 1); ?>
												<?php } ?>
											</td>
										<?php } 
										} ?>
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
									<?php if($roomoroffer == "offer"){ ?>	
										<input type="hidden" name="offer" onChange="easyreservations_send_calendar()" value="<?php echo $resourceID; ?>">
										<b><?php echo __( 'Room' , 'easyReservations' ); ?></b>: <select name="room" onChange="easyreservations_send_calendar()" style="margin-top:5px;width:220px;"><?php echo reservations_get_room_options();?></select><br>
									<?php } else { ?>
										<b><?php echo __( 'Offer' , 'easyReservations' ); ?></b>: <select name="offer" onChange="easyreservations_send_calendar()" style="margin-top:5px;width:220px;"><option value="0"><?php printf ( __( 'none' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options();?></select><br>
										<input type="hidden" name="room" onChange="easyreservations_send_calendar()" value="<?php echo $resourceID; ?>">
									<?php } ?>
										<b><?php echo __( 'Persons' , 'easyReservations' ); ?></b>: <select name="persons" onChange="easyreservations_send_calendar()" style="margin-top:5px;width:80px;"><?php echo easyReservations_num_options(1, 10); ?></select> 
										<b><?php echo __( 'Childs' , 'easyReservations' ); ?></b>: <select name="childs" onChange="easyreservations_send_calendar()" style="margin-top:5px;width:80px;"><?php echo easyReservations_num_options(0, 10); ?></select><br>
										<b><?php echo sprintf("Reservated %s days ago", '</b><select name="reservated" onChange="easyreservations_send_calendar()" style="margin-top:5px;">'.easyReservations_num_options(0, 150).'</select><b>');  ?></b>
										<input type="hidden" name="date" onChange="easyreservations_send_calendar()" value="0">
										<input type="hidden" name="size" value="350,350,1">
									</form>
								</td>
							</tr>
						</tbody>
					</table>
					<script>easyreservations_send_calendar();</script>

				</td>
				<td>
				</td>
				<td style="width:35%" valign="top">
					<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_groundprice" name="set_groundprice">
					<input type="hidden" name="action" id="action" value="set_groundprice"><input type="hidden" name="countrooms" id="countrooms" value="<?php echo $counroooms; ?>">
					<table class="<?php echo RESERVATIONS_STYLE; ?>">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Base price per night' , 'easyReservations' ));?>	<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_groundprice').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
							<?php if(isset($groundpricefield)) echo $groundpricefield;?>
							<?php if($roomoroffer == "room"){ ?>
								<tr class="alternate">
									<td><b style="<?php if(!is_numeric($reservations_current_room_count) || $reservations_current_room_count < 1)  echo 'color:#F20909;'; ?>"><?php printf ( __( 'How many rooms with the same price?' , 'easyReservations' ));?>:</b></td>
									<td style="text-align:right;"><input type="text" name="roomcount" style="width:30px;margin: 2px;<?php if(!is_numeric($reservations_current_room_count) || $reservations_current_room_count < 1)  echo 'border-color:#F20909;'; ?>" value="<?php echo $reservations_current_room_count; ?>" style=""></td>
								</tr>
							<?php } ?>
							<tr <?php if($roomoroffer == "offer"){ ?>class="alternate"<?php } ?>>
								<td><b><?php printf ( __( 'Child discount' , 'easyReservations' ));?>:</b></td>
								<td style="text-align:right;"><input type="text" name="child_price" style="width:60px;text-align:right" value="<?php echo $reservations_current_child_price; ?>" style="margin: 2px;"> <?php echo '&'.get_option("reservations_currency").';<br>'; ?></td>
							</tr>
							<tr <?php if($roomoroffer == "room"){ ?>class="alternate"<?php } ?>>
								<td><b><?php printf ( __( 'Permission to admin the %s' , 'easyReservations' ), $roomoroffer);?>:</b></td>
								<td style="text-align:right">
									<select name="easy-resource-permission"><?php echo easyreservations_get_roles_options(get_post_meta($resourceID, 'easy-resource-permission', true));?></select>
								</td>
							</tr>
						</tbody>
					</table>
					</form>
				<?php if($roomoroffer == "offer"){ ?>
					<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_infobox" name="set_infobox">
					<input type="hidden" name="action" id="action" value="set_infobox">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Offer-box informations' , 'easyReservations' ));?>	<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_infobox').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
								<tr>
									<td>From - To:</td>
									<td style="text-align:right;"><input type="text" name="fromtobox" value="<?php echo $reservations_current_value_fromto;?>"></td>
								</tr>
								<tr>
									<td>Percent/Price:</td>
									<td style="text-align:right;"><input type="text" name="pricebox" value="<?php echo $reservations_current_value_percent;?>"></td>
								</tr>
								<tr>
									<td>Short Description:</td>
									<td style="text-align:right;"><input type="text" name="descbox" value="<?php echo $reservations_current_value_short;?>"></td>
								</tr>
						</tbody>
					</table>
					</form>
				<?php } ?>
					<form method="post" id="filter_form" name="filter_form">

					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px" id="filter-table">
						<tbody>
							<tr>
								<td>
									<div style="margin:2px;padding:2px"><b>Add</b> <a id="show_add_price_link" href="javascript:show_add_price();document.filter_form.reset();">Price</a> | <a id="show_add_discount_link" href="javascript:show_add_discount();document.filter_form.reset();">Discount</a> | <a id="show_add_avail_link" href="javascript:show_add_avail();document.filter_form.reset();">Unavail</a> <b>Filter</b> <a href="javascript:reset_filter_form()" style="float:right;margin-right:3px">X</a></div>
										<input type="hidden" name="filter_type" id="filter_type">
										<div id="filter_form_name" class="hide-it">
												<div class="fakehr"></div>
												<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Name' , 'easyReservations' ); ?>:</b> <input type="text" name="filter_form_name_field" id="filter_form_name_field"> <i>Give your filter a name</i><br>
										</div>
										<div id="filter_form_importance" class="hide-it">
												<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Priority' , 'easyReservations' ); ?>:</b> <select name="price_filter_imp" id="price_filter_imp"><?php echo easyreservations_num_options(1,99); ?></select><br>
										</div>

										<div id="filter_form_time_cond" class="hide-it">
											<div class="fakehr"></div>
											<span class="easy-h3"><?php echo __( 'Condition' , 'easyReservations' ); ?></span>
											<div class="fakehr"></div>
											<input type="radio" name="price_filter_cond" value="date"> <b class=""><?php echo __( 'Date' , 'easyReservations' ); ?></b><br>
											<div class="fakehr"></div>

											<span style="padding:2px 0px 2px 18px;">
												<?php echo __( 'Change price at' , 'easyReservations' ); ?> <input type="text" id="price_filter_date" name="price_filter_date" style="width:71px">
											</span><br>
											<div class="fakehr"></div>
											<input type="radio" name="price_filter_cond" value="range"> <b><?php echo __( 'Date range' , 'easyReservations' ); ?></b><br>
											<div class="fakehr"></div>
											<span style="padding:2px 0px 2px 18px;">
												<?php echo __( 'Change price between' , 'easyReservations' ); ?> <input type="text" id="price_filter_range_from" name="price_filter_range_from" style="width:71px"><?php echo __( 'and' , 'easyReservations' ); ?> <input type="text" id="price_filter_range_to" name="price_filter_range_to" style="width:71px">
											</span><br>
											<div class="fakehr"></div>

											<input type="radio" name="price_filter_cond" value="unit"> <b><?php echo __( 'Unit' , 'easyReservations' ); ?></b><br>
											<div class="fakehr"></div>

											<span style="padding:2px 0px 2px 18px;"><b><u><?php echo __( 'Days' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price for entire calendar week' , 'easyReservations' ); ?></i></span><br>
											<span style="min-width:99%;display:block;float:left">
												<div style="padding:0px 0px 0px 18px;margin:3px;width:90px;float:left;">
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="1"> <?php echo __( 'Monday' , 'easyReservations' ); ?></span><br>
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="2"> <?php echo __( 'Tuesday' , 'easyReservations' ); ?></span><br>
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="3"> <?php echo __( 'Wednesday' , 'easyReservations' ); ?></span><br>
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="4"> <?php echo __( 'Thursday' , 'easyReservations' ); ?></span>
												</div>
												<div style="margin:3px;width:90px;float:left;">
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="5"> <?php echo __( 'Friday' , 'easyReservations' ); ?></span><br>
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="6"> <?php echo __( 'Saturday' , 'easyReservations' ); ?></span><br>
													<span style="margin:3px;"><input type="checkbox" name="price_filter_unit_days[]" value="7"> <?php echo __( 'Sunday' , 'easyReservations' ); ?></span><br>
												</div>
											</span>

											<span style="padding:2px 0px 2px 18px;margin-top:5px;float:none"><b><u><?php echo __( 'Calendar Week' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price for entire month' , 'easyReservations' ); ?></i></span><br>
											<span style="min-width:99%;display:block;float:left">
												<div style="padding:0px 0px 0px 18px;margin:3px;width:36px;float:left;">
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="1"> 1</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="2"> 2</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="3"> 3</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="4"> 4</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="5"> 5</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="6"> 6</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="7"> 7</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="8"> 8</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="9"> 9</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="10"> 10</span>
												</div>
												<div style="margin:3px;width:36px;float:left;">
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="11"> 11</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="12"> 12</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="13"> 13</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="14"> 14</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="15"> 15</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="16"> 16</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="17"> 17</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="18"> 18</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="19"> 19</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="20"> 20</span>
												</div>
												<div style="margin:3px;width:36px;float:left;">
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="21"> 21</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="22"> 22</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="23"> 23</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="24"> 24</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="25"> 25</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="26"> 26</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="27"> 27</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="28"> 28</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="29"> 29</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="30"> 30</span>
												</div>
												<div style="margin:3px;width:36px;float:left;">
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="31"> 31</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="32"> 32</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="33"> 33</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="34"> 34</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="35"> 35</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="36"> 36</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="37"> 37</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="38"> 38</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="39"> 39</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="40"> 40</span>
												</div>
												<div style="margin:3px;width:36px;float:left">
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="41"> 41</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="42"> 42</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="43"> 43</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="44"> 44</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="45"> 45</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="46"> 46</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="47"> 47</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="48"> 48</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="49"> 49</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="50"> 50</span>
												</div>
												<div style="margin:3px;width:36px;float:left">
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="51"> 51</span>
													<span><input type="checkbox" name="price_filter_unit_cw[]" value="52"> 52</span>
												</div>
											</span>

											<span style="padding:2px 0px 2px 18px;margin-top:3px;float:none"><b><u><?php echo __( 'Monthes' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price for entire quarter' , 'easyReservations' ); ?></i></span><br>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="1"> <?php echo __( 'January' , 'easyReservations' ); ?></span>
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="2"> <?php echo __( 'February' , 'easyReservations' ); ?></span>
												<span style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="3"> <?php echo __( 'March' , 'easyReservations' ); ?></span>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="4"> <?php echo __( 'April' , 'easyReservations' ); ?></span>
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="5"> <?php echo __( 'May' , 'easyReservations' ); ?></span>
												<span style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="6"> <?php echo __( 'June' , 'easyReservations' ); ?></span>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="7"> <?php echo __( 'July' , 'easyReservations' ); ?></span>
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="8"> <?php echo __( 'August' , 'easyReservations' ); ?></span>
												<span style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="9"> <?php echo __( 'September' , 'easyReservations' ); ?></span>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="10"> <?php echo __( 'October' , 'easyReservations' ); ?></span>
												<span style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="11"> <?php echo __( 'November' , 'easyReservations' ); ?></span>
												<span style="width:80px;"><input type="checkbox" name="price_filter_unit_month[]" value="12"> <?php echo __( 'December' , 'easyReservations' ); ?></span>
											</div>

											<span style="padding:2px 0px 2px 18px;margin-top:3px"><b><u><?php echo __( 'Quarter' , 'easyReservations' ); ?></u></b></span><br>
											<span style="padding:2px 0px 2px 18px;"><i><?php echo __( 'select nothing to change price for entire year' , 'easyReservations' ); ?></i></span><br>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="1"> 1</span>
												<span style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="2"> 2</span>
												<span style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="3"> 3</span>
												<span style="width:40px;"><input type="checkbox" name="price_filter_unit_quarter[]" value="4"> 4</span>
											</div>

											<span style="padding:2px 0px 2px 18px;margin-top:3px"><b><u><?php echo __( 'Year' , 'easyReservations' ); ?></u></b></span><br>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2010"> 2010</span>
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2011"> 2011</span>
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2012"> 2012</span>
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2013"> 2013</span>
												<span style="width:50px;"><input type="checkbox" name="price_filter_unit_year[]" value="2014"> 2014</span>
											</div>
											<div style="padding:0px 0px 0px 20px;">
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2015"> 2015</span>
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2016"> 2016</span>
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2017"> 2017</span>
												<span style="width:50px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2018"> 2018</span>
												<span style="width:50px;"><input type="checkbox" name="price_filter_unit_year[]" value="2019"> 2019</span>
											</div>
										</div>
										<div id="filter_form_discount" class="hide-it">
											<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Type' , 'easyReservations' ); ?>:</b> <select name="filter_form_discount_type" id="filter_form_discount_type" onchange="setWord(this.value)"><option value="early">Days between reservation and arrival</option><option value="loyal">Recurring guests</option><option value="stay">Amount of nights</option><option value="pers">Amount of Persons</option></select><br>
											<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Condition' , 'easyReservations' ); ?>:</b> <select name="filter_form_discount_cond" id="filter_form_discount_cond"><?php echo easyreservations_num_options(1,99); ?></select> <span id="filter_form_discount_cond_verb">Days</span><br>
											<b style="padding:4px;display:inline-block;min-width:65px"><?php echo __( 'Mode' , 'easyReservations' ); ?>:</b> 
												<select name="filter_form_discount_mode" id="filter_form_discount_mode">
													<option value="price_res"><?php echo __( 'Price per Reservation' , 'easyReservations' ); ?></option>
													<option value="price_day"><?php echo __( 'Price per Day' , 'easyReservations' ); ?></option>
													<option value="%"><?php echo __( 'Percent' , 'easyReservations' ); ?></option>
												</select><br>
											<i><?php echo __( 'If you add more than one discount of the same type only the first condition match from high to low will be given' , 'easyReservations' ); ?></i>
										</div>
										<div id="filter_form_price" class="hide-it">
											<div class="fakehr"></div>
											<span class="easy-h3"><?php echo __( 'Price' , 'easyReservations' ); ?></span>
											<div class="fakehr"></div>
											<?php if($roomoroffer == "offer"){
												foreach($roomcategories as $num => $roomcategorie){?>
													<span style="display:block;margin:2px;padding:3px 5px;">
													<b style="min-width:90px;display:inline-block"><?php echo __(get_the_title($roomcategorie->ID)); ?></b>
													<input type="text" name="filter-price-fields[]" id="filter-price-fields-<?php echo $roomcategorie->ID; ?>">
													<input type="hidden" name="filter-price-field-room[]" id="filter-price-field-room-<?php echo $num; ?>" value="<?php echo $roomcategorie->ID; ?>">
												</span>
												<?php } ?>
											<?php } else { ?>
													<b><?php echo __(get_the_title($resourceID)); ?></b>: <input type="text" name="filter-price-field" id="filter-price-field">
											<?php } ?>
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
				document.getElementById('price_filter_date').value = filter[i]['date'];
			} else if(cond == 'range'){
				document.getElementsByName('price_filter_cond')[1].checked = true;
				document.getElementById('price_filter_range_from').value = filter[i]['from'];
				document.getElementById('price_filter_range_to').value = filter[i]['to'];
			} else {
				document.getElementsByName('price_filter_cond')[2].checked = true;
				
				var day_checkboxes = document.getElementsByName('price_filter_unit_days[]');
				if(filter[i]['day'] != ''){
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
			if(pricefield){
				pricefield.value = price;
			} else {
				var price_split = price.split('-');
				for(var x = 0; x < price_split.length; x++){
					var room_split = price_split[x].split(':');
					document.getElementById('filter-price-fields-'+room_split[0]).value = room_split[1];
				}
			}
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

		document.getElementById('filter_type').value="price";
	}
	function show_add_discount(){
		document.getElementById('filter_form_name').className = '';
		document.getElementById('filter_form_discount').className = '';
		document.getElementById('filter_form_price').className = '';
		document.getElementById('filter_form_button').className = '';

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
		if(v == 'early' || v=='stay') var verb = '<?php echo __( 'days' , 'easyReservations' ); ?>';
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

</script><style> .ui-datepicker-trigger { }</style>
<?php
	} elseif($site=='addresource'){
		
		if($addresource=='room'){
			$roomoroffer='Room';
			$cat=get_the_category_by_ID($room_category);
		} else {
			$roomoroffer='Offer';
			$cat=get_the_category_by_ID($offer_cat);
		}
		
		if(isset($prompt)) echo $prompt;

 ?><form method="post" action="" name="addresource" id="addresource"><?php wp_nonce_field('easy-resource-add','easy-resource-add'); ?>
<input type="hidden" name="roomoroffer" value="<?php echo $addresource; ?>">
	<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:340px;">
		<thead>
			<tr>
				<th colspan="2"><?php echo __( 'Add' , 'easyReservations' ); echo ' '.$roomoroffer; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="alternate">
				<td colspan="2"><small><?php echo __( 'This will add a post to the' , 'easyReservations' ).' '.$roomoroffer.' '.__( 'category' , 'easyReservations' ).' ('.$cat.') '; ?><br><?php echo __( 'It will be private and only visible in forms and admin' , 'easyReservations' ); ?></small></td>
			</tr>
			<tr>
				<td nowrap><?php echo $roomoroffer.'\'s '.__( 'Title' , 'easyReservations' ); ?></td>
				<td><input type="text" size="32" name="thetitle"></td>
			</tr>
			<tr class="alternate">
				<td nowrap><?php echo $roomoroffer.'\'s '.__( 'Content' , 'easyReservations' ); ?></td>
				<td><textarea name="thecontent" rows="5" cols="23"></textarea></td>
			</tr>
			<tr>
				<td nowrap><?php echo $roomoroffer.'\'s '.__( 'Image' , 'easyReservations' ); ?></td>
				<td>
					<label for="upload_image">
						<input id="upload_image" type="text" size="32" name="upload_image" value="URL" /> 
						<a id="upload_image_button" style="vertical-align:t"><img src="<?php echo admin_url().'images/media-button-image.gif'; ?>"></a>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="button" onclick="document.getElementById('addresource').submit(); return false;" style="margin-top:4px;" class="easySubmitButton-primary" value="<?php printf ( __( 'Add' , 'easyReservations' )); echo ' '.$roomoroffer; ?>">
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