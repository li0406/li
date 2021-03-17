<template>
  <div class="member-invoiceReport-add">
    <div class="qian-main">
      <div class="main">
        <div class="content-detail">
          <div class="flex mb20">
            <div v-if="invoiceDetails.status_name!=''" class="mr40">
              开票状态：
              <span class="danger-color">{{ invoiceDetails.status_name }}</span>
            </div>
            <div v-if="invoiceDetails.check_reason!=''">审核备注：{{ invoiceDetails.check_reason }}</div>
          </div>
          <div v-if="paymentDetails&&paymentDetails.length>0" class="mb20">小报备：</div>
          <div v-if="paymentDetails&&paymentDetails.length>0" class="mb20">
            <el-table :data="paymentDetails" border style="width: 100%">
              <el-table-column align="center" type="index" label="序号" />
              <el-table-column align="center" prop="company_name" label="公司名称" />
              <el-table-column align="center" prop="city_name" label="城市" />
              <el-table-column align="center" prop="cooperation_type_name" label="合作类型" />
              <el-table-column align="center" prop="creator_name" label="报备人" />
              <el-table-column align="center" prop="payment_total_money" label="收款金额" />
              <el-table-column align="center" prop="payment_date" label="汇款时间" />
              <el-table-column align="center" prop="exam_status_name" label="状态" />
              <el-table-column align="center" label="操作">
                <template slot-scope="scope">
                  <span class="check-btn" @click="toSmallReport(scope.row)">查看</span>
                </template>
              </el-table-column>
              <el-table-column align="center" width="240" label="预警">
                <template slot-scope="scope">
                  <span class="danger-color">{{ scope.row.warning_info }}</span>
                </template>
              </el-table-column>
            </el-table>
          </div>
          <div class="flex bor-bot-das">
            <div class="mr50 msg-w">
              <div
                v-if="invoiceDetails.type_name!=''"
                class="mb20"
              >开票类型：{{ invoiceDetails.type_name }}</div>
              <div
                v-if="invoiceDetails.billing_company_name!=''"
                class="mb20"
              >开票公司：{{ invoiceDetails.billing_company_name }}</div>
              <div
                v-if="invoiceDetails.is_in_account_name!=''"
                class="mb20"
              >是否到账：{{ invoiceDetails.is_in_account_name }}</div>
              <div
                v-if="(invoiceDetails.invoice_price!=0)&&(invoiceDetails.invoice_price!='')"
              >开票金额：{{ invoiceDetails.invoice_price }}</div>
            </div>
            <div class="msg-w">
              <div
                v-if="invoiceDetails.apply_email!=''"
                class="mb20"
              >申请人邮箱：{{ invoiceDetails.apply_email }}</div>
              <div
                v-if="invoiceDetails.receiver_name!=''"
                class="mb20"
              >收件人姓名：{{ invoiceDetails.receiver_name }}</div>
              <div
                v-if="invoiceDetails.receiver_phone!=''"
                class="mb20"
              >收件人电话：{{ invoiceDetails.receiver_phone }}</div>
              <div
                v-if="invoiceDetails.receiver_address!=''"
              >收件人地址：{{ invoiceDetails.receiver_address }}</div>
            </div>
          </div>
          <div class="flex bor-bot-das">
            <div class="mr50 msg-w">
              <div
                v-if="invoiceDetails.invoice_title!=''"
                class="mb20"
              >发票抬头：{{ invoiceDetails.invoice_title }}</div>
              <div
                v-if="invoiceDetails.taxpayer_identification_number!=''"
                class="mb20"
              >纳税人识别号：{{ invoiceDetails.taxpayer_identification_number }}</div>
              <div v-if="invoiceDetails.company_bank!='' || invoiceDetails. company_bank_account != ''">开户行及账号：{{ invoiceDetails.company_bank }} {{ invoiceDetails. company_bank_account }}</div>
            </div>
            <div class="msg-w">
              <div v-if="invoiceDetails.company_address!='' || invoiceDetails.company_phone!='' ">地址及电话：{{ invoiceDetails.company_address }} {{ invoiceDetails.company_phone }}</div>
            </div>
          </div>
          <div v-if="paymentPics!=''" class="bor-bot-das">
            <div class="mb20">小报备截图</div>
            <div>
              <img
                v-for="(img,index) of paymentPics"
                :key="index"
                class="imgwh mr10"
                :src="img.img_full"
                alt
                @click="smarepscrpic(paymentPics,index)"
              >
            </div>
          </div>
          <div v-if="invoicePics!=''" class="bor-bot-das">
            <div class="mb20">回传合同和营业执照</div>
            <div>
              <img
                v-for="(img,index) of invoicePics"
                :key="index"
                class="imgwh mr10"
                :src="img.img_full"
                alt
                @click="smarepscrpic(invoicePics,index)"
              >
            </div>
          </div>
          <div v-if="invoiceOtherPics!=''" class="bor-bot-das">
            <div class="mb20">其他说明</div>
            <div>
              <img
                v-for="(img,index) of invoiceOtherPics"
                :key="index"
                class="imgwh mr10"
                :src="img.img_full"
                alt
                @click="smarepscrpic(invoiceOtherPics,index)"
              >
            </div>
            <div v-if="invoiceDetails.other_remarks" class="sup">{{ invoiceDetails.other_remarks }}</div>
          </div>
          <div>
            <div
              v-if="invoiceDetails.invoice_report_username!=''"
              class="sup"
            >报备人：{{ invoiceDetails.invoice_report_username }}</div>
            <div v-if="invoiceDetails.top_team_name!=''">所属部门：{{ invoiceDetails.top_team_name }}</div>
          </div>
        </div>
      </div>
      <!-- 操作日志 -->
      <div v-if="invoiceLogs&&invoiceLogs.length>0">
          <el-table :data="invoiceLogs" border style="width: 100%">
            <el-table-column align="center" prop="time" label="时间" />
            <el-table-column align="center" prop="action_type" label="状态" />
            <el-table-column align="center" prop="remark" label="备注" />
            <el-table-column align="center" prop="op_name" label="操作人" />
          </el-table>
      </div>

    </div>
    <bigimg :isshow="showbigimg" :urllist="imglist" :sub="imgindex" />
  </div>
