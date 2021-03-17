//  修改订单密码

<template>
  <div class="password-udpate">
    <qz-card title="修改订单密码" divider>
      <el-form ref="form" :model="form" label-width="100px" :rules="rules">
        <el-form-item label="设置新密码" prop="password">
          <el-input
            type="password"
            placeholder="设置密码（6位以上，16位以下，包含字母数字）"
            style="width:360px"
            maxlength="16"
            v-model.trim="form.password"
          ></el-input>
        </el-form-item>
        <el-form-item label="重复新密码" prop="repeatPassword">
          <el-input
            type="password"
            placeholder="设置密码（6位以上，16位以下，包含字母数字）"
            style="width:360px"
            maxlength="16"
            v-model.trim="form.repeatPassword"
          ></el-input>
        </el-form-item>
        <el-form-item label="验证码" prop="code">
          <el-input placeholder="请输入验证码" style="width:160px;margin-right:14px" v-model="form.code"></el-input>
          <el-button type="danger" plain @click="clickSendCode" :disabled="btnDisabled">{{btnTitle}}</el-button>
        </el-form-item>
        <el-form-item>
          <el-button type="danger" @click="onSubmit">确认修改</el-button>
        </el-form-item>
      </el-form>
    </qz-card>
  </div>
</template>

<script>
import QzCard from '@/components/card.vue';
import ApiPublic from '@/api/public';
import dayjs from 'dayjs';
import ApiOrder from '@/api/order';

export default {
  components: { QzCard },
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
      btnTitle: '发送验证码至手机',
      btnDisabled: false,
      form: {
        password: '',
        repeatPassword: '',
        code: '',
      },
      rules: {
        password: [
          { required: true, message: '请输入新密码', trigger: 'blur' },
          { validator: validatePass, trigger: 'blur' },
        ],
        repeatPassword: [
          { required: true, message: '请重复输入新密码', trigger: 'blur' },
          { validator: validatePass2, trigger: 'blur' },
        ],
        code: [{ required: true, message: '请输入验证码', trigger: 'blur' }],
      },
    };
  },
  methods: {
    async onSubmit() {
      this.$refs.form.validate(async (valid) => {
        if (valid) {
          const data = { pass: this.form.password, code: this.form.code };
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
    validateBtn() {
      let time = 60;
      const timer = setInterval(() => {
        if (time === 0) {
          clearInterval(timer);
          this.btnDisabled = false;
          this.btnTitle = '发送验证码至手机';
        } else {
          this.btnTitle = `${time}秒后重试`;
          this.btnDisabled = true;
          time -= 1;
        }
      }, 1000);
    },
    async clickSendCode() {
      const vm = this;

      const res1 = await ApiPublic.geeTestApi1({
        client_type: 'web',
        scene: 2,
        timestamp: dayjs().valueOf(),
      });

      // eslint-disable-next-line no-undef
      initGeetest({ ...res1.data, offline: !res1.data.success, product: 'bind', https: false }, (captchaObj) => {
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
                vm.validateBtn();
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
  },
};
</script>

<style lang="less" scoped>
.password-update {
  .code {
    margin-right: 20px;
  }
}
</style>

