$(function(){
    /*
        * 勾选审核不通过条件-----------------------------------------------
        * 含有广告
        * 含有联系方式
        * 禁用的LOGO、水印
        * 涉黄
        * 涉暴
        * 涉及国家机关或领导人
        * 其他原因勾选
    */

    $('.img-box').click(function(){
        var caseid = $(this).attr('data-id');
        var imgid = $(this).attr('imgid');
        showCaseBox(caseid,imgid);
    });

    function getPrevCase(){
        var prevcaseid = $('#previd').val();
        if(prevcaseid != ''){
            showCaseBox(prevcaseid);
        }else{
            alert('没有了');
        }
    }

    function getNextCase(act){
        var nextcaseid = $('#nextid').val();
        if(nextcaseid != ''){
            showCaseBox(nextcaseid);
        }else{
            alert('没有了')
        }
    }

    function setCaseImgStatus(id,status){
        $.ajax({
            url: '/imageverify/setCaseImgStatus/',
            type: 'POST',
            dataType: 'json',
            data: {
                id:id,
                status:status
            }
        })
        .done(function(data) {
            if(data.status == '1'){
                return true;
            }else{
                return false;
            }
        })
        .fail(function(xhr) {
            return false;
        })
    }

    function getReview(imgid){
        $('#reviewBox input').prop("checked",false);
        $('.other-info').hide();
        $('.other-info').val('');
        $('#appealBox').hide();

        $.ajax({
            url: '/imageverify/getImgReview/',
            type: 'POST',
            dataType: 'json',
            data: {
                id:imgid
            }
        })
        .done(function(data) {
            if(data.status == '1'){

                if(data.data.appeal){
                    $('#appealBox').show();
                    $('#appealBox').find('.time').html(data.data.appeal_time);
                    $('#appealBox').find('.contents').html(data.data.appeal);
                }
                if(data.data.reason){
                    $('.other-info').show();
                    $('.other-info').val(data.data.reason);
                    $('.other-input').attr("checked",true);
                }
                if(data.data.status != ''){
                    var status = data.data.status;
                    if(status != ''){
                        for(var o in status){
                            $('label input[value="'+status[o]+'"]').prop("checked",true);
                        }
                    }
                }

                //状态：0未审核 1已审核 2审核不通过 3申诉中
                var imgStatus = data.data.imgStatus;
                var statusText = '';
                var isPass = true;

                if(imgStatus == '1'){
                    statusText = '未审核';
                }else if(imgStatus == '2'){
                    statusText = '已审核';
                }else if(imgStatus == '3'){
                    isPass = false;
                    statusText = '审核不通过';
                }else if(imgStatus == '4'){
                    statusText = '申诉中';
                }else{
                    statusText = '未知';
                }
                $('#imgStatus').html(statusText);

                //状态切换
                if(isPass == true){
                    //禁用审核功能
                    $('#reviewBox input').each(function(){
                        $(this).attr('disabled',false);
                    });
                    $('#reviewBox textarea').attr('disabled',false);

                    $('#deleteImgBtn').show();
                    $('#passImgBtn').hide();
                }else{
                    //禁用审核功能
                    $('#reviewBox input').each(function(){
                        $(this).attr('disabled',true);
                    });
                    $('#reviewBox textarea').attr('disabled',true);

                    $('#deleteImgBtn').hide();
                    $('#passImgBtn').show();
                }
            }else if (data.status == '2'){
                //没有数据
            }else{
                alert('操作失败了');
                return false;
            }
        })
        .fail(function(xhr) {
            alert('操作失败');
            return false;
        })
    }

    function getUrlQuery(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }

    function showCaseBox(caseid, imgid) {
        if (!imgid) {
            imgid = 0;
        }

        var status = $('#searchStatus').val();
        if (status == '') {
            status = '1';
        }

        $('.modal-body').html('');
        $.ajax({
            url: '/imageverify/getcaseinfo/',
            type: 'GET',
            dataType: 'HTML',
            data: {
                id: caseid,
                imgid: imgid,
                cs: getUrlQuery('cs'),
                cid: getUrlQuery('cid'),
                des_id: getUrlQuery('des_id'),
                com_id: getUrlQuery('com_id'),
                status: getUrlQuery('status'),
                time_start: getUrlQuery('time_start'),
                time_end: getUrlQuery('time_end')
            }
        })
            .done(function (res) {
                $('.modal-body').html(res);

                var thisImg = $('.tuji-list').find("img[src='" + $('#slide-img').attr('src') + "']").parent();
                var id = thisImg.find('img').attr('data-id');
                getReview(id);
                thisImg.css('background', '#ccc');

                //其他原因勾选
                $('.other-input').click(function () {
                    if ($(this).is(':checked')) {
                        $('.other-info').show();
                    } else {
                        $('#otherReason').val('');
                        $('.other-info').hide();
                    }
                });

                // ---------评分块score-----------
                // 获取评分
                var scoreLen = $('#grade').val();
                var allLen = $('.star-item').length;
                if (scoreLen) { // 判断获取评分是否存在
                    for (var i = 0; i < scoreLen; i++) {
                        $('.star-item').eq(i).find('i').removeClass('fa-star-o').addClass('fa-star');
                    }
                }
                $('.star-item').each(function (index, el) { // 鼠标经过评分效果
                    $(this).hover(function () {
                        var indexLen = $(this).attr('index-num');
                        for (var i = 0; i < allLen; i++) {
                            $('.star-item').eq(i).find('i').removeClass('fa-star').addClass('fa-star-o');
                        }

                        for (var i = 0; i < indexLen; i++) {
                            $('.star-item').eq(i).find('i').removeClass('fa-star-o').addClass('fa-star');
                        }

                    }, function () {
                        var indexLen = $(this).attr('index-num');
                        for (var i = 0; i < allLen; i++) {
                            $('.star-item').eq(i).find('i').removeClass('fa-star').addClass('fa-star-o');
                        }

                        for (var i = 0; i < indexLen; i++) {
                            $('.star-item').eq(i).find('i').removeClass('fa-star-o').addClass('fa-star');
                        }
                    });
                });
                // 点击评分设置评分
                $('.star-item').click(function () {
                    scoreLen = $(this).attr('index-num');
                    $('#grade').val(scoreLen);
                    var id = $('#caseid').val();

                    $.ajax({
                        url: '/imageverify/setCaseGrade/',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: id,
                            grade: scoreLen
                        }
                    })
                        .done(function (data) {
                            if (data.status == '1') {
                                //do something.
                            } else {
                                alert('系统错误。');
                            }
                        })
                        .fail(function (xhr) {
                            alert('系统错误（xhr）');
                        })
                });
                // 当鼠标离开评分块的时候 设置默认评分
                $('.score-star').mouseleave(function () {
                    for (var i = 0; i < allLen; i++) {
                        $('.star-item').eq(i).find('i').removeClass('fa-star').addClass('fa-star-o');
                    }
                    if (scoreLen) {
                        for (var i = 0; i < scoreLen; i++) {
                            $('.star-item').eq(i).find('i').removeClass('fa-star-o').addClass('fa-star');
                        }
                    }
                });

                //审核通过图片
                $('#passImg').click(function () {

                    var thisImg = $('.tuji-list').find("img[src='" + $('#slide-img').attr('src') + "']").parent();
                    var nextImg = thisImg.next();
                    var nextImgstat = nextImg.html();
                    var id = thisImg.find('img').attr('data-id');
                    var caseid = $('#caseid').val();

                    setCaseImgStatus(id, 2);

                    if (nextImgstat) {
                        thisImg.css('background', 'none');
                        nextImg.css('background', '#ccc');
                        $('#slide-img').attr('src', nextImg.find('img').attr('src'));
                        getReview(nextImg.find('img').attr('data-id'));
                    } else {
                        getNextCase();
                    }
                });

                //删除图片
                $('#deleteImg').click(function () {

                    var thisImg = $('.tuji-list').find("img[src='" + $('#slide-img').attr('src') + "']").parent();
                    var nextImg = thisImg.next();
                    var nextImgstat = nextImg.html();
                    var id = thisImg.find('img').attr('data-id');
                    var caseid = $('#caseid').val();
                    var reason = [];
                    var otherReason = $('#otherReason').val();

                    $("[name='review']:checked").each(function () {
                        reason.push($(this).val());
                    });

                    $.ajax({
                        url: '/imageverify/setCaseImgStatus/',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: id,
                            caseid: caseid,
                            status: '3', //审核不通过
                            reason: reason,
                            otherReason: otherReason
                        }
                    })
                        .done(function (data) {
                            if (data.status == '1') {
                                //将当前图片状态设为不通过
                                thisImg.find('img').attr('status', '3');
                                //thisImg.remove();
                            } else {
                                alert('操作失败了');
                            }
                        })
                        .fail(function (xhr) {
                            alert('操作失败');
                        })

                    if (nextImgstat) {
                        thisImg.css('background', 'none');
                        nextImg.css('background', '#ccc');
                        $('#slide-img').attr('src', nextImg.find('img').attr('src'));
                        getReview(nextImg.find('img').attr('data-id'));
                    } else {
                        getNextCase();
                    }
                });

                // 跳过图片
                $('#skipImg').click(function () {
                    var thisImg = $('.tuji-list').find("img[src='" + $('#slide-img').attr('src') + "']").parent();
                    var nextImg = thisImg.next();
                    var nextImgstat = nextImg.html();
                    var id = thisImg.find('img').attr('data-id');
                    if (nextImgstat) {
                        thisImg.css('background', 'none');
                        nextImg.css('background', '#ccc');
                        $('#slide-img').attr('src', nextImg.find('img').attr('src'));
                        getReview(nextImg.find('img').attr('data-id'));
                    } else {
                        getNextCase('2');
                    }
                });

                // 图片审核 -----------------------------------------------

                // 向右点击->切换下一张图片
                $('#right-bar i').click(function () {
                    var thisImg = $('.tuji-list').find("img[data-id='" + $('#slide-img').attr('data-id') + "']").parent();
                    var nextImg = thisImg.next();
                    var nextImgstat = nextImg.html();
                    var id = thisImg.find('img').attr('data-id');

                    //状态：1未审核 2已审核 3审核不通过 4申诉中
                    var theStatus = thisImg.find('img').attr('status');
                    if (theStatus == '1') {
                        setCaseImgStatus(id, 2);
                    } else if (theStatus == '4') {
                        if (confirm("确认审核通过？")) {
                            setCaseImgStatus(id, 2);
                        }
                    }

                    if (nextImgstat) {
                        thisImg.css('background', 'none');
                        nextImg.css('background', '#ccc');
                        $('#slide-img').attr('src', nextImg.find('img').attr('src'));
                        $('#slide-img').attr('data-id', nextImg.find('img').attr('data-id'));
                        getReview(nextImg.find('img').attr('data-id'));
                    } else {
                        getNextCase('3');
                    }
                    return false;
                });

                //向左点击->切换上一张图片
                $('#left-bar i').click(function () {
                    var thisImg = $('.tuji-list').find("img[data-id='" + $('#slide-img').attr('data-id') + "']").parent();
                    var prevImg = thisImg.prev();
                    var prevImgstat = prevImg.html();
                    var id = thisImg.find('img').attr('data-id');

                    //状态：1未审核 2已审核 3审核不通过 4申诉中
                    var theStatus = thisImg.find('img').attr('status');
                    if (theStatus == '1') {
                        setCaseImgStatus(id, 2);
                    } else if (theStatus == '4') {
                        if (confirm("确认审核通过？")) {
                            setCaseImgStatus(id, 2);
                        }
                    }

                    if (prevImgstat) {
                        thisImg.css('background', 'none');
                        prevImg.css('background', '#ccc');
                        $('#slide-img').attr('src', prevImg.find('img').attr('src'));
                        $('#slide-img').attr('data-id', prevImg.find('img').attr('data-id'));
                        getReview(prevImg.find('img').attr('data-id'));
                    } else {
                        getPrevCase();
                    }
                });

                //向左点击->上一图集
                $('#prev-tuji').click(function () {
                    getPrevCase();
                });

                //向右点击->下一图集
                $('#next-tuji').click(function () {
                    getNextCase('4');
                });

                //审核不通过
                // 1未审核 2已审核 3审核不通过 4申诉中
                $('#deleteTuji').click(function () {
                    var id = $('#caseid').val();
                    var status = $(this).attr('data-id');
                    $.ajax({
                        url: '/imageverify/setCaseStatus/',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: id,
                            status: status
                        }
                    })
                        .done(function (data) {
                            if (data.status == '1') {
                                getNextCase('5');
                            } else {
                                alert('系统错误。');
                                return false;
                            }
                        })
                        .fail(function (xhr) {
                            alert('系统错误（xhr）');
                            return false;
                        })
                });
                // 预览缩略图列表点击切换大图
                $('.tuji-list .img-sm-item').on('click', function () {
                    var oSrc = $(this).find('img').attr('src');
                    var oDataId = $(this).find('img').attr('data-id');
                    var oIndexCount = $(this).attr('index-count');
                    $('.slide-item').find('img').attr('src', oSrc).attr('index-count', oIndexCount);
                    $('.slide-item').find('img').attr('data-id', oDataId);
                    $(this).css('background', '#ccc').siblings().css('background', 'none');
                    getReview($(this).find('img').attr('data-id'));
                });
            }).fail(function (xhr) {
            alert('系统错误（xhr）');
            return false;
        });
    }
});