<?php
function reservation_main_page() {

$offer_cat = get_option("reservations_special_offer_cat");
$room_category =get_option("reservations_room_category");
if($offer_cat != ''){

	global $wpdb;
	include('pagination.class.php');

	if(isset($_POST['delete'])) $post_delete=$_POST['delete'];
	if(isset($_POST['roomexactly'])) $roomexactly=$_POST['roomexactly'];
	if(isset($_POST['approve_message'])) $approve_message=$_POST['approve_message'];
	if(isset($_POST['sendthemail'])) $sendthemail=$_POST['sendthemail'];
	if(isset($_POST['hasbeenpayed'])) $hasbeenpayed=$_POST['hasbeenpayed'];
	if(isset($_POST['approve'])) $post_approve=$_POST['approve'];
	if(isset($_POST['editthereservation'])) $editthereservation=$_POST['editthereservation'];
	if(isset($_POST['addreservation'])) $addreservation=$_POST['addreservation'];
	if(isset($_POST['daybutton'])) update_option("reservations_show_days",$_POST['daybutton']);

	if(isset($_GET['more'])){
		$moreget=$_GET['more'];
		$moregets=$_GET['more'];
	} else $moreget = 0;
	if(isset($_GET['perpage'])) {
		$perpage=$_GET['perpage'];
		update_option("reservations_on_page",$perpage);
	}
	if(isset($_GET['orderby'])) $orderby=$_GET['orderby'];
	if(isset($_GET['deletecustomfield'])) $deletecustomfield=$_GET['deletecustomfield'];
	if(isset($_GET['deletepricefield'])) $deletepricefield=$_GET['deletepricefield'];
	if(isset($_GET['sendmail'])) $sendmail=$_GET['sendmail'];
	if(isset($_GET['search'])) $search=$_GET['search'];
	if(isset($_GET['order'])) $order=$_GET['order'];
	if(isset($_GET['typ'])) $typ=$_GET['typ'];
	if(isset($_GET['approve'])) $approve=$_GET['approve'];
	if(isset($_GET['view']))  $view=$_GET['view'];
	if(isset($_GET['delete'])) $delete=$_GET['delete'];
	if(isset($_GET['edit'])) $edit=$_GET['edit'];
	if(isset($_GET['add'])) $add=$_GET['add'];
	if(isset($_POST['room-saver-from'])){
		$timestamp_timebetween=strtotime($_POST['room-saver-from'])-strtotime(date("d.m.Y", time())); // to show days before arrivaldate in Reservation Overview
		$moreget+=round($timestamp_timebetween/86400);
	}
	
	if(!isset($edit) AND !isset($view) AND !isset($add) AND !isset($approve) AND !isset($sendmail)  AND !isset($delete)){
		$nonepage = 0;
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + BULK ACTIONS (trash,delete,undo trash) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

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
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'moved to Trash' , 'easyReservations' ).'. <a href="admin.php?page=reservations&bulkArr[]='.$linkundo.'&bulk=2">'.__( 'Undo' , 'easyReservations' ).'</a></p></div>';

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
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'restored from the Trash' , 'easyReservations' ).'.</p></div>';

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
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;"  class="updated below-h2"><p>'.$anzahl.' '.__('deleted permanently', 'easyReservations').'</p></div>';
			}
		}
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE CUSTOM FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($deletecustomfield)){
		global $wpdb;
		$sql_customquerie = "SELECT custom FROM ".$wpdb->prefix ."reservations WHERE id='$edit' LIMIT 1";
		$customquerie = $wpdb->get_results($sql_customquerie );
		$explthecustom=explode("&;&", $customquerie[0]->custom);
		$countthem=0;
		$filterouts=array_values(array_filter($explthecustom)); //make array out of filters
		foreach($filterouts as $customs){
			$countthem++;
			if($countthem==$deletecustomfield) $theminzs = $customs.'&;&';
		}
		$finishedcustom=str_replace($theminzs, '', $customquerie[0]->custom);
		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$finishedcustom' WHERE id='$edit' "));
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Custom Field deleted' , 'easyReservations' ).'</p></div>';
	}

	if(isset($_POST['setPrice'])){
		echo $_POST['setPrice'];
	}
	
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE PRICE FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($deletepricefield)){
		global $wpdb;
		$sql_custompquerie = "SELECT customp FROM ".$wpdb->prefix ."reservations WHERE id='$edit' LIMIT 1";
		$custompquerie = $wpdb->get_results($sql_custompquerie);
		$explthecustomp=explode("&;&", $custompquerie[0]->customp);
		$countthemp=0;
		$filteroutx=array_values(array_filter($explthecustomp)); //make array out of custom fields
		foreach($filteroutx as $customps){
			$countthemp++;
			if($countthemp==$deletepricefield) $theminzps = $customps.'&;&';
		}
		$finishedcustomp=str_replace($theminzps, '', $custompquerie[0]->customp);
		$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET customp='$finishedcustomp' WHERE id='$edit' "));
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Custom Field deleted' , 'easyReservations' ).'</p></div>';
	}
	
	if(isset($_POST['showRooms'])){
		update_option("reservations_show_rooms",implode(",", $_POST['showRooms']));
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($editthereservation)){
		global $wpdb;

		$errors=0;
		$moneyerrors=0;
		$name=$_POST["name"];
		$date=$_POST["date"];
		$dateend=$_POST["dateend"];
		$email=$_POST["email"];
		$EDITroomex=$_POST["roomexactly"];
		$EDITroom=$_POST["room"];
		$note=$_POST["note"];
		$nights=$_POST["nights"];
		$persons=$_POST["persons"];
		$childs=$_POST["childs"];
		$country=$_POST["country"];
		$specialoffer=$_POST["offer"];
		$fixReservation = $_POST["fixReservation"];

		if(isset($_POST["priceset"])){
			if($_POST["priceset"]=='') $EDITpriceset=0;
			else $EDITpriceset=$_POST["priceset"];
		}
		if(isset($_POST["EDITwaspaid"])){
			if($_POST["EDITwaspaid"]=='') $EDITwaspaid=0;
			else $EDITwaspaid=$_POST["EDITwaspaid"];
		}
		$EDITreservationStatus=$_POST["reservationStatus"];
		$customfields="";
		$custompfields="";

		for($theCount = 1; $theCount < 16; $theCount++){
			if(isset($_POST["customvalue".$theCount]) AND isset($_POST["customtitle".$theCount])){
				$customfields.= $_POST["customtitle".$theCount].'&:&'.$_POST["customvalue".$theCount].'&;&';
			}
		}

		for($theCount = 1; $theCount < 16; $theCount++){
			if(isset($_POST["customPvalue".$theCount]) AND isset($_POST["customPtitle".$theCount])){
				if(easyreservations_check_price($_POST["custom_price".$theCount]) == 'error') $moneyerrors++;
				$custompfields.= $_POST["customPtitle".$theCount].'&:&'.$_POST["customPvalue".$theCount].':'.easyreservations_check_price($_POST["custom_price".$theCount]) .'&;&';
			}
		}

		$getprice=easyreservations_price_calculation($edit, '');
		if($fixReservation == "on" AND isset($EDITpriceset)){
			if(easyreservations_check_price($EDITpriceset) != 'error'){
				$theNewEditPrice = easyreservations_check_price($EDITpriceset);
			} else {
				$moneyerrors++;
			}
		} elseif($fixReservation == "off"){
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

		$timestampstartedit=strtotime($date);
		$timestampendedit=strtotime($dateend);
		$dat=date("Y-m", $timestampstartedit);
		$rightdate=date("Y-m-d", $timestampstartedit);
		$calcdaysbetween=round(($timestampendedit-$timestampstartedit)/60/60/24);

		if($EDITroomex > get_post_meta($EDITroom, 'roomcount', true)) $errors++;
		if($timestampstartedit > $timestampendedit) $errors++;

		if($errors > 0){
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="error below-h2"><p>'.__( 'Depature before arrival or roomcount too high' , 'easyReservations' ).'</p></div>';
		} elseif($moneyerrors > 0){
			$prompt='<div style="width: 97.6%; padding: 5px; margin: -11px 0 5px 0;" class="error below-h2"><p>'.__( 'Wrong money formatting' , 'easyReservations' ).'</p></div>';
		} elseif(reservations_check_room_availibility_exactly_all($date, $calcdaysbetween, $EDITroom, $EDITroomex, $edit) > 0){
			$prompt='<div style="width: 97.6%; padding: 5px; margin: -11px 0 5px 0;" class="error below-h2"><p>'.__( 'Selected Room is occupied at this date' , 'easyReservations' ).'</p></div>';
		} else {

			if(isset($sendthemail) AND $sendthemail=="on"){
			
				$checkSQLedit = "SELECT email, name, arrivalDate, nights, number, childs, country, room, special, approve, notes, custom FROM ".$wpdb->prefix ."reservations WHERE id='$edit'";
				$checkQuerry = $wpdb->get_results($checkSQLedit ); //or exit(__( 'Wrong ID or eMail' , 'easyReservations' ));

				$beforeArray = array( 'arrivalDate' => date("d.m.Y", strtotime($checkQuerry[0]->arrivalDate)), 'nights' => $checkQuerry[0]->nights, 'email' => $checkQuerry[0]->email, 'name' => $checkQuerry[0]->name, 'persons' => $checkQuerry[0]->number, 'childs' => $checkQuerry[0]->childs, 'room' => $checkQuerry[0]->room, 'offer' => $checkQuerry[0]->special, 'message' => $checkQuerry[0]->notes, 'custom' => $checkQuerry[0]->custom, 'country' => $checkQuerry[0]->country );
				$afterArray = array( 'arrivalDate' => date("d.m.Y", $timestampstartedit), 'nights' => $calcdaysbetween, 'email' => $email, 'name' => $name, 'persons' => $persons, 'childs' => $childs, 'room' =>  $EDITroom, 'offer' => $specialoffer, 'message' => $note, 'custom' => $customfields, 'country' => $country );

				$changelog = easyreservations_generate_res_Changelog($beforeArray, $afterArray);
			}

			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrivalDate='$rightdate', nights='$calcdaysbetween', name='$name', email='$email', notes='$note', room='$EDITroom', number='$persons', childs='$childs', special='$specialoffer', dat='$dat', roomnumber='$EDITroomex', price='$settepricei', custom='$customfields', customp='$custompfields', approve='$EDITreservationStatus', country='$country' WHERE id='$edit' "));

			if(isset($sendthemail) AND $sendthemail=="on"){
				if($specialoffer == 0) $theMailOffer = __( 'None' , 'easyReservations' );
				else $theMailOffer = get_the_title($specialoffer);
				$emailformation=get_option('reservations_email_to_user_admin_edited_msg');
				$subj=get_option("reservations_email_to_user_admin_edited_subj");
				easyreservations_send_mail($emailformation, $email, $subj, $approve_message, $edit, $timestampstartedit, $timestampendedit, $name, $email, $calcdaysbetween, $persons, $childs, $country, get_the_title($EDITroom), $theMailOffer, $customfields, easyreservations_get_price($edit), $note, $changelog);
			}

			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Reservation edited!' , 'easyReservations' ).'</p></div>';
		}
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($addreservation)){
		$errors=0;
		$ADDname=$_POST["name"];
		$ADDdate=$_POST["date"];
		$ADDdateend=$_POST["dateend"];
		$ADDemail=$_POST["email"];
		$ADDroomex=$_POST["roomexactly"];
		$ADDroom=$_POST["room"];
		$ADDnote=$_POST["note"];
		$ADDpersons=$_POST["persons"];
		$ADDchilds=$_POST["childs"];
		$ADDcountry=$_POST["country"];
		$ADDspecialoffer=$_POST["offer"];
		$ADDstatus=$_POST["reservationStatus"];
		$ADDcustomFields='';
		$ADDcustompFields='';

		$theInputPOSTs=array($_POST["date"], $_POST["name"], $_POST["email"], $_POST["room"], $_POST["dateend"], $_POST["persons"], $_POST["offer"]);

		foreach($theInputPOSTs as $input){
			if($input==''){ $errors++; }
		}

		for($theCount = 1; $theCount < 16; $theCount++){
			if(isset($_POST["customvalue".$theCount]) AND isset($_POST["customtitle".$theCount])){
				$customfields.= $_POST["customtitle".$theCount].'&:&'.$_POST["customvalue".$theCount].'&;&';
			}
		}

		for($theCount = 1; $theCount < 16; $theCount++){
			if(isset($_POST["customPvalue".$theCount]) AND isset($_POST["customPtitle".$theCount])){
				if(easyreservations_check_price($_POST["custom_price".$theCount]) == 'error') $moneyerrors++;
				$custompfields.= $_POST["customPtitle".$theCount].'&:&'.$_POST["customPvalue".$theCount].':'.easyreservations_check_price($_POST["custom_price".$theCount]) .'&;&';
			}
		}

		$ADDtimestampsanf=strtotime($ADDdate);
		$ADDtimestampsend=strtotime($ADDdateend);
		if($ADDroomex > get_post_meta($ADDroom, 'roomcount', true)) $errors++;
		if($ADDtimestampsanf > $ADDtimestampsend) $errors++;

		$ADDanznights=round(($ADDtimestampsend-$ADDtimestampsanf)/60/60/24);
		$ADDdat=date("Y-m", $ADDtimestampsanf);
		$ADDrightdate=date("Y-m-d", $ADDtimestampsanf);

		if($errors > 0){
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Please fill out all Fields' , 'easyReservations' ).'</p></div>';
		} elseif(reservations_check_room_availibility_exactly_all($ADDrightdate, $ADDanznights, $ADDroom, $ADDroomex, '') > 0){
			$prompt='<div style="width: 97.6%; padding: 5px; margin: -11px 0 5px 0;" class="error below-h2"><p>'.__( 'Selected Room is occupied at this Date' , 'easyReservations' ).'</p></div>';
		} elseif($moneyerrors > 0){
			$prompt='<div style="width: 97.6%; padding: 5px; margin: -11px 0 5px 0;" class="error below-h2"><p>'.__( 'Wrong money formatting' , 'easyReservations' ).'</p></div>';
		}else {

			$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(arrivalDate, name, email, notes, nights, dat, room, roomnumber, number, childs, country, special, approve, custom, customp, reservated ) 
			VALUES ('$ADDrightdate', '$ADDname', '$ADDemail', '$ADDnote', '$ADDanznights', '$ADDdat', '$ADDroom', '$ADDroomex', '$ADDpersons', '$ADDchilds', '$ADDcountry', '$ADDspecialoffer', '$ADDstatus', '$ADDcustomFields', '$ADDcustompFields', NOW() )"  ) ); 

			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Reservation added!' , 'easyReservations' ).'</p></div>';

			$newID = mysql_insert_id();

			if($_POST["fixReservation"] == "on"){
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
				else $prompt.='<br><div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Price couldnt be fixed, input wasnt money' , 'easyReservations' ).'</p></div>';
			}

			?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations&typ=pending"><?php
		}
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GET INFORMATIONS IF A RESERVATION IS CALLED DIRECTLY (view,edit,approve,reject,sendmail) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($approve)  || isset($delete) || isset($view) || isset($edit) || isset($sendmail)) { //Query of View Reject Edit Sendmail and Approve
		if(isset($edit)) $theIDofRes = $edit;
		elseif(isset($approve)) $theIDofRes = $approve;
		elseif(isset($view)) $theIDofRes = $view;
		elseif(isset($sendmail)) $theIDofRes = $sendmail;
		elseif(isset($delete)) $theIDofRes = $delete;

		$sql_approvequerie = "SELECT id, name, approve, nights, arrivalDate, room, special, roomnumber, number, childs, country, email, custom, customp, notes, price FROM ".$wpdb->prefix ."reservations WHERE id='$theIDofRes'";
		$approvequerie = $wpdb->get_results($sql_approvequerie );

		$id=$approvequerie[0]->id;
		$name=$approvequerie[0]->name;
		$reservationStatus=$approvequerie[0]->approve;
		$reservationFrom=$approvequerie[0]->nights;
		$reservationDate=$approvequerie[0]->arrivalDate;
		$room=$approvequerie[0]->room;
		$special=$approvequerie[0]->special;
		$exactlyroom=$approvequerie[0]->roomnumber;
		$persons=$approvequerie[0]->number;
		$childs=$approvequerie[0]->childs;
		$country=$approvequerie[0]->country;
		$mail_to=$approvequerie[0]->email;
		$customs=$approvequerie[0]->custom;
		$customsp=$approvequerie[0]->customp;
		$message_r=$approvequerie[0]->notes;

		$information='<small>'.__( 'This is how the price would get calculated now. After changing Filters/Groundprice/Settings or the reservations price it wont match the fixed price anymore.' , 'easyReservations' ).'</small>';
		$pricexpl=explode(";", $approvequerie[0]->price);
		if(isset($approve)  || isset($delete) || isset($view)) $roomwhere= $room; // For Overview only show date on view
		$roomsgetpost=get_post($room);
		$rooms=$roomsgetpost->post_title;

		if($special == 0) $specials="None";
		else {
			$specialgetpost=get_post($special);
			$specials=$specialgetpost->post_title;	
		}

		$timpstampanf=strtotime($reservationDate);
		$anznights=60*60*24*$reservationFrom;
		$timestampend=$anznights+$timpstampanf;

		$timestamp_timebetween=$timpstampanf-strtotime(date("d.m.Y", time()))-172800; // to show days before arrivaldate in Reservation Overview
		$moreget+=round($timestamp_timebetween/86400);
		if(isset($edit)) $edtlink='&edit='.$edit;
		elseif(isset($approve)) $edtlink='&approve='.$approve;
		elseif(isset($delete)) $edtlink='&delete='.$delete;
		elseif(isset($sendmail)) $edtlink='&sendmail='.$sendmail;
		elseif(isset($view)) $edtlink='&view='.$view;
	}

	if(isset($add)) $edtlink='&add';

	if(isset($sendmail) AND isset($_POST['thesendmail'])){
		$emailformation=get_option('reservations_email_sendmail_msg');
		$subj=get_option('reservations_email_sendmail_subj');

		easyreservations_send_mail($emailformation, $mail_to, $subj, $approve_message, $id, $timpstampanf, $timestampend, $name, $mail_to, $reservationFrom, $persons, $childs, $country, $rooms, $specials, $customs, easyreservations_get_price($approve), $message_r, '');
	}

	if(isset($post_approve) && $post_approve=="yes"){

		$pricearry = easyreservations_price_calculation($approve, '');
		if($hasbeenpayed=="on") $priceset2=$pricearry['price'].';1'; else $priceset2=$pricearry['price'].';0';

		if(reservations_check_room_availibility_exactly_all($reservationDate, $reservationFrom, $room, $roomexactly, $id) == 0){
			$priceset=str_replace(",", ".", $priceset2);
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes', roomnumber='$roomexactly', price='$priceset' WHERE id='$approve'"  ) ); 	

			if(isset($sendthemail) AND $sendthemail=="on"){
				$emailformation=get_option('reservations_email_to_userapp_msg');
				$subj=get_option("reservations_email_to_userapp_subj");
				easyreservations_send_mail($emailformation, $mail_to, $subj, $approve_message, $id, $timpstampanf, $timestampend, $name, $mail_to, $reservationFrom, $persons, $childs, $country, $rooms, $specials, $customs, easyreservations_get_price($approve), $message_r, '');
			}
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p> '.__( 'Reservation approved' , 'easyReservations' ).'</p></div>';
			?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
		}	else {	
			$prompt='<div style="width: 97.6%; padding: 5px; margin: -11px 0 5px 0;" class="error below-h2"><p>'.__( 'Selected Room is occupied at this Date' , 'easyReservations' ).'</p></div>';
		}
	}

	if(isset($post_delete) && $post_delete=="yes"){
		$pricearry = easyreservations_price_calculation($approve, '');
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='no' WHERE id=$delete"  ) ); 
		if(isset($sendthemail) AND $sendthemail=="on"){
			$emailformation=get_option('reservations_email_to_userdel_msg');
			$subj=get_option("reservations_email_to_userdel_subj");
			easyreservations_send_mail($emailformation, $mail_to, $subj, $approve_message, $id, $timpstampanf, $timestampend, $name, $mail_to, $reservationFrom, $persons, $childs, $country, $rooms, $specials, $customs, easyreservations_get_price($approve), $message_r, '');
		}
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'Reservation rejected' , 'easyReservations' ).'</p></div>';
		?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
	}
	if(isset($_POST['dayPicker'])){
		$dayPicker=$_POST['dayPicker'];

		$daysbetween=(strtotime($dayPicker)-strtotime(date("d.m.Y", time())))/86400;
		$moreget=$daysbetween+2;
		$moregets=$daysbetween+2;
	}

	$daysshow=get_option("reservations_show_days"); //How many Days to Show
	$reservations_on_page = get_option("reservations_on_page");
	$reservations_show_rooms = get_option("reservations_show_rooms");
	$roomArray = reservations_get_room_ids();

	$roomsChecks='<div id="myOnPageContent" style="display:none;height:200px;"><form name="showrooms" method="post"><b>'.__( 'Show Rooms' , 'easyReservations' ).'</b><br>';
	foreach($roomArray as $theNumber => $raum){

		if($reservations_show_rooms == ''){
			$checkED="checked";
		} elseif( substr_count($reservations_show_rooms, $raum[0]) > 0){
			$checkED="checked";
		} else {
			$checkED="";
		}
		$allRooms[] = $raum[0].',';
		$roomsChecks.='<input type="checkbox" name="showRooms['.$theNumber.']" value="'.$raum[0].'" '.$checkED.'> '.__($raum[1]).'<br>';

	}
	$roomsChecks.='<br><b>'.__( 'Show Days' , 'easyReservations' ).':</b> <input width="10" name="daybutton" value="'.$daysshow.'"> '.__( 'Days' , 'easyReservations' ).'<br><input type="submit" value="Set" class="easySubmitButton-secondary"></form></div>';

	echo $roomsChecks;

	if($reservations_show_rooms == ''){
		$RoomsArray=$allRooms;
	} else {
		$explodeShowingRooms = explode(",",$reservations_show_rooms);
		foreach($explodeShowingRooms as $ShowingRoom){
			$RoomsArray[] = $ShowingRoom;
		}
	}

	$show_overview_on_list = 1;

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
		$numberlaststart=((strtotime($reservationDate)+(86400*$reservationFrom))-$timesx)/86400+1;
		
		if($numberlaststart<10) $numberlaststart='0'.$numberlaststart;
		if($numberhighstart<10) $numberhighstart='0'.$numberhighstart;
	}

	$regular_guest_explodes = explode(",", str_replace(" ", "", get_option("reservations_regular_guests")));
	foreach( $regular_guest_explodes as $regular_guest){
		$regular_guest_array[]=$regular_guest;
	}
	
	if(!isset($moregets)) $moregets=0;
	if(!isset($moreget)) $moreget=0;
	
	if(RESERVATIONS_STYLE == 'widefat'){
		$ovBorderColor='#9E9E9E';
		$ovBorderStatus='dotted';
		$ovSettingsIcon='settings';
	} elseif(RESERVATIONS_STYLE == 'greyfat'){
		$ovBorderColor='#838383';
		$ovBorderStatus='dashed';
		$ovSettingsIcon='settingsTry';
	}
	if(isset($prompt)) echo ' '.$prompt;
	?>
