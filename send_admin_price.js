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
		resObjektTwo.open('post', url.replace("send_admin_price.js", "") + 'send_price.php' ,true);
		resObjektTwo.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		resObjektTwo.onreadystatechange = handleResponsePrice;
		resObjektTwo.send('from=' + escape(document.editreservation.date.value) + '&to=' + escape(document.editreservation.dateend.value) + '&room=' + escape(document.editreservation.room.value) + '&persons=' + escape(document.editreservation.persons.value) + '&childs=' + escape(document.editreservation.childs.value) + '&email=' + escape(document.editreservation.email.value) + '&offer=' + escape(document.editreservation.specialoffer.value));
		document.getElementById("showPrice").innerHTML = '<img style="vertical-align:text-bottom;" src="' + url.replace("js/send_admin_price.js", "") + 'images/loading.gif">';
}

function handleResponsePrice() {
	var text="";
  document.getElementById("showPrice").style.visibility = "visible";
  if(resObjektTwo.readyState == 4){
  	text=resObjektTwo.responseText;
    document.getElementById("showPrice").innerHTML = text;
  }
}