var easyClick = 0;
var easyCellnr = 0;

function easyreservations_click_calendar(t,d,w){
	if(easyClick == 2){
		jQuery(".calendar-cell-selected").removeClass("calendar-cell-selected");
		easyClick = 0;
	}
	if(easyClick == 1){
		easyCellnr = parseFloat(easyCellnr);
		var axis = parseFloat(t.axis);
		if(easyCellnr <= t.axis){
		//alert(easyCellnr+ ' - ' +t.axis);
			for (i=easyCellnr; i<=axis; i++){
				var element = '#easy-cal-' + w + '-'+ i;
				if(i != axis && jQuery(element).hasClass('calendar-cell-full') == true){
					jQuery(".calendar-cell-selected").removeClass("calendar-cell-selected");
					var To = document.getElementById('easy-form-to');
					if(To) To.value = '';
					jQuery(t.parentNode.parentNode.parentNode).addClass("calendar-full");
					break;
				}
				jQuery(element).addClass("calendar-cell-selected");
			}
			var To = document.getElementById('easy-form-to');
			if(To) To.value = d;
			var ToWidget = document.getElementById('easy-widget-datepicker-to');
			if(ToWidget) ToWidget.value = d;
			if(window.easyreservations_send_price) easyreservations_send_price();
			if(window.easyreservations_send_validate) easyreservations_send_validate();

			easyClick = 2;
		} else {
			easyClick = 0;
			jQuery(".calendar-cell-selected").removeClass("calendar-cell-selected");
		}
	}
	if(easyClick == 0){
		jQuery(t.parentNode.parentNode.parentNode).removeClass("calendar-full");
		jQuery(t).addClass("calendar-cell-selected");
		var From = document.getElementById('easy-form-from');
		if(From) From.value = d;
		var FromWidget = document.getElementById('easy-widget-datepicker-from');
		if(FromWidget) FromWidget.value = d;

		easyCellnr  = t.axis;
		easyClick = 1;
	}
}

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
		easyCellnr = 1;
		return false;
	});
}