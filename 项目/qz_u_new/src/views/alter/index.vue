<template>
<div class="alter-contains" v-loading="loading">
    <el-row class="title">账户中心</el-row>
    <div class="alter-table">
        <div class="head">
            <div class="w32">账号信息</div>
            <div class="w28">说明</div>
            <div class="w16">操作</div>
        </div>
        <div class="con">
            <div class="w100">
                登录用户名
                <span class="name">{{user}}</span>
            </div>
        </div>
        <div class="con">
            <div class="w32">
                登录手机
                <span class="name">{{tel_safe}}</span>
            </div>
            <div class="w28 make">绑定手机后可直接用手机号登录</div>
            <div class="w16 red" v-if="ifReplace" @click="unbind">更换手机号</div>
            <div class="w16 red" v-if="!ifReplace" @click="bind">更换手机号</div>
        </div>
        <div class="con">
            <div class="w32">
                登录密码
                <span class="name">******</span>
            </div>
            <div class="w28 make">绑定手机后修改登录密码</div>
            <div @click="changepass()" class="w16 red">修改密码</div>
        </div>
    </div>

    <el-dialog title="更换手机号" :visible.sync="dialogVisible" width="400px" top="300px" :before-close="handleClose" :close-on-click-modal="handleModal" class="dialog">
        <el-form label-position="right" label-width="80px" :model="formLabel">
            <el-form-item label="手机号码：">
                <span>{{tel_safe}}</span>
            </el-form-item>
            <el-form-item label="验证码：">
                <el-input class="code" v-model="code" maxlength="6" placeholder="请输入短信验证码"></el-input>
                <el-button plain class="codebtn" v-if="btnTitle" @click="btnClick" :disabled="disabled" v-preventReClick>{{btnTitle}}</el-button>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button type="danger" @click="save">保存</el-button>
        </span>
    </el-dialog>

    <el-dialog title="更换手机号" :visible.sync="dialogVisibleTwo" width="400px" top="300px" :before-close="handleCloseTwo" :close-on-click-modal="handleModal" class="dialog">
        <el-form label-position="right" label-width="80px" :model="formLabel">
            <el-form-item label="手机号码：">
                <el-input class="phone" v-model="xphone" maxlength="11" placeholder="请输入手机号码"></el-input>
            </el-form-item>
            <el-form-item label="验证码：">
                <el-input class="code" v-model="xcode" maxlength="6" placeholder="请输入短信验证码"></el-input>
                <el-button class="codebtn" v-if="btnNew" @click="btnClickTwo" :disabled="disabledTwo" v-preventReClick>{{btnNew}}</el-button>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button type="danger" @click="saveTwo">保存</el-button>
        </span>
    </el-dialog>
    <!-- 未获取到手机号 -->
    <el-dialog title="绑定手机号" :visible.sync="safeVisible" width="400px" top="300px" :close-on-click-modal="false" :show-close="false" class="dialog safe">
        <p class="tips">您当前未绑定手机号，为不影响您的正常使用，请绑定手机号</p>
        <el-form label-position="right" label-width="100px" :rules="rules" ref="safeForm" :model="safeForm">
            <el-form-item label="手机号码：" prop="phone">
                <el-col :span="16">
                    <el-input class="code" v-model="safeForm.phone" maxlength="11" placeholder="请输入手机号"></el-input>
                </el-col>
            </el-form-item>
            <el-form-item label="验证码：" prop="code">
                <el-col :span="16">
                    <el-input class="code" v-model="safeForm.code" maxlength="6" placeholder="请输入短信验证码"></el-input>
                </el-col>
                <el-col :span="8">
                    <el-button plain class="codebtn" v-if="btnTitle" @click="btnsafeClick('safeForm')" :disabled="disabled" v-preventReClick>{{btnTitle}}</el-button>
                </el-col>
            </el-form-item>
            <el-form-item>
                <el-button type="danger" @click="safeButton('safeForm')">确 定</el-button>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer"></span>
    </el-dialog>
</div>
</template>

