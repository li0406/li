$(function () {
    var keyword = $('input[name=keyword]').val();
    sessionStorage.removeItem("page");
    $('.search_main_title').on('click','li',function(){
        $(this).addClass('active').siblings('li').removeClass('active');
        module = $(this).attr('data-id');
        if (module == "") {
            window.location.href = "/search/?keyword="+keyword;
        } else {
            window.location.href = "/search/"+module+"/?keyword="+keyword;
        }
    });

    $('.getMore').click(function () {
        var p = sessionStorage.getItem("page");
        if (p == null) {
            p = 1;
        }
        p = parseInt(p) + 1;
        sessionStorage.setItem("page",p);
        getMoreList(keyword,p,module);
    })

    function  getMoreList(keyword,page,m) {
        $.ajax({
            type: "POST",
            url: '/search/getsearchlist',
            dataType: "JSON",
            data: {
                keyword: keyword,
                page: page,
                module: m
            },
            success : function(data){
                if(data.error_code == 0){
                    if (data.data != null){
                        var tpl = "";
                        switch (m) {
                            case "gonglue":
                                for(var i in data.data){
                                    if (data.data[i].three_short_name == null) {
                                        continue;
                                    }
                                    tpl +='<a href="/gonglue/'+ data.data[i].three_short_name +'/'+ data.data[i].id +'.html">'+
                                           '<div class="item clearfix">'+
                                                '<div class="thumb-pic">'+

                                                '<img src="'+data.data[i].img_path +'" alt="'+ data.data[i].title +'" title="'+ data.data[i].title +'">'+

                                                '</div>'+
                                                '<div class="item-main">'+
                                                '<div class="title text-nowrap">'+
                                                 data.data[i].title +
                                                '</div>'+
                                                '<div class="desc">'+
                                                data.data[i].description +
                                                '</div>'+
                                                '<div class="action">'+
                                                '<span class="eye-time">'+ data.data[i].createtime +'</span>'+
                                                '</div>'+
                                                '</div>'+
                                                '</div></a>'
                                }
                                $('.zx_ul').append(tpl);
                                break;
                            case "baike":
                                for(var i in data.data){
                                    if (data.data[i].id == null) {
                                        continue;
                                    }

                                    tpl += '<a href="/baike/'+ data.data[i].id +'.html">'+
                                            '<div class="item clearfix">'+
                                            '<div class="thumb-pic">'+
                                            '<img src="'+ data.data[i].img_path +'" alt="'+ data.data[i].title +'" title="'+ data.data[i].title +'">'+
                                            '</div>'+
                                            '<div class="item-main">'+
                                            '<div class="title text-nowrap">'+
                                            data.data[i].title+
                                            '</div>'+
                                            '<div class="desc">'+
                                            data.data[i].description+
                                            '</div>'+
                                            '<div class="action">'+
                                            '<span class="eye-time">'+ data.data[i].createtime +'</span>'+
                                            '</div>'+
                                            '</div>'+
                                            '</div></a>'

                                }
                                $('#bk_ul').append(tpl);
                                break;
                            case "wenda":
                                for(var i in data.data){
                                    if (data.data[i].id == null) {
                                        continue;
                                    }
                                    tpl += '<a href="/wenda/x'+ data.data[i].id +'.html">'+
                                            '<div class="wenda-item">'+
                                            '<div class="item-content">'+
                                            '<div class="item-top oflow">'+
                                            '<div class="user-img fl">'+
                                            '<img src="'+ data.data[i].user_logo +'" alt="'+ data.data[i].title +'">'+
                                            '</div>'+
                                            '<div class="user-name fl">'+ data.data[i].user_name +'</div>'+
                                            '</div>'+
                                            '<p class="item-body">'+
                                             data.data[i].title +
                                            '</p>'+
                                            '<div class="item-foot">'+
                                            '<span class="fl">'+ data.data[i].createtime +'</span>'+
                                            '<span class="fr"><i>'+ data.data[i].anwsers +'</i>人回答</span>'+
                                            '</div>'+
                                            '</div>'+
                                            '</div></a>'
                                }

                                $('#wd_ul').append(tpl);
                                break;
                        }
                    } else {
                        $('.getMore').attr("disabled","disabled").html('没有更多了')
                    }
                } else {
                    console.log('错误信息')
                }
            },
            error:function(xhr){
                console.log('网络错误')
            }
        })
    }

    var date = new Date().getTime();
    // 添加搜索缓存
    const fooLocalStorage = {
        set: function (key, value, ttl_ms) {
            var data = { value: value, expirse: new Date(ttl_ms).getTime() };
            localStorage.setItem(key, JSON.stringify(data));
        },
        get: function (key) {
            var data = JSON.parse(localStorage.getItem(key));
            if (data !== null) {
                if (data.expirse != null && data.expirse < new Date().getTime()) {
                    localStorage.removeItem(key);
                } else {
                    return data.value;
                }
            }
            return null;
        }
    };

    var historyData = fooLocalStorage.get("historyData");

    var time = 30 * 24 * 60 * 60 * 1000;
    document.addEventListener("keyup", function (evnet) {
        if (event.keyCode == 13) {
            var keyword = $('input[name=keyword]').val();
            if(keyword == ''){
                return
            }else{
                // 去重、倒序
                historyData.unshift(keyword);
                historyData = historyData.unique();
                fooLocalStorage.set("historyData", historyData, date + time);
                window.location.href = "/search?keyword="+keyword;
            }
        }
        return false;
    }, false)

    Array.prototype.unique = function () {
        var res = [];
        var json = {};
        for (var i = 0; i < this.length; i++) {
            if (!json[this[i]]) {
                res.push(this[i]);
                json[this[i]] = 1;
            }
        }
        return res;
    }
})