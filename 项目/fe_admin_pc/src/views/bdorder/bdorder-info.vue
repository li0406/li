<template>
  <div class="bdorder-info">
    <editPage title="百度接单后台详情">
      <el-card class="mb20">
        <h4 class="mb20">收件人信息</h4>
        <p>收件人：王小二</p>
        <p>收件人手机号：18666669999</p>
        <p>收件地址：收件地址收件地址收件地址</p>
      </el-card>
      <el-card class="mb20">
        <h4 :class="!isDelivery ? '' : 'mb20'">
          <span>订单状态：{{ isDelivery ? '已发货' : '未发货' }}</span>
          <el-button v-if="!isDelivery" size="small" type="primary" class="ml40">发货</el-button>
          <el-button v-else class="fr" @click="dialogVisible = true">修改物流信息</el-button>
        </h4>
        <template v-if="isDelivery">
          <p>物流公司：顺丰快递</p>
          <p>物流单号：123213414</p>
          <p>发货时间：2020.12.12  15：33：33</p>
        </template>
      </el-card>
      <el-card class="mb20">
        <h4>
          <span>打款凭证</span>
          <el-button size="small" type="primary" class="ml100">点击查看</el-button>
        </h4>
      </el-card>
      <el-card class="mb20">
        <h4 class="mb20">订单商品</h4>
        <el-table :data="tableData" border style="width: 100%">
          <el-table-column prop="orderNo" label="商品编号" align="center" />
          <el-table-column prop="custName" label="商品名称" align="center" />
          <el-table-column prop="goodsName" label="商品封面" align="center" />
          <el-table-column prop="type_name" label="商品SKU" align="center" />
          <el-table-column prop="saleNum" label="商品SKU" align="center" />
          <el-table-column prop="saleNum" label="商品数量" align="center" />
        </el-table>
      </el-card>
    </editPage>
    <el-dialog title="填写物流信息" :visible.sync="dialogVisible" width="600px">
      <el-form ref="form" :model="form" label-width="100px">
        <el-form-item label="物流公司" prop="name">
          <el-input v-model="form.name" clearable />
        </el-form-item>
        <el-form-item label="物流单号" prop="name">
          <el-input v-model="form.name" clearable />
          <el-button type="text" class="ml10" icon="el-icon-delete" />
        </el-form-item>
        <el-form-item>
          <el-button class="m0a" type="success" size="mini" icon="el-icon-plus">更多物流单号</el-button>
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
export default {
  name: 'BdorderInfo',
  data() {
    return {
      isDelivery: false,
      currentPage: 1,
      pageSize: 20,
      total: 0,
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
  computed: {},
  watch: {
    'fromData.date': {
      handler(val) {
        console.log(val)
        this.date_begin = val[0] || ''
        this.date_end = val[1] || ''
      }
    }
  },
  created() {
    this.getList()
  },
  mounted() {},
  methods: {
    onSearch() {
      this.currentPage = 1
      this.getList()
    },
    onExport() {
      console.log('导出')
    },
    handleData(val) {
      const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize
      }
      return obj
    },
    getList() {
      /* const obj = this.handleData()
      this.$apis.FINANCE.financeList(obj).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.pageNo = res.page.pageNo
          this.convertTableData()
        }
      }) */
    },
    arraySpanMethod({ row, column, rowIndex, columnIndex }) {
      if ([0, 1, 8, 9, 10, 11].includes(columnIndex)) {
        const _row = this.rowspan[rowIndex]
        const _col = _row > 0 ? 1 : 0 // 如果这一行隐藏了，这列也隐藏
        return {
          rowspan: _row,
          colspan: _col
        }
      }
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    orderInfoTo() {
      this.$router.push('/wares/goods-addedit')
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
.bdorder-info {
  padding: 20px;
  h4{
    font-size: 16px;
    line-height: 20px;
  }
  p{
    font-size: 14px;
    line-height: 30px;
  }
  .bottom-btn{
   text-align: center;
  }
  .ml100{
    margin-left: 100px;
  }
  .el-input{
    width: 300px;
  }
}
</style>
