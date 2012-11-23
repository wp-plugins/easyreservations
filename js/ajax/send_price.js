window.easyLastPrice = 0;
function easyreservations_send_price(){
	if(document.easyFrontendFormular.easyroom) var room = document.easyFrontendFormular.easyroom.value;
	else alert('no room field - correct that')
	var interval_array = eval("(" + easyAjax.interval + ")");
	var interval = interval_array[room];
	jQuery(".easyFrontendFormular .easy-button").addClass('deactive2');
	var error = 0;var addprice = 0;var price = 0;var nights = 1;var childs = 0;var persons = 1;var to = '';var toplus = 0;var fromplus = 0;var captcha = 'x!';var tom = 0;var toh = 12*60; var fromm = 0; var fromh = 12*60; coupon = '';
	var customPrices = 0;
	jQuery("#showPrice").html('<img style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading.gif">');

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
		var nightsfield = document.easyFrontendFormular.nights;
		if(nightsfield){
			nights = nightsfield.value;
		}
	}
	if(document.getElementById('date-to-hour')) toh = parseInt(document.getElementById('date-to-hour').value) * 60;
	if(document.getElementById('date-to-min')) tom = parseInt(document.getElementById('date-to-min').value);
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
	var allpersons = parseFloat(persons)+parseFloat(childs);
	if(document.easyFrontendFormular.email) var email = document.easyFrontendFormular.email.value;
	else alert('no email field - correct that');
	if(document.easyFrontendFormular.captcha_value) captcha = document.easyFrontendFormular.captcha_value.value;
	if(document.easyFrontendFormular.thename) var thename = document.easyFrontendFormular.thename.value;
	else alert('no name field - correct that');
	if(document.easyFrontendFormular.coupon) var coupon = document.easyFrontendFormular.coupon.value;
	
	jQuery('[id^="custom_price"]:checked,select[id^="custom_price"]').each(function(){
		var price = 0;
		var addprice = 0;
		var Type = this.type;
		if(Type == "select-one"){
			explodenormalprice = this.value.split(':');
			addprice = 1;
		} else if(Type == "radio" &&  this.checked != undefined && this.checked){
			explodenormalprice = this.value.split(':');
			addprice = 1;
		} else if(Type == "checkbox" &&  this.checked){
			explodenormalprice = this.value.split(':');
			addprice = 1;
		} else if(Type == "hidden"){
			explodenormalprice = this.value.split(':');
			addprice = 1;
		}
		if(addprice == 1){
			fieldprice = explodenormalprice[1];
			fieldprice = fakeIfStatements(fieldprice, persons, childs, nights, room);
			if(fieldprice === false) fieldprice = explodenormalprice[1];
			if(this.className == 'pp'){
				price = fieldprice * allpersons;
			} else if(this.className == 'pa'){
				price = fieldprice * persons;
			} else if(this.className == 'pc'){
				price = fieldprice * childs;
			} else if(this.className == 'pn'){
				price = fieldprice * nights;
			} else if(this.className == 'pb'){
				price = fieldprice * allpersons * nights;
			} else {
				price = fieldprice;
			}
			if(!isNaN(parseFloat(price)) && isFinite(price)) customPrices += parseFloat(price);
		}
	});

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
			jQuery(".easyFrontendFormular .easy-button").removeClass('deactive2');
			response = JSON.parse(response);
			jQuery("#showPrice").html(response[0]);
			window.easyLastPrice = parseFloat(response[1]);
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