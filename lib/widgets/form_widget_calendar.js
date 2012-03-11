function generateXMLHttpReqWidgetObj(){
  var resWidgetObjekt = null;
  try {
    resWidgetObjekt = new ActiveXObject("Microsoft.XMLHTTP");
  }
  catch(Error){
    try {
      resWidgetObjekt = new ActiveXObject("MSXML2.XMLHTTP");
    }
    catch(Error){
      try {
      resWidgetObjekt = new XMLHttpRequest();
      }
      catch(Error){
        alert(
          "Generating XMLHttpRequest-Obj not possible");
      }
    }
  }
  return resWidgetObjekt;
}
function generateAJAXWidgetObjekt(){
  this.generateXMLHttpReqWidgetObj = generateXMLHttpReqWidgetObj;
}
xyxxy = new generateAJAXWidgetObjekt();
resWidgetObjekt = xyxxy.generateXMLHttpReqWidgetObj();

function easyRes_sendReq_widget_Calendar() {
	var url = document.getElementById('urlWidgetCalendar').value;

		if(document.widget_formular.date.value !=""){
			resWidgetObjekt.open('post', url,true);
			resWidgetObjekt.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			resWidgetObjekt.onreadystatechange = handleResponseWidgetCal;
			resWidgetObjekt.send('date=' + escape(document.widget_formular.date.value) + '&room=' + escape(document.widget_formular.room.value) +  '&offer=' + escape(document.widget_formular.offer.value) + '&size=' + escape(document.widget_formular.size.value) + '&type=widget');
		} else {
			document.getElementById("show_widget_calendar").style.visibility = "hidden";
		}
}

function handleResponseWidgetCal() {
	var text="";
  document.getElementById("show_widget_calendar").style.visibility = "visible";
  if(resWidgetObjekt.readyState == 4){
  	text=resWidgetObjekt.responseText;
    document.getElementById("show_widget_calendar").innerHTML = text;
  }
}