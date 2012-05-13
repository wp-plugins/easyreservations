var errors_state = 0;
function easyreservations_send_validate(y){
	if(errors_state == 0){
		errors_state = 1;
		var errornr = 1; var custom = '';

		jQuery("#easy-show-error-div").addClass('hide-it');
		jQuery("[id^='easy-custom-req-']").each ( function (i) { 
			if(this.value == '') custom +=	this.id + ',';
			});
		jQuery("[id^='easy-form-'],[id^='easy-custom-']").removeClass('form-error');
		jQuery("label[id^='easy-error-field-']").remove();
		document.getElementById('easy-show-error').innerHTML = '';

		if(document.easyFrontendFormular.room) var room = document.easyFrontendFormular.room.value;
		else alert('no room field - correct that')
		var interval_array = eval("(" + easyAjax.interval + ")");
		var interval = interval_array[room];
		var nights = 1; var to = ''; var toplus = 0; var fromplus = 0; var childs = 0; var persons = 1; var captcha = 'x!'; var tom = 0; var toh = 12; var fromm = 0; var fromh = 12;

		if(y) var mode = y;
		else mode = 'normal';

		var tsecurity = document.easyFrontendFormular.pricenonce.value;

		var fromfield = document.easyFrontendFormular.from;
		if(fromfield){
			fromfield.style.borderColor = '#DDDDDD';
			var from = fromfield.value;
			if(document.getElementById('date-from-hour')) fromh = parseInt(document.getElementById('date-from-hour').value) * 60;
			if(document.getElementById('date-from-min')) fromm = parseInt(document.getElementById('date-from-min').value);
			toh =  fromm;
			fromplus = (fromh + fromm)*60;
		} else alert('no arrival field - correct that');

		var tofield = document.easyFrontendFormular.to;
		if(tofield){
			tofield.style.borderColor = '#DDDDDD';
			to = tofield.value;
			if(document.getElementById('date-to-hour')) toh = parseInt(document.getElementById('date-to-hour').value) * 60;
			if(document.getElementById('date-to-min')) tom = parseInt(document.getElementById('date-to-min').value);
		} else { 
			if(document.easyFrontendFormular.nights){
				nights = document.easyFrontendFormular.nights.value;
			}
		}
		toplus = (toh + tom)*60;

		if(from){
			instance = jQuery( fromfield ).data( "datepicker" );
			if(instance){
				dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, from, instance.settings );
				if(tofield){
					instance = jQuery( tofield ).data( "datepicker" );
					dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, to, instance.settings );
					var difference_ms = Math.abs(dateanf - dateend);
					var diff = difference_ms/1000;
					diff += ((86400-fromplus)+toplus);
					diff -= 86400;
					nights = Math.ceil(diff/interval);
				}
			}
		}

		if(document.easyFrontendFormular.childs) childs = document.easyFrontendFormular.childs.value;
		if(document.easyFrontendFormular.persons) persons = document.easyFrontendFormular.persons.value;
		if(document.easyFrontendFormular.email) var email = document.easyFrontendFormular.email.value;
		else alert('no email field - correct that');
		if(document.easyFrontendFormular.captcha_value) captcha = document.easyFrontendFormular.captcha_value.value;
		if(document.easyFrontendFormular.thename) var thename = document.easyFrontendFormular.thename.value;
		else alert('no name field - correct that');

		var data = {
			action: 'easyreservations_send_validate',
			security:tsecurity,
			captcha:captcha,
			from:from,
			fromplus:fromplus,
			to:to,
			toplus:toplus,
			nights:nights,
			mode:mode,
			childs:childs,
			persons:persons,
			room: room,
			customs:custom,
			email:email,
			thename:thename
		};
		var the_error_field = 'easy-form-units';
		if(document.getElementById('easy-form-to')) the_error_field = 'easy-form-to';

		if(errornr == 1){
			jQuery.post(easyAjax.ajaxurl , data, function(response) {
				errornr = 0;
				errors = response;
				var warning = ''; var elem  = '';
				if(errors != '' && errors != null && errors != 1){
					errornr++;
					if(mode == 'send' && errors.length > 0) jQuery("#easy-show-error-div").removeClass('hide-it');
					warningli = '';
					for(var i = 0; i < errors.length; i++){
						var field = errors[i];
						i++;
						var error = errors[i];
						if(field == 'date'){
							jQuery('#easy-form-from').addClass('form-error');
							jQuery('#' +the_error_field ).addClass('form-error');
							warning = '<label for="easy-form-to" class="easy-show-error" id="easy-error-field-'+the_error_field+'">'+error+'</label>'
							elem = jQuery('#' +the_error_field ).parent().get(0);
							if(elem && elem.tagName == 'SPAN') jQuery(elem).after(warning);
							else jQuery('#' +the_error_field ).after(warning);
							if(mode == 'send'){
								warningli = '<li><label for="'+the_error_field+'">'+error+'</label></li>'
								document.getElementById('easy-show-error').innerHTML += warningli;
							}
						} else {
							jQuery('#'+field).addClass('form-error');
							warning = '<label for="'+field+'" class="easy-show-error" id="easy-error-field-'+field+'">'+error+'</label>'
							if(mode == 'send'){
								warningli = '<li><label for="'+field+'">'+error+'</label></li>'
								document.getElementById('easy-show-error').innerHTML += warningli;
							}
							if(field == 'easy-form-captcha') field = 'easy-form-captcha-img';
							elem = jQuery('#'+field).parent().get(0);
							if(elem && elem.tagName == 'SPAN') jQuery(elem).after(warning);
							else jQuery('#'+field).after(warning);


						}
					}
				}
				errors_state = 0;
				if(errornr == 0 && mode == 'send') document.getElementById('easyFrontendFormular').submit();
			});
		}
	}
}