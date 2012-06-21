<?php
//Load bootstrap file
require('../../../../../wp-blog-header.php');
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

	$roomsoptions = reservations_get_room_options();
?><html xmlns="http://www.w3.org/1999/xhtml" style="background:#fff">
	<head>
	<title><?php _e("easyReservations Shortcodes", "easyReservations"); ?></title>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
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
							<select id="easyreservation_type_select" name="easyreservation_type_select" style="width: 100px" onChange="jumpto(document.easyreservations_tiny_popUp.easyreservation_type_select.options[document.easyreservations_tiny_popUp.easyreservation_type_select.options.selectedIndex].value)">
								<option value="choose"><?php _e("choose", "easyReservations"); ?></option>
								<option value="form"><?php _e("Formular", "easyReservations"); ?></option>
								<option value="calendar"><?php _e("Calendar", "easyReservations"); ?></option>
								<option value="edit"><?php _e("Edit", "easyReservations"); ?></option>
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

	if(x == "form"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_form_chooser"><?php _e("Form", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_form_chooser" name="easyreservation_form_chooser" style="width: 100px"><?php echo $formoptions; ?></select></label> <?php _e("Select form", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Style", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_formstyle_chooser" name="easyreservation_formstyle_chooser" style="width: 100px"><option value="none"><?php _e("White", "easyReservations"); ?></option><option value="blue"><?php _e("Blue", "easyReservations"); ?></option></select></label> <?php _e("Select style", "easyReservations"); ?></td>';
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
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_room"><?php _e("Room", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_calendar_room" name="easyreservation_calendar_room" style="width: 100px"><?php echo $roomsoptions; ?></select></label> <?php _e("Select default room", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_style"><?php _e("Style", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_calendar_style" name="easyreservation_calendar_style" style="width: 100px" onchange="getCalendarInfos()"><option value="1"><?php _e("simple", "easyReservations"); ?></option><option value="2"><?php _e("modern", "easyReservations"); ?></option><?php do_action('easy-tinymce-add-style'); ?></select></label> <?php _e("Select calendar style", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Price", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_show_price" name="easyreservation_show_price" style="width: 100px" onchange="getCalendarInfos()"><option value="0"><?php _e("no", "easyReservations"); ?></option><option value="1">150&<?php echo RESERVATIONS_CURRENCY; ?>;</option><option value="2">150</option><option value="3"><?php echo reservations_format_money(150,1); ?></option><option value="4"><?php echo reservations_format_money(150); ?></option></select></label> <?php _e("Show price in calendar", "easyReservations"); ?></td>';
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
	} else if(x == "edit"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td colspan="2" nowrap="nowrap" valign="top"><label for="easyreservation_edit_daysback"><?php _e("Days between arrival and today for last chance to edit", "easyReservations"); ?>: ';
			FieldAdd += '<select id="easyreservation_edit_daysback" name="easyreservation_edit_daysback" style="width: 55px"><?php echo easyReservations_num_options(-100,100,1); ?></select> d</label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_edit_table"><?php _e("Table", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_edit_table" name="easyreservation_edit_table" checked></label> <?php _e("Show table with other reservations by the same email", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_status"><?php _e("Status", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox" id="easyreservation_show_status" name="easyreservation_show_status" checked></label> <?php _e("Show status", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Price", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_show_price" name="easyreservation_show_price" checked></label> <?php _e("Show price", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_edit_roomname"><?php _e("Name for Room", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="text"  id="easyreservation_edit_roomname" name="easyreservation_edit_roomname" value="Room"></label> <?php _e("e.g. Apartment", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><?php _e("This shortcode adds the function for your guests to edit their reservations afterwards", "easyReservations"); ?>. <?php _e("You have to copy the URL of this site to the easyReservations general settings", "easyReservations"); ?>.<br><b><?php _e("Only add the edit-form on one page or post", "easyReservations"); ?>.</b></td></tr>';

		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} <?php do_action('easy-tinymce-add', $roomsoptions); ?>
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
} else if(y == "calendar"){
		classAttribs += ' room="' + document.getElementById('easyreservation_calendar_room').value + '"';
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
	} else if(y == "edit"){
		classAttribs += ' daysbefore="' + document.getElementById('easyreservation_edit_daysback').value + '"';
		if(document.getElementById('easyreservation_show_status').checked == true) classAttribs += ' status="1"';
		if(document.getElementById('easyreservation_show_price').checked == true) classAttribs += ' price="1"';
		if(document.getElementById('easyreservation_edit_table').checked == true) classAttribs += ' table="1"';
		classAttribs += ' roomname="' + document.getElementById('easyreservation_edit_roomname').value + '"';
	} <?php do_action('easy-tinymce-save'); ?>

	if(y != "choose") tinyMCEPopup.editor.execCommand('mceInsertContent', false, tagtext+classAttribs+']');

	tinyMCEPopup.close();
	return;
}
</script>