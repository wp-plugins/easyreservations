function removeElement(parentDiv, childDiv){
	if (childDiv == parentDiv) {
		alert("The parent div cannot be removed.");
	} else if (document.getElementById(childDiv)) {     
		var child = document.getElementById(childDiv);
		var parent = document.getElementById(parentDiv);
		parent.removeChild(child);
		document.easyFrontendFormular.offer.value=0;
		if(window.easyreservations_send_price) easyreservations_send_price();
	} else {
		alert("Child div has already been removed or does not exist.");
		return false;
	}
}
function easyreservations_build_datepicker(){
	var dates = jQuery( "#easy-form-from, #easy-form-to" ).datepicker({
		dateFormat: 'dd.mm.yy',
		minDate: -1,
		onSelect: function( selectedDate ) {
			var option = this.id == "easy-form-from" ? "minDate" : "maxDate",
				instance = jQuery( this ).data( "datepicker" ),
				date = jQuery.datepicker.parseDate(
					instance.settings.dateFormat ||
					jQuery.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
					dates.not( this ).datepicker( "option", option, date );
					if(window.easyreservations_send_price) easyreservations_send_price();
					if(window.easyreservations_send_validate) easyreservations_send_validate();
		}
	});
}
easyreservations_build_datepicker();