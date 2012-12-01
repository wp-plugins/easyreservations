var easyClick = 0;
var easyClickFirst = '';
var easyCellnr = 0;
var easyCalM = 0;
function easyreservations_click_calendar(t,d,w,m){
	
	jQuery('.reqdisabled').removeClass('reqdisabled');
	if(easyClick == 2){
		jQuery(".calendar-cell-selected").removeClass("calendar-cell-selected");
		easyClick = 0;
	}
	if(easyClick == 1){
		jQuery('.reqstartdisabled').addClass('reqdisabled');
		if(jQuery(t).hasClass('reqenddisabled')) return false;
		easyCellnr = parseFloat(easyCellnr);
		easyCalM = parseFloat(easyCalM);
		var axis = parseFloat(t.axis);
		if(easyCalM != m) axis = 31;
		//alert('easyCellnr: '+easyCellnr+' axis: '+axis+' m: '+m+' easyCalM: '+easyCalM);
		if(!document.getElementById('easy-cal-' + w + '-'+ easyCellnr + '-' + easyCalM)) easyCellnr = 1;

		if(easyCellnr <= axis && parseFloat(m) >= easyCalM){

			for (i=easyCellnr; i<=axis; i++){
				var element = '#easy-cal-' + w + '-'+ i + '-' + easyCalM;
				if(i != axis && jQuery(element).hasClass('calendar-cell-full') == true){
					jQuery(".calendar-cell-selected").removeClass("calendar-cell-selected");
					var To = document.getElementById('easy-form-to');
					if(To) To.value = '';
					jQuery(t.parentNode.parentNode.parentNode.parentNode).addClass("calendar-full");
					break;
				}
				jQuery(element).addClass("calendar-cell-selected");
				if(i  == 31 && easyCalM != m){
					easyCalM = easyCalM + 1;
					i = 0;
					if(easyCalM == m) axis = parseFloat(t.axis);
				}
			}
			var Too = document.getElementById('easy-form-to');
			if(Too) Too.value = d;
			var ToWidget = document.getElementById('easy-widget-datepicker-to');
			if(ToWidget) ToWidget.value = d;
			if(document.getElementById('easy-form-units') && document.getElementById('easy-form-from')){
				instance = jQuery( '#easy-form-from' ).data( "datepicker" );
				if(instance){
					dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, document.getElementById('easy-form-from').value, instance.settings );
					dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, d, instance.settings );
					var difference_ms = Math.abs(dateanf - dateend);
					var diff = difference_ms/1000;
					var interval = 86400;
					var interval_array = eval("(" + easyAjax.interval + ")");
					if(interval_array[document.getElementById('form_room').value]) interval = interval_array[document.getElementById('form_room').value];
					nights = Math.ceil(diff/interval);
					jQuery('#easy-form-units').val(nights);
				}
			}
			if(window.easyreservations_send_price) easyreservations_send_price();
			if(window.easyreservations_send_validate) easyreservations_send_validate();
			if(window.easyreservations_send_search) easyreservations_send_search();

			easyClick = 2;
		} else {
			easyClick = 2;
			easyCalM = 0;
			jQuery(".calendar-cell-selected").removeClass("calendar-cell-selected");
		}
	}
	if(easyClick == 0){
		if(jQuery(t).hasClass('reqenddisabled')) return false;
		jQuery('.reqenddisabled').addClass('reqdisabled');
		easyCalM = m;
		easyClickFirst = t.id;
		jQuery(t.parentNode.parentNode.parentNode.parentNode).removeClass("calendar-full");
		jQuery(t).addClass("calendar-cell-selected");
		var From = document.getElementById('easy-form-from');
		if(From) From.value = d;
		var FromWidget = document.getElementById('easy-widget-datepicker-from');
		if(FromWidget) FromWidget.value = d;

		easyCellnr  = t.axis;
		easyClick = 1;
	}
}

function easyreservations_send_calendar(where, e ){
	if(where == 'shortcode' && !jQuery("#showCalender")) return;
	if(where != 'shortcode' && !jQuery("#show_widget_calendar")) return;
	e =  window.event;
	if(e){ e = e.target || e.srcElement; }
	if(where == 'shortcode'){
		var tsecurity = document.CalendarFormular.calendarnonce.value;
		var room = document.CalendarFormular.easyroom.value;
		var sizefield = document.CalendarFormular.size;
		if(sizefield) var size = sizefield.value;
		else var size = '300,260,0,1';
		var datefield = document.CalendarFormular.date;
		if(datefield) var date = datefield.value;
		else var date = '0';
		var monthfield = document.CalendarFormular.monthes;
		if(monthfield) var monthes = monthfield.value;
		else var monthes = 1;
	} else {
		var tsecurity = document.widget_formular.calendarnonce.value;
		var room = document.widget_formular.easyroom.value;
		var sizefield = document.widget_formular.size;
		if(sizefield) var size = sizefield.value;
		else var size = '300,0,1';
		var datefield = document.widget_formular.date;
		if(datefield) var date = datefield.value;
		else var date = '0';
		var monthes = 1;
	}

	var data = {
		action: 'easyreservations_send_calendar',
		security:tsecurity,
		room: room,
		size: size,
		date: date,
		where:where,
		monthes:monthes
	};
	
	jQuery.post(easyAjax.ajaxurl , data, function(response) {
		if(where == 'shortcode') jQuery("#showCalender").html(response);
		else jQuery("#show_widget_calendar").html(response);
		return false;
	});
}