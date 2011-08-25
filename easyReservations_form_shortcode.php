<?php
function reservations_shortcode($atts) {
?><link href="<?php echo  WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css'; ?>" rel="stylesheet" type="text/css"/>
 <script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery.min.js"></script>
 <script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
 <script type="text/javascript">
function removeElement(parentDiv, childDiv){
		if (childDiv == parentDiv) {
			alert("The parent div cannot be removed.");
		}
		else if (document.getElementById(childDiv)) {     
		var child = document.getElementById(childDiv);
		var parent = document.getElementById(parentDiv);
		parent.removeChild(child);
		}
		else {
			alert("Child div has already been removed or does not exist.");
			return false;
		}
	}
	$(document).ready(function() {
		$("#datepicker").datepicker( { altFormat: 'dd.mm.yyyy', style: 'font-size:1em' });
		$("#datepicker2").datepicker( { altFormat: 'dd.mm.yyyy' });
	});
</script><?php

			global $post;
			global $wpdb;

			if($_POST['action']) { // Check and Set the Form Inputs
					if(isset($_POST['thename'])){
						if ($_POST['thename'] != '' AND strlen($_POST['thename']) < 20 AND strlen($_POST['thename']) > 3){
						$name_form=$_POST['thename'];}
						else { $error.=  '<b>'.__( 'Please enter correct Name' , 'easyReservations' ).'</b><br>'; }
					} else { $name_form=$_POST['firstname'].' '.$_POST['thename']; }

					if ($_POST['from'] != '' AND strtotime($_POST['from'])-time() > '0' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['from']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['from']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['from']))) {
					$arrivaldate_form=date("Y-m-d", strtotime($_POST['from']));
					$arrivaldate_form2=date("d.m.Y", strtotime($_POST['from']));
					$month_form=date("Y-m",strtotime($_POST['from']));}
					else { $error.=  '<b>'.__( 'Please enter correct arrival Date' , 'easyReservations' ).'</b><br>'; }

					if(isset($_POST['to'])){
					if ($_POST['to'] AND strtotime($_POST['to'])-time() > '0' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['to']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['to']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['to']))) {
					$dayofdeparture=strtotime($_POST['from']);
					$daysbetween=strtotime($_POST['to'])-strtotime($_POST['from']);
					$nights_form=$daysbetween/24/60/60; }
					else { $error.=  '<b>'.__( 'Please enter correct destination Date' , 'easyReservations' ).'</b><br>'; }
					} else { $nights_form=$_POST['nights']; }

					$pattern_mail = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,5}$";
					if ($_POST['email'] != '' AND strlen($_POST['email']) < 40 AND strlen($_POST['email']) > 5 AND eregi($pattern_mail, $_POST['email'])){
					$email_form=$_POST['email'];}
					else { $error.=  '<b>'.__( 'Please enter correct eMail' , 'easyReservations' ).'</b><br>'; }

					if ($_POST['phone'] != '' AND strlen($_POST['phone']) < 20 AND strlen($_POST['phone']) > 5){
					$phone_form=$_POST['phone']; }
					else { $error.=  '<b>'.__( 'Please enter correct Phone' , 'easyReservations' ).'</b><br>'; }
					
					if ($_POST['persons'] != '' AND strlen($_POST['persons']) < 4 AND is_numeric($_POST['persons'])){
					$persons_form=$_POST['persons']; }
					else { $error.=  '<b>'.__( 'Please enter correct amount of Persons' , 'easyReservations' ).'</b><br>'; }

					if ($_POST['room'] != '' AND strlen($_POST['room']) < 10 AND is_numeric($_POST['room'])){
					$room_form=$_POST['room']; }
					else { $error.= '<b>'.__( 'Please select a Room', 'easyReservations').'</b><br>'; }

					if ($_POST['address'] != '' ){
					$address_form=$_POST['address']; }
					else { $error.= '<b>'.__( 'Please enter a correct Address' , 'easyReservations' ).'</b><br>'; }

					if ($_POST['message']){
					$message_form=$_POST['message'];}

					if($_POST['specialoffer'] != '0'){
					$specialoffer_form=$_POST['specialoffer'];}
					else $specialoffer_form='0';

					if(!isset($specialoffer_form)) $specialoffer_form=0;

					if($specialoffer_form > 0){
						$numbererrors=reservations_check_availibility($specialoffer_form, $arrivaldate_form2, $nights_form, $room_form);
						if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Special Offer isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
						$numbererrors=reservations_check_availibility($room_form, $arrivaldate_form2, $nights_form, $room_form);
						if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Room isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
					} else {
						$numbererrors=reservations_check_availibility($room_form, $arrivaldate_form2, $nights_form, $room_form);
						if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Room isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
					}
			}

			if($_POST['action'] AND !$error) { //When Check gives no error Insert into Database and send mail
				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(name, phone, email, notes, nights, arrivalDate, dat, room, number, special ) 
				VALUES ('$name_form', '$phone_form', '$email_form', '$message_form*/*$address_form', '$nights_form', '$arrivaldate_form', '$month_form', '$room_form', '$persons_form', '$specialoffer_form' )" ) ); 
				echo __( 'Your Reservation was sent' , 'easyReservations' );

					if($specialoffer_form != "0"){
						$post_id_7 = get_post($specialoffer_form);
						$specialoffer = __($post_id_7->post_title);
					}
					if($specialoffer_form == "0") $specialoffer =  __( 'None' , 'easyReservations' );

					$post_id8 = get_post($room_form);
					$roomtitle = __($post_id8->post_title);

				$emailformation=get_option('reservations_email_to_admin_msg');
				preg_match_all(' /\[.*\]/U', $emailformation, $matchers);
				$mergearrays=array_merge($matchers[0], array());
				$edgeoneremoave=str_replace('[', '', $mergearrays);
				$edgetworemovess=str_replace(']', '', $edgeoneremoave);
					foreach($edgetworemovess as $fieldsx){
						$field=explode(" ", $fieldsx);
						if($field[0]=="thename"){
							$emailformation=preg_replace('['.$fieldsx.']', $name_form, $emailformation);
						}
						if($field[0]=="phone"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$phone_form.'', $emailformation);
						}
						if($field[0]=="email"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$email_form.'', $emailformation);
						}
						if($field[0]=="arrivaldate"){
							$emailformation=preg_replace('['.$fieldsx.']', $arrivaldate_form, $emailformation);
						}
						if($field[0]=="to"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.date("d.m.Y", $dayofdeparture).'', $emailformation);
						}
						if($field[0]=="nights"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$nights_form.'', $emailformation);
						}
						if($field[0]=="address"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$address_form.'', $emailformation);
						}
						if($field[0]=="message"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$message_form.'', $emailformation);
						}
						if($field[0]=="persons"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$persons_form.'', $emailformation);
						}
						if($field[0]=="room"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$roomtitle.'', $emailformation);
						}
						if($field[0]=="offer"){
							$emailformation=preg_replace('['.$fieldsx.']', ''.$specialoffer.'', $emailformation);
						}
					}
				$finalemailedgeremove1=str_replace('[', '', $emailformation);
				$finalemailedgesremoved=str_replace(']', '', $finalemailedgeremove1);
				$makebrtobreak=str_replace('<br>', "\n", $finalemailedgesremoved);

				$msg=$makebrtobreak;
				$reservation_support_mail = get_option("reservations_support_mail");
				$subj=get_option("reservations_email_to_admin_subj");
				$eol="\n";
				$headers = "From: ".get_bloginfo('name')." <".$reservation_support_mail.">".$eol;
				$headers .= "Message-ID: <".time()."-".$reservation_support_mail.">".$eol;

				wp_mail($reservation_support_mail,$subj,$msg,$headers);
			}

			$room_category = get_option('reservations_room_category');
			$special_offer_cat = get_option("reservations_special_offer_cat");

			$comefrom=wp_get_referer(); //Get Refferer for Offer box Style
			$parsedURL = parse_url ($comefrom);
			$splitPath = explode ('/', end($parsedURL));
			$splitPathTry2 = preg_split ('/\//', end($parsedURL), 0, PREG_SPLIT_NO_EMPTY); 
			$buildarray = array($splitPathTry2);
			$getlast=end($buildarray);
			$explodeID=preg_split ('/p=/', $splitPathTry2[0], 0, PREG_SPLIT_NO_EMPTY); 

			$args=array(
				'name' => end($getlast),
				'post_type' => 'post',
				'post_status' => 'publish',
				'showposts' => 1,
			);

			$my_post = get_posts($args);
			$theIDs = $my_post[0]->ID;
			if(get_option('permalink_structure')==''){ $theIDs=$explodeID[0]; }
			if(strpos(get_option('permalink_structure'),"%post_id%")!==false){ $theIDs=end($getlast); }
			$cates=get_the_category($theIDs);
			$cate=$cates[0]->term_id;
			$fromto = get_post_meta($theIDs, 'fromto');

				$posts = "SELECT post_title, ID FROM ".$wpdb->prefix ."posts WHERE post_type='post' AND post_status='publish'"; //Get all Rooms and Special Offers
				$results = $wpdb->get_results($posts);

					foreach($results as $result){
						$id=$result->ID;
						$name=$result->post_title;
						$categ=get_the_category($id);
						$care=$categ[0]->term_id;
						if($care==$room_category) { $room.='<option value="'.$id.'">'.__($name).'</option>'; }
						if($care==$special_offer_cat) { $special_option.='<option value="'.$id.'">'.__($name).'</option>'; }
					}

					if($cate==$special_offer_cat){
						$image_id = get_post_thumbnail_id($theIDs);  
						$image_url = wp_get_attachment_image_src($image_id,'large');  
						$image_url = $image_url[0];  
						$desc = get_post_meta($theIDs, 'short', true);
							if(strlen(__($desc)) >= 45) { $desc = substr(__($desc),0,45)."..."; }
						$special_offer_promt.='<div id="parent"><div id="child" align="center">';
						$special_offer_promt.='<div align="left" style="width: 324px; border: #ffdc88 solid 1px; vertical-align: middle; background: #fffdeb; padding: 5px 5px 5px 5px; font:12px/18px Arial,serif; border-collapse: collapse;">';
							if(get_post_meta($theIDs, 'percent', true)!=""){ $special_offer_promt.='<span style="height: 20px; border: 0px; padding: 1px 5px 0 5px; margin: 32px 0 0 -50px; font:14px/18px Arial,serif; font-weight: bold; color: #fff; text-align: right; background: #ba0e01; position: absolute;">'.__(get_post_meta($theIDs, 'percent', true)).'</span>'; }
						$special_offer_promt.='<img src="'.$image_url.'" style="height:55px; width:55px; border:0px; margin:0px 10px 0px 0px; padding:0px;" class="alignleft"> '.__( 'You\'ve choosen' , 'easyReservations' ).': <b>'.__(get_the_title($theIDs)).'</b><img style="float: right;" src="'.WP_PLUGIN_URL.'/easyreservations/images/close.png" onClick="'."removeElement('parent','child')".'"><br>'.__( 'Available' , 'easyReservations' ).': '.__($fromto[0]).'<br>'.__($desc).' <input type="hidden"  name="specialoffer" value="'.__($theIDs).'" /></div>';
						$special_offer_promt.='</div></div>';
					} 

			$special_offer_select='<select name="specialoffer"><option value="0" select="selected">'. __( 'None' , 'easyReservations' ).'</option>'.$special_option.'</select>';
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// START OF AVAIL CALENDAR/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			?><style type="text/css" media="screen">
.calendarbox{
background:-moz-linear-gradient( 50% 0%, #32373C 0%, #50555A 5%, #64696E 30%, #7D8287 100% );
width:43%;
border:1px solid #fff;-moz-border-radius:9px;border-radius:9px;	
padding:5px;
}
.calendartable {
border:1px solid #fff;
border-right:0px;
}
.calendarcell {
color: #fff;
text-align:center;
vertical-align:top;
border-right: 1px solid #fff;
height: 50px;
text-align: center;
vertical-align: middle;
width: 65px;
}
.calendarheadercell {
text-align:center;
border-right: 1px solid #fff; 
background: #eee;
}
.callenderlast {
background: #cccccc; 
color: #666666;
}
.callenderp {
border-collapse: collapse;
text-align: center;
}
</style><?php


			$diff=1;
			$setet=0;
			$timenow=time()+$_GET['plus']+(86400*55); //390 good motnh
			$yearnow=date("Y", $timenow);
			$monthnow=date("m", $timenow);
			$num = cal_days_in_month(CAL_GREGORIAN, $monthnow, $yearnow); // 31
			$num2 = cal_days_in_month(CAL_GREGORIAN, $monthnow-1, $yearnow); // 31
			$plusvariable=($num2*86400)+$_GET['plus'];
			echo $monthnow.'<table id="clander" class="calendartable" cellpadding="0"><thead><tr><th class="calendarheadercell">mo</th><th class="calendarheadercell">di</th><th class="calendarheadercell">mi</th><th class="calendarheadercell">do</th><th class="calendarheadercell">fr</th><th class="calendarheadercell">sa</th><th class="calendarheadercell">so</th></tr></thead><tbody style="text-align:center">';
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
							echo '<td style="text-align:center">'.($num2-$dayindex+2+$setet).'</td>'; $setet++; 
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
				/* FÜR MEHERER RÄUME!!!!!!!!!!!!!!!!!
				$countrooms=0;
				$argsx = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC');
				$roomcategories = get_posts( $argsx );
				foreach( $roomcategories as $roomcategorie ){
					$countrooms++;
					
					if($countrooms <= 3 AND reservations_check_availibility_for_room($roomcategorie->ID, date("d.m.Y", $dateofeachday)) == 0){
						$calendarbgimage.='<p style="margin: -17px 0 -17px 0;position:relative;top:-17px; left:14px"><img src="'.WP_PLUGIN_URL.'/easyreservations/images/room'.$countrooms.'.png"></p>';
					}
				}
				*/

				if(reservations_check_availibility_for_room(4, date("d.m.Y", $dateofeachday)) != 0){
					$backgroundtd="#cc0000";
				} else {
					$backgroundtd="#006600";
				}

				echo '<td class="calendarcell"  style="text-align:center; background:'.$backgroundtd.'">'.$diff++.' </td>'; $setet++;
				if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28){ echo '</tr>'; }
				$calendarbgimage="";
			}

			if($diff-1==$num AND $setet/7 != $rowcount){
				$calc=($rowcount*7)-($setet+1);
				for($countits=0; $countits < $calc+1; $countits++){
					echo '<td class="calendarcell callenderlast"><p class="callenderp">'.($countits+1).'</p></td>';
				}
			}

			echo '</tr></tbody></table>';
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			if(isset($atts[0])) $formtest=get_option('reservations_form_'.$atts[0].'');
			else $formtest=get_option('reservations_form');

			preg_match_all(' /\[.*\]/U', $formtest, $matches); 
			$mergearray=array_merge($matches[0], array());
			$edgeoneremove=str_replace('[', '', $mergearray);
			$edgetworemoves=str_replace(']', '', $edgeoneremove);
				foreach($edgetworemoves as $fields){
					$field=explode(" ", $fields);
					if($field[0]=="date-from"){
						$formtest=preg_replace('['.$fields.']', '<input id="datepicker" type="text" name="from">', $formtest);
					}
					if($field[0]=="date-to"){
						$formtest=preg_replace('['.$fields.']', '<input id="datepicker2"  type="text" name="to">', $formtest);
					}
					if($field[0]=="nights"){
						if(isset($field[1])) $number=$field[1]; else $number=31;
						for($count = 1; $count <= $number; $count++){  $nights_options.='<option value="'.$count.'">'.$count.'</option>'; }
						$formtest=preg_replace('['.$fields.']', '<select name="nights">'.$nights_options.'</select>', $formtest);
					}
					if($field[0]=="persons"){
						if($field[1]=="select"){
							if(isset($field[2])) $number=$field[2];
							for($count = 1; $count <= $number; $count++){  $person_options.='<option value="'.$count.'">'.$count.'</option>'; }
							$formtest=preg_replace('['.$fields.']', '<select name="persons">'.$person_options.'</select>', $formtest);
						}
						if($field[1]=="text"){
							if(isset($field[2])) $number=$field[2]; else $number=2;
							for($count = 1; $count <= $number; $count++){  $person_options.='<option value="'.$count.'">'.$count.'</option>'; }
							$formtest=preg_replace('['.$fields.']', '<input name="persons" type="text" size="'.$number.'px">', $formtest);
						}
					}
					if($field[0]=="thename"){
						$formtest=preg_replace('['.$fields.']', '<input type="text" name="thename" >', $formtest);
					}
					if($field[0]=="firstname"){
						$formtest=preg_replace('['.$fields.']', '<input type="text" name="firstname" >', $formtest);
					}
					if($field[0]=="secondname"){
						$formtest=preg_replace('['.$fields.']', '<input type="text" name="secondname" >', $formtest);
					}
					if($field[0]=="error"){
						$formtest=preg_replace('['.$fields.']', ''.$error.'', $formtest);
					}
					if($field[0]=="phone"){
						$formtest=preg_replace('['.$fields.']', '<input type="text" name="phone" >', $formtest);
					}
					if($field[0]=="email"){
						$formtest=preg_replace('['.$fields.']', '<input type="text" name="email" >', $formtest);
					}
					if($field[0]=="address"){
						$formtest=preg_replace('['.$fields.']', '<textarea type="text" name="address"  style="width:200px;"></textarea>', $formtest);
					}
					if($field[0]=="message"){
						$formtest=preg_replace('['.$fields.']', '<textarea type="text" name="message" style="width:200px; height: 100px;"></textarea>', $formtest);
					}
					if($field[0]=="hidden"){
						$formtest=preg_replace('['.$fields.']', '<input type="hidden" name="room" value="'.$field[1].'">', $formtest);
					}
					if($field[0]=="room"){
						$formtest=preg_replace('['.$fields.']', '<select name="room">'.$room.'</select>', $formtest);
					}
					if($field[0]=="offer"){
						if($field[1]=="select"){
							$formtest=preg_replace('['.$fields.']', ''.$special_offer_select.'', $formtest);
						}
						if($field[1]=="box"){
							$formtest=preg_replace('['.$fields.']', ''.$special_offer_promt.'', $formtest);
						}
					}
					if($field[0]=="submit"){
						if(isset($field[1])) $valuesubmit=$field[1]; else $valuesubmit='Submit';
						$formtest=preg_replace('['.$fields.']', '<input type="submit" value="'.$valuesubmit.'">', $formtest);
					}
				}
			$finalformedgeremove1=str_replace('[', '', $formtest);
			$finalformedgesremoved=str_replace(']', '', $finalformedgeremove1);
			$finalform='<form method="post"><input type="hidden" name="action" value="newreservation">'.$finalformedgesremoved.'</form>';
			return $finalform;
    }	?>