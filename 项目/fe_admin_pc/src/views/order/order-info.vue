<template>
  <div class="order-info">
    <editPage v-loading="tableLoading" title="订单详情">
      <template v-if="orderStatus === 1 || orderStatus === 2" slot="btn">
        <el-button type="primary" @click="editOrder">修改收货人信息</el-button>
        <el-button @click="closeOrder">关闭订单</el-button>
      </template>
      <titleTable class="mb20 w1200" title="收货人信息" icon-name="el-icon-s-comment">
        <el-table :data="tableData" border :header-cell-style="helderColor">
          <el-table-column prop="receiverName" label="收货人" align="center" width="200" />
          <el-table-column prop="receiverPhone" label="手机号码" align="center" width="200" />
          <el-table-column prop="receiveAreaDetails" label="收货地址" align="center" />
        </el-table>
      </titleTable>
      <titleTable class="mb20 w1200" title="基本信息" icon-name="el-icon-s-order">
        <el-table :data="tableData" border :header-cell-style="helderColor">
          <el-table-column prop="orderNo" label="订单编号" align="center" />
          <el-table-column prop="payFlowNo" label="交易流水号" align="center" />
          <el-table-column prop="custName" label="用户账号" align="center" />
          <el-table-column prop="payType" label="支付方式" align="center" />
          <el-table-column prop="createDate" label="创建时间" align="center" />
          <el-table-column prop="sellerId" label="卖家ID" align="center" />
          <el-table-column prop="shopName" label="卖家名称" align="center" />
        </el-table>
      </titleTable>
      <titleTable class="mb20 w1200" title="商品信息" icon-name="el-icon-s-shop">
        <el-table :data="orderDetailVOList" border :header-cell-style="helderColor">
          <el-table-column label="商品图片" align="center">
            <template slot-scope="scope">
              <el-image
                style="width: 100px; height: 100px"
                :src="scope.row.goodsDetail.goodsCover"
                :preview-src-list="[scope.row.goodsDetail.goodsCover]"
              />
            </template>
          </el-table-column>
          <el-table-column label="商品名称" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.goodsName }}
            </template>
          </el-table-column>
          <el-table-column prop="salePrice" label="单价（单位：元）" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.salePrice }}
            </template>
          </el-table-column>
          <el-table-column label="属性" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.goodsSkuName }}
            </template>
          </el-table-column>
          <el-table-column prop="goodsNumber" label="数量" align="center" />
          <el-table-column prop="purchasePrice" label="齐装采购价" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.purchasePrice }}
            </template>
          </el-table-column>
          <el-table-column prop="supplyPrice" label="齐装供销价" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.supplyPrice }}
            </template>
          </el-table-column>
          <el-table-column prop="supplyPrice" label="装企销售价" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.salePrice }}
            </template>
          </el-table-column>
          <el-table-column label="小计" align="center">
            <template slot-scope="scope">
              {{ scope.row.goodsDetail.realPrice }}
            </template>
          </el-table-column>
        </el-table>
        <h5 class="goodsTotal">合计：<span class="num">{{ orderPrice }}</span></h5>
      </titleTable>
      <titleTable class="mb20 w1200" title="费用信息" icon-name="el-icon-s-flag">
        <el-table :data="tableData" border :header-cell-style="helderColor">
          <el-table-column prop="purchaseTotalPrice" label="齐装采购总价（百度底价）" align="center" />
          <el-table-column prop="supplyTotalPrice" label="齐装供销总价（齐装底价）" align="center" />
          <el-table-column prop="saleTotalPrice" label="装企销售总价" align="center" />
          <el-table-column prop="orderPrice" label="实际收款" align="center" />
          <el-table-column prop="settleTotalPrice" label="百度结算总额" align="center" />
          <el-table-column prop="companySettleTotalPrice" label="装企结算总额" align="center" />
        </el-table>
      </titleTable>
    </editPage>
    <el-dialog v-loading="dialogLoading" title="修改收货人信息" :visible.sync="dialogVisible" width="780px" :close-on-click-modal="false">
      <el-form ref="form" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="收货人姓名" prop="receiverName">
          <el-input v-model="form.receiverName" width="200px" maxlength="10" />
        </el-form-item>
        <el-form-item label="手机号码" prop="receiverPhone">
          <el-input v-model="form.receiverPhone" maxlength="11" />
        </el-form-item>
        <el-form-item label="所在区域" prop="address">
          <el-select v-model="receivePrinceCode" placeholder="省" class="mr5" @change="addressChange(3,receivePrinceCode)">
            <el-option v-for="item in provinceList" :key="item.id" :label="item.name" :value="item.code" />
          </el-select>
          <el-select v-model="receiveCityCode" placeholder="市" class="mr5" @change="addressChange(4,receiveCityCode)">
            <el-option v-for="item in cityList" :key="item.id" :label="item.name" :value="item.code" />
          </el-select>
          <el-select v-model="receiveProperCode" placeholder="区" @change="areaChange">
            <el-option v-for="item in areaList" :key="item.id" :label="item.name" :value="item.code" />
          </el-select>
        </el-form-item>
        <el-form-item label="详细地址" prop="receiverArea">
          <el-input v-model="form.receiverArea" type="textarea" :autosize="{ minRows: 2, maxRows: 4}" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="dialogOk">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import titleTable from '@/components/TitleTable/index'