<div class="easyReservationHeadline" >
	<span class="easyReservationHeadlineBox"><a href="admin.php?page=reservations"><?php echo __( 'Reservations' , 'easyReservations' );?></a> <a href="admin.php?page=reservations&add" rel="simple_overlay"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png"></a> </span> 
	<div style="float:right;font-size: 13px">
		<i><?php echo date("d. M Y H:i", time()); ?></i>
	</div>
</div>
<div id="wrap"><?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + OVERVIEW + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(($show_overview_on_list == 1) AND $room_category != 0){ ?>


	<input type="hidden" id="hiddenfieldclick" name="hiddenfieldclick">
	<input type="hidden" id="hiddenfieldclick2" name="hiddenfieldclick2">
	<table class="<?php echo RESERVATIONS_STYLE; ?> overview" cellspacing="0" cellpadding="0" id="overview" style="width:99%;" onmouseout="document.getElementById('ov_datefield').innerHTML = '';">
		<thead>
			<tr>
				<th colspan="<?php echo $daysshow+1; ?>"  class="overviewHeadline"><form id="pickForm" action="" name="pickForm" method="post" style="float:left;margin-top:2px;"><input name="dayPicker" id="dayPicker" type="hidden"></form> &nbsp;<b class="overviewDate"><?php echo $dateshow; ?></b><span id="ov_datefield"></span><form method="post" style="float:right"><a href="#TB_inline?height=50&width=280&inlineId=myOnPageContent" title="<?php printf ( __( 'Overview Settings' , 'easyReservations' ));?>" class="thickbox"><img style="vertical-align:text-bottom" src="<?php echo RESERVATIONS_IMAGES_DIR.'/'.$ovSettingsIcon; ?>.png"></a><input name="daybutton" class="easyButton"   value="10" type="submit"><input name="daybutton" class="easyButton" value="20" type="submit"><input name="daybutton" class="easyButton" value="30" type="submit"></form></th>
			</tr>
			<tr id="overviewTheadTr">
				<td style="width:126px;vertical-align:middle;text-align:center;font-size:18px;" class="h1overview">
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=<?php echo $moregets-($daysshow*3);?><?php if(isset($typ)) echo '&typ='.$typ; ?>" title="-90 <?php echo __( 'Days' , 'easyReservations' ); ?>"><b style="letter-spacing:-4px">&lsaquo; &lsaquo; &lsaquo; &nbsp;&nbsp;</b></a> 
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=<?php echo $moregets-($daysshow);?><?php if(isset($typ)) echo '&typ='.$typ; ?>" title="-30 <?php echo __( 'Days' , 'easyReservations' ); ?>"><b>&laquo;</b></a> 
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=<?php echo $moregets-10;?><?php if(isset($typ))echo '&typ='.$typ; ?>" title="-10 <?php echo __( 'Days' , 'easyReservations' ); ?>"><b>&lsaquo;</b></a> 
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=0<?php if(isset($typ)) echo '&typ='.$typ; ?>"  title="<?php echo __( 'Present' , 'easyReservations' ); ?>"><b>&omicron;</b></a> 
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=<?php echo $moregets+10;  if(isset($typ)) echo '&typ='.$typ; ?>" title="+10 <?php echo __( 'Days' , 'easyReservations' ); ?>"><b>&rsaquo;</b></a> 
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=<?php echo $moregets+$daysshow; if(isset($typ)) echo '&typ='.$typ; ?>" title="+30 <?php echo __( 'Days' , 'easyReservations' ); ?>"><b>&raquo;</b></a> 
					<a href="admin.php?page=reservations<?php if(isset($edtlink)) echo $edtlink; ?>&more=<?php echo $moregets+($daysshow*3); if(isset($typ)) echo '&typ='.$typ; ?>" title="+90 <?php echo __( 'Days' , 'easyReservations' ); ?>"><b style="letter-spacing:-4px">&rsaquo; &rsaquo; &rsaquo; &nbsp;&nbsp;</b></a>
				</td>
			<?php
				$s=$daysshow+$more;
				$co=0+$more;
				while($co < $s){
					$thedaydate=$timevariable+(86400*$co);
					if(isset($timpstampanf) AND $timpstampanf <= $thedaydate AND $thedaydate <= $timestampend) { $backgroundhighlight='backgroundhighlight'; } elseif(date("d.m.Y", $thedaydate) ==  date("d.m.Y", time())) { $backgroundhighlight='backgroundtoday'; } else { $backgroundhighlight='backgroundnormal'; }
					?>
					<td  class="<?php echo  $backgroundhighlight; ?> overviewDays" style="vertical-align:middle;min-width:23px">
						<?php echo date("j",$thedaydate); ?><br><?php echo substr(date("D",$thedaydate), 0 , 2); ?>
					</td>
					<?php $co++;
				} ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan="<?php echo $daysshow+1; ?>" class="overviewFooter"><span style="vertical-align:middle;" id="resetdiv"></span><span style="float:right;"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/blue_dot.png'; ?>">&nbsp;<small><?php echo __( 'Past Reservations' , 'easyReservations' ); ?></small> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/green_dot.png'; ?>">&nbsp;<small><?php echo __( 'Present Reservations' , 'easyReservations' ); ?></small> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/red_dot.png'; ?>">&nbsp;<small><?php echo __( 'Future Reservations' , 'easyReservations' ); ?></small><?php if(isset($id)){ ?> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/yellow_dot.png'; ?>">&nbsp;<small><?php echo __( 'Active Reservation' , 'easyReservations' ); ?></small><?php } ?></span></th>
			</tr>
		</tfoot>
		<tbody>
		<?php				
			if(isset($roomwhere)){
				$room_args = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'post_status' => 'publish|private', 'numberposts' => -1, 'post__in' => array($roomwhere));
				$roomcategories = get_posts( $room_args );
			} else {
				$room_args = array( 'type' => 'post', 'category' => $room_category, 'orderby' => 'post_title', 'order' => 'ASC', 'post_status' => 'publish|private', 'numberposts' => -1, 'post__in' => $RoomsArray);
				$roomcategories = get_posts( $room_args );
			}
			foreach( $roomcategories as $roomcategorie ){ /* - + - FOREACH ROOM - + - */
				$roomsIDentify=$roomcategorie->ID;
				$roomcounty=get_post_meta($roomsIDentify, 'roomcount', true);
				$rowcount=0;
				?>
				<tr style="background:#EAE8E8">
					<td style="border-bottom: 1px solid <?php echo $ovBorderColor; ?>;border-top: 1px solid <?php echo $ovBorderColor; ?>;"><span>&nbsp;<?php echo __( $roomcategorie->post_title); ?></td>
						<?php 	
						$s=$daysshow+$more;
						$co=0+$more;
						$countDaystoShow=0;
						while($co < $s){
							$roomDayPersons=get_post_meta($roomsIDentify, 'roomcount', true)-reservations_check_availibility_for_room($roomsIDentify, date("d.m.Y", $timesx+($countDaystoShow*86400)));
							if($roomDayPersons <= 0) $textcolor='#FF3B38'; else $textcolor='#118D18';
							?><td axis="<?php echo $countDaystoShow+2;?>" style="border-top:1px solid <?php echo $ovBorderColor; ?>;border-bottom:1px solid <?php echo $ovBorderColor; ?>;text-align:center;border-left: 1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;color:<?php echo $textcolor; ?>" ><small><?php echo $roomDayPersons; ?></small></td>
							<?php
							$countDaystoShow++; $co++;
						} ?></tr>
				<?php
				while($roomcounty > $rowcount){  /* - + - FOREACH EXACTLY ROOM - + - */
					$rowcount++;

					if($timesx < time()) $lastbackground='#2A78D8';
					else $lastbackground='#CC3333';
					if($rowcount == $roomcounty) $borderbottom=0;
					else $borderbottom=1;

					if(reservations_check_availibility_for_room_exactly($roomsIDentify, $rowcount,date("d.m.Y", time()))==0) $roombg='';
					else $roombg='overviewfullRoom';
				?>
					<tr id="room<?php echo $rowcount.'-'.$roomsIDentify; ?>">
						<td class="roomhead <?php echo $roombg; ?>" style="color:#8C8C8C;text-shadow:none;border-style:none;vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>;" onclick="<?php if(isset($edit)){ ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf); ?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationFrom*86400)); ?>';setVals2(<?php echo $roomsIDentify; ?>,<?php echo $rowcount; ?>);<?php } if(isset($edit) OR isset($approve)){ ?>changer();clickOne(document.getElementById('<?php echo $roomsIDentify.'-'.$rowcount.'-'.$numberhighstart; ?>'),'<?php echo date("d.m.Y", $timpstampanf); ?>', '#fff');clickTwo(document.getElementById('<?php echo $roomsIDentify.'-'.$rowcount.'-'.$numberlaststart; ?>'),'<?php echo date("d.m.Y", $timpstampanf+($reservationFrom*86400)); ?>', '#fff');<?php } if(isset($approve)){ ?>document.reservation_approve.roomexactly.selectedIndex=<?php echo $rowcount-1; ?>;<?php } ?>"  nowrap>&nbsp;#<?php if(strlen($rowcount) == 1) echo '0'; echo $rowcount; ?>
					</td><?php
					$sql_ResInRommAndDate = "SELECT id, name, nights, arrivalDate FROM ".$wpdb->prefix ."reservations WHERE room='$roomsIDentify' AND roomnumber='$rowcount' AND approve='yes' AND roomnumber != '' AND (arrivalDate BETWEEN '$stardate' AND '$enddate' OR DATE_ADD(arrivalDate, INTERVAL nights DAY) BETWEEN '$stardate' AND '$enddate' OR '$stardate'  BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)) ORDER BY arrivalDate ASC";
					$ResInRommAndDateResults = $wpdb->get_results($sql_ResInRommAndDate);
					$CoutResNights2=0; $CoutResNights3=0; $CountNumberOfAdd=0; $wasFull=0; $countdifferenz=0; $oldCountNumberOfAdd=0; $itIS=0; $cellcount=0;

					foreach($ResInRommAndDateResults as $reservation){
						$res_id=$reservation->id;
						$res_name=$reservation->name;
						$res_adate=$reservation->arrivalDate;
						$res_nights=$reservation->nights;
						for($CoutResNights=0; $CoutResNights <= $res_nights; $CoutResNights++){
							if($timesx < strtotime($res_adate)+(($CoutResNights*86400)+86400) AND $timesy+86400 > strtotime($res_adate)+($CoutResNights*86400)){
								$daysOccupied[]=date("d.m.Y", strtotime($res_adate)+(($CoutResNights-1)*86400)+86400+86400);
								$numberOccupied[]=$countdifferenz;
							}
						}
						$reservationarray[]=array( 'name' =>$res_name, 'ID' =>$res_id, 'nights' => $res_nights, 'arDate' => $res_adate );
						$countdifferenz++;
						$wasAroom=1;
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

						if($cellcount < 10){
							$preparedCellcount='0'.$cellcount;
						} else {
							$preparedCellcount=$cellcount;
						}

						if($dateToday < time()){
							$background2="url(".RESERVATIONS_IMAGES_DIR ."/patbg.png) repeat";
						} else $background2='';

						if(reservations_check_availibility_for_room_filter($roomsIDentify, date("d.m.Y", $dateToday-86400)) > 0){
							$colorbgfree='#FFEDED';
						} elseif(date("d.m.Y", $dateToday-86400)==date("d.m.Y", time())){
							$colorbgfree = '#EDF0FF';
						} elseif(date("N", $dateToday-86400)==6 OR date("N", $dateToday-86400)==7){
							$colorbgfree = '#FFFFEB';
						} else {
							$colorbgfree='#FFFFFF';
						}

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
								$lastDay=$daysOccupied[$CoutResNights3-1];
								if(isset($id) AND $reservationarray[$CountNumberOfAdd]['ID'] == $id){
									$farbe2=str_replace("DERSTRING", "yellow", $farbe2);
									$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
									$backgrosund='#FFE400';
								} elseif(strtotime($reservationarray[$CountNumberOfAdd]['arDate']) < time() AND strtotime($reservationarray[$CountNumberOfAdd]['arDate'])+(86400*$reservationarray[$CountNumberOfAdd]['nights']) > time()){
									$farbe2=str_replace("DERSTRING", "green", $farbe2);
									$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
									$backgrosund='#118D18';
								} elseif($reservationarray[$CountNumberOfAdd]['arDate'] > date("Y-m-d", time())){
									$farbe2=str_replace("DERSTRING", "red", $farbe2);
									$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
									$backgrosund='#CC3333';
								} else {
									$farbe2=str_replace("DERSTRING", "blue", $farbe2);
									$farbe2=str_replace("DERZEWEITESTRING", $lastbackground, $farbe2);
									$backgrosund='#2A78D8';
								}
								if($reservationarray[$CountNumberOfAdd]['arDate'] < date("Y-m-d", $timesx)){
									$daybetween=($timesx/86400)-(strtotime($reservationarray[$CountNumberOfAdd]['arDate'])/86400);
									$minusdays=round($daybetween)-1;
									$nightsproof=$reservationarray[$CountNumberOfAdd]['nights']-$minusdays;
								} elseif(strtotime($reservationarray[$CountNumberOfAdd]['arDate'])+(86400*$reservationarray[$CountNumberOfAdd]['nights']) > $timesy) {
									$daybetween=($timesy/86400)-((strtotime($reservationarray[$CountNumberOfAdd]['arDate'])+(86400*$reservationarray[$CountNumberOfAdd]['nights']))/86400);
									$minusdays=substr(round($daybetween), 1, 10);
									$nightsproof=$reservationarray[$CountNumberOfAdd]['nights']-$minusdays;
								} else {
									$minusdays=0;
									$nightsproof=$reservationarray[$CountNumberOfAdd]['nights'];
								}

								if($itIS==1){
									?><td id="<?php echo $roomsIDentify.'-'.$rowcount.'-'.$preparedCellcount; ?>" colspan="<?php echo $reservationarray[$CountNumberOfAdd]['nights']-1-$minusdays; ?>" class="er_overview_cell" onclick="<?php echo "location.href = 'admin.php?page=reservations&edit=".$reservationarray[$CountNumberOfAdd]['ID']."';"; ?>" style="border-style:none; background: <?php echo $farbe2;?>; color: #FFFFFF;cursor: pointer;text-decoration:none;height:22px;padding:0px;font: normal 11px Arial, sans-serif;vertical-align:middle;text-align:center; overflow:hidden;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left: <?php echo $borderside; ?>px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;"  abbr="<?php echo $farbe2;?>" title="<?php echo $reservationarray[$CountNumberOfAdd]['name']; ?>" onmouseover="hoverEffect(this,0);">
									<?php echo substr($reservationarray[$CountNumberOfAdd]['name'], 0, ($reservationarray[$CountNumberOfAdd]['nights']-1-$minusdays)*2); ?>
									</td><?php
								} elseif($itIS==$nightsproof+1 OR $itIS==$nightsproof OR $itIS==0) {
									?><td id="<?php echo $roomsIDentify.'-'.$rowcount.'-'.$preparedCellcount; ?>"<?php if($borderside == 0) echo ' class="er_overview_cell" ';?><?php if((isset($edit) OR isset($add) OR isset($nonepage)) AND $onClick==0){ ?>onclick="changer();clickTwo(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');clickOne(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');setVals2('<?php echo $roomsIDentify; ?>','<?php echo $rowcount; ?>');" <?php } elseif($onClick==1){ ?>onclick="<?php echo "location.href = 'admin.php?page=reservations&edit=".$reservationarray[$CountNumberOfAdd]['ID']."';"; ?>"<?php } ?> style="background: <?php echo $farbe2;?>; color: #FFFFFF;padding:0px; text-align:center;overflow:hidden; text-shadow:none; border-style:none; text-decoration:none; font: normal 11px Arial, sans-serif; vertical-align:middle;border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left:  <?php echo $borderside; ?>px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;<?php if($onClick==1){ ?>cursor: pointer;<?php } ?>" abbr="<?php echo $farbe2;?>" axis="<?php echo $cellcount+1; ?>" onmouseover="hoverEffect(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');">
									</td><?php
								}
								$lastbackground=$backgrosund;
								$wasFull=1;
								$oldName=$reservationarray[$CountNumberOfAdd]['name'];
								$oldCountNumberOfAdd=$CountNumberOfAdd;
							} else {
								if($wasFull == 1) $CountNumberOfAdd++;
								if(isset($id) AND $timpstampanf+86400 <= $dateToday AND $dateToday <= $timestampend+(86400*2)){
									$hoverclass='name="hoverclass'.$roomsIDentify.$rowcount.'"';
								} else { $hoverclass=""; }

								$CoutResNights2=0;
								?><td id="<?php echo $roomsIDentify.'-'.$rowcount.'-'.$preparedCellcount; ?>" <?php echo $hoverclass;?> <?php if(isset($edit) OR isset($add) OR isset($nonepage)){ ?>onclick="changer();clickTwo(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');clickOne(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');setVals2('<?php echo $roomsIDentify; ?>','<?php echo $rowcount; ?>');"<?php } ?> style=" border-style:none; border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left: 1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;background:<?php echo $background2.' '.$colorbgfree;?>" abbr="<?php echo $background2.' '.$colorbgfree;?>" onmouseover="hoverEffect(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');" axis="<?php echo $cellcount+1; ?>">
								<?php
								$wasFull=0;
							}
						} else {
								if(isset($id) AND $timpstampanf+86400 <= $dateToday AND $dateToday <= $timestampend+(86400*2)){
									$hoverclass='name="hoverclass'.$roomsIDentify.$rowcount.'"';
								} else { $hoverclass=""; }
							?><td id="<?php echo $roomsIDentify.'-'.$rowcount.'-'.$preparedCellcount; ?>" <?php echo $hoverclass;?>  <?php if(isset($edit) OR isset($add) OR isset($nonepage)){ ?>onclick="changer();clickTwo(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');clickOne(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');setVals2('<?php echo $roomsIDentify; ?>','<?php echo $rowcount; ?>');"<?php } ?> style="border-style:none; border-bottom: <?php echo $borderbottom; ?>px solid <?php echo $ovBorderColor; ?>; border-left: 1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>;background:<?php echo $background2.' '.$colorbgfree;?>" abbr="<?php echo $background2.' '.$colorbgfree;?>" onmouseover="hoverEffect(this,'<?php echo date("d.m.Y", $dateToday-86400); ?>');" onmouseout="" axis="<?php echo $cellcount+1; ?>"></td><?php
						}
					}
					unset($daysOccupied);
					unset($numberOccupied);
					unset($reservationarray);
					echo '</tr>';
				}
			} ?>
		</tbody>
	</table>
