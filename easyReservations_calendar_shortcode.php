<?php
function reservations_calendar_shortcode($atts) {
?>
<script language="JavaScript" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/xmlhttprequestobject.js"></script>
<script language="JavaScript" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/send.js"></script>
<form name="formular" id="formular">
	<input type="hidden" name="room" onChange="sndReq()" value="<?php echo $atts[0]; ?>">
	<input type="hidden" name="date" onChange="sndReq()" value="0">
</form>
<div id="zeige"></div>
<script>
	sndReq();
</script>
<?php
}	?>