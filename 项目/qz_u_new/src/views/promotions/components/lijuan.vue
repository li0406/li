<template>
  <div class="lijuan-contains">
    <el-row class="title">可领取礼券</el-row>
    <!--数据-->
    <div class="table-box">
      <el-table :data="dataList" style="width: 100%" border v-loading="loading">
        <el-table-column label="礼券名称" align="center">
          <template slot-scope="scope">
            <span
              @click="$router.push(`/coupon/klqdetail/${scope.row.id}`)"
              class="lqName"
            >{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="立减金额" prop="realt_discount" align="center"></el-table-column>
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="handle(scope.row)" class="green">领用</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!--分页-->
      <div class="page">
        <el-pagination
          :current-page="page.currentPage"
          :page-sizes="[10, 20, 30, 50]"
          :page-size="page.pageSize"
          layout="total, sizes, prev, pager, next, jumper"
          :total="page.total"
          @current-change="currentChange"
          @size-change="sizeChange"
        ></el-pagination>
      </div>
    </div>

    <el-dialog :title="stepTitle" :visible.sync="dialogVisible" width="800px" center>
      <div v-show="ifShow">
        <div class="rules-content">
          <h3>第一章&nbsp; 适用范围</h3>
          <p>本规则适用于所有齐装网平台发起的优惠券营销活动，商家必须遵守以下规则，方可参加齐装网平台的优惠券营销活动。</p>
          <h3>第二章 定义</h3>
          <h4>2.1通用优惠券</h4>
          <p>2.1.1通用优惠券是为装修业主提供的一种营销活动，由齐装网预先设定优惠内容及使用规则。各商家可自由参与该营销活动，开通并发放通用优惠券给装修业主领取和使用。</p>
          <h4>2.1专用优惠券</h4>
          <p>2.1.1专用优惠券是为装修业主提供的一种营销活动，由各商家自行定义优惠内容和使用规则，经齐装网工作人员审核通过即可发布至齐装网前端页面展示，装修业主领取后可至对应的装修门店进行使用。</p>
          <h3>第三章 优惠券信息发布规范</h3>
          <h4>3.1优惠券价格和描述规范</h4>
          <p>3.1.1优惠券的价格和描述必须真实并且符合逻辑，优惠价格一律以齐装网网站前端页面展示出来的价格为准。</p>
          <p>3.1.2商家在经营活动的描述及宣传中，其内容应当提供真实、合法、准确客观的信息，不得使用含糊、易引起误解、以及广告法规定禁止使用的语言和文字，不得以保留最终解释权为由，损害消费者的合法权益。</p>
          <h4>3.2优惠券的发布和退出</h4>
          <p>3.2.1商家可自愿参与通用优惠券和专用优惠券的营销活动，通过商家用户中心自行上传或与齐装网平台的销售人员沟通后由销售人员代为上传，经齐装网工作人员审核通过后即可发布上线。</p>
          <p>3.2.2商家可在用户中心下架优惠券营销活动或联系齐装网平台销售人员代为下架，下架后的优惠券信息将不再在显示在齐装网前端页面上。用户在商家下架前领取的优惠券，商家仍应按优惠券的内容履行服务。/p&gt;&nbsp;</p>
          <p>3.2.3商家下架优惠券后，若想再次发布，仍需要经过齐装网工作人员的审核通过后才可上线。</p>
          <p>3.2.4为保证优惠活动的公平性、严肃性和稳定性，商家一旦自愿参与通用优惠券和专用优惠券的营销活动，在齐装网平台显示的优惠券领取期间内或备案的优惠券数量、金额领完前，商家无权自行在用户中心下架优惠券营销活动。如有特殊原因确需下架的，需要经过齐装网工作人员的审核通过后才可下架。</p>
          <h4>3.3优惠券审核</h4>
          <p>3.3.1商家参与优惠券促销活动并上传优惠券营销信息后，齐装网平台将对商家发布的优惠券信息进行审核，审核通过后即可在齐装网平台前端页面发布上线，并会通过商家用户中心的消息通知等方式告知商家，商家可在用户中心查看已发布的优惠券信息。</p>
          <p>3.3.2如商家上传的优惠券信息不符合本规则中的规范，齐装网平台有权取消该优惠券的发布，并会通过商家用户中心的消息通知等方式告知商家不可上线的原因。</p>
          <p>3.3.3对于审核不通过的优惠券信息，商家可以进行修改并再次上传，再次审核通过后即可发布上线至齐装网平台前端页面。</p>
          <h3>第四章 附则</h3>
          <p>4.1齐装网可根据平台运营情况随时调整本管理规则并向商家公示；商家在齐装网平台上发布优惠券促销信息即表示接受齐装网其后不时调整、颁布的管理规则。</p>
          <p>4.2商家应遵守国家法律、行政法规、部门规章等规范性文件。对任何涉嫌违反国家法律、行政法规、部门规章等规范性文件的行为，本规则已有规定的，适用于本规则。本规则尚无规定的，齐装网有权酌情处理。但齐装网对商家的处理不免除其应承担的法律责任。商家在齐装网的任何行为，应同时遵守与齐装网及其关联公司签订的各项协议；如有违约即视为对本规则的违反。</p>
          <p>4.3 本规则于2018年12月20日首次发布并生效。</p>
        </div>
        <el-checkbox v-model="protocol">我已认真阅读并同意该协议</el-checkbox>
        <br />
        <div class="footer">
          <el-button type="danger" :disabled="!protocol" @click="stepOne()">确 定</el-button>
        </div>
      </div>
      <div v-show="!ifShow">
        <el-form
          :model="ruleForm"
          :rules="rules"
          ref="ruleForm"
          label-width="100px"
          class="demo-ruleForm"
        >
          <el-form-item label="礼券名称：" label-width="100px" align="left">
            <span>{{modelName}}</span>
          </el-form-item>
          <el-form-item label="立减金额：" label-width="100px" align="left">
            <span>{{modeRealt}}</span>
          </el-form-item>
          <el-form-item label="活动时间：" label-width="100px" align="left" prop="activity">
            <el-date-picker
              v-model="ruleForm.activity"
              format="yyyy-MM-dd HH:mm:ss"
              value-format="yyyy-MM-dd HH:mm:ss"
              type="datetimerange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
            ></el-date-picker>
          </el-form-item>

          <el-form-item label="有效时间：" label-width="100px" align="left" prop="valid">
            <el-date-picker
              v-model="ruleForm.valid"
              format="yyyy-MM-dd HH:mm:ss"
              value-format="yyyy-MM-dd HH:mm:ss"
              type="datetimerange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
            ></el-date-picker>
          </el-form-item>
          <el-form-item label="领取数量：" label-width="100px" align="left" prop="grant_num">
            <el-input
              style="width: 400px"
              v-model="ruleForm.grant_num"
              maxlength="2"
              v-number-input
            ></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="submitForm('ruleForm')">确认</el-button>
            <el-button @click="resetForm">取消</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import api from '@/api/promotions';

export default {
  name: 'GlobalList',
  data() {
    const actTime = (rule, value, callback) => {
      if (value.length < 1) {
        callback(new Error('请选择活动时间'));
      } else {
        callback();
      }
    };
    const effTime = (rule, value, callback) => {
      if (value.length < 1) {
        callback(new Error('请选择有效时间'));
      } else {
        callback();
      }
    };
    return {
      // 搜索 活动主题
      eventName: '',
      // 搜索 活动状态
      eventStatus: '',
      // 页数信息
      page: {
        total: null,
        currentPage: 1,
        pageSize: 10,
      },
      // 列表数组
      dataList: [],
      loading: false,
      dialogVisible: false,
      protocol: false,
      stepTitle: '齐装网优惠券营销规则',
      ifShow: true,
      ruleForm: {
        activity: [],
        valid: [],
      },
      rules: {
        grant_num: { required: true, message: '请填写领取数量', trigger: 'blur' },
        activity: { required: true, validator: actTime, trigger: 'blur' },
        valid: { required: true, validator: effTime, trigger: 'blur' },
      },
      modelName: '',
      modeRealt: '',
    };
  },
  created() {
    this.getList();
  },
  watch: {
    'ruleForm.activity': {
      handler(val) {
        if (val === null) {
          this.ruleForm.activity = [];
        }
      },
      immediate: true,
      deep: true,
    },
    'ruleForm.valid': {
      handler(val) {
        if (val === null) {
          this.ruleForm.valid = [];
        }
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    // 初始化列表
    getList() {
      this.loading = true;
      const query = {
        page: this.page.currentPage,
        limit: this.page.pageSize,
      };
      api.canReceive(query).then((res) => {
        this.loading = false;
        this.dataList = res.data.data.list;
        this.page.total = res.data.data.page.total_number;
      });
    },

    // 改变页数
    currentChange(val) {
      this.page.currentPage = val;
      this.getList();
    },

    // 改变每页显示数量
    sizeChange(val) {
      this.page.pageSize = val;
      this.getList();
    },

    handle(row) {
      this.ruleForm.card_id = row.id;
      this.modelName = row.name;
      this.modeRealt = row.realt_discount;
      this.dialogVisible = true;
      this.protocol = false;
      this.ifShow = true;
      this.stepTitle = '齐装网优惠券营销规则';
    },
    stepOne() {
      this.ifShow = false;
      this.stepTitle = '领用';
    },
    submitForm(formName) {
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        const params = {
          card_id: this.ruleForm.card_id,
          activity_start: this.ruleForm.activity[0],
          activity_end: this.ruleForm.activity[1],
          valid_start: this.ruleForm.valid[0],
          valid_end: this.ruleForm.valid[1],
          grant_num: this.ruleForm.grant_num,
        };
        if (valid) {
          api
            .cardSave(params)
            .then((res) => {
              if (res.data.error_code === 0) {
                this.$message.success('操作成功');
                this.$refs[formName].resetFields();
                this.dialogVisible = false;
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
    resetForm() {
       this.$refs.ruleForm.clearValidate()
      this.dialogVisible = false;
      this.ruleForm = {
        activity: [],
        valid: [],
        grant_num: '',
      };
    },
  },
};
</script>
<style lang="less" scoped>
.el-link {
  margin: 0 10px;
}

.more-button {
  float: right;
}
.lijuan-contains {
  padding-bottom: 50px;
  background: #fff;
}
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

.page {
  margin-top: 15px;
  margin-bottom: 80px;
  text-align: right;
}
.ylsl {
  color: #03c;
}
.ylsl:hover {
  color: #03c;
  cursor: pointer;
}
.green {
  color: green;
  font-size: 14px;
}
.red {
  color: red;
  font-size: 14px;
}
.yellow {
  color: yellow;
  font-size: 14px;
}
.table-box {
  padding: 30px;
}
.lqName {
  color: #03c;
}

.lqName:hover {
  cursor: pointer;
}
.rules-content {
  height: 480px;
  margin-bottom: 20px;
  overflow-y: auto;
}
.footer {
  margin-top: 20px;
  text-align: center;
}
</style>

