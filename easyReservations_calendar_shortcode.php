<?php
	function reservations_calendar_shortcode($atts) {
		global $easyreservations_script;
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui');
		wp_enqueue_script( 'easyreservations_send_calendar' );

		$atts = shortcode_atts(array(
			'room' => 0,
			'resource' => 0,
			'width' => '300',
			'style' => 1,
			'price' => 0,
			'header' => 0,
			'req' => 0,
			'interval' => 1,
			'monthes' => 1,
			'select' => 2
		), $atts);

		$atts['width'] = (float) $atts['width'];
		if($atts['width'] > 100 || $atts['width'] < 3) $atts['width'] = 100;
		if(!is_numeric($atts['resource']) || $atts['resource'] < 1)	$atts['resource'] = $atts['room'];
		if(isset($_POST['easyroom']) && is_numeric($_POST['easyroom'])) $atts['resource'] = $_POST['easyroom'];

		if (wp_style_is('easy-cal-'.$atts['style'], 'registered')) wp_enqueue_style('easy-cal-'.$atts['style'], false, array(), false, 'all');
		else wp_enqueue_style('easy-form-none' , false, array(), false, 'all');

		$return = '<form name="CalendarFormular" id="CalendarFormular" style="width:'.$atts['width'].'%">';
			$return .= '<input type="hidden" name="easyroom" onChange="easyreservations_send_calendar(\'shortcode\')" value="'.$atts['resource'].'">';
			$return .= '<input type="hidden" name="date" onChange="easyreservations_send_calendar(\'shortcode\')" value="0">';
			$return .= '<input type="hidden" name="calendarnonce" value="'.wp_create_nonce( 'easy-calendar' ).'">';
			$return .= '<div id="showCalender" style="margin-right:auto;margin-left:auto;vertical-align:middle;padding:0;width:100%"></div>';
		$return .= '</form><!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org -->';
		$easyreservations_script .= ';var easyCalendarAtts='.json_encode($atts).';if(window.easyreservations_send_calendar) easyreservations_send_calendar("shortcode"); else jQuery(document).ready(function(){easyreservations_send_calendar("shortcode");});';

		return $return;
	}
?>