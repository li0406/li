<template>
  <div class="receive-container">
    <div class="title">领用查询</div>
    <div class="search">
      <el-form
        label-position="left"
        class="formBox"
        label-width="86px"
        ref="ruleForm"
        :model="ruleForm"
      >
        <el-form-item prop="card_code" label="礼券编号:" class="mf mw300">
          <el-input v-model="ruleForm.card_code" maxlength="20" placeholder="请输入编号"></el-input>
        </el-form-item>
        <el-form-item prop="yz_tel" label="业主手机号:" class="mf mw300">
          <el-input v-model="ruleForm.yz_tel" maxlength="11" placeholder="请输入手机号"></el-input>
        </el-form-item>
        <el-form-item class="button mr mw300">
          <el-button type="danger" @click="submitForm('ruleForm')">查询</el-button>
          <el-button type="danger" plain @click="resetForm('ruleForm')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="tables">
      <el-table stripe :data="tableData" border v-loading="loading">
        <el-table-column prop="card_code" label="礼券编号"></el-table-column>
        <el-table-column prop="name" label="礼券名称"></el-table-column>
        <el-table-column label="业主手机号">
          <template slot-scope="scope">
            <span>{{ scope.row.tel }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="xiaoqu" label="小区名称"></el-table-column>
        <el-table-column label="领取时间">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time | fiterTime }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="pagination">
        <el-pagination
          class="seat"
          :current-page="currentPage"
          :page-sizes="[10, 20, 50, 100]"
          :page-size="pageSize"
          layout="total, sizes, prev, pager, next, jumper"
          :total="totals"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>
  </div>
</template>

<script>
import apiPromotions from '@/api/promotions';
import dayjs from 'dayjs';

export default {
  name: 'Receive',
  filters: {
    fiterTime(val) {
      const day = dayjs(val * 1000).format('YYYY-MM-DD HH:mm:ss');
      return day;
    },
  },
  data() {
    return {
      id: this.$route.params.id ? this.$route.params.id : '',
      mode: this.$route.query.mode ? this.$route.query.mode : '',
      ruleForm: {
        card_code: '',
        yz_tel: '',
      },
      currentPage: 1,
      totals: 0,
      pageSize: 10,
      loading: false,
      tableData: [],
      limit: 10,
    };
  },
  created() {
    this.getList(); // 接口通了把此注释放开
  },
  methods: {
    handleArguments() {
      const queryObj = {
        id: this.id,
        card_code: this.ruleForm.card_code,
        yz_tel: this.ruleForm.yz_tel,
        page: this.currentPage,
        limit: this.limit,
      };
      return queryObj;
    },
    getList() {
      const queryObj = this.handleArguments();
      if (this.mode === 1) {
        this.loading = true;
        apiPromotions.List(queryObj).then((res) => {
          if (parseInt(res.data.error_code, 10) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              // eslint-disable-next-line no-plusplus
              this.currentPage--;
              this.getList();
            } else {
              this.loading = false;
              this.tableData = [];
              this.totals = res.data.data.page.total_number;
              this.pageSize = res.data.data.page.page_size;
              res.data.data.list.forEach((item) => {
                this.tableData.push(item);
              });
            }
          } else {
            this.loading = false;
            this.$message({
              type: 'error',
              message: res.data.error_msg,
              center: true,
              offset: 200,
            });
          }
        });
      } else {
        this.loading = true;
        apiPromotions.cardList(queryObj).then((res) => {
          if (parseInt(res.data.error_code, 10) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              // eslint-disable-next-line no-plusplus
              this.currentPage--;
              this.getList();
            } else {
              this.loading = false;
              this.tableData = [];
              this.totals = res.data.data.page.total_number;
              this.pageSize = res.data.data.page.page_size;
              res.data.data.list.forEach((item) => {
                this.tableData.push(item);
              });
            }
          } else {
            this.loading = false;
            this.$message({
              type: 'error',
              message: res.data.error_msg,
              center: true,
              offset: 200,
            });
          }
        });
      }
    },
    // 分页处理
    handleSizeChange(size) {
      this.limit = size;
      this.getList();
    },
    handleCurrentChange(val) {
      this.currentPage = val;
      this.getList();
    },
    submitForm() {
      this.currentPage = 1;
      this.getList();
    },
    resetForm(formName) {
      this.$refs[formName].resetFields();
    },
  },
};
</script>

<style lang="less" scoped>
.receive-container {
  padding-bottom: 50px;
  background: #fff;
  .title {
    width: 100%;
    height: 60px;
    padding-left: 30px;
    color: #303133;
    font-weight: 500;
    font-size: 16px;
    line-height: 60px;
    border-bottom: 2px solid #e4e7ed;
  }
  .search {
    padding: 20px;
    padding-right: 0;
    padding-bottom: 10px;
    padding-left: 30px;
    overflow: hidden;
  }
  .mr {
    margin-right: 30px;
  }
  .pagination {
    margin-top: 20px;
    margin-bottom: 80px;
    overflow: hidden;
    .seat {
      float: right;
    }
  }
  .button {
    float: right;
    width: 274px !important;
    margin-right: 0 !important;
    .el-form-item__content {
      float: right;
    }
  }
  .mf {
    float: left;
  }
  .mw300 {
    width: 256px;
    margin-right: 20px;
  }
  .mw350 {
    width: 468px;
  }
  .lastItem {
    margin-right: 70px;
  }
  .tables {
    padding: 30px;
    .phones {
      color: #00f;
      cursor: pointer;
    }
  }
}
</style>
