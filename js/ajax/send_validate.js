function easyreservations_send_validate(){

	var error = 0;
	var customPrices = '';
	jQuery("#showError").html('');

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
	else error = 'depature date';

	var roomfield = document.easyFrontendFormular.room;
	if(roomfield) var room = roomfield.value;
	else error =  'room';

	var offerfield = document.easyFrontendFormular.offer;
	if(offerfield) var offer = offerfield.value;
	else var offer = 0;

	var childsfield = document.easyFrontendFormular.offer;
	if(childsfield) var childs = childsfield.value;
	else var childs = 0;

	var personsfield = document.easyFrontendFormular.persons;
	if(personsfield){
		personsfield.style.borderColor = '#DDDDDD';
		var persons = personsfield.value;
	}
	else var persons = 0;

	var emailfield = document.easyFrontendFormular.email;
	if(emailfield){
		emailfield.style.borderColor = '#DDDDDD';
		var email = emailfield.value;
	}
	else var email = 'f.e.r.y@web.de';

	var thenamefield = document.easyFrontendFormular.thename;
	if(thenamefield){
		thenamefield.style.borderColor = '#DDDDDD';
		var thename = thenamefield.value;
	}
	else var thename = 'f.e.r.y@web.de';
		

	var data = {
		action: 'easyreservations_send_validate',
		security:tsecurity,
		from:from,
		to:to,
		childs:childs,
		persons:persons,
		room: room,
		offer: offer,
		email:email,
		thename:thename
	};
	
	if(error == 0){
		jQuery.post(easyAjax.ajaxurl , data, function(response) {
			//jQuery("#showError").html(response);
			errors = response;
			if(errors != '' && errors != null){
				for(var i = 0; i < errors.length; i++){
					var field = errors[i];
					i++;
					var error = errors[i];
					if(field == 'date'){
						document.getElementById('easy-form-from').style.border = '#E80000';
						document.getElementById('easy-form-to').style.border= '#E80000';
						jQuery("#showError").html(error);
					} else document.getElementById(field).style.border = '#E80000';
				}
			}
		});
	}
}
