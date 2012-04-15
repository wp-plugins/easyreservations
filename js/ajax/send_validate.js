function easyreservations_send_validate(y){

	var errornr = 1;
	var custom = '';
	jQuery("#easy-show-error-div").addClass('hide-it');
	//jQuery("div[id$='-error']").html('');
	jQuery("[id^='easy-custom-req-']").each ( function (i) { 
		if(this.value == '') custom +=	this.id + ',';
    });
	jQuery("[id^='easy-form-'],[id^='easy-custom-']").removeClass('form-error');
	jQuery("label[id^='easy-error-field-']").remove();
	document.getElementById('easy-show-error').innerHTML = '';

	if(y) var mode = y;
	else mode = 'normal';

	var tsecurity = document.easyFrontendFormular.pricenonce.value;

	var fromfield = document.easyFrontendFormular.from;
	if(fromfield){
		fromfield.style.borderColor = '#DDDDDD';
		var from = fromfield.value;
	}
	else error = 'arrival date';
	
	var tofield = document.easyFrontendFormular.to;
	if(tofield){
		tofield.style.borderColor = '#DDDDDD';
		var to = tofield.value;
	}
	else to = from + 86400;

	var roomfield = document.easyFrontendFormular.room;
	if(roomfield) var room = roomfield.value;
	else error =  'room';

	var offerfield = document.easyFrontendFormular.offer;
	if(offerfield) var offer = offerfield.value;
	else var offer = 0;

	var childsfield = document.easyFrontendFormular.childs;
	if(childsfield) var childs = childsfield.value;
	else var childs = 0;

	var personsfield = document.easyFrontendFormular.persons;
	if(personsfield){
		var persons = personsfield.value;
	}
	else var persons = 0;

	var emailfield = document.easyFrontendFormular.email;
	if(emailfield){
		var email = emailfield.value;
	} else var email = 'test@test.com';

	var thenamefield = document.easyFrontendFormular.thename;
	if(thenamefield){
		var thename = thenamefield.value;
	} else var thename = 'testuser';

	var captchavalue = document.easyFrontendFormular.captcha_value;
	if(captchavalue) var captcha = captchavalue.value;
	else var captcha = 'x!';

	var data = {
		action: 'easyreservations_send_validate',
		security:tsecurity,
		captcha:captcha,
		from:from,
		to:to,
		mode:mode,
		childs:childs,
		persons:persons,
		room: room,
		customs:custom,
		offer: offer,
		email:email,
		thename:thename
	};

	if(errornr == 1){
		jQuery.post(easyAjax.ajaxurl , data, function(response) {
			errornr = 0;
			errors = response;
			var warning = '';
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
						jQuery('#easy-form-to').addClass('form-error');
						warning = '<label for="easy-form-to" class="easy-show-error" id="easy-error-field-'+field+'">'+error+'</label>'
						jQuery('#easy-form-to').after(warning);
						if(mode == 'send'){
							warningli = '<li><label for="easy-form-to">'+error+'</label></li>'
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
						jQuery('#'+field).after(warning);

					}
				}
			}
			if(errornr == 0 && mode == 'send') document.getElementById('easyFrontendFormular').submit();
		});
	}
}