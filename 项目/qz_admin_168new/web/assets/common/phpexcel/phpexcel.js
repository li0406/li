!function($){
    var methods = {
        init:function(options){
            var defaults = {
                data:null,
                title:null,
                url:"download.php",
                show:true,
                width:null
            }
            var options = $.extend({},defaults, options);
            if(options.data == null || typeof options.data != "object"){
                $.error( '数据格式错误！' );
                return false;
            }
            var _data_all = [];
            var data_array ={};
            if(options.show){
                var main = $("<div class='export-main'></div>");
                var bg = $("<div class='export-bg'></div>");
                bg.appendTo(main);
                var content = $("<div class='export-content'></div>");
                content.appendTo(main);
                var header =  $("<div class='export-header'></div>");
                header.appendTo(content);
                var title =  $("<em class='export-header'></em>");
                title.text(options.title);
                title.appendTo(header);
                var close = $("<span class='export-close'>x</span>");
                close.click(function(event) {
                    main.remove();
                });
                close.appendTo(header);
                var basic = $("<div id='export-basic' class='export-basic'></div>");
                basic.appendTo(content);
                var footer =  $("<div class='export-footer'></div>");
                footer.appendTo(content);
                var button = $("<button>确定</button>");
                button.click(function(event) {
                    $(this).attr("disabled","disabled");
                    $(this).html("读取数据中...");
                    var data = hot.getData();
                    var _data = {};
                    for (var i = 0; i < data.length; i++) {
                        var _sub = {};
                        for (var j = 0; j < data[i].length; j++) {
                            var _prop = data_array[i+"_"+j];
                            if(typeof _prop != "undefined"){
                                var _child = {
                                    text:data[i][j],
                                    fontColor: getHexBackgroundColor(_prop["fontColor"]),
                                    bgColor:getHexBackgroundColor(_prop["bgColor"]),
                                    isString:data[i][j]["isString"],
                                    col_span:data[i][j]["col_span"]
                                };
                                _sub[j] = _child;
                            }
                        };
                        _data[i] = _sub;
                    };
                    submitForm(_data);
                });

                button.appendTo(footer);
                main.appendTo($("body"));
                if(options.width != null){
                    content.css("width",options.width+"px");
                    ml = -(options.width/2);
                    content.css("margin-left",ml+"px");
                }
                for(var i in options.data){
                    var _sub = [];
                    for(var j in options.data[i]){
                        var _child = options.data[i][j].text;
                        _sub.push(_child);
                        data_array[i+"_"+j] = options.data[i][j];
                    }
                    _data_all.push(_sub);
                }

                var container = document.getElementById('export-basic');

                var cellRenderer = function (instance, td, row, col, prop, value, cellProperties) {
                    Handsontable.renderers.TextRenderer.apply(this, arguments);
                    var _data = data_array[row+"_"+col];
                    if(typeof _data != "undefined"){
                        td.style.color = _data.fontColor;
                        td.style.fontWeight = _data.fontWeight;
                        td.style.backgroundColor = _data.bgColor;
                    }
                };

                var hot = new Handsontable(container,{
                    data:_data_all,
                    colHeaders: true,
                    rowHeaders: true,
                    stretchH: 'all',
                    columnSorting: true,
                    contextMenu: true,
                    height:600,
                    cells:function(row, col, prop){
                        var cellProperties = {};
                        cellProperties.renderer = cellRenderer;
                        return cellProperties;
                    },
                    beforeRemoveCol:function(index,amount){
                        var sCol = index;
                        var eCol = amount;
                        for (var i = 0; i < options.data.length; i++) {
                            for (var j = sCol; j < eCol; j++) {
                                 delete options.data[i][j];
                            };
                        };

                        for (var i = 0; i < options.data.length; i++) {
                            var other_array = [];
                            for (var j = 0; j < options.data[i].length; j++) {
                                if(typeof options.data[i][j] != "undefined"){
                                    other_array.push(options.data[i][j]);
                                }
                            };
                            options.data[i] = other_array;
                        };

                        data_array = [];
                        for(var i in options.data){
                            for(var j in options.data[i]){
                                data_array[i+"_"+j] = options.data[i][j];
                            }
                        }
                    },
                    beforeRemoveRow:function(index,amount){
                        var sRow = index;
                        var eRow = sRow+amount;
                        for (var i = sRow; i < eRow; i++) {
                             delete options.data[i];
                        };


                        var other_array = [];
                        for (var i = 0; i < options.data.length; i++) {
                            if(typeof options.data[i] != "undefined"){
                                other_array.push(options.data[i]);
                            }
                        };
                        options.data = other_array;

                        data_array = [];
                        for(var i in options.data){
                            for(var j in options.data[i]){
                                data_array[i+"_"+j] = options.data[i][j];
                            }
                        }
                    }
                });

                $("body").keypress(function(event) {
                    if(event.key == "Escape"){
                        main.remove();
                    }
                });

            }else{
                var _data ={};
                for(var i in options.data){
                    var _sub ={};
                    for(var j in options.data[i]){
                        var _child = {
                            text:options.data[i][j]["text"],
                            fontColor: getHexBackgroundColor(options.data[i][j]["fontColor"]),
                            bgColor:getHexBackgroundColor(options.data[i][j]["bgColor"]),
                            isString:options.data[i][j]["isString"],
                            col_span:options.data[i][j]["col_span"]
                        };
                        _sub[j] = _child;
                    }
                    _data[i] = _sub;
                }

                submitForm(_data);
            }

            function getHexBackgroundColor(rgb){
                if(typeof rgb != "undefined"){
                    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

                    function hex(x) {
                        return ("0" + parseInt(x).toString(16)).slice(-2);
                    }
                    if(rgb != null){
                         return rgb= "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
                    }
                }
                return "#FFFFFF";
            }

            function submitForm(_data){
                _data = JSON.stringify(_data);
                var form=$("<form></form>");//定义一个form表单
                form.attr("style","display:none");
                form.attr("target","");
                form.attr("method","post");
                form.attr("action",options.url);
                var _input=$("<input>");
                _input.attr("type","hidden");
                _input.attr("name","data");
                _input.attr("value",_data);
                form.append(_input);
                var _title=$("<input>");
                _title.attr("type","hidden");
                _title.attr("name","title");
                _title.attr("value",options.title);
                form.append(_title);
                $("body").append(form);//将表单放置在web中
                form.submit();//表单提交
            }
        }
    }

    $.fn.exportExcel = function(method) {
        if(methods[method]) {
            return methods[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.exportExcel' );
            return this;
        }
    }
}(window.jQuery);