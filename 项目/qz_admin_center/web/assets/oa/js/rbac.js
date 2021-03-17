var Rbac = function(){
    return {
        init:function(){
            $(".menu-form").validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    name: {
                        required: true
                    },
                    parent: {
                        required: true
                    },
                    link: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "请填写菜单名称"
                    },
                    parent: {
                        required: "请选择父类菜单"
                    },
                    link: {
                        required: "请填写菜单链接"
                    }
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },
                success: function (label) {
                    label.closest('.form-group').removeClass('has-error');
                    label.remove();
                },

                errorPlacement: function (error, element) {
                    error.addClass('help-small no-left-padding red').insertAfter(element.closest('.input-icon'));
                }
            });
        }
    }
}();