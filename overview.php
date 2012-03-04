<?php
	if(isset($_POST['more'])){
		require('../../../wp-blog-header.php');
		$moreget = $_POST['more'];
		$moregets = $_POST['more'];
		$main_options = get_option("reservations_main_options");
		$overview_options = $main_options['overview'];
	}

	if(isset($_POST['dayPicker'])){
		$dayPicker=$_POST['dayPicker'];
		require('../../../wp-blog-header.php');

		$daysbetween=(strtotime($dayPicker)-strtotime(date("d.m.Y", time())))/86400;
		$moreget=$daysbetween+2;
		$moregets=$daysbetween+2;
		$main_options = get_option("reservations_main_options");
		$overview_options = $main_options['overview'];
	}

	if(isset($_POST['reservationDate'])) $reservationDate = $_POST['reservationDate'];
	if(isset($_POST['reservationNights'])) $reservationNights = $_POST['reservationNights'];
	if(isset($_POST['roomwhere'])) $roomwhere = $_POST['roomwhere'];
	if(isset($_POST['add'])) $add = $_POST['add'];
	if(isset($_POST['edit'])) $edit = $_POST['edit'];
	if(isset($_POST['nonepage'])) $nonepage = $_POST['nonepage'];
	if(isset($_POST['id'])) $id = $_POST['id'];
	if(isset($_POST['daysshow'])) $daysshow = $_POST['daysshow'];
	else	$daysshow = $overview_options['overview_show_days']; //How many Days to Show
	$reservations_show_rooms = $overview_options['overview_show_rooms'];

	if($reservations_show_rooms == ''){
		$RoomsArray=reservations_get_room_ids(1);
	} else {
		$explodeShowingRooms = explode(",",$reservations_show_rooms);
		foreach($explodeShowingRooms as $ShowingRoom){
			$RoomsArray[] = $ShowingRoom;
		}
	}

	/* - - - - - - - - - - - - - - - - *\
	|
	|	Calculate Overview
	|
	/* - - - - - - - - - - - - - - - - */

	$timevariable=strtotime(date("d.m.Y", time()))-172800; //Timestamp of first Second of today
	$eintagmalstart=86400*$moreget;
	$eintagmalend=86400*$daysshow;
	$timesx=$timevariable+$eintagmalstart; // Timestamp of Startdate of Overview
	$timesy=$timesx+$eintagmalend; // Timestamp of Enddate of Overview
	$more=$moreget;
	$dateshow=date("d. F Y", $timesx).' - '.date("d. F Y", $timesy-86400);											
	$stardate=date("Y-m-d", $timesx); // Formated Startdate
	$enddate=date("Y-m-d", $timesy-86400); // Formated Enddate

	if(!isset($daysbetween)){
		$daysbetween=($timesx/86400)-(strtotime(date("d.m.Y", time()))/86400);
	}

	if(isset($reservationDate)){
		$numberhighstart=(strtotime($reservationDate)-$timesx)/86400+1;
		$numberlaststart=((strtotime($reservationDate)+(86400*$reservationNights))-$timesx)/86400+1;
		
		if($numberlaststart<10) $numberlaststart='0'.$numberlaststart;
		if($numberhighstart<10) $numberhighstart='0'.$numberhighstart;
	}
	
	if(!isset($moregets)) $moregets=0;
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
	<input type="hidden" id="hiddenfieldclick2" name="hiddenfieldclick2"><input type="hidden" id="timesy" name="timesy" value="<?php echo $timesy; ?>"><input type="hidden" id="getmore" name="getmore" value="<?php echo $moregets; ?>">
	<table class="<?php echo RESERVATIONS_STYLE; ?> overview" cellspacing="0" cellpadding="0" id="overview" style="width:99%;" onmouseout="document.getElementById('ov_datefield').innerHTML = '';">
		<thead>
			<tr>
				<th colspan="<?php echo $daysshow+1; ?>"  class="overviewHeadline"><form id="pickForm" action="" name="pickForm" method="post" style="float:left;margin-top:2px;"><input name="dayPicker" id="dayPicker" type="hidden"></form> &nbsp;<b class="overviewDate"><?php echo $dateshow; ?></b><span id="ov_datefield"></span><form method="post" style="float:right"><input name="daybutton" class="easyButton" value="10" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moregets; ?>','',10);resetSet();"><input name="daybutton" class="easyButton" value="20" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moregets; ?>','',20);resetSet();"><input name="daybutton" class="easyButton" value="30" type="button" onclick="easyRes_sendReq_Overview('<?php echo $moregets; ?>','',30);resetSet();"></form></th>
			</tr>
