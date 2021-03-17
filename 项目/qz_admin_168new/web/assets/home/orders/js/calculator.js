/**
 * 分单有引导计算器
 * 调用方法:$(selector).calculation({allPrice:number,halfPrice:number})
 * 运行环境：依赖jquery库
 * 2017-08-23
 */
!function($){
	$.fn.calculation=function(obj){
		if(!obj||!obj.allPrice||!obj.halfPrice){
			$.error("缺少报价参数！");
			return false;
		}else if(isNaN(parseFloat(obj.allPrice))||isNaN(parseFloat(obj.halfPrice))){
			$.error("报价参数类型错误！");
			return false;
		}
		var _this = this;
		var _calMask = $("<div id='cal-mask' class='cal-mask'></div>");
		_calMask.css({width:"100%",height:"100%",background:"rgba(0,0,0,0.5)",position:"absolute",top:"0px",left:"0px",zIndex:"4001"});
		_calMask.appendTo(_this);

		var _computBox = $("<div class='cal-computBox' style='background: white; margin: auto; padding: 10px 20px 25px; left: 0px; top: 0px; width: 430px; height: 260px; right: 0px; bottom: 0px; position: absolute; z-index: 5000;'></div>");
		var _title = $("<div style='line-height:30px'>计算器</div>");
		_title.appendTo(_computBox);


		var _content = $("<div class='cal-content' style='padding: 15px; border: 1px solid rgb(215, 215, 215); border-image: none; font-size: 14px; margin-bottom: 10px;'></div>");

		var _one = $("<div class='cal-one'><div class='cal-inputBox'><span class='cal-label'>面积：</span><input class='cal-size' type='text' /></div><div class='cal-inputBox'><span class='cal-label'>预算：</span><input  class='cal-money' type='text'/>万</div><div class='cal-inputBox1'><button class='cal-button'>计算</button></div></div>");

		_one.find(".cal-inputBox").attr("style",'width: 140px; text-align: center; display: inline-block;');
		_one.find(".cal-inputBox input[type=text]").attr("style",'border: 1px solid #ccc; border-image: none; width: 60px;  margin-right: 5px; margin-left: 5px;');
		_one.find(".cal-inputBox1").attr("style",'width: 60px; display: inline-block;');
		_one.find(".cal-inputBox1 button").attr("style",'background: rgb(25, 158, 216); border-radius: 3px; border: currentColor; border-image: none; width: 50px; height: 22px; color: white; margin-left: 35px; cursor: pointer;');

		_one.find("button").click(function(event) {
			if(rules()){
				getResult(obj);
			}
		});

		var _two =$("<div class='cal-two' style='margin: 15px auto; overflow: hidden;'><div class='cal-leftBox'><div style='line-height: 26px;'><input type='radio' name='cal-bao' value='0' checked/>半包</div><div style='line-height: 26px;'><input type='radio' name='cal-bao' value='1'/>全包</div></div><div class='cal-leftBox'><div style='line-height: 26px;'><input type='radio' name='cal-mianji' value='0' checked />建筑</div><div style='line-height: 26px;'><input type='radio' name='cal-mianji' value='1'/>套内</div></div><div class='cal-rightBox'><div style='width: 49%; line-height: 26px; float: left;'><input type='radio' name='cal-zxnr' value='0' checked />不含家具家电</div><div style='width: 49%; line-height: 26px; float: left;'><input type='radio' name='cal-zxnr' value='1'/>含家具家电</div><div style='width: 49%; line-height: 26px; float: left;'><input type='radio' name='cal-zxnr' value='2'/>含家具</div><div style='width: 49%; line-height: 26px; float: left;'><input type='radio' name='cal-zxnr' value='3'/>含家电</div></div>");

		_two.find(".cal-leftBox").attr("style",'padding: 2px 6px 2px 15px; width: 70px; border-right-color: rgb(222, 222, 222); border-right-width: 2px; border-right-style: dashed; float: left;');
		_two.find(".cal-rightBox").attr("style",'padding: 1px 6px 2px 15px; width: 200px; overflow: hidden; float: left;');

		var _three = $("<div class='three'>计算结果：<span>0</span></div>");

		var _footer = $("<div class='cal-forth'><span id='cal-error' class='cal-error' style='color:red'></span><button id='cal-closed' style='background: rgb(25, 158, 216); border-radius: 3px; border: currentColor; border-image: none; width: 60px; height: 25px; color: white; float: right; cursor: pointer;'>关闭</button></div>");

		_footer.find("button").click(function(event) {
			closeWindow();
		});

		_one.appendTo(_content);
		_two.appendTo(_content);
		_three.appendTo(_content);
		_content.appendTo(_computBox);
		_footer.appendTo(_computBox);
		_computBox.appendTo(_this);

		/*关闭功能*/
		function closeWindow(){
			_calMask.remove();
			_computBox.remove();
		}

		/*验证*/
		function rules(){
			var size = _one.find(".cal-size");
			var money = _one.find(".cal-money");
			var error = _footer.find(".cal-error");
			error.html('');
			if ((size.val() == "" || isNaN(size.val()))) {
				error.html("面积填写格式错误！");
				size.focus();
				return false;
			}

			if ((money.val() == "" || isNaN(money.val()))) {
				error.html("预算填写格式错误！");
				money.focus();
				return false;
			}
			return true;
		}

		/*计算结果*/
		function getResult(price){
			/*计算结果=m×条件A×条件B÷条件C-Y×10000*/
			var result = 0,m = 0,A = 0,B = 0,C = 0,Y = 0;

			/*条件A*/
			if(_two.find("input[name=cal-bao]:checked").val() == 0){
				A = parseFloat(price.halfPrice);
			}else{
				A = parseFloat(price.allPrice);
			}

			/*条件B*/
			if(_two.find("input[name=cal-mianji]:checked").val()==0){
				B = 1;
			}else{
				B = 1.1;
			}
			/*条件C（）*/
			if(_two.find("input[name=cal-zxnr]:checked").val()==0){
				C = 1;
			}else if(_two.find("input[name=cal-zxnr]:checked").val()==1){
				C = 0.7;
			}else if(_two.find("input[name=cal-zxnr]:checked").val()==2){
				C = 0.9;
			}else{
				C = 0.8
			}
			m = parseFloat($('.cal-size').val());
			Y = parseFloat($('.cal-money').val());
			result = m*A*B/C-Y*10000;
			result = result.toFixed(2);
			if(result > 0){
				_three.find("span").html("<i class='red' style='font-style:normal'>"+result+"</i>");
			}else if(result==0){
				result=Math.abs(result);
				_three.find("span").html("<i class='green' style='font-style:normal'>"+result+"</i>");
			}else{
				result=Math.abs(result);
				_three.find("span").html("<i class='green' style='font-style:normal'>"+result+"</i>");
			}
		}
}
}(jQuery);

