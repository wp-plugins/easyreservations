<?php
	/**
	* 	@functions for admin and frontend 
	*/

	/**
	* 	Hook languages to admin & frontend 
	*/


	/**
	* 	Hook on adminbar, add link to admin-panel and count of pending reservations
	*/

	function easyreservations_admin_bar() {
		global $wp_admin_bar, $wpdb;

		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) as Num FROM ".$wpdb->prefix ."reservations WHERE approve=''"));

		if($count!=0) $c="<span id=\"ab-awaiting-mod\" class=\"pending-count\">".$count."</span>"; else $c ="";
		$wp_admin_bar->add_menu( array(
			'id' => 'reservations',
			'title' => __('Reservations '.$c.''),
			'href' => admin_url( 'admin.php?page=reservations&typ=pending')
		) );
	}

	add_action( 'wp_before_admin_bar_render', 'easyreservations_admin_bar' );

	/**
	*	Formats string into money
	*
	*	$amount = money
	*	$mode = 1 for currency sign
	*/

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
	*	Calculate price
	*
	*	$id = reservations id
	*	$newRes = array of reservations information to fake a new reservation
	*	$exact = only for exstensive calculation testing
	*/

	function easyreservations_price_calculation($id, $newRes, $exact=""){ //This is for calculate price just from the reservation ID
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
		/*

			Get Filters From Offer or from Room if Offer = 0

		*/
		if($res[0]->special=="0" OR $res[0]->special==""){ 
			preg_match_all("/[\[](.*?)[\]]/", get_post_meta($res[0]->room, 'reservations_filter', true), $getfilters); $roomoroffer=$res[0]->room; $roomoroffertext=__( 'Room' , 'easyReservations' );
		} else { 
			preg_match_all("/[\[](.*?)[\]]/", get_post_meta($res[0]->special, 'reservations_filter', true), $getfilters); $roomoroffer=$res[0]->special; $roomoroffertext=__( 'Offer' , 'easyReservations' );
		}

		$filterouts=array_filter($getfilters[1]); //make array out of filters
		$countfilter=count($filterouts);// count the filter-array elements

		$roomsoroffersgp = get_post_meta($roomoroffer, 'reservations_groundprice', true);
		$roomsgp = get_post_meta($res[0]->room, 'reservations_groundprice', true);
		$datearray[]='';

		/*

			Sort Price Filters by priorities if no priority was set

		*/
		$arrivalDateRes = strtotime($res[0]->arrivalDate);
		
		foreach($filterouts as $filterout){ //foreach filter array
			$filtertype=explode(" ", $filterout);
			if(!preg_match('/(loyal|stay|pers|avail|early)/i', $filtertype[0]) AND !preg_match("/^[0-9]$/", $filtertype[1])){
				if(preg_match('/(january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|septembre|sep|octobre|oct|novembre|nov|decembre|dec)/', $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 4 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match('/(week|weekdays|weekend|moneday|mon|tuesday|tue|wednesday|wed|thursday|thu|friday|fri|saturday|sat|sunday|sun)/', $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 2 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match("/(([0-9]{4}[\;])+|^[0-9]{4}$)/", $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 6 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match("/(([0-9]{1,2}[\;])+|^[0-9]{1,2}$)/", $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 3 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match('/(q1|quarter1|q2|quarter2|q3|quarter3|q4|quarter4)/', $filtertype[1])){
					 $filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 5 '.$filtertype[1].' ', $filterouts);
				} elseif(preg_match("/[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}[\-][\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}/", $filtertype[1]) OR preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[1])){
					$filterouts = preg_replace("/\s".$filtertype[1]."\s/", ' 1 '.$filtertype[1].' ', $filterouts);
				}
			}
		}

		/*

			Apply Filters

		*/
		asort($filterouts); //sort left filters for any not "date-range" price fields
		$countleftfilters=0;
		foreach($filterouts as $filterout){ //foreach filter array
			$numberoffilter++;
			$filtertype=explode(" ", $filterout);
			if(!preg_match('/(loyal|stay|pers|avail|early)/i', $filtertype[0])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition
				if(preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}[\-][\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[2])){ // If Price filter with dd.mm.yyyy-dd.mm.yyyy Condition
					$explodedates=explode("-", $filtertype[2]);
					$arivaldattes=$arrivalDateRes; 
					if($arrivalDateRes < strtotime($explodedates[1]) && $arrivalDateRes+($res[0]->nights*86400) > strtotime($explodedates[0])){ // I felt very smart here :D
						if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filtertype[3])){
							$specialexplodes=explode("-", $filtertype[3]);
							foreach($specialexplodes as $specialexplode){
								$priceroomexplode=explode(":", $specialexplode);
								if($priceroomexplode[0]==$res[0]->room){
									for($count = 1; $count <= $res[0]->nights; $count++){
										if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1]) AND !in_array($arivaldattes, $datearray) AND !in_array($arivaldattes, $datearray)){
											$price+=$priceroomexplode[1]; $countpriceadd++;
											if($exact == 1) $exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
											$datearray[]=$arivaldattes;
										}
										$arivaldattes+=86400;
									}
								}
							}
						}
						elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filtertype[3])){ //If Filter Value is XX
							for($count = 1; $count <= $res[0]->nights; $count++){
								if($arivaldattes >= strtotime($explodedates[0]) AND $arivaldattes <= strtotime($explodedates[1]) AND !in_array($arivaldattes, $datearray) AND !in_array($arivaldattes, $datearray)){
									$price+=$filtertype[3]; $countpriceadd++;
									if($exact == 1) $exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
									$datearray[]=$arivaldattes;
								}
								$arivaldattes+=86400;
							}
						}
					}
				} elseif(preg_match("/^[\d]{2}+[\.]+[\d]{2}+[\.]+[\d]{4}$/", $filtertype[2])){ // If Price filter with dd.mm.yyyy Condition
					$arivaldattes=$arrivalDateRes;
					for($count = 1; $count <= $res[0]->nights; $count++){
						if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filtertype[3])){
							$specialexplodes=explode("-", $filtertype[3]);
							foreach($specialexplodes as $specialexplode){
								$priceroomexplode=explode(":", $specialexplode);
								if($priceroomexplode[0]==$res[0]->room){
									if(date("d.m.Y", $arivaldattes) == date("d.m.Y", strtotime($filtertype[2])) AND !in_array($arivaldattes, $datearray) AND !in_array($arivaldattes, $datearray)){
										$price+=$priceroomexplode[1]; $countpriceadd++;
										if($exact == 1) $exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
										$datearray[]=$arivaldattes;
									}
									$arivaldattes+=86400;
								}
							}
						} elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filtertype[3])){ //If Filter Value is XX
							if(date("d.m.Y", $arivaldattes) == date("d.m.Y", strtotime($filtertype[2])) AND !in_array($arivaldattes, $datearray)){
								$price+=$filtertype[3]; $countpriceadd++;
								if($exact == 1) $exactlyprice[] = array('date'=>$arivaldattes, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ));
								$datearray[]=$arivaldattes;
							}
							$arivaldattes+=86400;
						}
					}
				} else {
					if(preg_match("/^[a-zA-Z]+$/", $filtertype[2]) OR preg_match("/^[0-9]{2,4}$/", $filtertype[2])){
						$conditionarrays[]=$filtertype[2];
					} else {
						$explodedaynames=explode(";", $filtertype[2]);
						foreach($explodedaynames as $explodedayname){
							if($explodedayname != ''){
								$conditionarrays[]=$explodedayname;
							}
						}
					}

					foreach($conditionarrays as $condition){
						$arivaldaae=$arrivalDateRes;
						for($count = 1; $count <= $res[0]->nights; $count++){
							$derderder=0;

							if(!in_array($arivaldaae, $datearray)){
								if(preg_match('/(week|weekdays|weekend|moneday|mon|tuesday|tue|wednesday|wed|thursday|thu|friday|fri|saturday|sat|sunday|sun)/', $condition)){
									if($condition == 'week' OR $condition == 'weekdays'){
										if((date("D", $arivaldaae) == "Mon" OR date("D", $arivaldaae) == "Tue" OR date("D", $arivaldaae) == "Wed" OR date("D", $arivaldaae) == "Thu" OR date("D", $arivaldaae) == "Sun")){
											$derderder=1;
											$daystring='Weekdays';
										}
									} elseif($condition == 'weekend'){
										if(date("D", $arivaldaae) == "Sat" OR date("D", $arivaldaae) == "Fri"){
											$derderder=1;
											$daystring='Weekend';
										}
									} elseif(($condition == 'monday' OR $condition == 'mon')){
										if(date("D", $arivaldaae) == "Mon"){
											$derderder=1;
											$daystring='Monday';
										}
									} elseif(($condition == 'tuesday' OR $condition == 'tue')){
										if(date("D", $arivaldaae) == "Tue"){
											$derderder=1;
											$daystring='Tuesday';
										}
									} elseif(($condition == 'wednesday' OR $condition == 'wed')){
										if(date("D", $arivaldaae) == "Wed"){
											$derderder=1;
											$daystring='Wednesday';
										}
									} elseif(($condition == 'thursday' OR $condition == 'thu')){
										if(date("D", $arivaldaae) == "Thu"){
											$derderder=1;
											$daystring='Thursday';
										}
									} elseif(($condition == 'friday' OR $condition == 'fri')){
										if(date("D", $arivaldaae) == "Fri"){
											$derderder=1;
											$daystring='Friday';
										}
									} elseif(($condition == 'saturday' OR $condition == 'sat')){
										if(date("D", $arivaldaae) == "Sat"){
											$derderder=1;
											$daystring='Saturday';
										}
									} elseif(($condition == 'sunday' OR $condition == 'sun')){
										if(date("D", $arivaldaae) == "Sun"){
											$derderder=1;
											$daystring='Sunday';
										}
									}
								}  elseif(preg_match("/(([0-9]{1,2}[\;])+|^[0-9]{1,2}$)/", $condition)){
									if(date("W", $arivaldaae) == $condition){ 
										$derderder=1;
										$daystring='Calendar Week';
									}
								} elseif(preg_match('/(january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|septembre|sep|octobre|oct|novembre|nov|decembre|dec)/', $condition)){
									if(($condition == 'january' OR $condition == 'jan')){
										if(date("m", $arivaldaae) == "01"){
											$derderder=1;
											$daystring='January';
										}
									} elseif(($condition == 'february' OR $condition == 'feb')){
										if(date("m", $arivaldaae) == "02"){
											$derderder=1;
											$daystring='February';
										}
									} elseif(($condition == 'march' OR $condition == 'mar')){
										if(date("m", $arivaldaae) == "03"){
											$derderder=1;
											$daystring='March';
										}
									} elseif(($condition == 'april' OR $condition == 'apr')){
										if(date("m", $arivaldaae) == "04"){
											$derderder=1;
											$daystring='April';
										}
									} elseif(($condition == 'may' OR $condition == 'May')){
										if(date("m", $arivaldaae) == "05"){
											$derderder=1;
											$daystring='May';
										}
									} elseif(($condition == 'june' OR $condition == 'jun')){
										if(date("m", $arivaldaae) == "06"){
											$derderder=1;
											$daystring='June';
										}
									} elseif(($condition == 'july' OR $condition == 'jul')){
										if(date("m", $arivaldaae) == "07"){
											$derderder=1;
											$daystring='July';
										}
									} elseif(($condition == 'august' OR $condition == 'aug')){
										if(date("m", $arivaldaae) == "08"){
											$derderder=1;
											$daystring='August';
										}
									} elseif(($condition == 'september' OR $condition == 'sep')){
										if(date("m", $arivaldaae) == "09"){
											$derderder=1;
											$daystring='September';
										}
									} elseif(($condition == 'october' OR $condition == 'oct')){
										if(date("m", $arivaldaae) == "10"){
											$derderder=1;
											$daystring='October';
										}
									} elseif(($condition == 'november' OR $condition == 'nov')){
										if(date("m", $arivaldaae) == "11"){
											$derderder=1;
											$daystring='November';
										}
									} elseif(($condition == 'december' OR $condition == 'dec')){
										if(date("m", $arivaldaae) == "12"){
											$derderder=1;
											$daystring='December';
										}
									}
								} elseif(preg_match('/(q1|quarter1|q2|quarter2|q3|quarter3|q4|quarter4)/', $condition)){
									if($condition == 'q1' OR $condition == 'quarter1'){
										if(ceil(date("m", $arivaldaae) / 3) == 1){
											$derderder=1;
											$daystring='1. Quartar';
										}
									} elseif(($condition == 'q2' OR $condition == 'quarter2')){
										if(ceil(date("m", $arivaldaae) / 3) == 2){
											$derderder=1;
											$daystring='2. Quartar';
										}
									} elseif($condition == 'q3' OR $condition == 'quarter3'){
										if(ceil(date("m", $arivaldaae) / 3) == 3){
											$derderder=1;
											$daystring='3. Quartar';
										}
									} elseif($condition == 'q4' OR $condition == 'quarter4'){
										if(ceil(date("m", $arivaldaae) / 3) == 4){
											$derderder=1;
											$daystring='4. Quartar';
										}
									}
								} elseif(preg_match("/(([0-9]{4}[\;])+|^[0-9]{4}$)/", $condition)){
									if(date("Y", $arivaldaae) == $condition){
										$derderder=1;
										$daystring='Year';
									}
								}

								if($derderder==1){
									if(preg_match("/[0-9]+[\:][0-9]+[\.]?[0-9]*/", $filtertype[3])){
										$specialexplodes=explode("-", $filtertype[3]);
										foreach($specialexplodes as $specialexplode){
											$priceroomexplode=explode(":", $specialexplode);
											if($priceroomexplode[0]==$res[0]->room){
												$price+=$priceroomexplode[1]; $countpriceadd++;
												if($exact == 1) $exactlyprice[] = array('date'=>$arivaldaae, 'priceday'=>$priceroomexplode[1], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.$daystring);
												$datearray[]=$arivaldaae;
											}
										}
									} elseif(preg_match("/^[0-9]+[\.]?[0-9]*$/", $filtertype[3])){ //If Filter Value is XX
										$price+=$filtertype[3]; $countpriceadd++; 
										if($exact == 1) $exactlyprice[] = array('date'=>$arivaldaae, 'priceday'=>$filtertype[3], 'type'=>get_the_title($roomoroffer).' '.__( ' Price Filter' , 'easyReservations' ).' '.$daystring);
										$datearray[]=$arivaldaae;
									}
								}
							}
							$arivaldaae += 86400;
						}
					}
				}
				unset($filterouts[$countleftfilters]); //Remove Filter from Filter array to speed up later foreach
				$conditionarrays= '';
			}
			$countleftfilters++;
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

		if(count($filterouts) > 0){  //IF Filter array has elemts left they should be Discount Filters or nonsense
			$numberoffilter++;
			$staywasfull=0; $loyalwasfull=0; $perswasfull=0; $earlywasfull=0;
			arsort($filterouts); // Sort rest of array with high to low

			foreach($filterouts as $filterout){
			$filtertype=explode(" ", $filterout);

				if($filtertype[0]=="stay"){// Stay Filter
					if($staywasfull==0){
						if($filtertype[1] <= $res[0]->nights){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent; 
								if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$percent, 'type'=>get_the_title($roomoroffer).' '.__( ' Stay filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								if($exact == 1) $exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Stay filter' , 'easyReservations' ));
							}
						$staywasfull++;
						}
					}
				}

				elseif($filtertype[0]=="loyal"){// Loyal Filter
					if($loyalwasfull==0){
						$items1 = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='".$res[0]->email."' AND arrivalDate + INTERVAL 1 DAY < NOW()")); //number of total rows in the database
						if($filtertype[1] <= $items1){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent;
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$percent, 'type'=>get_the_title($roomoroffer).' '.__( ' Loyal filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Loyal filter' , 'easyReservations' ));
							}
						$loyalwasfull++;
						}
					}
				}

				elseif($filtertype[0]=="pers"){// Persons Filter
					if($perswasfull==0){
						if($filtertype[1] <= $res[0]->number){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent;
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$discount, 'type'=>get_the_title($roomoroffer).' '.__( ' Persons filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Persons filter' , 'easyReservations' ));
							}
						$perswasfull++;
						}
					}
				}
				
				elseif($filtertype[0]=="early"){// Early Bird Discount Filter
					if($earlywasfull==0){
						$dayBetween=round(($arrivalDateRes/86400)-(strtotime($res[0]->reservated)/86400)); // cals days between booking and arrival
						if($filtertype[1] <= $dayBetween){
							if(substr($filtertype[2], -1) == "%"){
								$percent=$price/100*str_replace("%", "", $filtertype[2]);
								$discount+=$percent;
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$discount, 'type'=>get_the_title($roomoroffer).' '.__( ' Early Bird filter' , 'easyReservations' ).' '.$filtertype[2]);
							} else {
								$discount+=$filtertype[2];
								$exactlyprice[] = array('date'=>$arrivalDateRes+($countpriceadd*86400), 'priceday'=>'-'.$filtertype[2], 'type'=>get_the_title($roomoroffer).' '.__( ' Early Bird filter' , 'easyReservations' ));
							}
						$earlywasfull++;
						}
					}
				}
			}
		}

		$price-=$discount; //Price minus Discount

		$price=str_replace(".", ",", $price);
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

	function easyreservations_check_avail($resourceID, $date, $exactly=0, $nights=0, $offer=0, $mode=0, $id=0){
		global $wpdb;
		$error=null;
		
		if($offer > 0) $error .= reservations_check_avail_filter($offer, $date, $nights, $mode);
		$date_format = date("Y-m-d", $date);
		$roomcount = get_post_meta($resourceID, 'roomcount', true);
		if($id > 0) $idsql = "id!='".$id."' AND";
		else  $idsql = '';

		if($resourceID > 0){
			$error .= reservations_check_avail_filter($resourceID, $date, $nights, $mode);

			if($nights > 0){
				if($exactly > 0){
					for($i = 0; $i < $nights; $i++){
						$date_format=date("Y-m-d", $date+($i*86400));
						$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix."reservations WHERE approve='yes' AND room='$resourceID' AND roomnumber='$exactly' AND $idsql '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"));
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
					$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql roomnumber='$exactly' AND '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - Interval 1 DAY "));
					if($mode==1 && $count > $roomcount) $error .= date("d.m.Y", $date).', ';
					elseif($mode==0)  $error += $count;
				} else {
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$resourceID' AND $idsql '$date_format' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY");
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

	function reservations_check_avail_filter($resourceID, $date, $times=0, $mode=0){ //Check if a Room or Offer is Avail or Full
		$filters = get_post_meta($resourceID, 'reservations_filter', true);
		if($mode == 0) $roomcount=get_post_meta($resourceID, 'roomcount', true);

		$error = '';

		if(!empty($filters)){
			preg_match_all("/[\[](.*?)[\]]/", $filters, $filters_values);
			$filters_array_ =array_values(array_filter($filters_values)); //make array out of filters
			$filters_array_clean = $filters_array_[1];

			foreach($filters_array_clean as $filter){
				$filtertype=explode(" ", $filter);
				if($filtertype[0]=='avail'){
					$explodedates=explode("-", $filtertype[1]);
					if($times > 0){
						for($i=0; $times > $i; $i++){
							$datet = $date+($i*86400);
							if($datet >= strtotime($explodedates[0]) && $datet <= strtotime($explodedates[1])){
								if($mode == 1) $error .= date("d.m.Y", $datet).', ';
								else $error += $roomcount;
							}
						}
					} else {
						if($date >= strtotime($explodedates[0]) && $date <= strtotime($explodedates[1])){
							if($mode == 1) $error .= date("d.m.Y", $date).', ';
							else $error += $roomcount;
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
	*/

	function reservations_check_pay_status($id){
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

		return $ispayed;
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

	function easyreservations_get_rooms($content=0){
		global $wpdb;
		if($content == 1) $con = ", cat_posts.post_content"; else $con = "";
		$room_category =get_option("reservations_room_category");

		$rooms = $wpdb->get_results("SELECT cat_posts.ID, cat_posts.post_title $con
		FROM wp_term_taxonomy AS cat_term_taxonomy 
		INNER JOIN wp_terms AS cat_terms ON cat_term_taxonomy.term_id = cat_terms.term_id 
		INNER JOIN wp_term_relationships AS cat_term_relationships ON cat_term_taxonomy.term_taxonomy_id = cat_term_relationships.term_taxonomy_id 
		INNER JOIN wp_posts AS cat_posts ON cat_term_relationships.object_id = cat_posts.ID 
		WHERE (cat_posts.post_status = 'publish' OR cat_posts.post_status = 'private') AND cat_posts.post_type = 'post' AND cat_term_taxonomy.taxonomy = 'category' AND cat_terms.term_id = '$room_category'");

		return $rooms;
	}

	function reservations_get_room_options($selected=''){
		$roomcategories = easyreservations_get_rooms();
		$rooms_options='';
		foreach( $roomcategories as $roomcategorie ){
			if(isset($selected) AND !empty($selected) AND $selected == $roomcategorie->ID) $select = ' selected="selected"'; else $select = "";
			$rooms_options .= '<option value="'.$roomcategorie->ID.'"'.$select.'>'.__($roomcategorie->post_title).'</option>';
		}
		return $rooms_options;
	}

	function easyreservations_get_offers($conent=0){
		global $wpdb;
		if($content == 1) $con = ", cat_posts.post_content"; else $con = "";
		$offer_category =get_option("reservations_special_offer_cat");

		$offers = $wpdb->get_results("SELECT cat_posts.ID, cat_posts.post_title $con
		FROM wp_term_taxonomy AS cat_term_taxonomy 
		INNER JOIN wp_terms AS cat_terms ON cat_term_taxonomy.term_id = cat_terms.term_id 
		INNER JOIN wp_term_relationships AS cat_term_relationships ON cat_term_taxonomy.term_taxonomy_id = cat_term_relationships.term_taxonomy_id 
		INNER JOIN wp_posts AS cat_posts ON cat_term_relationships.object_id = cat_posts.ID 
		WHERE (cat_posts.post_status = 'publish' OR cat_posts.post_status = 'private') AND cat_posts.post_type = 'post' AND cat_term_taxonomy.taxonomy = 'category' AND cat_terms.term_id = '$offer_category'");

		return $offers;
	}

	function reservations_get_offer_options($selected=''){
		$offercategories = easyreservations_get_offers();
		$offer_options='';
		foreach( $offercategories as $offercategorie ){
			if(isset($selected) AND !empty($selected) AND $selected == $offercategorie->ID) $select = ' selected="selected"'; $select = "";
			$offer_options .= '<option value="'.$offercategorie->ID.'"'.$select.'>'.__($offercategorie->post_title).'</option>';
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

		return array(
			'AF'=>'Afghanistan',
			'AL'=>'Albania',
			'DZ'=>'Algeria',
			'AS'=>'American Samoa',
			'AD'=>'Andorra',
			'AO'=>'Angola',
			'AI'=>'Anguilla',
			'AQ'=>'Antarctica',
			'AG'=>'Antigua And Barbuda',
			'AR'=>'Argentina',
			'AM'=>'Armenia',
			'AW'=>'Aruba',
			'AU'=>'Australia',
			'AT'=>'Austria',
			'AZ'=>'Azerbaijan',
			'BS'=>'Bahamas',
			'BH'=>'Bahrain',
			'BD'=>'Bangladesh',
			'BB'=>'Barbados',
			'BY'=>'Belarus',
			'BE'=>'Belgium',
			'BZ'=>'Belize',
			'BJ'=>'Benin',
			'BM'=>'Bermuda',
			'BT'=>'Bhutan',
			'BO'=>'Bolivia',
			'BA'=>'Bosnia And Herzegovina',
			'BW'=>'Botswana',
			'BV'=>'Bouvet Island',
			'BR'=>'Brazil',
			'IO'=>'British Indian Ocean Territory',
			'BN'=>'Brunei',
			'BG'=>'Bulgaria',
			'BF'=>'Burkina Faso',
			'BI'=>'Burundi',
			'KH'=>'Cambodia',
			'CM'=>'Cameroon',
			'CA'=>'Canada',
			'CV'=>'Cape Verde',
			'KY'=>'Cayman Islands',
			'CF'=>'Central African Republic',
			'TD'=>'Chad',
			'CL'=>'Chile',
			'CN'=>'China',
			'CX'=>'Christmas Island',
			'CC'=>'Cocos (Keeling) Islands',
			'CO'=>'Columbia',
			'KM'=>'Comoros',
			'CG'=>'Congo',
			'CK'=>'Cook Islands',
			'CR'=>'Costa Rica',
			'CI'=>'Cote D\'Ivorie (Ivory Coast)',
			'HR'=>'Croatia (Hrvatska)',
			'CU'=>'Cuba',
			'CY'=>'Cyprus',
			'CZ'=>'Czech Republic',
			'CD'=>'Democratic Republic Of Congo (Zaire)',
			'DK'=>'Denmark',
			'DJ'=>'Djibouti',
			'DM'=>'Dominica',
			'DO'=>'Dominican Republic',
			'TP'=>'East Timor',
			'EC'=>'Ecuador',
			'EG'=>'Egypt',
			'SV'=>'El Salvador',
			'GQ'=>'Equatorial Guinea',
			'ER'=>'Eritrea',
			'EE'=>'Estonia',
			'ET'=>'Ethiopia',
			'FK'=>'Falkland Islands (Malvinas)',
			'FO'=>'Faroe Islands',
			'FJ'=>'Fiji',
			'FI'=>'Finland',
			'FR'=>'France',
			'FX'=>'France, Metropolitan',
			'GF'=>'French Guinea',
			'PF'=>'French Polynesia',
			'TF'=>'French Southern Territories',
			'GA'=>'Gabon',
			'GM'=>'Gambia',
			'GE'=>'Georgia',
			'DE'=>'Germany',
			'GH'=>'Ghana',
			'GI'=>'Gibraltar',
			'GR'=>'Greece',
			'GL'=>'Greenland',
			'GD'=>'Grenada',
			'GP'=>'Guadeloupe',
			'GU'=>'Guam',
			'GT'=>'Guatemala',
			'GN'=>'Guinea',
			'GW'=>'Guinea-Bissau',
			'GY'=>'Guyana',
			'HT'=>'Haiti',
			'HM'=>'Heard And McDonald Islands',
			'HN'=>'Honduras',
			'HK'=>'Hong Kong',
			'HU'=>'Hungary',
			'IS'=>'Iceland',
			'IN'=>'India',
			'ID'=>'Indonesia',
			'IR'=>'Iran',
			'IQ'=>'Iraq',
			'IE'=>'Ireland',
			'IL'=>'Israel',
			'IT'=>'Italy',
			'JM'=>'Jamaica',
			'JP'=>'Japan',
			'JO'=>'Jordan',
			'KZ'=>'Kazakhstan',
			'KE'=>'Kenya',
			'KI'=>'Kiribati',
			'KW'=>'Kuwait',
			'KG'=>'Kyrgyzstan',
			'LA'=>'Laos',
			'LV'=>'Latvia',
			'LB'=>'Lebanon',
			'LS'=>'Lesotho',
			'LR'=>'Liberia',
			'LY'=>'Libya',
			'LI'=>'Liechtenstein',
			'LT'=>'Lithuania',
			'LU'=>'Luxembourg',
			'MO'=>'Macau',
			'MK'=>'Macedonia',
			'MG'=>'Madagascar',
			'MW'=>'Malawi',
			'MY'=>'Malaysia',
			'MV'=>'Maldives',
			'ML'=>'Mali',
			'MT'=>'Malta',
			'MH'=>'Marshall Islands',
			'MQ'=>'Martinique',
			'MR'=>'Mauritania',
			'MU'=>'Mauritius',
			'YT'=>'Mayotte',
			'MX'=>'Mexico',
			'FM'=>'Micronesia',
			'MD'=>'Moldova',
			'MC'=>'Monaco',
			'MN'=>'Mongolia',
			'MS'=>'Montserrat',
			'MA'=>'Morocco',
			'MZ'=>'Mozambique',
			'MM'=>'Myanmar (Burma)',
			'NA'=>'Namibia',
			'NR'=>'Nauru',
			'NP'=>'Nepal',
			'NL'=>'Netherlands',
			'AN'=>'Netherlands Antilles',
			'NC'=>'New Caledonia',
			'NZ'=>'New Zealand',
			'NI'=>'Nicaragua',
			'NE'=>'Niger',
			'NG'=>'Nigeria',
			'NU'=>'Niue',
			'NF'=>'Norfolk Island',
			'KP'=>'North Korea',
			'MP'=>'Northern Mariana Islands',
			'NO'=>'Norway',
			'OM'=>'Oman',
			'PK'=>'Pakistan',
			'PW'=>'Palau',
			'PA'=>'Panama',
			'PG'=>'Papua New Guinea',
			'PY'=>'Paraguay',
			'PE'=>'Peru',
			'PH'=>'Philippines',
			'PN'=>'Pitcairn',
			'PL'=>'Poland',
			'PT'=>'Portugal',
			'PR'=>'Puerto Rico',
			'QA'=>'Qatar',
			'RE'=>'Reunion',
			'RO'=>'Romania',
			'RU'=>'Russia',
			'RW'=>'Rwanda',
			'SH'=>'Saint Helena',
			'KN'=>'Saint Kitts And Nevis',
			'LC'=>'Saint Lucia',
			'PM'=>'Saint Pierre And Miquelon',
			'VC'=>'Saint Vincent And The Grenadines',
			'SM'=>'San Marino',
			'ST'=>'Sao Tome And Principe',
			'SA'=>'Saudi Arabia',
			'SN'=>'Senegal',
			'SC'=>'Seychelles',
			'SL'=>'Sierra Leone',
			'SG'=>'Singapore',
			'SK'=>'Slovak Republic',
			'SI'=>'Slovenia',
			'SB'=>'Solomon Islands',
			'SO'=>'Somalia',
			'ZA'=>'South Africa',
			'GS'=>'South Georgia And South Sandwich Islands',
			'KR'=>'South Korea',
			'ES'=>'Spain',
			'LK'=>'Sri Lanka',
			'SD'=>'Sudan',
			'SR'=>'Suriname',
			'SJ'=>'Svalbard And Jan Mayen',
			'SZ'=>'Swaziland',
			'SE'=>'Sweden',
			'CH'=>'Switzerland',
			'SY'=>'Syria',
			'TW'=>'Taiwan',
			'TJ'=>'Tajikistan',
			'TZ'=>'Tanzania',
			'TH'=>'Thailand',
			'TG'=>'Togo',
			'TK'=>'Tokelau',
			'TO'=>'Tonga',
			'TT'=>'Trinidad And Tobago',
			'TN'=>'Tunisia',
			'TR'=>'Turkey',
			'TM'=>'Turkmenistan',
			'TC'=>'Turks And Caicos Islands',
			'TV'=>'Tuvalu',
			'UG'=>'Uganda',
			'UA'=>'Ukraine',
			'AE'=>'United Arab Emirates',
			'UK'=>'United Kingdom',
			'US'=>'United States',
			'UM'=>'United States Minor Outlying Islands',
			'UY'=>'Uruguay',
			'UZ'=>'Uzbekistan',
			'VU'=>'Vanuatu',
			'VA'=>'Vatican City (Holy See)',
			'VE'=>'Venezuela',
			'VN'=>'Vietnam',
			'VG'=>'Virgin Islands (British)',
			'VI'=>'Virgin Islands (US)',
			'WF'=>'Wallis And Futuna Islands',
			'EH'=>'Western Sahara',
			'WS'=>'Western Samoa',
			'YE'=>'Yemen',
			'YU'=>'Yugoslavia',
			'ZM'=>'Zambia',
			'ZW'=>'Zimbabwe'
		);
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

		$countryArray = easyReservations_country_array();

		return $countryArray[$country];

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
				$theForm=str_replace('[editlink]', get_option("reservations_edit_url").'?edit&id='.$theID.'&email='.$theEmail.'&nonce='.wp_create_nonce('easy-user-edit-link'), $theForm);
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

?>