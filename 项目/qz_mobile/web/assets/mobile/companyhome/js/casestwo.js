$(function () {
    var _href = window.location.href;
    var currentUrl = $('.currentUrl').data('url')
    var temp = ''

    function parseQueryString(url) {
        var obj = {};
        var keyvalue = [];
        var key = "",
            value = "";
        var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
        for (var i in paraString) {
            keyvalue = paraString[i].split("=");
            key = keyvalue[0];
            value = keyvalue[1];
            obj[key] = value;
        }
        return obj;
    }

    var width = $('#menuTitle').children('li').length * 0.83 + 'rem';
    $('#menuTitle').css('width', width);
    var jroll = new JRoll("#wrapper", {scrollX: true, scrollY: false});
    var _params = parseQueryString(_href)

    if (_params.category) {
        var index = _params.category
        $("#menuTitle li").each(function () {
            if ($(this).data('id') == index) {
                $(this).addClass('cur').siblings().removeClass('cur')
                $('.all').removeClass('cur')
                var leftWidth = $('li.cur').offset().left;
                if ($("#menuTitle").find('li').hasClass('cur')) {
                    if ($('.cur').offset().left > 170) {
                        jroll.scrollTo(-leftWidth + 170, 0, 300, false);
                    }
                    /* else {
                         jroll.scrollTo(-leftWidth + 10, 0, 300, true);
                     }*/
                }
                return false
            }
        })
    } else {
        $('.all').addClass('cur')
    }

    $("#menuTitle li").click(function () {
        var index = $(this).data('id')
        _params.category = index
        if (!_params.class) {
            _params.class = ''
        }
        if (!_params.p) {
            _params.p = ''
        }
        window.location.href = currentUrl + '?' + 'class=' + _params.class + '&category=' + _params.category
    })

    if (_params.class) {
        switch (_params.class) {
            case '1':
                $('.type').show();
                $('.type').children('span').html('家装');
                $('.chooseType').hide();
                break;
            case '2':
                $('.type').show();
                $('.type').children('span').html('公装');
                $('.chooseType').hide();
                break;
            case '3':
                $('.type').show();
                $('.type').children('span').html('工地');
                $('.chooseType').hide();
                break;
            default:
                break;
        }
    } else {
        $('.type').show();
        $('.type').children('span').html('类型');
        $('.chooseType').hide();
    }


    $(".chooseType span").click(function () {
        temp = $(this).data('id')
        if (temp == '0') {
            temp = ''
        }
        if (temp == undefined) {
            $('.chooseType').hide()
            $('.type').show()
            return false
        }
        _params.class = temp
        if (!_params.category) {
            _params.category = ''
        }
        if (!_params.p) {
            _params.p = ''
        }
        window.location.href = currentUrl + '?' + 'class=' + temp + '&category=' + _params.category
    })

    // 首次加载
    // $("#footer").hide();
    $('.type').click(function () {
        $(this).hide().siblings('.chooseType').show();
    })
    $('.chooseType').click(function () {
        $(this).hide().siblings('.type').show();
    })


})
