

function checkAllController(theForm,obj,checkName){
	if(obj.checked==true){
		eleArr=theForm.elements[checkName+'[]'];
		for (i=0;i<eleArr.length;i++){eleArr[i].checked= true ;}
	}else{
		eleArr=theForm.elements[checkName+'[]'];
		for (i=0;i<eleArr.length;i++){eleArr[i].checked= false ;}
	}
}