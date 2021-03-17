<template>
  <div v-loading="loading">
    <el-card>
      <div class="col333 font13">
        <div class="font18">结算中心</div>
        <div class="mt20"><span v-if="leaveTime !== '-1' && leaveTime !== '-2'">订单提交成功，请尽快完成！</span>订单号：{{ orderNo }}</div>
        <div class="flex spa-bet ali-ite">
          <div />
          <div v-if="leaveTime !== '-2'" class="mr20 flex spa-bet ali-ite">应付金额<div class="font20 colF29126 ml10">{{ orderPrice }}</div>元</div>
        </div>
        <div v-if="leaveTime === '-1'">订单已失效</div>
        <div v-else-if="leaveTime === '-2'" class="mt20">订单已完成</div>
        <div v-else>请您在 <span class="colFF4A4A">{{ time }}</span> 完成支付，否则订单会被自动取消</div>
      </div>
      <el-card v-if="leaveTime !== '-1' && leaveTime !== '-2'" class="mt30">
        <div class="font16 col333">使用银行卡打款</div>
        <div class="font13 col878E93 mt20">为保证资金安全，请进行线下打款</div>
        <div class="flex spa-bet ali-ite bank-card-msg mt20">
          <div class="flex spa-bet ali-ite font15">
            <div class="mr20">公司名称：{{ holderName }}</div>
            <div class="mr20">开户行：{{ bankName }}</div>
            <div>账号：{{ cardNo }}</div>
          </div>
          <div class="col3B7CFF font14 point" @click="copyCardNo(cardNo)">复制</div>
        </div>
        <div class="font16 col333 mt20">请上传打款凭证</div>
        <div class="mt20">
          <el-upload
            class="upload"
            :action="action"
            :limit="3"
            :on-exceed="handleExceed"
            :on-success="handleUploadSuccess"
            :data="uploadExtendData"
            :before-upload="beforeAction"
            :on-preview="handlePreview"
            :on-remove="handleRemove"
            :file-list="fileList"
            list-type="picture-card"
          >
            <el-button size="small" type="primary">点击上传</el-button>
            <div slot="tip" class="el-upload__tip">请上传打款凭证，最多上传3</div>
          </el-upload>
          <el-dialog :visible.sync="dialogImg">
            <img width="100%" :src="dialogImageUrl" alt="">
          </el-dialog>
        </div>
      </el-card>
      <!-- <el-card v-if="leaveTime !== '-1'" class="mt10">
        <div class="font16 col333">备注</div>
        <div>
          <el-input
            v-model="remark"
            class="mt20"
            type="textarea"
            :autosize="{ minRows: 6, maxRows: 6}"
            placeholder="请填写备注"
          />
        </div>
      </el-card> -->
      <div class="mt20">
        <el-button v-if="leaveTime !== '-1' && leaveTime !== '-2'" type="warning" @click="pay">确定支付</el-button>
        <el-button v-if="leaveTime === '-1' || leaveTime === '-2'" type="primary" @click="goMall">前往商城</el-button>
        <el-button v-if="leaveTime === '-2'" type="primary" @click="goOrder">查看订单</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
