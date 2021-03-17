<template>
<div class="register">
    <div class="flex cencontent h130 ali-ite jus-bet">
        <div class="flex navlogotitle">
            <img @click="towwwqizuang()" class="navlogo" src="../../assets/newlogo.png" alt="" />
            <div class="registertitle font24">修改密码</div>
        </div>
        <div class="font14">
            <div>已有账号,<span class="colFF4F64 point" @click="tologin()">前往登录</span></div>
        </div>
    </div>
    <div class="cencontent h600 bgfff">
        <div class="cencontent hl72 dividingline">
            <div class="tabstep hl72 jus-bet flex font18 col999">
                <div :class="['fg', { 'bor-b': selon > 0 }]">01.验证手机</div>
                <div :class="['fg', { 'bor-b': selon > 1 }]">02.修改密码</div>
                <div :class="['fg', { 'bor-b': selon > 2 }]">03.修改成功</div>
            </div>
        </div>
        <div class="cencontent h600">
            <div class="stepcontent1" v-show="selon == 1">
                <el-form :model="registerform" :rules="registerformrules" ref="registerform">
                    <el-form-item class="mt60" prop="tel">
                        <div class="flex">
                            <div class="el-icon-user front-icon"></div>
                            <el-input class="pl52" placeholder="请输入手机号" oninput="value=value.replace(/^\.+|[^\d.]/g,'')" maxlength="11" v-model="registerform.tel">
                                <!-- <template slot="prepend"><span class="el-icon-user"></span></template> -->
                            </el-input>
                        </div>
                    </el-form-item>
                    <el-form-item prop="checkcode">
                        <div class="flex">
                            <div class="flex">
                                <div class="el-icon-lock front-icon"></div>
                                <el-input class="w280 pl52" placeholder="请输入验证码" oninput="value=value.replace(/^\.+|[^\d.]/g,'')" maxlength="4" v-model="registerform.checkcode">
                                    <!-- <template slot="prepend"><span class="el-icon-lock"></span></template> -->
                                </el-input>
                            </div>
                            <button class="sendcode point" v-if="getcodebul" @click.prevent="getcode('registerform')">发送验证码</button>
                            <button class="sendcodedisable point" v-else disabled>{{countdown}}s</button>
                        </div>
                    </el-form-item>
                    <el-form-item>
                        <button v-if="(registerform.tel!=''&&registerform.checkcode!='')" class="majoritybtn point font17 mt60 canPoint" type="primary" @click.prevent="submit('registerform')">
                            提交
                        </button>
                        <button v-else class="majoritybtn point font17 mt60 canNotPoint" disabled type="primary">
                            提交
                        </button>
                    </el-form-item>
                </el-form>
            </div>
            <div class="steppassword2" v-if="selon == 2">
                <el-form label-width="120px" label-position="right" :rules="enterprisemsgrules" :model="enterprisemsgfrom" ref="enterprisemsgfrom">
                    <el-form-item label="新密码：" prop="newpassword">
                        <el-input class="fill" v-model="enterprisemsgfrom.newpassword" maxlength="18" :show-password="true" placeholder="请输入新密码"></el-input>
                    </el-form-item>
                    <el-form-item label="确认密码：" prop="repeatpassword">
                        <el-input class="fill" v-model="enterprisemsgfrom.repeatpassword" maxlength="18" :show-password="true" placeholder="请再次输入新密码"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <div class="flex jus-bet mt50">
                            <button class="p-backbtn point" @click.prevent="back()">上一步</button>
                            <button v-if="(enterprisemsgfrom.newpassword!=''&&enterprisemsgfrom.repeatpassword!='')" class="p-nextstepbtn point canPoint" @click.prevent="nextstep('enterprisemsgfrom')">下一步</button>
                            <button v-else class="p-nextstepbtn point canNotPoint" disabled>下一步</button>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <div class="stepcontent3" v-if="selon == 3">
                <div class="suc-img-div">
                    <img class="suc-img" src="../../assets/注册成功icon.png" alt="">
                </div>
                <div class="colE46A6A font18 mt20">
                    恭喜你，修改密码成功！
                </div>
                <button class="canPoint point mt126 font14 tologin-btn" @click.prevent="tologin()">前往登录</button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import api from '@/api/redosystem';
import alter from '@/api/alter';
import dayjs from 'dayjs';
import {
    validPhone
} from '@/utils/validate';

