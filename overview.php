<?php
	require('../../../wp-config.php');

	$moreget = $_POST['more'];
	$main_options = get_option("reservations_main_options");
	$overview_options = $main_options['overview'];

	if(isset($_POST['interval'])) $interval = $_POST['interval'];
	else $interval = 86400;

	if($interval == 3600){
		$date_pat = "d.m.Y H";
	} else {
		$date_pat = "d.m.Y";
	}

	if(isset($_POST['dayPicker'])){
		$dayPicker=$_POST['dayPicker'];
		$daysbetween=(strtotime($dayPicker)-strtotime(date("d.m.Y", time())))/$interval;
		$moreget=round($daysbetween/$interval*$interval)+2;
		$main_options = get_option("reservations_main_options");
		$overview_options = $main_options['overview'];
	}
	$monthes = easyreservations_get_date_name(1);
	$days = easyreservations_get_date_name(0,2);

	if(isset($_POST['roomwhere']) && !empty($_POST['roomwhere'])) $roomwhere = $_POST['roomwhere'];
	if(isset($_POST['approve'])) $approve = $_POST['approve'];
	if(isset($_POST['add']) && !empty($_POST['add'])) $add = $_POST['add'];
	if(isset($_POST['edit']) && !empty($_POST['edit'])) $edit = $_POST['edit'];
	if(isset($_POST['nonepage']) && $_POST['nonepage'] == 0) $nonepage = $_POST['nonepage'];
	if(isset($_POST['id']) && !empty($_POST['id'])) $id = $_POST['id'];
	if(isset($_POST['res_date_from_stamp'])){
		$exlodetime = explode("-", $_POST['res_date_from_stamp']);
		$reservation_arrival_stamp = $exlodetime[0];
		$reservation_departure_stamp = $exlodetime[1];
	}

	if(isset($_POST['daysshow'])) $daysshow = $_POST['daysshow'];
	else $daysshow = $overview_options['overview_show_days']; //How many Days to Show
	$reservations_show_rooms = $overview_options['overview_show_rooms'];

	if(!isset($reservations_show_rooms) || empty($reservations_show_rooms)) $show_rooms=easyreservations_get_rooms(0,1);
	else {
		global $wpdb;
		$show_rooms = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE ID in($reservations_show_rooms) ORDER BY menu_order");
	}

	/* - - - - - - - - - - - - - - - - *\
	|
	|	Calculate Overview
	|
	/* - - - - - - - - - - - - - - - - */

	$timevariable=strtotime(date("d.m.Y", time()))-($interval*3); //Timestamp of first Second of today
	$eintagmalstart=$interval*$moreget;
	$eintagmalend=$interval*$daysshow;
	$timesx=$timevariable+$eintagmalstart; // Timestamp of Startdate of Overview
	$timesy=$timesx+$eintagmalend; // Timestamp of Enddate of Overview
	$more=$moreget;
	$dateshow=date("d. ", $timesx).$monthes[date("n", $timesx)-1].date(" Y", $timesx).' - '.date("d. ", $timesy-$interval).$monthes[date("n", $timesy-$interval)-1].date(" Y", $timesy-$interval);											
	$stardate=date("Y-m-d H:i", $timesx); // Formated Startdate
	$enddate=date("Y-m-d H:i", $timesy-$interval); // Formated Enddate
	if(!isset($daysbetween)) $daysbetween = ($timesx/$interval)-(strtotime(date("d.m.Y", time()))/$interval);

	if(isset($reservation_arrival_stamp)){
		$numberhighstart=ceil(($reservation_arrival_stamp-$timesx)/$interval);
		$numberlaststart=ceil(($reservation_departure_stamp-$timesx)/$interval);
		if($numberlaststart<10) $numberlaststart='0'.$numberlaststart;
		if($numberhighstart<10) $numberhighstart='0'.$numberhighstart;
	}

	if(!isset($moreget)) $moreget=0;

	if(RESERVATIONS_STYLE == 'widefat'){
		$ovBorderColor='#9E9E9E';
		$ovBorderStatus='dotted';
	} elseif(RESERVATIONS_STYLE == 'greyfat'){
		$ovBorderColor='#777777';
		$ovBorderStatus='dashed';
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + OVERVIEW + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?><input type="hidden" id="hiddenfieldclick" name="hiddenfieldclick"><input type="hidden" id="timesx" name="timesx" value="<?php echo $timesx; ?>">
	<input type="hidden" id="hiddenfieldclick2" name="hiddenfieldclick2"><input type="hidden" id="timesy" name="timesy" value="<?php echo $timesy; ?>">
	<input type="hidden" id="getmore" name="getmore" value="<?php echo $moreget; ?>">
	<table class="<?php echo RESERVATIONS_STYLE; ?> overview" cellspacing="0" cellpadding="0" id="overview" style="width:99%;" onmouseout="document.getElementById('ov_datefield').innerHTML = '';">
		<thead>
			<tr>
				<th colspan="<?php echo $daysshow+1; ?>"  class="overviewHeadline">
					<span id="pickForm"><input name="dayPicker" id="dayPicker" type="hidden" value="<?php if(isset($dayPicker)) echo $dayPicker; ?>"></span> &nbsp;<b class="overviewDate"><?php echo $dateshow; ?></b><span id="ov_datefield"></span>
					<span style="float:right">
						<?php if($interval == 3600){ ?> 
							<input name="daybutton" class="easySubmitButton-secondary" value="Days" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moreget; ?>','no','<?php echo $daysshow; ?>',86400);resetSet();">
						<?php } else { ?>
							<input name="daybutton" class="easySubmitButton-secondary" value="Hours" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moreget; ?>','no','<?php echo $daysshow; ?>',3600);resetSet();">
						<?php } ?>
						<input name="settimes" class="easySubmitButton-secondary" value="15" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moreget; ?>','',15,<?php echo $interval; ?>);resetSet();">
						<input name="settimes" class="easySubmitButton-secondary" value="30" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moreget; ?>','',30,<?php echo $interval; ?>);resetSet();">
						<input name="settimes" class="easySubmitButton-secondary" value="45" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moreget; ?>','',45,<?php echo $interval; ?>);resetSet();">
					</span>
				</th>
			</tr>
		<tr id="overviewTheadTr">
			<td style="width:126px;vertical-align:middle;text-align:center;font-size:18px;" class="h1overview" <?php if($interval == 3600) echo 'rowspan="2"'; ?>>
				<a onclick="easyRes_sendReq_Overview('<?php echo $moreget-($daysshow);?>','no', '<?php echo $daysshow; ?>', <?php echo $interval; ?>);" title="-<?php echo ($daysshow).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b style="letter-spacing:-4px">&lsaquo; &lsaquo; &lsaquo; &nbsp;&nbsp;</b></a> 
				<a onclick="easyRes_sendReq_Overview('<?php echo $moreget-round($daysshow/2);?>','no', '<?php echo $daysshow; ?>',<?php echo $interval; ?>);" title="-<?php echo round($daysshow/2).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&laquo;</b></a> 
				<a onclick="easyRes_sendReq_Overview('<?php echo $moreget-round($daysshow/3);?>','no', '<?php echo $daysshow; ?>',<?php echo $interval; ?>);" title="-<?php echo round($daysshow/3).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&lsaquo;</b></a> 
				<span id="easy-overview-loading"><a onclick="easyRes_sendReq_Overview('0','no', '<?php echo $daysshow; ?>',<?php echo $interval; ?>);" title="<?php echo __( 'Present' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&omicron;</b></a></span>
				<a onclick="easyRes_sendReq_Overview('<?php echo $moreget+round($daysshow/3);?>','no', '<?php echo $daysshow; ?>',<?php echo $interval; ?>);" title="+<?php echo round($daysshow/3).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&rsaquo;</b></a> 
				<a onclick="easyRes_sendReq_Overview('<?php echo $moreget+round($daysshow/2);?>','no', '<?php echo $daysshow; ?>',<?php echo $interval; ?>);" title="+<?php echo round($daysshow/2).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&raquo;</b></a> 
				<a onclick="easyRes_sendReq_Overview('<?php echo $moreget+($daysshow);?>','no', '<?php echo $daysshow; ?>',<?php echo $interval; ?>);" title="+<?php echo ($daysshow).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b style="letter-spacing:-4px">&rsaquo; &rsaquo; &rsaquo; &nbsp;&nbsp;</b></a>
			</td>
	<?php
		$co=0;
		$lastdate = time();
		if(isset($nonepage)) $date_style = 'ov-days-hover'; else $date_style = '';
		while($co < $daysshow){
			$thedaydate=$timesx+($interval*$co);
			if($interval == 86400 || date("d.m.Y", $lastdate) != date("d.m.Y", $thedaydate)){
				if($interval == 3600){
					$tomorrow = strtotime(date("d.m.Y", $thedaydate))+86400;
					$diff = round(($tomorrow - $thedaydate)/$interval);
				} else{
					$diff = 1;
					if(isset($reservation_arrival_stamp) && $thedaydate >=  $reservation_arrival_stamp-$interval && $thedaydate <= $reservation_departure_stamp) $background_highlight='backgroundhighlight';
				}
				if(date("d.m.Y", $thedaydate) ==  date("d.m.Y", time())) $background_highlight='backgroundtoday';
				elseif(!isset($background_highlight)) $background_highlight='backgroundnormal';?>
				<td colspan="<?php echo $diff; ?>" class="<?php echo  $background_highlight; ?> ov-days <?php echo $date_style; ?>" style="vertical-align:middle;padding:1px 0px;<?php if($interval == 86400) echo 'min-width:20px;'; ?>" onclick="overviewSelectDate('<?php echo date(RESERVATIONS_DATE_FORMAT,$thedaydate); ?>');">
					<?php if($interval == 86400) echo date("j",$thedaydate).'<br><small>'.$days[date("N",$thedaydate)-1]; else echo '<small style="overflow:hidden;display:inline-block;">'.date(RESERVATIONS_DATE_FORMAT, $thedaydate);; echo '</small>'; ?>
				</td><?php
				unset($background_highlight);
			}
			$co++;
			$lastdate = $thedaydate;
		} ?>
	</tr>
	<?php if($interval == 3600) { ?><tr><?php
		$co=0;
		if(isset($nonepage)) $date_style = 'ov-days-hover'; else $date_style = '';
		while($co < $daysshow){
			$thedaydate=$timesx+($interval*$co);
			if(isset($reservation_arrival_stamp) && $thedaydate >= $reservation_arrival_stamp && $thedaydate <= $reservation_departure_stamp) $background_highlight='backgroundhighlight';
			elseif(date("H", $thedaydate) == 00)  $background_highlight='ov-days-hours-first';
			else $background_highlight='ov-days-hours';?>
			<td  class="<?php echo  $background_highlight; ?> ov-days <?php echo $date_style; ?>" style="vertical-align:middle;min-width:23px" onclick="overviewSelectDate('<?php echo date(RESERVATIONS_DATE_FORMAT,$thedaydate); ?>');">
				<?php echo '<small>'.date("H", $thedaydate).'</small>'; ?>
			</td><?php $co++;
		} ?>
		</tr>
	<?php } ?>
	
</thead>
<tfoot>
	<tr>
		<th colspan="<?php echo $daysshow+1; ?>" class="overviewFooter">
			<span style="vertical-align:middle;" id="resetdiv"></span>
			<span style="float:right;">
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'/images/blue_dot.png'; ?>">&nbsp;<small><?php echo __( 'Past' , 'easyReservations' ); ?></small> 
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'/images/green_dot.png'; ?>">&nbsp;<small><?php echo __( 'Present' , 'easyReservations' ); ?></small> 
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'/images/red_dot.png'; ?>">&nbsp;<small><?php echo __( 'Future' , 'easyReservations' ); ?></small>
				<?php if(isset($id)){ ?> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'/images/yellow_dot.png'; ?>">&nbsp;<small><?php echo __( 'Active' , 'easyReservations' ); ?></small><?php } ?>
			</span>
		</th>
	</tr>
</tfoot>
<tbody>
<?php
	if(isset($roomwhere)) $all_resources = $wpdb->get_results("SELECT ID, post_title FROM wp_posts WHERE ID='$roomwhere'");
	else $all_resources = $show_rooms;

	foreach( $all_resources as $key => $resource ){ /* - + - FOREACH ROOM - + - */
		$res = new Reservation(false, array('dontclean', 'arrival' => $timesx, 'resource' => (int) $resource->ID ));
		$res->interval = $interval;
		$roomID=$resource->ID;
		$roomcounty = get_post_meta($roomID, 'roomcount', TRUE);
		$resource_names = get_post_meta($roomID, 'easy-resource-roomnames', TRUE);
		$rowcount=0;

		$resource_sql = $wpdb->get_results($wpdb->prepare("SELECT id, name, departure, arrival, roomnumber FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$roomID' AND (arrival BETWEEN '$stardate' AND '$enddate' OR departure BETWEEN '$stardate' AND '$enddate' OR '$stardate'  BETWEEN arrival AND departure) ORDER BY room ASC, roomnumber ASC, arrival ASC"));

		unset($reservations);
		foreach($resource_sql as $resourc){
			if(!empty($resourc->roomnumber)){
				$reservations[$resourc->roomnumber][] = array($resourc);
				$co=0;
			}
		} ?>
		<tr class="ov_resource_row" style="background:#EAE8E8">
			<td><span>&nbsp;<a href="admin.php?page=reservation-resources&room=<?php echo $resource->ID; ?>" style="color: #6B6B6B;"><?php echo __( $resource->post_title); ?></a></td>
				<?php
				$co=0;
				while($co < $daysshow){
					if($overview_options['overview_show_avail'] == 1){
						$res->arrival = $timesx+($co*$interval);
						$roomDayPersons=round($roomcounty-$res->checkAvailability(3),1);
						if($roomDayPersons <= 0) $textcolor='#FF3B38'; else $textcolor='#118D18';
					} else $textcolor = '';
					?><td axis="<?php echo $co+2;?>" style="color:<?php echo $textcolor; ?>" ><?php if($overview_options['overview_show_avail'] == 1)  echo '<small>'.$roomDayPersons.'</small>'; ?></small></td><?php
					$co++;
				} ?>
		</tr><?php

		while($roomcounty > $rowcount){  /* - + - FOREACH EXACTLY ROOM - + - */
			if(isset($resource_names[$rowcount]) && !empty($resource_names[$rowcount])) $name = $resource_names[$rowcount];
			else $name = '#'.($rowcount+1);
			$rowcount++;

			if($timesx < time()) $lastbackground='#2A78D8';
			else $lastbackground='#CC3333';
			if($rowcount == $roomcounty) $borderbottom=0;
			else $borderbottom=1; ?>
			<tr id="room<?php echo $rowcount.'-'.$roomID; ?>">
				<td class="roomhead" style="color:#8C8C8C;" onclick="<?php if(isset($edit)){ ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$reservation_arrival_stamp); ?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$reservation_departure_stamp); ?>';setVals2(<?php echo $roomID; ?>,<?php echo $rowcount; ?>);<?php } if(isset($edit) || isset($approve)){ ?>changer();clickOne(document.getElementById('<?php echo $roomID.'-'.$rowcount.'-'.$numberhighstart; ?>'),'<?php echo $reservation_arrival_stamp; ?>');clickTwo(document.getElementById('<?php echo $roomID.'-'.$rowcount.'-'.$numberlaststart; ?>'),'<?php echo $reservation_departure_stamp; ?>');<?php } if(isset($approve)){ ?>document.reservation_approve.roomexactly.selectedIndex=<?php echo $rowcount-1; ?>;<?php } ?>"  nowrap>
					&nbsp;<?php echo $name; ?>
				</td><?php

			$CoutResNights2=0; $CoutResNights3=0; $CountNumberOfAdd=0; $wasFull=0; $countdifferenz=0; $itIS=0; $cellcount=0; $datesHalfOccupied = '';

			if(isset($reservations[$rowcount])){
				foreach($reservations[$rowcount] as $reservationsd){
					foreach($reservationsd as $reservation){
						$res_id=$reservation->id;
						$res_name=$reservation->name;
						$res_adate_stamp = strtotime($reservation->arrival);
						$res_adate = $res_adate_stamp - (int) date("i", $res_adate_stamp);
						$res_departure_stamp= strtotime($reservation->departure);
						$res_departure = $res_departure_stamp - (int) date("i", $res_departure_stamp);
						$res_nights = ($res_departure_stamp - $res_adate_stamp) / $interval;
						if($res_nights < 1){
							$round = round((($res_adate + $res_departure-$interval)/2)/$interval) * $interval;
							$datesHalfOccupied[$round]['i'] += 1;
							$datesHalfOccupied[$round]['v'] .= date('d.m H:i', $res_adate_stamp).' - '.date('d.m H:i', $res_departure_stamp).' <b>'.$res_name.'</b> (#'.$res_id.')<br>';
							$datesHalfOccupied[$round]['id'][] = $res_id;
						} else {
							$res_nights = round($res_nights);
							for($i=0; $i <= $res_nights; $i++){
								if($timesx <= $res_adate+($i*$interval) && $res_nights >= 1){
									$daysOccupied[]=date($date_pat, $res_adate+($i*$interval)+$interval);
									$numberOccupied[]=$countdifferenz;
								}
							}
						}
						$reservationarray[]=array( 'name' =>$res_name, 'ID' =>$res_id, 'departure' => $res_departure, 'arDate' => $res_adate, 'nights' => $res_nights );
						$countdifferenz++;
					}
				}
			}

			$showdatenumber_start=0+$more;
			$showdatenumber_end=$daysshow+$more;

			while($showdatenumber_start < $showdatenumber_end){
				$cellcount++;
				$showdatenumber_start++;
				$oneDay=$interval*$showdatenumber_start;
				$dateToday=$timevariable+$oneDay;
				$wasFullTwo=0;
				$borderside=1;
				$onClick=0;

				if($cellcount < 10) $preparedCellcount='0'.$cellcount;
				else $preparedCellcount=$cellcount;

				if($dateToday < time()) $background2="url(".RESERVATIONS_URL ."/images/patbg.png) repeat";
				else $background2='';

				$res->arrival = $dateToday-$interval;
				$avail = $res->availFilter($roomcounty, 0, (int) $interval);

				if($avail > 0) $colorbgfree='#FFEDED';
				elseif(date($date_pat, $dateToday-$interval)==date($date_pat, time())) $colorbgfree = '#EDF0FF';
				elseif(date("N", $dateToday-$interval)==6 OR date("N", $dateToday-$interval)==7) $colorbgfree = '#FFFFEB';
				else $colorbgfree='#FFFFFF';

				if(isset($daysOccupied)){

					if(in_array(date($date_pat, $dateToday), $daysOccupied)){

						if($numberOccupied[$CoutResNights3] != $CountNumberOfAdd && $cellcount != 1) $CountNumberOfAdd++;
						if($reservationarray[$CountNumberOfAdd]['nights'] < 1) while($reservationarray[$CountNumberOfAdd]['nights'] < 1) $CountNumberOfAdd++;
						$arrival = $reservationarray[$CountNumberOfAdd]['arDate'];
						$departure = $reservationarray[$CountNumberOfAdd]['departure'];
						$nights = $reservationarray[$CountNumberOfAdd]['nights'];

						if(isset($daysOccupied[$CoutResNights3+1]) && isset($numberOccupied[$CoutResNights3-1]) && $numberOccupied[$CoutResNights3-1] != $daysOccupied[$CoutResNights3] && $numberOccupied[$CoutResNights3-1] != $numberOccupied[$CoutResNights3]) $wasFullTwo=1;

						if(($CoutResNights2 == 0 && $cellcount != 1) || ($wasFullTwo == 1 && $cellcount != 1) || $dateToday - $arrival <= $interval){
							$farbe2="url(".RESERVATIONS_URL ."/images/DERSTRING_start.png) right top no-repeat, ".$background2." ".$colorbgfree; 
							$itIS=0;
						} elseif($CoutResNights2 != 0 || $cellcount == 1 || (isset($daysOccupied[$CoutResNights3]) && $lastDay==$daysOccupied[$CoutResNights3])){
							$farbe2="url(".RESERVATIONS_URL ."/images/DERSTRING_middle.png) top repeat-x";
							if($cellcount != 1) $borderside=0;
							$itIS++;
						}
						if(isset($daysOccupied[$CoutResNights3+1]) AND $daysOccupied[$CoutResNights3] != $daysOccupied[$CoutResNights3+1] && $numberOccupied[$CoutResNights3] != $numberOccupied[$CoutResNights3+1]){
							$farbe2="url(".RESERVATIONS_URL ."/images/DERSTRING_end.png) left top no-repeat, ".$background2." ".$colorbgfree; 
							$itIS=0;
						}
						if(isset($daysOccupied[$CoutResNights3+1]) && $daysOccupied[$CoutResNights3] == $daysOccupied[$CoutResNights3+1] && array_key_exists($CoutResNights3+1, $daysOccupied)){
							$farbe2='url('.RESERVATIONS_URL .'/images/DERSTRING_cross.png) left top no-repeat DERZEWEITESTRING';
							$CoutResNights2=0;
							$CoutResNights3++;
							$CountNumberOfAdd++;
							$arrival = $reservationarray[$CountNumberOfAdd]['arDate'];
							$departure = $reservationarray[$CountNumberOfAdd]['departure'];
							$nights = $reservationarray[$CountNumberOfAdd]['nights'];
							$itIS=0;
							$onClick=1;
						}
						if(!in_array(date($date_pat, $dateToday+$interval), $daysOccupied)) $farbe2="url(".RESERVATIONS_URL ."/images/DERSTRING_end.png) left top no-repeat, ".$background2." ".$colorbgfree; 

						$CoutResNights2++;
						$CoutResNights3++;
						$addname=" ";
						$lastDay=$daysOccupied[$CoutResNights3-1];
						if(isset($id) && $reservationarray[$CountNumberOfAdd]['ID'] == $id){
							$farbe2=str_replace("DERSTRING", "yellow", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#FFE400';
							$addname=' name="activeres"';
						} elseif($arrival < time() && $departure > time()){
							$farbe2=str_replace("DERSTRING", "green", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#118D18';
						} elseif($arrival > time()){
							$farbe2=str_replace("DERSTRING", "red", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#CC3333';
						} else {
							$farbe2=str_replace("DERSTRING", "blue", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#2A78D8';
						}

						$minusdays=0;
						$nightsproof=$nights;

						if($arrival < $timesx){
							$daybetween=($timesx-$arrival)/$interval;
							$minusdays=round($daybetween)-1;
							$nightsproof=$nights-$minusdays;
						} 
						if($departure > $timesy) {
							$daybetween=($timesy/$interval)-(($arrival/$interval)+$nights);
							$minusdays+=substr(round($daybetween), 1, 10);
							$nightsproof=$nights-$minusdays;
						}
						
						$title_one = 	date('d.m H:i', $arrival).' - '.date('d.m H:i', $departure).' <b>'.$reservationarray[$CountNumberOfAdd]['name'].'</b> (#'.$reservationarray[$CountNumberOfAdd]['ID'].')<br>';

						if($itIS==1){
							?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>"<?php echo $addname; ?> title="<?php echo $title_one; ?>" colspan="<?php echo $nights-1-$minusdays; ?>" class="er_overview_cell" onclick="<?php echo "location.href = 'admin.php?page=reservations&edit=".$reservationarray[$CountNumberOfAdd]['ID']."';"; ?>" style="background: <?php echo $farbe2;?>;cursor: pointer;text-decoration:none;padding:0px;font: normal 11px Arial, sans-serif;vertical-align:middle;; overflow:hidden;"  abbr="<?php echo $farbe2;?>" title="<?php echo $reservationarray[$CountNumberOfAdd]['name']; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date('d.m H:i', $arrival+$interval).' - '.date('d.m H:i', $departure); ?>');"<?php } ?>>
							<?php echo substr($reservationarray[$CountNumberOfAdd]['name'], 0, ($nights-1-$minusdays)*3); ?>
							</td><?php
						} elseif($itIS==$nightsproof+1 || $itIS==$nightsproof || $itIS==0) {
								$value = ''; $title = '';
								if($borderside == 0 ) $title .= $title_one;
								if(isset($datesHalfOccupied[$dateToday-$interval])){
									$value = $datesHalfOccupied[$dateToday-$interval]['i'];
									$title .= $datesHalfOccupied[$dateToday-$interval]['v'];
									$tableclick = 'document.getElementById(\'easy-table-search-field\').value = \''.$reservationarray[$CountNumberOfAdd]['ID'].','.implode(',', $datesHalfOccupied[$dateToday-$interval]['id']).'\';easyreservation_send_table(\'all\', 1);';
									if(isset($id) && in_array($id, $datesHalfOccupied[$dateToday-$interval]['id'])) $colorbgfree = '#FCEA74';
									elseif(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", time())) $colorbgfree = '#6ECC72';
									elseif($dateToday-$interval > time()) $colorbgfree = '#E07B7B';
									else $colorbgfree = '#6F9DD6';
									$farbe2 = substr($farbe2, 0, -7).$colorbgfree;
								}
								if($borderside == 1 ) $title .= $title_one;?>
							<td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>"  title="<?php echo $title; ?>" <?php if($borderside == 0) echo 'class="er_overview_cell"'; ?> <?php echo $addname; ?> onclick="<?php if(isset($nonepage) && isset($tableclick)) echo $tableclick; elseif((isset($edit) || isset($add) || isset($nonepage)) && $onClick==0){ ?>;changer();clickTwo(this,'<?php echo $dateToday-$interval; ?>');clickOne(this,'<?php echo $dateToday-$interval; ?>');setVals2('<?php echo $roomID; ?>','<?php echo $rowcount; ?>');<?php  } elseif($onClick==1){ echo "location.href = 'admin.php?page=reservations&edit=".$reservationarray[$CountNumberOfAdd]['ID']."';"; } ?>" style="background: <?php echo $farbe2;?>; padding:0px; overflow:hidden; text-shadow:none; border-style:none; text-decoration:none; font: normal 11px Arial, sans-serif; vertical-align:middle;<?php if($onClick==1) echo 'cursor:pointer'; ?>" abbr="<?php echo $farbe2;?>" axis="<?php echo $cellcount+1; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date(RESERVATIONS_DATE_FORMAT, $dateToday-$interval); ?>');"<?php } ?>>
								<?php echo $value; ?>
							</td><?php
						}
						unset($tableclick);
						$lastbackground=$backgrosund;
						$wasFull=1;
					} else {
						if($wasFull == 1) $CountNumberOfAdd++;
						$CoutResNights2=0;
						$class = ''; $value = ''; $title = '';
						if(isset($datesHalfOccupied[$dateToday-$interval])){
							$value = $datesHalfOccupied[$dateToday-$interval]['i'];
							$title = $datesHalfOccupied[$dateToday-$interval]['v'];
							$class = 'class="er_overview_cell"';
							$tableclick = 'onclick="document.getElementById(\'easy-table-search-field\').value = \''.implode(',', $datesHalfOccupied[$dateToday-$interval]['id']).'\';easyreservation_send_table(\'all\', 1);"';
							if(isset($id) && in_array($id, $datesHalfOccupied[$dateToday-$interval]['id'])) $colorbgfree = '#FCEA74';
							if(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", time())) $colorbgfree = '#118D18';
							elseif($dateToday-$interval > time()) $colorbgfree = '#CC3333';
							else $colorbgfree = '#2A78D8';
						}
						?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>" title="<?php echo $title; ?>" <?php echo $class; if(isset($edit) || isset($add) || isset($nonepage)){ ?>onclick="<?php if(isset($nonepage) && isset($tableclick)) echo $tableclick; else { ?>changer();clickTwo(this,'<?php echo $dateToday-$interval; ?>');clickOne(this,'<?php echo $dateToday-$interval; ?>');setVals2('<?php echo $roomID; ?>','<?php echo $rowcount; ?>');<?php } ?>"<?php } ?> style="background:<?php echo $background2.' '.$colorbgfree;?>" abbr="<?php echo $background2.' '.$colorbgfree;?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date($date_pat, $dateToday-$interval); ?>');"<?php } ?> axis="<?php echo $cellcount+1; ?>">
								<?php echo $value; ?>
						<?php
						$wasFull=0;
					}
				} else {
					$class = ''; $value = ''; $title = '';
					if(isset($datesHalfOccupied[$dateToday-$interval])){
						$value = $datesHalfOccupied[$dateToday-$interval]['i'];
						$title = $datesHalfOccupied[$dateToday-$interval]['v'];
						$class = 'class="er_overview_cell"';
						$tableclick = 'onclick="document.getElementById(\'easy-table-search-field\').value = \''.implode(',', $datesHalfOccupied[$dateToday-$interval]['id']).'\';easyreservation_send_table(\'all\', 1);"';
						if(isset($id) && in_array($id, $datesHalfOccupied[$dateToday-$interval]['id'])) $colorbgfree = '#FCEA74';
						if(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", time())) $colorbgfree = '#118D18';
						elseif($dateToday-$interval > time()) $colorbgfree = '#CC3333';
						else $colorbgfree = '#2A78D8';
					}
					?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>" title="<?php echo $title; ?>" <?php echo $class; if(isset($edit) || isset($add) || isset($nonepage)){ ?>onclick="<?php if(isset($nonepage) && isset($tableclick)) echo $tableclick; else { ?>changer();clickTwo(this,'<?php echo  $dateToday-$interval; ?>');clickOne(this,'<?php echo $dateToday-$interval; ?>');setVals2('<?php echo $roomID; ?>','<?php echo $rowcount; ?>');<?php } ?>"<?php } ?> style="background:<?php echo $background2.' '.$colorbgfree;?>" abbr="<?php echo $background2.' '.$colorbgfree;?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date($date_pat, $dateToday-$interval); ?>');"<?php } ?> axis="<?php echo $cellcount+1; ?>"><?php echo $value; ?></td><?php
				}
			}
			unset($daysOccupied);
			unset($datesHalfOccupied);
			unset($numberOccupied);
			unset($reservationarray);
			echo '</tr>';
		}
		$res->destroy();
	} ?></tbody>
</table>