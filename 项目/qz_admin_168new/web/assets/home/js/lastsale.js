$(function(){
            //选择角色
            $("button[data-type='city']").click(function(event) {
                var _this = $(this);
                if(_this.hasClass('btn-success')){
                    _this.removeClass('btn-success');
                    _this.removeClass('editing');
                }else{
                    _this.addClass('btn-success');
                    _this.addClass('editing');
                }
            });

})
$(window).load(function(){
   

    //移出城市
    $("#removeCity").bind('click',function(){
        var pointid = $("#editCity").attr("data-id");
        var ids = '';
        $(".editing").each(function(){
            ids += $(this).attr("data-id")+'|';
        });
        $.ajax({
            url: "/salesetting/removeCityPointValues/",
            type: 'POST',
            dataType: 'json',
            data:{
                pointid : pointid,
                ids : ids
            }  
        })
        .done(function(result) {
            if(result.status == 1){
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();   
            }else{
                alert("失败请重试！");
                window.location.reload();
            }           
        })
        .fail(function(xhr) {
            alert("失败请重试！");
        });

    });
    //打开移动系数
    $("#moveOtherPoint").bind("click",function(){
        $("#movePointContainer").removeClass("sale-none");
    })
    //确认移动系数
    $("#confirmMove").bind('click',function(){
        var pointid = $("#editCity").attr("data-id");
        var ids = '';
        $(".editing").each(function(){
            ids += $(this).attr("data-id")+'|';
        });
        //移动到的系数
        var pid = $("input[name=cityPoint]:checked").attr("data-id");
        $.ajax({
            url: "/salesetting/editCityPointValues/",
            type: 'POST',
            dataType: 'json',
            data:{
                pointid : pointid,
                ids : ids,
                pid : pid
            }  
        })
        .done(function(result) {
            if(result.status == 1){
                //var mbPoingID = $(this).attr("data-id");
                alert(result.info);
                window.location.reload();   
            }else{
                alert("失败请重试！");
                window.location.reload();
            }           
        })
        .fail(function(xhr) {
            alert("失败请重试！");
        });
    });

    //移动系数中添加系数

    
    //编辑系数城市
    $("#editCity").bind("click",function(){
        $("#editCityList").removeClass("sale-none");
    })
    //放弃当前编辑
    $("#giveupEdit").bind("click",function(){
        $("#editCityList").addClass("sale-none");
    })
    //移动城市：增加系数
    $("#addPoint").bind("click",function(){
        $("#addPointOnMove").removeClass("sale-none");
    })
    //移动城市：关闭添加窗口
    $("#moveCityClose").bind("click",function(){
        $("#addPointOnMove").addClass("sale-none");
    })
    //移动城市：确认添加系数
    $("#confirmMoveAdd").bind("click",function(){
        var citypoint = $("input[name=addonmove]").val();
        //确定时写入数据库
        $.ajax({
            url: "/salesetting/addCityPoint/",
            type: 'POST',
            dataType: 'json',
            data:{
                citypoint : citypoint
            }  
        })
        .done(function(result) {
            if(result.status == 1){
                //var mbPoingID = $(this).attr("data-id");
                var html = '<input type="radio" name="cityPoint" data-id="'+result.data+'" /><span>'+citypoint+'</span><br />';
                $("#addPointContainer").append(html);
                alert(result.data);
                $("#addPointOnMove").addClass("sale-none");

                //window.location.reload(); 
            }else{
                alert(result.info);
            }           
        })
        .fail(function(xhr) {
            alert("失败请重试！");
        });
    })

    
    
    
    //关闭确认系数
    $("#movePointClose").bind("click",function(){
        $("#movePointContainer").addClass("sale-none");
    })
    //打开新增城市
    $("#addCity").bind("click",function(){
        $("#addCityContainer").removeClass("sale-none");
    })
    $("#giveupAdd").bind("click",function(){
        $("#addCityContainer").addClass("sale-none");
    })
});

function deleteCityPoint(e){
    alert(e);
}
function editCityPoint(e){
    alert(e);
}
  var selectAllCity = function(a){
    console.log(a);
            a = $(a);
            var cks = a.parent().parent().find(":checkbox");
            !a.data("checked") ?  
                cks.prop("checked",true) & a.data("checked",1)
                : cks.prop("checked",false) & a.data("checked",0);
        }