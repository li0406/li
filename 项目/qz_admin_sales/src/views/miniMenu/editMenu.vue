<template>
  <div class="mini-list">
    <div class="search">
      <div class="mr20 fl">
        父级菜单：<br>
        <el-select v-model="fuMenu" placeholder="请选择">
          <el-option
              v-for="item in fathers"
              :key="item.value"
              :label="item.label"
              :value="item.value">
          </el-option>
        </el-select>
      </div>
      <div class="mr20 fl">
        状态：<br>
        <el-select v-model="sMenu" placeholder="请选择">
          <el-option
                  v-for="item in sons"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
          </el-option>
        </el-select>
      </div>
      <div class="menu-top">
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">搜索</el-button>
        <el-button class="add-pri" icon="el-icon-circle-plus" @click="handleAdd">新增菜单</el-button>
      </div>
    </div>
    <div class="qian-main">
      <el-table
              v-loading="loading"
              :data="tableData"
              border
      >
        <el-table-column
                align="center"
                prop="name"
                label="名称"
        />
        <el-table-column
                align="center"
                label="父级菜单"
        >
          <template slot-scope="scope">
            {{ scope.row.parentid | transParentid }}
          </template>
        </el-table-column>
        <el-table-column
                align="center"
                prop="px"
                label="排序"
        />
        <el-table-column
                align="center"
                prop="system_menu"
                label="目标功能"
        />
        <el-table-column
                align="center"
                prop="enabled"
                label="状态"
        >
          <template slot-scope="scope">
            {{ scope.row.enabled | transEnabled }}
          </template>
        </el-table-column>
        <el-table-column
                align="center"
                label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetail(scope.row.id)">编辑</span>
            <span class="link-red pointer" @click="handleBan(scope.row)">{{ parseInt(scope.row.enabled) == 2 ?
            '启用' : '禁用' }}</span>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
</template>

<script>
import { fetchMiniList, fetchMiniSwitch } from '@/api/miniMenu'
export default {
  name: 'EditMenu',
  filters: {
    transParentid(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '工作台'
      } else if (parseInt(val) === 2) {
        return '统计'
      }
    },
    transEnabled(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '启用'
      } else if (parseInt(val) === 2) {
        return '禁用'
      }
    }
  },
  data() {
    return {
      fuMenu: '',
      sMenu: '',
      loading: false,
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      tableData: [],
      fathers: [
        {
          value: '',
          label: '全部'
        },
        {
          value: '1',
          label: '工作台'
        },
        {
          value: '2',
          label: '统计'
        }
      ],
      sons: [
        {
          value: '',
          label: '全部'
        },
        {
          value: '1',
          label: '启用'
        },
        {
          value: '2',
          label: '禁用'
        }
      ]
    }
  },
  created() {
    this.fetchMiniList()
  },
  methods: {
    // 获取列表数据
    handleArguments() {
      const queryObj = {
        parentid: this.fuMenu,
        enabled: this.sMenu
      }
      return queryObj
    },
    fetchMiniList() {
      const queryObj = this.handleArguments()
      this.loading = true
      fetchMiniList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.tableData = []
          res.data.data.list.forEach((item, index) => {
            this.tableData.push(item)
          })
          this.loading = false
        }
      })
    },
    // 搜索
    handleSearch() {
      this.fetchMiniList()
    },
    // 禁止
    fetchMiniSwitch(obj, cb) {
      const enable = (parseInt(obj.enabled) === 1 ? 2 : 1)
      fetchMiniSwitch({
        id: obj.id,
        enabled: enable
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          cb && cb.call(this)
        } else {
          this.$message.error('网络异常，请稍后再试')
        }
      })
    },
    handleBan(obj) {
      let statusVal = '启用'
      if (parseInt(obj.enabled) === 1) {
        statusVal = '禁用'
      }
      this.$confirm('您确定要' + statusVal + '吗？', statusVal + '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        center: true
      })
        .then(() => {
          this.fetchMiniSwitch(obj, function() {
            obj.enabled = parseInt(obj.enabled) === 2 ? 1 : 2
            this.$message({
              type: 'success',
              message: statusVal + '成功!'
            })
          })
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消'
          })
        })
    },
    // 增加新
    handleAdd() {
      this.$router.push({
        path: 'AddMenu/'
      })
    },
    // 编辑
    handleDetail(id) {
      this.$router.push({
        path: 'AddMenu/',
        query: { id: id }
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .link-red{
    margin-left: 10px;
  }
  .add-pri:hover{
    color: #ffffff;
    border-color: #55E5A2;
    background-color: #55E5A2
  }
  .add-pri:active{
    color: #ffffff;
    border-color: #55E5A2;
    background-color: #55E5A2
  }
  .el-select{
    margin-top:4px;
  }
  .menu-top{
    margin-top:4px;
  }
  .add-pri{
    background: #00A65A;
    color: #ffffff;
  }
  .mini-list {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .fl {
      float: left;
    }
    .mr20 {
      margin-right: 20px;
    }
    .search {
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
    }
    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
    }
    .el-pagination{
      text-align: center;
      margin-top: 20px;
    }
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
  }
</style>