export default {
  name: 'OrderInfo',
  components: {
    titleTable
  },
  data() {
    var validateAddress = (rule, value, callback) => {
      const receivePrinceCode = this.receivePrinceCode
      const receiveCityCode = this.receiveCityCode
      const receiveProperCode = this.receiveProperCode
      if (receivePrinceCode === '') {
        callback(new Error('请选择省'))
      } else if (receiveCityCode === '') {
        callback(new Error('请选择市'))
      } else if (receiveProperCode === '') {
        callback(new Error('请选择区'))
      } else {
        callback()
      }
    }
    return {
      provinceList: [],
      cityList: [],
      areaList: [],
      receivePrinceCode: '',
      receiveCityCode: '',
      receiveProperCode: '',
      receivePrince: '',
      receiveCity: '',
      receiveProper: '',
      form: {
        receiverName: '',
        receiverPhone: '',
        receiverArea: ''
      },
      tableLoading: false,
      tableData: [],
      orderDetailVOList: [],
      orderPrice: '',
      orderStatus: '',
      dialogVisible: false,
      dialogLoading: false,
      helderColor: {
        background: '#64b4c8',
        color: '#333'
      },
      rules: {
        receiverName: [
          { required: true, message: '请输入名称', trigger: 'blur' }
        ],
        receiverPhone: [
          { required: true, message: '请输入手机号', trigger: 'blur' },
          { pattern: /^1[3-9]\d{9}$/, message: '手机号格式不对', trigger: 'blur' }
        ],
        receiverArea: [
          { required: true, message: '请输入详细地址', trigger: 'blur' }

        ],
        address: [
          { validator: validateAddress, trigger: 'blur' }
        ]
      }
    }
  },
  created() {
    this.id = this.$route.query.id
    this.getInfo()
    this.getByParentCode(2)
  },
  mounted() {},
  methods: {
    getInfo() {
      this.tableLoading = true
      this.$apis.ORDER.orderDetail({ id: this.id }).then(res => {
        this.tableLoading = false
        if (res.code === 0) {
          this.tableData = [res.data]
          this.orderDetailVOList = res.data.orderDetailVOList
          this.orderStatus = Number(res.data.orderStatus)
          this.orderPrice = res.data.orderPrice
        } else {
          this.$message.error(res.message)
        }
      }).catch(() => {
        this.tableLoading = false
      })
    },
    closeOrder() {
      this.$confirm('确定要关闭订单?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.ORDER.orderClose({ id: this.id }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.$router.push('/order/order-manage')
          }
        })
      }).catch(() => {
        return false
      })
    },
    editOrder() {
      this.dialogVisible = true
      this.dialogLoading = true
      this.$apis.ORDER.getReceive({ id: this.id }).then(res => {
        this.dialogLoading = false
        if (res.code === 0) {
          const data = res.data
          this.form.receiverName = data.receiverName
          this.form.receiverPhone = data.receiverPhone
          this.form.receiverArea = data.receiveAreaDetails
          this.receivePrinceCode = data.receivePrinceCode
          this.receiveCityCode = data.receiveCityCode
          this.receiveProperCode = data.receiveProperCode
          this.receivePrince = data.receivePrince
          this.receiveCity = data.receiveCity
          this.receiveProper = data.receiveProper
          this.getByParentCode(3, this.receivePrinceCode)
          this.getByParentCode(4, this.receiveCityCode)
        } else {
          this.$message.error(res.message)
        }
      })
    },
    dialogOk() {
      this.$refs['form'].validate((valid) => {
        if (valid) {
          this.getupdateReceive()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    getupdateReceive() {
      const obj = {
        id: this.id,
        receiverName: this.form.receiverName,
        receiverPhone: this.form.receiverPhone,
        receiverArea: this.form.receiverArea,
        receivePrinceCode: this.receivePrinceCode,
        receiveCityCode: this.receiveCityCode,
        receiveProperCode: this.receiveProperCode,
        receivePrince: this.receivePrince,
        receiveCity: this.receiveCity,
        receiveProper: this.receiveProper
      }
      this.$apis.ORDER.orderUpdateReceive(obj).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.dialogVisible = false
          this.getInfo()
        } else {
          this.$message.error(res.message)
        }
      })
    },
    addressChange(level, pcode) {
      this.getByParentCode(level, pcode)
      this.receiveProperCode = ''
      if (level === 3) {
        this.receiveCityCode = ''
        this.areaList = []
        this.receivePrince = this.provinceList.find(item => item.code === pcode).name
      } else {
        this.receiveCity = this.cityList.find(item => item.code === pcode).name
      }
    },
    areaChange() {
      this.receiveProper = this.areaList.find(item => item.code === this.receiveProperCode).name
      this.$forceUpdate()
    },
    getByParentCode(level, pcode) {
      const obj = {
        level,
        pcode: pcode || ''
      }
      this.$apis.COMMON.getByParentCode(obj).then(res => {
        if (res.code === 0) {
          if (level === 2) {
            this.provinceList = res.data
          } else if (level === 3) {
            this.cityList = res.data
          } else {
            this.areaList = res.data
          }
        } else {
          this.$message.error(res.message)
        }
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.order-info {
  padding: 20px;
  .el-input{
    width: 300px;
  }
  .el-textarea{
    width: 500px;
  }
  .goodsTotal{
    font-weight: normal;
    text-align: right;
    line-height: 30px;
    margin: 20px 0;
    background-color: #64b4c8;
    padding-right: 20px;
    .num{
      color: #c00;
    }
  }
}
</style>
