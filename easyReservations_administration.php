<?php
function reservation_main_page() {

	global $wpdb;
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
	if(isset($_POST['approve'])) {
		$post_approve=$_POST['approve'];
	}
	if(isset($_POST['editthereservation'])){
		$editthereservation=$_POST['editthereservation'];
	}	
	if(isset($_POST['datepicker'])){
		$datepicker=$_POST['datepicker'];
		$startchooser=round((strtotime($datepicker) - time())/60/60/24)+1;
	}

	if(isset($_GET['more'])) $moreget=$_GET['more'];
	if(isset($_GET['orderby'])) $orderby=$_GET['orderby'];
	if(isset($_GET['perpage'])) {
		$perpage=$_GET['perpage'];
		update_option("reservations_on_page",$perpage);
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
			$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>'.$anzahl.' moved to Trash. <a href="admin.php?page=reservations&bulkArr[]='.$linkundo.'&bulk=2">Undo</a></p></div>';

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
			$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>'.$anzahl.' restored from the Trash.</p></div>';

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
			$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>'.$anzahl.' '.__('deleted permanently', 'easyReservations').'</p></div>';
			}
		}
	}

	if(isset($post_approve) && $post_approve=="yes"){
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes', roomnumber='$roomexactly' WHERE id=$approve"  ) ); 	
		if($sendmail=="on"){
			$emailformation=get_option('reservations_email_to_userapp_msg');
			preg_match_all(' /\[.*\]/U', $emailformation, $matchers); 
			$mergearrays=array_merge($matchers[0], array());
			$edgeoneremoave=str_replace('[', '', $mergearrays);
			$edgetworemovess=str_replace(']', '', $edgeoneremoave);
				foreach($edgetworemovess as $fieldsx){
					$field=explode(" ", $fieldsx);
					if($field[0]=="adminmessage"){
						$emailformation=preg_replace('['.$fieldsx.']', $approve_message, $emailformation);
					}
					elseif($field[0]=="thename"){
						$emailformation=preg_replace('['.$fieldsx.']', $name, $emailformation);
					}
					elseif($field[0]=="phone"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$phone.'', $emailformation);
					}
					elseif($field[0]=="email"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$mail_to.'', $emailformation);
					}
					elseif($field[0]=="arrivaldate"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.date("d.m.Y", $timpstampanf).'', $emailformation);
					}
					elseif($field[0]=="to"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.date("d.m.Y", $timestampend).'', $emailformation);
					}
					elseif($field[0]=="nights"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$reservationFrom.'', $emailformation);
					}
					elseif($field[0]=="address"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$addresse.'', $emailformation);
					}
					elseif($field[0]=="message"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$message_r[0].'', $emailformation);
					}
					elseif($field[0]=="persons"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$persons.'', $emailformation);
					}
					elseif($field[0]=="room"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.__($rooms).'', $emailformation);
					}
					elseif($field[0]=="offer"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.__($specials).'', $emailformation);
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
			?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
	}

	if(isset($post_delete) && $post_delete=="yes"){
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
						$emailformation=preg_replace('['.$fieldsx.']', $approve_message, $emailformation);
					}
					elseif($field[0]=="thename"){
						$emailformation=preg_replace('['.$fieldsx.']', $name, $emailformation);
					}
					elseif($field[0]=="phone"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$phone.'', $emailformation);
					}
					elseif($field[0]=="email"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$mail_to.'', $emailformation);
					}
					elseif($field[0]=="arrivaldate"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.date("d.m.Y", $timpstampanf).'', $emailformation);
					}
					elseif($field[0]=="to"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.date("d.m.Y", $timestampend).'', $emailformation);
					}
					elseif($field[0]=="nights"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$reservationFrom.'', $emailformation);
					}
					elseif($field[0]=="address"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$addresse.'', $emailformation);
					}
					elseif($field[0]=="message"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$message_r[0].'', $emailformation);
					}
					elseif($field[0]=="persons"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.$persons.'', $emailformation);
					}
					elseif($field[0]=="room"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.__($rooms).'', $emailformation);
					}
					elseif($field[0]=="offer"){
						$emailformation=preg_replace('['.$fieldsx.']', ''.__($specials).'', $emailformation);
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
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'Reservations deleted permanently' , 'easyReservations' ).'</p></div>';
	}
	if(isset($editthereservation)){

			global $wpdb;

			$name=$_POST["name"];
			$date=$_POST["date"];
			$dateend=$_POST["dateend"];
			$email=$_POST["email"];
			$phone=$_POST["phone"];
			$roomex=$_POST["roomexactly"];
			$room=$_POST["room"];
			$note=$_POST["note"];
			$nights=$_POST["nights"];
			$persons=$_POST["persons"];
			$specialoffer=$_POST["specialoffer"];
			$address=$_POST["address"];

			$timestampstartedit=strtotime($date);				
			$timestampendedit=strtotime($dateend);				
			$dat=date("Y-m", $timestampstartedit);
			$rightdate=date("Y-m-d", $timestampstartedit);
			$calcdaysbetween=round(($timestampendedit-$timestampstartedit)/60/60/24);

			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$rightdate', nights='$calcdaysbetween', name='$name', phone='$phone', email='$email', notes='$note*/*$address', room='$room', number='$persons', special='$specialoffer', dat='$dat', roomnumber='$roomex' WHERE id='$edit' ")); 

			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Reservation edited!' , 'easyReservations' ).'</p></div>';
	}
	
	if(isset($approve)  || isset($delete) || isset($view) || isset($edit)) { //Query of View Reject and Approve
		$sql_approvequerie = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id='$approve' OR id='$delete' OR id='$view' OR id='$edit'";

		$approvequerie = $wpdb->get_results($sql_approvequerie );

			$id=$approvequerie[0]->id;
			$name=$approvequerie[0]->name;
			$reservationFrom=$approvequerie[0]->nights;
			$reservationDate=$approvequerie[0]->arrivalDate;
			$phone=$approvequerie[0]->phone;
			$room=$approvequerie[0]->room;
			$special=$approvequerie[0]->special;
			$exactlyroom=$approvequerie[0]->roomnumber;
			$persons=$approvequerie[0]->number;
			$mail_to=$approvequerie[0]->email;
			$message_r=explode("*/*", $approvequerie[0]->notes);

			if(isset($approve)  || isset($delete) || isset($view)) $roomwhere="AND room='$room'"; // For Overview only show date on view
			$roomsgetpost=get_post($room);
			$rooms=$roomsgetpost->post_title;

			if(!$message_r[1]) $addresse = 'None'; else $addresse = $message_r[1];

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

	$csv_hdr = "Name, Arrival Date, Desination Date, Nights, eMail, Persons, Price, Room, Special Offer"; // Header for Output

	?><div id="icon-themes" class="icon32"></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;">Reservations <a class="add-new-hari" href="admin.php?page=add-reservation" rel="simple_overlay" style="width:20px;heigth:29px;"href="#">Add New</a></h2><div id="wrap"><?php if($prompt) echo ' '.$prompt; ?>
		<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START OVERVIEW
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if((isset($approve) || isset($delete) || isset($view)) OR $show_overview_on_list == 1){ ?>			
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
					$sql_R = "SELECT DISTINCT room FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $roomwhere ORDER BY room ASC";
					$results = $wpdb->get_results( $sql_R );
						foreach( $results as $result ) {
							$roomsidentify=$result->room;
							$roomcounty=get_post_meta($roomsidentify, 'roomcount', true);
							$rowcount="0";
							while($roomcounty > $rowcount){
								$rowcount++; ?>
								<tr><td onclick="<?php if($approve){ ?>document.reservation_approve.roomexactly.selectedIndex=<?php echo $rowcount-1; ?>;<?php } if($edit){ ?>document.editreservation.roomexactly.selectedIndex=<?php echo $rowcount; ?>;document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationFrom*86400))?>';setVals(<?php echo $roomsidentify; ?>);<?php } if ($edit OR $approve) { ?>this.style.background='<?php echo "url(".WP_PLUGIN_URL ."/easyreservations/images/stare2.png) right center no-repeat "; ?>';<?php for($xixi=0; $xixi <= $reservationFrom; $xixi++){ if($xixi==0){ $backgroundclick="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_start.png) no-repeat ".$colorbackgroundfree.""; } elseif($xixi==$reservationFrom){ $backgroundclick="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_end.png) no-repeat ".$colorbackgroundfree.""; } else { $backgroundclick="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat"; } ?>document.getElementsByName('hoverclass<?php echo $roomsidentify.$rowcount; ?>')[<?php echo $xixi; ?>].style.background='<?php echo $backgroundclick; ?>';<?php } } ?>" ondblclick="<?php if($approve){ ?>document.reservation_approve.roomexactly.selectedIndex=0;<?php } if($edit){ ?>document.editreservation.roomexactly.selectedIndex=0;document.editreservation.room.selectedIndex=0;<?php } if ($edit OR $approve) { ?><?php for($xixi=0; $xixi <= $reservationFrom; $xixi++){ ?>document.getElementsByName('hoverclass<?php echo $roomsidentify.$rowcount; ?>')[<?php echo $xixi; ?>].style.background='<?php echo $colorbackgroundfree; ?>';<?php } ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationFrom*86400)); ?>';this.style.background='<?php echo "url(".WP_PLUGIN_URL ."/easyreservations/images/starempty.png) right center no-repeat"; ?>'; <?php } ?>" <?php if ($edit OR $approve) { ?>onmouseover="this.style.background='<?php echo "url(".WP_PLUGIN_URL ."/easyreservations/images/starempty.png) right center no-repeat "; ?>'; " onmouseout="this.style.background=''" <?php } ?> style="text-shadow:none; border-style:none; width: 160px;max-height: 30px;background:#f9f9f9; vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid #dfdfdf;" nowrap><?php echo get_the_title($roomsidentify).' '.$rowcount; ?></td>
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

									$sql_R2 = "SELECT name, nights, arrivalDate, room FROM ".$wpdb->prefix ."reservations WHERE room='$roomsidentify' AND roomnumber='$rowcount' AND approve='yes' AND  roomnumber != '' AND '$datedatumtodaydate' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ORDER BY arrivalDate ASC";
									$results2 = $wpdb->get_results( $sql_R2 );
									$itwasfull=0;

									foreach( $results2 as $result){
									$fullcount++;
										$name=$result->name;
										$expl= explode(" ", $name);
										$nights=$result->nights;
										$timpstamp_start=strtotime($result->arrivalDate)+86400;
										$eintags12=60*60*24*$showdatenumber_start;
										$datetodays=$timevariable+$eintags12;
										$anznights=60*60*24*$nights;
										$timestamp_end=$anznights+$timpstamp_start;

										if($datetodays >= $timpstamp_start AND $datetodays <= $timestamp_end){
											$itwasfull++;
											$farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat";
											if($letztername!=$name){
												$farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_start.png) no-repeat ".$colorbackgroundfree."";
											}

											if($cellcount=="1") $farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat";
											if($letztername != "") $farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat"; ?>

											<td onclick="<?php if($edit){ ?>document.editreservation.roomexactly.selectedIndex=<?php echo $rowcount; ?>;document.getElementById('datepicker').value='<?php echo date("d.m.Y",$datetodays-86400)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$datetodays+($reservationFrom*86400)-86400)?>';setVals(<?php echo $roomsidentify; ?>); <?php } ?>" title="Name: <?php echo $name; ?><br><?php printf ( __( 'Date' , 'easyReservations' ));?>: <?php echo date("d.m.Y",$datetodays-86400)?><br><?php printf ( __( 'Room' , 'easyReservations' ));?>: <?php echo __(get_the_title($roomsidentify))?> # <?php echo $rowcount; ?><br><?php printf ( __( 'Status: Occupied' , 'easyReservations' ));?>"  style="background: <?php echo $farbe;?>; color:<?php echo $fontcoloriffull; ?>; text-align:center; overflow:hidden; text-shadow:none; border-style:none; text-decoration:none; font: normal 12px Arial, sans-serif; vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $colorborder; ?>; border-left:  <?php echo $borderside; ?>px solid <?php echo $colorborder; ?>;">
												<?php echo date("d",$datetodays-86400); ?></div>
											</td><?php
											$letztername=$name;
										}
									}   
									if($itwasfull == 0) { 
												if($letztername AND $cellcount != '1'){ 
													$farbe2='url('.WP_PLUGIN_URL .'/easyreservations/images/'.$colorfull.'_end.png) no-repeat '.$colorbackgroundfree.''; 
												} else { 
													$farbe2=$colorbackgroundfree;
												}
												$fullcount=0;
												if($timpstampanf+86400 <= $datumtoday AND $datumtoday <= $timestampend+86400*2){
												$hoverclass='name="hoverclass'.$roomsidentify.$rowcount.'"';
												} else { $hoverclass=""; }
												?>
												<td <?php echo $hoverclass;?> onclick="<?php if($edit){ ?>document.editreservation.roomexactly.selectedIndex=<?php echo $rowcount; ?>;document.getElementById('datepicker').value='<?php echo date("d.m.Y",$datumtoday-86400)?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$datumtoday+($reservationFrom*86400)-86400)?>';setVals(<?php echo $roomsidentify; ?>); <?php } ?>" " title="<?php echo __( 'Date' , 'easyReservations' ).' '.date("d.m.Y",$datumtoday-86400); ?><br><?php echo __( 'Room' , 'easyReservations' ).': '.__(get_the_title($roomsidentify)).' # '.$rowcount.'<br>'.__( 'Status: Empty' , 'easyReservations' ); ?>" style="height:30px; text-align:center;text-shadow:none; border-style:none; vertical-align: middle;  color:<?php echo $fontcolorifempty; ?>; border-bottom: <?php echo $borderbottom;?>px solid <?php echo $colorborder;?>; border-left: <?php echo $borderside;?>px solid <?php echo $colorborder;?>; background:<?php echo $farbe2;?>"><?php echo  date("d",$datumtoday-86400); ?></td>												<?php
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
<?php if($edit OR $view){ ?>
$(document).ready(function(){
		$("#dateas").datepicker({ 
			showOn: 'button', 
			buttonImageOnly: true, 
			rangeSelect: true,
			buttonImage: '<?php echo WP_PLUGIN_URL?>/easyreservations/images/day.png',
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
							if(!isset($approve) && !isset($delete) && !isset($view)  && !isset($edit)) {  ?>
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

														if($typ=="" or $typ=="active" or !$typ) { $type="yes"; $items=$items1; $typlink="&typ=active"; } // If type is actice
														if($typ=="pending") { $type=""; $items=$items3; $typlink="&typ=pending"; } // If type is pending
														if($typ=="deleted") { $type="no"; $items=$items2; $typlink="&typ=deleted";} // If type is rejected
														if($typ=="old") { $type="yes"; $items=$items4; $typlink="&typ=old"; $zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY < NOW()";} // If type is old
														if($typ=="trash") { $type="del"; $items=$items5; $typlink="&typ=trash"; $zeichen=""; } // If type is trash

														if($order=="ASC") { $orderlink="&order=ASC"; $orders="ASC";}
														elseif($order=="DESC") { $orderlink="&order=DESC"; $orders="DESC";}

														if($orderby=="date") { $orderbylink="&orderby=date"; $ordersby="arrivalDate";}
														elseif($orderby=="name") { $orderbylink="&orderby=name"; $ordersby="name";}
														elseif($orderby=="room") { $orderbylink="&orderby=room"; $ordersby="room";}
														elseif($orderby=="special") { $orderbylink="&orderby=special"; $ordersby="special";}
														elseif($orderby=="nights") { $orderbylink="&orderby=nights"; $ordersby="nights";}
														else { $ordersby="arrivalDate"; $orders="ASC"; }

														if($specialselector != 0 AND $specialselector != "") { $specialsql="AND special='".$specialselector."'"; $speciallink="&orderby=".$specialselector;   }
														if($roomselector != 0 AND $roomselector != "") { $roomsql="AND room='".$roomselector."'"; $roomlink="&orderby=".$roomselector;  } 
														if($monthselector != 0 AND $monthselector != "") { $monthsql="AND dat='".$monthselector."'"; $monthlink="&orderby=".$monthselector; }
														if($perpage != 0) { $perpagelink="&perpage=".$perpage; }
														if($perpage == 0) $perpage=$reservations_on_page;
														if($more != 0) $morelink="&more=";

														if($items > 0) {
																$p = new pagination;
																$p->items($items);
																$p->limit($perpage); // Limit entries per page
																if($search) $p->target("admin.php?page=reservations&search=".$search.""); else $p->target("admin.php?page=reservations".$typlink."".$orderbylink."".$orderlink."".$perpagelink."".$speciallink."".$roomlink."".$monthlink);
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
															<li class="all"><a href="admin.php?page=reservations&typ=active" class="current"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li class="old"><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="pending"){ ?>
															<li class="all"><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending" class="current"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li class="trash"><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="deleted"){ ?>
															<li class="all"><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted" class="current"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li class="trash"><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="old"){ ?>
															<li class="all"><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old" class="current"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li class="trash"><a href="admin.php?page=reservations&typ=trash">Trash<span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="trash"){ ?>
															<li class="all"><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=trash" class="current"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li>
															<?php } ?>
														</ul>
													</td>
													<td style="width:60%; text-align:center; font-size:12px;" nowrap><form method="get" action="admin.php"><input type="hidden" name="page" value="reservations"> <!-- Begin of Filter //--> 
																				<select name="monthselector"><option value="0"><?php printf ( __( 'Show all Dates' , 'easyReservations' ));?></option><!-- Filter Months //--> 
																					<?php 
																					$posts = "SELECT DISTINCT dat FROM ".$wpdb->prefix ."reservations WHERE arrivalDate >= NOW() GROUP BY dat ORDER BY dat ";
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
																					$name=$result->post_title;																	
																					$categ=get_the_category($id);
																					$care=$categ[0]->term_id;

																					if($care==$room_category) { echo '<option value="'.$id.'">'.__($name).'</option>'; } 
																					} ?>
																			</select> <select name="specialselector" class="postform"><option value="0"><?php printf ( __( 'View all Offers ' , 'easyReservations' ));?></option> <!-- Filter Special Offers //-->
																					<?php 

																					foreach( $results as $result )	{			
																						$id=$result->ID;	
																						$name=$result->post_title;																	
																						$categ=get_the_category($id);
																						$care=$categ[0]->term_id;

																						if($care==$offer_cat) { echo '<option value="'.$id.'">'.__($name).'</option>'; }
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
															<th><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
															<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" style="background-position:<?php echo $marginroom;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" style="background-position:<?php echo $marginroom;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:<?php echo $marginroom;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
															<th><?php if($order=="ASC" and $orderby=="special") { ?><a class="asc2" style="background-position:<?php echo $marginspecial;?>  8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="special") { ?><a class="desc2" style="background-position:<?php echo $marginspecial;?> 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
																<?php } else { ?><a class="stand2"  style="background-position:<?php echo $marginspecial;?> 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
															<th><?php printf ( __( 'Address' , 'easyReservations' )); ?></th>
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
															<th><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
															<th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" style="background-position:46px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" style="background-position:46px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
																<?php } else { ?><a class="stand2" style="background-position:46px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
															<th><?php if($order=="ASC" and $orderby=="special") { ?><a class="asc2" style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
																<?php } elseif($order=="DESC" and $orderby=="special") { ?><a class="desc2" style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
																<?php } else { ?><a class="stand2"  style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
															<th><?php if($reservation_form_address == "0"){ printf ( __( 'Message' , 'easyReservations' )); } else {  printf ( __( 'Address' , 'easyReservations' )); }?></th>
														</tr></tfoot><tbody>
													<?php
														$nr="0";
														if($search) {
															$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE name like '%$search%' OR email like '%$search%' OR notes like '%$search%' OR arrivalDate like '%$search%' $limit"; // Search query
														} else {
														   $sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE approve='$type' $monthsql $roomsql $specialsql $zeichen ORDER BY $ordersby $orders $limit";  // Main Table query
														}
														$result = mysql_query($sql) or die (mysql_error());

															if(mysql_num_rows($result) > 0 ){
															while ($row = mysql_fetch_assoc($result)) {
																	$id=$row['id'];
																	$name = $row['name'];
																	$nights=$row['nights'];
																	$person=$row['number'];

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
															<td width="7%" nowrap><?php echo easyreservations_price_calculation($id)."&".get_option('reservations_currency').";" ; ?><?php $csv_output .= $pricecalculation['price'] . ", "; ?></td>
															<td width="12%" nowrap><?php echo __($rooms) ;?> <?php echo $row['roomexactly'] ;?><?php $csv_output .= __($rooms) . ", "; ?></td>
															<td width="12%" nowrap><?php if($special==0) echo 'None'; else echo __($specials);?><?php $csv_output .= __($specials) . "\n"; ?></td>
															<td width="13%"><?php if($message[1]){ echo substr($message[1], 0, 36); } else { echo __( 'None' , 'easyReservations' ); } ?></td>
														</tr>
													<?php }
													} else { ?> <!-- if no results form main quary !-->
															<tr>
																<td></td><td><?php printf ( __( 'No Reservations found!' , 'easyReservations' ));?></td><td></td><td></td><td></td><td></td><td></td><td></td> <!-- Mail Table Body if empty //-->
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
											if((isset($approve) || isset($delete) || isset($view)) && !isset($reservation_approve)) { 
											if($nr%2==0) $class="alternate"; else $class="";?> <!-- // Content will only show on delete, view or approve Reservation -->

											<?php if(!isset($view)){ ?><table  style="width:99%;"><tr><td style="width:64%;" valign="top"><?php } else { $width='style="width:59%;"'; echo '<br>'; } ?>
												<br><table class="widefat" <?php echo $width; ?>>
													<thead>
														<tr>
															<th colspan="2"><?php if($approve) { echo __( 'Approve' , 'easyReservations' ); } if($delete) { echo __( 'Reject' , 'easyReservations' );  } if($view) { echo __( 'View' , 'easyReservations' ); } echo ' '.__( 'Reservation' , 'easyReservations' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $name;?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $reservationFrom;?>)</small></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $mail_to;?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/phone.png"> <?php printf ( __( 'Phone' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $phone;?></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo $persons;?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
															<td><b><?php echo __($rooms);?></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png"> <?php printf ( __( 'Special Offer' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php if($specials){ echo __($specials);} else { printf ( __( 'None' , 'easyReservations' )); }  ?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/money.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php echo easyreservations_price_calculation($id)."&".get_option('reservations_currency').";" ; ?></b></td>
														</tr>
														<tr>
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/house.png"> <?php printf ( __( 'Address' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php if($message_r[1]){ echo __($message_r[1]); } else { printf ( __( 'Not asked for' , 'easyReservations' )); } ?></b></td>
														</tr>
														<tr class="alternate">
															<td style="vertical-align:text-bottom;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
															<td><b><?php echo $message_r[0];?></b></td>
														</tr>
													</tbody>
												</table>

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
												<form id="editreservation" name="editreservation" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>"> 
												<input type="hidden" name="editthereservation" id="editthereservation" value="editthereservation">
												<table class="widefat" style="width:50%">
													<thead>
														<tr>
															<th colspan="2"><?php printf ( __( 'Edit Reservation' , 'easyReservations' ));?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="2"><div class="explainbox" style="width:94%; margin-bottom:2px;"><div id="left"><b><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/money.png"> <?php echo easyreservations_price_calculation($id)."&".get_option('reservations_currency').";" ; ?></b></div><div id="right"><span style="float:right"><?php echo reservations_get_administration_links($edit, 'edit');?></span></div><div id="center"><b style="text-transform: capitalize;color:<?php echo $color; ?>"> <?php echo reservations_check_type($edit); ?></b></div></div></td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
															<td><input type="text" name="name" align="middle" value="<?php echo $name;?>" class="regular-text"></td>
														</tr>
														<tr  class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
															<td><input type="text" id="datepicker" style="width:70px" name="date" value="<?php echo date("d.m.Y",$timpstampanf); ?>" class="regular-text"> <b>-</b> <input type="text" id="datepicker2" style="width:70px" name="dateend" value="<?php echo date("d.m.Y",$timestampend); ?>" class="regular-text"></td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
															<td><input type="text" name="email" value="<?php echo $mail_to;?>" class="regular-text"></td>
														</tr>
														<tr  class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/phone.png"> <?php printf ( __( 'Phone' , 'easyReservations' ));?>:</td> 
															<td><input type="text" name="phone" value="<?php echo $phone;?>" class="regular-text"></td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
															<td><select name="persons"><option value="<?php echo $persons;?>" select><?php echo $persons;?></option> <?php
															for($countpersons=1; $countpersons < 100; $countpersons++){
															echo '<option value="'.$countpersons.'">'.$countpersons.'</option>';
															}
															?></select></td>
														</tr>
														<tr  class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
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
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png"> <?php printf ( __( 'Special Offer' , 'easyReservations' ));?>:</b></td> 
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
														<tr  class="alternate">
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/house.png"> <?php printf ( __( 'Address' , 'easyReservations' ));?>:</b></td> 
															<td ><textarea name="address" cols="30" rows="1"><?php if($edit) echo $message_r[1];?></textarea></td>
														</tr>
														<tr>
															<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
															<td><textarea name="note" cols="42" rows="10"><?php echo $message_r[0];?></textarea><br><br></td>
														</tr>
													</tbody>
												</table><br><a href="javascript:{}" onclick="document.getElementById('editreservation').submit(); return false;" class="button-primary"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a></form>
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
														<td nowrap><div class="explainbox" style="width:96%"><div id="left"><b><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/money.png"> <?php $pricecalculation=easyreservations_price_calculation($delorapp); echo $pricecalculation['price']."&".get_option('reservations_currency').";" ; ?></b></div><div id="right"><span style="float:right"><?php echo reservations_get_administration_links($delorapp,  $delorapptext);?></span></div><div id="center"><b style="text-transform: capitalize;color:<?php echo $color; ?>"> <?php echo reservations_check_type($delorapp); ?></b></div></div></td>
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
																	<p><input type="checkbox" name="sendmail" checked><small> <?php printf ( __( 'Send Mail to Guest' , 'easyReservations' ));  ?></small></p>
																	<p><?php printf ( __( 'To' , 'easyReservations' ));?> <?php if($approve) { printf ( __( 'Approve' , 'easyReservations' )); } if($delete) { printf ( __( 'Reject' , 'easyReservations' ));}?> <?php printf ( __( 'the Reservation, write a message and press "Send' , 'easyReservations' ));?> & <?php if($approve) { echo "Approve"; } if($delete) { echo "Reject";}?>". <?php printf ( __( 'The Customer will recieve that message in an eMail' , 'easyReservations' ));?>.</p>
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

					$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(arrivalDate, name, phone, email, notes, nights, dat, room, number, special, approve ) 
					VALUES ('$rightdate', '$name', '$phone', '$email', '$note*/*$address', '$anznights', '$dat', '$room', '$persons', '$specialoffer', '' )"  ) ); 

					$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>Reservation added!</p></div>';
					?><meta http-equiv="refresh" content="1; url=admin.php?page=reservations&typ=pending"><?php
				}

				$room_category = get_option("reservations_room_category");
				$special_offer_cat = get_option("reservations_special_offer_cat");
				$reservation_form_address=get_option("reservation_form_address");
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
							<td  style="vertical-align:text-bottom;" nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td>
							<td><input type="text" name="name" align="middle" class="regular-text"></td>
						</tr>
						<tr valign="top" class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/day.png" > <?php printf ( __( 'Date of Arrvial' , 'easyReservations' ));?></td>
							<td style="vertical-align:middle;"><input type="text" id="datepicker" name="date" style="width:70px" class="regular-text"> - <input type="text" id="datepicker2" name="nights" style="width:70px" class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td>
							<td><input type="text" name="email"class="regular-text"></td>
						</tr>
						<tr valign="top" class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/phone.png"> <?php printf ( __( 'Phone' , 'easyReservations' ));?></td>
							<td><input type="text" name="phone" class="regular-text"></td>
						</tr>						
						<tr valign="top">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td>
							<td><select name="persons" style="width:40px;"><?php 
								for($ix=1;$ix <= 99;$ix++){
								echo '<option value="'.$ix.'">'.$ix.' </option>'; 
								}  ?></select><span class="description"> Number of persons</span></td>
						</tr>
						<tr valign="top" class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?></td>
							<td><select id="room" name="room"><?php 
								$posts = "SELECT post_title, ID FROM $wpdb->posts WHERE post_type='post' AND post_status='publish'";
								$roomsresult = $wpdb->get_results($posts);

								foreach( $roomsresult as $result )	{			
								$id=$result->ID;	
								$categ=get_the_category($id);
								$care=$categ[0]->term_id;
									if($care==$room_category) {
										echo '<option value="'.$id.'">'.__($result->post_title).'</option>'; 
									} } ?>
							</select>
							</td></tr>
						<tr valign="top">
							<td nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png" > <?php printf ( __( 'Special Offer' , 'easyReservations' ));?></td>
							<td><select name="specialoffer"><option value="0" select="selected"><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php 
								foreach( $roomsresult as $result )	{			
								$id=$result->ID;
								$categ=get_the_category($id);
								$care=$categ[0]->term_id;
								if($care==$special_offer_cat) {	echo '<option value="'.$id.'">'.__($result->post_title).'</option>'; }
								} ?>
							</select></td></tr>
							<tr valign="top" class="alternate">
								<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/house.png"> <?php printf ( __( 'Address' , 'easyReservations' ));?>:</b></td> 
								<td><textarea name="address" cols="30" rows="1"></textarea></td>
							</tr>
							<tr valign="top">
								<td nowrap><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/message.png"> <?php printf ( __( 'Notes' , 'easyReservations' ));?></td>
								<td><textarea name="note" cols="42" rows="10"></textarea><br><br><br><a href="javascript:{}" onclick="document.getElementById('addreservation').submit(); return false;" class="button-secondary"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><br><br></td>
							</tr>
							</tbody></table></form></div>
<?php }
function reservation_settings_page() { //Set Settings
			global $wpdb;

			$countcleans= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() AND approve != 'yes' "));
			
			//Get current Options
			$reservations_price_per_persons = get_option("reservations_price_per_persons");
			$reservations_currency = get_option("reservations_currency");
			$reservation_support_mail = get_option("reservations_support_mail");
			$offer_cat = get_option("reservations_special_offer_cat");
			$room_category = get_option('reservations_room_category');

			// Get current Overview Colors
			$reservations_show_days=get_option("reservations_show_days");
			$border_bottom=get_option("reservations_border_bottom");
			$border_side=get_option("reservations_border_side");
			$backgroundiffull=get_option("reservations_backgroundiffull");
			$fontcoloriffull=get_option("reservations_fontcoloriffull");
			$fontcolorifempty=get_option("reservations_fontcolorifempty");
			$colorborder=get_option("reservations_colorborder");
			$colorbackgroundfree=get_option("reservations_colorbackgroundfree");

			if(isset($_GET["form"])){
				$formnameget = $_GET['form'];
				 $reservations_form=get_option("reservations_form_".$formnameget.""); $howload="reservations ".$formnameget.""; 
			} else {
				$reservations_form=get_option("reservations_form"); $howload="reservations"; 
			}

			if(isset($_GET["deleteform"])){
				$namtetodelete = $_GET['deleteform'];
			} 

			if(isset($_POST["action"])){ 
				$action = $_POST['action'];
			}

			if(isset($_GET["site"])){
				$settingpage = $_GET['site'];
			} else {
				$settingpage="general"; $ifgeneralcurrent='class="current"';
			}

			if($settingpage=="email"){
				$ifemailcurrent='class="current"';
				$reservations_email_to_admin_msg=get_option("reservations_email_to_admin_msg");
				$reservations_email_to_admin_subj=get_option("reservations_email_to_admin_subj");
				$reservations_email_to_userapp_msg=get_option("reservations_email_to_userapp_msg");
				$reservations_email_to_userapp_subj=get_option("reservations_email_to_userapp_subj");
				$reservations_email_to_userdel_msg=get_option("reservations_email_to_userdel_msg");
				$reservations_email_to_userdel_subj=get_option("reservations_email_to_userdel_subj");
			}

			
			if($settingpage=="about") { $settingpage="about"; $ifaboutcurrent='class="current"'; }

			if($action == "reservation_clean_database") {
				$promt='cleaned';
				$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() AND approve != 'yes' ") ); 
			}

		if($action == "reservation_settingss"){
			//Set Reservation settings 
			$reservations_price_per_persons = $_POST["reservations_price_per_persons"];
			$reservationss_support_mail = $_POST["reservations_support_mail"];
			$offer_cat = $_POST["offer_cat"];
			$room_category2 = $_POST["room_category"];
			update_option("reservations_price_per_persons",$reservations_price_per_persons);
			update_option("reservations_room_category",$room_category2);
			update_option("reservations_support_mail",$reservationss_support_mail);
			update_option("reservations_special_offer_cat",$offer_cat);	

			//Set Currency
			$reservations_currency = $_POST["reservations_currency"];
			update_option("reservations_currency",$reservations_currency);
			
			//Set Overview 
			$reservations_show_days = $_POST["reservations_show_days"];
			$border_bottom = $_POST["border_bottom"];
			$border_side = $_POST["border_side"];
			$fontcoloriffull = $_POST["fontcoloriffull"];
			$backgroundiffull = $_POST["backgroundiffull"];
			$fontcolorifempty = $_POST["fontcolorifempty"];
			$colorborder = $_POST["colorborder"];
			$colorbackgroundfree = $_POST["colorbackgroundfree"];
			update_option("reservations_show_days",$reservations_show_days);
			update_option("reservations_border_bottom",$border_bottom);
			update_option("reservations_border_side",$border_side);
			update_option("reservations_backgroundiffull",$backgroundiffull);
			update_option("reservations_fontcoloriffull",$fontcoloriffull);
			update_option("reservations_fontcolorifempty",$fontcolorifempty);
			update_option("reservations_colorborder",$colorborder);
			update_option("reservations_colorbackgroundfree",$colorbackgroundfree);
		}

		if($action == "reservations_email_settings"){
			//Set Reservation Mails
			$reservations_email_to_admin_msg = $_POST["reservations_email_to_admin_msg"];
			$reservations_email_to_admin_subj = $_POST["reservations_email_to_admin_subj"];
			update_option("reservations_email_to_admin_msg",$reservations_email_to_admin_msg);
			update_option("reservations_email_to_admin_subj",$reservations_email_to_admin_subj);

			$reservations_email_to_userapp_msg = $_POST["reservations_email_to_userapp_msg"];
			$reservations_email_to_userapp_subj = $_POST["reservations_email_to_userapp_subj"];
			update_option("reservations_email_to_userapp_msg",$reservations_email_to_userapp_msg);
			update_option("reservations_email_to_userapp_subj",$reservations_email_to_userapp_subj);

			$reservations_email_to_userdel_msg = $_POST["reservations_email_to_userdel_msg"];
			$reservations_email_to_userdel_subj = $_POST["reservations_email_to_userdel_subj"];
			update_option("reservations_email_to_userdel_msg",$reservations_email_to_userdel_msg);
			update_option("reservations_email_to_userdel_subj",$reservations_email_to_userdel_subj);
		}

		if($action == "reservations_form_settings"){ // Change a Form
			// Set Form
			$reservations_form_value = $_POST["reservations_formvalue"];
			$formnamesgets = $_POST["formnamesgets"];
			if($formnamesgets==""){
				update_option("reservations_form", $reservations_form_value);
			} else {
				update_option('reservations_form_'.$formnamesgets.'', $reservations_form_value);
			}
			$reservations_form = $_POST["reservations_formvalue"];
		}

		if($action == "reservation_change_permissions"){ // Change a Form
			$permissionselect = $_POST["permissionselect"];
			update_option('reservations_main_permission', $permissionselect);
		}

		if(isset($namtetodelete)){
			delete_option('reservations_form_'.$namtetodelete.'');
		}
		
		if($action == "reservations_form_add"){// Add Form after check twice for stupid Users :D
			if($_POST["formname"]!=""){
			
				$formname0='reservations_form_'.$_POST["formname"];
				$formname1='reservations_form_'.$_POST["formname"].'_1';
				$formname2='reservations_form_'.$_POST["formname"].'_2';
				
				if(get_option($formname0)==""){
					add_option(''.$formname0.'', ' ', '', 'yes' );
				} elseif(get_option($formname1)==""){
					add_option(''.$formname1.'', ' ', '', 'yes');
				} else { add_option(''.$formname2.'', ' ', '', 'yes'); }
			}
		} 
		if($settingpage=="form"){//Get current Form Options
			$ifformcurrent='class="current"';
			//Form Options
			$form = "SELECT option_name, option_value FROM ".$wpdb->prefix ."options WHERE option_name like 'reservations_form_%' "; // Get User made Forms
			$formresult = $wpdb->get_results($form);
				foreach( $formresult as $result )	{
					$formcutedname=str_replace('reservations_form_', '', $result->option_name);
					if($formcutedname!=""){
					$forms.=' | <a href="admin.php?page=settings&site=form&form='.$formcutedname.'">'.$formcutedname.'</a> <a href="admin.php?page=settings&site=form&deleteform='.$formcutedname.'"><img style="vertical-align:middle;" src="'.WP_PLUGIN_URL.'/easyreservations/images/delete.png"></a>';
					}
				}
		}
		
		$reservations_main_permission=get_option("reservations_main_permission");

?>
<script>
function addtext() {
	var newtext = document.reservations_form_settings.inputstandart.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
function addtextforRoom() {
	var newtext = document.reservations_form_settings.inputstandartroom.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
function addtextforOffer() {
	var newtext = document.reservations_form_settings.inputstandartoffer.value;
	document.reservations_form_settings.reservations_formvalue.value = newtext;
}
function addtextforemail1() {
	var newtext = document.reservations_email_settings.inputemail1.value;
	document.reservations_email_settings.reservations_email_to_admin_msg.value = newtext;
}
function addtextforemail2() {
	var newtext = document.reservations_email_settings.inputemail2.value;
	document.reservations_email_settings.reservations_email_to_userapp_msg.value = newtext;
}
function addtextforemail3() {
	var newtext = document.reservations_email_settings.inputemail3.value;
	document.reservations_email_settings.reservations_email_to_userdel_msg.value = newtext;
}
</script>
<div id="icon-options-general" class="icon32"><br></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;"><?php printf ( __( 'Settings' , 'easyReservations' ));?></h2>
<div id="wrap">


<div class="tabs-box widefat">
	<ul class="tabs">
		<li><a <?php echo $ifgeneralcurrent; ?> href="admin.php?page=settings"><img style="vertical-align:text-bottom ;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/pref.png"> <?php printf ( __( 'General' , 'easyReservations' ));?></a></li>
		<li><a <?php echo $ifformcurrent; ?> href="admin.php?page=settings&site=form"><img style="vertical-align:text-bottom ;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/form.png"> <?php printf ( __( 'Form' , 'easyReservations' ));?></a></li>
		<li><a <?php echo $ifemailcurrent; ?> href="admin.php?page=settings&site=email"><img style="vertical-align:text-bottom ;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'eMails' , 'easyReservations' ));?></a></li>
		<li><a <?php echo $ifaboutcurrent; ?> href="admin.php?page=settings&site=about"><img style="vertical-align:text-bottom ;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/day.png"> <?php printf ( __( 'About' , 'easyReservations' ));?></a></li>
	</ul>
</div><br>

<?php if($settingpage=="general"){ ?>
<table cellspacing="0" style="width:99%">
	<tr cellspacing="0"><td style="width:70%;" >
	<form method="post" action="admin.php?page=settings"  id="reservation_settingss">
		<input type="hidden" name="action" value="reservation_settingss">
			<table class="widefat" style="width:100%;">
			<thead>
				<tr>
					<th style="width:45%;"> <?php printf ( __( 'Overview Style' , 'easyReservations' ));?></th>
					<th style="width:55%;" > </th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
						<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/day.png"> <?php printf ( __( 'Count Days' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Select how many Days to show at the Reservation Overview' , 'easyReservations' ));?>" name="reservations_show_days" value="<?php echo $reservations_show_days;?>" class="regular-text"></td>
				</tr>
				<tr valign="top" class="alternate">
					<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/background.png"> <?php printf ( __( 'Style of Reservations' , 'easyReservations' ));?></td>
					<td><select data-hex="true" title="<?php printf ( __( 'Style of the Reservations in the Reservation Overview' , 'easyReservations' ));?>" name="backgroundiffull" class="regular-text"><option select="selected" value="<?php echo $backgroundiffull;?>" ><?php echo $backgroundiffull;?></option><option value="green" >green</option><option value="red" >red</option><option value="yellow" >yellow</option><option value="blue" >blue</option><option value="pink" >pink</option></select></td>
				</tr>
				<tr valign="top">
					<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/background.png"> <?php printf ( __( 'Backgroundcolor if empty' , 'easyReservations' ));?></td>
					<td nowrap><input type="color" data-hex="true" title="<?php printf ( __( 'Background of the empty Days in the Reservation Overview' , 'easyReservations' ));?>" name="colorbackgroundfree" align="middle" value="<?php echo $colorbackgroundfree;?>" class="regular-text"></td>
				</tr>
				<tr valign="top" class="alternate">
					<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/bordercolor.png"> <?php printf ( __( 'Bordercolor' , 'easyReservations' ));?></td>
					<td><input type="color" data-hex="true" title="<?php printf ( __( 'Color of the Border in the Reservation Overview' , 'easyReservations' ));?>" name="colorborder" align="middle" value="<?php echo $colorborder;?>" class="regular-text"></td>
				</tr>
				<tr valign="top">
					<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/border.png"> <?php printf ( __( 'Border' , 'easyReservations' ));?></td>
					<td><?php printf ( __( 'Bottom' , 'easyReservations' ));?> <select data-hex="true" title="<?php printf ( __( 'Turn the Bottom-Border in the Reservation Overview on or off' , 'easyReservations' ));?>" name="border_bottom"><option select="selected" value="<?php echo $border_bottom;?>"><?php if($border_bottom=="0") printf ( __( 'None' , 'easyReservations' )); if($border_bottom=="1") printf ( __( 'Thin' , 'easyReservations' )); if($border_bottom=="2") printf ( __( 'Thick' , 'easyReservations' ));?></option><option value="0"><?php printf ( __( 'None' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Thin' , 'easyReservations' ));?></option><option value="2"><?php printf ( __( 'Thick' , 'easyReservations' ));?></option></select> <?php printf ( __( 'Side' , 'easyReservations' ));?> <select data-hex="true" title="<?php printf ( __( 'Turn the Side-Border in the Reservation Overview on or off' , 'easyReservations' ));?>" name="border_side"><option select="selected" value="<?php echo $border_side;?>"><?php if($border_side=="0") printf ( __( 'None' , 'easyReservations' )); if($border_side=="1") printf ( __( 'Thin' , 'easyReservations' )); if($border_side=="2") printf ( __( 'Thick' , 'easyReservations' ));?></option><option value="0"><?php printf ( __( 'None' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Thin' , 'easyReservations' ));?></option><option value="2"><?php printf ( __( 'Thick' , 'easyReservations' ));?></option></select></td>
				</tr>
				<tr valign="top" class="alternate">
					<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/font.png"> <?php printf ( __( 'Font Color if Full' , 'easyReservations' ));?></td>
					<td><input type="color" data-hex="true" title="<?php printf ( __( 'Font Color of full Days in the Reservation Overview' , 'easyReservations' ));?>"  name="fontcoloriffull" align="middle" value="<?php echo $fontcoloriffull;?>" class="regular-text"></td>
				</tr>
				<tr valign="top">
					<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/font.png"> <?php printf ( __( 'Font Color if Empty' , 'easyReservations' ));?></td>
					<td><input type="color" data-hex="true" title="<?php printf ( __( 'Font Color of empty Days in the Reservation Overview' , 'easyReservations' ));?>"  name="fontcolorifempty" align="middle" value="<?php echo $fontcolorifempty;?>" class="regular-text"></td>
				</tr>
			</table>
			<br>
			<table class="widefat" style="width:100%;">
				<thead>
					<tr>
						<th style="width:45%;"> <?php printf ( __( 'Reservation Settings' , 'easyReservations' ));?> </th>
						<th style="width:55%;"> </th>
					</tr>
				</thead>
				<tbody style="border:0px">
					<tr valign="top" style="border:0px">
						<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'Reservation Support Mail' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Mail for Reservations' , 'easyReservations' ));?>" name="reservations_support_mail" value="<?php echo $reservation_support_mail;?>" class="regular-text"></td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Rooms Category' , 'easyReservations' ));?></td>
						<td><select  title="<?php printf ( __( 'Choose the Post-Category of Rooms' , 'easyReservations' ));?>" name="room_category" ><option  value="<?php echo $room_category ?>"><?php echo get_cat_name($room_category);?></a></option>
						<?php
							$argss = array( 'type' => 'post' );
							$roomcategories = get_categories( $argss );
								foreach( $roomcategories as $roomcategorie ){
								$id=$roomcategorie->term_id;
									
									if($id!=$room_category) {
										echo '<option value="'.$id.'">'.__($roomcategorie->name).'</option>';
									}
								} ?>
							</select></td>
					</tr>
					<tr valign="top">
						<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png"> <?php printf ( __( 'Special Offers Category' , 'easyReservations' ));?></td>
						<td><select title="<?php printf ( __( 'Choose the Post-Category of Offers' , 'easyReservations' ));?>" name="offer_cat" ><option value="<?php echo $offer_cat ?>" select="selected"><?php echo get_cat_name($offer_cat);?></a></option>
						<?php
								$args = array( 'type' => 'post' );

								$categories = get_categories( $args );
								foreach( $categories as $categorie ){
								$idx=$categorie->term_id;	

									if($idx!=$offer_cat){
										echo '<option value="'.$idx.'">'.__($categorie->name).'</option>'; 
									}
								} ?>
							</select></td>
					</tr>
					<tr valign="top"  class="alternate">
						<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/calc.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td><select name="reservations_price_per_persons" title="<?php printf ( __( 'Select type of Price calculation' , 'easyReservations' ));?>"><?php if($reservations_price_per_persons == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><?php } ?><?php if($reservations_price_per_persons == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/dollar.png"> <?php printf ( __( 'Currency' , 'easyReservations' ));?></td>
						<td><select name="reservations_currency" title="<?php printf ( __( 'Select currency' , 'easyReservations' ));?>"><?php if($reservations_currency=='euro'){ ?><option select="selected"  value="euro"><?php printf ( __( 'Euro' , 'easyReservations' ));?> &euro;</option><?php } ?><?php if($reservations_currency=='dollar'){ ?><option select="selected"  value="dollar"><?php printf ( __( 'Dollar' , 'easyReservations' ));?> &dollar;</option><?php } ?><?php if($reservations_currency == 'pound'){ ?><option select="selected"  value="pound"><?php printf ( __( 'Pound' , 'easyReservations' ));?> &pound;</option><?php } ?><?php if($reservations_currency == 'yen'){ ?><option select="selected"  value="yen"><?php printf ( __( 'Yen' , 'easyReservations' ));?> &yen;</option><?php } ?><option value="euro"><?php printf ( __( 'Euro' , 'easyReservations' ));?> &euro;</option><option value="dollar"><?php printf ( __( 'Dollar' , 'easyReservations' ));?> &dollar;</option><option  value="pound"><?php printf ( __( 'Pound' , 'easyReservations' ));?> &pound;</option><option  value="yen"><?php printf ( __( 'Yen' , 'easyReservations' ));?> &yen;</option></td>
					</tr>
				</tbody>
			</table><br><a href="javascript:{}" onclick="document.getElementById('reservation_settingss').submit(); return false;" class="button-primary" style="margin-left:auto;margin-rigth:auto;"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a>
			</form><br>
			</td><td style="width:1%;" valign="top">
			</td><td style="width:29%;" valign="top">
				<form method="post" action="admin.php?page=settings" id="reservation_clean_database">
				<input type="hidden" name="action" value="reservation_clean_database" id="reservation_clean_database">
					<table class="widefat" style="width:100%;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th style="width:80%;"> <?php printf ( __( 'Clean Database' , 'easyReservations' ));?></th>
								<th style="width:20%;"></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td><img style="vertical-align:text-bottom;" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/database.png"> <?php printf ( __( 'Delete all unapproved Old Reservations' , 'easyReservations' ));?>  (<?php echo $countcleans;?>)</td>
									<td><input title="<?php printf ( __( 'Delete all unapproved, rejected or trashed Old Reservations' , 'easyReservations' ));?>" type="submit" value="<?php printf ( __( 'Clean DB' , 'easyReservations' ));?>" class="button-secondary"></td>
								</tr>
						</tbody>
					</table>
				</form><br>
				<form method="post" action="admin.php?page=settings" id="reservation_change_permissions">
				<input type="hidden" name="action" value="reservation_change_permissions" id="reservation_change_permissions">
					<table class="widefat" style="width:100%;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Change Permissions' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">

									<td><select title="<?php printf ( __( 'Select needed permission for Reservations Admin Panel' , 'easyReservations' ));?>" name="permissionselect">
										<option value="<?php echo $reservations_main_permission; ?>"><?php if($reservations_main_permission=='edit_posts') echo 'Author'; elseif($reservations_main_permission=='manage_categories') echo 'Editor'; elseif($reservations_main_permission=='manage_options') echo 'Administrator'; elseif($reservations_main_permission=='manage_network') echo 'Super Admin'; ?></option>
										<?php
										$permissionarrays=array('edit_posts','manage_categories', 'manage_options', 'manage_network');
										foreach($permissionarrays as $permissionarray){
											if($permissionarray!=$reservations_main_permission){
												if($permissionarray=='edit_posts') $permissionname='Author'; elseif($permissionarray=='manage_categories') $permissionname='Editor'; elseif($permissionarray=='manage_options') $permissionname='Administrator'; elseif($permissionarray=='manage_network') $permissionname='Super Admin';
												echo '<option value="'.$permissionarray.'">'.$permissionname.'</option>';
											}
										}										
										?>
									</select> <a href="javascript:{}" onclick="document.getElementById('reservation_change_permissions').submit(); return false;" class="button-secondary" style="margin-left:auto;margin-rigth:auto;"><span><?php printf ( __( 'Set' , 'easyReservations' ));?></span></a>
									</td>
								</tr>
						</tbody>
					</table>
				</form><br>
					<table class="widefat">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Informations' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td colspan="2" style="vertical-align:middle;" coldspan="2"><b><?php printf ( __( 'Room IDs' , 'easyReservations' ));?>:</b><br><?php $termin=reservations_get_room_ids();
									$nums=0;
									foreach ($termin as $nmbr => $inhalt){
										echo $termin[$nums][1].': <b>'.$termin[$nums][0].'</b><br>';
										$nums++;
									}  ?><br>
									<b><?php printf ( __( 'Offer IDs' , 'easyReservations' ));?>:</b><br><?php $termin=reservations_get_offer_ids();
									$nums=0;
									foreach ($termin as $nmbr => $inhalt){
										echo $termin[$nums][1].': <b>'.$termin[$nums][0].'</b><br>';
										$nums++;
									}  ?><br><b><?php printf ( __( 'Support Mail' , 'easyReservations' ));?>:</b><br> feryazbeer@googemail.com</td>
								</tr>
						</tbody>
					</table>
		</td></tr></table><br>
<?php } elseif($settingpage=="form"){ 

	$formstandart.="
	[error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[phone]</p>

	<p>Address:<br>[address]</p>

	<p>Room: [room]</p>

	<p>Offer: [offer select]</p>	

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";

	$formroomstandart.="
	[hidden room XY][error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[phone]</p>

	<p>Address:<br>[address]</p>

	<p>Offer: [offer select]</p>

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";

	$formofferstandart.="
	[hidden offer XY][error]

	<p>From:<br>[date-from]</p>

	<p>To:<br>[date-to]</p>

	<p>Persons:<br>[persons select 10]</p>

	<p>Name:<br>[thename]</p>

	<p>eMail:<br>[email]</p>

	<p>Phone:<br>[phone]</p>

	<p>Address:<br>[address]</p>

	<p>Room: [room]</p>

	<p>Message:<br>[message]</p>

	<p>[submit Send]</p>";?>
		<table class="widefat" style="width:99%;">
			<thead>
				<tr>
					<th style="width:45%;"> <?php printf ( __( 'Reservation Form' , 'easyReservations' ));?></th>
					<th style="width:55%;"></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td style="width:60%;"><a href="admin.php?page=settings&site=form"><?php printf ( __( 'Standart' , 'easyReservations' ));?></a><?php echo $forms; ?><div style="float:right"><form method="post" action="admin.php?page=settings&site=form"  id="reservations_form_add"><input type="hidden" name="action" value="reservations_form_add"/><input name="formname" type="text"><a href="javascript:{}" onclick="document.getElementById('reservations_form_add').submit(); return false;" class="button-primary" ><span><?php printf ( __( 'Add' , 'easyReservations' ));?></span></a></form></div> </td>
					<td style="width:40%;" style="text-align:center"><?php printf ( __( 'Include to Page or Post with' , 'easyReservations' ));?> <code class="codecolor">[<?php echo $howload; ?>]</code></td>
				</tr>	
			<form method="post" action="admin.php?page=settings&site=form<?php if($formnameget!=""){ echo '&form='.$formnameget; } ?>"  id="reservations_form_settings" name="reservations_form_settings">
			<input type="hidden" name="action" value="reservations_form_settings"/>
			<input type="hidden" name="formnamesgets" value="<?php echo $formnameget; ?>"/>
			<input type="hidden" value="<?php echo $formstandart; ?>" name="inputstandart">
			<input type="hidden" value="<?php echo $formroomstandart; ?>" name="inputstandartroom">
			<input type="hidden" value="<?php echo $formofferstandart; ?>" name="inputstandartoffer">
			<tr valign="top">
				<td style="width:60%;"><textarea style="width:100%; height: 600px;" title="<?php printf ( __( 'The ID of the Special Offer Category' , 'easyReservations' ));?>" name="reservations_formvalue" id="reservations_formvalue"><?php echo $reservations_form; ?></textarea><br><br> <a href="javascript:{}" onclick="document.getElementById('reservations_form_settings').submit(); return false;" class="button-primary" ><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><div style="float:right;"><input type="button" value="Default Form" onClick="addtext();" class="button-secondary" ><input type="button" value="post in one Room" onClick="addtextforRoom();" class="button-secondary" ><input type="button" value="post in one Offer" onClick="addtextforOffer();" class="button-secondary" ></div></td>
				<td style="width:40%;">
					<div class="explainbox">
						<p><code class="codecolor">[error]</code> <i><?php printf ( __( 'wrong inputs & unavailable dates' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[date-from]</code> <i><?php printf ( __( 'day of arrival with datepicker' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[date-to]</code> <i><?php printf ( __( 'day of departure with datepicker' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[nights x]</code> <i><?php printf ( __( 'nights to stay as select' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = $max</code> <i><?php printf ( __( 'max days to stay' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[persons x]</code> <i><?php printf ( __( 'number of guests' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = text</code> <i><?php printf ( __( 'as textfield ' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = select $max</code> <i><?php printf ( __( 'as select' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[name]</code> <i><?php printf ( __( 'name of guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[firstname]</code> <i><?php printf ( __( 'firstname of guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[secondname]</code> <i><?php printf ( __( 'name of guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[phone]</code> <i><?php printf ( __( 'phone of guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email of guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[room]</code> <i><?php printf ( __( 'select of rooms' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[hidden type x]</code> <i><?php printf ( __( 'for forms for just one room/offer' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">type = room/offer</code> <i><?php printf ( __( 'type of form' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = ID</code> <i><?php printf ( __( 'ID of the Room/Offer Post' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[offer x]</code> <i><?php printf ( __( 'special offers' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = select</code> <i><?php printf ( __( 'as select' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = box</code> <i><?php printf ( __( 'as box if guest come from offer-post' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[address]</code> <i><?php printf ( __( 'address of guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[message]</code> <i><?php printf ( __( 'message from guest' , 'easyReservations' ));?></i></p>
						<p><code class="codecolor">[submit x]</code> <i><?php printf ( __( 'submit button' , 'easyReservations' ));?></i></p>
						<p style="margin-left:20px;"><code class="codecolor">x = Value</code> <i><?php printf ( __( 'value of submit button' , 'easyReservations' ));?></i></p>
					</div>
				</td>
			</tr>
			</tbody>
		</table>
		</form>
<?php } elseif($settingpage=="email"){ 	
	$emailstandart1="New Reservation on Blogname from<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
	$emailstandart2="Your Reservation on Blogname has been approved.<br> 
[adminmessage]<br><br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
	$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
Name: [thename] <br>eMail: [email] <br>From: [arrivaldate] <br>To: [to] <br>Persons: [persons] <br>Persons: [persons] <br>Phone: [phone] <br>Address: [address] <br>Room: [room] <br>Offer: [offer] <br>Message: [message]";
	?>
		<form method="post" action="admin.php?page=settings&site=email"  id="reservations_email_settings" name="reservations_email_settings">
		<input type="hidden" name="action" value="reservations_email_settings"/>
		<input type="hidden" value="<?php echo $emailstandart1; ?>" name="inputemail1">
		<input type="hidden" value="<?php echo $emailstandart2; ?>" name="inputemail2">
		<input type="hidden" value="<?php echo $emailstandart3; ?>" name="inputemail3">
		<table style="width:99%;"><tr><td  style="width:60%;" valign="top">
		<table class="widefat">
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Mail to admin for new Reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td style="width:100%;"><input type="text" name="reservations_email_to_admin_subj" style="width:30%;" value="<?php echo $reservations_email_to_admin_subj; ?>"> Subject</td>
				</tr>	
				<tr valign="top">
					<td style="width:100%;"><textarea name="reservations_email_to_admin_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_admin_msg; ?></textarea><br><br><a href="javascript:{}" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="button-primary" ><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><div style="float:right;"><input type="button" value="Default Mail" onClick="addtextforemail1();" class="button-secondary" ></div><br><br></td>
				</tr>	
			</tbody>
		</table>
	<br>
		<table class="widefat">
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Mail to User when approve Reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td style="width:100%;"><input type="text" name="reservations_email_to_userapp_subj" style="width:30%;" value="<?php echo $reservations_email_to_userapp_subj; ?>"> Subject    <?php bloginfo('rss_url'); ?> </td>
				</tr>	
				<tr valign="top">
					<td style="width:100%;"><textarea name="reservations_email_to_userapp_msg"  id="reservations_email_to_userapp_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_userapp_msg; ?></textarea><br><br><a href="javascript:{}" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="button-primary" ><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><div style="float:right;"><input type="button" value="Default Mail" onClick="addtextforemail2();" class="button-secondary" ></div><br><br></td>
				</tr>	
			</tbody>
		</table>
	<br>
		<table class="widefat">
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Mail to User when reject Reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td style="width:100%;"><input type="text" name="reservations_email_to_userdel_subj" style="width:30%;" value="<?php echo $reservations_email_to_userdel_subj; ?>"> Subject</td>
				</tr>	
				<tr valign="top">
					<td style="width:100%;"><textarea name="reservations_email_to_userdel_msg" style="width:99%;height:120px;"><?php echo $reservations_email_to_userdel_msg; ?></textarea><br><br><a href="javascript:{}" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="button-primary" ><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><div style="float:right;"><input type="button" value="Default Mail" onClick="addtextforemail3();" class="button-secondary" ></div><br><br></td>
				</tr>	
			</tbody>
		</table>
		</td>
		<td  style="width:1%;"></td>
		<td  style="width:39%;"  valign="top">
			<table class="widefat">
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Possibilitys' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;">
						<div class="explainbox">
							<p><code class="codecolor">&lt;br&gt;</code> <i><?php printf ( __( 'wordwrap' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[thename]</code> <i><?php printf ( __( 'name' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[arrivaldate]</code> <i><?php printf ( __( 'arrival date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[to]</code> <i><?php printf ( __( 'departure date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[nights]</code> <i><?php printf ( __( 'nights to stay' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[phone]</code> <i><?php printf ( __( 'phone' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[address]</code> <i><?php printf ( __( 'address of guest' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[persons]</code> <i><?php printf ( __( 'number of guests' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[room]</code> <i><?php printf ( __( 'choosen room' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[offer]</code> <i><?php printf ( __( 'choosen offer' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[message]</code> <i><?php printf ( __( 'message from guest' , 'easyReservations' ));?></i></p>								
						</div>
					</td>
				</tr>	

			</tbody>
		</table>
		</td></tr></table>
		</form>
<?php } 
	if($settingpage=="about"){ ?>
	<table style="width:99%;" cellspacing="0"><tr><td style="width:60%;" style="width:49%;"  valign="top">
		<table class="widefat" >
			<thead>
				<tr>
					<th> <?php printf ( __( 'Changelog' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
						<div class="changebox"  align="left">
						<p><b style="font-size:13px">easyReservations Version 1.1.1</b><br>
								<b>* ADDED</b> <i>Hidden Field from Form works for Offers and Rooms now</i><br>
								<b>* ADDED</b> <i>Select needed Permissions for Reservations Admin</i><br>
								<b>* FIXED</b> <i>mouseOver in Overview</i><br>
								<b>* FIXED</b> <i>Datepicker in Edit</i><br>
								<b>* FIXED</b> <i>Upgrade Script. Everythink should be fine now.</i><br>
								<b>* FIXED</b> <i>General Settings</i><br>
								<div class="fakehr"></div><br>
						<p><b style="font-size:13px">easyReservations Version 1.1</b><br>
								<b>* NEW FUNCTION</b> <i>Filters! Each Room or Offer can now have unlimeted Filters for more flexiblity. Price, Availibility, and Discount for longer Stays or recoming Guests.</i><br>
								<b>* NEW FUNCTION</b> <i>The Form is very customizable now! Can have unlimited Forms, Forms for just one Room and edit the Style of them very easy.</i><br>
								<b>* NEW FUNCTION</b> <i>eMails are customizable now!</i><br>
								<b>* NEW FUNCTION</b> <i>Statistis! Starts with four Charts, more to come.</i><br>
								<b>* ADDED</b> <i>Overview is Clickable when approve or edit! 1x Click on roomname for change the room; Doubleclick for reset; [edit] click on date to change them fast (no visual response)</i><br>
								<b>* ADDED</b> <i>Settings Tabs</i><br>
								<b>* ADDED</b> <i>Checking availibility from Room/Offer Avail Filters and if Room is empty</i><br>
								<b>* REVAMP</b> <i>Rewrote the Edit Part and added it to Main Site</i><br>
								<b>* REVAMP</b> <i>Settings</i><br>
								<b>* FIXED</b> <i>Order by Date in Reservation Table</i><br>
								<b>* FIXED</b> <i>Search Reservations</i><br>
								<b>* FIXED</b> <i>many other minor bugs</i><br>
								<b>* DELETED</b> <i>Seasons; unnecessary because of  new Filter System</i><br>
								<b>* DELETED</b> <i>Form Options; unnecessary because of new Form System</i></p>
								<div class="fakehr"></div><br>
						<p><b style="font-size:13px">easyReservations Version 1.0.1</b><br>
								<b>* ADDED</b> <i>function easyreservations_price_calculation($id) to calculate Price from Reservation ID.</i><br>
								<b>* REVAMP</b> <i>the Overview now uses 95% less mySQL Queries! Nice speed boost for Administration.</i><br>
								<b>* FIXED</b> <i>Box Style of Offers in Reservation Form will now work on every Permalink where the id or the slug is at the end. Thats on almost every Site.</i><br>
								<b>* FIXED</b> <i>Box Style of Offers in Reservation Form should display right on the most Themes now. If not, please sent Screenshot and Themename.</i><br>
								<b>* FIXED</b> <i>Room/Offer in Approve/Reject Reservation Mail to User is now translatable</i><br>
								<b>* FIXED</b> <i>German Language is working now</i></p>
						</div>
					</td>
				</tr>	
			</tbody>
		</table>
		</td><td style="width:1%;"></td><td style="width:39%;" style="width:49%;"  valign="top">
				<table class="widefat" >
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Links' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
						<div class="changebox"><p><a href="http://www.feryaz.de/dokumentation/"><?php printf ( __( 'Documentation' , 'easyReservations' ));?></a></p> <div class="fakehr"></div><p><a href="http://www.feryaz.de/suggestions/"><?php printf ( __( 'Suggest Ideas & Report Bugs' , 'easyReservations' ));?></a></p><div class="fakehr"> </div><p><a href="http://wordpress.org/extend/plugins/easyreservations/"><?php printf ( __( 'Wordpress Repostry' , 'easyReservations' ));?></a></p><div>
					</td>
				</tr>	
			</tbody>
		</table><br>
		<table class="widefat">
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Latest News' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;">
						<?php // import rss feed
						if(function_exists('fetch_feed')) {
						// fetch feed items
						$rss = fetch_feed('http://feryaz.de/feed/rss/');
						if(!is_wp_error($rss)) : // error check
							$maxitems = $rss->get_item_quantity(5); // number of items
							$rss_items = $rss->get_items(0, $maxitems);
						endif;
						// display feed items ?>
						<dl>
						<?php if($maxitems == 0) echo '<dt>Feed not available.</dt>'; // if empty
						else foreach ($rss_items as $item) : ?>

							<dt>
								<a href="<?php echo $item->get_permalink(); ?>" 
								title="<?php echo $item->get_date('j F Y @ g:i a'); ?>">
								<?php echo $item->get_title(); ?>
								</a>
							</dt>
							<dd>
								<?php echo $item->get_description(); ?>
							</dd>

						<?php endforeach; ?>
						</dl>
						<?php } ?>
					</td>
				</tr>	
			</tbody>
		</table><br><table class="widefat" >
			<thead>
				<tr>
					<th style="width:100%;"> <?php printf ( __( 'Donate' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="center">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="EZGXTQHU6JSUL">
<input type="image" src="https://www.paypalobjects.com/en_US/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>


					</td>
				</tr>	
			</tbody>
		</table><br>
</tr></table>

<?php } ?>
</div>
<script>
// perform JavaScript after the document is scriptable.


     $(document).ready(function () {
        $('color_5').bind('colorpicked', function () {
          alert($(this).val());
        });
      });
$(function() {
// select all desired input fields and attach tooltips to them
$("#reservation_settingss :input, #reservation_clean_database :input").tooltip({

	tipClass: 'tooltip_klein',
	// place tooltip on the right edge
	position: "center right",

	// a little tweaking of the position
	offset: [-2, 10],

	// use the built-in fadeIn/fadeOut effect
	effect: "fade",

	// custom opacity setting
	opacity: 0.7

});
});</script>
<?php }  ?>