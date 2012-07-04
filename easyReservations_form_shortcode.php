<?php
function reservations_form_shortcode($atts){

	global $post;
	$finalform = "";
	$error = '';
	if(isset($atts[0])) $theForm = stripslashes(get_option('reservations_form_'.$atts[0]));
	else $theForm = stripslashes (get_option("reservations_form"));
	if(empty($theForm)) $theForm = stripslashes (get_option("reservations_form"));

	$atts = shortcode_atts(array(
		'room' => 0,
		'price' => 1,
		'submit' => __( 'Your reservation was sent' , 'easyReservations' ),
		'style' => 'none',
		'redirect' => ''
	), $atts);

	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle' , false, array(), false, 'all');
	wp_enqueue_script('easy-form-js');
	wp_enqueue_style('easy-form-'.$atts['style'] , false, array(), false, 'all');

	if(strpos($theForm, '[error') !== false){
		$validate_action = 'easyreservations_send_validate();';
		wp_enqueue_script( 'easyreservations_send_validate' );
	} else $validate_action = '';

	if(strpos($theForm, '[show_price') !== false){
		$price_action = "easyreservations_send_price();";
		wp_enqueue_script( 'easyreservations_send_price' );
		add_action('wp_print_footer_scripts', 'easyreservtions_send_price_script'); //get price directily after loading
	} else $price_action = '';

	if(strpos(get_the_content($post->ID), '[easy_calendar') !== false) $isCalendar = true;
	else $isCalendar = false;

	if(isset($_POST['easynonce'])) { // Check and Set the Form Inputs

		if (!wp_verify_nonce($_POST['easynonce'], 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		global $the_rooms_intervals_array;

		if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
		else $captcha ="";

		if(isset($_POST['thename'])) $name_form=$_POST['thename'];
		else $name_form = "";

		if(isset($_POST['from'])) $from=$_POST['from'];
		else $from = "";

		if(isset($_POST['to'])) $to=$_POST['to'];
		else $to = "";

		if(isset($_POST['persons'])) $persons=$_POST['persons'];
		else $persons = 1;

		if(isset($_POST['email'])) $email=$_POST['email'];
		else $email = "";

		if(isset($_POST['childs'])) $childs=$_POST['childs'];
		else $childs = 0;

		if(isset($_POST['nights'])) $nights=$_POST['nights'];
		else $nights = 0;

		if(isset($_POST['country'])) $country=$_POST['country'];
		else $country = "";

		if(isset($_POST['easyroom'])) $room=$_POST['easyroom'];
		else $room = "";

		$fromplus = 0;
		if(isset($_POST['date-from-hour'])) $fromdplus = (int) $_POST['date-from-hour'] * 60;
		else $fromdplus += 12*60;
		$fromplus+= $fromdplus;
		if(isset($_POST['date-from-min'])) $fromplus += (int) $_POST['date-from-min'];
		if($fromplus > 0) $fromplus *= 60;
		$toplus = 0;
		if(isset($_POST['date-to-hour'])) $toplus += (int) $_POST['date-to-hour'] * 60;
		else $toplus += 12*60;
		if(isset($_POST['date-to-min'])) $toplus += (int) $_POST['date-to-min'];
		if($fromplus > 0) $toplus *= 60;

		preg_match_all(' /\[.*\]/U', $theForm, $matches); 
		$mergearray=array_merge($matches[0], array());
		$edgeoneremove=str_replace('[', '', $mergearray);
		$edgetworemoves=str_replace(']', '', $edgeoneremove);
		$custom_form='';
		$custom_price='';

		foreach($edgetworemoves as $fields){
			$field=explode(" ", $fields);
			if($field[0]=="custom"){
				if(isset($_POST['easy-custom-'.$field[2]]) && !empty($_POST['easy-custom-'.$field[2]])){
					$custom_form[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => $_POST['easy-custom-'.$field[2]]);
				} else { 
					if($field[count($field)-1] == "*") $error.= '<li>'.sprintf(__( '%s is required', 'easyReservations'), ucfirst($field[2])).'</li>'; 
				}
			}
			if($field[0]=="price"){
				if(isset($_POST[$field[2]])){
					$explodeprice = explode(":",$_POST[$field[2]]);
					if(isset($explodeprice[2]) && $explodeprice[2] == 1) $theprice = $explodeprice[1] * ($persons+$childs);
					elseif(isset($explodeprice[2]) && $explodeprice[2] == 2) $theprice = $explodeprice[1] * easyreservations_get_nights($the_rooms_intervals_array[$room], strtotime($from)+$fromplus,strtotime($to)+$toplus);
					elseif(isset($explodeprice[2]) && $explodeprice[2] == 3) $theprice = $explodeprice[1] * easyreservations_get_nights($the_rooms_intervals_array[$room], strtotime($from)+$fromplus,strtotime($to)+$toplus) * ($persons+$childs);
					else $theprice = $explodeprice[1];
					$custom_price[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => $explodeprice[0], 'amount' => $theprice );
				}
			}
		}

		$custom_forms = maybe_serialize($custom_form);
		$custom_prices = maybe_serialize($custom_price);

		if($error == '') $error .= easyreservations_check_reservation( array( 'captcha' => $captcha, 'thename' => $name_form, 'from' => $from, 'fromplus' => $fromplus, 'to' => $to, 'toplus' => $toplus, 'nights' => $nights, 'email' => $email, 'persons' => $persons, 'childs' => $childs, 'country' => $country, 'room' => $room, 'custom' => $custom_forms, 'customp' => $custom_prices, 'redirect' => $atts['redirect']), 'user-add');
		if(is_numeric($error)){
			$theID = $error;
			$error = '';
		}
	}

	if(isset($_POST['easynonce']) && empty($error) && isset($from)) { //When Check gives no error Insert into Database and send mail
		$finalform.= '<div class="easy_form_success"><b>'.$atts['submit'].'!</b>';
		$price = easyreservations_price_calculation($theID, '');
		$price = str_replace(",", ".", $price['price']);
		if($atts['price'] == 1) $finalform.= '<span class="easy_show_price_submit">'.__('Price','easyReservations').': <b>'.reservations_format_money($price, 1).'</b></span>';
		if(function_exists('easyreservations_generate_paypal_button')){
			$finalform .= easyreservation_deposit_function($price);
			$finalform .= easyreservations_generate_paypal_button($theID, strtotime($from), strtotime($to), $room, $email, $persons, $childs);
		}
		$finalform.='</div>';
	}
	$finalformedgesremoved = easyreservations_generate_form($theForm, $price_action, $validate_action, $isCalendar, $atts['room'], $error);

	if($finalform == '') $finalform.='<div class="easyFrontendFormular"><form method="post" id="easyFrontendFormular" name="easyFrontendFormular"><input name="easynonce" type="hidden" value="'.wp_create_nonce('easy-user-add').'"><input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">'.$finalformedgesremoved.'<!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org --></form></div>';

		if(isset($_POST['from'])){
			$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.from.value="'.$_POST['from'].'";document.easyFrontendFormular.to.value="'.$_POST['to'].'"; var theCustomField = \'\';</script>'; 
			foreach($_POST as $key => $val){
				if (strpos($key, 'easy-custom-') === 0){
					$finalform .= '<script>theCustomField = document.getElementsByName(\''.$key.'\'); if(theCustomField[0]) theCustomField[0].value = \''.$val.'\';</script>'; 
				}
			}
		}
		if(isset($_POST['thename'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.thename.value="'.$_POST['thename'].'";</script>';
		if(isset($_POST['message'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.message.value="'.$_POST['message'].'";</script>';
		if(isset($_POST['date-from-hour']) && is_numeric($_POST['date-from-hour'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-from-hour\'); if(datefromfield) datefromfield.value="'.$_POST['date-from-hour'].'";</script>';
		if(isset($_POST['date-from-min']) && is_numeric($_POST['date-from-min'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-from-min\'); if(datefromfield) datefromfield.value="'.$_POST['date-from-min'].'";</script>';
		if(isset($_POST['date-to-hour']) && is_numeric($_POST['date-to-hour'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-to-hour\'); if(datefromfield) datefromfield.value="'.$_POST['date-to-hour'].'";</script>';
		if(isset($_POST['date-to-min']) && is_numeric($_POST['date-to-min'])) $finalform .= '<script>var datefromfield = document.getElementById(\'date-to-min\'); if(datefromfield) datefromfield.value="'.$_POST['date-to-min'].'";</script>';
		if(isset($_POST['email'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.email.value="'.$_POST['email'].'";</script>';
		if(isset($_POST['persons']))	$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.persons.selectedIndex='.($_POST['persons']-1).';</script>';
		if(isset($_POST['childs'])) $finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.childs.selectedIndex='.$_POST['childs'].';</script>';
		if(isset($_POST['country'])) $finalform .= '<script>function setCountry(country) {var x = document.easyFrontendFormular.country;if(x){for (var i = 0; i < x.options.length; i++) {if (x.options[i].value == country){x.options[i].selected = true;break;}}}}setCountry("'.$_POST['country'].'");</script>';
		if(isset($_POST['room'])) $finalform .= '<script>function setRoom(roomid){var x=document.easyFrontendFormular.easyroom;if(x){for (var i = 0; i < x.options.length; i++){if(x.options[i].value == roomid){x.options[i].selected = true;break;}}}}setRoom('.$_POST['room'].');</script>';
	add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');
	return $finalform;
}

function easyreservations_make_datepicker(){
	echo ' <script type="text/javascript"> easyreservations_build_datepicker(); </script> ';
}
?>