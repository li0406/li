// 装修案例

<template>
  <div class="decoration-case">
    <div class="search">
      <div class="flex space-between min-width">
        <div>
          <qz-search-item class="qz-search-item" label="发布时间" style="margin-right: 16px;">
            <el-date-picker
              v-model="dateRange"
              value-format="yyyy/MM/dd"
              format="yyyy/MM/dd"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
            ></el-date-picker>
          </qz-search-item>
          <qz-search-item class="qz-search-item" label="案例类型" style="margin-right: 16px;">
            <!-- 所有查询添加全部 -->
            <el-select v-model="searchValue.classid" placeholder="请选择案例类型">
              <el-option key="0" label="全部" value></el-option>
              <el-option
                v-for="item in option.classid"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              ></el-option>
            </el-select>
          </qz-search-item>
          <qz-search-item class="qz-search-item" label="装修风格" style="margin-right: 16px;">
            <el-select v-model="searchValue.fengge" placeholder="请选择装修风格">
              <el-option key="0" label="全部" value></el-option>
              <el-option
                v-for="item in option.fengge"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              ></el-option>
            </el-select>
          </qz-search-item>
        </div>
        <div class="flex">
          <div>
            <div class="show-bul" v-if="showBul" @click="changeBul()">展开</div>
            <div class="show-bul" v-else @click="changeBul()">收起</div>
          </div>
          <div>
            <el-button type="danger" @click="getTableData">查询</el-button>
            <el-button type="danger" plain @click="reset">重置</el-button>
            <el-button type="danger" @click="gotoAddCase">+添加案例</el-button>
          </div>
        </div>
      </div>
      <div :class="[{'none':showBul},{'block':!showBul}]">
        <qz-search-item class="qz-search-item" label="小区名称" style="margin-right: 16px;">
          <el-input v-model="searchValue.title" placeholder="小区名称"></el-input>
        </qz-search-item>

        <qz-search-item class="qz-search-item" label="设计师" style="margin-right: 16px;">
          <el-select v-model="searchValue.designer" placeholder="请选择设计师">
            <el-option key="0" label="全部" value></el-option>
            <el-option
              v-for="item in designerList"
              :key="item.id"
              :label="item.nick_name"
              :value="item.id"
            ></el-option>
          </el-select>
        </qz-search-item>

        <qz-search-item class="qz-search-item" label="状态" style="margin-right: 16px;">
          <el-select v-model="searchValue.status" placeholder="请选择状态">
            <el-option key="0" label="全部" value></el-option>
            <el-option
              v-for="item in option.status"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            ></el-option>
          </el-select>
        </qz-search-item>

        <qz-search-item class="qz-search-item" label="户型" style="margin-right: 16px;">
          <el-select v-model="searchValue.huxing" placeholder="请选择户型">
            <el-option key="0" label="全部" value></el-option>
            <el-option
              v-for="item in option.huxing"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            ></el-option>
          </el-select>
        </qz-search-item>
      </div>
    </div>
    <el-table stripe :data="tableData" border v-loading="tableLoading">
      <el-table-column prop="cover_image" label="案例图片">
        <template slot-scope="scope">
          <img :src="scope.row.cover_image" alt="pic" style="width: 60px;height:60px" />
        </template>
      </el-table-column>
      <el-table-column prop="title" label="小区名称"></el-table-column>
      <el-table-column prop="huxing" label="户型结构"></el-table-column>
      <!-- FIXME:房屋类型是哪个字段 -->
      <el-table-column prop="leixing" label="房屋类型"></el-table-column>
      <el-table-column prop="mianji" label="建筑面积(㎡)"></el-table-column>
      <el-table-column prop="fengge" label="风格"></el-table-column>
      <el-table-column prop="dname" label="设计师"></el-table-column>
      <el-table-column prop="jiage" label="造价"></el-table-column>
      <!-- <el-table-column prop="on_name" label="状态"></el-table-column> -->
      <el-table-column prop="status_name" label="状态"></el-table-column>
      <el-table-column prop="classid" label="案例类型">
        <template slot-scope="scope">
          <qz-table-cell :option="{1: '家装案例',2:'公装案例',3:'在建工地'}" :select="scope.row.classid"></qz-table-cell>
        </template>
      </el-table-column>
      <el-table-column prop="time" label="发布时间"></el-table-column>
      <el-table-column prop="url" label="操作" fixed="right">
        <template slot-scope="scope">
          <a :href="scope.row.url" target="_blank" style="margin-right: 16px;">查看</a>
          <el-button type="text" @click="handleButtonUpdate(scope)" style="margin-right: 16px;">修改</el-button>
          <el-popconfirm title="您确定删除该装修案例吗？" @onConfirm="handleButtonDelete(scope)">
            <el-button slot="reference" type="text">删除</el-button>
          </el-popconfirm>
        </template>
      </el-table-column>
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
import QzTableCell from '@/components/table-cell.vue';

