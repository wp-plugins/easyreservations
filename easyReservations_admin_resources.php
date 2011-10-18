<?php
function reservation_resources_page(){ 

$offer_cat = get_option("reservations_special_offer_cat");
$room_category = get_option('reservations_room_category');

if($_GET['delete']){
	wp_delete_post($_GET['delete']);
}
if($_GET['room']){
	$resourceID=$_GET['room'];
	$site='rooms';
}
if(isset($_POST['thecontent'])){
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
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Resource added' , 'easyReservations' ).'</p></div>';
	} else $prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Please enter a Title' , 'easyReservations' ).'</p></div>';
}
if(isset($_GET['addresource'])){
	$addresource=$_GET['addresource'];
	$site='addresource';
}
if(isset($_GET['site'])){
	$site=$_GET['site'];
}
if(isset($_GET['deletefilter'])){
	$deletefilter=$_GET['deletefilter'];
	$thefilter=$_GET['thefilter'];
	$filters = spliti("\[|\] |\]", get_post_meta($deletefilter, 'reservations_filter', true));
	$filterouts=array_values(array_filter($filters)); //make array out of filters
	asort($filterouts);
	$dienum=0;
		foreach($filterouts as $filter){
			$dienum++;
			if($dienum==$thefilter) $thedeletefilter=$filter;
		}
	$deletedfilter=str_replace('['.$thedeletefilter.']', '', get_post_meta($deletefilter, 'reservations_filter', true));
	update_post_meta($deletefilter,'reservations_filter',$deletedfilter);
}