<script>
jQuery(function() {
  jQuery("#dayPicker").datepicker({
	changeMonth: true,
	changeYear: true,
    showOn: 'both',
    buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png',
    buttonImageOnly: true,
	defaultDate: '<?php echo $daysbetween; ?>',
	onSelect: function(dateText){
		jQuery(this).parent("form")[0].submit();
	}
  });
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

var Click = 0;

function clickOne(t,d){
	if( Click == 0){
		if(t){
			var Celle = t.id;
			document.getElementById("hiddenfieldclick").value=Celle;
			t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_start.png") right top no-repeat, '+t.abbr;
			<?php if(isset($edit) OR isset($add)){ ?>document.getElementById('datepicker').value=d;<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-from').value=d;<?php } ?>
			document.getElementById('resetdiv').innerHTML='<img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/refreshTry.png" style="vertical-align:bottom;cursor:pointer;" onclick="resetSet()">';
			Click = 1;
		} else document.getElementById('resetdiv').innerHTML += "<?php echo __( 'full' , 'easyReservations' ); ?>!";
	}
}

function clickTwo(t,d){
	if( Click == 1){
		var Last = document.getElementById("hiddenfieldclick").value;
		if(t){
			var Celle = t.id;
			var way = 0;
		} else {
			var Line = Last.substring(0,Last.length-2);
			var x = '<?php echo ($timesy-$timesx)/86400; ?>';
			var Celle = Line + x;
			var way = 1;
			t = document.getElementById(Celle);
		}
	
		if(Last < Celle && t.parentNode.id==document.getElementById(Last).parentNode.id){
			document.getElementById("hiddenfieldclick2").value=Celle;
			if(way == 0) t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_end.png") left top no-repeat, '+t.abbr;
			else t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_middle.png") repeat-x';
			t.style.borderLeft='0px';
			<?php if(isset($edit) OR isset($add)){ ?>document.getElementById('datepicker2').value=d;<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-to').value=d;<?php } ?>
			var theid= '';
			var work = 1;
			while(theid != Last){
				if(t.className == "er_overview_cell"){resetSet(); document.getElementById('resetdiv').innerHTML += "<?php echo __( 'full' , 'easyReservations' ); ?>!"; work = 0; break; }
				t=t.previousSibling;
				theid=t.id;
				if(theid != Last){
					t.style.borderLeft='0px';
					t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_middle.png") repeat-x';
				}
			}
			Click = 2;
			if(work == 1){
			<?php if(isset($add) OR isset($edit)) echo "easyRes_sendReq_Price();"; ?>
			<?php if(isset($nonepage)){ ?> document.roomsaver.submit();<?php } ?>
			}
		}
	}
}

function changer(x){
	if( Click == 2 ){
		resetSet();
		Click = 0;
	}
}

function fakeClick(from, to, room, exactly){
	var x = <?php echo $timesx; ?>;
	var y = <?php echo $timesy; ?>;
	var TagFrom = new Date(from*1000);
	var TagTo = new Date(to*1000);

	if(x < from && y > from){
		var daysbetween = ((from - x) / 86400)+1;
		if(daysbetween < 10) daysbetween = '0' + daysbetween;

		var daysbetween2 = ((to - x) / 86400)+1;
		if(daysbetween2 < 10) daysbetween2 = '0' + daysbetween2;

		var id = room + '-' + exactly + '-' + daysbetween;
		var id2 = room + '-' + exactly + '-' + daysbetween2;

		clickOne(document.getElementById(id),TagFrom,','+document.getElementById(id).style.background);
		clickTwo(document.getElementById(id2),TagTo,'#fff');
	}
}

function resetSet(){
	var First = document.getElementById("hiddenfieldclick").value;
	var Last = document.getElementById("hiddenfieldclick2").value;

	if(Click == 2 || Last != '' ){
		t=document.getElementById(Last);
		if(t){
		t.style.background=t.abbr;
		if(t.className != "er_overview_cell") t.style.borderLeft='1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>';
		var theid= '';
		while(theid != First){
			t=t.previousSibling;
			theid=t.id;
			if(t.className != "er_overview_cell") t.style.borderLeft='1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>';
			t.style.background=t.abbr;
		}
		Click = 0;
		document.getElementById('resetdiv').innerHTML='';
		document.getElementById("hiddenfieldclick2").value="";
		document.getElementById("hiddenfieldclick").value="";
		<?php if(isset($approve)){ ?>document.reservation_approve.roomexactly.selectedIndex=0;<?php } 
				elseif(isset($edit) OR isset($add)){ ?>
			document.editreservation.room.selectedIndex=0;
			document.editreservation.roomexactly.selectedIndex=0;
			<?php if(isset($edit)){ ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$timpstampanf)?>';
			document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$timpstampanf+($reservationFrom*86400)); ?>';
			<?php } else { ?>
			document.getElementById('datepicker').value='';
			document.getElementById('datepicker2').value='';
			<?php } } ?>
		} else Click = 0;
	} else if(Click == 1){
		var First = document.getElementById("hiddenfieldclick").value;
		var t = document.getElementById(First);
		document.getElementById('resetdiv').innerHTML='';
		t.style.background=t.abbr;
		Click = 0;
	}
}

function setVals2(roomid,roomex){
	<?php if(isset($edit) OR isset($add)){ ?>
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

function hoverEffect(t,d) {
	if(d == 0) document.getElementById("ov_datefield").innerHTML = ""; else document.getElementById("ov_datefield").innerHTML = ' (' + d + ')';
	if( Click == 1){
		var Last = document.getElementById("hiddenfieldclick").value;
		var Now = t.id;

		var Lastinfos = Last.split("-");
		var Nowinfos = Now.split("-");

		if(Nowinfos[2] > Lastinfos[2]){

			var rightid = Lastinfos[0] + '-' + Lastinfos[1] + '-' + Nowinfos[2];
			var t=document.getElementById(rightid);
			if(t){
			document.getElementById("hiddenfieldclick2").value = rightid;

			t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_end.png") left top no-repeat, '+t.abbr;
			t.style.borderLeft='0px';

			var x=t;
			var y=t;

			var theidx= 0;
			var theidy= 0;
			while(theidx != Last){
				x=x.previousSibling;
				theidx=x.id;
				if(theidx != Last){
					x.style.borderLeft='0px';
					x.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/black_middle.png") repeat-x';
				}
			}
			if(y !=  y.parentNode.lastChild){
				while(theidy != y.parentNode.lastChild.id){
					y=y.nextSibling;
					theidy=y.id;
					if(y.className != "er_overview_cell") y.style.borderLeft='1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>';
					y.style.background=y.abbr;
				}
				if(y.parentNode.lastChild.className != "er_overview_cell"){
					y.parentNode.lastChild.style.background=y.abbr;
					y.parentNode.lastChild.style.borderLeft='1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>';
				}
			}
			}
		}
	}
}
</script>
<?php if(isset($edit) OR isset($add)) echo '<br>'; } else echo '<br><b style="color:#FF0000">'.__( 'Add and set rooms post-category and add rooms to get the Overview' , 'easyReservations' ).'</b>'; 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!isset($approve) && !isset($delete) && !isset($view) && !isset($edit) && !isset($sendmail) && !isset($add)) {  
?><input type="hidden" name="action" value="reservation"><?php
			if(isset($_GET['specialselector'])) $specialselector=$_GET['specialselector'];
			if(isset($_GET['monthselector'])) $monthselector=$_GET['monthselector'];
			if(isset($_GET['roomselector'])) $roomselector=$_GET['roomselector'];

			$zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY >= NOW()";
			$orders="DESC";
			$ordersby="date";
			$orderlink="&order=DESC"; $orderbylink = ""; $orderlink = ""; $perpagelink = ""; $monthslink = ""; $roomslink = ""; $offerslink = "";
			$specialsql = "";
			$monthsql  = "";
			$roomsql  = "";

			$items1 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen")); // number of total rows in the database
			$items2 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen")); // number of total rows in the database
			$items3 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen")); // number of total rows in the database
			$items4 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY < NOW()")); // number of total rows in the database
			$items5 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='del'")); // number of total rows in the database
			$items6 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations")); // number of total rows in the database

			if(!isset($typ) OR (isset($typ) AND $typ=='active') OR (isset($typ) AND $typ=='')) { $type="approve='yes'"; $items=$items1; $typlink="&typ=active"; $orders="ASC";  } // If type is actice
			elseif(isset($typ) AND $typ=="pending") { $type="approve=''"; $items=$items3; $typlink="&typ=pending"; $ordersby="id"; $orders="DESC"; } // If type is pending
			elseif(isset($typ) AND $typ=="deleted") { $type="approve='no'"; $items=$items2; $typlink="&typ=deleted";} // If type is rejected
			elseif(isset($typ) AND $typ=="old") { $type="approve='yes'"; $items=$items4; $typlink="&typ=old"; $zeichen="AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY < NOW()";  } // If type is old
			elseif(isset($typ) AND $typ=="trash") { $type="approve='del'"; $items=$items5; $typlink="&typ=trash"; $zeichen=""; } // If type is trash
			elseif(isset($typ) AND $typ=="all") { $type="approve!='sda'"; $items=$items6; $typlink="&typ=all"; $zeichen=""; } // If type is all

			if(isset($order) AND $order=="ASC") { $orderlink="&order=ASC"; $orders="ASC";}
			elseif(isset($order) AND $order=="DESC") { $orderlink="&order=DESC"; $orders="DESC";}

			if(!isset($orderby) OR (isset($orderby) AND $orderby=="date")) { $orderbylink="&orderby=date"; $ordersby="arrivalDate"; $orders="ASC";}
			elseif(isset($orderby) AND $orderby=="name") { $orderbylink="&orderby=name"; $ordersby="name";}
			elseif(isset($orderby) AND $orderby=="room") { $orderbylink="&orderby=room"; $ordersby="room";}
			elseif(isset($orderby) AND $orderby=="special") { $orderbylink="&orderby=special"; $ordersby="special";}
			elseif(isset($orderby) AND $orderby=="nights") { $orderbylink="&orderby=nights"; $ordersby="nights";}

			if(!isset($orderby) AND isset($typ) AND $typ=="pending") { $ordersby="id"; $orders="DESC"; }
			if(!isset($orderby) AND isset($typ) AND $typ=="old") { $ordersby="arrivalDate"; $orders="DESC"; }

			if(isset($specialselector) AND $specialselector != 0 AND $specialselector != "") { $specialsql="AND special='".$specialselector."'"; }
			if(isset($roomselector) AND $roomselector != 0 AND $roomselector != "") { $roomsql="AND room='".$roomselector."'";  } 
			if(isset($monthselector) AND $monthselector != 0 AND $monthselector != "") { $monthsql="AND dat='".$monthselector."'";  }
			if(isset($monthselector) AND $monthselector != 0 AND $monthselector != "") { $monthsql="AND dat='".$monthselector."'";  }
			if(isset($perpage) AND $perpage != 0) { $perpagelink="&perpage=".$perpage; }
			elseif(!isset($perpage)) $perpage=$reservations_on_page;
			if(isset($more) AND $more != 0) $morelink="&more=";

			if(isset($specialselector) OR isset($monthselector) OR isset($roomselector)){
				$items7 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE $type $monthsql $roomsql $specialsql $zeichen"));
				$items=$items7; 
				$roomslink='&roomselector='.$roomselector; 
				$monthslink='&monthselector='.$monthselector; 
				$offerslink='&specialselector='.$specialselector; 
			}
			
			if(!isset($specialselector)) $specialselector="";
			if(!isset($roomselector)) $roomselector="";

			if(isset($items) AND $items > 0) {
				$p = new pagination;
				$p->items($items);
				$p->limit($perpage); // Limit entries per page
				if(isset($search)) $p->target("admin.php?page=reservations&search=".$search.""); else $p->target("admin.php?page=reservations".$typlink."".$orderbylink."".$orderlink."".$perpagelink."".$monthslink."".$roomslink."".$offerslink);
				if(isset($_GET[$p->paging])) $pagination = $_GET[$p->paging]; else $pagination = '';
				$p->currentPage($pagination); // Gets and validates the current page
				$p->calculate(); // Calculates what to show
				$p->parameterName('paging');
				$p->adjacents(1); //No. of page away from the current page

				if(!isset($_GET['paging'])) {
					$p->page = 1;
				} else {
					$p->page = $_GET['paging'];
				}

				$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
			} else $limit = 'LIMIT 0'; ?><form name="roomsaver" method="post" action="admin.php?page=reservations&add"><input type="hidden" id="room" name="room"><input type="hidden" id="roomexactly" name="roomexactly"><input type="hidden" name="room-saver-from" id="room-saver-from"><input type="hidden" name="room-saver-to" id="room-saver-to"></form> 
		<table style="width:99%"><tr> <!-- Type Chooser //--> 
			<td style="width:20%;">
				<ul class="subsubsub">
					<?php if(!isset($typ) OR (isset($typ) AND ($typ=="" OR $typ=="active"))){ ?>
					<li><a href="admin.php?page=reservations&typ=active" class="current"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
					<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
					<?php } elseif(isset($typ) AND $typ=="pending"){ ?>
					<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=pending" class="current"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
					<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
					<?php } elseif(isset($typ) AND $typ=="deleted"){ ?>
					<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=deleted" class="current"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
					<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
					<?php } elseif(isset($typ) AND $typ=="old"){ ?>
					<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=old" class="current"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
					<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash">Trash<span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
					<?php } elseif(isset($typ) AND $typ=="all"){ ?>
					<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=all" class="current"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
					<?php if($items5 > 0) { ?>| <li><a href="admin.php?page=reservations&typ=trash">Trash<span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
					<?php } elseif(isset($typ) AND $typ=="trash"){ ?>
					<li><a href="admin.php?page=reservations&typ=active"><?php printf ( __( 'Active' , 'easyReservations' ));?><span class="count">(<?php echo $items1; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=pending"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=deleted"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=old"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=all"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
					<li><a href="admin.php?page=reservations&typ=trash" class="current"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li>
					<?php } ?>
				</ul>
			</td>
			<td style="width:60%; text-align:center; font-size:12px;" nowrap><form method="get" action="admin.php"><input type="hidden" name="page" value="reservations"><input type="hidden" name="typ" value="<?php if(isset($typ)) echo $typ; ?>"> <!-- Begin of Filter //--> 
				<select name="monthselector"><option value="0"><?php printf ( __( 'Show all Dates' , 'easyReservations' ));?></option><!-- Filter Months //--> 
						<?php
					$posts = "SELECT DISTINCT dat FROM ".$wpdb->prefix ."reservations WHERE $type $zeichen GROUP BY dat ORDER BY dat ";
					$results = $wpdb->get_results($posts);

					foreach( $results as $result ){	
						$dat=$result->dat;	
						$zerst = explode("-",$dat);
						if($zerst[1]=="01") $month=__( 'January' , 'easyReservations' ); elseif($zerst[1]=="02") $month=__( 'February' , 'easyReservations' ); elseif($zerst[1]=="03") $month=__( 'March' , 'easyReservations' ); elseif($zerst[1]=="04") $month=__( 'April' , 'easyReservations' ); elseif($zerst[1]=="05") $month=__( 'May' , 'easyReservations' ); elseif($zerst[1]=="06") $month=__( 'June' , 'easyReservations' ); elseif($zerst[1]=="07") $month=__( 'July' , 'easyReservations' ); elseif($zerst[1]=="08") $month=__( 'August' , 'easyReservations' ); elseif($zerst[1]=="09") $month=__( 'September' , 'easyReservations' ); elseif($zerst[1]=="10") $month=__( 'October' , 'easyReservations' ); elseif($zerst[1]=="11") $month=__( 'November' , 'easyReservations' ); elseif($zerst[1]=="12") $month=__( 'December' , 'easyReservations' );
						echo '<option value="'.$dat.'">'.$month.' '.__($zerst[0]).'</option>'; 
					} ?>
				</select>
				<select name="roomselector" class="postform"><option value="0"><?php printf ( __( 'View all Rooms' , 'easyReservations' ));?></option><?php echo reservations_get_room_options($roomselector); ?></select>
				<select name="specialselector" class="postform"><option value="0"><?php printf ( __( 'View all Offers ' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options($specialselector); ?></select>
				<input size="1px" type="text" name="perpage" value="<?php echo $perpage; ?>" maxlength="3"></input><input class="easySubmitButton-secondary" type="submit" value="<?php  printf ( __( 'Filter' , 'easyReservations' )); ?>"></form><!-- End of Filter //-->
			</td>
			<td style="width:20%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
				<form method="get" action="admin.php" name="search" enctype="form-data"><input type="hidden" name="page" value="reservations"><input type="text" style="width:130px;" name="search" value="<?php if(isset($search)) echo $search;?>" class="all-options"></input><input class="easySubmitButton-secondary" type="submit" value="<?php  printf ( __( 'Search' , 'easyReservations' )); ?>" id="submitbutton"></form>
			</td>
		</tr>
		</table>
		<form action="admin.php?page=reservations" method="get" name="frmAdd" id="frmAdd">
		<table  class="reservationTable <?php echo RESERVATIONS_STYLE; ?>" style="width:99%;"> <!-- Main Table //-->
			<thead> <!-- Main Table Header //-->
				<tr>
					<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="name") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="name") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="date") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="date") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<th><?php printf ( __( 'Persons' , 'easyReservations' ));?></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="room") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="room") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="special") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="special") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
						<?php } else { ?><a class="stand2"  href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
					<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
					<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
				</tr></thead>
				<tfoot><tr><!-- Main Table Footer //-->
					<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="name") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="name") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="date") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="date") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<th><?php printf ( __( 'Persons' , 'easyReservations' ));?></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="room") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="room") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
					<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="special") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="special") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
					<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
					<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
				</tr></tfoot><tbody>
			<?php
				$nr=0;
				if(isset($search)) {
					$sql = "SELECT id, arrivalDate, name, email, number, nights, notes, room, roomnumber, special, approve, price FROM ".$wpdb->prefix ."reservations WHERE name like '%$search%' OR email like '%$search%' OR notes like '%$search%' OR arrivalDate like '%$search%' $limit"; // Search query
				} else {
					$sql = "SELECT id, arrivalDate, name, email, number, nights, notes, room, roomnumber, special, approve, price FROM ".$wpdb->prefix ."reservations WHERE $type $monthsql $roomsql $specialsql $zeichen ORDER BY $ordersby $orders $limit";  // Main Table query
				}
				$result = mysql_query($sql) or die (mysql_error());

					if(mysql_num_rows($result) > 0 ){
					$export_IDs='';
					while ($row = mysql_fetch_assoc($result)){
						$id=$row['id'];
						$name = $row['name'];
						$nights=$row['nights'];
						$person=$row['number'];

						if(!empty($row['price'])){
							$priceTableexplode=explode(";", $row['price']);
							if($priceTableexplode[0] == ''){
								$thepriceArray = easyreservations_price_calculation($id, '');
								$actualPrice = easyreservations_check_price($thepriceArray['price']);
							} else {
								$actualPrice = $priceTableexplode[0];
							}
						} else {
							$thepriceArray = easyreservations_price_calculation($id, '');
							$actualPrice = easyreservations_check_price($thepriceArray['price']);
						}
						
						if(!empty($row['price']) AND preg_match('/\;/' , $row['price']) ){
							if(!isset($priceTableexplode)) $priceTableexplode=explode(';', $row['price']);

							if($priceTableexplode[1] == $actualPrice){
								$pricebgcolor='color:#3A9920;padding:1px;';
							} elseif($priceTableexplode[1] == 0 OR !isset($priceTableexplode[1])){
								$pricebgcolor='color:#FF3B38;padding:1px;';
							} elseif($priceTableexplode[1] < $actualPrice){
								$pricebgcolor='color:#F7B500;padding:1px;';
							} else {
								$pricebgcolor='color:#FF3B38;padding:1px;';
							}
						} else {
								$pricebgcolor='color:#FF3B38;padding:1px;';
						}

						$special=$row['special'];
						if($special != 0){
							$specialgetpost=get_post($special);
							$specials=$specialgetpost->post_title;
						} else {
							$specials= __( 'None' , 'easyReservations' );
						}

						$room=$row['room'];
						$roomsgetpost=get_post($room);
						$rooms=$roomsgetpost->post_title;

						if($nr%2==0) $class="alternate"; else $class="";
						$timpstampanf=strtotime($row['arrivalDate']);
						$anznights=60*60*24*$nights;
						$timestampend=(60*60*24*$nights)+$timpstampanf;

						if(in_array($row['email'], $regular_guest_array)){
							$highlightClass='highlight';
						} else $highlightClass='';
						
						$export_IDs.=$id.', ';

						$nr++;
						?>
				<tr class="<?php echo $class.' '.$highlightClass; ?> $highlightClass$highlightClasstest" height="47px"><!-- Main Table Body //-->
					<td width="2%" style="text-align:center;vertical-align:middle;"><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $id;?>"></td>
					<td width="17%" class="row-title" valign="top" nowrap><div class="test"><a href="admin.php?page=reservations&view=<?php echo $id;?>"><?php echo $name;?></a><div class="test2" style="margin:5px 0 0px 0;"><a href="admin.php?page=reservations&edit=<?php echo $id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a> <?php if(isset($typ) AND ($typ=="deleted" OR $typ=="pending")) { ?>| <a style="color:#28a70e;" href="admin.php?page=reservations&approve=<?php echo $id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a><?php } if(!isset($typ) OR (isset($typ) AND ($typ=="active" or $typ=="pending"))) { ?> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&delete=<?php echo $id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a><?php } if(isset($typ) AND $typ=="trash") { ?>| <a href="admin.php?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a><?php } ?> | <a href="admin.php?page=reservations&sendmail=<?php echo $id;?>"><?php printf ( __( 'Mail' , 'easyReservations' ));?></a></div></div></td>
					<td width="20%" nowrap><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $nights; ?> <?php printf ( __( 'Nights' , 'easyReservations' ));?>)</small></td>
					<td width="12%"><a href="admin.php?page=reservations&sendmail=<?php echo $id; ?>"><?php echo $row['email'];?></a></td>
					<td width="5%" style="text-align:center;"><?php echo $person;?></td>
					<td width="12%" nowrap><?php echo __($rooms) ;?> <?php if(isset($row['roomexactly'])) echo $row['roomexactly']; ?></td>
					<td width="12%" nowrap><?php echo __($specials);?></td>
					<td width="13%"><?php echo substr($row['notes'], 0, 36); ?></td>
					<td width="7%" nowrap style="text-align:right"><b style="<?php echo $pricebgcolor; ?>"><?php echo easyreservations_get_price($id); ?></b></td>
				</tr>
			<?php }
			} else { ?> <!-- if no results form main quary !-->
					<tr>
						<td colspan="9"><b><?php printf ( __( 'No Reservations found!' , 'easyReservations' ));?></b></td> <!-- Mail Table Body if empty //-->
					<tr>
			<?php } ?>
			</tbody>
		</table>
		<table  style="width:99%;"> 
			<tr>
				<td style="width:33%;"><!-- Bulk Options //-->
					<select name="bulk" id="bulk"><option select="selected" value="0"><?php echo __( 'Bulk Actions' ); ?></option><?php if((isset($typ) AND $typ!="trash") OR !isset($typ)) { ?><option value="1"><?php printf ( __( 'Move to Trash' , 'easyReservations' ));?></option><?php }  if(isset($typ) AND $typ=="trash") { ?><option value="2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></option><option value="3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></option><?php } ;?></select>  <input class="easySubmitButton-secondary" type="submit" value="<?php printf ( __( 'Apply' , 'easyReservations' ));?>" /> </form>
				</td>
				<td style="width:33%;" nowrap> <!-- Pagination  //-->
					<?php if($items > 0) { ?><div class="tablenav" style="text-align:center; margin:0 115px 4px 0;"><div style="background:#ffffff;" class='tablenav-pages'><?php echo $p->show(); ?></div></div><?php } ?>
				</td>
				<td style="width:33%;margin-left: auto; margin-right: 0pt; text-align: right;"> <!-- Num Elements //-->
					<span class="displaying-nums"><?php echo $nr;?> <?php printf ( __( 'Elements' , 'easyReservations' ));?></span>
				</td>
			</tr>
		</table></form>
		<?php require_once(dirname(__FILE__)."/easyReservations_admin_main_stats.php"); ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:10%; float:left;margin-right:10px;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Upcoming reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:0px;background-color:#fff">
						<div id="container" style="margin:5px 0px 0px 0px;"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:10%; float:left;margin-right:10px;clear:none;">
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
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:320px;clear:none;">
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
						<form  name="export" action="<?php echo WP_PLUGIN_URL; ?>/easyreservations/export.php" method="post" nowrap>
							<select style="margin-top:2px;" name="export_type" onchange="exportSelect(this.value);"><option value="tab"><?php printf ( __( 'Reservations in table' , 'easyReservations' ));?></option><option value="all"><?php printf ( __( 'All reservations' , 'easyReservations' ));?></option><option value="sel"><?php printf ( __( 'Select reservations' , 'easyReservations' ));?></option></select> <select name="export_tech"><option value="xls"><?php printf ( __( 'Exel File' , 'easyReservations' ));?></option><option value="csv"><?php printf ( __( 'CSV File' , 'easyReservations' ));?></option></select>
							<input type="hidden" value="<?php if(isset($export_IDs)) echo $export_IDs; ?>" name="export_IDs">
							<div id="exportDiv">
								</div><div class="fakehr"></div>
								<b><?php echo __( 'Informations' , 'easyReservations' );?></b><br>
								<span style="float:left;width:80px;"><input type="checkbox" name="info_ID" checked> <?php echo __( 'ID' , 'easyReservations' );?><br><input type="checkbox" name="info_name" checked> <?php echo __( 'Name' , 'easyReservations' );?><br><input type="checkbox" name="info_email" checked> <?php echo __( 'eMail' , 'easyReservations' );?><br><input type="checkbox" name="info_persons" checked> <?php echo __( 'Persons' , 'easyReservations' );?></span>
								<span style="float:left;width:120px;wrap:no-wrap;"><input type="checkbox" name="info_date" checked> <?php echo __( 'Date' , 'easyReservations' );?><br><input type="checkbox" name="info_nights" checked> <?php echo __( 'Nights' , 'easyReservations' );?><br><input type="checkbox" name="info_status" checked> <?php echo __( 'Status' , 'easyReservations' );?><br><input type="checkbox" name="info_note" checked> <?php echo __( 'Note' , 'easyReservations' );?></span>
								<span nowrap><input type="checkbox" name="info_country" checked> <?php echo __( 'Country' , 'easyReservations' );?><br><input type="checkbox" name="info_room" checked> <?php echo __( 'Room' , 'easyReservations' );?><br><input type="checkbox" name="info_offer" checked> <?php echo __( 'Offer' , 'easyReservations' );?><br><input type="checkbox" name="info_price" checked> <?php echo __( 'Price/Paid' , 'easyReservations' );?></span><br>
								<br><div class="fakehr"></div>
								<input class="easySubmitButton-secondary" style="margin-top:5px;" type="submit" value="<?php printf ( __( 'Export reservations' , 'easyReservations' ));?>">
							</div>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
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
			for (i=0;i<eleArr.length;i++){eleArr[i].checked= true ;}
		}else{
			eleArr=theForm.elements[checkName+'[]'];
			for (i=0;i<eleArr.length;i++){eleArr[i].checked= false ;}
		}
	}

