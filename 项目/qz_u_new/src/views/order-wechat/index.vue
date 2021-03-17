// 微信接收订单

<template>
  <div>
    <div v-if="data.length !== 0">
      <div class="order-wechat">
        <qz-card title="微信接收订单" divider>
          <div class="bind">
            <span>已绑定账号</span>
            <!--  TODO: 绑定微信超过三个 添加微信账号不能用 -->
            <el-button
              v-if="data.length < 3"
              type="text"
              @click="handleButtonAddWechatClick"
            >+添加微信账号</el-button>
          </div>
          <el-row>
            <el-col :span="8" v-for="item in data" :key="item.open_id">
              <div class="item">
                <img :src="item.img" class="img" alt="微信头像" />
                <div class="con">
                  <p>
                    <span>微信昵称:</span>
                    <span>{{item.nickname}}</span>
                  </p>
                  <p class="city">
                    <span>所在城市:</span>
                    <span>{{item.city}}</span>
                  </p>
                </div>

                <el-popconfirm title="您确定删除该已绑定账号吗？" @onConfirm="handleIClick(item.open_id)">
                  <i slot="reference" class="el-icon-delete delete"></i>
                </el-popconfirm>
              </div>
            </el-col>
          </el-row>
        </qz-card>
      </div>
    </div>
    <div v-else>
      <qz-card title="微信接收订单" divider>
        <div>
          <p style="color:#ff5353; font-size:14px;">您的店铺尚未绑定微信接收订单，无法实时了解订单情况，建议您设置微信接收订单。</p>
          <p style="color:#ff5353; font-size:14px;">特别提示：为了订单的安全性，记得关注绑定账号情况哦！</p>
          <el-button type="danger" @click="handleButtonAddWechatClick">立即设置</el-button>
        </div>
      </qz-card>
    </div>

    <!-- 微信二维码 wechat -->
    <el-dialog
      title="扫码绑定"
      :visible.sync="wechat.dialogVisible"
      @close="handleDialogWechatClose"
      width="480px"
      destroy-on-close
    >
      <img :src="wechat.url" alt="wechat" />
      <p>请用微信扫描二维码</p>
    </el-dialog>

    <!-- 验证码 code -->
    <el-dialog
      width="480px"
      :visible.sync="code.dialogVisible"
      @close="handleDialogCodeClose"
      destroy-on-close
    >
      <p>为了您的账户安全，我们需要验证您的安全手机</p>
      <el-form label-position="right" label-width="80px" :model="code.form" ref="code.form">
        <el-form-item
          label="验证码："
          prop="code"
          :rules="[{ required: true, message: '请输入短信验证码', trigger: 'blur' }]"
        >
          <el-input
            v-model="code.form.code"
            maxlength="6"
            placeholder="请输入短信验证码"
            style="width:230px;margin-right:10px"
          ></el-input>
          <el-button
            type="danger"
            plain
            @click="handleButtonCodeGet"
            :disabled="btnDisabled"
          >{{btnTitle}}</el-button>
        </el-form-item>
        <el-form>
          <div style="text-align:right;">
            <el-button type="danger" @click="handleButtonCodeCheck">验证</el-button>
            <el-button type="danger" plain @click="handleButtonCodeCancel">取消</el-button>
          </div>
        </el-form>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import QzCard from '@/components/card.vue';
import ApiOrder from '@/api/order';
import ApiPublic from '@/api/public';
import dayjs from 'dayjs';

