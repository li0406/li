$(function(){
    var areaNums = 1;
  $("#more").click(function(){
      if(areaNums <= 15) {
          var htmltpl = '<div class="form-group">' +
              '<span>区域名：</span>' +
              '<input type="text" name="qz_area[]" placeholder="区域名">' +
              '</div>'
          $('.quyu-box').append(htmltpl);
          areaNums ++;
      }else{
          alert('已达系统支持的最大数！');
      }

  })

    $("#save").click(function(event){
        var data = $("#form1").serializeArray();
        $.ajax({
            url: '/basicinfo/quyu/',
            type: 'POST',
            dataType: 'JSON',
            data: data
        })
            .done(function(data) {
                if (data.status == 0) {
                    window.location.href = window.location.href;
                } else {
                    alert(data.info);
                }
            });
    });

})