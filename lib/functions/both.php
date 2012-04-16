<?php
	/**
	* 	@functions for admin and frontend 
	*/

	/**
	* 	Hook on adminbar, add link to admin-panel and count of pending reservations
	*/
	function easyreservation_resource_init() {
		$labels = array(
			'name' => _x('Rooms', 'post type general name'),
			'singular_name' => _x('Room', 'post type singular name'),
			'add_new' => _x('Add Room', 'book'),
			'add_new_item' => __('Add New Room'),
			'edit_item' => __('Edit Room'),
			'new_item' => __('New Room'),
			'all_items' => __('All Rooms'),
			'view_item' => __('View Room'),
			'search_items' => __('Search Rooms'),
			'not_found' =>  __('No rooms found'),
			'not_found_in_trash' => __('No rooms found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => 'Rooms'
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => array('slug' => 'room'), 
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
		$labels = array(
			'name' => _x('Offers', 'post type general name'),
			'singular_name' => _x('Offer', 'post type singular name'),
			'add_new' => _x('Add Offer', 'book'),
			'add_new_item' => __('Add New Offer'),
			'edit_item' => __('Edit Offer'),
			'new_item' => __('New Offer'),
			'all_items' => __('All Offers'),
			'view_item' => __('View Offer'),
			'search_items' => __('Search Offers'),
			'not_found' =>  __('No offers found'),
			'not_found_in_trash' => __('No offers found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => 'Offers'
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => array('slug' => 'offer'), 
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => false, 
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields'  )
		); 
		register_post_type('easy-offers',$args);
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
		global $wpdb;

		if(!isset($newRes) || $newRes == ""){
			$reservation = "SELECT room, special, arrivalDate, nights, email, number, childs, price, customp, reservated FROM ".$wpdb->prefix ."reservations WHERE id='$id' LIMIT 1";
			$res = $wpdb->get_results( $reservation );
		} else {
			$res = $newRes; // newRes is an array with a db fake of a reservaton. for new reservations, testing purposes or the price in calendars | need to have theese format but you can enter fake emails ect: array(room => '', special => '', arrivalDate => '', nights => '', email => '', number => '', childs => '', price => '', customp => '', reservated => '');
			$fake = 1;
		}

		$price=0; // This will be the Price
		$countpriceadd=0; // Count times (=days) a sum gets added to price
		$countgroundpriceadd=0; // Count times (=days) a groundprice is added to price
		$arrivalDateRes = strtotime($res[0]->arrivalDate);
		$datearray = array();
		$exactlyprice = "";

		/*
			Get Filters From Offer or from Room if Offer = 0
		*/

		if($res[0]->special=="0" || $res[0]->special==""){ 
			$filters = get_post_meta($res[0]->room, 'easy_res_filter', true); $roomoroffer=$res[0]->room; $roomoroffertext=__( 'Room' , 'easyReservations' );
		} else { 
			$filters = get_post_meta($res[0]->special, 'easy_res_filter', true); $roomoroffer=$res[0]->special; $roomoroffertext=__( 'Offer' , 'easyReservations' );
		}

		$resource_groundprice = get_post_meta($roomoroffer, 'reservations_groundprice', true);
		$room_groundprice = get_post_meta($res[0]->room, 'reservations_groundprice', true);
		$price_per_person = get_post_meta($roomoroffer, 'easy-resource-price', true);

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
							} else { // Range price filter
								$from = strtotime($filter['from']);
								$to = strtotime($filter['to']);
								if($res_from_stamp_day >= $from && $res_from_stamp_day  <= $to){
									$price_add = 1;
								}
							}
						}

						if($price_add == 1){
							if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filter['price'])){
								$specialexplodes=explode(";", $filter['price']);
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
								if($exact == 1) $exactlyprice[] = array('date'=>$res_from_stamp_day, 'priceday'=>$filter['price'], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.__($filter['name']));
								$datearray[] = $res_from_stamp_day;
							}
						}
					}
					unset($filters[$num]);
				}
			}
		}

		while($countpriceadd < $res[0]->nights){
			if(preg_match("/^[0-9]+[\.]?[0-9]+$/", $resource_groundprice)){
				$price+=$resource_groundprice;		
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

				if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$resource_groundprice, 'type'=>get_the_title($roomoroffer).' '.__( 'base Price' , 'easyReservations' ));
				$countgroundpriceadd++;
			} else {
				$specialexploder=explode("-", $resource_groundprice);
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
					$price+=$room_groundprice; // Calculate price for permamently Price
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
					if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countgroundpriceadd*86400)+($ifDateHasToBeAdded*86400), 'priceday'=>$room_groundprice, 'type'=>get_the_title($res[0]->room).' '.__( 'base Price' , 'easyReservations' ));
					$countgroundpriceadd++;
				}
			}
			$countpriceadd++;
		}

		$checkprice=$price;		
		if($price_per_person == 1 && ($res[0]->number > 1 || $res[0]->childs > 0)) {  // Calculate Price if  "Calculate per person"  was choosen

			if($res[0]->number > 1){
				$price=$price*$res[0]->number; 
				if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$price-$checkprice, 'type'=>__( 'Price for  persons' , 'easyReservations' ).' x'.$res[0]->number);
				$countpriceadd++;
			}

			if(!empty($res[0]->childs) && $res[0]->childs != 0){
				$childprice = get_post_meta($roomoroffer, 'reservations_child_price', true);
				if(substr($childprice, -1) == "%"){
					$percent=$checkprice/100*(str_replace("%", "", $childprice)*$res[0]->nights);
					$childsPrice = ($checkprice - $percent);
				} else {
					$childsPrice = ($checkprice - $childprice*$res[0]->nights);
				}

				if($price_per_person == 1) $childsPrice = $childsPrice*$res[0]->childs;
				$price += $childsPrice;
				if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$childsPrice, 'type'=>__( 'Price per child' , 'easyReservations' ).' x'.$res[0]->childs);
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
						if($filter['cond'] <= $res[0]->nights){
							$discount_add = 1;
						}
					}
				} elseif($filter['type'] == 'loyal'){// Loyal Filter
					if($loyalwasfull==0 || $loyalwasfull_charge == 0){
						if(is_email($res[0]->email)){
							$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='".$res[0]->email."' AND arrivalDate + INTERVAL 1 DAY < NOW()")); //number of total rows in the database
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
						$dayBetween=round(($arrivalDateRes/86400)-(strtotime($res[0]->reservated)/86400)); // cals days between booking and arrival
						if($filter['cond'] <= $dayBetween){
							$discount_add = 1;
						}
					}
				}

				if($discount_add == 1){
					if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filter['price'])){//If Filter Amount is offer
						$specialexplodes=explode(";", $filter['price']);
						foreach($specialexplodes as $specialexplode){
							$priceroomexplode=explode(":", $specialexplode);
							if($priceroomexplode[0] == $res[0]->room){
								$discount_amount = $priceroomexplode[1];
								$countpriceadd++; break;
							}
						}
					} elseif(preg_match("/^[\-]{0,1}[0-9]+[\.]?[0-9]*$/", $filter['price'])){ //If Filter Value is XX
						$discount_amount = $filter['price'];
						$countpriceadd++;
					}
					
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
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$percent, 'type'=>get_the_title($roomoroffer).' '.$filter['type'].' filter '.__($filter['name']));
					} elseif($filter['modus'] == "price_res"){
						$price+=$discount_amount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$discount_amount, 'type'=>get_the_title($roomoroffer).' '.$filter['type'].' filter '.__($filter['name']));
					} elseif($filter['modus'] == "price_pers"){
						$the_discount = $discount_amount * ($res[0]->number + $res[0]->childs);
						$price += $the_discount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$the_discount, 'type'=>get_the_title($roomoroffer).' '.$filter['type'].' filter '.__($filter['name']));
					} else { // $filter['modus'] == price_day
						$the_discount = $discount_amount *  $res[0]->nights;
						$price+= $the_discount;
						if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$the_discount, 'type'=>get_the_title($roomoroffer).' '.$filter['type'].' filter '.__($filter['name']));
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
					if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$percent, 'type'=>__( 'Reservation custom price %' , 'easyReservations' ).' '.$customprice['title']);
				} else {
					$customprices+=$customprice['amount'];
					if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>$customprice['amount'], 'type'=>__( 'Reservation custom price' , 'easyReservations' ).' '.$customprice['title']);
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
		$currency = get_option('reservations_currency');

		if($currency == "#8364") $separator = true;
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
			if($currency == "#8364") $money = $add.$money.' &'.$currency.';';
			else  $money = '&'.$currency.'; '.$add.$money;
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

	function easyreservations_check_avail($resourceID, $date, $exactly=0, $nights=1, $offer=0, $mode=0, $id=0, $avail=1, $status = 0){
		global $wpdb;
		$error=null;

		if($offer > 0) $error .= reservations_check_avail_filter($offer, $date, $nights, $mode, $resourceID);
		$date_format = date("Y-m-d", $date);
		$roomcount = get_post_meta($resourceID, 'roomcount', true);
		if($id > 0 && ($status == 0 || $status == 'yes')) $idsql = " id != '$id' AND";
		else  $idsql = '';

		if($resourceID > 0){
			if($avail == 1) $error .= reservations_check_avail_filter($resourceID, $date, $nights, $mode);
			if($nights > 1){
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
						elseif($mode==0 && $count > $roomcount)  $error += $roomcount;
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
			if($second == 0) $roomcount = get_post_meta($resourceID, 'roomcount', true);
			else $roomcount = get_post_meta($second, 'roomcount', true);
		} elseif($mode == 2){
			$roomcount = get_post_meta($resourceID, 'roomcount', true);
		}

		$error = '';

		if(!empty($filters)) $countfilter=count($filters); else $countfilter=0; // count the filter-array element
		if($countfilter > 0){
			foreach($filters as $filter){
				if($filter['type'] == 'unavail'){
					for($i=0; $i < $times; $i++){
						$datet = $date+($i*86400);
						if($filter['cond'] == 'unit'){ // Unit price filter
							if(empty($filter['year']) || ( in_array(date("Y", $datet), explode(",", $filter['year'])))){
								if(empty($filter['quarter']) || ( in_array(ceil(date("m", $datet) / 3), explode(",", $filter['quarter'])))){
									if(empty($filter['month']) || ( in_array(date("n", $datet), explode(",", $filter['month'])))){
										if(empty($filter['cw']) || ( in_array(date("W", $datet), explode(",", $filter['cw'])))){
											if(empty($filter['day']) || ( in_array(date("N", $datet), explode(",", $filter['day'])))){
												if($mode == 1) $error .= date(RESERVATIONS_DATE_FORMAT, $datet).', ';
												elseif($mode == 2)  $error[$i] = $roomcount;
												else $error += $roomcount;
											}
										}
									}
								}
							}
						} elseif($filter['cond'] == 'date'){ // Date price filter
							if(date("d.m.Y", $datet) == $filter['date']){
								if($mode == 1) $error .= date(RESERVATIONS_DATE_FORMAT, $datet).', ';
								elseif($mode == 2) $error[$i] = $roomcount;
								else $error += $roomcount;
							}						
						} else {// Range price filter
							if($datet >= strtotime($filter['from']) && $datet <= strtotime($filter['to'])){
								if($mode == 1) $error .= date(RESERVATIONS_DATE_FORMAT, $datet).', ';
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

	function reservations_get_room_options($selected='', $check=0){
		$roomcategories = easyreservations_get_rooms(0, $check);
		$rooms_options='';
		foreach( $roomcategories as $roomcategorie ){
			if(isset($selected) && !empty($selected) && $selected == $roomcategorie->ID) $select = ' selected="selected"'; else $select = "";
			$rooms_options .= '<option value="'.$roomcategorie->ID.'"'.$select.'>'.__($roomcategorie->post_title).'</option>';
		}
		return $rooms_options;
	}

	function easyreservations_get_offers($content=0, $check=0){
		global $wpdb;
		if($content == 1) $con = ", post_content"; else $con = "";

		$offers = $wpdb->get_results("SELECT ID, post_title $con FROM ".$wpdb->prefix ."posts WHERE post_type='easy-offers'");
		
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
			if(isset($selected) && !empty($selected) && $selected == $offercategorie->ID) $select = 'selected="selected"'; else $select = "";
			$offer_options .= '<option value="'.$offercategorie->ID.'" '.$select.'>'.__($offercategorie->post_title).'</option>';
		}
		return $offer_options;
	}

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

	/**
	*	Returns changelog
	*
	*	$beforeArray = array of reservation before editation
	*	$afterArray = array of reservation after editation
	*/

	function easyreservations_generate_res_changelog($beforeArray, $afterArray){		
		$changelog = '';

		if($beforeArray['arrivalDate'] != $afterArray['arrivalDate']){
			$changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT, (strtotime($beforeArray['arrivalDate']))).' => '.date(RESERVATIONS_DATE_FORMAT, (strtotime($afterArray['arrivalDate']))).'<br>';
		}

		//if($beforeArray['arrivalDate'] != $afterArray['arrivalDate'] || $beforeArray['nights'] != $afterArray['nights']){
		if((strtotime($beforeArray['arrivalDate'])+($beforeArray['nights']*86400)) != (strtotime($afterArray['arrivalDate'])+($afterArray['nights']*86400))){
			$changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT, ((strtotime($beforeArray['arrivalDate'])+($beforeArray['nights']*86400)))).' => '.date(RESERVATIONS_DATE_FORMAT, ((strtotime($afterArray['arrivalDate'])+($afterArray['nights']*86400)))).'<br>';
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
			$changelog .= __('Custom fields got edited', 'easyReservations' ).'<br>';
		}

		if(isset($beforeArray['customp']) && $beforeArray['customp'] != $afterArray['customp']){
			$changelog .= __('Custom price fields got edited' , 'easyReservations' ).'<br>';
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

		for($num = $start; $num <= $end; $num++){

			if(!empty($sel) && $num == $sel ) $isel = 'selected="selected"'; else $isel = '';

			$return .= '<option value="'.$num.'"'.$isel.'>'.$num.'</option>';

		}

		return $return;
	}

	function easyreservations_send_mail($theForm, $mailTo, $mailSubj, $theMessage, $theID, $theChangelog){ //Send formatted Mails from anywhere
		global $wpdb;
		preg_match_all(' /\[.*\]/U', $theForm, $matchers);
		$mergearrays=array_merge($matchers[0], array());
		$edgeoneremoave=str_replace('[', '', $mergearrays);
		$edgetworemovess=str_replace(']', '', $edgeoneremoave);

		$sql = "SELECT id, name, approve, nights, arrivalDate, room, special, roomnumber, number, childs, country, email, custom, customp, notes, price, reservated FROM ".$wpdb->prefix ."reservations WHERE id='%s'";
		$infos = $wpdb->get_row($wpdb->prepare($sql, $theID));

		$get_price = easyreservations_price_calculation($theID);

		$arrivalDate = strtotime($infos->arrivalDate);
		$departureDate = $arrivalDate + (86400 *  $infos->nights);
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
			} elseif($field[0]=="arrivaldate"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', date(RESERVATIONS_DATE_FORMAT, $arrivalDate), $theForm);
			} elseif($field[0]=="changelog"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $theChangelog, $theForm);
			} elseif($field[0]=="departuredate"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', date(RESERVATIONS_DATE_FORMAT, $departureDate), $theForm);
			} elseif($field[0]=="nights"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->nights, $theForm);
			} elseif($field[0]=="note"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->notes, $theForm);
			} elseif($field[0]=="reserved" || $field[0]=="reservated" ){
				$theForm=preg_replace('/\['.$fieldsx.']/U', human_time_diff( strtotime($infos->reservated) ), $theForm);
			} elseif($field[0]=="persons" || $field[0]=="adults"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->number, $theForm);
			} elseif($field[0]=="childs"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', $infos->childs, $theForm);
			} elseif($field[0]=="rooms"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.easyreservations_get_the_title($infos->room).'', $theForm);
			} elseif($field[0]=="roomnumber"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.easyreservations_get_roomname($infos->roomnumber, $infos->room).'', $theForm);
			} elseif($field[0]=="country"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.easyReservations_country_name($infos->country).'', $theForm);
			} elseif($field[0]=="offers"){
				$theForm=preg_replace('/\['.$fieldsx.']/U', ''.easyreservations_get_the_title($infos->special).'', $theForm);
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
				if(!empty($theCustoms)){
					$customs=easyreservations_get_customs($infos->custom, 0, 'cstm', 'edit');
					foreach($customs as $custom){
						$theCustominMail  .= $custom['title'].': '.$custom['value'].'<br>';
					}
				}
				$theForm=str_replace('['.$field[0].']', $theCustominMail, $theForm);
			} elseif($field[0]=="customprices"){
				$theCustominMail = '';
				if(!empty($theCustomPrices)){
					$customs=easyreservations_get_customs($infos->customp, 0, 'cstm', 'edit');
					foreach($customs as $custom){
						$theCustominMail  .= $custom['title'].' - '.$custom['value'].': '.$custom['amount'].'<br>';
					}
				}
				$theForm=str_replace('['.$field[0].']', $theCustominMail, $theForm);
			}
		}

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
				$newprice = $explode[0].';'.$amount;

				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET price='$newprice' WHERE id='%s' ", $id) );
			} else $error = __( 'Money format error' , 'easyReservations' );
		} else $error = __( 'Wrong Identification' , 'easyReservations' );

		return $error;
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
		check_ajax_referer( 'easy-calendar', 'security' );

		$explodeSize = explode(",", $_POST['size']);
		if(isset($explodeSize[0]) && $explodeSize[0] != '') $width = $explodeSize[0];
		if(isset($explodeSize[1]) && $explodeSize[1] != '') $price = $explodeSize[1];
		else $price = 0;
		if(isset($explodeSize[2]) && $explodeSize[2] != '') $interval = $explodeSize[2];
		else $interval = 1;
		if(isset($explodeSize[3]) && $explodeSize[3] != '') $header = $explodeSize[3];
		else $header = 0;
		$month_names = easyreservations_get_date_name(1);
		$day_names = easyreservations_get_date_name(0,2);
		if($width == 0 || empty($width)) $width=300;
		if($price == 1) $currency = '&'.get_option('reservations_currency').';';
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

			echo '<table class="calendar-direct-table" style="width:'.$thewidth.';margin:0px;float:left">';
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

					$fake_res = array( 'arrivalDate' => date("d.m.Y", $dateofeachday), 'nights' => 1, 'reservated' => date("d.m.Y", $dateofeachday-$reservated), 'room' => $_POST['room'], 'special' => $_POST['offer'], 'number' => $persons, 'childs' => $childs, 'email' => 'test@test.deve', 'price' => '', 'customp' => '' );
					$fake_res_object = (object) $fake_res;
					$calculate_price = easyreservations_price_calculation( '', array($fake_res_object) );

					if($price == 1) $formated_price = $calculate_price['price'].$currency;
					elseif($price == 2) $formated_price = $calculate_price['price'];
					elseif($price == 3) $formated_price = reservations_format_money($calculate_price['price'], 1);
					elseif($price == 4) $formated_price = reservations_format_money($calculate_price['price']);

					$final_price = '<span class="calendar-cell-price">'.$formated_price.'</b>';
				} else $final_price = '';

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
				echo '<td class="calendar-cell'.$todayClass.$backgroundtd.'" onclick="easyreservations_click_calendar(this,\''.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'\', \''.$rand.'\', \''.$key.'\')" id="easy-cal-'.$rand.'-'.$diff.'-'.$key.'" axis="'.$diff.'">'.$diff.''.$final_price.'</td>'; $setet++; $diff++;
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
		$mode = $_POST['mode'];
		$val_from = strtotime($_POST['from']);
		$val_to = strtotime($_POST['to']);
		$val_name = $_POST['thename'];
		$val_persons = (int) $_POST['persons'];
		$val_childs = (int) $_POST['childs'];
		$val_room = (int) $_POST['room'];
		
		$val_nights = ($val_to - $val_from)/86400;
		$error = "";

		if((strlen($val_name) > 30 || strlen($val_name) <= 1 ||  !preg_match('/^[A-Za-z\s]+$/i',$val_name)) && $val_name != ""){ /* check name */
			$error[] = 'easy-form-thename';
			$error[] = __( 'Please enter a correct name' , 'easyReservations' );
		}

		if($val_from-(strtotime(date("d.m.Y", time()))) < 0){ /* check arrival Date */
			$error[] = 'easy-form-from';
			$error[] =  __( 'The arrival date has to be in future' , 'easyReservations' );
		}

		if($val_to <= $val_from){ /* check difference between arrival and departure date */
			$error[] = 'easy-form-to';
			$error[] = __( 'Depature before arrival' , 'easyReservations' );
		}

		if(!is_email( $_POST['email']) && $_POST['email'] != ""){ /* check email */
			$error[] = 'easy-form-email';
			$error[] =  __( 'Please enter a correct eMail' , 'easyReservations' );
		}

		if (!is_numeric($_POST['persons'])){ /* check persons */
			$error[] = 'easy-form-persons';
			$error[] = __( 'Persons has to be a number' , 'easyReservations' );
		}

		if($val_from > time()) $numbererrors=easyreservations_check_avail($_POST['room'], $val_from, 0, (( $val_to - $val_from ) / 86400), (int) $_POST['offer'], 1 ); /* check rooms availability */

		if($numbererrors > 0){
			$error[] = 'date';
			$error[] = __( 'Isn\'t available at' , 'easyReservations' ).' '.$numbererrors;
		}

		$resource_req = get_post_meta($_POST['room'], 'easy-resource-req', TRUE);
		if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 1, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);
		$rooms = easyreservations_get_rooms();

		if($resource_req['pers-min'] > ($val_persons+$val_childs)){
			$error[] = 'easy-form-persons';
			$error[] =  sprintf(__( 'At least %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-min'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['pers-max'] != 0 && $resource_req['pers-max'] < ($val_persons+$val_childs)){
			$error[] = 'easy-form-persons';
			$error[] =  sprintf(__( 'Maximum %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-max'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['nights-min'] > $val_nights){
			$error[] = 'date';
			$error[] =  sprintf(__( 'At least %1$s nights in %2$s' , 'easyReservations' ), $resource_req['nights-min'], __(easyreservations_get_the_title($val_room, $rooms)));
		}
		if($resource_req['nights-max'] != 0 && $resource_req['nights-max'] < $val_nights){
			$error[] = 'date';
			$error[] =  sprintf(__( 'Maximum  %1$s nights in %2$s' , 'easyReservations' ), $resource_req['nights-max'], __(easyreservations_get_the_title($val_room, $rooms)));
		}

		if($_POST['offer'] > 0){
			$offers = easyreservations_get_offers();
			$resource_req = get_post_meta($_POST['offer'], 'easy-resource-req', TRUE);
			if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 1, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);

			if($resource_req['pers-min'] > ($val_persons+$val_childs)){
				$error[] = 'easy-form-persons';
				$error[] =  sprintf(__( 'At least %1$s persons for %2$s' , 'easyReservations' ), $resource_req['pers-min'], __(easyreservations_get_the_title($_POST['offer'], $offers)));
			}
			if($resource_req['pers-max'] != 0 && $resource_req['pers-max'] < ($val_persons+$val_childs)){
				$error[] = 'easy-form-persons';
				$error[] =  sprintf(__( 'Maximum %1$s persons for %2$s' , 'easyReservations' ), $resource_req['pers-max'], __(easyreservations_get_the_title($_POST['offer'], $offers)));
			}
			if($resource_req['nights-min'] > $val_nights){
				$error[] = 'date';
				$error[] =  sprintf(__( 'At least  %1$s nights for %2$s' , 'easyReservations' ), $resource_req['nights-max'], __(easyreservations_get_the_title($_POST['offer'], $offers)));
			}
			if($resource_req['nights-max'] != 0 && $resource_req['nights-max'] < $val_nights){
				$error[] = 'date';
			$error[] =  sprintf(__( 'Maximum  %1$s nights for %2$s' , 'easyReservations' ), $resource_req['nights-max'], __(easyreservations_get_the_title($_POST['offer'], $offers)));
			}
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

		wp_localize_script( 'easyreservations_send_calendar', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL ) );
		wp_localize_script( 'easyreservations_send_price', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => WP_PLUGIN_URL ) );
		wp_localize_script( 'easy-form-js', 'easyDate', array( 'easydateformat' => RESERVATIONS_DATE_FORMAT ) );

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