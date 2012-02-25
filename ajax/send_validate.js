function generateXMLHttpReqObjThree(){
  var resObjektTwo = null;
  try {
    resObjektThree = new ActiveXObject("Microsoft.XMLHTTP");
  }
  catch(Error){
    try {
      resObjektThree = new ActiveXObject("MSXML2.XMLHTTP");
    }
    catch(Error){
      try {
      resObjektThree = new XMLHttpRequest();
      }
      catch(Error){
        alert("AJAX error");
      }
    }
  }
  return resObjektThree;
}
function generateAJAXObjektThree(){
  this.generateXMLHttpReqObjThree = generateXMLHttpReqObjThree;
}
xxy = new generateAJAXObjektThree();
resObjektThree = xxy.generateXMLHttpReqObjThree();

function easyRes_sendReq_Validate() {
	if(document.getElementById('urlValidate').type == "hidden") var url = document.getElementById('urlValidate').value;
	else var url = document.getElementById('urlValidate').src;

	if(document.easyFrontendFormular.from.value != "" && document.easyFrontendFormular.to.value != ""){
		resObjektThree.open('post', url.replace("send_validate.js", "") + 'send_validate.php' ,true);
		resObjektThree.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		resObjektThree.onreadystatechange = handleResponseValidate;
		resObjektThree.send('from=' + escape(document.easyFrontendFormular.from.value) + '&to=' + escape(document.easyFrontendFormular.to.value) + '&room=' + escape(document.easyFrontendFormular.room.value) + '&persons=' + escape(document.easyFrontendFormular.persons.value) + '&email=' + escape(document.easyFrontendFormular.email.value) + '&offer=' + escape(document.easyFrontendFormular.offer.value) + '&thename=' + escape(document.easyFrontendFormular.thename.value));
		document.getElementById("showError").innerHTML = '<img style="vertical-align:text-bottom;" src="' + url.replace("js/ajax/send_validate.js", "") + 'images/loading.gif">';
	} else {
		document.getElementById("showError").style.visibility = "hidden";
	}
}

function handleResponseValidate() {
	var text="";
  document.getElementById("showError").style.visibility = "visible";
  if(resObjektThree.readyState == 4){
  	text=resObjektThree.responseText;
    document.getElementById("showError").innerHTML = text;
  }
}