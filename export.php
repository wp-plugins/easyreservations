<?php
	/*
		This file will generate our CSV table. There is nothing to display on this page, it is simply used
		to generate our CSV file and then exit. That way we won't be re-directed after pressing the export
		to CSV button on the previous page.
	*/

	require('../../../wp-blog-header.php');
	
	if($_POST['export_tech'] == 'xls'){
	
		require('/lib/export/export-xls.class.php');
		$filename = "easyReservation_".date("Y-m-d_H-i",time()).".xls"; // The file name you want any resulting file to be called.

		#create an instance of the class
		$xls = new ExportXLS($filename);

		$selects = 'id, ';
		if(isset($_POST['info_ID'])){
			$header[] = 'ID';
		}
		if(isset($_POST['info_name'])){
			$header[] = 'Name';
			$selects .= 'name, ';
		}
		if(isset($_POST['info_email'])){
			$header[] = 'Email';
			$selects .= 'email, ';
		}
		if(isset($_POST['info_persons'])){
			$header[] = 'Persons';
			$selects .= 'number, ';
		}
		if(isset($_POST['info_date'])){
			$header[] = 'From';
			$header[] = 'To';
			$selects .= 'arrivalDate, ';
		}
		if(isset($_POST['info_nights'])){
			$header[] = 'Nights';
			$selects .= 'nights, ';
		}
		if(isset($_POST['info_country'])){
			$header[] = 'Country';
			$selects .= 'country, ';
		}
		if(isset($_POST['info_status'])){
			$header[] = 'Status';
			$selects .= 'approve, ';
		}
		if(isset($_POST['info_room'])){
			$header[] = 'Room';
			$selects .= 'room, roomnumber, ';
		}
		if(isset($_POST['info_offer'])){
			$header[] = 'Offer';
			$selects .= 'special, ';
		}
		if(isset($_POST['info_note'])){
			$header[] = 'Note';
			$selects .= 'notes, ';
		}
		if(isset($_POST['info_price'])){
			$header[] = 'Price';
			$header[] = 'Paid';
			$selects .= 'price, ';
		}

		$selects = substr($selects,0,-2);
		$xls->addHeader($header);

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

		foreach($reservationsExportArray as $count => $exportReservations){
			if($count > 0) $row = array();
			if(isset($_POST['info_ID'])) $row[] = $exportReservations->id;
			if(isset($exportReservations->name)) $row[] = $exportReservations->name;
			if(isset($exportReservations->email)) $row[] = $exportReservations->email;
			if(isset($exportReservations->number)) $row[] = $exportReservations->number;
			if(isset($exportReservations->arrivalDate)){ $row[] = date("d.m.Y", strtotime($exportReservations->arrivalDate)); $row[] = date("d.m.Y", strtotime($exportReservations->arrivalDate)+(86400*$exportReservations->nights)); }
			if(isset($exportReservations->nights)) $row[] = $exportReservations->nights;
			if(isset($exportReservations->country)) $row[] = easyReservations_country_name($exportReservations->country);
			if(isset($exportReservations->approve)){
				if($exportReservations->approve == '') $status='Pending';
				elseif($exportReservations->approve == 'yes') $status='Approved';
				elseif($exportReservations->approve == 'no') $status='Rejected';
				elseif($exportReservations->approve == 'del') $status='Trashed';
				$row[] = $status;
			}
			if(isset($exportReservations->room)) $row[] = str_replace("Private: ", "", get_the_title($exportReservations->room)).' #'.$exportReservations->roomnumber;
			if(isset($exportReservations->special)){
				if($exportReservations->special == 0) $offer='None';
				else $offer=get_the_title($exportReservations->special);
				$row[] = $offer;
			}
			if(isset($exportReservations->notes)) $row[] = $exportReservations->notes;
			if(isset($exportReservations->price)){
				$priceExpl = explode(";", $exportReservations->price);
				if($priceExpl[0] != '') $exportPrice = $priceExpl[0];
				else {
					$priceFunction = easyreservations_price_calculation($exportReservations->id, '');
					$exportPrice = str_replace(',', '.', $priceFunction['price']);
				}
				if($priceExpl[1] != '') $exportPaid = $priceExpl[1];
				else $exportPaid = 0;

				$row[] = $exportPrice;
				$row[] = $exportPaid;
			}
			
			$xls->addRow($row);
			
		}

		$xls->sendFile();
	} else {
		$out = '';//First we'll generate an output variable called out. It'll have all of our text for the CSV file.

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
		if(isset($_POST['info_country'])){
			$out .= 'Country, ';
			$selects .= 'country, ';
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
			if(isset($exportReservations->country)) $out .= easyReservations_country_name($exportReservations->country) .', ';
			if(isset($exportReservations->approve)){
				if($exportReservations->approve == '') $status='Pending';
				elseif($exportReservations->approve == 'yes') $status='Approved';
				elseif($exportReservations->approve == 'no') $status='Rejected';
				elseif($exportReservations->approve == 'del') $status='Trashed';
				$out .= $status.', ';
			}
			if(isset($exportReservations->room)) $out .= str_replace("Private: ", "", get_the_title($exportReservations->room)).' #'.$exportReservations->roomnumber.', ';
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

		if(!isset($file)) $file = 'easyReservation';

		//Now we're ready to create a file. This method generates a filename based on the current date & time.
		$filename = $file."_".date("Y-m-d_H-i",time());

		//Generate the CSV file header
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		header("Content-disposition: filename=".$filename.".csv");

		//Print the contents of out to the generated file.
		print $out;
	}

	//Exit the script
	exit;

?>