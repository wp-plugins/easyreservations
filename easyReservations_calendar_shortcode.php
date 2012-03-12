<?php
function reservations_calendar_shortcode($atts) {

if(isset($atts['room'])) $room = $atts['room']; else $room = 0;
if(isset($atts['offer'])) $offer = $atts['offer']; else $offer = 0;
if(isset($atts['width'])) $width = $atts['width']; else $width = '';
if(isset($atts['heigth'])) $heigth = $atts['heigth']; else $heigth = '';
if(isset($atts['price'])) $price = $atts['price']; else $price = 0;
if(isset($atts['style'])) $style = $atts['style']; else $style = 1;

?><input type="hidden" id="urlCalendar" value="<?php echo RESERVATIONS_JS_DIR; ?>/ajax/send_calendar.js">
<form name="CalendarFormular" id="CalendarFormular">
	<input type="hidden" name="room" onChange="easyRes_sendReq_Calendar()" value="<?php echo $room; ?>">
	<input type="hidden" name="offer" onChange="easyRes_sendReq_Calendar()" value="<?php echo $offer; ?>">
	<input type="hidden" name="date" onChange="easyRes_sendReq_Calendar()" value="0">
	<input type="hidden" name="size" value="<?php echo $width.','.$heigth.','.$price.','.$style; ?>">
</form><!-- Provided by easyReservations free Wordpress Plugin http://www.feryaz.de -->
<div id="showCalender" style="margin-right:auto;margin-left:auto;vertical-align:middle;padding:0;width:<?php if(!empty($width)) echo $width; else echo 300; ?>px"></div><script>easyRes_sendReq_Calendar();</script><?php
}	?>