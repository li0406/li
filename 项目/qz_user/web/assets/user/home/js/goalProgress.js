/*
 *  Tinacious Design goalProgress jQuery plugin
 *  Plugin URL: https://github.com/tinacious/goalProgress
 *
 *  Christina Holly (Tinacious Design)
 *  http://tinaciousdesign.com
 *
 */
!function($){
	 var methods = {
        init: function(options){
        	var defaults = {
				goalAmount: 100,
				currentAmount: 50,
				speed: 50,
				textBefore: '',
				textAfter: '',
				milestoneNumber: 70,
				milestoneClass: 'almost-full',
                text:"",
                state:"ready"
			}
			var _this = this;
        	var _self = methods;
			var settings =  $.extend({},defaults, options);
			_this.data("settings",settings);
        	_self.create(_this,settings);
        },
        create:function(obj,options){
        	var _this = obj;
        	var _self = methods;
			var goalAmountParsed = parseInt(options.goalAmount);
        	var currentAmountParsed = parseInt(options.currentAmount);
   	      	var percentage = (currentAmountParsed / goalAmountParsed) * 100;

        	var progressBar = '<div class="progressBar"></div>';
        	var progressInfo = '<div  class="progressInfo"><i>' + options.text + '</i></div>';
        	var progressBarWrapped = '<div class="goalProgress">' + progressBar + progressInfo +'</div>';
        	_this.append(progressBarWrapped);
            var text = options.text;
            if(options.state != "ready"){
                text = options.textBefore+percentage+options.textAfter;
            }
            _self.updateProgress(_this,percentage,options.speed,options.text);
        },
        updateProgress:function(obj,percentage,speed,text,callback){
            var _self = methods;
        	var rendered = obj.find('div.progressBar');
        	var info = obj.find('div.progressInfo i');
            // Remove Spaces
            // info.html(info.text().replace(/\s/g, '&nbsp;'));
			// Animate!
            rendered.animate({width: percentage +'%'}, speed,function(e){
                info.html(text);
                if(typeof callback == "function"){
                    callback();
                }
            });
        },
        abort:function(){
            var _this = this;
            var _self = methods;
            var settings = _this.data().settings;
            var _arguments = arguments[1];
            var t = _this.find('div.progressBar');
            _this.data("percentage",0);
            t.stop(true, true);
            _self.updateProgress(_this,100,settings.speed,settings.text);
            if(typeof _arguments[0] == "function"){
                _arguments[0]();
            }
        },
        queue:function(){
            var _self = methods;
            var _this = this;
            var options = _this.data().settings;
            var _arguments = arguments[1];
            var options = _this.data().settings;
            var beforePercentage = parseInt(_arguments[2]);
            var percentage = beforePercentage + _arguments[0];
            _this.parent().queue("fx",function(){
                for (var i = beforePercentage; i <= percentage; i++) {
                    if(i == percentage){
                        var callback = _arguments[3];
                    }
                   _self.updateProgress(_this,i,options.speed,_arguments[1]+i+"%",callback);
                };
            });
        },
        dequeue:function(){
            var _this = this;
            var _self = methods;
            while(_this.parent().queue("fx").length){
                _this.parent().dequeue("fx");
            }
        },
        reset:function(){
            var _this = this;
             _this.find('div.progressBar').width(0);
        },
        finish:function(){
            var _this = this;
            var _self = methods;
            var _this = this;
            var options = _this.data().settings;
            var _arguments = arguments[1];
            var percentage = _arguments[0];
            var callback =  _arguments[2];
            _this.parent().queue("fx",function(){
                _self.updateProgress(_this,percentage,options.speed,_arguments[1]+percentage+"分");
            });
            if(typeof callback == "function"){
               callback();
            }
        }
    }

    $.fn.goalProgress = function(method) {
        if(methods[method]) {
            return methods[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.goalProgress' );
            return this;
        }
    }

}(window.jQuery);