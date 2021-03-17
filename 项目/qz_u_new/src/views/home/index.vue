// 商家首页
<template>
<div class="home-business">
    <el-row :gutter="10">
        <el-col :span="19">
            <qz-card class="fund" v-if="isFund && mode == 1 || mode == 3">
                <fund></fund>
            </qz-card>
            <qz-card class="fund" v-if="isFund && mode == 2">
                <fund-two :col1="8" :col2="16"></fund-two>
            </qz-card>
            <!-- <div class="flex" v-if="isFund && (mode == 3)">
              <qz-card class="fund flex1">
                  <fund></fund>
              </qz-card>
              <div class="fund modsign">
                <div class="font16 col333 signtitle">保产值模式数据</div>
                <div class="flex signdata">
                  <div>
                    <div class="font24">{{guaranteeddata.count}}</div>
                    <div class="font14 mt5">已分配订单</div>
                  </div>
                  <div>
                    <div><span class="font24">{{guaranteeddata.current}}</span>万</div>
                    <div class="font14 mt5">已完成产值</div>
                  </div>
                  <div>
                    <div><span class="font24">{{guaranteeddata.target}}</span>万</div>
                    <div class="font14 mt5">目标产值</div>
                  </div>
                </div>
              </div>
            </div> -->
            <div class="flex" v-if="isFund && mode == 4">
              <qz-card class="fund flex1">
                  <fund-two :col1="16" :col2="8"></fund-two>
              </qz-card>
              <div class="fund modsign">
                <div class="font16 col333 signtitle">保产值模式数据</div>
                <div class="flex signdata">
                  <div>
                    <div class="font24">{{guaranteeddata.count}}</div>
                    <div class="font14 mt5">已分配订单</div>
                  </div>
                  <div>
                    <div><span class="font24">{{guaranteeddata.current}}</span>万</div>
                    <div class="font14 mt5">已完成产值</div>
                  </div>
                  <div>
                    <div><span class="font24">{{guaranteeddata.target}}</span>万</div>
                    <div class="font14 mt5">目标产值</div>
                  </div>
                </div>
              </div>
            </div>
            <el-row class="back" v-if="isFund"></el-row>
            <qz-card title="客户跟进">
                <customer></customer>
            </qz-card>
            <qz-card title="最新城市签单动态" extra="齐装网，业主的选择！">
                <new-city-order></new-city-order>
            </qz-card>
            <qz-card title="最新申请量房动态" extra="成为齐装网会员，立刻获得业主信息！">
                <new-aplly></new-aplly>
            </qz-card>
        </el-col>
        <el-col :span="5" class="home-right">
            <qz-card title="齐装云管家APP">
                <app-code></app-code>
            </qz-card>
            <qz-card title="操作记录" info="若不是您本人操作，请与齐装网客服联系，谢谢！" divider>
                <operation></operation>
            </qz-card>
            <qz-card title="齐装网在线服务">
                <div class="hot-img" style="display:inline-block;">
                    <img src="@/assets/service.png" />
                </div>
                <div style="display:inline-block;">
                    <p class="hot">
                        <span>热线:</span>
                        <span>400-6969-336</span>
                    </p>
                    <p class="hot">
                        <span>邮箱:</span>
                        <span>services@qizuang.com</span>
                    </p>
                </div>
            </qz-card>
        </el-col>
    </el-row>
    <el-dialog title="更换密码" :visible.sync="dialogVisible" width="390px" :close-on-click-modal="isFund" :show-close="isFund">
        <span class="tips">您当前账号密码太弱,为保障安全,请您重新设置密码</span>
        <el-form :model="ruleForm" status-icon :rules="rules" ref="ruleForm" label-width="70px" class="demo-ruleForm">
            <el-form-item label="密码" prop="password">
                <el-input type="password" v-model="ruleForm.password"></el-input>
            </el-form-item>
            <el-form-item label="确认密码" prop="confirm_password">
                <el-input type="password" v-model="ruleForm.confirm_password"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="danger" @click="submitForm('ruleForm')">提交</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
    <div class="bindNum" v-if="isFund && bindNum > 0">
        <span class="close el-icon-close" @click="closeWork"></span>
        <div class="title">绑定提醒</div>
        <div class="con">
            您当前仍有
            <span>{{bindNum}}</span>位设计师未绑定手机号，为不影响子账号功能的使用，请前往绑定设计师手机号。
        </div>
        <el-button class="go" type="danger" @click="goWork">立即前往</el-button>
    </div>
    <!-- 账户安全提示弹窗 -->
    <el-dialog :show-close="false" :close-on-click-modal="false" custom-class="tips-dialog" top="240px" :visible.sync="securitytips">
        <div class="tips-div">
            <img class="tips-img" src="https://staticqn.qizuang.com/custom/20201012/Fu_MTwZRbhM9xq-6DVQmEtV3QeuC" alt="">
        </div>
        <div class="w365">
            <div class="font18 col333 mt27">账号安全提示</div>
            <div class="font14 col999 mt9 lh">检测到您的账户存在多次密码输入错误的情况，建议您重新设置登录密码以防账号泄漏</div>
            <div class="flex mt43 jus-bet">
                <div class="cancel-btn point font14" @click="cancel()">忽略</div>
                <div class="setup-btn colE46A6A point font14" @click="setpassword()">立即设置密码</div>
            </div>
        </div>
    </el-dialog>
