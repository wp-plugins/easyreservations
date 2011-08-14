<?php
function reservation_main_page() {

	global $wpdb;

	$siteurl = get_option('siteurl');

	//  Include the Pagination Function named Digg Style Pagination Class 
	include('pagination.class.php');

	//  Get Options from wp_options; Hope they'r not to much
	$reservation_form_address=get_option("reservation_form_address");
	$reservation_support_mail = get_option("reservations_reservation_mail");
	$reservations_on_page = get_option("reservations_on_page");
	$offer_cat = get_option("reservations_special_offer_cat");
	$room_category = get_option("reservations_room_category");
	$season1 = explode("-", get_option("reservation_season1"));
	$season2 = explode("-", get_option("reservation_season2"));
	$season3 = explode("-", get_option("reservation_season3"));
	$season4 = explode("-", get_option("reservation_season4"));
	$season5 = explode("-", get_option("reservation_season5"));

	if(isset($_POST['delete'])) {
		$post_delete=$_POST['delete'];
	}	
	if(isset($_POST['roomexactly'])) {
		$roomexactly=$_POST['roomexactly'];
	}
	if(isset($_POST['reservation_action'])) {
		$reservation_approve=$_POST['reservation_action'];
	}
	if(isset($_POST['approve_message'])) {
		$approve_message=$_POST['approve_message'];
	}
	if(isset($_POST['approve'])) {
		$post_approve=$_POST['approve'];
	}

	if(isset($_GET['more'])) {
		$more=$_GET['more'];
	}
	if(isset($_GET['perpage'])) {
		$perpage=$_GET['perpage'];
		update_option("reservations_on_page",$perpage);
	}
	if(isset($_GET['orderby'])) {
		$orderby=$_GET['orderby'];
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
			$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>'.$anzahl.' deleted permanently.</p></div>';
			}
		}
	}

	if(isset($approve)  || isset($delete) || isset($view)) { //Query of View Reject and Approve
		$sql_A = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id='$approve' OR id='$delete' OR id='$view' ";

		$results = $wpdb->get_results($sql_A );

		foreach( $results as $result ) {

			$id=$result->id;
			$name=$result->name;
			$reservationFrom=$result->nights;
			$reservationDate=$result->arrivalDate;
			$phone=$result->phone;
			$room=$result->room;
			$special=$result->special;
			$persons=$result->number;
			$mail_to=$result->email;
			$message_r=explode("*/*", $result->notes);
			$roomwhere="AND room='$room'";
			$roomsgetpost=get_post($room);
			$rooms=$roomsgetpost->post_title;
			
			if(!$message_r) $addresse = 'None'; else $addresse = $message_r[1];
			
			$specialgetpost=get_post($special);
			$specials=$specialgetpost->post_title;	

			if($special=="0") $specials="None";

			$timpstampanf=strtotime($reservationDate);
			$anznights=60*60*24*$reservationFrom;
			$timestampend=$anznights+$timpstampanf;
			
			$timestamp_timebetween=$timpstampanf-time()-432000;
			$more=round($timestamp_timebetween/24/60/60);
		}
	}

	if(isset($post_approve) && $post_approve=="yes")
	{
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes', roomnumber='$roomexactly' WHERE id=$approve"  ) ); 	
	}

	if(isset($post_delete) && $post_delete=="yes")
	{
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='no' WHERE id=$delete"  ) ); 	
	}

	if(isset($reservation_approve) && $reservation_approve=="reservation_approve")
	{
		$subject = "Your Reservation on ".get_bloginfo('name');
		$eol="\n";
		$mime_boundary=md5(time());
		$headers = "From: ".$mail_to." <".$mail_to.">".$eol;
		$headers .= "Message-ID: <".time()."-".$mail_to.">".$eol;
		$headers .= "Content-Type: text/html; charset=UTF-8; boundary=\"".$mime_boundary."\"".$eol.$eol;

		$message = "";
		$message.=$approve_message."<br/><br/>";
		$message.="<strong>Reservation Details</strong><br/><br/>";
		$message.="Name: ".$name."<br/>";
		$message.="Phone: ".$phone."<br/>";
		if($reservation_form_address == '1' ) $message.="Address: ".$addresse."<br/>";
		$message.="E-mail: ".$mail_to."<br/>";
		$message.="From: ".$reservationDate." - To : ".date("d.m.Y", $timestampend)."<br/>";
		$message.="Room: "._($rooms)."<br/>";
		$message.="Special Offer: ".__($specials)."<br/>";

		$mail_sent = mail($mail_to,$subject,$message,$headers);	
		?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
	}
	//  Get Options from wp_options; Hope they'r not to much
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
	$show_overview_on_list = get_option("reservation_overview_showgeneraly");

	$season1 = explode(" - ", get_option("reservation_season1"));
	$season2 = explode(" - ", get_option("reservation_season2"));
	$season3 = explode(" - ", get_option("reservation_season3"));
	$season4 = explode(" - ", get_option("reservation_season4"));
	$season5 = explode(" - ", get_option("reservation_season5"));
	$siteurl = get_option('siteurl');

	//Calculations for Overview Dates
	$daysshow=get_option("reservations_show_days");
	$timevariable=time();
	$enddate=$daysshow+$more;
	$startdate=0+$more;
	$eintagmalstart=60*60*24*$startdate;
	$eintagmalend=60*60*24*$enddate;
	$timesx=$timevariable+$eintagmalstart;
	$timesy=$timevariable+$eintagmalend;
	if(date("F", $timesx)==date("F", $timesy)) $dateshow=date("F", $timesx);
	else $dateshow=date("F", $timesx).'/'.date("F", $timesy);

	$csv_hdr = "Name, Arrival Date, Desination Date, Nights, eMail, Persons, Price, Room, Special Offer"; // Header for Output

	?><div id="icon-themes" class="icon32"><br></div><h2 style="font-family: HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,sans-serif; font-weight: normal; font-size: 23px;">Reservations <a class="add-new-hari" href="admin.php?page=add-reservation">Add New</a></h2><div id="wrap">
		<?php echo $prompt; 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START OVERVIEW
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if((isset($approve) || isset($delete) || isset($view)) OR $show_overview_on_list == '1'){ ?>
			<table cellspacing="0" cellpadding="0" class="widefat" style="background:#f9f9f9;width:99%;"><thead><tr><td style=" text-align:center;vertical-align:middle;"><b><?php echo $dateshow; ?></b><br><a href="<?php echo $pageURL;?>?page=reservations&more=<?php echo $more-$daysshow;?><?php if(isset($_GET['typ']))echo '&typ='.$typ; ?>"><</a> <a href="<?php echo $pageURL;?>?page=reservations&more=0<?php if(isset($_GET['typ']))echo '&typ='.$typ; ?>">0</a> <a href="<?php echo $pageURL;?>?page=reservations&more=<?php echo $more+$daysshow;?>">></a></td>
				<?php
					$s=$daysshow+$more;
					$co=0+$more;
					$overviewfont='font: 10px #333333 Verdana, Arial, Helvetica, sans-serif; font-family: Georgia,Times New Roman,Times,serif;';
					while($co < $s){
						$eintags=60*60*24*$co;
						$timesx =$timevariable+$eintags;
						$datesarray.=date("m", $timesx).",";
						if(date("d.m.Y", $timpstampanf) == date("d.m.Y", $timesx)) { $backgroundhighlight='#eeeeee'; } else { $backgroundhighlight='#f9f9f9'; }
						?>
						<td style="text-align: center;background:<?php echo $backgroundhighlight; ?>;<?php echo $overviewfont; ?> border-left:  1px solid #dfdfdf; " class="h1overview"><?php echo date("d",$timesx); ?><br><?php echo date("D",$timesx); ?></td> 
					<?php $co++; } ?>
				</tr></thead><tbody style=" background: #f9f9f9;">
				<?php		
					$sql_R = "SELECT DISTINCT room FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $roomwhere ORDER BY id ASC";
					$results = $wpdb->get_results( $sql_R );

					foreach( $results as $result ) {
						$roomsidentify=$result->room;
						$roomcounty=get_post_meta($roomsidentify, 'roomcount', true);
						$rowcount="0";

							while($roomcounty > $rowcount){
								$rowcount++;
								echo '<tr><td style="text-shadow:none; border-style:none; width: 160px;max-height: 30px; '.$overviewfont.';background:#f9f9f9; vertical-align:middle;border-bottom: '.$borderbottom.'px solid #dfdfdf;" nowrap>'.get_the_title($roomsidentify).' '.$rowcount.'</td>';
								$showdatenumber_start="0"+$more;
								$showdatenumber_end=$daysshow+$more;
								$cellcount="0";

								while($showdatenumber_start < $showdatenumber_end){
									$cellcount++;
									$showdatenumber_start++;
									$eintagerss=60*60*24*$showdatenumber_start;
									$datumtoday=$timevariable+$eintagerss;
									$datedatumtodaydate=date("Y-m-d", $datumtoday);
									$dateplus14=$datumtoday+(86400*14);
									
									$sql_R2 = "SELECT name, nights, arrivalDate, room FROM ".$wpdb->prefix ."reservations WHERE room='$roomsidentify' AND roomnumber='$rowcount' AND approve='yes' AND  roomnumber != '' AND '$datedatumtodaydate' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ORDER BY arrivalDate ASC";
									$results2 = $wpdb->get_results( $sql_R2 );
									$itwasfull='0';
									foreach( $results2 as $result){
										$name=$result->name;
										$expl= explode(" ", $name);
										$nights=$result->nights;
										$timpstamp_start=strtotime($result->arrivalDate)+86400; 
										$eintags12=60*60*24*$showdatenumber_start;
										$datetodays=$timevariable+$eintags12;
										$datetodays2=$timevariable+$eintags12;
										$anznights=60*60*24*$nights;
										$timestampend=$anznights+$timpstamp_start;
										
											if($datetodays >= $timpstamp_start AND $datetodays <= $timestampend) {
												$xor=$datetodays;
												$itwasfull='1';
												$farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat"; if($letztername!=$expl[0]) $farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_start.png) no-repeat ".$colorbackgroundfree.""; if($cellcount=="1") { $farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat"; } if($letztername != "") {$farbe="url(".WP_PLUGIN_URL ."/easyreservations/images/".$colorfull."_middle.png) repeat";  }
												?><td title="Name: <?php echo $expl[0].' '.$expl[1]; ?><br><?php printf ( __( 'Date' , 'easyReservations' ));?>: <?php echo date("d.m.Y",$datetodays2-86400)?><br><?php printf ( __( 'Room' , 'easyReservations' ));?>: <?php echo __(get_the_title($roomsidentify))?> # <?php echo $rowcount; ?><br><?php printf ( __( 'Status: Occupied' , 'easyReservations' ));?>"  style="background: <?php echo $farbe;?>; text-decoration:none; font: normal 12px Arial, sans-serif; color:<?php echo $fontcoloriffull; ?>; text-align:center; text-shadow:none; border-style:none; border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $colorborder; ?>; border-left:  <?php echo $borderside; ?>px solid <?php echo $colorborder; ?>; vertical-align:middle;"><?php echo date("d",$datetodays-86400); ?></td>
												<?php $letztername=$expl[0];
											}
									}   if($itwasfull=='0') { if($datumtoday != $xor){ if($letztername AND $cellcount != '1'){ $farbe2='url('.WP_PLUGIN_URL .'/easyreservations/images/'.$colorfull.'_end.png) no-repeat '.$colorbackgroundfree.''; } else { $farbe2=$colorbackgroundfree; } echo '<td title="'.__( 'Date' , 'easyReservations' ).': '.date("d.m.Y",$datumtoday-86400).'<br>'.__( 'Room' , 'easyReservations' ).':: '.__(get_the_title($roomsidentify)).' # '.$rowcount.'<br>'.__( 'Status: Empty' , 'easyReservations' ).'" style="height:30px; color:'.$fontcolorifempty.'; text-align:center;text-shadow:none; border-style:none; border-bottom: '.$borderbottom.'px solid '.$colorborder.'; border-left: '.$borderside.'px solid '.$colorborder.'; vertical-align: middle; background:'.$farbe2.'">'.date("d",$datumtoday-86400).'</td>'; $letztername=''; $letzternameleer=$datetodays; $xor=''; } } }  $warvoll=''; echo '</tr>'; } } ?>
				</tbody></table><br>
<script>
$("[title]").tooltip({ deley: 0, predelay: 30 , tipClass: 'tooltip_klein',position: "center right", offset: [-50, 50] });
</script><?php  }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							if(!isset($approve) && !isset($delete) && !isset($view)) { ?>
									<input type="hidden" name="action" value="reservation"/>
												<?php
														if(isset($_GET['specialselector'])) $specialselector=$_GET['specialselector'];
														if(isset($_GET['monthselector'])) $monthselector=$_GET['monthselector'];
														if(isset($_GET['roomselector'])) $roomselector=$_GET['roomselector'];

														$test=date("Y-n-j",time());
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
														if($order=="DESC") { $orderlink="&order=DESC"; $orders="DESC";}

														if($orderby=="date") { $orderbylink="&orderby=date"; $ordersby="reservationDate";}
														if($orderby=="name") { $orderbylink="&orderby=name"; $ordersby="name";}
														if($orderby=="room") { $orderbylink="&orderby=room"; $ordersby="room";}
														if($orderby=="special") { $orderbylink="&orderby=special"; $ordersby="special";}
														if($orderby=="nights") { $orderbylink="&orderby=nights"; $ordersby="nights";}

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
															<?php if($items5 > 0) { ?>| <li class="old"><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="deleted"){ ?>
															<li class="all"><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted" class="current"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li class="old"><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
															<?php } if($typ=="old"){ ?>
															<li class="all"><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
															<li class="publish"><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
															<li class="trash"><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
															<li class="old"><a href="admin.php?page=reservations&typ=old" class="current"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
															<?php if($items5 > 0) { ?>| <li class="old"><a href="admin.php?page=reservations&typ=trash">Trash<span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
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
												</tr></table>
												
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
																<?php } else { ?><a class="stand2"  style="background-position:<?php echo $marginspecial;?> 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Special Offer' , 'easyReservations' ));?></a></th>
															<th><?php if($reservation_form_address == "0"){ printf ( __( 'Message' , 'easyReservations' )); } else {  printf ( __( 'Address' , 'easyReservations' )); }?></th>
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
																<?php } else { ?><a class="stand2"  style="background-position:88px 8px;" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Special Offer' , 'easyReservations' ));?></a></th>
															<th><?php if($reservation_form_address == "0"){ printf ( __( 'Message' , 'easyReservations' )); } else {  printf ( __( 'Address' , 'easyReservations' )); }?></th>
														</tr></tfoot><tbody>
													<?php
														$nr="0";
														if($search) {
															$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE name like '%$search%' OR email like '%$search%' OR notes like '%$search%' OR reservationDate like '%$search%' ORDER BY $ordersby $orders $limit"; // Search query
														} else {
														   $sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE approve='$type' $monthsql $roomsql $specialsql $zeichen ORDER BY $ordersby $orders $limit";  // Main Table query
														}

														$result = mysql_query($sql) or die ('Error, query failed');

															if (mysql_num_rows($result) > 0 ) {
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
																	$timestampend=$anznights+$timpstampanf;

																	$nr++; 
																	
																	?>
														<tr class="<?php echo $class; ?> test" height="47px"><!-- Main Table Body //-->
															<td width="2%" style="text-align:center;vertical-align:middle;"><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $id;?>"></td>
															<td width="17%" class="row-title" valign="top" nowrap><div class="test"><a href="admin.php?page=reservations&view=<?php echo $id;?>"><?php echo $name;?></a><div class="test2" style="margin:5px 0 0px 0;"><a href="<?php echo $pageURL;?>?page=add-reservation&edit=<?php echo $id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a> <?php if($typ=="deleted" or $typ=="pending") { ?>| <a style="color:#28a70e;" href="<?php echo $pageURL;?>?page=reservations&approve=<?php echo $id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a><?php } ?> <?php if($typ=="" or $typ=="active" or $typ=="pending") { ?>| <a style="color:#bc0b0b;" href="<?php echo $pageURL;?>?page=reservations&delete=<?php echo $id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a><?php } ?>  <?php if($typ=="trash") { ?>| <a href="<?php echo $pageURL;?>?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> | <a style="color:#bc0b0b;" href="<?php echo $pageURL;?>?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a><?php } ?> | <a href="<?php echo $pageURL;?>?page=reservations&view=<?php echo $id;?>"><?php printf ( __( 'View' , 'easyReservations' ));?></a></div></div><?php $csv_output .= $name . ", "; ?></td>
															<td width="20%" nowrap><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $nights; ?> <?php printf ( __( 'Nights' , 'easyReservations' ));?>)</small><?php $csv_output .= date("d.m.Y",$timpstampanf) . ", "; ?><?php $csv_output .= date("d.m.Y",$timestampend) . ", "; ?><?php $csv_output .= $nights . ", "; ?></td>
															<td width="12%"><?php echo $row['email'];?><?php $csv_output .= $row['email'] . ", "; ?></td>
															<td width="5%" style="text-align:center;"><?php echo $row['number'];?><?php $csv_output .= $row['number'] . ", "; ?></td>
															<td width="7%" nowrap><?php echo easyreservations_price_calculation($id)."&".get_option('reservations_currency').";" ; ?><?php $csv_output .= easyreservations_price_calculation($id). ", "; ?></td>
															<td width="12%" nowrap><?php echo __($rooms) ;?> <?php echo $row['roomexactly'] ;?><?php $csv_output .= __($rooms) . ", "; ?></td>
															<td width="12%" nowrap><?php if($special==0) echo 'None'; else echo __($specials);?><?php $csv_output .= __($specials) . "\n"; ?></td>
															<td width="13%"><?php if($reservation_form_address == "0"){ echo substr($message[0], 0, 36); } else { if($message[1]){ echo substr($message[1], 0, 36); } else { echo __( 'None' , 'easyReservations' ); } }?></td>
														</tr>
													<?php }
													} else { ?> <!-- if no results form main quary !-->
															<tr>
																<td></td><td><?php printf ( __( 'No Record Found!' , 'easyReservations' ));?></td><td></td><td></td><td></td><td></td><td></td><td></td> <!-- Mail Table Body if empty //-->
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
													<input class="button-secondary" type="submit" value="Export table as CSV">
													<input type="hidden" value="<?php echo $csv_hdr; ?>" name="csv_hdr">
													<input type="hidden" value="<?php echo $csv_output; ?>" name="csv_output">
												</form>
										<?php }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
											if((isset($approve) || isset($delete) || isset($view)) && !isset($reservation_approve)) { ?> <!-- // Content will only show on delete, view or approve Reservation -->

											<h3><?php if($approve) { echo "Approve"; } if($delete) { echo "Reject";} if($view) { echo "View";}?> Reservation</h3>
											<table  style="width:99%;"><tr valign="top"><td  style="width:64%;">
												<table class="widefat">
													<thead>
														<tr>
															<th style="width:50%;"><b><?php printf ( __( 'Title' , 'easyReservations' ));?></b></th>
															<th style="width:50%;"><b><?php printf ( __( 'Information' , 'easyReservations' ));?></b></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
															<td><?php echo $name;?></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/date.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
															<td><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $reservationFrom;?>)</small></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
															<td><?php echo $mail_to;?></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/phone.png"> <?php printf ( __( 'Phone' , 'easyReservations' ));?>:</td> 
															<td><?php echo $phone;?></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
															<td><?php echo $persons;?></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
															<td><?php echo __($rooms);?></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png"> <?php printf ( __( 'Special Offer' , 'easyReservations' ));?>:</b></td> 
															<td><?php if($specials){ echo __($specials);} else { printf ( __( 'None' , 'easyReservations' )); }  ?></td>
														</tr>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/money.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?>:</b></td> 
															<td><?php echo easyreservations_price_calculation($id).'&'.get_option('reservations_currency').';';?></td>
														</tr>
														<?php if($reservation_form_address == "1"){ ?>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/house.png"> <?php printf ( __( 'Address' , 'easyReservations' ));?>:</b></td> 
															<td><?php if($message_r[1]){ echo __($message_r[1]); } else { printf ( __( 'Not asked for' , 'easyReservations' )); } ?></td>
														</tr>
														<?php } ?>
														<tr>
															<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
															<td><?php echo $message_r[0];?></td>
														</tr>
													</tbody>
												</table>

									<?php } 

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

										if((isset($approve) || isset($delete)) && !isset($reservation_approve)) { ?>  <!-- Content will only show on delete or approve Reservation //--> 
											</td><td  style="width:1%;"></td><td  style="width:35%;" valign="top">
											<form method="post" action="<?php echo $pageURL;?>?page=reservations<?php if($approve) echo "&approve=".$approve ;?><?php if($delete) echo "&delete=".$delete ;?>"  id="reservation_approve">
												<input type="hidden" name="reservation_action" value="reservation_approve"/>
												<input type="hidden" name="action" value="reservation_approve"/>
												<?php if($approve) { ?><input type="hidden" name="approve" value="yes" /><?php } ?>
												<?php if($delete) { ?><input type="hidden" name="delete" value="yes" /><?php } ?><br>
												<table class="widefat" valign="top">
													<thead>
														<tr>
															<th><?php if($approve) {  printf ( __( 'Approve the Reservation' , 'easyReservations' ));  }  if($delete) {  printf ( __( 'Reject the Reservation' , 'easyReservations' ));  } ?><b/></th>
														</tr>
													</thead>
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
													<tbody>
														<tr>
															<td>
																	<p><?php printf ( __( 'To' , 'easyReservations' ));?> <?php if($approve) { printf ( __( 'Approve' , 'easyReservations' )); } if($delete) { printf ( __( 'Reject' , 'easyReservations' ));}?> <?php printf ( __( 'the Reservation, write a text and press "Send' , 'easyReservations' ));?> & <?php if($approve) { echo "Approve"; } if($delete) { echo "Reject";}?>". <?php printf ( __( 'The Customer will recieve that message in an eMail' , 'easyReservations' ));?>.</p>
																	<p class="label"><strong>Text:</strong></p>
																	<textarea cols="60" rows="4" name="approve_message" class="text-area-1" width="100px"></textarea>
															</td>
														</tr>
														<tr class="item last">
															<?php if($approve) { ?><td><p><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;"  class="button-secondary"><span>Send & Approve</span></a></p></td><?php } ?>
															<?php if($delete) { ?><td><p><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;" class="button-secondary"><span>Send & Reject</span></a></p></td><?php } ?>
														</tr>
													</tbody>
												</table>
											</form><td></tr></table>
									<?php } ?>
									<?php if(isset($reservation_approve) && $reservation_approve=="reservation_approve") { ?> <!-- Content will only show after approe or delete Reervation //--> 
											<table>
												<tr>
													<td>
														<div style="height:300px;">
															<p style="align:center; font-weight:bold;" ><?php printf ( __( 'The message has been sent!' , 'easyReservations' ));?></p>
														</div>
													</td>
												</tr>
											</table>
									<?php } }

function reservation_add_reservaton() {
				global $wpdb;

				if(isset($_GET['edit'])) $edit=$_GET['edit'];

				if($edit) {
				$posts = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id='".$edit."'";
				$results = $wpdb->get_results($posts);
				$idx=$results[0]->id;
				$namex=$results[0]->name;
				$phonex=$results[0]->phone;	
				$roomy=$results[0]->room;
				$roomynumb=$results[0]->roomnumber;
				$personsx=$results[0]->number;
				$emailx=$results[0]->email;
				$datexy=$results[0]->arrivalDate;
				$datex=date("d.m.Y", strtotime($datexy));
				$nightsx=$results[0]->nights;
				$notesx=explode("*/*", $results[0]->notes);
				$specialy=$results[0]->special;

				$post_id = get_post($specialy); 
				$specialx = $post_id->post_title;

				$post_id2 = get_post($roomy); 
				$roomx = $post_id2->post_title;	
				}

				if(isset($_POST["action"])){ 
					$action = $_POST['action'];
				}	
				if(isset($_POST["name"])){
					$name=$_POST["name"];
				}
				if(isset($_POST["date"])){
					$date=$_POST["date"];
				}
				if(isset($_POST["email"])){
					$email=$_POST["email"];
				}
				if(isset($_POST["phone"])){
					$phone=$_POST["phone"];
				}
				if(isset($_POST["roomexactly"])){
					$roomex=$_POST["roomexactly"];
				}
				if(isset($_POST["room"])){
					$room=$_POST["room"];
				}
				if(isset($_POST["note"])){
					$note=$_POST["note"];
				}
				if(isset($_POST["nights"])){
					$nights=$_POST["nights"];
				}
				if(isset($_POST["persons"])){
					$persons=$_POST["persons"];
				}
				if(isset($_POST["specialoffer"])){
					$specialoffer=$_POST["specialoffer"];
				}
				if(isset($_POST["address"])){
					$address=$_POST["address"];
				}

				$epls=strtotime($date);				
				$dat=date("Y-m", $epls);
				$rightdate=date("Y-n-j", $epls);
				$rightdate2=date("Y-m-d", $epls);

				if($action == "addreservation") {

					$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(arrivalDate, name, phone, email, notes, nights, dat, room, number, special, approve ) 
					VALUES ('$rightdate2', '$name', '$phone', '$email', '$note*/*$address', '$nights', '$dat', '$room', '$persons', '$specialoffer', '' )"  ) ); 

					$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>Reservation added!</p></div>';
					?><meta http-equiv="refresh" content="1; url=admin.php?page=reservations&typ=pending"><?php
				}

				if($action == "edit") {

				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$rightdate2', nights='$nights', name='$name', phone='$phone', email='$email', notes='$note*/*$address', arrivalDate='$rightdate', room='$room', number='$persons', special='$specialoffer', dat='$dat', roomnumber='$roomex' WHERE id='$edit' ")); 

				$prompt='<div style="width: 97%; padding: 5px;" class="updated below-h2"><p>'.printf ( __( 'Reservation edited!' , 'easyReservations' )).'</p></div>';
				?><meta http-equiv="refresh" content="0; url=admin.php?page=add-reservation&edit=<?php echo $edit;?>"><?php
				}
				$room_category = get_option("reservations_room_category");
				$special_offer_cat = get_option("reservations_special_offer_cat");
				$reservation_form_address=get_option("reservation_form_address");
			echo $prompt;?>
			<script>
			  $(document).ready(function() {
				$("#datepicker").datepicker( { altFormat: 'dd.mm.yyyy' });
			  });
			</script><script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
			<div id="icon-options-general" class="icon32"><br></div><h2><?php printf ( __( 'Add Reservation' , 'easyReservations' ));?></h2>
			<div id="wrap">
				<?php if($edit){ ?><form method="post" action=""  id="edit"><input type="hidden" name="action" value="edit"/><?php } else {?><form method="post" action=""  id="addreservation"><input type="hidden" name="action" value="addreservation"/><?php } ?>								
						<table class="widefat" style="width:50%;">
						<thead>
						<tr>
							<th style="width:40%;">Titel</th>
							<th style="width:60%;">Value</th>
						</tr>
						</thead>
						<tbody>
						<tr valign="top">
							<td  style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td>
							<td><input type="text" name="name" align="middle" value="<?php if($edit) echo $namex;?>" class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/date.png" > <?php printf ( __( 'Date of Arrvial' , 'easyReservations' ));?></td>
							<td style="vertical-align:middle;"><input type="text" id="datepicker" name="date" value="<?php if($edit){ echo $datex; } ?>" class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td>
							<td><input type="text" name="email" value="<?php if($edit) echo $emailx;?>" class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/phone.png"> <?php printf ( __( 'Phone' , 'easyReservations' ));?></td>
							<td><input type="text" name="phone" value="<?php if($edit) echo $phonex;?>" class="regular-text"></td>
						</tr>
						<tr valign="top">
							<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/nights.png"> <?php printf ( __( 'Nights' , 'easyReservations' ));?></td>
							<td><select name="nights"><?php if($edit){?><option select="selected"><?php echo $nightsx;?></option><?php } ?><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option></select><span class="description"> Nights to stay</span></td>
						</tr>
						
						<tr valign="top">
							<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td>
							<td><select name="persons" style="width:40px;"><?php if($edit){?><option select="selected"><?php echo $personsx;?></option><?php } ?><option>1</option><option>2</option><option>3</option><option>4</option></select><span class="description"> Number of persons</span></td>
						</tr>
						<tr valign="top">
							<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?></td>
							<td><select id="room" name="room"><?php if($edit){?><option value="<?php echo $roomy;?>" select="selected"><?php echo __($roomx);?></option><?php } ?>
								<?php 
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
							<?php if($edit){ ?><select id="roomexactly" name="roomexactly">
								<?php 
								$roomcounty=get_post_meta($roomy, 'roomcount', true);
								$ix="0";
								if($roomcounty) echo '<option select="selected" value="'.$roomynumb.'">'.$roomynumb.'</option>';
								while($ix < $roomcounty){
								$ix++;
								echo '<option value="'.$ix.'">'.$ix.'</option>'; 
								}  echo '</select>'; }  ?>
							</td></tr>
						<tr valign="top">
							<td><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png" > <?php printf ( __( 'Special Offer' , 'easyReservations' ));?></td>
							<td><select name="specialoffer"><?php if($edit AND $specialy != "0"){?><option option value="<?php echo $specialy;?>" select="selected"><?php echo __($specialx);?></option><option><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php } else { ?><option value="0" select="selected"><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php }
								foreach( $roomsresult as $result )	{			
								$id=$result->ID;
								$categ=get_the_category($id);
								$care=$categ[0]->term_id;
								if($care==$special_offer_cat) {	echo '<option value="'.$id.'">'.__($result->post_title).'</option>'; }
								} ?>
							</select></td></tr>
							<?php if($reservation_form_address == "1"){ ?>
							<tr valign="top">
								<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/house.png"> <?php printf ( __( 'Address' , 'easyReservations' ));?>:</b></td> 
								<td><textarea name="address" cols="30" rows="1"><?php if($edit) echo $notesx[1];?></textarea></td>
							</tr>
							<?php } ?>
							<tr valign="top">
								<td><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/message.png"> <?php printf ( __( 'Notes' , 'easyReservations' ));?></td>
								<td><textarea name="note" cols="42" rows="10"><?php if($edit) echo $notesx[0];?></textarea><br><br><br><?php if($edit) {?><a href="javascript:{}" onclick="document.getElementById('edit').submit(); return false;" class="button-secondary"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><?php } else {?><a href="javascript:{}" onclick="document.getElementById('addreservation').submit(); return false;" class="button-secondary"><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a><?php } ?><br><br></td>
							</tr>
							</tbody></table></form></div>
<?php }
function reservation_settings_page() { //Set Settings
			global $wpdb;

			//Get current Options
			$reservations_price_per_persons = get_option("reservations_price_per_persons");
			$reservations_currency = get_option("reservations_currency");
			$season1 = get_option("reservation_season1");
			$season2 = get_option("reservation_season2");
			$season3 = get_option("reservation_season3");
			$season4 = get_option("reservation_season4");
			$season5 = get_option("reservation_season5");
			$reservation_support_mail = get_option("reservations_support_mail");
			$offer_cat = get_option("reservations_special_offer_cat");
			$room_category = get_option("reservations_room_category");
			
			// Get current Overview Colors
			$reservations_show_days=get_option("reservations_show_days");
			$border_bottom=get_option("reservations_border_bottom");
			$border_side=get_option("reservations_border_side");
			$backgroundiffull=get_option("reservations_backgroundiffull");
			$fontcoloriffull=get_option("reservations_fontcoloriffull");
			$fontcolorifempty=get_option("reservations_fontcolorifempty");
			$colorborder=get_option("reservations_colorborder");
			$colorbackgroundfree=get_option("reservations_colorbackgroundfree");

			//Get current Form Options
			$reservation_overview_showgeneraly=get_option("reservation_overview_showgeneraly");
			$reservation_form_address=get_option("reservation_form_address");
			$reservation_form_phone=get_option("reservation_form_phone");
			$reservation_form_nights=get_option("reservation_form_nights");
			$reservation_form_special=get_option("reservation_form_special");

			if(isset($_POST["action"])){ 
				$action = $_POST['action'];
			}
			
			if($action == "reservation_clean_database") {
			echo 'cleaned';
				$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() AND approve != 'yes' ") ); 
			}
			
			if($action == "reservation_settingss") {

			//Set Reservation settings 
			$reservations_price_per_persons = $_POST["reservations_price_per_persons"];
			$reservationss_support_mail = $_POST["reservations_support_mail"];
			$offer_cat = $_POST["special_offer_cat"];
			$room_category2 = $_POST["room_category"];
			update_option("reservations_price_per_persons",$reservations_price_per_persons);
			update_option("reservations_room_category",$room_category2);
			update_option("reservations_support_mail",$reservationss_support_mail);
			update_option("reservations_special_offer_cat",$offer_cat);	

			//Set Seasons
			$reservations_currency = $_POST["reservations_currency"];
			$season1 = $_POST["season1"];
			$season2 = $_POST["season2"];
			$season3 = $_POST["season3"];
			$season4 = $_POST["season4"];
			$season5 = $_POST["season5"];
			update_option("reservations_currency",$reservations_currency);
			update_option("reservation_season1",$season1);
			update_option("reservation_season2",$season2);
			update_option("reservation_season3",$season3);
			update_option("reservation_season4",$season4);
			update_option("reservation_season5",$season5);
			
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

			//Set Form Options
			$reservation_overview_showgeneraly = $_POST["reservation_overview_showgeneraly"];
			$reservation_form_address = $_POST["reservation_form_address"];
			$reservation_form_phone = $_POST["reservation_form_phone"];
			$reservation_form_nights = $_POST["reservation_form_nights"];
			$reservation_form_special = $_POST["reservation_form_special"];
			if($reservation_overview_showgeneraly != "1" OR !$reservation_overview_showgeneraly) $reservation_overview_showgeneraly="0";
			update_option("reservation_overview_showgeneraly",$reservation_overview_showgeneraly);
			update_option("reservation_form_address",$reservation_form_address);
			update_option("reservation_form_phone",$reservation_form_phone);
			update_option("reservation_form_nights",$reservation_form_nights);
			update_option("reservation_form_special",$reservation_form_special);
		}
    ?>
	<div id="icon-options-general" class="icon32"><br></div><h2><?php printf ( __( 'Settings' , 'easyReservations' ));?></h2><div id="wrap">
	<form method="post" action="admin.php?page=settings" id="reservation_clean_database">
	<input type="hidden" name="action" value="reservation_clean_database"/>
	<table style="width:99%">
	<tr><td style="width:75%;">
		<table class="widefat" style="width:100%;">
			<thead>
				<tr>
					<th style="width:45%;"> <?php printf ( __( 'Clean Database' , 'easyReservations' ));?></th>
					<th style="width:55%;"> </th>
				</tr>
			</thead>
			<tbody>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/database.png"> <?php printf ( __( 'Clean Database' , 'easyReservations' ));?></td>
						<td><input title="<?php printf ( __( 'Delete all unapproved, rejected or trashed Old Reservations' , 'easyReservations' ));?>" type="submit" value="<?php printf ( __( 'Clean Database' , 'easyReservations' ));?>" class="button-secondary"></td>
					</tr>
			</tbody>
		</table>
	</form><br>
	<form method="post" action=""  id="reservation_settingss">
		<input type="hidden" name="action" value="reservation_settingss"/>
		<div id="inputs">
			<table class="widefat" style="width:100%;">
			<thead>
				<tr>
					<th style="width:45%;"> <?php printf ( __( 'Overview Style' , 'easyReservations' ));?></th>
					<th style="width:55%;" > </th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/list.png"> <?php printf ( __( 'Show Overview Reservation on List', 'easyReservations' ));?></td>
					<td><input type="checkbox" title="<?php printf ( __( 'When deselected the Overview only will be shown when view, approve or reject a Reservation' , 'easyReservations' ));?>" name="reservation_overview_showgeneraly" value="1" <?php if(get_option("reservation_overview_showgeneraly") == "1") echo 'checked';?>></td>
				</tr>
				<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/day.png"> <?php printf ( __( 'Count Days' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Select how many Days to show at the Reservation Overview' , 'easyReservations' ));?>" name="reservations_show_days" value="<?php echo $reservations_show_days;?>" class="regular-text"></td>
				</tr>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/background.png"> <?php printf ( __( 'Style of Reservations' , 'easyReservations' ));?></td>
					<td><select data-hex="true" title="<?php printf ( __( 'Style of the Reservations in the Reservation Overview' , 'easyReservations' ));?>" name="backgroundiffull" class="regular-text"><option select="selected" value="<?php echo $backgroundiffull;?>" ><?php echo $backgroundiffull;?></option><option value="green" >green</option><option value="red" >red</option><option value="yellow" >yellow</option><option value="blue" >blue</option><option value="pink" >pink</option></select></td>
				</tr>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/background.png"> <?php printf ( __( 'Backgroundcolor if empty' , 'easyReservations' ));?></td>
					<td nowrap><input type="color" data-hex="true" title="<?php printf ( __( 'Background of the empty Days in the Reservation Overview' , 'easyReservations' ));?>" name="colorbackgroundfree" align="middle" value="<?php echo $colorbackgroundfree;?>" class="regular-text"></td>
				</tr>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/bordercolor.png"> <?php printf ( __( 'Bordercolor' , 'easyReservations' ));?></td>
					<td><input type="color" data-hex="true" title="<?php printf ( __( 'Color of the Border in the Reservation Overview' , 'easyReservations' ));?>" name="colorborder" align="middle" value="<?php echo $colorborder;?>" class="regular-text"></td>
				</tr>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/border.png"> <?php printf ( __( 'Border' , 'easyReservations' ));?></td>
					<td><?php printf ( __( 'Bottom' , 'easyReservations' ));?> <select data-hex="true" title="<?php printf ( __( 'Turn the Bottom-Border in the Reservation Overview on or off' , 'easyReservations' ));?>" name="border_bottom"><option select="selected" value="<?php echo $border_bottom;?>"><?php if($border_bottom=="0") printf ( __( 'None' , 'easyReservations' )); if($border_bottom=="1") printf ( __( 'Thin' , 'easyReservations' )); if($border_bottom=="2") printf ( __( 'Thick' , 'easyReservations' ));?></option><option value="0"><?php printf ( __( 'None' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Thin' , 'easyReservations' ));?></option><option value="2"><?php printf ( __( 'Thick' , 'easyReservations' ));?></option></select> <?php printf ( __( 'Side' , 'easyReservations' ));?> <select data-hex="true" title="<?php printf ( __( 'Turn the Side-Border in the Reservation Overview on or off' , 'easyReservations' ));?>" name="border_side"><option select="selected" value="<?php echo $border_side;?>"><?php if($border_side=="0") printf ( __( 'None' , 'easyReservations' )); if($border_side=="1") printf ( __( 'Thin' , 'easyReservations' )); if($border_side=="2") printf ( __( 'Thick' , 'easyReservations' ));?></option><option value="0"><?php printf ( __( 'None' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Thin' , 'easyReservations' ));?></option><option value="2"><?php printf ( __( 'Thick' , 'easyReservations' ));?></option></select></td>
				</tr>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/font.png"> <?php printf ( __( 'Font Color if Full' , 'easyReservations' ));?></td>
					<td><input type="color" data-hex="true" title="<?php printf ( __( 'Font Color of full Days in the Reservation Overview' , 'easyReservations' ));?>"  name="fontcoloriffull" align="middle" value="<?php echo $fontcoloriffull;?>" class="regular-text"></td>
				</tr>
				<tr valign="top">
					<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/font.png"> <?php printf ( __( 'Font Color if Empty' , 'easyReservations' ));?></td>
					<td><input type="color" data-hex="true" title="<?php printf ( __( 'Font Color of empty Days in the Reservation Overview' , 'easyReservations' ));?>"  name="fontcolorifempty" align="middle" value="<?php echo $fontcolorifempty;?>" class="regular-text"></td>
				</tr>
			</table></div>
			<br>
			<table class="widefat" style="width:100%;">
				<thead>
					<tr>
						<th style="width:45%;"> <?php printf ( __( 'Reservation Settings' , 'easyReservations' ));?> </th>
						<th style="width:55%;"> </th>
					</tr>
				</thead>
				<tbody>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/email.png"> <?php printf ( __( 'Reservation Support Mail' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Mail for Reservations' , 'easyReservations' ));?>" name="reservations_support_mail" value="<?php echo $reservation_support_mail;?>" class="regular-text"></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png"> <?php printf ( __( 'Special Offers Category' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'The ID of the Special Offer Category' , 'easyReservations' ));?>" name="special_offer_cat" value="<?php echo $offer_cat;?>" class="regular-text"> </td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/room.png"> <?php printf ( __( 'Rooms Category' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'The ID of the Rooms Category' , 'easyReservations' ));?>" name="room_category" value="<?php echo $room_category;?>" class="regular-text"></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/calc.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td><select name="reservations_price_per_persons" title="<?php printf ( __( 'Select type of Price calculation' , 'easyReservations' ));?>"><?php if($reservations_price_per_persons == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><?php } ?><?php if($reservations_price_per_persons == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Price per Person' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'Price per Room' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/dollar.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td><select name="reservations_currency" title="<?php printf ( __( 'Select currency' , 'easyReservations' ));?>"><?php if($reservations_currency=='euro'){ ?><option select="selected"  value="euro"><?php printf ( __( 'Euro' , 'easyReservations' ));?> &euro;</option><?php } ?><?php if($reservations_currency=='dollar'){ ?><option select="selected"  value="dollar"><?php printf ( __( 'Dollar' , 'easyReservations' ));?> &dollar;</option><?php } ?><?php if($reservations_currency == 'pound'){ ?><option select="selected"  value="pound"><?php printf ( __( 'Pound' , 'easyReservations' ));?> &pound;</option><?php } ?><?php if($reservations_currency == 'yen'){ ?><option select="selected"  value="yen"><?php printf ( __( 'Yen' , 'easyReservations' ));?> &yen;</option><?php } ?><option value="euro"><?php printf ( __( 'Euro' , 'easyReservations' ));?> &euro;</option><option value="dollar"><?php printf ( __( 'Dollar' , 'easyReservations' ));?> &dollar;</option><option  value="pound"><?php printf ( __( 'Pound' , 'easyReservations' ));?> &pound;</option><option  value="yen"><?php printf ( __( 'Yen' , 'easyReservations' ));?> &yen;</option></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/snow.png"> <?php printf ( __( 'Season #1' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Date of the 1st Season' , 'easyReservations' ));?>" name="season1" value="<?php echo $season1;?>" class="regular-text"></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/rain.png"> <?php printf ( __( 'Season #2' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Date of the 2nd Season' , 'easyReservations' ));?>" name="season2" value="<?php echo $season2;?>" class="regular-text"></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/sunny.png"> <?php printf ( __( 'Season #3' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Date of the 3rd Season' , 'easyReservations' ));?>" name="season3" value="<?php echo $season3;?>" class="regular-text"></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/rain.png"> <?php printf ( __( 'Season #4' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Date of the 4th Season' , 'easyReservations' ));?>" name="season4" value="<?php echo $season4;?>" class="regular-text"></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/snow.png"> <?php printf ( __( 'Season #5' , 'easyReservations' ));?></td>
						<td><input type="text" title="<?php printf ( __( 'Date of the 5th Season' , 'easyReservations' ));?>" name="season5" value="<?php echo $season5;?>" class="regular-text"> </td>
					</tr>
				</tbody>
			</table><br>
			<table class="widefat" style="width:100%;">
				<thead>
					<tr>
						<th style="width:45%;"> <?php printf ( __( 'Reservation Form Settings' , 'easyReservations' ));?> </th>
						<th style="width:55%;"> </th>
					</tr>
				</thead>
				<tbody>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/phone.png"> <?php printf ( __( 'Ask for phone?' , 'easyReservations' ));?></td>
						<td><select name="reservation_form_phone" title="<?php printf ( __( 'Select Yes to ask for Phone in Reservation Form' , 'easyReservations' ));?>"><?php if($reservation_form_phone == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'No' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Yes' , 'easyReservations' ));?></option><?php } ?><?php if($reservation_form_phone == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Yes' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'No' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/house.png"> <?php printf ( __( 'Ask for Address?' , 'easyReservations' ));?></td>
						<td><select name="reservation_form_address" title="<?php printf ( __( 'Select Yes to ask for Address in Reservation Form' , 'easyReservations' ));?>"><?php if($reservation_form_address == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'No' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Yes' , 'easyReservations' ));?></option><?php } ?><?php if($reservation_form_address == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Yes' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'No' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/date.png"> <?php printf ( __( 'Date Select Style' , 'easyReservations' ));?></td>
						<td><select name="reservation_form_nights" title="<?php printf ( __( 'Select type of Date choosing' , 'easyReservations' ));?>"><?php if($reservation_form_nights == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'From - To' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'From - Nights' , 'easyReservations' ));?></option><?php } ?><?php if($reservation_form_nights == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'From - Nights' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'From - To' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
					<tr valign="top">
						<td style="vertical-align:middle;"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/special.png"> <?php printf ( __( 'Special Style' , 'easyReservations' ));?></td>
						<td><select name="reservation_form_special" title="<?php printf ( __( 'Select Style of Special Offer' , 'easyReservations' ));?>"><?php if($reservation_form_special == '0'){ ?><option select="selected"  value="0"><?php printf ( __( 'Select' , 'easyReservations' ));?></option><option value="1"><?php printf ( __( 'Box' , 'easyReservations' ));?></option><?php } ?><?php if($reservation_form_special == '1'){ ?><option select="selected"  value="1"><?php printf ( __( 'Box' , 'easyReservations' ));?></option><option  value="0"><?php printf ( __( 'Select' , 'easyReservations' ));?></option><?php } ?></select></td>
					</tr>
				</tbody>
			</table>
			</td><td style="width:1%;" valign="top">
			</td><td style="width:24%;" valign="top">
					<table class="widefat">
						<thead>
							<tr>
								<th> <?php printf ( __( 'About this Plugin' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td style="vertical-align:middle;"><?php printf ( __( 'This Plugin was made to learn PHP.' , 'easyReservations' ));?><br><?php printf ( __( 'It isnt even nearly professional, but i  plan to improve it with future knowledge. ' , 'easyReservations' ));?><br><?php printf ( __( 'Ive you\'ve any tipps or suggestions please contact me over:' , 'easyReservations' ));?><br><b>feryazbeer@googemail.com</b></td>
								</tr>
						</tbody>
					</table><br>
					<table class="widefat">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Plugin Website' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
								<tr valign="top">
									<td style="vertical-align:middle;"><?php printf ( __( 'Website for Plugin is online!' , 'easyReservations' ));?><br><?php printf ( __( 'With a Documentation, a Vote-System for future Features and a Contact Page' , 'easyReservations' ));?><br><b><a href="http://www.feryaz.de/">www.feryaz.de</a></b></td>
								</tr>
						</tbody>
					</table>
		</td></tr></table><br>
		<a href="javascript:{}" onclick="document.getElementById('reservation_settingss').submit(); return false;" class="button-secondary" ><span><?php printf ( __( 'Save Changes' , 'easyReservations' ));?></span></a>
	</form>
</div>
<script>
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