export default {
  name: 'DecorationCase',
  computed: {
    threeMonthsAgo: () => {
      const d = new Date();
      const begin = `${d.getFullYear()}/${d.getMonth() - 2}/${d.getDate()}`;
      const end = `${d.getFullYear()}/${d.getMonth() + 1}/${d.getDate()}`;
      return [begin, end];
    },
  },
  async created() {
    await this.getOptionSearchParams();
    await this.getOptionFengGe();

    await this.getTableData();
    await this.getemployeedropdowlist();
    this.dateRange = this.threeMonthsAgo;
  },
  data() {
    return {
      showBul: true,
      tableData: [],
      option: {
        classid: [], // 案例类型
        // on: [], // 状态
        status: [
          { id: '1', name: '未审核' },
          { id: '2', name: '已审核' },
          { id: '3', name: '审核不通过' },
        ], // 状态
        fengge: [], // 装修风格
        huxing: [
          { id: '10', name: '普通户型' },
          { id: '11', name: '跃层户型' },
          { id: '12', name: '复式户型' },
          { id: '14', name: '别墅' },
          { id: '15', name: '其它' },
        ], // 户型
      },
      designerList: [], //  设计师列表
      dateRange: [], //  时间 默认前三个月
      searchValue: {
        designer: '', //  设计师
        title: '', // 小区名称
        classid: '', // 案例类型
        // on: '', // 状态
        status: '', // 状态
        fengge: '', // 装修风格
        huxing: '', // 户型
      },
      pagination: {
        page: 1,
        limit: 10,
        total: 0,
      },
      tableLoading: false,
    };
  },
  components: { QzSearchItem, QzTableCell },
  methods: {
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
    //  展开 收起
    changeBul() {
      this.showBul = !this.showBul;
    },
    // 案例类型、状态
    async getOptionSearchParams() {
      const result = await ApiCase.searchParams();

      const { class: classId } = result.data.data;

      this.option.classid = Object.keys(classId).map((value) => ({
        id: value,
        name: classId[value],
      }));

      // this.option.on = Object.keys(on).map((value) => ({
      //   id: value,
      //   name: on[value],
      // }));

      // 状态选项由前台写死
      // this.option.status = Object.keys(status).map((value) => ({
      //   id: value,
      //   name: status[value],
      // }));
    },
    // 风格
    async getOptionFengGe() {
      const result = await ApiCase.fengGe();

      this.option.fengge = result.data.data;
    },
    async getTableData() {
      this.tableLoading = true;
      const result = await ApiCase.list({
        ...this.searchValue,
        begin: this.dateRange[0],
        end: this.dateRange[1],
        filter: this.searchValue.classid === '' ? 1 : 3,
        page: this.pagination.page,
        // ISSUE: api 分页名称不统一
        perCount: this.pagination.limit,
      });
      if (result.data.error_code === 0) {
        this.tableLoading = false;
        const {
          data: { data },
          data: {
            data: { page },
          },
        } = result;

        this.tableData = data.list;

        this.pagination.page = page.page_current;
        this.pagination.total = page.total_number;
      } else {
        this.$message.error(result.data.error_msg);
        this.exportData = [];
        this.exportLoading = false;
      }
    },
    //  重置
    reset() {
      this.dateRange = this.threeMonthsAgo;
      this.searchValue = {
        designer: '', //  设计师
        title: '', // 小区名称
        classid: '', // 案例类型
        // on: '', // 状态
        status: '', // 状态
        fengge: '', // 装修风格
        huxing: '', // 户型
      };
      this.pagination = {
        page: 1,
        limit: 10,
        total: 0,
      };
    },
    handleButtonUpdate(scope) {
      this.$router.push(`/add-case/${scope.row.id}`);
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
    handleSizeChange(size) {
      this.pagination.limit = size;
      this.getTableData();
    },
    handleCurrentChange(currentPage) {
      this.pagination.page = currentPage;
      this.getTableData();
    },
    gotoAddCase() {
      this.$router.push('/add-case/new');
    },
  },
};
</script>

<style lang="less">
.decoration-case {
  .search {
    margin-bottom: 10px;
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
  .flex {
    display: flex;
    flex-wrap: wrap;
  }
  .space-between {
    justify-content: space-between;
  }
  .show-bul {
    margin-right: 20px;
    font-size: 14px;
    cursor: pointer;
  }
  .block {
    display: block;
  }
  .none {
    display: none;
  }
}
</style>
