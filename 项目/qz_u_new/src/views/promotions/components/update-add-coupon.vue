<template>
  <div class="update-add-coupon">
    <h2>{{ this.$route.params.id === '0' ? '添加专用礼券' : '重新申请' }}</h2>
    <el-form :model="couponForm" :rules="rules" ref="couponForm">
      <el-form-item label="礼券名称：" label-width="100px" align="left" prop="name">
        <el-input style="width: 400px" v-model="couponForm.name"></el-input>
      </el-form-item>

      <el-form-item label="优惠活动：" label-width="100px" align="left">
        <div>
          <el-radio v-model="couponForm.radio" :label="1" style="margin-top: -20px">
            <el-row class="test">
              <el-col :span="6">满减金额</el-col>

              <el-col :span="2" style="text-align: center">满</el-col>

              <el-col :span="7">
                <el-form-item
                  prop="full"
                  :rules="{required: couponForm.radio === 1,message:'请输入满减金额'}"
                >
                  <el-input v-model="couponForm.full" v-number-input></el-input>
                </el-form-item>
              </el-col>

              <el-col :span="2" style="text-align: center">减</el-col>

              <el-col :span="7">
                <el-form-item
                  prop="less"
                  :rules="{required: couponForm.radio === 1,message:'请输入满减金额'}"
                >
                  <el-input v-model="couponForm.less" v-number-input></el-input>
                </el-form-item>
              </el-col>
            </el-row>
          </el-radio>
        </div>

        <div style="margin-top: 5px">
          <el-radio v-model="couponForm.radio" :label="2">
            <el-row class="test">
              <el-col :span="6">满送礼品</el-col>

              <el-col :span="2" style="text-align: center">满</el-col>

              <el-col :span="7">
                <el-form-item
                  prop="full2"
                  :rules="{required: couponForm.radio === 2,message:'请输入满送金额'}"
                >
                  <el-input v-model="couponForm.full2" v-number-input></el-input>
                </el-form-item>
              </el-col>

              <el-col :span="2" style="text-align: center">送</el-col>

              <el-col :span="7">
                <el-form-item
                  prop="give"
                  :rules="{required: couponForm.radio === 2,message:'请输入满送礼品'}"
                >
                  <el-input v-model="couponForm.give"></el-input>
                </el-form-item>
              </el-col>
            </el-row>
          </el-radio>
        </div>
      </el-form-item>

      <el-form-item label="发放数量：" label-width="100px" align="left" prop="quantity">
        <el-input style="width: 400px" v-model="couponForm.quantity" v-number-input></el-input>
      </el-form-item>

      <el-form-item label="活动时间：" label-width="100px" align="left" prop="activityTime">
        <el-date-picker
          v-model="couponForm.activityTime"
          format="yyyy-MM-dd HH:mm:ss"
          type="datetimerange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
        ></el-date-picker>
      </el-form-item>

      <el-form-item label="有效时间：" label-width="100px" align="left" prop="effectiveTime">
        <el-date-picker
          v-model="couponForm.effectiveTime"
          format="yyyy-MM-dd HH:mm:ss"
          type="datetimerange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
        ></el-date-picker>
      </el-form-item>

      <el-form-item label="使用规则：" label-width="100px" align="left" prop="rule">
        <el-input
          v-model="couponForm.rule"
          type="textarea"
          :rows="4"
          placeholder="请输入内容"
          style="width: 400px"
        ></el-input>
      </el-form-item>

      <el-form-item label="模板选用：" label-width="100px" align="left">
        <div class="template-chose" v-if="couponForm.radio === 1">
          <img src="@/assets/modul_m1.png" alt />
          <img src="@/assets/modul_pc1.png" alt />
        </div>

        <div class="template-chose" v-if="couponForm.radio === 2">
          <img src="@/assets/modul_m2.png" alt />
          <img src="@/assets/modul_pc2.png" alt />
        </div>
      </el-form-item>

      <el-form-item label-width="20px">
        <!-- `checked` 为 true 或 false -->
        <el-checkbox v-model="couponForm.protocol"></el-checkbox>我已认真阅读并同意
        <strong @click="centerDialogVisible = true">《商家优惠券协议》</strong>
      </el-form-item>

      <el-form-item label-width="20px">
        <el-button type="danger" :disabled="!couponForm.protocol" @click="sumbit('couponForm')">上传审核</el-button>
        <el-button
          type="primary"
          style="background:#0cc;border: 1px solid #0cc"
          @click="preview('couponForm')"
        >预览</el-button>
        <el-button type="info" @click="$router.go(-1)">取消</el-button>
      </el-form-item>
    </el-form>

    <!--预览-->
    <el-dialog title="预览模板" :visible.sync="dialogVisible" width="400px">
      <div class="mj" v-if="couponForm.radio === 1">
        <div>
          移动端:
          <div class="mobile" style="margin-top: 15px">
            <p>
              <strong>{{ couponForm.less }}</strong>满
              <span>{{ couponForm.full }}</span>元可领
            </p>
            <p v-if="couponForm.effectiveTime !== ''">
              使用时间：{{ couponForm.effectiveTime[0] | dateFormat }} ~ {{
              couponForm.effectiveTime[1] | dateFormat }}
            </p>
            <div class="lq">
              立即
              <br />领取
            </div>
          </div>
        </div>

        <div style="margin-top: 15px">
          PC端:
          <div class="pc" style="margin-top: 10px">
            <p>
              <strong>{{ couponForm.less }}</strong>满
              <span>{{ couponForm.full }}</span>元可领
            </p>
            <p v-if="couponForm.effectiveTime !== ''">
              使用时间：{{ couponForm.effectiveTime[0] | dateFormat }} ~ {{
              couponForm.effectiveTime[1] | dateFormat }}
            </p>
          </div>
        </div>
      </div>

      <div class="ms" v-else-if="couponForm.radio === 2 ">
        <div>
          移动端:
          <div class="mobile" style="margin-top: 15px">
            <p>
              <strong>{{ couponForm.give }}</strong>满
              <span>{{ couponForm.full2 }}</span>元可领
            </p>
            <p v-if="couponForm.effectiveTime !== ''">
              使用时间：{{ couponForm.effectiveTime[0] | dateFormat }} ~ {{
              couponForm.effectiveTime[1] | dateFormat }}
            </p>
            <div class="lq">
              立即
              <br />领取
            </div>
          </div>
        </div>

        <div style="margin-top: 15px">
          PC端:
          <div class="pc" style="margin-top: 15px">
            <p>
              <strong>{{ couponForm.give }}</strong>满
              <span>{{ couponForm.full2 }}</span>元可领
            </p>
            <p v-if="couponForm.effectiveTime !== ''">
              使用时间：{{ couponForm.effectiveTime[0] | dateFormat }} ~ {{ couponForm.effectiveTime[1] |
              dateFormat }}
            </p>
          </div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="齐装网优惠券营销规则"
      :visible.sync="centerDialogVisible"
      width="800px"
      style="line-height:32px;"
      center
    >
      第一章 适用范围
      <br />本规则适用于所有齐装网平台发起的优惠券营销活动，商家必须遵守以下规则，方可参加齐装网平台的优惠券营销活动。
      <br />
      <br />第二章 定义
      <br />2.1通用优惠券
      <br />2.1.1通用优惠券是为装修业主提供的一种营销活动，由齐装网预先设定优惠内容及使用规则。各商家可自由参与该营销活动，开通并发放通用优惠券给装修业主领取和使用。
      <br />2.1专用优惠券
      <br />2.1.1专用优惠券是为装修业主提供的一种营销活动，由各商家自行定义优惠内容和使用规则，经齐装网工作人员审核通过即可发布至齐装网前端页面展示，装修业主领取后可至对应的装修门店进行使用。
      <br />
      <br />第三章 优惠券信息发布规范
      <br />3.1优惠券价格和描述规范
      <br />3.1.1优惠券的价格和描述必须真实并且符合逻辑，优惠价格一律以齐装网网站前端页面展示出来的价格为准。
      <br />3.1.2商家在经营活动的描述及宣传中，其内容应当提供真实、合法、准确客观的信息，不得使用含糊、易引起误解、以及广告法规定禁止使用的语言和文字，不得以保留最终解释权为由，损害消费者的合法权益。
      <br />3.2优惠券的发布和退出
      <br />3.2.1商家可自愿参与通用优惠券和专用优惠券的营销活动，通过商家用户中心自行上传或与齐装网平台的销售人员沟通后由销售人员代为上传，经齐装网工作人员审核通过后即可发布上线。
      <br />3.2.2商家可在用户中心下架优惠券营销活动或联系齐装网平台销售人员代为下架，下架后的优惠券信息将不再在显示在齐装网前端页面上。用户在商家下架前领取的优惠券，商家仍应按优惠券的内容履行服务。
      <br />3.2.3商家下架优惠券后，若想再次发布，仍需要经过齐装网工作人员的审核通过后才可上线。
      <br />3.2.4为保证优惠活动的公平性、严肃性和稳定性，商家一旦自愿参与通用优惠券和专用优惠券的营销活动，在齐装网平台显示的优惠券领取期间内或备案的优惠券数量、金额领完前，商家无权自行在用户中心下架优惠券营销活动。如有特殊原因确需下架的，需要经过齐装网工作人员的审核通过后才可下架。
      <br />3.3优惠券审核
      <br />3.3.1商家参与优惠券促销活动并上传优惠券营销信息后，齐装网平台将对商家发布的优惠券信息进行审核，审核通过后即可在齐装网平台前端页面发布上线，并会通过商家用户中心的消息通知等方式告知商家，商家可在用户中心查看已发布的优惠券信息。
      <br />3.3.2如商家上传的优惠券信息不符合本规则中的规范，齐装网平台有权取消该优惠券的发布，并会通过商家用户中心的消息通知等方式告知商家不可上线的原因。
      <br />3.3.3对于审核不通过的优惠券信息，商家可以进行修改并再次上传，再次审核通过后即可发布上线至齐装网平台前端页面。
      <br />
      <br />第四章 附则
      <br />4.1齐装网可根据平台运营情况随时调整本管理规则并向商家公示；商家在齐装网平台上发布优惠券促销信息即表示接受齐装网其后不时调整、颁布的管理规则。
      <br />4.2商家应遵守国家法律、行政法规、部门规章等规范性文件。对任何涉嫌违反国家法律、行政法规、部门规章等规范性文件的行为，本规则已有规定的，适用于本规则。本规则尚无规定的，齐装网有权酌情处理。但齐装网对商家的处理不免除其应承担的法律责任。商家在齐装网的任何行为，应同时遵守与齐装网及其关联公司签订的各项协议；如有违约即视为对本规则的违反。
      <br />4.3 本规则于2018年12月20日首次发布并生效。
      <br />
    </el-dialog>
  </div>
