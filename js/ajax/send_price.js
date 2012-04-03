function easyreservations_send_price(){

	var error = 0;
	var customPrices = '';

	var loading = '<img style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading.gif">';
	jQuery("#showPrice").html(loading);

	var tsecurity = document.easyFrontendFormular.pricenonce.value;

	var fromfield = document.easyFrontendFormular.from;
	if(fromfield) var from = fromfield.value;
	else error = 'arrival date';
	
	var tofield = document.easyFrontendFormular.to;
	if(tofield) var to = tofield.value;
	else error = 'depature date';
	
	if(from && to){ 
		instance = jQuery( fromfield ).data( "datepicker" );
		if(instance){
			dateanf = jQuery.datepicker.parseDate(
				instance.settings.dateFormat ||
				jQuery.datepicker._defaults.dateFormat,
				from, instance.settings );
			instance = jQuery( tofield ).data( "datepicker" );
			dateend = jQuery.datepicker.parseDate(
				instance.settings.dateFormat ||
				jQuery.datepicker._defaults.dateFormat,
				to, instance.settings );
			var one_day = 1000 * 60 * 60 * 24;
			var difference_ms = Math.abs(dateanf - dateend);
			var nights = Math.round(difference_ms/one_day);
		} else var nights = 1;
	} else var nights = 1;

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
	if(personsfield) var persons = personsfield.value;
	else var persons = 1;

	var emailfield = document.easyFrontendFormular.email;
	if(emailfield) var email = emailfield.value;
	else var email = 'f.e.r.y@web.de';
	
	for(var i = 0; i < 16; i++){
		if(document.getElementById('custom_price'+i)){
			var Element = document.getElementById('custom_price'+i);
			var Type = Element.type;
			if(Type == "select-one"){
				var normalprice = Element.value;
				explodenormalprice = normalprice.split(':');
				if(Element.className == 'pp') price = explodenormalprice[1] * persons;
				else if(Element.className == 'pn') price = explodenormalprice[1] * nights;
				else price = explodenormalprice[1] ;
				customPrices += 'testPrice!:!' + explodenormalprice[0] + ':' + price+ '!;!';
			} else if(Type == "radio" &&  Element.checked != undefined){
				var normalprice = getRadioCheckedValue('custom_price'+i);
				explodenormalprice = normalprice.split(':');
				if(Element.className == 'pp') price = explodenormalprice[1] * persons;
				else if(Element.className == 'pn') price = explodenormalprice[1] * nights;
				else price = explodenormalprice[1] ;
				customPrices += 'testPrice!:!' + explodenormalprice[0] + ':' + price+ '!;!';
			} else if(Type == "checkbox" &&  Element.checked){
				var normalprice = Element.value;
				explodenormalprice = normalprice.split(':');
				if(Element.className == 'pp') price = explodenormalprice[1] * persons;
				else if(Element.className == 'pn') price = explodenormalprice[1] * nights;
				else price = explodenormalprice[1];
				customPrices += 'testPrice!:!' + explodenormalprice[0] + ':' + price+ '!;!';
			} else if(Type == "hidden"){
				var normalprice = Element.value;
				explodenormalprice = normalprice.split(':');
				if(Element.className == 'pp') price = explodenormalprice[1] * persons;
				else if(Element.className == 'pn') price = explodenormalprice[1] * nights;
				else price = explodenormalprice[1] ;
				customPrices += 'testPrice!:!' + explodenormalprice[0] + ':' + price+ '!;!';
			}
		}
	}

	var data = {
		action: 'easyreservations_send_price',
		security:tsecurity,
		from:from,
		to:to,
		childs:childs,
		persons:persons,
		room: room,
		offer: offer,
		email:email,
		customp:customPrices
	};
	
	if(error == 0){
		jQuery.post(easyAjax.ajaxurl , data, function(response) {
			jQuery("#showPrice").html(response);
			return false;
		});
	}
}

function getRadioCheckedValue(radio_name){
	var oRadio = document.forms['easyFrontendFormular'].elements[radio_name];
	for(var i = 0; i < oRadio.length; i++){
		if(oRadio[i].checked){
			return oRadio[i].value;
		}
	}
	return '';
}