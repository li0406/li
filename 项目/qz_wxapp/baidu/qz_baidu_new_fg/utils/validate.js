const validate = {
    // 非空校验
    checkNull: function (data, msg) {
        if (data == undefined || data.length == 0) {
            swan.showToast({
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
            swan.showToast({
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
        if (num > 1000) {
            swan.showToast({
                title: '请输入1-1000的数字(可不为整数)',
                icon: 'none'
            });
            return false
        }
        if (!reg.test(num)) {
            swan.showToast({
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
        this.checkNumber(size)
    },
    // 联系人校验
    checkName: function (name) {
        var reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        this.checkNull(name, '请输入您的称呼')
        if (!reg.test(name)) {
            swan.showToast({
                title: '请输入正确的称呼',
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 公司名称校验
    checkCompany: function (name) {
        var reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (!reg.test(name)) {
            swan.showToast({
                title: '请输入正确的公司名称',
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 小区校验
    checkXiaoqu: data => {
    }
}
module.exports = validate
