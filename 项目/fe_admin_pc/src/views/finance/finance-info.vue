<template>
  <div class="order-info">
    <editPage title="订单详情">
      <template slot="btn">
        <el-button type="primary" @click="editOrder">修改收货人信息</el-button>
        <el-button @click="closeOrder">关闭订单</el-button>
      </template>
      <div class="w1200">
        <el-steps class="steps" :active="2" align-center>
          <el-step title="提交订单" description="2020-12-23 12:59:00" icon="el-icon-circle-check" />
          <el-step title="支付订单" description="2020-12-23 12:59:00" icon="el-icon-circle-check" />
          <el-step title="平台发货" description="2020-12-23 12:59:00" icon="el-icon-circle-check" />
          <el-step title="完成" description="2020-12-23 12:59:00" icon="el-icon-circle-check" />
        </el-steps>
        <div class="status"> 当前订单状态：待付款 </div>
      </div>
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
    <el-dialog title="修改收货人信息" :visible.sync="dialogVisible">
      <el-form ref="form" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="收货人姓名" prop="name">
          <el-input v-model="form.name" clearable width="200px" />
        </el-form-item>
        <el-form-item label="手机号码" prop="name">
          <el-input v-model="form.name" clearable />
        </el-form-item>
        <el-form-item label="所在区域" prop="region">
          <el-select v-model="form.region" placeholder="省" clearable class="mr5">
            <el-option label="区域一" value="shanghai" />
            <el-option label="区域二" value="beijing" />
          </el-select>
          <el-select v-model="form.region" placeholder="市" clearable class="mr5">
            <el-option label="区域一" value="shanghai" />
            <el-option label="区域二" value="beijing" />
          </el-select>
          <el-select v-model="form.region" placeholder="区" clearable class="mr5">
            <el-option label="区域一" value="shanghai" />
            <el-option label="区域二" value="beijing" />
          </el-select>
        </el-form-item>
        <el-form-item label="详细地址">
          <el-input v-model="form.desc" type="textarea" :autosize="{ minRows: 2, maxRows: 4}" clearable />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
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
    return {
      id: '',
      fromData: {
        goods_name: '',
        goods_no: '',
        goods_status: '',
        type_id: ''
      },
      date_begin: '',
      date_end: '',
      tableLoading: false,
      tableData: [],
      dialogVisible: false,
      helderColor: {
        background: '#64b4c8',
        color: '#333'
      },
      form: {
        name: '',
        region: ''
      },
      rules: {
        name: [
          { required: true, message: '请输入活动名称', trigger: 'blur' }
        ],
        region: [
          { required: true, message: '请选择活动区域', trigger: 'change' }
        ],
        desc: [
          { required: true, message: '请输入活动名称', trigger: 'blur' }
        ]
      }
    }
  },
  created() {
    this.id = this.$route.query.id
    this.settleType = this.$route.query.type
    this.getInfo()
  },
  mounted() {},
  methods: {
    onSearch() {
      this.getList()
    },
    onExport() {
      console.log('导出')
    },
    getInfo() {
      const obj = {
        id: this.id,
        settleType: this.settleType
      }
      this.$apis.FINANCE.financeDetail(obj).then(res => {
        if (res.code === 0) {
          this.data = res.data || []
        }
      })
    },
    closeOrder() {
      console.log(1)
    },
    editOrder() {
      this.dialogVisible = true
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
  .steps{
    padding: 50px 0;
    background-color: #f8f8f8;
    ::v-deep .el-step__head.is-finish{
      color: #67C23A;
      border-color: #67C23A;
    }
    ::v-deep .el-step__icon-inner{
      font-size: 40px;
    }
  }
  .status{
    padding-left: 30px;
    line-height: 60px;
    background-color: #ccc;
    color: red;
    margin-bottom: 20px;
  }
}
</style>