export default {
  name: 'OrderWechat',
  async created() {
    await this.getData();
  },
  components: { QzCard },
  data() {
    return {
      btnTitle: '获取验证码',
      btnDisabled: false,
      wechat: {
        dialogVisible: false,
        url: '',
      },
      code: {
        form: { code: '' },
        dialogVisible: false,
      },
      data: [],
    };
  },
  methods: {
    async handleIClick(openId) {
      const res = await ApiOrder.wechatDel({
        open_id: openId,
      });
      if (res.data.error_code === 0) {
        this.$message.success('删除成功');
        await this.getData();
      } else {
        this.$message.error('删除失败');
      }
    },
    async getData() {
      const res = await ApiOrder.wechat();
      if (res.data.error_code === 0) {
        this.data = res.data.data.list;
      } else {
        this.$message.error('微信列表数据获取失败');
      }
    },
    async handleButtonAddWechatClick() {
      const res = await ApiOrder.showBindQrcode();

      // 需要短信验证码校验
      if (res.data.error_code === 4000101) {
        this.code.dialogVisible = true;
      }
      // 短信验证码校验通过
      if (res.data.error_code === 0) {
        this.wechat.url = res.data.data.url;
        this.wechat.dialogVisible = true;
        this.timer = window.setInterval(async () => {
          const res2 = await ApiOrder.checkBindWechat();
          // 扫描并点击确认
          if (res2.data.error_code === 0) {
            this.wechat.dialogVisible = false;
            const res3 = await ApiOrder.wechat();
            this.data = res3.data.data.list;
            clearInterval(this.timer);
          }
          // 二维码过期
          if (res2.data.error_code === 4000600) {
            this.$message.info('二维码过期');
            this.url = '';
            this.wechat.dialogVisible = false;
            clearInterval(this.timer);
          }
        }, 1000);
      }
    },
    handleDialogWechatClose() {
      clearInterval(this.timer);
    },
    handleDialogCodeClose() {
      this.code.dialogVisible = false;
    },
    handleButtonCodeCancel() {
      this.code.dialogVisible = false;
    },
    async handleButtonCodeGet() {
      await this.sendCode();
    },
    async sendCode() {
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
                type: 2, // 1.跟换订单密码 2.绑定微信验证
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
    validateBtn() {
      let time = 60;
      const timer = setInterval(() => {
        if (time === 0) {
          clearInterval(timer);
          this.btnDisabled = false;
          this.btnTitle = '获取验证码';
        } else {
          this.btnTitle = `${time}秒后重试`;
          this.btnDisabled = true;
          time -= 1;
        }
      }, 1000);
    },

    async handleButtonCodeCheck() {
      this.$refs['code.form'].validate(async (valid) => {
        if (valid) {
          const res = await ApiOrder.showBindQrcode({ code: this.code.form.code });

          // FIXME: 验证逻辑不清晰有问题
          // FIXME: 验证error_code
          // 手机需要验证
          if (res.data.error_code === 4000101) {
            this.code.dialogVisible = true;
          }

          if (res.data.error_code === 0) {
            // 手机号验证成功，关闭手机号验证弹框
            this.code.dialogVisible = false;
            this.wechat.url = res.data.data.url;
            this.wechat.dialogVisible = true;
            this.timer = window.setInterval(async () => {
              const res2 = await ApiOrder.checkBindWechat();
              if (res2.data.error_code === 0) {
                this.wechat.dialogVisible = false;
                const result = await ApiOrder.wechat();
                this.data = result.data.data.list;
                clearInterval(this.timer);
                this.$refs['code.form'].resetFields();
              }
              // 二维码过期
              if (res2.data.error_code === 4000600) {
                this.$message.info('二维码过期');
                this.url = '';
                this.wechat.dialogVisible = false;
                clearInterval(this.timer);
              }
            }, 1000);
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'warning',
            });
          }
        }
      });
    },
  },
};
</script>

<style lang="less" scoped>
.order-wechat {
  .item {
    position: relative;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    width: 90%;
    height: 150px;
    padding: 10px;
    background: rgba(255, 255, 255, 1);
    border: 1px solid rgba(227, 227, 227, 1);
    border-radius: 2px;
    cursor: pointer;
    .img {
      display: block;
      width: 120px;
      height: 120px;
    }
    .con {
      width: 280px;
      height: 100%;
      margin-left: 10px;
      overflow: hidden;
      p {
        width: 100%;
        height: 40px;
        margin-bottom: 12px;
        overflow: hidden;
        color: rgba(51, 51, 51, 1);
        font-weight: bold;
        font-size: 16px;
        line-height: 40px;
        white-space: nowrap;
        text-overflow: ellipsis;
      }
      .city {
        font-weight: 400;
        font-size: 14px;
      }
    }
    .delete {
      position: absolute;
      top: 20px;
      right: 20px;
      color: #aeaeae;
      font-size: 20px;
    }
  }
  .item:hover {
    border: 1px solid rgba(243, 206, 206, 1);
  }
  .item:hover .delete {
    color: #ff5d5d;
  }
  .bind {
    width: 100%;
    margin: 10px 0;
    overflow: hidden;

    span {
      color: rgba(102, 102, 102, 1);
      font-weight: bold;
      font-size: 16px;
      line-height: 24px;
    }
    .el-button {
      float: right;
      color: #ff5353;
    }
  }
}
</style>

