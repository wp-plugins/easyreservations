<?php
	/*
		This file will generate our CSV table. There is nothing to display on this page, it is simply used
		to generate our CSV file and then exit. That way we won't be re-directed after pressing the export
		to CSV button on the previous page.
	*/

	//First we'll generate an output variable called out. It'll have all of our text for the CSV file.
	$out = '';
	require('../../../wp-blog-header.php');
	//Next we'll check to see if our variables posted and if they did we'll simply append them to out.

	$selects = 'id, ';
	if(isset($_POST['info_ID'])){
		$out .= 'ID, ';
	}
	if(isset($_POST['info_name'])){
		$out .= 'Name, ';
		$selects .= 'name, ';
	}
	if(isset($_POST['info_email'])){
		$out .= 'Email, ';
		$selects .= 'email, ';
	}
	if(isset($_POST['info_persons'])){
		$out .= 'Persons, ';
		$selects .= 'number, ';
	}
	if(isset($_POST['info_date'])){
		$out .= 'From, To, ';
		$selects .= 'arrivalDate, ';
	}
	if(isset($_POST['info_nights'])){
		$out .= 'Nights, ';
		$selects .= 'nights, ';
	}
	if(isset($_POST['info_status'])){
		$out .= 'Status, ';
		$selects .= 'approve, ';
	}
	if(isset($_POST['info_room'])){
		$out .= 'Room, ';
		$selects .= 'room, roomnumber, ';
	}
	if(isset($_POST['info_offer'])){
		$out .= 'Offer, ';
		$selects .= 'special, ';
	}
	if(isset($_POST['info_note'])){
		$out .= 'Note, ';
		$selects .= 'notes, ';
	}
	if(isset($_POST['info_price'])){
		$out .= 'Price, Paid, ';
		$selects .= 'price, ';
	}

	$selects = substr($selects,0,-2);
	$out = substr($out,0,-2);
	$out .= "\n";

	if(isset($_POST['export_type']) AND $_POST['export_type'] == 'tab'){
		global $wpdb;
		$IDs = substr($_POST['export_IDs'],0,-2);
		$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations WHERE id IN ($IDs)";
	} elseif(isset($_POST['export_type']) AND $_POST['export_type'] == 'all'){
		global $wpdb;
		$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations";
	} elseif(isset($_POST['export_type']) AND $_POST['export_type'] == 'sel'){
		
		if(isset($_POST['approved'])) $status .= "OR approve = 'yes' ";
		if(isset($_POST['rejected'])) $status .= "OR approve = 'no' ";
		if(isset($_POST['trashed'])) $status .= "OR approve = 'del' ";
		if(isset($_POST['pending'])) $status .= "OR approve = '' ";
		$status = substr($status,2);

		if(isset($_POST['past'])) $time .= "OR (arrivalDate < NOW() AND NOW() NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
		if(isset($_POST['present'])) $time .= "OR NOW() BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ";
		if(isset($_POST['future'])) $time .= "OR (arrivalDate > NOW() AND NOW() NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
		$time = substr($time,2);

		global $wpdb;
		$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations WHERE ($status) AND ($time)";
	}
	$reservationsExportArray = $wpdb->get_results($sql_reservations);

	foreach($reservationsExportArray as $exportReservations){
		if(isset($_POST['info_ID'])) $out .= $exportReservations->id .', ';
		if(isset($exportReservations->name)) $out .= $exportReservations->name .', ';
		if(isset($exportReservations->email)) $out .= $exportReservations->email .', ';
		if(isset($exportReservations->number)) $out .= $exportReservations->number .', ';
		if(isset($exportReservations->arrivalDate)) $out .= date("d.m.Y", strtotime($exportReservations->arrivalDate)).', '.date("d.m.Y", strtotime($exportReservations->arrivalDate)+(86400*$exportReservations->nights)).', ';
		if(isset($exportReservations->nights)) $out .= $exportReservations->nights .', ';
		if(isset($exportReservations->approve)){
			if($exportReservations->approve == '') $status='Pending';
			elseif($exportReservations->approve == 'yes') $status='Approved';
			elseif($exportReservations->approve == 'no') $status='Rejected';
			elseif($exportReservations->approve == 'del') $status='Trashed';
			$out .= $status.', ';
		}
		if(isset($exportReservations->room)) $out .= get_the_title($exportReservations->room).' #'.$exportReservations->roomnumber.', ';
		if(isset($exportReservations->special)){
			if($exportReservations->special == 0) $offer='None';
			else $offer=get_the_title($exportReservations->special);
			$out .= $offer.', ';
		}
		if(isset($exportReservations->notes)) $out .= $exportReservations->notes .', ';
		if(isset($exportReservations->price)){
			$priceExpl = explode(";", $exportReservations->price);
			if($priceExpl[0] != '') $exportPrice = $priceExpl[0];
			else {
				$priceFunction = easyreservations_price_calculation($exportReservations->id, '');
				$exportPrice = $priceFunction['price'];
			}
			if($priceExpl[1] != '') $exportPaid = $priceExpl[1];
			else $exportPaid = 0;

			$out .= $exportPrice.', '.$exportPaid.', ';
		}

		$out = substr($out,0,-2);
		$out .= "\n";
	}

	if(!isset($file)) $file = 'Reservations';

	//Now we're ready to create a file. This method generates a filename based on the current date & time.
	$filename = $file."_".date("Y-m-d_H-i",time());

	//Generate the CSV file header
	header("Content-type: application/vnd.ms-excel");
	header("Content-disposition: csv" . date("Y-m-d") . ".csv");
	header("Content-disposition: filename=".$filename.".csv");

	//Print the contents of out to the generated file.
	print $out;

	//Exit the script
	exit;

?>