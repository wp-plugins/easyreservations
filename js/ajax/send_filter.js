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

function easyRes_sendReq_Filter(i) {
	var url = document.getElementById('urlFilter').src;	
	var filter = document.getElementById('reservations_filter');

	resObjektTwo.open('post', url.replace("send_filter.js", "") + 'send_filter.php' ,true);
	resObjektTwo.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	resObjektTwo.onreadystatechange = handleResponsePrice;
	resObjektTwo.send('filter=' + filter.value +'&id= ' + escape(document.getElementById('theResourceID').value)  );
}

function handleResponsePrice() {

  if(resObjektTwo.readyState == 4){
	easyRes_sendReq_Calendar();
  }
}