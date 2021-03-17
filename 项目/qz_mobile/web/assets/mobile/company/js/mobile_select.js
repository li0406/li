(function($){
	//创建一个mySelect类
	var mySelect=function(domself,arr,houseType){
		 this.myself=domself;
		 this.arr=arr;
		 this.houseType=houseType;
	}
	//给这个类加属性
	mySelect.prototype={
		addSelect:function(){
			var _this=this;
			_this.myself.addClass("m_select_box");
			_this.myself.append("<p data-id='"+_this.arr[0].id+"'' style='color:#999'>"+_this.arr[0].name+"</p><i class='select_font'>&#xe791;</i>");
			_this.myself.on("click",function(){
				$("body").append("<div class='m_select_mask'><div class='m_select_container'><div class='m_select_list'></div></div>");
				$(".m_select_mask").fadeIn();
				for(var i=0; i<_this.arr.length;i++){
					$(".m_select_list").append("<div class='m_select_item' data-id='"+_this.arr[i].id+"'>"+_this.arr[i].name+"</div>")
				}
				$(".m_select_list").animate({"bottom":"0px"});
				$("body").addClass("m_select_fixed");
				$("body").on("click",".m_select_container",function(){
					$(".m_select_list").animate({"bottom":"-100%"},500,function(){
						$(".m_select_mask").fadeOut().remove();
					});
					$("body").removeClass("m_select_fixed");
				});
				$("body").on("click",".m_select_item",function(){
					_this.myself.children("p").text("");
					$(".m_select_list").animate({"bottom":"-100%"},500,function(){
						$(".m_select_mask").fadeOut().remove();
					});
					$("body").removeClass("m_select_fixed");
					_this.myself.children("p").css('color',"#333").append($(this).text())
					_this.myself.children("p").data("id",$(this).data("id"));
					_this.houseType.val($(this).data("id"));

				})
				//阻止子元素继承父元素事件
				/*$("body").on("click",".m_select_container .m_select_list",function(e){
					e.stopPropagation();
				});*/

			});
		}
	}
	//创建一个插件
	$.fn.mobileSelect=function(arr,houseType){
		var _this=this;
		var myselect=new mySelect(_this,arr,houseType);
		return myselect.addSelect();
	}
})(jQuery);