?><div id="icon-index" class="icon32"></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 18px;">Resources<div id="wrap"><br>
<?php
if($site=='' or $site=='main'){
$categoryids = array($room_category, $offer_cat);
if($room_category == '') echo '<b>'.__( 'Add and Set Room Post Category' , 'easyReservations' ).'</b><br>';
if($offer_cat == '') echo '<b>'.__( 'Add and Set Offer Post Category' , 'easyReservations' ).'</b>';

foreach($categoryids as $categoryid){

	if($categoryid == $room_category){ 
		$roomoroffer=__( 'Rooms' , 'easyReservations' );
		$roo = 'room'; 
	} else { 
		$roomoroffer=__( 'Offers' , 'easyReservations' ); 
		$roo = 'offer'; 
	
}
?> <table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-bottom:5px;">
			<thead>
				<tr>
					<th style="min-width:72px"><?php echo $roomoroffer; ?> <a href="admin.php?page=reservation-resources&addresource=<?php echo $roo; ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png"></a></th>
					<th nowrap><?php echo __( 'Title' , 'easyReservations' );?></th>
					<th nowrap><?php echo __( 'ID' , 'easyReservations' );?></th>
					<?php if($categoryid == $room_category){ ?><th style="text-align:center;" nowrap><?php echo __( 'Quantity' , 'easyReservations' ); ?></th><?php } ?>
					<th style="text-align:right" nowrap><?php echo __( 'Base Price' , 'easyReservations' ); ?></th>
					<th nowrap><?php echo __( 'Reservations' , 'easyReservations' ); ?></th>
					<th nowrap><?php echo __( 'Filter' , 'easyReservations' ); ?></th>
					<?php if($categoryid == $room_category){ ?><th nowrap><?php echo __( 'Status' , 'easyReservations' ); ?></th><?php } ?>
					<th nowrap><?php echo __( 'Excerpt' , 'easyReservations' ); ?></th>
					<th nowrap></th>
				</tr>
			</thead>
			<tbody>
<?php
		global $wpdb;

		$roomargs = array(  'post_status' => 'publish|private', 'type' => 'post', 'category' => $categoryid, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => -1);
		$allrooms = get_posts( $roomargs );
		$countallrooms=count($allrooms);
		if($categoryid == $room_category){
			if($countallrooms == 0) echo __( 'add Post to Room Category to add a Room' , 'easyReservations' );
		} else {
			if($countallrooms == 0) echo  __( 'add Post to Offer Category to add an Offer' , 'easyReservations' );
		}
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
				$numberoffilter++; //count filters
				$filtertype=explode(" ", $filterout);
				if($filtertype[0]=="price") $bgcolor='#FC7876';
					elseif($filtertype[0]=="stay") $bgcolor='#76AEFC';
					elseif($filtertype[0]=="pers") $bgcolor='#85E4FC';
					elseif($filtertype[0]=="avail") $bgcolor='#81FC76';
					elseif($filtertype[0]=="loyal") $bgcolor='#FCF776';
					$get_filters .= '<b style="background:'.$bgcolor.';padding:1px">['.$filterout.']</b> <a href="admin.php?page=reservation-resources&deletefilter='.$allroom->ID.'&thefilter='.$num.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a><br>';
					$num++;
				}
				if(reservations_is_room($allroom->ID)){ 
					$price=reservations_format_money(get_post_meta($allroom->ID, 'reservations_groundprice', true)).' &'.get_option("reservations_currency").';'; 
				} else {
					if(!preg_match("/^[0-9]+.[0-9]{1,2}$/", get_post_meta($allroom->ID, 'reservations_groundprice', true))){
						$price='';
						$explprices=explode("-", get_post_meta($allroom->ID, 'reservations_groundprice', true));
						foreach($explprices as $explprice){
							$explidprice=explode(":", $explprice);
							$price.='<b>'.__(get_the_title($explidprice[0])).':</b> '.reservations_format_money($explidprice[1]).' &'.get_option("reservations_currency").';<br>';
						}
					} else {
						$price=reservations_format_money(get_post_meta($allroom->ID, 'reservations_groundprice', true)).' &'.get_option("reservations_currency").';'; 
					}
				}
				if(reservations_check_availibility_for_room($allroom->ID, date("d.m.Y", time())) > get_post_meta($allroom->ID, 'roomcount', true)) $status='Full ('.reservations_check_availibility_for_room($allroom->ID, date("d.m.Y", time())).'/'.get_post_meta($allroom->ID, 'roomcount', true).')'; 
				else $status='Empty ('.reservations_check_availibility_for_room($allroom->ID, date("d.m.Y", time())).'/'.get_post_meta($allroom->ID, 'roomcount', true).')'; 
				if(reservations_is_room($allroom->ID)){ $countallrooms = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$allroom->ID'")); } // number of total rows in the database				
				else { $countallrooms = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='$allroom->ID'")); } // number of total rows in the database				
					?><tr class="<?php echo $class; ?>">
							<td style="text-align:center; vertical-align:middle;"><?php if(function_exists('get_the_post_thumbnail')){ ?><a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><?php echo get_the_post_thumbnail($allroom->ID, array(70,70)); ?></a><?php } ?></td>
							<td><?php echo '<b>'.__($allroom->post_title).'</b>'; ?></td>
							<td><?php echo '<b>'.$allroom->ID.'</b>'; ?></td>
							<?php if(reservations_is_room($allroom->ID)){ ?><td style="text-align:center;"><?php echo get_post_meta($allroom->ID, 'roomcount', true); ?></td><?php } ?>
							<td style="text-align:right;width:100px" nowrap><?php echo $price;?></td>
							<td style="text-align:center;width:85px" nowrap><?php echo $countallrooms; ?></td>
							<td nowrap><?php echo $get_filters; ?></td>
							<?php if(reservations_is_room($allroom->ID)){ ?><td nowrap><?php echo $status; ?></td><?php } ?>
							<td style="width:150px"><?php echo substr($allroom->post_content, 0, 36); ?></td>
							<td style="text-align:right">
								<a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit Post' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a>
								<a href="admin.php?page=reservation-resources&room=<?php echo $allroom->ID;?>" title="<?php echo __( 'edit' , 'easyReservations' ); if($categoryid == $room_category) echo ' '. __( 'Room' , 'easyReservations' ); else echo ' '. __( 'Offer' , 'easyReservations' );?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"></a>
								<a href="<?php echo get_permalink( $allroom->ID ); ?>" target="_blank" title="<?php echo __( 'view' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/page_white_link.png"></a>
								<a href="admin.php?page=reservation-resources&delete=<?php echo $allroom->ID;?>" title="<?php echo __( 'trash & delete' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/trash.png"></a>
							</td>
						</tr><?php
						/*for($countexactroom=1; get_post_meta($allroom->ID, 'roomcount', true) >= $countexactroom; $countexactroom++){
							$exactlyroomquerie = "SELECT name FROM ".$wpdb->prefix."reservations WHERE room='$allroom->ID' AND roomnumber='$countexactroom' AND approve='yes' AND NOW() BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ";
							$exactlyroomcount = mysql_num_rows(mysql_query($exactlyroomquerie));
							$exactlyresult = $wpdb->get_results($exactlyroomquerie);
							?><tr style="background:#fff">
							<td colspan="2"><?php echo __($allroom->post_title).' '.$countexactroom; ?></td>
							<td colspan="2"><?php if($exactlyroomcount > 0) echo 'Full'; else echo 'Empty';?></td>
							<td colspan="2"><?php if($exactlyroomcount > 0) echo 'Guest: '.$exactlyresult[0]->name; ?></td>
							</tr><?php
						}*/
			}
?>
</tbody>
</table>
<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
	} elseif($site=='rooms'){
		if(isset($_POST['action'])) {
			$action=$_POST['action'];
			$action2=$_POST['action2'];
		}

		if(reservations_is_room($resourceID)) $roomoroffer='room'; else $roomoroffer='offer';

		if($action=='set_filter'){
			$filterpost=$_POST['reservations_filter'];
			$replacefilter=str_replace(",", ".", $filterpost);
			update_post_meta($resourceID,'reservations_filter', $replacefilter);
		}

		if($action=='set_groundprice'){
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
			else $prompt='<div style="width: 98%; padding: 1px 3px 1px 1px; margin: -7px 3px 5px 0;" class="error below-h2"><p>&nbsp;'.__( 'Insert right Money format' , 'easyReservations' ).'</p></div>';

			if($action2=='set_roomcount'){
				$reservations_room_count=$_POST['roomcount'];
				$reservations_current_room_count = get_post_meta($resourceID, 'roomcount', TRUE);
				if ($reservations_current_room_count){
					if($reservations_room_count == "")  delete_post_meta($resourceID,'roomcount');
					else update_post_meta($resourceID,'roomcount',$reservations_room_count);
				} elseif($reservations_room_count != ""){
					add_post_meta($resourceID, 'roomcount', $reservations_room_count, TRUE);
				}
			}
		} elseif($action=='set_infobox'){
			$reservations_percent=$_POST['pricebox'];
			$reservations_from_to=$_POST['fromtobox'];
			$reservations_short=$_POST['descbox'];
			$reservations_current_value_fromto = get_post_meta($resourceID, 'reservations_fromto', TRUE);
			$reservations_current_value_percent = get_post_meta($resourceID, 'reservations_percent', TRUE);
			$reservations_current_value_short = get_post_meta($resourceID, 'reservations_short', TRUE);

			if ($reservations_current_value_percent) {
				if($reservations_percent == "")  delete_post_meta($resourceID,'reservations_percent');
				else update_post_meta($resourceID,'reservations_percent',$reservations_percent);
			} elseif($reservations_percent != "") {
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

		$argss = array( 'post_status' => 'publish|private', 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
		$roomcategories = get_posts( $argss );
		$counroooms=0;
		$count=count($roomcategories);
		foreach( $roomcategories as $roomcategorie ){
			$wassetted=0;
			$counroooms++;
			$roomsoptions .= '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
			$roomjsarray .= ' <b> '.__($roomcategorie->post_title).'</b>:<input type="hidden" name="idroom'.$counroooms.'" id="idroom'.$counroooms.'" value="'.$roomcategorie->ID.'"><input type="text" id="priceroom'.$counroooms.'" name="priceroom'.$counroooms.'" value="Price" style="width:55px;text-align:right">';

			$explgpprices=explode("-", get_post_meta($resourceID, 'reservations_groundprice', true));
			
			foreach($explgpprices as $num=>$explgpprice){
				$explidgpprice=explode(":", $explgpprice);
				if($explidgpprice[0] == $roomcategorie->ID){
					if(is_int(($num+1)/2)) $class='alternate'; else $class='';
					$roomgpadd .= '<tr class="'.$class.'"><td>'.__($roomcategorie->post_title).': <input type="hidden" name="idgproom'.$counroooms.'" id="idgproom'.$counroooms.'" value="'.$roomcategorie->ID.'"></td><td style="text-align:right;"><input type="Text" id="gppriceroom'.$counroooms.'" name="gppriceroom'.$counroooms.'" value="'.$explidgpprice[1].'" style="width:60px;margin-top:-2px;text-align:right"> &'.get_option("reservations_currency").';</td></tr>';
					$wassetted++;
				}
			}
			
			if( $wassetted == 0) $roomgpadd .= '<tr class="'.$class.'"><td>'.__($roomcategorie->post_title).': <input type="hidden" name="idgproom'.$counroooms.'" id="idgproom'.$counroooms.'" value="'.$roomcategorie->ID.'"></td><td style="text-align:right;"><input type="Text" id="gppriceroom'.$counroooms.'" name="gppriceroom'.$counroooms.'" value="Price" style="width:60px"></td></tr>';
			$roomsadd .= "document.getElementById('idroom".$counroooms."').value+':'+document.getElementById('priceroom".$counroooms."').value+";
			if($counroooms!=$count) $roomsadd .= "'-'+";
		}
		$args2 = array( 'type' => 'post', 'post_status' => 'publish|private', 'category' => $offer_cat, 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => -1);
		$specialcategories = get_posts( $args2 );
		foreach( $specialcategories as $specialcategorie ){
			$offeroptions .= '<option value="'.$specialcategorie->ID.'">'.__($specialcategorie->post_title).'</option>';
		}

		for($counts=1; $counts < 100; $counts++){
			$personsoptions .= '<option value="'.$counts.'">'.$counts.'</option>';
		}
		
		if($roomoroffer == "room"){  
			$groundpricefield='<tr><td><b>'.__( 'Groundprice' , 'easyReservations' ).':</b></td><td style="text-align:right;"><input type="text" value="'.get_post_meta($resourceID, 'reservations_groundprice', true).'" style="width:60px;text-align:right" name="groundprice"> &'.get_option("reservations_currency").';</td></tr>';
			$gprice='<b>'.__( 'Groundprice' , 'easyReservations' ).': </b>'.reservations_format_money(get_post_meta($resourceID, 'reservations_groundprice', true)).' &'.get_option("reservations_currency").';'; 
		} else {
			if(!preg_match("/^[0-9]+.[0-9]{1,2}$/", get_post_meta($resourceID, 'reservations_groundprice', true))){
				$gprice='';
				$explprices=explode("-", get_post_meta($resourceID, 'reservations_groundprice', true));
				foreach($explprices as $explprice){
					$explidprice=explode(":", $explprice);
					$gprice.='<b>'.__(get_the_title($explidprice[0])).':</b> '.reservations_format_money($explidprice[1]).' &'.get_option("reservations_currency").'; ';
				}
			} else {
				$gprice='<b>'.__( 'Groundprice' , 'easyReservations' ).': </b>'.reservations_format_money(get_post_meta($resourceID, 'reservations_groundprice', true)).' &'.get_option("reservations_currency").';'; 
			}
			$groundpricefield = $roomgpadd;
		}

		$reservations_current_value_fromto = get_post_meta($resourceID, 'reservations_fromto', TRUE);
		$reservations_current_room_count = get_post_meta($resourceID, 'roomcount', TRUE);
		$reservations_current_value_percent = get_post_meta($resourceID, 'reservations_percent', TRUE);
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
									<td style="width:90px;" valign="top"><?php if(function_exists('get_the_post_thumbnail')) echo get_the_post_thumbnail($resourceID, array(90,90)); ?><br><?php echo __( 'Status' , 'easyReservations' ).': <b>'; echo __($allrooms->post_status).'</b><br>'; echo __( 'Comments' , 'easyReservations' ).': <b>'; echo __($allrooms->comment_count).'</b>'; ?></td>
									<td><?php echo __($allrooms->post_content); ?></td>
								</tr>
							</tbody>
					</table>
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
												<option>Add Filter</option>
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

									<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_filter" name="set_filter">
									<input type="hidden" name="action" id="action" value="set_filter">
									<textarea style="width:100%;font: 18px #000000; background:#FFFFE0; border: 1px solid #E6DB55; margin:3px 0 4px 0;"  id="reservations_filter" name="reservations_filter" onChange="addSaveButton()"><?php echo get_post_meta($resourceID, 'reservations_filter', true); ?></textarea><br>
									<?php 	$getfilters = spliti("\[|\] |\]", get_post_meta($resourceID, 'reservations_filter', true));
											$filterouts=array_values(array_filter($getfilters)); //make array out of filters
											$countfilter=count($filterouts);// count the filter-array element
											asort($filterouts);
											$get_filters='';
											$numberoffilter=0;
											foreach($filterouts as $filterout){ //foreach filter array
												$numberoffilter++; //count filters
												$filtertype=explode(" ", $filterout);
												if($filtertype[0]=="price") $bgcolor='#FC7876';
												elseif($filtertype[0]=="stay") $bgcolor='#76AEFC';
												elseif($filtertype[0]=="avail") $bgcolor='#81FC76';
												elseif($filtertype[0]=="pers") $bgcolor='#85E4FC';
												elseif($filtertype[0]=="loyal") $bgcolor='#FCF776';
												$get_filters .= '<b style="background:'.$bgcolor.';padding:1px;white-space: nowrap" >['.$filterout.']</b> <a href="admin.php?page=reservation-resources&room='.$resourceID.'&deletefilter='.$resourceID.'&thefilter='.$numberoffilter.'"><img style="vertical-align:text-middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a>, ';
											}
											echo $get_filters; ?>
										<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_filters').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Set Filters' , 'easyReservations' ));?>">
								</form></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>
				</td>
				<td style="width:35%" valign="top">
					<table class="<?php echo RESERVATIONS_STYLE; ?>">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Groundprice' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_groundprice" name="set_groundprice">
							<input type="hidden" name="action" id="action" value="set_groundprice"><input type="hidden" name="countrooms" id="countrooms" value="<?php echo $counroooms; ?>">
									<?php echo $groundpricefield;?>
							<?php if($roomoroffer == "room"){ ?><tr class="alternate"><td><input type="hidden" name="action2" id="action2" value="set_roomcount"><b><?php printf ( __( 'Roomcount' , 'easyReservations' ));?>:</b></td><td style="text-align:right;"><input type="text" name="roomcount" style="width:30px;" value="<?php echo $reservations_current_room_count; ?>" style="margin: 2px;"></td></tr><?php } ?>
							</form>
						</tbody>
					</table>
					<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_groundprice').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>">
				<?php if($roomoroffer == "offer"){ ?>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Offer Box Informations' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_infobox" name="set_infobox">
								<input type="hidden" name="action" id="action" value="set_infobox">
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
					<input type="button" style="float:right;margin-top:3px" onclick="document.getElementById('set_infobox').submit(); return false;" class="easySubmitButton-secondary" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>">
				<?php } ?>
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:7px">
						<thead>
							<tr>
								<th><?php printf ( __( 'Filter Help' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div id="Helper"></div><div id="Helper2"></div><div id="Helper3"></div>
									<div class="explainbox" style='border:solid 1px #FFF141;" background:#FFFDFD;'>
									  <p><code>[price]</code> <small><?php printf ( __( 'Set Price for specific Time Period' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'Time Period' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Price in the selected Period' , 'easyReservations' ));?></small></p>
									  <div class="explfakehr"></div><p><code>[stay]</code> <small><?php printf ( __( 'Set Discount for longer Stays' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'minimum Stay in days' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[loyal]</code> <small><?php printf ( __( 'Set Discount for recurring Guests' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'Visits to have for Discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[early]</code> <small><?php printf ( __( 'Set early bird discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'minimum days between reservation and arrival for Discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[pers]</code> <small><?php printf ( __( 'Set Group-Discount for more Persons' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small><?php printf ( __( 'Number of Persons for Discount' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'Discount' , 'easyReservations' ));?> (XX&<?php echo get_option("reservations_currency"); ?>; <?php printf ( __( 'or' , 'easyReservations' ));?> XX%)</small></p>
									  <div class="explfakehr"></div><p><code>[avail]</code> <small><?php printf ( __( 'Make Resource unavailable for period of Time' , 'easyReservations' ));?></small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>dd.mm.yyyy-dd.mm.yyyy</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small><?php printf ( __( 'empty' , 'easyReservations' ));?></small></p>
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

function jumpto(x){ // Chained inputs;

	var end = 0;

	if(thetext1 == false){
		if (x == "pers") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="personcond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b><?php echo __( 'Select the Amount of needed Persons for Discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "stay") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="staycond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select the Amount of needed Nights for Discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "loyal") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="loyalcond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select the Amount of needed past approved Reservations with the same eMail for Discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "early") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="earlycond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b><?php echo __( 'Select the Amount of needed Days before reservation for Discount' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		} else if (x == "avail") {
			var Output  = '<input type="text" name="eins" id="avail1" value="dd.mm.yyyy" style="width:73px"> - <input type="text" name="zwei" id="avail2" value="dd.mm.yyyy" style="width:73px">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the period in which the Resource is unavailable' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>10.11.2012 - 20.12.2012</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Avail Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<a href="javascript:AddAvail()"><b>Add</b></a>';
			document.getElementById("Text4").innerHTML += Output;
		} else if (x == "price") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[document.form1.eins.options.selectedIndex].value)"><option>Condition</option><option value="daterange">Date Range</option><option value="date">Date</option><option value="unit">Unit of time</option></select> ';
			document.getElementById("Text").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Choose the Type of Condition for the Price Filter' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><b>Date Range</b> <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>01.06.2013 - 23.08.2003</code></i>';
				Help += '<br> &emsp; <i><b>Day</b> <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>10.05.2012</code></i>';
				Help += '<br> &emsp; <i><b>Unit of Time</b> <i><?php echo __( 'a recurring or fixed unit of Time' , 'easyReservations' ); ?> <code>monday;2011;jan</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Avail Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		}
	} else if (thetext2 == false) {
		if (x == "personcond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in Amount of Discount in Money or Percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>4587</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>58.24</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>11%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Persons Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "staycond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in Amount of Discount in Money or Percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>348</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>11.99</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>20%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Stay Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "earlycond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in Amount of Discount in Money or Percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>367</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>9844.76</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>45%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Loyal Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;
			
			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "loyalcond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price" style="width:50px">';
			document.getElementById("Text2").innerHTML += Output;
			
			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in Amount of Discount in Money or Percent' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>46</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>9874.7</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>3%</code></i>';
			Help += '<br><b>2. <?php echo __( 'Click on "Add" to add the Loyal Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
			document.getElementById("Helper").innerHTML = Help;
			
			thetext2 = true;
			document.form1.eins.disabled=true;
		} else if (x == "daterange"){
			var Output  = '<input type="text" name="zwei" id="price1" value="dd.mm.yyyy" style="width:73px"> - <input type="text" name="drei" id="price2" value="dd.mm.yyyy" style="width:73px" onClick="jumpto(document.form1.price2.id)"><input type="hidden" id="hidden" value="-">';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the Dates from the period in wich the Groundprice should be different' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>22.10.2011-10.01.2012</code></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			unitorrange = true;
			document.form1.eins.disabled=true;
		} else if (x == "date"){
			var Output  = '<input type="text" name="zwei" id="price1" value="dd.mm.yyyy" style="width:73px" onClick="jumpto(document.form1.price2.id)"><input type="hidden" id="price2" value=""><input type="hidden" id="hidden" value="">';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the Date in wich the Groundprice should be different' , 'easyReservations' ); ?></b>';
			Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>19.03.2013</code></i></div><br>';
			document.getElementById("Helper").innerHTML = Help;

			thetext2 = true;
			unitorrange = true;
			document.form1.eins.disabled=true;
		} else if (x == "unit") {
			var Output  = '<input type="text" name="zwei" id="price2" value="Condition" onClick="jumpto(document.form1.price2.id)"><input type="hidden" name="drei" id="price1" value=""><input type="hidden" name="vier" id="hidden" value=""> ';
			document.getElementById("Text2").innerHTML += Output;

			var Help = '<div class="explainbox"><b>1. <?php echo __( 'Type in the Unit of time in wich the Groundprice should be different' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <?php echo __( 'The Condition Field has to match ' , 'easyReservations' ); ?><b>option1;option2;option3 ...</b>';
				Help += '<br> &emsp; <b><?php echo __( 'Type of Units by Priority' , 'easyReservations' ); ?></b> <i>(date-range Filtes have always more priority)</i>';
				Help += '<br> &emsp; <i><b>Day</b> used if the Day to calculate is Weekday - (monday - sunday & week, weekend)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: monday <?php echo __( 'or' , 'easyReservations' ); ?> friday;sunday;tuesday <?php echo __( 'or' , 'easyReservations' ); ?> mon;fri;sun;sat</i>';
				Help += '<br> &emsp; <i><b>Calendarweek</b> used if Day to Calculate is in selected Calendarweek(s) [1 - 52]</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: 42 <?php echo __( 'or' , 'easyReservations' ); ?> 1;2;3;65</i>';
				Help += '<br> &emsp; <i><b>Month</b> used if Day to calculate is in selected Month(s) (january - december)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: march <?php echo __( 'or' , 'easyReservations' ); ?> january;february;apr;august <?php echo __( 'or' , 'easyReservations' ); ?> dez;nov;july;mai</i>';
				Help += '<br> &emsp; <i><b>Quarter</b> used if Day to calculate is in selected Quarter(s) (1 - 4)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: 1 <?php echo __( 'or' , 'easyReservations' ); ?> 2;3;1</i>';
				Help += '<br> &emsp; <i><b>Year</b> used if Day to calculate is in selected Year(s) (1970 - 2038)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: 2013 <?php echo __( 'or' , 'easyReservations' ); ?> 2011;2013;2014</i></div><br>';
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
				Help += '<b>1. <?php echo __( 'Type in the Unit of time in wich the Groundprice should be different' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <?php echo __( 'The Condition Field has to match ' , 'easyReservations' ); ?><b>option1;option2;option3 ...</b>';
				Help += '<br> &emsp; <b><?php echo __( 'Type of Units by Priority' , 'easyReservations' ); ?></b> <i>(date-range Filtes have always more priority)</i>';
				Help += '<br> &emsp; <i><b>Day</b> used if the Day to calculate is Weekday - (monday - sunday & week, weekend)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>monday</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>friday;sunday;tuesday</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>mon;fri;sun;sat</code></i>';
				Help += '<br> &emsp; <i><b>Calendarweek</b> used if Day to Calculate is in selected Calendarweek(s) [1 - 52]</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>42</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>1;2;3;65</code></i>';
				Help += '<br> &emsp; <i><b>Month</b> used if Day to calculate is in selected Month(s) (january - december)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>march</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>january;february;apr;august</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>dez;nov;july;mai</code></i>';
				Help += '<br> &emsp; <i><b>Quarter</b> used if Day to calculate is in selected Quarter(s) (1 - 4)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>q1</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>q2;quarter3;q1</code></i>';
				Help += '<br> &emsp; <i><b>Year</b> used if Day to calculate is in selected Year(s) (1970 - 2038)</i>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>2013</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>2011;2013;2014</code></i><br>';
			} else { 
				Help += '<b>1. <?php echo __( 'Type in the Dates from the period in wich the Groundprice should be different' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>22.10.2011-10.01.2012</code></i><br>';
			}
			
			if (roomoffer == "offer") {
				var zahlrooms = 0;
				var Output  = '';
				end = 7;
				Output  += odas;
				
				Help += '<b>2. <?php echo __( 'Type in the Price for each Room in the selected Period' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>5437</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>3.04</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>69%</code></i>';
			} else if (roomoffer == "room") {
				end = 6;
				var Output  = '<input type="text" name="drei" id="drei" value="Price" style="width:50px">';

				Help += '<b>2. <?php echo __( 'Type in the Price for the Room in the selected Period' , 'easyReservations' ); ?></b>';
				Help += '<br> &emsp; <i><?php echo __( 'for Example' , 'easyReservations' ); ?>: <code>143</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>585.78</code> <?php echo __( 'or' , 'easyReservations' ); ?> <code>50%</code></i>';
			}
			Help += '<br><b>3. <?php echo __( 'Click on "Add" to add the Price Filter to the Resource' , 'easyReservations' ); ?></b></div><br>';
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
 ?>
<form method="post" action="" name="addresource" id="addresource">
<input type="hidden" name="roomoroffer" value="<?php echo $addresource; ?>">
	<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:340px;">
		<thead>
			<tr>
				<th colspan="2"><?php echo __( 'Add' , 'easyReservations' ); echo ' '.$roomoroffer; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="alternate">
				<td colspan="2"><small><?php echo __( 'This will add a Post to the' , 'easyReservations' ).' '.$roomoroffer.' '.__( 'Category' , 'easyReservations' ).' ('.$cat.') '; ?><br><?php echo __( 'It will be Private and only visible in Forms and Admin' , 'easyReservations' ); ?></small></td>
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
</script>
<?php }
} ?>