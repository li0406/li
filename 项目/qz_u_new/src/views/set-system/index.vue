// 订单设置
<template>
<div class="setOrder">
    <qz-card title="订单设置" divider>
        <div class="setorder-div">
            <div class="flex jus-bet content-text">
                <div class="font14 col333">
                    <div class="font18 fw500">查单密码设置</div>
                    <div class="mt20">查看密码开关</div>
                    <div class="col999 mt10">开启后，查看订单则需要输入查单密码，首次开启则需要设置查单密码</div>
                </div>
                <div>
                    <el-switch class="elswi" :width="60" v-model="onOff" active-color="#6FB446" inactive-color="#E5E5E5">
                    </el-switch>
                </div>
            </div>
            <div v-if="onOff" @click="openchangepass()" class="point flex jus-bet changpassword-div">
                <div class="font14 col333">修改查单密码</div>
                <div>
                    <i class="el-icon-arrow-right btn-icon"></i>
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
            <el-form :model="newlookpass" :rules="newlookpassform" ref="changepass">
                <el-form-item prop="password">
                    <el-input v-model="newlookpass.password" maxlength="18" placeholder="请输入旧查单密码" :show-password="true"></el-input>
                </el-form-item>
                <el-form-item prop="repeatpassword">
                    <el-input v-model="newlookpass.repeatpassword" maxlength="18" placeholder="请输入新查单密码" :show-password="true"></el-input>
                </el-form-item>
                <el-form-item prop="newrepeatpassword">
                    <el-input v-model="newlookpass.newrepeatpassword" maxlength="18" placeholder="请确认新查单密码" :show-password="true"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <div v-if="(newlookpass.password!=''&&newlookpass.repeatpassword!=''&&newlookpass.newrepeatpassword!='')" class="nextstep canPoint point" @click="resetpassword('changepass',2)">确定</div>
        <div v-else class="nextstep canNotPoint point">确定</div>
    </el-dialog>
    <el-dialog top="30vh" title="请设置查单密码" :close-on-click-modal="false" :visible.sync="setpasswordbul1" custom-class="resetsty" @close="closemethods()">
        <div class="setsutitle">8位以上,18位以下,包括字母和数字</div>
        <div class="newpass">
            <el-form :model="newlookpass1" :rules="newlookpassform1" ref="newpass">
                <el-form-item prop="password">
                    <el-input v-model="newlookpass1.password" maxlength="18" placeholder="请输入查单密码" :show-password="true"></el-input>
                </el-form-item>
                <el-form-item prop="repeatpassword">
                    <el-input v-model="newlookpass1.repeatpassword" maxlength="18" placeholder="请确认查单密码" :show-password="true"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <div v-if="(newlookpass1.password!=''&&newlookpass1.repeatpassword!='')" class="nextstep canPoint point" @click="resetpassword('newpass',1)">确定</div>
        <div v-else class="nextstep canNotPoint point">确定</div>
    </el-dialog>

</div>
</template>

<script>
import api from '@/api/redosystem';
import ApiPublic from '@/api/public';
import dayjs from 'dayjs';
import ApiOrder from '@/api/order';

