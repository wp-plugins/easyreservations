function easyreservations_send_calendar(where){

	if(where == 'shortcode'){
		var tsecurity = document.CalendarFormular.calendarnonce.value;
		var room = document.CalendarFormular.room.value;
		var offerfield = document.CalendarFormular.offer;
		if(offerfield) var offer = offerfield.value;
		else var offer = 0;
		var sizefield = document.CalendarFormular.size;
		if(sizefield) var size = sizefield.value;
		else var size = '300,260,0,1';
		var datefield = document.CalendarFormular.date;
		if(datefield) var date = datefield.value;
		else var date = '0';
	} else {
		var tsecurity = document.widget_formular.calendarnonce.value;
		var room = document.widget_formular.room.value;
		var offerfield = document.widget_formular.offer;
		if(offerfield) var offer = offerfield.value;
		else var offer = 0;
		var sizefield = document.widget_formular.size;
		if(sizefield) var size = sizefield.value;
		else var size = '300,260,0,1';
		var datefield = document.widget_formular.date;
		if(datefield) var date = datefield.value;
		else var date = '0';
	}

	var data = {
		action: 'easyreservations_send_calendar',
		security:tsecurity,
		room: room,
		offer: offer,
		size: size,
		date: date,
		where:where
	};
	
	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(easyAjax.ajaxurl , data, function(response) {
	//jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>' , data, function(response) {
		if(where == 'shortcode') jQuery("#showCalender").html(response);
		else jQuery("#show_widget_calendar").html(response);
		return false;
	});
}