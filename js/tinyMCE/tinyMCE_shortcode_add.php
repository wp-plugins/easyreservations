<?php
//Load bootstrap file
require('../../../../../wp-config.php');
$wp->init(); $wp->parse_request(); $wp->query_posts();
$wp->register_globals(); $wp->send_headers();

global $wpdb;
//Check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__("You are not allowed to access this file.", "easyReservations"));

	$form = "SELECT option_name FROM ".$wpdb->prefix ."options WHERE option_name like 'reservations_form_%' "; // Get User made Forms
	$formresult = $wpdb->get_results($form);
	$formoptions = '<option value="">'.__("Standard", "easyReservations").'</option>';
	foreach( $formresult as $result )	{
		$formcutedname=str_replace('reservations_form_', '', $result->option_name);
		if($formcutedname!=""){
			$formoptions.= '<option value=" '.$formcutedname.'">'.$formcutedname.'</option>';
		}
	}

	$roomsoptions = easyreservations_resource_options();
?><html xmlns="http://www.w3.org/1999/xhtml" style="background:#fff">
	<head>
	<title><?php _e("easyReservations Shortcodes", "easyReservations"); ?></title>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js?ver=1.7.2'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-admin/js/utils.dev.js?ver=3.4.1'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/hoverIntent.dev.js?ver=r6'></script>
	<script type='text/javascript'>
	/* <![CDATA[ */
	var commonL10n = {"warnDelete":"You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete."};
	/* ]]> */
	</script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-admin/js/common.dev.js?ver=3.4.1'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.color.dev.js?ver=2.0-4561m'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/ui/jquery.ui.widget.min.js?ver=1.8.20'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/ui/jquery.ui.position.min.js?ver=1.8.20'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/wp-pointer.dev.js?ver=20111129a'></script>
	<script type='text/javascript'>
	/* <![CDATA[ */
	var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"<?php echo addslashes(get_option('siteurl')); ?>\/wp-includes\/js\/thickbox\/loadingAnimation.gif","closeImage":"http:\/\/127.0.0.1\/er\/wp-includes\/js\/thickbox\/tb-close.png"};
	/* ]]> */
	</script>
	<style>
		input[type="text"], select {
			padding:3px;
			width: 120px;
			background-color: #FFFFFF;
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			border-color: #DFDFDF;
			border-radius: 3px 3px 3px 3px;
			border-style: solid;
			border-width: 1px;
		}

		.easyreservations_tiny_popUp {
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			line-height: 30px;
		}

		.easyreservations_tiny_popUp td {
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			color: #666666 !important;
		}

		label {
			font-weight:bold;
			color: #333333;
		}
	</style>
	<base target="_self" />
	</head>
	<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none;background:#fff">
		<form name="easyreservations_tiny_popUp" action="#">
			<table border="0" cellpadding="0" cellspacing="0"  class="easyreservations_tiny_popUp" style="width:99%;">
				<tbody>
					<tr>
						<td nowrap="nowrap" style="border-bottom:1px solid #ececec;padding-bottom:4px;width:30%"><label for="easyreservation_type_select"><?php _e("Select", "easyReservations"); ?></label></td>
						<td  style="border-bottom:1px solid #ececec;padding-bottom:4px;width:70%">
							<select id="easyreservation_type_select" name="easyreservation_type_select" style="width: 100px" onChange="jumpto(this.value)">
								<option value="choose"><?php _e("choose", "easyReservations"); ?></option>
								<option value="form"><?php _e("Formular", "easyReservations"); ?></option>
								<option value="calendar"><?php _e("Calendar", "easyReservations"); ?></option>
								<?php do_action('easy-tinymce-add-name'); ?>
							 </select> <?php _e("Choose type of shortcode", "easyReservations"); ?>
						</td>
					</tr>
				</tbody>
				<tbody id="tiny_Field">
					<div style="float: left">
						<tr><td colspan="2"><?php _e("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations"); ?>. <?php _e("This can happen with posts in category-views or on homepage", "easyReservations"); ?>.<br><?php _e("To prevent this add the shortcodes after the [more] tag", "easyReservations"); ?>.<br></td></tr>
					</div>
				</tbody>
			</table>
			<div class="mceActionPanel" style="vertical-align:bottom;">
				<div style="float: left">
					<input type="submit" id="insert" name="insert" value="<?php _e("Insert", "easyReservations"); ?>" onclick="insertEasyShortcode();" />
				</div>
				<div style="float: right">
					<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", "easyReservations"); ?>" onclick="tinyMCEPopup.close();" />
				</div>
			</div>
		</form>
	</body>
