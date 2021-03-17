<!--
 * @Author: your name
 * @Date: 2020-06-28 09:49:10
 * @LastEditTime: 2020-07-08 09:11:03
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_u_new\src\views\workers-list\addStaff\setFirst.vue
-->
<template>
  <div>
    <div>
      <h3>填写账号信息</h3>
    </div>
    <div>
      <div class="role-msg">
        <div>
          <span>*</span>手机号码：
        </div>
        <div>
          <el-input class="role-input"
                    maxlength="11"
                    oninput="value=value.replace(/[^\d]/g,'')"
                    v-model="telNum"
                    placeholder="请填写手机号码"></el-input>
        </div>
      </div>
      <div class="role-msg">
        <div>
          <span>*</span>验证码:
        </div>
        <div>
          <el-input class="role-input"
                    v-model="code"
                    maxlength="6"
                    oninput="value=value.replace(/[\W]/g,'')"
                    placeholder="请输入短信验证码"></el-input>
        </div>
        <div>
          <el-button type="danger"
                     plain
                     v-if="btnTitle"
                     @click="testTel(1)"
                     :disabled="disabled"
                     v-preventReClick>{{btnTitle}}</el-button>
        </div>
      </div>
      <div class="role-msg">
        <div>
          <span>*</span>职位：
        </div>
        <div>
          <el-select class="role-input"
                     v-model="role"
                     placeholder="请选择职位">
            <el-option v-for="role of roleList"
                       :key="role.value"
                       :label="role.label"
                       :value="role.value"></el-option>
          </el-select>
        </div>
      </div>
      <div class="role-msg">
        <div>
          <span>*</span>初始密码：
        </div>
        <div>初始密码默认为qz123hy456</div>
      </div>
    </div>
    <div class="btns">
      <el-button class="btn-back"
                 @click="toStaffList()">返回</el-button>
      <el-button type="danger"
                 @click="toSetSecond()">下一步</el-button>
    </div>
  </div>
</template>
<script>
import alter from '@/api/alter';
import api from '@/api/staff';
import dayjs from 'dayjs';
// eslint-disable-next-line no-unused-vars
import preventReClick from '@/utils/prevent-click';

export default {
  data () {
    return {
      btnTitle: '获取验证码', // 获取验证码按钮
      disabled: false,
      totalTime: 60, //  倒数时间
      tel_safe: '',
      unbind_random: '',
      ifReplace: true,
      loading: false,
      telNum: '', //  电话号码
      code: '', //  验证码
      roleList: [
        {
          value: '1',
          label: '客服',
        },
        {
          value: '2',
          label: '设计师',
        },
        {
          value: '3',
          label: '首席设计师',
        },
        {
          value: '4',
          label: '设计总监',
        },
      ], // 职位列表
      role: '', // 选择的职位
    };
  },
  methods: {
    btnClick () {
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
          initGeetest({ ...res.data, offline: !res.data.success, product: 'bind', https: false }, function (captchaObj) {
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
                          phone: vm.telNum,
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
    validateBtn () {
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
    // eslint-disable-next-line consistent-return
    save () {

      if (!this.code) {
        this.$message({
          message: '请输入短信验证码',
          type: 'warning',
        });
        return;
      }
      if (!this.role) {
        this.$message.error('请选择职位');
        return;
      }
      this.loading = true;
      alter
        .check({
          phone: this.telNum,
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
            this.$router.push({
              path: '/set-second', // 跳转路由
            });
            this.$locRemove('setSecond');
            this.$locRemove('setThird');
            this.$locRemove('setFourth');
            this.$locSet('setFirst', {
              tel: this.telNum, // 电话号码
              role: this.role, //  职位
            });
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'warning',
            });
          }
        });
    },
    //  点击返回按钮
    toStaffList () {
      this.$locRemove('bul');
      this.$router.push({
        path: '/workers-list',
      });
    },
    //  清除四个阶段用户所选择的数据
    clearAllstageData () {
      this.$locRemove('setFirst');
      this.$locRemove('setSecond');
      this.$locRemove('setThird');
      this.$locRemove('setFourth');
    },
    //  手机号码验证
    testTel (bul) {
      if (!this.telNum) {
        this.$message.error('请输入手机号');
        return;
      }
      const params = {
        tel: this.telNum,
      };
      api
        .accountcheck(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            // this.$message({
            //   message: '提交成功！',
            //   type: 'success',
            // });
            if (bul) {
              this.btnClick();
            } else {

              this.save();
            }
          } else {
            //  失败
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  点击下一步
    toSetSecond () {
      if (!this.telNum) {
        this.$message.error('请输入手机号');
        return;
      }
      this.testTel(0)
    },
  },
  mounted () {
    this.clearAllstageData();
    this.$locSet('bul', 0);
    const params = this.$locGet('setFirst');
    if (params) {
      this.telNum = params.tel;
      this.role = params.role;
    }
  },
};
</script>
<style scoped>
.btns {
  display: flex;
  margin: 20px 0 0 0;
}
/* 取消按钮样式覆盖 */
.btn-back {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.btn-back:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
.role-msg {
  display: flex;
  align-items: center;
}
.role-msg > div:nth-of-type(1) {
  width: 100px;
  height: 40px;
  line-height: 40px;
  text-align: center;
}
.role-msg > div:nth-of-type(2) {
  width: 300px;
  height: 40px;
  margin: 10px;
  line-height: 40px;
}
.role-msg > div:nth-of-type(1) > span {
  margin: 0 3px 0 0;
  color: rgba(233, 71, 71, 1);
}
.role-input {
  width: 100%;
}
</style>
