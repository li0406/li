<template>
  <div>
    <div class="app-search">
      <qz-search-item class="qz-search-item" label="礼券名称：">
        <el-input v-model="couponName" placeholder="请输入礼券名称" @input="searchChange"></el-input>
      </qz-search-item>

      <qz-search-item class="qz-search-item" label="礼券状态：" style="margin: 0 16px 0 16px;">
        <el-select v-model="couponStatus" placeholder="请选择">
          <el-option key label="全部" value></el-option>
          <el-option key="1" label="可用" value="1"></el-option>
          <el-option key="2" label="未生效" value="2"></el-option>
          <el-option key="3" label="失效" value="3"></el-option>
          <el-option key="4" label="下架" value="4"></el-option>
        </el-select>
      </qz-search-item>
      <qz-search-item class="qz-search-item" label="审核状态：" style="margin: 0 16px 0 16px;">
        <el-select v-model="auditStatus" placeholder="请选择">
          <el-option key label="全部" value></el-option>
          <el-option key="0" label="待审核" value="1"></el-option>
          <el-option key="1" label="审核通过" value="2"></el-option>
          <el-option key="2" label="未通过" value="3"></el-option>
          <el-option key="3" label="撤回" value="4"></el-option>
        </el-select>
      </qz-search-item>

      <div class="more-button" style="float: right">
        <!-- ISSUE: type="danger" 错误用法，主题色和功能性颜色冲突 -->
        <el-button type="danger" @click="getList">查询</el-button>
        <el-button type="danger" plain @click="edit(0)">添加礼券</el-button>
      </div>
    </div>

    <div class="list">
      <el-table border style="width: 100%" :data="dataList">
        <el-table-column label="礼券名称" align="center">
          <template slot-scope="scope">
            <span
              @click="$router.push(`/coupon/detail/${scope.row.id}`)"
              class="lqName"
            >{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" align="center">
          <template
            slot-scope="scope"
          >{{ scope.row.activity_start | dateFormat }} - {{ scope.row.activity_end | dateFormat }}</template>
        </el-table-column>

        <el-table-column label="有效时间" align="center">
          <template
            slot-scope="scope"
          >{{ scope.row.start | dateFormat }} - {{ scope.row.end | dateFormat }}</template>
        </el-table-column>

        <el-table-column prop="realt_discount" label="优惠活动" align="center"></el-table-column>

        <el-table-column prop="amount" label="发放数量" align="center"></el-table-column>

        <el-table-column label="已领数量" align="center">
          <template slot-scope="scope">
            <span
              class="ylsl"
              @click="$router.push({path:`/receive/${scope.row.id}`,query:{mode: 1}})"
            >{{ scope.row.receive_num }}</span>
          </template>
        </el-table-column>

        <el-table-column label="礼券状态" align="center">
          <template slot-scope="scope">
            <span style="color: #06f" v-if="scope.row.card_status === 1">未生效</span>
            <span style="color: #3f6600" v-else-if="scope.row.card_status === 2">可用</span>
            <span style="color: #f00" v-else-if="scope.row.card_status === 3">下架</span>
            <span style="color: #666" v-else-if="scope.row.card_status === 4">失效</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" align="center">
          <template slot-scope="scope">
            <span style="color: #f60" v-if="scope.row.check === 1">待审核</span>
            <span style="color:#008000" v-else-if="scope.row.check === 2">审核通过</span>
            <span style="color: #ff003f" v-else-if="scope.row.check === 3">未通过</span>
            <span style="color: #03c" v-else-if="scope.row.check === 4">撤回</span>
          </template>
        </el-table-column>

        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <span
              style="color: #f60"
              v-if="scope.row.action_status === 1"
              @click="edit(scope.row.id)"
              class="cz"
            >重新申请</span>
            <span
              style="color:#060"
              v-else-if="scope.row.action_status === 2"
              class="cz"
              @click="operating(scope.row.id,scope.row.action_status)"
            >撤回</span>
            <span
              style="color: #f00"
              v-else-if="scope.row.action_status === 3"
              class="cz"
              @click="operating(scope.row.id,scope.row.action_status)"
            >下架</span>
            <span
              style="color: #900"
              v-else-if="scope.row.action_status === 4"
              class="cz"
              @click="operating(scope.row.id,scope.row.action_status)"
            >上架</span>
          </template>
        </el-table-column>
      </el-table>
    </div>

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

export default {
  name: 'couponList',

  data() {
    return {
      couponName: '',
      couponStatus: '',
      auditStatus: '',
      page: {
        total: 0,
        pageSize: 10,
        currentPage: 1,
      },
      dataList: [],
    };
  },
  created() {
    this.getList();
  },

  components: {
    QzSearchItem,
  },
  methods: {
    getList() {
      const query = {
        name: this.couponName,
        card_status: this.couponStatus,
        check_status: this.auditStatus,
        page: this.page.currentPage,
        limit: this.page.pageSize,
      };
      api.getCouponList(query).then((res) => {
        this.dataList = res.data.data.list;
        this.page.total = res.data.data.page.total_number;
      });
    },

    edit(id) {
      this.$router.push(`/coupon/edit/${id}`);
    },

    searchList() {},

    searchChange() {},

    currentChange(val) {
      this.page.currentPage = val;
      this.getList();
    },

    sizeChange(val) {
      this.page.pageSize = val;
      this.getList();
    },

    operating(id, action) {
      let actionName = '';
      if (action === 2) actionName = '撤回';
      if (action === 3) actionName = '下架';
      if (action === 4) actionName = '上架';

      this.$confirm(`确认${actionName}吗？`, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(() => {
        api.operating(id, action).then((res) => {
          if (res.data.error_code === 0) {
            this.$message.success('操作成功');
            this.getList();
          } else {
            this.$message.error('操作失败');
          }
        });
      });
    },
  },

  filters: {
    dateFormat(time) {
      const date = new Date(time * 1000);
      const y = date.getFullYear();
      const m = date.getMonth() + 1 < 10 ? `0${date.getMonth() + 1}` : date.getMonth() + 1;
      const d = date.getDate() < 10 ? `0${date.getDate()}` : date.getDate();
      return `${y}-${m}-${d}`;
    },
  },
};
</script>

<style scoped lang="scss">
.page {
  margin-top: 15px;
  margin-bottom: 80px;
  text-align: right;
}

.lqName {
  color: #03c;
}

.lqName:hover {
  cursor: pointer;
}

.cz:hover {
  cursor: pointer;
}
.ylsl {
  color: #03c;
}
.ylsl:hover {
  color: #03c;
  cursor: pointer;
}
</style>

