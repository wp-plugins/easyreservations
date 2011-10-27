<?php
function reservations_calendar_shortcode($atts) {

if(isset($atts['room'])) $room = $atts['room']; else $room = 0;
if(isset($atts['offer'])) $offer = $atts['offer']; else $offer = 0;
if(isset($atts['width'])) $width = $width['width']; else $width = '';
if(isset($atts['heigth'])) $heigth = $atts['heigth']; else $heigth = '';
if(isset($atts['price'])) $price = $atts['price']; else $price = 0;

?>
<script language="JavaScript" id="urlCalendar" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/send_calendar.js"></script>
<form name="formular" id="formular">
	<input type="hidden" name="room" onChange="easyRes_sendReq_Calendar()" value="<?php echo $room; ?>">
	<input type="hidden" name="offer" onChange="easyRes_sendReq_Calendar()" value="<?php echo $offer; ?>">
	<input type="hidden" name="date" onChange="easyRes_sendReq_Calendar()" value="0">
	<input type="hidden" name="size" value="<?php echo $width.','.$heigth.','.$price; ?>">
</form>
<div id="showCalender" style="width:300px;margin-right:auto;margin-left:auto;vertical-align:middle;padding:0"></div><script>easyRes_sendReq_Calendar();</script><?php
}	?>