</script>
<?php }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + VIEW RESERVATION + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if((isset($approve) || isset($delete) || isset($view) || isset($sendmail)) && !isset($reservation_approve)){ ?> <!-- // Content will only show on delete, view or approve Reservation -->

	<?php if(!isset($view)){ ?><table  style="width:99%;" cellspacing="0"><tr><td style="width:64%;" valign="top"><br><?php } else { $width='style="width:480px;"'; echo '<br>'; } ?>
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
					<td colspan="2" nowrap><?php echo easyreservations_reservation_info_box($view, 'view'); ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo $name;?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $reservationFrom;?>)</small></b></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo $mail_to;?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
					<td><?php printf ( __( 'Adults' , 'easyReservations' ));?>: <b><?php echo $persons;?></b> <?php printf ( __( 'Childs' , 'easyReservations' ));?>: <b><?php echo $childs;?></b></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo __($rooms);?></b></td>
				</tr>
				<?php $countryArray = easyReservations_country_array(); ?>
				<?php if(!empty($country)){ ?>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Coutry' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo easyReservations_country_name($country); ?></b></td>
				</tr>
				<?php } ?>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Offer' , 'easyReservations' ));?>:</b></td> 
					<td><b><?php if($specials){ echo __($specials);} else { printf ( __( 'None' , 'easyReservations' )); }  ?></b></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?>:</b></td> 
					<td><b><?php 
					echo easyreservations_get_price($id); ?></b></td>
				</tr>
				<?php if(!empty($message_r)){ ?>
				<tr class="alternate">
					<td style="vertical-align:top;" nowrap ><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
					<td><b><?php echo $message_r; ?></b></td>
				</tr>
				<?php } ?>
				<?php 
				$explodecustoms=explode("&;&", $customs);
				$thenumber=0;
				$customsmerge=array_values(array_filter($explodecustoms));
				foreach($customsmerge as $custom){
					$customexp=explode("&:&", $custom);
					if($thenumber%2==0) $class=""; else $class="alternate";
					echo '<tr class="'.$class.'">';
					echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> '.__($customexp[0]).':</b></td>';
					echo '<td><b>'.$customexp[1].'</b></td></tr>';
					$thenumber++;
				}
				$explodecustoms=explode("&;&", $customsp);
				$thenumber=0;
				$customsmerge=array_values(array_filter($explodecustoms));
				foreach($customsmerge as $customp){
					$custompexp=explode("&:&", $customp);
					$explodeprice=explode(":", $custompexp[1]);
					if($thenumber%2==0) $class=""; else $class="alternate";
					echo '<tr class="'.$class.'">';
					echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.__($custompexp[0]).':</b></td>';
					echo '<td><b>'.$explodeprice[0].'</b>: <b>'.reservations_format_money($explodeprice[1]).' &'.get_option("reservations_currency").';</b></td></tr>';
					$thenumber++;
				}
				?>
			</tbody>
		</table><br>
	<div <?php if(isset($width)) echo $width; ?>><?php echo easyreservations_detailed_price($id); ?></div>
