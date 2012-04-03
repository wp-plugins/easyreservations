<?php
function reservations_form_shortcode($atts){

	global $post;
	$finalform = "";
	$error = "";

	if(isset($atts['style'])) $style=$atts['style'];
	else $style = "none";

	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle');
	wp_enqueue_script('easy-form-js');
	wp_enqueue_style('easy-form-'.$style);

	if(isset($atts[0])) $theForm=stripslashes(get_option('reservations_form_'.$atts[0].''));
	else $theForm=stripslashes (get_option("reservations_form"));

	if(strpos($theForm, '[error') !== false){
		$validate_action = 'easyreservations_send_validate();';
		wp_enqueue_script( 'easyreservations_send_validate' );
	} else $validate_action = '';

	if(strpos($theForm, '[show_price') !== false){
		$price_action = "easyreservations_send_price();";
		wp_enqueue_script( 'easyreservations_send_price' );
		add_action('wp_print_footer_scripts', 'easyreservtions_send_price_script'); //get price directily after loading
	} else $price_action = '';
	
	if(strpos(get_the_content($post->ID), '[easy_calendar') !== false){
		$isCalendar = true;
	} else $isCalendar = false;

	if(isset($_POST['easynonce'])) { // Check and Set the Form Inputs

		if (!wp_verify_nonce($_POST['easynonce'], 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );

		if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
		else $captcha ="";

		if(isset($_POST['thename'])) $name_form=$_POST['thename'];
		else $name_form = "";

		if(isset($_POST['from'])) $from=$_POST['from'];
		else $from = "";

		if(isset($_POST['to'])) $to=$_POST['to'];
		else $to = "";

		if(isset($_POST['persons'])) $persons=$_POST['persons'];
		else $persons = "";

		if(isset($_POST['email'])) $email=$_POST['email'];
		else $email = "";

		if(isset($_POST['childs'])) $childs=$_POST['childs'];
		else $childs = 0;

		if(isset($_POST['country'])) $country=$_POST['country'];
		else $country = "";

		if(isset($_POST['room'])) $room=$_POST['room'];
		else $room = "";

		if(isset($_POST['message'])) $message=$_POST['message'];
		else $message = "";

		if(isset($_POST['offer'])) $offer=$_POST['offer'];
		else $offer = "";

		preg_match_all(' /\[.*\]/U', $theForm, $matches); 
		$mergearray=array_merge($matches[0], array());
		$edgeoneremove=str_replace('[', '', $mergearray);
		$edgetworemoves=str_replace(']', '', $edgeoneremove);
		$custom_form='';
		$custom_price='';

		foreach($edgetworemoves as $fields){
			$field=explode(" ", $fields);
			if($field[0]=="custom"){
				if(isset($_POST[$field[2]]) AND !empty($_POST[$field[2]])){
					$custom_form[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => $_POST[$field[2]]);
				} else { 
					if($field[count($field)-1] == "*") $error.= sprintf(__( 'Please fill out %s', 'easyReservations'), $field[2]).'<br>'; 
				}
			}
			if($field[0]=="price"){
				if(isset($_POST[$field[2]])){
					$explodeprice = explode(":",$_POST[$field[2]]);
					if(isset($explodeprice[2]) && $explodeprice[2] == 1) $theprice = $explodeprice[1] * ($persons+$childs);
					elseif(isset($explodeprice[2]) && $explodeprice[2] == 2) $theprice = $explodeprice[1] * round((strtotime($to)-strtotime($from))/86400);
					else $theprice = $explodeprice[1];
					$custom_price[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => $explodeprice[0], 'amount' => $theprice );
				}
			}
		}

		$custom_forms = maybe_serialize($custom_form);
		$custom_prices = maybe_serialize($custom_price);

		if($error == '') $error .= easyreservations_check_reservation( array( 'captcha' => $captcha, 'thename' => $name_form, 'from' => $from, 'to' => $to, 'email' => $email, 'persons' => $persons, 'childs' => $childs, 'country' => $country, 'room' => $room, 'message' => $message, 'offer' => $offer, 'custom' => $custom_forms, 'customp' => $custom_prices), 'user-add');
		if(is_numeric($error)){
			$theID = $error;
			$error = '';
		}
		if($error != '') $error='<div id="post_errors" class="showError">'.substr($error, 0, -4).'</div>';
	}

	if(isset($_POST['easynonce']) && empty($error) && isset($from)) { //When Check gives no error Insert into Database and send mail
		$finalform.= '<div class="easy_form_success"><b>'.__( 'Your reservation was sent' , 'easyReservations' ).'!</b>';
			
		if(function_exists('easyreservations_generate_paypal_button')){
			if($nights < 1 || $nights == '' ){
				$nights = round((strtotime($to)-strtotime($from))/86400);
			}
			$finalform .= easyreservations_generate_paypal_button($theID, strtotime($from), $nights, $room, $offer, $email, 0);
		}
		$finalform.='</div>';
	}
	if(isset($atts['room'])) $theRoom = $atts['room']; else $theRoom = 0;

	$finalformedgesremoved = easyreservations_generate_form($theForm, $price_action, $validate_action, $isCalendar, $theRoom = 0);

	if($finalform == '') $finalform.='<div class="easyFrontendFormular"><form method="post" id="easyFrontendFormular" name="easyFrontendFormular" class=""><input name="easynonce" type="hidden" value="'.wp_create_nonce('easy-user-add').'"><input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">'.$error.$finalformedgesremoved.'<!-- Provided by easyReservations free Wordpress Plugin http://www.feryaz.de --></form></div>';

	if(isset($_POST['from'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.from.value="'.$_POST['from'].'";document.easyFrontendFormular.to.value="'.$_POST['to'].'";</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['thename'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.thename.value="'.$_POST['thename'].'";</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['message'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.message.value="'.$_POST['message'].'";</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['email'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.email.value="'.$_POST['email'].'";</script>';
		$informationsFromOutside = 1;
	}
	
	if(isset($_POST['persons'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.persons.selectedIndex='.($_POST['persons']-1).';</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['childs'])){
		$finalform .= '<script>if(document.easyFrontendFormular) document.easyFrontendFormular.childs.selectedIndex='.$_POST['childs'].';</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['country'])){
		$finalform .= '<script>
function setCountry(country) {
	var x = document.getElementById("easy-form-country");
	if(x){
		for (var i = 0; i < x.options.length; i++) {   
			if (x.options[i].value == country){ 
				x.options[i].selected = true;     
				break;
			}
		}
	}

}  setCountry("'.$_POST['country'].'");</script>';
		$informationsFromOutside = 1;
	}
	
	if(isset($_POST['room'])){
		$finalform .= '<script>
function setRoom(roomid) {
	var x = document.getElementById("form_room"); 
	if(x){
		for (var i = 0; i < x.options.length; i++) {   
			if (x.options[i].value == roomid){ 
				x.options[i].selected = true;     
				break;
			}
		}
	}
}  setRoom('.$_POST['room'].');</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['offer']) AND $_POST['offer'] != 0){
		$finalform .= '<script>
function setOffer(offerid) {
	var x = document.getElementById("form_offer");
	if(x){
		for (var i = 0; i < x.options.length; i++){   
			if (x.options[i].value == offerid){ 
				x.options[i].selected = true;     
				break;
			}
		}
	}
}  setOffer('.$_POST['offer'].');</script>';
		$informationsFromOutside = 1;
	}

	add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');

	return $finalform;
}

function easyreservations_make_datepicker(){
	echo ' <script type="text/javascript"> easyreservations_build_datepicker(); </script> ';
}

?>