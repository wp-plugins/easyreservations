var errors_state = 0;
var easyErrorEffectSave = true;
function easyreservations_send_validate(y){
	if(errors_state == 0){
		errors_state = 1;
		easyErrorEffectSave = true;
		var errornr = 1; var custom = '';
		if(y == "send") easyOverlayDimm(0);
		jQuery("#easy-show-error-div").addClass('hide-it');
		//jQuery(".easyFrontendFormular .easy-button").addClass('deactive1').attr('disabled', 'disabled');
		jQuery("[id^='easy-custom-req-']").each ( function (i){
			if(custom.indexOf(this.id+',') >= 0) return;
			if(this.value == '') custom +=	this.id + ',';
			else if(this.type == 'checkbox' && this.checked == false) custom +=	this.id + ',';
			else if( this.type == 'radio' && this.checked == false) custom += this.id + ',';
		});
		jQuery("[id^='easy-form-'],[id^='easy-custom-']").removeClass('form-error');
		jQuery("label[id^='easy-error-field-']").fadeOut("slow", function(){
			jQuery(this).remove();
		});
		document.getElementById('easy-show-error').innerHTML = '';
		if(document.easyFrontendFormular.easyroom) var room = document.easyFrontendFormular.easyroom.value;
		else alert('no room field - correct that')
		var interval_array = eval("(" + easyAjax.interval + ")");
		var interval = interval_array[room];
		var nights = 1; var to = ''; var toplus = 0; var fromplus = 0; var childs = 0; var persons = 1; var captcha = 'x!'; var captcha_prefix = ''; var tom = 0; var toh = 12*60; var fromm = 0; var fromh = 12*60; var theid = '';
		if(y) var mode = y;
		else mode = 'normal';
		var tsecurity = document.easyFrontendFormular.pricenonce.value;
		var fromfield = document.easyFrontendFormular.from;
		if(fromfield){
			fromfield.style.borderColor = '#DDDDDD';
			var from = fromfield.value;
			if(document.getElementById('date-from-hour')) fromh = parseInt(document.getElementById('date-from-hour').value) * 60;
			if(document.getElementById('date-from-min')) fromm = parseInt(document.getElementById('date-from-min').value);
			fromplus = (fromh + fromm)*60;
		} else alert('no arrival field - correct that');

		var tofield = document.easyFrontendFormular.to;
		if(tofield){
			tofield.style.borderColor = '#DDDDDD';
			to = tofield.value;
		} else {
			if(document.easyFrontendFormular.nights) nights = document.easyFrontendFormular.nights.value;
		}
		if(document.getElementById('date-to-hour')) toh = parseInt(document.getElementById('date-to-hour').value) * 60;
		if(document.getElementById('date-to-min')) tom = parseInt(document.getElementById('date-to-min').value);
		toplus = (toh + tom)*60;
		
		if(from){
			instance = jQuery( fromfield ).data( "datepicker" );
			if(instance && tofield){
				dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, from, instance.settings );
				dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, to, instance.settings );
				var difference_ms = Math.abs(dateanf - dateend);
				var diff = difference_ms/1000;
				diff += toplus;
				diff -= fromplus;
				nights = Math.ceil(diff/interval);
			}
		}

		if(document.easyFrontendFormular.childs) childs = document.easyFrontendFormular.childs.value;
		if(document.easyFrontendFormular.persons) persons = document.easyFrontendFormular.persons.value;
		if(document.easyFrontendFormular.email) var email = document.easyFrontendFormular.email.value;
		else alert('no email field - correct that');
		if(document.easyFrontendFormular.captcha_value) captcha = document.easyFrontendFormular.captcha_value.value;
		if(document.easyFrontendFormular.captcha_prefix) captcha_prefix = document.easyFrontendFormular.captcha_prefix.value;
		if(document.easyFrontendFormular.thename) var thename = document.easyFrontendFormular.thename.value;
		else alert('no name field - correct that');
		if(document.easyFrontendFormular.editID) theid = document.easyFrontendFormular.editID.value;

		var data = {
			action: 'easyreservations_send_validate',
			security:tsecurity,
			id:theid,
			captcha:captcha,
			captcha_prefix:captcha_prefix,
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
				easyErrorEffectSave = false;
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
							if(document.getElementById(the_error_field )) elem = jQuery('#' +the_error_field ).parent().get(0);
							else elem = jQuery('#easy-form-from').parent().get(0);
							if(elem && elem.tagName == 'SPAN'){
								jQuery(elem).after(warning);
							} else {
								if(document.getElementById(the_error_field )){
									jQuery('#' +the_error_field ).after(warning);
								} 
								else jQuery('#easy-form-from').after(warning);
							}
							if(mode == 'send'){
								warningli = '<li><label for="'+the_error_field+'">'+error+'</label></li>'
								document.getElementById('easy-show-error').innerHTML += warningli;
							}
						} else {
							jQuery('#'+field + ':last').addClass('form-error');
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
						jQuery("label[class=easy-show-error]").hide();
						jQuery("label[class=easy-show-error]").fadeIn("slow");
					}
				} else {
                    //jQuery(".easyFrontendFormular .easy-button").removeClass('deactive1').removeAttr('disabled');
                }

				errors_state = 0;
				if(errornr == 0 && mode == 'send'){
					jQuery('*[id^="custom_price"]:checked,select[id^="custom_price"],input[id^="custom_price"][type="radio"]').each(function(){
						var price = 0;
						var addprice = this;
						var Type = this.type;

						if(Type == "select-one"){
							explodenormalprice = this.value.split(':');
							addprice = jQuery(this).find('option:selected');
						} else if(Type == "radio" &&  this.checked != undefined && this.checked){
							explodenormalprice = this.value.split(':');
						} else if(Type == "checkbox" &&  this.checked){
							explodenormalprice = this.value.split(':');
						} else if(Type == "hidden"){
							explodenormalprice = this.value.split(':');
						} else return;
						fieldprice = explodenormalprice[1];
						fieldprice = fakeIfStatements(fieldprice, persons, childs, nights, room);
						if(fieldprice !== false){
							if(!isNaN(parseFloat(fieldprice)) && isFinite(fieldprice)){
								var classname = '';
								if(this.className && this.className != '') classname = ':'+this.className;
								jQuery(addprice).attr('value', explodenormalprice[0]+':'+fieldprice+classname);
							}
						}
					});
					if(easyReservationAtts['multiple'] == 0){
						document.getElementById('easyFrontendFormular').submit();
					} else {
						if(easyReservationEdit) easyFormSubmit();
						else easyInnerlay(1);
					}
				} else easyOverlayDimm(1);
			});
		}
	}
}