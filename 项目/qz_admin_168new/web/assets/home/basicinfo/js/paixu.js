$(function(){
  var columns = document.querySelectorAll('#columns .column');
  var dragEl = null;
  [].forEach.call(columns,function(column){
    column.addEventListener("dragstart",domdrugstart,false);
    column.addEventListener('dragenter', domdrugenter, false);
    column.addEventListener('dragover', domdrugover, false);
    column.addEventListener('dragleave', domdrugleave, false);
    column.addEventListener('drop', domdrop, false);
    column.addEventListener('dragend', domdrapend, false);
  });

  function domdrugstart(e) {
    e.target.style.opacity = '0.5';
    dragEl = this;
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/html",this.innerHTML);

  }

  function domdrugenter(e) {
    e.target.classList.add('over');
  }

  function domdrugover(e) {
    if (e.preventDefault) {
      e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
  }

  function domdrugleave(e) {
    e.target.classList.remove('over');
  }

  function domdrop(e) {
    if (e.stopPropagation) {
      e.stopPropagation();
    }
    // 位置互换
    if (dragEl != this) {

      var thisId = this.getAttribute('data-dad-id')
      var dragId = dragEl.getAttribute('data-dad-id')
      this.setAttribute('data-dad-id',dragId)
      dragEl.setAttribute('data-dad-id',thisId)
      
      dragEl.innerHTML = this.innerHTML;
      this.innerHTML = e.dataTransfer.getData('text/html');


    }
    return false;
  }

  function domdrapend(e) {
    [].forEach.call(columns, function (column) {
      column.classList.remove('over');
      column.style.opacity = '1';
    });
  }

  $(".btn-save").click(function(event){
    var id = [];
    var orders = [];

    $('.clearfix .item').each(function(){
      id.push($(this).attr('data-dad-id'));
      orders.push($(this).attr('data-dad-position'));
    });

    console.log(id);
    console.log(orders);

    $.ajax({
      url: '/basicinfo/paixu?id={$quyuInfo.cid}',
      type: 'POST',
      dataType: 'JSON',
      data: {
        id:id,
        orders:orders
      }
    })
        .done(function(data) {
          if(data.status == 0){
            window.location.href = window.location.href;
          }else{
            alert(data.info);
          }
        })
        .fail(function(xhr) {
          alert('操作失败');
        })

  });

})