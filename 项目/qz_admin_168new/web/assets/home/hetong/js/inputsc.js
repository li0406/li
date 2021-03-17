 /*上传图片*/
$("#upload_hetong").change(function(e){
    var file = e.target.files[0];
    preview(file,"#hetong_img_list");
});
$("#upload_piaoju").change(function(e){
    var file = e.target.files[0];
    preview(file,"#piaoju_img_list");
});

function preview(file,img_box){
    var img = new Image();
    url = img.src=URL.createObjectURL(file);
    if($(img_box).children().length==0){
       $(img_box).append("<div class='img_box_container pull-left'><div class='remove_img'><i class='fa fa-close'></i></div></div>");
    }else{
       $(img_box).append("<div class='img_box_container pull-left'><div class='plus_img pull-left'>+</div><div class='remove_img'><i class='fa fa-close'></i></div></div>");
    }

    var img_list=$(img_box).children(".img_box_container:last-child");
    img.onload = function(e){
        URL.revokeObjectURL(url);
        var parent_list=$(img_box).children(".img_box_container");
        $(img_list).append($(img));
    }
}
$(".upload_img_list").on("click",".remove_img",function(event){
    var container=$(this).parent().parent();
    var id_name=container.attr("id");
    if(id_name=='zf_ht_list'){
        if(container.children().length==1){
           $("#btn_zf_ht").attr("class","button noChose");
           $("#btn_zf_ht").attr("data-status",0);
        }else{
           $("#btn_zf_ht").attr("class","button hasBg");
           $("#btn_zf_ht").attr("data-status",1);
        }
    }else if(id_name=='zf_pj_list'){
        if(container.children().length==1){
           $("#btn_zf_pj").attr("class","button noChose");
           $("#btn_zf_pj").attr("data-status",0);
        }else{
           $("#btn_zf_pj").attr("class","button hasBg");
           $("#btn_zf_pj").attr("data-status",1);
        }
    }
   $(this).parent().remove();
   $(this).parent().parent().find(".img_box_container:first-child").children(".plus_img").remove();

});