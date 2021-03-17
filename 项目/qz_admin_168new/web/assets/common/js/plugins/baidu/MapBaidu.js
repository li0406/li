// 封装百度地图
function MapBaidu() {
    if (!arguments.length)
        return  null;

    return  new MapBaidu.fn.__init__(arguments[0], arguments[1] || '');
}
MapBaidu.prototype  =
MapBaidu.fn =       {
city:       '苏州',
// 构造函数
__init__:   function(dom, city) {
    var map = new BMap.Map(dom);
    map.enableScrollWheelZoom();
    map.enableKeyboard();

    map.addControl(new BMap.ScaleControl());
    map.addControl(new BMap.NavigationControl());

    this._map   = map;
    this._geo   = new BMap.Geocoder();

    if (city)
        this.setCity(city);

    this._dis   = new BMapLib.DistanceTool(map);
    this._prepareMarkTool();
},

_prepareInfoWin:    function() {
    var me      = this;
    var html    = [ ];
    html.push('<table border="0" cellpadding="1" cellspacing="1" >');
    html.push('  <tr>');
    html.push('      <td align="left" class="common">描述：</td>');
    html.push('      <td colspan="2"><textarea rows="3" style="border:solid 1px " placeholder="标注描述" id="txtName"></textarea></td>');
    html.push('  </tr>');
    html.push('  <tr>');
    html.push('      <td align="left" class="common">地址：</td>');
    html.push('      <td colspan="2"><p id="txtAddr" lng="" lat=""></p></td>');
    html.push('  </tr>');
    html.push('  <tr>');
    html.push('      <td align="center" colspan="3">');
    html.push('          <input type="button" style="padding:7px;" name="btnOK" value="确定">&nbsp;&nbsp;');
    html.push('          <input type="button" style="padding:7px;" name="btnClear" value="删除">');
    html.push('      </td>');
    html.push('  </tr>');
    html.push('</table>');

    var el_obj  = $(html.join('\n')).
        css('width', '100%').css('height', '100%');
    el_obj.find('.common').css('width', '50px');

    var addr    = el_obj.find('#txtAddr').css('margin', '0px');
    var com     = el_obj.find('#txtName').css('width', '90%');

    var infoWin = new BMap.InfoWindow(el_obj[0], {
        width:  330,
        offset: new BMap.Size(0, -10)
    });
    el_obj.find('input[name="btnClear"]').click(function() {
        var pt  = infoWin.getPosition();
        me.delMark(pt, me.getMark(pt).get_id_());
    });
    el_obj.find('input[name="btnOK"]').click(function() {
        me.saveMark(infoWin.getPosition(), infoWin.getInfo());
    });

    infoWin.getInfo = function() {
        var txt     = com.val().split('\n');
        var point   = this.getPosition();
        var cname   = [ ];
        for (var v, j, i = txt.length; i--; )
            if (v = $.trim(txt[i])) {
                for (j = cname.length; j--; )
                    if (cname[j] === v)
                        break;
                if (j < 0)
                    cname.unshift(v);
            }

        return  {
            'lng':  point.lng,
            'lat':  point.lat,
            'com':  cname.join('\n'),
            'addr': me.getMark(point).getTitle()
        };
    }
    infoWin.loadInfo= function(data) {
        var info    = {
            'com':  '',
            'addr': ''
        };
        if (data) {
            info    = this.getInfo();
            var tags= 'com addr'.split(' ');
            for (var tag, i = tags.length; i--; ) {
                tag = tags[i];
                if ('undefined'!== typeof data[tag])
                    info[tag]   = data[tag] || '';
            }
        }
        addr.text(info['addr']);
        com.val(info['com']);
    }
    infoWin.addEventListener('close', function() {
        this.loadInfo();
    });
    /*
    infoWin.addEventListener('open', function() {
        com.trigger('focus');
    }); */

    me._infoWin   = infoWin;
},

// 为标注做准备
_prepareMarkTool:   function() {
    // 准备信息窗口
    this._prepareInfoWin();
    var infoWin = this._infoWin;

    var me      = this;
    var mkr     = new BMapLib.MarkerTool(me._map, {
        icon:       BMapLib.MarkerTool.SYS_ICONS[8],
        followText: "点击地图添加标注，按 ESC 取消"
    });

    mkr.addEventListener("markend", function(evt) {
        var mk  = evt.marker;
        me.mark(mk.getPosition(), '').openInfoWindow(infoWin);
        me._map.removeOverlay(mk);
    });

    this._mktool= mkr;
},

// 设置地图的中心城市
setCity:    function(city) {
    this.city   = city;
    this._map.centerAndZoom(city);
},

// 开启测距工具
startDis:   function() {
    this._dis.open();
},

// 启动标注工具
startMark:  function() {
    this._mktool.open();
},

// 关闭信息窗口
closeInfoWin:   function() {
    var infoWin = this._infoWin;
    infoWin.close();
},

// 地图搜索
search:     function(search_str, fn_on_search, be_center) {
    var cb  = fn_on_search || this._on_search;
    var me = this;
    this._geo.getPoint(search_str, function(point) {
        cb.call(me, point, search_str, be_center);
    }, this.city);
},

// 在地图上作标记
mark:       function(point, remark, addr, id) {
    var mk      = new BMap.Marker(point);
    var infoWin = this._infoWin;
    if (!addr) {
        this._geo.getLocation(point, function(rs) {
            var info= rs.addressComponents;
            mk.setTitle(addr = info.city + info.district + info.street + info.streetNumber);
            if (infoWin.isOpen())
                infoWin.loadInfo({ addr: addr, com: remark });
        });
    } else {
        mk.setTitle(addr);
    }
    mk.get_id_   = function() { return +id };

    mk.addEventListener('dblclick', function() {
        mk.openInfoWindow(infoWin);
        infoWin.loadInfo({ com: mk.getLabel().content });
    });

    var label   = new BMap.Label(remark, {
        offset: new BMap.Size(27, 3)
    });
    mk.setLabel(label);
    this._map.addOverlay(mk);

    return  mk;
},

// 从地图上取出一个标记
getMark:    function(point) {
    var map = this._map;
    var obj = map.getOverlays();

    for (var pt, i = obj.length; i--; )
        if (obj[i] instanceof BMap.Marker) {
            pt = obj[i].getPosition();
            if (pt && pt.lng == point.lng && pt.lat == point.lat)
                return  obj[i];
        }

    // 没有找到
    return  false;
}
};
MapBaidu.fn.__init__.prototype  = MapBaidu.fn;


MapBaidu.fn.getIcon = function (idx) {
    return  new BMap.Icon('/Public/js/BMap/icon.gif', new BMap.Size(24, 24));
}
// 搜索后的默认回调
MapBaidu.fn._on_search  = function(point, search_str, be_center) {
    if (point) {
        if (be_center) {
            var marker  = new BMap.Marker(point, { title: search_str });
            marker.setAnimation(BMAP_ANIMATION_BOUNCE);
            marker.setTitle(be_center);
            this._map.addOverlay(marker);
            this._map.setCenter(point);
        } else {
            var marker  = this.mark(point, '',  search_str);
            marker.setIcon(this.getIcon());
        }
    } else {
        alert('没有找到 '+ search_str +'！');
    }
}

// 保存标记
MapBaidu.fn.saveMark    = function(point, data) {
    var mk  = this.getMark(point);
    if (mk)
        mk.getLabel().setContent(data.com);
    this.closeInfoWin();
}
// 删除标记
MapBaidu.fn.delMark     = function(point, id) {
    var mk  = this.getMark(point);
    if (mk)
        this._map.removeOverlay(mk);
}
