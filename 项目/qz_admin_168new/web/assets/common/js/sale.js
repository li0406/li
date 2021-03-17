$(function){
	alert(1);
	$(".fa-edit").bind("click",function(){
		var mbPoingID = $(this).parent().attr("id");
		alert(mbPoingID);
	}
}