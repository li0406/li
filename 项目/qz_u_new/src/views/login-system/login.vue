<template>
<div class="login">
    <div class="login-card flex">
        <div class="qrcode-div">
            <img class="logo mt32" src="../../assets/logo白.png" alt="">
            <div class="title font16 mt14 colfff">欢迎登录齐装网商家后台系统</div>
            <div class="mt45 guide">
                <img v-if="imgurl!=''" class="qrcode point" :src="imgurl" alt="">
                <img v-else class="qrcode point" src="../../assets/默认图.png" alt="">
                <div class="guide-div"></div>
                <div class="beoverdue" v-if="refreshbul">
                    <div class="refreshbtn el-icon-refresh point" @click="getqrcodeimg()">&nbsp;刷新</div>
                </div>
            </div>
            <div class="font14 mt24 colfff">扫码登录请使用</div>
            <div class="font14 mt28 colfff">打开齐装云管家APP【首页-扫一扫】登录</div>
        </div>
        <div class="form">
            <div class="loginmsg">
                <div class="flex tabs-login font21 jus-aro mt42 col666">
                    <div :class="['item','point',{'on':type==1}]" @click="switchtab(1)">密码登录</div>
                    <div :class="['item','point',{'on':type==2}]" @click="switchtab(2)">快捷登录</div>
                </div>
                <div class="tab-card">
                    <el-form :model="formpassword" :rules="passrules" v-show="type==1" ref="userpass">
                        <el-form-item class="mt60" prop="username">
                            <div class="flex">
                                <div class="el-icon-user front-icon"></div>
                                <el-input class="pl52" placeholder="请输入账号名/手机号" v-model="formpassword.username">
                                    <!-- <template slot="prepend"><span class="el-icon-user"></span></template> -->
                                </el-input>
                            </div>
                        </el-form-item>
                        <el-form-item prop="password">
                            <div class="mt14 flex">
                                <div class="el-icon-lock front-icon"></div>
                                <el-input class="pl52" placeholder="请输入密码" v-model="formpassword.password" :show-password="true">
                                    <!-- <template slot="prepend"><span class="el-icon-lock"></span></template> -->
                                </el-input>
                            </div>
                        </el-form-item>
                        <el-form-item>
                            <div class="depuser flex jus-bet">
                                <label><input class="inputcheckbox" type="checkbox" v-model="depuser" /><span class="setpass point">记住账号</span></label>
                                <div class="colFF4F64 point" @click="tochangepassword()">忘记密码？</div>
                            </div>
                        </el-form-item>
                        <el-form-item>
                            <button v-if="(formpassword.username!=''&&formpassword.password!='')" class="majoritybtn canPoint point font17" type="primary" @click.prevent="loginpassword('userpass')">立即登录</button>
                            <button v-else class="majoritybtn canNotPoint point font17" disabled type="primary">立即登录</button>
                        </el-form-item>
                        <el-form-item>
                            <div>还没有账号？<span class="colFF4F64 point" @click="toregister()">去注册</span></div>
                        </el-form-item>
                    </el-form>
                    <el-form :model="formquick" :rules="quickrules" v-show="type==2" ref="quickrulesform">
                        <el-form-item class="mt60" prop="tel">
                            <div class="flex">
                                <div class="el-icon-user front-icon"></div>
                                <el-input class="pl52" maxlength="11" oninput="value=value.replace(/^\.+|[^\d.]/g,'')" placeholder="请输入手机号" v-model="formquick.tel">
                                    <!-- <template slot="prepend"><span class="el-icon-user"></span></template> -->
                                </el-input>
                            </div>
                        </el-form-item>
                        <el-form-item prop="checkcode">
                            <div class="flex mt14">
                                <div class="flex">
                                    <div class="el-icon-lock front-icon"></div>
                                    <el-input class="w280 pl52" placeholder="请输入验证码" oninput="value=value.replace(/^\.+|[^\d.]/g,'')" maxlength="4" v-model="formquick.checkcode">
                                        <!-- <template slot="prepend"><span class="el-icon-lock"></span></template> -->
                                    </el-input>
                                </div>
                                <button class="sendcode point" v-if="getcodebul" @click.prevent="getcode('quickrulesform')">发送验证码</button>
                                <button class="sendcodedisable point" v-else disabled>{{countdown}}s</button>
                            </div>
                        </el-form-item>
                        <el-form-item>
                            <button v-if="(formquick.tel!=''&&formquick.checkcode)" class="majoritybtn canPoint point font17 mt67" type="primary" @click.prevent="quicklogin()">立即登录</button>
                            <button v-else class="majoritybtn canNotPoint point font17 mt67" disabled type="primary">立即登录</button>
                        </el-form-item>
                        <el-form-item>
                            <div>还没有账号？<span class="colFF4F64 point" @click="toregister()">去注册</span></div>
                        </el-form-item>
                    </el-form>
                </div>
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
            refreshbul: false, // 二维码是否过期
            geetest_validate: '', //  极验证编码
            imgurl: '', //  二维码图片地址
            code: '', //  唯一标识码

            codetimer: null, // 二维码扫描定时器
            getcodebul: true, // 获取验证码按钮
            timer: null, // 定时器
            countdown: 60, // 倒计时
            type: 1, // 切换tab 变换样式 1：密码登录 2：快捷登录
            //  密码登录
            formpassword: {
                username: '', //  用户名
                password: '', //  密码
            },
            depuser: false, //  是否记住账号
            //  快捷登录
            formquick: {
                tel: '', //  手机号
                checkcode: '', //  验证码
            },
            passrules: { // 密码登录验证
                username: [{
                    required: true,
                    message: '请输入用户名',
                    trigger: 'blur'
                }, ],
                password: [{
                    required: true,
                    message: '请输入密码',
                    trigger: 'blur'
                }]
            },
            quickrules: { // 验证码登录验证
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
        }
    },
    created() {
      Object.keys(localStorage).forEach((item) => item.indexOf('newusername') === -1 ? localStorage.removeItem(item) : '')
        this.getqrcodeimg()
        if (this.$locGet('newusername')) {
            this.formpassword.username = this.$locGet('newusername')
        } else {
            this.formpassword.username = ''
        }
    },
    destroyed() {
        clearInterval(this.codetimer)
    },
    methods: {
        //  登录-获取登录二维码
        getqrcodeimg() {
            const params = {}
            api.qr(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.imgurl = res.data.data.image
                    this.code = res.data.data.code
                    this.refreshbul = false
                    this.codetimer = window.setInterval(() => {
                        this.qrcheck(this.code)
                    }, 1000)
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  登录-二维码状态扫描
        qrcheck(code) {
            const params = {
                code
            }
            api.qrcheck(params).then((res) => {
                if (res.data.error_code === 0) {
                    if (res.data.data) {
                        localStorage.setItem('token', res.data.data);
                        this.$router.push('home');
                    }
                }
                if (res.data.error_code !== 0) {
                    this.refreshbul = true
                    clearInterval(this.codetimer)
                }

            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  发送验证码（无token验证）
        send() {
            const params = {
                phone: this.formquick.tel, // 手机号
                type: 1, //  1:手机登录  2.忘记密码 3.修改安全手机-验证原手机 4.修改安全手机-验证新手机 5 注册
                geetest_validate: this.geetest_validate //  极验的geetest_validate
            }
            api.send(params).then((res) => {
                if (res.data.error_code === 0) {
                    this.getcodebul = false
                    this.starttiming()
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
                    this.btnClick(2)
                }
            })
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
        //  切换登录方式
        switchtab(type) {
            this.type = type
        },
        //  密码登录
        loginpassword(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    this.btnClick(1)
                }
            })
        },
        //  快捷登录
        quicklogin() {
            this.loginphone()
        },
        //  去注册
        toregister() {
            this.$router.push({
                path: '/register'
            })
        },
        //  忘记密码
        tochangepassword() {
            this.$router.push({
                path: '/changepassword'
            })
        },
        btnClick(type) {
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
                                            this.geetest_validate = validate.geetest_validate
                                            if (type === 1) {
                                                this.loginpass()
                                            } else {
                                                this.send()
                                            }
                                        }
                                    });
                            });
                    });
                });
        },
        // 账号密码登录
        loginpass() {
            const params = {
                login_name: this.formpassword.username, // 登录账号：账号或手机号
                account_pwd: this.formpassword.password, // 账号密码
                geetest_validate: this.geetest_validate //  极验证
            }
            api.loginpass(params).then((res) => {
                if (res.data.error_code === 0) {
                    localStorage.setItem('token', res.data.data.token);
                    if (this.depuser) {
                        this.$locSet('newusername', this.formpassword.username)
                    } else {
                        this.$locRemove('newusername')
                    }
                    this.$router.push('home');
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        //  手机验证码登录
        loginphone() {
            const params = {
                phone: this.formquick.tel, //  手机号
                msgcode: this.formquick.checkcode //  验证码
            }
            api.loginphone(params).then((res) => {
                if (res.data.error_code === 0) {
                    localStorage.setItem('token', res.data.data.token);
                    this.$router.push('home');
                } else {
                    this.$message.error(res.data.error_msg)
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
    }
}
</script>

<style scoped>
@import "./css/common.css";
</style>
