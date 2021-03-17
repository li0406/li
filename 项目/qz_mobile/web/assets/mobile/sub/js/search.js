$(function(){
    $('.trash-icon').click(function(){
        $('.mask').show();
    });
    $('.del_cancel').click(function(){
        $('.mask').hide();
    });

    $('.del_sure').click(function(){
        localStorage.removeItem('historyData');
        $('.history').hide();
        $('.mask').hide();
    });
    $('.history_ul').on('click','li',function(){
        var keyword = $(this).text();
        location.href = "/search?keyword=" + keyword;
    });

    var historyData = [];
    var date = new Date().getTime();

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

    //获取localStorage的值
    var gethistoryData = fooLocalStorage.get("historyData");
    if (gethistoryData != null) {
        historyData = gethistoryData;
        var tpl = '';
        for(var i = 0; i < gethistoryData.length;i++){
            tpl += '<li>'+ gethistoryData[i] +'</li>'
        }
        $('.history_ul').html(tpl);
    }
    if($('.history_ul').children('li').length == 0){
        $('.history').hide();
    }

    var time = 30*24*60*60*1000;
    document.addEventListener("keyup", function(evnet){
        if(event.keyCode == 13){
            var keyword = $('input[name=keyword]').val();
            if(keyword == ''){
                return
            } else {
                // 去重、倒序
                historyData.unshift(keyword);
                historyData = historyData.unique();
                fooLocalStorage.set("historyData", historyData, date + time);
                window.location.href = "/search?keyword="+keyword;
            }
        }
        return false;
    }, false)

    Array.prototype.unique = function(){
        var res = [];
        var json = {};
        for(var i = 0; i < this.length; i++){
         if(!json[this[i]]){
          res.push(this[i]);
          json[this[i]] = 1;
         }
        }
        return res;
    }

})