const validate = {
    // 非空校验
    checkNull: function (data, msg) {
        if (data == undefined || data.length == 0) {
            tt.showToast({
                title: msg,
                icon:'none'
            });
            return false
        } else {
            return true
        }
    },
    // 手机号码校验
    checkPhone: function (phone) {
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        this.checkNull(phone, "请输入您的手机号")
        if (!reg.test(phone)) {
            tt.showToast({
                title: "请输入正确的手机号",
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 纯数字校验
    checkNumber: function (num) {
        var reg = new RegExp("^([0-9]{1,}||[0-9]{1,}[.][0-9]*)$");
        if (num > 10000 || num < 1 ) {
            tt.showToast({
                title: '请输入1-10000的数字(可不为整数)',
                icon: 'none'
            });
            return false
        }
        if (!reg.test(num)) {
            tt.showToast({
                title: '只能输入大于0的整数',
               icon: 'none'
            });
            return false
        }
        return true
    },
    // 面积校验
    checkSize: function (size) {
        this.checkNull(size, "请输入您的房屋面积")
        if (size < 1 || size > 10000) {
            tt.showToast({
                title: '房屋面积请输入1-10000的数字',
               icon: 'none'
            });
            return false
        }
        return true
    },
    // 联系人校验
    checkName: function (name) {
        var reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        let len = name.length
        this.checkNull(name, '请输入您的称呼')
        if (!reg.test(name) || len>10) {
            tt.showToast({
                title: '请输入正确的姓名',
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 公司名称校验
    checkCompany: function (name) {
        let len = name.length
        var reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (!reg.test(name) || len>20) {
            tt.showToast({
                title: '请输入正确的公司名称',
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 小区校验
    checkXiaoqu:function (xiaoqu) {
        if (!isNaN(xiaoqu)) {
            tt.showToast({
                title: "小区名称不能为纯数字",
                icon: 'none'
            });
            return false
        }
        return true
    }
}
module.exports = validate
