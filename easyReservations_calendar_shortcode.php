<?php

	function reservations_calendar_shortcode($atts) {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'easyreservations_send_calendar' );
	
		if(isset($atts['room'])) $room = $atts['room']; else $room = 0;
		if($room === 0 && isset($atts['resource'])) $room = $atts['resource'];
		if(isset($atts['width'])) $width = $atts['width']; else $width = 300;
		if(isset($atts['price'])) $price = $atts['price']; else $price = 0;
		if(isset($atts['style'])) $style = $atts['style']; else $style = 1;
		if(isset($atts['monthes'])) $monthes = $atts['monthes']; else $monthes = 1;
		if(isset($atts['header'])) $header = $atts['header']; else $header = 0;
		if(isset($atts['interval'])) $interval = $atts['interval']; else $interval = 1;
		if(empty($width)) $width = 300;
		
		if (wp_style_is('easy-cal-'.$style, 'registered')) wp_enqueue_style('easy-cal-'.$style, false, array(), false, 'all');
		else wp_enqueue_style('easy-form-none' , false, array(), false, 'all');	

		if(isset($_POST['easyroom']) && is_numeric($_POST['easyroom'])) $room = $_POST['easyroom'];
                
		$return = '<form name="CalendarFormular" id="CalendarFormular" style="margin:0px !important;padding:0px !important;display:inline-block">';
			$return .= '<input type="hidden" name="easyroom" onChange="easyreservations_send_calendar(\'shortcode\')" value="'.$room.'">';
			$return .= '<input type="hidden" name="date" onChange="easyreservations_send_calendar(\'shortcode\')" value="0">';
			$return .= '<input type="hidden" name="size" value="'.$width.','.$price.','. $interval.','.$header.','.$style.'">';
			$return .= '<input type="hidden" name="monthes" value="'.$monthes.'">';
			$return .= '<input type="hidden" name="calendarnonce" value="'.wp_create_nonce( 'easy-calendar' ).'">';
			$return .= '<div id="showCalender" style="margin-right:auto;margin-left:auto;vertical-align:middle;padding:0;width:'.$width.'px"></div>';
		$return .= '</form><!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org -->';

		add_action('wp_print_footer_scripts', 'easyreservtions_send_cal_script');
		return $return;
	}

	function easyreservtions_send_cal_script(){
		echo '<script>easyreservations_send_calendar("shortcode");</script>';
	}
	function easyreservtions_send_cal_script_widget(){
		echo '<script>easyreservations_send_calendar("widget");</script>';
	}
?>