<?php require('../../../../wp-blog-header.php'); 

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

?><link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/calendar/style_1.css" rel="stylesheet" type="text/css"/><?php

	if(isset($_POST['type']) AND $_POST['type'] == "widget"){
		$onClick = "easyRes_sendReq_widget_Calendar();";
		$formular = "widget_formular";
	} else {
		$onClick = "easyRes_sendReq_Calendar();";
		$formular = "formular";
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
	echo '<table class="calendartable" cellpadding="0" style="width:'.$width.'px;height:'.$height.'px;margin-left:auto;"><thead><tr style="height:32px;"><th class="monthSelectPrev"><a class="monthSelector" onClick="document.'.$formular.'.date.value='.($_POST['date']-1).';'.$onClick.'"><</a></th><th colspan="5" class="calendarheadline">'.$monthString.' '.date("Y", $timenow).'</th><th class="monthSelectNext"><a class="monthSelector" onClick="document.'.$formular.'.date.value='.($_POST['date']+1).';'.$onClick.'">></a></th></tr>';
	echo '<tr style="height:32px;"><th class="calendarheadercell">'.__( 'Mo' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Tu' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'We' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Th' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Fr' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Sa' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Su' , 'easyReservations' ).'</th></tr></thead><tbody style="text-align:center">';
	while($diff <= $num){

		$dateofeachday=strtotime($diff.'.'.$monthnow.'.'.$yearnow);
		$dayindex=date("N", $dateofeachday);
		if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28 OR $setet==35){ echo '<tr style="text-align:center">'; $rowcount++; }
		if($setet==0 AND $diff==1 AND $dayindex != "1"){ 
			echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2).'</td>'; $setet++; 
			if($setet==1 AND $diff==1 AND $dayindex != "2"){ 
				echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2+$setet).'</td>'; $setet++; 
				if($setet==2 AND $diff==1 AND $dayindex != "3"){ 
				echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2+$setet).'</td>'; $setet++;
					if($setet==3 AND $diff==1 AND $dayindex != "4"){ 
					echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2+$setet).'</td>'; $setet++; 
						if($setet==4 AND $diff==1 AND $dayindex != "5"){ 
						echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2+$setet).'</td>'; $setet++;
							if($setet==5 AND $diff==1 AND $dayindex != "6"){
							echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2+$setet).'</td>'; $setet++;
								if($setet==6 AND $diff==1 AND $dayindex != "7"){
								echo '<td class="calendarcell callenderlast">'.($num2-$dayindex+2+$setet).'</td>'; $setet++; 
								}
							}
						}
					}
				}
			}
		}

		if($explodeSize[2] == 1){
			$Array = array( 'arrivalDate' => date("d.m.Y", $dateofeachday), 'nights' => 1, 'reservated' => date("d.m.Y", time()), 'room' => $_POST['room'], 'special' => $_POST['offer'], 'persons' => 1, 'email' => 'test@test.deve', 'price' => '', 'customp' => '' );
			$obj = (object) $Array;
			$resArray = array($obj);
			$thePrice = easyreservations_price_calculation('', $resArray);
			$price = reservations_format_money($thePrice['price']);	
		} else $price = '';

		/* FÜR MEHERER RÄUME
		$countrooms=0;
		$argsx = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
		$roomcategories = get_posts( $argsx );
		foreach( $roomcategories as $roomcategorie ){
			$countrooms++;
			if($countrooms <= 3 AND reservations_check_availibility_for_room($roomcategorie->ID, date("d.m.Y", $dateofeachday)) == 0){
				$calendarbgimage.='<p style="margin: -17px 0 -17px 0;position:relative;top:-17px; left:14px"><img src="'.RESERVATIONS_IMAGES_DIR.'/room'.$countrooms.'.png"></p>';
			}
		}*/

		if(date("d.m.Y", $dateofeachday) == date("d.m.Y", time())) $todayClass=" today";
		else $todayClass="";

		if(reservations_check_availibility_for_room($_POST['room'], date("d.m.Y", $dateofeachday)) >= get_post_meta($_POST['room'], 'roomcount', true)){
			$backgroundtd=" calendarFull";
		} elseif(reservations_check_availibility_for_room($_POST['room'], date("d.m.Y", $dateofeachday)) > 0){
			$backgroundtd=" calendarOccu";
		} else {
			$backgroundtd=" calendarEmpty";
		}

		echo '<td class="calendarcell'.$todayClass.$backgroundtd.'"  style="text-align:center;">'.$diff++.'<br>'.$price.'</td>'; $setet++;
		if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28){ echo '</tr>'; }
	}

	if($diff-1==$num AND $setet/7 != $rowcount){
		$calc=($rowcount*7)-($setet+1);
		for($countits=0; $countits < $calc+1; $countits++){
			echo '<td class="calendarcell callenderlast">'.($countits+1).'</td>';
		}
	}

	echo '</tr></tbody></table>';
?>