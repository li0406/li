const validate = {
    // 非空校验
    checkNull: function (data, msg) {
        if (data == undefined || data.length == 0 || data == '') {
            swan.showToast({
                title: msg,
                icon: 'none'
            });
            return false
        } else {
            return true
        }
    },
    // 手机号码校验
    checkPhone: function (phone) {
        let reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (!this.checkNull(phone, "请输入您的手机号")) return false
        if (!reg.test(phone)) {
            swan.showToast({
                title: "请输入正确的手机号",
                icon: 'none'
            });
            return false
        }
        return true
    },
    checkPhoneNum: function (phone, msg) {
        let reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (!this.checkNull(phone, "请输入您的手机号")) return false

        if (!reg.test(phone)) {
            console.log(111)
            swan.showToast({
                title: msg,
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 纯数字校验
    checkNumber: function (num) {
        // let reg = new RegExp("^([1-9]{1,}||[0-9]{1,}[.][0-9]*)$"); ^([1-9]\d{0,2}|1000)$
        // let reg = new RegExp("^(([1-9]\d{0,2}|1000)[.][0-9]{1}*)$");
        if (parseFloat(num) > 1000 || parseFloat(num) <= 0) {
            swan.showToast({
                title: '请输入1-1000的数字(可不为整数)',
                icon: 'none'
            });
            return false
        }
        // if (!reg.test(num)) {
        //     swan.showToast({
        //         title: '只能输入大于0的整数',
        //         icon: 'none'
        //     });
        //     return false
        // }
        return true
    },
    // 面积校验
    checkSize: function (size) {
        this.checkNull(size, "请输入您的房屋面积")
        this.checkNumber(size)
    },
    // 联系人校验
    checkName: function (name) {
        let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (!this.checkNull(name, '请输入您的称呼')) return false
        if (!reg.test(name)) {
            swan.showToast({
                title: '请输入正确的称呼',
                icon: 'none'
            });
            return false
        }
        return true
    },
     checkConnectName: function (name,msg) {
        let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (!this.checkNull(name, '请输入您的称呼')) return false
        if (!reg.test(name)) {
            swan.showToast({
                title: msg,
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 公司名称校验
    checkCompany: function (name) {
        let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        this.checkNull(name, '请输入公司名称')
        if (!reg.test(name)) {
            swan.showToast({
                title: '请输入正确的公司名称',
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 公司名称校验
    checkCompanyName: function (name, msg) {
        let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (!this.checkNull(name, '请输入公司名称')) return false
        if (!reg.test(name)) {
            console.log(11)
            swan.showToast({
                title: msg,
                icon: 'none'
            });
            return false
        }
        return true
    },
    // 小区校验
    checkXiaoqu: function (name) {
        let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (!reg.test(name)) {
            swan.showToast({
                title: '请输入正确的小区名称',
                icon: 'none'
            });
            return false
        }
        return true
    }
}
module.exports = validate
