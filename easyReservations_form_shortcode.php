<?php
function reservations_form_shortcode($atts) {
?><link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/jquery-ui.css" rel="stylesheet" type="text/css"/>
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
		$("#datepicker").datepicker( { dateFormat: 'dd.mm.yy', style: 'font-size:1em' });
		$("#datepicker2").datepicker( { dateFormat: 'dd.mm.yy' });
	});
</script><style>
.warning {
	background: #fff6bf url(<?php echo RESERVATIONS_IMAGES_DIR;?>/exclamation.png) center no-repeat;
	background-position: 15px 50%; /* x-pos y-pos */
	text-align: left;
	padding: 5px 20px 5px 45px;
	border-top: 2px solid #ffd324;
	border-bottom: 2px solid #ffd324;
	font-family :Arial;
	width:250px;
	}
</style><?php

			global $post;
			global $wpdb;

			if(isset($_POST['action'])) { // Check and Set the Form Inputs
					if(isset($_POST['thename'])){
						if ($_POST['thename'] != '' AND strlen($_POST['thename']) < 20 AND strlen($_POST['thename']) > 3){
						$name_form=$_POST['thename'];}
						else { $error.=  '<b>'.__( 'Please enter correct Name' , 'easyReservations' ).'</b><br>'; }
					}

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

					if ($_POST['persons'] != '' AND strlen($_POST['persons']) < 4 AND is_numeric($_POST['persons'])){
					$persons_form=$_POST['persons']; }
					else { $error.=  '<b>'.__( 'Please enter correct amount of Persons' , 'easyReservations' ).'</b><br>'; }

					if($_POST['room'] != '' AND strlen($_POST['room']) < 10 AND is_numeric($_POST['room'])){
					$room_form=$_POST['room']; }
					else { $error.= '<b>'.__( 'Please select a Room', 'easyReservations').'</b><br>'; }

					if(isset($_POST['message'])) $message_form=$_POST['message'];

					if($_POST['specialoffer'] != '0'){
					$specialoffer_form=$_POST['specialoffer'];}
					else $specialoffer_form='0';

					if(!isset($specialoffer_form)) $specialoffer_form=0;
					
					if(isset($atts[0])) $formtest=get_option('reservations_form_'.$atts[0].'');
					else $formtest=get_option('reservations_form');

					preg_match_all(' /\[.*\]/U', $formtest, $matches); 
					$mergearray=array_merge($matches[0], array());
					$edgeoneremove=str_replace('[', '', $mergearray);
					$edgetworemoves=str_replace(']', '', $edgeoneremove);

						foreach($edgetworemoves as $fields){
							$field=explode(" ", $fields);
							if($field[0]=="custom"){
								if($_POST[$field[2]]){
									$custom_form.= $field[2].'&:&'.$_POST[$field[2]].'&;&';
								}
								//else { $error.= '<b>'.__( 'Please fill out ', 'easyReservations').$field[2].'</b><br>'; }
							}
							if($field[0]=="price"){
								if($_POST[$field[2]]){
									$custom_price.= $field[2].'&:&'.$_POST[$field[2]].'&;&';
								}
								//else { $error.= '<b>'.__( 'Please fill out ', 'easyReservations').$field[2].'</b><br>'; }
							}
						}

					if($specialoffer_form > 0){
						$numbererrors=reservations_check_availibility($specialoffer_form, $arrivaldate_form2, $nights_form, $room_form);
						if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Special Offer isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
						$numbererrors=reservations_check_availibility($room_form, $arrivaldate_form2, $nights_form, $room_form);
						if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Room isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
					} else {
						$numbererrors=reservations_check_availibility($room_form, $arrivaldate_form2, $nights_form, $room_form);
						if($numbererrors > 0){ $error.= '<b>('.$numbererrors.'x) '.__( 'Room isn\'t available at' , 'easyReservations' ).' '.$arrivaldate_form2.'</b><br>'; }
					}
					if($error!='') $error='<p class="warning">'.$error.'</p>';
			}

			if(isset($_POST['action']) AND !$error) { //When Check gives no error Insert into Database and send mail

				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(name,  email, notes, nights, arrivalDate, dat, room, number, special, custom, customp, reservated ) 
				VALUES ('$name_form', '$email_form', '$message_form', '$nights_form', '$arrivaldate_form', '$month_form', '$room_form', '$persons_form', '$specialoffer_form', '$custom_form', '$custom_price', NOW() )" ) );

				echo '<b>'.__( 'Your Reservation was sent' , 'easyReservations' ).'</b><br><br>';

				$newID = mysql_insert_id();
				$thePrice = easyreservations_get_price($newID);

				if($specialoffer_form != "0"){
					$post_id_7 = get_post($specialoffer_form);
					$specialoffer = __($post_id_7->post_title);
				}
				if($specialoffer_form == "0") $specialoffer =  __( 'None' , 'easyReservations' );

				$post_id8 = get_post($room_form);
				$roomtitle = __($post_id8->post_title);

				$emailformation=get_option('reservations_email_to_admin_msg');
				$subj=get_option("reservations_email_to_admin_subj");
				$emailformation2=get_option('reservations_email_to_user_msg');
				$subj2=get_option("reservations_email_to_user_subj");
				$reservation_support_mail = get_option("reservations_support_mail");

				easyreservations_send_mail($emailformation, $reservation_support_mail, $subj, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form);
				easyreservations_send_mail($emailformation2, $reservation_support_mail, $subj2, '', $newID, strtotime($arrivaldate_form), strtotime($_POST['to']), $name_form, $email_form, $nights_form, $persons_form, $roomtitle, $specialoffer, $custom_form, $thePrice, $message_form);
			}

			$room_category = get_option('reservations_room_category');
			$special_offer_cat = get_option("reservations_special_offer_cat");


			if(isset($atts[0])) $formtest=get_option('reservations_form_'.$atts[0].'');
			else $formtest=stripslashes (get_option("reservations_form"));

			preg_match_all(' /\[.*\]/U', $formtest, $matches); 
			$mergearray=array_merge($matches[0], array());
			$edgeoneremove=str_replace('[', '', $mergearray);
			$edgetworemoves=str_replace(']', '', $edgeoneremove);
				foreach($edgetworemoves as $fields){
					$field=array_values(array_filter(preg_split('/("[^"]*"|\'[^\']*\'|\s+)/', str_replace("\\", "", $fields), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE), 'trim'));
					if($field[0]=="date-from"){
						$formtest=preg_replace('/'.$fields.'/', '<input id="datepicker" type="text" name="from">', $formtest);
					}
					elseif($field[0]=="date-to"){
						$formtest=preg_replace('/'.$fields.'/', '<input id="datepicker2"  type="text" name="to">', $formtest);
					}
					elseif($field[0]=="nights"){
						if(isset($field[1])) $number=$field[1]; else $number=31;
						for($count = 1; $count <= $number; $count++){  $nights_options.='<option value="'.$count.'">'.$count.'</option>'; }
						$formtest=preg_replace('/'.$fields.'/', '<select name="nights">'.$nights_options.'</select>', $formtest);
					}
					elseif($field[0]=="persons"){
						if($field[1]=="Select"){
							if(isset($field[2])) $number=$field[2];
							$person_options='';
							for($count = 1; $count <= $number; $count++){  $person_options.='<option value="'.$count.'">'.$count.'</option>'; }
							$formtest=preg_replace('/'.$fields.'/', '<select name="persons">'.$person_options.'</select>', $formtest);
						}
						elseif($field[1]=="text"){
							$formtest=preg_replace('/'.$fields.'/', '<input name="persons" type="text" size="70px">', $formtest);
						}
					}
					elseif($field[0]=="thename"){
						$formtest=preg_replace('/'.$fields.'/', '<input type="text" name="thename" value="'.$name_form.'">', $formtest);
					}
					elseif($field[0]=="error"){
						if(isset($error)) $form_error=$error;
						else $form_error = '';
						$formtest=preg_replace('/'.$fields.'/', $form_error, $formtest);
					}
					elseif($field[0]=="email"){
						$formtest=preg_replace('/'.$fields.'/', '<input type="text" name="email" >', $formtest);
					}
					elseif($field[0]=="message"){
						$formtest=preg_replace('/'.$fields.'/', '<textarea type="text" name="message" style="width:200px; height: 100px;"></textarea>', $formtest);
					}
					elseif($field[0]=="hidden"){
						if($field[1]=="room"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="hidden" name="room" value="'.$field[2].'">', $formtest);
						} 
						elseif($field[1]=="offer"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="hidden" name="specialoffer" value="'.$field[2].'">', $formtest);
						}
						elseif($field[1]=="from"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="hidden" name="from" value="'.$field[2].'">', $formtest);
						}
						elseif($field[1]=="to"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="hidden" name="to" value="'.$field[2].'">', $formtest);
						}
						elseif($field[1]=="persons"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="hidden" name="to" value="'.$field[2].'">', $formtest);
						}
					}
					elseif($field[0]=="rooms"){
						$formtest=preg_replace('/'.$fields.'/', '<select name="room" onChange="document.formular.room.value=this.value;sndReq()">'.reservations_get_room_options().'</select>', $formtest);
					}
					elseif($field[0]=="calendar"){
						$calendarLoad='<script language="JavaScript" src="'.WP_PLUGIN_URL.'/easyreservations/xmlhttprequestobject.js"></script>';
						$calendarLoad.='<script language="JavaScript" src="'.WP_PLUGIN_URL.'/easyreservations/send.js"></script>';
						$calendarLoad.='<form name="formular">';
						$calendarLoad.='<input type="hidden" name="room" onChange="sndReq()" value="5">';
						$calendarLoad.='<input type="hidden" name="date" onChange="sndReq()" value="0">';
						$calendarLoad.='</form>';
						$calendarLoad.='<div id="zeige"></div><input type="button" onClick="document.newreservation.date.value=0;">';
						$formtest=preg_replace('/'.$fields.'/', $calendarLoad, $formtest);
					}
					elseif($field[0]=="custom"){
						$valuefield=str_replace('"', '', $field[3]);
						if($field[1]=="text"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="text" name="'.$field[2].'">', $formtest);
						}
						elseif($field[1]=="textarea"){
							$formtest=preg_replace('/'.$fields.'/', '<textarea name="'.$field[2].'"></textarea>', $formtest);
						}
						elseif($field[1]=="check"){
							$formtest=preg_replace('/'.$fields.'/', '<input type="checkbox" name="'.$field[2].'">', $formtest);
						}
						elseif($field[1]=="radio"){
							if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
								$formtest=preg_replace('/'.$fields.'/', '<input type="radio" name="'.$field[2].'" value="'.$valuefield.'"> '.$valuefield, $formtest);
							}
							elseif(preg_match("/^[a-zA-Z0-9_ \\,\\t]+$/", $valuefield)){
								$valueexplodes=explode(",", $valuefield);
								$custom_radio='';
								foreach($valueexplodes as $value){
									if($value != '') $custom_radio .= '<input type="radio" name="'.$field[2].'" value="'.$value.'"> '.$value.'<br>';
								}
								$formtest=preg_replace('/'.$fields.'/', $custom_radio, $formtest);
							}
						}
						elseif($field[1]=="select"){
							if(preg_match("/^[0-9]+$/", $valuefield)){
								for($dienum=1; $valuefield >= $dienum; $dienum++){
									$custom_select .= '<option value="'.$dienum.'">'.$dienum.'</option>';
								}
								$formtest=preg_replace('/'.$fields.'/', '<select name="'.$field[2].'">'.$custom_select.'</select>', $formtest);
							}
							elseif(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
							
								$formtest=preg_replace('/'.$fields.'/', '<select name="'.$field[2].'"><option value="'.$valuefield.'">'.$field[3].'</option></select>', $formtest);
							}
							elseif(strstr($valuefield,",")) {
								$valueexplodes=explode(",", $valuefield);
								$custom_select='';
								foreach($valueexplodes as $value){
									if($value != '') $custom_select .= '<option value="'.$value.'">'.$value.'</option>';
								}
								$formtest=str_replace(''.$fields.'', '<select name="'.$field[2].'">'.$custom_select.'</select>', $formtest);
							}
						}
					} 
					elseif($field[0]=="price"){
						$valuefield=str_replace('"', '', $field[3]);
						if($field[1]=="checkbox"){
							$explodeprice=explode(":", $valuefield);
							$formtest=preg_replace('/'.$fields.'/', '<input type="checkbox" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].'">', $formtest);
						} elseif($field[1]=="radio"){
							if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
								$explodeprice=explode(":", $valuefield);
								$formtest=preg_replace('/'.$fields.'/', '<input type="radio" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].'"> '.$explodeprice[0].': '.reservations_format_money($explodeprice[1]).' &'.get_option("reservations_currency").';<br>', $formtest);
							} elseif(strstr($valuefield,",")) {
								$valueexplodes=explode(",", $valuefield);
								$custom_radio == '';
								foreach($valueexplodes as $value){
									$explodeprice=explode(":", $value);
									if($value != '') $custom_radio .= '<input type="radio" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].'"> '.$explodeprice[0].': '.reservations_format_money($explodeprice[1]).' &'.get_option("reservations_currency").';<br>';
								}
								$formtest=preg_replace('/'.$fields.'/', $custom_radio, $formtest);
							}
						} elseif($field[1]=="select"){
							if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
								$explodeprice=explode(":", $valuefield);
								$formtest=preg_replace('/'.$fields.'/', '<select name="'.$field[2].'"><option value="'.$explodeprice[0].':'.$explodeprice[1].'">'.$explodeprice[0].': '.reservations_format_money($explodeprice[1]).' &'.get_option("reservations_currency").';</option></select>', $formtest);
							} elseif(preg_match("/^[a-zA-Z0-9].+$/", $valuefield)){
								$valueexplodes=explode(",", $valuefield);
								$custom_select='';
								foreach($valueexplodes as $value){
									$explodeprice=explode(":", $value);
									if($value != '') $custom_select .= '<option value="'.$explodeprice[0].':'.$explodeprice[1].'">'.$explodeprice[0].': '.reservations_format_money($explodeprice[1]).' &'.get_option("reservations_currency").';</option>';
								}
								$formtest=str_replace($fields, '<select name="'.$field[2].'">'.$custom_select.'</select>', $formtest);
							}
						}
					}
					elseif($field[0]=="offers"){
						if($field[1]=="select"){
							$formtest=preg_replace('/'.$fields.'/', '<select name="specialoffer"><option value="0" select="selected">'. __( 'None' , 'easyReservations' ).'</option>'.reservations_get_offer_options().'</select>', $formtest);
						}
						elseif($field[1]=="box"){
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
								'showposts' => 1,
							);

							$my_post = get_posts($args);
							$theIDs = $my_post[0]->ID;
							if(get_option('permalink_structure')==''){ $theIDs=$explodeID[0]; }
							if(strpos(get_option('permalink_structure'),"%post_id%")!==false){ $theIDs=end($getlast); }
							$cates=get_the_category($theIDs);
							$cate=$cates[0]->term_id;
							$special_offer_promt='';

							if($cate==$special_offer_cat){
								$image_id = get_post_thumbnail_id($theIDs);  
								$image_url = wp_get_attachment_image_src($image_id,'large');  
								$image_url = $image_url[0];  
								$desc = get_post_meta($theIDs, 'reservations_short', true);
								$fromto = get_post_meta($theIDs, 'reservations_fromto', true);
									if(strlen(__($desc)) >= 45) { $desc = substr(__($desc),0,45)."..."; }
								$special_offer_promt.='<div id="parent"><div id="child" align="center">';
								$special_offer_promt.='<div align="left" style="width: 324px; border: #ffdc88 solid 1px; vertical-align: middle; background: #fffdeb; padding: 5px 5px 5px 5px; font:12px/18px Arial,serif; border-collapse: collapse;">';
									if(get_post_meta($theIDs, 'reservations_percent', true)!=""){ $special_offer_promt.='<span style="height: 20px; border: 0px; padding: 1px 5px 0 5px; margin: 32px 0 0 -50px; font:14px/18px Arial,serif; font-weight: bold; color: #fff; text-align: right; background: #ba0e01; position: absolute;">'.__(get_post_meta($theIDs, 'reservations_percent', true)).'</span>'; }
								$special_offer_promt.='<img src="'.$image_url.'" style="height:55px; width:55px; border:0px; margin:0px 10px 0px 0px; padding:0px;" class="alignleft"> '.__( 'You\'ve choosen' , 'easyReservations' ).': <b>'.__(get_the_title($theIDs)).'</b><img style="float: right;" src="'.RESERVATIONS_IMAGES_DIR.'/close.png" onClick="'."removeElement('parent','child')".'"><br>'.__( 'Available' , 'easyReservations' ).': '.__($fromto[0]).'<br>'.__($desc).' <input type="hidden"  name="specialoffer" value="'.$theIDs.'" /></div>';
								$special_offer_promt.='</div></div>';
							}

							$formtest=preg_replace('/'.$fields.'/', ''.$special_offer_promt.'', $formtest);
						}
					}
					elseif($field[0]=="submit"){
						if(isset($field[1])) $valuesubmit=$field[1]; else $valuesubmit='Submit';
						$formtest=preg_replace('/'.$fields.'/', '<input type="submit" value="'.$valuesubmit.'">', $formtest);
					}
				}
			$finalformedgeremove1=str_replace('[', '', $formtest);
			$finalformedgesremoved=str_replace(']', '', $finalformedgeremove1);
			$finalform='<form method="post"><input type="hidden" name="action" value="newreservation">'.$finalformedgesremoved.'</form>';
			return $finalform;
    }