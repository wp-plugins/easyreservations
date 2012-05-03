function easyreservations_build_datepicker(){

	if(easyDate.easydateformat == 'Y/m/d') var dateformatse = 'yy/mm/dd';
	else if(easyDate.easydateformat == 'm/d/Y') var dateformatse = 'mm/dd/yy';
	else if(easyDate.easydateformat == 'Y-m-d') var dateformatse = 'yy-mm-dd';
	else if(easyDate.easydateformat == 'd/m/Y') var dateformatse = 'dd/mm/yy';
	else if(easyDate.easydateformat == 'd.m.Y') var dateformatse = 'dd.mm.yy';

	var dates = jQuery( "#easy-form-from, #easy-form-to" ).datepicker({
		dateFormat: dateformatse,
		minDate: -1,
		onSelect: function( selectedDate ) {
			if(this.id == 'easy-form-from'){
				var option = this.id == "easy-form-from" ? "minDate" : "maxDate",
				instance = jQuery( this ).data( "datepicker" ),
				date = jQuery.datepicker.parseDate( instance.settings.dateFormat ||	jQuery.datepicker._defaults.dateFormat,	selectedDate, instance.settings );
				date.setDate(date.getDate()+1);
				dates.not( this ).datepicker( "option", option, date );
			}
			if(window.easyreservations_send_validate) easyreservations_send_validate();
			if(window.easyreservations_send_price) easyreservations_send_price();		
		}
	});
}
easyreservations_build_datepicker();