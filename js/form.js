function easyreservations_build_datepicker(){
	var dateformatse = 'dd.mm.yy';
	if(easyDate.easydateformat == 'Y/m/d') dateformatse = 'yy/mm/dd';
	else if(easyDate.easydateformat == 'm/d/Y') dateformatse = 'mm/dd/yy';
	else if(easyDate.easydateformat == 'Y-m-d') dateformatse = 'yy-mm-dd';
	else if(easyDate.easydateformat == 'd-m-Y') dateformatse = 'dd-mm-yy';
	else if(easyDate.easydateformat == 'd.m.Y') dateformatse = 'dd.mm.yy';

	var dates = jQuery( "#easy-form-from, #easy-form-to" ).datepicker({
		dateFormat: dateformatse,
		minDate: 0,
		beforeShowDay: function(date){
			if(window.easydisabledays && document.easyFrontendFormular.easyroom){
				return easydisabledays(date,document.easyFrontendFormular.easyroom.value);
			} else {
				return [true];
			}
		},
		firstDay: 1,
		onSelect: function( selectedDate ) {
			if(this.id == 'easy-form-from'){
				var option = this.id == "easy-form-from" ? "minDate" : "maxDate",
				instance = jQuery( this ).data( "datepicker" ),
				date = jQuery.datepicker.parseDate( instance.settings.dateFormat ||	jQuery.datepicker._defaults.dateFormat,	selectedDate, instance.settings );
				date.setDate(date.getDate());
				dates.not( this ).datepicker( "option", option, date );
			}
			if(window.easyreservations_send_validate) easyreservations_send_validate();
			if(window.easyreservations_send_price) easyreservations_send_price();		
		}
	});
}

function fakeIfStatements(fieldprice, persons, childs, nights, room){
	var myregexp = /;/i;
	var match = myregexp.exec(fieldprice);
	if(match != null){
		fieldpriceexplode = fieldprice.split(/-(?=[^\}]*(?:\{|$))/);
		thetype = fieldpriceexplode[1];
		explstatements = fieldpriceexplode[0].split(/;(?=[^\}]*(?:\{|$))/);
		for (var i in explstatements){
			explif = explstatements[i].split(/\>(?=[^\}]*(?:\{|$))/);
			if(thetype == 'pers'){
				if((parseFloat(persons)+parseFloat(childs)) >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			} else if(thetype == 'child'){
				if(childs >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				} 
			} else if(thetype == 'res'){
				if(room == parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				} 
			} else if(thetype == 'both'){
				if(((parseFloat(persons)+parseFloat(childs))*nights) >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			} else if(thetype == 'adul'){
				if(persons >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			} else if(thetype == 'night'){
				if(nights >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			}
		}
		return fieldprice
	}
	return false;
}