export default {
  name: 'CashRegister',
  data() {
    return {
      loading: true,
      action: this.$config.sevenCattleUrl,
      dialogImageUrl: '',
      dialogImg: false,
      fileList: [],
      uploadExtendData: {
        key: '',
        token: ''
      },
      imgList: [],
      disabled: false,
      remark: '',
      bankName: '',
      cardNo: '',
      holderName: '',
      orderId: '',
      orderNo: '',
      orderPrice: '',
      payPrice: '',
      leaveTime: '-1',
      payType: 2,
      paymentVoucher: '',
      time: '',
      timer: ''
    }
  },
  watch: {
    leaveTime(val) {
      if (val !== '-1' && val !== '-2' && val) {
        const days = parseInt(val / (1000 * 60 * 60 * 24))
        const hours = parseInt((val % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = parseInt((val % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = parseInt((val % (1000 * 60)) / 1000)
        this.time = `${days > 0 ? days + '天' : ''} ${hours > 9 ? hours : '0' + hours} : ${minutes > 9 ? minutes : '0' + minutes} ： ${seconds > 9 ? seconds : '0' + seconds}`
      }
    }
  },
  created() {
    this.getOrderSettle()
    this.getSevenCattleToken()
  },
  beforeDestroy() {
    clearInterval(this.timer)
  },
  methods: {
    getOrderSettle() {
      this.loading = true
      this.$apis.ORDER.orderSettle().then(res => {
        this.loading = false
        if (res.code === 0) {
          this.bankName = res.data.bankName
          this.cardNo = res.data.cardNo
          this.holderName = res.data.holderName
          this.leaveTime = res.data.leaveTime || '-1'
          this.orderNo = res.data.orderNo
          this.orderId = res.data.id
          this.orderPrice = res.data.orderPrice
          if (this.leaveTime === '-1') {
            this.$confirm('订单已失效，请重新下单', '提示', {
              type: 'warning'
            }).then(() => {
              this.$router.push(`/distribution-mall/selection-mall`)
            }).catch(() => {})
          } else {
            this.timer = setInterval(this.dataTime, 1000)
          }
        }
      })
    },
    dataTime() {
      if (this.leaveTime > 1000) {
        this.leaveTime -= 1000
      } else if (this.leaveTime !== '-2') {
        this.leaveTime = '-1'
        clearInterval(this.timer)
        this.$confirm('订单已失效，请重新下单', '提示', {
          type: 'warning'
        }).then(() => {
          this.$router.push(`/distribution-mall/selection-mall`)
        }).catch(() => {})
      }
    },
    copyCardNo(value) {
      const oInput = document.createElement('input') // 创建一个 Input标签
      oInput.value = value
      document.body.appendChild(oInput)
      oInput.select() // 选择对象;
      // 执行浏览器复制命令
      // 复制命令会将当前选中的内容复制到剪切板中
      // 如这里构建的 Input标签
      document.execCommand('Copy')
      this.$message.message('复制成功')
      // 复制成功后再将构造的标签 移除
      oInput.remove()
    },
    pay() {
      if (this.imgList.length > 0) {
        const obj = {
          orderId: this.orderId,
          payPrice: this.orderPrice,
          payType: this.payType,
          remark: this.remark,
          paymentVoucher: this.imgList.join(',')
        }
        console.log(obj)
        this.$apis.ORDER.orderPay(obj).then(res => {
          if (res.code === 0) {
            this.leaveTime = '-2'
            this.$message.success(res.message)
          } else {
            this.$message.error(res.message)
          }
        })
      } else {
        this.$message.warning('请上传打款凭证')
      }
    },
    goMall() {
      this.$router.push(`/distribution-mall/selection-mall`)
    },
    goOrder() {
      this.$router.push(`/order-management/user-order`)
    },
    handleExceed() {
      this.$message.warning('最多上传3张')
    },
    upImageKey() {
      const date = new Date().toLocaleDateString()
      const time = new Date().getTime()
      const math = Math.ceil(Math.random() * 100000)
      return `img/${date}/${time}${math}.jpg`
    },
    beforeAction(file) {
      const isImg = file.type === 'image/jpeg' || file.type === 'image/png'
      if (!isImg) {
        this.$message.error('上传图片只能是JPG或PNG 格式!')
        return isImg
      }
      const key = this.upImageKey()
      this.uploadExtendData.key = key
    },
    getSevenCattleToken() {
      const params = {}
      this.$apis.COMMON.getSevenCattleToken(params).then(res => {
        if (res.code === 0) {
          this.uploadExtendData.token = res.data
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    handleUploadSuccess(res, file, fileList) {
      const imgUrl = this.$config.imgServerDomain + res.key
      this.fileList = fileList
      this.imgList.push(imgUrl)
    },
    handleRemove(file, fileList) {
      if (file.response) {
        this.imgList.forEach((item, index) => {
          if (item.indexOf(file.response.key) !== -1) {
            this.fileList.splice(index, 1)
            this.imgList.splice(index, 1)
          }
        })
      } else {
        this.imgList.forEach((item, index) => {
          if (item.url.indexOf(file.url) !== -1) {
            this.fileList.splice(index, 1)
            this.imgList.splice(index, 1)
          }
        })
      }
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url
      this.dialogImg = true
    }
  }
}
</script>

<style lang="scss" scoped>
.bank-card-msg{
  height: 70px;
  background-color: #F0F3F7;
  padding: 0 20px;
  border-radius: 5px;
}
</style>
