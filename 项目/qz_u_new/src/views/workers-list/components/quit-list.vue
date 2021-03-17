<!--
 * @Author: your name
 * @Date: 2020-06-22 14:41:16
 * @LastEditTime: 2020-07-07 17:55:54
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_u_new\src\views\workers-list\components\position-list.vue
-->
<template>
  <div class="outline">
    <!-- 检索 -->
    <div class="retrieval">
      <div>
        <div>
          <el-input v-model="accountName"
                    placeholder="姓名"></el-input>
        </div>
        <div>
          <el-select v-model="jobName"
                     placeholder="请选择职位">
            <el-option v-for="job of jobList"
                       :key="job.value"
                       :label="job.label"
                       :value="job.value"></el-option>
          </el-select>
        </div>
        <div>
          <el-button type="danger"
                     @click="getAccountlist()">搜索</el-button>
        </div>
      </div>
    </div>
    <!-- 检索 -->

    <!-- 表格 -->
    <div>
      <el-table :data="tableData"
                border
                v-loading="loading">
        <!-- 无数据 -->
        <template v-slot:empty>
          <div style="margin:32px">
            <qz-empty></qz-empty>
            <p>当前暂无数据</p>
          </div>
        </template>
        <!-- 无数据 -->
        <el-table-column label="头像">
          <template slot-scope="scope">
            <div class="head-style">
              <img v-if="scope.row.logo"
                   :src="scope.row.logo" />
            </div>
          </template>
        </el-table-column>
        <!-- <el-table-column prop="accountNumberID" label="账号ID"></el-table-column> -->
        <!-- <el-table-column prop="accountNumberName" label="账号名称"></el-table-column> -->
        <el-table-column prop="nick_name"
                         label="姓名"></el-table-column>
        <el-table-column prop="position"
                         label="职位"></el-table-column>
        <el-table-column label="任职状态">
          <template slot-scope="scope">
            <div v-if="scope.row.state == 1">在职</div>
            <div v-if="scope.row.state == 2">离职</div>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at"
                         label="离职时间"></el-table-column>
        <el-table-column label="操作"
                         width="200px"
                         fixed="right">
          <template slot-scope="scope">
            <div class="operation">
              <div>
                <el-link type="primary"
                         :underline="false">
                  <div @click="entry(scope.row)">入职</div>
                </el-link>
              </div>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- 离职弹窗 -->
      <el-dialog title="确定离职？"
                 :visible.sync="showQuit"
                 width="20%"
                 top="35vh"
                 :close-on-click-modal="false"
                 center>
        <div>
          <div class="info">
            <div class="info-div">离职后账号将与公司解绑,无法登陆当前公司后台且无法查看该公司任何数据,请谨慎操作！</div>
            <div class="reason-button">
              <el-button type="danger"
                         @click="quitConfirm()">确定</el-button>
              <el-button class="can-btn-sty"
                         @click="quitCancel()">取消</el-button>
            </div>
          </div>
        </div>
      </el-dialog>

      <!-- 取消推荐弹窗 -->
      <el-dialog title="确定取消推荐？"
                 :visible.sync="shove"
                 width="20%"
                 top="35vh"
                 :close-on-click-modal="false"
                 center>
        <div>
          <div class="info">
            <div class="info-div">确定取消推荐后，该设计师将不会在您的网店首页展示。</div>
            <div class="reason-button">
              <el-button type="danger"
                         @click="shoveConfirm()">确定</el-button>
              <el-button class="can-btn-sty"
                         @click="shoveCancel()">取消</el-button>
            </div>
          </div>
        </div>
      </el-dialog>

      <!-- 分页 -->
      <!--
      <div class="pagination" v-if="tableData!=''">
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
      -->
      <!-- 分页 -->
    </div>
    <!-- 表格 -->
  </div>
</template>

<script>
import api from '../../../api/staff';

