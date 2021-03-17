<template>
  <div>
    <el-card>
      <el-form :inline="true">
        <el-form-item label="商品名称">
          <el-input v-model="goodsName" clearable placeholder="商品名称" />
        </el-form-item>
        <el-form-item label="商品ID">
          <el-input v-model="goodsNo" clearable placeholder="商品ID" />
        </el-form-item>
        <el-form-item v-if="type === 1" label="销售状态">
          <el-select v-model="sGoodsStatus" clearable placeholder="请选择">
            <el-option label="已上架" value="1" />
            <el-option label="未上架" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item v-else label="排序">
          <el-select v-model="orderBy" clearable placeholder="排序">
            <!-- <el-option label="按上架时间" value="0" /> -->
            <el-option label="按销量" value="1" />
            <el-option label="按价格" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit">查询</el-button>
        </el-form-item>
        <el-form-item class="fr">
          <el-button v-if="type === 3 || type === 1" type="primary" @click="batchLaunch(1)">批量上架</el-button>
          <el-button v-if="type === 2 || type === 1" @click="batchLaunch(2)">批量下架</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="mt10">
      <el-table
        ref="multipleTable"
        v-loading="tableLoading"
        border
        :data="tableData"
        tooltip-effect="dark"
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column label="商品图片" show-overflow-tooltip align="center">
          <template slot-scope="scope">
            <el-image
              style="width: 100px; height: 100px"
              :src="scope.row.goodsCover"
              :preview-src-list="[scope.row.goodsCover]"
            />
          </template>
        </el-table-column>
        <el-table-column prop="goodsName" label="商品名称" align="center" />
        <el-table-column prop="goodsNo" label="商品ID" align="center" />
        <el-table-column prop="goodsSkuDesc" label="备注" align="center" />
        <el-table-column prop="normalPrice" label="市场价" align="center" />
        <el-table-column prop="supplyPrice" label="供货价" align="center" />
        <el-table-column prop="salePrice" label="销售价" align="center" />
        <el-table-column v-if="type !== 1" prop="saleNum" label="已售" align="center" />
        <el-table-column v-else label="销售状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.sgoodsStatus | goodsStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="address" label="操作" width="150" align="center">
          <template slot-scope="scope">
            <el-button class="col3B7CFF" type="text" @click="viewdetails(scope.row.id)">详情</el-button>
            <el-button v-if="scope.row.sgoodsStatus !== '1'" class="col3B7CFF" type="text" @click="updownrack(scope.row.id, 1)">上架</el-button>
            <el-button v-if="scope.row.sgoodsStatus === '1'" class="col3B7CFF" type="text" @click="updownrack(scope.row.id, 2)">下架</el-button>
            <el-button class="col3B7CFF" type="text" @click="delgoods(scope.row.id)">删除</el-button>
            <el-button class="col3B7CFF" type="text" @click="setsalesprice(scope.row.id)">设置销售价</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        v-if="total"
        class="mt20 text-center"
        :current-page="pageNo"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </el-card>
    <drawer :drawer="drawer" :info="dataInfo" />
  </div>
</template>

<script>
export default {
  name: 'AddressManagement',
  components: {
    drawer: () => import('@/components/drawer')
  },
  filters: {
    goodsStatus(val) {
      switch (val) {
        case '0':
          return '无效'
        case '1':
          return '已上架'
        case '2':
          return '已下架'
      }
    }
  },
  props: {
    type: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      tableLoading: false,
      drawer: false,
      pageNo: 1,
      total: 0,
      pageSize: 20,
      goodsName: '',
      sGoodsStatus: '',
      orderBy: '',
      goodsNo: '',
      tableData: [],
      dataInfo: {},
      multipleSelection: []
    }
  },
  created() {
    if (this.type === 2) {
      this.sGoodsStatus = 1
    } else if (this.type === 3) {
      this.sGoodsStatus = 2
    }
    this.getList()
  },
  methods: {
    updownrack(id, val) {
      this.$confirm(`你确定要${val === 1 ? '上' : '下'}架选中的商品嘛${val === 1 ? '' : '，用户将无法购买'}？`, `${val === 1 ? '上' : '下'}架提示`, {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.goodsupdate({ id, goodsStatus: val })
      }).catch(() => {})
    },
    delgoods(id) {
      this.$confirm('你确定要删除选中的商品吗？', '删除提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.GOODS.delete({ ids: [id] }).then(res => {
          if (res.code === 0) {
            this.$message({
              type: 'success',
              message: '删除成功!'
            })
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        }).catch(() => {
          console.log('error')
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    setsalesprice(id) {
      this.$prompt('销售价', '设置销售价', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputPattern: /^[0-9]+(.[0-9]{1,3})?$/,
        inputErrorMessage: '请输入数字'
      }).then(({ value }) => {
        this.goodsupdate({ id, salePrice: value })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '取消输入'
        })
      })
    },
    viewdetails(id) {
      this.drawer = true
      this.$apis.GOODS.goodsDetail({ id: id }).then(res => {
        console.log(res)
        if (res.code === 0) {
          this.dataInfo = res.data
        }
      })
    },
    getList() {
      const obj = {
        pageNo: this.pageNo,
        pageSize: this.pageSize,
        orderBy: this.orderBy,
        goodsStatus: this.sGoodsStatus,
        goodsName: this.goodsName,
        goodsNo: this.goodsNo
      }
      this.tableLoading = true
      this.$apis.GOODS.list(obj).then(res => {
        this.tableLoading = false
        if (res.code === 0) {
          this.tableData = res.data
          this.pageNo = res.page.pageNo
          this.pageSize = res.page.pageSize
          this.total = res.page.total
        } else {
          this.$message.error(res.message)
        }
      }).catch(() => {
        this.tableLoading = true
      })
    },
    handleSizeChange(val) {
      this.pageNo = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.pageNo = val
      this.getList()
    },
    // 上下架 设置价格
    goodsupdate(obj) {
      console.log(obj)
      this.$apis.GOODS.update(obj).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.getList()
        } else {
          this.$message.error(res.message)
        }
      }).catch(() => {
        console.log('error')
      })
    },
    handleSelectionChange(val) {
      console.log(val)
      this.multipleSelection = val
    },
    batchLaunch(val) {
      if (!this.multipleSelection.length) {
        this.$message.warning('请先选择商品')
        return
      }
      this.$confirm(`你确定要${val === 1 ? '上' : '下'}选中的商品吗？`, '删除提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const updateGoodsStatusList	= this.multipleSelection.map(item => {
          return item.id
        })
        const obj = {
          goodsStatus: val,
          updateGoodsStatusList
        }
        this.$apis.GOODS.updateGoodsStatus(obj).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        }).catch(() => {
          console.log('error')
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    handleChange(value) {
      console.log(value)
    },
    onSubmit() {
      this.pageNo = 1
      this.getList()
    }
  }
}
</script>
