<template>
  <div>
    <!--搜索-->
    <div class="app-search">
      <qz-search-item class="qz-search-item" label="标题名称：">
        <el-input v-model="eventName" placeholder="请输入标题名称"></el-input>
      </qz-search-item>

      <qz-search-item class="qz-search-item" label="活动状态：" style="margin: 0 16px 0 16px;">
        <el-select v-model="eventStatus" placeholder="请选择">
          <el-option label="全部" value=""></el-option>
          <el-option label="正在进行" value="1"></el-option>
          <el-option label="等待中" value="2"></el-option>
          <el-option label="过期活动" value="3"></el-option>
          <el-option label="暂停" value="4"></el-option>
        </el-select>
      </qz-search-item>

      <div class="more-button">
        <el-button type="danger" @click="getList">查询</el-button>
        <el-button type="danger" plain @click="editEvent(0)">添加活动</el-button>
      </div>
    </div>
    <!--数据-->
    <el-table :data="dataList" style="width: 100%" border v-loading="loading">
      <el-table-column prop="title" label="活动主题" align="center"></el-table-column>

      <el-table-column label="活动时间" align="center">
        <template
          slot-scope="scope"
        >{{ scope.row.start | dateFormat }} - {{ scope.row.end | dateFormat }}</template>
      </el-table-column>

      <el-table-column label="发布时间" align="center">
        <template slot-scope="scope">{{ scope.row.time | dateFormat }}</template>
      </el-table-column>

      <el-table-column label="活动状态" align="center">
        <!--状态码 0：过期活动  1：进行中  2：等待中  3：暂停-->
        <template slot-scope="scope">
          <div style="display: inline-block;width: 50px">
            <el-switch
              v-if="scope.row.change_status !== 0"
              v-model="scope.row.change_status"
              :active-value="1"
              :inactive-value="2"
              active-color="#13ce66"
              inactive-color="#ff4949"
              @change="statusChange(scope.row.id,scope.row.change_status)"
            ></el-switch>
          </div>
          <span style="display: inline-block;width: 80px">{{scope.row.type_name}}</span>
        </template>
      </el-table-column>

      <el-table-column label="操作" align="center">
        <template slot-scope="scope">
          <el-link type="primary" :underline="false">
            <div @click="editEvent(scope.row.id)">编辑</div>
          </el-link>
          <el-link type="danger" :underline="false">
            <div @click="del(scope.row.id)">删除</div>
          </el-link>
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

export default {
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
        state: this.eventStatus,
        page: this.page.currentPage,
        limit: this.page.pageSize,
      };
      api.getEventList(query).then((res) => {
        this.loading = false;
        this.dataList = res.data.data.list;
        this.page.total = res.data.data.page.total_number;
      });
    },

    statusChange(id, status) {
      api.statusChange(id, status).then((res) => {
        if (res.data.error_code === 0) {
          this.$message.success('操作成功');
          this.getList();
        } else {
          this.$message.success(res.data.error_msg);
        }
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

    // 编辑操作
    editEvent(id) {
      this.$router.push(`/editEvent/${id}`);
    },

    del(id) {
      this.$confirm('此操作将删除该记录, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          api
            .deleteEvent(id)
            .then((res) => {
              if (res.data.error_code === 0) {
                this.$message.success('删除成功');
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
<style scoped>
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
</style>
