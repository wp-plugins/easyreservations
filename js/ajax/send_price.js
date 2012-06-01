function easyreservations_send_price(){
	if(document.easyFrontendFormular.room) var room = document.easyFrontendFormular.room.value;
	else alert('no room field - correct that')
	var interval_array = eval("(" + easyAjax.interval + ")");
	var interval = interval_array[room];
	var error = 0; var addprice = 0; var price = 0; var nights = 1; var childs = 0;	var persons = 1; var to = ''; var toplus = 0; var fromplus = 0; var captcha = 'x!'; var tom = 0; var toh = 12; var fromm = 0; var fromh = 12; coupon = '';
	var customPrices = '';

	var loading = '<img style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading.gif">';
	jQuery("#showPrice").html(loading);

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
		var nightsfield = document.easyFrontendFormular.nights;
		if(nightsfield){
			nights = nightsfield.value;
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
	if(document.easyFrontendFormular.coupon) var coupon = document.easyFrontendFormular.coupon.value;
	for(var i = 0; i < 16; i++){
		addprice = 0;
		if(document.getElementById('custom_price'+i)){
			var Element = document.getElementById('custom_price'+i);
			var Type = Element.type;
			if(Type == "select-one"){
				var normalprice = Element.value;
				explodenormalprice = normalprice.split(':');
				addprice = 1;
			} else if(Type == "radio" &&  Element.checked != undefined){
				var normalprice = getRadioCheckedValue('custom_price'+i);
				explodenormalprice = normalprice.split(':');
				addprice = 1;
			} else if(Type == "checkbox" &&  Element.checked){
				var normalprice = Element.value;
				explodenormalprice = normalprice.split(':');
				addprice = 1;
			} else if(Type == "hidden"){
				var normalprice = Element.value;
				explodenormalprice = normalprice.split(':');
				addprice = 1;
			}
			if(addprice == 1){
				fieldprice = explodenormalprice[1];
//				var myregexp = /^[\(]([0-9]+[\,]?)+[\)]$/i;var match = myregexp.exec(fieldprice); if(match != null) {}
				if(Element.className == 'pp') price = fieldprice * persons;
				else if(Element.className == 'pn') price = fieldprice * nights;
				else if(Element.className == 'pb') price = fieldprice * persons * nights;
				else price = fieldprice;
				customPrices += 'testPrice!:!' + explodenormalprice[0] + ':' + price+ '!;!';
			}
		}
	}

	var data = {
		action: 'easyreservations_send_price',
		security:tsecurity,
		from:from,
		fromplus:fromplus,
		to:to,
		toplus:toplus,
		nights:nights,
		childs:childs,
		persons:persons,
		room: room,
		email:email,
		coupon:coupon,
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