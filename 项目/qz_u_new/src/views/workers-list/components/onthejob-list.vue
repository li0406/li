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
      <div>
        <el-button type="danger" @click="addStaff()">添加/绑定员工</el-button>
        <el-button type="danger" @click="designerSort()" plain>设计师排序</el-button>
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
            <div v-if="scope.row.state==1">在职</div>
            <div v-if="scope.row.state==2">离职</div>
          </template>
        </el-table-column>
        <el-table-column prop="created_at"
                         label="入职时间"></el-table-column>

        <el-table-column label="操作"
                         width="200px"
                         fixed="right">
          <template slot-scope="scope">
            <div class="operation"
                 v-if="scope.row.is_tel==1">
              <div>
                <el-link type="primary"
                         :underline="false">
                  <div @click="seeRole(scope.row.account)">查看</div>
                </el-link>
              </div>
              <div>
                <el-link type="primary"
                         :underline="false">
                  <div @click="quitMethods(scope.row.account)">离职</div>
                </el-link>
              </div>
              <div>
                <el-link type="primary"
                         :underline="false">
                  <div @click="resetPassWord(scope.row)">重置密码</div>
                </el-link>
              </div>
            </div>
            <div class="operation"
                 v-if="scope.row.is_tel==2">
              <div>
                <el-link type="danger"
                         :underline="false">
                  <div @click="bindingTel(scope.row.account)">绑定手机号</div>
                </el-link>
              </div>
              <div>
                <el-link type="danger"
                         :underline="false">
                  <div @click="modify(scope.row.account)">修改</div>
                </el-link>
              </div>
              <div>
                <el-link type="info"
                         :underline="false">
                  <div @click="quitMethods(scope.row.account)">离职</div>
                </el-link>
              </div>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- 离职弹窗 -->
      <el-dialog title="确定离职？"
                 :visible.sync="showQuit"
                 width="384px"
                 top="35vh"
                 :close-on-click-modal="false"
                 center>
        <div>
          <div class="info">
            <div class="info-div">
              离职后账号将与公司解绑,无法登陆当前公司后台且无法查看该公司任何数据,请谨慎操作！
            </div>
            <div class="reason-button">
              <el-button type="danger"
                         @click="quitConfirm()">确定</el-button>
              <el-button class="can-btn-sty"
                         @click="quitCancel()">取消</el-button>
            </div>
          </div>
        </div>
      </el-dialog>

      <!-- 重置密码 -->
      <el-dialog title="重置密码"
                 :visible.sync="resetFlag"
                 width="384px"
                 top="35vh"
                 :close-on-click-modal="false">
        <div>
          <div class="reset">
            <div class="reset-div">
              <div>姓名：{{temporaryName}}</div>
              <div class="reset-number">账号：{{temporaryAccount}} </div>
              <div>你确定要重置该账号密码，默认密码为qz123hy456</div>
            </div>
            <div class="reset-button">
              <el-button type="danger"
                         @click="resetConfirm()">确定</el-button>
              <el-button class="can-btn-sty"
                         @click="resetCancel()">取消</el-button>
            </div>
          </div>
        </div>
      </el-dialog>

      <!-- 绑定手机号 -->
      <el-dialog title="绑定手机号"
                 :visible.sync="isShowTelDialog"
                 custom-class="dialog"
                 width="460.8px"
                 :close-on-click-modal="false"
                 top="35vh">
        <div class="telDialog">
          <div class="dialogTitle">当前员工未绑定手机号,无法使用企业子账号相关功能,请为员工添加账号</div>
          <div class="flex">
            <div>手机号</div>
            <div>
              <el-input v-model="bindingTelNum"
                        maxlength="11"
                        oninput="value=value.replace(/[^\d]/g,'')"
                        class="bindingTelNumsty"
                        placeholder="请输入需要绑定的员工手机号"></el-input>
            </div>
          </div>
          <div class="flex">
            <div>验证码</div>
            <div>
              <el-input v-model="bindingCode"
                        maxlength="6"
                        oninput="value=value.replace(/[\W]/g,'')"
                        placeholder="请输入验证码"></el-input>
            </div>
            <div>
              <el-link type="danger"
                       v-if="btnTitle"
                       @click="testTel()"
                       :underline="false"
                       :disabled="disabled"
                       v-preventReClick>{{btnTitle}}</el-link>
            </div>
          </div>
          <div class="determine-btn">
            <el-button type="danger"
                       class="btn"
                       @click="save()">确 定</el-button>
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
import alter from '@/api/alter';
import api from '@/api/staff';
import dayjs from 'dayjs';
// eslint-disable-next-line no-unused-vars
import preventReClick from '@/utils/prevent-click';