export default {
    data() {
        const validatePhone = (rule, value, callback) => {
            if (!value) {
                callback(new Error('请输入手机号码'));
            } else if (!validPhone(value)) {
                callback(new Error('请填写11位正确手机号'));
            } else {
                callback();
            }
        };
        return {
            md5: '',
            getcodebul: true, // 获取验证码按钮
            timer: null, // 定时器
            countdown: 60, // 倒计时
            selon: 1, //  阶段样式 1 第一阶段 2第二阶段 3第三阶段
            //  第一阶段的表单
            registerform: {
                tel: '',
                checkcode: '',
            },
            checkcode: '', // 接口获取的验证码
            //  第一表单验证
            registerformrules: {
                tel: [{
                    required: true,
                    validator: validatePhone,
                    trigger: ['blur', 'change']
                }],
                checkcode: [{
                        required: false,
                        message: '请输入验证码',
                        trigger: 'blur'
                    },
                    {
                        min: 4,
                        max: 4,
                        message: '请输入4位验证码',
                        trigger: 'blur'
                    },
                ]
            },
            //  第二阶段表单
            enterprisemsgfrom: {
                newpassword: '',
                repeatpassword: '',
            },
            //  第二表单验证
            enterprisemsgrules: {
                newpassword: [{
                    required: true,
                    message: '请输入新密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ],
                repeatpassword: [{
                    required: true,
                    message: '请再次输入新密码',
                    trigger: 'blur'
                }, {
                    min: 8,
                    max: 18,
                    message: '请输入8-18位密码',
                    trigger: 'blur'
                }, ]
            },
        };
    },
    methods: {
        //  忘记密码
        change(params, selon, step) {
            api.change(params, step).then((res) => {
                if (res.data.error_code === 0) {
                    this.selon = selon;
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  获取验证码按钮
        getcode(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    this.btnClick()
                }
            })
        },
        //  发送验证码（无token验证）
        send(geetestValidate) {
            const params = {
                phone: this.registerform.tel, // 手机号
                type: 2, //  1:手机登录  2.忘记密码 3.修改安全手机-验证原手机 4.修改安全手机-验证新手机 5 注册
                geetest_validate: geetestValidate //  极验的geetest_validate
            }
            api.send(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.md5 = res.data.data.md5
                    this.getcodebul = false
                    this.starttiming()
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        btnClick() {
            // eslint-disable-next-line radix
            alter
                .geetestapi1({
                    client_type: 'web',
                    scene: 1,
                    timestamp: dayjs().valueOf(),
                })
                .then((res) => {
                    // eslint-disable-next-line no-undef
                    initGeetest({
                        ...res.data,
                        offline: !res.data.success,
                        product: 'bind',
                        https: false
                    }, (captchaObj) => {
                        captchaObj.reset();
                        captchaObj
                            .onReady(() => {
                                captchaObj.verify();
                            })
                            .onSuccess(() => {
                                const validate = captchaObj.getValidate();
                                alter
                                    .geetestapi2({
                                        geetest_challenge: validate.geetest_challenge,
                                        geetest_validate: validate.geetest_validate,
                                        geetest_seccode: validate.geetest_seccode,
                                        scene: 1,
                                        client_type: 'web',
                                        t: new Date().getTime(),
                                    })
                                    .then((response) => {
                                        // eslint-disable-next-line eqeqeq
                                        if (response.data.status == 'success') {
                                            this.send(validate.geetest_validate)
                                        }
                                    });
                            });
                    });
                });
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
        //  跳转外链官网
        towwwqizuang() {
            window.open('https://www.qizuang.com/')
        },
        //  跳转注册协议页面
        toRegAger() {
            window.location.href = "https://www.qizuang.com/about/legal/"
        },
        //  去登录
        tologin() {
            this.$router.push({
                path: '/login',
            });
        },
        //  第一阶段
        //  提交到第二阶段
        submit(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    const params = {
                        phone: this.registerform.tel, // 手机号码
                        msgcode: this.registerform.checkcode, // 验证码
                    }
                    this.change(params, 2, 1)
                } else {
                    // return false;
                }
            })
        },
        //  第二阶段
        //  上一步
        back() {
            this.selon = 1;
        },
        //  下一步
        nextstep(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    const params = {
                        phone: this.registerform.tel, // 手机号码
                        md5: this.md5,
                        account_pwd_new: this.enterprisemsgfrom.newpassword, // 新密码
                        account_pwd_new_confirm: this.enterprisemsgfrom.repeatpassword // 确认新密码
                    }
                    this.change(params, 3, 2)
                } else {
                    // return false;
                }
            })
        },
    },
};
</script>

<style>
@import './css/common.css';

.bor {
    border: 1px solid;
}
</style>