<tr id="overviewTheadTr">
		<td style="width:126px;vertical-align:middle;text-align:center;font-size:18px;" class="h1overview">
			<a onclick="easyRes_sendReq_Overview('<?php echo $moregets-($daysshow);?>','no');resetSet();" title="-<?php echo ($daysshow).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b style="letter-spacing:-4px">&lsaquo; &lsaquo; &lsaquo; &nbsp;&nbsp;</b></a> 
			<a onclick="easyRes_sendReq_Overview('<?php echo $moregets-($daysshow/2);?>','no');resetSet();" title="-<?php echo ($daysshow/2).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&laquo;</b></a> 
			<a onclick="easyRes_sendReq_Overview('<?php echo $moregets-7;?>','no');resetSet();" title="-7 <?php echo __( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&lsaquo;</b></a> 
			<span id="easy-overview-loading"><a onclick="easyRes_sendReq_Overview('0','no');resetSet();" title="<?php echo __( 'Present' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&omicron;</b></a></span>
			<a onclick="easyRes_sendReq_Overview('<?php echo $moregets+7;?>','no');resetSet();" title="+7 <?php echo __( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&rsaquo;</b></a> 
			<a onclick="easyRes_sendReq_Overview('<?php echo $moregets+($daysshow/2);?>','no');resetSet();" title="+<?php echo ($daysshow/2).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&raquo;</b></a> 
			<a onclick="easyRes_sendReq_Overview('<?php echo $moregets+($daysshow);?>','no');resetSet();" title="+<?php echo ($daysshow).' '.__( 'Days' , 'easyReservations' ); ?>" style="cursor:pointer;"><b style="letter-spacing:-4px">&rsaquo; &rsaquo; &rsaquo; &nbsp;&nbsp;</b></a>
		</td>
	<?php
		$co=0;
		while($co < $daysshow){
			$thedaydate=$timesx+(86400*$co);
			if(isset($timpstampanf) AND $thedaydate >= $thedaydate AND $thedaydate <= $timestampend) $backgroundhighlight='backgroundhighlight'; elseif(date("d.m.Y", $thedaydate) ==  date("d.m.Y", time())) $backgroundhighlight='backgroundtoday'; else $backgroundhighlight='backgroundnormal';?>
			<td  class="<?php echo  $backgroundhighlight; ?> overviewDays" style="vertical-align:middle;min-width:23px">
				<?php echo date("j",$thedaydate); ?><br><?php echo substr(date("D",$thedaydate), 0 , 2); ?>
			</td><?php $co++;
		} ?>
	</tr>
