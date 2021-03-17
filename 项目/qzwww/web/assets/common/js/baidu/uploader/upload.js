(function($) {
    /**
     * 上传空间
     * auto 是否自动上传
     * fileNumLimit 文件数量
     * @type {Object}
     */
    methods = {
        init: function(options) {
            var _this = $(this);
            var _sele = methods;
            var defaults = {
                auto:false,
                host:"",
                old_host:"",
                pick:{
                    id:"#filePicker",
                    label:"点击选择图片",
                    multiple: true
                },
                server:"",
                thumb: {
                    width: 150,
                    height: 150,
                    // 图片质量，只有type为`image/jpeg`的时候才有效。
                    quality: 90,
                    allowMagnify: false, //是否允许放大
                    crop: false // 是否允许裁剪
                },
                formData:{},
                addButton:{
                    id: '#filePicker2',
                    label: '继续添加'
                },
                drag:false,
                width:0,
                height:0,
                fileNumLimit:10,
                threads:10,
                callback:null,
                removePath:"",
                thumbnailWidth:110,
                thumbnailHeight:110
            }
            var options =  $.extend({},defaults, options);

            var $wrap = _this.find('#uploader');
            // 图片容器
            var $queuelist = $('<div id="fileList" class="queuelist" class="uploader-list"></div>').appendTo($wrap),
                $queue = $('<ul class="filelist"></ul>')
                .appendTo($queuelist),
                //添加按钮
                $buttons = $('<div class="upload-buttons"></div>').appendTo($wrap),
                $filePicker = $('<div id="filePicker" class="filePicker" ></div>').appendTo($buttons),
                // 上传按钮
                $upload = $('<button id="ctlBtn" class="btn btn-default uploadBtn ctlBtn" disabled="disabled">开始上传</button>').appendTo($buttons),
                //重置按钮
                $retBtn = $('<button class="btn btn-info resetBtn" disabled="disabled">重置</button>').appendTo($buttons),
                //上传信息
                $info = $('<div class="uploader-info"></div>').appendTo($buttons),
                // 状态栏，包括进度和控制按钮
                $statusBar = $wrap.find('.statusBar'),
                // 没选择文件之前的内容。
                $placeHolder = $wrap.find('.placeholder'),
                $progress = $statusBar.find('.progress').hide(),
                // 添加的文件数量
                fileCount = 0,
                // 添加的文件总大小
                fileSize = 0,
                // 优化retina, 在retina下这个值是2
                ratio = window.devicePixelRatio || 1,
                // 缩略图大小
                // thumbnailWidth = 110 * ratio,
                // thumbnailHeight = 110 * ratio,
                // 可能有pedding, ready, uploading, confirm, done.
                state = 'pedding',
                // 所有文件的进度信息，key为file id
                percentages = {},
                // 判断浏览器是否支持图片的base64
                isSupportBase64 = (function() {
                    var data = new Image();
                    var support = true;
                    data.onload = data.onerror = function() {
                        if (this.width != 1 || this.height != 1) {
                            support = false;
                        }
                    }
                    data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
                    return support;
                })(),
                // 检测是否已经安装flash，检测flash的版本
                flashVersion = (function() {
                    var version;

                    try {
                        version = navigator.plugins['Shockwave Flash'];
                        version = version.description;
                    } catch (ex) {
                        try {
                            version = new ActiveXObject('ShockwaveFlash.ShockwaveFlash')
                                .GetVariable('$version');
                        } catch (ex2) {
                            version = '0.0';
                        }
                    }
                    version = version.match(/\d+/g);
                    return parseFloat(version[0] + '.' + version[1], 10);
                })(),
                supportTransition = (function() {
                    var s = document.createElement('p').style,
                        r = 'transition' in s ||
                        'WebkitTransition' in s ||
                        'MozTransition' in s ||
                        'msTransition' in s ||
                        'OTransition' in s;
                    s = null;
                    return r;
                })(),
                removePath='/Index/removeFile',
                previewPath ="/Index/preview",
                // WebUploader实例
                uploader;

            if (!WebUploader.Uploader.support('flash') && WebUploader.browser.ie) {

                // flash 安装了但是版本过低。
                if (flashVersion) {
                    (function(container) {
                        window['expressinstallcallback'] = function(state) {
                            switch (state) {
                                case 'Download.Cancelled':
                                    alert('您取消了更新！')
                                    break;

                                case 'Download.Failed':
                                    alert('安装失败')
                                    break;

                                default:
                                    alert('安装已成功，请刷新！');
                                    break;
                            }
                            delete window['expressinstallcallback'];
                        };

                        var swf = '/Public/uploader/expressInstall.swf';
                        // insert flash object
                        var html = '<object type="application/' +
                            'x-shockwave-flash" data="' + swf + '" ';

                        if (WebUploader.browser.ie) {
                            html += 'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
                        }

                        html += 'width="100%" height="100%" style="outline:0">' +
                            '<param name="movie" value="' + swf + '" />' +
                            '<param name="wmode" value="transparent" />' +
                            '<param name="allowscriptaccess" value="always" />' +
                            '</object>';

                        container.html(html);

                    })($wrap);

                    // 压根就没有安转。
                } else {
                    $wrap.html('<a href="http://www.adobe.com/go/getflashplayer" target="_blank" border="0"><img alt="get flash player" src="http://www.adobe.com/macromedia/style_guide/images/160x41_Get_Flash_Player.jpg" /></a>');
                }

                return;
            } else if (!WebUploader.Uploader.support()) {
                alert('Web Uploader 不支持您的浏览器！');
                return;
            }

            if(options.drag){
                //拖拽
                $queue.dragsort({
                    dragSelector: ".file-item",
                    dragEnd: function() {
                        var length = $queue.find(".file-item").length;
                        for (var i = 0; i < length; i++) {
                            var li = $queue.find(".file-item").eq(i);
                            li.attr("tabIndex",i);
                            // if(uploader.getFiles().length > 0){
                            //     uploader.getFiles()[li.attr("tab")].tabIndex = i;
                            // }
                        };
                        //重新排序
                        uploader.sort(function(a,b){
                            if ( a.tabIndex < b.tabIndex ){
                              return -1;
                            }

                            if ( a.tabIndex > b.tabIndex ){
                              return 1;
                            }
                            return 0;
                        });

                    },
                    dragBetween: false,
                    placeHolderTemplate: "<li style='border: 1px dashed #F1F1F1; float:left;'></li>"
                });
            }

            // 实例化
            uploader = WebUploader.create({
                pick:options.pick,
                //swf文件路径
                swf: '/assets/common/js/baidu/uploader/dist/Uploader.swf',
                //上传文件路径
                // server: 'http://liaoxuezhi.fe.baidu.com/webupload/fileupload.php',
                // server: 'http://www.2betop.net/fileupload.php',
                server: options.server,
                //指定接受类型的文件
                accept: {
                    title: 'Images',
                    //文件后缀
                    extensions: 'gif,jpg,jpeg,png',
                    mimeTypes: 'image/*'
                },
                //配置缩略图
                thumb:options.thumb,
                //配置压缩的图片的选项,如果此选项为false, 则图片在上传前不进行压缩。
                compress: false,
                //自动上传
                auto: options.auto,
                //Drag And Drop拖拽的容器,如果不指定,则不启动 exp:#dndArea
                dnd: '',
                //通过粘贴来添加截屏的图片 指定监听paste事件的容器，如果不指定，不启用此功能。
                //#uploader
                paste: '',
                //分片上传
                chunked: false,
                //指定运行时启动顺序。默认会想尝试 html5 是否支持
                // runtimeOrder: 'flash',
                //是否已二进制的流的方式发送文件
                sendAsBinary: false,
                //验证文件总数量，超出则步允许上传
                fileNumLimit: options.fileNumLimit,
                //验证文件总大小是否超出限制, 超出则不允许加入队列 单位:字节
                fileSizeLimit: 1048576*10* options.fileNumLimit,
                //验证单个文件的大小, 超出则不允许加入队列 单位:字节 1048576=1M
                fileSingleSizeLimit: 1048576*10,
                //去重， 根据文件名字、文件大小和最后修改时间来生成hash Key
                duplicate: true,
                //禁用组件黑名单 string/array
                disableWidgets: "",
                //上传并发数
                threads: options.threads,
                //文件上传请求的参数表
                formData: options.formData
            });
            // 拖拽时不接受 js, txt 文件。
            uploader.on('dndAccept', function(items) {
                var denied = false,
                    len = items.length,
                    i = 0,
                    // 修改js类型
                    unAllowed = 'text/plain;application/javascript ';

                for (; i < len; i++) {
                    // 如果在列表里面
                    if (~unAllowed.indexOf(items[i].type)) {
                        denied = true;
                        break;
                    }
                }

                return !denied;
            });
            // 添加“添加文件”的按钮，
            uploader.addButton(options.addButton);
            uploader.on('ready', function() {
                $wrap.find(".upload-buttons button").attr("disabled", "disabled");
                addMyFiles();
                window.uploader = uploader;
            });

            uploader.on("beforeFileQueued",function(file){
                var count = $queue.find("li").length;
                var fileNumLimit = uploader.options.fileNumLimit;
                if((fileNumLimit - count) == 0 ){
                    return false;
                }
                var index  = $queuelist.find("li").length;
                file.tabIndex = index;
            });

            // 当有文件添加进来时执行，负责view的创建
            function addFile(file) {
                var $li = $('<li id="' + file.id + '" class="file-item" tab="'+file.tabIndex+'" tabIndex="'+file.tabIndex+'" ></li>');
                var $content = $('<div class="file-content"></div>').appendTo($li);
                var $imgwrap = $('<div class="imgWrap"></div>').appendTo($li.find(".file-content"));
                var $statusBar = $('<div class="statusBar"><div class="panelbg"></div></div>').appendTo($li.find(".file-content"));
                var $imgwait = $("<div class='imgwait'></div>");
                $btns = $('<div class="file-panel">' +
                    '<span title="删除" class="icon cancel"></span>' +
                    '<span title="向右旋转" class="icon rotateRight"></span>' +
                    '<span title="向左旋转" class="icon rotateLeft"></span>' +
                    '<span title="设为封面" class="icon tag"></span>' +
                    '</div>').appendTo($li.find(".statusBar"));

                var $progress = $('<div class="progress"><span></span></div>')
                    showError = function(code) {
                        switch (code) {
                            case 'exceed_size':
                                text = '文件大小超出';
                                break;

                            case 'interrupt':
                                text = '上传暂停';
                                break;

                            default:
                                text = '上传失败，请重试';
                                break;
                        }
                    };

                if (file.getStatus() === 'invalid') {
                    showError(file.statusText);
                } else {
                    // @todo lazyload
                    $imgwrap.append($imgwait);
                    uploader.makeThumb(file, function(error, src) {
                        var img;
                        if (error) {
                            $imgwrap.text($imgwait);
                            return;
                        }

                        if (isSupportBase64) {
                            img = $('<img src="' + src + '">');
                            $imgwrap.empty().append(img);
                        } else {
                            $.ajax(previewPath, {
                                method: 'POST',
                                data: src,
                                dataType: 'json'
                            }).done(function(response) {
                                if (response.result) {
                                    img = $('<img src="' + response.result + '">');
                                    $imgwrap.empty().append(img);
                                } else {
                                    $imgwrap.append($imgwait);
                                }
                            });
                        }
                    }, options.thumbnailWidth, options.thumbnailHeight);

                    percentages[file.id] = [file.size, 0];
                    file.rotation = 0;
                }

                file.on('statuschange', function(cur, prev) {
                    if (prev === 'progress') {
                        //$prgress.hide().width(0);
                    } else if (prev === 'queued') {
                        $li.off('mouseenter mouseleave');
                        $btns.remove();
                    }

                    // 成功
                    if (cur === 'error' || cur === 'invalid') {
                        showError(file.statusText);
                        percentages[file.id][1] = 1;
                    } else if (cur === 'interrupt') {
                        showError('interrupt');
                    } else if (cur === 'queued') {
                        percentages[file.id][1] = 0;
                    } else if (cur === 'progress') {
                        $li.find(".statusBar").find(".file-panel").remove();
                        $statusBar.append($progress);
                        $statusBar.css({
                            'bottom': '0'
                        });
                    } else if (cur === 'complete') {
                        $statusBar.css({
                            'bottom': '0'
                        });
                    }
                    $li.removeClass('state-' + prev).addClass('state-' + cur);
                });

                $li.on('mouseenter', function() {
                    $li.find('.statusBar').animate({
                        "bottom": 0
                    }, 300);
                });

                $li.on('mouseleave', function() {
                    $li.find('.statusBar').animate({
                        "bottom": "-=" + 20
                    }, 300);
                });

                $btns.on('click', 'span', function() {
                    var index = $(this).index(),
                        deg;
                    switch (index) {
                        case 0:
                            uploader.removeFile(file);
                            return;
                        case 1:
                            file.rotation += 90;
                            break;
                        case 2:
                            file.rotation -= 90;
                            break;
                        case 3:
                            $queue.find("li").not($li).removeClass('img_on').removeAttr("data-on");
                            if(typeof $li.attr("data-on") == 'undefined'){
                                $li.addClass('img_on');
                                $li.attr("data-on","2");
                            }else{
                                $li.removeClass('img_on').removeAttr("data-on");
                            }

                            break;
                    }

                    if (supportTransition) {
                        deg = 'rotate(' + file.rotation + 'deg)';
                        $imgwrap.css({
                            '-webkit-transform': deg,
                            '-mos-transform': deg,
                            '-o-transform': deg,
                            'transform': deg
                        });
                    } else {
                        $imgwrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
                    }
                });
                $li.appendTo($queue);
            }

            $retBtn.on('click', function() {
                $queue.find("li").each(function() {
                    var id = $(this).attr("id");
                    if(id.indexOf("my_") == -1){
                        var $file = {
                            "id": $(this).attr("id")
                        };
                        removeFile($file);
                    }
                });
                uploader.reset();
                setState("pedding");
            });

            // 负责view的销毁
            function removeFile(file) {
                var $li = $('#' + file.id);
                delete percentages[file.id];
                updateTotalProgress();
                $li.off().find('.file-panel').off().end().remove();
            }

            function updateTotalProgress() {
                var loaded = 0,
                    total = 0,
                    spans = $progress.children(),
                    percent;

                $.each(percentages, function(k, v) {
                    total += v[0];
                    loaded += v[0] * v[1];
                });
                percent = total ? loaded / total : 0;
                updateStatus();
            }

            function updateStatus() {
                var text = '',
                    stats;

                if (state === 'ready') {
                    text = '选中' + fileCount + '张图片，共' +
                        WebUploader.formatSize(fileSize) + '。';
                } else if (state === 'confirm') {
                    stats = uploader.getStats();
                    if (stats.uploadFailNum) {
                        text = '已成功上传' + stats.successNum + '张照片至XX相册，' +
                            stats.uploadFailNum + '张照片上传失败，<a class="retry" href="#">重新上传</a>失败图片或<a class="ignore" href="#">忽略</a>'
                    }

                } else {
                    stats = uploader.getStats();
                    text = '共' + fileCount + '张（' +
                        WebUploader.formatSize(fileSize) +
                        '），已上传' + stats.successNum + '张';

                    if (stats.uploadFailNum) {
                        text += '，失败' + stats.uploadFailNum + '张';
                    }
                }
            }

            function setState(val) {
                var file, stats;

                if (val === state) {
                    return;
                }

                $upload.removeClass('state-' + state);
                $upload.addClass('state-' + val);
                state = val;
                switch (state) {
                    case 'pedding':
                        uploader.refresh();
                        $filePicker.find('.webuploader-pick').removeClass('btn-disabled');
                        $filePicker.find('input[type=file]').attr("disabled", false);
                        $upload.attr("disabled", "disabled");
                        $retBtn.attr("disabled", "disabled");
                        break;
                    case 'ready':
                        $upload.attr("disabled", false);
                        $retBtn.attr("disabled", false);
                        uploader.refresh();
                        break;
                    case 'uploading':
                        $filePicker.find('.webuploader-pick').addClass('btn-disabled')
                        $filePicker.find('input[type=file]').attr("disabled", "disabled");
                        $upload.attr("disabled", "disabled");
                        $retBtn.attr("disabled", "disabled");
                        break;
                    case 'paused':
                        $filePicker.find('.webuploader-pick').removeClass('btn-disabled');
                        $filePicker.find('input[type=file]').attr("disabled", false);
                        break;
                    case 'confirm':
                        stats = uploader.getStats();
                        if (stats.successNum && !stats.uploadFailNum) {
                            setState('finish');
                            return;
                        }
                        break;
                    case 'finish':
                        stats = uploader.getStats();
                        if (stats.successNum) {
                            //alert( '上传成功' );
                            $filePicker.find('.webuploader-pick').removeClass('btn-disabled');
                            $filePicker.find('input[type=file]').attr("disabled", false);
                        } else {
                            // 没有成功的图片，重设
                            state = 'done';
                            location.reload();
                        }
                        break;
                }

                //updateStatus();
            }
             /**
             * 初始化对象成功后,绑定数据
             */
           function addMyFiles(){
                var $files = $wrap.attr("data-data");
                if($files == ""){
                    return false;
                }

                if(typeof $files == 'string'){
                    $files = eval('('+$files+')');
                }
                var $fileNum = 0;
                for(var i in $files){
                    addMyFile($files[i]);
                    $fileNum +=1;
                }

                if(uploader.options.fileNumLimit == $fileNum){
                    $filePicker.find('.webuploader-pick').addClass('btn-disabled')
                    $filePicker.find('input[type=file]').attr("disabled", "disabled");
                }

                function addMyFile($file){
                    var index = $queuelist.find("li").length;
                    var $li = $('<li id="my_file' + $file.id + '" data-id="' + $file.id + '" tabIndex="'+index+'" tab="'+index+'"  class="file-item"></li>');
                    var $content = $('<div class="file-content"></div>').appendTo($li);
                    var $imgwrap = $('<div class="imgWrap"></div>').appendTo($li.find(".file-content"));
                    var $statusBar = $('<div class="statusBar"><div class="panelbg"></div></div>').appendTo($li.find(".file-content"));

                    if($file.img_on != 0){
                        $li.addClass('img_on');
                    }
                    $btns = $('<div class="file-panel">' +
                        '<span title="删除" class="icon cancel"></span>' +
                        '<span title="设为封面" class="icon tag"></span>' +
                        '</div>').appendTo($li.find(".statusBar"));

                    var $progress = $('<div class="progress"><span></span></div>'),
                        $imgWrap = $li.find('div.imgWrap');
                    var src="";//路径

                    if(typeof($file.img_host)!="undefined" && $file.img_host=="qiniu")
                    {
                        src = "http://"+options.host+"/"+$file.img_path;
                    }else{
                        if(typeof $file.img != "undefined"){
                            $file.img_path = $file.img_path+"s_"+$file.img;
                        }
                        src ="http://"+options.old_host+"/"+$file.img_path;
                    }
                    var img = $('<img src="'+src+ '">');
                    img.appendTo($imgwrap);
                    $li.appendTo($queue);

                    $btns.on('click', 'span', function() {
                        var index = $(this).index(),
                            deg;
                        switch (index) {
                            case 0:
                                removeMyFile($file);
                                return;
                            case 1:
                                $queue.find("li").not($li).removeClass('img_on').removeAttr("data-on");
                                if(typeof $li.attr("data-on") == 'undefined'){
                                    $li.addClass('img_on');
                                    $li.attr("data-on","1");
                                }else{
                                    $li.removeClass('img_on').removeAttr("data-on");
                                }
                                break;
                        }
                    });

                    $li.on('mouseenter', function() {
                        $li.find('.statusBar').animate({
                            "bottom": 0
                        }, 300);
                    });

                    $li.on('mouseleave', function() {
                        $li.find('.statusBar').animate({
                            "bottom": "-=" + 20
                        }, 300);
                    });
                    return $li;
                }

                function removeMyFile(file){
                    var $key = file.img_path;
                    var $id = file.id;
                    $.ajax({
                        url: options.removePath,
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            id: $id,
                            key:$key
                        },
                        success:function(data){
                            if(data.status == 1){
                                var $li = $('#my_file' + file.id);
                                $li.remove();
                                $filePicker.find('.webuploader-pick').removeClass('btn-disabled')
                                $filePicker.find('input[type=file]').attr("disabled", false);
                                //重新排序
                                var length = $queue.find(".file-item").length;
                                for (var i = 0; i < length; i++) {
                                    var li = $queue.find(".file-item").eq(i);
                                    li.attr("tabIndex",i);
                                   // uploader.getFiles()[li.attr("tab")].tabIndex = i;
                                };
                                $(".uploader-info").html(data.info);
                            }else{
                                $(".uploader-info").html(data.info);
                            }
                        },
                        error:function(xhr){

                        }
                    });
                }
            }

            uploader.onUploadProgress = function(file, percentage) {
                var $li = $('#' + file.id),
                    $percent = $li.find('.progress span');

                $percent.css('width', percentage * 100 + '%');
                percentages[file.id][1] = percentage;
                updateTotalProgress();
            };

            uploader.onFileQueued = function(file) {
                fileCount++;
                fileSize += file.size;

                if (fileCount === 1) {
                    $statusBar.show();
                }

                addFile(file);
                setState('ready');
                updateTotalProgress();
            };

            uploader.onFileDequeued = function(file) {
                fileCount--;
                fileSize -= file.size;

                if (!fileCount) {
                    setState('pedding');
                }

                removeFile(file);
                updateTotalProgress();
            };

            uploader.on('all', function(type) {
                var stats;
                switch (type) {
                    case 'uploadFinished':
                        setState('confirm');
                        break;

                    case 'startUpload':
                        setState('uploading');
                        break;

                    case 'stopUpload':
                        setState('paused');
                        break;
                }
            });

            uploader.on("uploadAccept", function(block, ret, fn) {
                if (ret.status == 1) {
                    return true;
                }
                return false;
            });

            uploader.onUploadSuccess = function(file, res) {
                $wrap.find('#' + file.id).find('.progress').remove();
                $wrap.find('#' + file.id).find(' .statusBar').append('<div class="success"><span>上传成功</span></div>');
                res["tabIndex"] = file.tabIndex;
                res["id"] = file.id;
                if(typeof options.callback == "function"){
                    options.callback(res);
                }
            }

            uploader.onUploadError = function(file, res) {
                $wrap.find('#' + file.id).find('.progress').remove();
                $wrap.find('#' + file.id).find('.statusBar').append('<div class="error"><span>上传失败</span></div>');
                $info.html(res.info);//回显错误信息
                $retBtn.attr("disabled", false);//将重置按钮解除锁定
            }

            uploader.onError = function(code) {
                //alert( 'Eroor: ' + code );
            };

            $upload.on('click', function() {
                if ($(this).hasClass('disabled')) {
                    return false;
                }
                if (state === 'ready') {
                    uploader.upload();
                } else if (state === 'paused') {
                    uploader.upload();
                } else if (state === 'uploading') {
                    uploader.stop();
                }
            });
            $upload.addClass('state-' + state);
            updateTotalProgress();
        }
    }

    $.fn.uploader = function(method) {
        if(methods[method]) {
            return methods[method].apply(this, arguments);
        } else if( typeof(method) == 'object' || !method ) {
           return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.goalProgress' );
            return this;
        }
    }

})(jQuery);
