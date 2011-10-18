function sndReq() {
  if(document.formular.date.value !=""){
    resObjekt.open('post', 'http://localhost/demo/wp-content/plugins/easyreservations/calendar.php' ,true);
	resObjekt.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    resObjekt.onreadystatechange = handleResponse;
    resObjekt.send('date=' + escape(document.formular.date.value) + '&room=' + escape(document.formular.room.value));
  }
  else {
    document.getElementById("zeige").style.visibility = "hidden";
  }
}
function handleResponse() {
	var text="";
  document.getElementById("zeige").style.visibility = "visible";
  if(resObjekt.readyState == 4){
  	text=resObjekt.responseText;
    document.getElementById("zeige").innerHTML = text;
  } 
}