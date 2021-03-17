<template>
  <div class="smallreport-detail">
    <div v-loading="loading" class="main">
      <el-form ref="form" :model="data" label-width="150px" size="mini">
        <el-form-item label="公司名称：">
          <span>{{ data.company_name }}</span>
          <el-button v-if="data.is_related === 2" type="primary" class="ml50" @click="handleCheck">已报备会员</el-button>
        </el-form-item>
        <el-form-item label="会员类型：">{{ data.cooperation_type_name }}</el-form-item>
        <el-form-item :label="(type === 11 ? '退款':'收款') + '城市：'">{{ data.city_name }}</el-form-item>
        <el-form-item v-if="type === 1" label="会员倍数：">{{ data.viptype }}</el-form-item>
        <el-form-item v-if="type === 14" label="会员倍数：">标杆会员（保产值）</el-form-item>
        <el-form-item v-if="type === 6 || type === 8 || type === 10" label="返点比例：">{{ data.back_ratio }}</el-form-item>
        <el-form-item v-if="type === 15" label="返点比例：">标杆会员（保产值）</el-form-item>
        <el-form-item v-if="type === 8" label="返点订单：">
          <span>{{ data.order_id }}</span>
          <span class="qd-jine">签单金额：</span>
          <span>{{ data.order_qiandan_jine }}万元 &nbsp; (
            <span :style="{'color': data.uback_money_compare === 1 ? '#009900' : '#c00'}">{{ data.order_back_money }}</span>
            )</span>
        </el-form-item>

        <el-form-item v-if="type === 10 || type === 13" label="保证金余额：">{{ company.deposit_money }}</el-form-item>
        <el-form-item v-if="type === 10" label="保证金抵扣轮单费：" :class="Number(data.deposit_to_round_money) > Number(company.deposit_money) ? 'red' : 'green'">{{ data.deposit_to_round_money }}</el-form-item>
        <el-form-item v-if="type === 13" label="保证金抵扣金额：" :class="Number(data.deposit_to_platform_money) > Number(company.deposit_money) ? 'red' : 'green'">{{ data.deposit_to_platform_money }}</el-form-item>
        <el-form-item :label="(type === 11 ? '退款':'收款') + '类型：'">{{ data.payment_type_name }}</el-form-item>
        <el-form-item v-if="type !== 10 && type != 11 && type != 13" label="收款金额：">{{ data.payment_total_money }}</el-form-item>
        <el-form-item v-if="type == 11" label="退款金额：">{{ data.refund_money }}</el-form-item>
        <el-form-item v-if="type === 1 || type === 14" label="会员费：">{{ data.round_order_money }}</el-form-item>
        <el-form-item v-if="type === 6 || type === 15" label="轮单费：">{{ data.round_order_money }}</el-form-item>
        <el-form-item v-if="type === 1 || type === 6 || type === 14 || type === 15" label="平台使用费：">{{ data.platform_money }}</el-form-item>
        <el-form-item v-if="(type === 1 || type === 6 || type === 12 || type === 14 || type === 15) && data.platform_money || type === 13" label="平台使用费有效期：">{{ data.platform_money_start_date }} 至 {{ data.platform_money_end_date }}</el-form-item>
        <el-form-item v-if="type === 6 || type === 15" label="保证金：">{{ data.deposit_money }}</el-form-item>
        <div v-if="type === 8" class="money-item">
          <span class="title">保证金抵扣（算业绩）：</span>
          <span class="num">{{ data.deposit_money }}</span>
          <span class="title">轮单费抵扣（不算业绩）：</span>
          <span>{{ data.round_order_money }}</span>
        </div>
        <el-form-item v-if="data.other_money" label="其他金额：">{{ data.other_money }}</el-form-item>
        <el-form-item v-if="data.other_money" label="其他金额类型：">{{ data.other_money_remark }}</el-form-item>
        <el-form-item v-if="type !== 10 && type !== 11 && type !== 13" label="收款方名称：">
          <template v-if="data.payee_list.length > 1">
            <span v-for="item in data.payee_list" :key="item.payee_px" class="person-list">{{ item.payee_type_name }}
              <template v-if="item.payee_money > 0">
                (<span class="blue">{{ item.payee_money }}</span>)
              </template>
            </span>
          </template>
          <temppate v-else-if="data.payee_list.length === 1">
            <span class="person-list">{{ data.payee_list[0].payee_type_name }}（<span class="blue">{{ data.payee_list[0].payee_money ? data.payee_list[0].payee_money : data.payment_total_money }}</span>）</span>
          </temppate>
        </el-form-item>
        <el-form-item :label="(type === 11 ? '退款':'汇款') + '时间：'">{{ data.payment_date_show }}</el-form-item>
        <el-form-item v-if="type !== 10 && type !== 11 && type !== 13" label="汇款方名称：">{{ data.payment_uname }}</el-form-item>
        <el-form-item v-if="data.auth_imgs.length > 0" :label="(type === 11 ? '退款':'汇款') + '凭证：'">
          <el-row>
            <el-col v-for="(item,index) in data.auth_imgs" :key="item.img_src" :span="6">
              <img :src="item.img_full" alt class="upimg" @click="preview(index)">
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="销售备注：">
          <div class="w600">{{ data.remark }}</div>
        </el-form-item>
        <el-form-item label="报备人：">
          <span>{{ data.creator_name }}</span>
          <span v-if="data.person_list.length>0" class="qd-jine">其他业绩人：</span>
          <span v-if="data.person_list">
            <span
              v-for="(item,index) in data.person_list"
              :key="index"
              class="person-list"
            >{{ item.saler_name }}({{ item.share_ratio_text }})</span>
          </span>
          <span v-else>-</span>
        </el-form-item>
        <el-form-item label="审批状态：">
          <span>{{ data.exam_status_name }}</span>
        </el-form-item>
        <el-form-item label="备注：">
          <span v-if="data.exam_status === 4">{{ data.exam_reason }}</span>
        </el-form-item>
        <el-table
          :data="data.check_log"
          style="width: 100%"
        >
          <el-table-column
            label="时间"
            align="center"
          >
            <template slot-scope="scope">
              {{ scope.row.created_at | filtersTime }}
            </template>
          </el-table-column>
          <el-table-column
            prop="action_type"
            label="状态"
            align="center"
          />
          <el-table-column
            prop="remark"
            label="备注"
            align="center"
          />
          <el-table-column
            prop="op_name"
            label="操作人"
            align="center"
          />
        </el-table>
        <el-row class="mb-20" />
      </el-form>
    </div>
    <div id="app">
      <div
        v-if="centerDialogVisible"
        class="imgMask"
        @click.stop="centerDialogVisible=!centerDialogVisible"
      >
        <i class="iconfont el-icon-arrow-left prev" @click.stop="prev" />
        <div class="showImg">
          <img class="bigImg" :src="data.auth_imgs[num].img_full">
        </div>
        <i class="iconfont el-icon-arrow-right next" @click.stop="next" />
      </div>
    </div>
  </div>
