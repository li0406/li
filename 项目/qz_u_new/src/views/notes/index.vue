<template>
  <div class="notes-contains">
    <el-row class="title">评价管理</el-row>
    <div>
      <div class="tables">
        <el-table stripe :data="tableData" border v-loading="loading">
          <el-table-column prop="name" label="用户名" width="120"></el-table-column>
          <el-table-column prop="text" label="评价详情">
            <template slot-scope="scope">
              <div class="text">{{ scope.row.text}}</div>
            </template>
          </el-table-column>
          <el-table-column prop="time" label="发布时间" width="200">
            <template slot-scope="scope">{{ scope.row.time | transTime }}</template>
          </el-table-column>
          <el-table-column prop="rptxt" label="回复内容">
            <template slot-scope="scope">{{ scope.row.rptxt ? scope.row.rptxt : '------'}}</template>
          </el-table-column>
          <el-table-column fixed="right" label="操作" width="260" align="center">
            <template slot-scope="scope">
              <el-button @click="handleDetails(scope.row.id)" type="text" size="small">查看详情</el-button>
              <el-button @click="handleBack(scope.row)" type="text" size="small">回复</el-button>
              <el-button
                type="text"
                size="small"
                v-if="scope.row.rptxt"
                @click="delTalk(scope.row.id)"
              >删除回复</el-button>
              <el-popover placement="right" width="268" trigger="hover">
                <p style="font-size:12px;">点击推荐之后,此条用户评论会在前台装修公司频道,装修公司列表中显示,如下图所示:</p>
                <img
                  class="popimg"
                  src="//staticqn.qizuang.com/custom/20200717/FjR3JOryY2HDmmpC6D92fqJKHqMe"
                  alt
                  style="width: 240px;"
                />
                <el-button
                  slot="reference"
                  type="text"
                  size="small"
                  v-if="scope.row.recommend == 1"
                  class="recommend"
                  @click="noRecommend(scope.row.id,scope.row.recommend)"
                >
                  推荐
                  <i class="el-icon-warning-outline"></i>
                </el-button>
              </el-popover>
              <el-button
                type="text"
                size="small"
                v-if="scope.row.recommend == 2"
                class="rob"
                @click="noRecommend(scope.row.id,scope.row.recommend)"
              >取消推荐</el-button>
            </template>
          </el-table-column>
        </el-table>
        <div class="pagination">
          <el-pagination
            class="seat"
            :current-page="currentPage"
            :page-sizes="[10, 20, 50, 100]"
            :page-size="pageSize"
            layout="total,prev, pager, next, jumper"
            :total="totals"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>
    </div>
    <el-dialog title="评价详情" :visible.sync="centerDialogVisible" width="600px" center>
      <p class="itemText">{{itemText}}</p>
      <el-carousel :interval="5000" arrow="always" v-if="imgs.length > 0">
        <el-carousel-item v-for="(item, index) in imgs" :key="index">
          <img :src="item" style="height:100%" alt />
        </el-carousel-item>
      </el-carousel>
      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="centerDialogVisible = false">关闭</el-button>
      </span>
    </el-dialog>
    <!-- 回复 -->
    <el-dialog
      :title="'评论回复【'+ back.name+'】'"
      :visible.sync="dialogVisible"
      class="back"
      width="600px"
    >
      <div class="titles">用户评分</div>
      <div class="cons">
        施工水平:
        <span class="el-icon-star-on" v-for="(item, index) in back.sg" :key="index+'A'"></span>
        {{back.sgScore}}分
        <br />设计水平:
        <span class="el-icon-star-on" v-for="(item, index) in back.sj" :key="index+'B'"></span>
        {{back.sjScore}}分
        <br />服务质量:
        <span class="el-icon-star-on" v-for="(item, index) in back.fw" :key="index+'C'"></span>
        {{back.fwScore}}分
        <br />
      </div>
      <div class="titles reply">回复评论</div>
      <el-input type="textarea" :rows="3" placeholder="请输入内容" v-model="textarea"></el-input>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="backEvent">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import apiNotes from '@/api/notes';
