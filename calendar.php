<?php require('../../../wp-blog-header.php'); ?>
<style type="text/css" media="screen">
	.calendarbox{
		background:-moz-linear-gradient( 50% 0%, #32373C 0%, #50555A 5%, #64696E 30%, #7D8287 100% );
		width:43%;
		border:1px solid #fff;-moz-border-radius:9px;border-radius:9px;	
		padding:5px;
	}
	.calendartable {
		border:1px solid #000 !important;
		width: 300px !important;
		
	}
	.calendarcell {
		color: #fff;
		text-align:center;
		vertical-align:top;
		border-right: 1px solid #fff;
		border-bottom: 1px solid #fff;
		border-top:none !important;
		height: 20px;
		text-align: center !important;
		vertical-align: middle;
		width: 18px;
		padding: 4px !important;
		
	}
	tr .calendarcell:last-of-type{
		border-right:0;
	}
	tr .calendarheadercell:last-of-type{
		border-right:0;
	}
	tr:last-of-type td {
		border-bottom:0;
	}
	.calendarheadercell {
		text-align:center;
		border-bottom: 1px solid #fff; 
		background: #000;
		color:#fff !important;
	}
	.callenderlast {
		background: #cccccc; 
		color: #666666;
	}
	.callenderp {
		border-collapse: collapse;
		text-align: center;
	}
	.monthSelectPrev {
		text-align:left;
		background: #575757;
	}
	.monthSelectNext {
		text-align:right;
		background: #575757;
	}
	.calendarheadline {
		font-family: "Arial";
		font-size: 12px !important;
		text-align:center;
		background: #575757;
		color:#fff !important;
	}
	.monthSelector {
		margin: 0px 4px;
		background:#898989;
		color:#000;
		font-weight:bold;
		text-decoration:none;
		padding:3px 5px;
		-moz-border-radius: 2px;
		-khtml-border-top-radius: 2px;
		-webkit-border-top-radius: 2px;
		border-top-radius: 2px;
	}
	.monthSelector:hover {
		cursor:pointer;
		background:#3A3A3A;
		color:#fff;
		text-decoration:none;
	}
</style>
<?php
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
	echo '<table id="clander" class="calendartable" cellpadding="0"><thead><tr><th class="monthSelectPrev"><a class="monthSelector" onClick="document.formular.date.value='.($_POST['date']-1).';sndReq()"><</a></th><th colspan="5" class="calendarheadline">'.$monthString.' '.date("Y", $timenow).'</th><th class="monthSelectNext"><a class="monthSelector" onClick="document.formular.date.value='.($_POST['date']+1).';sndReq()">></a></th></tr>';
	echo '<tr><th class="calendarheadercell">'.__( 'Mo' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Tu' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Wh' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Th' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Fr' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Sa' , 'easyReservations' ).'</th><th class="calendarheadercell">'.__( 'Su' , 'easyReservations' ).'</th></tr></thead><tbody style="text-align:center">';
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
		/* FÜR MEHERER RÄUME
		$countrooms=0;
		$argsx = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
		$roomcategories = get_posts( $argsx );
		foreach( $roomcategories as $roomcategorie ){
			$countrooms++;
			
			if($countrooms <= 3 AND reservations_check_availibility_for_room($roomcategorie->ID, date("d.m.Y", $dateofeachday)) == 0){
				$calendarbgimage.='<p style="margin: -17px 0 -17px 0;position:relative;top:-17px; left:14px"><img src="'.RESERVATIONS_IMAGES_DIR.'/room'.$countrooms.'.png"></p>';
			}
		}
		*/
		if(reservations_check_availibility_for_room($_POST['room'], date("d.m.Y", $dateofeachday)) > get_post_meta($_POST['room'], 'roomcount', true)){
			$backgroundtd="#cc0001";
		} elseif(reservations_check_availibility_for_room($_POST['room'], date("d.m.Y", $dateofeachday)) > 0){
			$backgroundtd="#ffcb00";
		} else {
			$backgroundtd="#006600";
		}

		echo '<td class="calendarcell"  style="text-align:center; background:'.$backgroundtd.'">'.$diff++.' </td>'; $setet++;
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