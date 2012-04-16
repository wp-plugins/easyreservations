<?php
	/*
		This file will generate our CSV table. There is nothing to display on this page, it is simply used
		to generate our CSV file and then exit. That way we won't be re-directed after pressing the export
		to CSV button on the previous page.
	*/

	require('../../../wp-config.php');
	//if (!wp_verify_nonce($_POST['easy-main-export'], 'easy-main-export' )) exit;

	if($_POST['export_tech'] == 'xls' || $_POST['export_tech'] == 'csv'){
		if($_POST['export_tech'] == 'xls'){
			$export_mode = true;
			require('./lib/export/export-xls.class.php');
			$filename = "easyReservation_".date("Y-m-d_H-i",time()).".xls"; // The file name you want any resulting file to be called.
			#create an instance of the class
			$xls = new ExportXLS($filename);
			$header = '';
		} else {
			$export_mode = false;
			$filename = $file."_".date("Y-m-d_H-i",time());
			$out = '';
		}

		$selects = 'id, ';
		if(isset($_POST['info_ID'])){
			if($export_mode) $header[] = 'ID';
			else $out .= 'ID, ';
		}
		if(isset($_POST['info_name'])){
			if($export_mode) $header[] = 'Name';
			else $out .= 'Name, ';
			$selects .= 'name, ';
		}
		if(isset($_POST['info_email'])){
			if($export_mode) $header[] = 'Email';
			else $out .= 'Email, ';
			$selects .= 'email, ';
		}
		if(isset($_POST['info_persons'])){
			if($export_mode){
				$header[] = 'Adults';
				$header[] = 'Childs';
			} else $out .= 'Adults, Childs, ';
			$selects .= 'number, childs, ';
		}
		if(isset($_POST['info_date'])){
			if($export_mode){
				$header[] = 'From';
				$header[] = 'To';
			} else $out .= 'From, To, ';
			$selects .= 'arrivalDate, nights, ';
		}
		if(isset($_POST['info_nights'])){
			if($export_mode) $header[] = 'Nights';
			else $out .= 'Nights, ';
		}
		if(isset($_POST['info_reservated'])){
			if($export_mode) $header[] = 'Reserved';
			else $out .= 'Reserved, ';
			$selects .= 'reservated, ';
		}
		if(isset($_POST['info_country'])){
			if($export_mode) $header[] = 'Country';
			else $out .= 'Country, ';
			$selects .= 'country, ';
		}
		if(isset($_POST['info_status'])){
			if($export_mode) $header[] = 'Status';
			else $out .= 'Status, ';
			$selects .= 'approve, ';
		}
		if(isset($_POST['info_room'])){
			if($export_mode) $header[] = 'Room';
			else $out .= 'Room, ';
			$selects .= 'room, ';
		}
		if(isset($_POST['info_roomnumber'])){
			if($export_mode) $header[] = 'Roomnumber';
			else $out .= 'Roomnumber, ';
			$selects .= 'roomnumber, ';
		}
		if(isset($_POST['info_offer'])){
			if($export_mode) $header[] = 'Offer';
			else $out .= 'Offer, ';
			$selects .= 'special, ';
		}
		if(isset($_POST['info_note'])){
			if($export_mode) $header[] = 'Note';
			else $out .= 'Note, ';
			$selects .= 'notes, ';
		}
		if(isset($_POST['info_price'])){
			if($export_mode) {
				$header[] = 'Price';
				$header[] = 'Paid';
			} else $out .= 'Price, Paid, ';
			$selects .= 'price, ';
		}
		if(isset($_POST['info_custom'])){
			$selects .= 'custom, ';
		}

		$selects = substr($selects,0,-2);

		if(isset($_POST['export_type']) && $_POST['export_type'] == 'tab'){
			global $wpdb;
			$IDs = substr($_POST['easy-export-id-field'],0,-2);
			$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations WHERE id IN ($IDs)";
		} elseif(isset($_POST['export_type']) && $_POST['export_type'] == 'all'){
			global $wpdb;
			$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations";
		} elseif(isset($_POST['export_type']) && $_POST['export_type'] == 'sel'){
			
			if(isset($_POST['approved'])) $status .= "OR approve = 'yes' ";
			if(isset($_POST['rejected'])) $status .= "OR approve = 'no' ";
			if(isset($_POST['trashed'])) $status .= "OR approve = 'del' ";
			if(isset($_POST['pending'])) $status .= "OR approve = '' ";
			$status = substr($status,2);

			if(isset($_POST['past'])) $time .= "OR (arrivalDate < DATE(NOW()) AND DATE(NOW()) NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
			if(isset($_POST['present'])) $time .= "OR DATE(NOW()) BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ";
			if(isset($_POST['future'])) $time .= "OR (arrivalDate > DATE(NOW()) AND DATE(NOW()) NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
			$time = substr($time,2);

			global $wpdb;
			$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations WHERE ($status) AND ($time)";
		}
		$reservationsExportArray = $wpdb->get_results($sql_reservations);
		
		if(isset($_POST['info_custom'])){
			$the_customs_titles = '';$the_customs = '';
			foreach($reservationsExportArray as $key => $exportReservations){
				
				$customs = easyreservations_get_customs($exportReservations->custom);
				foreach($customs as $custom){
					$the_customs[$custom['title']][$key] = $custom['value'];
					$the_customs_titles[] = $custom['title'];
				}
			}
			$the_customs_titles = array_unique($the_customs_titles);
			foreach($the_customs_titles as $key => $custom_title){
				if(!empty($custom_title)){
					if($export_mode) $header[] = $custom_title;
					else $out .= $custom_title.', ';
				}
				else unset($the_customs_titles[$key]);
			}
		}
		if($export_mode){
			$xls->addHeader($header);
			foreach($reservationsExportArray as $count => $exportReservations){
				if($count > 0) $row = array();
				if(isset($_POST['info_ID'])) $row[] = $exportReservations->id;
				if(isset($exportReservations->name)) $row[] = $exportReservations->name;
				if(isset($exportReservations->email)) $row[] = $exportReservations->email;
				if(isset($exportReservations->number)) $row[] = $exportReservations->number;
				if(isset($exportReservations->childs)) $row[] = $exportReservations->childs;
				if(isset($exportReservations->arrivalDate)){ $row[] = date(RESERVATIONS_DATE_FORMAT, strtotime($exportReservations->arrivalDate)); $row[] = date(RESERVATIONS_DATE_FORMAT, strtotime($exportReservations->arrivalDate)+(86400*$exportReservations->nights)); }
				if(isset($_POST['info_nights'])) $row[] = $exportReservations->nights;
				if(isset($exportReservations->reservated)) $row[] = date(RESERVATIONS_DATE_FORMAT, strtotime($exportReservations->reservated));
				if(isset($exportReservations->country)) $row[] = easyReservations_country_name($exportReservations->country);
				if(isset($exportReservations->approve)){
					if($exportReservations->approve == '') $status='Pending';
					elseif($exportReservations->approve == 'yes') $status='Approved';
					elseif($exportReservations->approve == 'no') $status='Rejected';
					elseif($exportReservations->approve == 'del') $status='Trashed';
					$row[] = $status;
				}
				if(isset($exportReservations->room)) $row[] = str_replace("Private: ", "", get_the_title($exportReservations->room));
				if(isset($exportReservations->roomnumber)) $row[] = easyreservations_get_roomname($exportReservations->roomnumber, $exportReservations->room);
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
					if(isset($priceExpl[1]) && $priceExpl[1] != '') $exportPaid = $priceExpl[1];
					else $exportPaid = 0;

					$row[] = $exportPrice;
					$row[] = $exportPaid;
				}
				if(isset($exportReservations->custom)){
					foreach($the_customs_titles as $key => $custom_title){
						if(isset($the_customs[$custom_title][$count])) $row[] = $the_customs[$custom_title][$count];
						else $row[] = '';
					}
				}
				$xls->addRow($row);
			}
			$xls->sendFile();
		} else {
			
			$out = substr($out,0,-2)."\n";
		foreach($reservationsExportArray as $exportReservations){
			if(isset($_POST['info_ID'])) $out .= $exportReservations->id .', ';
			if(isset($exportReservations->name)) $out .= $exportReservations->name .', ';
			if(isset($exportReservations->email)) $out .= $exportReservations->email .', ';
			if(isset($exportReservations->number)) $out .= $exportReservations->number .', ';
			if(isset($exportReservations->childs)) $out .= $exportReservations->childs .', ';
			if(isset($exportReservations->arrivalDate)) $out .= date(RESERVATIONS_DATE_FORMAT, strtotime($exportReservations->arrivalDate)).', '.date(RESERVATIONS_DATE_FORMAT, strtotime($exportReservations->arrivalDate)+(86400*$exportReservations->nights)).', ';
			if(isset($exportReservations->nights)) $out .= $exportReservations->nights .', ';
			if(isset($exportReservations->reservated)) $out .= $exportReservations->reservated .', ';
			if(isset($exportReservations->country)) $out .= easyReservations_country_name($exportReservations->country) .', ';
			if(isset($exportReservations->approve)){
				if($exportReservations->approve == '') $status='Pending';
				elseif($exportReservations->approve == 'yes') $status='Approved';
				elseif($exportReservations->approve == 'no') $status='Rejected';
				elseif($exportReservations->approve == 'del') $status='Trashed';
				$out .= $status.', ';
			}
			if(isset($exportReservations->room)) $out .= str_replace("Private: ", "", get_the_title($exportReservations->room)).', ';
			if(isset($exportReservations->special)){
				if($exportReservations->special == 0) $offer='None';
				else $offer=get_the_title($exportReservations->special);
				$out .= $offer.', ';
			}
			if(isset($exportReservations->roomnumber)) $out .= easyreservations_get_roomname($exportReservations->roomnumber, $exportReservations->room).', ';
			if(isset($exportReservations->notes)) $out .= str_replace(array(',', '/n'), '', $exportReservations->notes ).', ';
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

		//Now we're ready to create a file. This method generates a filename based on the current date & time.
		$filename = "easyReservation_".date("Y-m-d_H-i",time()).'.csv';

		//Generate the CSV file header
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($out));
		// Output to browser with appropriate mime type, you choose <img src="http://thetechnofreak.com/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
		header("Content-type: text/x-csv");
		//header("Content-type: text/csv");
		//header("Content-type: application/csv");		header("Content-disposition: attachment; filename=".$filename.".csv;");
		header("Content-Disposition: attachment; filename=$filename");

		//Print the contents of out to the generated file.
		print $out;

		}

	} elseif($_POST['export_tech'] == 'xml'){
		global $wpdb;

		$xml = new SimpleXMLElement("<?xml version='1.0' standalone='yes'?><Database/>");

		if(isset($_POST['export_type']) AND $_POST['export_type'] == 'tab'){
			global $wpdb;
			$IDs = substr($_POST['easy-export-id-field'],0,-2);
			$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id in($IDs)";
		} elseif(isset($_POST['export_type']) AND $_POST['export_type'] == 'all'){
			global $wpdb;
			$sql = "SELECT * FROM ".$wpdb->prefix ."reservations";
		} elseif(isset($_POST['export_type']) AND $_POST['export_type'] == 'sel'){
			$status = '';
			
			if(isset($_POST['approved'])) $status .= "OR approve = 'yes' ";
			if(isset($_POST['rejected'])) $status .= "OR approve = 'no' ";
			if(isset($_POST['trashed'])) $status .= "OR approve = 'del' ";
			if(isset($_POST['pending'])) $status .= "OR approve = '' ";
			if(!empty($status)){
				$status = '('.substr($status,2).')';
			}
			
			$time = '';

			if(isset($_POST['past'])) $time .= "OR (arrivalDate < DATE(NOW()) AND DATE(NOW()) NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
			if(isset($_POST['present'])) $time .= "OR DATE(NOW()) BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ";
			if(isset($_POST['future'])) $time .= "OR (arrivalDate > DATE(NOW()) AND DATE(NOW()) NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
			if(!empty($time)){
				$time = '('.substr($time,2).')';
			}

			global $wpdb;
			$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE $status AND $time";
		}

		$res =  $wpdb->get_results($sql);
		$i = 0;
		$dbversion = "1.6";
		$xml->addAttribute('xmlns:db','1.6');

		//$xml->addChild("database", $dbversion); 
		foreach($res as $data){
			$i++;
			$row = $xml->addChild('row');
			foreach ($data as $key => $val){
				$xml->addChild($key,utf8_encode($val));
			}

			$filename = "easyReservations_Backup_DB-".$dbversion."_".date("Y-m-d_H-i",time());

			//Generate the CSV file header
			header("Content-type: text/force-download");
			header("Content-disposition: xml" . date("Y-m-d") . ".xml");
			header("Content-disposition: filename=".$filename.".xml");

		}
		$row = $xml->addChild("row"); 

		$xml = $xml->asXML();
		print $xml;
	}
	//Exit the script
	exit;
?>