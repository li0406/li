swfobject.addDomLoadEvent(function () {
    var swf = new fullAvatarEditor("swfContainer", {
            id: 'swf',
            upload_url: '/uplogo/', //上传路径
            method: "POST",
            src_upload: 0,//默认为0；是否上传原图片的选项，有以下值：0:不上传；1:上传；2 :显示复选框由用户选择
            isShowUploadResultIcon: false,//在上传完成时（无论成功和失败），是否显示表示上传结果的图标
            src_size: "2MB",//选择的本地图片文件所允许的最大值，必须带单位，如888Byte，88KB，8MB
            src_size_over_limit: "文件大小超出2MB，请重新选择图片。",//当选择的原图片文件的大小超出指定最大值时的提示文本。可使用占位符{0}表示选择的原图片文件的大小。
            // src_box_width: "300",//原图编辑框的宽度
            // src_box_height: "300",//原图编辑框的高度
            tab_visible: false,//是否显示选项卡*
            // browse_box_width: "300",//图片选择框的宽度
            // browse_box_height: "300",//图片选择框的高度
            avatar_sizes: "150*150",  //生成的头像大小
            avatar_sizes_desc: "150*150像素",
        }, function (msg) {
            switch (msg.code) {
                //case 1 :
                // alert("页面成功加载了组件！");
                // break;
                //case 2 :
                //alert("已成功加载图片到编辑面板。");
                // break;
                case 3 :
                    if (msg.type == 0) {
                        layer.msg("摄像头已准备就绪且用户已允许使用。", {time: 1300});
                    } else if (msg.type == 1) {
                        layer.msg("摄像头已准备就绪但用户未允许使用!", {time: 1300});
                    } else {
                        layer.msg("摄像头被占用!", {time: 1300});
                    }
                    break;
                case 5 :
                    if (msg.type == 0) {
                        if (msg.content.success == true) {
                            $(".sj-touxiangtk").hide();
                            $(".mask").hide()
                            $(".sj-success").show().delay(2000).hide(0);
                            //替换头像地址
                            var src = "http://" + msg.content.data;
                            $(".logoImg").attr("src", src);
                            $("input[name='logo-img']").val(src);
                        }
                    } else {
                        console.log('上传失败！：' + msg.type);
                    }
                    break;
            }
        }
    );
    $("#upload").click(function () {
        //启用上传
        swf.call("upload");
    });
});