</template>

<script>
import api from '@/api/promotions';

export default {
  name: 'update-add-coupon',
  data() {
    const actTime = (rule, value, callback) => {
      if (value.length < 1) {
        callback(new Error('请选择活动时间'));
      } else if (
        new Date(this.couponForm.activityTime[0]) < this.couponForm.effectiveTime[0] ||
        new Date(this.couponForm.activityTime[1]) > this.couponForm.effectiveTime[1]
      ) {
        callback(new Error('活动时间需在有效时间内,请重新选择'));
      } else {
        callback();
      }
    };
    const effTime = (rule, value, callback) => {
      if (value.length < 1) {
        callback(new Error('请选择有效时间'));
      } else if (
        new Date(this.couponForm.activityTime[0]) < this.couponForm.effectiveTime[0] ||
        new Date(this.couponForm.activityTime[1]) > this.couponForm.effectiveTime[1]
      ) {
        callback(new Error('活动时间需在有效时间内,请重新选择'));
      } else {
        callback();
      }
    };
    return {
      couponForm: {
        id: '',
        name: '',
        radio: 1,
        quantity: '',
        activityTime: [],
        effectiveTime: [],
        rule:
          '1. 用户获得的优惠券将会自动发送至账户和用户手机号，是否获得优惠券以到账为准。\n' +
          '2. 活动过程中凡以不正当手段参与领券的用户，本公司有权终止其参与活动，并取消其领取资格（如已发放，该券予以作废）\n' +
          '3. 每张优惠券仅可使用一次。',
        protocol: false,
        full: '',
        full2: '',
        give: '',
        less: '',
      },
      dialogVisible: false,
      rules: {
        name: { required: true, message: '请输入礼券名称' },
        radio: { required: true, message: '请选择优惠活动' },
        quantity: { required: true, message: '请输入发放数量' },
        activityTime: { required: true, validator: actTime, trigger: 'blur' },
        effectiveTime: { required: true, validator: effTime, trigger: 'blur' },
        rule: { required: true, message: '请输入规则' },
      },
      centerDialogVisible: false,
    };
  },
  watch: {
    'couponForm.effectiveTime': {
      handler(val) {
        if (val === null) {
          this.couponForm.effectiveTime = [];
        }
      },
      immediate: true,
      deep: true,
    },
  },
  mounted() {
    if (this.$route.params.id !== '0') {
      this.couponForm.id = this.$route.params.id;
      this.getDetail();
    }
  },
  methods: {
    getDetail() {
      api.getCouponDetail(this.$route.params.id).then((res) => {
        this.couponForm.name = res.data.data.name;
        this.couponForm.radio = res.data.data.active_type;
        this.couponForm.quantity = res.data.data.amount;
        this.couponForm.activityTime.push(res.data.data.activity_start * 1000, res.data.data.activity_end * 1000);
        this.couponForm.effectiveTime.push(res.data.data.start * 1000, res.data.data.end * 1000);
        this.couponForm.full = res.data.data.money1;
        this.couponForm.less = res.data.data.money2;
        this.couponForm.full2 = res.data.data.money3;
        this.couponForm.give = res.data.data.gift;
      });
    },

    preview(formName) {
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.dialogVisible = true;
        } else {
          this.$message.error('请先完善活动信息');
          return false;
        }
      });
    },

    sumbit(formName) {
      const params = {
        id: this.couponForm.id,
        name: this.couponForm.name,
        active_type: this.couponForm.radio,
        money_meet: this.couponForm.full,
        money_subtract: this.couponForm.less,
        gift_meet: this.couponForm.full2,
        gift_subtract: this.couponForm.give,
        grant_num: this.couponForm.quantity,
        activity_start: new Date(this.couponForm.activityTime[0]),
        activity_end: new Date(this.couponForm.activityTime[1]),
        valid_start: new Date(this.couponForm.effectiveTime[0]),
        valid_end: new Date(this.couponForm.effectiveTime[1]),
        use_rule: this.couponForm.rule,
        module: this.couponForm.radio,
      };
      if (Number(this.couponForm.full) < Number(this.couponForm.less)) {
        this.$message.error('金额填写有误,减免金额需小于使用金额');
        return;
      }

      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          api
            .saveCoupon(params)
            .then((res) => {
              if (res.data.error_code === 0) {
                this.$message.success('操作成功');
                localStorage.setItem('activeName', 'effect');
                this.$router.push('/promotions');
              } else {
                this.$message.error(res.data.error_msg);
              }
            })
            .catch((error) => {
              this.$message.error(error);
            });
        } else {
          return false;
        }
      });
    },
  },

  filters: {
    dateFormat(time) {
      const date = new Date(time);
      const y = date.getFullYear();
      const m = date.getMonth() + 1 < 10 ? `0${date.getMonth() + 1}` : date.getMonth() + 1;
      const d = date.getDate() < 10 ? `0${date.getDate()}` : date.getDate();
      return `${y}-${m}-${d}`;
    },
  },
};
</script>

