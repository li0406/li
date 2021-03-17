<template>
  <div class="deposit-detail">
    <div class="search">
      <el-form
        label-position="left"
        label-width="86px"
        class="formBox"
        ref="ruleForm"
        :model="ruleForm"
      >
        <el-form-item label="流水号:" prop="trade_no" class="mf mw300">
          <el-input v-model="ruleForm.trade_no" placeholder="请输入流水号" clearable></el-input>
        </el-form-item>
        <el-form-item label="交易时间:" prop="day" class="mf mw350">
          <el-date-picker
            v-model="ruleForm.day"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          ></el-date-picker>
        </el-form-item>
        <el-form-item label="保证金类型:" prop="type" class="mf mw300 lastItem">
          <el-select v-model="ruleForm.type" placeholder="请选择" clearable>
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item class="button">
          <el-button type="danger" @click="submitForm('ruleForm')">查询</el-button>
          <el-button type="danger" plain @click="resetForm('ruleForm')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-row class="cost">
      <el-col :span="5" class="money">
        <span>保证金余额：{{capital.deposit_money}}元</span>
      </el-col>
      <el-col :span="4" class="daoc">
        <el-button type="info" plain @click="handleReport" v-loading="exportLoading">导出.xlsx</el-button>
      </el-col>
    </el-row>
    <div class="tables">
      <el-table stripe :data="tableData" border v-loading="loading">
        <el-table-column prop="trade_type" label="保证金类型">
          <template slot-scope="scope">{{ scope.row.trade_type_name }}</template>
        </el-table-column>
        <el-table-column prop="trade_amount_name" label="金额"></el-table-column>
        <el-table-column prop="account_amount" label="保证金余额"></el-table-column>
        <el-table-column prop="trade_no" label="流水号"></el-table-column>
        <el-table-column prop="created_at" label="交易时间">
          <template slot-scope="scope">{{ scope.row.created_at | transTime }}</template>
        </el-table-column>
        <el-table-column prop="trade_remark" label="备注"></el-table-column>
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
import apiFinance from '@/api/finance';
// eslint-disable-next-line camelcase
import { export_json_to_excel } from '@/excel/Export2Excel';
import dayjs from 'dayjs';

export default {
  name: 'Round',
  filters: {
    // eslint-disable-next-line consistent-return
    transType(val) {
      if (val === 11) {
        return '保证金充值';
      }
      if (val === 12) {
        return '保证金扣费';
      }
      if (val === 13) {
        return '保证金部分退还';
      }
      if (val === 14) {
        return '保证金全部退还';
      }
      if (val === 15) {
        return '备用';
      }
    },
    transTime(val) {
      const day = dayjs(val * 1000).format('YYYY-MM-DD HH:mm');
      return day;
    },
  },
  data() {
    return {
      ruleForm: {
        trade_no: '',
        day: '',
        type: '',
      },
      rule: {},
      options: [
        {
          value: '',
          label: '全部',
        },
        {
          value: '11',
          label: '保证金充值',
        },
        {
          value: '12',
          label: '保证金扣费',
        },
        {
          value: '13',
          label: '保证金部分退还',
        },
        {
          value: '14',
          label: '保证金全部退还',
        },
        {
          value: '15',
          label: '保证金转化轮单费',
        },
        {
          value: '16',
          label: '保证金转平台使用费',
        },
        {
          value: '17',
          label: '保证金折扣返点',
        },
      ],
      currentPage: 1,
      totals: 0,
      pageSize: 10,
      loading: false,
      tableData: [],
      exportLoading: false,
      capital: {},
      limt: 10,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    handleArguments() {
      let begin = '';
      let end = '';
      if (this.ruleForm.day) {
        begin = this.ruleForm.day[0];
        end = this.ruleForm.day[1];
      }
      const queryObj = {
        start_time: begin,
        end_time: end,
        page: this.currentPage,
        trade_no: this.ruleForm.trade_no,
        type: this.ruleForm.type,
        limit: this.limit,
      };
      return queryObj;
    },
    getList() {
      const queryObj = this.handleArguments();
      this.loading = true;
      apiFinance.deposit(queryObj).then((res) => {
        if (parseInt(res.data.error_code, 10) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            // eslint-disable-next-line no-plusplus
            this.currentPage--;
            this.getList();
          } else {
            this.loading = false;
            this.tableData = [];
            this.capital = res.data.data.capital;
            this.totals = res.data.data.page.total_number;
            this.pageSize = res.data.data.page.page_size;
            res.data.data.list.forEach((item) => {
              this.tableData.push(item);
            });
          }
        } else {
          this.loading = false;
        }
      });
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
    // 导出
    async handleReport() {
      this.exportLoading = true;
      const tHeader = ['保证金类型', '金额', '保证金余额', '流水号', '交易时间', '备注'];
      // 上面设置Excel的表格第一行的标题
      const filterVal = ['trade_type_name', 'trade_amount_name', 'account_amount', 'trade_no', 'xtime', 'trade_remark'];

      try {
        const queryObj = this.handleArguments();
        const res = await apiFinance.deposit(queryObj);
        if (res.data.error_code === 0) {
          const list = res.data.data.list;
          if (list.length < 1) {
            this.exportLoading = false;
            this.$message.error('导出数据为空');
            return;
          }
          list.forEach((it) => {
            switch (it.trade_type) {
              case 11:
                // eslint-disable-next-line no-param-reassign
                it.xtype = '保证金充值';
                break;
              case 12:
                // eslint-disable-next-line no-param-reassign
                it.xtype = '保证金扣费';
                break;
              case 13:
                // eslint-disable-next-line no-param-reassign
                it.xtype = '保证金部分退还';
                break;
              case 14:
                // eslint-disable-next-line no-param-reassign
                it.xtype = '保证金全部退还';
                break;
              case 15:
                // eslint-disable-next-line no-param-reassign
                it.xtype = '备用';
                break;
              default:
                // eslint-disable-next-line no-param-reassign
                it.xtype = '';
            }
            // eslint-disable-next-line no-param-reassign
            it.xtime = dayjs(it.created_at * 1000).format('YYYY-MM-DD HH:mm');
          });
          const data = this.formatJson(filterVal, list);
          export_json_to_excel(tHeader, data, '轮单明细');
          this.exportLoading = false;
        } else {
          this.$message.error(res.data.error_msg);
          this.exportData = [];
          this.exportLoading = false;
        }
      } catch (error) {
        this.$message.error('后台服务出错');
      }
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]));
    },
  },
};
</script>

<style lang="less">
.deposit-detail {
  .search {
    min-width: 1650px;
    padding: 20px;
    padding-right: 0;
    padding-bottom: 10px;
    overflow: hidden;
  }
  .mr {
    margin-right: 30px;
  }
  .money {
    width: 340px;
    span {
      margin-right: 30px;
    }
  }
  .pagination {
    margin-top: 20px;
    margin-bottom: 80px;
    overflow: hidden;
    .seat {
      float: right;
    }
  }
  .cost {
    padding-left: 18px;
    font-size: 14px;
  }
  .button {
    float: left;
    width: 550px;
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
  .daoc {
    float: right;
    button {
      float: right;
    }
  }
  .el-table__header tr,
  .el-table__header th {
    height: 50px;
    padding: 0;
  }
}
</style>
