<template>
  <div class="round-details">
    <div class="search">
      <el-form
        label-position="left"
        class="formBox"
        label-width="86px"
        ref="ruleForm"
        :model="ruleForm"
      >
        <el-form-item label="装修预算:" prop="yusuan" class="mf mw300">
          <el-select v-model="ruleForm.yusuan" placeholder="请选择" clearable>
            <el-option
              v-for="item in ysoptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="装修类型:" prop="lx" class="mf mw300">
          <el-select v-model="ruleForm.lx" placeholder="请选择" clearable>
            <el-option
              v-for="item in lxOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="装修方式:" prop="fangshi" class="mf mw300 lastItem">
          <el-select v-model="ruleForm.fangshi" placeholder="请选择" clearable>
            <el-option
              v-for="item in fsOptions"
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
    <div class="tables">
      <el-table stripe :data="tableData" border v-loading="loading">
        <el-table-column label="发布日期">
          <template slot-scope="scope">
            <span>{{ scope.row.time |transTime }}</span>
          </template>
        </el-table-column>
        <el-table-column prop label="所在区域">
          <template slot-scope="scope">
            <span>{{ scope.row.cname}}{{ scope.row.area_name}}</span>
          </template>
        </el-table-column>
        <el-table-column prop="community_name" label="小区名称"></el-table-column>
        <el-table-column prop="acreage" label="建筑面积">
          <template slot-scope="scope">
            <span>{{ scope.row.acreage }}㎡</span>
          </template>
        </el-table-column>
        <el-table-column label="装修类型">
          <template slot-scope="scope">
            <span>{{ scope.row.lx == 1 ? '家装' : '公装' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="fangshi" label="装修方式"></el-table-column>
        <el-table-column prop="yusuan" label="装修预算"></el-table-column>
        <el-table-column prop label="剩余时间">
          <template slot-scope="scope">
            <span class="times">{{ scope.row | diffTime }}</span>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="150">
          <template slot-scope="scope">
            <el-button @click="handleDetails(scope.row.id)" type="text" size="small">订单详情</el-button>
            <el-popconfirm
              confirmButtonText="确定"
              cancelButtonText="不抢"
              title="是否确定抢取该订单？抢单成功后，可在'我的订单'查看哦~"
              @onConfirm="handleEdit(scope.row.id)"
              @onCancel="handCancel(scope.row.id)"
              width="232"
            >
              <el-button slot="reference" type="text">抢单</el-button>
            </el-popconfirm>
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
    <el-dialog title="订单详情" :visible.sync="centerDialogVisible" width="600px" center>
      <div class="border" v-loading="detailLoading">
        <div class="left">订单编号</div>
        <div class="right">{{detailData.order_id}}</div>
        <div class="left">发布时间</div>
        <div class="right">{{detailData.real_date}}</div>
        <div class="left">所在区域</div>
        <div class="right">{{detailData.city_name}}{{detailData.area_name}}</div>
        <div class="left">小区名称</div>
        <div class="right">{{detailData.xiaoqu}}</div>
        <div class="left">房屋面积</div>
        <div class="right">{{detailData.mianji}}</div>
        <div class="left">房屋户型</div>
        <div class="right">{{detailData.huxing_name}}</div>
        <div class="left">装修类型</div>
        <div class="right">{{detailData.leixing_name}}</div>
        <div class="left">预算</div>
        <div class="right">{{detailData.yusuan_name}}</div>
        <div class="left">装修方式</div>
        <div class="right">{{detailData.fangshi_name}}</div>
        <div class="left">装修风格</div>
        <div class="right">{{detailData.fengge_name}}</div>
        <div class="left">开工时间</div>
        <div class="right">{{detailData.start}}</div>
        <div class="left">量房时间</div>
        <div class="right">{{detailData.lftime}}</div>
        <div class="last">
          <div class="l">装修要求</div>
          <div class="r" v-html="zxtext"></div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button class="no-gan" @click="footNo(detailData.order_id)">不抢</el-button>
        <el-button type="primary" @click="footQiang(detailData.order_id)">抢单</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import apiOrder from '@/api/order';

import dayjs from 'dayjs';

export default {
  name: 'Rounddetail',
  filters: {
    transTime(val) {
      const day = dayjs(val * 1000).format('YYYY-MM-DD HH:mm');
      return day;
    },
    diffTime(val) {
      const status = val.order_status;
      const atTime = dayjs().unix();
      // eslint-disable-next-line eqeqeq
      const times = status ==1 ? val.rob_time + 7200 - atTime : val.rob_time + 3600 - atTime;
      const min = Math.floor(times % 3600);
      const shi = Math.floor(times / 3600) > 0 ? `${Math.floor(times / 3600)}小时` : '';
      const diffTimes = times > 0 ? `${shi}${Math.floor(min / 60)}分` : '00:00:00';
      return diffTimes;
    },
  },
  data() {
    return {
      ruleForm: {
        yusuan: '',
        lx: '',
        fangshi: '',
      },
      rule: {},
      ysoptions: [
        {
          value: '',
          label: '请选择',
        },
        {
          value: '16',
          label: '4万以下',
        },
        {
          value: '17',
          label: '4-7万',
        },
        {
          value: '18',
          label: '7-10万',
        },
        {
          value: '19',
          label: '10-15万',
        },
        {
          value: '21',
          label: '15-20万',
        },
        {
          value: '22',
          label: '20-30万',
        },
        {
          value: '23',
          label: '30-50万',
        },
        {
          value: '24',
          label: '50-100万',
        },
        {
          value: '25',
          label: '100万以上',
        },
        {
          value: '42',
          label: '面议',
        },
      ],
      lxOptions: [
        {
          value: '',
          label: '全部',
        },
        {
          value: '1',
          label: '家装',
        },
        {
          value: '2',
          label: '公装',
        },
      ],
      fsOptions: [
        {
          value: '',
          label: '全部',
        },
        {
          value: '29',
          label: '半包',
        },
        {
          value: '30',
          label: '全包',
        },
        {
          value: '31',
          label: '清包',
        },
        {
          value: '32',
          label: '面议',
        },
      ],
      currentPage: 1,
      totals: 0,
      pageSize: 10,
      loading: false,
      tableData: [],
      page_count: 10,
      // 弹框
      centerDialogVisible: false,
      detailData: {},
      zxtext: '',
      detailLoading: false,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    handleArguments() {
      const queryObj = {
        yusuan: this.ruleForm.yusuan,
        lx: this.ruleForm.lx,
        fangshi: this.ruleForm.fangshi,
        page: this.currentPage,
        page_count: this.page_count,
        type: 1,
      };
      return queryObj;
    },
    getList() {
      const queryObj = this.handleArguments();
      this.loading = true;
      apiOrder.robList(queryObj).then((res) => {
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
    },
    handleDetails(e) {
      this.centerDialogVisible = true;
      this.detailLoading = true;
      apiOrder.robDetail({ order_id: e }).then((res) => {
        if (parseInt(res.data.error_code, 10) === 0) {
          this.detailLoading = false;
          this.detailData = res.data.data.detail;
          this.zxtext = res.data.data.detail.text.replace(/(\r\n|\n|\r)/gm, '<br />');
        } else {
          this.$message({
            type: 'error',
            message: res.data.error_msg,
            center: true,
            offset: 200,
          });
        }
      });
    },
    handleEdit(e) {
      apiOrder.roborder({ order_id: e }).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.$message({
            message: '抢单成功',
            type: 'success',
          });
          this.getList();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    handCancel(e) {
      apiOrder.norob({ order_id: e }).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.$message({
            message: '温馨提示: 该订单已从订单池中删除',
            type: 'success',
          });
          this.getList();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    footQiang(e) {
      apiOrder.roborder({ order_id: e }).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.$message({
            message: '抢单成功',
            type: 'success',
          });
          this.centerDialogVisible = false;
          this.getList();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    footNo(e) {
      apiOrder.norob({ order_id: e }).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.$message({
            message: '温馨提示: 该订单已从订单池中删除',
            type: 'success',
          });
          this.centerDialogVisible = false;
          this.getList();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    // 分页处理
    handleSizeChange(size) {
      this.page_count = size;
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

<style lang="less">
.round-details {
  padding-bottom: 50px;
  .search {
    padding: 20px;
    padding-right: 0;
    padding-bottom: 10px;
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
  .tables {
    .times {
      color: red;
    }
    .rob {
      color: red;
    }
  }
  .border {
    box-sizing: border-box;
    width: 100%;
    height: auto;
    overflow: hidden;
    border: 1px solid #e6e6e6;
    border-right: 0;
    div {
      float: left;
      box-sizing: border-box;
      border-right: 1px solid #e6e6e6;
      border-bottom: 1px solid #e6e6e6;
    }
    .left {
      width: 20%;
      height: 38px;
      line-height: 38px;
      text-align: center;
    }
    .right {
      width: 30%;
      height: 38px;
      padding: 0 10px;
      overflow: hidden;
      line-height: 38px;
      white-space: nowrap;
      text-align: left;
      text-overflow: ellipsis;
    }
    .last {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      height: auto;
      overflow: hidden;
      border-bottom: 0;
      .l {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 19.8%;
        overflow: hidden;
        border: none;
      }
      .r {
        width: 80.2%;
        padding: 10px;
        overflow: hidden;
        line-height: 32px;
        border: none;
        border-left: 1px solid #e6e6e6;
      }
    }
  }
  .no-gan {
    margin-right: 70px;
  }
  .el-popover__reference {
    span {
      margin-left: 20px;
      color: red;
    }
  }
}
</style>
