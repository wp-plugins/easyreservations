<?php

function reservation_main_page() {
	wp_enqueue_style( 'datestyle');
	$main_options = get_option("reservations_main_options");
	$show = $main_options['show'];
	$overview_options = $main_options['overview'];
	global $wpdb, $the_rooms_intervals_array, $the_rooms_array;
	
	if(isset($_POST['main_settings'])) $prompt='<div class="updated" style="margin-top:-5px !important"><p>'.__( 'Reservations dashboard settings saved' , 'easyReservations' ).'</p></div>';
	$all_rooms = $the_rooms_array;

	if(isset($_POST['delete'])) $post_delete=$_POST['delete'];
	if(isset($_POST['roomexactly'])) $roomexactly=$_POST['roomexactly'];
	if(isset($_POST['approve_message'])) $approve_message=$_POST['approve_message'];
	if(isset($_POST['sendthemail'])) $sendthemail=$_POST['sendthemail'];
	if(isset($_POST['hasbeenpayed'])) $hasbeenpayed=$_POST['hasbeenpayed'];
	if(isset($_POST['approve'])) $post_approve=$_POST['approve'];
	if(isset($_POST['editthereservation'])) $editthereservation=$_POST['editthereservation'];
	if(isset($_POST['addreservation'])) $addreservation=$_POST['addreservation'];

	if(isset($_GET['more'])) $moreget=$_GET['more'];
	else $moreget = 0;
	if(isset($_GET['perpage'])) update_option("reservations_on_page",$_GET['perpage']);
	if(isset($_GET['deletecustomfield'])) $deletecustomfield=$_GET['deletecustomfield'];
	if(isset($_GET['deletepricefield'])) $deletepricefield=$_GET['deletepricefield'];
	if(isset($_GET['sendmail'])) $sendmail=$_GET['sendmail'];
	if(isset($_GET['approve'])) $approve=$_GET['approve'];
	if(isset($_GET['view']))  $view=$_GET['view'];
	if(isset($_GET['delete'])) $delete=$_GET['delete'];
	if(isset($_GET['edit'])) $edit=$_GET['edit'];
	if(isset($_GET['add'])) $add=$_GET['add'];

	if(isset($_POST['room-saver-from'])){
		$timestamp_timebetween=$_POST['room-saver-from']-strtotime(date("d.m.Y", time())); // to show days before arrival in Reservation Overview
		$moreget+=round($timestamp_timebetween/86400);
	}

	if(!isset($edit) && !isset($view) && !isset($add) && !isset($approve) && !isset($sendmail)  && !isset($delete)){
		$nonepage = 0;
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + BULK ACTIONS (trash,delete,undo trash) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_GET['bulk']) && check_admin_referer( 'easy-main-bulk', 'easy-main-bulk' )){ // GET Bulk Actions
		if(isset($_GET['bulkArr'])) {
			$to=0;
			$listes=$_GET['bulkArr'];

			if($_GET['bulk']==1){ //  If Move to Trash 
				if(count($listes)  > 1) {
					foreach($listes as $liste){
						$to++;
						$ids=$liste;
						$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='del' WHERE id='$ids' ") );
					}
				} else {
					$ids=$listes[0];
					$to++;
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='del' WHERE id='$ids' ") ); 
				}
			if ($to!=1) { $linkundo=implode("&bulkArr[]=", $listes); } else { $linkundo=$liste; }
			if ($to==1) { $anzahl=__('Reservation', 'easyReservations'); } else { $anzahl=$to.' '.__('Reservations', 'easyReservations');  }
			$prompt='<div class="updated" style="margin-top:-5px !important"><p>'.$anzahl.' '.__( 'moved to trash' , 'easyReservations' ).'. <a href="'.wp_nonce_url('admin.php?page=reservations&bulkArr[]='.$linkundo.'&bulk=2', 'easy-main-bulk').'">'.__( 'Undo' , 'easyReservations' ).'</a></p></div>';

			}
			if($_GET['bulk']=="2"){ //  If Undo Trashing
				if(count($listes)  > "1" ) { 
					foreach($listes as $liste){
						$ids=$liste;
						$to++;
						$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='' WHERE id='$ids' ") ); 	
					}
				}  else { 
					$ids=$listes[0];
					$to++;
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='' WHERE id='$ids' ") ); }

			if($to==1) { $anzahl=__('Reservation', 'easyReservations'); } else { $anzahl=$to.' '.__('Reservations', 'easyReservations');  }
			$prompt='<div class="updated" style="margin-top:-5px !important"><p>'.$anzahl.' '.__( 'restored from trash' , 'easyReservations' ).'</p></div>';

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

			if($to==1){ $anzahl=__('Reservation', 'easyReservations'); } else { $anzahl=$to.' '.__('Reservations', 'easyReservations');  }
			$prompt='<div class="updated" style="margin-top:-5px !important"><p>'.$anzahl.' '.__('deleted permanently', 'easyReservations').'</p></div>';
			}
		}
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE CUSTOM FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($deletecustomfield)){
		$deletes = easyreservations_edit_custom(array(), $edit, 0, 0, $deletecustomfield, 0, 0, 0, 1);
		if($deletes != 'error') $prompt='<div class="updated" style="margin-top:-5px !important"><p>'.__( 'Custom field deleted' , 'easyReservations' ).'</p></div>';
		else echo '<div class="updated" style="margin-top:-5px !important"><p>error #23 no key exist</p></div>';
	}


/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE PRICE FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
	if(isset($deletepricefield)){
		$deletes = easyreservations_edit_custom(array(), $edit, 0, 0, $deletepricefield, 1, 0, 0, 1);
		if($deletes == 'success') $prompt='<div class="updated" style="margin-top:-5px !important"><p>'.__( 'Custom price field deleted' , 'easyReservations' ).'</p></div>';
		else echo '<div class="updated" style="margin-top:-5px !important"><p>error #23 no key exist</p></div>';
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($editthereservation) && check_admin_referer( 'easy-main-edit', 'easy-main-edit' )){
		global $wpdb;

		$errors=0;
		$moneyerrors=0;
		$res_the_name=$_POST["name"];
		$date=$_POST["date"];
		$dateend=$_POST["dateend"];
		$email=$_POST["email"];
		$EDITroomex=$_POST["roomexactly"];
		$EDITroom=$_POST["room"];
		$persons=$_POST["persons"];
		$childs=$_POST["childs"];
		$country=$_POST["country"];
		$reservation_date=strtotime($_POST["reservation_date"]);
		$edit_user=$_POST["edit_user"];
		if(isset($_POST['from-time-hour'])) $from_hour = ((int) $_POST['from-time-hour']*60)+(int) $_POST['from-time-min']; else $from_hour = 12*60;
		if(isset($_POST['to-time-hour']))  $to_hour = ((int) $_POST['to-time-hour']*60)+(int) $_POST['to-time-min'] ;else $to_hour = 12*60;

		if(isset($_POST["priceset"])){
			if($_POST["priceset"]=='') $EDITpriceset=0;
			else $EDITpriceset=$_POST["priceset"];
		}
		if(isset($_POST["EDITwaspaid"])){
			if($_POST["EDITwaspaid"]=='') $EDITwaspaid=0;
			else $EDITwaspaid=$_POST["EDITwaspaid"];
		}
		$EDITreservationStatus=$_POST["reservation_status"];
		$customfields=""; $new_customfields = '';
		$custompfields=""; $new_custompfields = '';

		for($theCount = 0; $theCount < 500; $theCount++){
			if(isset($_POST["customvalue".$theCount]) && isset($_POST["customtitle".$theCount])){
				$customfields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$theCount], 'title' => $_POST["customtitle".$theCount], 'value' => $_POST["customvalue".$theCount]);
			}
		}

		for($theCount = 0; $theCount < 500; $theCount++){
			if(isset($_POST["customPvalue".$theCount]) && isset($_POST["customPtitle".$theCount])){
				if(easyreservations_check_price($_POST["custom_price".$theCount]) == 'error') $moneyerrors++;
				$custompfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$theCount], 'title' => $_POST["customPtitle".$theCount], 'value' => $_POST["customPvalue".$theCount], 'amount' => easyreservations_check_price($_POST["custom_price".$theCount]));
			}
		}

		if(!empty($customfields))	$new_customfields =	    easyreservations_edit_custom($customfields,	$edit, 0, 1, false, 0, 'cstm', 0, 1);
		if(!empty($custompfields)) $new_custompfields =	easyreservations_edit_custom($custompfields, $edit, 0, 1, false, 1, 'cstm', 0, 1);

		$getprice=easyreservations_price_calculation($edit, '');
		if(isset($_POST["fixReservation"]) && isset($EDITpriceset) && $EDITpriceset != 0){
			if(easyreservations_check_price($EDITpriceset) != 'error'){
				$theNewEditPrice = easyreservations_check_price($EDITpriceset);
			} else {
				$moneyerrors++;
			}
		} elseif(isset($_POST["fixReservation"])){
			$theNewEditPrice = $getprice['price'];
		} else {
			$theNewEditPrice = '';
		}

		if(easyreservations_check_price($EDITwaspaid) != 'error'){
			$theNewEditPaid = easyreservations_check_price($EDITwaspaid);
		} else {
			$moneyerrors++;
		}

		$settepricei = $theNewEditPrice.';'.$theNewEditPaid;

		$timestampstartedit=strtotime($date) + ($from_hour*60);
		$timestampendedit=strtotime($dateend) + ($to_hour*60);
		$rightdate=date("Y-m-d H:i:s", $timestampstartedit);
		$reservation_date_sql=date("Y-m-d H:i:s", $reservation_date);
		$reservation_end_sql=date("Y-m-d H:i:s", $timestampendedit);
		$roomnumbers = get_post_meta($EDITroom, 'roomcount', true);

		if($EDITroomex > $roomnumbers) $errors++;
		if($timestampstartedit > $timestampendedit) $errors++;

		if($errors > 0 && easyreservations_check_val()){
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Departure before arrival or roomcount too high' , 'easyReservations' ).'</p></div>';
		} elseif($moneyerrors > 0){
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Wrong money formatting' , 'easyReservations' ).'</p></div>';
		} elseif(easyreservations_check_avail($EDITroom, $timestampstartedit, $EDITroomex, $timestampendedit, 0, $edit, 0, $EDITreservationStatus) > 0){
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Selected resource is occupied at this time' , 'easyReservations' ).'</p></div>';
		} else {
			$changelog = '';
			if(isset($sendthemail) && $sendthemail=="on"){

				$checkSQLedit = "SELECT email, name, arrival, departure, number, childs, country, room, approve, custom FROM ".$wpdb->prefix ."reservations WHERE id='$edit'";
				$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

				$beforeArray = array( 'arrival' => date(RESERVATIONS_DATE_FORMAT, strtotime($checkQuerry[0]->arrival)), 'departure' => date(RESERVATIONS_DATE_FORMAT, strtotime($checkQuerry[0]->departure)), 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country );
				$afterArray = array( 'arrival' => date(RESERVATIONS_DATE_FORMAT, $timestampstartedit), 'departure' => date(RESERVATIONS_DATE_FORMAT, $timestampendedit), 'email' => $email, 'name' => $res_the_name, 'persons' => $persons, 'childs' => $childs, 'room' =>  $EDITroom, 'custom' => $customfields, 'country' => $country );

				$changelog = easyreservations_generate_res_changelog($beforeArray, $afterArray);
			}

			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrival='$rightdate', departure='$reservation_end_sql', name='$res_the_name', email='$email', room='$EDITroom', number='$persons', childs='$childs', roomnumber='$EDITroomex', price='$settepricei', approve='$EDITreservationStatus', country='$country', reservated='$reservation_date_sql', user='$edit_user' WHERE id='$edit' "));

			if(isset($sendthemail) && $sendthemail=="on"){
				$emailformation=get_option('reservations_email_to_user_admin_edited');
				if($emailformation['active'] == 1) easyreservations_send_mail($emailformation['msg'], $email, $emailformation['subj'], $approve_message, $edit, $changelog);
			}

			$prompt = '<div class="updated" style="margin-top:-5px !important"><p>'.__( 'Reservation edited!' , 'easyReservations' ).'</p><p><a href="admin.php?page=reservations">&#8592; Back to Dashboard</a></p></div>';
		}
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($addreservation) && check_admin_referer( 'easy-main-add', 'easy-main-add' )){
		$errors="";
		$ADDname=$_POST["name"];
		$ADDdate=$_POST["date"];
		$ADDdateend=$_POST["dateend"];
		$ADDemail=$_POST["email"];
		$ADDroomex=$_POST["roomexactly"];
		$ADDroom=$_POST["room"];
		$ADDpersons=$_POST["persons"];
		$ADDchilds=$_POST["childs"];
		$ADDcountry=$_POST["country"];
		$ADDstatus=$_POST["reservationStatus"];
		$ADDreservation_date=$_POST["reservation_date"];
		$ADDuser=$_POST["edit_user"];
		if(isset($_POST['from-time-hour'])) $from_hour = ((int) $_POST['from-time-hour']*60)+(int) $_POST['from-time-min']; else $from_hour = 43200;
		if(isset($_POST['to-time-hour']))  $to_hour = ((int) $_POST['to-time-hour']*60)+(int)$_POST['to-time-min'];else $to_hour = 43200;
		$theInputPOSTs=array($_POST["date"], $_POST["name"], $_POST["email"], $_POST["room"], $_POST["dateend"], $_POST["persons"]);

		foreach($theInputPOSTs as $input){
			if($input==''){ $errors .= " "; }
		}

		$ADDcustomFields = '';

		for($theCount = 0; $theCount < 100; $theCount++){
			if(isset($_POST["customvalue".$theCount]) && isset($_POST["customtitle".$theCount])){
				$ADDcustomFields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$theCount], 'title' => $_POST["customtitle".$theCount], 'value' => $_POST["customvalue".$theCount]);
			}
		}

		$ADDcustomPfields = '';
		for($theCount = 0; $theCount < 100; $theCount++){
			if(isset($_POST["customPvalue".$theCount]) && isset($_POST["customPtitle".$theCount])){
				if(easyreservations_check_price($_POST["custom_price".$theCount]) == 'error') $moneyerrors++;
				$ADDcustomPfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$theCount], 'title' => $_POST["customPtitle".$theCount], 'value' => $_POST["customPvalue".$theCount], 'amount' => easyreservations_check_price($_POST["custom_price".$theCount]) );
			}
		}

		$ADDcustomFields_right = maybe_serialize($ADDcustomFields);
		$ADDcustomPfields_right = maybe_serialize($ADDcustomPfields);

		$ADDtimestampsanf = strtotime($ADDdate)+($from_hour*60);
		$ADDtimestampsend = strtotime($ADDdateend)+($to_hour*60);

		$ADDroomnumbers = get_post_meta($ADDroom, 'roomcount', true);
		if($ADDroomex > $ADDroomnumbers) $errors .= __( 'Resource number was too high' , 'easyReservations' );
		if($ADDtimestampsanf > $ADDtimestampsend) $errors .= __( 'The departure date has to be after the arrival date' , 'easyReservations' );

		$ADDrightdate=date("Y-m-d H:i:s", $ADDtimestampsanf);
		$ADDrightend=date("Y-m-d H:i:s", $ADDtimestampsend);
		$ADDreservation_date_sql=date("Y-m-d H:i:s", strtotime($ADDreservation_date));

		if($errors != ""){
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Please fill out all Fields' , 'easyReservations' ).'</p></div>';
		} elseif(easyreservations_check_avail($ADDroom, $ADDtimestampsanf, $ADDroomex, $ADDtimestampsend, 0, 0, 0) > 0){
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Selected resource is occupied at this time' , 'easyReservations' ).'</p></div>';
		} elseif($moneyerrors > 0){
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Wrong money formatting' , 'easyReservations' ).'</p></div>';
		} else {

			if(easyreservations_check_val()) $wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(arrival, name, email, departure, room, roomnumber, number, childs, country, approve, custom, customp, reservated, user ) VALUES ('$ADDrightdate', '$ADDname', '$ADDemail', '$ADDrightend', '$ADDroom', '$ADDroomex', '$ADDpersons', '$ADDchilds', '$ADDcountry', '$ADDstatus', '$ADDcustomFields_right', '$ADDcustomPfields_right', '$ADDreservation_date_sql', '$ADDuser'  )"  ) ); 

			$prompt='<div class="updated" style="margin-top:-5px !important"><p>'.__( 'Reservation has been added' , 'easyReservations' ).'</p></div>';

			$newID = mysql_insert_id();

			if(isset($_POST["fixReservation"]) && $_POST["fixReservation"] == "on"){
				if($_POST["setChoose"] == "custm"){
					if(easyreservations_check_price($_POST["priceAmount"]) == 'error') $errors++;
					$thePriceAdd = easyreservations_check_price($_POST["priceAmount"]);
				} else {
					$thepriceArray = easyreservations_price_calculation($newID, '');
					$thePriceAdd = $thepriceArray['price'];
				}

				if($_POST["paidAmount"] == '') $thePricePaid = 0;
				else $thePricePaid = $_POST["paidAmount"];

				if(easyreservations_check_price($thePricePaid) == 'error') $errors++;

				$theNewPrice = $thePriceAdd.';'.$thePricePaid;

				if($errors == 0) $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET price='$theNewPrice' WHERE id='$newID' "));
				else $prompt.='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Price couldnt be fixed, input wasnt money' , 'easyReservations' ).'</p></div>';
			}
			?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations#pending"><?php
		}
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GET INFORMATIONS IF A RESERVATION IS CALLED DIRECTLY (view,edit,approve,reject,sendmail) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($approve)  || isset($delete) || isset($view) || isset($edit) || isset($sendmail)) { //Query of View Reject Edit Sendmail and Approve
		if(isset($edit)) $theIDofRes = $edit;
		elseif(isset($approve)) $theIDofRes = $approve;
		elseif(isset($view)) $theIDofRes = $view;
		elseif(isset($sendmail)) $theIDofRes = $sendmail;
		elseif(isset($delete)) $theIDofRes = $delete;

		$sql_approvequerie = "SELECT id, name, approve, arrival, departure, room, roomnumber, number, childs, country, email, custom, customp, price, reservated, user FROM ".$wpdb->prefix ."reservations WHERE id='%s'";
		$approvequerie = $wpdb->get_results($wpdb->prepare($sql_approvequerie, $theIDofRes ));

		$id=$approvequerie[0]->id;
		$res_the_name=$approvequerie[0]->name;
		$reservationStatus=$approvequerie[0]->approve;
		$reservation_departure_stamp = strtotime($approvequerie[0]->departure);
		$reservation_arrival=$approvequerie[0]->arrival;
		$reservation_arrival_stamp = strtotime($reservation_arrival);
		$room=$approvequerie[0]->room;
		$reservationNights= easyreservations_get_nights($the_rooms_intervals_array[$room],$reservation_arrival_stamp, $reservation_departure_stamp);
		$exactlyroom=$approvequerie[0]->roomnumber;
		$exactly_room_name = easyreservations_get_roomname($exactlyroom, $room);
		$persons=$approvequerie[0]->number;
		$childs=$approvequerie[0]->childs;
		$country=$approvequerie[0]->country;
		$mail_to=$approvequerie[0]->email;
		$the_user = $approvequerie[0]->user;
		if($approvequerie[0]->custom == '') $customs = ''; else $customs=easyreservations_get_customs($approvequerie[0]->custom, 0, 'cstm');
		if($approvequerie[0]->customp == '') $customsp = ''; else $customsp=easyreservations_get_customs($approvequerie[0]->customp, 0, 'cstm');

		$reservated=date(RESERVATIONS_DATE_FORMAT, strtotime($approvequerie[0]->reservated));
		$get_role = get_post_meta($room, 'easy-resource-permission', true);
		if(!empty($get_role) && !current_user_can($get_role)) die('You havnt the rights to view this reservation');

		$information='<small>'.__( 'This is how the price would get calculated now. After changing Filters/Groundprice/Settings or the reservations price it wont match the fixed price anymore.' , 'easyReservations' ).'</small>';
		$pricexpl=explode(";", $approvequerie[0]->price);
		if(isset($approve)  || isset($delete) || isset($view)) $roomwhere= $room; // For Overview only show date on view
		$room_name=easyreservations_get_the_title($room, $all_rooms);
		$roomcount = get_post_meta($room, 'roomcount', true);

		if(!empty($exactlyroom) && $exactlyroom > 0 && $exactlyroom > $roomcount) $prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Exactly room is above roomcount' , 'easyReservations' ).'</p></div>';

		$timestamp_timebetween=$reservation_arrival_stamp-strtotime(date("d.m.Y", time()))-(86400*3); // to show days before arrivaldate in Reservation Overview
		$moreget+=ceil($timestamp_timebetween/86400);
	}

	if(isset($sendmail) && isset($_POST['thesendmail'])){
		$emailformation=get_option('reservations_email_sendmail');

		if($emailformation['active'] == 1) easyreservations_send_mail($emailformation['msg'], $mail_to, $emailformation['subj'], $approve_message, $id, '');
		$prompt='<div class="updated" style="margin-top:-5px !important"><p>'.__( 'eMail sent successfully' , 'easyReservations' ).'</p></div>';
	}

	if(isset($post_approve) && $post_approve=="yes"){

		$pricearry = easyreservations_price_calculation($approve, '');
		if(isset($hasbeenpayed) && $hasbeenpayed=="on") $priceset2=$pricearry['price'].';1'; else $priceset2=$pricearry['price'].';0';

		if(easyreservations_check_avail($room, $reservation_arrival_stamp, $roomexactly, $reservation_departure_stamp, 0, $id) == 0){
			$priceset=str_replace(",", ".", $priceset2);
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes', roomnumber='$roomexactly', price='$priceset' WHERE id='$approve'"  ) ); 	

			if(isset($sendthemail) && $sendthemail=="on"){
				$emailformation=get_option('reservations_email_to_userapp');
				if($emailformation['active'] == 1) easyreservations_send_mail($emailformation['msg'], $mail_to, $emailformation['subj'], $approve_message, $id, '');
			}
			$prompt='<div class="updated" style="margin-top:-5px !important"><p> '.__( 'Reservation approved' , 'easyReservations' ).'</p></div>';
			?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
		}	else {	
			$prompt='<div class="error" style="margin-top:-5px !important"><p>'.__( 'Selected resource is occupied at this time' , 'easyReservations' ).'</p></div>';
		}
	}

	if(isset($post_delete) && $post_delete=="yes"){
		$pricearry = easyreservations_price_calculation($approve, '');
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='no' WHERE id=$delete"  ) ); 
		if(isset($sendthemail) && $sendthemail=="on"){
			$emailformation=get_option('reservations_email_to_userdel');
			if($emailformation['active'] == 1) easyreservations_send_mail($emailformation['msg'], $mail_to, $emailformation['subj'], $approve_message, $id, '');
		}
		$prompt='<div class="updated" style="margin-top:-5px !important"><p>'.$anzahl.' '.__( 'Reservations rejected' , 'easyReservations' ).'</p></div>';
		?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
	} ?>
