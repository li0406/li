$(function () {
    $('.multiple_group').multiSelect({
        selectableOptgroup: true,
        selectableHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='搜索'>",
        selectionHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='搜索'>",
        afterInit: function (ms) {
            var that = this,
                $selectableSearch = that.$selectableUl.prev(),
                $selectionSearch = that.$selectionUl.prev(),
                selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';
            that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                .on('keydown', function (e) {
                    if (e.which === 40) {
                        that.$selectableUl.focus();
                        return false;
                    }
                });
            that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                .on('keydown', function (e) {
                    if (e.which == 40) {
                        that.$selectionUl.focus();
                        return false;
                    }
                });
        },
        afterSelect: function () {
            this.qs1.cache();
            this.qs2.cache();
        },
        afterDeselect: function () {
            this.qs1.cache();
            this.qs2.cache();
        }
    });
    $('#my_multi_select').multiSelect({
        selectableOptgroup: true
    });

    $('.choose-tips .c-choose').on('click', function () {
        $('.multiple_group').multiSelect('select_all')
        return false
    })
    $('.choose-tips .c-cancel').on('click', function () {
        $('.multiple_group').multiSelect('deselect_all')
        return false
    })

    var id = location.href.substring(location.href.indexOf('=') + 1);
    $('#btnSave').click(function () {
        $.ajax({
            url: '/orderpond/addcityandserv/',
            type: 'POST',
            dataType: 'JSON',
            data: {
                pond_id: id,
                city: $('.multiple_group').val(),
                service: $('#my_multi_select').val(),
                dimension:$(".dimension").val(),
                type:$("input[name=type]:checked").val()
            }
        })
            .done(function (data) {
                alert(data.info);
                if (data.status == 1) {
                    window.location.href = '/orderpond/config/';
                }
            });
    });
});
