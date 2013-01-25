<?php
/**
 * Reservations Class
 * *
 * @author Feryaz Beer (support@easyreservations.org)
 * @version 1.0
 *
 * Usage:
 * new Reservation(52); //by ID, load from database
 * new Reservation(false, $array); //$array from foreach with all informations or custom one for new reservation
 */

	class Reservation {
		public $id;
		public $arrival = '';
		public $departure = '';
		public $name = '';
		public $email = '';
		public $country = '';
		public $adults = 1;
		public $childs = 0;
		public $custom = array();
		public $prices = array();
		public $status = '';
		public $interval = 86400;
		public $resource = 0;
		public $resourcenumber = 0;
		public $reservated = 0;
		public $times = 1; // hours/days/weeks of reservation by resource interval
		public $user = 0; // reservation connected to wp_user; unused information
		public $price = 0;
		public $paid = 0;
		public $history = array(); // If called Calculate(true); this will contain a calculation history
		public $pricepaid = ''; // row price from database - contains price;paid if the price is fixed or paid
		public $admin = true; 

		//----------------------------------------- Initialize -------------------------------------------------//

		/**
		 * Construction
		 * @param integer/bool $id ID of reservation
		 * @param array/bool $array Array with reservations informations
		 * @param bool $admin false for frontend
		 * @throws easyException #1, #2
		 */
		public function __construct($id = false, $array = false, $admin = true){
			$this->id = $id;
			$this->admin = $admin;

			if($this->id && ($this->id < 1 || !is_numeric($this->id))){
				throw new easyException( 'ID must be Integer and > 0; ID: '.$this->id, 1 );
			} elseif($id && $array){
				$this->ArrayToReservation($this->cleanSqlContent($array));
			} elseif($this->id){
				return $this->getInformations($this->id);
			} elseif($array){
				$this->fake = true;
				if(isset($array[0]) && $array[0] == 'dontclean'){
					unset($array[0]);
					$this->ArrayToReservation($array);
				} else $this->ArrayToReservation($this->cleanSqlContent($array));
			} else {
				throw new easyException( 'Need either reservations ID or array with informations', 2 );
			}
		}

		/**
		 * Select from database
		 * @global obj $wpdb Database class
		 * @param int $id ID of reservation
		 * @throws easyException #3
		 */
		private function getInformations($id){
			global $wpdb;
			$reservation = $wpdb->get_results($wpdb->prepare("SELECT id, name, approve, arrival, departure, room, roomnumber, number, childs, country, email, custom, customp, price, reservated, user FROM ".$wpdb->prefix ."reservations WHERE id='%d'", $id ));

			if(isset($reservation[0]) && $reservation[0] && $reservation[0] !== 0){
				$this->ArrayToReservation($this->cleanSqlContent((array) $reservation[0]));
				return true;
			} else {
				throw new easyException( 'Reservation isn\'t existing ID: '.$id, 3 );
				return false;
			}
		}

		/**
		 * Change keys of database array to object informations names
		 */
		private function cleanSqlContent($array){
			if(isset($array['room'])) $array['resource'] = (int) $array['room'];
			if(isset($array['roomnumber'])) $array['resourcenumber'] = (int) $array['roomnumber'];
			if(isset($array['approve'])) $array['status'] = $array['approve'];
			if(isset($array['customp'])) $array['prices'] = $array['customp'];
			if(isset($array['number'])) $array['adults'] = (int) $array['number'];
			if(isset($array['childs'])) $array['childs'] = (int) $array['childs'];
			if(isset($array['price'])) $array['pricepaid'] = $array['price'];
			if(!is_numeric($array['arrival'])) $array['arrival'] = strtotime($array['arrival']);
			if(!is_numeric($array['departure'])) $array['departure'] = strtotime($array['departure']);
			if(isset($array['reservated']) && !is_numeric($array['reservated'])) $array['reservated'] = strtotime($array['reservated']);
			unset($array['room'], $array['roomnumber'], $array['customp'], $array['approve'], $array['price'], $array['number']);
			return $array;
		}

		/**
		 * Informations from fake/db array to class infromations; check if resource exists; get resource interval
		 * @throws easyException #4, #5
		 */
		public function ArrayToReservation($array){
			if(!empty($array)){
				foreach($array as $key => $information){
					if(isset($this->$key) || in_array($key, array('fake', 'fixed', 'coupon'))) $this->$key = $information;
				}
			}

			if($this->resource){
				if($this->resource && is_numeric($this->resource) && $this->resource  > 0){
					easyreservations_load_resources(true);
					global $the_rooms_intervals_array, $the_rooms_array;
					if(isset($the_rooms_intervals_array[$this->resource])){
						$this->interval = $the_rooms_intervals_array[$this->resource];
						$this->resourcename = __($the_rooms_array[$this->resource]->post_title);
						$this->times = $this->getTimes( 0 );
					} else {
						$this->destroy();
						//throw new easyException( 'Resource isn\' existing ID: '.$this->resource, 4 );
					}
				} else {
					throw new easyException( 'Resource ID must be Integer and > 0; ID: '.$this->resource, 5 );
				}
			}
		}

		//----------------------------------------- Functions -------------------------------------------------//

		/**
		 *	Calculate reservation; access with $obj->price
		 * @param type $history if true $obj->history will contain a calculation history
		 * @return int $obj->price 
		 */
		public function Calculate($history = false){
			$this->price = 0;
			$countpriceadd=0; // Count times (=days) a sum gets added to price
			$countgroundpriceadd=0; // Count times (=days) a groundprice is added to price
			$datearray = '';
			$filters = get_post_meta($this->resource, 'easy_res_filter', true);
			$resource_groundprice = get_post_meta($this->resource, 'reservations_groundprice', true);
			$price_per_person = get_post_meta($this->resource, 'easy-resource-price', true);
			if(is_array($price_per_person)){
				if(isset($price_per_person[1]) && $price_per_person[1] == 1) $this->once = true;
				$price_per_person = $price_per_person[0];
			}
			$taxes = get_post_meta($this->resource, 'easy-resource-taxes', true);
			if($this->departure == 0) $this->departure = $this->arrival+$this->interval;

			if(!empty($filters)){
				foreach($filters as $num => $filter){
					if($filter['type'] == 'price'){
						for($t = 0; $t < $this->times; $t++){
							$i = $this->arrival + ($t*$this->interval);
							if(isset($this->once) && $countpriceadd > 0) break;
							if(!is_array($datearray) || !In_array($i, $datearray)){
								if($this->timeFilter($filter,$i)){
									if(strpos($filter['price'], '%') !== false){
										$percent = str_replace('%',  '', $filter['price']);
										$amount = round($resource_groundprice/100*$percent,2);
									} else $amount = $filter['price'];
									$this->price+=$amount; $countpriceadd++;
									if($history) $this->history[] = array('date' => $i, 'priceday' => $amount, 'type' => 'pricefilter', 'name' => __($filter['name']));
									$datearray[] = $i;
								}
							}
						}
						unset($filters[$num]);
					} else break;
				}
			}

			while($countpriceadd < $this->times){
				if(isset($this->once) && $countpriceadd > 0) break;
				$this->price+=$resource_groundprice;
				$ifDateHasToBeAdded=0;
				if(isset($datearray)){ $getrightday=0;
					while($getrightday==0){
						if(is_array($datearray) && in_array($this->arrival+($countgroundpriceadd*$this->interval)+($ifDateHasToBeAdded*$this->interval), $datearray)){
							$ifDateHasToBeAdded++;
						} else {
							$getrightday++;
						}
					}
					$datearray[]=$this->arrival+($countgroundpriceadd*$this->interval)+($ifDateHasToBeAdded*$this->interval);
				}
				if($history) $this->history[] = array('date'=>$this->arrival+($countgroundpriceadd*$this->interval)+($ifDateHasToBeAdded*$this->interval), 'priceday'=>$resource_groundprice, 'type'=> 'groundprice');
				$countgroundpriceadd++;
				$countpriceadd++;
			}

			$checkprice=$this->price;
			if($price_per_person == 1 && ($this->adults > 1 || $this->childs > 0)) {  // Calculate Price if  "Calculate per person"  was choosen
				if($this->adults > 1){
					$price_adults = $checkprice*$this->adults;
					$this->price += $price_adults-$checkprice;
					if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$price_adults-$checkprice,'type'=> 'persons', 'name' => $this->adults);
					$countpriceadd++;
				}

				if(!empty($this->childs) && $this->childs > 0){
					$childprice = get_post_meta($this->resource, 'reservations_child_price', true);
					if($childprice != -1){
						if(substr($childprice, -1) == "%"){
							$percent=$checkprice/100*(str_replace("%", "", $childprice)*$this->times);
							$childsPrice = ($checkprice - $percent);
						} else $childsPrice = ($checkprice - $childprice*$this->times);
						$childsPrice = $childsPrice*$this->childs;
						$this->price += $childsPrice;
						if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$childsPrice, 'type'=> 'childs', 'name' => $this->childs);
						$countpriceadd++;
					}
				}
			}

			if(!empty($filters)){  //IF Filter array has elemts left they should be discounts or unavails or nonsense
				$staywasfull=0; $loyalwasfull=0; $perswasfull=0; $earlywasfull=0;
				$staywasfull_charge=0; $loyalwasfull_charge=0; $perswasfull_charge=0; $earlywasfull_charge=0;
				foreach($filters as $filter){
					$discount_add = 0;
					if($filter['type'] == 'stay'){// Stay Filter
						if(($staywasfull==0 || $staywasfull_charge == 0) && (int) $filter['cond'] <= (int) $this->times){
 							$discount_add = 1;
						}
					} elseif($filter['type'] == 'loyal'){// Loyal Filter
						if(($loyalwasfull==0 || $loyalwasfull_charge == 0) && is_email($this->email)){
							global $wpdb;
							$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='%s' AND departure < NOW()",$this->email)); //number of total rows in the database
							if($filter['cond'] <= $items1){
								$discount_add = 1;
							}
						}
					} elseif($filter['type'] == 'pers'){// Persons Filter
						if($perswasfull==0 || $perswasfull_charge == 0){
							if($filter['cond'] <= ($this->adults + $this->childs)){
								$discount_add = 1;
							}
						}
					} elseif($filter['type'] == 'early'){// Early Bird Discount Filter
						if($earlywasfull==0 || $earlywasfull_charge == 0){
							if($this->reservated == 0) $this->reservated = time();
							$dayBetween=round(($this->arrival-$this->reservated)/$this->interval,2); // cals days between booking and arrival
							if($filter['cond'] <= $dayBetween){
								$discount_add = 1;
							}
						}
					} elseif($filter['type'] != 'price') break;

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
							else break;
						}

						if($filter['modus'] == '%'){
							$percent=$this->price/100* (int) $discount_amount;
							$this->price+=$percent;
							if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$percent, 'type'=>$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
						} elseif($filter['modus'] == "price_res"){
							$this->price+=$discount_amount;
							if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$discount_amount, 'type'=>$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
						} elseif($filter['modus'] == "price_pers"){
							$the_discount = $discount_amount * ($this->adults + $this->childs);
							$this->price += $the_discount;
							if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$the_discount, 'type'=>$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
						} elseif($filter['modus'] == "price_both"){
							$the_discount = $discount_amount * ($this->adults + $this->childs) * $this->times;
							$this->price += $the_discount;
							if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$the_discount, 'type'=>$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
						} else { // $filter['modus'] == price_day
							$the_discount = $discount_amount *  $this->times;
							$this->price+= $the_discount;
							if($history) $this->history[] = array('date'=>$this->arrival+($countpriceadd*$this->interval), 'priceday'=>$the_discount, 'type'=>$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
						}
					}
				} 
			}

			if(function_exists('easyreservations_calculate_coupon')){
				$save = '';
				if(isset($this->coupon) && !empty($this->coupon) && $this->coupon !== false){
					$explode = explode(',', $this->coupon);
					$coupons = '';
					foreach($explode as $code) if(!empty($code)) $coupons[] = array('type' => 'coup','value'=>$code);
					if(!empty($coupons)) $save = easyreservations_calculate_coupon($coupons, $this, $countpriceadd);
				} elseif((!isset($this->fake) || !$this->fake) && !empty($this->prices)){
					$save = easyreservations_calculate_coupon($this->prices, $this, $countpriceadd);
				}
				if(!empty($save)){
					$this->price += $save['price'];
					if($history) $this->history = array_merge((array) $this->history, (array) $save['exactly']);
					$countpriceadd = $save['countpriceadd'];
				}
			}

			$customprices = 0;
			if(!empty($this->prices)){
				if(is_numeric($this->prices)){
					$customprices = $this->prices;
					$this->price += $customprices;
				} else {
					$customps = $this->getCustoms($this->prices, 'cstm');
					foreach($customps as $customprice){
						if(isset($customprice['type']) && $customprice['type'] == "cstm"){
							if(!isset($customprice['amount'])) continue;
							if(substr($customprice['amount'], -1) == "%"){
								$percent=$this->price/100*str_replace("%", "", $customprice['amount']);
								$customprices+=$percent;
								if($history) $this->history[] = array('date'=>$this->arrival+(($countpriceadd+10)*$this->interval), 'priceday'=>$percent, 'type' => 'customp_p', 'name' => __($customprice['title']), 'value' => __($customprice['value']), 'amount' => $customprice['amount']);
							} else {
								$customprices+=$customprice['amount'];
								if($history) $this->history[] = array('date'=>$this->arrival+(($countpriceadd+10)*$this->interval), 'priceday'=>$customprice['amount'], 'type' => 'customp_n', 'name' => __($customprice['title']),'value' => __($customprice['value']), 'amount' => $customprice['amount']);
							}
						}
					}
					$this->price+=$customprices; //Price plus Custom prices
				}
			}

			if(!empty($this->pricepaid)){
				$pricexpl=explode(";", $this->pricepaid);
				if($pricexpl[0]  > 0 && $pricexpl[0]!=''){
					$this->price=$pricexpl[0];
					$this->fixed = true;
				}
				if(isset($pricexpl[1]) && $pricexpl[1] > 0) $this->paid=str_replace(',','.',$pricexpl[1]);
				if(!is_numeric($this->paid) || $this->paid <= 0) $this->paid = 0;
			}

			$checkprice = $this->price;
			$checkprice_both = 0;
			if($taxes && !empty($taxes)){
				$this->taxrate = 0;
				$this->taxamount = 0;
				foreach($taxes as $tax){
					if(!isset($tax[2]) || $tax[2] == 0){
						if($checkprice_both == 0) $checkprice_both = $this->price;
						$theprice = $checkprice_both;
						$plus = 20;
					} elseif($tax[2] == 1){
						$theprice = $checkprice - $customprices;
						$plus = 5;
					} elseif($tax[2] == 2 && !isset($this->fixed)){
						$theprice = $customprices;
						$plus = 15;
					}
					$taxamount = $theprice / 100 * $tax[1];
					if(!isset($this->fixed)) $this->price += $taxamount;
					$this->taxamount += $taxamount;
					$this->taxrate += $tax[1];
					if($history) $this->history[] = array('date'=>$this->arrival+(($countpriceadd+$plus)*$this->interval), 'priceday'=>$taxamount, 'type' => 'tax', 'name' => __($tax[0]), 'amount' => $tax[1], 'class' => (isset($tax[2])) ? $tax[2] : 0);
				}
			}

			if($history && !empty($this->history)){
				$dates = null;
				foreach ($this->history as $key => $row) {
					$dates[$key]  = $row['date'];
				}
				array_multisort($dates, SORT_ASC, $this->history);
			}

			$this->price = round($this->price,2);
			return $this->price;
		}

		/**
		* Check availability
		* @global obj $wpdb database connection
		* @param int $mode 0: returns number; 1: returns unavail dates string
		* @param bool $filter 
		* @return int/string availability information 
		*/
		public function 	checkAvailability($mode=0, $filter = true){
			global $wpdb, $reservations_settings;
			$error=null;
			$afterpersons=false;
			$interval = easyreservations_get_interval($this->interval, $this->resource, 1);
			$res_number = '';
			$arrival = "arrival";
			$departure = "departure";
			$mergeres = 0;
	
			if($interval == 3600) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:00';
			else $date_pattern = RESERVATIONS_DATE_FORMAT;
			
			if(isset($reservations_settings['mergeres']) && is_array($reservations_settings['mergeres']) && ($mode != 0 || !$this->admin)){
				if(isset($reservations_settings['mergeres']['blockbefore']) && $reservations_settings['mergeres']['blockbefore'] > 0) $arrival = "arrival - INTERVAL ".((int) $reservations_settings['mergeres']['blockbefore'] * 60)." SECOND";
				if(isset($reservations_settings['mergeres']['blockbefore']) && $reservations_settings['mergeres']['blockbefore'] > 0) $departure = "departure + INTERVAL ".((int) $reservations_settings['mergeres']['blockafter'] * 60)." SECOND";
				$mergeres = $reservations_settings['mergeres']['merge'];
			}

			if($mergeres > 0){
				$roomcount = $mergeres;
				$res_sql = '';
			} else {
				if($this->resourcenumber > 0) $res_number = " roomnumber='$this->resourcenumber' AND";
				$res_sql = "room='$this->resource' AND";
				$roomcount = get_post_meta($this->resource, 'roomcount', true);
				if(is_array($roomcount)){
					$roomcount = $roomcount[0];
					$afterpersons = true;
				}
			}

			if($this->id) $idsql = " id != '$this->id' AND";
			else $idsql = '';
			if($this->resource > 0){
				if($filter) $error = $this->availFilter($roomcount, $mode, $interval);
				if($mode < 3){
					if($mode == 0){
						$startdate = date("Y-m-d H:i:s", $this->arrival+60);
						$enddate = date("Y-m-d H:i:s", $this->departure-60);

						if($afterpersons){
							$prepare = $wpdb->prepare("SELECT SUM(number+childs) FROM ".$wpdb->prefix."reservations WHERE approve='yes' AND $res_sql $idsql '%s' <= $departure AND '%s' >= $arrival", array($startdate, $enddate));
							$count = $wpdb->get_var($prepare);
							if($count == NULL || $count < 1) $count = 0;
							$count = $count+$this->childs+$this->adults;
							if($count > $roomcount) $error += $count;
						} else {
							$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."reservations WHERE approve='yes' AND $res_sql $res_number $idsql '%s' <= $departure AND '%s' >= $arrival", array($startdate, $enddate)));
							if(!empty($res_number) || $count >= $roomcount) $error += $count;
						}
					} else {
						for($t = 0; $t < $this->times; $t++){
							$i = strtotime(date('Y-m-d', $this->arrival))+($interval/2)+$t*$interval;
							$startdate=date("Y-m-d H:i:s", $i);
							$enddate=date("Y-m-d H:i:s", $i+$interval-60);
							if($interval == 3600)	$addstart = " HOUR($departure) != HOUR('$startdate')";
							else $addstart = " DATE($departure) != DATE('$startdate')";
							if($afterpersons){
								$count = $wpdb->get_var($wpdb->prepare("SELECT SUM(number+childs) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND $res_sql $idsql (%s < $departure AND %s > $arrival) AND $addstart", array($startdate, $enddate)));
								if($count < 1) $count = 0;
								$count = $count+$this->childs+$this->adults;
								if($mode == 1 && $count > $roomcount) $error .= date($date_pattern, $i).', ';
								elseif($mode == 0 && $count > $roomcount)  $error += $roomcount;
							} else {
								$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND $res_sql $idsql (%s <= $departure AND %s >= $arrival) AND $addstart", array($startdate, $enddate)));
								if($mode == 1 && $count >= $roomcount) $error .= date($date_pattern, $i).', ';
								elseif($mode == 0 && $count >= $roomcount)  $error += $roomcount;
							}
						}
					}
				} else {
					$startdate = date("Y-m-d H:i:s", $this->arrival);
					if($afterpersons){
						$addstart = "";
						if($interval > 3600) $addstart = "AND DATE('$startdate') != DATE($departure)";
						$count = $wpdb->get_var("SELECT sum(number+childs) as count FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND $res_sql $idsql '$startdate' BETWEEN $arrival AND $departure $addstart");
						if($mode == 4 && $count >= $roomcount) $error += $count;
						elseif($mode == 3) $error += $count;
					} else {
						$addstart = ''; $addend = '';
						$addstart1 =   "DATE('$startdate') BETWEEN DATE($arrival) AND DATE($departure)";
						if($interval == 3600){
							$addstart = " AND HOUR($arrival) = HOUR('$startdate') AND $arrival != '$startdate'";
							$addend  = " AND HOUR($departure) = HOUR('$startdate')";
							$addstart1 =   "'$startdate' BETWEEN $arrival AND $departure - INTERVAL 1 SECOND";
						}
						$count = $wpdb->get_var("SELECT sum(Case When DATE($arrival) = DATE('$startdate')$addstart Then 0.51 When DATE($departure) = DATE('$startdate')$addend Then 0.5 Else 1 End) as count FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND $res_sql $idsql $addstart1");
						if($mode == 4 && $count >= $roomcount) $error += $count;
						elseif($mode == 3) $error += $count;
					}
				}
			}
			if($mode == 1) $error = substr($error,0,-2);
			if(empty($error)) $error = false;
			return $error;
		}

		public function availFilter($roomcount=1, $mode=0, $interval = false){ //Check if a resource is Avail or Full
			$filters = get_post_meta($this->resource, 'easy_res_filter', true);
			if($this->interval == 3600) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:00';
			else $date_pattern = RESERVATIONS_DATE_FORMAT;
			if(!$interval) $interval = easyreservations_get_interval($this->interval, $this->resource, 1);
			if($this->departure == 0) $departure = $this->arrival + $interval;
			else $departure = $this->departure;
			$error = '';
			if(!empty($filters)){
				foreach($filters as $filter){
					if($filter['type'] == 'unavail'){
						for($t = 0; $t < $this->times; $t++){
							$i = $this->arrival + ($t*$this->interval);
							$check = $this->timeFilter($filter, $i, $interval);
							if($check){
								if($mode == 1 && is_string($check)) $error .= $check;
								elseif($mode == 1) $error .= date($date_pattern, $i).', ';
								elseif($mode == 2)  $error[$i] = $roomcount;
								else $error += $roomcount;
							}
						}
					}
				}
			}
			return $error;
		}

		private function timeFilter($filter, $i, $interval = false){
			if($filter['cond'] == 'unit'){
				return $this->unitFilter($filter,$i, $interval);
			} elseif($filter['cond'] == 'date'){
				if(date("d.m.Y", $i) == date("d.m.Y", $filter['date']) && ($this->interval > 3600 || date("H",$i) == date("H", $filter['date']))) return true;
			} else {
				$from = $filter['from'];
				$to = $filter['to'];
				if($i >= $from && $i  <= $to){
					return true;
				}
			}
			return false;
		}

		private function unitFilter($filter, $i, $interval = false){
			if(!$interval) $interval = $this->interval;
			if(!isset($filter['year']) || empty($filter['year']) || ( in_array(date("Y", $i), explode(",", $filter['year'])))){
				if(!isset($filter['quarter']) || empty($filter['quarter']) || ( in_array(ceil(date("m", $i) / 3), explode(",", $filter['quarter'])))){
					if(!isset($filter['month']) || empty($filter['month']) || ( in_array(date("n", $i), explode(",", $filter['month'])))){
						if(!isset($filter['cw']) || empty($filter['cw']) || ( in_array(date("W", $i), explode(",", $filter['cw'])))){
							if(!isset($filter['day']) || empty($filter['day']) || ($interval < 86500 &&  in_array(date("N", $i), explode(",", $filter['day'])))){
								if(!isset($filter['hour']) || empty($filter['hour']) || ($interval < 3650 && in_array(date("H", $i), explode(",", $filter['hour'])))){
									return true;
								} elseif(isset($filter['hour']) && !empty($filter['hour']) && ($interval > 3650)){
									$time = strtotime(date(RESERVATIONS_DATE_FORMAT,$i));
									foreach(explode(",", $filter['hour']) as $hour){
										$checktime = $time+($hour*3600);
										if($this->arrival <= $checktime && $this->departure >=  $checktime) return date(RESERVATIONS_DATE_FORMAT.' H:00', $checktime).', ';
									}
								}
							}
						}
					}
				}
			}
			return false;
		}

		/**
		 * Add/edit/delete custom informations(s)
		 * @param array $new_custom
		 * @param bool $mass true for replacing all customs of the selected type/modus/check
		 * @param bool/int $thekey Key of custom information to edit/delete
		 * @param bool $price true for price fields
		 * @param string/bool $type Filter by type; false for all
		 * @param string/bool $modus Filter by modues; false for all
		 * @param array/bool $check Filter by array('attribute'  => 'value'); false for all
		 * @return array with all customs/prices
		 */
		public function Customs($new_custom, $mass = false, $thekey = false, $price = false, $type = false, $modus = false, $check = false){
			if($price) $all_customs = $this->prices;
			else $all_customs = $this->custom;
			$all_customs_save = '';

			if(!empty($all_customs)){
				$all_customs_save  = maybe_unserialize($all_customs);
				if($mass){
					if(!empty($all_customs_save)){
						foreach($all_customs_save as $key => $cstm){
							if((!$type || $cstm['type'] == $type) && (!$modus || $cstm['mode'] == $modus || $cstm['mode'] == 'visible') && (!$check || $cstm[$check[0]] == $check[1])){
								unset($all_customs_save[$key]);
							}
						}
					}
				} else {
					if(!is_numeric($thekey)){
						$all_customs_save = $all_customs_save;
					} else {
						if(isset($all_customs_save[$thekey]) && (!$type || $all_customs_save[$thekey]['type'] == $type) && (!$modus || $all_customs_save[$thekey]['mode'] == $modus) && (!$check || $all_customs_save[$thekey][$check[0]] == $check[1])) unset($all_customs_save[$thekey]);
						else return false;
					}
				}
			}

			if(!is_array($all_customs_save) || empty($all_customs_save)) $all_customs_save = array();
			if($new_custom){
				if(!isset($new_custom[0]) || !is_array($new_custom[0])) $new_custom = array($new_custom);
				foreach($new_custom as $newcustom){
					$all_customs_save[] = $newcustom;
				}
			}
			//$all_customs_serial = maybe_serialize($all_customs_save);

			if($price) $this->prices = $all_customs_save;
			else $this->custom = $all_customs_save;
			return $all_customs_save;
		}

		public function getCustoms($custom = false, $type = false, $modus = false, $oderbytitle = false){
			if(!$custom) $custom = $this->custom;
			if(!is_array($custom)) $custom = maybe_unserialize($custom);

			if(!empty($custom) && is_array($custom)){
				foreach($custom as $key => $cstm){
					if(empty($cstm) || ( $type && isset($cstm['type']) && $cstm['type'] !== $type ) || ( $modus && isset($cstm['mode']) && $cstm['mode'] != $modus && $cstm['mode'] != 'visible' )) unset($custom[$key]);
					elseif($oderbytitle){
						$custom[strtolower($cstm['title'])] = $cstm;
						unset($custom[$key]);
					}
				}
			}

			return $custom;
		}

		function formatPrice($color_paid=false, $cur = true, $dig = 2, $amount = false){
			if(!$amount && $amount !== 0) $amount = $this->price;
			if($cur) $price = easyreservations_format_money($amount, 1, $dig);
			else $price = easyreservations_format_money($amount, 0, $dig);

			if($color_paid){
				if($this->paid == $this->price) $pricebgcolor='color:#118D18;';
				elseif($this->paid > $this->price) $pricebgcolor='color:#ab2ad6;';
				elseif($this->paid > 0) $pricebgcolor='color:#ffcb49;';
				else $pricebgcolor='color:#BC0B0B;';
				$price = '<b style="'.$pricebgcolor.';padding:1px;font-weight:bold !important;">'.$price.'</b>';
			}
			return $price;
		}

		public function getTimes($mode = 1){
			if(RESERVATIONS_USE_TIME == 1){
				$arrival = $this->arrival;
				$departure = $this->departure;
			} else {
				$arrival = strtotime(date('d.m.Y', (int) $this->arrival))+43200;
				$departure = strtotime(date('d.m.Y', (int) $this->departure))+43200;
			}
			$diff = 0;
			if(version_compare(PHP_VERSION, '5.3.0') >= 0){
				$timezone = new DateTimeZone(date_default_timezone_get ());
				$transitions = $timezone->getTransitions($arrival, $departure);
				if(isset($transitions[1]) && $transitions[0]['offset'] != $transitions[1]['offset']){
					if($transitions[0]['offset'] > $transitions[1]['offset']) $diff = $transitions[0]['offset'] - $transitions[1]['offset'];
					else $diff = $transitions[1]['offset'] - $transitions[0]['offset'];
				}
			}
			$number = ($departure-$arrival-$diff) / easyreservations_get_interval($this->interval, 0,  $mode);
			$this->times = ( is_numeric($number)) ? (ceil(ceil($number/0.01)*0.01)) : false;
			if($this->times < 1) $this->times = 1;
			return $this->times;
		}

		public function Validate($mode = 'send', $avail = 1, $mini = false){
			$errors = '';
			$this->name = trim($this->name);
			if(strlen($this->name) > 50 || ($mode == 'send' && (empty($this->name) || strlen($this->name) <= 1))){
				if(!$this->admin) $errors[] = 'easy-form-thename';
				$errors[] = __( 'Please enter a correct name' , 'easyReservations' );
			}

			$this->email = trim($this->email);
			if($mode == 'send'  && (!is_email( $this->email) || empty($this->email))){ /* check email */
				if(!$this->admin) $errors[] = 'easy-form-email';
				$errors[] =  __( 'Please enter a correct Email' , 'easyReservations' );
			}

			if($this->departure < 1000000 ||  $this->arrival < 1000000){
				if(!$this->admin) $errors[] = 'date';
				$errors[] =  __( 'Please enter correct dates' , 'easyReservations' );
				$daterror = true;
			}

			if($this->departure < $this->arrival){ /* check arrival Date */
				if(!$this->admin)  $errors[] = 'easy-form-to';
				$errors[] = __( 'The departure date has to be after the arrival date' , 'easyReservations');
				$daterror = true;
			}

			if(!is_numeric($this->adults) || $this->adults < 1){ /* check persons */
				if(!$this->admin)  $errors[] = 'easy-form-persons';
				$errors[]  = __( 'Adults has to be a number at least one' , 'easyReservations' );
			}
			$this->adults = (int) $this->adults;

			if(!is_numeric($this->childs)){ /* check persons */
				if(!$this->admin)  $errors[] = 'easy-form-childs';
				$errors[]  = __( 'Children\'s has to be a number' , 'easyReservations' );
			}
			$this->childs = (int) $this->childs;

			if(!isset($daterror) && !$mini){
				$availability = $this->checkAvailability($avail, ($this->admin) ? false : true);
				if($availability){
					if(!$this->admin){
						$errors[] = 'date';
						if($avail > 0) $errors[] = __( 'Not available at' , 'easyReservations' ).' '.$availability;
						else $errors[] = __( 'Selected time is occupied' , 'easyReservations' );
					} else $errors[] = __( 'Selected time is occupied' , 'easyReservations' );
				} 
//				else {
//					$availability = $this->checkAvailability(0, ($this->admin) ? false : true);
//					if($availability){
//						if(!$this->admin) $errors[] = 'date';
//						$errors[] = __( 'Selected time is occupied' , 'easyReservations' );
//					}
//				}
			}

			if(!$this->admin){
				if($this->arrival < time()-86400){ /* check arrival Date */
					$errors[] = 'easy-form-from';
					$errors[] = __( 'The arrival date has to be in future' , 'easyReservations');
				}

				$filters = get_post_meta($this->resource, 'easy_res_filter', true);
				$filtred = false;
				if($filters && !empty($filters)){
					foreach($filters as $filter){
						if($filter['type'] == 'req'){
							for($t = 0; $t < $this->times; $t++){
								$price_add = 0;
								$i = $this->arrival + ($t*$this->interval);
								if($this->timeFilter($filter, $i)){
									$price_add = 1;
								}
							}
							if($price_add == 1){
								$errors = $this->checkRequirements($filter['req'], $errors, $mini);
								$filtred = true;
							}
						}
					}
				}

				if(!$filtred){
					$resource_req = get_post_meta($this->resource, 'easy-resource-req', TRUE);
					if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 0, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);
					$errors = $this->checkRequirements($resource_req, $errors, $mini);
				}
			}

			if(empty($errors)) return false;
			else return $errors;
		}

		private function checkRequirements($resource_req, $errors, $mini = false){
			easyreservations_load_resources(true);
			global $the_rooms_array, $the_rooms_intervals_array, $easy_max_persons;
			$easy_max_persons = $resource_req['pers-max'];
			if($resource_req['pers-min'] > ($this->adults+$this->childs)){
				if($mini) $errors[] = array('pers-min', $resource_req['pers-min']);
				else {
					$errors[] = 'easy-form-persons';
					$errors[] =  sprintf(__( 'At least %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-min'], __($the_rooms_array[$this->resource]->post_title));
				}
			}
			if($resource_req['pers-max'] != 0 && $resource_req['pers-max'] < ($this->adults+$this->childs)){
				if($mini) $errors[] = array('pers-max', $resource_req['pers-max']);
				else {
					$errors[] = 'easy-form-persons';
					$errors[] =  sprintf(__( 'Maximum %1$s persons in %2$s' , 'easyReservations' ), $resource_req['pers-max'], __($the_rooms_array[$this->resource]->post_title));
				}
			}
			if($resource_req['nights-min'] != 0 && $resource_req['nights-min'] > $this->times){
				if($mini) $errors[] = array('nights-min', $resource_req['nights-min']);
				else {
					$errors[] = 'date';
					$errors[] =  sprintf(__( 'At least %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-min'], easyreservations_interval_infos($the_rooms_intervals_array[$this->resource], 0, $resource_req['nights-min']), __($the_rooms_array[$this->resource]->post_title));
				}
			}
			if($resource_req['nights-max'] != 0 && $resource_req['nights-max'] < $this->times){
				if($mini) $errors[] = array('nights-max', $resource_req['nights-max']);
				else {
					$errors[] = 'date';
					$errors[] =  sprintf(__( 'Maximum %1$s %2$s in %3$s' , 'easyReservations' ), $resource_req['nights-max'], easyreservations_interval_infos($the_rooms_intervals_array[$this->resource], 0, $resource_req['nights-max']), __($the_rooms_array[$this->resource]->post_title));
				}
			}
			$daynames = easyreservations_get_date_name(0, 3);
			if(isset($resource_req['start-on']) && $resource_req['start-on'] != 0){
				if(!in_array(date("N", $this->arrival), $resource_req['start-on'])){
					$errors[] = 'easy-form-from';
					$start_days = '';
					foreach($resource_req['start-on'] as $starts){
						$start_days .= $daynames[$starts-1].', ';
					}
					if($mini) $errors[] = array('start-on', substr($start_days,0,-2));
					else $errors[] = sprintf(__( 'Arrival only possible on %s' , 'easyReservations' ), substr($start_days,0,-2));
				}
			}
			if(isset($resource_req['end-on']) && $resource_req['end-on'] != 0){
				if(!in_array(date("N", $this->departure), $resource_req['end-on'])){
					$errors[] = 'easy-form-to';
					$end_days = '';
					foreach($resource_req['end-on'] as $ends){
						$end_days .= $daynames[$ends-1].', ';
					}
					if($mini) $errors[] = array('end-on', substr($end_days,0,-2));
					else $errors[] = sprintf(__( 'Departure only possible on %s' , 'easyReservations' ), substr($end_days,0,-2));
				}
			}
			return $errors;
		}

		public function updatePricepaid($amount){
			if(!empty($this->pricepaid)){
				$explode = explode(';', $this->pricepaid);
				if(isset($explode[1]) && $explode[1] > 0) $amount = $amount + $explode[1];
				$this->pricepaid = $explode[0].';'.$amount;
			} else $this->pricepaid = ';'.$amount;
		}

		/**
		 * Send Mail
		 * @param string $options_name name of email option
		 * @param string $to (optional) Recriver's email - default: $this->email
		 * @param string $attachment (optional) URL of Attachment - default: false
		 * @return type bool true on success
		 */
		public function sendMail($options_name, $to = false, $attachment = false){ //Send formatted Mails from anywhere
			if(is_array($options_name)){
				$option = $options_name;
				$options_name = 'none';
			} else $option = get_option($options_name);
			if(isset($option['active']) && $option['active'] == 1){
				$theForm = $option['msg'];
				$subj = $option['subj'];
				$local = false;
				if(isset($_POST['easy-set-local'])){
					$oldlocal = get_locale();
					$local = $_POST['easy-set-local'];
					setlocale(LC_TIME, $local);
				}

				if(isset($_POST["approve_message"]) && !empty($_POST["approve_message"])) $theForm = $_POST["approve_message"].'-!DIVMESSAGE!-'.$theForm;
				$this->Calculate();
				$tags = easyreservations_shortcode_parser($theForm, true);
				foreach($tags as $fields){
					$field=shortcode_parse_atts( $fields);
					if(!isset($field[0])) continue;
					if($field[0]=="adminmessage"){
						$explode = explode('-!DIVMESSAGE!-',$theForm);
						if(isset($explode[1])){
							$message = $explode[0];
							$theForm = $explode[1];
						} elseif(isset($_POST["approve_message"])) $message = $_POST["approve_message"];
						$theForm=preg_replace('/\['.$fields.']/U', $message, $theForm);
					} elseif($field[0]=="ID"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->id, $theForm);
					} elseif($field[0]=="thename"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->name, $theForm);
					} elseif($field[0]=="email"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->email, $theForm);
					} elseif($field[0]=="arrivaldate" || $field[0]=="arrival" || $field[0]=="date-from"){
						$theForm=preg_replace('/\['.$fields.']/U', date(RESERVATIONS_DATE_FORMAT_SHOW, $this->arrival), $theForm);
					} elseif($field[0]=="changelog"){
						$changelog = '';
						if(isset($this->changelog)) $changelog = $this->changelog;
						$theForm=preg_replace('/\['.$fields.']/U', $changelog, $theForm);
					} elseif($field[0]=="departuredate" || $field[0]=="departure"){
						$theForm=preg_replace('/\['.$fields.']/U', date(RESERVATIONS_DATE_FORMAT_SHOW, $this->departure), $theForm);
					} elseif($field[0]=="units" || $field[0]=="times"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->times, $theForm);
					} elseif($field[0]=="nights" || $field[0]=="days"){
						$theForm=preg_replace('/\['.$fields.']/U', easyreservations_get_nights(86400, $this->reservated, time() ), $theForm);
					} elseif($field[0]=="hours"){
						$theForm=preg_replace('/\['.$fields.']/U', easyreservations_get_nights(3600, $this->reservated, time() ), $theForm);
					} elseif($field[0]=="weeks"){
						$theForm=preg_replace('/\['.$fields.']/U', easyreservations_get_nights(604800, $this->reservated, time(), 0), $theForm);
					} elseif($field[0]=="adults"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->adults, $theForm);
					} elseif($field[0]=="childs"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->childs, $theForm);
					} elseif($field[0]=="date"){
						if(isset($field[1])) $date = date($field[1], time());
						else $date = date(RESERVATIONS_DATE_FORMAT_SHOW, time());
						$theForm=preg_replace('/\['.$fields.']/U', $date, $theForm);
					} elseif($field[0]=="persons"){
						$theForm=preg_replace('/\['.$fields.']/U', $this->childs+$this->adults, $theForm);
					} elseif($field[0]=="rooms" || $field[0]=="resource"){
						$theForm=preg_replace('/\['.$fields.']/U',$this->resourcename, $theForm);
					} elseif($field[0]=="roomnumber" || $field[0]=="resource-number" || $field[0]=="resource-nr"){
						$theForm=preg_replace('/\['.$fields.']/U', __(easyreservations_get_roomname($this->resourcenumber, $this->resource)), $theForm);
					} elseif($field[0]=="country"){
						$theForm=preg_replace('/\['.$fields.']/U', easyreservations_country_name($this->country), $theForm);
					} elseif($field[0]=="price"){
						$theForm=str_replace('[price]', easyreservations_format_money($this->price), $theForm);
					} elseif($field[0]=="paid"){
						$theForm=str_replace('[paid]', easyreservations_format_money($this->paid), $theForm);
					} elseif($field[0]=="editlink"){
						$the_link = get_option("reservations_edit_url");
						$nonce =  substr(wp_hash(wp_nonce_tick() .'easyusereditlink0', 'nonce'), -12, 10);
						$the_edit_link = trim($the_link).'?edit&id='.$this->id.'&email='.$this->email.'&ernonce='.$nonce;
						if(!empty($the_link)) $theForm=str_replace('[editlink]', $the_edit_link, $theForm);
						else  $theForm=str_replace('[editlink]', '', $theForm);
					} elseif($field[0]=="customs"){
						$theCustominMail = '';
						if(!empty($this->custom)){
							$customs=$this->getCustoms($this->custom, 'cstm', 'edit', true);
							if(!isset($field[1])){
								foreach($customs as $custom) $theCustominMail .= $custom['title'].': '.$custom['value'].'<br>';
							} elseif(isset($field[1]) && isset($customs[strtolower($field[1])])){
								$theCustominMail .= $customs[strtolower($field[1])]['value'];
							} elseif(isset($field[1]) && isset($field['else'])) $theCustominMail .= $field['else'];
						}
						$theForm=str_replace('['.$fields.']', $theCustominMail, $theForm);
					} elseif($field[0]=="customprices" || $field[0]=="prices"){
						$theCustominMail = '';
						if(!empty($this->prices)){
							$customs= $this->getCustoms($this->prices, 'cstm', 'edit', true);
							if(!isset($field[1])){
								foreach($customs as $custom) $theCustominMail .= $custom['title'].' - '.$custom['value'].':  '.easyreservations_format_money($custom['amount']).'<br>';
							} elseif(isset($field[1]) && isset($customs[strtolower($field[1])])){
								$custom = $customs[strtolower($field[1])];
								if(isset($field['hide'])){
									if($field['hide'] == 'amount') $theCustominMail = $custom['value'];
									else $theCustominMail = easyreservations_format_money($custom['amount']);
								} else $theCustominMail .= $custom['value'].': '.easyreservations_format_money($custom['amount']);
							} elseif(isset($field[1]) && isset($field['else'])) $theCustominMail .= $field['else'];
						}
						$theForm=str_replace('['.$fields.']', $theCustominMail, $theForm);
					} elseif($field[0]=="paypal"){
						$link = '';
						if(function_exists('easyreservations_generate_paypal_button')){
							$percent = false;
							if(isset($field[1]) && is_numeric($field[1])) $percent = $field[1];
							$link = easyreservations_generate_paypal_button($this, $this->price, true, true, $percent);
							if(isset($field[2])){
								$field[2] = str_replace('"', '', $field[2]);
								$link = '<a href="'.$link.'">'.$field[2].'</a>';
							}
						}
						$theForm = str_replace('['.$fields.']', $link, $theForm);
					} else $theForm = apply_filters('easy-email-tag', $theForm, $fields, $field, $this);
				}

				$theForm = apply_filters( 'easy-email-content', $theForm, $local);
				$subj = apply_filters( 'easy-email-subj', $subj, $local);

				if(function_exists('easyreservations_send_multipart_mail')) $msg = easyreservations_send_multipart_mail($theForm);
				else {
					$theForm = explode('<--HTML-->', $theForm);
					$msg = str_replace('<br>', "\n",str_replace(']', '',  str_replace('[', '', $theForm[0])));
				}

				$support_mail = get_option("reservations_support_mail");

				if(empty($support_mail)) throw new easyException( 'No support email found', 6 ); 
				elseif(is_array($support_mail)) $send_from = $support_mail[0];
				else{
					if(preg_match('/[\,]/', $support_mail)){
						$support_mail  = explode(',', $support_mail);
						$send_from = $support_mail[0];
					} else $send_from = $support_mail;
				}

				$headers = "From: \"".str_replace(array(','), array(''), get_bloginfo('name'))."\" <".$send_from.">\n";

				if(!$attachment && function_exists('easyreservations_insert_attachment')) $attachment = easyreservations_insert_attachment($this, str_replace('reservations_email_', '', $options_name));
				if(!$to || empty($to)){
					$to = $support_mail;
					$headers = "From: \"".$this->name."\" <".$this->email.">\n";
				}

				$mail = @wp_mail($to,$subj,$msg,$headers, $attachment);
				
				if(isset($oldlocal)) setlocale(LC_TIME, $oldlocal);
				if($attachment) unlink($attachment);	
				return $mail;
			}
		}

		private function generateChangelog(){
			easyreservations_load_resources();
			global $the_rooms_array;
			$beforeArray = $this->save;
			unset($this->save);
			$afterArray = (array) $this;
			$changelog = '';

			if($beforeArray['arrival'] != $afterArray['arrival']) $changelog .= __('The arrival date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT, $beforeArray['arrival']).' => '.date(RESERVATIONS_DATE_FORMAT, $afterArray['arrival']).'<br>';
			if($beforeArray['departure'] != $afterArray['departure']) $changelog .= __('The departure date was edited' , 'easyReservations' ).': '.date(RESERVATIONS_DATE_FORMAT_SHOW, $beforeArray['departure']).' => '.date(RESERVATIONS_DATE_FORMAT_SHOW, $afterArray['departure']).'<br>';
			if($beforeArray['name'] != $afterArray['name']) $changelog .= __('The name was edited' , 'easyReservations' ).': '.$beforeArray['name'].' => '.$afterArray['name'].'<br>';
			if($beforeArray['email'] != $afterArray['email']) $changelog .= __('The email was edited' , 'easyReservations' ).': '.$beforeArray['email'].' => '.$afterArray['email'].'<br>';
			if($beforeArray['adults'] != $afterArray['adults']) $changelog .= __('The amount of adults was edited' , 'easyReservations' ).': '.$beforeArray['adults'].' => '.$afterArray['adults'].'<br>';
			if($beforeArray['childs'] != $afterArray['childs']) $changelog .= __('The amount of childs was edited' , 'easyReservations' ).': '.$beforeArray['childs'].' => '.$afterArray['childs'].'<br>';
			if($beforeArray['country'] != $afterArray['country']) $changelog .= __('The country was edited' , 'easyReservations' ).': '.$beforeArray['country'].' => '.$afterArray['country'].'<br>';
			if($beforeArray['resource'] != $afterArray['resource']) $changelog .= __('The resource was edited' , 'easyReservations' ).': '.__($the_rooms_array[$beforeArray['resource']]->post_title).' => '.__($the_rooms_array[$afterArray['resource']]->post_title).'<br>';
			if($beforeArray['custom'] != $afterArray['custom']) $changelog .= __('Custom fields got edited', 'easyReservations' ).'<br>';
			if(isset($beforeArray['prices']) && $beforeArray['prices'] != $afterArray['prices']) $changelog .= __('Prices got edited' , 'easyReservations' ).'<br>';

			return $this->changelog = $changelog;
		}

		public function getStatus($color = false){
			$statuse = array('yes' => array(__('approved', 'easyReservations' ), '#1FB512'), 'no' => array(__('rejected', 'easyReservations' ), '#D61111'), 'del' => array(__('trashed', 'easyReservations' ), '#870A0A'), '' => array(__('pending', 'easyReservations' ), '#3BB0E2'));

			$formated_status = $statuse[$this->status][0];
			if($color) $formated_status = '<b style="color:'.$statuse[$this->status][1].';text-transform:capitalize">'.$formated_status.'</b>';
			$formated_status = apply_filters('easy-status-out', $formated_status, $this);

			return $formated_status;
		}

		//----------------------------------------- Save -------------------------------------------------//

		/**
		 * Call to edit reservation
		 * @param array $informations informations to edit
		 * @param bool $validate false to not validate reservation
		 * @param bool/string $mail (optional) name of email's option
		 * @return bool true on success
		 */
		public function editReservation($informations = array('all'), $validate = true, $mail = false, $to = false){
			if(is_array($informations) && !empty($informations)){
				$array = '';
				if($informations[0] == 'all') $informations = array('arrival', 'name', 'email', 'departure', 'resource', 'resourcenumber', 'adults', 'childs', 'country', 'status', 'custom', 'prices', 'reservated', 'user', 'pricepaid');
				foreach($informations as $information){
					if(isset($this->$information)) $array[$information] = $this->$information;
				}
				if($this->admin && $this->status != 'yes') $theval = false;
				else $theval = $this->Validate('send', 0);
				if(!$validate || !$theval){
					$edit = $this->edit($this->ReservationToArray($array));
					if($mail && !$edit){
						if(!is_array($mail)) $mail = array($mail); 
						if(!is_array($to)) $to = array($to);
						//$this->generateChangelog();
						foreach($mail as $key => $themail) if($to[$key] !== true)  $this->sendMail($mail[$key], $to[$key]);
					}
					return $edit;
				} else return $theval;
			}
		}

		/**
		 * Call to add reservation
		 * @param mixed $mail (optional) name of email's option
		 * @return int ID of new reservation
		 */
		public function addReservation($mail = false, $to = false){
			if($this->admin && $this->status != 'yes') $validate = false;
			else $validate = $this->Validate('send', 0);
			if(!$validate){
				$array = array();
				foreach($this as $key => $information){
					$array[$key] = $information;
				}
				$add = $this->add($this->ReservationToArray($array));
				if($mail && !$add){
					if(!is_array($mail)) $mail = array($mail);
					if(!is_array($to)) $to = array($to);
					foreach($mail as $key => $themail) if($to[$key] !== true) $this->sendMail($mail[$key], $to[$key]);
				}
				return $add;
			} else return $validate;
		}

		/**
		 *	Objects informations array to database array
		 * @param array $array the array to edit
		 */
		private function ReservationToArray($array){
			if(isset($array['custom']) && is_array($array['custom'])) $array['custom'] = maybe_serialize($array['custom']);
			if(isset($array['prices']) && is_array($array['prices'])) $array['prices'] = maybe_serialize($array['prices']);
			if(isset($array['resource'])) $array['room'] = (int) $array['resource'];
			if(isset($array['resourcenumber'])) $array['roomnumber'] = $array['resourcenumber'];
			if(isset($array['status'])) $array['approve'] = $array['status'];
			if(isset($array['prices'])) $array['customp'] = $array['prices'];
			if(isset($array['custom'])) $array['custom'] = $array['custom'];
			if(isset($array['adults'])) $array['number'] = $array['adults'];
			if(isset($array['reservated'])) $array['reservated'] = date('Y-m-d H:i:s', $array['reservated']);
			if(isset($array['pricepaid'])) $array['price'] = $array['pricepaid'];
			if(isset($array['arrival'])) $array['arrival'] = date('Y-m-d H:i:s', $array['arrival']);
			if(isset($array['departure'])) $array['departure'] = date('Y-m-d H:i:s', $array['departure']);
			if(!isset($array['price'])) $array['price'] = '';
			unset($array['resource'], $array['resourcenumber'], $array['status'], $array['prices'], $array['adults'], $array['pricepaid']);
			return $array;
		}

		/**
		 * Edit reservation
		 * 
		 * @global obj $wpdb database connection
		 * @return bool true on succes
		 * @throws easyException mysql error
		 */
		private function edit($array){
			if(!empty($array)){
				global $wpdb;
				$sql = '';
				foreach($array as $key => $info){
					$sql[$key] = $info;
				}
				$return = $wpdb->update( $wpdb->prefix.'reservations', $sql, array('id' => $this->id));
				if($return === 0){
					throw new easyException( 'No changes');
					return true;
				} elseif(!$return){
					throw new easyException( 'Reservation couldn\'t be edited. Error: '.mysql_error(), mysql_errno() );
					return true;
				} else return false;
			}
		}

		/**
		 * Add reservation
		 *
		 * @global obj $wpdb database connection
		 * @return int ID of new reservation
		 * @throws easyException mysql error
		 */
		private function add($array){
			global $wpdb;
			$informations = array('arrival', 'name', 'email', 'departure', 'room', 'roomnumber', 'number', 'childs', 'country', 'approve', 'custom', 'customp', 'reservated', 'user', 'price');
			$rarray = '';
			foreach($array as $key => $info){
				if(!in_array($key, $informations)) unset($array[$key]);
				else {
					$rarray[$key] = $info;
				}
			}
			if(!isset($rarray['price'])) $rarray['price'] = '';
			$return = $wpdb->insert( $wpdb->prefix.'reservations', $rarray);
			if(!$return){
				throw new easyException( 'Reservation couldn\'t be added. Error: '.mysql_error(), mysql_errno() );
				return true;
			} else {
				$this->id = $wpdb->insert_id;
				if(!$this->admin) do_action('easy-add-res', $this, 1);
				return false;
			}
		}

		public function destroy(){
			unset($this);
		}

		public function deleteReservation(){
			global $wpdb;
			$return =$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE id='%d'", $this->id) );
			if(!$return){
				throw new easyException( 'Reservation couldn\'t be deleted. Error: '.mysql_error(), mysql_errno() );
				return true;
			} else return $this->id = mysql_insert_id();
		}
	}

	class easyException extends Exception {}
?>