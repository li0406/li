<template>
<div class="notsetpassword">
    <qz-card title="全部订单" divider>
        <div class="notset-div">
            <div class="font18 bold col333">您当前未设置查单密码</div>
            <div class="font14 col999 mt15 w420">您可以根据您的选择是否使用查单密码，以便您的数据安全我们建议您使用查单密码</div>
            <div class="flex jus-bet mt70">
                <div class="wh420">
                    <div class="imgsty-div mt41">
                        <img class="imgsty" src="https://staticqn.qizuang.com/custom/20201014/Fp57McgRgzMpEiLLaXDZPfMaeYUr" alt="">
                    </div>
                    <div class="continuebtn font14 mt64 point" @click="nextstep(1)">继续使用查单密码</div>
                    <div class="font12 col999 mt15 text-cen">提高订单数据的安全隐秘性，不容易被泄漏</div>
                </div>
                <div class="wh420">
                    <div class="imgsty-div mt41">
                        <img class="imgsty" src="https://staticqn.qizuang.com/custom/20201014/FmvMoEfCmEXk1kYXvfaJBTY-Yygb" alt="">
                    </div>
                    <div class="closebtn font14 mt64 point" @click="nextstep(2)">我想关闭查单密码</div>
                    <div class="font12 col999 mt15 text-cen">更加方便随时查看订单</div>
                </div>
            </div>
        </div>
    </qz-card>
    <!--手机验证码弹框-->
    <el-dialog top="30vh" title="输入手机验证码" :close-on-click-modal="false" :visible.sync="phonecodebul" custom-class="phonecodesty">
        <div class="subtitle">
            <div>已发送至18702534567</div>
            <div class="point colD92B2B" v-if="getcodebul" @click="getcode()">获取验证码</div>
            <div v-else><span class="col0091FF">{{countdown}}s</span>后重新获取验证码</div>
        </div>
        <div class="inputs">
            <input class="input-div" type="text" maxlength="1" v-model="code1">
            <input class="input-div" type="text" maxlength="1" v-model="code2">
            <input class="input-div" type="text" maxlength="1" v-model="code3">
            <input class="input-div" type="text" maxlength="1" v-model="code4">
        </div>
        <div v-if="(code1!=''&&code2!=''&&code3!=''&&code4!='')" class="nextstep canPoint point" @click="nextstep()">下一步</div>
        <div v-else class="nextstep canNotPoint point">下一步</div>
    </el-dialog>
    <!--关闭设置查单密码弹框-->
    <el-dialog top="30vh" title="查单密码关闭提示" :close-on-click-modal="false" :visible.sync="setbul" width="420px" custom-class="setsty">
        <div class="setsutitle">您确定需要关闭查单密码嘛？关闭后可能订单的数据会存在泄漏的风险</div>
        <div class="setask">如何开启查单密码功能</div>
        <div class="setimg-div"></div>
        <div class="settips">可在个人中心- 设置- 开启查单密码</div>
        <div class="setbtns">
            <div class="setbtnl point" @click="setbtnl()">我在想想</div>
            <div class="setbtnr point" @click="setbtnr()">确认关闭</div>
        </div>
    </el-dialog>
    <!--重新设置查单密码弹框-->
    <el-dialog top="30vh" title="请设置查单密码" :close-on-click-modal="false" :visible.sync="setpasswordbul" width="420px" custom-class="resetsty">
        <div class="setsutitle">8位以上,18位以下,包括字母和数字</div>
        <div class="newpass">
            <el-form :model="newlookpass" :rules="newlookpassform" ref="newpass">
                <el-form-item prop="password">
                    <el-input v-model="newlookpass.password" maxlength="18" placeholder="请输入查单密码" :show-password="true"></el-input>
                </el-form-item>
                <el-form-item prop="repeatpassword">
                    <el-input v-model="newlookpass.repeatpassword" maxlength="18" placeholder="确认查单密码" :show-password="true"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <div v-if="(newlookpass.password!=''&&newlookpass.repeatpassword!='')" class="nextstep canPoint point" @click="resetpassword('newpass')">确定</div>
        <div v-else class="nextstep canNotPoint point">确定</div>
    </el-dialog>