export default {
  props: ['activeName'],
  watch: {
    activeName (val) {
      if (val === 'quit') {
        this.getAccountlist();
      }
    },
  },
  components: {
    QzEmpty: () => import('@/components/empty.vue'), // 没列表数据
  },
  data () {
    return {
      // 头部检索
      accountName: '',
      //  点击入职将label 职位转化为数字
      roleList: [
        {
          value: '1',
          label: '客服',
        },
        {
          value: '2',
          label: '设计师',
        },
        {
          value: '3',
          label: '首席设计师',
        },
        {
          value: '4',
          label: '设计总监',
        },
      ],
      //  点击入职 将工作经验 转化为数字
      works: [
        {
          value: '1',
          label: '应届',
        },
        {
          value: '2',
          label: '1年',
        },
        {
          value: '3',
          label: '2年',
        },
        {
          value: '4',
          label: '3-5年',
        },
        {
          value: '5',
          label: '5-8年',
        },
        {
          value: '6',
          label: '8-10年',
        },
        {
          value: '7',
          label: '10年以上',
        },
      ],
      jobList: [
        {
          value: '1',
          label: '客服',
        },
        {
          value: '2',
          label: '设计师',
        },
        {
          value: '3',
          label: '首席设计师',
        },
        {
          value: '4',
          label: '设计总监',
        },
      ],
      jobName: '',

      // 表格数据 在职列表
      tableData: [
        // {
        //   headPortrait: '../../../assets/logo.png',
        //   accountNumberID: '495069',
        //   accountNumberName: 'qz_sjszh00001',
        //   name: '张三',
        //   job: '设计师',
        //   jobState: '在职',
        //   createTime: '2020-05-28 14:20',
        // },
        // {
        //   headPortrait: '../../../assets/logo.png',
        //   accountNumberID: '495069',
        //   accountNumberName: 'qz_sjszh00001',
        //   name: '张三',
        //   job: '设计师',
        //   jobState: '在职',
        //   createTime: '2020-05-28 14:20',
        // },
        // {
        //   headPortrait: '../../../assets/logo.png',
        //   accountNumberID: '495069',
        //   accountNumberName: 'qz_sjszh00001',
        //   name: '张三',
        //   job: '设计师',
        //   jobState: '在职',
        //   createTime: '2020-05-28 14:20',
        // },
        // {
        //   headPortrait: '../../../assets/logo.png',
        //   accountNumberID: '495069',
        //   accountNumberName: 'qz_sjszh00001',
        //   name: '张三',
        //   job: '设计师',
        //   jobState: '在职',
        //   createTime: '2020-05-28 14:20',
        // },
        // {
        //   headPortrait: '../../../assets/logo.png',
        //   accountNumberID: '495069',
        //   accountNumberName: 'qz_sjszh00001',
        //   name: '张三',
        //   job: '设计师',
        //   jobState: '在职',
        //   createTime: '2020-05-28 14:20',
        // },
        // {
        //   headPortrait: '../../../assets/logo.png',
        //   accountNumberID: '495069',
        //   accountNumberName: 'qz_sjszh00001',
        //   name: '张三',
        //   job: '设计师',
        //   jobState: '在职',
        //   createTime: '2020-05-28 14:20',
        // },
      ],

      //  分页
      pageSize: 10, //  每页显示条数
      currentPage: 1, // 当前页
      totals: 0, //  总页数
      limit: 0, // 每页显示几条

      //  列表缓冲效果
      loading: false, // 默认无缓冲效果

      // 离职弹窗
      showQuit: false, // 默认无
      //  取消推荐弹窗
      shove: false, // 默认无
    };
  },
  methods: {
    //  点击入职
    entry (entryMsg) {
      this.$locSet('bul', 1);
      this.GetAccountInfo(entryMsg.account); // 获取账号信息
      this.getRolelist(entryMsg.account); // 获取角色信息
    },
    //  获取账号信息
    GetAccountInfo (account) {
      const params = {
        account,
      };
      api
        .accountinfo(params)
        .then((res) => {
          this.setAllData(res.data.data);
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  获取角色列表
    getRolelist (account) {
      const params = {
        account,
      };
      api
        .rolelist(params)
        .then((res) => {
          const list = res.data.data;
          const selRoleId = [];
          const selRoleList = [];
          // console.log(list);
          list.forEach((item) => {
            item.child.forEach((obj) => {
              if (obj.is_checked === 1) {
                selRoleList.push(obj);
              }
            });
          });
          selRoleList.forEach((selObj) => {
            selRoleId.push(selObj.id);
          });

          this.$locSet('setThird', {
            selRoleId,
            selRoleList,
          });
          this.$router.push({
            path: '/set-third', // 跳转路由
          });
          // console.log(selRoleId, selRoleList);
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  存入所有步骤的数据
    setAllData (data) {
      // console.log(data);
      //  步骤1
      let role = '';
      this.roleList.forEach((roleItem) => {
        // 转化
        if (roleItem.label === data.position) {
          role = roleItem.value;
        }
      });
      this.$locSet('setFirst', {
        tel: data.account, // 电话号码
        role, //  职位
      });
      //  步骤2
      let experience = '';
      this.works.forEach((experienceItem) => {
        // 转化
        if (experienceItem.label === data.experience) {
          experience = experienceItem.value;
        }
      });
      const account = {
        logo: data.logo, //  头像
        nick_name: data.nick_name, //  名字
        // position: this.ruleForm.jobs, //  职位
        experience, //  工作经验
        account: data.account,
        profile: data.profile, // 个人简介
        honor: data.honor, // 荣誉奖励
      };
      if (data.charge !== undefined) {
        this.$locSet('setFourth', {
          account,
          designer: {
            charge: data.charge, // 设计收费
            fengge: data.fengge, // 风格
            lingyu: data.lingyu, // 领域
            works: data.works, // 代表作品
            concept: data.concept, // 设计理念
          },
        });
      } else {
        this.$locSet('setFourth', {
          account,
        });
      }

      // console.log(this.$locGet('setFourth'));
    },
    //  获取在职员工列表
    getAccountlist () {
      const params = {
        name: this.accountName, //  账户名称/昵称
        position: this.jobName, // 员工职位  1客服 2设计师 3首席设计师 4设计总监
        state: 2, // 是否在职 1在职 2离职
      };
      api
        .accountlist(params)
        .then((res) => {
          this.tableData = res.data.data.list;
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    // 离职弹窗处理
    quitMethods () {
      this.showQuit = true;
    },
    // 离职弹窗
    //  确定
    quitConfirm () {
      this.showQuit = false;
    },
    //  取消
    quitCancel () {
      this.showQuit = false;
    },
    //  取消推荐弹窗
    //  确定
    shoveConfirm () {
      this.shove = false;
    },
    //  取消
    shoveCancel () {
      this.shove = false;
    },
    shoveMethods (flag) {
      // 推荐
      if (flag) {
        this.$message({
          message: '恭喜你，这是一条成功消息',
          type: 'success',
        });
      } else {
        //  取消推荐
        this.shove = true;
      }
    },
    // 分页处理
    handleSizeChange (size) {
      //  切换每页显示数量触发
      this.limit = size;
      // console.log(size)
    },
    handleCurrentChange (val) {
      //  切换当前页触发
      this.currentPage = val;
      // console.log(val)
    },
  },
};
</script>

<style lang="less" scoped>
/* 检索样式 */
.retrieval {
  display: flex;
}
.retrieval > div:nth-of-type(1) {
  display: flex;
  width: 50%;
}
.retrieval > div:nth-of-type(1) > div {
  margin: 0 20px 0 0;
}
.retrieval > div:nth-of-type(2) {
  width: 50%;
  padding: 0 20px 0 0;
  text-align: right;
}

/* 表格样式 */
/* 头像 */
.head-style {
  text-align: center;
}
.head-style > img {
  width: 50px;
  height: 50px;
  text-align: center;
  vertical-align: middle;
  border-radius: 50%;
}
/* 操作样式 查看 离职 首页推荐 */
.operation {
  display: flex;
}
.operation > div {
  margin: 0 10px 0 0;
}
.operation div {
  min-width: 40px;
  text-align: center;
}

/* 分页样式 */
.pagination {
  float: right;
  margin-top: 10px;
  margin-bottom: 80px;
}
/* 离职弹窗 */
.info {
  margin: -40px 0 0 0;
  text-align: center;
}
.info-div {
  padding: 0 40px;
  line-height: 20px;
}
.reason-button {
  text-align: center;
}
.reason-button > button {
  width: 100px;
}
/* 取消按钮样式覆盖 */
.can-btn-sty {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.can-btn-sty:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
.outline {
  margin-bottom: 120px;
}
</style>
<style lang="less">
.outline {
  .el-table__header tr,
  .el-table__header th {
    height: 50px;
    padding: 0;
  }
}
</style>
