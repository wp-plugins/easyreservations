function getRadioCheckedValue(radio_name)
{
   var oRadio = document.forms['easyFrontendFormular'].elements[radio_name];
   for(var i = 0; i < oRadio.length; i++)
   {
      if(oRadio[i].checked)
      {
         return oRadio[i].value;
      }
   }
   return '';
}

function generateXMLHttpReqObjTwo(){
  var resObjektTwo = null;
  try {
    resObjektTwo = new ActiveXObject("Microsoft.XMLHTTP");
  }
  catch(Error){
    try {
      resObjektTwo = new ActiveXObject("MSXML2.XMLHTTP");
    }
    catch(Error){
      try {
      resObjektTwo = new XMLHttpRequest();
      }
      catch(Error){
        alert("AJAX error");
      }
    }
  }
  return resObjektTwo;
}
function generateAJAXObjektTwo(){
  this.generateXMLHttpReqObjTwo = generateXMLHttpReqObjTwo;
}
xxx = new generateAJAXObjektTwo();
resObjektTwo = xxx.generateXMLHttpReqObjTwo();

function easyRes_sendReq_Price() {
	if(document.getElementById('urlPrice').type == "hidden") var url = document.getElementById('urlPrice').value;
	else var url = document.getElementById('urlPrice').src;
	var customPrices = '';

	if(document.easyFrontendFormular.from.value != "" && document.easyFrontendFormular.to.value != ""){
		resObjektTwo.open('post', url.replace("send_price.js", "") + 'send_price.php' ,true);
		resObjektTwo.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		resObjektTwo.onreadystatechange = handleResponsePrice;
		if(document.easyFrontendFormular.childs === undefined) var childs = '';
		else var childs = '&childs=' + document.easyFrontendFormular.childs.value;


		for(var i = 0; i < 16; i++){
			if(document.getElementById('custom_price'+i)){
				var Element = document.getElementById('custom_price'+i);
				var Type = Element.type;
				if(Type == "select-one"){
					customPrices += 'testPrice!:!' + Element.value + '!;!';
				} else if(Type == "radio" &&  Element.checked != undefined){
					var sel1 = 'custom_price'+i;
					customPrices += 'testPrice!:!' + getRadioCheckedValue('custom_price'+i) + '!;!';
				} else if(Type == "checkbox" &&  Element.checked){
					customPrices += 'testPrice!:!' + Element.value + '!;!';
				}
			}
		}

		if(customPrices!=''){
			var customAdd = '&customp='+ customPrices;
		} else {
			var customAdd = '';
		}

		resObjektTwo.send('from=' + escape(document.easyFrontendFormular.from.value) + '&to=' + escape(document.easyFrontendFormular.to.value) + '&room=' + escape(document.easyFrontendFormular.room.value) + '&persons=' + escape(document.easyFrontendFormular.persons.value) + '&email=' + escape(document.easyFrontendFormular.email.value) + '&offer=' + escape(document.easyFrontendFormular.offer.value) + childs + customAdd);
		document.getElementById("showPrice").innerHTML = '<img style="vertical-align:text-bottom;" src="' + url.replace("js/ajax/send_price.js", "") + 'images/loading.gif">';
	}
}

function handleResponsePrice() {
	var text="";
  if(resObjektTwo.readyState == 4){
  	text=resObjektTwo.responseText;
    document.getElementById("showPrice").innerHTML = text;
  }
}