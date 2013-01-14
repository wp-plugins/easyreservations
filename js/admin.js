jQuery(document).ready(function(){
	jQuery('form').bind('change',function() {
		var submit = jQuery(this).find('input[type="submit"]');
		if(submit.length > 0){
			submit.addClass('savemark');
			easyReservationsBlinkButton(submit, this);
		} else {
			var button = jQuery(this).find('input[type="button"]');
			if(button.length > 0){
				button.addClass('savemark');
				easyReservationsBlinkButton(button, this);
			}
		}
	});
});

var button = new Array();

function easyReservationsBlinkButton(e, p){
	var count = jQuery.inArray(jQuery(p).attr("id"), button);
	if(count == -1 ){
		button.push(jQuery(p).attr("id"));
		setInterval(function(){easyReservationsChangeClass(e)},1500);
	}
	return true;
}

function easyReservationsChangeClass(e){
	if(e.hasClass('savemark')){
		e.removeClass('savemark');
	} else {
		e.addClass('savemark');
	}
	return true;
}