$(function () {
    //商家信息


    // 下拉多选
    var dataJson = {
        "option": [
            {
                "id": "卧室家具",
                "name": "卧室家具",
                "child": [
                    {
                        "id": "床类",
                        "name": "床类"
                    },
                    {
                        "id": "床垫类",
                        "name": "床垫类"
                    },
                    {
                        "id": "卧室柜类",
                        "name": "卧室柜类"
                    },
                    {
                        "id": "卧室其他分类",
                        "name": "卧室其他分类"
                    }
                ]
            },
            {
                "id": "客厅家具",
                "name": "客厅家具",
                "child": [
                    {
                        "id": "沙发类",
                        "name": "沙发类"
                    }, {
                        "id": "茶几类",
                        "name": "茶几类"
                    },
                    {
                        "id": "客厅柜类",
                        "name": "客厅柜类"
                    },
                    {
                        "id": "客厅其他分类",
                        "name": "客厅其他分类"
                    }
                ]
            },
            {
                "id": "餐厅家具",
                "name": "餐厅家具",
                "child": [
                    {
                        "id": "餐桌类",
                        "name": "餐桌类"
                    },
                    {
                        "id": "餐椅类",
                        "name": "餐椅类"
                    },
                    {
                        "id": "餐边柜类",
                        "name": "餐边柜类"
                    },
                    {
                        "id": "餐厅其他分类",
                        "name": "餐厅其他分类"
                    }
                ]
            },
            {
                "id": "书房家具",
                "name": "书房家具",
                "child": [
                    {
                        "id": "书桌/工作台",
                        "name": "书桌/工作台"
                    },
                    {
                        "id": "书柜/书架",
                        "name": "书柜/书架"
                    }, {
                        "id": "书椅/转椅",
                        "name": "书椅/转椅"
                    }, {
                        "id": "书房其他分类",
                        "name": "书房其他分类"
                    }
                ]
            },
            {
                "id": "儿童家具",
                "name": "儿童家具",
                "child": [
                    {
                        "id": "儿童床",
                        "name": "儿童床"
                    }, {
                        "id": "儿童床垫",
                        "name": "儿童床垫"
                    }, {
                        "id": "儿童柜类",
                        "name": "儿童柜类"
                    }, {
                        "id": "儿童桌/椅",
                        "name": "儿童桌/椅"
                    }, {
                        "id": "儿童其他分类",
                        "name": "儿童其他分类"
                    }
                ]
            },
            {
                "id": "其他家具",
                "name": "其他家具",
                "child": [
                    {
                        "id": "户外桌/椅",
                        "name": "户外桌/椅"
                    }, {
                        "id": "办公桌/椅",
                        "name": "办公桌/椅"
                    }, {
                        "id": "办公沙发",
                        "name": "办公沙发"
                    }, {
                        "id": "其他",
                        "name": "其他"
                    }
                    ]
            },
            {
                "id": '全屋家具',
                "name": "全屋家具",
                "child": [
                    {
                        "id": "全部家具",
                        "name": "全部家具"
                    }
                    ]
            }

        ]
    };
    var l1 = 0;
    var cname1, cname2;
    var cid1, cid2;
    var canClick = true;
    var canClose = false;
    var plArry = jjpl_val;
    $('.sj-select-container').click(function () {
        $('.select-box').toggle();
        $('.sj-tips').hide();
        if (canClick) {
            $('ul', $('.select-box')).html('');
            fillData();
            canClick = false;
        }
        $('.arrow-icon').toggleClass('fa-sort-up')

    });

    $('body').on('click', '.closeHandle', function (e) {
        $(this).parent().remove();
        plArry.splice(plArry.indexOf($(this).parent().data('value')),1)
        if (!$('.sj-select-chose').children().length) {
            $('.select-box').hide();
            $('.sj-select-tips').show();

            $('.arrow-icon').removeClass('fa-sort-up').addClass('fa-sort-down')
        }
    });
    $('ul.first', $('.select-box')).on('click', 'li', function () {
        $(this).addClass('selected').siblings().removeClass('selected');
        $('ul.second').html('');
        fillData($(this).index());
        l1 = $(this).index();
        cname1 = $(this).text();
        cid1 = $(this).data('value');
        canClose = false;
    });
    $('ul.second', $('.select-box')).on('click', 'li', function () {
        $(this).addClass('selected').siblings().removeClass('selected');
        var _this = $(this)
        cname2 = _this.text();
        cid2 = _this.data('value');
        canClose = true;
        var hasExist = false;
        if(plArry.indexOf(cid2)!=-1){
            plArry.splice(plArry.indexOf(cid2),1)
        }
        // 重复多选
        var that = null;
        $('.sj-select-chose').find('li').each(function () {
            if ($(this).text().split('x')[1] == cname2) {
                hasExist = true;
                that = $(this);
            }
        });
        hasExist ? that.remove() : $('.sj-select-container ul').append('<li data-value="' + _this.text() + '">' + '<span class="closeHandle">x</span>' + cname2 + '</li>');

        if ($('.sj-select-container ul').find('li')) {
            plArry=[]
            $('.sj-select-chose').find('li').each(function (i) {
                plArry.push($(this).data('value'))
            })
        }
        $('.sj-select-tips').hide();
        $('.select-box').hide();
        $('.arrow-icon').addClass('fa-sort-down').removeClass('fa-sort-up')
    });

    //填充级联数据
    function fillData(l1, l2) {
        var temp_html = "";
        if (typeof(dataJson.option) != 'undefined' && arguments.length == 0) {
            $.each(dataJson.option, function (i, pro) {
                temp_html += '<li data-value="' + pro.id + '">' + pro.name + '</li>';
            });
        } else if (typeof(dataJson.option[l1].child) != 'undefined' && arguments.length == 1) {
            $.each(dataJson.option[l1].child, function (i, pro) {
                temp_html += '<li data-value="' + pro.id + '">' + pro.name + '</li>';
            });
        }
        $('.select-box ul:eq(' + arguments.length + ')').html(temp_html);
    }

//--------------------------------------------------
    $(".sj-shangchuan").click(function () {
        $(".mask").show();
        $(".sj-touxiangtk").show();
    });
    $(".sj-closeqx").click(function () {
        $(".mask").hide();
        $(".sj-touxiangtk").hide();
    });

    //展开、收起
    var flag = true;
    $(".sj-toggle").click(function () {
        if (flag) {
            $(".sj-info-form").hide();
            $(".sj-toggle .sj-toggle-name").text("展开");
            $('.sj-toggle i').removeClass("fa-angle-up").addClass('fa-angle-down');
            flag = !flag;
        } else {
            $(".sj-info-form").show();
            $(".sj-toggle .sj-toggle-name").text("收起");
            $('.sj-toggle i').removeClass("fa-angle-down").addClass('fa-angle-up');
            flag = !flag;
        }
    });

    var itemList1 = [{id: '定制家具', text: '定制家具'}, {id: '家具成品', text: '家具成品'}, {id: '品牌加盟', text: '品牌加盟'}, { id: '品牌工厂店', text: '品牌工厂店'}, {id: '其他', text: '其他'}];
    var itemList2 = [{id:'请选择',text:'请选择'},{id: '欧式风格', text: '欧式风格'}, {id: '现代简约风格', text: '现代简约风格'}, {id: '中式风格',text: '中式风格'}, {id: '古典风格', text: '古典风格'}, {id: '美式风格', text: '美式风格'}, {id: '田园风格', text: '田园风格'}, { id: '地中海风格',text: '地中海风格'}, {id: '混搭风格', text: '混搭风格'}, {id: '其他', text: '其他'}, {id: '全部包含', text: '全部包含'}];
    var itemList3 = [{id:'请选择',text:'请选择'},{id: '中端家具', text: '中端家具'}, {id: '中高端家具', text: '中高端家具'}, {id: '高端家具', text: '高端家具'}, {
        id: '全部包含',
        text: '全部包含'
    }];

    //家具商
    $("#sj-jy-type").select2({
        placeholder: "请选择",
        allowClear: false,
        tags: true,
        multiple: true,
        data: itemList1
    });

    // 家具风格
    $("#sj-fg-style").select2({
        placeholder: "请选择",
        tags: true,
        multiple: false,
        data: itemList2
    });
    $("#sj-fg-style").val('').trigger('change');
    // 产品等级
    $("#sj-cp-grade").select2({
        placeholder: "请选择",
        tags: true,
        multiple: false,
        data: itemList3
    });
    $("#sj-cp-grade").val('').trigger('change');

    if (jylx_val.length && jylx_val[0] != '') {
        $("#sj-jy-type").val(jylx_val).trigger("change");
    }
    if (jjpl_val.length > 0 && jjpl_val[0] != '') {
        $('.sj-select-tips').hide()
        var temp = '';
        for (var i = 0; i < jjpl_val.length; i++) {
            temp += "<li data-value=" + jjpl_val[i] + "><span class='closeHandle'>x</span>" + jjpl_val[i] + "</li>"
        }
        $(".sj-select-chose").append(temp)
    }
    if (jjfg_val.length && jjfg_val[0] != '') {
        $("#sj-fg-style").val(jjfg_val).trigger("change");
    }
    if (jjdg_val.length && jjdg_val[0] != '') {
        $("#sj-cp-grade").val(jjdg_val).trigger("change");
    }

    $('.edit_info_btn').on("click", function () {
        var jjsName = $("input[name='qc']").val().trim(),
            jjsShortName = $("input[name='jc']").val().trim(),
            lxrName = $("input[name='name']").val(),
            telNum = $("input[name='tel']").val().trim(),
            calsNum = $("input[name='cals']").val().trim(),
            callNum = $("input[name='cal']").val().trim(),
            sex = $("input[type='radio']:checked").val(),
            addres = $("input[name='dz']").val().trim(),
            qqNum = $("input[name='nickname']").val().trim(),
            logoImg = $("input[name='logo-img']").val();
        var jylx = $('#sj-jy-type').select2('val'),
            jjpl = plArry,
            jjfg = $('#sj-fg-style').select2('val'),
            jjdg = $('#sj-cp-grade').select2('val'),
            jjpp = $('#sj-jj-brand').val(),
            jjfw = $("#sj-jy-content").val();
        if (jjsName == '') {
            msg("家具商名称不能为空~");
            return false;
        }
        var data = {};
        data.qc = jjsName;
        data.jc = jjsShortName;
        data.name = lxrName;
        data.tel = telNum;
        data.qq = qqNum;
        data.logo = logoImg;
        data.cal = callNum;
        data.dz = addres;
        data.sex = sex;
        data.cals = calsNum;
        if (jylx != null && jylx.length > 0) {
            data.sale_type = jylx;
        }
        if (jjpl != null && jjpl.length > 0) {
            data.furniture_category = jjpl.join(',');
        }
        if (jjfg != null && jjfg.length > 0) {
            data.furniture_style = jjfg;
        }
        if (jjdg != null && jjdg.length > 0) {
            data.furniture_level = jjdg;
        }
        data.sale_range = jjfw;
        data.furniture_brand = jjpp;
        $.ajax({
            url: '/user/editinfo',
            method: "POST",
            dataType: "JSON",
            data: data
        }).done(function (res) {
            if (res.status == 1) {
                msg(res.info, function () {
                    $(".mask").hide();
                    $(".sj-touxiangtk").hide();
                    $(".sj-shangchuan").html("更改头像");
                    window.location.reload();
                });
            } else {
                msg(res.info);
            }
        })
            .fail(function (xhr) {
                msg("未知错误，请刷新重试")
            });
    })
});

function msg(msg, fn) {
    layer.msg(
        msg,
        {time: 1300},
        fn || function () {

        }
    )
}
