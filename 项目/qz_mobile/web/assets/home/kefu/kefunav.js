
/*主菜单#kefunav第N个选项对应#kefuson下第N个UL*/
$(document).ready(function() {


	$("#kefunav li").click(function(){
		
			var kenum = $(this).index("#kefunav li");
			$("#kefuson").show();
			$("#kefuson ul").eq(kenum).fadeIn("fast");
			$(this).parent().fadeOut(100);

		});
		
		$(".returnbtn").click(function(){
			$(this).parents("#kefuson").each(function(){
				$("#kefuson ul").fadeOut(100);
				});
			$(this).parents("#kefuson").hide();
			$("#kefunav").fadeIn("fast");
			
			});

	});