<?php }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($edit)){
	$highestRoomCount=reservations_get_highest_roomcount();
	
	$customfields = "";
	$explodecustoms=explode("&;&", $customs);
	$thenumber0=0;
	$thenumber1=0;
	$customsmerge=array_values(array_filter($explodecustoms));
	foreach($customsmerge as $custom){
		$customexp=explode("&:&", $custom);
		if($thenumber0%2==0) $class=""; else $class="alternate";
		$thenumber0++;
		$thenumber1++;
		$customfields .= '<tr class="'.$class.'">';
		$customfields .= '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> '.__($customexp[0]).':</b> <a href="admin.php?page=reservations&edit='.$edit.'&deletecustomfield='.$thenumber1.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a> <input type="hidden" name="customtitle'.$thenumber1.'" value="'.$customexp[0].'"></td>';
		$customfields .= '<td><b><input type="text" name="customvalue'.$thenumber1.'" value="'.$customexp[1].'"></b></td></tr>';
	}
	$thenumber2=0;
	$explodecustomprices=explode("&;&", $customsp);
	$customsmerges=array_values(array_filter($explodecustomprices));
	foreach($customsmerges as $customprice){
		$custompriceexp=explode("&:&", $customprice);
		if($thenumber0%2==0) $class=""; else $class="alternate";
		$thenumber0++;
		$thenumber2++;
		$priceexpexplode=explode(":", $custompriceexp[1]);
		$customfields .= '<tr class="'.$class.'">';
		$customfields .= '<td style="vertical-align:text-bottom;text-transform:capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.__($custompriceexp[0]).':</b> <a href="admin.php?page=reservations&edit='.$edit.'&deletepricefield='.$thenumber2.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_IMAGES_DIR.'/delete.png"></a> <input type="hidden" name="customPtitle'.$thenumber2.'" value="'.$custompriceexp[0].'"></td>';
		$customfields .= '<td><b><input type="text" name="customPvalue'.$thenumber2.'" value="'.$priceexpexplode[0].'" style="width:200px"><input type="text" name="custom_price'.$thenumber2.'" id="custom_price'.$thenumber2.'" onchange="easyRes_sendReq_Price();" value="'.$priceexpexplode[1].'" style="width:70px;"></b> &'.get_option("reservations_currency").';</td></tr>';
	}
?><script language="JavaScript" id="urlPrice" src="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_admin_price.js"></script>
	<script>
	jQuery(document).ready(function() {
		jQuery("#datepicker").datepicker( { dateFormat: 'dd.mm.yy'});
		jQuery("#datepicker2").datepicker({ dateFormat: 'dd.mm.yy' }); 
	});

	var Add = 0 + <?php echo $thenumber1; ?>;
	function addtoForm(){ // Add field to the Form
		Add += 1;
		document.getElementById("testit").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+document.getElementById("customtitle").value+' <img style="vertical-align:middle;" onclick="delfromForm('+Add+',\''+document.getElementById("customtitle").value+'\',\''+document.getElementById("customvalue").value+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customvalue").value+'<input type="hidden" name="customtitle'+Add+'" value="'+document.getElementById("customtitle").value+'"><input type="hidden" name="customvalue'+Add+'" value="'+document.getElementById("customvalue").value+'"></td></tr>';
	}

	var PAdd = 0 + <?php echo $thenumber2; ?>;
	function addPtoForm(){ // Add field to the Form
		PAdd += 1;
		document.getElementById("customPrices").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> '+document.getElementById("customPtitle").value+' <img style="vertical-align:middle;" onclick="delPfromForm('+PAdd+',\''+document.getElementById("customPtitle").value+'\',\''+document.getElementById("customPvalue").value+'\',\''+document.getElementById("customPamount").value+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customPvalue").value+': '+document.getElementById("customPamount").value+'<input name="customPtitle'+PAdd+'" value="'+document.getElementById("customPtitle").value+'" type="hidden"><input name="customPvalue'+PAdd+'" value="'+document.getElementById("customPvalue").value+'" type="hidden"><input name="custom_price'+PAdd+'" id="custom_price'+PAdd+'" value="'+document.getElementById("customPamount").value+'" type="hidden"></td></tr>';
		easyRes_sendReq_Price();
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
		easyRes_sendReq_Price();
	}

	function setPrice(){
		if( document.editreservation.fixReservation.checked == true ){
			var string = '<input type="text" value="<?php echo $pricexpl[0]; ?>" name="priceset" style="width:60px;text-align:right;"><?php echo ' &'.get_option('reservations_currency').';';?>';
			document.getElementById("priceSetter").innerHTML += string;
		} else if( document.editreservation.fixReservation.checked == false ){
			document.getElementById("priceSetter").innerHTML = '';
		}
	}