</div>
</template>

<script>
import QzCard from '@/components/card.vue';
import home from '@/api/home';
import api from '@/api/redosystem';
import ApiPublic from '@/api/public';
// import QZ_CONFIG from '@/utils/config';
import Operation from './operation.vue';
import NewAplly from './new-apply.vue';
import Fund from './fund.vue'; // 1.0 常规会员
import FundTwo from './fund-two.vue'; // 2.0 新签返会员
import Customer from './customer.vue';
import NewCityOrder from './new-city-order.vue';
import AppCode from './app-code.vue';

export default {
    data() {
        const validatePass = (rule, value, callback) => {
            const reg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,18}$/g;
            if (value === '') {
                callback(new Error('请输入密码'));
            } else if (!reg.test(value)) {
                callback(new Error('6-18个字符之间，字母与数字组合'));
            } else {
                if (this.ruleForm.confirm_password !== '') {
                    this.$refs.ruleForm.validateField('confirm_password');
                }
                callback();
            }
        };
        const validatePass2 = (rule, value, callback) => {
            if (value === '') {
                callback(new Error('请再次输入密码'));
            } else if (value !== this.ruleForm.password) {
                callback(new Error('两次输入密码不一致!'));
            } else {
                callback();
            }
        };
        return {
            securitytips: false, // 账户安全提示弹窗 默认不弹出
            tag: '',
            mode: '',
            isFund: false,
            dialogVisible: false,
            ruleForm: {
                password: '',
                confirm_password: '',
            },
            rules: {
                password: [{
                    validator: validatePass,
                    trigger: 'blur'
                }],
                confirm_password: [{
                    validator: validatePass2,
                    trigger: 'blur'
                }],
            },
            bindNum: '',
            guaranteeddata: {
                count: 0,// 已分配订单
                current: 0,// 完成值
                target: 0// 目标值
            }
        };
    },
    created() {
        if(this.$route.query.token||localStorage.getItem('token')){
            if (this.$route.query.token) {
                localStorage.setItem('token', this.$route.query.token);
            }
            this.getBasicinfo();
            const bul = Boolean(sessionStorage.getItem('securitytips'))
            if(!bul){
                this.accountsafecheck();
            }
        }else{
            this.$router.push({
                path:'login'
            })
        }
    },
    methods: {
        // 账号安全检测
        accountsafecheck() {
            const params = {}
            api.accountsafecheck(params).then((res) => {
                if (res.data.error_code === 310007) { // 登录次数过多
                    this.securitytips = true
                }
            }).catch((err) => {
                this.$message.error(err)
            })
        },
        cancel() {
            this.securitytips = false
            sessionStorage.setItem('securitytips',true)
        },
        setpassword() {
            this.$router.push({
                path: '/changepassword'
            })
            // window.location.href = `${QZ_CONFIG.CP_OLD_BASE}/getpassword/`;
        },
        getBasicinfo() {
            ApiPublic.getBasicinfo().then((res) => {
                if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
                    const base = res.data.data;
                    this.tag = base.tag;
                    this.mode = base.mode;
                    if(base.mode==='4'){
                      this.guaranteed();
                    }
                    if (base.tag === 'company') {
                        this.isFund = true;
                        // 1. 先弱密码检测,2.后商家电话信息监测
                        // this.compwdcheck();
                        this.saleTel();
                        this.unbindtelnotice();
                    } else {
                        this.isFund = false;
                        this.pwdcheck(); // 用户信息监测
                    }
                } else {
                    this.$message({
                        message: res.data.error_msg,
                        type: 'error',
                    });
                }
            });
        },
        saleTel() {
            home.saleTel().then((res) => {
                if (parseInt(res.data.error_code, 10) === 4000102) {
                    this.$message.error(res.data.error_msg);
                    this.$router.push({
                        name: 'Alter',
                        params: {
                            safe: 1
                        }
                    });
                } else {
                    this.compwdcheck();
                }
            });
        },
        guaranteed(){
          home.guaranteed().then((res) => {
                if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
                    this.guaranteeddata = res.data.data
                } else {
                    this.$message({
                        message: res.data.error_msg,
                        type: 'error',
                    });
                }
            });
        },
        unbindtelnotice() {
            ApiPublic.unbindtelnotice().then((res) => {
                if (parseInt(res.data.error_code, 10) === 0) {
                    this.bindNum = res.data.data;
                }
            });
        },
        closeWork() {
            this.bindNum = 0;
        },
        goWork() {
            this.$router.push({
                path: '/workers-list'
            });
        },
        pwdcheck() {
            home.pwdcheck().then((res) => {
                if (parseInt(res.data.error_code, 10) === 310003) {
                    this.$message.error('您当前帐户密码太弱!');
                    this.dialogVisible = true;
                }
            });
        },
        submitForm(formName) {
            // eslint-disable-next-line consistent-return
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    home.pwdup(this.ruleForm).then((res) => {
                        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
                            this.$message({
                                message: '修改密码成功',
                                type: 'success',
                            });
                            this.dialogVisible = false;
                        } else {
                            this.$message({
                                message: res.data.error_msg,
                                type: 'error',
                            });
                        }
                    });
                } else {
                    // console.log('error submit!!');
                    return false;
                }
            });
        },
        compwdcheck() {
            home.pwdcheck().then((res) => {
                if (parseInt(res.data.error_code, 10) === 310003) {
                    this.$confirm('您当前帐户密码过于简单,为了保障您的账户安全,请前往修改密码', '提示', {
                            confirmButtonText: '前往修改',
                            closeOnClickModal: false,
                            showCancelButton: false,
                            showClose: false,
                            closeOnPressEscape: false,
                            type: 'error',
                        })
                        .then(() => {
                            this.$router.push({
                                path: '/changepassword'
                            })
                            // window.location.href = `${QZ_CONFIG.CP_OLD_BASE}/getpassword/`;
                        })
                        .catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消修改',
                            });
                        });
                }
            });
        },
    },
    components: {
        QzCard,
        Operation,
        NewAplly,
        Fund,
        FundTwo,
        Customer,
        NewCityOrder,
        AppCode,
    },
};
</script>