import dayjs from 'dayjs';

export default {
  name: 'Notes',
  filters: {
    transTime(val) {
      const day = dayjs(val * 1000).format('YYYY-MM-DD HH:mm:ss');
      return day;
    },
  },
  data() {
    return {
      currentPage: 1,
      totals: 0,
      pageSize: 10,
      loading: false,
      tableData: [],
      limt: 20,
      // 详情
      centerDialogVisible: false,
      itemText: '',
      imgs: [],
      // 回复
      dialogVisible: false,
      textarea: '',
      back: {},
    };
  },
  created() {
    this.getList();
  },
  methods: {
    handleArguments() {
      const queryObj = {
        page: this.currentPage,
      };
      return queryObj;
    },
    getList() {
      const queryObj = this.handleArguments();
      this.loading = true;
      apiNotes.getlist(queryObj).then((res) => {
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
    // 分页处理
    handleSizeChange(size) {
      this.limit = size;
      this.getList();
    },
    handleCurrentChange(val) {
      this.currentPage = val;
      this.getList();
    },
    noRecommend(e, f) {
      const text = f === 1 ? '确定取消推荐此条评论吗?' : '确定推荐此条评论吗?';
      const result = f === 1 ? '已取消推荐' : '已推荐';
      this.$confirm(text, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          apiNotes.recomentup({ id: e }).then((res) => {
            if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
              this.$message({
                message: result,
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
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消操作',
          });
        });
    },
    delTalk(e) {
      this.$confirm('确定删除吗?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          apiNotes.delreply({ id: e }).then((res) => {
            if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
              this.$message({
                message: '已删除',
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
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消操作',
          });
        });
    },
    handleDetails(e) {
      this.itemText = '';
      this.imgs = '';
      this.centerDialogVisible = true;
      apiNotes.commentup({ id: e }).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.itemText = res.data.data.text;
          this.imgs = res.data.data.imgs;
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    handleBack(e) {
      this.back = {
        id: e.id,
        name: e.name,
        fw: Math.ceil(e.fw / 2),
        sg: Math.ceil(e.sg / 2),
        sj: Math.ceil(e.sj / 2),
        fwScore: e.fw,
        sgScore: e.sg,
        sjScore: e.sj,
      };
      this.textarea = e.rptxt;
      this.dialogVisible = true;
    },
    // 待完善
    backEvent() {
      this.dialogVisible = false;
      const textarea = this.textarea;
      const id = this.back.id;
      apiNotes.replyup({ id, content: textarea }).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.getList();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
  },
};
</script>

<style  lang="less">
.notes-contains {
  padding-bottom: 50px;
  background: #fff;
  .title {
    width: 100%;
    height: 60px;
    margin-bottom: 10px;
    padding-left: 30px;
    color: rgba(51, 51, 51, 1);
    font-weight: 400;
    font-size: 16px;
    line-height: 60px;
    border-bottom: 1px solid #f2f2f2;
  }
  .tables {
    padding: 30px;
    .pagination {
      margin-top: 20px;
      margin-bottom: 80px;
      overflow: hidden;
      .seat {
        float: right;
      }
    }
    .popimg {
      display: block;
      width: 240px;
      height: 85px;
    }
    .recommend {
      margin: 0 10px;
    }
    .rob {
      margin-left: 10px;
    }
    .text {
      display: -webkit-box;
      overflow: hidden;
      text-overflow: ellipsis;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
    }
  }
  .itemText {
    font-size: 14px;
    line-height: 24px;
  }
  .el-carousel__item {
    text-align: center;
  }
  .back {
    .titles {
      font-weight: bold;
      font-size: 16px;
      line-height: 32px;
    }
    .cons {
      font-size: 14px;
      line-height: 28px;
      span {
        color: #ffaf52;
        font-size: 18px;
      }
    }
    .reply {
      margin: 20px 0 10px;
    }
  }
}
</style>
