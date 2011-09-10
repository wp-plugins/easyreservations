<?php
function reservation_main_page() {

	global $wpdb;
	$wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations DROP phone ") ); 
	//  Include the Pagination Function named Digg Style Pagination Class 
	include('pagination.class.php');

	if(isset($_POST['delete'])) {
		$post_delete=$_POST['delete'];
	}	
	if(isset($_POST['roomexactly'])) {
		$roomexactly=$_POST['roomexactly'];
	}
	if(isset($_POST['approve_message'])) {
		$approve_message=$_POST['approve_message'];
	}
	if(isset($_POST['sendmail'])) {
		$sendmail=$_POST['sendmail'];
	}
	if(isset($_POST['hasbeenpayed'])) {
		$hasbeenpayed=$_POST['hasbeenpayed'];
	}
	if(isset($_POST['approve'])) {
		$post_approve=$_POST['approve'];
	}
	if(isset($_POST['editthereservation'])){
		$editthereservation=$_POST['editthereservation'];
	}	
	if(isset($_POST['datepicker'])){
		$datepicker=$_POST['dateas'];
		$startchooser=round((strtotime($datepicker) - time())/60/60/24)+1;
	}

	if(isset($_GET['more'])) $moreget=$_GET['more'];
	if(isset($_GET['orderby'])) $orderby=$_GET['orderby'];
	if(isset($_GET['perpage'])) {
		$perpage=$_GET['perpage'];
		update_option("reservations_on_page",$perpage);
	}
	if(isset($_GET['addcustomfield'])) {
		$addcustomfield=$_GET['addcustomfield'];
	}
	if(isset($_GET['deletecustomfield'])) {
		$deletecustomfield=$_GET['deletecustomfield'];
	}
	if(isset($_GET['search'])) {
		$search=$_GET['search'];
	}
	if(isset($_GET['order'])) {
		$order=$_GET['order'];
	}
	if(isset($_GET['typ'])) {
		$typ=$_GET['typ'];
	}
	if(isset($_GET['approve'])) {
		$approve=$_GET['approve'];
	}
	if(isset($_GET['view'])) {
		$view=$_GET['view'];
	}
	if(isset($_GET['delete'])) {
		$delete=$_GET['delete'];
	}
	if(isset($_GET['edit'])) {
		$edit=$_GET['edit'];
	}

	if(isset($_GET['bulk'])) { // GET Bulk Actions

		if(isset($_GET['bulkArr'])) {

			$to=0;
			$listes=$_GET['bulkArr'];
			
			if($_GET['bulk']=="1"){ //  If Move to Trash 

				if(count($listes)  > "1" ) {
					foreach($listes as $liste) {

					$to++;
					$ids=$liste;
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='del' WHERE id='$ids' ") ); 	

				} } else { 
					$ids=$listes[0];
					$to++;
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='del' WHERE id='$ids' ") ); }

			if ($to!=1) { $linkundo=implode("&bulkArr[]=", $listes); } else { $linkundo=$liste; }
			if ($to==1) { $anzahl=__('Reservation', 'easyReservations'); } else { $anzahl=$to.' '.__('Reservations', 'easyReservations');  }
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' moved to Trash. <a href="admin.php?page=reservations&bulkArr[]='.$linkundo.'&bulk=2">Undo</a></p></div>';

			}
			if($_GET['bulk']=="2"){ //  If Undo Trashing

				if(count($listes)  > "1" ) { 
					foreach($listes as $liste){

						$ids=$liste;
						$to++;
						$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes' WHERE id='$ids' ") ); 	
					}
				}  else { 
					$ids=$listes[0];
					$to++;
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes' WHERE id='$ids' ") ); }


			if ($to==1) { $anzahl=__('Reservation', 'easyReservations'); } else { $anzahl=$to.' '.__('Reservations', 'easyReservations');  }
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' restored from the Trash.</p></div>';

			}
			if($_GET['bulk']=="3"){ //  If Delete Permanently 

				if(count($listes)  > "1" ) { 
					foreach($listes as $liste){
						$ids=$liste;
						$to++;
						$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE id='$ids'  ") );	
					}
				} else { 
					$ids=$listes[0];
					$to++;
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE id='$ids' ") );	
				}

			if ($to==1) { $anzahl=__('Reservation', 'easyReservations'); } else { $anzahl=$to.' '.__('Reservations', 'easyReservations');  }
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;"  class="updated below-h2"><p>'.$anzahl.' '.__('deleted permanently', 'easyReservations').'</p></div>';
			}
		}
	}
	
	if($addcustomfield){
			global $wpdb;
			$sql_customquerie = "SELECT custom FROM ".$wpdb->prefix ."reservations WHERE id='$addcustomfield' LIMIT 1";
			$customquerie = $wpdb->get_results($sql_customquerie );
			$presentcustom=$customquerie[0]->custom;

			$addthecustomf=$presentcustom.$_POST['customtitle'].'&:&'.$_POST['customvalue'].'&;&';
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$addthecustomf' WHERE id='$addcustomfield' "));
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Custom Field added' , 'easyReservations' ).'</p></div>';
	}
	
	if($deletecustomfield){
			global $wpdb;
			$sql_customquerie = "SELECT custom FROM ".$wpdb->prefix ."reservations WHERE id='$edit' LIMIT 1";
			$customquerie = $wpdb->get_results($sql_customquerie );
			$explthecustom=explode("&;&", $customquerie[0]->custom);
			$countthem=0;
			$filterouts=array_values(array_filter($explthecustom)); //make array out of filters
			foreach($filterouts as $customs){
				$countthem++;
				if($countthem==$deletecustomfield) $theminzs = $customs.'&;&';
			}
			$finishedcustom=str_replace($theminzs, '', $customquerie[0]->custom);
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$finishedcustom' WHERE id='$edit' "));
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Custom Field deleted' , 'easyReservations' ).'</p></div>';
	}
	
	if(isset($editthereservation)){

			global $wpdb;

			$name=$_POST["name"];
			$date=$_POST["date"];
			$dateend=$_POST["dateend"];
			$email=$_POST["email"];
			$roomex=$_POST["roomexactly"];
			$room=$_POST["room"];
			$note=$_POST["note"];
			$nights=$_POST["nights"];
			$persons=$_POST["persons"];
			$specialoffer=$_POST["specialoffer"];
			$priceset=$_POST["priceset"];
			$waspayed=$_POST["waspayed"];
			$customfields="";

			for($i=1; $i < 10; $i++){
				if(isset($_POST["custom_value_".$i.""])){
					$customfields .= $_POST["custom_title_".$i.""].'&:&'.$_POST["custom_value_".$i.""].'&;&';
				}
			}

			if(isset($priceset)) $theprice=$priceset; else { $getprice=easyreservations_price_calculation($edit); $theprice=$getprice['price']; }
			if($waspayed=="on") $itwaspayed=1; else $itwaspayed=0; 
			if(isset($priceset))  $settepricei = $theprice.';'.$itwaspayed;
			$timestampstartedit=strtotime($date);
			$timestampendedit=strtotime($dateend);
			$dat=date("Y-m", $timestampstartedit);
			$rightdate=date("Y-m-d", $timestampstartedit);
			$calcdaysbetween=round(($timestampendedit-$timestampstartedit)/60/60/24);

			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$rightdate', nights='$calcdaysbetween', name='$name', email='$email', notes='$note', room='$room', number='$persons', special='$specialoffer', dat='$dat', roomnumber='$roomex', price='$settepricei', custom='$customfields' WHERE id='$edit' ")); 

			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Reservation edited!' , 'easyReservations' ).'</p></div>';
	}
	
	if(isset($approve)  || isset($delete) || isset($view) || isset($edit)) { //Query of View Reject and Approve
		$sql_approvequerie = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id='$approve' OR id='$delete' OR id='$view' OR id='$edit'";
		$approvequerie = $wpdb->get_results($sql_approvequerie );

			$id=$approvequerie[0]->id;
			$name=$approvequerie[0]->name;
			$reservationFrom=$approvequerie[0]->nights;
			$reservationDate=$approvequerie[0]->arrivalDate;
			$room=$approvequerie[0]->room;
			$special=$approvequerie[0]->special;
			$exactlyroom=$approvequerie[0]->roomnumber;
			$persons=$approvequerie[0]->number;
			$mail_to=$approvequerie[0]->email;
			$customs=$approvequerie[0]->custom;
			$message_r=$approvequerie[0]->notes;

			if($approvequerie[0]->price != '' AND $approvequerie[0]->approve=='yes'){
				$pricexpl=explode(";", $approvequerie[0]->price);
				if($pricexpl[1]==1){
					$checked='checked'; 
					$paystatus = ' - <b style="text-transform: capitalize;color:#1FB512;">'. __( 'paid' , 'easyReservations' ).'</b>';
				}
				elseif($pricexpl[1]==0){
					$paystatus = ' - <b style="text-transform: capitalize;color:#FF3B38;">'. __( 'unpaid' , 'easyReservations' ).'</b>';
				}
				else $checked='';
				$pricefield='<tr><td nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.__( 'Price' , 'easyReservations' ).':</td> <td><input type="text" name="priceset" style="width:70px" value="'.$pricexpl[0].'"> &'.get_option('reservations_currency').'; <input type="checkbox" name="waspayed" '.$checked.'><small>Guest has payed</small></td></tr>';
				$information='<small>'.__( 'Shows the price how it would be calculated now. When changing Filters/Groundprice/Settings or the Reservations Price after this Reservation was approved it wont match the Price anymore.' , 'easyReservations' ).'</small>';
			
			} else $pricefield='';

			if(isset($approve)  || isset($delete) || isset($view)) $roomwhere=get_the_title($room); // For Overview only show date on view
			$roomsgetpost=get_post($room);
			$rooms=$roomsgetpost->post_title;

			$specialgetpost=get_post($special);
			$specials=$specialgetpost->post_title;	

			if($special=="0") $specials="None";

			$timpstampanf=strtotime($reservationDate);
			$anznights=60*60*24*$reservationFrom;
			$timestampend=$anznights+$timpstampanf;

			$timestamp_timebetween=$timpstampanf-time()-432000; // to show days before arrivaldate in Reservation Overview
			if(!$startchooser) $moreget+=round($timestamp_timebetween/24/60/60);
			if($edit) $edtlink='&edit='.$edit;
			elseif($approve) $edtlink='&approve='.$approve;
			elseif($delete) $edtlink='&delete='.$delete;
	}

	if(isset($post_approve) && $post_approve=="yes"){
	
		$pricearry = easyreservations_price_calculation($approve);
		if($hasbeenpayed=="on") $priceset2=$pricearry['price'].';1'; else $priceset2=$pricearry['price'].';0';
		$priceset=str_replace(",", ".", $priceset2);
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes', roomnumber='$roomexactly', price='$priceset' WHERE id='$approve'"  ) ); 	
		
		if($sendmail=="on"){
			$emailformation=get_option('reservations_email_to_userapp_msg');
			preg_match_all(' /\[.*\]/U', $emailformation, $matchers); 
			$mergearrays=array_merge($matchers[0], array());
			$edgeoneremoave=str_replace('[', '', $mergearrays);
			$edgetworemovess=str_replace(']', '', $edgeoneremoave);
				foreach($edgetworemovess as $fieldsx){
					$field=explode(" ", $fieldsx);
					if($field[0]=="adminmessage"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', $approve_message, $emailformation);
					}
					elseif($field[0]=="thename"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', $name, $emailformation);
					}
					elseif($field[0]=="email"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$mail_to.'', $emailformation);
					}
					elseif($field[0]=="arrivaldate"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $timpstampanf).'', $emailformation);
					}
					elseif($field[0]=="to"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $timestampend).'', $emailformation);
					}
					elseif($field[0]=="nights"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$reservationFrom.'', $emailformation);
					}
					elseif($field[0]=="message"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$message_r.'', $emailformation);
					}
					elseif($field[0]=="persons"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$persons.'', $emailformation);
					}
					elseif($field[0]=="room"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.__($rooms).'', $emailformation);
					}
					elseif($field[0]=="offer"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.__($specials).'', $emailformation);
					}
					elseif($field[0]=="price"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.reservations_format_money($pricearry['price']).' '.get_option('reservations_currency'), $emailformation);
					}
					elseif($field[0]=="customs"){
						$explodecustoms2=explode("&;&", $customs);
						$customsmerge2=array_values(array_filter($explodecustoms2));
						foreach($customsmerge2 as $custom2){
							$customaexp2=explode("&:&", $custom2);
							$customsemails  .= $customaexp2[0].': '.$customaexp2[1].'<br>';
						}
						$emailformation=preg_replace('[customs]', $customsemails, $emailformation);
					}
				}

			$finalemailedgeremove1=str_replace('[', '', $emailformation);
			$finalemailedgesremoved=str_replace(']', '', $finalemailedgeremove1);
			$makebrtobreak=str_replace('<br>', "\n", $finalemailedgesremoved);
			$msg=$makebrtobreak;

			$reservation_support_mail = get_option("reservations_support_mail");
			$subj=get_option("reservations_email_to_userapp_subj");
			$eol="\n";
			$headers = "From: ".get_bloginfo('name')." <".$reservation_support_mail.">".$eol;
			$headers .= "Message-ID: <".time()."-".$reservation_support_mail.">".$eol;
		
			wp_mail($mail_to,$subj,$msg,$headers);
		}
	}

	if(isset($post_delete) && $post_delete=="yes"){
		$pricearry = easyreservations_price_calculation($approve);
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='no' WHERE id=$delete"  ) ); 
		if($sendmail=="on"){
			$emailformation=get_option('reservations_email_to_userdel_msg');
			preg_match_all(' /\[.*\]/U', $emailformation, $matchers); 
			$mergearrays=array_merge($matchers[0], array());
			$edgeoneremoave=str_replace('[', '', $mergearrays);
			$edgetworemovess=str_replace(']', '', $edgeoneremoave);
				foreach($edgetworemovess as $fieldsx){
					$field=explode(" ", $fieldsx);
					if($field[0]=="adminmessage"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', $approve_message, $emailformation);
					}
					elseif($field[0]=="thename"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', $name, $emailformation);
					}
					elseif($field[0]=="email"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$mail_to.'', $emailformation);
					}
					elseif($field[0]=="arrivaldate"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $timpstampanf).'', $emailformation);
					}
					elseif($field[0]=="to"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $timestampend).'', $emailformation);
					}
					elseif($field[0]=="nights"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$reservationFrom.'', $emailformation);
					}
					elseif($field[0]=="message"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$message_r.'', $emailformation);
					}
					elseif($field[0]=="persons"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.$persons.'', $emailformation);
					}
					elseif($field[0]=="room"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.__($rooms).'', $emailformation);
					}
					elseif($field[0]=="offer"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.__($specials).'', $emailformation);
					}
					elseif($field[0]=="price"){
						$emailformation=preg_replace('/\['.$fieldsx.']/U', ''.reservations_format_money($pricearry['price']).' '.get_option('reservations_currency'), $emailformation);
					}
					elseif($field[0]=="customs"){
						$explodecustoms2=explode("&;&", $customs);
						$customsmerge2=array_values(array_filter($explodecustoms2));
						foreach($customsmerge2 as $custom2){
							$customaexp2=explode("&:&", $custom2);
							$customsemails  .= $customaexp2[0].': '.$customaexp2[1].'<br>';
						}
						$emailformation=preg_replace('[customs]', $customsemails, $emailformation);
					}
				}
			$finalemailedgeremove1=str_replace('[', '', $emailformation);
			$finalemailedgesremoved=str_replace(']', '', $finalemailedgeremove1);
			$makebrtobreak=str_replace('<br>', "\n", $finalemailedgesremoved);
			$msg=$makebrtobreak;
			
			$reservation_support_mail = get_option("reservations_support_mail");
			$subj=get_option("reservations_email_to_userdel_subj");
			$eol="\n";
			$headers = "From: ".get_bloginfo('name')." <".$reservation_support_mail.">".$eol;
			$headers .= "Message-ID: <".time()."-".$reservation_support_mail.">".$eol;
			
			wp_mail($mail_to,$subj,$msg,$headers);
		}
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'Reservation rejected' , 'easyReservations' ).'</p></div>';
		?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
	}
	//Get Options from wp_options; Hope they'r not to much
	$reservation_support_mail = get_option("reservations_reservation_mail");
	$reservations_on_page = get_option("reservations_on_page");
	$offer_cat = get_option("reservations_special_offer_cat");
	$room_category =get_option("reservations_room_category");
	$fontcoloriffull=get_option("reservations_fontcoloriffull");
	$fontcolorifempty=get_option("reservations_fontcolorifempty");
	$colorborder=get_option("reservations_colorborder");
	$colorbackgroundfree=get_option("reservations_colorbackgroundfree");
	$colorfull=get_option("reservations_backgroundiffull");
	$borderbottom=get_option("reservations_border_bottom");
	$borderside=get_option("reservations_border_side");
	$show_overview_on_list = 1;

	//Calculations for Overview Dates
	echo $startchooser;
	$daysshow=get_option("reservations_show_days");
	$timevariable=time();
	$enddate=$daysshow+$startchooser+$moreget;
	$startdate=0+$startchooser+$moreget;
	$eintagmalstart=60*60*24*$startdate;
	$eintagmalend=60*60*24*$enddate;
	$timesx=$timevariable+$eintagmalstart;
	$timesy=$timevariable+$eintagmalend;
	$more=$startchooser+$moreget;
	if(date("F", $timesx)==date("F", $timesy)) $dateshow=date("F", $timesx);
	else $dateshow=date("F", $timesx).'/'.date("F", $timesy);


	?><div id="icon-themes" class="icon32"></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;">Reservations <a class="add-new-hari" href="admin.php?page=add-reservation" rel="simple_overlay" style="width:20px;heigth:29px;"href="#">Add New</a></h2><div id="wrap"><?php if($prompt) echo ' '.$prompt; ?>
		<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START OVERVIEW
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if((isset($approve) || isset($delete) || isset($view) || isset($edit)) OR $show_overview_on_list == 1){ ?>			
			<table cellspacing="0" cellpadding="0" class="widefat" style="background:#f9f9f9; width:99%;" align="top">
				<thead>
					<tr>
						<td style=" text-align:center;vertical-align:middle;">
							<b><?php echo $dateshow; ?></b><br><form method="post" name="detegepickt" id="detegepickt" style="float:left"><input id="dateas" name="dateas" type="hidden"></form><a href="<?php echo $pageURL;?>?page=reservations<?php echo $edtlink; ?>&more=<?php echo $more-$daysshow;?><?php if(isset($_GET['typ']))echo '&typ='.$typ; ?>"><</a> <a href="<?php echo $pageURL;?>?page=reservations<?php echo $edtlink; ?>&more=0<?php if(isset($_GET['typ']))echo '&typ='.$typ; ?>">0</a> <a href="<?php echo $pageURL;?>?page=reservations<?php echo $edtlink; ?>&more=<?php echo $more+$daysshow;?>">></a>
						</td>
					<?php
						$s=$daysshow+$more;
						$co=0+$more;
						$overviewfont='font: 10px #333333 font-family: Georgia,Times New Roman,Times,serif;';
						while($co < $s){
							$eintags=60*60*24*$co;
							$timesx =time()+$eintags;
							$datesarray.=date("m", $timesx).",";
							if($timpstampanf <= $timesx AND $timesx <= $timestampend+86400) { $backgroundhighlight='#a4a4a4'; } else { $backgroundhighlight='#f9f9f9'; }
							?>
							<td style="text-align: center;background:<?php echo $backgroundhighlight; ?>;<?php echo $overviewfont; ?> border-left:  1px solid #dfdfdf; " class="h1overview">
								<?php echo date("d",$timesx); ?><br><?php echo date("D",$timesx); ?>
							</td>
							<?php $co++;
						} ?>
					</tr>
				</thead>
				<tbody style=" background: #f9f9f9;">
				<?php
					//$sql_R = "SELECT DISTINCT room FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $roomwhere ORDER BY room ASC";
					//$results = $wpdb->get_results( $sql_R );
				
					if(isset($roomwhere)){ $argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'name' => $roomwhere ); }
					else { $argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC' ); }
					$roomcategories = get_posts( $argss ); 

						foreach( $roomcategories as $roomcategorie ){
					//	foreach( $results as $result ) {
							$roomsidentify=$roomcategorie->ID;
							$roomcounty=get_post_meta($roomsidentify, 'roomcount', true);
							$rowcount="0";
							while($roomcounty > $rowcount){
								$rowcount++; ?>
								<tr><td onclick="<?php if($approve){ ?>document.reservation_approve.roomexactly.selectedIndex=<?php echo $rowcount-1; ?>;<?php } if($edit){ ?>document.editreservation.roomexactly.selectedIndex=<?php echo $rowcount; ?>;document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationFrom*86400))?>';setVals(<?php echo $roomsidentify; ?>);<?php } if ($edit OR $approve) { ?>this.style.background='<?php echo "url(".RESERVATIONS_IMAGES_DIR."/stare2.png) right center no-repeat "; ?>';<?php for($xixi=0; $xixi <= $reservationFrom; $xixi++){ if($xixi==0){ $backgroundclick="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_start.png) no-repeat ".$colorbackgroundfree.""; } elseif($xixi==$reservationFrom){ $backgroundclick="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_end.png) no-repeat ".$colorbackgroundfree.""; } else { $backgroundclick="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_middle.png) repeat"; } ?>document.getElementsByName('hoverclass<?php echo $roomsidentify.$rowcount; ?>')[<?php echo $xixi; ?>].style.background='<?php echo $backgroundclick; ?>';<?php } } ?>" ondblclick="<?php if($approve){ ?>document.reservation_approve.roomexactly.selectedIndex=0;<?php } if($edit){ ?>document.editreservation.roomexactly.selectedIndex=0;document.editreservation.room.selectedIndex=0;<?php } if ($edit OR $approve) { ?><?php for($xixi=0; $xixi <= $reservationFrom; $xixi++){ ?>document.getElementsByName('hoverclass<?php echo $roomsidentify.$rowcount; ?>')[<?php echo $xixi; ?>].style.background='<?php echo $colorbackgroundfree; ?>';<?php } ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationFrom*86400)); ?>';this.style.background='<?php echo "url(".RESERVATIONS_IMAGES_DIR ."/starempty.png) right center no-repeat"; ?>'; <?php } ?>" <?php if ($edit OR $approve) { ?>onmouseover="this.style.background='<?php echo "url(".RESERVATIONS_IMAGES_DIR ."/starempty.png) right center no-repeat "; ?>'; " onmouseout="this.style.background=''" <?php } ?> style="text-shadow:none; border-style:none; width: 160px;max-height: 30px;background:#f9f9f9; vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid #dfdfdf;" nowrap><?php echo get_the_title($roomsidentify).' '.$rowcount; ?></td>
								<?php
								$showdatenumber_start=0+$more;
								$showdatenumber_end=$daysshow+$more;
								$cellcount=0;
								$fullcount=0;
								while($showdatenumber_start < $showdatenumber_end){
									$cellcount++;
									$showdatenumber_start++;
									$eintagerss=60*60*24*$showdatenumber_start;
									$datumtoday=$timevariable+$eintagerss;
									$datedatumtodaydate=date("Y-m-d", $datumtoday);

									$sql_R2 = "SELECT id, name, nights, arrivalDate, room FROM ".$wpdb->prefix ."reservations WHERE room='$roomsidentify' AND roomnumber='$rowcount' AND approve='yes' AND  roomnumber != '' AND '$datedatumtodaydate' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ORDER BY arrivalDate ASC";
									$overviewresults = $wpdb->get_results( $sql_R2 );
									$itwasfull=0;

									foreach( $overviewresults as $result){
									$fullcount++;
										$nameov=$result->name;
										$expl= explode(" ", $nameov);
										$nights=$result->nights;
										$timpstamp_start=strtotime($result->arrivalDate)+86400;
										$eintags12=60*60*24*$showdatenumber_start;
										$datetodays=$timevariable+$eintags12;
										$anznights=60*60*24*$nights;
										$timestamp_end=$anznights+$timpstamp_start;

										if($datetodays >= $timpstamp_start AND $datetodays <= $timestamp_end){
											$itwasfull++;
											$farbe="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_middle.png) repeat";
											if($letztername!=$nameov){
												$farbe="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_start.png) no-repeat ".$colorbackgroundfree."";
											}

											if($cellcount=="1") $farbe="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_middle.png) repeat";
											if($letztername != "") $farbe="url(".RESERVATIONS_IMAGES_DIR ."/".$colorfull."_middle.png) repeat"; ?>

											<td onclick="<?php if(!$edit AND !$approve) echo "location.href = 'admin.php?page=reservations&edit=".$result->id."';"; ?><?php if($edit){ ?>document.editreservation.roomexactly.selectedIndex=<?php echo $rowcount; ?>;document.getElementById('datepicker').value='<?php echo date("d.m.Y",$datetodays-86400)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$datetodays+($reservationFrom*86400)-86400)?>';setVals(<?php echo $roomsidentify; ?>); <?php } ?>" title="Name: <?php echo $nameov; ?><br><?php printf ( __( 'Date' , 'easyReservations' ));?>: <?php echo date("d.m.Y",$datetodays-86400)?><br><?php printf ( __( 'Room' , 'easyReservations' ));?>: <?php echo __(get_the_title($roomsidentify))?> # <?php echo $rowcount; ?><br><?php printf ( __( 'Status: Occupied' , 'easyReservations' ));?>"  style="background: <?php echo $farbe;?>; color:<?php echo $fontcoloriffull; ?>; text-align:center; overflow:hidden; text-shadow:none; border-style:none; text-decoration:none; font: normal 12px Arial, sans-serif; vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $colorborder; ?>; border-left:  <?php echo $borderside; ?>px solid <?php echo $colorborder; ?>;">
												<?php echo date("d",$datetodays-86400); ?></div>
											</td><?php
											$letztername=$nameov;
										}
									}   
									if($itwasfull == 0) { 
												if($letztername AND $cellcount != '1'){ 
													$farbe2='url('.RESERVATIONS_IMAGES_DIR .'/'.$colorfull.'_end.png) no-repeat '.$colorbackgroundfree.''; 
												} else { 
													$farbe2=$colorbackgroundfree;
												}
												$fullcount=0;
												if($timpstampanf+86400 <= $datumtoday AND $datumtoday <= $timestampend+86400*2){
												$hoverclass='name="hoverclass'.$roomsidentify.$rowcount.'"';
												} else { $hoverclass=""; }
												?>
												<td <?php echo $hoverclass;?> onclick="<?php if($edit){ ?>document.editreservation.roomexactly.selectedIndex=<?php echo $rowcount; ?>;document.getElementById('datepicker').value='<?php echo date("d.m.Y",$datumtoday-86400)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$datumtoday+($reservationFrom*86400)-86400)?>';setVals(<?php echo $roomsidentify; ?>); <?php } ?>" " title="<?php echo __( 'Date' , 'easyReservations' ).' '.date("d.m.Y",$datumtoday-86400); ?><br><?php echo __( 'Room' , 'easyReservations' ).': '.__(get_the_title($roomsidentify)).' # '.$rowcount.'<br>'.__( 'Status: Empty' , 'easyReservations' ); ?>" style="height:30px; text-align:center;text-shadow:none; border-style:none; vertical-align: middle;  color:<?php echo $fontcolorifempty; ?>; border-bottom: <?php echo $borderbottom;?>px solid <?php echo $colorborder;?>; border-left: <?php echo $borderside;?>px solid <?php echo $colorborder;?>; background:<?php echo $farbe2;?>"><?php echo  date("d",$datumtoday-86400); ?></td><?php
												$letztername='';
											}
								} echo '</tr>';
							}
						} ?>
				</tbody></table>
