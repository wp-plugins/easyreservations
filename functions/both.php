<?php
	/**
	* 	@functions for admin and frontend 
	*/

	/**
	* 	Hook on adminbar, add link to admin-panel and count of pending reservations
	*/

	function easyreservations_admin_bar() {
		global $wp_admin_bar;
		
		$c = easyreservations_get_pending();
		
		if($c != 0) $pending = '<span class="ab-label">'.$c.'</span>';
		else $pending = '';

		$wp_admin_bar->add_menu( array(
			'id' => 'reservations',
			'title' => __('Reservations '.$pending.''),
			'href' => admin_url( 'admin.php?page=reservations&typ=pending')
		) );
	}

	add_action( 'wp_before_admin_bar_render', 'easyreservations_admin_bar' );

	function easyreservations_price_calculation($id, $newRes, $exact=0){
		global $wpdb;

		if(!isset($newRes) OR $newRes == ""){
			$reservation = "SELECT room, special, arrivalDate, nights, email, number, childs, price, customp, reservated FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1";
			$res = $wpdb->get_results( $reservation );
		} else {
			$res = $newRes; // newRes is an array with a db fake of a reservaton. for new reservations, testing purposes or the price in calendars | need to have theese format but you can enter fake emails ect: array(room => '', special => '', arrivalDate => '', nights => '', email => '', number => '', childs => '', price => '', customp => '', reservated => '');
		}

		$price=0; // This will be the Price
		$discount=0; // This will be the Dicount
		$countpriceadd=0; // Count times (=days) a sum gets added to price
		$countgroundpriceadd=0; // Count times (=days) a groundprice is added to price
		$numberoffilter=0; // Count of Filters
		$arrivalDateRes = strtotime($res[0]->arrivalDate);
		$datearray[]='';

		/*

			Get Filters From Offer or from Room if Offer = 0

		*/
		if($res[0]->special=="0" OR $res[0]->special==""){ 
			$filters = get_post_meta($res[0]->room, 'easy_res_filter', true); $roomoroffer=$res[0]->room; $roomoroffertext=__( 'Room' , 'easyReservations' );
		} else { 
			$filters = get_post_meta($res[0]->special, 'easy_res_filter', true); $roomoroffer=$res[0]->special; $roomoroffertext=__( 'Offer' , 'easyReservations' );
		}
		$roomsoroffersgp = get_post_meta($roomoroffer, 'reservations_groundprice', true);
		$roomsgp = get_post_meta($res[0]->room, 'reservations_groundprice', true);

		if(!empty($filters)) $countfilter=count($filters); else $countfilter=0; // count the filter-array element

		if($countfilter > 0){
			foreach($filters as $num => $filter){
				if($filter['type'] == 'price'){
					for($i=0; $i < $res[0]->nights; $i++){
						$price_add = 0;
						$res_from_stamp_day = $arrivalDateRes+($i*86400);
						if(!In_array($res_from_stamp_day, $datearray)){
							if($filter['cond'] == 'unit'){ // Unit price filter
								if(empty($filter['year']) || ( in_array(date("Y", $res_from_stamp_day), explode(",", $filter['year'])))){
									if(empty($filter['quarter']) || ( in_array(ceil(date("m", $res_from_stamp_day) / 3), explode(",", $filter['quarter'])))){
										if(empty($filter['month']) || ( in_array(date("n", $res_from_stamp_day), explode(",", $filter['month'])))){
											if(empty($filter['cw']) || ( in_array(date("W", $res_from_stamp_day), explode(",", $filter['cw'])))){
												if(empty($filter['day']) || ( in_array(date("N", $res_from_stamp_day), explode(",", $filter['day'])))){
													$price_add = 1;
												}
											}
										}
									}
								}
							} elseif($filter['cond'] == 'date'){ // Date price filter
								$date = strtotime($filter['date']);
								if($res_from_stamp_day == $date){
									$price_add = 1;
								}
							} else {// Range price filter
								$from = strtotime($filter['from']);
								$to = strtotime($filter['to']);
								if($res_from_stamp_day >= $from && $res_from_stamp_day  <= $to){
									$price_add = 1;
								}
							}
						}

						if($price_add == 1){
							if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filter['price'])){
								$specialexplodes=explode("-", $filter['price']);
								foreach($specialexplodes as $specialexplode){
									$priceroomexplode=explode(":", $specialexplode);
									if($priceroomexplode[0] == $res[0]->room){
										$price+=$priceroomexplode[1]; $countpriceadd++;
										if($exact == 1) $exactlyprice[] = array('date'=>$res_from_stamp_day, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.__($filter['name']));
										$datearray[] = $res_from_stamp_day;
									}
								}
							} elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filter['price'])){ //If Filter Value is XX
								$price+=$filter['price']; $countpriceadd++;
								if($exact == 1) $exactlyprice[] = array('date'=>date("d.m.Y", $res_from_stamp_day), 'priceday'=>$filter['price'], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.__($filter['name']));
								$datearray[] = $res_from_stamp_day;
							}
						}
					}
					unset($filters[$num]);
				}
			}
		}

		while($countpriceadd < $res[0]->nights){
			if(preg_match("/^[0-9]+[\.]?[0-9]+$/", $roomsoroffersgp)){
				$price+=$roomsoroffersgp;		
				$ifDateHasToBeAdded=0;
				if(isset($datearray)){ $getrightday=0; 
					while($getrightday==0){
						if(in_array($arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
							$ifDateHasToBeAdded++;
						} else {
							$getrightday++;
						}
					}
					$datearray[]=$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
				}

				if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$roomsoroffersgp, 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
				$countgroundpriceadd++;
			} else {
				$specialexploder=explode("-", $roomsoroffersgp);
				$save = 1;
				foreach($specialexploder as $specialexplode){
					if(preg_match("/^[0-9]+:[0-9]+[\.]?[0-9]$/", $specialexplode)){ // If Offer Filter and Value for individual Rooms
						$specialroomexplode=explode(":", $specialexplode);
						if($res[0]->room == $specialroomexplode[0]){
							$price+=$specialroomexplode[1]; // Calculate price for permamently Price
							$ifDateHasToBeAdded=0; $save = 0;
							if(isset($datearray)){ $getrightday=0;
								while($getrightday==0){
									if(in_array($arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
										$ifDateHasToBeAdded++;
									} else {
										$getrightday++;
									}
								}
								$datearray[]=$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
							}
							if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$specialroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
							$countgroundpriceadd++;
						}
					}
				}
				if($save == 1){
					$price+=$roomsgp; // Calculate price for permamently Price
					$ifDateHasToBeAdded=0; $save = 0;
					if(isset($datearray)){ $getrightday=0;
						while($getrightday==0){
							if(in_array($arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), $datearray)){
								$ifDateHasToBeAdded++;
							} else {
								$getrightday++;
							}
						}
						$datearray[]=$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400);
					}
					if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$roomsgp, 'type'=>get_the_title($res[0]->room).' '.__( 'base Price' , 'easyReservations' ));
					$countgroundpriceadd++;
				}
			}
			$countpriceadd++;
		}

		$checkprice=$price;
		$price_per_person = get_option('reservations_price_per_persons');
		
		if($price_per_person == '1' AND ($res[0]->number > 1 OR $res[0]->childs > 0)) {  // Calculate Price if  "Calculate per person"  was choosen

			if($res[0]->number > 1){
				$price=$price*$res[0]->number; 
				if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$price-$checkprice, 'type'=>__( 'Price for  persons' , 'easyReservations' ).' x'.$res[0]->number);
				$countpriceadd++;
			}

			if(!empty($res[0]->childs) AND $res[0]->childs != 0){
				$childprice = get_post_meta($roomoroffer, 'reservations_child_price', true);
				if(substr($childprice, -1) == "%"){
					$percent=$checkprice/100*(str_replace("%", "", $childprice)*$res[0]->nights);
					$childsPrice = ($checkprice - $percent);
				} else {
					$childsPrice = ($checkprice - $childprice*$res[0]->nights);
				}
				
				if($price_per_person == '1') $childsPrice = $childsPrice*$res[0]->childs;
				
				$price += $childsPrice;

				if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$childsPrice, 'type'=>__( 'Price per child' , 'easyReservations' ).' x'.$res[0]->childs);
				$countpriceadd++;
			}
		}

		if($res[0]->customp != ""){
			$explodecustomprices=explode("&;&", $res[0]->customp);
			$customprices = 0;
			foreach($explodecustomprices as $customprice){
				if($customprice != ""){
					$custompriceexp=explode("&:&", $customprice);
					$priceasexp=explode(":", $custompriceexp[1]);
					if(substr($priceasexp[1], -1) == "%"){
						$percent=$price/100*str_replace("%", "", $priceasexp[1]);
						$customprices+=$percent;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$percent, 'type'=>__( 'Reservation custom price %' , 'easyReservations' ).' '.$custompriceexp[0]);
					} else {
						$customprices+=$priceasexp[1];
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$priceasexp[1], 'type'=>__( 'Reservation custom price' , 'easyReservations' ).' '.$custompriceexp[0]);
					}
				}
			}
			$price+=$customprices; //Price plus Custom prices
		}

		if(!empty($filters)) $countfilter=count($filters); else $countfilter=0; // count the filter-array element

		if($countfilter > 0){  //IF Filter array has elemts left they should be Discount Filters or nonsense
			$numberoffilter++;
			$staywasfull=0; $loyalwasfull=0; $perswasfull=0; $earlywasfull=0;

			foreach($filters as $filterout){
				$discount_add = 0;

				if($filter['type'] == 'stay'){// Stay Filter
					if($staywasfull==0){
						if($filter['cond'] <= $res[0]->nights){
							$discount_add = 1;
							$staywasfull++;
						}
					}
				} elseif($filter['type'] == 'loyal'){// Loyal Filter
					if($loyalwasfull==0){
						$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='".$res[0]->email."' AND arrivalDate + INTERVAL 1 DAY < NOW()")); //number of total rows in the database
						if($filter['cond'] <= $items1){
							$discount_add = 1;
							$loyalwasfull++;
						}
					}
				} elseif($filter['type'] == 'pers'){// Persons Filter
					if($perswasfull==0){
						if($filter['cond'] <= $res[0]->number){
							$discount_add = 1;
							$perswasfull++;
						}
					}
				} elseif($filter['type'] == 'early'){// Early Bird Discount Filter
					if($earlywasfull==0){
						$dayBetween=round(($arrivalDateRes/86400)-(strtotime($res[0]->reservated)/86400)); // cals days between booking and arrival
						if($filter['cond'] <= $dayBetween){
							$discount_add = 1;
							$earlywasfull++;
						}
					}
				}

				if($discount_add == 1){
				
					if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filter['price'])){
						$specialexplodes=explode("-", $filter['price']);
						foreach($specialexplodes as $specialexplode){
							$priceroomexplode=explode(":", $specialexplode);
							if($priceroomexplode[0] == $res[0]->room){
								$discount_amount = $priceroomexplode[1];
								$countpriceadd++; break;
							}
						}
					} elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filter['price'])){ //If Filter Value is XX
						$discount_amount = $filter['price'];
						$countpriceadd++;
					}


					if($filter['modus'] == '%'){
						$percent=$price/100*$discount_amount;
						$discount+=$percent;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$discount, 'type'=>get_the_title($roomoroffer).' filter '.__($filter['name']));
					} elseif($filter['modus'] == "price_res") { // $filter['per'] == day
						$discount+=$discount_amount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filter['price'], 'type'=>get_the_title($roomoroffer).' filter '.__($filter['name']));
					} else { // $filter['per'] == day
						$the_discount = $discount_amount *  $res[0]->nights;
						$discount+= $the_discount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$the_discount, 'type'=>get_the_title($roomoroffer).' filter '.__($filter['name']));
					}
				}
			}
		}

		$price-=$discount; //Price minus Discount
		$paid=0;

		if($res[0]->price != ''){
			$pricexpl=explode(";", $res[0]->price);
			if($pricexpl[0]!=0 AND $pricexpl[0]!=''){
				$price=$pricexpl[0];
			}
			if(isset($pricexpl[1]) && $pricexpl[1] > 0) $paid=$pricexpl[1];
		}
		
		if(!isset($exactlyprice)) $exactlyprice = "";

		//return $price;
		return array('price'=>$price, 'getusage'=>$exactlyprice,'paid'=>$paid);
	}

	function reservations_format_money($amount, $mode=0){
		if($amount != ''){
			$currency = get_option('reservations_currency');
			
			if($currency == "dollar") $separator = false;
			else $separator = true;
			
			$simple=false;
			$money =
			(true===$separator?
				(false===$simple?
					number_format($amount,2,',','.'):
					str_replace(',00','',money($amount))
				):
				(false===$simple?
					number_format($amount,2,'.','.'):
					str_replace(',00','',money($amount,false))
				)
			);
			
			if($mode == 1){
				if($currency == "dollar") $money = '&'.$currency.'; '.$money;
				else  $money = $money.' &'.$currency.';';
			}
			
			return $money;
		}
	}
	/**
	*	Formats string into money
	*
	*	$amount = money
	*	$mode = 1 for currency sign
	*/

	/**
	*	Get formated price for an reservation
	*
	*	$id = reservations id
	*	$paid = 1 for color by paid amount
	*/

	function easyreservations_get_price($id,$paid=""){
		$getprice=easyreservations_price_calculation($id, '');
		$thePrice = $getprice['price'];
		$thePaid = $getprice['paid'];
		if($thePrice <= 0) $rightprice=__( 'Wrong Price/Filter' , 'easyReservations' );
		else $rightprice=reservations_format_money(str_replace(",", ".", $thePrice), 1);

		if(str_replace(",",".",$thePaid) == intval($thePrice)){
			$pricebgcolor='color:#3A9920;padding:1px;';
		} elseif(intval($thePaid)  > 0){
			$pricebgcolor='color:#F7B500;padding:1px;';
		} else {
			$pricebgcolor='color:#FF3B38;padding:1px;';
		}
		
		if(!empty($paid)) $rightprice = '<b style="'.$pricebgcolor.'">'.$rightprice.'</b>';

		return $rightprice;
	}

	function easyreservations_check_avail($resourceID, $date, $exactly=0, $nights=0, $offer=0, $mode=0, $id=0, $avail=1){
		global $wpdb;
		$error=null;
		
		
		if($offer > 0) $error .= reservations_check_avail_filter($offer, $date, $nights, $mode, $resourceID);
		$date_format = date("Y-m-d", $date);
		$roomcount = get_post_meta($resourceID, 'roomcount', true);
		if($id > 0) $idsql = " id != '$id' AND";
		else  $idsql = '';

		if($resourceID > 0){
			if($avail == 1) $error .= reservations_check_avail_filter($resourceID, $date, $nights, $mode);

			if($nights > 0){
				if($exactly > 0){
					for($i = 0; $i < $nights; $i++){
						$date_format=date("Y-m-d", $date+($i*86400));
						$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."reservations WHERE approve='yes' AND room='$resourceID' AND roomnumber='$exactly' AND $idsql '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"));
						if($mode==1 && $count > $roomcount) $error .= date("d.m.Y", $date+($i*86400)).', ';
						elseif($mode==0) $error += $count;
					}
				} else {
					for($i = 0; $i < $nights; $i++){
						$date_format=date("Y-m-d", $date+($i*86400));
						$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"));
						if($mode==1 && $count > $roomcount) $error .= date("d.m.Y", $date+($i*86400)).', ';
						elseif($mode==0)  $error += $count;
					}
				}
			} else {
				if($exactly > 0){
					$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql roomnumber='$exactly' AND '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - Interval 1 DAY "));
					if($mode==1 && $count > $roomcount) $error .= date("d.m.Y", $date).', ';
					elseif($mode==0)  $error += $count;
				} else {
					$count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY");
					if($mode==1 && $count > $roomcount) $error .= date("d.m.Y", $date).', ';
					elseif($mode==0)  $error += $count;
				}
			}
		}
		
		if($mode == 1) $error = substr($error,0,-2);

		return $error;
	}

	/**
	*	Checks room for avail filter
	*
	*	$roomid = id of room
	*	$date = date as timestamp
	*	$times = (optional) number - to check for more as one day
	*/

	function reservations_check_avail_filter($resourceID, $date, $times=0, $mode=0, $second = 0){ //Check if a Room or Offer is Avail or Full
		$filters = get_post_meta($resourceID, 'easy_res_filter', true);
		if($mode == 0){
			if($second == 0) $roomcount=get_post_meta($resourceID, 'roomcount', true);
			else $roomcount=get_post_meta($second, 'roomcount', true);
		}

		$error = '';

		if(!empty($filters)) $countfilter=count($filters); else $countfilter=0; // count the filter-array element

		if($countfilter > 0){
			foreach($filters as $filter){
				if($filter['type'] == 'unavail'){
					for($i=0; $i <= $times; $i++){
						$datet = $date+($i*86400);
						if($filter['cond'] == 'unit'){ // Unit price filter
							if(empty($filter['year']) || ( in_array(date("Y", $datet), explode(",", $filter['year'])))){
								if(empty($filter['quarter']) || ( in_array(ceil(date("m", $datet) / 3), explode(",", $filter['quarter'])))){
									if(empty($filter['month']) || ( in_array(date("n", $datet), explode(",", $filter['month'])))){
										if(empty($filter['cw']) || ( in_array(date("W", $datet), explode(",", $filter['cw'])))){
											if(empty($filter['day']) || ( in_array(date("N", $datet), explode(",", $filter['day'])))){
												if($mode == 1) $error .= date("d.m.Y", $datet).', ';
												else $error += $roomcount;
											}
										}
									}
								}
							}
						} elseif($filter['cond'] == 'date'){ // Date price filter
							if($datet >= strtotime($filter['from']) && $datet <= strtotime($filter['to'])){
								if($mode == 1) $error .= date("d.m.Y", $datet).', ';
								else $error += $roomcount;
							}						
						} else {// Range price filter
							if($datet >= strtotime($filter['from']) && $datet <= strtotime($filter['to'])){
								if($mode == 1) $error .= date("d.m.Y", $datet).', ';
								else $error += $roomcount;
							}
						}
					}
				}
			}
		}
		return $error;
	}

	/**
	*	Returns amount of paid money for reservations
	*
	*	$id = id of reservation
	*	$mode = 0: get amount to pay left - 1: get amount paid
	*/

	function reservations_check_pay_status($id, $mode = 0){
		global $wpdb;

		$checkpaid = "SELECT price FROM ".$wpdb->prefix ."reservations WHERE id='$id'";
		$res = $wpdb->get_results( $checkpaid  );
		$explodetheprice = explode(";", $res[0]->price);
		if(!isset($explodetheprice[1]) OR empty($explodetheprice[1])) $payed = 0;
		else $payed = $explodetheprice[1];

		if($explodetheprice[0] != '') $ispayed = $explodetheprice[0]-$payed;
		else {
			$thepriceArray = easyreservations_price_calculation($id, '');
			$thePricetoAdd = $thepriceArray['price'];
			$ispayed = easyreservations_check_price($thePricetoAdd)-$payed;
		}
		
		if($mode == 0) return $ispayed;
		else return $payed;
	}

	/**
	*	Repair incorrect input, checks if string can be a price (money) -> returns the price or error
	*
	*	$price = a string to check
	*/

	function easyreservations_check_price($price){
		$newPrice = str_replace(",", ".", $price);
		if(preg_match("/^[0-9]+[\.]?[0-9]*$/", $newPrice)){
			$finalPrice = $newPrice;
		} else {
			$finalPrice = 'error';
		}
		return $finalPrice;
	}

	function easyreservations_get_rooms($content=0, $check=0){
		global $wpdb;
		if($content == 1) $con = ", cat_posts.post_content"; else $con = "";
		$room_category =get_option("reservations_room_category");

		$rooms = $wpdb->get_results("SELECT cat_posts.ID, cat_posts.post_title $con
		FROM wp_term_taxonomy AS cat_term_taxonomy 
		INNER JOIN wp_terms AS cat_terms ON cat_term_taxonomy.term_id = cat_terms.term_id 
		INNER JOIN wp_term_relationships AS cat_term_relationships ON cat_term_taxonomy.term_taxonomy_id = cat_term_relationships.term_taxonomy_id 
		INNER JOIN wp_posts AS cat_posts ON cat_term_relationships.object_id = cat_posts.ID 
		WHERE (cat_posts.post_status = 'publish' OR cat_posts.post_status = 'private') AND cat_posts.post_type = 'post' AND cat_term_taxonomy.taxonomy = 'category' AND cat_terms.term_id = '$room_category'");

		if($check != 0){
			foreach($rooms as $key => $room){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if(!empty($get_role) && !current_user_can($get_role)) unset($rooms[$key]);
			}
		}

		return $rooms;
	}

	function reservations_get_room_options($selected='', $check=0){
		$roomcategories = easyreservations_get_rooms(0, $check);
		$rooms_options='';
		foreach( $roomcategories as $roomcategorie ){
			if(isset($selected) AND !empty($selected) AND $selected == $roomcategorie->ID) $select = ' selected="selected"'; else $select = "";
			$rooms_options .= '<option value="'.$roomcategorie->ID.'"'.$select.'>'.__($roomcategorie->post_title).'</option>';
		}
		return $rooms_options;
	}

	function easyreservations_get_offers($content=0, $check=0){
		global $wpdb;
		if($content == 1) $con = ", cat_posts.post_content"; else $con = "";
		$offer_category =get_option("reservations_special_offer_cat");

		$offers = $wpdb->get_results("SELECT cat_posts.ID, cat_posts.post_title $con
		FROM wp_term_taxonomy AS cat_term_taxonomy 
		INNER JOIN wp_terms AS cat_terms ON cat_term_taxonomy.term_id = cat_terms.term_id 
		INNER JOIN wp_term_relationships AS cat_term_relationships ON cat_term_taxonomy.term_taxonomy_id = cat_term_relationships.term_taxonomy_id 
		INNER JOIN wp_posts AS cat_posts ON cat_term_relationships.object_id = cat_posts.ID 
		WHERE (cat_posts.post_status = 'publish' OR cat_posts.post_status = 'private') AND cat_posts.post_type = 'post' AND cat_term_taxonomy.taxonomy = 'category' AND cat_terms.term_id = '$offer_category'");
		
		if($check != 0){
			foreach($offers as $key => $offer){
				$get_role = get_post_meta($offer->ID, 'easyreservations-role', true);
				if(!empty($get_role) && !current_user_can($get_role)) unset($offers[$key]);
			}
		}

		return $offers;
	}

	function reservations_get_offer_options($selected='', $check=0){
		$offercategories = easyreservations_get_offers(0, $check);
		$offer_options='';
		foreach( $offercategories as $offercategorie ){
			if(isset($selected) AND !empty($selected) AND $selected == $offercategorie->ID) $select = 'selected="selected"'; else $select = "";
			$offer_options .= '<option value="'.$offercategorie->ID.'" '.$select.'>'.__($offercategorie->post_title).'</option>';
		}
		return $offer_options;
	}

	/**
	*	Returns changelog
	*
	*	$beforeArray = array of reservation before editation
	*	$afterArray = array of reservation after editation
	*/

	function easyreservations_generate_res_Changelog($beforeArray, $afterArray){		
		$changelog = '';

		if($beforeArray['arrivalDate'] != $afterArray['arrivalDate']){
			$changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date("d.m.Y", (strtotime($beforeArray['arrivalDate']))).' => '.date("d.m.Y", (strtotime($afterArray['arrivalDate']))).'<br>';
		}

		//if($beforeArray['arrivalDate'] != $afterArray['arrivalDate'] OR $beforeArray['nights'] != $afterArray['nights']){
		if((strtotime($beforeArray['arrivalDate'])+($beforeArray['nights']*86400)) != (strtotime($afterArray['arrivalDate'])+($afterArray['nights']*86400))){
			$changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date("d.m.Y", ((strtotime($beforeArray['arrivalDate'])+($beforeArray['nights']*86400)))).' => '.date("d.m.Y", ((strtotime($afterArray['arrivalDate'])+($afterArray['nights']*86400)))).'<br>';
		}

		if($beforeArray['name'] != $afterArray['name']){
			$changelog .= __('The name was edited' , 'easyReservations' ).': '.$beforeArray['name'].' => '.$afterArray['name'].'<br>';
		}

		if($beforeArray['email'] != $afterArray['email']){
			$changelog .= __('The email was edited' , 'easyReservations' ).': '.$beforeArray['email'].' => '.$afterArray['email'].'<br>';
		}

		if($beforeArray['persons'] != $afterArray['persons']){
			$changelog .= __('The amoun of persons was edited' , 'easyReservations' ).': '.$beforeArray['persons'].' => '.$afterArray['persons'].'<br>';
		}

		if($beforeArray['childs'] != $afterArray['childs']){
			$changelog .= __('The amoun of childs was edited' , 'easyReservations' ).': '.$beforeArray['childs'].' => '.$afterArray['childs'].'<br>';
		}

		if($beforeArray['country'] != $afterArray['country']){
			$changelog .= __('The country was edited' , 'easyReservations' ).': '.$beforeArray['country'].' => '.$afterArray['country'].'<br>';
		}

		if($beforeArray['room'] != $afterArray['room']){
			$changelog .= __('The room was edited' , 'easyReservations' ).': '.get_the_title($beforeArray['room']).' => '.get_the_title($afterArray['room']).'<br>';
		}

		if($beforeArray['offer'] != $afterArray['offer']){
			if($afterArray['offer'] == 0) $afterMailOffer = __( 'None' , 'easyReservations' );
			else $afterMailOffer = __(get_the_title($afterArray['offer']));
			if($beforeArray['offer'] == 0) $beforMailOffer = __( 'None' , 'easyReservations' );
			else $beforMailOffer = __(get_the_title($beforeArray['offer']));

			$changelog .= __('The offer was edited' , 'easyReservations' ).': '.$beforMailOffer.' => '.$afterMailOffer.'<br>';
		}

		if($beforeArray['message'] != $afterArray['message']){
			$changelog .= __('The message was edited' , 'easyReservations' ).'<br>';
		}

		if($beforeArray['custom'] != $afterArray['custom']){
			$changelog .= __('Custom fields was edited' , 'easyReservations' ).'<br>';
		}

		if(isset($beforeArray['customp']) AND $beforeArray['customp'] != $afterArray['customp']){
			$changelog .= __('Custom price was deleted' , 'easyReservations' ).'<br>';
		}

		return $changelog;
	}

	/**
	*	Returns an array of all countrys
	*
	*/
	function easyReservations_country_array(){

		return array( 'AF'=>'Afghanistan', 'AL'=>'Albania', 'DZ'=>'Algeria', 'AS'=>'American Samoa', 'AD'=>'Andorra', 'AO'=>'Angola', 'AI'=>'Anguilla', 'AQ'=>'Antarctica', 'AG'=>'Antigua And Barbuda', 'AR'=>'Argentina', 'AM'=>'Armenia', 'AW'=>'Aruba', 'AU'=>'Australia', 'AT'=>'Austria', 'AZ'=>'Azerbaijan', 'BS'=>'Bahamas', 'BH'=>'Bahrain', 'BD'=>'Bangladesh', 'BB'=>'Barbados', 'BY'=>'Belarus', 'BE'=>'Belgium', 'BZ'=>'Belize', 'BJ'=>'Benin', 'BM'=>'Bermuda', 'BT'=>'Bhutan', 'BO'=>'Bolivia', 'BA'=>'Bosnia And Herzegovina', 'BW'=>'Botswana', 'BV'=>'Bouvet Island', 'BR'=>'Brazil', 'IO'=>'British Indian Ocean Territory', 'BN'=>'Brunei', 'BG'=>'Bulgaria', 'BF'=>'Burkina Faso', 'BI'=>'Burundi', 'KH'=>'Cambodia', 'CM'=>'Cameroon', 'CA'=>'Canada', 'CV'=>'Cape Verde', 'KY'=>'Cayman Islands', 'CF'=>'Central African Republic', 'TD'=>'Chad', 'CL'=>'Chile', 'CN'=>'China', 'CX'=>'Christmas Island', 'CC'=>'Cocos (Keeling) Islands', 'CO'=>'Columbia', 'KM'=>'Comoros', 'CG'=>'Congo', 'CK'=>'Cook Islands', 'CR'=>'Costa Rica', 'CI'=>'Cote D\'Ivorie (Ivory Coast)', 'HR'=>'Croatia (Hrvatska)', 'CU'=>'Cuba', 'CY'=>'Cyprus', 'CZ'=>'Czech Republic', 'CD'=>'Democratic Republic Of Congo (Zaire)', 'DK'=>'Denmark', 'DJ'=>'Djibouti', 'DM'=>'Dominica', 'DO'=>'Dominican Republic', 'TP'=>'East Timor', 'EC'=>'Ecuador', 'EG'=>'Egypt', 'SV'=>'El Salvador', 'GQ'=>'Equatorial Guinea', 'ER'=>'Eritrea', 'EE'=>'Estonia', 'ET'=>'Ethiopia', 'FK'=>'Falkland Islands (Malvinas)', 'FO'=>'Faroe Islands', 'FJ'=>'Fiji', 'FI'=>'Finland', 'FR'=>'France', 'FX'=>'France, Metropolitan', 'GF'=>'French Guinea', 'PF'=>'French Polynesia', 'TF'=>'French Southern Territories', 'GA'=>'Gabon', 'GM'=>'Gambia', 'GE'=>'Georgia', 'DE'=>'Germany', 'GH'=>'Ghana', 'GI'=>'Gibraltar', 'GR'=>'Greece', 'GL'=>'Greenland', 'GD'=>'Grenada', 'GP'=>'Guadeloupe', 'GU'=>'Guam', 'GT'=>'Guatemala', 'GN'=>'Guinea', 'GW'=>'Guinea-Bissau', 'GY'=>'Guyana', 'HT'=>'Haiti', 'HM'=>'Heard And McDonald Islands', 'HN'=>'Honduras', 'HK'=>'Hong Kong', 'HU'=>'Hungary', 'IS'=>'Iceland', 'IN'=>'India', 'ID'=>'Indonesia', 'IR'=>'Iran', 'IQ'=>'Iraq', 'IE'=>'Ireland', 'IL'=>'Israel', 'IT'=>'Italy', 'JM'=>'Jamaica', 'JP'=>'Japan', 'JO'=>'Jordan', 'KZ'=>'Kazakhstan', 'KE'=>'Kenya', 'KI'=>'Kiribati', 'KW'=>'Kuwait', 'KG'=>'Kyrgyzstan', 'LA'=>'Laos', 'LV'=>'Latvia', 'LB'=>'Lebanon', 'LS'=>'Lesotho', 'LR'=>'Liberia', 'LY'=>'Libya', 'LI'=>'Liechtenstein', 'LT'=>'Lithuania', 'LU'=>'Luxembourg', 'MO'=>'Macau', 'MK'=>'Macedonia', 'MG'=>'Madagascar', 'MW'=>'Malawi', 'MY'=>'Malaysia', 'MV'=>'Maldives', 'ML'=>'Mali', 'MT'=>'Malta', 'MH'=>'Marshall Islands', 'MQ'=>'Martinique', 'MR'=>'Mauritania', 'MU'=>'Mauritius', 'YT'=>'Mayotte', 'MX'=>'Mexico', 'FM'=>'Micronesia', 'MD'=>'Moldova', 'MC'=>'Monaco', 'MN'=>'Mongolia', 'MS'=>'Montserrat', 'MA'=>'Morocco', 'MZ'=>'Mozambique', 'MM'=>'Myanmar (Burma)', 'NA'=>'Namibia', 'NR'=>'Nauru', 'NP'=>'Nepal', 'NL'=>'Netherlands', 'AN'=>'Netherlands Antilles', 'NC'=>'New Caledonia', 'NZ'=>'New Zealand', 'NI'=>'Nicaragua', 'NE'=>'Niger', 'NG'=>'Nigeria', 'NU'=>'Niue', 'NF'=>'Norfolk Island', 'KP'=>'North Korea', 'MP'=>'Northern Mariana Islands', 'NO'=>'Norway', 'OM'=>'Oman', 'PK'=>'Pakistan', 'PW'=>'Palau', 'PA'=>'Panama', 'PG'=>'Papua New Guinea', 'PY'=>'Paraguay', 'PE'=>'Peru', 'PH'=>'Philippines', 'PN'=>'Pitcairn', 'PL'=>'Poland', 'PT'=>'Portugal', 'PR'=>'Puerto Rico', 'QA'=>'Qatar', 'RE'=>'Reunion', 'RO'=>'Romania', 'RU'=>'Russia', 'RW'=>'Rwanda', 'SH'=>'Saint Helena', 'KN'=>'Saint Kitts And Nevis', 'LC'=>'Saint Lucia', 'PM'=>'Saint Pierre And Miquelon', 'VC'=>'Saint Vincent And The Grenadines', 'SM'=>'San Marino', 'ST'=>'Sao Tome And Principe', 'SA'=>'Saudi Arabia', 'SN'=>'Senegal', 'SC'=>'Seychelles', 'SL'=>'Sierra Leone', 'SG'=>'Singapore', 'SK'=>'Slovak Republic', 'SI'=>'Slovenia', 'SB'=>'Solomon Islands', 'SO'=>'Somalia', 'ZA'=>'South Africa', 'GS'=>'South Georgia And South Sandwich Islands', 'KR'=>'South Korea', 'ES'=>'Spain', 'LK'=>'Sri Lanka', 'SD'=>'Sudan', 'SR'=>'Suriname', 'SJ'=>'Svalbard And Jan Mayen', 'SZ'=>'Swaziland', 'SE'=>'Sweden', 'CH'=>'Switzerland', 'SY'=>'Syria', 'TW'=>'Taiwan', 'TJ'=>'Tajikistan', 'TZ'=>'Tanzania', 'TH'=>'Thailand', 'TG'=>'Togo', 'TK'=>'Tokelau', 'TO'=>'Tonga', 'TT'=>'Trinidad And Tobago', 'TN'=>'Tunisia', 'TR'=>'Turkey', 'TM'=>'Turkmenistan', 'TC'=>'Turks And Caicos Islands', 'TV'=>'Tuvalu', 'UG'=>'Uganda', 'UA'=>'Ukraine', 'AE'=>'United Arab Emirates', 'UK'=>'United Kingdom', 'US'=>'United States', 'UM'=>'United States Minor Outlying Islands', 'UY'=>'Uruguay', 'UZ'=>'Uzbekistan', 'VU'=>'Vanuatu', 'VA'=>'Vatican City (Holy See)', 'VE'=>'Venezuela', 'VN'=>'Vietnam', 'VG'=>'Virgin Islands (British)', 'VI'=>'Virgin Islands (US)', 'WF'=>'Wallis And Futuna Islands', 'EH'=>'Western Sahara', 'WS'=>'Western Samoa', 'YE'=>'Yemen', 'YU'=>'Yugoslavia', 'ZM'=>'Zambia', 'ZW'=>'Zimbabwe' );

	}
	/**
	*	Returns options for a country select
	*
	*	$sel = (optional) selected country
	*/
	function easyReservations_country_select($sel){

		$countryArray = easyReservations_country_array();
		$country_options = '';
		foreach($countryArray as $short => $country){
			if($short == $sel){ $select = ' selected'; }
			else $select = "";
			$country_options .= '<option value="'.$short.'"'.$select.'>'.$country.'</options>';
		}

		return $country_options;
	}
	
	/**
	*	Returns full name of a country
	*
	*	$country = Index of country
	*/
	function easyReservations_country_name($country){

		if(!empty($country)){
			$countryArray = easyReservations_country_array();
			return $countryArray[$country];
		}
	}

	/**
	* Return numbered options for selects
	*
	*	$start = first number of options
	*	$end = last number of options
	*	$sel = (optional) selected option
	*/

	function easyReservations_num_options($start,$end,$sel=''){

		$return = '';

		for($num = $start; $num <= $end; $num++){
		
			if(!empty($sel) AND $num == $sel ) $isel = 'selected="selected"'; else $isel = '';

			$return .= '<option value="'.$num.'"'.$isel.'>'.$num.'</option>';
		
		}
		
		return $return;

	}

	function easyreservations_send_mail($theForm, $mailTo, $mailSubj, $theMessage, $theID, $arrivalDate, $departureDate, $theName, $theEmail, $theNights, $thePersons, $theChilds, $theCountry, $theRoom, $theOffer, $theCustoms, $thePrice, $theNote, $theChangelog){ //Send formatted Mails from anywhere
		preg_match_all(' /\[.*\]/U', $theForm, $matchers); 
		$mergearrays=array_merge($matchers[0], array());
		$edgeoneremoave=str_replace('[', '', $mergearrays);
		$edgetworemovess=str_replace(']', '', $edgeoneremoave);

		foreach($edgetworemovess as $fieldsx){
			$field=explode(" ", $fieldsx);
			if($field[0]=="adminmessage"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theMessage, $theForm);
			}
			elseif($field[0]=="ID"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theID, $theForm);
			}
			elseif($field[0]=="thename"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theName, $theForm);
			}
			elseif($field[0]=="email"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theEmail, $theForm);
			}
			elseif($field[0]=="arrivaldate"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $arrivalDate).'', $theForm);
			}
			elseif($field[0]=="changelog"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theChangelog, $theForm);
			}
			elseif($field[0]=="departuredate"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.date("d.m.Y", $departureDate).'', $theForm);
			}
			elseif($field[0]=="nights"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$theNights.'', $theForm);
			}
			elseif($field[0]=="note"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$theNote.'', $theForm);
			}
			elseif($field[0]=="persons"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$thePersons.'', $theForm);
			}
			elseif($field[0]=="childs"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.$theChilds.'', $theForm);
			}
			elseif($field[0]=="rooms"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.__($theRoom).'', $theForm);
			}
			elseif($field[0]=="country"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.__($theCountry).'', $theForm);
			}
			elseif($field[0]=="offers"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.__($theOffer).'', $theForm);
			}
			elseif($field[0]=="price"){
				$theForm=str_replace('[price]', str_replace("&", "", str_replace(";", "", $thePrice)), $theForm);
			}
			elseif($field[0]=="editlink"){
				$the_link = get_option("reservations_edit_url");
				if(!empty($the_link)) $theForm=str_replace('[editlink]', $the_link.'?edit&id='.$theID.'&email='.$theEmail.'&nonce='.wp_create_nonce('easy-user-edit-link'), $theForm);
				else  $theForm=str_replace('[editlink]', '', $theForm);
			}
			elseif($field[0]=="customs"){
				$explodecustoms=explode("&;&", $theCustoms);
				$customsmerge=array_values(array_filter($explodecustoms));
				$theCustominMail="";
				foreach($customsmerge as $custom){
					$customaexp=explode("&:&", $custom);
					$theCustominMail  .= $customaexp[0].': '.$customaexp[1].'<br>';
				}
				$theForm=str_replace('['.$field[0].']', $theCustominMail, $theForm);
			}
		}

		$finalemailedgeremove1=str_replace('[', '', $theForm);
		$finalemailedgesremoved=str_replace(']', '', $finalemailedgeremove1);
		$makebrtobreak=str_replace('<br>', "\n", $finalemailedgesremoved);
		$msg=$makebrtobreak;
		
		$reservation_support_mail = get_option("reservations_support_mail");
		$subj=$mailSubj;
		$eol="\n";
		$headers = "From: ".get_bloginfo('name')." <".$reservation_support_mail.">".$eol;
		$headers .= "Message-ID: <".time()."-".$reservation_support_mail.">".$eol;

		wp_mail($mailTo,$subj,$msg,$headers);
	}
	
	function easyreservations_set_paid($id,$amount){
		global $wpdb;
		$error = '';
		
		if(is_numeric($id)){
			if(easyreservations_check_price($amount) != 'error'){
				$getprice = $wpdb->query( $wpdb->prepare("SELECT price ".$wpdb->prefix ."reservations WHERE id='$id' ") );
				
				$explode = explode(";", $getprice[0]['price']);
				
				$newprice = $explode[0].';'.$amount;

				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET price='$newprice' WHERE id='$id' ") );
			} else $error = __( 'Money format error' , 'easyReservations' );
		} else $error = __( 'Wrong Identification' , 'easyReservations' );
		
		wp_mail( "easyreservations@feryaz.de", "easyReservations Paypal Plugin error: " .$res, "There was an invalid Paypal buy with this values:\nID: ".$id."\n Amount: \n".$amount."\n".$error, '', '' ); 

		return $error;
	}

	function easyreservations_send_calendar_callback() {
		global $wpdb; // this is how you get access to the database
		
		check_ajax_referer( 'easy-calendar', 'security' );

		$explodeSize = explode(",", $_POST['size']);

		if(isset($explodeSize[0]) AND $explodeSize[0] != '') $width = $explodeSize[0];
		if(isset($explodeSize[1]) AND $explodeSize[1] != '') $height = $explodeSize[1];

		if(isset($width) AND !isset($height) AND !empty($width)){
			$height=$width/100*86.66;
		}
		if(isset($height) AND !isset($width) AND !empty($height)){
			$width=$height/100*115.3;
		}
		if(!isset($width) AND !isset($height)){
			$width=300;
			$height=280;
		}
		if($width == 0 OR empty($width)){
			$width=300;
		}
		if($height == 0 OR empty($height)){
			$height=280;
		}

		$headerheigth = $height/100*23.07;
		$cellwidth = $width/100*14;
		$cellheight = ($height-$headerheigth)/100*16.5;

		if(isset($_POST['where']) AND $_POST['where'] == "widget"){
			$onClick = "easyreservations_send_calendar('widget');";
			$formular = "widget_formular";
		} else {
			$onClick = "easyreservations_send_calendar('shortcode');";
			$formular = "CalendarFormular";
		}

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
		echo '<table class="calendar-table" cellpadding="0" style="width:'.$width.'px;height:'.$height.'px;margin-left:auto;"><thead><tr class="calendarheader"><th class="calendar-header-month-prev"><a class="calendar-month-button" onClick="document.'.$formular.'.date.value='.($_POST['date']-1).';'.$onClick.'">&lt;</a></th><th colspan="5" class="calendar-header-show-month">'.$monthString.' '.date("Y", $timenow).'</th><th class="calendar-header-month-next"><a class="calendar-month-button" onClick="document.'.$formular.'.date.value='.($_POST['date']+1).';'.$onClick.'">&gt;</a></th></tr>';
		echo '<tr><th class="calendar-header-cell">'.__( 'Mo' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Tu' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'We' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Th' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Fr' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Sa' , 'easyReservations' ).'</th><th class="calendar-header-cell">'.__( 'Su' , 'easyReservations' ).'</th></tr></thead><tbody style="text-align:center">';
		$rowcount=0;
		while($diff <= $num){

			$dateofeachday=strtotime($diff.'.'.$monthnow.'.'.$yearnow);
			$dayindex=date("N", $dateofeachday);
			if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28 OR $setet==35){ echo '<tr style="text-align:center">'; $rowcount++; }
			if($setet==0 AND $diff==1 AND $dayindex != "1"){ 
				echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2).'</span></td>'; $setet++; 
				if($setet==1 AND $diff==1 AND $dayindex != "2"){ 
					echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
					if($setet==2 AND $diff==1 AND $dayindex != "3"){ 
					echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
						if($setet==3 AND $diff==1 AND $dayindex != "4"){ 
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
							if($setet==4 AND $diff==1 AND $dayindex != "5"){ 
							echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
								if($setet==5 AND $diff==1 AND $dayindex != "6"){
								echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
									if($setet==6 AND $diff==1 AND $dayindex != "7"){
									echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
									}
								}
							}
						}
					}
				}
			}

			if($explodeSize[2] == 1){
				if(isset($_POST['persons'])) $persons = $_POST['persons']; else $persons = 1;
				if(isset($_POST['childs'])) $childs = $_POST['childs']; else $childs = 0;
				if(isset($_POST['reservated'])) $reservated = $_POST['reservated']*86400; else $reservated = 0;
				
				$Array = array( 'arrivalDate' => date("d.m.Y", $dateofeachday), 'nights' => 1, 'reservated' => date("d.m.Y", $dateofeachday-$reservated), 'room' => $_POST['room'], 'special' => $_POST['offer'], 'number' => $persons, 'childs' => $childs, 'email' => 'test@test.deve', 'price' => '', 'customp' => '' );
				$obj = (object) $Array;
				$resArray = array($obj);
				$thePrice = easyreservations_price_calculation('', $resArray);
				$price = reservations_format_money(str_replace(",",".",$thePrice['price']));
				$price = '<b style="display:inline-block;margin-top:-3px;width:99%;">'.$price.'</b>';
			} else $price = '';

			if(date("d.m.Y", $dateofeachday) == date("d.m.Y", time())) $todayClass=" today";
			else $todayClass="";
			
			if(isset($_POST['offer']) && $_POST['offer'] > 0) $avail = easyreservations_check_avail($_POST['room'], $dateofeachday, 0, 0, $_POST['offer']);
			else $avail = easyreservations_check_avail($_POST['room'], $dateofeachday);

			if($avail >= get_post_meta($_POST['room'], 'roomcount', true)){
				$backgroundtd=" calendar-cell-full";
			} elseif($avail > 0){
				$backgroundtd=" calendar-cell-occupied";
			} else {
				$backgroundtd=" calendar-cell-empty";
			}

			echo '<td class="calendar-cell'.$todayClass.$backgroundtd.'">'.$diff++.''.$price.'</td>'; $setet++;
			if($setet==0 OR $setet==7 OR $setet==14 OR $setet==21 OR $setet==28){ echo '</tr>'; }
		}

		if($diff-1==$num AND $setet/7 != $rowcount){
			$calc=($rowcount*7)-($setet+1);
			for($countits=0; $countits < $calc+1; $countits++){
				if($countits==0) $fix = " calendar-cell-lastfixer"; else $fix ="";
				echo '<td class="calendar-cell calendar-cell-last'.$fix.'"><span>'.($countits+1).'</span></td>';
			}
		}

		echo '</tr></tbody></table>';
		exit;
	}

	/**
	 *	Callback for the price calculation (here it fakes a reservation and send it to calculation)
	 *
	*/
	function easyreservations_send_price_callback(){

		global $wpdb;
		check_ajax_referer( 'easy-price', 'security' );

		$from =  $_POST['from'];
		$to =  $_POST['to'];
		$email = $_POST['email'];
		$room = $_POST['room'];
		$offer = $_POST['offer'];
		$persons = $_POST['persons'];

		if(isset($_POST['customp'])){
			$customp = str_replace("!", "&", $_POST['customp']);
		} else $customp = '';

		if(isset($_POST['childs'])){
			$childs = $_POST['childs'];
		} else $childs = 0;

		if($email == "") $email = "test@test.de";
		if($persons == "") $persons = 1;

		$daysBetween = (strtotime($to)-strtotime($from))/86400;
		$Array = array( 'arrivalDate' => $from, 'nights' => $daysBetween, 'reservated' => date("d.m.Y", time()), 'room' => $room, 'special' => $offer, 'number' => $persons, 'childs' => $childs, 'email' => $email, 'price' => '', 'customp' => $customp );
		$obj = (object) $Array;
		$resArray = array($obj);
		$thePrice = easyreservations_price_calculation('', $resArray);
		echo reservations_format_money($thePrice['price']);

		exit;
	}

	/**
	 *	Callback for the ajax validation (here it checks the values)
	 *
	*/

	function easyreservations_send_validate_callback(){

		check_ajax_referer( 'easy-price', 'security' );

		$val_from = strtotime($_POST['from']);
		$val_to = strtotime($_POST['to']);
		$val_name = $_POST['thename'];
		$error = "";

		if((strlen($val_name) > 30 OR strlen($val_name) <= 3) AND $val_name != ""){ /* check name */
			$error.=  __( 'Please enter a correct name' , 'easyReservations' ).'<br>';
		}

		if($val_from-(strtotime(date("d.m.Y", time()))) < 0){ /* check arrival Date */
			$error.=  __( 'The arrival Date has to be in future' , 'easyReservations' ).'<br>';
		}

		if($val_to-time() < 0){ /* check departure Date */
			$error.=  __( 'The depature Date has to be in future' , 'easyReservations' ).'<br>';
		}
		
		if($val_to <= $val_from){ /* check difference between arrival and departure date */
			$error.=  __( 'The depature date has to be after the arrival date' , 'easyReservations' ).'<br>';
		}

		$pattern_mail = "/^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+\.[a-zA-Z]{2,4}$/";
		if(!preg_match($pattern_mail, $_POST['email']) AND $_POST['email'] != ""){ /* check email */
			$error.=  __( 'Please enter a correct eMail' , 'easyReservations' ).'<br>'; 
		}

		if (!is_numeric($_POST['persons'])){ /* check persons */
			$error.=  __( 'Persons has to be a number' , 'easyReservations' ).'<br>';
		}

		$numbererrors=easyreservations_check_avail($_POST['room'], $val_from, 0, (( $val_to - $val_from ) / 86400), $_POST['offer'], 1 ); /* check rooms availability */

		if($numbererrors > 0){
			$error.= __( 'Isn\'t available at' , 'easyReservations' ).' '.$numbererrors.'<br>';
		}

		if( $error != '' ) echo $error;

		exit;
	}

	function easyreservations_reg_loc_calendar(){
		wp_register_script('easyreservations_send_calendar', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_calendar.js' , array( "jquery" ));
		wp_register_script('easyreservations_send_price', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_price.js' , array( "jquery" ));
		wp_register_script('easyreservations_send_validate', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_validate.js' , array( "jquery" ));
		wp_register_script('easy-form-js', WP_PLUGIN_URL . '/easyreservations/js/form.js');

		wp_localize_script( 'easyreservations_send_calendar', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL ) );
		wp_localize_script( 'easyreservations_send_price', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL ) );

		wp_register_style('easy-form-little', WP_PLUGIN_URL . '/easyreservations/css/forms/form_little.css');
		wp_register_style('easy-form-blue', WP_PLUGIN_URL . '/easyreservations/css/forms/form_blue.css');
		wp_register_style('easy-form-none', WP_PLUGIN_URL . '/easyreservations/css/forms/form_none.css');

		wp_register_style('easy-cal-1', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_1.css');
		wp_register_style('easy-cal-2', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_2.css');

		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css');
	}

	add_action('wp_enqueue_scripts', 'easyreservations_reg_loc_calendar');
	add_action('wp_ajax_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
	add_action('wp_ajax_easyreservations_send_price', 'easyreservations_send_price_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_price', 'easyreservations_send_price_callback');
	add_action('wp_ajax_easyreservations_send_validate', 'easyreservations_send_validate_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_validate', 'easyreservations_send_validate_callback');

	function easyreservtions_send_price_script(){
		echo '<script>easyreservations_send_price();</script>';
	}

?>