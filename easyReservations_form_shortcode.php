<?php
function reservations_form_shortcode($atts){

	global $post;
	$finalform = "";

	if(isset($atts['style'])) $style=$atts['style'];
	else $style = "none";

	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle');
	wp_enqueue_script('easy-form-js');
	wp_enqueue_style('easy-form-'.$style);

	if(isset($atts[0])) $theForm=stripslashes(get_option('reservations_form_'.$atts[0].''));
	else $theForm=stripslashes (get_option("reservations_form"));

	if(strpos($theForm, '[error]') !== false){
		$validate_action = 'easyreservations_send_validate();';
		wp_enqueue_script( 'easyreservations_send_validate' );
	} else $validate_action = '';

	if(strpos($theForm, '[show_price]') !== false){
		$price_action = "easyreservations_send_price();";
		wp_enqueue_script( 'easyreservations_send_price' );
		add_action('wp_print_footer_scripts', 'easyreservtions_send_price_script'); //get price directily after loading
	} else $price_action = '';
	
	if(strpos(get_the_content($post->ID), '[easy_calendar') !== false){
		$isCalendar = true;
	} else $isCalendar = false;

	if(isset($_POST['easynonce'])) { // Check and Set the Form Inputs

		if (!wp_verify_nonce($_POST['easynonce'], 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );

		$error = "";

		if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
		else $captcha ="";

		if(isset($_POST['thename'])) $name_form=$_POST['thename'];
		else $name_form = "";

		if(isset($_POST['from'])) $from=$_POST['from'];
		else $from = "";

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
					$custom_form.= $field[2].'&:&'.$_POST[$field[2]].'&;&';
				} else { 
					if($field[count($field)-1] == "*") $error.= sprintf(__( 'Please fill out %s', 'easyReservations'), $field[2]).'<br>'; 
				}
			}
			if($field[0]=="price"){
				if($_POST[$field[2]]){
					$explodeprice = explode(":",$_POST[$field[2]]);
					if(isset($explodeprice[2])) $theprice = $explodeprice[1] * $persons;
					else $theprice = $explodeprice[1];
					$custom_price.= $field[2].'&:&'.$explodeprice[0].':'.$theprice.'&;&';
				}
				//else { $error.= __( 'Please fill out ', 'easyReservations').$field[2].'</b><br>'; }
			}
		}

		$error .= easyreservations_check_reservation( array( 'captcha' => $captcha, 'thename' => $name_form, 'from' => $from, 'to' => $to, 'nights' => $nights, 'email' => $email, 'persons' => $persons, 'childs' => $childs, 'country' => $country, 'room' => $room, 'message' => $message, 'offer' => $offer, 'custom' => $custom_form, 'customp' => $custom_price), 'user-add');
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

	$room_category = get_option('reservations_room_category');
	$special_offer_cat = get_option("reservations_special_offer_cat");

	$theForm = stripslashes($theForm);

	preg_match_all(' /\[.*\]/U', $theForm, $matches);
	$mergearray=array_merge($matches[0], array());
	$edgeoneremove=str_replace('[', '', $mergearray);
	$edgetworemoves=str_replace(']', '', $edgeoneremove);
	$customPrices = 0;
	$roomfield = 0;

	foreach($edgetworemoves as $fields){
		$field=array_values(array_filter(preg_split('/("[^"]*"|\'[^\']*\'|\s+)/', str_replace("\\", "", $fields), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE), 'trim'));
		if($field[0]=="date-from"){
			$theForm=str_replace('['.$fields.']', '<input id="easy-form-from" type="text" name="from" value="'.date("d.m.Y", time()).'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="date-to"){
			$theForm=str_replace('['.$fields.']', '<input id="easy-form-to"  type="text" name="to" value="'.date("d.m.Y", time()+172800).'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="nights"){
			if(isset($field[1])) $number=$field[1]; else $number=31;
			$theForm=preg_replace('/\['.$fields.'\]/', '<select name="nights">'.easyReservations_num_options(1,$number).'</select>', $theForm);
		} elseif($field[0]=="persons"){
			if($field[1]=="Select"){
				$start = 1;
				if(isset($field[2])) $end = $field[2]; else $end = 6;
				if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
				$theForm=preg_replace('/\['.$fields.'\]/', '<select name="persons" onchange="'.$price_action.'">'.easyReservations_num_options($start,$end).'</select>', $theForm);
			} elseif($field[1]=="text"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input name="persons" type="text" size="70px" onchange="'.$price_action.$validate_action.'">', $theForm);
			}
		} elseif($field[0]=="childs"){
			if($field[1]=="Select"){
				$start = 0;
				if(isset($field[2])) $end = $field[2]; else $end = 6;
				if(isset($field[3])){ $start = $field[2]; $end = $field[3]; }
				$theForm=preg_replace('/\['.$fields.'\]/', '<select name="childs" onchange="'.$price_action.'">'.easyReservations_num_options($start,$end).'</select>', $theForm);
			} elseif($field[1]=="text"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input name="childs" type="text" size="70px" onchange="'.$price_action.'">', $theForm);
			}
		} elseif($field[0]=="thename"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" name="thename" onchange="'.$validate_action.'">', $theForm);
		} elseif($field[0]=="error"){
			if(isset($error)) $form_error=$error;
			else $form_error = '';
			$theForm=preg_replace('/\['.$fields.'\]/', '<div id="showError" class="showError"></div>'.$form_error, $theForm);
		} elseif($field[0]=="email"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" name="email" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="country"){
			$theForm=str_replace('['.$fields.']', '<select id="easy-form-country" name="country">'.easyReservations_country_select('').'</select>', $theForm);
		} elseif($field[0]=="show_price"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<span class="showPrice">'.__( 'Price' , 'easyReservations' ).': <span id="showPrice" style="font-weight:bold;"><b>0,00</b></span> &'.get_option("reservations_currency").';</span>', $theForm);
		} elseif($field[0]=="message"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<textarea name="message" style="width:200px; height: 100px;"></textarea>', $theForm);
		} elseif($field[0]=="captcha"){
			if(!isset($chaptchaFileAdded)) require_once(dirname(__FILE__).'/lib/captcha/captcha.php');
		    $captcha_instance = new ReallySimpleCaptcha();
			$word = $captcha_instance->generate_random_word();
			$prefix = mt_rand();
			$url = $captcha_instance->generate_image($prefix, $word);

			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" name="captcha_value" style="width:40px;"><img style="vertical-align:middle;margin-top: -5px;" src="'.RESERVATIONS_LIB_DIR.'/captcha/tmp/'.$url.'"><input type="hidden" value="'.$prefix.'" name="captcha_prefix">', $theForm);
		} elseif($field[0]=="hidden"){
			if($field[1]=="room"){
				$roomfield=1;
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="room" value="'.$field[2].'">', $theForm);
			}  elseif($field[1]=="offer"){
				if(isset($field[2])) $offer_value = $field[2]; else $offer_value = 0;
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="offer" value="'.$offer_value.'">', $theForm);
			} elseif($field[1]=="from"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="from" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="to"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="to" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="persons"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="persons" value="'.$field[2].'">', $theForm);
			} elseif($field[1]=="childs"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="childs" value="'.$field[2].'">', $theForm);
			}
		} elseif($field[0]=="rooms"){	
			$roomfield=1;
			if($isCalendar == true) $calendar_action = "document.CalendarFormular.room.value=this.value;easyreservations_send_calendar('shortcode');"; else $calendar_action = '';
			$theForm=str_replace('['.$fields.']', '<select name="room" id="form_room" onChange="'.$calendar_action.$price_action.'">'.reservations_get_room_options().'</select>', $theForm);
		} elseif($field[0]=="custom"){
			if(isset($field[3])) $valuefield=str_replace('"', '', $field[3]);
			if($field[1]=="text"){
				$theForm=str_replace('['.$fields.']', '<input type="text" name="'.$field[2].'">', $theForm);
			} elseif($field[1]=="textarea"){
				$theForm=str_replace($fields, '<textarea name="'.$field[2].'"></textarea>', $theForm);
			} elseif($field[1]=="check"){
				$theForm=str_replace($fields, '<input type="checkbox" name="'.$field[2].'">', $theForm);
			} elseif($field[1]=="radio"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$theForm=str_replace($fields, '<input type="radio" name="'.$field[2].'" value="'.$valuefield.'"> '.$valuefield, $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9_ \\,\\t]+$/", $valuefield)){
					$valueexplodes=explode(",", $valuefield);
					$custom_radio='';
					foreach($valueexplodes as $value){
						if($value != '') $custom_radio .= '<input type="radio" name="'.$field[2].'" value="'.$value.'"> '.$value.'<br>';
					}
					$theForm=str_replace($fields, $custom_radio, $theForm);
				}
			} elseif($field[1]=="select"){
				if(preg_match("/^[0-9]+$/", $valuefield)){
					$theForm=preg_replace('/\['.$fields.'\]/', '<select name="'.$field[2].'">'.easyReservations_num_options(1,$valuefield).'</select>', $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$theForm=preg_replace('/\['.$fields.'\]/', '<select name="'.$field[2].'"><option value="'.$valuefield.'">'.$field[3].'</option></select>', $theForm);
				} elseif(strstr($valuefield,",")) {
					$valueexplodes=explode(",", $valuefield);
					$custom_select='';
					foreach($valueexplodes as $value){
						if($value != '') $custom_select .= '<option value="'.$value.'">'.$value.'</option>';
					}
					$theForm=str_replace($fields, '<select name="'.$field[2].'">'.$custom_select.'</select>', $theForm);
				}
			}
		} elseif($field[0]=="price"){
			$valuefield=str_replace('"', '', $field[3]);
			if(isset($field[4]) && $field[4] == 'pp' ){
				$personfield = 'class="'.$field[4].'"';
				$personfields = ':1';
			} else {
				$personfield = '';
				$personfields = '';
			}
			if($field[1]=="checkbox"){
				$explodeprice=explode(":", $valuefield);
				$theForm=preg_replace('/\['.$fields.'\]/', '<input id="custom_price'.$customPrices.'" '.$personfield.' type="checkbox" onchange="'.$price_action.'" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'">', $theForm);
			} elseif($field[1]=="radio"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$explodeprice=explode(":", $valuefield);
					$theForm=preg_replace('/\['.$fields.'\]/', '<input id="custom_price'.$customPrices.'" '.$personfield.' type="radio" onchange="'.$price_action.'" name="'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'"> '.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1), $theForm);
				} elseif(strstr($valuefield,",")) {
					$valueexplodes=explode(",", $valuefield);
					$custom_radio = '<pre>';
					foreach($valueexplodes as $value){
						$explodeprice=explode(":", $value);
						if($value != '') $custom_radio .= '<input id="custom_price'.$customPrices.'" type="radio" '.$personfield.' name="'.$field[2].'" onchange="'.$price_action.'" value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'"> '.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1).'<br>';
					}
					$theForm=preg_replace('/\['.$fields.'\]/', $custom_radio.'</pre>', $theForm);
				}
			} elseif($field[1]=="select"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $valuefield)){
					$explodeprice=explode(":", $valuefield);
					$theForm=preg_replace('/\['.$fields.'\]/', '<select id="custom_price'.$customPrices.'" name="'.$field[2].'" onchange="'.$price_action.'"><option value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'">'.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1).'</option></select>', $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9].+$/", $valuefield)){
					$valueexplodes=explode(",", $valuefield);
					$custom_select='';
					foreach($valueexplodes as $value){
						$explodeprice=explode(":", $value);
						if($value != '') $custom_select .= '<option  '.$personfield.'  value="'.$explodeprice[0].':'.$explodeprice[1].$personfields.'">'.$explodeprice[0].': '.reservations_format_money($explodeprice[1], 1).'</option>';
					}
					$theForm=str_replace($fields, '<select  '.$personfield.'  id="custom_price'.$customPrices.'" onchange="'.$price_action.'" name="'.$field[2].'">'.$custom_select.'</select>', $theForm);
				}
			}
			$customPrices++;
		} elseif($field[0]=="offers"){
			if($field[1]=="select"){
				if($isCalendar == true) $calendar_action = "document.CalendarFormular.room.value=this.value;easyreservations_send_calendar('shortcode');"; else $calendar_action = '';
				$theForm=preg_replace('/\['.$fields.'\]/', '<select name="offer" id="form_offer" onchange="'.$price_action.'"><option value="0">'. __( 'None' , 'easyReservations' ).'</option>'.reservations_get_offer_options().'</select>', $theForm);
			} elseif($field[1]=="box"){
				$comefrom=wp_get_referer(); //Get Refferer for Offer box Style
				$parsedURL = parse_url ($comefrom);
				$splitPath = explode ('/', end($parsedURL));
				$splitPathTry2 = preg_split ('/\//', end($parsedURL), 0, PREG_SPLIT_NO_EMPTY); 
				$buildarray = array($splitPathTry2);
				$getlast=end($buildarray);
				$explodeID=preg_split ('/p=/', $splitPathTry2[0], 0, PREG_SPLIT_NO_EMPTY); 

				$args=array(
					'name' => end($getlast),
					'post_type' => 'post',
					'showposts' => 1,
				);

				$my_post = get_posts($args);
				if(!empty($my_post)) {
					$theIDs = $my_post[0]->ID;
					if(get_option('permalink_structure')==''){ $theIDs=$explodeID[0]; }
					if(strpos(get_option('permalink_structure'),"%post_id%")!==false){ $theIDs=end($getlast); }
					$cates=get_the_category($theIDs);
					$cate=$cates[0]->term_id;
				} else $cate = 0;

				$special_offer_promt="";

				if($cate==$special_offer_cat){
					$image_id = get_post_thumbnail_id($theIDs);  
					$image_url = wp_get_attachment_image_src($image_id,'large');  
					$image_url = $image_url[0];  
					$desc = get_post_meta($theIDs, 'reservations_short', true);
					$fromto = get_post_meta($theIDs, 'reservations_fromto', true);
						if(strlen(__($desc)) >= 45) { $desc = substr(__($desc),0,45)."..."; }
					$special_offer_promt.='<div id="parent"><div id="child" align="center">';
					$special_offer_promt.='<div align="left" style="width: 324px; border: #ffdc88 solid 1px; vertical-align: middle; background: #fffdeb; padding: 5px 5px 5px 5px; font:12px/18px Arial,serif; border-collapse: collapse;">';
						if(get_post_meta($theIDs, 'reservations_percent', true)!=""){ $special_offer_promt.='<span style="height: 20px; border: 0px; padding: 1px 5px 0 5px; margin: 32px 0 0 -50px; font:14px/18px Arial,serif; font-weight: bold; color: #fff; text-align: right; background: #ba0e01; position: absolute;">'.__(get_post_meta($theIDs, 'reservations_percent', true)).'</span>'; }
					$special_offer_promt.='<img src="'.$image_url.'" style="height:55px; width:55px; border:0px; margin:0px 10px 0px 0px; padding:0px;" class="alignleft"> '.__( 'You\'ve choosen' , 'easyReservations' ).': <b>'.__(get_the_title($theIDs)).'</b><img style="float: right;" src="'.RESERVATIONS_IMAGES_DIR.'/close.png" onClick="'."removeElement('parent','child')".';'.$price_action.'"><br>'.__( 'Available' , 'easyReservations' ).': '.__($fromto[0]).'<br>'.__($desc).'</div>';
					$special_offer_promt.='</div></div><input type="hidden"  name="offer" value="'.$theIDs.'">';
				} else $special_offer_promt.='<input type="hidden" name="offer" value="0">';

				$theForm=preg_replace('/\['.$fields.'\]/', ''.$special_offer_promt.'', $theForm);
			}
		} elseif($field[0]=="submit"){
			if(isset($field[1])) $valuesubmit=$field[1]; else $valuesubmit='Submit';
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="submit" value="'.$valuesubmit.'">', $theForm);
		}
	}

	if($roomfield == 0 && isset($atts['room'])) $finalformedgesremoved .= '<input type="hidden" name="room" value="'.$atts['room'].'">';
	
	$finalformedgeremove1=str_replace('[', '', $theForm);
	$finalformedgesremoved=str_replace(']', '', $finalformedgeremove1);
	$finalform.='<div class="easyFrontendFormular"><form method="post" id="easyFrontendFormular" name="easyFrontendFormular" class=""><input name="easynonce" type="hidden" value="'.wp_create_nonce('easy-user-add').'"><input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">'.$finalformedgesremoved.'<!-- Provided by easyReservations free Wordpress Plugin http://www.feryaz.de --></form></div>';

	if(isset($_POST['from'])){
		$finalform .= '<script>document.easyFrontendFormular.from.value="'.$_POST['from'].'";document.easyFrontendFormular.to.value="'.$_POST['to'].'";</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['thename'])){
		$finalform .= '<script>document.easyFrontendFormular.thename.value="'.$_POST['thename'].'";</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['message'])){
		$finalform .= '<script>document.easyFrontendFormular.message.value="'.$_POST['message'].'";</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['email'])){
		$finalform .= '<script>document.easyFrontendFormular.email.value="'.$_POST['email'].'";</script>';
		$informationsFromOutside = 1;
	}
	
	if(isset($_POST['persons'])){
		$finalform .= '<script>document.easyFrontendFormular.persons.selectedIndex='.($_POST['persons']-1).';</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['childs'])){
		$finalform .= '<script>document.easyFrontendFormular.childs.selectedIndex='.$_POST['childs'].';</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['country'])){
		$finalform .= '<script>
function setCountry(country) {
	var x = document.getElementById("easy-form-country");
	for (var i = 0; i < x.options.length; i++) 
	{   
		if (x.options[i].value == country)
		{ 
			x.options[i].selected = true;     
			break;
		}
	}
	
}  setCountry("'.$_POST['country'].'");</script>';
		$informationsFromOutside = 1;
	}
	
	if(isset($_POST['room'])){
		$finalform .= '<script>
function setRoom(roomid) {
	var x = document.getElementById("form_room"); 
	for (var i = 0; i < x.options.length; i++) 
	{   
		if (x.options[i].value == roomid)
		{ 
			x.options[i].selected = true;     
			break;
		}
	}
}  setRoom('.$_POST['room'].');</script>';
		$informationsFromOutside = 1;
	}

	if(isset($_POST['offer']) AND $_POST['offer'] != 0){
		$finalform .= '<script>
function setOffer(offerid) {
	var x = document.getElementById("form_offer"); 
	for (var i = 0; i < x.options.length; i++) 
	{   
		if (x.options[i].value == offerid)
		{ 
			x.options[i].selected = true;     
			break;
		}
	}
}  setOffer('.$_POST['offer'].');</script>';
		$informationsFromOutside = 1;
	}

	add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');

	return $finalform;

}
function easyreservations_make_datepicker(){
	echo '<script>easyreservations_build_datepicker();</script>';
}

?>