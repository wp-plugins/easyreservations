<?php
function reservation_resources_page(){ 

$offer_cat = get_option("reservations_special_offer_cat");
$room_category = get_option('reservations_room_category');

if(isset($_GET['delete']) && check_admin_referer( 'easy-resource-delete')){
	wp_delete_post($_GET['delete']);
	$prompt='<div class="updated"><p>'.__( 'Reservation deleted' , 'easyReservations' ).'</p></div>';
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
			$roo = 'offer'; 
			?><tr class="tmiddle">
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
		if( $roo == 'room' ) $allrooms = easyreservations_get_rooms(1);
		else $allrooms = easyreservations_get_offers(1);
		$countallrooms=count($allrooms);
		$countresource=0;

		foreach( $allrooms as $allroom ){
			$countresource++;
			if($countresource%2==0) $class="alternate"; else $class="";
			$getfilters = split('\[|\] |\]', get_post_meta($allroom->ID, 'reservations_filter', true));
			$filterouts=array_values(array_filter($getfilters)); //make array out of filters
			$countfilter=count($filterouts);// count the filter-array element
			asort($filterouts);
			$get_filters='';
			$num=1;
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
			if(reservations_is_room($allroom->ID)) $countallrooms = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$allroom->ID'"));
			else $countallrooms = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='$allroom->ID'"));

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
					<td style="text-align:center" nowrap><?php echo $num-1; ?></td>
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
		if(isset($_POST['action'])) {
			$action=$_POST['action'];
			$action2=$_POST['action2'];
		} else $action = "";

		if(reservations_is_room($resourceID)) $roomoroffer='room'; else $roomoroffer='offer';

		if($action=='set_filter'){
			$filterpost=$_POST['reservations_filter'];
			$replacefilter=str_replace(",", ".", $filterpost);
			update_post_meta($resourceID,'reservations_filter', $replacefilter);
		}

		if($action=='set_groundprice'){ /* SET GROUND PRICE */
			$error=0;

			if($roomoroffer == "room"){
				if(easyreservations_check_price($_POST['groundprice']) == 'error') $error++;
				$gpricepost=easyreservations_check_price($_POST['groundprice']);
			} elseif ($roomoroffer == "offer"){
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
					} elseif($reservations_room_count != ""){
						add_post_meta($resourceID, 'roomcount', $reservations_room_count, TRUE);
					}
				} else $prompt='<div class="error"><p>&nbsp;'.__( 'The roomcount has to be a number' , 'easyReservations' ).'</p></div>';
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
		
		if($roomoroffer == "room"){ 
			$gp = get_post_meta($resourceID, 'reservations_groundprice', true);
			if(isset($gp) && $gp)
			$groundpricefield='<tr><td><b>'.__( 'Groundprice' , 'easyReservations' ).':</b></td><td style="text-align:right;"><input type="text" value="'.$gp.'" style="width:60px;text-align:right" name="groundprice"> &'.get_option("reservations_currency").';</td></tr>';
			$gprice='<b>'.__( 'Groundprice' , 'easyReservations' ).': </b>'.reservations_format_money(get_post_meta($resourceID, 'reservations_groundprice', true), 1);
		} else {
			if(!preg_match("/^[0-9]+.[0-9]{1,2}$/", get_post_meta($resourceID, 'reservations_groundprice', true))){
				$gprice='';
				$explprices=explode("-", get_post_meta($resourceID, 'reservations_groundprice', true));
				foreach($explprices as $explprice){
					$explidprice=explode(":", $explprice);
					$gprice.='<b>'.__(get_the_title($explidprice[0])).':</b> '.reservations_format_money($explidprice[1], 1).' ';
				}
			} else {
				$gprice='<b>'.__( 'Groundprice' , 'easyReservations' ).': </b>'.reservations_format_money(get_post_meta($resourceID, 'reservations_groundprice', true), 1);
			}
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
  ?><script language="JavaScript" id="urlCalendar" src="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_calendar.js"></script>
		<table style="width:99%">
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
					<?php
					$theFilters = get_post_meta($resourceID, 'reservations_filter', true);
					if(!empty($theFilters)){
						preg_match_all("/[\[](.*?)[\]]/", $theFilters, $getfilters);
						$filteroutsa=array_values(array_filter($getfilters)); //make array out of filters
						unset($filteroutsa[0]);
						$filterouts = $filteroutsa[1];
						$countprices = 0;
						foreach($filterouts as $filterout){ //foreach filter array
							$filtertype=explode(" ", $filterout);
							if(!preg_match('/(loyal|stay|pers|avail|early)/i', $filtertype[0])){
								$countprices++;
								if(!preg_match("/^[0-9]$/", $filtertype[1])){
									if(preg_match('/(january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|septembre|sep|octobre|oct|novembre|nov|decembre|dec)/', $filtertype[1])){
										 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 4 '.$filtertype[1].' ', $filterouts);
									} elseif(preg_match('/(week|weekdays|weekend|monday|mon|tuesday|tue|wednesday|wed|thursday|thu|friday|fri|saturday|sat|sunday|sun)/', $filtertype[1])){
										 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 2 '.$filtertype[1].' ', $filterouts);
									} elseif(preg_match("/(([0-9]{4}[\;])+|^[0-9]{4}$)/", $filtertype[1])){
										 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 6 '.$filtertype[1].' ', $filterouts);
									} elseif(preg_match("/(([0-9]{1,2}[\;])+|^[0-9]{1,2}$)/", $filtertype[1])){
										 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 3 '.$filtertype[1].' ', $filterouts);
									} elseif(preg_match('/(q1|quarter1|q2|quarter2|q3|quarter3|q4|quarter4)/', $filtertype[1])){
										 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 5 '.$filtertype[1].' ', $filterouts);
									} elseif(preg_match("/[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}[\-][\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}/", $filtertype[1]) OR preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[1])){
										$filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 1 '.$filtertype[1].' ', $filterouts);
									}
								}
							} elseif(preg_match('/avail/i', $filtertype[0])) {
								$filterouts = preg_replace("/.*(?<![^0-9])".$filterout."/", "2".$filterout."", $filterouts);
							} elseif(preg_match('/stay/i', $filtertype[0])) {
								$filterouts = preg_replace("/.*(?<![^0-9])".$filterout."/", "3".$filterout."", $filterouts);
							} elseif(preg_match('/loyal/i', $filtertype[0])) {
								$filterouts = preg_replace("/.*(?<![^0-9])".$filterout."/", "4".$filterout."", $filterouts);
							} elseif(preg_match('/early/i', $filtertype[0])) {
								$filterouts = preg_replace("/.*(?<![^0-9])".$filterout."/", "5".$filterout."", $filterouts);
							} elseif(preg_match('/pers/i', $filtertype[0])) {
								$filterouts = preg_replace("/.*(?<![^0-9])".$filterout."/", "6".$filterout."", $filterouts);
							}
						}

						$countfilter=count($filterouts);// count the filter-array element
						asort($filterouts);

						$get_filters='';
						$numberoffilter=0;

						if($countfilter > 0){
					?>
					<script>
						var currency = '<?php echo get_option('reservations_currency'); ?>';
						
						function easyRes_format_money(price){
							var pricevalue = parseInt(price);

							if(currency=='dollar') var thepricestring = '&nbsp;&'+ currency +'; ' + pricevalue.toFixed(2);
							else var thepricestring = pricevalue.toFixed(2) + '&nbsp;&'+ currency +';';
							
							return thepricestring;
						}
						
						var modus = 0;

						function easyRes_sendReq_Filters(i){
							var pricefield = document.getElementById('easy-res-filter-price-amount-field-'+i);
							var condfield = document.getElementById('easy-res-filter-price-cond-field-'+i);
							var condstring = document.getElementById('easy-res-filter-price-cond-string-'+i);

							if(modus  == 0 ){
							
								if(condstring){
									condfield.style.visibility = 'visible';
									condfield.style.maxWidth = '70%';
									condfield.style.width = '70%';
									condfield.style.position = 'relative';

									condstring.style.visibility = 'hidden'; 
									condstring.style.maxWidth = '0.1px';
									condstring.style.position = 'absolute';
									createAutocomplete();
								}

								document.getElementById('er-res-filter-edit-img-'+i).src = '<?php echo RESERVATIONS_IMAGES_DIR.'/check.png'; ?>';

								if(pricefield){
									var priceamount = document.getElementById('easy-res-filter-price-amount-field-'+i);
									var pricestring = document.getElementById('easy-res-filter-price-amount-string-'+i);

									pricestring.style.visibility = 'hidden';
									pricestring.style.maxWidth = '1px';
									pricestring.style.position = 'absolute';

									pricefield.style.visibility = 'visible';
									pricefield.style.maxWidth = '50px';
									pricefield.style.position = 'relative';

								} else {
									var price_fields = document.getElementsByName('easy-res-filter-price-amount-field-'+i);
									var price_strings = document.getElementsByName('easy-res-filter-price-amount-string-'+i);

									for(var x=0;x<price_fields.length;x++){
										price_strings[x].style.visibility = 'hidden';
										price_strings[x].style.maxWidth = '1px';
										price_strings[x].style.position = 'absolute';

										price_fields[x].style.visibility = 'visible';
										price_fields[x].style.maxWidth = '50px';
										price_fields[x].style.position = 'relative';
									}
								}
								modus = 1;

							} else {

								var imp = document.getElementById('impselect_'+i).value;
								var filter = document.getElementById('filter_'+i).value;

								if(pricefield){
									var pricestring = document.getElementById('easy-res-filter-price-amount-string-'+i);
									var pricevalue = parseInt(pricefield.value);
									pricestring.style.visibility = 'visible';
									pricestring.style.maxWidth = '200px';
									pricestring.style.position = 'relative';

									pricefield.style.visibility = 'hidden';
									pricefield.style.maxWidth = '0.1px';
									pricefield.style.position = 'absolute';

									document.getElementById('easy-res-filter-price-amount-string-'+i).innerHTML = easyRes_format_money(pricefield.value);
									
									var price = pricefield.value;
								} else {
									var price_fields = document.getElementsByName('easy-res-filter-price-amount-field-'+i);
									var price_rooms = document.getElementsByName('easy-res-filter-price-room-field-'+i);
									var price_strings = document.getElementsByName('easy-res-filter-price-amount-string-'+i);

									var price = '';
									for(var x=0;x<price_fields.length;x++){
										price += price_rooms[x].value + ':' + price_fields[x].value + '-';
										price_fields[x].style.visibility = 'hidden';
										price_fields[x].style.maxWidth = '1px';
										price_fields[x].style.position = 'absolute';

										price_strings[x].style.visibility = 'visible';
										price_strings[x].style.maxWidth = '50px';
										price_strings[x].style.position = 'relative';
										price_strings[x].innerHTML = easyRes_format_money(price_fields[x].value);
									}
									price = price.substr(0,price.length-1);
									//document.getElementById('easy-res-filter-price-amount-string-'+i).innerHTML = thepricestring;
									//alert(price);
								}
								if(condstring){
									condstring.style.visibility = 'visible';
									condstring.style.maxWidth = '99%';
									condstring.style.position = 'relative';

									condfield.style.visibility = 'hidden'; 
									condfield.style.maxWidth = '0.1px';
									condfield.style.position = 'absolute'; 
								}
								
								var timestings = condfield.value;
								if(timestings.substr(timestings.length -1) == ';'){
									//var timestings = timestings.substr(0, timestings.length -1);
								}
								if(timestings.substr(0,1) == ';'){
									//var timestings = timestings.substr(0, 1);
								}
								//timestings = timestings.replace(";;", ";");
								var newfilter = 'price ' + imp + ' ' + timestings + ' ' + price;
								var oldfilter = document.getElementById('reservations_filter').value;

								if(oldfilter.search(filter) == -1){
									var filtercheck = filter.replace("price ", "");
									filtercheck = filtercheck.substr(2);
									var texta = 'price ' + filtercheck;
									var text = oldfilter.replace(texta, newfilter);
								} else {
									var text = oldfilter.replace(filter, newfilter);
								}
								
								modus = 0;

								document.getElementById('reservations_filter').value = text;
								document.getElementById('filter_'+i).value = newfilter;

								easyreservations_send_filter(newfilter, i);
								document.getElementById('er-res-filter-edit-img-'+i).src = '<?php echo RESERVATIONS_IMAGES_DIR.'/edit.png'; ?>';

							}
						}

						function easy_delete_filter(i){
							var fField = document.getElementById('filter_'+i);
							var f = fField.value;

							var allf = document.getElementById('reservations_filter').value;

							fField.parentNode.style.display = 'none';

							document.getElementById('reservations_filter').value = allf.replace('[' + f + ']', '');

							easyreservations_send_filter();
						}
					</script>
							<input type="hidden" id="theResourceID" value="<?php echo $resourceID; ?>">
							<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
							<thead>
								<tr>
									<th><?php echo __( 'Type' , 'easyReservations' ); ?></th>
									<th style="text-align:center;"><?php echo __( 'Priority' , 'easyReservations' ); ?></th>
									<th><?php echo __( 'Time' , 'easyReservations' ); ?></th>
									<th><?php echo __( 'Price' , 'easyReservations' ); ?></th>
									<th style="text-align:center"><?php echo __( 'Del' , 'easyReservations' ); ?></th>
								</tr>
							</thead>
							<tbody id="sortable"><?php

							foreach($filterouts as $nummer => $filterout){ //foreach filter array
								$filtertype=explode(" ", $filterout);
								if(preg_match('/price/i', $filtertype[0])) {
									$numberoffilter++; //count filters
									if($numberoffilter%2==0) $class="alternate"; else $class=""; ?>
									<tr class="<?php echo $class; ?>">
										<input type="hidden" id="filter_<?php echo $nummer; ?>" value="<?php echo $filterout; ?>">
										<td class="resourceType" style="background:#BF4848;border-left:0px;width:60px;cursor:pointer"><?php echo $filtertype[0]; ?></td>
										<td style="vertical-align:middle;text-align:center;width:40px"><select style="width:40px" id="impselect_<?php echo $nummer; ?>" onchange="easyRes_sendReq_Filters(<?php echo $nummer; ?>)"><?php echo easyReservations_num_options(1,9,$filtertype[1]); ?></select></td>
										<td>
											<span id="easy-res-filter-price-cond-string-<?php echo $nummer; ?>">
												<?php if($countprices == $numberoffilter) echo substr(easyreservations_get_price_filter_description($filtertype), 0 , -38).' '.__( 'groundprice get used', 'easyReservations' ); else echo easyreservations_get_price_filter_description($filtertype); ?>
											</span>
											<input type="text" class="easy-resource-condition-auto" id="easy-res-filter-price-cond-field-<?php echo $nummer; ?>" style="visibility:hidden;max-width:0.1px" value="<?php echo $filtertype[2]; ?>">
											<!--onchange="var e =document.getElementById('easy-res-filter-price-cond-string-<?php //echo $numberoffilter; ?>'); e.style.visible = 'visible'; e.style.position = 'relative'; e.style.maxWidth = '100px'; this.style.visibility = 'hidden'; this.style.maxWidth = '0.1px';"-->
										</td>
										<?php if($roomoroffer == "offer"){
											$explprices=explode("-", $filtertype[3]);
											$i = 0;
											$offer_prices = '';

											foreach($roomcategories as $roomcategorie){
											//	print_r($explprices);
											//	$key = array_search($roomcategorie->ID.':', $explprices);
												$keys = preg_grep("/^[".$roomcategorie->ID."]+[:]{1}/i", $explprices);
												if(count($keys) > 0){
													$explidprice=explode(":", $keys[$i]);
													$offer_prices.= '<b>'.__(get_the_title($roomcategorie->ID)).':</b> <span name="easy-res-filter-price-amount-string-'.$nummer.'" id="easy-res-filter-price-amount-string-'.$nummer.'-'.$i.'">'.reservations_format_money($explidprice[1], 1).'</span><input type="hidden" name="easy-res-filter-price-room-field-'.$nummer.'" value="'.$roomcategorie->ID.'"><input type="text" name="easy-res-filter-price-amount-field-'.$nummer.'" id="easy-res-filter-price-amount-field-'.$nummer.'-'.$i.'" style="visibility:hidden;max-width:0.1px" value="'.$explidprice[1].'"><br>';
													$i++;
												} else {
													$offer_prices.= '<b>'.__(get_the_title($roomcategorie->ID)).':</b> <span id="easy-res-filter-price-amount-string-'.$nummer.'-'.$i.'">'.reservations_format_money('0', 1).'</span><input type="hidden" name="easy-res-filter-price-room-field-'.$nummer.'" value="'.$roomcategorie->ID.'"><input type="text" name="easy-res-filter-price-amount-field-'.$nummer.'" id="easy-res-filter-price-amount-field-'.$nummer.'-'.$i.'" style="visibility:hidden;max-width:0.1px" value="0"><br>';
													$i++;
												}
											} ?>
											<td><?php echo $offer_prices; ?></td>
										<?php } else { ?>
											<td>
												<?php if(isset($filtertype[3]) && $filtertype[3] > 0){ ?>
													<?php echo '<span id="easy-res-filter-price-amount-string-'.$nummer.'">'.reservations_format_money($filtertype[3], 1).'</span><input type="text" id="easy-res-filter-price-amount-field-'.$nummer.'"  style="visibility:hidden;max-width:0.1px" value="'.$filtertype[3].'">'; ?>
												<?php } else { ?>
													<?php echo '<span id="easy-res-filter-price-amount-string-'.$nummer.'">'.reservations_format_money('0', 1).'</span><input type="text" id="easy-res-filter-price-amount-field-'.$nummer.'"  style="visibility:hidden;max-width:0.1px" value="0">'; ?>
												<?php } ?>
											</td>
										<?php } ?>
										<td style="vertical-align:middle;text-align:center">
											<?php echo ' <a onclick="easyRes_sendReq_Filters('.$nummer.')"><img id="er-res-filter-edit-img-'.$nummer.'" style="vertical-align:text-middle;" src="'.RESERVATIONS_IMAGES_DIR.'/edit.png"></a>'; ?>
											<?php echo ' <a onclick="easy_delete_filter('.$nummer.')"><img style="vertical-align:text-middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a>'; ?>
										</td>
									</tr>
									<?php
									unset($filterouts[$nummer]);
								}
							}
							if($numberoffilter == 0) echo '<td colspan="5">'.__( 'No price filter set' , 'easyReservations' ).'</td>';
							?>
							</tbody>
							<tbody>
								<tr class="tmiddle">
									<td><?php echo __( 'Type' , 'easyReservations' ); ?></td>
									<td colspan="2"><?php echo __( 'Condition' , 'easyReservations' ); ?></td>
									<td><?php echo __( 'Discount' , 'easyReservations' ); ?></td>
									<td style="text-align:center"><?php echo __( 'Del' , 'easyReservations' ); ?></td>
								</tr>
							<?php
							$numberoffilter2 = 0;
							$countfilter=count($filterouts);// count the filter-array element
							if($countfilter > 0){
								arsort($filterouts);
								foreach($filterouts as $filterout){ //foreach filter array
									$filtertype=explode(" ", substr($filterout, 1));
									$numberoffilter++; //count filters
									$numberoffilter2++;
									if($numberoffilter%2==0) $class="alternate"; else $class="";

									if($filtertype[0]=="stay"){
										$bgcolor='#76AEFC';
										$condition_string = sprintf(__('If guest stays %s days or more he\'ll get an discount of','easyReservations'), '<b>'.$filtertype[1].'</b>');
									}
									elseif($filtertype[0]=="avail"){
										$bgcolor='#81FC76';
										$explode = explode("-", $filtertype[1]);
										$condition_string = sprintf(__('Resource is unavail bewteen %1$s and %2$s','easyReservations'), '<b>'.$explode[0].'</b>', '<b>'.$explode[1].'</b>');
									}
									elseif($filtertype[0]=="pers"){
										$bgcolor='#1CA0E1';
										$condition_string = sprintf(__('If guest reservates %s days before his arrival he\'ll get an discount of','easyReservations'), '<b>'.$filtertype[1].'</b>');
									}
									elseif($filtertype[0]=="loyal"){
										$bgcolor='#FCF776';
										if($filtertype[1] == 1) $end = 'st';
										elseif($filtertype[1] == 2) $end = 'nd';
										elseif($filtertype[1] == 3) $end = 'rd';
										else $end = 'th';
										$condition_string = sprintf(__('If guest comes the %1$s%2$s time he\'ll get an discount of','easyReservations'), '<b>'.$filtertype[1].'</b>', $end);
									}
									elseif($filtertype[0]=="early"){
										$bgcolor='#FCF776';
										$condition_string = sprintf(__('If the guest reservates %s days before his arrival he\'ll get a discount of','easyReservations'), '<b>'.$filtertype[1].'</b>');
									}
									?>
									<tr class="<?php echo $class; ?>" name="notsort">
										<td class="resourceType" style="background:<?php echo $bgcolor; ?>"><?php echo $filtertype[0]; ?></td>
										<td colspan="2"><?php echo $condition_string; ?></td>
										<?php
										if($filtertype[0] != "avail"){
											if($roomoroffer == "offer"){
												if(preg_match('/\:/', $filtertype[2])){
												$offer_prices='';
												$explprices=explode("-", $filtertype[2]);
													foreach($explprices as $explprice){
														$explidprice=explode(":", $explprice);
														if(substr($explidprice[0], -1) == "%") $money = $explidprice[0];
														else $money = reservations_format_money($explidprice[0], 1);
														$offer_prices .= '<b>'.__(get_the_title($explidprice[0])).':</b> '.$money.'<br>';
													} 
												} else {
													if(substr($filtertype[2], -1) == "%") $offer_prices = $filtertype[2];
													else $offer_prices = reservations_format_money($filtertype[2], 1);
												} ?><td><?php echo $offer_prices; ?></td><?php 
											} else {
												if(substr($filtertype[2], -1) == "%") $money = $filtertype[2];
												else $money = reservations_format_money($filtertype[2], 1); ?><td><?php echo $money; ?></td><?php 
											}
										} else echo '<td></td>'; ?>
										<td style="vertical-align:middle;text-align:center">
											<?php echo ' <a onclick="easy_delete_filter('.$nummer.')"><img style="vertical-align:text-middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a>'; ?>
										</td>
									</tr>
								<?php
								}
							} else echo '<tr><td colspan="5">'.__( 'No filter set' , 'easyReservations' ).'</td></tr>';  ?>
							</table><?php
						}
					}
					?>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
						<thead>
							<tr>
								<th><?php echo __( 'Filters' , 'easyReservations' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<form id="form1" name="form1" style="line-height: 2">
										<div style="float: left;" >
											<select name="jumpmenu" id="jumpmenu" onChange="jumpto(document.form1.jumpmenu.options[document.form1.jumpmenu.options.selectedIndex].value)">
												<option><?php echo __( 'Add Filter' , 'easyReservations' ); ?></option>
												<option value="price">Price</option>
												<option value="pers">Persons</option>
												<option value="stay">Stay</option>
												<option value="loyal">Loyal</option>
												<option value="early">Early</option>
												<option value="avail">Availibility</option>
											</select>
										</div>
										<div id="Text" style="float: left;"></div>
										<div id="Text2" style="float: left;"></div>
										<div id="Text3" style="float: left;"></div>
										<div id="Text4" style="float: left;"></div> 
										&nbsp;<a href="javascript:resetform();" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Reset' , 'easyReservations' ); ?></a>
									</form>
									<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_filters" name="set_filters">
										<input type="hidden" name="action" id="action" value="set_filter">
										<textarea style="width:100%;font: 18px #000000; background:#FFFFE0; border: 1px solid #E6DB55; margin:3px 0 4px 0;"  id="reservations_filter" name="reservations_filter" onChange="addSaveButton()"><?php echo get_post_meta($resourceID, 'reservations_filter', true); ?></textarea><br>
								</form>
								</td>
							</tr>
						</tbody>
					</table>
					<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_filters').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Set Filters' , 'easyReservations' ));?>">

					<div id="showCalender" style="margin:6px 6px 0 0;float:left"></div>
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
										<input type="hidden" name="offer" onChange="easyRes_sendReq_Calendar()" value="<?php echo $resourceID; ?>">
										<b><?php echo __( 'Room' , 'easyReservations' ); ?></b>: <select name="room" onChange="easyRes_sendReq_Calendar()" style="margin-top:5px;width:220px;"><?php echo reservations_get_room_options();?></select><br>
									<?php } else { ?>
										<b><?php echo __( 'Offer' , 'easyReservations' ); ?></b>: <select name="offer" onChange="easyRes_sendReq_Calendar()" style="margin-top:5px;width:220px;"><option value="0"><?php printf ( __( 'none' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options();?></select><br>
										<input type="hidden" name="room" onChange="easyRes_sendReq_Calendar()" value="<?php echo $resourceID; ?>">
									<?php } ?>
										<b><?php echo __( 'Persons' , 'easyReservations' ); ?></b>: <select name="persons" onChange="easyRes_sendReq_Calendar()" style="margin-top:5px;width:80px;"><?php echo easyReservations_num_options(1, 10); ?></select> 
										<b><?php echo __( 'Childs' , 'easyReservations' ); ?></b>: <select name="childs" onChange="easyRes_sendReq_Calendar()" style="margin-top:5px;width:80px;"><?php echo easyReservations_num_options(0, 10); ?></select><br>
										<b><?php echo sprintf("Reservated %s days ago", '</b><select name="reservated" onChange="easyRes_sendReq_Calendar()" style="margin-top:5px;">'.easyReservations_num_options(0, 150).'</select><b>');  ?></b>
										<input type="hidden" name="date" onChange="easyRes_sendReq_Calendar()" value="0">
										<input type="hidden" name="size" value="350,350,1">
									</form>
								</td>
							</tr>
						</tbody>
					</table>
					<script>
						easyRes_sendReq_Calendar();
					</script>
				</td>
				<td>
				</td>
				<td style="width:35%" valign="top">
					<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_groundprice" name="set_groundprice">
					<input type="hidden" name="action" id="action" value="set_groundprice"><input type="hidden" name="countrooms" id="countrooms" value="<?php echo $counroooms; ?>">
					<table class="<?php echo RESERVATIONS_STYLE; ?>">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Groundprice' , 'easyReservations' ));?>	<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_groundprice').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
							<?php echo $groundpricefield;?>
							<?php if($roomoroffer == "room"){ ?>
								<tr class="alternate">
									<td><b style="<?php if(!is_numeric($reservations_current_room_count) || $reservations_current_room_count < 1)  echo 'color:#F20909;'; ?>"><?php printf ( __( 'How many rooms with the same price?' , 'easyReservations' ));?>:</b></td>
									<td style="text-align:right;"><input type="text" name="roomcount" style="width:30px;margin: 2px;<?php if(!is_numeric($reservations_current_room_count) || $reservations_current_room_count < 1)  echo 'border-color:#F20909;'; ?>" value="<?php echo $reservations_current_room_count; ?>" style=""></td>
								</tr>
							<?php } ?>
							<tr class?"<?php if($roomoroffer == "offer"){ ?>alternate<?php } ?>">
								<td><b><?php printf ( __( 'Child discount' , 'easyReservations' ));?>:</b></td>
								<td style="text-align:right;"><input type="text" name="child_price" style="width:60px;text-align:right" value="<?php echo $reservations_current_child_price; ?>" style="margin: 2px;"> <?php echo '&'.get_option("reservations_currency").';<br>'; ?></td>
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
							</form>
						</tbody>
					</table>
				<?php } ?>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
						<thead>
							<tr>
								<th><?php printf ( __( 'Filter help' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div id="Helper"></div><div id="Helper2"></div><div id="Helper3"></div>
									<div class="explainbox" style='border:solid 1px #FFF141;" background:#FFFDFD;'>
									  <p><code>[price]</code> <small><?php printf ( __( 'Set Price for specific time period' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'Time period' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Price in the selected period' , 'easyReservations' ));?></small></p>
									  <div class="explfakehr"></div><p><code>[stay]</code> <small><?php printf ( __( 'Set discount for longer stays' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'minimum stay in days' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[loyal]</code> <small><?php printf ( __( 'Set discount for recurring guests' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'Visits to have for discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[early]</code> <small><?php printf ( __( 'Set early bird discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'minimum days between reservation and arrival for discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[pers]</code> <small><?php printf ( __( 'Set group-discount for more persons' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'Number of persons for discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[avail]</code> <small><?php printf ( __( 'Make resource unavailable for period of time' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>dd.mm.yyyy-dd.mm.yyyy</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'empty' , 'easyReservations' ));?></small></p>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
		<script language="javascript" type="text/javascript" >
function AddOne(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+']';
}
function AddTwo(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+']';
}
function AddThree(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+' '+
    document.getElementById("zwei").value+']';
}
function AddAvail(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("avail1").value+'-'+
    document.getElementById("avail2").value+']';
}
function AddPriceRoom(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("price1").value+
	document.getElementById("hidden").value+
    document.getElementById("price2").value+' '+
    document.getElementById("drei").value+']';
}
function AddPriceOffer(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("price1").value+
	document.getElementById("hidden").value+
    document.getElementById("price2").value+' '+
    <?php echo $roomsadd; ?>']';
}
function AddFour(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("eins").value+' '+
    document.getElementById("zwei").value+' '+
    document.getElementById("drei").value+']';
}

var thetext1 = false;
var thetext2 = false;
var thetext3 = false;
var thetext4 = false;
var unitorrange = false;
var roomoffer = '<?php echo $roomoroffer; ?>';
var odas = '<?php echo $roomjsarray;?>';

function resetform(){ // Reset fields in Form
	var Nichts = '';
	document.form1.reset();
	document.form1.jumpmenu.disabled=false;
	document.getElementById("Text").innerHTML = Nichts;
	document.getElementById("Text2").innerHTML = Nichts;
	document.getElementById("Text3").innerHTML = Nichts;
	document.getElementById("Text4").innerHTML = Nichts;
	document.getElementById("Helper").innerHTML = Nichts;
	thetext1 = false;
	thetext2 = false;
	thetext3 = false;
	thetext4 = false;
	unitorrange = false;
}
var fixHelper = function(e, ui) {
    ui.children().each(function() {
        jQuery(this).width(jQuery(this).width());
    });
    return ui;
};
jQuery(function() {
	jQuery( "#sortable" ).sortable({
		placeholder: "ui-state-highlight",
		helper: fixHelper,
		handle: '.resourceType',
		cancel: 'input,button,textarea',
        start: function(event, ui) {
            start = ui.item.prevAll().length + 1;
        },
        update: function(event, ui) {
			end = ui.item.prevAll().length + 1;
			var diff = end - start;

			var table = jQuery(this);
			
			var i = 1;

			jQuery('tr', table).each(function(column) {
				i++;
				jQuery(this).removeClass('alternate');
				if(i%2==0) {
					jQuery(this).addClass('alternate');
				}
			});
			jQuery('select', ui.item).each(function(column) {
				var selectedIndex = jQuery(this)[0].selectedIndex;
				var newIndex = diff + selectedIndex;

				if(newIndex >= 0 && newIndex <= jQuery(this)[0].length) jQuery(this)[0].selectedIndex += diff;
				else{
					if(newIndex > jQuery(this)[0].length) jQuery(this)[0].selectedIndex = jQuery(this)[0].length;
					else jQuery(this)[0].selectedIndex = 0;
				}
			});
        }

	});
	
	jQuery( ".resourceType" ).disableSelection();
});

function jumpto(x){ // Chained inputs;

	var end = 0;

	if(thetext1 == false){
		if (x == "pers") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="personcond">Condition</option><?php echo easyReservations_num_options(1,99); ?></select>';
			document.getElementById("Text").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b><?php echo __( 'Select the amount of needed persons for discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "stay") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="staycond">Condition</option><?php echo easyReservations_num_options(1,99); ?></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select the amount of needed nights for discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "loyal") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="loyalcond">Condition</option><?php echo easyReservations_num_options(1,99); ?></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select the amount of needed past approved reservations with the same email for discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "early") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="earlycond">Condition</option><?php echo easyReservations_num_options(1,99); ?></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select the amount of needed days between reservation and arrival for discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "avail") {
			var Output  = '<input type="text" name="eins" id="avail1" value="dd.mm.yyyy" style="width:73px"> - <input type="text" name="zwei" id="avail2" value="dd.mm.yyyy" style="width:73px">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			createPickersAvail();

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the period in which the resource is unavailable' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>10.11.2012 - 20.12.2012</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the avail filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<a href="javascript:AddAvail()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><b>Add</b></a>';
			document.getElementById("Text4").innerHTML += Output;
		} else if (x == "price") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)"><option>Condition</option><option value="daterange">Date Range</option><option value="date">Date</option><option value="unit">Unit of time</option></select> ';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Choose the type of condition for the price filter' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b>Date Range</b> <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>01.06.2013 - 23.08.2003</code></i>';
				Help += '<br> &emsp; <i><b>Day</b> <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>10.05.2012</code></i>';
				Help += '<br> &emsp; <i><b>Unit of Time</b> <i><?php echo __( 'a recurring or fixed unit of time' , 'easyReservations' ); ?> <code>monday;2011;jan</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the avail filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		}
	} else if (thetext2 == false) {
		if (x == "personcond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in amount of discount in money or percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>4587</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>58.24</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>11%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the persons filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "staycond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in amount of discount in money or percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>348</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>11.99</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>20%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the stay filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "earlycond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in amount of discount in money or percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>367</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>9844.76</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>45%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the loyal filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;
			
			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "loyalcond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in amount of discount in money or percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>46</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>9874.7</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>3%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the loyal filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;
			
			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "daterange"){
			var Output  = '<input type="text" name="zwei" id="price1" value="dd.mm.yyyy" style="width:73px"> - <input type="text" name="drei" id="price2" value="dd.mm.yyyy" style="width:73px" onClick="jumpto(document.form1.price2.id)"><input type="hidden" id="hidden" value="-">';
			document.getElementById("Text2").innerHTML += Output;
			createPickersPrice();

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the dates from the period in which the groundprice should be different' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>22.10.2011-10.01.2012</code></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			unitorrange = true;
			document.form1.eins.disabled=true;
		} else if (x == "date"){
			var Output  = '<input type="text" name="zwei" id="price1" value="dd.mm.yyyy" style="width:73px" onClick="jumpto(document.form1.price2.id)"><input type="hidden" id="price2" value=""><input type="hidden" id="hidden" value="">';
			document.getElementById("Text2").innerHTML += Output;
			createPickersPrice();

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the date in which the groundprice should be different' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>19.03.2013</code></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			unitorrange = true;
			document.form1.eins.disabled=true;
		} else if (x == "unit") {
			var Output  = '<input type="text" name="zwei" id="price2" value="Condition" onClick="if(this.value == \'Condition\') this.value = \'\'; jumpto(document.form1.price2.id)"><input type="hidden" name="drei" id="price1" value=""><input type="hidden" name="vier" id="hidden" value=""> ';
			document.getElementById("Text2").innerHTML += Output;
			createAutocomplete();

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the unit of time in which the groundprice should be different' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <?php echo __( 'The condition field has to match ' , 'easyReservations' ); ?><b>option1;option2;option3 ...</b>';
				Help += '<br> &emsp; <b><?php echo __( 'Type of units by priority' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Day' , 'easyReservations' ); ?></b> <?php echo __( 'used if the day to calculate is weekday - (monday - sunday & week, weekend)' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: monday <?php echo __( 'or' , 'easyReservations' ); ?> friday;sunday;tuesday <?php echo __( 'or' , 'easyReservations' ); ?> mon;fri;sun;sat</i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Calendarweek' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected calendarweek(s)' , 'easyReservations' ); ?> (1 - 52)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: 42 <?php echo __( 'or' , 'easyReservations' ); ?> 1;2;3;65</i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Month' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected month(s) (january - december)' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: march <?php echo __( 'or' , 'easyReservations' ); ?> january;february;apr;august <?php echo __( 'or' , 'easyReservations' ); ?> dez;nov;july;mai</i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Quarter' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected quarter(s)' , 'easyReservations' ); ?> (1 - 4)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: 1 <?php echo __( 'or' , 'easyReservations' ); ?> 2;3;1</i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Year' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected year(s)' , 'easyReservations' ); ?> (1970 - 2038)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: 2013 <?php echo __( 'or' , 'easyReservations' ); ?> 2011;2013;2014</i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		}
	} else if (thetext3 == false){
		if (x == "Name") {
			end = 4;
			var Output  = '<input type="text" name="drei" id="drei" value="Options">';
			document.getElementById("Text3").innerHTML += Output;
			thetext3 = true;
		} else if (x == "price2") {
			var Help = '<div class="explainbox">';
			
			if(unitorrange == false) {
				Help += '<b>1. <?php echo __( 'Type in the unit of time in which the groundprice should be different' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <?php echo __( 'The Condition Field has to match ' , 'easyReservations' ); ?><b>option1;option2;option3 ...</b>';
				Help += '<br> &emsp; <b><?php echo __( 'Type of Units by Priority' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Day' , 'easyReservations' ); ?></b> <?php echo __( 'used if the day to calculate is weekday - (monday - sunday & week, weekend)' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>monday</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>friday;sunday;tuesday</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>mon;fri;sun;sat</code></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Calendarweek' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected calendarweek(s)' , 'easyReservations' ); ?> (1 - 52)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>42</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>1;2;3;65</code></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Month' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected month(s) (january - december)' , 'easyReservations' ); ?></i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>march</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>january;february;apr;august</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>dez;nov;july;mai</code></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Quarter' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected quarter(s)' , 'easyReservations' ); ?> (1 - 4)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>q1</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>q2;quarter3;q1</code></i>';
				Help += '<br> &emsp; <i><b><?php echo __( 'Year' , 'easyReservations' ); ?></b> <?php echo __( 'used if day to calculate is in selected year(s)' , 'easyReservations' ); ?> (1970 - 2038)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>2013</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>2011;2013;2014</code></i><br>';
			} else { 
				Help += '<b>1. <?php echo __( 'Type in the dates from the period in which the groundprice should be different' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>22.10.2011-10.01.2012</code></i><br>';
			}
			
			if (roomoffer == "offer") {
				var zahlrooms = 0;
				var Output  = '';
				end = 7;
				Output  += odas;
				
				Help += '<b>2. <?php echo __( 'Type in the price for each room in the selected period' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>5437</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>3.04</code></i>';
			} else if (roomoffer == "room") {
				end = 6;
				var Output  = '<input type="text" name="drei" id="drei" value="Price" style="width:50px">';

				Help += '<b>2. <?php echo __( 'Type in the Price for the Room in the selected Period' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><?php echo __( 'for example' , 'easyReservations' ); ?>: <code>143</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>585.78</code></i>';
			}
			Help += '<br><b>3. <?php echo __( 'Click on "Add" to add the price filter to the resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.getElementById("Text3").innerHTML += Output;
			thetext3 = true;
		}
	}
	if (end == 1) {
		var Output  = '&nbsp;<a href="javascript:AddOne()"  class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 2) {
		var Output  = '&nbsp;<a href="javascript:AddTwo()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 3) {
		var Output  = '&nbsp;<a href="javascript:AddThree()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 4) {
		var Output  = '&nbsp;<a href="javascript:AddFour()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 5) {
		var Output  = '&nbsp;<a href="javascript:AddAvail()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 6) {
		var Output  = '&nbsp;<a href="javascript:AddPriceRoom()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 7) {
		var Output  = '&nbsp;<a href="javascript:AddPriceOffer()" class="easySubmitButton-primary" style="line-height:1;vertical-align:top;"><?php echo __( 'Add' , 'easyReservations' ); ?></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
}

function createPickersPrice(context) {
  jQuery("#price1, #price2", context || document).datepicker({
	dateFormat: 'dd.mm.yy'
  });
}
function createPickersAvail(context) {
  jQuery("#avail1, #avail2", context || document).datepicker({
	dateFormat: 'dd.mm.yy'
  });
}
function createAutocomplete(context) {

	jQuery(function() {
		var availableTags = [
			{ label: "<?php echo __( 'Monday', 'easyReservations'); ?>", value: "mon", category: "Days" }, { label: "<?php echo __( 'Tuesday', 'easyReservations'); ?>", value: "tue", category: "Days" }, { label: "<?php echo __( 'Wednesday', 'easyReservations'); ?>", value: "wed", category: "Days" }, { label: "<?php echo __( 'Thursday', 'easyReservations'); ?>", value: "thu", category: "Days" }, { label: "<?php echo __( 'Friday', 'easyReservations'); ?>", value: "fri", category: "Days" }, { label: "<?php echo __( 'Saturday', 'easyReservations'); ?>", value: "sat", category: "Days" }, { label: "<?php echo __( 'Sunday', 'easyReservations'); ?>", value: "sun", category: "Days" },
			{ label: "<?php echo __( 'Week', 'easyReservations'); ?>", value: "week", category: "Days" }, { label: "<?php echo __( 'Weekend', 'easyReservations'); ?>", value: "weekend", category: "Days" }, 
			{ label: "<?php echo __( 'January', 'easyReservations'); ?>", value: "jan", category: "Monthes" }, { label: "<?php echo __( 'February', 'easyReservations'); ?>", value: "feb", category: "Monthes" }, { label: "<?php echo __( 'March', 'easyReservations'); ?>", value: "mar", category: "Monthes" }, { label: "<?php echo __( 'April', 'easyReservations'); ?>", value: "apr", category: "Monthes" }, { label: "<?php echo __( 'May', 'easyReservations'); ?>", value: "may", category: "Monthes" }, { label: "<?php echo __( 'June', 'easyReservations'); ?>", value: "jun", category: "Monthes" }, { label: "<?php echo __( 'July', 'easyReservations'); ?>", value: "jul", category: "Monthes" }, { label: "<?php echo __( 'August', 'easyReservations'); ?>", value: "aug", category: "Monthes" }, { label: "<?php echo __( 'September', 'easyReservations'); ?>", value: "sep", category: "Monthes" }, { label: "<?php echo __( 'October', 'easyReservations'); ?>", value: "oct", category: "Monthes" }, { label: "<?php echo __( 'November', 'easyReservations'); ?>", value: "nov", category: "Monthes" }, { label: "<?php echo __( 'December', 'easyReservations'); ?>", value: "dec", category: "Monthes" }, 
			{ label: "<?php echo __( 'Quarter', 'easyReservations'); ?> 1", value: "q1", category: "Quarters" }, { label: "<?php echo __( 'Quarter', 'easyReservations'); ?> 2", value: "q2", category: "Quarters" }, { label: "<?php echo __( 'Quarter', 'easyReservations'); ?> 3", value: "q3", category: "Quarters" }, { label: "<?php echo __( 'Quarter', 'easyReservations'); ?> 4", value: "q4", category: "Quarters" },
			{ label: "<?php echo __( 'Year', 'easyReservations'); ?> 2010", value: "2010", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2011", value: "2011", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2012", value: "2012", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2013", value: "2013", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2014", value: "2014", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2015", value: "2015", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2016", value: "2016", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2017", value: "2017", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2018", value: "2018", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2019", value: "2019", category: "Years" }, { label: "<?php echo __( 'Year', 'easyReservations'); ?> 2020", value: "2020", category: "Years" },
			{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 1", value: "1", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 2", value: "2", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 3", value: "3", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 4", value: "4", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 5", value: "5", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 6", value: "6", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 7", value: "7", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 8", value: "8", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 9", value: "9", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 10", value: "10", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 11", value: "11", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 12", value: "12", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 13", value: "13", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 14", value: "14", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 15", value: "15", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 16", value: "16", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 17", value: "17", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 18", value: "18", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 19", value: "19", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 20", value: "20", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 21", value: "21", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 21", value: "21", category: "Calendar weeks" }, { label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 22", value: "22", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 23", value: "23", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 24", value: "24", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 25", value: "25", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 26", value: "26", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 27", value: "27", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 28", value: "28", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 29", value: "29", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 30", value: "30", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 31", value: "31", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 32", value: "32", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 33", value: "33", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 34", value: "34", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 35", value: "35", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 36", value: "36", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 37", value: "37", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 38", value: "38", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 39", value: "39", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 40", value: "40", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 41", value: "41", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 42", value: "42", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 43", value: "43", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 44", value: "44", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 45", value: "45", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 46", value: "46", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 47", value: "47", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 48", value: "48", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 49", value: "49", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 50", value: "50", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 51", value: "51", category: "Calendar weeks" },{ label: "<?php echo __( 'Calendar week', 'easyReservations'); ?> 52", value: "52", category: "Calendar weeks" },
		];

		function split( val ) {
			return val.split( /;\s*/ );
		}
	
		function extractLast( term ) {
			return split( term ).pop();
		}
	
		jQuery( "#price2, .easy-resource-condition-auto" ).keypress(function(event) {
			if ( event.which == 32 ) {
				//event.preventDefault();
				var theValue = jQuery(this).val();
				var lastChar = theValue.substr(theValue.length - 1);
				if(lastChar != ';')	jQuery(this).val(theValue+';'); 
				return false;
			}
		});
	
		jQuery( "#price2, .easy-resource-condition-auto" )
			// don't navigate away from the field on tab when selecting an item
			.bind( "keydown", function( event ) {
				if ( event.keyCode === jQuery.ui.keyCode.TAB &&
						jQuery( this ).data( "autocomplete" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				minLength: 0,
				source: function( request, response ) {
					// delegate back to autocomplete, but extract the last term
					response( jQuery.ui.autocomplete.filter(
						availableTags, extractLast( request.term ) ) );
				},
				focus: function() {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();

					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );

					this.value = terms.join( ";" );
					return false;
				}
			});
	});
}
</script>
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
} ?>