<style>
.el-checkbox__input.is-checked + .el-checkbox__label {
  color: #606266;
}
</style>

<style scoped lang="scss">
.update-add-coupon {
  padding: 20px;
  padding-bottom: 100px;
  background: #fff;

  h2 {
    margin: 0 0 30px 0;
    font-weight: 400;
  }

  .test {
    position: relative;
    top: 14px;
    display: inline-block;
    width: 375px;
    line-height: 40px;
  }
}

.template-chose {
  img {
    width: 350px;
    margin-right: 15px;
  }
}

.ms {
  p {
    margin: 0;
  }

  .mobile {
    box-sizing: border-box;
    width: 355px;
    height: 98px;
    padding-top: 18px;
    padding-left: 22px;
    color: #fff;
    background: url('../../../assets/modul_bgm_2.png') no-repeat center;
    background-size: 100%;

    p {
      font-size: 14px;
      line-height: 28px;

      strong {
        margin-right: 5px;
        font-size: 18px;
      }

      span {
        margin-left: 5px;
      }
    }

    .lq {
      position: absolute;
      top: 25px;
      right: 23px;
      font-weight: 600;
      font-size: 20px;
    }
  }

  .pc {
    box-sizing: border-box;
    width: 355px;
    height: 98px;
    padding-top: 18px;
    padding-left: 22px;
    color: #fff;
    background: url('../../../assets/modul_bgp_2.png') no-repeat center;
    background-size: 100%;

    p {
      font-size: 14px;
      line-height: 28px;

      strong {
        margin-right: 5px;
        font-size: 18px;
      }

      span {
        margin-left: 5px;
      }
    }
  }
}

.mj {
  p {
    margin: 0;
  }

  .mobile {
    position: relative;
    box-sizing: border-box;
    width: 355px;
    height: 98px;
    padding-top: 18px;
    padding-left: 22px;
    color: #fff;
    background: url('../../../assets/modul_bgm_1.png') no-repeat center;
    background-size: 100%;

    p {
      font-size: 14px;
      line-height: 28px;

      strong {
        margin-right: 5px;
        font-size: 18px;
      }

      span {
        margin-left: 5px;
      }
    }

    .lq {
      position: absolute;
      top: 25px;
      right: 23px;
      font-weight: 600;
      font-size: 20px;
    }
  }

  .pc {
    box-sizing: border-box;
    width: 355px;
    height: 98px;
    padding-top: 18px;
    padding-left: 22px;
    color: #fff;
    background: url('../../../assets/modul_bgp_1.png') no-repeat center;
    background-size: 100%;

    p {
      font-size: 14px;
      line-height: 28px;

      strong {
        margin-right: 5px;
        font-size: 18px;
      }

      span {
        margin-left: 5px;
      }
    }
  }
}
</style>
