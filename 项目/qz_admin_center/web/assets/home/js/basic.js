var Basic = function(){
    return {
        init:function(){
            $(".basic-form").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    username: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: "请填写昵称"
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

            $(".password-form").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    password: {
                        required: true
                    },
                    newpassword: {
                        required: true
                    },
                    confirmpassword: {
                        required: true
                    },
                    passwordAgin: {
                        required:true
                    }
                },
                messages: {
                    password: {
                        required: "请填写原始密码"
                    },
                    newpassword: {
                        required: "请填写新密码"
                    },
                    confirmpassword: {
                        required: "请填写确认密码"
                    },
                     passwordAgin: {
                        required:"凄凄切切"
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

            $(".safe-form").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    question1: {
                        required: true
                    },
                    question2: {
                        required: true
                    },
                    question3: {
                        required: true
                    },
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
                    question1: {
                        required: "请选择安全问题一"
                    },
                    question2: {
                        required: "请选择安全问题二"
                    },
                    question3: {
                        required: "请选择安全问题三"
                    },
                    answer1: {
                        required: "请填写安全问题一"
                    },
                    answer2: {
                        required: "请填写安全问题二"
                    },
                    answer3: {
                        required: "请填写安全问题三"
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

            $(".tel-form").validate({
                errorElement: 'label', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    tel: {
                        required: true
                    }
                },
                messages: {
                    tel: {
                        required: "请填写手机号码"
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