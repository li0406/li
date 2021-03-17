;$(function(){
	var level=0;
	var htmlNode="";
	var detailType=0;
	var dataList=[];
	var p = 2;
	var isloading = false;
	//添加一级菜单
	$("#add-menu").click(function(event){
		switch_main(1,1,$(this));
		event.stopPropagation();
	});

	//一级菜单切换(选中当前菜单)
	$("#level-one").on("click",".menu-one",function(event){
		var _this = $(this);
		_this.addClass('menu-one-active').siblings().removeClass('menu-one-active');
		var length = _this.find("li").length;
		$(".menu-two-box").hide();
		$("#neirong").removeClass('hideNone');
		if (length >= 0) {
			_this.find(".menu-two-box").show();
		}

		if (length > 0) {
			$("#neirong").addClass('hideNone');
		}

		switch_main(2,1,$(this));
		event.stopPropagation();
	});

	//添加二级菜单
	$("#level-one").on("click",".menu-two-plus",function(event){
		switch_main(1,2,$(this));
		$("#neirong").removeClass('hideNone');
		event.stopPropagation();
	});

	//删除菜单
	$("#del-order").click(function(){
		var target = $(this).parents(".order-box").attr("data-target");
		$("#"+target).remove();
		var span = $(".menu-one-active").children("span");
		var length = $(".menu-one-active").find("li").length;
		if (length < 5) {
			$(".menu-one-active").find(".menu-two-plus").show();
		}

		if (length == 0) {
			switch_content("view",span);
		}

		var length = $(".menu-one").length;
		if (length < 3) {
			$("#add-menu").show();
		}



	});

	//选择素材
	$(".add-tuwen").click(function(event) {
	    $("#mask, .select-sucai-box").fadeIn();
	    var $container = $('#sc-contanier');
	    $container.imagesLoaded(function() {
	        $container.masonry({
	            itemSelector: '.sucai-item',
	            isAnimated: true,
	        });
		});
	});

	//选中素材
	$("#sc-contanier").on("click",".sucai-item",function(){
		var select = $(this).attr("data-select");
		htmlNode = $(this).prop("outerHTML");
		$(".add-tuwen").hide();
		$(".tw-content").removeClass('hideNone');
		$(".sucai-itme-mask").remove();
		if(select == "true"){
			$(this).attr("data-select","false").find(".sucai-itme-mask").remove();
		}else{
			$(this).children('.sc-detail').append("<div class='sucai-itme-mask'><div class='center-i'><i class='fa fa-check'></i></div></div>").attr("data-select","true");
			$(".sucai-itme-mask").height($(this).find(".sc-detail").outerHeight());
			$(this).siblings('.sucai-item').attr("data-select","false").find(".sucai-itme-mask").remove();
		}
	});

	//搜索素材
	$("input[name=search-sucai]").keydown(function(event) {
		if(event.keyCode==13){
			if ($("#search-container")){
				$("#search-container").remove();
			}
			var subStr = $(this).val();
			$(".sucai-content").append("<div id='search-container'></div>");
			$("").html("").fadeIn(0);
			$('#sc-contanier').fadeOut(0);

			for(var i=0; i<$("#sc-contanier .sucai-item").length; i++){
				var searchStr = $("#sc-contanier .sucai-item").eq(i).children('.sc-detail').children('span').text().trim("");

				if(searchStr.match(subStr)){
					 var htmlNode="<div class='sucai-item' data-select='false'>"+$("#sc-contanier .sucai-item").eq(i).html().trim("")+"</div>";
					 $("#search-container").append(htmlNode);
				}
			}

		    var $container = $('#search-container');
		    $container.imagesLoaded(function() {
		        $container.masonry({
		            itemSelector: '.sucai-item',
		            isAnimated: true,
		        });
			});

		}
	});

	$("#select-sc").click(function(){//添加选中素材
		if (htmlNode == "") {
			alert("请选择素材");
			return
		}
		var target = $(".tw-content").parents(".order-box").attr("data-target");
		var span = $("#"+target).children('span');
		var parent = $(".sucai-itme-mask").parents(".sucai-item");
		span.attr("data-media",parent.attr("data-id"));
		$(".add-tuwen,#mask,.select-sucai-box").fadeOut();
		$(".tw-content").html(htmlNode).fadeIn();
		$(".tw-content").find('.sucai-item').attr("style","");
		$(".tw-content").find(".sucai-itme-mask").remove();
	});


	$("#cancel-sc,#close-sec").click(function(event) {
		$("#mask,.select-sucai-box").fadeOut();
		$(".add-tuwen").fadeIn();
	});

	//保存 发布
	$("#save-order").click(function(event) {
		$(".confirm-box").fadeIn();
	});

	//确认保存信息
	$("#okBtn").click(function(event) {
		var data = [];
		$(".menu-one").each(function(){
			var span  = $(this).children('span');
			var sub = {
				name:span.text(),
				type:"click",
				children:new Array()
			}
			var length = $(this).find("li").length;

			if (length > 0) {
				$(this).find("li").each(function(){
					span = $(this).find("span");
					var child = {
						name:span.text(),
						type:"click",
						msg:{
							type:span.attr("data-sub-type"),
							text:span.attr("data-msg"),
							url:span.attr("data-href"),
							materialId:span.attr("data-media")
						}
					}
					sub.children.push(child);
				});
			} else {
				sub["msg"] = {
					type:span.attr("data-sub-type"),
					text:span.attr("data-msg"),
					url:span.attr("data-href"),
					materialId:span.attr("data-media")
				}
			}
			data.push(sub);
		});

		data = JSON.stringify(data);
		$.ajax({
			url: '/xiongzhang/setmenu/',
			type: 'POST',
			dataType: 'JSON',
			data: {menu:data}
		})
		.done(function(data) {
			if (data.status == 1) {
				$(".confirm-box,#mask").fadeOut();
			} else {
				alert(data.info);
			}
		});
	});

	//取消保存信息
	$("#cancelBtn").click(function(event) {
		$(".confirm-box,#mask").fadeOut();
	});

	$("input[name=order-name]").on("input propertychange",function(){
		var length = $(this).val().replace(/[^\x00-\xff]/g,"01").length;
		if (length > 8) {
			return false;
		}
		var target = $(this).parents(".order-box").attr("data-target");

		$("#"+target).children('span').text($(this).val());
	});

	$("input[name=link-href]").on("input propertychange",function(){
		var target = $(this).parents(".order-box").attr("data-target");
		$("#"+target).children('span').attr("data-href",$(this).val());
		$("#"+target).children('span').attr("data-type",$(".content-typ-active").attr("data-type"));
	});

	//图文切换
	$(".text-type").click(function(event) {
		var _this = $(this);
		_this.addClass("text-active").siblings().removeClass('text-active');
		var index = $(".text-type").index(_this);
		var target = $(this).parents(".order-box").attr("data-target");
		var span = $("#"+target).children('span');

		if (index == 1) {
			//图文
			switch_content("view_limited",span);
		} else if (index == 0) {
			//文字
			switch_content("view_text",span);
		}
	});

	//文本框赋值
	$("textarea[name=content]").on("input propertychange",function(){
		var target = $(this).parents(".order-box").attr("data-target");
		$("#"+target).children('span').attr("data-msg",$(this).val());
	});

	//二级菜单切换(选中当前菜单)
	$("#level-one").on("click","li",function(event){
		$(this).addClass("menu-two-active").siblings().removeClass("menu-two-active");
		$("#neirong").removeClass('hideNone');
		switch_main(2,1,$(this));
		event.stopPropagation();
	});

	//菜单内容类型切换
	$(".content-type").click(function(){
		$(this).addClass('content-typ-active').siblings().removeClass('content-typ-active');
		var index = $(".content-type").index($(this));
		$(".content-type-container").addClass('hideNone');

		var target = $(this).parents(".order-box").attr("data-target");
		var span = $("#"+target).children('span');
		var subType = span.attr("data-sub-type");

		if (index == 0) {
			//跳转链接
			$(".content-type-container").eq(0).removeClass('hideNone');
			switch_content("view",span);
		} else if (index == 1){
			//回复消息
			$(".content-type-container").eq(1).removeClass('hideNone');
			var index = $(".text-type").index($(".text-active"));
			if (index == 0) {
				switch_content("view_text",span);
			} else {
				 switch_content("view_limited",span);
			}
		}
	});

	function switch_main(select,level,obj,children) {
		$(".order-box").show();
		$(".content-type.content-typ-active").removeClass('content-typ-active');
		$(".content-type-container").addClass('hideNone');
		$(".order-box").removeClass('hideNone');
		$(".text-ts").hide();
		var length = 0;
		obj.show();

		switch(select){
			case 1:
				//菜单添加
				$(".content-type").eq(0).addClass('content-typ-active');
				$(".content-type-container").eq(0).removeClass('hideNone');
				var id = 0;
				if (level == 1) {
					//添加一级菜单
					length = $(".menu-one").length;
					id = "menu" + (length + 1);
					$("#neirong").removeClass('hideNone');

					if (length < 3) {
						var menuOneItem = "<div id='"+ id +"' class='menu-one menu-one-active children-active'><span  data-type='click' data-sub-type='view'>菜单名称</span><div class='menu-two-box'><ul></ul><div class='menu-two-plus'>+</div></div></div>";
						$("#level-one").append(menuOneItem);

						if ((length+1) == 3) {
							obj.hide();
						}

						$(".menu-one-active").show();
					}


				} else if(level == 2) {
					//添加二级菜单
					parentid = obj.parents(".menu-one").attr("id");
					length = obj.parents(".menu-one").find("li").length;

					if (length < 5 ) {
						id = parentid +"-"+ ( length + 1);
						$(".menu-two-active").removeClass('menu-two-active');
						var menuTwoItem = "<li id='"+id+"' data-type='click' class='menu-two-active menu-two'><span data-sub-type='view'>菜单名称</span></li>";
						obj.prev("ul").prepend(menuTwoItem);
						if ((length + 1) == 5) {
							obj.hide();
						}
					}

				}

				$("input[name='order-name']").val("菜单名称");
				$(".order-box").attr("data-target",id);

			    break;
			case 2:
				//菜单切换
				var id = obj.attr("id");
				var span = obj.children('span');
				var subType =  span.attr("data-sub-type");
				var name = span.text();
				$("input[name='order-name']").val(name);
				$(".order-box").attr("data-target",id);
				$(".content-typ-active").removeClass('content-typ-active');
				$(".content-type-container").addClass('hideNone');
				switch_content(subType,span);
				break;
		}
	}

	/**
	 * 清除内容
	 * @param  {[type]} event) {			}       [description]
	 * @return {[type]}        [description]
	 */
	$(".btn-danger").click(function(event) {
		var target = $(".order-box").attr("data-target");
		if (typeof target !== "undefined") {
			var span = $("#"+target).children('span');
			var subType = span.attr("data-sub-type");
			span.attr("data-sub-type","view");
			span.attr("data-href","");
			span.attr("data-msg","");
			span.attr("data-media","");
			switch(subType){
				default:
					//链接回复
					$("input[name=link-href]").val("");
					span.attr("data-href","");
					break;
				case "view_text":
					//文字回复
					$(".textarea").val("");
					break;
				case "view_limited":
					//图文回复
					$(".tw-content").html("").addClass('hideNone');
					$(".add-tuwen").show();
					break;
			}
		}
	});

	function switch_content(subType,target) {
		target.attr("data-sub-type",subType);
		$(".tuwen").addClass('hideNone');
		$(".tw-content").addClass('hideNone');
		$(".textarea").addClass('hideNone');

		switch(subType){
			default:
				//链接回复
				var href = target.attr("data-href");
				$(".content-type").eq(0).addClass('content-typ-active');
				$(".content-type-container").eq(0).removeClass('hideNone');
				$("input[name=link-href]").val(href);
				break;
			case "view_text":
				//文字回复
				$(".content-type").eq(1).addClass('content-typ-active');
				$(".content-type-container").eq(1).removeClass('hideNone');
				$(".textarea").removeClass('hideNone').val(target.attr("data-msg"));
				$(".text-type").eq(0).addClass('text-active').siblings().removeClass('text-active');
				target.attr("data-msg",$(".textarea").val());
				break;
			case "view_limited":
				//图文回复
				$(".content-type").eq(1).addClass('content-typ-active');
				$(".content-type-container").eq(1).removeClass('hideNone');
				$(".tuwen").removeClass('hideNone');
				$(".text-type").eq(1).addClass('text-active').siblings().removeClass('text-active');
				$(".add-tuwen").show();
				target.attr("data-msg","");

				var media_id = target.attr("data-media");

				if (media_id != null && media_id != "") {
					$.ajax({
						url: '/xiongzhang/getperpetualmaterial/',
						type: 'POST',
						dataType: 'JSON',
						data: {media_id:media_id}
					})
					.done(function(data) {
						if (data.status == 1) {
							var info = data.data;
							var src = info.src;
							if (src == null || src == "") {
								src = "/assets/home/xiongzhang/img/placeholder-xiongzhang.png";
							}
							var tmp =  '<div class="sucai-item" data-id="'+info.media_id+'" data-select="false"><div class="sc-detail"><img  src="'+src+'" /><span>'+info.title+'</span></div></div>';
							$(".add-tuwen").hide();
							$(".tw-content").removeClass('hideNone').html( tmp );
						}
					});
				}
				break;
		}
	}

	/**
	 * 滚动条监听事件
	 * @param  {[type]} ){	　　var scrollTop     [description]
	 * @return {[type]}           [description]
	 */
	$(".sucai-content").scroll(function(){
		var _this =  $(this);
		var scrollTop = _this.scrollTop();
	  　var scrollHeight = $("#sc-contanier").height();
	　　var windowHeight = $(this).height();
	　　if(scrollHeight - (scrollTop + windowHeight) <= 100 ){
　　　　		$.ajax({
				url: '/xiongzhang/getmateriallist/',
				type: 'get',
				dataType: 'JSON',
				data: {p: p}
			})
			.done(function(data) {
				if (data.status == 1) {
					if (data.data != null && data.data != false) {
						p = data.page;
						var container = $('#sc-contanier');
						for(var i in data.data) {
							var child = data.data[i];
							var src = child.news_item.src == null?'/assets/home/xiongzhang/img/placeholder-xiongzhang.png':child.news_item.src;
							var tmp = $('<div class="sucai-item" data-id="'+child.media_id+'"  data-select="false"><div class="sc-detail"><img  src="'+src+'" /><span>'+child.news_item.title+'</span></div></div>');
							container.prepend(tmp).masonry( 'prepended', tmp );
						}
						isloading = false;
						container.imagesLoaded( function() {
                            container.masonry('layout');
                        });
						$(".sucai-content").scrollTop(0);
					} else {
						alert("没有数据了");
					}
				}
			});
	　　}
	});

	//开关
    $('.switch input').bootstrapSwitch({
        onText: "是",
        offText: "否",
        onColor: "info",
        offColor: "danger",
        onSwitchChange:function(e, data){
            var $el = $(e.target);
            if($el.attr("checked") == "checked"){
                $el.attr("checked",false);
            }else{
                $el.attr("checked","checked")
            }

            $.ajax({
                url: '/xiongzhang/switchreply',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    enabled:$el.attr("checked") == "checked"?1:0
                }
            });
        }
    });
});