<script>
import alter from '@/api/alter';
// eslint-disable-next-line no-unused-vars
import preventReClick from '@/utils/prevent-click';
import dayjs from 'dayjs';
import ApiHome from '@/api/home';
import {
    validPhone
} from '@/utils/validate';

export default {
    name: 'Alter',
    data() {
        const validatePhone = (rule, value, callback) => {
            if (!value) {
                callback(new Error('请输入正确的手机号'));
            } else if (!validPhone(value)) {
                callback(new Error('请填写11位正确手机号'));
            } else {
                callback();
            }
        };
        const validateCode = (rule, value, callback) => {
            if (!value && this.codeInfo) {
                callback(new Error('请输入短信验证码'));
            } else {
                callback();
            }
        };
        return {
            dialogVisible: false,
            formLabel: {},
            btnTitle: '获取验证码',
            disabled: false,
            code: '',
            phone: '18135515998',
            // 绑定
            dialogVisibleTwo: false,
            xphone: '',
            xcode: '',
            btnNew: '获取验证码',
            disabledTwo: false,
            // 时间参数
            totalTime: 60,
            tel_safe_true: '',
            tel_safe: '',
            unbind_random: '',
            ifReplace: true,
            loading: false,
            user: '',
            handleModal: false,
            // 未绑定安全手机
            safeVisible: false,
            safeForm: {
                phone: '',
                code: '',
            },
            rules: {
                phone: [{
                    required: true,
                    validator: validatePhone,
                    trigger: ['blur', 'change']
                }],
                code: [{
                    required: true,
                    validator: validateCode,
                    trigger: 'blur'
                }],
            },
            codeInfo: true,
        };
    },
    created() {
        this.getData();
        this.saleTel();
        // eslint-disable-next-line eqeqeq
        if (this.$route.params.safe == 1) {
            this.safeVisible = true;
        }
    },
    methods: {
        changepass() {
            this.$router.push({
                path: '/changepassword'
            })
        },
        saleTel() {
            ApiHome.saleTel().then((res) => {
                if (parseInt(res.data.error_code, 10) === 4000102) {
                    this.safeVisible = true;
                }
            });
        },
        // 获取验证码
        safeButton(safeForm) {
            this.codeInfo = true;
            // eslint-disable-next-line consistent-return
            this.$refs[safeForm].validate((valid) => {
                if (valid) {
                    alter
                        .bindFirst(this.safeForm)
                        // eslint-disable-next-line no-shadow
                        .then((res) => {
                            // eslint-disable-next-line radix
                            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                this.$message({
                                    message: '绑定成功',
                                    type: 'success',
                                    offset: '200',
                                });
                                this.$router.go(0);
                            } else {
                                this.$message({
                                    message: res.data.error_msg,
                                    type: 'warning',
                                });
                            }
                        });
                } else {
                    return false;
                }
            });
        },
        btnsafeClick(safeForm) {
            this.codeInfo = false;
            const vm = this;
            // eslint-disable-next-line consistent-return
            this.$refs[safeForm].validate((valid) => {
                if (valid) {
                    // eslint-disable-next-line radix
                    alter
                        .geetestapi1({
                            client_type: 'web',
                            scene: 2,
                            timestamp: dayjs().valueOf(),
                        })
                        .then((res) => {
                            // eslint-disable-next-line no-undef
                            initGeetest({
                                ...res.data,
                                offline: !res.data.success,
                                product: 'bind',
                                https: false
                            }, function (
                                captchaObj,
                            ) {
                                captchaObj.reset();
                                captchaObj
                                    .onReady(function () {
                                        captchaObj.verify();
                                    })
                                    .onSuccess(function () {
                                        const validate = captchaObj.getValidate();
                                        alter
                                            .geetestapi2({
                                                geetest_challenge: validate.geetest_challenge,
                                                geetest_validate: validate.geetest_validate,
                                                geetest_seccode: validate.geetest_seccode,
                                                scene: 2,
                                                client_type: 'web',
                                                t: new Date().getTime(),
                                            })
                                            .then((response) => {
                                                // eslint-disable-next-line eqeqeq
                                                if (response.data.status == 'success') {
                                                    // vm.validateBtn();
                                                    alter
                                                        .getSendCode({
                                                            phone: vm.safeForm.phone,
                                                            type: 4,
                                                            geetest_validate: validate.geetest_validate,
                                                            temp_id: '',
                                                        })
                                                        // eslint-disable-next-line no-shadow
                                                        .then((res) => {
                                                            // eslint-disable-next-line radix
                                                            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                                                vm.$message({
                                                                    message: '短信验证码已发送',
                                                                    type: 'success',
                                                                });
                                                                vm.validateBtn();
                                                            } else {
                                                                vm.$message({
                                                                    message: res.data.error_msg,
                                                                    type: 'warning',
                                                                });
                                                            }
                                                        });
                                                }
                                            });
                                    });
                            });
                        });
                } else {
                    return false;
                }
            });
        },
        getData() {
            alter.get({}).then((res) => {
                // eslint-disable-next-line radix
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    const form = res.data.data;
                    this.tel_safe_true = form.tel_safe_true;
                    this.tel_safe = form.tel_safe;
                    this.user = form.user;
                } else {
                    this.$message({
                        message: res.data.error_msg,
                        type: 'error',
                    });
                }
            });
        },
        unbind() {
            this.dialogVisible = true;
        },
        handleClose() {
            this.dialogVisible = false;
        },
        btnClick() {
            const vm = this;
            // eslint-disable-next-line radix
            alter
                .geetestapi1({
                    client_type: 'web',
                    scene: 2,
                    timestamp: dayjs().valueOf(),
                })
                .then((res) => {
                    // eslint-disable-next-line no-undef
                    initGeetest({
                        ...res.data,
                        offline: !res.data.success,
                        product: 'bind',
                        https: false
                    }, function (captchaObj) {
                        captchaObj.reset();
                        captchaObj
                            .onReady(function () {
                                captchaObj.verify();
                            })
                            .onSuccess(function () {
                                const validate = captchaObj.getValidate();
                                alter
                                    .geetestapi2({
                                        geetest_challenge: validate.geetest_challenge,
                                        geetest_validate: validate.geetest_validate,
                                        geetest_seccode: validate.geetest_seccode,
                                        scene: 2,
                                        client_type: 'web',
                                        t: new Date().getTime(),
                                    })
                                    .then((response) => {
                                        // eslint-disable-next-line eqeqeq
                                        if (response.data.status == 'success') {
                                            // vm.validateBtn();
                                            alter
                                                .getSendCode({
                                                    phone: vm.tel_safe_true,
                                                    type: 3,
                                                    geetest_validate: validate.geetest_validate,
                                                    temp_id: 202005111037,
                                                })
                                                // eslint-disable-next-line no-shadow
                                                .then((res) => {
                                                    // eslint-disable-next-line radix
                                                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                                        vm.$message({
                                                            message: '短信验证码已发送',
                                                            type: 'success',
                                                        });
                                                        vm.validateBtn();
                                                    } else {
                                                        vm.$message({
                                                            message: res.data.error_msg,
                                                            type: 'warning',
                                                        });
                                                    }
                                                });
                                        }
                                    });
                            });
                    });
                });
        },

        validateBtn() {
            let time = this.totalTime;
            const timer = setInterval(() => {
                if (time === 0) {
                    clearInterval(timer);
                    this.disabled = false;
                    this.btnTitle = '获取验证码';
                } else {
                    this.btnTitle = `${time}秒后重试`;
                    this.disabled = true;
                    // eslint-disable-next-line no-plusplus
                    time--;
                }
            }, 1000);
        },
        save() {
            if (!this.code) {
                this.$message({
                    message: '请输入短信验证码',
                    type: 'warning',
                });
                return;
            }
            this.loading = true;
            alter
                .check({
                    phone: this.tel_safe_true,
                    type: 3,
                    action: 'unbind',
                    code: this.code,
                })
                // eslint-disable-next-line no-shadow
                .then((res) => {
                    // eslint-disable-next-line radix
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.unbind_random = res.data.data.random;
                        this.ifReplace = false;
                        this.dialogVisible = false;
                        this.loading = false;
                        this.dialogVisibleTwo = true;
                    } else {
                        this.$message({
                            message: res.data.error_msg,
                            type: 'warning',
                        });
                    }
                });
        },
        // 更换手机号
        validateBtnTwo() {
            let time = this.totalTime;
            const timer = setInterval(() => {
                if (time === 0) {
                    clearInterval(timer);
                    this.disabledTwo = false;
                    this.btnNew = '获取验证码';
                } else {
                    this.btnNew = `${time}秒后重试`;
                    this.disabledTwo = true;
                    // eslint-disable-next-line no-plusplus
                    time--;
                }
            }, 1000);
        },
        bind() {
            this.dialogVisibleTwo = true;
        },
        // eslint-disable-next-line consistent-return
        validatePhone() {
            if (!this.xphone) {
                this.$message({
                    message: '请输入正确的手机号',
                    type: 'warning',
                });
            } else if (!/^(13|14|15|16|17|18|19)[0-9]{9}$/.test(this.xphone)) {
                this.$message({
                    message: '请填写11位正确手机号',
                    type: 'warning',
                });
            } else {
                this.errors = {};
                return true;
            }
        },
        handleCloseTwo() {
            this.dialogVisibleTwo = false;
        },
        btnClickTwo() {
            if (this.validatePhone()) {
                const vm = this;
                // eslint-disable-next-line radix
                alter
                    .geetestapi1({
                        client_type: 'web',
                        scene: 2,
                        timestamp: dayjs().valueOf(),
                    })
                    .then((res) => {
                        // eslint-disable-next-line no-undef
                        initGeetest({
                            ...res.data,
                            offline: !res.data.success,
                            product: 'bind',
                            https: false
                        }, function (
                            captchaObj,
                        ) {
                            captchaObj.reset();
                            captchaObj
                                .onReady(function () {
                                    captchaObj.verify();
                                })
                                .onSuccess(function () {
                                    const validate = captchaObj.getValidate();
                                    alter
                                        .geetestapi2({
                                            geetest_challenge: validate.geetest_challenge,
                                            geetest_validate: validate.geetest_validate,
                                            geetest_seccode: validate.geetest_seccode,
                                            scene: 2,
                                            client_type: 'web',
                                            t: new Date().getTime(),
                                        })
                                        .then((response) => {
                                            // eslint-disable-next-line eqeqeq
                                            if (response.data.status == 'success') {
                                                // vm.validateBtn();
                                                alter
                                                    .getSendCode({
                                                        phone: vm.xphone,
                                                        type: 1,
                                                        geetest_validate: validate.geetest_validate,
                                                        temp_id: '',
                                                    })
                                                    // eslint-disable-next-line no-shadow
                                                    .then((res) => {
                                                        // eslint-disable-next-line radix
                                                        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                                            vm.$message({
                                                                message: '短信验证码已发送',
                                                                type: 'success',
                                                            });
                                                            vm.validateBtnTwo();
                                                        } else {
                                                            vm.$message({
                                                                message: res.data.error_msg,
                                                                type: 'warning',
                                                            });
                                                        }
                                                    });
                                            }
                                        });
                                });
                        });
                    });
            }
        },
        // eslint-disable-next-line consistent-return
        async saveTwo() {
            const vm = this;
            if (this.validatePhone()) {
                if (!this.xcode) {
                    this.$message({
                        message: '请输入短信验证码',
                        type: 'warning',
                    });
                    return false;
                }

                this.loading = true;

                const res1 = await alter.check({
                    phone: this.xphone,
                    type: 3,
                    action: 'bind',
                    code: this.xcode,
                });
                if (res1.data.error_code === 0) {
                    const random = res1.data.data.random;

                    const res2 = await alter.bind({
                        phone: this.xphone,
                        unbind_random: this.unbind_random,
                        bind_random: random,
                    });

                    if (res2.data.error_code === 0) {
                        this.$message({
                            message: '更换手机号成功',
                            type: 'success',
                        });
                        this.getData();
                        this.dialogVisibleTwo = false;
                        this.loading = false;
                        this.ifReplace = true;

                        const res3 = await ApiHome.logout();

                        if (res3.data.error_code === 0) {
                            this.$locRemove('token')
                            this.$router.push('/login');
                        } else {
                            this.$message.error('退出失败');
                        }
                    } else {
                        this.$message({
                            message: res2.data.error_msg,
                            type: 'warning',
                        });
                        this.dialogVisibleTwo = false;
                        this.loading = false;
                        this.ifReplace = true;
                        this.code = '';
                        this.xcode = '';
                        this.xphone = '';
                        this.btnTitle = '获取验证码';
                        this.btnNew = '获取验证码';
                        this.totalTime = 60;
                        setTimeout(function () {
                            vm.$router.go(0);
                        }, 2000);
                    }
                } else {
                    this.$message({
                        message: res1.data.error_msg,
                        type: 'warning',
                    });
                }
            }
        },
    },
};
</script>

