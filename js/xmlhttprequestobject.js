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