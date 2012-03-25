function easyreservations_send_validate(){

	var error = 0;
	var customPrices = '';

		var loading = '<img style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading.gif">';
		jQuery("#showError").html(loading);

		var tsecurity = document.easyFrontendFormular.pricenonce.value;

		var fromfield = document.easyFrontendFormular.from;
		if(fromfield) var from = fromfield.value;
		else error = 'arrival date';
		
		var tofield = document.easyFrontendFormular.to;
		if(tofield) var to = tofield.value;
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
		if(personsfield) var persons = personsfield.value;
		else var persons = 0;

		var emailfield = document.easyFrontendFormular.email;
		if(emailfield) var email = emailfield.value;
		else var email = 'f.e.r.y@web.de';

		var thenamefield = document.easyFrontendFormular.thename;
		if(thenamefield) var thename = thenamefield.value;
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
			jQuery("#showError").html(response);
			return false;
		});
	}
}
