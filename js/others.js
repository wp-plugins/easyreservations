function TableToExcel()
{
var strCopy = document.getElementById("MyTable").innerHTML;
window.clipboardData.setData("Text", strCopy);
var objExcel = new ActiveXObject ("Excel.Application");
objExcel.visible = true;

var objWorkbook = objExcel.Workbooks.Add;
var objWorksheet = objWorkbook.Worksheets(1);
objWorksheet.Paste;
}