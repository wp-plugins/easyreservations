<?php

function reservations_shortcode() {
?><script type="text/javascript">  

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
</script><link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script><script>
  $(document).ready(function() {
    $("#datepicker").datepicker( { altFormat: 'dd.mm.yyyy' });
  });
   $(document).ready(function() {
    $("#datepicker2").datepicker( { altFormat: 'dd.mm.yyyy' });
  });
</script><?php
			global $post;
			global $wpdb;	
			$reservation_address_option=get_option("reservation_form_address");
			$form_nights_enabled=get_option("reservation_form_nights");
			$reservation_form_phone=get_option("reservation_form_phone");
			$special_offer_style=get_option("reservation_form_special");

			
			if($_POST['action']) {
					if ($_POST['thename'] != '' AND strlen($_POST['thename']) < 20 AND strlen($_POST['thename']) > 3){
					$name_form=$_POST['thename'];}
					else { $error.= __( 'Please enter correct Name' , 'easyReservations' ).'<br>'; }
			
					if ($_POST['from'] != '' AND strtotime($_POST['from'])-time() > '0' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['from']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['from']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['from']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['from']))) {
					$arrivaldate_form=date("Y-m-d", strtotime($_POST['from']));
					$month_form=date("Y-m",strtotime($_POST['from']));}
					else { $error.= __( 'Please enter correct arrival Date' , 'easyReservations' ).'<br>'; }
					
					if($form_nights_enabled == '0'){
					if ($_POST['to'] AND strtotime($_POST['to'])-time() > '0' AND (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $_POST['to']) OR ereg ("([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})", $_POST['to']) OR ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $_POST['to']) OR ereg ("([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})", $_POST['to']))) {
					$daysbetween=strtotime($_POST['to'])-strtotime($_POST['from']);
					$nights_form=$daysbetween/24/60/60; }
					else { $error.= __( 'Please enter correct destination Date' , 'easyReservations' ).'<br>'; }
					} elseif($form_nights_enabled == '1'){ $nights_form=$_POST['nights']; }

					$pattern_mail = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,5}$";
					if ($_POST['email'] != '' AND strlen($_POST['email']) < 40 AND strlen($_POST['email']) > 5 AND eregi($pattern_mail, $_POST['email'])){
					$email_form=$_POST['email'];}
					else { $error.= __( 'Please enter correct eMail' , 'easyReservations' ).'<br>'; }

					if($reservation_form_phone == "1"){
					if ($_POST['phone'] != '' AND strlen($_POST['phone']) < 20 AND strlen($_POST['phone']) > 5){
					$phone_form=$_POST['phone'];}
					else { $error.= __( 'Please enter correct Phone' , 'easyReservations' ).'<br>'; }
					}

					if ($_POST['persons'] != '' AND strlen($_POST['persons']) < 4 AND is_numeric($_POST['persons'])){
					$persons_form=$_POST['persons'];}
					else { $error.= __( 'Please enter correct amount of Persons' , 'easyReservations' ).'<br>'; }

					if ($_POST['room'] != '' AND strlen($_POST['room']) < 10 AND is_numeric($_POST['room'])){
					$room_form=$_POST['room'];}
					else { $error.= __( 'Please select a Room', 'easyReservations').'<br>'; }

					if($reservation_address_option == "1"){
					if ($_POST['address'] != '' ){
					$address_form=$_POST['address']; }
					else { $error.= __( 'Please enter a correct Address' , 'easyReservations' ).'<br>'; }
					}

					if ($_POST['message']){
					$message_form=$_POST['message'];}
					
					if($_POST['specialoffer'] != '0'){
					$specialoffer_form=$_POST['specialoffer'];}
					else $specialoffer_form='0';
			}
			
			if($_POST['action'] AND !$error) {
				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(name, phone, email, notes, nights, arrivalDate, dat, room, number, special ) 
				VALUES ('$name_form', '$phone_form', '$email_form', '$message_form*/*$address_form', '$nights_form', '$arrivaldate_form', '$month_form', '$room_form', '$persons_form', '$specialoffer_form' )" ) ); 
				echo __( 'Your Reservation was sent' , 'easyReservations' );
				
					if($specialoffer_form != "0"){
						$post_id_7 = get_post($specialoffer_form); 
						$specialoffer = $post_id_7->post_title;	
					}
					if($specialoffer_form == "0") $specialoffer =  __( 'None' , 'easyReservations' );	
			
					$post_id8 = get_post($room); 
					$roomtitle = $post_id8->post_title;	
					
					$reservation_support_mail = get_option("reservations_support_mail");
					$subj="New Reservation";
					$msg="New Reservation from \n Name: ".$name_form." \n Phone: ".$phone_form." \n eMail: ".$email_form." \n Wanted Arrival Date: ".$arrivaldate_form." \n Nights to stay: ".$nights_form." \n Number of Persons: ".$persons_form." \n In the Room: ".__($roomtitle)." \n Has chosen the Special offer: ".__($specialoffer)." \n With the Message: ".$message_form." \n ";

					mail($reservation_support_mail,$subj,$msg);
			}
			
			$room_category = get_option('reservations_room_category');
			$max_places = get_option("reservations_table_count");
			$special_offer_cat = get_option("reservations_special_offer_cat");

			$comefrom=wp_get_referer();
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

					$posts = "SELECT post_title, ID FROM ".$wpdb->prefix ."posts WHERE post_type='post' AND post_status='publish'";
					$results = $wpdb->get_results($posts);

								foreach( $results as $result ) {
									$id=$result->ID;
									$name=$result->post_title;
									$categ=get_the_category($id);
									$care=$categ[0]->term_id;
									if($care==$room_category) { $room.='<option value="'.$id.'">'.__($name).'</option>'; }
									if($care==$special_offer_cat) { $special_option.='<option value="'.$id.'">'.__($name).'</option>'; }
								}
					if($reservation_form_phone == "1"){ $phonerow = '<tr><td style="padding:8px;">'.__( 'Phone' , 'easyReservations' ).':</td><td><input class="phone_input" type="text" name="phone"></td></tr>'; }

					if($special_offer_style=="1") {
						if($cate==$special_offer_cat){ 
								$image_id = get_post_thumbnail_id($theIDs);  
								$image_url = wp_get_attachment_image_src($image_id,'large');  
								$image_url = $image_url[0];  
								$desc = get_post_meta($theIDs, 'short', true);
									if(strlen(__($desc)) >= 45) { $desc = substr(__($desc),0,45)."..."; }
								$special_offer_promt.='<div id="parent"><div id="child" align="center">';
								$special_offer_promt.='<div align="left" style="width: 324px; border: #ffdc88 solid 1px; vertical-align: middle; background: #fffdeb; padding: 5px 5px 5px 5px; font:12px/18px Arial,serif; border-collapse: collapse;">';
									if(get_post_meta($theIDs, 'percent', true)!=""){ $special_offer_promt.='<span style="height: 20px; border: 0px; padding: 1px 5px 0 5px; margin: 32px 0 0 -50px; font:14px/18px Arial,serif; font-weight: bold; color: #fff; text-align: right; background: #ba0e01; position: absolute;">'.__(get_post_meta($theIDs, 'percent', true)).'</span>'; }
								$special_offer_promt.='<img src="'.$image_url.'" style="height:55px; width:55px; border:0px; margin:0px 10px 0px 0px; padding:0px;" class="alignleft"> '.__( 'You\'ve choosen' , 'easyReservations' ).': <b>'.__(get_the_title($theIDs)).'</b><img style="float: right;" src="'.WP_PLUGIN_URL.'/easyReservations/images/close.png" onClick="'."removeElement('parent','child')".'"><br>'.__( 'Available' , 'easyReservations' ).': '.__($fromto[0]).'<br>'.__($desc).' <input type="hidden"  name="specialoffer" value="'.__($theIDs).'" /></div>';
								$special_offer_promt.='</div></div>';
						} else $special_offer_promt.='<input type="hidden"  name="specialoffer" value="0" />';
					}
					else { $special_offer_field='<tr><td style="padding:8px;">Special Offer:</td><td><select name="specialoffer"><option value="0" select="selected">'. __( 'None' , 'easyReservations' ).'</option>'.$special_option.'</select></td></tr>'; }

					if($reservation_address_option == "1"){ $addressform='<tr valign="top"><td style="padding:8px;">'.__( 'Address' , 'easyReservations' ).':</td><td><textarea name="address" rows="2" cols="20"></textarea></td></tr>'; }

					if($form_nights_enabled=="0") { $field='<input id="datepicker2" class="to_input" type="text" name="to">'; $nightsname=__( 'To' , 'easyReservations' ); } 

								$form=''.$special_offer_promt.'<br><div align="center">'.$error.'<form method="post"><input type="hidden" name="action" value="newreservation"/>
									<table style="width:55%;">
										<tr>
											<td style="padding:8px; width:50%">'.__( 'Name' , 'easyReservations' ).':</td><td style="width:50%"><input class="name_input" type="text" id="names" name="thename"></td>
										</tr>
										<tr>
											<td style="padding:8px;">'.__( 'From' , 'easyReservations' ).':</td><td><input id="datepicker" type="text" name="from"></td>
										</tr>
										<tr>
											<td style="padding:8px;">'.$nightsname.'</td><td>'.$field.'</td>
										</tr>
										<tr>
											<td style="padding:8px;">'.__( 'eMail' , 'easyReservations' ).':</td><td><input class="email_input" type="text" name="email"></td>
										</tr>
										'.$phonerow.'
										<tr>
											<td style="padding:8px;">'.__( 'Persons' , 'easyReservations' ).':</td><td><input size="2px" class="persons_input" type="text" name="persons"></td>
										</tr>
										'.$addressform.'
										<tr>
											<td style="padding:8px;">'.__( 'Room' , 'easyReservations' ).':</td><td><select class="toom_input" name="room">'.$room.'</select></td>
										</tr>
									'.$special_offer_field.'
										<tr valign="top">
											<td style="padding:8px;">'.__( 'Message' , 'easyReservations' ).':</td><td><textarea name="message" rows="4" cols="20" ></textarea></td>
										</tr>
									</table>
									<input type="submit" value="Submit">
									</form></div>';

			return $form;
    }

	?>