<style lang="less">
.alter-contains {
    height: 100%;
    background: #fff;

    .title {
        width: 100%;
        height: 60px;
        margin-bottom: 10px;
        padding-left: 30px;
        color: rgba(51, 51, 51, 1);
        font-weight: 400;
        font-size: 16px;
        line-height: 60px;
        border-bottom: 1px solid #f2f2f2;
    }

    .alter-table {
        width: 764px;
        margin: 30px;
        overflow: hidden;
        background: rgba(255, 255, 255, 1);
        border: 1px solid rgba(227, 227, 227, 1);

        .head {
            height: 45px;
            color: rgba(102, 102, 102, 1);
            font-weight: 400;
            font-size: 14px;
            line-height: 45px;
            text-align: left;
            background: rgba(250, 250, 250, 1);
        }

        .w32 {
            float: left;
            box-sizing: border-box;
            width: 320px;
            padding: 0 20px;

            .name {
                margin-left: 72px;
            }
        }

        .w28 {
            float: left;
            box-sizing: border-box;
            width: 280px;
            padding: 0 10px;
        }

        .w16 {
            display: block;
            float: left;
            box-sizing: border-box;
            width: 160px;
            padding: 0 10px;
        }

        .con {
            width: 100%;
            height: 70px;
            line-height: 70px;
            border-bottom: 1px solid rgba(227, 227, 227, 0.6);

            .w100 {
                box-sizing: border-box;
                width: 100%;
                height: 70px;
                padding: 0 20px;
                line-height: 70px;

                .name {
                    margin-left: 60px;
                }
            }

            .red {
                color: #ff5353;
                cursor: pointer;
            }

            .make {
                color: rgba(153, 153, 153, 1);
                font-weight: 400;
                font-size: 14px;
            }
        }
    }

    .el-dialog__footer {
        text-align: center;
    }

    .dialog {
        .el-form-item__label {
            font-size: 12px;
        }

        .code {
            width: 165px;
            height: 36px;
            font-size: 12px;
        }

        .codebtn {
            width: 90px;
            height: 34px;
            margin-left: 10px;
            color: rgba(255, 83, 83, 1);
            font-weight: 400;
            font-size: 12px;
            line-height: 33px;
            text-align: center;
            background-color: transparent;
            border: 0;
            outline: none;
            cursor: pointer;
        }

        .el-input__inner {
            height: 36px;
            line-height: 36px;
        }

        .phone {
            width: 95%;
            font-size: 12px;
        }
    }

    .safe {
        .el-dialog__body {
            padding-top: 0;
            padding-bottom: 0;
        }

        .tips {
            margin-bottom: 40px;
            color: #ff5353;
            font-size: 12px;
        }

        .codebtn {
            line-height: 8px;
        }
    }
}
</style>
