<?php

	function reservations_calendar_shortcode($atts) {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'easyreservations_send_calendar' );
	
		if(isset($atts['room'])) $room = $atts['room']; else $room = 0;
		if(isset($atts['offer'])) $offer = $atts['offer']; else $offer = 0;
		if(isset($atts['width'])) $width = $atts['width']; else $width = '';
		if(isset($atts['heigth'])) $heigth = $atts['heigth']; else $heigth = '';
		if(isset($atts['price'])) $price = $atts['price']; else $price = 0;
		if(isset($atts['style'])) $style = $atts['style']; else $style = 1;
		wp_enqueue_style('easy-cal-'.$style);

		?><input type="hidden" id="urlCalendar" value="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_calendar.js">
		<form name="CalendarFormular" id="CalendarFormular">
			<input type="hidden" name="room" onChange="easyreservations_send_calendar('shortcode')" value="<?php echo $room; ?>">
			<input type="hidden" name="offer" onChange="easyreservations_send_calendar('shortcode')" value="<?php echo $offer; ?>">
			<input type="hidden" name="date" onChange="easyreservations_send_calendar('shortcode')" value="0">
			<input type="hidden" name="size" value="<?php echo $width.','.$heigth.','.$price; ?>">
			<input type="hidden" name="calendarnonce" value="<?php echo wp_create_nonce( 'easy-calendar' ); ?>">
		</form><!-- Provided by easyReservations free Wordpress Plugin http://www.feryaz.de -->
		<div id="showCalender" style="margin-right:auto;margin-left:auto;vertical-align:middle;padding:0;width:<?php if(!empty($width)) echo $width; else echo 300; ?>px"></div>
		<?php
		add_action('wp_print_footer_scripts', 'easyreservtions_send_cal_script');
	}

	function easyreservtions_send_cal_script(){
		echo '<script>easyreservations_send_calendar("shortcode");</script>';
	}
	function easyreservtions_send_cal_script_widget(){
		echo '<script>easyreservations_send_calendar("widget");</script>';
	}
?>