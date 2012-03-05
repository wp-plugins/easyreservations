<?php
function reservation_main_page() {

	$offer_cat = get_option("reservations_special_offer_cat");
	$room_category =get_option("reservations_room_category");
	$main_options = get_option("reservations_main_options");
	$show = $main_options['show'];
	$overview_options = $main_options['overview'];

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

	if(isset($_GET['more'])){
		$moreget=$_GET['more'];
		$moregets=$_GET['more'];
	} else $moreget = 0;
	if(isset($_GET['perpage'])) update_option("reservations_on_page",$_GET['perpage']);
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
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'moved to Trash' , 'easyReservations' ).'. <a href="admin.php?page=reservations&bulkArr[]='.wp_nonce_url($linkundo, 'easy-main-bulk').'&bulk=2">'.__( 'Undo' , 'easyReservations' ).'</a></p></div>';

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
		} elseif(easyreservations_check_avail($EDITroom, $timestampstartedit, $EDITroomex, $calcdaysbetween, 0, 0, $edit) > 0){
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
		$errors="";
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
			if($input==''){ $errors .= " "; }
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
		if($ADDroomex > get_post_meta($ADDroom, 'roomcount', true)) $errors .= __( 'Roomcount was too high' , 'easyReservations' );
		if($ADDtimestampsanf > $ADDtimestampsend) $errors .= __( 'The depature date has to be after the arrival date' , 'easyReservations' );

		$ADDanznights=round(($ADDtimestampsend-$ADDtimestampsanf)/60/60/24);
		$ADDdat=date("Y-m", $ADDtimestampsanf);
		$ADDrightdate=date("Y-m-d", $ADDtimestampsanf);

		if($errors != ""){
			$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Please fill out all Fields' , 'easyReservations' ).' - '.$errors.'</p></div>';
		} elseif(easyreservations_check_avail($ADDroom, $ADDtimestampsanf, $ADDroomex, $ADDanznights) > 0){
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
		$reservationNights=$approvequerie[0]->nights;
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
		$rooms=get_the_title($room);

		if($special == 0) $specials="None";
		else $specials=get_the_title($special);

		$timpstampanf=strtotime($reservationDate);
		$anznights=60*60*24*$reservationNights;
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

		easyreservations_send_mail($emailformation, $mail_to, $subj, $approve_message, $id, $timpstampanf, $timestampend, $name, $mail_to, $reservationNights, $persons, $childs, $country, $rooms, $specials, $customs, easyreservations_get_price($approve), $message_r, '');
	}

	if(isset($post_approve) && $post_approve=="yes"){

		$pricearry = easyreservations_price_calculation($approve, '');
		if($hasbeenpayed=="on") $priceset2=$pricearry['price'].';1'; else $priceset2=$pricearry['price'].';0';

		if(easyreservations_check_avail($room, $timpstampanf, $roomexactly, $reservationNights, 0, 0, $id) == 0){
			$priceset=str_replace(",", ".", $priceset2);
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET approve='yes', roomnumber='$roomexactly', price='$priceset' WHERE id='$approve'"  ) ); 	

			if(isset($sendthemail) AND $sendthemail=="on"){
				$emailformation=get_option('reservations_email_to_userapp_msg');
				$subj=get_option("reservations_email_to_userapp_subj");
				easyreservations_send_mail($emailformation, $mail_to, $subj, $approve_message, $id, $timpstampanf, $timestampend, $name, $mail_to, $reservationNights, $persons, $childs, $country, $rooms, $specials, $customs, easyreservations_get_price($approve), $message_r, '');
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
			easyreservations_send_mail($emailformation, $mail_to, $subj, $approve_message, $id, $timpstampanf, $timestampend, $name, $mail_to, $reservationNights, $persons, $childs, $country, $rooms, $specials, $customs, easyreservations_get_price($approve), $message_r, '');
		}
		$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.$anzahl.' '.__( 'Reservation rejected' , 'easyReservations' ).'</p></div>';
		?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
			
	}

	if(isset($prompt)) echo '<br> '.$prompt;

if($show['show_overview']==1){ //Hide Overview completly
	if(RESERVATIONS_STYLE == 'widefat'){
		$ovBorderColor='#9E9E9E';
		$ovBorderStatus='dotted';
	} elseif(RESERVATIONS_STYLE == 'greyfat'){
		$ovBorderColor='#777777';
		$ovBorderStatus='dashed';
	}
?>
<div class="easyReservationHeadline" >
	<span class="easyReservationHeadlineBox"><a href="admin.php?page=reservations"><?php echo __( 'Reservations' , 'easyReservations' );?></a> <a href="admin.php?page=reservations&add" rel="simple_overlay"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png"></a> </span> 
	<div style="float:right;font-size: 13px">
		<i><?php echo date("d. M Y H:i", time()); ?></i>
	</div>
</div>
<div id="wrap">
<script>
function generateXMLHttpReqObjThree(){
  var resObjektTwo = null;
  try {
    resObjektThree = new ActiveXObject("Microsoft.XMLHTTP");
  }
  catch(Error){
    try {
      resObjektThree = new ActiveXObject("MSXML2.XMLHTTP");
    }
    catch(Error){
      try {
      resObjektThree = new XMLHttpRequest();
      }
      catch(Error){
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

function easyRes_sendReq_Overview(x,y,daystoshow) {
	if(x && x != 'no') x = 'more=' + x;
	else var x = '';
	if(y && y != 'no') y =  '&dayPicker=' + y;
	else var y = '';
	var reservationDate = '<?php if(isset($reservationDate)) echo $reservationDate; ?>';
	if(reservationDate != '') var z = '&reservationDate=' + reservationDate;
	else var z = '';
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
	var id = '<?php if(isset($id)) echo $id; ?>';
	if(id != '') var e = '&id=' + id;
	else var e = '';
	var nonepage = '<?php if(isset($nonepage)) echo $nonepage; ?>';
	if(nonepage != '') var f = '&nonepage=' + nonepage;
	else var f = '';
	if(daystoshow) var g = '&daysshow=' + daystoshow;
	else g = '&daysshow=' + <?php echo $overview_options['overview_show_days']; ?>;
	
	if((y != "" || x != "") && save == 0){
		save = 1;
		resObjektThree.open('post', '<?php echo WP_PLUGIN_URL; ?>/easyreservations/overview.php' ,true);
		resObjektThree.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		resObjektThree.onreadystatechange = handleResponseValidate;
		resObjektThree.send(x + y + z + a + b + c + d + e + f + g);
		document.getElementById('pickForm').innerHTML = '<img style="" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/loading1.gif">';
	}
}

function handleResponseValidate() {
	var text="";
  if(resObjektThree.readyState == 4){
  	text=resObjektThree.responseText;
    document.getElementById("theOverviewDiv").innerHTML = text;
	createPickers();
	save = 0;
  }
}

function createPickers(context) {
	//alert(document.getElementById("getmore").value);
	jQuery("#dayPicker", context || document).datepicker({
		changeMonth: true,
		changeYear: true,
		showOn: 'both',
		buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png',
		buttonImageOnly: true,
		defaultDate: +10,
		onSelect: function(dateText){
			easyRes_sendReq_Overview('no', document.getElementById("dayPicker").value);
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
}

jQuery(function() {
  jQuery("#dayPicker").datepicker({
	changeMonth: true,
	changeYear: true,
    showOn: 'both',
    buttonImage: '<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png',
    buttonImageOnly: true,
	defaultDate: 10,
	onSelect: function(dateText){
		easyRes_sendReq_Overview('no', document.getElementById("dayPicker").value);
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

function clickOne(t,d,color,mode){
	if( Click == 0){
			var activres = document.getElementsByName('activeres');
			if(activres[0]){
				var ares = document.getElementById(activres[0].id);
				var firstDate = new Date(<?php if(isset($timpstampanf)) echo $timpstampanf.'000'; ?>);

				if(ares.getAttribute("colSpan") == null){
					var splitidbefor=ares.id.split("-")

					var firstDay = firstDate.getDate();
					if(firstDay < 10) firstDay = '0' + firstDay;
					var firstMonth = firstDate.getMonth()+1;
					if(firstMonth < 10) firstMonth = '0' + firstMonth;

					var dateNow = firstDay + '.' + firstMonth + '.' + firstDate.getFullYear();

					ares.setAttribute("onclick", "changer();clickTwo(this,'"+dateNow+"'); clickOne(this,'"+dateNow+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");
					ares = ares.nextSibling;
				}
				var td ="";
				var i = 0;
				var idbefor = ares.previousSibling;
				var splitidbefor=idbefor.id.split("-")
				var Colspan = ares.colSpan;
				var Parent = ares.parentNode;
				var next = ares.nextSibling;
				if(next){ next.removeAttribute("class"); next.removeAttribute("name"); }
				ares.setAttribute("colSpan", "1");
				ares.removeAttribute("class");
				ares.removeAttribute("onclick");
				ares.removeAttribute("name");
				if(ares.firstChild) ares.removeChild(ares.firstChild);

				var firstDay = firstDate.getDate();
				if(firstDay < 10) firstDay = '0' + firstDay;
				var firstMonth = firstDate.getMonth()+1;
				if(firstMonth < 10) firstMonth = '0' + firstMonth;

				var dateNow = firstDay + '.' + firstMonth + '.' + firstDate.getFullYear();

				ares.setAttribute("onclick", "changer();clickTwo(this,'"+dateNow+"'); clickOne(this,'"+dateNow+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");

				while(i != Colspan){
					firstDate.setDate(firstDate.getDate()+1);

					var clone = ares.cloneNode(true);
					var newid = parseInt(splitidbefor[2])+i+1;					
					if(newid < 10) newid = '0' + newid;
					
					var firstDay = firstDate.getDate();
					if(firstDay < 10) firstDay = '0' + firstDay;
					var firstMonth = firstDate.getMonth()+1;
					if(firstMonth < 10) firstMonth = '0' + firstMonth;

					var dateNow = firstDay + '.' + firstMonth + '.' + firstDate.getFullYear();
					clone.setAttribute("onclick", "changer();clickTwo(this,'"+dateNow+"');clickOne(this,'"+dateNow+"');setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");

					clone.setAttribute("id", splitidbefor[0] + '-' + splitidbefor[1] + '-' + newid);
					
					Parent.insertBefore(clone, ares);
					i++;
				}
				Parent.removeChild(ares);
			}

		if(t){
			if(color) var color = color; else var color = "black";
			document.getElementById("hiddenfieldclick").value=t.id;

			if(mode == 1) t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_middle.png") repeat-x';
			else t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_start.png") right top no-repeat, '+t.abbr;
			<?php if(isset($edit) OR isset($add)){ ?>document.getElementById('datepicker').value=d;<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-from').value=d;<?php } ?>
			document.getElementById('resetdiv').innerHTML='<img src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/refreshTry.png" style="vertical-align:bottom;cursor:pointer;" onclick="resetSet()">';
			Click = 1;
		} else {
			document.getElementById('resetdiv').innerHTML += "<?php echo __( 'inexistent' , 'easyReservations' ); ?>!";
			var field = document.getElementById('roomexactly');
			if(field && field.type == "select-one" ){
				field.style.borderColor="#F20909";
			}

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
			t = document.getElementById(Last).parentNode.lastChild;
		}
		var Celle = t.id;
		if(color) color = color; else var color = "black";

		if(Last < Celle && t.parentNode.id==document.getElementById(Last).parentNode.id){
			document.getElementById("hiddenfieldclick2").value=Celle;
			if(way == 0) t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_end.png") left top no-repeat, '+t.abbr;
			else t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_middle.png") repeat-x';
			t.style.borderLeft='0px';
			<?php if(isset($edit) OR isset($add)){ ?>document.getElementById('datepicker2').value=d;<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-to').value=d;<?php } ?>
			var theid= '';
			var work = 1;
			while(theid != Last){
				if(t.className == "er_overview_cell" && t.name != "activeres" && color == "black"){
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
				if(theid != Last){
					t.style.borderLeft='0px';
					t.style.background='url("<?php echo RESERVATIONS_IMAGES_DIR; ?>/'+ color +'_middle.png") repeat-x';
				}
			}
			Click = 2;
			if(work == 1){
				<?php if(isset($add) OR isset($edit)) echo "easyRes_sendReq_Price();"; ?>
				if(color == "black"){ <?php if(isset($nonepage)){ ?>document.roomsaver.submit();<?php } ?>}
			}
		}
	}
}

function changer(x){
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
	var x = document.getElementById("timesx").value;
	var y = document.getElementById("timesy").value;
	var TagFrom = new Date(from*1000);
	var TagTo = new Date(to*1000);
	var mode = 0;

	if(from < y && to > x){
		var daysbetween = ((from - x) / 86400)+1;
		if(daysbetween < 10 && daysbetween >= 0) daysbetween = '0' + daysbetween;
		if(daysbetween <= 1){ daysbetween = '01'; var mode = 1; }

		var daysbetween2 = ((to - x) / 86400)+1;
		if(daysbetween2 < 10) daysbetween2 = '0' + daysbetween2;

		var id = room + '-' + exactly + '-' + daysbetween;
		var id2 = room + '-' + exactly + '-' + daysbetween2;

		var FromDay = TagFrom.getDate();
		if(FromDay < 10) FromDay = '0' + FromDay;
		var FromMonth = TagFrom.getMonth()+1;
		if(FromMonth < 10) FromMonth = '0' + FromMonth;
		var ToDay = TagTo.getDate();
		if(ToDay < 10) ToDay = '0' + ToDay;
		var ToMonth = TagTo.getMonth()+1;
		if(ToMonth < 10) ToMonth = '0' + ToMonth;
		

		clickOne(document.getElementById(id),FromDay+'.'+FromMonth+'.'+TagFrom.getFullYear(),color, mode);
		clickTwo(document.getElementById(id2),ToDay+'.'+ToMonth+'.'+TagTo.getFullYear(),color);
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
			var testa = document.getElementById(First);
			if(testa.className != "er_overview_cell") testa.style.borderLeft='1px <?php echo $ovBorderStatus; ?> <?php echo $ovBorderColor; ?>';
			testa.style.background=t.abbr;

			Click = 0;
			document.getElementById('resetdiv').innerHTML='';
			document.getElementById("hiddenfieldclick2").value="";
			document.getElementById("hiddenfieldclick").value="";
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
<?php if($overview_options['overview_onmouseover'] == 1){ ?>
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
<?php } ?>
	<?php if($overview_options['overview_autoselect'] == 1 && (isset($add) || isset($edit))){ ?>
		function dofakeClick(order){
			var from = document.getElementById("datepicker").value;
			var to = document.getElementById("datepicker2").value;
			var now = <?php echo strtotime(date("d.m.Y", time())); ?> - 172800;

			if(from){
				var explodeFrom = from.split(".");
				var timestampFrom = Date.UTC(explodeFrom[2],explodeFrom[1]-1,explodeFrom[0]) / 1000;
				if(order == 0 || order == 1) easyRes_sendReq_Overview(((timestampFrom-now)/86400)-4,'');
			}

			var explodeTo = to.split(".");
			var timestampTo = Date.UTC(explodeTo[2],explodeTo[1]-1,explodeTo[0]) / 1000;
			var room = document.getElementById("room").value;
			var roomexactly = document.getElementById("roomexactly").value;
			var x = document.getElementById("timesx").value;
			var y = document.getElementById("timesy").value;
			
			//alert("from:"+timestampFrom+" | to:"+timestampTo+" | room:"+room+" | roomexactly:"+roomexactly+" | order:"+order);
			
			if(from && to && room && roomexactly && from != "" && to != "" && room != "" && roomexactly != "" && (order == 0 || order == 2) && timestampFrom < timestampTo){
				fakeClick(timestampFrom,timestampTo,room,roomexactly,"black");
			}
		}<?php
	} ?></script><div id="theOverviewDiv"><?php include('overview.php'); ?></div><?php
}
if(isset($edit) OR isset($add)) echo '<br>';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!isset($approve) && !isset($delete) && !isset($view) && !isset($edit) && !isset($sendmail) && !isset($add)) {
	if($show['show_table']==1){
	?><input type="hidden" name="action" value="reservation"><?php
			$reservations_on_page = get_option("reservations_on_page");
			$table_options =  $main_options['table'];
			$regular_guest_explodes = explode(",", str_replace(" ", "", get_option("reservations_regular_guests")));
			foreach( $regular_guest_explodes as $regular_guest) $regular_guest_array[]=$regular_guest;
			
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

			$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen")); // number of total rows in the database
			$items2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen")); // number of total rows in the database
			$items3 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen")); // number of total rows in the database
			$items4 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) + INTERVAL 1 DAY < NOW()")); // number of total rows in the database
			$items5 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='del'")); // number of total rows in the database
			$items6 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations")); // number of total rows in the database

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
			else $perpage=$reservations_on_page;
			if(isset($more) AND $more != 0) $morelink="&more=";

			if(isset($specialselector) OR isset($monthselector) OR isset($roomselector)){
				$items7 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE $type $monthsql $roomsql $specialsql $zeichen"));
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
				if(isset($_GET[$p->paging])) $pagination = $_GET[$p->paging]; else $pagination = 0;
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
			<?php if($table_options['table_filter_month'] == 1){ ?>
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
				<?php } ?>
				<?php if($table_options['table_filter_room'] == 1){ ?><select name="roomselector" class="postform"><option value="0"><?php printf ( __( 'View all Rooms' , 'easyReservations' ));?></option><?php echo reservations_get_room_options($roomselector); ?></select><?php } ?>
				<?php if($table_options['table_filter_offer'] == 1){ ?><select name="specialselector" class="postform"><option value="0"><?php printf ( __( 'View all Offers ' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options($specialselector); ?></select><?php } ?>
					<?php if($table_options['table_filter_days'] == 1){ ?><input size="1px" type="text" name="perpage" value="<?php echo $perpage; ?>" maxlength="3"></input><input class="easySubmitButton-secondary" type="submit" value="<?php  printf ( __( 'Filter' , 'easyReservations' )); ?>"></form><!-- End of Filter //--><?php } ?>
			</td>
			<td style="width:20%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
					<?php if($table_options['table_search'] == 1){ ?><form method="get" action="admin.php" name="search" enctype="form-data"><input type="hidden" name="page" value="reservations"><input type="text" style="width:130px;" name="search" value="<?php if(isset($search)) echo $search;?>" class="all-options"></input><input class="easySubmitButton-secondary" type="submit" value="<?php  printf ( __( 'Search' , 'easyReservations' )); ?>" id="submitbutton"></form><?php } ?>
			</td>
		</tr>
		</table>
		<form action="admin.php?page=reservations" method="get" name="frmAdd" id="frmAdd"><?php wp_nonce_field('easy-main-bulk','easy-main-bulk'); ?>
		<table  class="reservationTable <?php echo RESERVATIONS_STYLE; ?>" style="width:99%;"> <!-- Main Table //-->
			<thead> <!-- Main Table Header //-->
				<tr>
					<?php if($table_options['table_color'] == 1){ ?>
						<th style="max-width:4px;padding:0px;"></th>
					<?php } if($table_options['table_bulk'] == 1){ ?>
						<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="name") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="name") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_from'] == 1 || $table_options['table_to'] == 1 || $table_options['table_nights'] == 1){ ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="date") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="date") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_email'] == 1){ ?>
						<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ ?>
						<th style="text-align:center"><?php if($table_options['table_persons'] == 1 && $table_options['table_childs'] == 1) printf ( __( 'Persons' , 'easyReservations' )); elseif($table_options['table_persons'] == 1) echo __( 'Adults' , 'easyReservations' ); else echo __( 'Children\'s' , 'easyReservations' );?></th>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="room") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="room") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_offer'] == 1){  ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="special") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="special") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
						<?php } else { ?><a class="stand2"  href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_country'] == 1){  ?>
						<th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_message'] == 1){  ?>
						<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_custom'] == 1){  ?>
						<th><?php printf ( __( 'Custom fields' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_customp'] == 1){  ?>
						<th><?php printf ( __( 'Custom prices' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_paid'] == 1){  ?>
						<th style="text-align:right"><?php printf ( __( 'Paid' , 'easyReservations' ));?></th>
					<?php }  if($table_options['table_price'] == 1){  ?>
						<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr><!-- Main Table Footer //-->
					<?php if($table_options['table_color'] == 1){ ?>
						<th style="max-width:4px;padding:0px;"></th>
					<?php } if($table_options['table_bulk'] == 1){ ?>
						<th><input type="hidden" name="page" value="reservations"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="name") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="name") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=name&order=ASC";?>"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_from'] == 1 || $table_options['table_to'] == 1 || $table_options['table_nights'] == 1){ ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="date") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="date") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=date&order=ASC";?>"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
					<?php } if($table_options['table_email'] == 1){ ?>
						<th><?php printf ( __( 'eMail' , 'easyReservations' ));?></th>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ ?>
						<th style="text-align:center"><?php if($table_options['table_persons'] == 1 && $table_options['table_childs'] == 1) printf ( __( 'Persons' , 'easyReservations' )); elseif($table_options['table_persons'] == 1) echo __( 'Adults' , 'easyReservations' ); else echo __( 'Children\'s' , 'easyReservations' );?></th>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="room") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="room") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>">
						<?php } else { ?><a class="stand2" href="admin.php?page=reservations<?php echo $typlink."&orderby=room&order=ASC";?>"><?php } ?><?php printf ( __( 'Room' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_offer'] == 1){  ?>
						<th><?php if(isset($order) AND isset($orderby) AND $order=="ASC" and $orderby=="special") { ?><a class="asc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=DESC";?>">
						<?php } elseif(isset($order) AND isset($orderby) AND $order=="DESC" and $orderby=="special") { ?><a class="desc2" href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>">
						<?php } else { ?><a class="stand2"  href="admin.php?page=reservations<?php echo $typlink."&orderby=special&order=ASC";?>"><?php } ?><?php printf ( __( 'Offer' , 'easyReservations' ));?></a></th>
					<?php }  if($table_options['table_country'] == 1){  ?>
						<th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_message'] == 1){  ?>
						<th><?php printf ( __( 'Note' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_custom'] == 1){  ?>
						<th><?php printf ( __( 'Custom fields' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_customp'] == 1){  ?>
						<th><?php printf ( __( 'Custom prices' , 'easyReservations' )); ?></th>
					<?php }  if($table_options['table_paid'] == 1){  ?>
						<th style="text-align:right"><?php printf ( __( 'Paid' , 'easyReservations' ));?></th>
					<?php }  if($table_options['table_price'] == 1){  ?>
						<th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
					<?php } ?>
				</tr>
			</tfoot>
			<tbody>
			<?php
				$nr=0;
				$time = strtotime(date("d.m.Y",time()));
				if(isset($search)) $sql = "SELECT id, arrivalDate, name, email, number, childs, nights, notes, room, roomnumber, country, special, approve, price, custom, customp FROM ".$wpdb->prefix ."reservations WHERE name like '%$search%' OR email like '%$search%' OR notes like '%$search%' OR arrivalDate like '%$search%' $limit"; // Search query
				else $sql = "SELECT id, arrivalDate, name, email, number, childs, nights, notes, room, roomnumber, country, special, approve, price, custom, customp FROM ".$wpdb->prefix ."reservations WHERE $type $monthsql $roomsql $specialsql $zeichen ORDER BY $ordersby $orders $limit";  // Main Table query
				$result = mysql_query($sql) or die (mysql_error());

				if(mysql_num_rows($result) > 0 ){

					$export_IDs='';
					while ($row = mysql_fetch_assoc($result)){
						$id=$row['id'];
						$name = $row['name'];
						$nights=$row['nights'];
						$person=$row['number'];
						$childs=$row['childs'];
						$special=$row['special'];
						$room=$row['room'];
						$rooms=__(get_the_title($room));

						if($nr%2==0) $class="alternate"; else $class="";
						$timpstampanf=strtotime($row['arrivalDate']);
						$anznights=86400*$nights;
						$timestampend=(86400*$nights)+$timpstampanf;

						if(in_array($row['email'], $regular_guest_array)){
							$highlightClass='highlight';
						} else $highlightClass='';
						
						$export_IDs.=$id.', ';
						
						if($time - $timpstampanf > 0 AND $time - $timestampend > 0) $sta = "er_res_old";
						elseif($time - $timpstampanf > 0 AND $time - $timestampend <= 0) $sta = "er_res_now";
						else $sta = "er_res_future";

						$nr++;
						?>
				<tr class="<?php echo $class.' '.$highlightClass; ?>" height="47px" <?php if($table_options['table_onmouseover'] == 1 && $row['approve'] == "yes" && !empty($row['roomnumber'])){ ?>onmouseover="fakeClick('<?php echo $timpstampanf; ?>', '<?php echo $timestampend; ?>', '<?php echo $row['room']; ?>', '<?php echo $row['roomnumber']; ?>', 'yellow');" onmouseout="changer()"<?php } ?>><!-- Main Table Body //-->
					<?php if($table_options['table_color'] == 1){ ?>
						<td class="<?php echo $sta; ?>" style="max-width:4px !important;padding:0px !important;"></td>
					<?php } if($table_options['table_bulk'] == 1){ ?>
						<td width="2%" style="text-align:center;vertical-align:middle;"><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $id;?>"></td>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
						<td  valign="top" class="row-title" valign="top" nowrap>
							<div class="test">
								<?php if($table_options['table_name'] == 1){ ?>
									<a href="admin.php?page=reservations&view=<?php echo $id;?>"><?php echo $name;?></a>
								<?php } if($table_options['table_id'] == 1) echo ' (#'.$id.')'; ?>
								<div class="test2" style="margin:5px 0 0px 0;">
									<a href="admin.php?page=reservations&edit=<?php echo $id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a> 
									<?php if(isset($typ) AND ($typ=="deleted" OR $typ=="pending")) { ?>| <a style="color:#28a70e;" href="admin.php?page=reservations&approve=<?php echo $id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a>
									<?php } if(!isset($typ) OR (isset($typ) AND ($typ=="active" or $typ=="pending"))) { ?> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&delete=<?php echo $id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a>
									<?php } if(isset($typ) AND $typ=="trash") { ?>| <a href="admin.php?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&bulkArr[]=<?php echo $id;?>&bulk=3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a><?php } ?> | <a href="admin.php?page=reservations&sendmail=<?php echo $id;?>"><?php echo __( 'Mail' , 'easyReservations' );?></a>
								</div>
							</div>
						</td>
					<?php } if($table_options['table_from'] == 1 || $table_options['table_to'] == 1 || $table_options['table_nights'] == 1){ ?>
						<td nowrap><?php if($table_options['table_from'] == 1) echo date("d.m.Y",$timpstampanf); if($table_options['table_from'] == 1 && $table_options['table_to'] == 1) echo '-';  if($table_options['table_to'] == 1) echo date("d.m.Y",$timestampend);?><?php if($table_options['table_nights'] == 1){ ?> <small>(<?php echo $nights; ?> <?php printf ( __( 'Nights' , 'easyReservations' ));?>)</small><?php } ?></td>
					<?php } if($table_options['table_email'] == 1){ ?>
						<td><a href="admin.php?page=reservations&sendmail=<?php echo $id; ?>"><?php echo $row['email'];?></a></td>
					<?php } if($table_options['table_persons'] == 1 || $table_options['table_childs'] == 1){ ?>
						<td style="text-align:center;"><?php if($table_options['table_name'] == 1) echo $person; if($table_options['table_from'] == 1 && $table_options['table_to'] == 1) echo ' / '; if($table_options['table_childs'] == 1) echo $childs; ?></td>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
						<td nowrap><?php if($table_options['table_room'] == 1) echo '<a href="admin.php?page=reservation-resources&room='.$room.'">'.__($rooms).'</a>'; if($table_options['table_exactly'] == 1 && isset($row['roomnumber'])) echo ' #'.$row['roomnumber']; ?></td>
					<?php }  if($table_options['table_offer'] == 1){  ?>
						<td nowrap><?php if($special > 0) echo '<a href="admin.php?page=reservation-resources&room='.$special.'">'.__(get_the_title($special)).'</a>'; else echo __( 'None' , 'easyReservations' ); ?></td>
					<?php }  if($table_options['table_country'] == 1){  ?>
						<td nowrap><?php if($special > 0) echo easyReservations_country_name( $row['country']); ?></td>
					<?php }  if($table_options['table_message'] == 1){ ?>
						<td><?php echo substr($row['notes'], 0, 36); ?></td>
					<?php }  if($table_options['table_custom'] == 1){ ?>
						<td><?php $customs = easyreservations_get_custom_array($row['custom']);
								if(!empty($customs)){
									foreach($customs as $custom){
										echo '<b>'.$custom['title'].':</b> '.$custom['value'].'<br>';
									}
								}?></td>
					<?php }  if($table_options['table_customp'] == 1){ ?>
						<td><?php $customs = easyreservations_get_custom_price_array($row['customp']);
								if(!empty($customs)){
									foreach($customs as $custom){
										echo '<b>'.$custom['title'].':</b> '.$custom['value'].' - '.reservations_format_money($custom['price'], 1).'<br>';
									}
								}?></td>
					<?php } if($table_options['table_paid'] == 1){  ?>
						<td nowrap style="text-align:right"><?php $theExplode = explode(";", $row['price']); if(isset($theExplode[1]) && $theExplode[1] > 0) echo reservations_format_money( $theExplode[1], 1); else echo reservations_format_money( '0', 1); ?></td>
					<?php }  if($table_options['table_price'] == 1){  ?>
						<td nowrap style="text-align:right"><?php echo easyreservations_get_price($id, 1); ?></td>
					<?php } ?>
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
					<?php if($table_options['table_bulk'] == 1){ ?><select name="bulk" id="bulk"><option select="selected" value="0"><?php echo __( 'Bulk Actions' ); ?></option><?php if((isset($typ) AND $typ!="trash") OR !isset($typ)) { ?><option value="1"><?php printf ( __( 'Move to Trash' , 'easyReservations' ));?></option><?php }  if(isset($typ) AND $typ=="trash") { ?><option value="2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></option><option value="3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></option><?php } ;?></select>  <input class="easySubmitButton-secondary" type="submit" value="<?php printf ( __( 'Apply' , 'easyReservations' ));?>" /></form><?php } ?>
				</td>
				<td style="width:33%;" nowrap> <!-- Pagination  //-->
					<?php if($items > 0) { ?><div class="tablenav" style="text-align:center; margin:0 115px 4px 0;"><div style="background:#ffffff;" class='tablenav-pages'><?php echo $p->show(); ?></div></div><?php } ?>
				</td>
				<td style="width:33%;margin-left: auto; margin-right: 0pt; text-align: right;"> <!-- Num Elements //-->
					<span class="displaying-nums"><?php echo $nr;?> <?php printf ( __( 'Elements' , 'easyReservations' ));?></span>
				</td>
			</tr>
		</table></form>
		<?php if( $show['show_new'] == 1 OR $show['show_upcoming'] == 1 ) require_once(dirname(__FILE__)."/easyReservations_admin_main_stats.php"); ?>
		<?php } if($show['show_upcoming']==1){ ?>
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
		<?php } if($show['show_new']==1){ ?>
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
		<?php } if($show['show_export']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:320px;float:left;margin-right:10px;clear:none;">
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
		$dateToday = date("Y-m-d", time());
		$all_rooms = easyreservations_get_rooms();
		$rooms = 0;
		foreach ( $all_rooms as $room ) {
			$rooms += get_post_meta($room->ID, 'roomcount', true);
		}				
		$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE '$dateToday' BETWEEN arrivalDate AND arrivalDate + INTERVAL nights DAY AND approve='yes'"); // Search query 
	?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'What happen today' , 'easyReservations' ); ?><span style="float:right;font-family:Georgia;font-size:16px;vertical-align:middle" title="<?php echo __( 'workload today' , 'easyReservations' ); ?>"><?php echo round(100/$rooms*count($queryDepartures)); ?><span style="font-size:22px;vertical-align:middle">%<span></span>
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
									<th><?php echo __( 'Room' , 'easyReservations' ); ?></th>
									<th><?php echo __( 'Persons' , 'easyReservations' ); ?></th>
									<th style="text-align:right;"><?php echo __( 'Price' , 'easyReservations' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$queryArrivalers = $wpdb->get_results("SELECT id, name, room, number,childs FROM ".$wpdb->prefix ."reservations WHERE approve='yes'  AND arrivalDate = '$dateToday'"); // Search query
								$count = 0;

								foreach($queryArrivalers as $arrivler){
									if($count % 2 == 0) $class="odd";
									else $class="even";
								?>
									<tr class="<?php echo $class; ?>">
										<td><a href="admin.php?page=reservations&edit=<?php echo $arrivler->id; ?>"><?php echo $arrivler->name; ?></a></td>
										<td><?php echo get_the_title($arrivler->room); ?></td>
										<td><?php echo $arrivler->number; ?> (<?php echo $arrivler->childs; ?>)</td>
										<td style="text-align:right;"><?php echo easyreservations_get_price($arrivler->id,1); ?></td>
									</tr>
								<?php } ?>
							</tbody>
							<thead>
								<tr>
									<th colspan="4"><?php echo __( 'Depature today' , 'easyReservations' ); ?></th>
								</tr>
								<tr>
									<th> <?php echo __( 'Name' , 'easyReservations' ); ?></th>
									<th> <?php echo __( 'Room' , 'easyReservations' ); ?></th>
									<th> <?php echo __( 'Persons' , 'easyReservations' ); ?></th>
									<th style="text-align:right;"> <?php echo __( 'Price' , 'easyReservations' ); ?></th>
								</tr>
							</thead>

							<?php 
							$queryDepartures = $wpdb->get_results("SELECT id, name, room, number,childs FROM ".$wpdb->prefix ."reservations WHERE approve='yes'  AND arrivalDate + INTERVAL nights DAY = '$dateToday'"); // Search query
							foreach($queryDepartures as $depaturler){
								if($count % 2 == 0) $class="odd";
								else $class="even";
								?>
									<tr class="<?php echo $class; ?>">
										<td><a href="admin.php?page=reservations&edit=<?php echo $depaturler->id; ?>"><?php echo $depaturler->name; ?></a></td>
										<td><?php echo get_the_title($depaturler->room); ?></td>
										<td><?php echo $depaturler->number; ?> (<?php echo $depaturler->childs; ?>)</td>
										<td style="text-align:right;"><?php echo easyreservations_get_price($depaturler->id,1); ?></td>
									</tr>
							<?php } ?>
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
					<td><b><?php echo date("d.m.Y",$timpstampanf);?> - <?php echo date("d.m.Y",$timestampend);?> <small>(<?php echo $reservationNights;?>)</small></b></td>
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
					<td><b><?php echo __($rooms); ?></b></td>
				</tr>
				<?php $countryArray = easyReservations_country_array(); ?>
				<?php if(!empty($country)){ ?>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?>:</td> 
					<td><b><?php echo easyReservations_country_name($country); ?></b></td>
				</tr>
				<?php } ?>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Offer' , 'easyReservations' ));?>:</b></td> 
					<td><b><?php if($specials){ echo __($specials);} else { printf ( __( 'None' , 'easyReservations' )); }  ?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/money.png"> <?php printf ( __( 'Price' , 'easyReservations' ));?>:</b></td> 
					<td><b><?php 
					echo easyreservations_get_price($id); ?></b></td>
				</tr>
				<?php if(!empty($message_r)){ ?>
				<tr>
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
					if($thenumber%2==0) $class="alternate"; else $class="";
					echo '<tr class="'.$class.'">';
					echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/message.png"> '.__($customexp[0]).':</b></td>';
					echo '<td><b>'.$customexp[1].'</b></td></tr>';
					$thenumber++;
				}
				$explodecustoms=explode("&;&", $customsp);
				$customsmerge=array_values(array_filter($explodecustoms));
				foreach($customsmerge as $customp){
					$custompexp=explode("&:&", $customp);
					$explodeprice=explode(":", $custompexp[1]);
					$thenumber++;

					if($thenumber%2==0) $class=""; else $class="alternate";
					echo '<tr class="'.$class.'">';
					echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_IMAGES_DIR.'/money.png"> '.__($custompexp[0]).':</b></td>';
					echo '<td><b>'.$explodeprice[0].'</b>: <b>'.reservations_format_money($explodeprice[1], 1).'</b></td></tr>';
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
	$highestRoomCount=easyreservations_get_highest_roomcount();
	
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
							<td><input type="text" id="datepicker" style="width:73px" name="date" value="<?php echo date("d.m.Y",$timpstampanf); ?>" onchange="easyRes_sendReq_Price();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>"> <b>-</b> <input type="text" id="datepicker2" style="width:73px" name="dateend" value="<?php echo date("d.m.Y",$timestampend); ?>" onchange="easyRes_sendReq_Price();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?>:</td> 
							<td>
								<?php printf ( __( 'Adults' , 'easyReservations' ));?>:
								<select name="persons" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(1,50,$persons); ?></select>
								<?php printf ( __( 'Childs' , 'easyReservations' ));?>:
								<select name="childs" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(0,50,$childs); ?></select>
							</td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?>:</td> 
							<td>
								<select  name="room" id="room"  onchange="easyRes_sendReq_Price();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo reservations_get_room_options($room); ?></select> 
								<select id="roomexactly" name="roomexactly" onchange="changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo easyReservations_num_options(1,$highestRoomCount,$exactlyroom); ?></select>
							</td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png"> <?php printf ( __( 'Offer' , 'easyReservations' ));?>:</b></td> 
							<td><select  name="offer" id="offer" onchange="easyRes_sendReq_Price();"><option value="0" <?php selected($special, 0); ?>><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options($special); ?></select>
							</td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?>:</td> 
							<td><input type="text" name="email" value="<?php echo $mail_to;?>" onchange="easyRes_sendReq_Price();"></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?>:</td> 
							<td><select name="country"><option value="" <?php if($country=='') echo 'selected="selected"'; ?>><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyReservations_country_select($country); ?></select></td>
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
							<td nowrap><input type="checkbox" onclick="setPrice()" name="fixReservation" <?php if($pricexpl[0] != '') echo 'checked'; ?>> <span id="priceSetter"><?php if($pricexpl[0] != ''){ ?><input type="text" value="<?php echo $pricexpl[0]; ?>" name="priceset" style="width:60px;text-align: right;"><?php echo ' &'.get_option('reservations_currency').';'; } ?></span></td>
						</tr>
						<tr class="alternate">
							<td nowrap><?php printf ( __( 'Paid' , 'easyReservations' ));?></td>
							<td nowrap><input type="text" name="EDITwaspaid" value="<?php if(isset($pricexpl[1])) echo $pricexpl[1]; ?>" style="width:60px;text-align:right"> <?php echo ' &'.get_option('reservations_currency').';';?></td>
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
$highestRoomCount=easyreservations_get_highest_roomcount();
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
				<tr>
					<td nowrap style="width:45%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td> 
					<td><input type="text" name="name" align="middle"></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?>:</td> 
					<td><input type="text" id="datepicker" style="width:73px" name="date" onchange="easyRes_sendReq_Price();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>"> <b>-</b> <input type="text" id="datepicker2" style="width:73px" name="dateend" onchange="easyRes_sendReq_Price();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"></td>
				</tr>
				<tr valign="top">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td> 
					<td>
						<?php printf ( __( 'Adults' , 'easyReservations' ));?>:
						<select name="persons" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(1,50); ?></select>
						<?php printf ( __( 'Childs' , 'easyReservations' ));?>:
						<select name="childs" onchange="easyRes_sendReq_Price();"><?php echo easyReservations_num_options(0,50); ?></select>
				</tr>
				<tr valign="top" class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?></td>
					<td>
						<select id="room" name="room" onchange="easyRes_sendReq_Price();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo reservations_get_room_options(); ?></select>
						<select id="roomexactly" name="roomexactly" onchange="changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo easyReservations_num_options(1,$highestRoomCount); ?><option value=""><?php printf ( __( 'None' , 'easyReservations' ));?></option></select>
					</td>
				</tr>
				<tr valign="top">					
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png" > <?php printf ( __( 'Offer' , 'easyReservations' ));?></td>
					<td><select name="offer" onchange="easyRes_sendReq_Price();"><option value="0" select="selected"><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options(); ?></select>
					</td>
				</tr>
				<tr  class="alternate" >
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td> 
					<td><input type="text" name="email" onchange="easyRes_sendReq_Price();"></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?></td> 
					<td><select name="country"><option value=""><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyReservations_country_select(''); ?></select></td>
				</tr>

				<tr class="alternate">
					<td style="vertical-align:top;" nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Message' , 'easyReservations' ));?></b></td> 
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
						<td nowrap><input type="checkbox" onclick="setPrice();" name="fixReservation"> <?php printf ( __( 'Fix Price' , 'easyReservations' ));?> <br></td>
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
</form><?php if(isset($_POST['room-saver-to'])){ ?><script>fakeClick('<?php echo strtotime($_POST['room-saver-from']); ?>','<?php echo strtotime($_POST['room-saver-to']); ?>','<?php echo $_POST['room']; ?>','<?php echo $_POST['roomexactly']; ?>', '');setVals2(<?php echo $_POST['room'].','.$_POST['roomexactly']; ?>);document.getElementById('datepicker').value='<?php echo $_POST['room-saver-from']; ?>';document.getElementById('datepicker2').value='<?php echo $_POST['room-saver-to']; ?>';easyRes_sendReq_Price();</script><?php } //Set Room and Roomexactly after click on Overview and redirected to add 
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
	} else echo '<br><b>'.__( 'Add and set room post-category' , 'easyReservations' ).' <a href="admin.php?page=reservation-settings">here</a></b>';
} ?>