</template>

<script>
import smallReportDetail from '@/mixins/memberReport'
export default {
  name: 'Detail',
  filters: {
    filtersTime: function(time) {
      const date = new Date(time * 1000)// 把定义的时间赋值进来进行下面的转换
      const year = date.getFullYear()
      const month = date.getMonth() + 1
      const day = date.getDate()
      const hour = date.getHours() < 10 ? '0' + date.getHours() : date.getHours()
      const minute = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()
      const second = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds()
      return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second
    }
  },
  mixins: [smallReportDetail],
  data() {
    return {
      report_id: '',
      report_type: '',
      type: '',
      data: {
        order_id: '',
        cooperation_type: '',
        company_name: '',
        cooperation_type_name: '',
        back_ratio: '',
        viptype: '',
        city_name: '',
        payment_type_name: '',
        payment_money: '',
        round_order_money: '',
        deposit_money: '',
        deposit_to_round_money: '', // 保证金抵扣轮单费
        payee_list: '',
        payment_date_show: '',
        payment_uname: '',
        remark: '',
        auth_imgs: [],
        person_list: [],
        creator_name: '',
        qiandan_jine: '',
        order_qiandan_jine: '',
        order_back_money: '',
        uback_money_compare: '', // 会员返点小报备金额对比（1：一致；2：不一致）
        is_related: 0,
        exam_status: '',
        exam_status_name: '',
        exam_reason: '',
        payment_total_money: '',
        other_money: '',
        other_money_remark: '',
        check_log: [],
        platform_money: '',
        platform_money_start_date: '',
        platform_money_end_date: '',
        refund_money: '', // 退款金额
        deposit_to_platform_money: '' // 保证金抵扣平台使用费金额
      },
      company: {},
      relationId: '', // 已报备会员ID
      relationType: '', // 已报备会员类型
      centerDialogVisible: false,
      num: 0,
      loading: false
    }
  },
  created() {
    if (this.$route.query.id) {
      this.report_id = this.$route.query.id
    }
    if (this.$route.query.type) {
      this.report_type = this.$route.query.type
      if (this.report_type === '1' || this.report_type === '2' || this.report_type === '3' || this.report_type === '7') {
        this.type = 1
      } else {
        this.type = Number(this.report_type)
      }
    }
    this.getSmallReportDetail()
  },
  methods: {
    prev() {
      const imgNum = this.data.auth_imgs.length
      if (this.num === 0) {
        this.num = imgNum
      }
      this.num--
    },
    next() {
      const imgNum = this.data.auth_imgs.length
      if (this.num === imgNum - 1) {
        this.num = -1
      }
      this.num++
    },
    getSmallReportDetail() {
      const queryObj = {
        id: this.report_id
      }
      this.smallReportDetail(queryObj, this.setData)
      this.loading = true
    },
    setData(data) {
      this.loading = false
      const ret = data.detail
      for (var key in this.data) {
        this.data[key] = ret[key]
      }
      this.company = data.company_account
      this.relationId = ret.report_id
      this.relationType = ret.report_cooperation_type
    },
    handleCheck() {
      const { href } = this.$router.resolve({
        name: 'reportDetail',
        path: '/memberReport/detail',
        query: {
          id: this.relationId,
          type: this.relationType
        }
      })
      window.open(href, '_blank')
    },
    preview(index) {
      this.num = index
      this.centerDialogVisible = true
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.smallreport-detail {
  .el-form{
    .el-form-item {
      margin-bottom: 15px;
      .el-form-item__label,
      .el-form-item__content{
        font-size: 15px;
      }
      .qd-jine{
        color: #606266;
        font-weight: 700;
        margin-left: 160px;
      }
      .w600{
        width: 600px;
      }
      .ml50{
        margin-left: 50px;
      }
    }
    .money-item{
      margin-bottom: 15px;
      font-size: 15px;
      line-height: 28px;
      .title{
        color: #606266;
        font-weight: 700;
      }
      .num{
        display: inline-block;
        width: 245px;
      }
    }
  }
  .main {
    width: 100%;
    border-top: none !important;
  }
  .mb-20 {
    margin-bottom: 20px;
  }
  .lh-40 {
    line-height: 40px;
  }
  .upimg {
    display: inline-block;
    width: 100px;
    height: 100px;
    margin-right: 10px;
  }
  .person-list {
    margin-right: 10px;
  }
  .imgMask {
    position: fixed;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    z-index: 99999;
    background: rgba(0, 0, 0, 0.6);
  }
  .showImg {
    height: 550px;
    width: 800px;
    position: absolute;
    left: 50%;
    top: 50%;
    overflow: hidden;
    transform: translate(-50%, -50%);
  }
  .bigImg {
    height: 100%;
    display: block;
    margin: 0 auto;
  }
  .prev {
    position: absolute;
    top: 50%;
    left: 50px;
    font-size: 46px;
    cursor: pointer;
    color: #fff;
    transform: translate(10px, -50%);
  }
  .next {
    font-size: 46px;
    cursor: pointer;
    transform: translate(10px, -50%);
    position: absolute;
    top: 50%;
    color: #fff;
    right: 50px;
  }
  .green{
    color: green;
  }
  .red{
    color: red;
  }
  .blue{
    color: #0099ff;
  }
}
</style>