</script>
<form id="editreservation" name="editreservation" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>"> 
<input type="hidden" name="editthereservation" id="editthereservation" value="editthereservation">
	<table  style="width:99%;" cellspacing="0">
		<tr>
			<td style="width:550px;" valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:550px; margin-bottom:10px;">
					<thead>
						<tr>
							<th colspan="2"><?php printf ( __( 'Edit reservation' , 'easyReservations' ));?> <span style="background:#DB2000;padding:2px;text-shadow:0 1px 2px rgba(0,0,0,0.5);">#<?php echo $edit; ?></span></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2" nowrap><?php echo easyreservations_reservation_info_box($edit, 'edit'); ?></td>
						</tr>
						<tr>
							<td nowrap style="width:43%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
							<td><input type="text" name="name" align="middle" value="<?php echo $name;?>"></td>
						</tr>
						<tr  class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
							<td><input type="text" id="datepicker" style="width:73px" name="date" value="<?php echo date("d.m.Y",$timpstampanf); ?>" onchange="easyRes_sendReq_Price();"> <b>-</b> <input type="text" id="datepicker2" style="width:73px" name="dateend" value="<?php echo date("d.m.Y",$timestampend); ?>" onchange="easyRes_sendReq_Price();"></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
							<td><input type="text" name="email" value="<?php echo $mail_to;?>" onchange="easyRes_sendReq_Price();"></td>
						</tr>
						<tr  class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
							<td>
								<?php printf ( __( 'Adults' , 'easyReservations' ));?>:
								<select name="persons" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(1,50,$persons); ?></select>
								<?php printf ( __( 'Childs' , 'easyReservations' ));?>:
								<select name="childs" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(1,50,$childs); ?></select>
							</td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?>:</td> 
							<td><select name="country"><option value="" <?php if($country=='') echo 'selected="selected"'; ?>><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyReservations_country_select($country); ?></select></td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
							<td>
								<select  name="room" id="room"  onchange="easyRes_sendReq_Price();"><?php echo reservations_get_room_options($room); ?></select> 
								<select id="roomexactly" name="roomexactly"><?php echo easyReservations_num_options(1,$highestRoomCount,$exactlyroom); ?></select>
							</td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Offer' , 'easyReservations' ));?>:</b></td> 
							<td><select  name="offer" id="offer" onchange="easyRes_sendReq_Price();"><option value="0" <?php selected($special, 0); ?>><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options($special); ?></select>
							</td>
						</tr>
						<tr class="alternate">
							<td style="vertical-align:top;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
							<td><textarea name="note" cols="40" rows="6"><?php echo $message_r;?></textarea></td>
						</tr>
						<?php echo $customfields; ?>
					</tbody>
					<tbody id="testit">
					</tbody>
					<tbody id="customPrices">
					</tbody>
				</table>
				<input type="button" onclick="document.getElementById('editreservation').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Edit reservation' , 'easyReservations' ));?>"><span class="showPrice" style="float:right;"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &<?php echo get_option("reservations_currency"); ?>;</span></div>
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
							<td nowrap><select name="reservationStatus" style="width:99%;float:right"><option value="" <?php if($reservationStatus == '') echo 'selected'; ?>><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="yes" <?php if($reservationStatus == 'yes') echo 'selected'; ?>><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value="no" <?php if($reservationStatus == 'no') echo 'selected'; ?>><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option><option value="del" <?php if($reservationStatus == 'del') echo 'selected'; ?>><?php printf ( __( 'Trashed' , 'easyReservations' ));?></option></select></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR.'/money.png'; ?>"> <?php printf ( __( 'Fixed Price' , 'easyReservations' ));?></td>
							<td nowrap><input type="checkbox" onclick="setPrice()" name="fixReservation" <?php if($pricexpl[0] != '') echo 'checked'; ?>> <span id="priceSetter"><?php if($pricexpl[0] != ''){ ?><input type="text" value="<?php echo $pricexpl[0]; ?>" name="priceset" style="width:60px"><?php echo ' &'.get_option('reservations_currency').';'; } ?></span></td>
						</tr>
						<tr class="alternate">
							<td nowrap><?php printf ( __( 'Paid' , 'easyReservations' ));?></td>
							<td nowrap><input type="text" name="EDITwaspaid" value="<?php echo $pricexpl[1]; ?>" style="width:60px;text-align:right"> <?php echo ' &'.get_option('reservations_currency').';';?></td>
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
							<td nowrap><input type="text" name="customtitle" id="customtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><textarea type="text" name="customvalue" id="customvalue" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';">Value</textarea>
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
							<td nowrap><input type="text" name="customPtitle" id="customPtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><input type="text" name="customPvalue" id="customPvalue" value="Value" style="width:190px;margin-top:2px;" value="Value" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';"><input type="text" name="customPamount" id="customPamount" style="width:60px;margin-top:2px;text-align:right;" value="Amount" onfocus="if (this.value == 'Amount') this.value = '';" onblur="if (this.value == '') this.value = 'Amount';"><?php echo '&'.get_option('reservations_currency').';'; ?>
							<br><input type="button" onclick="addPtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom Price Field' , 'easyReservations' ));?>"></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
