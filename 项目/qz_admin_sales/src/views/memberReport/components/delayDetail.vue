<template>
  <div v-loading="loading" class="delay-detail">
    <div class="main">
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>合作类型：
          </div>
        </el-col>
        <el-col :span="18">{{ info.cooperation_type_name }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>网店代码：
          </div>
        </el-col>
        <el-col :span="18">{{ info.company_id }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">公司名称：</div>
        </el-col>
        <el-col :span="18">{{ info.company_name }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">会员类型：</div>
        </el-col>
        <el-col :span="18">{{ info.company_mode_name }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>本次会员时间：
          </div>
        </el-col>
        <el-col :span="18">{{ info.current_vip_start_show }} 至 {{ info.current_vip_end_show }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">本次分单量：</div>
        </el-col>
        <el-col :span="18">{{ info.current_fen_orders }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>本次报备合同：
          </div>
        </el-col>
        <el-col :span="18" :class="info.contract_date_compare === 1 ? 'green' : 'red'">{{ info.current_contract_start_show }} 至 {{ info.current_contract_end_show }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">承诺订单量：</div>
        </el-col>
        <el-col :span="4">{{ info.promise_orders }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>延期承诺单量：
          </div>
        </el-col>
        <el-col :span="3">{{ info.delay_promise_orders }}</el-col>
        <el-col :span="15" class="red">承诺订单量减去本次分单量</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">应延期天数：</div>
        </el-col>
        <el-col :span="3">{{ info.delay_days }} 天</el-col>
        <el-col :span="15" class="red">系统自动计算（本次分单差额/最近三个月每日平均分单数）</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>实际延期天数：
          </div>
        </el-col>
        <el-col :span="3"><span :class="info.delay_real_days === info.delay_sys_days ? 'green' : 'red'">{{ info.delay_real_days }}</span> 天</el-col>
        <el-col :span="15" class="red">应延期天数*70%</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">
            <i class="red">*</i>延期合同日期：
          </div>
        </el-col>
        <el-col :span="18">{{ info.delay_contract_start_show }} 至 {{ info.delay_contract_end_show }}</el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">上传图片：</div>
        </el-col>
        <el-col :span="18">
          <el-col v-for="(item,index) in img_list" :key="item" :span="5">
            <img :src="item" alt class="upimg" @click="preview(index)">
          </el-col>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6"><div class="lh-40">备注：</div></el-col>
        <el-col :span="18">{{ info.remarke }}</el-col>
      </el-row>
      <!--图片预览-->
      <div id="app">
        <div
          v-if="centerDialogVisible"
          class="imgMask"
          @click.stop="centerDialogVisible=!centerDialogVisible"
        >
          <i class="iconfont el-icon-arrow-left prev" @click.stop="prev" />
          <div class="showImg">
            <img class="bigImg" :src="url[num]">
          </div>
          <i class="iconfont el-icon-arrow-right next" @click.stop="next" />
        </div>
      </div>
    </div>
    <logs />
  </div>
</template>

<script>
import memberReportDetail from '@/mixins/memberReport'
import { fortmatDate } from '@/utils/index'
import logs from './logs'
import QZ_CONFIG from '@/utils/config'
export default {
  name: 'DelayDetail',
  components: {
    logs
  },
  mixins: [memberReportDetail],
  data() {
    return {
      loading: false,
      info: {},
      img_list: [],
      kfuploadImg: [],
      url: [],
      centerDialogVisible: false,
      num: 0
    }
  },
  created() {
    this.getMemberReportDetail()
  },
  methods: {
    prev() {
      const imgNum = this.url.length
      if (this.num === 0) {
        this.num = imgNum
      }
      this.num--
    },
    next() {
      const imgNum = this.url.length
      if (this.num === imgNum - 1) {
        this.num = -1
      }
      this.num++
    },
    getMemberReportDetail() {
      this.loading = true
      const queryObj = {
        cooperation_type: this.$route.query.type,
        id: this.$route.query.id
      }
      this.memberReportDetail(queryObj, this.setData)
    },
    setData(data) {
      const ret = data.info
      this.loading = false
      this.info = ret
      for (let i = 0; i < ret.img_list.length; i++) {
        this.img_list[i] = QZ_CONFIG.oss_img_host + ret.img_list[i]
      }
      this.img_list = this.img_list
      for (let i = 0; i < ret.kf_voucher_img.length; i++) {
        this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
      }
      this.kfuploadImg = this.kfuploadImg
      this.companyName = ret.company_name
      this.memberType = ret.cooperation_type_name
      this.department = ret.section
      this.username = ret.company_contact
      this.phone = ret.company_contact_phone
      this.account = ret.account ? ret.account : '----'
      this.money = ret.current_contract_amount
      this.role = ret.company_rolers ? ret.company_rolers : '----'
      this.tags = ret.company_tag ? ret.company_tag : '----'
      this.time =
        fortmatDate(ret.current_contract_start * 1000, '/') +
        ' - ' +
        fortmatDate(ret.current_contract_end * 1000, '/')
      this.note = ret.remarke ? ret.remarke : '----'
    },
    preview(index) {
      this.url = this.img_list
      this.num = index
      this.centerDialogVisible = true
    },
    kfpreview(index) {
      this.url = this.kfuploadImg
      this.num = index
      this.centerDialogVisible = true
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.delay-detail {
  .main {
    width: 800px;
    border-top: none !important;
  }
  .mb-20 {
    margin-bottom: 20px;
  }
  .keftips {
    width: 500px;
    position: absolute;
    left: 30px;
    top: -17px;
  }
  table {
    text-align: center;
  }
  .upimg {
    display: inline-block;
    width: 100px;
    height: 100px;
    margin-right: 10px;
  }
  .green{
    color: green;
  }
  .red{
    color: red;
  }
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
</style>