</div>
</template>

<script>
import api from '@/api/redosystem';
import ApiOrder from '@/api/order';

export default {
    components: {
        QzCard: () => import('@/components/card.vue')
    },
    data() {
        return {
            newlookpass: {
                password: '',
                repeatpassword: '',
            },
            newlookpassform: {
                password: [{
                    required: true,
                    message: '请输入新查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                repeatpassword: [{
                    required: true,
                    message: '请再次输入新查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
            },
            flag: 0, // 0默认 1点击快捷设置 2点击忘记查单密码
            onOff: false, //  查看密码开关
            phonecodebul: false, // 手机验证码弹框
            setbul: false, // 设置查单密码关闭弹框
            setpasswordbul: false, //  设置查单密码弹窗
            code1: '', // 验证码第一位数字
            code2: '', // 验证码第二位数字
            code3: '', // 验证码第三位数字
            code4: '', // 验证码第四位数字
            getcodebul: true, // 获取验证码按钮
            timer: null, // 定时器
            countdown: 60, // 倒计时
        }
    },
    methods: {
        resetpassword(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    this.editPwd(this.newlookpass.password, this.newlookpass.repeatpassword, '')
                } else {
                    // return false;
                }
            })
        },
        //  订单管理-修改订单密码
        editPwd(newpass, comfirmpass, oldpass) {
            const params = {
                pass: newpass, // 订单新密码
                comfirm_pass: comfirmpass, //  确认订单密码
                old_pass: oldpass //   旧密码
            }
            ApiOrder.editPwd(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.$parent.ischeckorderpass = 1
                    this.setpasswordbul = false
                    this.setbul = false
                    this.$message.success('订单密码设置成功');
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  订单管理-设置订单密码设置信息
        orderpassconfigup(value) {
            const params = {
                options_value: value //  1.开启 2.关闭
            }
            api.orderpassconfigup(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.setbul = false;
                    this.$parent.ischeckorderpass = 3
                    this.$message({
                        message: '关闭成功',
                        type: 'success',
                    });
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  订单管理-获取是否有订单密码
        // checkorderpassstatus() {
        //     const params = {}
        //     api.checkorderpassstatus(params).then((res) => {
        //         if (res.data.error_code === 0) {
        //             this.$locSet('enabled', res.data.data.enabled)
        //         } else {
        //             this.$message.error(res.data.error_msg)
        //         }
        //     }).catch((err) => {
        //         this.$message.error(err)
        //     })
        // },
        //  获取验证码按钮
        getcode() {
            this.getcodebul = false
            this.starttiming()
        },
        //  开始计时
        starttiming() {
            this.timer = window.setInterval(() => {
                this.countdown -= 1
                if (this.countdown === 0) {
                    this.getcodebul = true
                    this.countdown = 60
                    clearInterval(this.timer)
                }
            }, 1000)
        },
        //  打开验证码弹窗
        opensearchdialog(flag) {
            this.flag = flag
            this.getcodebul = false
            this.phonecodebul = true;
            if (this.countdown === 60) {
                this.starttiming()
            }
        },
        //  验证码弹窗下一步
        nextstep(flag) {
            if (flag === 1) { //  点击快捷设置
                this.setpasswordbul = true; // 打开设置查单密码弹窗
            } else { //  点击忘记查单密码
                this.setbul = true; // 打开关闭查单密码弹窗
            }
            this.phonecodebul = false; //  关闭验证手机验证码弹窗
            this.resetdata() // 重置数据
        },
        //  重置部分数据
        resetdata() {
            this.countdown = 60
            clearInterval(this.timer)
            this.code1 = ''
            this.code2 = ''
            this.code3 = ''
            this.code4 = ''
        },
        setbtnl() {
            this.setbul = false;
        },
        setbtnr() {
            this.orderpassconfigup(2)
        },
    }
}
</script>

<style lang="less">
.bor {
    border: 1px solid;
}

.notsetpassword {
    height: 100%;
    background-color: #fff;

    .notset-div {
        width: 1070px;
        margin: 0 auto;
        padding-top: 50px;
    }

    .flex {
        display: flex;
    }

    .jus-bet {
        justify-content: space-between;
    }

    .font18 {
        font-size: 18px;
    }

    .col333 {
        color: #333;
    }

    .font14 {
        font-size: 14px;
    }

    .col999 {
        color: #999;
    }

    .wh420 {
        width: 420px;
        height: 420px;
        border: 1px solid #DEDEDE;
    }

    .imgsty-div {
        width: 302px;
        height: 206px;
        margin: 0 auto;
    }

    .imgsty {
        width: 100%;
        height: 100%;
    }

    .continuebtn {
        width: 260px;
        height: 40px;
        margin: 0 auto;
        color: #fff;
        line-height: 40px;
        text-align: center;
        background-color: #E46A6A;
        border-radius: 2px;
    }

    .closebtn {
        width: 260px;
        height: 40px;
        margin: 0 auto;
        color: #D92B2B;
        line-height: 40px;
        text-align: center;
        border: 1px solid #D92B2B;
        border-radius: 2px;
    }

    .font12 {
        font-size: 12px;
    }

    .mt15 {
        margin-top: 15px;
    }

    .mt70 {
        margin-top: 70px;
    }

    .mt41 {
        margin-top: 41px;
    }

    .mt64 {
        margin-top: 64px;
    }

    .bold {
        font-weight: bold;
    }

    .text-cen {
        text-align: center;
    }

    .point {
        cursor: pointer;
    }

    .w420 {
        width: 420px;
    }

    /* 手机验证码弹窗 */
    .phonecodesty {
        width: 420px;
        height: 320px;
        background: #FFF;
        border-radius: 8px;
    }

    .el-dialog__title {
        color: #333;
        font-weight: bold;
        font-size: 18px;
    }

    .col0091FF {
        color: #0091FF;
    }

    .subtitle {
        display: flex;
        justify-content: space-between;
        color: #999;
        font-size: 14px;
    }

    .inputs {
        display: flex;
        justify-content: space-around;
        margin-top: 40px;
    }

    .input-div {
        width: 50px;
        height: 50px;
        font-size: 20px;
        text-align: center;
        border: 1px solid #999;
        border-radius: 2px;
    }

    .nextstep {
        width: 380px;
        height: 40px;
        margin-top: 40px;
        color: #fff;
        line-height: 40px;
        text-align: center;
        border-radius: 2px;
    }

    .canNotPoint {
        background-color: #E46A6A;
    }

    .canPoint {
        background-color: #D92B2B;
    }

    /* 设置查单密码弹框 */
    .setsty {
        width: 420px;
        height: 460px;
        background: #FFF;
        border-radius: 8px;
    }

    .setsutitle {
        margin-top: -25px;
        color: #999;
        font-size: 12px;
    }

    .setask {
        margin-top: 15px;
        color: #000;
        font-size: 14px;
    }

    .setimg-div {
        width: 380px;
        height: 220px;
        margin-top: 8px;
        background: #D8D8D8;
        background-image: url('http://staticqn.qizuang.com/custom/20201021/Fo2m85cF_5FzPdver-SIM40YiFb2');
        background-repeat: no-repeat;
    }

    .settips {
        margin-top: 8px;
        color: #6D7278;
        font-size: 14px;
    }

    .setbtns {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .setbtnl {
        width: 160px;
        height: 40px;
        color: #7D7D7D;
        line-height: 40px;
        text-align: center;
        border: 1px solid #DADADA;
        border-radius: 2px;
    }

    .setbtnr {
        width: 160px;
        height: 40px;
        color: #fff;
        line-height: 40px;
        text-align: center;
        background: #D92B2B;
        border-radius: 2px;
    }

    .colD92B2B {
        color: #D92B2B;
    }

    /* 设置新密码 */
    .newpass {
        margin-top: 40px;
    }

    .resetsty {
        width: 420px;
        background: #FFF;
        border-radius: 8px;
    }
}
</style>