<script>
$(document).ready(function(){
	$("[title]").tooltip({ deley: 0, predelay: 30 , tipClass: 'tooltip_klein',position: "center right", offset: [-50, 50] });
});
<?php if($edit){ ?>
$(document).ready(function() {
	$("#dateas").datepicker( { 
			showOn: 'button', 
			buttonImageOnly: true, 
			rangeSelect: true,
			buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR?>/day.png',
			onSelect: function(dateText){
				$(this).parent("form")[0].submit();
			}
		});
});
<?php } ?>
function setVals(roomid) {
	var x = document.getElementById("room"); 
	for (var i = 0; i < x.options.length; i++) 
	{   
		if (x.options[i].value == roomid)
		{ 
			x.options[i].selected = true;     
			break;
		}
	}
}
</script><?php if($edit) echo '<br>';}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							if(!isset($approve) && !isset($delete) && !isset($view)  && !isset($edit)) {  
								$csv_hdr = "Name, Arrival Date, Desination Date, Nights, eMail, Persons, Room, Special Offer, Price"; // Header for Output
							?>
									<input type="hidden" name="action" value="reservation"/>
												<?php
														if(isset($_GET['specialselector'])) $specialselector=$_GET['specialselector'];
														if(isset($_GET['monthselector'])) $monthselector=$_GET['monthselector'];
														if(isset($_GET['roomselector'])) $roomselector=$_GET['roomselector'];

														$zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY >= NOW()";
														$orders="DESC";
														$ordersby="id";

														$items1 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen")); // number of total rows in the database
														$items2 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen")); // number of total rows in the database
														$items3 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen")); // number of total rows in the database
														$items4 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY < NOW()")); // number of total rows in the database
														$items5 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='del'")); // number of total rows in the database
														$items6 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations")); // number of total rows in the database

														if($typ=="" or $typ=="active" or !$typ) { $type="approve='yes'"; $items=$items1; $typlink="&typ=active"; } // If type is actice
														elseif($typ=="pending") { $type="approve=''"; $items=$items3; $typlink="&typ=pending"; } // If type is pending
														elseif($typ=="deleted") { $type="approve='no'"; $items=$items2; $typlink="&typ=deleted";} // If type is rejected
														elseif($typ=="old") { $type="approve='yes'"; $items=$items4; $typlink="&typ=old"; $zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY < NOW()";} // If type is old
														elseif($typ=="trash") { $type="approve='del'"; $items=$items5; $typlink="&typ=trash"; $zeichen=""; } // If type is trash
														elseif($typ=="all") { $type="approve!='sda'"; $items=$items6; $typlink="&typ=all"; $zeichen=""; } // If type is trash

														if($order=="ASC") { $orderlink="&order=ASC"; $orders="ASC";}
														elseif($order=="DESC") { $orderlink="&order=DESC"; $orders="DESC";}

														if($orderby=="date") { $orderbylink="&orderby=date"; $ordersby="arrivalDate";}
														elseif($orderby=="name") { $orderbylink="&orderby=name"; $ordersby="name";}
														elseif($orderby=="room") { $orderbylink="&orderby=room"; $ordersby="room";}
														elseif($orderby=="special") { $orderbylink="&orderby=special"; $ordersby="special";}
														elseif($orderby=="nights") { $orderbylink="&orderby=nights"; $ordersby="nights";}
														else { $ordersby="arrivalDate"; $orders="ASC"; }
														if($typ=="pending") { $ordersby="id"; $orders="DESC"; }

														if($specialselector != 0 AND $specialselector != "") { $specialsql="AND special='".$specialselector."'"; $speciallink="&orderby=".$specialselector;   }
														if($roomselector != 0 AND $roomselector != "") { $roomsql="AND room='".$roomselector."'"; $roomlink="&orderby=".$roomselector;  } 
														if($monthselector != 0 AND $monthselector != "") { $monthsql="AND dat='".$monthselector."'"; $monthlink="&orderby=".$monthselector; }
														if($perpage != 0) { $perpagelink="&perpage=".$perpage; }
														if($perpage == 0) $perpage=$reservations_on_page;
														if($more != 0) $morelink="&more=";

														if($specialselector OR $monthselector OR $roomselector){
															$items7 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE $type $monthsql $roomsql $specialsql"));
															$items=$items7; 
															$roomslink='&roomselector='.$roomselector; 
															$monthslink='&monthselector='.$monthselector; 
															$offerslink='&specialselector='.$specialselector; 
															echo 'HALLO';
														}

														if($items > 0) {
																$p = new pagination;
																$p->items($items);
																$p->limit($perpage); // Limit entries per page
																if($search) $p->target("admin.php?page=reservations&search=".$search.""); else $p->target("admin.php?page=reservations".$typlink."".$orderbylink."".$orderlink."".$perpagelink."".$speciallink."".$monthselector."".$roomlink."".$monthlink."".$roomslink."".$offerslink);
																$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
																$p->calculate(); // Calculates what to show
																$p->parameterName('paging');
																$p->adjacents(1); //No. of page away from the current page

																if(!isset($_GET['paging'])) {
																	$p->page = 1;
																} else {
																	$p->page = $_GET['paging'];
																}

																$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
																} ?>
												<table style="width:99%"><tr> <!-- Type Chooser //--> 
													<td style="width:20%;">
														<ul class="subsubsub">
															<?php if($typ=="" or $typ=="active"){ ?>
															<li><a href="admin.php?page=reservations&typ=active" class="current"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="pending"){ ?>
															<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=pending" class="current"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="deleted"){ ?>
															<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=deleted" class="current"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="old"){ ?>
															<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=old" class="current"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash">Trash<span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="all"){ ?>
															<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=all" class="current"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash">Trash<span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="trash"){ ?>
															<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
															<li><a href="admin.php?page=reservations&typ=trash" class="current"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li>
															<?php } ?>
														</ul>
													</td>
													<td style="width:60%; text-align:center; font-size:12px;" nowrap><form method="get" action="admin.php"><input type="hidden" name="page" value="reservations"><input type="hidden" name="typ" value="<?php echo $typ; ?>"> <!-- Begin of Filter //--> 
																				<select name="monthselector"><option value="0"><?php printf ( __( 'Show all Dates' , 'easyReservations' ));?></option><!-- Filter Months //--> 
																					<?php 
																					$posts = "SELECT DISTINCT dat FROM ".$wpdb->prefix ."reservations WHERE $type $zeichen GROUP BY dat ORDER BY dat ";
																					$results = $wpdb->get_results($posts);

																					foreach( $results as $result )	{	
																					$dat=$result->dat;	
																					$zerst = explode("-",$dat);
																					if($zerst[1]=="01") $month=__( 'January' , 'easyReservations' ); if($zerst[1]=="02") $month=__( 'February' , 'easyReservations' ); if($zerst[1]=="03") $month=__( 'March' , 'easyReservations' ); if($zerst[1]=="04") $month=__( 'April' , 'easyReservations' ); if($zerst[1]=="05") $month=__( 'May' , 'easyReservations' ); if($zerst[1]=="06") $month=__( 'June' , 'easyReservations' ); if($zerst[1]=="07") $month=__( 'July' , 'easyReservations' ); if($zerst[1]=="08") $month=__( 'August' , 'easyReservations' ); if($zerst[1]=="09") $month=__( 'September' , 'easyReservations' ); if($zerst[1]=="10") $month=__( 'October' , 'easyReservations' ); if($zerst[1]=="11") $month=__( 'November' , 'easyReservations' ); if($zerst[1]=="12") $month=__( 'December' , 'easyReservations' );

																					echo '<option value="'.$dat.'">'.$month.' '.__($zerst[0]).'</option>'; 
																					} ?>
																			</select> <select name="roomselector" class="postform"><option value="0"><?php printf ( __( 'View all Rooms' , 'easyReservations' ));?></option> <!-- Filter Rooms //-->
																					<?php 
																					$posts = "SELECT ID, post_title FROM $wpdb->posts WHERE post_type='post' AND post_status='publish'";
																					$results = $wpdb->get_results($posts);

																					foreach( $results as $result )	{			
																					$id=$result->ID;	
																					$nameq=$result->post_title;																	
																					$categ=get_the_category($id);
																					$care=$categ[0]->term_id;

																					if($care==$room_category) { echo '<option value="'.$id.'">'.__($nameq).'</option>'; } 
																					} ?>
																			</select> <select name="specialselector" class="postform"><option value="0"><?php printf ( __( 'View all Offers ' , 'easyReservations' ));?></option> <!-- Filter Special Offers //-->
																					<?php 

																					foreach( $results as $result )	{			
																						$id=$result->ID;	
																						$namew=$result->post_title;																	
																						$categ=get_the_category($id);
																						$care=$categ[0]->term_id;

																						if($care==$offer_cat) { echo '<option value="'.$id.'">'.__($namew).'</option>'; }
																					} 

																					$margindate="38px";
																					$marginroom="46px";
																					$marginspecial="88px";
																					?>
																			</select>	
														<input size="1px" type="text" name="perpage" value="<?php echo $perpage; ?>" maxlength="3"></input><input class="button-secondary" type="submit" value="<?php  printf ( __( 'Filter' , 'easyReservations' )); ?>"></form><!-- End of Filter //-->
													</td>
													<td style="width:20%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
														<form method="get" action="admin.php" name="search" enctype="form-data"><input type="hidden" name="page" value="reservations"><input type="text" style="width:130px;" name="search" value="<?php if($search) echo $search;?>" class="all-options"></input><input class="button-secondary" type="submit" value="<?php  printf ( __( 'Search' , 'easyReservations' )); ?>" id="submitbutton"></form>
													</td>
												</tr>
												</table>

												<form action="admin.php?page=reservations" method="get" name="frmAdd" id="frmAdd">
												<table  class="widefat" style="width:99%;"> <!-- Main Table //-->
													<thead> <!-- Main Table Header //-->
														<tr>
															<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
															<th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" style="background-position:44px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" style="background-position:44px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:44px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
															<th><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" style="background-position:<?php echo $margindate;?> 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" style="background-position:<?php echo $margindate;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:<?php echo $margindate;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
															<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
															<th><?php printf ( __( 'Persons' , 'easyReservations' ));?></th>
															<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" style="background-position:<?php echo $marginroom;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" style="background-position:<?php echo $marginroom;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:<?php echo $marginroom;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
															<th><?php if($order=="ASC" and $orderby=="special") { ?><a class="asc2" style="background-position:<?php echo $marginspecial;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="special") { ?><a class="desc2" style="background-position:<?php echo $marginspecial;?> 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
																<?php } else { ?><a class="stand2"  style="background-position:<?php echo $marginspecial;?> 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
															<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
															<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
														</tr></thead>
														<tfoot><tr><!-- Main Table Footer //-->
															<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
															<th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" style="background-position:44px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" style="background-position:44px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:44px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
															<th><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" style="background-position:38px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" style="background-position:38px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:38px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
															<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
															<th><?php printf ( __( 'Persons' , 'easyReservations' ));?></th>
															<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" style="background-position:46px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" style="background-position:46px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:46px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
															<th><?php if($order=="ASC" and $orderby=="special") { ?><a class="asc2" style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="special") { ?><a class="desc2" style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
																<?php } else { ?><a class="stand2"  style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
															<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
															<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
														</tr></tfoot><tbody>
													<?php
														$nr="0";
														if($search) {
															$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE name like '%$search%' OR email like '%$search%' OR notes like '%$search%' OR arrivalDate like '%$search%' $limit"; // Search query
														} else {
															$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE $type $monthsql $roomsql $specialsql $zeichen ORDER BY $ordersby $orders $limit";  // Main Table query
														}
														$result = mysql_query($sql) or die (mysql_error());

															if(mysql_num_rows($result) > 0 ){
															while ($row = mysql_fetch_assoc($result)){
																$id=$row['id'];
																$name = $row['name'];
																$nights=$row['nights'];
																$person=$row['number'];

																if($row['approve'] == 'yes' AND $row['price'] != ''){
																	$priceexplode=explode(";", $row['price']);
																	if($priceexplode[1]==1){
																		$pricebgcolor='color:#3A9920;padding:1px;';
																	}
																	elseif($priceexplode[1]==0){
																		$pricebgcolor='color:#FF3B38;padding:1px;';
																	}
																}

																$special=$row['special'];
																$specialgetpost=get_post($special);
																$specials=$specialgetpost->post_title;
																$message=explode("*/*", $row['notes']);

																$room=$row['room'];
																$roomsgetpost=get_post($room);
																$rooms=$roomsgetpost->post_title;

																if($nr%2==0) $class="alternate"; else $class="";
																$timpstampanf=strtotime($row['arrivalDate']);
																$anznights=60*60*24*$nights;
																$timestampend=(60*60*24*$nights)+$timpstampanf;

																$nr++; 
																?>
														<tr class="<?php echo $class; ?> test" height="47px"><!-- Main Table Body //-->
															<td width="2%" style="text-align:center;vertical-align:middle;"><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $id;?>"></td>
															<td width="17%" class="row-title" valign="top" nowrap><div class="test"><a href="admin.php?page=reservations&view=<?php echo $id;?>"><?php echo $name;?></a><div class="test2" style="margin:5px 0 0px 0;"><a href="<?php echo $pageURL;?>?page=reservations&edit=<?php echo $id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a> <?php if($typ=="deleted" or $typ=="pending") { ?>| <a style="color:#28a70e;" href="<?php echo $pageURL;?>?page=reservations&approve=<?php echo $id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a><?php } ?> <?php if($typ=="" or $typ=="active" or $typ=="pending") { ?>| <a style="color:#bc0b0b;" href="<?php echo $pageURL;?>?page=reservations&delete=<?php echo $id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a><?php } ?>  <?php if($typ=="trash") { ?>| <a href="<?php echo $pageURL;?>?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> | <a style="color:#bc0b0b;" href="<?php echo $pageURL;?>?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a><?php } ?> | <a href="<?php echo $pageURL;?>?page=reservations&view=<?php echo $id;?>"><?php printf ( __( 'View' , 'easyReservations' ));?></a></div></div><?php $csv_output .= $name . ", "; ?></td>
															<td width="20%" nowrap><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $nights; ?> <?php printf ( __( 'Nights' , 'easyReservations' ));?>)</small><?php $csv_output .= date("d.m.Y",$timpstampanf) . ", "; ?><?php $csv_output .= date("d.m.Y",$timestampend) . ", "; ?><?php $csv_output .= $nights . ", "; ?></td>
															<td width="12%"><?php echo $row['email'];?><?php $csv_output .= $row['email'] . ", "; ?></td>
															<td width="5%" style="text-align:center;"><?php echo $row['number'];?><?php $csv_output .= $row['number'] . ", "; ?></td>
															<td width="12%" nowrap><?php echo __($rooms) ;?> <?php echo $row['roomexactly'] ;?><?php $csv_output .= __($rooms) . ", "; ?></td>
															<td width="12%" nowrap><?php if($special==0) echo __( 'None' , 'easyReservations' ); else echo __($specials);?><?php $csv_output .= __($specials) . ", "; ?></td>
															<td width="13%"><?php if($message[0]){ echo substr($message[0], 0, 36); } else { echo __( 'None' , 'easyReservations' ); } ?></td>
															<td width="7%" nowrap style="text-align:right"><b style="<?php echo $pricebgcolor; ?>"><?php echo easyreservations_get_price($id); ?></b><?php $csv_output .= str_replace(",", ".", str_replace(".", "", str_replace('&'.get_option('reservations_currency'), "", easyreservations_get_price($id)))) . "\n"; ?></td>
														</tr>
													<?php }
													} else { ?> <!-- if no results form main quary !-->
															<tr>
																<td colspan="9"><b><?php printf ( __( 'No Reservations found!' , 'easyReservations' ));?></b></td> <!-- Mail Table Body if empty //-->
															<tr>
													<?php } ?>
													</tbody>
												</table>
												<table  style="width:99%;"> 
													<tr>
														<td style="width:33%;"><!-- Bulk Options //-->
															<select name="bulk" id="bulk"><option select="selected" value="0"><?php echo __( 'Bulk Actions' ); ?></option><?php if($typ=="active" OR $typ=="pending" OR $typ=="old" OR $typ=="deleted" OR  $typ=="") { ?><option value="1"><?php printf ( __( 'Move to Trash' , 'easyReservations' ));?></option><?php }  if($typ=="trash") { ?><option value="2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></option><option value="3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?>'</option><?php } ;?></select>  <input class="button-secondary" type="submit" value="<?php printf ( __( 'Apply' , 'easyReservations' ));?>" /> </form>
														</td>
														<td style="width:33%;" nowrap> <!-- Pagination  //-->
															<?php if($items > 0) { ?><div class="tablenav" style="text-align:center; margin:0 115px 4px 0;"><div style="background:#ffffff;" class='tablenav-pages'><?php echo $p->show(); ?></div></div>	<?php } ?>
														</td>
														<td style="width:33%;margin-left: auto; margin-right: 0pt; text-align: right;"> <!-- Num Elements //-->
															<span class="displaying-nums"><?php echo $nr;?> <?php printf ( __( 'Elements' , 'easyReservations' ));?></span>
														</td>
													</tr>
												</table></form>
												<form  name="export" action="<?php echo WP_PLUGIN_URL; ?>/easyreservations/export.php" method="post">
													<input class="button-secondary" type="submit" value="<?php printf ( __( 'Export table as CSV' , 'easyReservations' ));?>">
													<input type="hidden" value="<?php echo $csv_hdr; ?>" name="csv_hdr">
													<input type="hidden" value="<?php echo $csv_output; ?>" name="csv_output">
												</form>
										<?php }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START APPROVE/DELETE/VIEW //
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
											if((isset($approve) || isset($delete) || isset($view)) && !isset($reservation_approve)){ ?> <!-- // Content will only show on delete, view or approve Reservation -->

											<?php if(!isset($view)){ ?><table  style="width:99%;" cellspacing="0"><tr><td style="width:64%;" valign="top"><br><?php } else { $width='style="width:480px;"'; echo '<br>'; } ?>
												<table class="widefat" <?php echo $width; ?>>
													<thead>
														<tr>
															<th colspan="2"><?php if($approve) { echo __( 'Approve' , 'easyReservations' ); } if($delete) { echo __( 'Reject' , 'easyReservations' );  } if($view) { echo __( 'View' , 'easyReservations' ); } echo ' '.__( 'Reservation' , 'easyReservations' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $name;?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $reservationFrom;?>)</small></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $mail_to;?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $persons;?></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo __($rooms);?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Special Offer' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php if($specials){ echo __($specials);} else { printf ( __( 'None' , 'easyReservations' )); }  ?></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php 
															echo easyreservations_get_price($id); ?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php echo $message_r[0];?></b></td>
														</tr>
														<?php 
														$explodecustoms=explode("&;&", $customs);
														$thenumber=0;
														$customsmerge=array_values(array_filter($explodecustoms));
														foreach($customsmerge as $custom){
															$customexp=explode("&:&", $custom);
															if($thenumber%2==0) $class=""; else $class="alternate";
															echo '<tr class="'.$class.'">';
															echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> '.__($customexp[0]).':</b></td>';
															echo '<td><b>'.$customexp[1].'</b></td></tr>';
															$thenumber++;
														}
														?>
													</tbody>
												</table><br>
											<div <?php echo $width; ?>><?php echo easyreservations_detailed_price($id); ?></div>

									<?php } 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
									if(isset($edit)){
									if(reservations_check_type($edit) == __('approved', 'easyReservations' )) $color='#1FB512';
									if(reservations_check_type($edit) == __('pending' , 'easyReservations' )) $color='#3BB0E2';
									if(reservations_check_type($edit) == __('rejected' , 'easyReservations' )) $color='#D61111';
									if(reservations_check_type($edit) == __('trashed' , 'easyReservations' )) $color='#870A0A';
									?> <!-- // Content will only show on edit Reservation -->
											<script>
											  $(document).ready(function() {
												$("#datepicker").datepicker( { altFormat: 'dd.mm.yyyy' });
												$("#datepicker2").datepicker( { altFormat: 'dd.mm.yyyy' });
											  });
											</script>
											<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
											<table  style="width:99%;" cellspacing="0">
											<tr>
											<td style="width:64%;" valign="top">
												<form id="editreservation" name="editreservation" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>"> 
												<input type="hidden" name="editthereservation" id="editthereservation" value="editthereservation">
												<table class="widefat" style="width:550px;">
													<thead>
														<tr>
															<th colspan="2"><?php printf ( __( 'Edit Reservation' , 'easyReservations' ));?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="2" nowrap><div class="explainbox" style="width:94%; margin-bottom:2px;"><div id="left"><b><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> <?php echo easyreservations_get_price($id); ?></b></div><div id="right"><span style="float:right"><?php echo reservations_get_administration_links($edit, 'edit');?></span></div><div id="center"><b style="text-transform: capitalize;color:<?php echo $color; ?>"><?php echo reservations_check_type($edit); ?></b> <?php echo $paystatus; ?></div></div></td>
														</tr>
														<tr>
															<td nowrap style="width:45%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
															<td><input type="text" name="name" align="middle" value="<?php echo $name;?>" class="regular-text"></td>
														</tr>
														<tr  class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
															<td><input type="text" id="datepicker" style="width:70px" name="date" value="<?php echo date("d.m.Y",$timpstampanf); ?>" class="regular-text"> <b>-</b> <input type="text" id="datepicker2" style="width:70px" name="dateend" value="<?php echo date("d.m.Y",$timestampend); ?>" class="regular-text"></td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
															<td><input type="text" name="email" value="<?php echo $mail_to;?>" class="regular-text"></td>
														</tr>
														<?php echo $pricefield; ?>
														<tr   class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
															<td><select name="persons"><option value="<?php echo $persons;?>" select><?php echo $persons;?></option> <?php
															for($countpersons=1; $countpersons < 100; $countpersons++){
																echo '<option value="'.$countpersons.'">'.$countpersons.'</option>';
															}
															?></select></td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
															<td><select  name="room" id="room"><option value="<?php echo $room;?>"><?php echo $rooms;?></option>
															<?php	
																$argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
																$roomcategories = get_posts( $argss );
																	foreach( $roomcategories as $roomcategorie ){
																		if($roomcategorie->ID!=$room){
																			echo '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
																		}
																	} ?></select> <select id="roomexactly" name="roomexactly">
																		<?php
																		$roomcounty=get_post_meta($room, 'roomcount', true);
																		$ix="0";
																		if($roomcounty) echo '<option select="selected" value="'.$exactlyroom.'">'.$exactlyroom.' </option>';
																		while($ix < reservations_get_highest_roomcount()){
																		$ix++;
																		echo '<option value="'.$ix.'">'.$ix.' </option>';
																		}  ?>
																</select>
															</td>
														</tr>
														<tr   class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Special Offer' , 'easyReservations' ));?>:</b></td> 
															<td><select  name="specialoffer" id="specialoffer"><?php if($special!=0){ ?><option  value="<?php echo $special;?>"><?php echo $specials;?><?php }?><option  value="0">None</option>
															<?php	
																$argss = array( 'type' => 'post', 'category' => $offer_cat, 'orderby' => 'post_title', 'order' => 'ASC');
																$specialcategories = get_posts( $argss );
																	foreach( $specialcategories as $specialcategorie ){
																		if($specialcategorie->ID!=$special){
																			echo '<option value="'.$specialcategorie->ID.'">'.__($specialcategorie->post_title).'</option>';
																		}
																	} ?></select>
															</td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
															<td><textarea name="note" cols="40" rows="6"><?php echo $message_r[0];?></textarea></td>
														</tr>
														<?php 
														$explodecustoms=explode("&;&", $customs);
														$thenumber=0;
														$customsmerge=array_values(array_filter($explodecustoms));
														foreach($customsmerge as $custom){
															$customexp=explode("&:&", $custom);
															if($thenumber%2==0) $class="alternate"; else $class="";
															$thenumber++;
															echo '<tr class="'.$class.'">';
															echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> '.__($customexp[0]).':</b> <a href="admin.php?page=reservations&edit='.$edit.'&deletecustomfield='.$thenumber.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a> <input type="hidden" name="custom_title_'.$thenumber.'" value="'.$customexp[0].'"></td>';
															echo '<td><b><input type="text" name="custom_value_'.$thenumber.'" value="'.$customexp[1].'" class="regular-text"></b></td></tr>';
														}
														?>
													</tbody>
												</table><br><a href="javascript:{}" onclick="document.getElementById('editreservation').submit(); return false;" class="button-primary"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a></form><br>
												<div  style="width:550px"><?php echo easyreservations_detailed_price($id); ?><?php echo $information; ?></div>
												</td><td style="width:1%"></td>
												<td valign="top">
												<form id="addcustomfield" name="addcustomfield" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>&addcustomfield=<?php echo $edit; ?>"> 
												<input type="hidden" name="addcustom" id="addcustom" value="<?php echo $edit; ?>">
													<table class="widefat">
														<thead>
															<tr>
																<th><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td nowrap><input type="text" name="customtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';"><br><textarea type="text" name="customvalue" value="Value" style="width:260px" onfocus="if (this.value == 'Value') this.value = '';">Value</textarea>
																<br><br><p><a href="javascript:{}" onclick="document.getElementById('addcustomfield').submit(); return false;" class="button-secondary"><span><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></span></a></p></td>
															</tr>
														</tbody>
													</table>
												</form>
												</td></tr></table>
									<?php } 

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

										if((isset($approve) || isset($delete)) && !isset($reservation_approve)) {
										if($delete){ $delorapp=$delete; $delorapptext='reject'; } elseif($approve){ $delorapp=$approve; $delorapptext='approve'; }

										if(reservations_check_type($delorapp) == __('approved', 'easyReservations' )) $color='#1FB512';
										if(reservations_check_type($delorapp) == __('pending' , 'easyReservations' )) $color='#3BB0E2';
										if(reservations_check_type($delorapp) == __('rejected' , 'easyReservations' )) $color='#D61111';
										if(reservations_check_type($delorapp) == __('trashed' , 'easyReservations' )) $color='#870A0A';?>  <!-- Content will only show on delete or approve Reservation //--> 
											</td><td  style="width:1%;"></td><td  style="width:35%;" valign="top" style="vertical-align:top;"><br>
											<form method="post" action="<?php echo $pageURL;?>?page=reservations<?php if($approve) echo "&approve=".$approve ;  if($delete) echo "&delete=".$delete ;?>"  id="reservation_approve" name="reservation_approve">
												<input type="hidden" name="action" value="reservation_approve"/>
												<?php if($approve) { ?><input type="hidden" name="approve" value="yes" /><?php } ?>
												<?php if($delete) { ?><input type="hidden" name="delete" value="yes" /><?php } ?><br>
												<table class="widefat" style="margin-top:-18px;" cellspacing="0" cellpadding="0">
													<thead>
														<tr>
															<th><?php if($approve) {  printf ( __( 'Approve the Reservation' , 'easyReservations' ));  }  if($delete) {  printf ( __( 'Reject the Reservation' , 'easyReservations' ));  } ?><b/></th>
														</tr>
													</thead>
													<tbody>
													<tr>
														<td nowrap><div class="explainbox" style="width:96%"><div id="left"><b><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> <?php echo easyreservations_get_price($delorapp); ?></b></div><div id="right"><span style="float:right"><?php echo reservations_get_administration_links($delorapp,  $delorapptext);?></span></div><div id="center"><b style="text-transform: capitalize;color:<?php echo $color; ?>"> <?php echo reservations_check_type($delorapp); ?></b><?php echo $paystatus; ?></div></div></td>
													</tr>
														<?php if($approve){ ?><tr>
															<td><?php printf ( __( 'Room:' , 'easyReservations' ));?> <?php echo __($rooms);?> # <select id="roomexactly" name="roomexactly">
																		<?php
																		$roomcounty=get_post_meta($room, 'roomcount', true);
																		$ix="0";
																		while($ix < $roomcounty){
																		$ix++;
																		echo '<option value="'.$ix.'">'.$ix.'</option>';
																		}  echo '</select>';
																		?></b></td>
														</tr><?php } ?>
														<tr>
															<td>
																	<p><input type="checkbox" name="sendmail" checked><small> <?php printf ( __( 'Send Mail to Guest' , 'easyReservations' ));  ?></small> <input type="checkbox" name="hasbeenpayed"><small>  <?php printf ( __( 'Has been paid' , 'easyReservations' ));  ?></small></p>
																	<p><?php printf ( __( 'To' , 'easyReservations' ));?> <?php if($approve) { printf ( __( 'Approve' , 'easyReservations' )); } if($delete) { printf ( __( 'Reject' , 'easyReservations' ));}?> <?php printf ( __( 'the Reservation, write a message and press Send' , 'easyReservations' ));?> & <?php if($approve) { echo "Approve"; } if($delete) { echo "Reject";}?>". <?php printf ( __( 'The Customer will recieve that message in an eMail' , 'easyReservations' ));?>.</p>
																	<p class="label"><strong>Text:</strong></p>
																	<textarea cols="60" rows="4" name="approve_message" class="text-area-1" width="100px"></textarea>
															</td>
													</tbody>
												</table>
													<?php if($approve) { ?><p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;"  class="button-primary"><span>Send & Approve</span></a></p><?php } ?>
													<?php if($delete) { ?><p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;" class="button-primary"><span>Send & Reject</span></a></p><?php } ?>
											</form><td></tr></table>
									<?php } ?>
									<?php if(isset($reservation_approve) && $reservation_approve=="reservation_approve") { ?> <!-- Content will only show after approe or delete Reervation //--> 
											<table>
												<tr>
													<td>
														<div style="height:300px;">
															<p style="align:center; font-weight:bold;" ><?php printf ( __( 'Message has been sent!' , 'easyReservations' ));?></p>
														</div>
													</td>
												</tr>
											</table>
									<?php }
									}
function reservation_add_reservaton() {
				global $wpdb;

				if(isset($_POST["action"])){ 
					$action = $_POST['action'];
				}	

				if($action == "addreservation") {

					$date=$_POST["date"];
					$name=$_POST["name"];
					$email=$_POST["email"];
					$phone=$_POST["phone"];
					$room=$_POST["room"];
					$note=$_POST["note"];
					$nights=$_POST["nights"];
					$persons=$_POST["persons"];
					$specialoffer=$_POST["specialoffer"];
					$address=$_POST["address"];

					$timestampanf=strtotime($date);
					$timestampend=strtotime($nights);
					$anznights=round(($timestampend-$timestampanf)/60/60/24);
					$dat=date("Y-m", $timestampanf);
					$rightdate=date("Y-m-d", $timestampanf);
					$rightdate2=date("Y-m-d", $timestampend);

					$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(arrivalDate, name, email, notes, nights, dat, room, number, special, approve ) 
					VALUES ('$rightdate', '$name', '$email', '$note', '$anznights', '$dat', '$room', '$persons', '$specialoffer', '' )"  ) ); 

					$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Reservation added!' , 'easyReservations' ).'</p></div>';

					?><meta http-equiv="refresh" content="1; url=admin.php?page=reservations&typ=pending"><?php
				}

				$room_category = get_option("reservations_room_category");
				$special_offer_cat = get_option("reservations_special_offer_cat");
			echo $prompt;?>
			<script>
			  $(document).ready(function() {
				$("#datepicker").datepicker( { altFormat: 'dd.mm.yyyy' });
				$("#datepicker2").datepicker( { altFormat: 'dd.mm.yyyy' });
			  });
			</script>

			<link href="<?php echo  WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css'; ?>" rel="stylesheet" type="text/css"/>
			<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery.min.js"></script>
			<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
			<div id="icon-options-general" class="icon32"><br></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;"><?php printf ( __( 'Add Reservation' , 'easyReservations' ));?></h2>
			<div id="wrap">
				<?php if($edit){ ?><form method="post" action=""  id="edit"><input type="hidden" name="action" value="edit"/><?php } else {?><form method="post" action=""  id="addreservation"><input type="hidden" name="action" value="addreservation"/><?php } ?>								
						<table class="widefat" style="width:50%;">
						<tbody>
						<tr valign="top">
							<td  style="vertical-align:text-bottom;" nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td>
							<td><input type="text" name="name" align="middle" class="regular-text"></td>
						</tr>
						<tr valign="top" class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png" > <?php printf ( __( 'Date' , 'easyReservations' ));?></td>
							<td style="vertical-align:middle;"><input type="text" id="datepicker" name="date" style="width:70px" class="regular-text"> - <input type="text" id="datepicker2" name="nights" style="width:70px" class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td>
							<td><input type="text" name="email"class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td>
							<td><select name="persons" style="width:40px;"><?php 
								for($ix=1;$ix <= 99;$ix++){
								echo '<option value="'.$ix.'">'.$ix.' </option>'; 
								}  ?></select><span class="description"> Number of persons</span></td>
						</tr>
						<tr valign="top" class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?></td>
							<td><select id="room" name="room"><?php 
								$argss = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
								$roomcategories = get_posts( $argss );
									foreach( $roomcategories as $roomcategorie ){
											echo '<option value="'.$roomcategorie->ID.'">'.__($roomcategorie->post_title).'</option>';
									}  ?>
							</select>
							</td></tr>
						<tr valign="top">
							<td nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png" > <?php printf ( __( 'Special Offer' , 'easyReservations' ));?></td>
							<td><select name="specialoffer"><option value="0" select="selected"><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php 
								$argss = array( 'type' => 'post', 'category' => $special_offer_cat, 'orderby' => 'post_title', 'order' => 'ASC');
								$specialcategories = get_posts( $argss );
									foreach( $specialcategories as $specialcategorie ){
										if($specialcategorie->ID!=$special){
											echo '<option value="'.$specialcategorie->ID.'">'.__($specialcategorie->post_title).'</option>';
										}
									} ?>
							</select></td></tr>
							<tr valign="top">
								<td nowrap><img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Notes' , 'easyReservations' ));?></td>
								<td><textarea name="note" cols="42" rows="10"></textarea><br><br><br><a href="javascript:{}" onclick="document.getElementById('addreservation').submit(); return false;" class="button-secondary"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><br><br></td>
							</tr>
							</tbody></table></form></div>
<?php }
?>