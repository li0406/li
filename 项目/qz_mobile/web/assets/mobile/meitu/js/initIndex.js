var cityinfo = rlpca;
(function(mui, doc) {
    mui.init();
    mui.ready(function() {
        var cityPicker3 = new mui.PopPicker({
            layer: 3
        });
        cityPicker3.setData(cityinfo);
        var showCityPickerButton = document.getElementById('showCityPicker2');
        var cityResult = document.getElementById('cityResult');
        showCityPickerButton.addEventListener('tap',
        function(event) {
            var cityID0 = $('input[name=province]').attr('data-id');
            var cityID1 = $('input[name=city]').attr('data-id');
            var cityID2 = $('input[name=area]').attr('data-id');
            cityPicker3.pickers[0].setSelectedValue(cityID0);
            var _time1 = setTimeout(function() {
                cityPicker3.pickers[1].setSelectedValue(cityID1);
                clearTimeout(_time1);
            },
            200);
            var _time2 = setTimeout(function() {
                cityPicker3.pickers[2].setSelectedValue(cityID2);
                clearTimeout(_time2);
            },
            300);
            cityPicker3.show(function(items) {
                var html = '<i class="fa fa-map-marker"></i>' + " " + (items[0] || {}).text + " " + (items[1] || {}).text + " " + (items[2] || {}).text;
                if ('请选择省' == (items[0] || {}).text) {
                    html = '<i class="fa fa-map-marker"></i> 请选择您所在的区域';
                }
                showCityPickerButton.innerHTML = html;
                showCityPickerButton.style.border = "1px solid #ddd";
                showCityPickerButton.style.color = "#3c3c3c";
                $('input[name=province]').attr('data-id', items[0].id);
                $('input[name=city]').attr('data-id', items[1].id);
                $('input[name=area]').attr('data-id', items[2].id);
            });
        },
        false);
    });
})(mui, document);