</thead>
<tfoot>
	<tr>
		<th colspan="<?php echo $daysshow+1; ?>" class="overviewFooter">
			<span style="vertical-align:middle;" id="resetdiv"></span>
			<span style="float:right;">
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/blue_dot.png'; ?>">&nbsp;<small><?php echo __( 'Past Reservations' , 'easyReservations' ); ?></small> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/green_dot.png'; ?>">&nbsp;<small><?php echo __( 'Present Reservations' , 'easyReservations' ); ?></small> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/red_dot.png'; ?>">&nbsp;<small><?php echo __( 'Future Reservations' , 'easyReservations' ); ?></small><?php if(isset($id)){ ?> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/yellow_dot.png'; ?>">&nbsp;<small><?php echo __( 'Active Reservation' , 'easyReservations' ); ?></small><?php } ?>
			</span>
		</th>
	</tr>
</tfoot>
<tbody>
<?php
	if(isset($roomwhere)){
		$room_args = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'post_status' => 'publish|private', 'numberposts' => -1, 'post__in' => array($roomwhere));
		$roomcategories = get_posts( $room_args );
	} else $roomcategories = easyreservations_get_rooms();

	foreach( $roomcategories as $roomcategorie ){ /* - + - FOREACH ROOM - + - */
		$roomID=$roomcategorie->ID;
		$roomcounty=get_post_meta($roomID, 'roomcount', true);
		$roomfilters = get_post_meta($resourceID, 'reservations_filter', true);
		$rowcount=0;
		
		$room_sql = $wpdb->get_results($wpdb->prepare("SELECT id, name, nights, arrivalDate, roomnumber FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$roomID' AND (arrivalDate BETWEEN '$stardate' AND '$enddate' OR DATE_ADD(arrivalDate, INTERVAL nights DAY) BETWEEN '$stardate' AND '$enddate' OR '$stardate'  BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)) ORDER BY room ASC, roomnumber ASC, arrivalDate ASC"));

		unset($reservations);
		foreach($room_sql as $res){
			if(!empty($res->roomnumber)){
				$reservations[$res->roomnumber][] = array($res);
				$co=0;
			}
		} ?>
		<tr style="background:#EAE8E8">
			<td style="border-bottom: 1px solid <?php echo $ovBorderColor; ?>;border-top: 1px solid <?php echo $ovBorderColor; ?>;"><span>&nbsp;<?php echo __( $roomcategorie->post_title); ?></td>
				<?php
				$co=0;
				while($co < $daysshow){
					if($overview_options['overview_show_avail'] == 1){
						$roomDayPersons=get_post_meta($roomID, 'roomcount', true)-easyreservations_check_avail($roomID, $timesx+($co*86400));
						if($roomDayPersons <= 0) $textcolor='#FF3B38'; else $textcolor='#118D18';
					} else $textcolor = '';
					?><td axis="<?php echo $co+2;?>" style="border-top:1px solid <?php echo $ovBorderColor; ?>;border-bottom:1px solid <?php echo $ovBorderColor; ?>;text-align:center;border-left: 1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;color:<?php echo $textcolor; ?>" ><?php if($overview_options['overview_show_avail'] == 1)  echo '<small>'.$roomDayPersons.'</small>'; ?></small></td><?php
					$co++;
				} ?>
		</tr><?php

		while($roomcounty > $rowcount){  /* - + - FOREACH EXACTLY ROOM - + - */
			$rowcount++;

			if($timesx < time()) $lastbackground='#2A78D8';
			else $lastbackground='#CC3333';
			if($rowcount == $roomcounty) $borderbottom=0;
			else $borderbottom=1; ?>
			<tr id="room<?php echo $rowcount.'-'.$roomID; ?>">
				<td class="roomhead" style="color:#8C8C8C;text-shadow:none;border-style:none;vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>;" onclick="<?php if(isset($edit)){ ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf); ?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationNights*86400)); ?>';setVals2(<?php echo $roomID; ?>,<?php echo $rowcount; ?>);<?php } if(isset($edit) OR isset($approve)){ ?>changer();clickOne(document.getElementById('<?php echo $roomID.'-'.$rowcount.'-'.$numberhighstart; ?>'),'<?php echo date("d.m.Y", $timpstampanf); ?>');clickTwo(document.getElementById('<?php echo $roomID.'-'.$rowcount.'-'.$numberlaststart; ?>'),'<?php echo date("d.m.Y", $timpstampanf+($reservationNights*86400)); ?>');<?php } if(isset($approve)){ ?>document.reservation_approve.roomexactly.selectedIndex=<?php echo $rowcount-1; ?>;<?php } ?>"  nowrap>&nbsp;#<?php if(strlen($rowcount) == 1) echo '0'; echo $rowcount; ?>
			</td><?php

			//$sql_ResInRommAndDate = $wpdb->get_results($wpdb->prepare("SELECT id, name, nights, arrivalDate FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$roomID' AND roomnumber='$rowcount' AND (arrivalDate BETWEEN '$stardate' AND '$enddate' OR DATE_ADD(arrivalDate, INTERVAL nights DAY) BETWEEN '$stardate' AND '$enddate' OR '$stardate'  BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)) ORDER BY arrivalDate ASC"));
			$CoutResNights2=0; $CoutResNights3=0; $CountNumberOfAdd=0; $wasFull=0; $countdifferenz=0; $itIS=0; $cellcount=0;
			
			if(isset($reservations[$rowcount])){
				foreach($reservations[$rowcount] as $reservationsd){
						foreach($reservationsd as $reservation){
							//print_r( $reservation);echo $roomID.'<br><br>';

							$res_id=$reservation->id;
							$res_name=$reservation->name;
							$res_adate=strtotime($reservation->arrivalDate);
							$res_nights=$reservation->nights;
							for($CoutResNights=0; $CoutResNights <= $res_nights; $CoutResNights++){
								if($timesx < $res_adate+(($CoutResNights*86400)+86400) AND $timesy+86400 > $res_adate+($CoutResNights*86400)){
									$daysOccupied[]=date("d.m.Y", $res_adate+(($CoutResNights-1)*86400)+86400+86400);
									$numberOccupied[]=$countdifferenz;
								}
							}
							$reservationarray[]=array( 'name' =>$res_name, 'ID' =>$res_id, 'nights' => $res_nights, 'arDate' => $res_adate );
							$countdifferenz++;
							$wasAroom=1;
						}
					
				}
			}

			$showdatenumber_start=0+$more;
			$showdatenumber_end=$daysshow+$more;

			while($showdatenumber_start < $showdatenumber_end){
				$cellcount++;
				$showdatenumber_start++;
				$oneDay=60*60*24*$showdatenumber_start;
				$dateToday=$timevariable+$oneDay;
				$wasFullTwo=0;
				$borderside=1;
				$onClick=0;

				if($cellcount < 10) $preparedCellcount='0'.$cellcount;
				else $preparedCellcount=$cellcount;

				if($dateToday < time()) $background2="url(".RESERVATIONS_IMAGES_DIR ."/patbg.png) repeat";
				else $background2='';

				if(reservations_check_avail_filter($roomID, $dateToday-86400 ) > 0) $colorbgfree='#FFEDED';
				elseif(date("d.m.Y", $dateToday-86400)==date("d.m.Y", time())) $colorbgfree = '#EDF0FF';
				elseif(date("N", $dateToday-86400)==6 OR date("N", $dateToday-86400)==7) $colorbgfree = '#FFFFEB';
				else $colorbgfree='#FFFFFF';

				if(isset($daysOccupied)){

					if(in_array(date("d.m.Y", $dateToday), $daysOccupied)){

						if($numberOccupied[$CoutResNights3] != $CountNumberOfAdd AND $cellcount != 1) $CountNumberOfAdd++;

						if(isset($daysOccupied[$CoutResNights3+1]) AND isset($numberOccupied[$CoutResNights3-1]) AND $numberOccupied[$CoutResNights3-1] != $daysOccupied[$CoutResNights3] AND $numberOccupied[$CoutResNights3-1] != $numberOccupied[$CoutResNights3]) $wasFullTwo=1;

						if(($CoutResNights2 == 0 AND $cellcount != 1) OR ($wasFullTwo == 1 AND $cellcount != 1)){
							$farbe2="url(".RESERVATIONS_IMAGES_DIR ."/DERSTRING_start.png) right top no-repeat, ".$background2." ".$colorbgfree; 
							$itIS=0;
						} elseif($CoutResNights2 != 0 OR $cellcount == 1 OR (isset($daysOccupied[$CoutResNights3]) AND $lastDay==$daysOccupied[$CoutResNights3])){
							$farbe2="url(".RESERVATIONS_IMAGES_DIR ."/DERSTRING_middle.png) top repeat-x";
							if($cellcount != 1) $borderside=0;
							$itIS++;
						}
						if(isset($daysOccupied[$CoutResNights3+1]) AND $daysOccupied[$CoutResNights3] != $daysOccupied[$CoutResNights3+1] AND $numberOccupied[$CoutResNights3] != $numberOccupied[$CoutResNights3+1]){
							$farbe2="url(".RESERVATIONS_IMAGES_DIR ."/DERSTRING_end.png) left top no-repeat, ".$background2." ".$colorbgfree; 
							$itIS=0;
						}
						if(isset($daysOccupied[$CoutResNights3+1]) AND $daysOccupied[$CoutResNights3] == $daysOccupied[$CoutResNights3+1] AND array_key_exists($CoutResNights3+1, $daysOccupied)){
							$farbe2='url('.RESERVATIONS_IMAGES_DIR .'/DERSTRING_cross.png) left top no-repeat DERZEWEITESTRING';
							$CoutResNights2=0;
							$CoutResNights3++;
							$CountNumberOfAdd++;
							$itIS=0;
							$onClick=1;
						}
						if(!in_array(date("d.m.Y", $dateToday+86400), $daysOccupied)){
							$farbe2="url(".RESERVATIONS_IMAGES_DIR ."/DERSTRING_end.png) left top no-repeat, ".$background2." ".$colorbgfree; 
						}

						$CoutResNights2++;
						$CoutResNights3++;
						$addname=" ";
						$lastDay=$daysOccupied[$CoutResNights3-1];
						if(isset($id) AND $reservationarray[$CountNumberOfAdd]['ID'] == $id){
							$farbe2=str_replace("DERSTRING", "yellow", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#FFE400';
							$addname=' name="activeres"';
							$startdate = $dateToday;
						} elseif($reservationarray[$CountNumberOfAdd]['arDate'] < time() AND $reservationarray[$CountNumberOfAdd]['arDate']+(86400*$reservationarray[$CountNumberOfAdd]['nights']) > time()){
							$farbe2=str_replace("DERSTRING", "green", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#118D18';
						} elseif($reservationarray[$CountNumberOfAdd]['arDate'] > time()){
							$farbe2=str_replace("DERSTRING", "red", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#CC3333';
						} else {
							$farbe2=str_replace("DERSTRING", "blue", $farbe2);
							$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
							$backgrosund='#2A78D8';
						}
						if($reservationarray[$CountNumberOfAdd]['arDate'] < $timesx){
							$daybetween=($timesx-$reservationarray[$CountNumberOfAdd]['arDate'])/86400;
							$minusdays=round($daybetween)-1;
							$nightsproof=$reservationarray[$CountNumberOfAdd]['nights']-$minusdays;
						} elseif($reservationarray[$CountNumberOfAdd]['arDate']+(86400*$reservationarray[$CountNumberOfAdd]['nights']) > $timesy) {
							$daybetween=(($timesy-$reservationarray[$CountNumberOfAdd]['arDate'])/86400)+$reservationarray[$CountNumberOfAdd]['nights'];
							$minusdays=substr(round($daybetween), 1, 10);
							$nightsproof=$reservationarray[$CountNumberOfAdd]['nights']-$minusdays;
						} else {
							$minusdays=0;
							$nightsproof=$reservationarray[$CountNumberOfAdd]['nights'];
						}

						if($itIS==1){
							?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>"<?php echo $addname; ?> colspan="<?php echo $reservationarray[$CountNumberOfAdd]['nights']-1-$minusdays; ?>" class="er_overview_cell" onclick="<?php echo "location.href = 'admin.php?page=reservations&edit=".$reservationarray[$CountNumberOfAdd]['ID']."';"; ?>" style="border-style:none; background: <?php echo $farbe2;?>; color: #FFFFFF;cursor: pointer;text-decoration:none;height:22px;padding:0px;font: normal 11px Arial, sans-serif;vertical-align:middle;text-align:center; overflow:hidden;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left: <?php echo $borderside; ?>px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;"  abbr="<?php echo $farbe2;?>" title="<?php echo $reservationarray[$CountNumberOfAdd]['name']; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,0);"<?php } ?>>
							<?php echo substr($reservationarray[$CountNumberOfAdd]['name'], 0, ($reservationarray[$CountNumberOfAdd]['nights']-1-$minusdays)*3); ?>
							</td><?php
						} elseif($itIS==$nightsproof+1 OR $itIS==$nightsproof OR $itIS==0) {
							?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>"<?php if($borderside == 0){ echo ' class="er_overview_cell" '; echo $addname; }?> <?php if((isset($edit) OR isset($add) OR isset($nonepage)) AND $onClick==0){ ?>onclick="changer();clickTwo(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');clickOne(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');setVals2('<?php echo $roomID; ?>','<?php echo $rowcount; ?>');" <?php } elseif($onClick==1){ ?>onclick="<?php echo "location.href = 'admin.php?page=reservations&edit=".$reservationarray[$CountNumberOfAdd]['ID']."';"; ?>"<?php } ?> style="background: <?php echo $farbe2;?>; color: #FFFFFF;padding:0px; text-align:center;overflow:hidden; text-shadow:none; border-style:none; text-decoration:none; font: normal 11px Arial, sans-serif; vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left:  <?php echo $borderside; ?>px <?php echo $ovBorderStatus.' '.$ovBorderColor; ?>;<?php if($onClick==1){ ?>cursor: pointer;<?php } ?>" abbr="<?php echo $farbe2;?>" axis="<?php echo $cellcount+1; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');"<?php } ?>>
							</td><?php
						}
						$lastbackground=$backgrosund;
						$wasFull=1;
					} else {
						if($wasFull == 1) $CountNumberOfAdd++;

						$CoutResNights2=0;
						?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>" <?php if(isset($edit) OR isset($add) OR isset($nonepage)){ ?>onclick="changer();clickTwo(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');clickOne(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');setVals2('<?php echo $roomID; ?>','<?php echo $rowcount; ?>');"<?php } ?> style=" border-style:none; border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left: 1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;background:<?php echo $background2.' '.$colorbgfree;?>" abbr="<?php echo $background2.' '.$colorbgfree;?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');"<?php } ?> axis="<?php echo $cellcount+1; ?>">
						<?php
						$wasFull=0;
					}
				} else {
					?><td id="<?php echo $roomID.'-'.$rowcount.'-'.$preparedCellcount; ?>"  <?php if(isset($edit) OR isset($add) OR isset($nonepage)){ ?>onclick="changer();clickTwo(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');clickOne(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');setVals2('<?php echo $roomID; ?>','<?php echo $rowcount; ?>');"<?php } ?> style="border-style:none; border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left: 1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;background:<?php echo $background2.' '.$colorbgfree;?>" abbr="<?php echo $background2.' '.$colorbgfree;?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');"<?php } ?> axis="<?php echo $cellcount+1; ?>"></td><?php
				}
			}
			unset($daysOccupied);
			unset($numberOccupied);
			unset($reservationarray);
			echo '</tr>';
		}
	} ?></tbody>
</table>