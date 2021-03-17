$(function(){
// locationcity
function locateMe(where) {
    var nav = $('.menutop');
    var field   = [];
    var y, idx  = -1;
    while (true) {
        if (-1 == (y = where.indexOf(':', idx))) {
            field.push(where.substr(idx));
            break;
        }
        field.push(where.substring(idx, y));
        idx = y + 1;
        if (field.lenth > 2)
            break;
    }

    $('.weiz > span', nav).html(field[2]);
    $('.weiz a:first-child', nav).css('visibility','visible');
    $('.weiz a:first-child', nav).attr('href', 'http://'+ field[1] +'.qizuang.com/');
}

var geo_url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js';
var where   = $.cookie('whereami');

if (!where) {
    $.getScript(geo_url, function() {
        if (remote_ip_info.ret == '1') {
            $.getJSON('//Api.qizuang.com/Www/getMyCity', {
                'cs':   remote_ip_info.city
            }, function(data, state) {
                if (data.info == 'OK') {
                    data = data.data;
                    where= data.cid +':'+ data.bm +':'+ data.oldName,
                    $.cookie('whereami', where, { expires: 1 });
                    locateMe(where);
                }
            });
        }
    });
} else {
    locateMe(where);
}


});