<style lang="less">
.home-business {
    position: relative;
    width: 100%;

    .back {
        height: 72px;
        margin-bottom: 10px;
        background: rgba(255, 250, 243, 1);
    }

    .hot-img {
        float: left;

        img {
            vertical-align: initial;
        }
    }

    .hot {
        margin: 10px 0;
        margin-left: 10px;
    }

    .fund {
        .header {
            display: none;
        }

        .content {
            padding: 0;
        }
    }

    .home-right {
        margin-bottom: 200px;
    }

    .tips {
        color: red;
        font-size: 12px;
    }

    .el-dialog__body {
        padding-top: 0;
    }

    .demo-ruleForm {
        margin-top: 30px;

        .el-input {
            width: 250px;
        }

        .el-button {
            padding: 12px 110px;
        }

        .el-input__inner {
            padding-right: 28px;
        }
    }

    .bindNum {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 99;
        box-sizing: border-box;
        width: 362px;
        padding: 14px 26px 14px 13px;
        background-color: #fff;
        border: 1px solid #ebeef5;
        border-radius: 8px;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);

        .close {
            position: absolute;
            top: 14px;
            right: 15px;
            color: #909399;
            font-size: 20px;
            cursor: pointer;
        }

        .title {
            color: #303133;
            font-weight: 700;
            font-size: 16px;
            line-height: 30px;
        }

        .con {
            margin: 6px 0 0;
            color: #606266;
            font-size: 14px;
            line-height: 21px;
            text-align: justify;

            span {
                color: rgba(234, 53, 53, 1);
            }
        }

        .go {
            float: right;
            margin-top: 20px;
        }
    }

    .tips-dialog {
        width: 420px;
        height: 420px;
        border-radius: 8px;
    }

    .tips-div {
        width: 220px;
        height: 176px;
        margin: 0 auto;
    }

    .tips-img {
        width: 100%;
        height: 100%;
    }

    .col333 {
        color: #333;
    }

    .font18 {
        font-size: 18px;
    }

    .col999 {
        color: #999;
    }

    .font14 {
        font-size: 14px;
    }

    .w365 {
        width: 365px;
        margin: 0 auto;
    }

    .flex {
        display: flex;
    }

    .mt27 {
        margin-top: 27px;
    }

    .mt9 {
        margin-top: 9px;
    }

    .mt43 {
        margin-top: 43px;
    }

    .jus-bet {
        justify-content: space-between;
    }

    .cancel-btn {
        width: 160px;
        height: 40px;
        color: #7D7D7D;
        line-height: 40px;
        text-align: center;
        border: 1px solid #DADADA;
        border-radius: 2px;
    }

    .setup-btn {
        width: 160px;
        height: 40px;
        color: #fff;
        line-height: 40px;
        text-align: center;
        border-radius: 2px;
    }

    .colE46A6A {
        background-color: #E46A6A;
    }

    .point {
        cursor: pointer;
    }

    .lh {
        line-height: 20px;
    }
    .modsign{
      width: 600px;
      margin: 0 0 10px 10px;
      background-color: #fff;
    }
    .font24{
      font-size: 24px;
    }
    .font16{
      font-size: 16px;
    }
    .bold{
      font-weight: bold;
    }
    .col666{
      color: #666;
    }
    .signdata{
      justify-content: space-around;
      margin: 60px 0 0 0;
      text-align: center;
    }
    .signtitle{
      margin: 30px 0 0 30px;
    }
    .mt5{
      margin-top: 5px;
    }
}
</style>
