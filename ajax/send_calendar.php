<?php 

require('../../../../../wp-blog-header.php'); 

$explodeSize = explode(",", $_POST['size']);

if(isset($explodeSize[0]) AND $explodeSize[0] != '') $width = $explodeSize[0];
if(isset($explodeSize[1]) AND $explodeSize[1] != '') $height = $explodeSize[1];

if(isset($width) AND !isset($height) AND !empty($width)){
	$height=$width/100*86.66;
}
if(isset($height) AND !isset($width) AND !empty($height)){
	$width=$height/100*115.3;
}
if(!isset($width) AND !isset($height)){
	$width=300;
	$height=280;
}
if($width == 0 OR empty($width)){
	$width=300;
}
if($height == 0 OR empty($height)){
	$height=280;
}

$headerheigth = $height/100*23.07;
$cellwidth = $width/100*14;
$cellheight = ($height-$headerheigth)/100*16.5;

?><link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/calendar/style_<?php if(isset($explodeSize[3]) AND !empty($explodeSize[3])) echo $explodeSize[3]; else echo '2';?>.css" rel="stylesheet" type="text/css"/><?php

	if(isset($_POST['type']) AND $_POST['type'] == "widget"){
		$onClick = "easyRes_sendReq_widget_Calendar();";
		$formular = "widget_formular";
	} else {
		$onClick = "easyRes_sendReq_Calendar();";
		$formular = "CalendarFormular";
	}

	$diff=1;
	$setet=0;
	$timenow=time()+($_POST['date']*86400*30); //390 good motnh
	$yearnow=date("Y", $timenow);
	$monthnow=date("m", $timenow);
	$monthString=date("F", $timenow);
	$num = cal_days_in_month(CAL_GREGORIAN, $monthnow, $yearnow); // 31

	if($monthnow-1 <= 0){
		$monthnowFix=13;
		$yearnowFix=$yearnow-1;
	} else {
		$monthnowFix=$monthnow;
		$yearnowFix=$yearnow;
	}

	$num2 = cal_days_in_month(CAL_GREGORIAN, $monthnowFix-1, $yearnowFix); // 31
	echo '<table class="calendar-table" cellpadding="0" style="width:'.$width.'px;height:'.$height.'px;margin-left:auto;"><thead><tr class="calendarheader"><th class="calendar-header-month-prev"><a class="calendar-month-button" onClick="document.'.$formular.'.date.value='.($_POST['date']-1).';'.$onClick.'">&lt;</a></th><th colspan="5" class="calendar-header-show-month">'.$monthString.' '.date("Y", $timenow).'</th><th class="calendar-header-month-next"><a class="calendar-month-button" onClick="document.'.$formular.'.date.value='.($_POST['date']+1).';'.$onClick.'">&gt;</a></th></tr>';
	echo '<tr><th class="calendar-header-cell">'.__( 'Mo' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Tu' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'We' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Th' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Fr' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Sa' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Su' , 'easyReservations' ).'</th></tr></thead><tbody style="text-align:center">';
	$rowcount=0;
	while($diff <= $num){

		$dateofeachday=strtotime($diff.'.'.$monthnow.'.'.$yearnow);
		$dayindex=date("N", $dateofeachday);
		if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28 OR $setet==35){ echo '<tr style="text-align:center">'; $rowcount++; }
		if($setet==0 AND $diff==1 AND $dayindex != "1"){ 
			echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2).'</span></td>'; $setet++; 
			if($setet==1 AND $diff==1 AND $dayindex != "2"){ 
				echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
				if($setet==2 AND $diff==1 AND $dayindex != "3"){ 
				echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
					if($setet==3 AND $diff==1 AND $dayindex != "4"){ 
					echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
						if($setet==4 AND $diff==1 AND $dayindex != "5"){ 
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
							if($setet==5 AND $diff==1 AND $dayindex != "6"){
							echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
								if($setet==6 AND $diff==1 AND $dayindex != "7"){
								echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
								}
							}
						}
					}
				}
			}
		}

		if($explodeSize[2] == 1){
			if(isset($_POST['persons'])) $persons = $_POST['persons']; else $persons = 1;
			if(isset($_POST['childs'])) $childs = $_POST['childs']; else $childs = 0;
			if(isset($_POST['reservated'])) $reservated = $_POST['reservated']*86400; else $reservated = 0;
			
			$Array = array( 'arrivalDate' => date("d.m.Y", $dateofeachday), 'nights' => 1, 'reservated' => date("d.m.Y", $dateofeachday-$reservated), 'room' => $_POST['room'], 'special' => $_POST['offer'], 'number' => $persons, 'childs' => $childs, 'email' => 'test@test.deve', 'price' => '', 'customp' => '' );
			$obj = (object) $Array;
			$resArray = array($obj);
			$thePrice = easyreservations_price_calculation('', $resArray);
			$price = reservations_format_money(str_replace(",",".",$thePrice['price']));
			$price = '<b style="display:inline-block;margin-top:-3px;width:99%;">'.$price.'</b>';
		} else $price = '';

		/* FÜR MEHERER RÄUME
		$countrooms=0;
		$argsx = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
		$roomcategories = get_posts( $argsx );
		foreach( $roomcategories as $roomcategorie ){
			$countrooms++;
			if($countrooms <= 3 AND easyreservations_check_avail($roomcategorie->ID, $dateofeachday) == 0){
				$calendarbgimage.='<p style="margin: -17px 0 -17px 0;position:relative;top:-17px; left:14px"><img src="'.RESERVATIONS_IMAGES_DIR.'/room'.$countrooms.'.png"></p>';
			}
		}*/

		if(date("d.m.Y", $dateofeachday) == date("d.m.Y", time())) $todayClass=" today";
		else $todayClass="";
		
		$avail = easyreservations_check_avail($_POST['room'], $dateofeachday);

		if($avail >= get_post_meta($_POST['room'], 'roomcount', true)){
			$backgroundtd=" calendar-cell-full";
		} elseif($avail > 0){
			$backgroundtd=" calendar-cell-occupied";
		} else {
			$backgroundtd=" calendar-cell-empty";
		}

		echo '<td class="calendar-cell'.$todayClass.$backgroundtd.'">'.$diff++.''.$price.'</td>'; $setet++;
		if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28){ echo '</tr>'; }
	}

	if($diff-1==$num AND $setet/7 != $rowcount){
		$calc=($rowcount*7)-($setet+1);
		for($countits=0; $countits < $calc+1; $countits++){
			if($countits==0) $fix = " calendar-cell-lastfixer"; else $fix ="";
			echo '<td class="calendar-cell calendar-cell-last'.$fix.'"><span>'.($countits+1).'</span></td>';
		}
	}

	echo '</tr></tbody></table>';
?>