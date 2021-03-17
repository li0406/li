// 3D 效果图
<template>
  <div class="effect-pic">
    <div>
      <div class="app-search">
        <qz-search-item class="qz-search-item" label="标题名称">
          <el-input v-model="search.title" placeholder="请输入标题名称"></el-input>
        </qz-search-item>
        <qz-search-item class="qz-search-item" label="设计师" style="margin: 0 16px 0 16px;">
          <el-select v-model="search.designer" placeholder="请选择设计师">
            <el-option key="0" label="全部" value></el-option>
            <el-option
              v-for="item in designerList"
              :key="item.id"
              :label="item.nick_name"
              :value="item.id"
            ></el-option>
          </el-select>
        </qz-search-item>
        <div class="more-button">
          <!-- ISSUE: type="danger" 错误用法，主题色和功能性颜色冲突 -->
          <el-button type="danger" @click="getTableData">查询</el-button>
          <el-button type="danger" plain @click="gotoAddEffect">+添加效果图</el-button>
        </div>
      </div>
    </div>
    <el-table :data="tableData" border>
      <el-table-column prop="title" label="标题名称"></el-table-column>
      <el-table-column prop="fengge" label="装修风格"></el-table-column>
      <el-table-column prop="mianji" label="房屋面积"></el-table-column>
      <el-table-column prop="status_name" label="状态"></el-table-column>
      <el-table-column prop="time" label="发布时间"></el-table-column>
      <el-table-column label="操作">
        <template slot-scope="scope">
          <a :href="scope.row.url" target="_blank" style="margin-right: 16px;">查看</a>
          <el-popconfirm title="您确定删除该3D效果图吗？" @onConfirm="handleButtonDelete(scope)">
            <el-button slot="reference" type="text">删除</el-button>
          </el-popconfirm>
        </template>
      </el-table-column>
      <el-table-column prop="cr_reason" label="原因"></el-table-column>
    </el-table>
    <div class="pagination">
      <el-pagination
        style="text-align:right"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        :current-page="pagination.page"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pagination.size"
        layout="total, sizes, prev, pager, next, jumper"
        :total="pagination.total"
      ></el-pagination>
    </div>
  </div>
</template>

<script>
import QzSearchItem from '@/components/search-item.vue';
import ApiCase from '@/api/case';

export default {
  props: ['isCall'],
  name: 'EffectPic',
  methods: {
    handleSizeChange(size) {
      this.pagination.limit = size;
      this.getTableData();
    },
    handleCurrentChange(currentPage) {
      this.pagination.page = currentPage;
      this.getTableData();
    },
    gotoAddEffect() {
      this.$router.push('/add-effect');
    },
    async handleButtonDelete(scope) {
      const res = await ApiCase.caseDel({ id: scope.row.id });
      if (res.data.error_code === 0) {
        this.$message.success('删除成功');
        this.getTableData();
      } else {
        this.$message.error('删除失败');
      }
    },
    async getTableData() {
      const result = await ApiCase.list({
        ...this.search,
        filter: 2,
        page: this.pagination.page,
        // ISSUE: api 分页名称不统一
        perCount: this.pagination.limit,
      });

      const {
        data: { data },
        data: {
          data: { page },
        },
      } = result;

      this.tableData = data.list;

      this.pagination.page = page.page_current;
      this.pagination.total = page.total_number;
    },
    // 网店管理-装修案例-获取设计师下拉菜单
    getemployeedropdowlist() {
      const params = {
        position: 2, //  职位 1客服 2设计师
        state: 2, //  显示状态 1显示在职 显示全部
      };
      ApiCase.getemployeedropdowlist(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            this.designerList = res.data.data; //  获取设计师列表
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
  },
  async created() {
    this.getemployeedropdowlist();
    await this.getTableData();
  },
  data() {
    return {
      designerList: [], //  设计师列表
      search: {
        title: '',
        designer: '',
      },
      pagination: {
        page: 1,
        limit: 10,
        total: 0,
      },
      tableData: [],
    };
  },
  components: {
    QzSearchItem,
  },
};
</script>


<style lang="less">
.effect-pic {
  .app-search {
    .more-button {
      float: right;
    }
  }
  .pagination {
    margin-top: 10px;
    margin-bottom: 80px;
  }
  .el-table__header tr,
  .el-table__header th {
    height: 50px;
    padding: 0;
  }
}
</style>
