<?php
	/**
	* 	@functions for admin and frontend 
	*/

	function easyreservation_resource_init() {
		$labels = array(
			'name' => _x('Resources', 'easyReservations'),
			'singular_name' => _x('Resource', 'easyReservations'),
			'add_new' => _x('Add Resource', 'easyReservations'),
			'add_new_item' => __('Add New Resource', 'easyReservations'),
			'edit_item' => __('Edit Resource', 'easyReservations'),
			'new_item' => __('New Resource', 'easyReservations'),
			'all_items' => __('All Resources', 'easyReservations'),
			'view_item' => __('View Resource', 'easyReservations'),
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => array('slug' => 'resource'), 
			'show_in_menu' => false, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => false, 
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields'  )
		); 
		register_post_type('easy-rooms',$args);
		register_post_type('easy-offers');
	}
	add_action( 'init', 'easyreservation_resource_init' );

	function easy_init_sessions() {
		if (!session_id()) {
			session_start();
		}
	}
	add_action('init', 'easy_init_sessions');

	function easyreservations_admin_bar() {
		global $wp_admin_bar;
		
		$c = easyreservations_get_pending();
		
		if($c != 0) $pending = '<span class="ab-label">'.$c.'</span>';
		else $pending = '';

		$wp_admin_bar->add_menu( array(
			'id' => 'reservations',
			'title' => '<span class="er-adminbar-icon"></span>'.$pending,
			'href' => admin_url( 'admin.php?page=reservations#pending'),
			'meta' => array('class' => 'er-adminbar-item')
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'reservations',
			'id' => 'reservations-new',
			'title' => 'New',
			'href' => admin_url( 'admin.php?page=reservations&add'),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'reservations',
			'id' => 'reservations-pending',
			'title' => 'Pending',
			'href' => admin_url( 'admin.php?page=reservations#pending'),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'reservations',
			'id' => 'reservations-nurrent',
			'title' => 'Current',
			'href' => admin_url( 'admin.php?page=reservations#current'),
		) );
	}

	add_action( 'wp_before_admin_bar_render', 'easyreservations_admin_bar' );

	function easyreservations_price_calculation($id, $newRes='', $exact=0){
		global $wpdb, $the_rooms_intervals_array;

		if(!isset($newRes) || $newRes == ""){
			$reservation = "SELECT arrival, departure, room, email, number, childs, price, customp, reservated FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1";
			$res = $wpdb->get_results( $reservation );
		} else {
			$res = $newRes; // newRes is an array with a db fake of a reservaton. for new reservations, testing purposes or the price in calendars | need to have theese format but you can enter fake emails ect: array(room => '', arrival => '', departure => '', email => '', number => '', childs => '', price => '', customp => '', reservated => '');
			$fake = 1;
		}

		$price=0; // This will be the Price
		$countpriceadd=0; // Count times (=days) a sum gets added to price
		$countgroundpriceadd=0; // Count times (=days) a groundprice is added to price
		$arrival = strtotime($res[0]->arrival);
		$departure = strtotime($res[0]->departure);
		$exactlyprice = "";
		$resources = $res[0]->room;

		if(preg_match('/\,/', $resources)) $resources = explode(',', $resources);
		elseif(!is_array($resources)) $resources = array($resources);

		foreach($resources as $resource){
		$datearray = '';
		$resource = $res[0]->room;
		$filters = get_post_meta($resource, 'easy_res_filter', true);
		$resource_groundprice = get_post_meta($resource, 'reservations_groundprice', true);
		$price_per_person = get_post_meta($resource, 'easy-resource-price', true);
		$resource_interval = $the_rooms_intervals_array[$resource];
		$nights = easyreservations_get_nights( $resource_interval, $arrival, $departure );

		if(!empty($filters)) $countfilter=count($filters); else $countfilter=0; // count the filter-array element

		if($countfilter > 0){
			foreach($filters as $num => $filter){	
				if($filter['type'] == 'price'){
					for($i=$arrival; $departure - $i >= $resource_interval/2 ; $i+=$resource_interval){
						$price_add = 0;
						if(!is_array($datearray) || !In_array($i, $datearray)){
							if($filter['cond'] == 'unit'){ // Unit price filter
								if(empty($filter['year']) || ( in_array(date("Y", $i), explode(",", $filter['year'])))){
									if(empty($filter['quarter']) || ( in_array(ceil(date("m", $i) / 3), explode(",", $filter['quarter'])))){
										if(empty($filter['month']) || ( in_array(date("n", $i), explode(",", $filter['month'])))){
											if(empty($filter['cw']) || ( in_array(date("W", $i), explode(",", $filter['cw'])))){
												if(empty($filter['day']) || ($resource_interval < 86500 &&  in_array(date("N", $i), explode(",", $filter['day'])))){
													if( !isset($filter['hour']) || empty($filter['hour']) || ($resource_interval < 3650 &&   in_array(date("H", $i), explode(",", $filter['hour'])))){
														$price_add = 1;
													}
												}
											}
										}
									}
								}
							} elseif($filter['cond'] == 'date'){ // Date price filter
								if(date("d.m.Y", $i) == date("d.m.Y", $filter['date']) && ($resource_interval > 3600 || date("H",$i) == date("H", $filter['date']))){
									$price_add = 1;
								}
							} else { // Range price filter
								$from = $filter['from'];
								$to = $filter['to'];
								if($i >= $from && $i  <= $to){
									$price_add = 1;
								}
							}
						}
						if($price_add == 1){
							$price+=$filter['price']; $countpriceadd++;
							if($exact == 1) $exactlyprice[] = array('date'=>$i, 'priceday'=>$filter['price'], 'type'=>get_the_title($resource).' '.__( ' Price Filter' , 'easyReservations' ).' '.__($filter['name']).' ('.$filter['cond'].')');
							$datearray[] = $i;
						}
					}
					unset($filters[$num]);
				}
			}
		}

		while($countpriceadd < $nights){
			$price+=$resource_groundprice;		
			$ifDateHasToBeAdded=0;
			if(isset($datearray)){ $getrightday=0;
				while($getrightday==0){
					if(is_array($datearray) && in_array($arrival+($countgroundpriceadd*$resource_interval)+($ifDateHasToBeAdded*$resource_interval), $datearray)){
						$ifDateHasToBeAdded++;
					} else {
						$getrightday++;
					}
				}
				$datearray[]=$arrival+($countgroundpriceadd*$resource_interval)+($ifDateHasToBeAdded*$resource_interval);
			}
			if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countgroundpriceadd*$resource_interval)+($ifDateHasToBeAdded*$resource_interval), 'priceday'=>$resource_groundprice, 'type'=>get_the_title($resource).' '.__( 'base Price' , 'easyReservations' ));
			$countgroundpriceadd++;
			$countpriceadd++;
		}

		$checkprice=$price;		
		if($price_per_person == 1 && ($res[0]->number > 1 || $res[0]->childs > 0)) {  // Calculate Price if  "Calculate per person"  was choosen

			if($res[0]->number > 1){
				$price=$price*$res[0]->number; 
				if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$price-$checkprice, 'type'=>__( 'Price for  persons' , 'easyReservations' ).' x'.$res[0]->number);
				$countpriceadd++;
			}

			if(!empty($res[0]->childs) && $res[0]->childs > 0){
				$childprice = get_post_meta($resource, 'reservations_child_price', true);
				if(substr($childprice, -1) == "%"){
						$percent=$checkprice/100*(str_replace("%", "", $childprice)*$nights);
					$childsPrice = ($checkprice - $percent);
				} else {
					$childsPrice = ($checkprice - $childprice*$nights);
				}

				if($price_per_person == 1) $childsPrice = $childsPrice*$res[0]->childs;
				$price += $childsPrice;
				if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$childsPrice, 'type'=>__( 'Price per child' , 'easyReservations' ).' x'.$res[0]->childs);
				$countpriceadd++;
			}
		}

		if(!empty($filters)) $countfilter=count($filters); else $countfilter=0; // count the filter-array element

		if($countfilter > 0){  //IF Filter array has elemts left they should be discounts or unavails or nonsense
			$staywasfull=0; $loyalwasfull=0; $perswasfull=0; $earlywasfull=0;
			$staywasfull_charge=0; $loyalwasfull_charge=0; $perswasfull_charge=0; $earlywasfull_charge=0;
			foreach($filters as $filter){
				$discount_add = 0;
				if($filter['type'] == 'stay'){// Stay Filter
					if($staywasfull==0 || $staywasfull_charge == 0){
						if($filter['cond'] <= $nights){
							$discount_add = 1;
						}
					}
				} elseif($filter['type'] == 'loyal'){// Loyal Filter
					if($loyalwasfull==0 || $loyalwasfull_charge == 0){
						if(is_email($res[0]->email)){
							$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='".$res[0]->email."' AND departure < NOW()")); //number of total rows in the database
							if($filter['cond'] <= $items1){
								$discount_add = 1;
							}
						}
					}
				} elseif($filter['type'] == 'pers'){// Persons Filter
					if($perswasfull==0 || $perswasfull_charge == 0){
						if($filter['cond'] <= ($res[0]->number + $res[0]->childs)){
							$discount_add = 1;
						}
					}
				} elseif($filter['type'] == 'early'){// Early Bird Discount Filter
					if($earlywasfull==0 || $earlywasfull_charge == 0){
						$dayBetween=round(($arrival/$resource_interval)-(strtotime($res[0]->reservated)/$resource_interval)); // cals days between booking and arrival
						if($filter['cond'] <= $dayBetween){
							$discount_add = 1;
						}
					}
				}

				if($discount_add == 1){
					$discount_amount = $filter['price'];
					$countpriceadd++;
					if(!isset($discount_amount)) break;

					if($discount_amount > 0){
						if($filter['type'] == 'stay' && $staywasfull_charge == 0) $staywasfull_charge++;
						elseif($filter['type'] == 'loyal' && $loyalwasfull_charge == 0) $loyalwasfull_charge++;
						elseif($filter['type'] == 'pers' && $perswasfull_charge == 0) $perswasfull_charge++;
						elseif($filter['type'] == 'early' && $earlywasfull_charge == 0) $earlywasfull_charge++;
						else break;
					} else {
						if($filter['type'] == 'stay' && $staywasfull == 0) $staywasfull++;
						elseif($filter['type'] == 'loyal' && $loyalwasfull == 0) $loyalwasfull++;
						elseif($filter['type'] == 'pers' && $perswasfull == 0) $perswasfull++;
						elseif($filter['type'] == 'early' && $earlywasfull == 0) $earlywasfull++;
					}

					if($filter['modus'] == '%'){
						$percent=$price/100* (int) $discount_amount;
						$price+=$percent;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$percent, 'type'=>get_the_title($resource).' '.$filter['type'].' filter '.__($filter['name']));
					} elseif($filter['modus'] == "price_res"){
						$price+=$discount_amount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$discount_amount, 'type'=>get_the_title($resource).' '.$filter['type'].' filter '.__($filter['name']));
					} elseif($filter['modus'] == "price_pers"){
						$the_discount = $discount_amount * ($res[0]->number + $res[0]->childs);
						$price += $the_discount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$the_discount, 'type'=>get_the_title($resource).' '.$filter['type'].' filter '.__($filter['name']));
					} else { // $filter['modus'] == price_day
						$the_discount = $discount_amount *  $nights;
						$price+= $the_discount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$the_discount, 'type'=>get_the_title($resource).' '.$filter['type'].' filter '.__($filter['name']));
					}
				}
			}
		}
		}

		if(!empty($res[0]->customp)){
			if(isset($fake)) $customps = easyreservations_get_custom_price_array($res[0]->customp);
			else $customps = easyreservations_get_customs($res[0]->customp, 0, 'cstm');
			$customprices = 0;
			foreach($customps as $customprice){
				if(substr($customprice['amount'], -1) == "%"){
					$percent=$price/100*str_replace("%", "", $customprice['amount']);
					$customprices+=$percent;
					if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$percent, 'type'=>__( 'Reservation custom price %' , 'easyReservations' ).' '.$customprice['title']);
				} else {
					$customprices+=$customprice['amount'];
					if($exact == 1) $exactlyprice[] = array('date'=>$arrival+($countpriceadd*$resource_interval), 'priceday'=>$customprice['amount'], 'type'=>__( 'Reservation custom price' , 'easyReservations' ).' '.$customprice['title']);
				}
			}
			$price+=$customprices; //Price plus Custom prices
		}

		$paid=0;

		if($res[0]->price != ''){
			$pricexpl=explode(";", $res[0]->price);
			if($pricexpl[0]!=0 AND $pricexpl[0]!=''){
				$price=$pricexpl[0];
			}
			if(isset($pricexpl[1]) && $pricexpl[1] > 0) $paid=$pricexpl[1];
			if(!is_numeric($paid) || $paid <= 0) $paid = 0;
		}

		//return $price;
		return array('price'=>$price, 'getusage'=>$exactlyprice,'paid'=>$paid);
	}

	/**
	* Format string into money
	*
	* @since 1.3
	*
	* @param int $amout amount of money to format
	* @param int 1 = currency sign | 0 = without
	* @return string formated money
	*/

	function reservations_format_money($amount, $mode=0){
		if($amount == '') $amount = 0;

		if(RESERVATIONS_CURRENCY == "#8364") $separator = true;
		else $separator = false;

		if($amount < 0){
			$amount = substr($amount, 1);
			$add = '-';
		} else $add = '';

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
			if(RESERVATIONS_CURRENCY == "#8364") $money = $add.$money.' &'.RESERVATIONS_CURRENCY.';';
			else  $money = '&'.RESERVATIONS_CURRENCY.'; '.$add.$money;
		}

		return $money;
	}

	/**
	* Get formated price for an reservation
	*
	* @since 1.2
	*
	* @param int $id Id of reservation
	* @param int $paid 1 for coloring amount by paid amounr
	* @param bool $currency show currency or not; default = true
	* @return string Price of reservation
	*/

	function easyreservations_get_price($id,$paids='', $cur = true){
		$getprice = easyreservations_price_calculation($id, '');
		$price = $getprice['price'];
		$paid = $getprice['paid'];
		if($price < 0) $rightprice = __( 'Wrong Price/Filter' , 'easyReservations' );
		else{
			if($cur) $rightprice = reservations_format_money(str_replace(",", ".", $price), 1);
			else $rightprice = reservations_format_money(str_replace(",", ".", $price), 0);
		}

		if(str_replace(",",".",$paid) == intval($price)) $pricebgcolor='color:#3A9920;padding:1px;';
		elseif(intval($paid)  > 0) $pricebgcolor='color:#F7B500;padding:1px;';
		else $pricebgcolor='color:#FF3B38;padding:1px;';

		if(!empty($paids)) $rightprice = '<b style="'.$pricebgcolor.'">'.$rightprice.'</b>';
		return $rightprice;
	}

	function easyreservations_get_custom_price_array($customp){
		$customs=array_values(array_filter(explode("&;&", $customp)));
		$customparray = '';
		foreach($customs as $customfield){
			$customexp=explode("&:&", $customfield);
			$priceexp=explode(":", $customexp[1]);
			$customparray[] = array( 'title' => $customexp[0], 'value' => $priceexp[0], 'amount' => $priceexp[1]); 
		}

		return $customparray;
	}

	function easyreservations_check_avail($resourceID, $arrival, $exactly=0, $departure = 0, $mode=0, $id=0, $avail=1, $status = 0, $interval = false){
		global $wpdb;
		$error=null;

		$date_format = date("Y-m-d H:i:s", $arrival);
		$roomcount = get_post_meta($resourceID, 'roomcount', true);
		if(!$interval){
			global $the_rooms_intervals_array;
			$resource_interval = $the_rooms_intervals_array[$resourceID];
			$interval = 86400;
			if($resource_interval == 3600) $interval = 3600;
		}

		if($interval == 3600) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:00';
		else $date_pattern = RESERVATIONS_DATE_FORMAT;

		if($id > 0 && ($status == 0 || $status == 'yes')) $idsql = " id != '$id' AND";
		else  $idsql = '';

		if($resourceID > 0){
			if($avail !== 0) $error .= reservations_check_avail_filter($resourceID, $arrival, $departure, $mode,$interval);
			if($departure > 0){
				if($exactly > 0){
					if($mode == 0){
						$startdate = date("Y-m-d H:i:s", $arrival+60);
						$middledate = date("Y-m-d H:i:s", ($arrival+$departure)/2);
						$enddate = date("Y-m-d H:i:s", $departure-60);
						$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."reservations WHERE approve='yes' AND room='$resourceID' AND roomnumber='$exactly' AND $idsql (arrival BETWEEN '$startdate' AND '$enddate' OR departure BETWEEN '$startdate' AND '$enddate' OR '$middledate' BETWEEN arrival AND departure)"));
						$error += $count;
					} else {
						for($i=$arrival; $departure - $i >= $interval/2 ; $i+=$interval){
							$date_format=date("Y-m-d H:i:s", $i);
							$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."reservations WHERE approve='yes' AND room='$resourceID' AND roomnumber='$exactly' AND $idsql ('$date_format' BETWEEN arrival AND departure OR DATE(arrival) = '".date("Y-m-d", $i)."') "));
							$error .= date($date_pattern, $i).', ';
						}
					}
				} else {
					for($i=$arrival; $departure - $i >= $interval/2 ; $i+=$interval){
						$date_format=date("Y-m-d H:i:s", $i);
						$date_format_end=date("Y-m-d H:i:s", $i+$interval);
						$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql (arrival BETWEEN '$date_format' AND '$date_format_end' OR departure BETWEEN '$date_format' AND '$date_format_end')"));
						if($mode==1 && $count >= $roomcount) $error .= date($date_pattern, $i).', ';
						elseif($mode==0 && $count >= $roomcount)  $error += $roomcount;
					}
				}
			} else {
				if($exactly > 0){
					$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND roomnumber='$exactly' AND '$idsql' '$date_format' BETWEEN arrival AND departure "));
					if($mode==1 &&  $count > 0) $error .= date($date_pattern, $arrival).', ';
					elseif($mode==0) $error += $count;
				} else {
					$date_format=date("Y-m-d H:i:s", $arrival);
					$date_format_end=date("Y-m-d H:i:s", $i+$interval);
					$count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql DATE('$date_format') BETWEEN DATE(arrival) AND DATE(departure)");
					if($mode==1 && $count > $roomcount) $error .= date($date_pattern, $arrival).', ';
					elseif($mode==0) $error += $count;
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

	function reservations_check_avail_filter($resourceID, $arrival, $departure=0, $mode=0, $interval = false){ //Check if a resource is Avail or Full
		$filters = get_post_meta($resourceID, 'easy_res_filter', true);
		$roomcount = get_post_meta($resourceID, 'roomcount', true);
		if(!$interval){
			global $the_rooms_intervals_array;
			$resource_interval = $the_rooms_intervals_array[$resourceID];
			$interval = 86400;
			if($resource_interval == 3600) $interval = 3600;
		}

		if($interval == 3600) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:00';
		else $date_pattern = RESERVATIONS_DATE_FORMAT;

		if($departure < 1) $departure = $arrival+$interval;
		$error = '';
		if(!empty($filters)){
			foreach($filters as $filter){
				if($filter['type'] == 'unavail'){
					for($i=$arrival; $departure - $i >= $interval/2 ; $i+=$interval){
						if($filter['cond'] == 'unit'){ // Unit price filter
							if(empty($filter['year']) || ( in_array(date("Y", $i), explode(",", $filter['year'])))){
								if(empty($filter['quarter']) || ( in_array(ceil(date("m", $i) / 3), explode(",", $filter['quarter'])))){
									if(empty($filter['month']) || ( in_array(date("n", $i), explode(",", $filter['month'])))){
										if(empty($filter['cw']) || ( in_array(date("W", $i), explode(",", $filter['cw'])))){
											if(empty($filter['day']) || ( in_array(date("N", $i), explode(",", $filter['day'])))){
												if(!isset($filter['hour']) || empty($filter['hour']) || ($interval == 3600 && in_array(date("H", $i), explode(",", $filter['hour'])))){
													if($mode == 1) $error .= date($date_pattern, $i).', ';
													elseif($mode == 2)  $error[$i] = $roomcount;
													else $error += $roomcount;
												}
											}
										}
									}
								}
							}
						} elseif($filter['cond'] == 'date'){ // Date price filter
							if(date("d.m.Y", $i) == date("d.m.Y", $filter['date']) && ($resource_interval > 3600 || date("H",$i) == date("H", $filter['date']))){
								if($mode == 1) $error .= date(RESERVATIONS_DATE_FORMAT_REAL, $i).', ';
								elseif($mode == 2) $error[$i] = $roomcount;
								else $error += $roomcount;
							}
						} else {// Range price filter
							if($i >= $filter['from'] && $i <= $filter['to']){
								if($mode == 1) $error .= date($date_pattern, $i).', ';
								elseif($mode == 2) $error[$i] = $roomcount;
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
		if(!isset($explodetheprice[1]) || empty($explodetheprice[1])) $payed = 0;
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
		if(preg_match("/^[\-]{0,1}[0-9]+[\.]?[0-9]*$/", $newPrice)){
			$finalPrice = $newPrice;
		} else {
			$finalPrice = 'error';
		}
		return $finalPrice;
	}

	function easyreservations_get_rooms($content=0, $check=0){
		global $wpdb;
		if($content == 1) $con = ", post_content"; else $con = "";

		$rooms = $wpdb->get_results("SELECT ID, post_title $con FROM ".$wpdb->prefix ."posts WHERE post_type='easy-rooms'");

		if($check != 0){
			foreach($rooms as $key => $room){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if(!empty($get_role) && !current_user_can($get_role)) unset($rooms[$key]);
			}
		}

		return $rooms;
	}
	
	$the_rooms_array = easyreservations_get_rooms();

	function reservations_get_room_options($selected='', $check=0, $exclude= ''){
		$rooms = easyreservations_get_rooms(0, $check);
		$rooms_options='';
		foreach( $rooms as $room ){
			if(empty($exclude) || !in_array($room->ID, $exclude)){
				if(!empty($selected) && $selected == $room->ID) $select = ' selected="selected"'; else $select = "";
				$rooms_options .= '<option value="'.$room->ID.'"'.$select.'>'.__($room->post_title).'</option>';
			}
		}
		return $rooms_options;
	}

	function easyreservations_get_rooms_intervals(){
		global $the_rooms_array;
		$rooms = $the_rooms_array;
		$room_intervals = '';
		if(!empty($rooms)){
			foreach( $rooms as $room ){
				$meta = get_post_meta($room->ID, 'easy-resource-interval', TRUE);
				if($meta) $room_intervals[$room->ID] = $meta;
				else $room_intervals[$room->ID] = 86400;
			}
		}
		return $room_intervals;
	}
	
	$the_rooms_intervals_array = easyreservations_get_rooms_intervals();

	function easyreservations_get_the_title($id, $resources=''){
		if($id > 0){
			if(empty($resources)){
				global $wpdb;
				$resource = $wpdb->get_results("SELECT post_title FROM ".$wpdb->prefix ."posts WHERE ID='$id'", ARRAY_N);
				$res = current($resource);
				return $res[0];
			} else {
				foreach($resources as $resource){
					if($resource->ID == $id) return $resource->post_title;
				}
			}
		} else {
			return __('None', 'easyReservations');
		}
	}

	function easyreservations_interval_infos($interval= 0, $mode = 0, $singular = 0){
		if($interval == 3600){
			$string = _n('hour', 'hours', $singular, 'easyReservations');
		} elseif($interval == 86400){
			$string = _n('day', 'days', $singular, 'easyReservations');
		} elseif($interval == 604800){
			$string = _n('week', 'weeks', $singular, 'easyReservations');
		} else $string = _n('unit', 'units', $singular, 'easyReservations');

		return $string;
	}

	function easyreservations_get_interval($interval = 0, $resourceID = 0, $mode = 0){
		if($interval == 0) $interval = get_post_meta($resourceID, 'easy-resource-interval', TRUE);
		if($mode == 0) return $interval;
		else{
			if($interval == 3600) return 3600;
			else return 86400;
		}
	}

	function easyreservations_get_nights($interval, $arrival, $departure){
		$number = ($departure-$arrival) / easyreservations_get_interval($interval, 0, 1);
		$significance = 0.01;
		return ( is_numeric($number) && is_numeric($significance) ) ? (ceil(ceil($number/$significance)*$significance)) : false;
	}
	/**
	*	Returns changelog
	*
	*	$beforeArray = array of reservation before editation
	*	$afterArray = array of reservation after editation
	*/

	function easyreservations_generate_res_changelog($beforeArray, $afterArray){		
		$changelog = '';

		if($beforeArray['arrival'] != $afterArray['arrival']){
			$changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT, strtotime($beforeArray['arrival'])).' => '.date(RESERVATIONS_DATE_FORMAT, strtotime($afterArray['arrival'])).'<br>';
		}

		if($beforeArray['departure'] != $afterArray['departure']){
			$changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT_SHOW, strtotime($beforeArray['departure'])).' => '.date(RESERVATIONS_DATE_FORMAT_SHOW, strtotime($afterArray['departure'])).'<br>';
		}

		if($beforeArray['name'] != $afterArray['name']){
			$changelog .= __('The name was edited' , 'easyReservations' ).': '.$beforeArray['name'].' => '.$afterArray['name'].'<br>';
		}

		if($beforeArray['email'] != $afterArray['email']){
			$changelog .= __('The email was edited' , 'easyReservations' ).': '.$beforeArray['email'].' => '.$afterArray['email'].'<br>';
		}

		if($beforeArray['persons'] != $afterArray['persons']){
			$changelog .= __('The amount of persons was edited' , 'easyReservations' ).': '.$beforeArray['persons'].' => '.$afterArray['persons'].'<br>';
		}

		if($beforeArray['childs'] != $afterArray['childs']){
			$changelog .= __('The amount of childs was edited' , 'easyReservations' ).': '.$beforeArray['childs'].' => '.$afterArray['childs'].'<br>';
		}

		if($beforeArray['country'] != $afterArray['country']){
			$changelog .= __('The country was edited' , 'easyReservations' ).': '.$beforeArray['country'].' => '.$afterArray['country'].'<br>';
		}

		if($beforeArray['room'] != $afterArray['room']){
			$changelog .= __('The room was edited' , 'easyReservations' ).': '.__(easyreservations_get_roomname($beforeArray['room'])).' => '.__(easyreservations_get_roomname($afterArray['room'])).'<br>';
		}

		if($beforeArray['message'] != $afterArray['message']){
			$changelog .= __('The message was edited' , 'easyReservations' ).'<br>';
		}

		if($beforeArray['custom'] != $afterArray['custom']){
			$changelog .= __('Custom fields got edited', 'easyReservations' ).'<br>';
		}

		if(isset($beforeArray['customp']) && $beforeArray['customp'] != $afterArray['customp']){
			$changelog .= __('Prices  got edited' , 'easyReservations' ).'<br>';
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
	function easyReservations_country_select($sel = ''){

		$countryArray = easyReservations_country_array();
		$country_options = '';
		foreach($countryArray as $short => $country){
			if($short == $sel){ $select = ' selected'; }
			else $select = "";
			$country_options .= '<option value="'.$short.'"'.$select.'>'.htmlentities($country,ENT_QUOTES).'</options>';
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

		for($num = (int) $start; $num <= $end; $num++){

			if(!empty($sel) && $num == $sel ) $isel = 'selected="selected"'; else $isel = '';
			if(strlen($start) == strlen($end) && $start < 10 && $end > 9 && $num < 10){
				$num = '0'.$num;
			}

			$return .= '<option value="'.$num.'" '.$isel.'>'.$num.'</option>';

		}

		return $return;
	}

	function easyreservations_send_mail($theForm, $mailTo, $mailSubj, $theMessage, $theID, $theChangelog){ //Send formatted Mails from anywhere
		global $wpdb, $the_rooms_intervals_array;
		preg_match_all(' /\[.*\]/U', $theForm, $matchers);
		$mergearrays=array_merge($matchers[0], array());
		$edgeoneremoave=str_replace('[', '', $mergearrays);
		$edgetworemovess=str_replace(']', '', $edgeoneremoave);

		$sql = "SELECT id, name, approve, departure, arrival, room, roomnumber, number, childs, country, email, custom, customp, price, reservated FROM ".$wpdb->prefix ."reservations WHERE id='%s'";
		$infos = $wpdb->get_row($wpdb->prepare($sql, $theID));

		$get_price = easyreservations_price_calculation($theID);

		$arrivalDate = strtotime($infos->arrival);
		$departureDate = strtotime($infos->departure);
		foreach($edgetworemovess as $fieldsx){
			$field=explode(" ", $fieldsx);
			if($field[0]=="adminmessage"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theMessage, $theForm);
			} elseif($field[0]=="ID"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theID, $theForm);
			} elseif($field[0]=="thename"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->name, $theForm);
			} elseif($field[0]=="email"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->email, $theForm);
			} elseif($field[0]=="arrivaldate" || $field[0]=="arrival" || $field[0]=="date-from"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', date(RESERVATIONS_DATE_FORMAT_SHOW, $arrivalDate), $theForm);
			} elseif($field[0]=="changelog"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theChangelog, $theForm);
			} elseif($field[0]=="departuredate" || $field[0]=="departure"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', date(RESERVATIONS_DATE_FORMAT_SHOW, $departureDate), $theForm);
			} elseif($field[0]=="units"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', easyreservations_get_nights($the_rooms_intervals_array[$infos->room], $arrivalDate, $departureDate), $theForm);
			} elseif($field[0]=="nights" || $field[0]=="days"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', easyreservations_get_nights(86400, strtotime($infos->reservated), time() ), $theForm);
			} elseif($field[0]=="hours"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', easyreservations_get_nights(3600, strtotime($infos->reservated), time() ), $theForm);
			} elseif($field[0]=="weeks"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', easyreservations_get_nights(604800, strtotime($infos->reservated), time() ), $theForm);
			} elseif($field[0]=="adults"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->number, $theForm);
			} elseif($field[0]=="childs"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->childs, $theForm);
			} elseif($field[0]=="persons"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->childs+$infos->number, $theForm);
			} elseif($field[0]=="rooms" || $field[0]=="resource"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', easyreservations_get_the_title($infos->room), $theForm);
			} elseif($field[0]=="roomnumber" || $field[0]=="resource-number" || $field[0]=="resource-nr"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', __(easyreservations_get_roomname($infos->roomnumber, $infos->room)), $theForm);
			} elseif($field[0]=="country"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', easyReservations_country_name($infos->country), $theForm);
			} elseif($field[0]=="price"){
				$theForm=str_replace('[price]', reservations_format_money($get_price['price']), $theForm);
			} elseif($field[0]=="paid"){
				$theForm=str_replace('[paid]', reservations_format_money($get_price['paid']), $theForm);
			} elseif($field[0]=="editlink"){
				$the_link = get_option("reservations_edit_url");
				$i = wp_nonce_tick();
				$nonce =  substr(wp_hash($i .'easyusereditlink0', 'nonce'), -12, 10);
				$the_edit_link = trim($the_link).'?edit&id='.$theID.'&email='.$infos->email.'&ernonce='.$nonce;
				if(!empty($the_link)) $theForm=str_replace('[editlink]', $the_edit_link, $theForm);
				else  $theForm=str_replace('[editlink]', '', $theForm);
			} elseif($field[0]=="customs"){
				$theCustominMail = '';
				if(!empty($infos->custom)){
					$customs=easyreservations_get_customs($infos->custom, 0, 'cstm', 'edit');
					foreach($customs as $custom){
						if(!isset($field[1]) || $field[1] == $custom['title']) $theCustominMail .= $custom['title'].': '.$custom['value'].'<br>';
					}
				}
				$theForm=str_replace('['.$field[0].']', $theCustominMail, $theForm);
			} elseif($field[0]=="customprices" || $field[0]=="prices"){
				$theCustominMail = '';
				if(!empty($infos->customp)){
					$customs=easyreservations_get_customs($infos->customp, 0, 'cstm', 'edit');
					foreach($customs as $custom){
						if(!isset($field[1]) || $field[1] == $custom['title']) $theCustominMail  .= $custom['title'].' - '.$custom['value'].': '.$custom['amount'].'<br>';
					}
				}
				$theForm=str_replace('['.$field[0].']', $theCustominMail, $theForm);
			}
		}

		$local = false;
		if(isset($_POST['easy-set-local'])) $local = $_POST['easy-set-local'];

		$theForm = apply_filters( 'easy-email-content', $theForm, $local);
		$mailSubj = apply_filters( 'easy-email-subj', $mailSubj, $local);

		$makebrtobreak=str_replace('<br>', "\n",str_replace(']', '',  str_replace('[', '', $theForm)));
		$msg=$makebrtobreak;

		$reservation_support_mail = get_option("reservations_support_mail");

		if(is_array($reservation_support_mail)) $send_from = $reservation_support_mail[0];
		else{
			if(preg_match('/[\,]/', $reservation_support_mail)){
				$implode  = implode(',', $reservation_support_mail);
				$send_from = $implode[0];
			} else $send_from = $reservation_support_mail;
		}
		$headers = "From: ".get_bloginfo('name')." <".$send_from.">\n";
		$headers .= "Message-ID: <".time()."-".$send_from.">\n";

		wp_mail($mailTo,$mailSubj,$msg,$headers);
	}

	function easyreservations_set_paid($id,$amount){
		global $wpdb;
		$error = '';

		if(is_numeric($id)){
			if(easyreservations_check_price($amount) != 'error'){
				$getprice = $wpdb->query( $wpdb->prepare("SELECT price ".$wpdb->prefix ."reservations WHERE id='%s' ", $id));
				$explode = explode(";", $getprice[0]['price']);
				if(isset($explode[1]) && $explode[1] > 0) $amount = $amount + $explode[1];
				$newprice = $explode[0].';'.$amount;

				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET price='$newprice' WHERE id='%s' ", $id) );
			} else $error = __( 'Money format error' , 'easyReservations' );
		} else $error = __( 'Wrong Identification' , 'easyReservations' );

		return $error;
	}
	
	function easyreservations_check_val(){
		if(has_action( 'er_mod_inst', 'easyreservations_add_module_notice') && strlen((string) easyreservations_add_module_notice(true)) == 280){
			return true;
		}
		return false;
	}

	function easyreservations_format_status($status, $color = 0){
		$statuse = array('yes' => array(
			__('approved', 'easyReservations' ), '#1FB512'
			),
			'no' => array(
			__('rejected', 'easyReservations' ), '#D61111'
			),
			'del' => array(
			__('trashed', 'easyReservations' ), '#870A0A'
			),
			'' => array(
			__('pending', 'easyReservations' ), '#3BB0E2'
			)
		);

		$formated_status = $statuse[$status][0];
		if($color == 1) $formated_status = '<b style="color:'.$statuse[$status][1].';text-transform:capitalize">'.$formated_status.'</b>';

		return $formated_status;
	}
	
	/**
	 *	Edit/Delete/Add custom informations to reservation
	 * 
	 * @global type $wpdb
	 * @param array $new_custom  one or multiple custom fields
	 * @param int $id ID of reservation
	 * @param array $customs current customs if  present to prevent multiple mysql queries
	 * @param type $mass 1 for delete all customs with $type $modus  and $check and add $new_custom afterwards
	 * @param int $thekey key of custom to edit or delete
	 * @param int $price  1 for price field 0 for custom field
	 * @param string $type check type of customs to edit/delete
	 * @param string $modus check modus of customs to edit/delete
	 * @param string $direct 1 to edit change the customs directly
	 * @param string $check check another information of customs to edit/delete
	 * @return array/int new customs /  key of custom
	 */

	function easyreservations_edit_custom($new_custom, $id, $customs = 0, $mass = 0, $thekey = false, $price = 0, $type = 0, $modus = 0, $direct = 0, $check = 0){
		if($customs == 0){
			global $wpdb;
			if($price == 0){
				$customs = $wpdb->get_results($wpdb->prepare("SELECT custom FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1"));
				$all_customs = $customs[0]->custom;
			} else {
				$customs = $wpdb->get_results("SELECT customp FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1");
				$all_customs = $customs[0]->customp;
			}
		} else $all_customs = $customs;

		$all_customs_save = '';

		if(!empty($all_customs)){
			$all_customs_save  = maybe_unserialize($all_customs);
			
			if($mass == 0){
				if(!is_numeric($thekey)){
					$all_customs_save = $all_customs_save;
				} else {
					if(isset($all_customs_save[$thekey]) && ($type === 0 || $all_customs_save[$thekey]['type'] == $type) && ($modus === 0 || $all_customs_save[$thekey]['mode'] == $modus) && ($check == 0 || $all_customs_save[$thekey][$check[0]] == $check[1])) unset($all_customs_save[$thekey]);
					else return '';
				}
			} else {
				if(!empty($all_customs_save)){
					foreach($all_customs_save as $key => $cstm){
						if(($type === 0 || $cstm['type'] == $type) && ($modus === 0 || $cstm['mode'] == $modus || $cstm['mode'] == 'visible') && ($check == 0 || $cstm[$check[0]] == $check[1])) unset($all_customs_save[$key]);
					}
				}
			}
		}

		if(empty($all_customs_save) || !isset($all_customs_save[0])) $all_customs_save = array();

		foreach($new_custom as $newcustom){
			$all_customs_save[] = $newcustom;
		}

		$all_customs_serial = maybe_serialize($all_customs_save);

		if($direct == 0) return $all_customs_serial;
		else {
			if($price == 0) $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$all_customs_serial' WHERE id='$id' "));
			else $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET customp='$all_customs_serial' WHERE id='$id' "));
			return $all_customs_save;
		}
	}

	function easyreservations_get_customs($custom, $ser = 0, $type = 0, $modus = 0){
		if($ser == 0 && is_serialized( $custom )) $custom = maybe_unserialize($custom);
		elseif($ser == 1 && is_serialized( $custom )) $custom = maybe_serialize($custom);

		if(!empty($custom)){
			foreach($custom as $key => $cstm){
				if( ( $type != 0 || $cstm['type'] != $type ) || ( $modus != 0 || ( isset($cstm['mode']) && $cstm['mode'] != $modus && $cstm['mode'] != 'visible' ) ) ) unset($custom[$key]);
			}
		}

		return $custom;
	}

	function easyreservations_send_calendar_callback(){
		global $the_rooms_intervals_array;
		check_ajax_referer( 'easy-calendar', 'security' );

		$explodeSize = explode(",", $_POST['size']);
		if(isset($explodeSize[0]) && $explodeSize[0] != '') $width = $explodeSize[0];
		if(isset($explodeSize[1]) && $explodeSize[1] != '') $price = $explodeSize[1];
		else $price = 0;
		if(isset($explodeSize[2]) && $explodeSize[2] != '') $interval = $explodeSize[2];
		else $interval = 1;
		if(isset($explodeSize[3]) && $explodeSize[3] != '') $header = $explodeSize[3];
		else $header = 0;

		$room_count = get_post_meta($_POST['room'], 'roomcount', true);
		$month_names = easyreservations_get_date_name(1);
		$day_names = easyreservations_get_date_name(0,2);
		if($width == 0 || empty($width)) $width=300;
		if($price == 1) $currency = '&'.RESERVATIONS_CURRENCY.';';
		if(isset($_POST['where']) && $_POST['where'] == "widget"){
			$onClick = "easyreservations_send_calendar('widget');";
			$formular = "widget_formular";
			$where = 'widget';
		} else {
			$onClick = "easyreservations_send_calendar('shortcode');";
			$formular = "CalendarFormular";
			$where = 'shortcode';
		}
		$divider = 1;
		$monthes = 1;

		if(isset($_POST['monthes']) && $where == 'shortcode' && preg_match('/^[0-9]+x{1}[0-9]+$/i', $_POST['monthes'])){
			$explode_monthes = explode('x', $_POST['monthes']);
			$monthes = $explode_monthes[0] * $explode_monthes[1];
			$divider = $explode_monthes[0];
		}

		if(function_exists('easyreservations_generate_multical') && $where == 'shortcode' && $monthes != 1){
			$timenows = easyreservations_generate_multical($_POST['date'], $monthes);
		} else {
			$timenows=array(time()+($_POST['date']*86400*30));
		}

		if(!isset($timenows[1])){
			$month = $month_names[date("n", $timenows[0])-1].' '.date("Y", $timenows[0]);
		} else {
			$anf =  $timenows[0];
			$end = $timenows[count($timenows)-1];
			if(date("Y", $anf) == date("Y", $end) ){
				$month=$month_names[date("n", $anf)-1].' - '.$month_names[date("n", $end)-1].' '.date("Y", $anf);
			} else {
				$month=$month_names[date("n", $anf)-1].' '.date("y", $anf).' - '.$month_names[date("n", $end)-1].' '.date("y", $end);
			}
		}

		echo '<table class="calendar-table" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr class="calendarheader">';
					echo '<th class="calendar-header-month-prev" onClick="easyClick = 0;document.'.$formular.'.date.value='.($_POST['date']-$interval).';'.$onClick.'">'.__('prev', 'easyReservations').'</th>';
					echo '<th colspan="5" class="calendar-header-show-month">'.$month.'</th>';
					echo '<th class="calendar-header-month-next" onClick="document.'.$formular.'.date.value='.($_POST['date']+$interval).';'.$onClick.'">'.__('next', 'easyReservations').'</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody style="text-align:center;white-space:nowrap;padding:0px">';
					echo '<tr>';
					echo '<td colspan="7" style="white-space:nowrap;padding:0px;margin:0px;">';

		if(count($timenows) > 1){
			$width = $width / $divider;
			$percent = 100 / $divider;
		} else $percent = 100;
		$rand = rand(1,999);

		$month_count=0;
		foreach($timenows as $timenow){
			$month_count++;
			$diff=1;
			$setet=0;
			$yearnow=date("Y", $timenow);
			$monthnow=date("m", $timenow);
			$key = $yearnow.$monthnow;
			$num = cal_days_in_month(CAL_GREGORIAN, $monthnow, $yearnow); // 31

			if($monthnow-1 <= 0){
				$monthnowFix=13;
				$yearnowFix=$yearnow-1;
			} else {
				$monthnowFix=$monthnow;
				$yearnowFix=$yearnow;
			}

			$num2 = cal_days_in_month(CAL_GREGORIAN, $monthnowFix-1, $yearnowFix); // 31

			if($divider % 2 != 0) $thewidth = ($width-0.33).'px';
			else $thewidth = $percent.'%';
			
			if($month_count % $divider == 0){
				$float = '';
			} else $float = 'float:left';

			echo '<table class="calendar-direct-table" style="width:'.$thewidth.';margin:0px;'.$float.'">';
				echo '<thead>';
				if($header == 1){
					echo '<tr>';
						echo '<th class="calendar-header-month" colspan="7">'.$month_names[date("n", $timenow)-1].'</th>';
					echo '</tr>';
				}
					echo '<tr>';
						echo '<th class="calendar-header-cell">'.$day_names[0].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[1].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[2].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[3].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[4].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[5].'</th>';
						echo '<th class="calendar-header-cell">'.$day_names[6].'</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody style="text-align:center;padding;0px;margin:0px">';

			$rowcount=0;
			while($diff <= $num){

				$dateofeachday=strtotime($diff.'.'.$monthnow.'.'.$yearnow);
				$dayindex=date("N", $dateofeachday);
				if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28 || $setet==35){ echo '<tr style="text-align:center">'; $rowcount++; }
				if($setet==0 && $diff==1 && $dayindex != "1"){ 
					echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2).'</span></td>'; $setet++; 
					if($setet==1 && $diff==1 && $dayindex != "2"){ 
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
						if($setet==2 && $diff==1 && $dayindex != "3"){ 
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
							if($setet==3 && $diff==1 && $dayindex != "4"){ 
							echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
								if($setet==4 && $diff==1 && $dayindex != "5"){ 
								echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
									if($setet==5 && $diff==1 && $dayindex != "6"){
									echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
										if($setet==6 && $diff==1 && $dayindex != "7"){
										echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++; 
										}
									}
								}
							}
						}
					}
				}

				if($price > 0){
					if(isset($_POST['persons'])) $persons = $_POST['persons']; else $persons = 1;
					if(isset($_POST['childs'])) $childs = $_POST['childs']; else $childs = 0;
					if(isset($_POST['reservated'])) $reservated = $_POST['reservated']*86400; else $reservated = 0;

					$fake_res = array( 'arrival' => date("Y-m-d H:i:s", $dateofeachday), 'departure' => date("Y-m-d H:i:s", $dateofeachday+$the_rooms_intervals_array[$_POST['room']]), 'reservated' => date("d.m.Y H:i", time()), 'room' => $_POST['room'], 'number' => $persons, 'childs' => $childs, 'email' => 'test@test.deve', 'price' => '', 'customp' => '' );
					$fake_res_object = (object) $fake_res;
					$calculate_price = easyreservations_price_calculation( '', array($fake_res_object) );
					if($price == 1 || $price == 2){ $explode = explode('.', $calculate_price['price']); $calculate_price['price'] = $explode[0]; }
					if($price == 1) $formated_price = $calculate_price['price'].$currency;
					elseif($price == 2) $formated_price = $calculate_price['price'];
					elseif($price == 3) $formated_price = reservations_format_money($calculate_price['price'], 1);
					elseif($price == 4) $formated_price = reservations_format_money($calculate_price['price']);

					$final_price = '<span class="calendar-cell-price">'.$formated_price.'</b>';
				} else $final_price = '';

				if(date("d.m.Y", $dateofeachday) == date("d.m.Y", time())) $todayClass=" today";
				else $todayClass="";

				$avail = easyreservations_check_avail($_POST['room'], $dateofeachday, 0,0,0,0,1,0,86400);

				if($avail >= $room_count){
					$backgroundtd=" calendar-cell-full";
				} elseif($avail > 0){
					$backgroundtd=" calendar-cell-occupied";
				} else {
					$backgroundtd=" calendar-cell-empty";
				}
				if($dateofeachday > time()) $onclick = 'onclick="easyreservations_click_calendar(this,\''.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'\', \''.$rand.'\', \''.$key.'\')"'; else $onclick ='style="cursor:default"';
				echo '<td class="calendar-cell'.$todayClass.$backgroundtd.'" '.$onclick.' id="easy-cal-'.$rand.'-'.$diff.'-'.$key.'" axis="'.$diff.'">'.$diff.''.$final_price.'</td>'; $setet++; $diff++;
				if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28){ echo '</tr>'; }
			}

			if(!empty($final_price)) $final_price =  '<span class="calendar-cell-price">&nbsp;</b>';

			if(($diff-1==$num && $setet/7 != $rowcount) || $setet < 36){
				if($divider == 1) $calc=($rowcount*7)-($setet+1);
				else $calc=42-($setet+1);
				for($countits=0; $countits < $calc+1; $countits++){
					if($countits==0) $fix = " calendar-cell-lastfixer"; else $fix ="";
					if($setet+$countits==35){ echo '</tr><tr>'; $setet++; }
					echo '<td class="calendar-cell calendar-cell-last'.$fix.'"><div>&nbsp;</div><span>'.($countits+1).'</span>'.$final_price.'</td>';
				}
			}

			echo '</tr></tbody></table>';
		}

		echo '</td></tr></tbody></table>';
		exit;
	}

	/**
	 *	Callback for the price calculation (here it fakes a reservation and send it to calculation)
	 *
	*/

	function easyreservations_send_price_callback(){
		check_ajax_referer( 'easy-price', 'security' );

		global $the_rooms_intervals_array;
		$room = $_POST['room'];
		$val_from = strtotime($_POST['from']) + (int) $_POST['fromplus'] ;
		if(!empty($_POST['to'])){
			$val_to = strtotime($_POST['to']) + (int) $_POST['toplus'] ;
		} else {
			$val_to = $val_from + ($_POST['nights'] * $the_rooms_intervals_array[$room]);
		}
		$email = $_POST['email'];
		$persons = $_POST['persons'];

		if(isset($_POST['customp'])){
			$customp = str_replace("!", "&", $_POST['customp']);
		} else $customp = '';

		if(isset($_POST['childs'])) $childs = $_POST['childs'];
		else $childs = 0;

		if($email == "") $email = "test@test.de";
		if($persons == "") $persons = 1;

		$Array = array( 'arrival' => date("Y-m-d H:i:s", $val_from), 'departure' => date("Y-m-d H:i:s", $val_to), 'reservated' => date("Y-m-d H:i:s", time()), 'room' => $room, 'number' => $persons, 'childs' => $childs, 'email' => $email, 'price' => '', 'customp' => $customp );
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
		$mode = $_POST['mode'];
		global $the_rooms_intervals_array;
		$val_room = $_POST['room'];
		$val_name = $_POST['thename'];
		$val_persons = (int) $_POST['persons'];
		$val_childs = (int) $_POST['childs'];
		$val_room = (int) $_POST['room'];
		$val_from = strtotime($_POST['from']) + (int) $_POST['fromplus'] ;
		if(!empty($_POST['to'])){
			$val_to = strtotime($_POST['to']) + (int) $_POST['toplus'] ;
			$val_nights = easyreservations_get_nights($the_rooms_intervals_array[$val_room], $val_from, $val_to);
			if($val_nights == 0) $val_nights = 1;
			$field = 'easy-form-to';
		} else {
			$val_nights = $_POST['nights'];
			$val_to = $val_from + ($val_nights * $the_rooms_intervals_array[$val_room]);
			$field = 'easy-form-units';
		}

		$error = "";

		if((strlen($val_name) > 30 || strlen($val_name) <= 1 ||  !preg_match('/^[A-Za-zöüäßąęśłóńźćż\s]+$/i',$val_name)) && $val_name != ""){ /* check name */
			$error[] = 'easy-form-thename';
			$error[] = __( 'Please enter a correct name' , 'easyReservations' );
		}

		if($val_from-time() < 0){ /* check arrival Date */
			$error[] = 'easy-form-from';
			$error[] =  __( 'The arrival date has to be in future' , 'easyReservations' );
		}

		if($val_to < $val_from){ /* check difference between arrival and departure date */
			$error[] = $field;
			$error[] = __( 'Departure before arrival' , 'easyReservations' );
		}

		if(!is_email( $_POST['email']) && $_POST['email'] != ""){ /* check email */
			$error[] = 'easy-form-email';
			$error[] =  __( 'Please enter a correct eMail' , 'easyReservations' );
		}

		if (!is_numeric($_POST['persons'])){ /* check persons */
			$error[] = 'easy-form-persons';
			$error[] = __( 'Persons has to be a number' , 'easyReservations' );
		}

		$numbererrors=easyreservations_check_avail($_POST['room'], $val_from, 0, $val_to , 1 ); /* check rooms availability */

		if($numbererrors > 0){
			$error[] = 'date';
			$error[] = __( 'Isn\'t available at' , 'easyReservations' ).' '.$numbererrors;
		}

		$resource_req = get_post_meta($_POST['room'], 'easy-resource-req', TRUE);
		if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 0, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);
		$rooms = easyreservations_get_rooms();

		if($resource_req['pers-min'] > ($val_persons+$val_childs)){
			$error[] = 'easy-form-persons';
			$error[] =  sprintf(__( 'At least %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-min'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['pers-max'] != 0 && $resource_req['pers-max'] < ($val_persons+$val_childs)){
			$error[] = 'easy-form-persons';
			$error[] =  sprintf(__( 'Maximum %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-max'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['nights-min'] != 0 && $resource_req['nights-min'] > $val_nights){
			$error[] = 'date';
			$error[] =  sprintf(__( 'At least %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-min'], easyreservations_interval_infos($the_rooms_intervals_array[$val_room], 0, $resource_req['nights-min']), __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['nights-max'] != 0 && $resource_req['nights-max'] < $val_nights){
			$error[] = 'date';
			$error[] =  sprintf(__( 'Maximum %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-max'], easyreservations_interval_infos($the_rooms_intervals_array[$val_room], 0, $resource_req['nights-max']), __(easyreservations_get_the_title($val_room, $rooms)));
		}

		if($mode == 'send' ){
			if(empty($val_name)){
				$error[] = 'easy-form-thename';
				$error[] =  __( 'Name is required' , 'easyReservations' );
			}
			if(empty($_POST['email'])){
				$error[] = 'easy-form-email';
				$error[] =  __( 'eMail is required' , 'easyReservations' );
			}
			$explode_customs = explode(',', substr($_POST['customs'],0,-1));
			foreach($explode_customs as $cstm){
				if(!empty($cstm)){
					$error[] = $cstm;
					$error[] =  sprintf(__( '%1$s is required' , 'easyReservations' ), ucfirst(str_replace('easy-custom-req-', '', $cstm)));
				}
			}
			if($_POST['captcha'] != 'x!'){
				if(empty($_POST['captcha'])){
					$error[] = 'easy-form-captcha';
					$error[] =  __( 'Captcha is required' , 'easyReservations' );
				} elseif(strlen($_POST['captcha']) != 4){
					$error[] = 'easy-form-captcha';
					$error[] =  __( 'Enter correct captcha' , 'easyReservations' );
				}
			}
		}

		if( $error != '' ){
			header( "Content-Type: application/json" );
			echo json_encode($error);
		} else echo true;

		exit;
	}

	function easyreservations_register_scripts(){
		wp_register_script('easyreservations_send_calendar', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_calendar.js' , array( "jquery" ));
		wp_register_script('easyreservations_send_price', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_price.js' , array( "jquery" ));
		wp_register_script('easyreservations_send_validate', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_validate.js' , array( "jquery" ));
		wp_register_script('easy-form-js', WP_PLUGIN_URL . '/easyreservations/js/form.js');

		global $the_rooms_intervals_array;

		wp_localize_script( 'easyreservations_send_calendar', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easyreservations_send_price', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easyreservations_send_validate', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easy-form-js', 'easyDate', array( 'easydateformat' => RESERVATIONS_DATE_FORMAT, 'interval' => json_encode($the_rooms_intervals_array) ) );

		wp_register_style('easy-form-little', WP_PLUGIN_URL . '/easyreservations/css/forms/form_little.css');
		wp_register_style('easy-form-blue', WP_PLUGIN_URL . '/easyreservations/css/forms/form_blue.css');
		wp_register_style('easy-form-none', WP_PLUGIN_URL . '/easyreservations/css/forms/form_none.css');

		wp_register_style('easy-cal-1', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_1.css');
		wp_register_style('easy-cal-2', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_2.css');

		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css');
	}

	add_action('wp_enqueue_scripts', 'easyreservations_register_scripts');
	add_action('wp_ajax_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
	add_action('wp_ajax_easyreservations_send_price', 'easyreservations_send_price_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_price', 'easyreservations_send_price_callback');
	add_action('wp_ajax_easyreservations_send_validate', 'easyreservations_send_validate_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_validate', 'easyreservations_send_validate_callback');
 
	function easyreservtions_send_price_script(){
		echo '<script>easyreservations_send_price(); </script>';
	}

	function easyreservations_get_roomname($number, $room, $roomnames = ''){
		$number = $number - 1;
		if(empty($number) && $number < 0) return $number;
		if(empty($roomnames)) $roomnames = get_post_meta($room, 'easy-resource-roomnames', TRUE);
		if(isset($roomnames[$number]) && !empty($roomnames[$number])) return $roomnames[$number];
		else return $number+1;
	}

	/**
	 * Get day or month names
	 * 
	 * @since 1.8
	 * 
	 * @param int $interval 0 for days - 1 for monthes
	 * @param int $substr number of characters to display 0=full
	 * @param int $date number of day/or month to retutn just that string
	 * @return array/string with name of date
	 */

	function easyreservations_get_date_name($interval = 0, $substr = 0, $date = false){
		$name = '';
		if($interval == 0){
			$name[] = __( 'Monday' , 'easyReservations' );
			$name[] = __( 'Tuesday' , 'easyReservations' );
			$name[] = __( 'Wednesday' , 'easyReservations' );
			$name[] = __( 'Thursday' , 'easyReservations' );
			$name[] = __( 'Friday' , 'easyReservations' );
			$name[] = __( 'Saturday' , 'easyReservations' );
			$name[] = __( 'Sunday' , 'easyReservations' );
		} else {
			$name[] = __( 'January' , 'easyReservations' );
			$name[] = __( 'February' , 'easyReservations' );
			$name[] = __( 'March' , 'easyReservations' );
			$name[] = __( 'April' , 'easyReservations' );
			$name[] = __( 'May' , 'easyReservations' );
			$name[] = __( 'June' , 'easyReservations' );
			$name[] = __( 'July' , 'easyReservations' );
			$name[] = __( 'August' , 'easyReservations' );
			$name[] = __( 'September' , 'easyReservations' );
			$name[] = __( 'October' , 'easyReservations' );
			$name[] = __( 'November' , 'easyReservations' );
			$name[] = __( 'December' , 'easyReservations' );
		}

		if($substr > 0){
			foreach($name as $key => $day){
				$name[$key] = substr($day, 0, $substr);
			}
		}

		if($date !== false) return $name[$date];
		else return $name;
	}
?>