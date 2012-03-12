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
        alert("AJAX error");
      }
    }
  }
  return resObjekt;
}
function generateAJAXObjekt(){
  this.generateXMLHttpReqObj = generateXMLHttpReqObj;
}
iol = new generateAJAXObjekt();
resObjekt = iol.generateXMLHttpReqObj();
var cal_save = 0;
function easyRes_sendReq_Calendar() {
	if(document.getElementById('urlCalendar').type == "hidden") var url = document.getElementById('urlCalendar').value;
	else var url = document.getElementById('urlCalendar').src;

		if(document.CalendarFormular.date.value !="" && cal_save == 0){
			cal_save = 1;
			resObjekt.open('post', url.replace("send_calendar.js", "") + 'send_calendar.php' ,true);
			resObjekt.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			resObjekt.onreadystatechange = handleResponseCal;
			
			if(document.CalendarFormular.childs === undefined) var childs = '';
			else var childs = '&childs=' + document.CalendarFormular.childs.value;
			if(document.CalendarFormular.persons === undefined) var persons = '';
			else var persons = '&persons=' + document.CalendarFormular.persons.value;
			if(document.CalendarFormular.reservated === undefined) var reservated = '';
			else var reservated = '&reservated=' + document.CalendarFormular.reservated.value;
			resObjekt.send('date=' + escape(document.CalendarFormular.date.value) + '&room=' + escape(document.CalendarFormular.room.value) +  '&offer=' + escape(document.CalendarFormular.offer.value) + '&size=' + escape(document.CalendarFormular.size.value) + childs + persons + reservated);
		}
}

function handleResponseCal() {
	var text="";
  if(resObjekt.readyState == 4){
  	text=resObjekt.responseText;
	cal_save = 0;
    document.getElementById("showCalender").innerHTML = text;
  }
}