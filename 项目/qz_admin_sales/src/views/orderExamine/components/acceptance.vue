<template>
  <div class="acceptance-dialog">
    <el-row>
      <el-col :span="12">
        <ul>
          <li><span class="title">发布时间：</span>{{ info.time_real | date }}</li>
          <li><span class="title">手机号码：</span>{{ info.tel }}</li>
          <li><span class="title">联系人：</span>{{ info.name }}</li>
          <li><span class="title">性别：</span>{{ info.sex }}</li>
          <li><span class="title">备用联系：</span>{{ info.other_contact }}</li>
          <li><span class="title">所属区域：</span>{{ info.city_name }}{{ info.area_name }}</li>
          <li><span class="title">小区名称：</span>{{ info.xiaoqu }}</li>
          <li><span class="title">地图坐标：</span>{{ info.jingwei }}</li>
          <li><span class="title">户型结构：</span>{{ info.huxing_name }}</li>
          <li><span class="title">面积：</span>{{ info.mianji }}</li>
          <li><span class="title">装修风格：</span>{{ info.fengge_name }}</li>
          <li><span class="title">装修类型：</span>{{ info.lx_name }}</li>
          <li><span class="title">装修预算：</span>{{ info.yusuan_name }}</li>
          <li><span class="title vip">分配会员：</span><span v-for="item in info.companys" :key="item.company_id" class="item"> {{ item.jc }}</span></li>
        </ul>
      </el-col>
      <el-col :span="12">
        <ul>
          <li><span class="title">申请会员：</span> <span style="color:#5cb85c">{{ info.qdcompanys?info.qdcompanys.jc:'' }}</span> <span v-if="isQf" style="color:#c00"> &nbsp; 返</span></li>
          <li><span class="title">签单金额：</span>{{ info.qiandan_jine }}万</li>
          <li>
            <span class="title vip">申请备注：</span>
            <el-input
              v-model="info.qiandan_info"
              type="textarea"
              :rows="2"
            />
          </li>
          <li>
            <span class="title vip">特殊需求：</span>
            <el-input
              v-model="info.text"
              type="textarea"
              :rows="4"
            />
          </li>
          <li>
            <span class="title">审核状态：</span>
            <el-radio-group v-model="info.qiandan_status">
              <el-radio :label="0">不通过</el-radio>
              <el-radio :label="1">通过</el-radio>
              <el-radio :label="2">继续跟踪</el-radio>
            </el-radio-group>
            <el-input
              v-if="info.qiandan_status === 0"
              v-model="info.reason"
              type="textarea"
              :rows="3"
              placeholder="请输入不通过原因"
              class="mt10"
            />
          </li>
          <li v-if="info.qiandan_chktime"><span class="title w130">签单最后审核时间：</span>{{ info.qiandan_chktime | date }}</li>
          <li class="tr">
            <el-button type="primary" @click="saveAudit">保存审核</el-button>
          </li>
        </ul>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { fetchDetail, FetchOrdersQdup } from '@/api/orderExamine'
export default {
  name: 'Acceptance',
  filters: {
    date(time) {
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
  props: {
    orderId: {
      type: String,
      default: ''
    },
    isQf: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      loading: false,
      info: {}
    }
  },
  watch: {
    orderId(val) {
      this.getList(val)
    }
  },
  created() {
    const orderId = this.orderId
    this.getList(orderId)
  },
  methods: {
    getList(id) {
      const that = this
      fetchDetail({ order_id: id }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          that.info = res.data.data.info
          if (res.data.data.info.qiandan_status === 0) {
            that.info.qiandan_status = ''
          }
        } else {
          that.tableData = []
          that.totals = 0
        }
      })
    },
    saveAudit() {
      if (this.info.qiandan_status !== 0 && this.info.qiandan_status !== 1 && this.info.qiandan_status !== 2) {
        this.$message.warning('请选择审核状态')
        return false
      }
      const obj = {}
      obj.id = this.info.id
      obj.xiaoqu = this.info.xiaoqu
      obj.cname = this.info.city_name
      obj.qz_area = this.info.area_name
      obj.jine = this.info.qiandan_jine
      obj.company = this.info.qdcompanys.jc
      obj.qiandan_status = this.info.qiandan_status
      obj.qiandan_addtime = this.info.qiandan_addtime
      if (this.info.reason && this.info.qiandan_status === 0) {
        obj.reason = this.info.reason
      }
      FetchOrdersQdup(obj).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.$message({
            type: 'success',
            message: '保存成功!'
          })
          this.$parent.handleClose()
        } else {
          this.$message({
            type: 'error',
            message: res.data.error_msg
          })
        }
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.acceptance-dialog{
  background: #fff;
  .title{
    display: inline-block;
    width: 70px;
    margin-right: 5px;
    text-align: center;;
  }
  ul{
    list-style:none;
    padding: 0;
    margin: 0;
    li{
      list-style-type:none;
      padding: 0;
      margin: 14px 0;
      .vip{
        display: block;
        margin-bottom: 5px;
      }
      .w130{
        width: 130px;
      }
      .mt10{
        margin-top: 10px;
      }
      .item{
        margin-right: 10px;
      }
    }
    .tr{
      margin-top: 30px;
      text-align: right;
    }
  }

}
</style>
