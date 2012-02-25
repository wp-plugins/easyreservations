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

?>

<html xmlns="http://www.w3.org/1999/xhtml" style="background:#fff">
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
			line-height: 25px;
			height: 83%;
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
			<table border="0" cellpadding="4" cellspacing="0"  class="easyreservations_tiny_popUp">
			<tbody>
				<tr>
					<td nowrap="nowrap" style="border-bottom:1px solid #ececec;padding-bottom:4px;"><label for="easyreservation_type_select"><?php _e("Select", "easyReservations"); ?></label></td>
					<td  style="border-bottom:1px solid #ececec;padding-bottom:4px;">
						<select id="easyreservation_type_select" name="easyreservation_type_select" style="width: 100px" onChange="jumpto(document.easyreservations_tiny_popUp.easyreservation_type_select.options[document.easyreservations_tiny_popUp.easyreservation_type_select.options.selectedIndex].value)">
							<option value="choose"><?php _e("choose", "easyReservations"); ?></option>
							<option value="form"><?php _e("Formular", "easyReservations"); ?></option>
							<option value="calendar"><?php _e("Calendar", "easyReservations"); ?></option>
							<option value="edit"><?php _e("Edit", "easyReservations"); ?></option>
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
			<div class="mceActionPanel">
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
			FieldAdd += '<tr><td colspan="2"><?php _e("This shortcode adds a form to the Page or Post", "easyReservations"); ?>.<br><b><?php _e("Only add one form per page or post", "easyReservations"); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "calendar"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_room"><?php _e("Room", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_calendar_room" name="easyreservation_calendar_room" style="width: 100px"><?php echo $roomsoptions; ?></select></label> <?php _e("Select default room", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_style"><?php _e("Style", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_calendar_style" name="easyreservation_calendar_style" style="width: 100px"><option value="1"><?php _e("simple", "easyReservations"); ?></option><option value="2"><?php _e("modern", "easyReservations"); ?></option></select></label> <?php _e("Select calendar style", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Price", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_show_price" name="easyreservation_show_price" style="width: 100px"><option value="0"><?php _e("no", "easyReservations"); ?></option><option value="1"><?php _e("yes", "easyReservations"); ?></option></select></label> <?php _e("Show price in calendar", "easyReservations"); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_width"><?php _e("Width", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_calendar_width" name="easyreservation_calendar_width" style="width: 90px" value="300"> px</label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_height"><?php _e("Height", "easyReservations"); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_calendar_height" name="easyreservation_calendar_height" style="width: 90px" value="260"> px</label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><?php _e("This shortcode adds an availability calendar to the post or page", "easyReservations"); ?>. <?php _e("You can combine it with a form or the edit-form by add it to the same page", "easyReservations"); ?>.<br><b><?php _e("Only add the calendar once per page or post", "easyReservations"); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "choose"){
		document.getElementById("tiny_Field").innerHTML = '<tr><td colspan="2"><?php _e("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations"); ?>. <?php _e("This can happen with posts in category-views or on homepage", "easyReservations"); ?>.<br><?php _e("To prevent this add the shortcodes after the [more] tag", "easyReservations"); ?>.<br></td></tr>';
	} else if(x == "edit"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td colspan="2" nowrap="nowrap" valign="top"><label for="easyreservation_edit_daysback"><?php _e("Days between arrival and last chance to edt", "easyReservations"); ?>: </label>';
			FieldAdd += '<label><input type="text" id="easyreservation_edit_daysback" name="easyreservation_edit_daysback" style="width: 40px" value="10"> d</label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><?php _e("This shortcode adds the function for your guests to edit their reservations afterwards", "easyReservations"); ?>. <?php _e("You have to copy the URL of this site to the easyReservations general settings", "easyReservations"); ?>.<br><b><?php _e("Only add the edit-form on one page or post", "easyReservations"); ?>.</b></td></tr>';

		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	}
}

function insertEasyShortcode() {

	//var html = tinyMCE.activeEditor.selection.getContent(); // GET CURRENT SELECT IN TEXT ! MAYBE IMPORTANT LATER
	//html = html.replace(/<p>/g,"").replace(/<\/p>/g,"<br \/>");

	var tagtext = '[easy_';
	
	var y = document.easyreservations_tiny_popUp.easyreservation_type_select.options[document.easyreservations_tiny_popUp.easyreservation_type_select.options.selectedIndex].value;

	classAttribs = document.getElementById('easyreservation_type_select').value;

	if(y == "form"){
		classAttribs += document.getElementById('easyreservation_form_chooser').value + ' style="' + document.getElementById('easyreservation_formstyle_chooser').value + '"';
	} else if(y == "calendar"){
		classAttribs += ' room="' + document.getElementById('easyreservation_calendar_room').value + '"';
		if(document.getElementById('easyreservation_calendar_width').value != "") classAttribs += ' width="' + document.getElementById('easyreservation_calendar_width').value + '"';
		if(document.getElementById('easyreservation_calendar_height').value != "") classAttribs += ' height="' + document.getElementById('easyreservation_calendar_height').value + '"';
		if(document.getElementById('easyreservation_calendar_style').value != "") classAttribs += ' style="' + document.getElementById('easyreservation_calendar_style').value + '"';
		if(document.getElementById('easyreservation_show_price').value != "") classAttribs += ' price="' + document.getElementById('easyreservation_show_price').value + '"';
	} else if(y == "edit"){
		classAttribs += ' daysbefore="' + document.getElementById('easyreservation_edit_daysback').value + '"';
	}

	if(y != "choose") tinyMCEPopup.editor.execCommand('mceInsertContent', false, tagtext+classAttribs+']');

	tinyMCEPopup.close();
	return;
}

</script>