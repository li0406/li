<template>
  <div class="round-detail">
    <div class="search">
      <el-form
        label-position="left"
        class="formBox"
        label-width="86px"
        ref="ruleForm"
        :model="ruleForm"
      >
        <el-form-item label="订单编号:" prop="order_id" class="mf mw300">
          <el-input v-model="ruleForm.order_id" placeholder="请输入订单编号" clearable></el-input>
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

        <el-form-item label="账单类型:" prop="type" class="mf mw300">
          <el-select v-model="ruleForm.type" placeholder="请选择" clearable>
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="收支类型:" prop="ie_type" class="mf mw300 lastItem">
          <el-select v-model="ruleForm.ie_type" placeholder="请选择" clearable>
            <el-option
              v-for="item in typeOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item class="button mr mw300">
          <el-button type="danger" @click="submitForm('ruleForm')">查询</el-button>
          <el-button type="danger" plain @click="resetForm('ruleForm')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-row class="cost">
      <el-col :span="10" class="money">
        <span>轮单余额：{{capital.account_amount}}元</span>
        <span v-if="capital.gift_amount > 0">赠送金额：{{capital.gift_amount}}元</span>
        <span>当前剩余补轮次数：{{capital.round_order_number}}</span>
      </el-col>
      <el-col :span="4" class="daoc">
        <el-button type="info" plain @click="handleReport" v-loading="exportLoading">导出.xlsx</el-button>
      </el-col>
    </el-row>
    <div class="tables">
      <el-table stripe :data="tableData" border v-loading="loading">
        <el-table-column prop="trade_type_name" label="账单类型"></el-table-column>
        <el-table-column prop="operation_type" label="收支类型">
          <template slot-scope="scope">{{ scope.row.operation_type == 1 ? '收入' : '支出' }}</template>
        </el-table-column>
        <el-table-column prop="trade_amount_name" label="金额"></el-table-column>
        <el-table-column prop="account_amount" label="轮单余额"></el-table-column>
        <el-table-column prop="gift_amount" label="赠送金"></el-table-column>
        <el-table-column prop="order_id" label="订单编号"></el-table-column>
        <el-table-column label="交易时间">
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
    transTime(val) {
      const day = dayjs(val * 1000).format('YYYY-MM-DD HH:mm');
      return day;
    },
  },
  data() {
    return {
      ruleForm: {
        order_id: '',
        day: '',
        type: '',
        ie_type: '',
      },
      rule: {},
      options: [
        {
          value: '',
          label: '全部',
        },
        {
          value: '1',
          label: '账户充值',
        },
        {
          value: '2',
          label: '其它扣费',
        },
        {
          value: '3',
          label: '轮单扣费',
        },
        {
          value: '5',
          label: '轮单费返还',
        },
      ],
      typeOptions: [
        {
          value: '',
          label: '全部',
        },
        {
          value: '1',
          label: '收入',
        },
        {
          value: '2',
          label: '支出',
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
        order_id: this.ruleForm.order_id,
        type: this.ruleForm.type,
        ie_type: this.ruleForm.ie_type,
        limit: this.limit,
      };
      return queryObj;
    },
    getList() {
      const queryObj = this.handleArguments();
      this.loading = true;
      apiFinance.account(queryObj).then((res) => {
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
          this.$message({
            type: 'error',
            message: res.data.error_msg,
            center: true,
            offset: 200,
          });
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
      const tHeader = ['账单类型', '收支类型', '金额', '轮单余额', '赠送金', '订单编号', '交易时间', '备注'];
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'trade_type_name',
        'xtype',
        'trade_amount_name',
        'account_amount',
        'gift_amount',
        'order_id',
        'xtime',
        'trade_remark',
      ];

      try {
        const queryObj = this.handleArguments();
        const res = await apiFinance.account(queryObj);
        if (res.data.error_code === 0) {
          const list = res.data.data.list;
          if (list.length < 1) {
            this.exportLoading = false;
            this.$message.error('导出数据为空');
            return;
          }
          list.forEach((it) => {
            // eslint-disable-next-line no-param-reassign
            it.xtype = it.operation_type === 1 ? '收入' : '支出';
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
.round-detail {
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
    width: 600px;
    span {
      margin-right: 50px;
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