</html>
<script>
function jumpto(x){ // Chained inputs;

	var click = 0;
	var end = 0;
	<?php
	// check if a custom form style is available and add the option to the fieldset on demand
	$customFormStyleOption = '';
	if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/form.css')) $customFormStyleOption = '<option value="custom">' . __("Custom Style", "easyReservations") . '</option>';
	$custom_calendar_style = '';
	if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/calendar.css')) $custom_calendar_style = '<option value="custom">' . __("Custom Style", "easyReservations") . '</option>';?>

	if(x == "form"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_form_chooser"><?php _e("Form", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_form_chooser" name="easyreservation_form_chooser" style="width: 100px"><?php echo $formoptions; ?></select></label> <?php _e("Select form template", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservations_resource"><?php _e("Resource", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservations_resource" name="easyreservations_resource" style="width: 100px"><?php echo $roomsoptions; ?></select></label> <?php _e("Attached to reservations if no resource [tag] in form", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Style", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_formstyle_chooser" name="easyreservation_formstyle_chooser" style="width: 100px"><option value="none"><?php _e("White", "easyReservations"); ?></option><option value="blue"><?php _e("Blue", "easyReservations"); ?></option><?php echo $customFormStyleOption ?></select></label> <?php _e("Select style", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td colspan="2" nowrap="nowrap" valign="top"><label for="easyreservation_form_submit_message"><?php _e("Submit message", "easyReservations"); ?>: </label>';
			FieldAdd += '<label><input type="text" id="easyreservation_form_submit_message" name="easyreservation_form_submit_message" style="width: 250px" value="Reservation successfull send"></label><i><?php _e("Message after successful submission", "easyReservations"); ?></i></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Show price", "easyReservations"); ?>: </label>';
			FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_show_price" name="easyreservation_edit_table" checked></label> <?php _e("After successful submission", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><?php _e("This shortcode adds a form to the Page or Post", "easyReservations"); ?>.<br><b><?php _e("Only add one form per page or post", "easyReservations"); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "calendar"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_room"><?php _e("Resource", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_calendar_room" name="easyreservation_calendar_room" style="width: 100px"><?php echo $roomsoptions; ?></select></label> <?php _e("Select default resource", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_style"><?php _e("Style", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_calendar_style" name="easyreservation_calendar_style" style="width: 100px" onchange="getCalendarInfos()"><?php echo $custom_calendar_style; ?><option value="1"><?php _e("simple", "easyReservations"); ?></option><option value="2"><?php _e("modern", "easyReservations"); ?></option><?php do_action('easy-tinymce-add-style'); ?></select></label> <?php _e("Select calendar style", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Price", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_show_price" name="easyreservation_show_price" style="width: 100px" onchange="getCalendarInfos()"><option value="0"><?php _e("no", "easyReservations"); ?></option><option value="1">150&<?php echo RESERVATIONS_CURRENCY; ?>;</option><option value="2">150</option><option value="3"><?php echo easyreservations_format_money(150,1); ?></option><option value="4"><?php echo easyreservations_format_money(150); ?></option></select></label> <?php _e("Show price in calendar", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_width"><?php _e("Width", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_calendar_width" name="easyreservation_calendar_width" style="width: 90px" value="148"> px</label> <?php _e("Min width", "easyReservations"); ?>: <span id="easyreservation_calendar_min_width" onclick="document.getElementById(\'easyreservation_calendar_width\').value = this.innerHTML">148</span>px</td>';
			FieldAdd += '</tr>';
			FieldAdd += '<?php do_action('easy-tinymce-cal',1); ?>';
			FieldAdd += '<tr><td colspan="2"><?php _e("This shortcode adds an availability calendar to the post or page", "easyReservations"); ?>. <?php _e("You can combine it with a form or the edit-form by add it to the same page", "easyReservations"); ?>.<br><b><?php _e("Only add the calendar once per page or post", "easyReservations"); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "choose"){
		document.getElementById("tiny_Field").innerHTML = '<tr><td colspan="2"><?php _e("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations"); ?>. <?php _e("This can happen with posts in category-views or on homepage", "easyReservations"); ?>.<br><?php _e("To prevent this add the shortcodes after the [more] tag", "easyReservations"); ?>.<br></td></tr>';
	}  <?php do_action('easy-tinymce-add', $roomsoptions); ?>
}

function getCalendarInfos(){
	
	if(document.getElementById("easyreservation_calendar_monthesy")){
		var cols = parseFloat(document.getElementById("easyreservation_calendar_monthesx").value);
		var rows = document.getElementById("easyreservation_calendar_monthesy").value;
		var price = document.getElementById("easyreservation_show_price").value;
		var style = document.getElementById("easyreservation_calendar_style").value;

		var anz = cols * parseFloat(rows);

		if(price == 0) var variable = 148;
		else var variable = 266 + cols -1;
		if(style == 3) variable += 17;

		if(anz == 1) var monthstring = '<?php _e("Month", "easyReservations"); ?>';
		else var monthstring = '<?php _e("Months", "easyReservations"); ?>';

		document.getElementById("easyreservation_calendar_monthes_count").innerHTML = anz + ' ' + monthstring;
		document.getElementById("easyreservation_calendar_min_width").innerHTML = cols * variable;
	} else {
		if(document.getElementById("easyreservation_show_price").value == 0) document.getElementById("easyreservation_calendar_min_width").innerHTML = 266;
		else  document.getElementById("easyreservation_calendar_min_width").innerHTML = 148;
	}
}
function insertEasyShortcode() {

	//var html = tinyMCE.activeEditor.selection.getContent(); // GET CURRENT SELECT IN TEXT ! MAYBE IMPORTANT LATER
	//html = html.replace(/<p>/g,"").replace(/<\/p>/g,"<br \/>");

	var tagtext = '[easy_';

	var y = document.easyreservations_tiny_popUp.easyreservation_type_select.options[document.easyreservations_tiny_popUp.easyreservation_type_select.options.selectedIndex].value;

	classAttribs = document.getElementById('easyreservation_type_select').value;

	if(y == "form"){
		classAttribs += document.getElementById('easyreservation_form_chooser').value + ' style="' + document.getElementById('easyreservation_formstyle_chooser').value + '" submit="' + document.getElementById('easyreservation_form_submit_message').value + '"';
		if(document.getElementById('easyreservation_show_price').checked == true) classAttribs += ' price="1"';
		if(document.getElementById('easyreservations_resource')) classAttribs += ' resource="'+document.getElementById('easyreservations_resource').value+'"';
	} else if(y == "calendar"){
		classAttribs += ' resource="' + document.getElementById('easyreservation_calendar_room').value + '"';
		if(document.getElementById('easyreservation_calendar_width').value != "") classAttribs += ' width="' + document.getElementById('easyreservation_calendar_width').value + '"';
		if(document.getElementById('easyreservation_calendar_style').value != "") classAttribs += ' style="' + document.getElementById('easyreservation_calendar_style').value + '"';
		if(document.getElementById('easyreservation_show_price').value != "") classAttribs += ' price="' + document.getElementById('easyreservation_show_price').value + '"';
		var monthesfield = document.getElementById('easyreservation_calendar_monthesx');
		if(monthesfield){
			classAttribs += ' monthes="' + monthesfield.value + 'x' + document.getElementById('easyreservation_calendar_monthesy').value + '"';
		}
		var intervalfield = document.getElementById('easyreservation_calendar_interval');
		if(intervalfield) classAttribs += ' interval="' + intervalfield.value + '"';
		var headerfield = document.getElementById('easyreservation_calendar_header');
		if(headerfield && headerfield.checked == true) classAttribs += ' header="1"';
	} <?php do_action('easy-tinymce-save'); ?>

	if(y != "choose") tinyMCEPopup.editor.execCommand('mceInsertContent', false, tagtext+classAttribs+']');

	tinyMCEPopup.close();
	return;
}
</script>
<?php 
require_once(WP_PLUGIN_DIR."/easyreservations/lib/tutorials/handle.tutorials.php");
easyreservations_load_pointer('tinymce');
do_action('admin_print_footer_scripts'); ?>