<?php
function easyreservation_start_session() {

	@session_cache_limiter('private, must-revalidate'); //private_no_expire
	@session_cache_expire(0);
	@session_start();
}

function reservations_edit_shortcode($atts){

	if (!session_id()) easyreservation_start_session();

	$return = '<link href="'.WP_PLUGIN_URL.'/easyreservations/css/forms/form_none.css" rel="stylesheet" type="text/css"/>';
	$return .= '<script>var dateformat = \''.RESERVATIONS_DATE_FORMAT.'\';if(dateformat == \'Y/m/d\') var dateformate = \'yy/mm/dd\';else if(dateformat == \'m/d/Y\') var dateformate = \'mm/dd/yy\';else if(dateformat == \'Y-m-d\') var dateformate = \'yy-mm-dd\';else if(dateformat == \'d-m-Y\') var dateformate = \'dd-mm-yy\';else if(dateformat == \'d.m.Y\') var dateformate = \'dd.mm.yy\';function easyreservations_build_datepicker(){ jQuery("#easy-form-from, #easy-form-to").datepicker( { dateFormat: dateformate }); } </script>';

	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle');
	
	if(isset($_GET['logout'])){
		session_destroy();
		unset($_SESSION['easy-user-edit-id'] );
		unset($_SESSION['easy-user-edit-email'] );
	}
	$the_link = get_option("reservations_edit_url");
	$edit_options = get_option("reservations_edit_options");

	if(strpos(get_the_content(), '[easy_calendar') !== false){
		$isCalendar = true;
	} else $isCalendar = false;
	
	if(!isset($_SESSION['easy-user-edit-id']) || !isset($_SESSION['easy-user-edit-email']) || (isset($_SESSION['easy-user-edit-id']) && !is_numeric($_SESSION['easy-user-edit-id']))){
		if(isset($_POST['email']) && isset($_POST['editID'])){
			if(!wp_verify_nonce($_POST['easy-user-edit-login'], 'easy-user-edit-login' ) && !wp_verify_nonce($_POST['easy-user-edit'], 'easy-user-edit' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$_SERVER['referer_url'].'">'.__( 'back' , 'easyReservations' ).'</a></div>';
			$theMail = (string) $_POST['email'];
			$theID = (int) $_POST['editID'];
			$_SESSION['easy-user-edit-id'] =  $theID;
			$_SESSION['easy-user-edit-email'] =  $theMail;
			if(isset($_POST['captcha_value'])){
				require_once(dirname(__FILE__).'/lib/captcha/captcha.php');
				$prefix = $_POST['captcha_prefix'];
				$captcha_instance = new ReallySimpleCaptcha();
				$correct = $captcha_instance->check($prefix, $_POST['captcha_value']);
				$captcha_instance->remove($prefix);
				$captcha_instance->cleanup(); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?
				if($correct != 1) return '<div style="text-align:center;">'.__(  'Please enter the correct captcha code' , 'easyReservations' ).' - <a href="'.$_SERVER['referer_url'].'">'.__( 'back' , 'easyReservations' ).'</a></div>';
			}
		} elseif(isset($_GET['email']) && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
			if(!wp_verify_nonce($_GET['ernonce'], 'easyusereditlink' )) return '<div style="text-align:center;">'.__('Link is only 24h valid', 'easyReservations').' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
			$theMail = (string) $_GET['email'];
			$theID = (int) $_GET['id'];
			$_SESSION['easy-user-edit-id'] =  $theID;
			$_SESSION['easy-user-edit-email'] =  $theMail;
		}
	} else {
		$theMail = $_SESSION['easy-user-edit-email'];
		$theID = $_SESSION['easy-user-edit-id'] ;
	}

	//if(wp_create_nonce('easyusereditlink') == $_GET['nonce']) echo $_GET['nonce']; else echo 'a<br>';

	if(isset($_GET['newid'])){
		if(!wp_verify_nonce($_GET['wpnonce'], 'easy-change-id' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
		if(is_numeric($_GET['newid'])){
			$theID = (int) $_GET['newid'];
			$_SESSION['easy-user-edit-id'] = $theID;
		}
	}

	if(isset($theMail) && isset($theID)){
		wp_enqueue_script( 'easyreservations_send_validate' );

		if(isset($atts['price'])){
			wp_enqueue_script( 'easyreservations_send_price' );
			add_action('wp_print_footer_scripts', 'easyreservtions_send_price_script'); //get price directily after loading
		}
		if(!isset($atts['roomname']) || empty($atts['roomname'])) $atts['roomname'] = __('Room', 'easyReservations');
		else $atts['roomname'] = __($atts['roomname']);

		add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');

		global $wpdb;

		if(isset($_POST['thename'])){
			$error = "";
			if (!wp_verify_nonce($_POST['easy-user-edit'], 'easy-user-edit' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$_SERVER['referer_url'].'">'.__( 'back' , 'easyReservations' ).'</a></div>';

			if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
			else $captcha ="";

			if(isset($_POST['thename'])) $name_form=$_POST['thename'];
			else $name_form = "";

			if(isset($_POST['from'])) $from=$_POST['from'];
			else $from = "";

			if(isset($_POST['old_email'])) $old_email=$_POST['old_email'];
			else $old_email = "";
			
			if(isset($_POST['to'])) $to=$_POST['to'];
			else $to = "";

			if(isset($_POST['nights'])) $nights=$_POST['nights'];
			else $nights = "";

			if(isset($_POST['persons'])) $persons=$_POST['persons'];
			else $persons = "";

			if(isset($_POST['email'])) $email=$_POST['email'];
			else $email = "";

			if(isset($_POST['childs'])) $childs=$_POST['childs'];
			else $childs = "";

			if(isset($_POST['country'])) $country=$_POST['country'];
			else $country = "";

			if(isset($_POST['room'])) $room=$_POST['room'];
			else $room = "";

			if(isset($_POST['editMessage'])) $message=$_POST['editMessage'];
			else $message = "";

			if(isset($_POST['offer'])) $offer=$_POST['offer'];
			else $offer = "";

			$customfields="";
			$custompfields="";

			for($theCount = 0; $theCount < 500; $theCount++){
				if(isset($_POST["custom_value_".$theCount]) && isset($_POST["custom_title_".$theCount])){
					$customfields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$theCount], 'title' => $_POST["custom_title_".$theCount], 'value' => $_POST["custom_value_".$theCount]);
				}
			}

			for($theCount = 0; $theCount < 500; $theCount++){
				if(isset($_POST["customPvalue".$theCount]) && isset($_POST["customPtitle".$theCount])){
					$custompfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$theCount], 'title' => $_POST["customPtitle".$theCount], 'value' => $_POST["customPvalue".$theCount], 'amount' => $_POST["customPprice".$theCount]);
				}
			}

			$error .= easyreservations_check_reservation( array( 'thename' => $name_form, 'from' => $from, 'to' => $to, 'nights' => $nights, 'email' => $email, 'persons' => $persons, 'childs' => $childs, 'country' => $country, 'room' => $room, 'message' => $message, 'offer' => $offer, 'custom' => $customfields, 'customp' => $custompfields, 'id' => $theID, 'old_email' => $old_email), 'user-edit');
		}

		if(isset($_POST['thename']) && empty($error)){ //When Check gives no error Insert into Database and send mail
			if(isset($edit_options['submit_text']) && !empty($edit_options['submit_text'])) $submit = $edit_options['submit_text'];
			else $submit = __( 'Your Reservation was edited' , 'easyReservations' );
			$return .= '<div class="easy_form_success">'.$submit.'</div>';
		}

		do_action( 'er_edit_add_action' );

		$edit_querie = $wpdb->get_results( $wpdb->prepare( "SELECT email, name, arrivalDate, nights, number, childs, room, special, approve, country, notes, custom, customp FROM ".$wpdb->prefix ."reservations WHERE id= '%d' AND email = '%s' " , $theID, $theMail )) or $sql_error = 1;

		if(isset($sql_error) && $sql_error == 1){
			session_destroy();
			unset($_SESSION['easy-user-edit-id'] );
			unset($_SESSION['easy-user-edit-email'] );
			return '<div style="text-align:center;">'.__(  'Wrong ID or eMail' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
		}

		if(isset($atts['daysbefore'])) $daysbeforearival = $atts['daysbefore'];
		else $daysbeforearival = 10;
		if(isset($atts['table'])) $other = $atts['table'];
		else $other = 1;
		
		//echo wp_nonce_url( $the_link.'?edit&id='.$theID.'&email='.$theMail, 'easyusereditlink' );

		if(isset($edit_querie[0])){
			$all_rooms = easyreservations_get_rooms(0,0);
			$all_offers = easyreservations_get_offers(0,0);

			if($other == 1){
				if(isset($edit_options['table_status'])) $status_sql = 'AND approve IN(\''.substr(implode("', '", $edit_options['table_status']), 0, -6).')';
				else $status_sql = '';
				if(isset($edit_options['table_time'])){
					$time_sql = '';
					if(in_array('past', $edit_options['table_time'])) $time_sql .= "OR DATE_ADD(arrivalDate, INTERVAL nights DAY) < DATE(NOW()) ";
					if(in_array('current', $edit_options['table_time'])) $time_sql .= "OR DATE(NOW()) BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) ";
					if(in_array('future', $edit_options['table_time'])) $time_sql .= "OR (arrivalDate > DATE(NOW()) AND DATE(NOW()) NOT BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY))";
					if(!empty($time_sql)){
						$time_sql = ' AND ('.substr($time_sql, 2).')';
					}
				}  else $time_sql = '';
				$other_sql = "SELECT id, name ,arrivalDate, nights, number, childs, room, special, approve, reservated FROM ".$wpdb->prefix ."reservations WHERE email='$theMail' $status_sql $time_sql ORDER BY arrivalDate DESC";
				$other_query = $wpdb->get_results( $wpdb->prepare( $other_sql ));

				if(!isset($edit_options['table_more']) || !is_numeric($edit_options['table_more'])) $edit_options['table_more'] = 1;
				if(isset($other_query[$edit_options['table_more']])){
					$in_id = in_array('id', $edit_options['table_infos']);
					$in_date = in_array('date', $edit_options['table_infos']);
					$in_name = in_array('name', $edit_options['table_infos']);
					$in_status = in_array('status', $edit_options['table_infos']);
					$in_persons = in_array('persons', $edit_options['table_infos']);
					$in_room = in_array('room', $edit_options['table_infos']);
					$in_offer = in_array('offer', $edit_options['table_infos']);
					$in_reservated = in_array('reservated', $edit_options['table_infos']);
					$in_price = in_array('price', $edit_options['table_infos']);

					if(isset($edit_options['table_style']) && $edit_options['table_style'] == 1) $class='class="easy-front-table"'; else $class = '';
					$return .= '<table '.$class.'>';
						$return .= '<thead>';
							$return .= '<tr>';
								if($in_id) $return .= '<th>ID</th>';
								if($in_date) $return .= '<th>Date</th>';
								if($in_name) $return .= '<th>Name</th>';
								if($in_status) $return .= '<th class="center">Status</th>';
								if($in_persons) $return .= '<th class="center">Persons</th>';
								if($in_room) $return .= '<th>'.$atts['roomname'].'</th>';
								if($in_offer) $return .= '<th>Offer</th>';
								if($in_reservated) $return .= '<th>Reservated</th>';
								if($in_price) $return .= '<th class="right">'.__( 'Price' , 'easyReservations' ).'</th>';
								$return .= '<th></th>';
							$return .= '</tr>';
						$return .= '</thead>';
						$return .= '<tbody>';
						foreach($other_query as $reservation){
							if($theID == $reservation->id) $class='class="current"'; else $class = '';
							$arrival = strtotime($reservation->arrivalDate);
							$return .= '<tr '.$class.'>';
								if($in_id) $return .= '<td>'.($reservation->id).'</td>';
								if($in_date) $return .= '<td>'.date(RESERVATIONS_DATE_FORMAT, $arrival).' - '.date(RESERVATIONS_DATE_FORMAT, $arrival+($reservation->nights*86400)).'</td>';
								if($in_name) $return .= '<td>'.($reservation->name).'</td>';
								if($in_status) $return .= '<td class="center">'.easyreservations_format_status($reservation->approve,1).'</td>';
								if($in_persons) $return .= '<td class="center">'.($reservation->number+$reservation->childs).'</td>';
								if($in_room) $return .= '<td>'.easyreservations_get_the_title($reservation->room, $all_rooms).'</td>';
								if($in_offer) $return .= '<td>'.easyreservations_get_the_title($reservation->special, $all_offers).'</td>';
								if($in_reservated) $return .= '<td>'.human_time_diff($reservation->reservated, time()).' '.__( 'ago' , 'easyReservations' ).'</td>';
								if($in_price) $return .= '<td class="right" style="white-space:nowrap">'.easyreservations_get_price($reservation->id, 1).'</td>';
								$return .= '<td><a  href="'.$the_link.'?edit&newid='.$reservation->id.'&wpnonce='.wp_create_nonce( 'easy-change-id' ).'"><img src="'.RESERVATIONS_IMAGES_DIR.'/book.png" style="vertical-align:text-bottom"></a></td>';
							$return .= '</tr>';
						}
						$return .= '</tbody>';
					$return .= '</table>';
				}
			}

			$special = $edit_querie[0]->special;
			$country = $edit_querie[0]->country;
			$persons=$edit_querie[0]->number;
			$childs=$edit_querie[0]->childs;
			$approve=$edit_querie[0]->approve;

			if(strtotime($edit_querie[0]->arrivalDate) < time()){
				$resPast = 1;
				$pastError = '<li>'.__( 'Your arrival date is past' , 'easyReservations' ).'</li>';
			} elseif(strtotime($edit_querie[0]->arrivalDate) < time()+(86400*$daysbeforearival)){
				$resPast = 1;
				$pastError = '<li>'.__( 'Please contact us to edit your reservation' , 'easyReservations' ).'</li>';
			} else {
				$resPast = 0;
				$pastError = '';
			}

			$left = reservations_check_pay_status($theID);
			if(function_exists('easyreservations_generate_paypal_button') && $left > 0){
				$paypal = easyreservations_generate_paypal_button($theID, strtotime($edit_querie[0]->arrivalDate), $edit_querie[0]->nights, $edit_querie[0]->room, $special, $edit_querie[0]->email, 0);
			} else {
				$paypal = '';
			}

			if(!empty($edit_options['edit_text'])) $return .= '<div style="margin-left:auto;text-align:center;margin-right:auto;margin: 0px 5px;padding:5px 5px;">'.$edit_options['edit_text'].'</div>';
			$return .= '<div class="easy-edit-status"><b>';
				if(isset($atts['status'])) $return .= __( 'Status' , 'easyReservations' ).': '.ucfirst(reservations_status_output($approve)).'';
				if(isset($atts['price']) && isset($atts['status'])) $return .= ' | ';
				if(isset($atts['price'])) $return .= __( 'Price' , 'easyReservations' ).': '.easyreservations_get_price($theID,1).' | '.__( 'Left' , 'easyReservations' ).': '.reservations_format_money($left, 1);
			$return .= '</b>'.$paypal.' - <a style="color:#ff0000" href="'.$the_link.'?edit&logout">'.__( 'logout' , 'easyReservations' ).'</a></div>';

			$return .= '<form method="post" id="easyFrontendFormular" name="easyFrontendFormular" style="width:99%;margin-left:auto;margin-right:auto;margin-top:10px;">';
			if(function_exists('easyreservations_generate_chat')){
				$return .= easyreservations_generate_chat( $theID, 'edit' );
				 $return .= '<div style="width:60%;">';
			} else $return .= '<div style="width:400px;margin-left:auto;margin-right:auto">';
				if(isset($error)) $pastError = $pastError.$error;
				if(isset($pastError) && !empty($pastError)) $hideclass="";
				else $hideclass=" hide-it";
	
				if(isset($atts['error_title'])) $error_title = $atts['error_title'];
				else $error_title='Errors found in the form';
				if(isset($atts['error_message'])) $error_message = $atts['error_message'];
				else $error_message='There is a problem with the form, please check and correct the following:';

				$return .= '<div id="easy-show-error-div" class="easy-show-error-div'.$hideclass.'"><h2>'.$error_title.'</h2>'.$error_message.'<ul id="easy-show-error">'.$pastError.'</ul></div>';

				$return .= '<input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">';
				$return .= '<input name="editID" id="editID" type="hidden" value="'.$theID.'">';
				$return .= '<input name="old_email" type="hidden" value="'.$edit_querie[0]->email.'">';
				$return .= '<input name="easy-user-edit" type="hidden" value="'.wp_create_nonce('easy-user-edit').'">';
				$return .= '<label>'.__( 'Name' , 'easyReservations' ).'<span class="small">'.__( 'Your name' , 'easyReservations' ).'</span></label><input type="text" name="thename" id="easy-form-thename" onchange="easyreservations_send_validate();" value="'.$edit_querie[0]->name.'">';
				$return .= '<label>'.__( 'eMail' , 'easyReservations' ).'<span class="small">'.__( 'Your email' , 'easyReservations' ).'</span></label><input type="text" name="email" id="easy-form-email" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();" value="'.$edit_querie[0]->email.'">';
				$return .= '<label>'.__( 'From' , 'easyReservations' ).'<span class="small">'.__( 'The arrival date' , 'easyReservations' ).'</span></label><input type="text" name="from" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();" id="easy-form-from" value="'.date(RESERVATIONS_DATE_FORMAT, strtotime($edit_querie[0]->arrivalDate)).'">';
				$return .= '<label>'.__( 'To' , 'easyReservations' ).'<span class="small">'.__( 'The departure date' , 'easyReservations' ).'</span></label><input type="text" name="to" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();" id="easy-form-to" value="'.date(RESERVATIONS_DATE_FORMAT, strtotime($edit_querie[0]->arrivalDate)+(86400*$edit_querie[0]->nights)).'">';
				$return .= '<label>'.__( 'Persons' , 'easyReservations' ).'<span class="small">'.__( 'The amount of persons' , 'easyReservations' ).'</span></label><select name="persons" id="easy-form-persons" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();">'.easyReservations_num_options(1,50,$persons).'</select>';
				if(isset($childs) && $childs != "") $return .= '<label>'.__( 'Children\'s' , 'easyReservations' ).'<span class="small">'.__( 'The amount of children\'s' , 'easyReservations' ).'</span></label><select name="childs" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();">'.easyReservations_num_options(0,50,$childs).'</select>';
				$return .= '<label>'.__( 'Country' , 'easyReservations' ).'<span class="small">'.__( 'Select your county' , 'easyReservations' ).'</span></label><select name="country">'.easyReservations_country_select($country).'</select>';
				if($isCalendar) $calendar_js = 'document.CalendarFormular.room.value=this.value;easyreservations_send_calendar(\'shortcode\');';
				$return .= '<label>'.$atts['roomname'].'<span class="small">'.__( 'Choose the' , 'easyReservations' ).' '.$atts['roomname'].'</span></label><select  name="room" id="room" onChange="'.$calendar_js.'easyreservations_send_price(\'front\');easyreservations_send_validate();">'.reservations_get_room_options($edit_querie[0]->room).'</select>';
				if(isset($atts['offers']) && $atts['offers'] == 1) $return .= '<label>'.__( 'Offer' , 'easyReservations' ).'<span class="small">'.__( 'Choose the offer' , 'easyReservations' ).'</span></label><select  name="offer" id="offer" onchange="easyreservations_send_price(\'front\');easyreservations_send_validate();"><option  value="0">'.__( 'None' , 'easyReservations' ).'</option>'.reservations_get_offer_options($special).'</select>';
				else $return .= '<input type="hidden" name="offer" id="offer" value="0">';
				if(!empty($edit_querie[0]->custom)){
					$customs=easyreservations_get_customs($edit_querie[0]->custom, 0, 'cstm', 'edit');
					if(!empty($customs)){
						foreach($customs as $key => $custom){
							if($custom['mode'] == 'visible' || $custom['mode'] == 'edit'){ 
								$return .= '<label>'.__($custom['title']).'<span class="small">'.__( "Type in information" , "easyReservations" ).'</span></label>';
								$return .= '<input type="hidden" name="custom_title_'.$key.'" value="'.$custom['title'].'">';
								if($custom['mode'] == 'edit') $return .= '<input type="text" name="custom_value_'.$key.'" value="'.$custom['value'].'"><input type="hidden" value="edit" name="custommodus'.$key.'">';
								else $return .= '<span style="display:inline-block;min-width:150px;min-height:40px;margin-left:10px">'.$custom['value'].'<input type="hidden" name="custommodus'.$key.'" value="visible"><input type="hidden" name="custom_value_'.$key.'" value="'.$custom['value'].'"></span>';
							}
						}
					}
				}
				if(!empty($edit_querie[0]->customp)){
					$customps=easyreservations_get_customs($edit_querie[0]->customp, 0, 'cstm', 'edit');
					if(!empty($customps)){
						foreach($customps as $thenumber2 => $customp){
							if($customp['mode'] == 'visible' || $customp['mode'] == 'edit'){ 
								$return .= '<label>'.__($customp['title']).'<span class="small">'.__( "Pay service" , "easyReservations" ).'</span></label><span class="formblock" style="width:50%"><b>'.$customp['value'].':</b> '.$customp['amount'].' &'.get_option("reservations_currency").';';
								if($customp['mode'] == 'edit') $return .= '<input type="checkbox"  id="custom_price'.$thenumber2.'" value="test:'.$customp['amount'].'" onchange="easyreservations_send_price(\'front\');" checked ><input name="customPmodus'.$thenumber2.'" type="hidden" value="edit">'; 
								else $return .= '<input type="hidden" name="customPmodus'.$thenumber2.'" value="visible"><input type="hidden" id="custom_price'.$thenumber2.'" value="test:'.$customp['amount'].'">';
								$return .= '<input type="hidden" name="customPtitle'.$thenumber2.'" value="'.$customp['title'].'"><input type="hidden" name="customPvalue'.$thenumber2.'" value="'.$customp['value'].'"><input type="hidden" name="customPprice'.$thenumber2.'" value="'.$customp['amount'].'"></span>';
							}
						}
					}
				}
				$return .= '<label>'.__( 'Message' , 'easyReservations' ).'<span class="small">'.__( 'Type in message' , 'easyReservations' ).'</span></label><textarea name="editMessage" style="width:170px;">'.$edit_querie[0]->notes.'</textarea>';
				$return .= '<div style="text-align:center">';
					if($resPast == 0) $return .= '<input type="submit" onclick="easyreservations_send_validate(\'send\'); return false;" value="'.__( 'Submit' , 'easyReservations' ).'">';
					if(isset($atts['price'])) $return .='<span class="showPrice" style="margin-left:10px">'.__( 'Price' , 'easyReservations' ).': <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &'.get_option("reservations_currency").';</span>';
				$return .= '</div>';
			$return .= '</div>';
			$return .= '</form>';

			return $return;
		} else return '<div style="text-align:center;">'.__(  'Wrong ID or eMail' , 'easyReservations' ).' - <a href="'.$_SERVER['referer_url'].'">'.__( 'back' , 'easyReservations' ).'</a></div>';
	} else {
		include_once(dirname(__FILE__).'/lib/captcha/captcha.php');
		$captcha_instance = new ReallySimpleCaptcha();
		$word = $captcha_instance->generate_random_word();
		$prefix = mt_rand();
		$url = $captcha_instance->generate_image($prefix, $word);
		
		if(!empty($edit_options['login_text'])) $return .= '<div style="text-align:center;" class="easy-edit-login-text">'.$edit_options['login_text'].'</div>';
		$return .= '<form method="post" id="easyFrontendFormular" style="padding-left:106px;margin-top:5px">';
			$return .= '<input type="hidden" value="'.$prefix.'" name="captcha_prefix">';
			$return .= '<input name="easy-user-edit-login" type="hidden" value="'.wp_create_nonce('easy-user-edit-login').'">';
			$return .= '<label>'.__( 'ID' , 'easyReservations' ).'<span class="small">'.__( 'ID of your reservation' , 'easyReservations' ).'</span></label><input name="editID" type="text"><br>';
			$return .= '<label>'.__( 'eMail' , 'easyReservations' ).'<span class="small">'.__( 'Your email' , 'easyReservations' ).'</span></label><input name="email" type="text"><br>';
			$return .= '<label>'.__( 'Captcha' , 'easyReservations' ).'<span class="small">'.__( 'Type in code' , 'easyReservations' ).'</span></label><input type="text" name="captcha_value" style="width:40px;"><img style="vertical-align:middle;" src="'.RESERVATIONS_LIB_DIR.'/captcha/tmp/'.$url.'">';
			$return .= '<input type="submit" onclick="document.getElementById(\'easyFrontendFormular\').submit(); return false;" class="button-primary" value="'.__( 'Submit' , 'easyReservations' ).'">';
		$return .= '</form>';
		return $return;
    }
}
?>