export default {
    components: {
        QzCard: () => import('@/components/card.vue'),
    },
    data() {
        const passwordReg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/;
        const validatePass = (rule, value, callback) => {
            if (value === '') {
                callback(new Error('请输入密码'));
            } else {
                if (!passwordReg.test(value)) {
                    callback(new Error('请输入6位以上，16位以下，包含字母数字。'));
                }
                if (this.form.repeatPassword !== '') {
                    this.$refs.form.validateField('repeatPassword');
                }
                callback();
            }
        };
        const validatePass2 = (rule, value, callback) => {
            if (value === '') {
                callback(new Error('请再次输入密码'));
            } else if (value !== this.form.password) {
                callback(new Error('两次输入密码不一致!'));
            } else {
                callback();
            }
        };
        return {
            newlookpass: {
                password: '',
                repeatpassword: '',
                newrepeatpassword: '',
            },
            newlookpassform: {
                password: [{
                    required: true,
                    message: '请输入旧密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                repeatpassword: [{
                    required: true,
                    message: '请输入新查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                newrepeatpassword: [{
                    required: true,
                    message: '请确认新查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
            },
            newlookpass1: {
                password: '',
                repeatpassword: '',
            },
            newlookpassform1: {
                password: [{
                    required: true,
                    message: '请输入查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                repeatpassword: [{
                    required: true,
                    message: '请确认查单密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
            },
            setpasswordbul: false, //  设置查单密码弹窗
            setpasswordbul1: false,
            onOff: true, //  查看密码开关
            phonecodebul: false, // 手机验证码弹框
            setbul: false, // 设置查单密码弹框
            code1: '', // 验证码第一位数字
            code2: '', // 验证码第二位数字
            code3: '', // 验证码第三位数字
            code4: '', // 验证码第四位数字
            getcodebul: true, // 获取验证码按钮
            timer: null, // 定时器
            countdown: 60, // 倒计时
            btnTitle: '发送验证码至手机',
            btnDisabled: false,
            form: {
                password: '',
                repeatPassword: '',
                code: '',
            },
            rules: {
                password: [{
                        required: true,
                        message: '请输入新密码',
                        trigger: 'blur'
                    },
                    {
                        validator: validatePass,
                        trigger: 'blur'
                    },
                ],
                repeatPassword: [{
                        required: true,
                        message: '请重复输入新密码',
                        trigger: 'blur'
                    },
                    {
                        validator: validatePass2,
                        trigger: 'blur'
                    },
                ],
                code: [{
                    required: true,
                    message: '请输入验证码',
                    trigger: 'blur'
                }],
            },
        }
    },
    created() {
        this.orderpass()
    },
    watch: {
        onOff(val) {
            this.orderpassconfigup(val)
        }
    },
    methods: {
        closemethods() {
            this.orderpassconfigup(false)
        },
        resetpassword(formName, type) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    if (type === 1) { // 新增
                        this.editPwd(this.newlookpass1.password, this.newlookpass1.repeatpassword, '')
                    } else { //    修改
                        this.editPwd(this.newlookpass.repeatpassword, this.newlookpass.newrepeatpassword, this.newlookpass.password)
                    }
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
                    this.setpasswordbul = false
                    this.setpasswordbul1 = false
                    this.$message.success('订单密码设置成功');
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        openchangepass() {
            this.setpasswordbul = true
        },
        //  订单管理-获取订单密码设置信息1.已启用设置 2.未启用设置 3.开启未设置密码
        orderpass() {
            const params = {}
            api.orderpass(params).then((res) => {
                if (res.data.error_code === 0) {
                    if (res.data.data.enabled === "1") {
                        this.onOff = true
                    } else if (res.data.data.enabled === "2") {
                        this.onOff = false
                    } else {
                        this.setpasswordbul1 = true
                        this.onOff = true
                    }
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  订单管理-设置订单密码设置信息
        orderpassconfigup(onOff) {
            const params = {
                options_value: onOff ? 1 : 2
            }
            api.orderpassconfigup(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.orderpass()
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  获取验证码按钮
        getcode() {
            this.clickSendCode()
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
        opensearchdialog() {
            this.clickSendCode()
        },
        //  验证码弹窗下一步
        nextstep() {
            this.phonecodebul = false;
            this.setbul = true;
            this.resetdata()
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
            this.setbul = false;
        },
        async onSubmit() {
            this.$refs.form.validate(async (valid) => {
                if (valid) {
                    const data = {
                        pass: this.form.password,
                        code: this.form.code
                    };
                    const res = await ApiOrder.editPwd(data);
                    if (res.data.error_code === 0) {
                        this.$refs.form.resetFields();
                        // this.$router.push('/order-all');
                        this.$router.go(-1)
                        this.$message.success('订单密码修改成功');
                    } else {
                        this.$message.error(res.data.error_msg);
                    }
                }
            });
        },
        async clickSendCode() {
            const vm = this;

            const res1 = await ApiPublic.geeTestApi1({
                client_type: 'web',
                scene: 2,
                timestamp: dayjs().valueOf(),
            });

            // eslint-disable-next-line no-undef
            initGeetest({
                ...res1.data,
                offline: !res1.data.success,
                product: 'bind',
                https: false
            }, (captchaObj) => {
                captchaObj
                    .onReady(() => {
                        captchaObj.verify();
                    })
                    .onSuccess(async () => {
                        // 二次服务器校验
                        const validate = captchaObj.getValidate();

                        const res2 = await ApiPublic.geeTestApi2({
                            geetest_challenge: validate.geetest_challenge,
                            geetest_validate: validate.geetest_validate,
                            geetest_seccode: validate.geetest_seccode,
                            scene: 2,
                            client_type: 'web',
                            timestamp: dayjs().valueOf(),
                        });

                        if (res2.data.status === 'success') {
                            this.getcodebul = false
                            this.phonecodebul = true;
                            if (this.countdown === 60) {
                                this.starttiming()
                            }
                            const res3 = await ApiPublic.smsSend({
                                phone: '',
                                type: 1, // 1.跟换订单密码 2.绑定微信验证
                                geetest_validate: validate.geetest_validate,
                            });

                            if (parseInt(res3.status, 10) === 200 && parseInt(res3.data.error_code, 10) === 0) {
                                vm.$message({
                                    message: '短信验证码已发送',
                                    type: 'success',
                                });
                            } else {
                                vm.$message({
                                    message: res3.data.error_msg,
                                    type: 'warning',
                                });
                            }
                        } else {
                            // 二次服务器校验失败重置验证组件
                            captchaObj.reset();
                        }
                    });
            });
        },
    }
}
</script>

<style lang="less">
.setOrder {
    height: 100%;
    background-color: #fff;

    .flex {
        display: flex;
    }

    .jus-bet {
        justify-content: space-between;
    }

    .setorder-div {
        width: 100%;
        border: 1px solid #E5E5E5;
    }

    .font18 {
        font-size: 18px;
    }

    .fw500 {
        font-weight: 500;
    }

    .col333 {
        color: #333;
    }

    .bold {
        font-weight: bold;
    }

    .font14 {
        font-size: 14px;
    }

    .col999 {
        color: #999;
    }

    .mt20 {
        margin-top: 20px;
    }

    .mt10 {
        margin-top: 10px;
    }

    .content-text {
        height: 168px;
        padding: 24px 0 0 16px;
    }

    .changpassword-div {
        bottom: 0;
        left: 0;
        height: 51px;
        padding: 0 66px 0 16px;
        line-height: 51px;
        border-top: 1px solid #E5E5E5;
    }

    .btn-icon {
        color: #979797;
        font-size: 20px;
        line-height: 51px;
    }

    .elswi {
        margin: 60px 66px 0 0;
    }

    .el-switch__core {
        height: 30px;
        border-radius: 20px;
    }

    .el-switch__core::after {
        width: 26px;
        height: 26px;
    }

    .el-switch.is-checked .el-switch__core::after {
        margin-left: -26px;
    }

    .point {
        cursor: pointer;
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
