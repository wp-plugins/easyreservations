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
			} else if(thetype == 'night' || thetype == 'times'){
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

var easyReservationIDs = new Array();
var easyReservationDatas = new Array();
var easyReservationsPrice = new Array();
var easyReservationEdit = false;
var easyEffectSavere = true;
var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 

function easyOverlayDimm(close){
	if(close && close == 1){
		jQuery('#easyFormOverlay,#easyFormInnerlay').fadeOut("slow", function(){
			jQuery('#easyFormOverlay,#easyFormInnerlay').remove();
		});
	} else {
		if(!document.getElementById('easyFormOverlay')){
			var form = jQuery('#easyFrontendFormular');
			var height = form.css('height');
			var width = form.css('width');
			var bgc = easyGetBackgroundColor(document.getElementById('easyFrontendFormular'));
			if(bgc  && bgc != '') var bgcolor = bgc;
			else var bgcolor = easyReservationAtts['bg'];
			form.before('<div id="easyFormOverlay" class="'+easyReservationAtts['multiple']+' easyloading" style="height:'+height+';width:'+width+';background-color:'+bgcolor+';"></div>');
			jQuery("#easyFormOverlay").fadeIn("slow");
		} else {
			easyOverlayDimm(1);
		}
	}
}

jQuery(document).keyup(function(e){if(e.keyCode == 116)easyCancelSubmit();});
jQuery('#easyFormOverlay').keyup(function(e){if(e.keyCode == 27)easyCancelSubmit();});
window.onbeforeunload = function(){easyCancelSubmit();}

function easyInnerlay(content){
	if(document.getElementById('easyFormOverlay')){
		if(easyReservationEdit) easyEdit(easyReservationDatas.length-1, false);
		var form = jQuery('#easyFrontendFormular');
		var width = form.css('width');
		width = parseFloat(width) - 50;
		if(content == 1){
			content = easyInnerlayTemplate[0].replace(/\\/g, "");
			var reservations = "";
			var allprice = 0;
			var thedatas = easyReservationDatas;
			var theprices = easyReservationsPrice;
			if(window.easyLastPrice && window.easyLastPrice > 0) var easyLastPrice= window.easyLastPrice;
			else var easyLastPrice = 0;
			if(!easyReservationEdit) thedatas.push(jQuery('#easyFrontendFormular').serialize());
			if(!easyReservationEdit) theprices.push(easyLastPrice);
			for(i in thedatas){
				var datas = thedatas[i];
				if(typeof(thedatas[i]) == 'string') datas = easyUnserialize(thedatas[i]);
				if(i == thedatas.length-1) reservations += '<tr class="active">';
				else reservations += '<tr>';
				reservations+='<td>'+datas['from'];
				if(datas['date-from-hour']){
					reservations+= ' '+datas['date-from-hour']+':';
					if(datas['date-from-min']) reservations+= datas['date-from-min'];
					else reservations+= '00';
				}
				if(datas['to']){
					reservations+= '<br>'+datas['to'];
					if(datas['date-to-hour']){
						reservations+= ' '+datas['date-to-hour']+':';
						if(datas['date-to-min']) reservations+= datas['date-to-min'];
						else reservations+= '00';
					} else {
						if(datas['date-from-hour']){
							reservations+= ' '+datas['date-from-hour']+':';
							if(datas['date-from-min']) reservations+= datas['date-from-min'];
							else reservations+= '00';
						}
					}
				}
				var res_info = all_resoures_array[datas['easyroom']]['post_title'];
				reservations+='</td><td>'+res_info+'</td>';
				if(easyReservationAtts['pers'] && easyReservationAtts['pers'] == 1){
					reservations+='<td>'+datas['persons'];
					if(res_info['childs']) reservations+='+'+datas['childs'];
					reservations+='</td>';
				}
				allprice += parseFloat(theprices[i]);
				reservations+='<td>'+easy_money_format(parseFloat(theprices[i]))+'</td>';
				if(i == thedatas.length-1){
					var onclickc = 'easyOverlayDimm(1);';
					var onclicke = 'easyOverlayDimm(1);';
				} else {
					var onclickc = 'easyCancelSubmit('+easyReservationIDs[i]+', this);';
					var onclicke = 'easyEdit('+i+',true);';
				}

				reservations+='<td><img src="'+easyAjax.plugin_url+'/easyreservations/images/edit.png" onclick="'+onclicke+'" title="edit"> <img src="'+easyAjax.plugin_url+'/easyreservations/images/delete.png" onclick="'+onclickc+'" title="cancel"></td>';
				reservations+='</tr>';
			}
			reservations+='<tr><td></td><td></td>';
			if(easyReservationAtts['pers'] && easyReservationAtts['pers'] == 1) reservations+= '<td></td>';
			reservations+='<td style="text-align:right">'+easy_money_format(allprice)+'</td><td></td></tr>';
			thedatas = undefined;
			easyReservationDatas.splice(-1,1);
			easyReservationsPrice.splice(-1,1);
		}
		
		easyEffectSavere = false;
		var thecon = '<div id="easyFormInnerlay" class="'+easyReservationAtts['multiple']+'" style="width:'+width+'px;">'+content+'</div>';
		jQuery('#easyFormOverlay').after(thecon);
		if(reservations) jQuery('#easy_overlay_tbody').html(reservations);
	
		jQuery("#easyFormInnerlay").fadeIn("slow");
		jQuery("#easyFormInnerlay").css("display", "inline-block");
		jQuery('#easyFormOverlay').removeClass('easyloading');
		window.location.hash = 'easyFormInnerlay';
		easyReservationEdit = false;
	}
}

function easyFormSubmit(submit){
	var data = jQuery('#easyFrontendFormular').serialize();
	var thesubmit = '';
	if(submit  && submit == 1){
		jQuery('#easyFormInnerlay').fadeOut("slow", function(){
				jQuery('#easyFormOverlay').addClass('easyloading');
				jQuery('#easyFormInnerlay').remove();
		});	
		thesubmit = '&submit=1';
	}
	data+='&atts[]='+JSON.stringify(easyReservationAtts);
	data+='&ids[]='+JSON.stringify(easyReservationIDs);
	if(easyReservationEdit) data+='&edit='+easyReservationEdit;
	jQuery.post(easyDate.ajaxurl , data+'&action=easyreservations_send_form'+thesubmit, function(response){
		if(response[0] == '['){
			response = JSON.parse(response);
			if(easyReservationEdit){
				for(i in easyReservationIDs){
					if(easyReservationIDs[i] == easyReservationEdit) var thei = i;
				}
				easyReservationDatas[thei] = jQuery('#easyFrontendFormular').serialize();
				easyReservationsPrice[thei] = response[1];
				easyInnerlay(1);
			} else {
				easyReservationIDs.push(response[0]);
				easyReservationDatas.push(data);
				easyReservationsPrice.push(response[1]);
				if(submit  && submit == 1){
					easyReservationIDs = new Array();
					easyReservationDatas = new Array();
					easyReservationsPrice = new Array();
					easyReservationEdit = false;
					easyEffectSavere = true;
					easyInnerlay(response[2]);
				} else if(submit) submit();
			}
			return true;
		} else {
			if(window.easyreservations_send_validate) easyreservations_send_validate();
			easyOverlayDimm(1);
		}
	});
	
}

function easyEdit(i,dimm){
	if(dimm && dimm !== false){
		if(!easyLastPrice) var easyLastPrice = 0;
		easyReservationDatas.push(jQuery('#easyFrontendFormular').serialize());
		easyReservationsPrice.push(easyLastPrice);
		easyReservationEdit = easyReservationIDs[i];
	}
	var data = easyReservationDatas[i];
	if(data){
		data = easyUnserialize(data);
		jQuery.each(data, function (name,value) {
			jQuery("input[name='"+name+"'],select[name='"+name+"']").each(function() {
				switch (this.nodeName.toLowerCase()) {
					case "input":
						switch (this.type) {
							case "radio":
							case "checkbox":
								if (this.value==value) { jQuery(this).click(); }
								break;
							default:
								jQuery(this).val(value);
								break;
						}
						break;
					case "select":
						jQuery("option",this).each(function(){
							if (this.value==value) { this.selected=true; }
						});
						break;
				}
			});
		});
		if(dimm) easyOverlayDimm(1);	
	} else alert('Error #3243 - please report in forum.');
}

function easyCancelSubmit(single,ele){
	if(ele){
		jQuery(ele).closest('tr').fadeOut("slow", function(){jQuery(ele).closest('tr').remove(); });	
	} else {		
		jQuery('#easyFormInnerlay').fadeOut("slow", function(){
			jQuery('#easyFormOverlay').addClass('easyloading');
			jQuery('#easyFormInnerlay').remove();
		});
	}
	var ids = '';
	if(easyReservationIDs && easyReservationIDs.length > 0){
		for(i in easyReservationIDs){
			ids += easyReservationIDs[i]+',';
		}
		if(ids != ''){
			var cancel = '';
			if(single){
				cancel = '&cancel='+single;
			}
			jQuery.post(easyDate.ajaxurl , 'delete='+ids+'&action=easyreservations_send_form'+cancel, function(response){

			});
		}
	}
	easyReservationIDs = new Array();
	easyReservationDatas = new Array();
	easyReservationsPrice = new Array();
	if(!ele) easyOverlayDimm(1);
}

function easyAddAnother(){
	jQuery('#easyFormInnerlay').fadeOut("slow", function(){
		jQuery('#easyFormOverlay').addClass('easyloading');
		jQuery('#easyFormInnerlay').remove();
	});
	easyFormSubmit(easyAddAnotherCallback);
}

function easyAddAnotherCallback(){
	document.getElementById('easyFrontendFormular').reset();
	easyOverlayDimm(1);
}

function easyUnserialize(serializedString){
	var str = decodeURI(serializedString);
	var pairs = str.split('&');
	var obj = {}, p, idx, val;
	for(var i=0, n=pairs.length; i < n; i++) {
		p = pairs[i].split('=');
		idx = p[0];
		if(idx.indexOf("[]") == (idx.length - 2)) {
			// Eh um vetor
			var ind = idx.substring(0, idx.length-2)
			if (obj[ind] === undefined) {
				obj[ind] = [];
			}
			obj[ind].push(easyParseValue(unescape(p[1])) );
		} else {
			obj[idx] = easyParseValue(unescape(p[1]));
		}
	}
	return obj;
}

function easyParseValue(strVal) {
	return ( strVal.match(/^[0-9]+$/) ) ? parseInt(strVal) : (strVal == 'true') ? true : (strVal == 'false') ? false : strVal.replace(/[+]/g, " ")
}

function easyGetBackgroundColor(element){
	element = element.parentNode;
	if(element){
		var bgc = easyrgb2hex(jQuery(element).css('backgroundColor'));
		if(bgc && bgc != '') return bgc;
		else{
			if(element === document.body) return false;
			return easyGetBackgroundColor(element);
		}
	} else return false;
}

function easyrgb2hex(rgb){
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	if(rgb) return "#" + easyhex(rgb[1]) + easyhex(rgb[2]) + easyhex(rgb[3]);
	else return false;
}

function easyhex(x){
	return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}

function easy_money_format(amount){
	var currency_settings = easyDate['currency'];
	if(currency_settings instanceof Array || currency_settings instanceof Object) var bla = 1;
	else {
		var sign = currency_settings;
		currency_settings = [];currency_settings['sign'] = sign;currency_settings['divider1'] = '.';currency_settings['divider2'] = ',';currency_settings['decimal'] = 1;currency_settings['place'] = 0;currency_settings['whitespace'] = 1;
	}
	var add = '';
	if(amount < 0){
		amount = amount.substr(1);
		add = '-';
	}
	amount = parseFloat(amount);
	var dig = 0;
	if(currency_settings['decimal'] == 1) dig = 2;
	var money = add+easy_number_format(amount, dig, currency_settings['divider2'],currency_settings['divider1']);
	var white = '';
	if(currency_settings['whitespace'] == 1) white = ' ';
	if(currency_settings['place'] == 1){
		return money+white+'&'+currency_settings['sign']+';';
	} else return '&'+currency_settings['sign']+';'+white+money;
}

function easy_number_format(number, decimals, dec_point, thousands_sep){
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function(n, prec){
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if(s[0].length > 3) s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	if((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}