export default {
  props: ['activeName'],
  watch: {
    activeName (val) {
      if (val === 'onthejob') {
        this.getAccountlist();
      }
    },
  },
  components: {
    QzEmpty: () => import('@/components/empty.vue'), // 没列表数据
  },
  data () {
    return {
      btnTitle: '获取验证码',
      disabled: false,
      totalTime: 60, //  倒数时间
      //  绑定手机号
      bindAccNum: '',//  原账号
      bindingTelNum: '',//  绑定手机号码
      bindingCode: '',// 绑定验证码
      isShowTelDialog: false,// 绑定手机号弹窗 默认关闭
      //  离职
      temporaryName: '',// 姓名
      temporaryAccount: '',//  手机号
      // 头部检索
      accountName: '',
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
        // }
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
      //  重置密码弹窗
      resetFlag: false, // 默认无
    };
  },
  methods: {
    modify (account) {// 修改用户信息
      this.$router.push({
        path: '/editMessage',
        query: {
          account
        }
      })    },
    btnClick () {
      const vm = this;
      // eslint-disable-next-line radix
      alter
        .geetestapi1({
          client_type: 'web',
          scene: 2,
          timestamp: dayjs().valueOf(),
        })
        .then((res) => {
          // eslint-disable-next-line no-undef
          initGeetest({
          ...res.data,
          offline: !res.data.success,
          product: 'bind',
          https: false
          }, (captchaObj) => {
            captchaObj.reset();
            captchaObj
              .onReady(() => {
                captchaObj.verify();
              })
              .onSuccess(() => {
                const validate = captchaObj.getValidate();
                alter
                  .geetestapi2({
                    geetest_challenge: validate.geetest_challenge,
                    geetest_validate: validate.geetest_validate,
                    geetest_seccode: validate.geetest_seccode,
                    scene: 2,
                    client_type: 'web',
                    t: new Date().getTime(),
                  })
                  .then((response) => {
                    // eslint-disable-next-line eqeqeq
                    if (response.data.status == 'success') {
                      // vm.validateBtn();
                      alter
                        .getSendCode({
                          phone: vm.bindingTelNum,
                          type: 3,
                          geetest_validate: validate.geetest_validate,
                          temp_id: 202005111037,
                        })
                        // eslint-disable-next-line no-shadow
                        .then((res) => {
                          // eslint-disable-next-line radix
                          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                            vm.$message({
                              message: '短信验证码已发送',
                              type: 'success',
                            });
                            vm.totalTime=60
                            vm.validateBtn();
                          } else {
                            vm.$message({
                              message: res.data.error_msg,
                              type: 'warning',
                            });
                          }
                        });
                    }
                  });
              });
          });
        });
    },
    validateBtn () {
      const timer = setInterval(() => {
        if (this.totalTime === 0) {
          clearInterval(timer);
          this.disabled = false;
          this.btnTitle = '获取验证码';
        } else {
          this.btnTitle = `${this.totalTime}秒后重试`;
          this.disabled = true;
          // eslint-disable-next-line no-plusplus
          this.totalTime--;
        }
      }, 1000);
    },
    //  点击绑定员工
    bindingTel (account) {
      this.totalTime=0
      this.eliminateDialogTel()// 切换绑定员工的时候 先清除弹出框数据
      this.isShowTelDialog = true
      this.bindAccNum = account// 再赋值
    },
    //  手机号码验证
    testTel () {
      if (!this.bindingTelNum) {
        this.$message.error('请输入手机号');
        return;
      }
      const params = {
        tel: this.bindingTelNum,
      };
      api
        .accountcheck(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            // this.$message({
            //   message: '提交成功！',
            //   type: 'success',
            // });
            this.btnClick()
          } else {
            //  失败
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    save () {
      if (!this.bindingCode) {
        this.$message({
          message: '请输入验证码',
          type: 'warning',
        });
        return;
      }
      this.loading = true;
      alter
        .check({
          phone: this.bindingTelNum,
          type: 3,
          action: 'unbind',
          code: this.bindingCode,
        })
        // eslint-disable-next-line no-shadow
        .then((res) => {
          // eslint-disable-next-line radix
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.loading = false;
            this.bindingTelSub()
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'warning',
            });
          }
        });
    },
    //  点击确定
    bindingTelSub () {
      // console.log(this.bindingTelNum, this.bindAccNum)
      const params = {
        account: this.bindAccNum,
        tel: this.bindingTelNum
      }
      api.telup(params).then((res) => {
        if (res.data.error_code === 0) {
          // 0成功 其余失败
          this.$message({
            message: '绑定成功！',
            type: 'success',
          });

          this.eliminateDialogTel()// 绑定成功清空弹窗数据
          this.getAccountlist()// 刷新列表
          this.isShowTelDialog = false//  绑定成功关闭弹窗
        } else {
          this.$message.error(res.data.error_msg);
        }
      })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  清除绑定员工弹窗数据
    eliminateDialogTel () {
      this.bindingTelNum = ''
      this.bindAccNum = ''
      this.bindingCode = ''
    },
    //  设计师排序
    designerSort () {
      this.$router.push({
        path: '/designerSort',
      });
    },
    seeRole (account) {
      this.$router.push({
        path: '/staff',
        query: {
          account,
        },
      });
    },
    //  获取在职员工列表
    getAccountlist () {
      this.loading = true;
      const params = {
        name: this.accountName, //  账户名称/昵称
        position: this.jobName, // 员工职位  1客服 2设计师 3首席设计师 4设计总监
        state: 1, // 是否在职 1在职 2离职
      };
      api
        .accountlist(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.loading = false;
            this.tableData = res.data.data.list;
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  员工入职离职
    accountstateup (account) {
      const params = {
        account,
      };
      api
        .accountstateup(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.$message({
              message: '离职成功！',
              type: 'success',
            });
            this.getAccountlist();
            this.showQuit = false;
          } else {
            this.$message.error(res.data.error_msg);
            this.showQuit = true;
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    resetpwd (account) {
      const params = {
        account,
      };
      api
        .resetpwd(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.$message({
              message: '重置密码成功！',
              type: 'success',
            });
            this.resetFlag = false;
          } else {
            this.$message.error(res.data.error_msg);
            this.resetFlag = true;
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  添加绑定员工
    addStaff () {
      this.$router.push('/staff-index');
    },
    // 离职弹窗处理
    quitMethods (account) {
      this.showQuit = true;
      this.temporaryAccount = account;
    },
    // 离职弹窗
    //  确定
    quitConfirm () {
      this.showQuit = false;
      this.accountstateup(this.temporaryAccount);
    },
    //  取消
    quitCancel () {
      this.showQuit = false;
      this.temporaryAccount = '';
    },
    //  重置密码
    //  确定
    resetConfirm () {
      this.resetFlag = false;
      this.resetpwd(this.temporaryAccount);
    },
    //  取消
    resetCancel () {
      this.resetFlag = false;
      this.temporaryAccount = '';
    },
    resetPassWord (account) {
      // 重置密码
      this.resetFlag = true;
      this.temporaryAccount = account.account;
      this.temporaryName = account.nick_name;
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
  created () {
    this.getAccountlist();
  },
};
</script>

<style lang="less" scoped>
/* 公用弹性盒子样式 */
.flex {
  display: flex;
}
.flex > div {
  margin: 0 0 0 20px;
}
/* 绑定手机号 */
.telDialog {
  margin: -50px 0 0 0;
}
.dialogTitle {
  margin: 0 0 20px 0;
  color: #aaa;
  line-height: 23px;
}
.bindingTelNumsty {
  width: 280px;
}
.determine-btn {
  margin-top: 20px;
  text-align: center;
}
.btn {
  width: 360px;
}
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
/* 重置密码弹窗 */
.reset {
  margin: -40px 0 0 0;
  text-align: left;
}
.reset-div {
  line-height: 30px;
}
.reset-button {
  text-align: right;
}
.reset-number {
  margin: 0 0 20px 0;
  border-bottom: 1px solid;
}
.reset-button > button {
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
