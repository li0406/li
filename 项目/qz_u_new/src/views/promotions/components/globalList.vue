<template>
  <div>
    <!--搜索-->
    <div class="app-search">
      <qz-search-item class="qz-search-item" label="礼券名称：">
        <el-input v-model="eventName" placeholder="请输入礼券名称"></el-input>
      </qz-search-item>

      <qz-search-item class="qz-search-item" label="礼券状态" style="margin: 0 16px 0 16px;">
        <el-select v-model="eventStatus" placeholder="请选择">
          <el-option label="全部" value></el-option>
          <el-option label="待确认" value="2"></el-option>
          <el-option label="未生效" value="6"></el-option>
          <el-option label="可用 " value="7"></el-option>
          <el-option label="失效 " value="4"></el-option>
          <el-option label="撤回  " value="1"></el-option>
          <el-option label="下架 " value="3"></el-option>
          <el-option label="未通过 " value="8"></el-option>
          <el-option label="已禁用 " value="5"></el-option>
        </el-select>
      </qz-search-item>

      <div class="more-button">
        <el-button type="danger" @click="getList">查询</el-button>
        <el-button type="danger" plain @click="golijuan">可领用礼券</el-button>
      </div>
    </div>
    <!--数据-->
    <el-table :data="dataList" style="width: 100%" border v-loading="loading">
      <el-table-column label="礼券名称" align="center">
        <template slot-scope="scope">
          <span
            @click="$router.push(`/coupon/ljdetail/${scope.row.id}`)"
            class="lqName"
          >{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="活动时间" align="center">
        <template
          slot-scope="scope"
        >{{ scope.row.activity_start | dateFormat }} 至 {{ scope.row.activity_end | dateFormat }}</template>
      </el-table-column>

      <el-table-column label="有效时间" align="center">
        <template
          slot-scope="scope"
        >{{ scope.row.start | dateFormat }} 至 {{ scope.row.end | dateFormat }}</template>
      </el-table-column>
      <el-table-column label="立减金额" prop="realt_discount" align="center"></el-table-column>
      <el-table-column label="发放数量" prop="amount" align="center"></el-table-column>
      <el-table-column label="已领数量" align="center">
        <template slot-scope="scope">
          <span
            class="ylsl"
            @click="$router.push({path:`/receive/${scope.row.id}`,query:{mode: 2}})"
          >{{scope.row.receive_num}}</span>
        </template>
      </el-table-column>
      <el-table-column label="礼券状态" align="center">
        <template slot-scope="scope">
          <span v-if="scope.row.status==2">待确认</span>
          <span v-if="scope.row.status==6">未生效</span>
          <span v-if="scope.row.status==7">可用</span>
          <span v-if="scope.row.status==4">失效</span>
          <span v-if="scope.row.status==1">撤回</span>
          <span v-if="scope.row.status==3" class="green">下架</span>
          <span v-if="scope.row.status==8">未通过</span>
          <span v-if="scope.row.status==5" class="yellow">已禁用</span>
        </template>
      </el-table-column>

      <el-table-column label="操作" align="center">
        <template slot-scope="scope">
          <el-button
            v-if="scope.row.status == 2"
            type="text"
            size="small"
            @click="del(scope.row.id, 2)"
            class="green"
          >撤回</el-button>
          <el-button
            v-if="scope.row.status == 7"
            type="text"
            size="small"
            @click="del(scope.row.id, 3)"
            class="red"
          >下架</el-button>
          <el-button
            v-if="scope.row.status == 3"
            type="text"
            size="small"
            @click="del(scope.row.id, 4)"
            class="red"
          >上架</el-button>
        </template>
      </el-table-column>
    </el-table>
    <!--分页-->
    <div class="page">
      <el-pagination
        :current-page="page.currentPage"
        :page-sizes="[10, 20, 30, 50]"
        :page-size="page.pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="page.total"
        @current-change="currentChange"
        @size-change="sizeChange"
      ></el-pagination>
    </div>
  </div>
</template>

<script>
import QzSearchItem from '@/components/search-item.vue';
import api from '@/api/promotions';
import dayjs from 'dayjs';

export default {
  name: 'GlobalList',
  data() {
    return {
      // 搜索 活动主题
      eventName: '',
      // 搜索 活动状态
      eventStatus: '',
      // 页数信息
      page: {
        total: null,
        currentPage: 1,
        pageSize: 10,
      },
      // 列表数组
      dataList: [],
      loading: false,
    };
  },
  created() {
    this.getList();
  },
  components: {
    QzSearchItem,
  },
  methods: {
    // 初始化列表
    getList() {
      this.loading = true;
      const query = {
        name: this.eventName,
        card_status: this.eventStatus,
        page: this.page.currentPage,
        limit: this.page.pageSize,
      };
      api.commonCard(query).then((res) => {
        this.loading = false;
        this.dataList = res.data.data.list;
        this.page.total = res.data.data.page.total_number;
      });
    },

    // 改变页数
    currentChange(val) {
      this.page.currentPage = val;
      this.getList();
    },

    // 改变每页显示数量
    sizeChange(val) {
      this.page.pageSize = val;
      this.getList();
    },

    del(id, status) {
      // eslint-disable-next-line no-nested-ternary
      const title = status === 4 ? '确定上架该礼券？' : status === 3 ? '确定下架该礼券？' : '确定撤回该礼券？';
      this.$confirm(title, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          api
            .cardChange({
              id,
              action_status: status,
            })
            .then((res) => {
              if (res.data.error_code === 0) {
                this.$message.success('操作成功');
                this.getList();
              } else {
                this.$message.error(res.data.error_msg);
              }
            })
            .catch((error) => {
              this.$message.error(error.data.error_msg);
            });
        })
        .catch(() => {});
    },

    golijuan() {
      this.$router.push(`/coupon/lijuan`);
    },
  },

  filters: {
    dateFormat(time) {
      return dayjs.unix(time).format('YYYY-MM-DD HH:mm:ss');
    },
  },
};
</script>
<style scoped lang="scss">
.el-link {
  margin: 0 10px;
}

.more-button {
  float: right;
}

.page {
  margin-top: 15px;
  margin-bottom: 80px;
  text-align: right;
}
.ylsl {
  color: #03c;
}
.ylsl:hover {
  color: #03c;
  cursor: pointer;
}
.green {
  color: green;
  font-size: 14px;
}
.red {
  color: red;
  font-size: 14px;
}
.yellow {
  color: #FFA500;
  font-size: 14px;
}
.lqName {
  color: #03c;
}

.lqName:hover {
  cursor: pointer;
}
</style>

