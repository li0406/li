<template>
  <div class="order-round">
    <div class="title">申请补轮</div>
    <div class="search">
      <el-form
        label-position="left"
        class="formBox"
        label-width="86px"
        ref="ruleForm"
        :model="ruleForm"
      >
        <el-form-item label="订单编号:" prop="order_id" class="mf mw360">
          <el-input
            v-model="ruleForm.order_id"
            oninput="value=value.replace(/^\.+|[^\d.]/g,'')"
            placeholder="请输入订单编号"
            clearable
            @blur="saveOrderId"
          ></el-input>
        </el-form-item>
        <el-form-item label="小区名称:" prop="xiaoqu" class="mf mw360">
          <el-input v-model="ruleForm.xiaoqu" class="inline-input" placeholder="请输入小区名称"></el-input>
        </el-form-item>

        <el-form-item label="审核状态:" prop="type" class="mf mw360">
          <el-select v-model="ruleForm.type" placeholder="请选择" clearable>
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item class="mr mw300">
          <el-button type="danger" @click="submitForm('ruleForm')">查询</el-button>
          <el-button type="danger" plain @click="resetForm('ruleForm')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="hint">
      <el-row class="header">
        有效项目扣费定义:
        <i class="el-icon-info" @click="show = !show"></i>
        <div class="unwind" @click="show = !show">
          <div v-if="!show">
            展开
            <i class="el-icon-arrow-down"></i>
          </div>
          <div v-if="show">
            收起
            <i class="el-icon-arrow-up"></i>
          </div>
        </div>
      </el-row>
      <transition name="fade">
        <p v-if="show" class="message">
          1、 自派出项目的75天内，至少一家装修公司与业主见面或量房，证明业主有装修需求，为有效单正常扣费；
          <br />2、因装修公司自身原因（如联系不及时，口头报价等）造成未能见面，正常扣费；
          <br />注明：若对派出项目有疑义，请提交 “申请补轮” 申请，提交申请期限为项目发布5天后及75天内，逾期提交拒不受理补轮操作。如有问题，请联系城市运营人员。
        </p>
      </transition>
    </div>
    <div class="tables">
      <el-table stripe :data="tableData" border v-loading="loading">
        <template v-slot:empty>
          <div style="margin:32px">
            <qz-empty></qz-empty>
            <p>暂无补轮订单</p>
          </div>
        </template>
        <el-table-column prop="order_id" label="订单编号"></el-table-column>
        <el-table-column prop="yz_name" label="业主"></el-table-column>
        <el-table-column prop="area_name" label="所在区域"></el-table-column>
        <el-table-column prop="xiaoqu" label="小区名称"></el-table-column>
        <el-table-column prop="mianji" label="建筑面积（㎡）"></el-table-column>
        <el-table-column prop="yusuan_name" label="装修预算"></el-table-column>
        <el-table-column prop="round_money" label="轮单费（元）"></el-table-column>
        <el-table-column label="审核状态">
          <template slot-scope="scope" v-if="scope.row.exam_status_name">
            <div
              :class="['aud-sta-sty',{'sta-red':(scope.row.exam_status==1)},{'sta-blue':(scope.row.exam_status==3)}]"
            >
              <div>{{scope.row.exam_status_name}}</div>
              <el-tooltip
                :content="scope.row.exam_remark"
                placement="top"
                effect="light"
                v-if="scope.row.exam_status==3"
              >
                <div class="small-icon">?</div>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="allot_date" label="发布时间"></el-table-column>
        <el-table-column prop="status_name" label="项目状态"></el-table-column>
        <el-table-column label="操作" width="200px" fixed="right">
          <template slot-scope="scope">
            <div class="point sta-red" v-if="scope.row.exam_status==1 || scope.row.exam_status==11" @click="tworevoke(scope.row.order_id)">撤消申请</div>
            <el-button v-else-if="scope.row.exam_status==2" disabled>申请补轮</el-button>
            <el-button type="danger" v-else @click="popHandle(scope.row.order_id)">申请补轮</el-button>
          </template>
        </el-table-column>
      </el-table>
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
    </div>
    <el-dialog
      :close-on-click-modal="false"
      :title="(check_ret==1)?statusTitle:''"
      :visible.sync="dialogVisible"
      width="30%"
      custom-class="dialog"
      :before-close="handleClose"
    >
      <!--订单不符合条件check_ret-->
      <div v-if="check_ret == 2">
        <div class="info">
          <div class="info-div">
            <i class="el-icon-warning icon-i"></i>
            <div class="info-div2">
              <div>{{tip_title}}</div>
              <div>{{tip_content}}</div>
            </div>
          </div>
          <el-button type="danger" @click="dialogVisible = false">确 定</el-button>
        </div>
      </div>
      <!--订单符合条件-->
      <div v-if="check_ret == 1">
        <div class="reason">
          <div
            :class="[{'sel-off':apply_reason!=applyReason.id},{'sel-on':apply_reason==applyReason.id}]"
            size="small"
            v-for="applyReason of applyReasonList"
            :key="applyReason.id"
            @click="addApplyReasonId(applyReason.id)"
          >{{applyReason.name}}</div>
        </div>
        <div class="reason-textarea">
          <textarea cols="30" rows="10" placeholder="请填写备注" v-model="apply_remark"></textarea>
        </div>
        <div class="reason-button">
          <el-button type="danger" @click="mendingWheel()">确认提交</el-button>
          <el-button class="can-btn-sty" @click="handleClose">取消</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import apiOrder from '@/api/order'; // 接口引入

