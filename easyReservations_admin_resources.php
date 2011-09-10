<?php

function reservation_resources_page(){ 
if($_GET['delete']){
	wp_delete_post($_GET['delete']);
}
if($_GET['room']){
	$resourceID=$_GET['room'];
	$site='rooms';
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
	$offer_cat = get_option("reservations_special_offer_cat");
	$room_category = get_option('reservations_room_category');

?><div id="icon-themes" class="icon32"></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;">Resources <a class="add-new-hari" href="admin.php?page=add-reservation" rel="simple_overlay" style="width:20px;heigth:29px;"href="#">Add New</a></h2><div id="wrap">
<?php
if($site=='' or $site=='main'){
$categoryids = array($room_category, $offer_cat);
if($room_category == '') echo '<b>'.__( 'Add and Set Room Post Category' , 'easyReservations' ).'</b><br>';
if($offer_cat == '') echo '<b>'.__( 'Add and Set Offer Post Category' , 'easyReservations' ).'</b>';

foreach($categoryids as $categoryid){
?>	<table class="widefat" style="width:99%">
			<thead>
				<tr>
					<th style="width:70px"> </th>
					<th nowrap><?php echo __( 'Title' , 'easyReservations' );?></th>
					<th nowrap><?php echo __( 'ID' , 'easyReservations' );?></th>
					<?php if($categoryid == $room_category){ ?><th nowrap><?php echo __( 'Quantity' , 'easyReservations' ); ?></th><?php } ?>
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

		$roomargs = array( 'type' => 'post', 'category' => $categoryid, 'orderby' => 'post_title', 'order' => 'ASC');
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
				$getfilters = spliti("\[|\] |\]", get_post_meta($allroom->ID, 'reservations_filter', true));
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
							<td style="text-align:center; vertical-align:middle;"><a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><?php echo get_the_post_thumbnail($allroom->ID, array(70,70)); ?></a></td>
							<td><?php echo '<b>'.__($allroom->post_title).'</b>'; ?></td>
							<td><?php echo '<b>'.$allroom->ID.'</b>'; ?></td>
							<?php if(reservations_is_room($allroom->ID)){ ?><td><?php echo get_post_meta($allroom->ID, 'roomcount', true); ?></td><?php } ?>
							<td style="text-align:right;width:100px" nowrap><?php echo $price;?></td>
							<td style="text-align:center;width:85px" nowrap><?php echo $countallrooms; ?></td>
							<td nowrap><?php echo $get_filters; ?></td>
							<?php if(reservations_is_room($allroom->ID)){ ?><td nowrap><?php echo $status; ?></td><?php } ?>
							<td style="width:150px"><?php echo substr($allroom->post_content, 0, 36); ?></td>
							<td style="text-align:right">
								<a href="post.php?post=<?php echo $allroom->ID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a>
								<a href="admin.php?page=reservation-resources&room=<?php echo $allroom->ID;?>" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"></a>
								<a href="<?php echo get_permalink( $allroom->ID ); ?>" target="_blank" title="<?php echo __( 'view' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/page_white_link.png"></a>
								<a href="admin.php?page=reservation-resources&delete=<?php echo $allroom->ID;?>" title="<?php echo __( 'delete' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/trash.png"></a>
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
</table></div><br>
<?php
}
	} elseif($site=='rooms'){
		if(isset($_POST['action'])) {
			$action=$_POST['action'];
		}

		if(reservations_is_room($resourceID)) $roomoroffer='room'; else $roomoroffer='offer';

		if($action=='set_filter'){
			$filterpost=$_POST['reservations_filter'];
			$replacefilter=str_replace(",", ".", $filterpost);
			update_post_meta($resourceID,'reservations_filter', $replacefilter);
		}

		if($action=='set_groundprice'){
			if($roomoroffer == "room"){
			$gpricepost=$_POST['groundprice'];
			$replacegprice=str_replace(",", ".", $filterpost);
			} elseif ($roomoroffer == "offer"){
				for($countits=1; $countits <=  $_POST['countrooms']; $countits++){
					$thegpgrice.= $_POST['idgproom'.$countits].':'.$_POST['gppriceroom'.$countits];
					if($countits !=  $_POST['countrooms']) $thegpgrice.='-';
				}
				$replacegprice=str_replace(",", ".", $thegpgrice);
			}
			update_post_meta($resourceID,'reservations_groundprice', $replacegprice);
		}

		$argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
		$roomcategories = get_posts( $argss );
		$counroooms=0;
		$count=count($roomcategories);
		foreach( $roomcategories as $roomcategorie ){
			$wassetted=0;
			$counroooms++;
			$roomsoptions .= '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
			$roomjsarray .= '<b>'.$roomcategorie->post_title.'</b>: <input type="hidden" name="idroom'.$counroooms.'" id="idroom'.$counroooms.'" value="'.$roomcategorie->ID.'"><input type="Text" id="priceroom'.$counroooms.'" name="priceroom'.$counroooms.'" value="Price" style="width:73px"> ';

			$explgpprices=explode("-", get_post_meta($resourceID, 'reservations_groundprice', true));
			foreach($explgpprices as $explgpprice){
				$explidgpprice=explode(":", $explgpprice);
				if($explidgpprice[0] == $roomcategorie->ID){
					$roomgpadd .= $roomcategorie->post_title.': <input type="hidden" name="idgproom'.$counroooms.'" id="idgproom'.$counroooms.'" value="'.$roomcategorie->ID.'"><input type="Text" id="gppriceroom'.$counroooms.'" name="gppriceroom'.$counroooms.'" value="'.$explidgpprice[1].'" style="width:73px"><br>';
					$wassetted++;
				}
			}
			if( $wassetted == 0) $roomgpadd .= $roomcategorie->post_title.': <input type="hidden" name="idgproom'.$counroooms.'" id="idgproom'.$counroooms.'" value="'.$roomcategorie->ID.'"><input type="Text" id="gppriceroom'.$counroooms.'" name="gppriceroom'.$counroooms.'" value="Price" style="width:73px"><br>';
			$roomsadd .= "document.getElementById('idroom".$counroooms."').value+':'+document.getElementById('priceroom".$counroooms."').value+";
			if($counroooms!=$count) $roomsadd .= "'-'+";
		}
		$args2 = array( 'type' => 'post', 'category' => $offer_cat, 'orderby' => 'post_title', 'order' => 'ASC');
		$specialcategories = get_posts( $args2 );
		foreach( $specialcategories as $specialcategorie ){
			$offeroptions .= '<option value="'.$specialcategorie->ID.'">'.__($specialcategorie->post_title).'</option>';
		}

		for($counts=1; $counts < 100; $counts++){
			$personsoptions .= '<option value="'.$counts.'">'.$counts.'</option>';
		}
		
		if($roomoroffer == "room"){  
			$groundpricefield='<input type="text" value="'.get_post_meta($resourceID, 'reservations_groundprice', true).'" name="groundprice"> &'.get_option("reservations_currency").';';
			$gprice='<b>'.__( 'Groundprice' , 'easyReservations' ).': </b>'.reservations_format_money(get_post_meta($resourceID, 'reservations_groundprice', true)).' &'.get_option("reservations_currency").';'; 
		} else {
			if(!preg_match("/^[0-9]+.[0-9]{1,2}$/", get_post_meta($resourceID, 'reservations_groundprice', true))){
				$gprice='';
				$explprices=explode("-", get_post_meta($resourceID, 'reservations_groundprice', true));
				foreach($explprices as $explprice){
					$explidprice=explode(":", $explprice);
					$gprice.='<b>'.__(get_the_title($explidprice[0])).':</b> '.reservations_format_money($explidprice[1]).' &'.get_option("reservations_currency").';<br>';
				}
			} else {
				$gprice='<b>'.__( 'Groundprice' , 'easyReservations' ).': </b>'.reservations_format_money(get_post_meta($resourceID, 'reservations_groundprice', true)).' &'.get_option("reservations_currency").';'; 
			}
			$groundpricefield = $roomgpadd;
		}
  ?>
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
    document.getElementById("price1").value+'-'+
    document.getElementById("price2").value+' '+
    document.getElementById("zwei").value+']';
}
function AddPriceOffer(){ // Add field to the Form
	document.getElementById("reservations_filter").value =
    document.getElementById("reservations_filter").value +
    '['+document.getElementById("jumpmenu").value+' '+
    document.getElementById("price1").value+'-'+
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
	thetext1 = false;
	thetext2 = false;
	thetext3 = false;
	thetext4 = false;
}

function jumpto(x){ // Chained inputs;

	var end = 0;

	if(thetext1 == false){
		if (x == "pers") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="personcond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		}
		if (x == "stay") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="staycond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		}
		if (x == "loyal") {
			var Output  = '<select name="eins" id="eins" onChange="jumpto(document.form1.eins.options[0].value)"><option value="loyalcond">Condition</option><?php echo $personsoptions; ?></select>';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		}
		if (x == "avail") {
			var Output  = '<input type="text" name="eins" id="avail1" value="dd.mm.yyyy" style="width:73px"> - <input type="text" name="zwei" id="avail2" value="dd.mm.yyyy" style="width:73px">';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
			var Output  = '&nbsp;<a href="javascript:AddAvail()"><b>Add</b></a>';
			document.getElementById("Text4").innerHTML += Output;
		}
		if (x == "price") {
			var Output  = '<input type="text" name="eins" id="price1" value="dd.mm.yyyy" style="width:73px"> - <input type="text" name="zwei" id="price2" value="dd.mm.yyyy" style="width:73px" onClick="jumpto(document.form1.price2.id)"> ';
			document.getElementById("Text").innerHTML += Output;
			thetext1 = true;
			document.form1.jumpmenu.disabled=true;
		}
	}
	if(thetext2 == false){
		if (x == "personcond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price">';
			document.getElementById("Text2").innerHTML += Output;
			thetext2 = true;
			document.form1.eins.disabled=true;
		}
		if (x == "staycond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price">';
			document.getElementById("Text2").innerHTML += Output;
			thetext2 = true;
			document.form1.eins.disabled=true;
		}
		if (x == "loyalcond") {
			end = 3;
			var Output  = '<input type="text" name="zwei" id="zwei" value="Price">';
			document.getElementById("Text2").innerHTML += Output;
			thetext2 = true;
			document.form1.eins.disabled=true;
		}
		if (x == "price2") {

			if (roomoffer == "offer") {
				var zahlrooms = 0;
				var Output  = '';
				end = 7;
				Output  += odas;
			}
			if (roomoffer == "room") {
				end = 6;
				var Output  = '<input type="text" name="zwei" id="zwei" value="Price">';
			}

			document.getElementById("Text2").innerHTML += Output;
			thetext2 = true;
		}
	}
	if(thetext3 == false){
		if (x == "Name") {
			end = 4;
			var Output  = '<input type="text" name="drei" id="drei" value="Options">';
			document.getElementById("Text3").innerHTML += Output;
			thetext3 = true;
		}
	}
	if (end == 1) {
		var Output  = '&nbsp;<a href="javascript:AddOne()" ><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png"></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 2) {
		var Output  = '&nbsp;<a href="javascript:AddTwo()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 3) {
		var Output  = '&nbsp;<a href="javascript:AddThree()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 4) {
		var Output  = '&nbsp;<a href="javascript:AddFour()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 5) {
		var Output  = '&nbsp;<a href="javascript:AddAvail()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 6) {
		var Output  = '&nbsp;<a href="javascript:AddPriceRoom()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
	if (end == 7) {
		var Output  = '&nbsp;<a href="javascript:AddPriceOffer()"><b>Add</b></a>';
		document.getElementById("Text4").innerHTML += Output;
	}
}
</script>
 
<div id="boxes">

<div id="dialog" class="window">
Simple Modal Windowsaw | 
<a href="#"class="close"/>Close it</a>
</div>
<!-- Mask to cover the whole screen -->
  <div id="mask"></div>
</div>
		<table>
			<tr>
				<td valign="top">
					<table class="widefat" style="width:750px">
							<thead>
								<tr>
									<th colspan="2"><b><?php echo __(get_the_title($resourceID)); ?></b><div style="float:right"><a href="post.php?post=<?php echo $resourceID; ?>&action=edit" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a></div></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="width:70px;" valign="top"><?php echo get_the_post_thumbnail($resourceID, array(70,70)); ?></td>
									<td><?php $roomargs = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'id' => $resourceID); 
									$allrooms = get_post( $resourceID );
									echo $allrooms->post_content;						
									?></td>
								</tr>
							</tbody>
					</table>
					<table class="widefat" style="width:750px;margin-top:7px">
						<thead>
							<tr>
								<th><b><?php echo __( 'Filters' , 'easyReservations' ); ?></b></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<form id="form1" name="form1">
										<div style="float: left;">
											<select name="jumpmenu" id="jumpmenu" onChange="jumpto(document.form1.jumpmenu.options[document.form1.jumpmenu.options.selectedIndex].value)">
												<option>Add Field</option>
												<option value="price">Price</option>
												<option value="pers">Persons</option>
												<option value="stay">Stay</option>
												<option value="loyal">Loyal</option>
												<option value="avail">Availibility</option>
											</select>
										</div>
										<div id="Text" style="float: left;"></div>
										<div id="Text2" style="float: left;"></div>
										<div id="Text3" style="float: left;"></div>
										<div id="Text4" style="float: left;"></div> 
										&nbsp;<a href="javascript:resetform();" style="vertical-align:text-bottom;">Reset</a>
									</form>
								<br>
								<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_filter" name="set_filter">
								<input type="hidden" name="action" id="action" value="set_filter">
								<textarea style="width:100%;font: 18px #000000; background:#FFFFE0; border: 1px solid #E6DB55; margin-bottom:4px;"  id="reservations_filter" name="reservations_filter" ><?php echo get_post_meta($resourceID, 'reservations_filter', true); ?></textarea><br>
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
											$get_filters .= '<b style="background:'.$bgcolor.';padding:1px;white-space: nowrap" >['.$filterout.'] <a href="admin.php?page=reservation-resources&room='.$resourceID.'&deletefilter='.$resourceID.'&thefilter='.$numberoffilter.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a></b>, ';
										}
										echo $get_filters; ?>
										<a style="float:right" href="javascript:{}" onclick="document.getElementById('set_filter').submit(); return false;" class="button-primary"><span><?php printf ( __( 'Save Filters' , 'easyReservations' ));?></span>
										</form></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>
				</td>
				<td>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php printf ( __( 'Groundprice' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<?php echo $gprice; ?>
									<form method="post" action="admin.php?page=reservation-resources&room=<?php echo $resourceID; ?>"  id="set_groundprice" name="set_groundprice">
									<input type="hidden" name="action" id="action" value="set_groundprice"><input type="hidden" name="countrooms" id="countrooms" value="<?php echo $counroooms; ?>">
									<?php echo $groundpricefield;?>
									<a style="float:right" href="javascript:{}" onclick="document.getElementById('set_groundprice').submit(); return false;" class="button-secondary"><span><?php printf ( __( 'Set' , 'easyReservations' ));?></span>
									</form>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="widefat" style="margin-top:7px">
						<thead>
							<tr>
								<th><?php printf ( __( 'Filter Help' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="explainbox" style='border:solid 1px #FFF141; padding: 3px; width:98%;" background:#FFFDFD;'>
									  <p><code>[price]</code> <small>Set Price for specific Time Period</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>dd.mm.yyyy-dd.mm.yyyy</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Price in the selected Period</small></p>
									  <p><code>[stay]</code> <small>Set Discount for longer Stays</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>minimum Stay in Days</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Discount (XX or XX%)</small></p>
									  <p><code>[loyal]</code> <small>Set Discount for recurring Guests</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>Visits to have for Discount</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Discount (XX or XX%)</small></p>
									  <p><code>[pers]</code> <small>Set Discount for more Persons</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>Number of Persons for Discount</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Discount (XX or XX%)</small></p>
									  <p><code>[avail]</code> <small>Set unavailable for period of Time</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>dd.mm.yyyy-dd.mm.yyyy</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>empty</small></p>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
<?php	}
} ?>