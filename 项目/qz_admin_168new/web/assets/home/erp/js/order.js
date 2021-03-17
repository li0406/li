/*
* @Author: qz_dc
* @Date:   2018-08-15 11:16:25
* @Last Modified by:   qz_dc
* @Last Modified time: 2018-09-29 11:37:52
*/
$(function(){
    $('.select2').select2({
        allowClear: true,
        language: "zh-CN"
    });

    // 排序箭头
    // $('table').on('click','.fa-sort-desc',function(){
    //     $(this).removeClass('fa-sort-desc').addClass('fa-sort-asc')
    // });
    // $('table').on('click','.fa-sort-asc',function(){
    //     $(this).removeClass('fa-sort-asc').addClass('fa-sort-desc')
    // });

    $('.fa').click(function(event) {
        var dataType = $(this).attr('data-type');
        var dataOrder = $(this).attr('data-order');
        var dataStr = $('#dataform').serialize();
        window.location = '/yxb/order?ordertype='+dataType+'&order='+dataOrder+'&'+dataStr;
    });


    // 初始化日历插件
    timePicker('.fa-start-time');
    timePicker('.fa-end-time');
    timePicker('.jie-start-time');
    timePicker('.jie-end-time');

    // 重置按钮
    $('.reset').on('click',function(event) {
        // $('form')[0].reset();
        $('select[name=city]').select2("val","");
        $('input[name=qz_order_id]').val('');
        $('input[name=order_id]').val('');
        $('select[name=ordersource]').val('');
        $('select[name=orderstatus]').val('');
        $('select[name=shigongstatus]').val('');
        $('input[name=fa-start-time]').val('');
        $('input[name=fa-end-time]').val('');
        $('input[name=jie-start-time]').val('');
        $('input[name=jie-end-time]').val('');

        timePicker('.fa-start-time');
        timePicker('.fa-end-time');
        timePicker('.jie-start-time');
        timePicker('.jie-end-time');
    });

    // 搜索按钮
    $('.search').on('click',function(event){

        var faStartTime = $('.fa-start-time').val();
        var faEndTime = $('.fa-end-time').val();
        var jieStartTime = $('.jie-start-time').val();
        var jieEndTime = $('.jie-end-time').val();
        // if(faStartTime==""){
        //     alert("请选择发单开始时间");
        //     return false;
        // }
        // if(faEndTime==""){
        //     alert("请选择发单结束时间");
        //     return false;
        // }
        // if(jieStartTime==""){
        //     alert("请选择接单开始时间");
        //     return false;
        // }
        // if(jieEndTime==""){
        //     alert("请选择接单结束时间");
        //     return false;
        // }
        if(toTimeStamp(faStartTime)>toTimeStamp(faEndTime)){
            alert('分单时间-开始必须小于等于分单时间-结束')
            return false;
        }
        if(toTimeStamp(jieStartTime)>toTimeStamp(jieEndTime)){
            alert('接单时间-开始必须小于等于接单时间-结束')
            return false;
        }

        window.location = '/yxb/order/?'+$("#dataform").serialize();return;

    });
    // 日期转化成时间戳
    function toTimeStamp(time){
        var date = time;
        date = date.substring(0,19);
        date = date.replace(/-/g,'/');
        var timestamp = new Date(date).getTime();
        return timestamp;
    };
    // 日历
    function timePicker(select){
        $(select).daterangepicker({
            singleDatePicker: true,   //设置为单个的datepicker，而不是有区间的datepicker 默认false
            showDropdowns: true,      //当设置值为true的时候，允许年份和月份通过下拉框的形式选择 默认false
            autoUpdateInput: false,    //当设置为false的时候,不给与默认值(当前时间)
            startDate: moment().hours(0).minutes(0).seconds(0),
            timePicker24Hour : true,  //设置小时为24小时制 默认false
            timePicker : true,
            timePickerSeconds:true,
            "locale": {
                format: 'YYYY-MM-DD HH:mm:ss',
                applyLabel: "应用",
                cancelLabel: "取消",
            }
        },
        function(start, end, label) {
            beginTimeTake = start;
            if(!this.startDate){
                this.element.val('');
            }else{
                this.element.val(this.startDate.format(this.locale.format));
            }
        });
    };
})