export default {
  name: 'Round',
  data() {
    return {
      //  订单管理-申请补轮-列表(刚进入申请补轮列表页或点击查询)
      //  入参
      ruleForm: {
        order_id: '', // 订单编号
        type: '', // 审核状态（1：确认中；2：已通过；3：未通过）如12按钮置灰
        xiaoqu: '', // 小区名称
      },
      pageSize: 10, //  每页显示条数
      currentPage: 1, // 当前页
      //  返回参数
      WheelMendingList: [], //  补轮列表

      //  订单管理-申请补轮-申请补轮检查（点击申请补轮校验订单是否满足补轮条件）
      //  入参
      repairOrderId: '', // 点击申请补轮之后传入的订单编号
      //  返回参数
      check_ret: '', //  检查结果（1：可申请；2：不可申请）
      //  1：可申请
      applyReasonList: [], // 申请原因列表
      //  2：不可申请
      tip_title: '', //  提示标题
      tip_content: '', // 提示内容

      //  订单管理-申请补轮-申请补轮
      //  入参同上repairOrderId
      apply_reason: '', //  申请原因
      apply_remark: '', //  申请备注
      //  返回参数是否成功

      show: false,

      options: [
        {
          value: '',
          label: '全部',
        },
        {
          value: '1',
          label: '核实中',
        },
        {
          value: '2',
          label: '已通过',
        },
        {
          value: '3',
          label: '未通过',
        },
      ],
      tableData: [],
      adminAccount: [],
      limit: 20,
      totals: 0,
      loading: false,
      dialogVisible: false,
      status: '',
      statusTitle: '请选择申请补轮原因',
    };
  },
  components: {
    QzEmpty: () => import('@/components/empty.vue'), // 没列表数据
  },
  methods: {
    tworevoke(id){
      this.$confirm('您的补轮申请即将撤消，您确定需要撤消吗？', '撤消申请', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.revoke(id)
      }).catch(() => {

      });
    },
    // 订单管理-申请补轮-撤消
    revoke(id){
      const params = {
        order_id:id
      }
      apiOrder.roundApplyCancel(params).then((res) => {
          if (parseInt(res.data.error_code, 10) === 0) {
            this.getList()
            this.$message({
              message: '订单撤消成功',
              type: 'success'
            });
          } else {
            this.loading = false;
            this.$message({
              type: 'error',
              message: res.data.error_msg,
              center: true,
              offset: 200,
            });
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  订单管理-申请补轮-列表
    getList() {
      const params = {
        order_id: this.ruleForm.order_id, // 订单编号
        xiaoqu: this.ruleForm.xiaoqu, // 小区名称
        exam_status: this.ruleForm.type, // 审核状态（1：确认中；2：已通过；3：未通过）
        page: this.currentPage, //  分页页码
      };
      this.loading = true;
      apiOrder
        .roundApplyList(params)
        .then((res) => {
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
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    // 搜索小区
    fetchAccounts() {
      this.adminAccount = [];
    },
    submitForm() {
      this.currentPage = 1;
      this.getList();
    },
    resetForm() {
      this.ruleForm.order_id=''
      this.ruleForm.xiaoqu=''
      this.ruleForm.type=''
      this.getList();
      // this.$refs[formName].resetFields();
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
    // 点击申请补轮
    popHandle(repairOrderId) {
      //  传入用户点击的订单编号
      this.repairOrderId = repairOrderId;
      this.repairInspect(); // 检查
    },
    repairInspect() {
      //  补轮检查接口
      const params = {
        order_id: this.repairOrderId, // 用户点击的订单ID
      };
      apiOrder
        .roundApplyCheck(params)
        .then((res) => {
          //  检查结果（1：可申请；2：不可申请）
          if (res.data.error_code === 0) {
            //  请求成功
            this.dialogVisible = true; //  成功弹窗

            this.check_ret = res.data.data.check_ret; // 补轮检查结果（1：可申请；2：不可申请）

            if (res.data.data.check_ret === 1) {
              //  可申请
              this.applyReasonList = res.data.data.apply_reason_list; // 申请原因列表
              this.tip_title = ''; //  置空提示标题
              this.tip_content = ''; //  置空提示内容
            } else {
              //  不可申请
              this.applyReasonList = []; //  置空申请原因列表
              this.tip_title = res.data.data.tip_title; // 提示标题
              this.tip_content = res.data.data.tip_content; // 提示内容
            }
          } else {
            //  失败
            this.dialogVisible = false; // 接口调用失败不弹窗
            this.$message.error(res.data.error_msg);
          }
        })
        .catch(() => {
        });
    },
    handleClose() {
      this.dialogVisible = false;
      this.clearStyPar();
    },
    clearStyPar() {
      //  清除选中样式和参数
      this.repairOrderId = ''; // 清除用户点击的订单id
      this.apply_reason = ''; //  申请原因
      this.apply_remark = ''; //  申请备注
    },
    addApplyReasonId(ReasonId) {
      // 点击补轮原因
      this.apply_reason = ReasonId; // 添加补轮原因id
    },
    //  订单管理-申请补轮-申请补轮
    mendingWheel() {
      if (this.apply_reason === '') {
        this.$message.error('提交失败，请选择申请补轮原因');
        return;
      }
      if (this.apply_remark === '' && this.apply_reason === 99) {
        this.$message.error('提交失败，请填写申请备注');
        return;
      }
      const params = {
        order_id: this.repairOrderId, // 用户点击的订单id
        apply_reason: this.apply_reason, //  申请原因id
        apply_remark: this.apply_remark, // 申请备注
      };
      apiOrder
        .roundApplySubmit(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.dialogVisible = false; // 补轮成功弹窗关闭
            this.$message({
              message: '提交成功！',
              type: 'success',
            });
            this.clearStyPar();
            this.getList(); // 申请补轮成功刷新列表页
          } else {
            //  失败
            this.dialogVisible = true; // 补轮失败弹窗不关闭
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    saveOrderId(e) {
      this.ruleForm.order_id = e.target.value; //  失去焦点将输入框的值赋给接口入参
    },
  },
  created() {
    if(this.$route.query.order_id){
      this.ruleForm.order_id=this.$route.query.order_id
    }
    this.getList();
  },
};
</script>

<style lang="less">
.order-round {
  background: #fff;
  .el-table__header tr,
  .el-table__header th {
    height: 50px;
    padding: 0;
  }
  .title {
    width: 100%;
    height: 60px;
    padding-left: 30px;
    color: #303133;
    font-weight: 500;
    font-size: 16px;
    line-height: 60px;
    border-bottom: 2px solid #e4e7ed;
  }
  .search {
    box-sizing: border-box;
    min-width: 1200px;
    height: 80px;
    margin-right: 30px;
    margin-left: 30px;
    padding-top: 20px;
    border-bottom: 1px solid #f2f6fc;
    .mf {
      float: left;
    }
    .mr {
      float: right;
    }
    .mw360 {
      width: 300px;
      margin-right: 20px;
    }
  }
  .fade-enter-active {
    transition: all 0.5s linear;
  }
  .fade-leave-active {
    transition: all 0.5s linear;
  }
  .fade-enter {
    transform: translateY(-80%);
    opacity: 0;
  }
  .fade-leave-to {
    transform: translateY(-50%);
    opacity: 0;
  }
  .hint {
    margin-top: 10px;
    .header {
      position: relative;
      height: 40px;
      padding-left: 30px;
      color: rgba(48, 49, 51, 1);
      font-weight: 400;
      font-size: 12px;
      line-height: 40px;
      .el-icon-info {
        position: absolute;
        top: 10px;
        left: 135px;
        font-size: 18px;
        cursor: pointer;
      }
      .unwind {
        position: absolute;
        top: 0;
        right: 30px;
        height: 40px;
        line-height: 40px;
        cursor: pointer;
      }
    }
    .message {
      padding-left: 30px;
      color: rgba(144, 147, 153, 1);
      font-weight: 400;
      font-size: 12px;
      line-height: 26px;
    }
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
  }
  .dialog {
    min-width: 400px;
  }
  //  弹窗样式
  //  不满足5天
  .icon-i {
    width: 24.5px;
    height: 24px;
    margin-right: 30px;
    color: #e94747;
    font-size: 48px;
    text-align: center;
  }
  .info {
    text-align: center;
  }
  .info-div {
    display: flex;
    justify-content: center;
  }
  .info-div2 {
    text-align: left;
  }
  .info-div2 > div:nth-of-type(1) {
    margin-bottom: 10px;
    color: #303133;
    font-weight: bold;
    font-size: 22px;
  }
  .info-div2 > div:nth-of-type(2) {
    margin: 10px 0 30px 0;
    color: #909399;
    font-size: 12px;
  }
  //  符合条件
  .reason > div {
    float: left;
    box-sizing: border-box;
    height: 30px;
    margin: 0 20px 10px 0;
    padding: 0 10px;
    line-height: 30px;
    border-radius: 5px;
    cursor: pointer;
  }
  //  单选 选择申请补轮原因
  .sel-off {
    color: #6e7073;
    background-color: #f5f7fa;
  }
  .sel-on {
    color: #e94848;
    background-color: #ffeced;
    border: 1px solid #ed6a6a;
  }
  //  备注
  .reason-textarea > textarea {
    width: 100%;
    height: 100%;
    margin: 5px 0 10px 0;
  }
  //  补轮 确定 取消按钮
  .reason-button {
    text-align: center;
  }
  .reason-button > button {
    width: 100px;
  }
  //  申请补轮按钮样式
  .repair-sty-btn {
    color: #fff;
    background-color: #e41f2b;
  }
  // 取消按钮样式覆盖
  .can-btn-sty {
    color: #606266;
    border: 1px solid #dcdfe6;
  }
  .can-btn-sty:hover {
    color: #f56c6c;
    background-color: rgba(255, 239, 239, 1);
    border: 1px solid #f56c6c;
  }
  //  审核状态样式
  .aud-sta-sty {
    display: flex;
    align-items: center;
  }
  .small-icon {
    width: 15px;
    height: 15px;
    margin: 0 0 0 5px;
    line-height: 15px;
    text-align: center;
    border: 1px solid;
    border-radius: 50%;
  }
  //  状态颜色
  .sta-red {
    color: #e94747;
  }
  .sta-blue {
    color: #1989fa;
  }
  .point{
    cursor: pointer;
  }
  .col666{
    color: #666;
  }
}
</style>
