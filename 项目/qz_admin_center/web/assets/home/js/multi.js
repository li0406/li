
var FormComponents = function () {
    var handleMultiSelect = function () {
        $('#my_multi_select').multiSelect({
            selectableOptgroup: true
        });


    }

    return {
        init:function(){
            handleMultiSelect();
        }
    };
}();