</form>
<script>easyRes_sendReq_Price();</script>
<?php } 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($add)){
$highestRoomCount=reservations_get_highest_roomcount();
?> <!-- // Content will only show on edit Reservation -->
	<script>
	  jQuery(document).ready(function() {
		jQuery("#datepicker").datepicker({ dateFormat: 'dd.mm.yy', beforeShow: function(){ setTimeout(function(){ jQuery(".ui-datepicker").css("z-index", 99); }, 10); }});
		jQuery("#datepicker2").datepicker({ dateFormat: 'dd.mm.yy', beforeShow: function(){ setTimeout(function(){ jQuery(".ui-datepicker").css("z-index", 99); }, 10); }});
	});

		var Add = 0;
	function addtoForm(){ // Add field to the Form
		Add += 1;
		document.getElementById("testit").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+document.getElementById("customtitle").value+' <img style="vertical-align:middle;" onclick="delfromForm('+Add+',\''+document.getElementById("customtitle").value+'\',\''+document.getElementById("customvalue").value+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customvalue").value+'<input type="hidden" name="customtitle'+Add+'" value="'+document.getElementById("customtitle").value+'"><input type="hidden" name="customvalue'+Add+'" value="'+document.getElementById("customvalue").value+'"></td></tr>';
	}

	var PAdd = 0;
	function addPtoForm(){ // Add field to the Form
		PAdd += 1;
		document.getElementById("customPrices").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> '+document.getElementById("customPtitle").value+' <img style="vertical-align:middle;" onclick="delPfromForm('+PAdd+',\''+document.getElementById("customPtitle").value+'\',\''+document.getElementById("customPvalue").value+'\',\''+document.getElementById("customPamount").value+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customPvalue").value+': '+document.getElementById("customPamount").value+'<input name="customPtitle'+PAdd+'" value="'+document.getElementById("customPtitle").value+'" type="hidden"><input name="customPvalue'+PAdd+'" value="'+document.getElementById("customPvalue").value+'" type="hidden"><input name="custom_price'+PAdd+'" id="custom_price'+PAdd+'" value="'+document.getElementById("customPamount").value+'" type="hidden"></td></tr>';
		easyRes_sendReq_Price();
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
		easyRes_sendReq_Price();
	}
	function setPrice(){
		if( document.editreservation.fixReservation.checked == true ){
			var string = '<tr><td colspan="2"><p><input name="setChoose" type="radio" value="custm"> <?php printf ( __( 'set price' , 'easyReservations' ));?> <input name="priceAmount" type="text" style="width:50px;height:20px"> <?php echo '&'.get_option('reservations_currency').';'; ?></p>';
			string += '<div style="margin-top:10px;"><input name="setChoose" type="radio" value="calc" checked> <?php printf ( __( 'fix the sum of the normal calculation' , 'easyReservations' ));?></div></td></tr>';
			string += '<tr><td><?php printf ( __( 'Paid' , 'easyReservations' ));?></td><td><span style="float:right"><input name="paidAmount" type="text"value="0" style="width:50px;height:20px;"> <?php echo '&'.get_option('reservations_currency').';'; ?></span></td></tr>';
			document.getElementById("priceCell").innerHTML += string;
		} else if( document.editreservation.fixReservation.checked == false ){
			document.getElementById("priceCell").innerHTML = '';
		}
	}
	
