var Forget = function(){
    return {
        init:function(){
            $(".step-form-1").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    name: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "请填写账号"
                    }
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('error'); // set error class to the control group
                },
                success: function (label) {
                    label.closest('.form-group').removeClass('error');
                    label.remove();
                },

                errorPlacement: function (error, element) {
                    error.addClass('help-small no-left-padding red').insertAfter(element.closest('.input-icon'));
                }
            });

            $(".step-form-2").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    answer1: {
                        required: true
                    },
                    answer2: {
                        required: true
                    },
                    answer3: {
                        required: true
                    }
                },
                messages: {
                    answer1: {
                        required: "请填写问题一的答案"
                    },
                    answer2: {
                        required: "请填写问题二的答案"
                    },
                    answer3: {
                        required: "请填写问题三的答案"
                    }
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('error'); // set error class to the control group
                },
                success: function (label) {
                    label.closest('.form-group').removeClass('error');
                    label.remove();
                },

                errorPlacement: function (error, element) {
                    error.addClass('help-small no-left-padding red').insertAfter(element.closest('.input-icon'));
                }
            });

            $(".step-form-3").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    password: {
                        required: true
                    },
                    confirmpassword: {
                        required: true
                    }
                },
                messages: {
                    password: {
                        required: "请填写密码"
                    },
                    confirmpassword: {
                        required: "请填写确认密码"
                    }
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('error'); // set error class to the control group
                },
                success: function (label) {
                    label.closest('.form-group').removeClass('error');
                    label.remove();
                },

                errorPlacement: function (error, element) {
                    error.addClass('help-small no-left-padding red').insertAfter(element.closest('.input-icon'));
                }
            });
        }
    }
}();