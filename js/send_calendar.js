function generateXMLHttpReqObj(){
  var resObjekt = null;
  try {
    resObjekt = new ActiveXObject("Microsoft.XMLHTTP");
  }
  catch(Error){
    try {
      resObjekt = new ActiveXObject("MSXML2.XMLHTTP");
    }
    catch(Error){
      try {
      resObjekt = new XMLHttpRequest();
      }
      catch(Error){
        alert(
          "Generating XMLHttpRequest-Obj not possible");
      }
    }
  }
  return resObjekt;
}
function generateAJAXObjekt(){
  this.generateXMLHttpReqObj = generateXMLHttpReqObj;
}
xx = new generateAJAXObjekt();
resObjekt = xx.generateXMLHttpReqObj();

function easyRes_sendReq_Calendar() {
	var url = document.getElementById('urlCalendar').src;

		if(document.formular.date.value !=""){
			resObjekt.open('post', url.replace("send_calendar.js", "") + 'send_calendar.php' ,true);
			resObjekt.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			resObjekt.onreadystatechange = handleResponseCal;
			resObjekt.send('date=' + escape(document.formular.date.value) + '&room=' + escape(document.formular.room.value) +  '&offer=' + escape(document.formular.offer.value) + '&size=' + escape(document.formular.size.value));
		} else {
			document.getElementById("showCalender").style.visibility = "hidden";
		}
}

function handleResponseCal() {
	var text="";
  document.getElementById("showCalender").style.visibility = "visible";
  if(resObjekt.readyState == 4){
  	text=resObjekt.responseText;
    document.getElementById("showCalender").innerHTML = text;
  }
}