</script><script language="JavaScript" id="urlPrice" src="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_admin_price.js"></script>
<form id="editreservation" name="editreservation" method="post" action=""> 
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
				<tr  class="alternate">
					<td nowrap style="width:45%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?>:</td> 
					<td><input type="text" name="name" align="middle" onchange="easyRes_sendReq_Price();"></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
					<td><input type="text" id="datepicker" style="width:73px" name="date" onchange="easyRes_sendReq_Price();"> <b>-</b> <input type="text" id="datepicker2" style="width:73px" name="dateend" onchange="easyRes_sendReq_Price();"></td>
				</tr>
				<tr  class="alternate" >
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
					<td><input type="text" name="email" onchange="easyRes_sendReq_Price();"></td>
				</tr>
				<tr  class="alternate" >
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?>:</td> 
					<td><select name="country"><option value=""><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyReservations_country_select(''); ?></select></td>
				</tr>
				<tr valign="top">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
							<td>
								<?php printf ( __( 'Adults' , 'easyReservations' ));?>:
								<select name="persons" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(1,50); ?></select>
								<?php printf ( __( 'Childs' , 'easyReservations' ));?>:
								<select name="childs" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(0,50); ?></select>
				</tr>
				<tr valign="top" class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?></td>
					<td>
						<select id="room" name="room" onchange="easyRes_sendReq_Price();"><?php echo reservations_get_room_options(); ?></select>
						<select id="roomexactly" name="roomexactly"><?php echo easyReservations_num_options(1,$highestRoomCount); ?><option value=""><?php printf ( __( 'None' , 'easyReservations' ));?></option></select>
					</td>
				</tr>
				<tr valign="top">					
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png" > <?php printf ( __( 'Offer' , 'easyReservations' ));?></td>
					<td><select name="offer" onchange="easyRes_sendReq_Price();"><option value="0" select="selected"><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options(); ?></select>
					</td>
				</tr>
				<tr class="alternate">
					<td style="vertical-align:top;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?>:</b></td> 
					<td><textarea name="note" cols="40" rows="6"></textarea></td>
				</tr>
			</tbody>
			<tbody id="testit">
			</tbody>
			<tbody id="customPrices">
			</tbody>
		</table>
		<br><input type="button" onclick="document.getElementById('editreservation').submit(); return false;" class="easySubmitButton-primary" value="<?php printf ( __( 'Add reservation' , 'easyReservations' ));?>"><span class="showPrice" style="float:right;"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &<?php echo get_option("reservations_currency"); ?>;</span></div>
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
						<td nowrap><?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td nowrap><input type="checkbox" onclick="setPrice();" name="fixReservation"> <?php printf ( __( 'Fix Price' , 'easyReservations' ));?> <u style="color:#000">?</u><br></td>
					</tr>
				</tbody>
				 <tbody id="priceCell">
				 </tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:248px;margin-bottom:4px;">
				<thead>
					<tr>
						<th><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap><input type="text" name="customtitle" id="customtitle" style="width:230px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><textarea type="text" name="customvalue" id="customvalue" value="Value" style="width:230px;margin-top:2px;" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';">Value</textarea>
						<br><input type="button" onclick="addtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom field' , 'easyReservations' ));?>"></td>
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
						<td nowrap><input type="text" name="customPtitle" id="customPtitle" style="width:230px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><input type="text" name="customPvalue" id="customPvalue" value="Value" style="width:150px;margin-top:2px;" value="Value" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';"><input type="text" name="customPamount" id="customPamount" style="width:60px;margin-top:2px;text-align:right;" value="Amount" onfocus="if (this.value == 'Amount') this.value = '';" onblur="if (this.value == '') this.value = 'Amount';"><?php echo '&'.get_option('reservations_currency').';'; ?>
						<br><input type="button" onclick="addPtoForm();" style="margin-top:3px" class="easySubmitButton-secondary" value="<?php printf ( __( 'Add custom price field' , 'easyReservations' ));?>"></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
</form><?php if(isset($_POST['room-saver-to'])){ ?><script>fakeClick('<?php echo strtotime($_POST['room-saver-from']); ?>','<?php echo strtotime($_POST['room-saver-to']); ?>','<?php echo $_POST['room']; ?>','<?php echo $_POST['roomexactly']; ?>');setVals2(<?php echo $_POST['room'].','.$_POST['roomexactly']; ?>);document.getElementById('datepicker').value='<?php echo $_POST['room-saver-from']; ?>';document.getElementById('datepicker2').value='<?php echo $_POST['room-saver-to']; ?>';easyRes_sendReq_Price();</script><?php } //Set Room and Roomexactly after click on Overview and redirected to add 
	} 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + APPROVE / REJECT - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($approve) OR isset($delete)) {
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
				<td nowrap><?php echo easyreservations_reservation_info_box($delorapp, $delorapptext); ?></td>
			</tr>
				<?php if(isset($approve)){ ?><tr>
					<td><?php printf ( __( 'Room' , 'easyReservations' ));?>: <?php echo __($rooms);?> # <select id="roomexactly" name="roomexactly">
					<?php echo easyReservations_num_options(1,$roomcounty,$exactlyroom); ?></td>
				</tr><?php } ?>
				<tr>
					<td>
							<p><input type="checkbox" name="sendthemail" checked><small> <?php printf ( __( 'Send mail to guest' , 'easyReservations' ));  ?></small> <input type="checkbox" name="hasbeenpayed"><small>  <?php printf ( __( 'Has been paid' , 'easyReservations' ));  ?></small></p>
							<p><?php printf ( __( 'To' , 'easyReservations' ));?> <?php if(isset($approve)) { printf ( __( 'Approve' , 'easyReservations' )); } if(isset($delete)) printf ( __( 'Reject' , 'easyReservations' ));?> <?php printf ( __( 'the reservation, write a message and press send' , 'easyReservations' ));?> & <?php if(isset($approve)) echo "Approve"; if(isset($delete)) echo "reject"; ?>. <?php printf ( __( 'The Guest will recieve that message in an eMail' , 'easyReservations' ));?>.</p>
							<p class="label"><strong>Text:</strong></p>
							<textarea cols="60" rows="4" name="approve_message" class="text-area-1" width="100px"></textarea>
					</td>
			</tbody>
		</table>
			<?php if(isset($approve)) { ?><p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;"  class="easySubmitButton-primary"><span>Send & Approve</span></a></p><?php } ?>
			<?php if(isset($delete)) { ?><p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_approve').submit(); return false;" class="easySubmitButton-primary"><span>Send & Reject</span></a></p><?php } ?>
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
					<td nowrap><?php echo easyreservations_reservation_info_box($sendmail, 'sendmail'); ?></td>
				</tr>
				<tr>
					<td>
							<textarea cols="60" rows="4" name="approve_message" class="text-area-1" width="100px"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<p style="float:right"><a href="javascript:{}" onclick="document.getElementById('reservation_sendmail').submit(); return false;" class="easySubmitButton-primary"><span><?php echo __( 'Send' , 'easyReservations' ); ?></span></a></p>
	</form><td></tr></table>
<?php }
	} else {
	echo '<br><b>'.__( 'Set the room-category in settings first' , 'easyReservations' ).' <a href="admin.php?page=settings">Click</a></b>';
	}
} ?>