<h2>
	<?php echo __( 'Reservations Dashboard' , 'easyReservations' );?>
	<a class="add-new-h2" href="admin.php?page=reservations&add"><?php echo __( 'Add New' , 'easyReservations' );?></a>
</h2>
<?php 	if(isset($prompt)) echo $prompt; 
if(!isset($show['show_welcome']) || $show['show_welcome'] != 0){?>
<div id="wrap">
<div class="easy-welcome-panel" id="easy-welcome-panel">
	<div class="wp-badge easy-badge">Version 2.0.2</div>
	<h3>Welcome to easyReservations 2.0.2!</h3>
	<p class="about-description">
		Now with hourly and weekly billing, reservations in any amount of time and many improvements!<br>
		Visit the brand new website at easyreservations.org with a better <a href="http://easyreservations.org/knowledgebase/" target="_blank">Documentation</a> and the <a href="http://easyreservations.org/forum/" target="_blank">Support Forum</a> - the only place to get support from now.<br>
		The first <a href="http://easyreservations.org/module/" target="_blank">Modules</a> got released to purchase: guestContact, extendedCalendar and the searchForm. <a href="http://easyreservations.org/module/paypal" target="_blank">>PayPal</a> Module is available too now!<br>
		Please think about supporting the development by getting a <a href="http://easyreservations.org/module/lifetime/" target="_blank">Life-Time Member</a>. You wont regret it. [<a href="#" onclick="document.getElementById('easy-welcome-panel').style.display = 'none';">dissmiss</a>]
	</p>
</div>
<?php
	if(!isset($show['show_welcome']) || $show['show_welcome'] != 1){
		$main_options['show']['show_welcome'] = 0;
		update_option('reservations_main_options', $main_options);
	}
}
if($show['show_overview']==1){ //Hide Overview completly
	if(RESERVATIONS_STYLE == 'widefat'){
		$ovBorderColor='#9E9E9E';
		$ovBorderStatus='dotted';
	} elseif(RESERVATIONS_STYLE == 'greyfat'){
		$ovBorderColor='#777777';
		$ovBorderStatus='dashed';
	}
?>
<script>
	function generateXMLHttpReqObjThree(){
		var resObjektTwo = null;
		try {
			resObjektThree = new ActiveXObject("Microsoft.XMLHTTP");
		} catch(Error){
			try {
				resObjektThree = new ActiveXObject("MSXML2.XMLHTTP");
			} catch(Error){
				try {
					resObjektThree = new XMLHttpRequest();
				} catch(Error){
					alert("AJAX error");
				}
			}
		}
		return resObjektThree;
	}

	function generateAJAXObjektThree(){
		this.generateXMLHttpReqObjThree = generateXMLHttpReqObjThree;
	}

	xxy = new generateAJAXObjektThree();
	resObjektThree = xxy.generateXMLHttpReqObjThree();
	var save = 0;
	var countov = 0;
	var the_ov_interval = 86400;

	function easyRes_sendReq_Overview(x,y,daystoshow, interval){
		
		jQuery('#jqueryTooltip').remove();
		the_ov_interval = interval;
		if(x && x != 'no') x = 'more=' + x;
		else var x = '';
		if(y && y != 'no') y =  '&dayPicker=' + y;
		else var y = '';
		var reservationNights = '<?php if(isset($reservationNights)) echo $reservationNights; ?>';
		if(reservationNights != '') var a = '&reservationNights=' + reservationNights;
		else var a = '';
		var roomwhere = '<?php if(isset($roomwhere)) echo $roomwhere; ?>';
		if(roomwhere != '') var b = '&roomwhere=' + roomwhere;
		else var b = '';
		var add = '<?php if(isset($add)) echo '1'; ?>';
		if(add != '') var c = '&add=' + add;
		else var c = '';
		var edit = '<?php if(isset($edit)) echo $edit; ?>';
		if(edit != '') var d = '&edit=' + edit;
		else var d = '';
		var app = '<?php if(isset($approve)) echo $approve; ?>';
		if(app != '') var appr = '&approve=' + app;
		else var appr = '';
		var id = '<?php if(isset($id)) echo $id; ?>';
		if(id != '') var e = '&id=' + id;
		else var e = '';
		var res_date_from_stamp = '<?php if(isset($reservation_arrival_stamp)) echo $reservation_arrival_stamp.'-'.$reservation_departure_stamp; ?>';
		if(res_date_from_stamp != '') var h = '&res_date_from_stamp=' + res_date_from_stamp;
		else var h = '';
		var nonepage = '<?php if(isset($nonepage)) echo $nonepage; ?>';
		if(nonepage != '') var f = '&nonepage=' + nonepage;
		else var f = '';
		if(daystoshow) var g = '&daysshow=' + daystoshow;
		else g = '&daysshow=' + <?php if(isset($overview_options['overview_show_days']) && !empty($overview_options['overview_show_days'])) echo $overview_options['overview_show_days']; else echo 30; ?>;

		if((y != "" || x != "") && save == 0){
			save = 1;
			resObjektThree.open('post', '<?php echo WP_PLUGIN_URL; ?>/easyreservations/overview.php' ,true);
			resObjektThree.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			resObjektThree.onreadystatechange = handleResponseValidate;
			resObjektThree.send(x + y + a + b + c + d + e + f + g + h + appr + '&interval=' + interval);
			if(document.getElementById('pickForm')) document.getElementById('pickForm').innerHTML = '<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading1.gif">';
		}
	}

	function handleResponseValidate(){
		var text="";
		if(resObjektThree.readyState == 4){
			text=resObjektThree.responseText;
			document.getElementById("theOverviewDiv").innerHTML = text;
			jQuery(document).ready(function(){
				createPickers();
			});
			Click = 0;
			var from = document.getElementById('datepicker');
			var to = document.getElementById('datepicker2');
			if(countov != 0 && window.dofakeClick && from && from.value != '<?php if(isset($reservation_arrival_stamp)) echo date('d.m.Y', $reservation_arrival_stamp); ?>' && to && to != '<?php if(isset($reservation_departure_stamp)) echo date('d.m.Y', $reservation_departure_stamp); ?>'){
				dofakeClick(2);
			}
			countov++;
			save = 0;
			jQuery.holdReady(false);

		}
	}

	function createPickers(){
		jQuery("#dayPicker").datepicker({
			changeMonth: true,
			changeYear: true,
			firstDay: 1,
			buttonText: '<?php echo __( 'choose date' , 'easyReservations' ); ?>',
			showOn: 'both',
			buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png',
			buttonImageOnly: true,
			defaultDate: +10,
			onSelect: function(){
				easyRes_sendReq_Overview('no', document.getElementById("dayPicker").value, '',<?php echo 86400; ?>);
			}
		});

		jQuery.fn.column = function(i) {
			if(i){
				return jQuery('tr td:nth-child('+(i)+')', this);
			}
		}

		jQuery(function() {
			jQuery("#overview td").hover(function() {
			
				var curCol = jQuery(this).attr("axis") ;
				if(curCol){
					jQuery('#overview').column(curCol).addClass("hover");
					jQuery('#overview').addClass("hover");
				}
			
			}, function() {
				var curCol = jQuery(this).attr("axis") ;
				if(curCol) jQuery('#overview').column(curCol).removeClass("hover"); 
			});
		});
		
		jQuery('#jqueryTooltip').destroy;
		var jqueryTooltip = jQuery('<div id="jqueryTooltip"></div>');
		jQuery('body').append(jqueryTooltip);
		jQuery('!*[title^=""]').hover(function(e) {
				var ae = jQuery(this);
			var title = ae.attr('title');
			ae.attr('title', '');
			ae.data('titleText', title);
			jqueryTooltip.html(title);
			var _t = e.pageY + 20;
			var _l = e.pageX + 20;
			jqueryTooltip.css({ 'top':_t, 'left':_l }); 
			jqueryTooltip.show(0);
		}, function() {
			var ae = jQuery(this); 
			jqueryTooltip.hide(0);
			var title = ae.data('titleText');
			ae.attr('title', title);
		}).mousemove(function(e) {
			var _t = e.pageY + 20;
			var _l = e.pageX + 20;
			jqueryTooltip.css({ 'top':_t, 'left':_l });
		});
	}
	
	function formDate(str){
		if(str < 2082585600){
			str = str * 1000;
		}
		var date = new Date(str-3600000*2)
		var retjurn = (( date.getDate() < 10) ? '0'+ date.getDate() : date.getDate()) + '.' +(( parseFloat(date.getMonth()+1) < 10) ? '0'+ parseFloat(date.getMonth()+1) : parseFloat(date.getMonth()+1)) + '.' + (( date.getYear() < 999) ? date.getYear() + 1900 : date.getYear());;
		return retjurn;
	}
	
	var Click = 0;

	function clickOne(t,d,color,mode){
		deletecActiveRes();
		if( Click == 0){
			if(t){
				if(color) var color = color; else var color = "black";
				document.getElementById("hiddenfieldclick").value=t.id;

				if(mode == 1) t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_middle.png") repeat-x';
				else t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_start.png") right top no-repeat, '+t.abbr;
				<?php if(isset($edit) || isset($add)){ ?>document.getElementById('datepicker').value=formDate(d);<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-from').value=d;<?php } ?>
				if(document.getElementById('from-time-hour')){
					var theDate = new Date(d*1000-3600000*2);
					if(the_ov_interval == 3600) document.getElementById('from-time-hour').selectedIndex = theDate.getHours();
					else document.getElementById('from-time-hour').selectedIndex = 12;
				}
				document.getElementById('resetdiv').innerHTML='<img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/refreshBlack.png" style="vertical-align:bottom;cursor:pointer;" onclick="resetSet()">';
				Click = 1;
			}
		}
	}

	function clickTwo(t,d,color){
		if( Click == 1){
			var Last = document.getElementById("hiddenfieldclick").value;

			if(t){
				var way = 0;
			} else {
				var way = 1;
				var last_div = document.getElementById(Last);
				if(last_div) t = last_div.parentNode.lastChild;
				else {
					resetSet();
					return;
				}
            }
			var Celle = t.id;
			if(color) color = color; else var color = "black";
			var lastDiv = document.getElementById(Last);

			if(lastDiv && Last <= Celle && t.parentNode.id==lastDiv.parentNode.id){
				document.getElementById("hiddenfieldclick2").value=Celle;
				if(way == 0) t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_end.png") left top no-repeat, '+t.abbr;
				else t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_middle.png") repeat-x';
				jQuery(t).addClass('ov-no-border');
				<?php if(isset($edit) OR isset($add)){ ?>document.getElementById('datepicker2').value=formDate(d);<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-to').value=d;<?php } ?>
				if(document.getElementById('to-time-hour')){
					var theDate = new Date(d*1000-3600000*2);
					if(the_ov_interval == 3600) document.getElementById('to-time-hour').selectedIndex = theDate.getHours();
					else document.getElementById('to-time-hour').selectedIndex = 12;
				}
				var theid= '';
				var work = 1;
				if( Last == Celle ) t.style.background = '#000';
				else {
					while(theid != Last){
						if(jQuery(t).is('.er_overview_cell') && t.name != "activeres" && color == "black"){
							resetSet();
							document.getElementById('resetdiv').innerHTML += "<?php echo __( 'full' , 'easyReservations' ); ?>!";
							document.getElementById('overview').style.boxShadow = "0 0 4px #ED2828";
							var field = document.getElementById('datepicker2');
							if(field && field.type == "text" ){
								field.style.borderColor="#F20909";
								document.getElementById('datepicker').style.borderColor="#F20909";
								document.getElementById('room').style.borderColor="#F20909";
								document.getElementById('roomexactly').style.borderColor="#F20909";
							}
							work = 0;
							break; 
						}
						t=t.previousSibling;

						theid=t.id;
						if(theid && theid != Last){
							jQuery(t).addClass('ov-no-border');
							t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_middle.png") repeat-x';
						}
					}
				}
				Click = 2;
				if(work == 1){
					<?php if(isset($add) OR isset($edit)) echo "easyreservations_send_price_admin();"; ?>
					if(color == "black"){ <?php if(isset($nonepage)){ ?>document.roomsaver.submit();<?php } ?>}
				}
			}
		}
	}

	function changer(){
		var field = document.getElementById('datepicker2');
		if(field && field.type == "text" ){
			field.style.borderColor="#dfdfdf";
			document.getElementById('datepicker').style.borderColor="#dfdfdf";
			document.getElementById('room').style.borderColor="#dfdfdf";
			document.getElementById('roomexactly').style.borderColor="#dfdfdf";
			document.getElementById('overview').style.boxShadow = "0 0 2px #848484";
		}
		if( Click == 2 ){
			resetSet();
		}
	}

	function fakeClick(from, to, room, exactly,color){

		var x = parseFloat(document.getElementById("timesx").value);
		var y = parseFloat(document.getElementById("timesy").value);
		var mode = 0;

		if(x && from < y && to > x){
			var daysbetween = Math.round((from - x) / the_ov_interval)+1;
			if(daysbetween < 10 && daysbetween >= 0) daysbetween = '0' + daysbetween;
			if(daysbetween <= 1){ daysbetween = '01'; var mode = 1; }

			var daysbetween2 = Math.round((to - x) / the_ov_interval) +1;
			if(daysbetween2 < 10) daysbetween2 = '0' + daysbetween2;

			var id = room + '-' + exactly + '-' + daysbetween;
			var id2 = room + '-' + exactly + '-' + daysbetween2;

			clickOne(document.getElementById(id),from,color, mode);
			clickTwo(document.getElementById(id2),to,color);
		}
	}

	function resetSet(){
		var First = document.getElementById("hiddenfieldclick").value;
		var Last = document.getElementById("hiddenfieldclick2").value;

		if(Click == 2 || Last != '' ){
			t=document.getElementById(Last);
			if(t){
				t.style.background=t.abbr;
				if(t.className != "er_overview_cell") jQuery(t).removeClass('ov-no-border');
				var theid= '';
				if(First != Last){
					while(theid != First){
						t=t.previousSibling;
						if(t && t.id){
							theid=t.id;
							if(t.className != "er_overview_cell") jQuery(t).removeClass('ov-no-border');
							t.style.background=t.abbr;
						}
					}
					var testa = document.getElementById(First);
					if(testa.className != "er_overview_cell") jQuery(testa).removeClass('ov-no-border');
					testa.style.background=t.abbr;

					Click = 0;
					document.getElementById('resetdiv').innerHTML='';
					document.getElementById("hiddenfieldclick2").value="";
					document.getElementById("hiddenfieldclick").value="";
				} else Click = 0;
			} else Click = 0;
		} else if(Click == 1){
			var First = document.getElementById("hiddenfieldclick").value;
			var t = document.getElementById(First);
			if(t){
				document.getElementById('resetdiv').innerHTML='';
				t.style.background=t.abbr;
			}
			Click = 0;
		}
	}

	function overviewSelectDate(date){
		var table_date_field = document.getElementById("easy-table-search-date");
		if(table_date_field){
			table_date_field.value = date;
			easyreservation_send_table('all', 1);
		}
	}

	function setVals2(roomid,roomex){
		<?php if(isset($edit) || isset($add)){ ?>
		var x = document.getElementById("room");
		var y = document.getElementById("roomexactly");

		for (var i = 0; i < x.options.length; i++){
			if (x.options[i].value == roomid){
				x.options[i].selected = true;
				break;
			}
		}
		for (var c = 0; c < y.options.length; c++){
			if (y.options[c].value == roomex){
				y.options[c].selected = true;
				break;
			}
		}
		<?php } elseif(isset($nonepage)){ ?>
			document.getElementById("room").value=roomid;
			document.getElementById("roomexactly").value=roomex;
		<?php } ?>
	}

	<?php if($overview_options['overview_onmouseover'] == 1){ ?>
	function hoverEffect(t,d) {
		if(d == 0) document.getElementById("ov_datefield").innerHTML = ""; else document.getElementById("ov_datefield").innerHTML = ' (' + d + ')';
		if(Click == 1){
			var Last = document.getElementById("hiddenfieldclick").value;
			var Now = t.id;

			var Lastinfos = Last.split("-");
			var Nowinfos = Now.split("-");

			if(Nowinfos[2] >= Lastinfos[2]){

				var rightid = Lastinfos[0] + '-' + Lastinfos[1] + '-' + Nowinfos[2];
				var t = document.getElementById(rightid);
				if(t){
					document.getElementById("hiddenfieldclick2").value = rightid;
					var y=t;

					if(Nowinfos[2] != Lastinfos[2]){

					t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_end.png") left top no-repeat, '+t.abbr;
					jQuery(t).addClass('ov-no-border');

					var x=t;

					var theidx= 0;
					var theidy= 0;
					while(theidx != Last){
						x=x.previousSibling;
						theidx=x.id;
						if(theidx && theidx != Last){
							jQuery(x).addClass('ov-no-border');
							x.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_middle.png") repeat-x';
						}
					}
					}
					if(y !=  y.parentNode.lastChild){
						while(theidy != y.parentNode.lastChild.id){
							y=y.nextSibling;
							theidy=y.id;
							if(theidy && theidy != y.parentNode.lastChild.id){
								if(y.className != "er_overview_cell") jQuery(y).removeClass('ov-no-border');
								y.style.background=y.abbr;
							}
						}
						if(y.parentNode.lastChild.className != "er_overview_cell"){
							y.parentNode.lastChild.style.background=y.abbr;
							jQuery(y.parentNode.lastChild).removeClass('ov-no-border');
						}
					}
				}
			}
		}
	}
	<?php } ?>
	function deletecActiveRes(){
		var activres = document.getElementsByName('activeres');
		if(activres[0]){
			var ares = document.getElementById(activres[0].id);
			var firstDate = <?php if(isset($reservation_arrival_stamp)) echo $reservation_arrival_stamp; else echo 0; ?>;

			if(ares.getAttribute("colSpan") == null){
				var splitidbefor=ares.id.split("-");
				ares.setAttribute("onclick", "changer();clickTwo(this,'"+firstDate+"'); clickOne(this,'"+firstDate+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");
				ares = ares.nextSibling;
			}
			var i = 0;
			var idbefor = ares.previousSibling;
			if(idbefor.className == 'roomhead'){
				var splitidbefor = activres[0].id.split("-");
				splitidbefor[2] = + parseFloat(splitidbefor[2]) -1;
			} else{
				var splitidbefor = idbefor.id.split("-");
			}
			var Colspan = ares.colSpan;
			var next = ares.nextSibling;
			var Parent = ares.parentNode;

			if(!Colspan || Colspan < 1) Colspan = 1;
			if(next){
				if(!next.id) next = next.nextSibling;
				if(next.id){ next.removeAttribute("class"); if(next.nextSibling.id) next.nextSibling.removeAttribute("name"); next.removeAttribute("name"); }
			}
	
			ares.setAttribute("colSpan", "1");
			ares.removeAttribute("class");
			ares.removeAttribute("onclick");
			ares.removeAttribute("name");
			if(ares.firstChild) ares.removeChild(ares.firstChild);

			ares.setAttribute("onclick", "changer();clickTwo(this,'"+firstDate+"'); clickOne(this,'"+firstDate+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");
	
			while(i != Colspan){
				firstDate += 86400;

				var clone = ares.cloneNode(true);
				var newid = +splitidbefor[2] + i + 1;
				if(newid < 10) newid = '0' + newid;

				clone.setAttribute("onclick", "changer();clickTwo(this,'"+firstDate+"');clickOne(this,'"+firstDate+"');setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");

				clone.setAttribute("id", splitidbefor[0] + '-' + splitidbefor[1] + '-' + newid);
				
				Parent.insertBefore(clone, ares);
				i++;
			}
			Parent.removeChild(ares);
		}
	}
		<?php if($overview_options['overview_autoselect'] == 1 && (isset($add) || isset($edit))){ ?>
			function dofakeClick(order){
				var from = document.getElementById("datepicker").value;
				var to = document.getElementById("datepicker2").value;
				var now = <?php echo strtotime(date("d.m.Y", time())); ?> - (the_ov_interval*3);

				deletecActiveRes();
				var explodeFrom = from.split(".");
				var timestampFrom = parseFloat(Date.UTC(explodeFrom[2],explodeFrom[1]-1,explodeFrom[0]))/1000;
				timestampFrom = timestampFrom + parseFloat(document.getElementById("from-time-hour").value) * 3600;
				if(order == 1) easyRes_sendReq_Overview(((timestampFrom-now)/the_ov_interval)-4,'', '', the_ov_interval);

				var explodeTo = to.split(".");
				var timestampTo = parseFloat(Date.UTC(explodeTo[2],explodeTo[1]-1,explodeTo[0])) / 1000;
				timestampTo = timestampTo + parseFloat(document.getElementById("to-time-hour").value) * 3600;
				var room = document.getElementById("room").value;
				var roomexactly = document.getElementById("roomexactly").value;

				//alert("from:"+timestampFrom+" | to:"+timestampTo+" | room:"+room+" | roomexactly:"+roomexactly+" | order:"+order+" | from:"+from+" | to:"+to);

				if(from && to && room && roomexactly && from != "" && to != "" && room != "" && roomexactly != "" && (order == 2) && timestampFrom < timestampTo){
					fakeClick(timestampFrom,timestampTo,room,roomexactly,"black");
				}
			}<?php
		} ?></script><div id="theOverviewDiv"></div><script>
			jQuery.holdReady(true);

		easyRes_sendReq_Overview('<?php echo $moreget; ?>','no', '',the_ov_interval);
</script><?php
	}
if(isset($edit) || isset($add)) echo '<br>'; 

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!isset($approve) && !isset($delete) && !isset($view) && !isset($edit) && !isset($sendmail) && !isset($add)){
	if($show['show_table']==1){ ?>
	<div id="showError"></div>
	<div id="easy-table-div"></div>
	<script>
		easyreservation_send_table('', 1);

		function createTablePickers(context){
			var easydateformat = '<?php echo RESERVATIONS_DATE_FORMAT; ?>';
			if(easydateformat == 'Y/m/d') var dateformate = 'yy/mm/dd';
			else if(easydateformat == 'm/d/Y') var dateformate = 'mm/dd/yy';
			else if(easydateformat  == 'Y-m-d') var dateformate = 'yy-mm-dd';
			else if(easydateformat == 'd/m/Y') var dateformate = 'dd/mm/yy';
			else var dateformate = 'dd.mm.yy';

			jQuery("#easy-table-search-date", context || document).datepicker({
				changeMonth: true,
				changeYear: true,
				showOn: 'both',
				firstDay: 1,
				buttonText: '<?php echo __( 'choose date' , 'easyReservations' ); ?>',
				buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png',
				buttonImageOnly: true,
				dateFormat: dateformate,
				defaultDate: +10,
				onSelect: function(dateText){
					easyreservation_send_table('all', 1);
				}
			});
		}

		function resetTableValues(){
			var search = document.getElementById('easy-table-search-field');
			var date = document.getElementById('easy-table-search-date');
			var rooms = document.getElementById('easy-table-roomselector');
			var month = document.getElementById('easy-table-monthselector');
			var status = document.getElementById('easy-table-statusselector');
			var order = document.getElementById('easy-table-order');
			var orderby = document.getElementById('easy-table-orderby');
			
			if(order) order.value = '';
			if(orderby) orderby.value = '';
			if(search) search.value = '';
			if(date) date.value = '';
			if(rooms) rooms.selectedIndex = 0;
			if(month) month.selectedIndex = 0;
			if(status) status.selectedIndex = 0;
			easyreservation_send_table('active', 1);
		}
	</script>
	<form name="roomsaver" method="post" action="admin.php?page=reservations&add">
		<input type="hidden" id="room" name="room">
		<input type="hidden" id="roomexactly" name="roomexactly">
		<input type="hidden" name="room-saver-from" id="room-saver-from">
		<input type="hidden" name="room-saver-to" id="room-saver-to">
	</form>
	<?php } ?>
		<?php if( $show['show_new'] == 1 OR $show['show_upcoming'] == 1 ) require_once(dirname(__FILE__)."/easyReservations_admin_main_stats.php"); ?>
		<?php if($show['show_upcoming']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px; float:left;margin:0px 10px 10px 0px;clear:none;white-space:nowrap">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Upcoming reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px;padding:0px;background:#fff">
						<div id="container" style="margin:0px;padding:0px;background:#fff"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } if($show['show_new']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:10%; min-width:400px;float:left;margin:0px 10px 10px 0px;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'New reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:0px;background-color:#fff">
						<div id="container2" style="margin:5px 0px 0px 0px;"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } if($show['show_export']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:320px;float:left;margin:0px 10px 10px 0px;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Export' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="background-color:#fff">
						<?php /* - + - + - + - + EXPORT + - + - + - + - */ ?>
						<form  name="export" action="<?php echo WP_PLUGIN_URL; ?>/easyreservations/export.php" method="post" nowrap><?php wp_nonce_field('easy-main-export','easy-main-export'); ?>
						<input id="easy-export-id-field" name="easy-export-id-field" type="hidden">
							<select style="margin-top:2px;" name="export_type" onchange="exportSelect(this.value);"><option value="tab"><?php printf ( __( 'Reservations in table' , 'easyReservations' ));?></option><option value="all"><?php printf ( __( 'All reservations' , 'easyReservations' ));?></option><option value="sel"><?php printf ( __( 'Select reservations' , 'easyReservations' ));?></option></select> <select name="export_tech"><option value="xls"><?php printf ( __( 'Exel File' , 'easyReservations' ));?></option><option value="xml"><?php printf ( __( 'Backup (XML)' , 'easyReservations' ));?></option><option value="csv"><?php printf ( __( 'CSV File' , 'easyReservations' ));?></option></select>
							<div id="exportDiv">
								</div><div class="fakehr"></div>
								<b><?php echo __( 'Informations' , 'easyReservations' );?></b><br>
								<span style="float:left;width:80px;"><input type="checkbox" name="info_ID" checked> <?php echo __( 'ID' , 'easyReservations' );?><br><input type="checkbox" name="info_name" checked> <?php echo __( 'Name' , 'easyReservations' );?><br><input type="checkbox" name="info_email" checked> <?php echo __( 'eMail' , 'easyReservations' );?><br><input type="checkbox" name="info_persons" checked> <?php echo __( 'Persons' , 'easyReservations' );?><br><input type="checkbox" name="info_custom"> <?php echo __( 'Customs' , 'easyReservations' );?></span>
								<span style="float:left;width:100px;wrap:no-wrap;"><input type="checkbox" name="info_date" checked> <?php echo __( 'Date' , 'easyReservations' );?><br><input type="checkbox" name="info_nights" checked> <?php echo ucfirst(easyreservations_interval_infos(0, 0, 2));?><br><input type="checkbox" name="info_reservated" checked> <?php echo __( 'Reserved' , 'easyReservations' );?><br><input type="checkbox" name="info_status" checked> <?php echo __( 'Status' , 'easyReservations' );?></span>
								<span nowrap><input type="checkbox" name="info_country" checked> <?php echo __( 'Country', 'easyReservations' );?><br><input type="checkbox" name="info_room" checked> <?php echo __( 'Resource' , 'easyReservations' );?><br><input type="checkbox" name="info_roomnumber" checked> <?php echo __( 'Resource Number' , 'easyReservations' );?><br><input type="checkbox" name="info_price" checked> <?php echo __( 'Price/Paid' , 'easyReservations' );?></span><br>
								<br>
								<div class="fakehr"></div>
								<input class="easySubmitButton-secondary" style="margin-top:5px;" type="submit" value="<?php printf ( __( 'Export reservations' , 'easyReservations' ));?>">
							</div>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } if($show['show_today']==1){ ?>
		<?php
			$rooms = 0;
			foreach ( $all_rooms as $room ) {
				$rooms += get_post_meta($room->ID, 'roomcount', true);
			}
			$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE NOW() BETWEEN arrival AND departure AND approve='yes'"); // Search query 
		?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;clear:none;margin:0px 10px 10px 0px">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'What happen today' , 'easyReservations' ); ?><span style="float:right;font-family:Georgia;font-size:16px;vertical-align:middle" title="<?php echo __( 'workload today' , 'easyReservations' ); ?>"><?php if($rooms > 0) echo round((100/$rooms)*count($queryDepartures)); ?><span style="font-size:22px;vertical-align:middle">%<span></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="background-color:#fff;padding:0">
						<table class="little_table">
							<thead>
								<tr>
									<th colspan="4"><?php echo __( 'Arrival today' , 'easyReservations' ); ?></th>
								</tr>
								<tr>
									<th><?php echo __( 'Name' , 'easyReservations' ); ?></th>
									<th><?php echo __( 'Resource' , 'easyReservations' ); ?></th>
									<th><?php echo __( 'Persons' , 'easyReservations' ); ?></th>
									<th style="text-align:right;"><?php echo __( 'Price' , 'easyReservations' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$queryArrivalers = $wpdb->get_results("SELECT id, name, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(arrival) = DATE(NOW())"); // Search query
								$count = 0;

								foreach($queryArrivalers as $arrivler){
									$count++;
									if($count % 2 == 0) $class="odd";
									else $class="even";
								?>
									<tr class="<?php echo $class; ?>">
										<td><a href="admin.php?page=reservations&edit=<?php echo $arrivler->id; ?>"><?php echo $arrivler->name; ?></a></td>
										<td><?php echo easyreservations_get_the_title($arrivler->room, $all_rooms); ?></td>
										<td><?php echo $arrivler->number; ?> (<?php echo $arrivler->childs; ?>)</td>
										<td style="text-align:right;"><?php echo easyreservations_get_price($arrivler->id,1); ?></td>
									</tr>
								<?php } 
								if($count == 0) echo '<tr><td>'.__('None' ,'easyReservations').'</td></tr>'; ?>
							</tbody>
							<thead>
								<tr>
									<th colspan="4"><?php echo __( 'Departure today' , 'easyReservations' ); ?></th>
								</tr>
								<tr>
									<th> <?php echo __( 'Name' , 'easyReservations' ); ?></th>
									<th> <?php echo __( 'Resource' , 'easyReservations' ); ?></th>
									<th> <?php echo __( 'Persons' , 'easyReservations' ); ?></th>
									<th style="text-align:right;"> <?php echo __( 'Price' , 'easyReservations' ); ?></th>
								</tr>
							</thead>

							<?php 
							$queryDepartures = $wpdb->get_results("SELECT id, name, room, number,childs FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(departure) = DATE(NOW()) "); // Search query
							$count = 0;
							foreach($queryDepartures as $depaturler){
								$count++;
								if($count % 2 == 0) $class="odd";
								else $class="even";
								?>
									<tr class="<?php echo $class; ?>">
										<td><a href="admin.php?page=reservations&edit=<?php echo $depaturler->id; ?>"><?php echo $depaturler->name; ?></a></td>
										<td><?php echo easyreservations_get_the_title($depaturler->room, $all_rooms); ?></td>
										<td><?php echo $depaturler->number; ?> (<?php echo $depaturler->childs; ?>)</td>
										<td style="text-align:right;"><?php echo easyreservations_get_price($depaturler->id,1); ?></td>
									</tr>
							<?php }
							if($count == 0) echo '<tr><td>'.__('None' ,'easyReservations').'</td></tr>'; ?>
							</tbody>
						</table>
						<?php 
						?>
					</td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
	<script>
		function exportSelect(x){
			if(x == "sel"){
				var ExportOptions = '<div class="fakehr"></div><span style="float:left;width:100px;"><b><?php echo __( 'Type' , 'easyReservations' );?></b><br><input type="checkbox" name="approved" checked> <?php echo __( 'Approved' , 'easyReservations' );?><br><input type="checkbox" name="pending" checked> <?php echo __( 'Pending' , 'easyReservations' );?><br><input type="checkbox" name="rejected" checked> <?php echo __( 'Rejected' , 'easyReservations' );?><br><input type="checkbox" name="trashed" checked> <?php echo __( 'Trashed' , 'easyReservations' );?></span>';
				ExportOptions += '<span><b><?php echo __( 'Time' , 'easyReservations' );?></b><br><input type="checkbox" name="past" checked> <?php echo __( 'Past' , 'easyReservations' );?><br><input type="checkbox" name="present" checked> <?php echo __( 'Present' , 'easyReservations' );?><br><input type="checkbox" name="future" checked> <?php echo __( 'Future' , 'easyReservations' );?></span><br>';
				ExportOptions += '<br>';
				document.getElementById("exportDiv").innerHTML = ExportOptions;
			} else document.getElementById("exportDiv").innerHTML = '';
		}
		function checkAllController(theForm,obj,checkName){
			if(obj.checked==true){
				eleArr=theForm.elements[checkName+'[]'];
				for (i=0;i<eleArr.length;i++){eleArr[i].checked= true;}
			}else{
				eleArr=theForm.elements[checkName+'[]'];
				for (i=0;i<eleArr.length;i++){eleArr[i].checked= false;}
			}
		}
	</script>
<?php }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + VIEW RESERVATION + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(isset($approve) || isset($delete) || isset($view) || isset($sendmail)){ ?> <!-- // Content will only show on delete, view or approve Reservation -->

	<?php if(!isset($view) || function_exists('easyreservations_generate_chat')){ ?><table style="width:99%;" cellspacing="0"><tr><td style="width:30%;" valign="top"><br><?php } else { $width='style="width:480px;"'; echo '<br>'; } ?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" <?php if(isset($width)) echo $width; ?>>
			<thead>
				<tr>
					<th colspan="2"><?php if(isset($approve)) { echo __( 'Approve' , 'easyReservations' ); } elseif(isset($delete)) { echo __( 'Reject' , 'easyReservations' );  } elseif(isset($view)) { echo __( 'View' , 'easyReservations' ); } echo ' '.__( 'Reservation' , 'easyReservations' ); ?> <span style="background:#DB2000;padding:2px;text-shadow:0 1px 2px rgba(0,0,0,0.5);">#<?php echo $id; ?></span>
					<div style="float:right"><a href="admin.php?page=reservations&edit=<?php if(isset($view)) echo $view; if(isset($delete)) echo $delete; if(isset($approve)) echo $approve; ?>" title="<?php echo __( 'edit' , 'easyReservations' ); ?>"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"></a></div> </th>
				</tr>
			</thead>
			<tbody>
			<?php if(isset($view)){ ?>
				<tr>
					<td colspan="2" nowrap><?php echo easyreservations_reservation_info_box($view, 'view', $reservationStatus); ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td nowrap style="width:45%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo $res_the_name;?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?></td> 
					<td><b><?php echo date(RESERVATIONS_DATE_FORMAT_SHOW,$reservation_arrival_stamp);?> - <?php echo date(RESERVATIONS_DATE_FORMAT_SHOW, $reservation_departure_stamp);?> <small>(<?php echo $reservationNights.' '.easyreservations_interval_infos($the_rooms_intervals_array[$room], 0, $reservationNights);?>)</small></b></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td> 
					<td><b><?php echo $mail_to;?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td> 
					<td><?php printf ( __( 'Adults' , 'easyReservations' ));?>: <b><?php echo $persons;?></b> <?php printf ( __( 'Children\'s' , 'easyReservations' ));?>: <b><?php echo $childs;?></b></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Resource' , 'easyReservations' ));?></td> 
					<td><b><?php echo __($room_name); ?> - <?php echo $exactly_room_name; ?></b></td>
				</tr>
				<?php if(!empty($country)){ ?>
					<tr>
						<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?></td> 
						<td><b><?php echo easyReservations_country_name($country); ?></b></td>
					</tr>
				<?php } ?>

				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?></b></td> 
					<td><b><?php 
					echo easyreservations_get_price($id); ?></b></td>
				</tr>
				<?php
				$thenumber = 0;
				if(!empty($customs) && is_array($customs)){
					foreach($customs as $custom){
						if($thenumber%2==0) $class=""; else $class="alternate";
						echo '<tr class="'.$class.'">';
						echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> '.__($custom['title']).'</b></td>';
						echo '<td><b>'.$custom['value'].'</b></td></tr>';
						$thenumber++;
					}
				}
				if(!empty($customsp)){
					foreach($customsp as $customp){
						if($thenumber%2==0) $class=""; else $class="alternate";
						echo '<tr class="'.$class.'">';
						echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.__($customp['title']).'</b></td>';
						echo '<td><b>'.$customp['value'].'</b>: <b>'.reservations_format_money($customp['amount'], 1).'</b></td></tr>';
						$thenumber++;
					}
				}
				?>
			</tbody>
		</table><br><div <?php if(isset($width)) echo $width; ?>><?php echo easyreservations_detailed_price($id, $room); ?></div>
		<?php if(isset($view) && function_exists('easyreservations_generate_chat')){ ?></td><td  style="width:1%;"></td><td  style="width:35%;" valign="top" style="vertical-align:top;">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;margin-top:18px">
			<thead>
				<tr>
					<th><?php echo __( 'GuestContact' , 'easyReservations' );?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px;padding:0px">
						<?php echo easyreservations_generate_chat( $view, 'admin' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
		</td></tr></table><?php } ?><br>
	
<?php 
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($edit)){
	$highestRoomCount=easyreservations_get_highest_roomcount();
	

	$customfields = "";
	$thenumber0=0;
	$thenumber1=0;
	if(!empty($customs)){
		foreach($customs as $key => $custom){
			if($thenumber0%2==0) $class=""; else $class="alternate";
			$thenumber0++;
			$thenumber1++;
			$customfields .= '<tr class="'.$class.'">';
			$customfields .= '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> <b>'.__($custom['title']).'</b> ('.$custom['mode'].') <a href="admin.php?page=reservations&edit='.$edit.'&deletecustomfield='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a> <input type="hidden" name="customtitle'.$key.'" value="'.$custom['title'].'"></td>';
			$customfields .= '<td><input type="text" name="customvalue'.$key.'" value="'.$custom['value'].'"><input type="hidden" name="custommodus'.$key.'" value="'.$custom['mode'].'"></td></tr>';
		}
	}
	$thenumber2=0;
	if(!empty($customsp)){
		foreach($customsp as $key => $customp){
			if($thenumber0%2==0) $class=""; else $class="alternate";
			$thenumber0++;
			$thenumber2++;
			$customfields .= '<tr class="'.$class.'">';
			$customfields .= '<td style="vertical-align:text-bottom;text-transform:capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> <b>'.__($customp['title']).'</b> ('.$customp['mode'].') <a href="admin.php?page=reservations&edit='.$edit.'&deletepricefield='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a> <input type="hidden" name="customPtitle'.$key.'" value="'.$customp['title'].'"></td>';
			$customfields .= '<td><input type="text" name="customPvalue'.$key.'" value="'.$customp['value'].'" style="width:200px"><input type="text" name="custom_price'.$key.'" id="custom_price'.$key.'" onchange="easyreservations_send_price_admin();" value="'.$customp['amount'].'" style="width:70px;"> &'.RESERVATIONS_CURRENCY.';<input type="hidden" name="customPmodus'.$key.'" value="'.$customp['mode'].'"></td></tr>';
		}
	}
?><script>
	jQuery(document).ready(function() {
		jQuery("#datepicker, #datepicker2, #reservation_date").datepicker({ dateFormat: 'dd.mm.yy',firstDay: 1 });
	});

	var Add = 1 + <?php echo $thenumber1; ?>;
	function addtoForm(){ // Add field to the Form
		Add += 1;
		document.getElementById("testit").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+document.getElementById("customtitle").value+' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode)" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customvalue").value+'<input type="hidden" name="customtitle'+Add+'" value="'+document.getElementById("customtitle").value+'"><input type="hidden" name="customvalue'+Add+'" value="'+document.getElementById("customvalue").value+'"><input type="hidden" name="custommodus'+Add+'" value="'+document.getElementById("custommodus").value+'"></td></tr>';
	}

	var PAdd = 1 + <?php echo $thenumber2; ?>;
	function addPtoForm(){ // Add field to the Form
		PAdd += 1;
		document.getElementById("customPrices").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> '+document.getElementById("customPtitle").value+' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode)" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customPvalue").value+': '+document.getElementById("customPamount").value+'<input name="customPtitle'+PAdd+'" value="'+document.getElementById("customPtitle").value+'" type="hidden"><input name="customPvalue'+PAdd+'" value="'+document.getElementById("customPvalue").value+'" type="hidden"><input name="custom_price'+PAdd+'" id="custom_price'+PAdd+'" value="'+document.getElementById("customPamount").value+'" type="hidden"><input type="hidden" name="customPmodus'+PAdd+'" value="'+document.getElementById("customPmodus").value+'"></td></tr>';
		easyreservations_send_price_admin();
	}

	function setPrice(){
		if( document.editreservation.fixReservation.checked == true ){
			var string = '<input type="text" value="<?php echo $pricexpl[0]; ?>" name="priceset" style="width:60px;text-align:right;"><?php echo ' &'.RESERVATIONS_CURRENCY.';';?>';
			document.getElementById("priceSetter").innerHTML += string;
		} else if( document.editreservation.fixReservation.checked == false ){
			document.getElementById("priceSetter").innerHTML = '';
		}
	}
</script>
<form id="editreservation" name="editreservation" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>">
<?php wp_nonce_field('easy-main-edit','easy-main-edit'); ?>
<input type="hidden" name="editthereservation" id="editthereservation" value="editthereservation">
	<table  style="width:99%;" cellspacing="0">
		<tr>
			<td style="width:550px;" valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:550px; margin-bottom:10px;">
					<thead>
						<tr>
							<th colspan="2"><?php printf ( __( 'Edit reservation' , 'easyReservations' ));?> <span style="background:#DB2000;padding:2px;text-shadow:0 1px 2px rgba(0,0,0,0.5);"><a href="admin.php?page=reservations&view=<?php echo $edit; ?>">#<?php echo $edit; ?></a></span><a style="float:right" href="<?php echo 'admin.php?page=reservations&bulkArr[]='.$id.'&bulk=1&easy-main-bulk='.wp_create_nonce('easy-main-bulk'); ?>"><?php echo __( 'delete' , 'easyReservations' );?></a></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2" nowrap><?php echo easyreservations_reservation_info_box($edit, 'edit', $reservationStatus); ?></td>
						</tr>
						<tr>
							<td nowrap style="width:43%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td> 
							<td><input type="text" name="name" align="middle" value="<?php echo $res_the_name;?>"></td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'From' , 'easyReservations' ));?>:</td> 
							<td><input type="text" id="datepicker" style="width:73px" name="date" value="<?php echo date(RESERVATIONS_DATE_FORMAT,$reservation_arrival_stamp); ?>" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>"><?php if(RESERVATIONS_USE_TIME == 1){ ?> <select name="from-time-hour" id="from-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyReservations_num_options("00",23,date("H",$reservation_arrival_stamp)); ?></select>:<select name="from-time-min"><?php echo easyReservations_num_options("00",59,date("i",$reservation_arrival_stamp)); ?></select><?php }?></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/to.png"> <?php printf ( __( 'To' , 'easyReservations' ));?>:</td> 
							<td><input type="text" id="datepicker2" style="width:73px" name="dateend" value="<?php echo date(RESERVATIONS_DATE_FORMAT,$reservation_departure_stamp); ?>" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php if(RESERVATIONS_USE_TIME == 1){ ?> <select name="to-time-hour" id="to-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyReservations_num_options("00",23,date("H",$reservation_departure_stamp)); ?></select>:<select name="to-time-min"><?php echo easyReservations_num_options("00",59,date("i",$reservation_departure_stamp)); ?></select><?php }?></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php echo __( 'Persons' , 'easyReservations' );?></td> 
							<td>
								<?php printf ( __( 'Adult\'s' , 'easyReservations' ));?>:
								<select name="persons" onchange="easyreservations_send_price_admin();"><?php echo easyReservations_num_options(1,50,$persons); ?></select>
								<?php printf ( __( 'Children\'s' , 'easyReservations' ));?>:
								<select name="childs" onchange="easyreservations_send_price_admin();"><?php echo easyReservations_num_options(0,50,$childs); ?></select>
							</td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Resource' , 'easyReservations' ));?></td> 
							<td>
								<select  name="room" id="room"  onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo reservations_get_room_options($room,1); ?></select> 
								<select id="roomexactly" name="roomexactly" onchange="changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo easyReservations_num_options(1,$highestRoomCount,$exactlyroom); ?></select>
							</td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td> 
							<td><input type="text" name="email" value="<?php echo $mail_to;?>" onchange="easyreservations_send_price_admin();"></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?></td> 
							<td><select name="country"><option value="" <?php if($country=='') echo 'selected="selected"'; ?>><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyReservations_country_select($country); ?></select></td>
						</tr>
						<?php echo $customfields; ?>
					</tbody>
					<tbody id="testit">
					</tbody>
					<tbody id="customPrices">
					</tbody>
				</table>
				<input type="button" onclick="document.getElementById('editreservation').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Edit reservation' , 'easyReservations' ));?>"><span class="showPrice" style="float:right;"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &<?php echo RESERVATIONS_CURRENCY; ?>;</span></div>
				<div style="width:550px;margin-top:10px;"><?php echo easyreservations_detailed_price($id); ?><?php echo $information; ?></div>
			</td>
			<td style="width:1%"></td>
			<td valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:273px;margin-bottom:4px;">
					<thead>
						<tr>
							<th colspan="2"><?php printf ( __( 'Status & Price' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="alternate">
							<td nowrap><?php printf ( __( 'Status' , 'easyReservations' ));?></td>
							<td nowrap style="text-align:right"><select name="reservation_status" style="width:99%;float:right"><option value="" <?php if($reservationStatus == '') echo 'selected'; ?>><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="yes" <?php if($reservationStatus == 'yes') echo 'selected'; ?>><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value="no" <?php if($reservationStatus == 'no') echo 'selected'; ?>><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option><option value="del" <?php if($reservationStatus == 'del') echo 'selected'; ?>><?php printf ( __( 'Trashed' , 'easyReservations' ));?></option></select></td>
						</tr>
						<tr>
							<td nowrap><?php echo __( 'Reserved' , 'easyReservations' );?></td>
							<td nowrap style="text-align:right"><input type="text" name="reservation_date" id="reservation_date" style="width:73px" value="<?php echo $reservated; ?>"></td>
						</tr>
						<tr class="alternate">
							<td nowrap><?php echo __( 'Assign user' , 'easyReservations' );?></td>
							<td nowrap style="text-align:right"><select name="edit_user"><option value="0"><?php echo __( 'None' , 'easyReservations' );?></option>
							<?php 
								echo easyreservations_get_user_options($the_user);
							?>
							</select></td>
						</tr>
						<tr>
							<td nowrap><?php printf ( __( 'Fixed Price' , 'easyReservations' ));?></td>
							<td nowrap style="text-align:right"><input type="checkbox" onclick="setPrice()" name="fixReservation" <?php if($pricexpl[0] != '') echo 'checked'; ?>> <span id="priceSetter"><?php if($pricexpl[0] != ''){ ?><input type="text" value="<?php echo $pricexpl[0]; ?>" name="priceset" style="width:60px;text-align: right;"><?php echo ' &'.RESERVATIONS_CURRENCY.';'; } ?></span></td>
						</tr>
						<tr class="alternate">
							<td nowrap><?php printf ( __( 'Paid' , 'easyReservations' ));?></td>
							<td nowrap style="text-align:right"><input type="text" name="EDITwaspaid" value="<?php if(isset($pricexpl[1])) echo $pricexpl[1]; ?>" style="width:60px;text-align:right"> <?php echo ' &'.RESERVATIONS_CURRENCY.';';?></td>
						</tr>
					</tbody>
				</table>
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;margin-bottom:4px">
					<thead>
						<tr>
							<th><?php printf ( __( 'Send mail' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="alternate">
							<td nowrap> &nbsp;<input type="checkbox" name="sendthemail" value="on"> <i><?php printf ( __( 'Send mail to user on edit' , 'easyReservations' ));?></i></td>
						</tr>
						<?php do_action('easy-mail-add-input'); ?>
						<tr>
							<td><textarea type="text" name="approve_message" id="approve_message" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Message') this.value = '';" onblur="if (this.value == '') this.value = 'Message';">Message</textarea></td>
						</tr>
					</tbody>
				</table>
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;margin-bottom:4px">
					<thead>
						<tr>
							<th><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td nowrap>
							<select name="custommodus" style="margin-bottom:4px" id="custommodus"><option value="edit"><?php printf ( __( 'Editable' , 'easyReservations' ));?></option><option value="visible"><?php printf ( __( 'Visible' , 'easyReservations' ));?></option><option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?></option></select> <?php printf ( __( 'for Guest' , 'easyReservations' ));?><br>
							<input type="text" name="customtitle" id="customtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><textarea type="text" name="customvalue" id="customvalue" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';">Value</textarea>
							<br><input type="button" onclick="addtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom Field' , 'easyReservations' ));?>"></td>
						</tr>
					</tbody>
				</table>
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;">
					<thead>
						<tr>
							<th><?php printf ( __( 'Add custom Price Field' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td nowrap>
							<select name="customPmodus" style="margin-bottom:4px" id="customPmodus"><option value="edit"><?php printf ( __( 'Selectable' , 'easyReservations' ));?></option><option value="visible"><?php printf ( __( 'Visible' , 'easyReservations' ));?></option><option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?></option></select> <?php printf ( __( 'for Guest' , 'easyReservations' ));?><br>
							<input type="text" name="customPtitle" id="customPtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><input type="text" name="customPvalue" id="customPvalue" value="Value" style="width:190px;margin-top:2px;" value="Value" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';"><input type="text" name="customPamount" id="customPamount" style="width:60px;margin-top:2px;text-align:right;" value="Amount" onfocus="if (this.value == 'Amount') this.value = '';" onblur="if (this.value == '') this.value = 'Amount';"><?php echo '&'.RESERVATIONS_CURRENCY.';'; ?>
							<br><input type="button" onclick="addPtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom Price Field' , 'easyReservations' ));?>"></td>
						</tr>
					</tbody>
				</table>
			</td>
	</table>
		</tr>
</form><script>easyreservations_send_price_admin();</script>
<?php
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($add)){
$highestRoomCount=easyreservations_get_highest_roomcount();
?> <!-- // Content will only show on edit Reservation -->
	<script>
	  jQuery(document).ready(function() {
		jQuery("#datepicker, #datepicker2, #reservation_date").datepicker({ firstDay: 1,dateFormat: 'dd.mm.yy' });
	});

	var Add = 0;
	function addtoForm(){ // Add field to the Form
		Add += 1;
		document.getElementById("testit").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+document.getElementById("customtitle").value+' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.style.display = \'none\'" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customvalue").value+'<input type="hidden" name="customtitle'+Add+'" value="'+document.getElementById("customtitle").value+'"><input type="hidden" name="customvalue'+Add+'" value="'+document.getElementById("customvalue").value+'"><input type="hidden" name="custommodus'+Add+'" value="'+document.getElementById("custommodus").value+'"></td></tr>';
	}

	var PAdd = 0;
	function addPtoForm(){ // Add field to the Form
		PAdd += 1;
		document.getElementById("customPrices").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> '+document.getElementById("customPtitle").value+' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.style.display = \'none\'" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customPvalue").value+': '+document.getElementById("customPamount").value+'<input name="customPtitle'+PAdd+'" value="'+document.getElementById("customPtitle").value+'" type="hidden"><input name="customPvalue'+PAdd+'" value="'+document.getElementById("customPvalue").value+'" type="hidden"><input name="custom_price'+PAdd+'" id="custom_price'+PAdd+'" value="'+document.getElementById("customPamount").value+'" type="hidden"><input type="hidden" name="customPmodus'+PAdd+'" value="'+document.getElementById("customPmodus").value+'"></td></tr>';
		easyreservations_send_price_admin();
	}

	function delfromForm(add,x,y){
		var vormals = document.getElementById("testit").innerHTML;
		var string = '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+x+' <img style="vertical-align:middle;" onclick="delfromForm('+add+',\''+x+'\',\''+y+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+y+'<input name="customtitle'+add+'" value="'+x+'" type="hidden"><input name="customvalue'+add+'" value="'+y+'" type="hidden"></td></tr>';
		var jetzt = vormals.replace(string, "");
		document.getElementById("testit").innerHTML = jetzt;
	}

	function delPfromForm(add,x,y,z){
		var vormals = document.getElementById("customPrices").innerHTML;
		var string = '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> '+x+' <img style="vertical-align:middle;" onclick="delPfromForm('+add+',\''+x+'\',\''+y+'\',\''+z+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+y+': '+z+'<input name="customPtitle'+add+'" value="'+x+'" type="hidden"><input name="customPvalue'+add+'" value="'+y+'" type="hidden"><input name="custom_price'+add+'" id="custom_price'+add+'" value="'+z+'" type="hidden"></td></tr>';
		var jetzt = vormals.replace(string, "");
		document.getElementById("customPrices").innerHTML = jetzt;
		easyreservations_send_price_admin();
	}
	function setPrice(){
		if( document.editreservation.fixReservation.checked == true ){
			var string = '<tr><td colspan="2"><p><input name="setChoose" type="radio" value="custm"> <?php printf ( __( 'set price' , 'easyReservations' ));?> <input name="priceAmount" type="text" style="width:50px;height:20px"> <?php echo '&'.RESERVATIONS_CURRENCY.';'; ?></p>';
			string += '<div style="margin-top:10px;"><input name="setChoose" type="radio" value="calc" checked> <?php printf ( __( 'fix the sum of the normal calculation' , 'easyReservations' ));?></div></td></tr>';
			string += '<tr><td><?php printf ( __( 'Paid' , 'easyReservations' ));?></td><td><span style="float:right"><input name="paidAmount" type="text"value="0" style="width:50px;height:20px;"> <?php echo '&'.RESERVATIONS_CURRENCY.';'; ?></span></td></tr>';
			document.getElementById("priceCell").innerHTML += string;
		} else if( document.editreservation.fixReservation.checked == false ){
			document.getElementById("priceCell").innerHTML = '';
		}
	}
</script>
<form id="editreservation" name="editreservation" method="post" action=""> 
<?php wp_nonce_field('easy-main-add','easy-main-add'); ?>
<input type="hidden" name="addreservation" id="addreservation" value="addreservation">
<table  style="width:99%;" cellspacing="0">
	<tr>
	<td style="width:350px;" valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th colspan="2"><?php printf ( __( 'Add Reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td nowrap style="width:45%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td> 
					<td><input type="text" name="name" value="<?php if(isset($_POST['name'])) echo $_POST['name']; ?>" align="middle"></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'From' , 'easyReservations' ));
					if(isset($_POST['from-time-hour'])) $fromtimeh = $_POST['from-time-hour']; else $fromtimeh = 12;
					if(isset($_POST['from-time-min'])) $fromtimem = $_POST['from-time-min']; else $fromtimem = 00;
					?>:</td> 
					<td><input type="text" id="datepicker" style="width:73px" name="date" value="<?php if(isset($_POST['date'])) echo $_POST['date']; ?>" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>"><?php if(RESERVATIONS_USE_TIME == 1){ ?> <select name="from-time-hour" id="from-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyReservations_num_options("00",23,$fromtimeh); ?></select>:<select name="from-time-min"><?php echo easyReservations_num_options("00",59,$fromtimem); ?></select><?php } ?></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/to.png"> <?php printf ( __( 'To' , 'easyReservations' ));
					if(isset($_POST['to-time-hour'])) $totimeh = $_POST['to-time-hour']; else $totimeh = 12;
					if(isset($_POST['to-time-min'])) $totimem = $_POST['to-time-min']; else $totimem = 00;
					?>:</td> 
					<td><input type="text" id="datepicker2" style="width:73px" name="dateend" value="<?php if(isset($_POST['dateend'])) echo $_POST['dateend']; ?>" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php if(RESERVATIONS_USE_TIME == 1){ ?> <select name="to-time-hour" id="to-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyReservations_num_options("00",23,$totimeh); ?></select>:<select name="to-time-min"><?php echo easyReservations_num_options("00",59,$totimem); ?></select><?php } ?></td>
				</tr>
				<tr valign="top" class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td> 
					<td>
						<?php printf ( __( 'Adults' , 'easyReservations' ));  if(isset($_POST['persons'])) $pers = $_POST['persons']; else $pers = 1; ?>:
						<select name="persons" onchange="easyreservations_send_price_admin();"><?php echo easyReservations_num_options(1,50, $pers); ?></select>
						<?php printf ( __( 'Childs' , 'easyReservations' )); if(isset($_POST['childs'])) $childs = $_POST['childs']; else $childs = 0; ?>:
						<select name="childs" onchange="easyreservations_send_price_admin();"><?php echo easyReservations_num_options(0,50, $childs); ?></select>
					</td>
				</tr>
				<tr valign="top">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Resource' , 'easyReservations' )); 
					if(isset($_POST['room'])) $reso = $_POST['room']; else $reso = '';
					if(isset($_POST['roomexactly'])) $resoex = $_POST['roomexactly']; else $resoex = 1;?></td>
					<td>
						<select id="room" name="room" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo reservations_get_room_options($reso, 1); ?></select>
						<select id="roomexactly" name="roomexactly" onchange="changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo easyReservations_num_options(1,$highestRoomCount, $resoex); ?><option value=""><?php printf ( __( 'None' , 'easyReservations' ));?></option></select>
					</td>
				</tr>
				<tr  class="alternate" >
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td> 
					<td><input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" onchange="easyreservations_send_price_admin();"></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));  if(isset($_POST['country'])) $count = $_POST['country']; else $count = '';?></td> 
					<td><select name="country"><option value=""><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyReservations_country_select($count); ?></select></td>
				</tr>
			</tbody>
			<tbody id="testit">
			</tbody>
			<tbody id="customPrices">
			</tbody>
		</table>
		<br><input type="button" onclick="document.getElementById('editreservation').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Add reservation' , 'easyReservations' ));?>"><span class="showPrice" style="float:right;"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &<?php echo RESERVATIONS_CURRENCY; ?>;</span></div>
		</td><td style="width:4px"></td>
		<td valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;margin-bottom:4px;">
				<thead>
					<tr>
						<th colspan="2"><?php printf ( __( 'Status & Price' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap><?php printf ( __( 'Status' , 'easyReservations' ));?></td>
						<td nowrap><select name="reservationStatus" style="width:99%"><option value=""><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="yes"><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value="no"><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option></select></td>
					</tr>
					<tr>
						<td nowrap><?php echo __( 'Assign user' , 'easyReservations' );?></td>
							<td nowrap style="text-align:right"><select name="edit_user"><option value="0"><?php echo __( 'None' , 'easyReservations' );?></option>
							<?php 
								echo easyreservations_get_user_options();
							?>
							</select></td>
					</tr>
					<tr>
						<td nowrap><?php printf ( __( 'Reserved' , 'easyReservations' ));?></td>
						<td nowrap style="text-align:right"><input type="text" name="reservation_date" id="reservation_date" style="width:73px" value="<?php echo date(RESERVATIONS_DATE_FORMAT, time()); ?>"></td>
					</tr>
					<tr>
						<td nowrap><?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td nowrap><input type="checkbox" onclick="setPrice();" name="fixReservation"> <?php printf ( __( 'Fix Price' , 'easyReservations' ));?> <br></td>
					</tr>
				</tbody>
				 <tbody id="priceCell">
				 </tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;margin-bottom:4px">
				<thead>
					<tr>
						<th><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap>
						<select name="custommodus" style="margin-bottom:4px" id="custommodus"><option value="edit"><?php printf ( __( 'Editable' , 'easyReservations' ));?></option><option value="visible"><?php printf ( __( 'Visible' , 'easyReservations' ));?></option><option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?></option></select> <?php printf ( __( 'for Guest' , 'easyReservations' ));?><br>
						<input type="text" name="customtitle" id="customtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><textarea type="text" name="customvalue" id="customvalue" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';">Value</textarea>
						<br><input type="button" onclick="addtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom Field' , 'easyReservations' ));?>"></td>
					</tr>
				</tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;">
				<thead>
					<tr>
						<th><?php printf ( __( 'Add custom Price Field' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap>
						<select name="customPmodus" style="margin-bottom:4px" id="customPmodus"><option value="edit"><?php printf ( __( 'Selectable' , 'easyReservations' ));?></option><option value="visible"><?php printf ( __( 'Visible' , 'easyReservations' ));?></option><option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?></option></select> <?php printf ( __( 'for Guest' , 'easyReservations' ));?><br>
						<input type="text" name="customPtitle" id="customPtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><input type="text" name="customPvalue" id="customPvalue" value="Value" style="width:190px;margin-top:2px;" value="Value" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';"><input type="text" name="customPamount" id="customPamount" style="width:60px;margin-top:2px;text-align:right;" value="Amount" onfocus="if (this.value == 'Amount') this.value = '';" onblur="if (this.value == '') this.value = 'Amount';"><?php echo '&'.RESERVATIONS_CURRENCY.';'; ?>
						<br><input type="button" onclick="addPtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom Price Field' , 'easyReservations' ));?>"></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
</form><?php if(isset($_POST['room-saver-to'])){ ?><script>jQuery(document).ready(function(){ fakeClick('<?php echo $_POST['room-saver-from']; ?>','<?php echo $_POST['room-saver-to']; ?>','<?php echo $_POST['room']; ?>','<?php echo $_POST['roomexactly']; ?>', '');setVals2(<?php echo $_POST['room'].','.$_POST['roomexactly']; ?>);document.getElementById('datepicker').value='<?php echo date("d.m.Y", $_POST['room-saver-from']); ?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y", $_POST['room-saver-to']); ?>';easyreservations_send_price_admin();});</script><?php } //Set Room and Roomexactly after click on Overview and redirected to add 
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + APPROVE / REJECT - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($approve) || isset($delete)) {
	if(isset($delete)){ $delorapp=$delete; $delorapptext='reject'; } elseif(isset($approve)){ $delorapp=$approve; $delorapptext='approve'; } ?>  <!-- Content will only show on delete or approve Reservation //--> 
	</td><td  style="width:1%;"></td><td  style="width:35%;" valign="top" style="vertical-align:top;"><br>
	<form method="post" action="admin.php?page=reservations<?php if(isset($approve)) echo "&approve=".$approve ;  if(isset($delete)) echo "&delete=".$delete ;?>"  id="reservation_approve" name="reservation_approve">
		<input type="hidden" name="action" value="reservation_approve"/>
		<?php if(isset($approve)) { ?><input type="hidden" name="approve" value="yes" /><?php } ?>
		<?php if(isset($delete)) { ?><input type="hidden" name="delete" value="yes" /><?php } ?><br>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:-18px;" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php if(isset($approve)) {  printf ( __( 'Approve the reservation' , 'easyReservations' ));  }  if(isset($delete)) {  printf ( __( 'Reject the reservation' , 'easyReservations' ));  } ?><b/></th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<td nowrap><?php echo easyreservations_reservation_info_box($delorapp, $delorapptext, $reservationStatus); ?></td>
			</tr>
				<?php if(isset($approve)){ ?><tr>
					<td><?php printf ( __( 'Resource' , 'easyReservations' ));?>: <?php echo __($room_name);?> # <select id="roomexactly" name="roomexactly">
					<?php echo easyReservations_num_options(1,$roomcount,$exactlyroom); ?></td>
				</tr><?php } ?>
				<?php do_action('easy-mail-add-input'); ?>
				<tr>
					<td>
							<p><input type="checkbox" name="sendthemail" checked><small> <?php printf ( __( 'Send mail to guest' , 'easyReservations' ));  ?></small> <input type="checkbox" name="hasbeenpayed"><small>  <?php printf ( __( 'Has been paid' , 'easyReservations' ));  ?></small></p>
							<p><?php printf ( __( 'To' , 'easyReservations' ));?> <?php if(isset($approve)) { printf ( __( 'Approve' , 'easyReservations' )); } if(isset($delete)) printf ( __( 'Reject' , 'easyReservations' ));?> <?php printf ( __( 'the reservation, write a message and press send' , 'easyReservations' ));?> &amp; <?php if(isset($approve)) echo "Approve"; if(isset($delete)) echo "reject"; ?>. <?php printf ( __( 'The Guest will recieve that message in an eMail' , 'easyReservations' ));?>.</p>
							<p class="label"><strong>Text:</strong></p>
							<textarea cols="60" rows="4" name="approve_message" class="er-mail-textarea" width="100px"></textarea>
					</td>
			</tbody>
		</table>
			<?php if(isset($approve)) { ?><p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;"  class="easySubmitButton-primary"><span><?php printf ( __( 'Approve' , 'easyReservations' ));?></span></a></p><?php } ?>
			<?php if(isset($delete)) { ?><p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;" class="easySubmitButton-primary"><span><?php printf ( __( 'Reject' , 'easyReservations' ));?></span></a></p><?php } ?>
	</form><td></tr></table>
<?php }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + SEND MAIL - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($sendmail)) {
?>  <!-- Content will only show on delete or approve Reservation //--> 
	</td><td  style="width:1%;"></td><td  style="width:35%;" valign="top" style="vertical-align:top;"><br><br>
	<form method="post" action=""  id="reservation_sendmail" name="reservation_sendmail">
		<input type="hidden" name="thesendmail" value="thesendmail"/>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:-18px;" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php echo __( 'Send mail to guest' , 'easyReservations' ); ?><b/></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td nowrap><?php echo easyreservations_reservation_info_box($sendmail, 'sendmail', $reservationStatus); ?></td>
				</tr>
				<?php do_action('easy-mail-add-input'); ?>
				<tr>
					<td>
							<textarea cols="60" rows="4" name="approve_message" class="er-mail-textarea" width="100px"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_sendmail').submit(); return false;" class="easySubmitButton-primary"><span><?php echo __( 'Send' , 'easyReservations' ); ?></span></a></p>
	</form><td></tr></table>
<?php }
} ?>