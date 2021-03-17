<template>
  <div>
    <el-card>
      <div class="flex spa-bet">
        <div>
          <div class="font16 col333">收货地址</div>
          <div class="font13 col999 mtb10">您已创建{{ total }}条收货地址，最多可创建10条</div>
        </div>
        <div>
          <el-button type="primary" @click="addaddressdialog">新增收货地址</el-button>
          <el-button @click="toggleSelection()">删除选中的地址</el-button>
        </div>
      </div>
      <el-table
        ref="multipleTable"
        :data="tableData"
        tooltip-effect="dark"
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column
          type="selection"
          width="55"
        />
        <el-table-column
          label="收货人"
          show-overflow-tooltip
        >
          <template slot-scope="scope">
            <div>
              {{ scope.row.receiverName }}
            </div>
          </template>
        </el-table-column>
        <el-table-column
          prop="name"
          label="收货人联系方式"
        >
          <template slot-scope="scope">
            <div class="font13">{{ scope.row.receiverPhone }}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="address"
          label="地址"
        >
          <template slot-scope="scope">
            <div>
              <div class="goods-title col333 font14">{{ scope.row.receivePrince }}{{ scope.row.receiveCity }}{{ scope.row.receiveProper }}</div>
              <div class="col878E93 font12">{{ scope.row.receiveAreaDetails }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          prop="address"
          label="默认地址"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.ifDefult=='1'" class="goods-default font13">默认地址</div>
            <div v-else class="col3B7CFF font13 point" @click="updateStatus(scope.row.id)">设置为默认地址</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="address"
          label="操作"
        >
          <template slot-scope="scope">
            <div class="flex">
              <div class="col3B7CFF font13 point" @click="getAddressDetails(scope.row.id)">编辑</div>
              <div class="colE94747 font13 point ml10" @click="deleteAddress([scope.row.id])">删除</div>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        v-if="tableData&&tableData.length>0"
        class="text-center mt30"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </el-card>
    <addressmsg ref="addressmsg" :addaddressbul="addaddressbul" @getgoodsmsg="addAddress" />
  </div>
</template>

<script>
export default {
  name: 'AddressManagement',
  components: {
    addressmsg: () => import('@/components/addressmsg')
  },
  data() {
    return {
      tableData: [],
      addaddressbul: false,
      currentPage: 1,
      pageSize: 10,
      total: 0,
      ids: []
    }
  },
  created() {
    this.queryListWithPage()
  },
  methods: {
    // 地址管理-获取详情
    getAddressDetails(id) {
      const data = {
        id
      }
      this.$apis.RETAIL.getAddressDetails(data).then(res => {
        if (res.code === 0) {
          this.$refs.addressmsg.addressDetails = res.data
          this.addaddressbul = true
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    //  地址管理-删除地址
    deleteAddress(ids) {
      this.$confirm('你确定要删除选中的地址吗？', '删除提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const data = {
          ids
        }
        console.log(data)
        this.$apis.RETAIL.deleteAddress(data).then(res => {
          if (res.code === 0) {
            this.queryListWithPage()
            this.$message({
              message: res.message,
              type: 'success'
            })
          } else {
            this.$message({
              message: res.message,
              type: 'error'
            })
          }
        }).catch(err => {
          console.log(err)
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.queryListWithPage()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.queryListWithPage()
    },
    addAddress(data) {
      this.$apis.RETAIL.addAddress(data).then(res => {
        if (res.code === 0) {
          this.queryListWithPage()
          this.$message.success(res.message)
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    //  地址管理-设置默认
    updateStatus(id) {
      this.$confirm('你确定要删除选中的地址吗？', '删除提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.RETAIL.updateStatus({ id }).then(res => {
          if (res.code === 0) {
            this.queryListWithPage()
            this.$message.success(res.message)
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    //  地址管理-地址列表
    queryListWithPage() {
      const data = {
        pageNo: this.currentPage,
        pageSize: this.pageSize
      }
      this.$apis.RETAIL.queryListWithPage(data).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
          this.currentPage = res.page.pageNo
          this.pageSize = res.page.pageSize
          this.total = res.page.total
        } else {
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    addaddressdialog() {
      this.addaddressbul = true
    },
    toggleSelection() {
      this.deleteAddress(this.ids)
    },
    handleSelectionChange(val) {
      const idarray = []
      val.forEach(item => {
        idarray.push(item.id)
      })
      this.ids = idarray
    },
    handleChange(value) {
      console.log(value)
    }
  }
}
</script>
