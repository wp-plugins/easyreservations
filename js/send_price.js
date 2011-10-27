function isset(varname) {
if(typeof( window[ varname ] ) != "undefined") return true;
else return false;
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
        alert(
          "Generating XMLHttpRequest-Obj not possible");
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
	var url = document.getElementById('urlPrice').src;
	var customPrices = '';

	for(var i = 0; i < 16; i++){
		if(document.getElementById('custom_price'+i)){
			var Element = document.easyFrontendFormular.custom_price+i;
			var Type = Element.type;
			if(Type == "select-one"){
				customPrices += 'testPrice%:%' + Element.value + '%;%';
			} else if(Type == "radio" &&  Element.checked != undefined){
				//for(var i = 0; i < Element.length; i++) {
				alert(Element.length);
					if(Element[i].checked) {
						customPrices += 'testPrice%:%' + Element[i].value + '%;%';
						alert('aaa');
					}
				}
			/*}*/
		}
	}
	
	if(customPrices!=''){
		var customAdd = '&customp='+ customPrices;
	} else {
		var customAdd = '';
	}

alert(customAdd);
	if(document.easyFrontendFormular.from.value != "" && document.easyFrontendFormular.to.value != ""){
		resObjektTwo.open('post', url.replace("send_price.js", "") + 'send_price.php' ,true);
		resObjektTwo.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		resObjektTwo.onreadystatechange = handleResponsePrice;
		if(document.easyFrontendFormular.childs === undefined) var childs = '';
		else  var childs = '&childs=' + document.easyFrontendFormular.childs.value;

		resObjektTwo.send('from=' + escape(document.easyFrontendFormular.from.value) + '&to=' + escape(document.easyFrontendFormular.to.value) + '&room=' + escape(document.easyFrontendFormular.room.value) + '&persons=' + escape(document.easyFrontendFormular.persons.value) + '&email=' + escape(document.easyFrontendFormular.email.value) + '&offer=' + escape(document.easyFrontendFormular.specialoffer.value) + childs + customAdd);
		document.getElementById("showPrice").innerHTML = '<img style="vertical-align:text-bottom;" src="' + url.replace("js/send_price.js", "") + 'images/loading.gif">';
	} else {
		document.getElementById("showPrice").style.visibility = "hidden";
	}
}

function handleResponsePrice() {
	var text="";
  document.getElementById("showPrice").style.visibility = "visible";
  if(resObjektTwo.readyState == 4){
  	text=resObjektTwo.responseText;
    document.getElementById("showPrice").innerHTML = text;
  }
}