</template>

<script>
import api from '@/api/invoiceReport'
export default {
  components: {
    bigimg: () => import('./components/bigimg')
  },
  data() {
    return {
      showbigimg: false,
      imgindex: 0,
      imglist: [],
      //  查看发票报备
      //  入参从路由中query取id
      //  返回参数
      invoiceDetails: {},
      paymentDetails: [], //  小报备详情信息
      paymentPics: [], //  小报备截图
      invoicePics: [], //  发票报备图片
      invoiceOtherPics: [], //  发票报备其他图片
      invoiceLogs:[]  //操作日志
    }
  },
  created() {
    this.view()
  },
  methods: {
    toSmallReport(item) {
      this.$router.push({
        path: '/smallReport/detail',
        query: {
          id: item.id,
          type: item.cooperation_type
        }
      })
    },
    //  查看发票报备
    view() {
      const params = {
        id: this.$route.query.id
      }
      api
        .view(params)
        .then((res) => {
          if (
            parseInt(res.status) === 200 &&
            parseInt(res.data.error_code) === 0
          ) {
            this.invoiceDetails = res.data.data.invoiceDetails
            this.paymentDetails = res.data.data.paymentDetails //  小报备详情信息
            this.paymentPics = res.data.data.paymentPics //  小报备截图
            this.invoicePics = res.data.data.invoicePics //  发票报备图片
            this.invoiceOtherPics = res.data.data.invoiceOtherPics //  发票报备其他图片
            this.invoiceLogs = res.data.data.invoiceLogs //操作日志
          } else {
            this.$message({
              type: 'warning',
              message: res.data.error_msg
            })
          }
        })
        .catch((err) => {
          this.$message.error(err)
        })
    },
    smarepscrpic(imglist, index) {
      this.imglist = imglist
      this.imgindex = index
      this.showbigimg = true
    },
    changeisshow() {
      this.showbigimg = !this.showbigimg
    }
  }
}
</script>

<style lang="scss" scoped>
.member-invoiceReport-add {
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
  .flex {
    display: flex;
  }
  .qian-main {
    padding: 40px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
    .main {
      border: 1px solid rgba(228, 228, 228, 1);
      border-radius: 6px;
      margin-bottom: 20px;
    }
    .lh-40 {
      line-height: 40px;
    }
    .mb-20 {
      margin-bottom: 20px;
    }
    .check-btn {
      color: #409eff;
      cursor: pointer;
    }
    .content-detail {
      padding: 40px 60px;
    }
    .mb20 {
      margin: 0 0 20px 0;
    }
    .mr40 {
      margin: 0 40px 0 0;
    }
    .mr10 {
      margin: 0 10px 0 0;
    }
    .danger-color {
      color: #ff0000;
    }
    .bor-bot-das {
      border-bottom: 1px dashed #ccc;
      padding: 20px 0 15px 0;
    }
    .mr50 {
      margin-right: 50px;
    }
    .msg-w {
      width: 400px;
    }
    .imgwh {
      width: 115px;
      height: 97px;
    }
    .sup {
      margin: 20px 0 10px 0;
      word-wrap:break-word;
    }
  }
}
</style>
