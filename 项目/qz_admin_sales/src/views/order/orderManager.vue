<template>
  <div class="order-content">
    <div class="search-box">
      <h2>订单查询</h2>
      <div class="content-form">
        <el-form ref="formInline" :inline="true" label-width="80px" :model="formInline">
          <div>
            <el-form-item label="开始时间" prop="date_begin">
              <el-date-picker
                v-model="formInline.date_begin"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="发布开始时间"
              />
            </el-form-item>
            <el-form-item label="结束时间" prop="date_end">
              <el-date-picker
                v-model="formInline.date_end"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="发布结束时间"
              />
            </el-form-item>
            <el-form-item label="订单编号" prop="id">
              <el-input v-model="formInline.id" placeholder="请输入订单号" />
            </el-form-item>
            <el-form-item label="预算" prop="yusuan" class="yusuan">
              <el-select
                v-model="formInline.yusuan"
                clearable
                placeholder="请选择预算"
                class="yusuan"
              >
                <el-option value="0" label="请选择预算" />
                <el-option
                  v-for="item in yusuan"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="分/赠送" prop="type_fw">
              <el-select
                v-model="formInline.type_fw"
                clearable
                placeholder="请选择"
                class="typefw"
              >
                <el-option value="0" label="请选择" />
                <el-option
                  v-for="item in type_fw"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </div>
          <div>
            <el-form-item label="装修类型" prop="leixing">
              <el-select
                v-model="formInline.leixing"
                clearable
                placeholder="请选择"
              >
                <el-option value="0" label="请选择" />
                <el-option
                  v-for="item in leixing"
                  :key="item.id"
                  :value="item.id"
                  :label="item.name"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="详细类型" prop="lxs">
              <el-select
                v-model="formInline.lxs"
                clearable
                placeholder="请选择"
              >
                <el-option value="0" label="请选择" />
                <el-option
                  v-for="item in lxsList"
                  :key="item.id"
                  :value="item.id"
                  :label="item.name"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="装修方式" prop="fangshi">
              <el-select
                v-model="formInline.fangshi"
                clearable
                placeholder="请选择"
              >
                <el-option value="0" label="请选择" />
                <el-option
                  v-for="item in fangshi"
                  :key="item.id"
                  :value="item.id"
                  :label="item.name"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="选择城市" prop="cid">
              <el-select
                v-model="formInline.cid"
                clearable
                placeholder="选城市"
                filterable
                @change="getCityHandle(formInline.cid)"
              >
                <el-option value="0" label="请选择" />
                <el-option
                  v-for="item in citys"
                  :key="item.cid"
                  :value="item.cid"
                  :label="item.cname"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="选择区县" prop="area_id">
              <el-select
                v-model="formInline.area_id"
                clearable
                placeholder="选区域"
                filterable
              >
                <el-option value="0" label="请选择" />
                <el-option
                  v-for="item in qx"
                  :key="item.area_id"
                  :value="item.area_id"
                  :label="item.value"
                />
              </el-select>
            </el-form-item>
          </div>
          <div>
            <el-form-item label="排序方式" prop="order_by">
              <el-select
                v-model="formInline.order_by"
                placeholder="请选择"
                clearable
              >
                <el-option value="1" label="按发单时间从近到远" />
                <el-option value="2" label="按会员跟单时间从近到远" />
              </el-select>
            </el-form-item>
            <el-form-item label="是否量房" prop="liangfang_status">
              <el-select
                v-model="formInline.liangfang_status"
                placeholder="请选择"
                clearable
              >
                <el-option value="1" label="已量房" />
                <el-option value="2" label="未量房" />
              </el-select>
            </el-form-item>
            <el-form-item label="跟单小计" prop="track_status">
              <el-select
                v-model="formInline.track_status"
                placeholder="请选择"
                clearable
              >
                <el-option value="1" label="有跟单小计" />
                <el-option value="2" label="无跟单小计" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="searchHandle">查询</el-button>
              <el-button type="default" @click="resetHandle('formInline')">重置</el-button>
              <el-button v-loading="exportLoading" type="success" @click="exportHandle">导出</el-button>
            </el-form-item>
          </div>
        </el-form>
      </div>
    </div>
    <div class="content-box">
      <h2>订单列表</h2>
      <div v-loading="visibleTable" class="content-table">
        <el-table
          :data="tableData"
          border
          style="width: 100%;"
        >
          <el-table-column
            prop="id"
            align="center"
            label="订单编号"
            width="180"
          />
          <el-table-column
            prop="date"
            align="center"
            label="发布日期"
            width="150"
          />
          <el-table-column
            prop="yz_name"
            label="业主名称"
            align="center"
            width="150"
          />
          <el-table-column
            prop="qy_name"
            align="center"
            width="150px"
            label="城市区县"
          />
          <el-table-column
            prop="xiaoqu"
            align="center"
            label="小区名称"
          />
          <el-table-column
            prop="mianji"
            align="center"
            label="面积（㎡）"
          />
          <el-table-column
            prop="yusuan_name"
            align="center"
            label="预算"
          />
          <el-table-column
            prop="leixing_name"
            align="center"
            label="装修类型"
          />
          <el-table-column
            prop="lxs"
            align="center"
            label="详细类型"
          />
          <el-table-column
            prop="fangshi_name"
            align="center"
            label="装修方式"
          />
          <el-table-column
            prop="lf_status_name"
            align="center"
            label="是否量房"
          />
          <el-table-column
            prop="type_fw_name"
            align="center"
            label="分/赠送"
          />
          <el-table-column
            prop="operate"
            align="center"
            width="100"
            label="操作"
          >
            <template slot-scope="scope">
              <el-button type="text" size="small" @click="checkHandle(scope.row.id)">查看<span v-if="scope.row.track_count !== 0" style="color:red;">({{ scope.row.track_count }})</span></el-button>
              <el-button v-if="scope.row.is_new_review===0" type="text" size="small" @click="visitHandle(scope.row.id)">回访</el-button>
              <el-button
                v-if="scope.row.call_repeat_count>0"
                size="small"
                type="text"
                @click="recordHandle(scope.row.id)"
              >呼叫记录({{ scope.row.call_repeat_count }})
              </el-button>
            </template>
          </el-table-column>
        </el-table>
        <el-row type="flex" justify="end" style="padding: 20px 0;">
          <el-pagination
            :current-page="page_current"
            :page-sizes="[10, 20, 30, 40]"
            :page-size="page_size"
            :total="total_number"
            layout="total, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </el-row>
      </div>
    </div>
    <!-- 查看 -->
    <qz-dialog
      :dia-title="'订单编号：' + orderInfo.id"
      :dialog-visible="dialogTableVisible"
      :dia-width="'650px'"
      @diaClose="closeDialog"
    >
      <table v-loading="dialoading" class="dia_table " :class="{'isqiandan':qiandan_status=='1'}">
        <tr>
          <td width="80">发布时间：</td>
          <td width="80"><input class="release-time" type="text" :value="orderInfo.date"></td>
          <td width="80">订单状态：</td>
          <td width="80"><input class="order-status" type="text" :value="orderInfo.type_fw_name"></td>
        </tr>
        <tr>

          <td width="80">业&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;主：</td>
          <td><input type="text" :value="orderInfo.yz_name"></td>
          <td width="80">联系电话：</td>
          <td width="240"><input type="text" :value="orderInfo.tel"></td>
        </tr>
        <tr>
          <td width="80">小区名称：</td>
          <td><input type="text" :value="orderInfo.xiaoqu "></td>
          <td width="80">区&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;域：</td>
          <td><input type="text" :value="orderInfo.qy_name"></td>
        </tr>
        <tr>
          <td width="80">面&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;积：</td>
          <td><input type="text" :value="orderInfo.mianji "></td>
          <td width="80">房屋户型：</td>
          <td width="240"><input type="text" :value=" orderInfo.huxing_name"></td>
        </tr>
        <tr>
          <td width="80">装修类型：</td>
          <td width="240"><input type="text" :value="orderInfo.leixing_name"></td>
          <td width="80">预&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;算：</td>
          <td><input type="text" :value=" orderInfo.yusuan_name"></td>
        </tr>
        <tr>
          <td width="80">风&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;格：</td>
          <td width="240"><input type="text" :value="orderInfo.fengge_name"></td>
        </tr>
        <tr class="request">
          <td colspan="10" style="vertical-align: top; ">装修要求：
            <textarea style="width: 80%; height: 150px; vertical-align: top; border-color: #d7d7d7; background: transparent;" v-html="orderInfo.text"/>
          </td>
        </tr>
        <tr>
          <td width="80" style="vertical-align:top">选择公司：</td>
          <td colspan="3">
            <span v-for="item in orderInfo.companys" :key="item.company_id" style="padding: 0 3px;">{{ item.jc }}</span>
          </td>
        </tr>
      </table>
        <div>
            <p class="qiz">齐装回访：</p>
            <div v-for="item in visit_list" :key='item.visit_date' class="rel">
                <div class="visit">
                    <span class="visit-data">{{item.visit_date}}</span>
                    <span class="visit-step">{{item.visit_step_name}}</span>
                    <span class="visit-company">{{item.company_jc}} </span>
                </div>
                <div class='visit-content' :class="fold ? 'fold' : 'unfold'" v-if="item.visit_content">
                    <div class="visit-content-copallse">{{item.visit_content}}</div>
                    <div class="btn" v-if="item.visit_content.length > 40">
                        <span @click="handleFold">展开</span>
                    </div>
                </div>
            </div>
        </div>
        <div style="padding-top:15px">
            <p class="qiz">装修公司跟进情况：</p>
            <div class="gsTag">
                <span
                    v-for='(item,index) in company_track_list'
                    :key="index"
                    :class='index==idx ? "cur":""'
                    @click='showCompanyHandle(index)'>{{item.company_jc}}</span>
                <div class="hide">
                    <div v-for="(item,index) in company_track_list" :key="index">
                        <div v-if='index == idx' class="overscroll" style="width: 593px">
                            <div v-for="(items,idx) in  item.track_list" :key="idx" class="track-list">
                                    <span>{{items.track_date}}</span>
                                    <span class="step">{{items.track_step_name}}</span>
                                    <div class="track-content"
                                    v-if="items.track_content !== '' ">{{items.track_content}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </qz-dialog>
    <!-- 回访 -->
    <el-dialog title="新增回访工单" :visible.sync="dialogFormVisible" width="600px"  >
        <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="150px">
            <el-form-item label="要求回访的装修公司" prop="company_list">
                <el-checkbox-group v-model="ruleForm.company_list">
                    <el-checkbox
                        v-for="el in company_list"
                        :key="el.company_id"
                        :label="el.company_id"
                        name="acompanys"
                        @change="chooseCompany(el.company_id)"
                        class="companyList">{{el.company_jc}}</el-checkbox>
                </el-checkbox-group>
            </el-form-item>
            <el-form-item label="回访阶段" prop="visit_step_list">
                <el-select v-model="ruleForm.visit_step_list" placeholder="请选择" >
                <el-option value="0" label="请选择" />
                <el-option
                    v-for="i in visit_step_list"
                    :key='i.id'
                    :label="i.name"
                    :value="i.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="需要回访内容" prop="visit_content">
                <el-input type="textarea" v-model="ruleForm.visit_content" :rows="4" class="w360"></el-input>
            </el-form-item>
            <el-form-item label="装企反馈详情" prop="feedback_details">
                <el-input type="textarea" v-model="ruleForm.feedback_details" :rows="4" class="w360"></el-input>
            </el-form-item>
            <el-form-item  class="dialog-footer">
                <el-button type="primary" @click="submitForm('ruleForm')" >提 交</el-button>
                <el-button @click="dialogFormVisible = false">取 消</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
  </div>
</template>

<script>
    import {
        fetchGetFormOptions,
        fetchGetFormCitys,
        fetchGetArea,
        fetchOrderList,
        fetchOrderCheck, // 查看详情
    } from '@/api/orderManage'
    import {getAddOptions,fetchAddVisit} from '@/api/orderList'
    import QzDialog from '@/components/QzDialog'
    import {export_json_to_excel} from '@/excel/Export2Excel'
    export default {
        name: "index",
        components: {
            QzDialog
        },
        created() {
            this.getFormOptions()
            this.getOrderList()
        },
        // 清空表单验证
        watch: {
            dialogFormVisible (val, oldVla) {
                if(this.$refs['ruleForm'] !== undefined) {
                    this.$refs["ruleForm"].resetFields()
                }
            }
        },
        data() {
            return {
                isFload: true,
                fold: true,
                showAllContent: 0,
                id:'', // id
                formInline: {
                    date_begin: '',
                    date_end: '',
                    yusuan: '',
                    type_fw: '', // 分/赠送
                    leixing: '', // 装修类型
                    fangshi: '',
                    lxs: '',
                    cid: '',
                    area_id: '',
                    id: '', // 订单编号
                    liangfang_status: '', // 量房状态（1：已量房；2：未量房）
                    track_status: '', // 跟单小计（1：有；2：无）
                    order_by: '1' // 排序方式（1：发单时间从近到远；2：会员跟单时间从近到远）
                },
                showCompany:'companyOne',
                tableData: [],
                yusuan: [], // 预算
                type_fw: [], // 分/赠单
                leixing: [],
                fangshi: [],
                citys: [],
                qx: [],
                // 分页
                page_current: 1,
                page_size: 0,
                total_number: 0,
                //弹窗

                dialogTableVisible: false,
                dialoading: false,
                orderInfo: {},
                exportLoading: false,
                visibleTable: false,
                export: 0,
                exportData: [],
                // 齐装回访
                visit_list:[
                    //  {'visit_date':'2019-11-12 11:22:33',
                    //  'visit_step_name':'回访阶段',
                    //  'visit_content':'回访内容回访内容回访内容回访内容回访内容回访内回访内容回访内容回访内容回访内容回访内容回访内容回访内容回访内容回访内容回访内容容回访内容回访内容回访内容回访内容',
                    //  'company_jc':'要求回访公司简称'}
                ], //齐装回访
                qiandan_status:'',
                visit_date:'', //回访时间
                visit_step_name:'',//回访阶段
                visit_content:'',//回访内容
                company_jc:'',//装修公司简称
                company_track_list:[],
                idx:0,
                track_list:[], //根据情况
                // 回访
                dialogFormVisible:false,
                ruleForm: {
                    orderid:'', //订单id
                    company_ids:[],
                    company_list: [],   //回访的装修公司
                    visit_step_list:'', //回访阶段
                    visit_content:'',   //回访内容
                    feedback_details:'' //反馈详情
                },
                company_list:[], //多选框装修公司
                lxsList: [
                  { id: 1, name: '新房装修' },
                  { id: 2, name: '整体翻新' },
                  { id: 3, name: '局部改造' }
                ],
                visit_step_list:[], //下拉框回访阶段
                rules:{
                    company_list: [
                        { required: true, message: '请选择要求回访的装修公司名称', trigger: 'change' }
                    ],
                    visit_step_list: [
                        { required: true, message: '请选择回访阶段', trigger: 'change' }
                    ],
                    visit_content: [
                        { required: true, message: '请填写回访内容', trigger: 'blur' }
                    ]
                }
            }
        },

        methods: {

            chooseCompany(val){
              console.log(this.ruleForm.company_list)
            },
            handleFold(e){
                let target = e.target.parentNode.parentNode
                console.log(e.target.innerText)
                if(e.target.innerText == '展开') {
                    target.style.height = 'auto'
                    e.target.innerText = '收起'
                }else{
                    target.style.height = '23px'
                    e.target.innerText = '展开'
                }

                this.fold = !this.fold;
                // if(this.fold) {
                //     this.showAllContent = 1
                // }else{
                //     this.showAllContent = 0
                // }
            },
            showCompanyHandle(index){
                this.idx = index
            },
            //form表单
            getFormOptions() {
                fetchGetFormOptions().then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.yusuan = res.data.data.yusuan;
                        this.type_fw = res.data.data.type_fw;
                        this.leixing = res.data.data.leixing;
                        this.fangshi = res.data.data.fangshi;
                    }
                })
                fetchGetFormCitys().then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.citys = res.data.data[0];
                    }
                })
            },
            getOrderList() {
                let query = this.formInline
                query = Object.assign({}, query, {
                    page: this.page_current,
                    page_size: 20
                })
                this.visibleTable = true
                fetchOrderList(query).then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        if (this.tableData.length <= 0 && this.page_current > 1) {
                            this.page_current--
                            this.getOrderList()
                            this.visibleTable = false
                        } else {
                            this.tableData = []
                            this.tableData = res.data.data.list
                            this.formInline.date_begin = res.data.data.search.time_begin
                            this.formInline.date_end = res.data.data.search.time_end
                            this.page_current = res.data.data.page.page_current
                            this.page_size = res.data.data.page.page_size
                            this.total_number = res.data.data.page.total_number
                            this.visibleTable = false
                        }
                    }else{
                        this.visibleTable = false
                        this.$message.error(res.data.error_msg)
                    }
                })
            },

            // 每页显示多少条数
            handleSizeChange(val) {
            this.page_current = 1
            this.page_size = val
            this.getOrderList()
            },
            // 跳转到第几页
            handleCurrentChange(val) {
            this.page_current = val
            this.getOrderList()
            },
            // 查看详情
            checkHandle(val) {
                this.dialogTableVisible = true
                let query = {id: val}
                fetchOrderCheck(query).then(res => {
                    console.log('--->',res.data.data.detail)
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.orderInfo = res.data.data.detail
                        this.qiandan_status =res.data.data.detail.qiandan_status
                        this.company_track_list = res.data.data.detail.company_track_list
                        this.company_track_list.map(item => {
                            this.track_list = item.track_list
                        })
                        this.visit_list = res.data.data.detail.visit_list; //齐装回访
                    } else {
                        this.$message.warning('未查询到符合要求内容')
                        this.orderInfo = []
                    }
                })
            },
            //回访弹框 装修公司列表 回访阶段
            visitHandle(orderId){
                getAddOptions({order_id:orderId}).then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.ruleForm.orderid =  orderId
                        this.company_list = res.data.data.company_list
                        this.ruleForm.company_ids = res.data.data.company_list.map(item => item.company_id) //要跟进公司id
                        this.visit_step_list = res.data.data.visit_step_list
                        this.dialogFormVisible = true
                    }
                })
            },
            // 参数
            handleArgs(){
                const queryObj = {
                    order_id:this.ruleForm.orderid, //订单id
                    company_ids: [] ,//跟进的装修公司
                    visit_step:this.ruleForm.visit_step_list,  //回访阶段
                    visit_need:this.ruleForm.visit_content , //需要回访内容
                    visit_user_need:this.ruleForm.feedback_details //装企反馈详情
                }
                if(this.ruleForm.company_list  instanceof Array){
                    queryObj.company_ids = this.ruleForm.company_list.join(',')
                }
                return queryObj
            },
            //清空表单
            clearForm(){
                this.ruleForm.orderid = ''
                this.ruleForm.company_ids = ''
                this.ruleForm.visit_step_list = ''
                this.ruleForm.visit_content = ''
                this.ruleForm.feedback_details = ''
            },
            // 回访新增
            ajaxVisit(){
                const query = this.handleArgs();
                fetchAddVisit(query).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.$message({
                            message: '保存成功',
                            type: 'success'
                        })
                        this.dialogFormVisible = false
                        this.clearForm();
                    }else{
                        this.$message.error(res.data.error_msg)
                    }
                }).catch(res => {
                    this.$message.error('网络异常，请稍后再试')
                })
            },
             //回访提交
            submitForm(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.ajaxVisit()
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },


            //关闭弹窗
            closeDialog() {
                this.dialogTableVisible = false
            },
            searchHandle() {
                let beginTime = this.formInline.date_begin
                let endTime = this.formInline.date_end
                if (endTime != '' && beginTime == '') {
                    this.$message.warning('请选择开始时间')
                    return false
                }
                if (beginTime != '' && endTime != '' && beginTime > endTime) {
                    this.$message.warning('开始时间小于结束时间')
                    return false
                }
                this.page_current = 1
                this.getOrderList();
            },
            resetHandle(formName) {
                this.$refs[formName].resetFields();
                this.getOrderList();
            },

            //导出
            exportHandle() {
            this.export = 1
            this.exportLoading = true;
            const tHeader = [
                '订单编号',
                '发布日期',
                '业主姓名',
                '地区',
                '小区名',
                '面积（㎡）',
                '预算',
                '分/赠送',
                '装修类型',
                '详细类型',
                '装修方式',
                '是否量房',
            ]
            // 上面设置Excel的表格第一行的标题
            const filterVal = [
                'id',
                'date',
                'yz_name',
                'qy_name',
                'xiaoqu',
                'mianji',
                'yusuan_name',
                'type_fw_name',
                'leixing_name',
                'lxs',
                'fangshi_name',
                'lf_status_name',
            ]
            this.fetchExportOrderList('export', () => {
                // 上面的index、phone_Num、school_Name是tableData里对象的属性
                const list = this.exportData // 把data里的exportData存到list
                const data = this.formatJson(filterVal, list)
                export_json_to_excel(tHeader, data, '会员签单统计')
                this.exportLoading = false
            })
            },
            formatJson(filterVal, jsonData) {
                return jsonData.map(v => filterVal.map(j => v[j]))
            },
            fetchExportOrderList(val, cb) {
                let query = this.formInline;
                query = Object.assign({}, query, {export: 1})
                fetchOrderList(query).then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) == 0) {
                        if (res.data.data.list.length > 0) {
                            this.exportData = res.data.data.list
                            cb & cb.call()
                        } else {
                            this.$message.warning('未查询到符合要求的数据')
                            this.exportLoading = false
                        }
                    } else {
                        this.$message.error(res.data.error_msg)
                        this.exportData = []
                        this.exportLoading = false
                    }
                })
            },

            //选择城市
            getCityHandle(val) {
                let query = {cid: val}
                fetchGetArea(query).then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.qx = res.data.data[0]
                    } else {
                    this.qx = []
                    this.$message.warning('未查询到符合要求的数据')
                    }
                })
            },
            //呼叫记录
            recordHandle(id) {
                this.$router.push({
                    path: 'voipRecord',
                    query: {
                        orderid: id
                    }
                })
            },
        }
    }

</script>

<style rel="stylesheet/scss" lang="scss">
  .order-content {
    padding: 10px 15px;
    .rel{
        position: relative;
    }
        .search-box h2, .content-box h2 {
            font-size: 16px;
            font-weight: normal;
            color: #666;
        }

        .content-form, .content-table {
            border-top: 3px solid #d2d6de;
            border-radius: 3px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            padding: 5px 15px;
            background: #fff;

        }
        .track-content{
            line-height: 2;
            word-break: break-all;
        }
        .content-form > form, .content-table {
            padding-top: 10px;
        }

        .content-box {
            margin-top: 10px;
        }

        .el-pagination {
            margin: 0 auto;
        }

        .dia_table {
            width: 100%;
        }

        .dia_table td {
            line-height: 2.5;
        }

        .dialog-title span {
            margin-top: 35px;
            font-size: 14px;
        }

        .dia_table input {
            padding: 5px 10px;
            border: 1px solid #d7d7d7;
        }

        .release-time {
            color: green;
        }

        .order-status {
            color: red;
        }

        .el-form-item__content .el-date-editor {
            width: 200px;
            /*width: 195px;*/
        }

        .content-form label {
            text-align: left;
        }

        .yusuan label {
            width: 52px;
        }

        .content-form .el-select {
            width: 200px;
        }
        .isqiandan{
            background: url("~@/assets/dialog_images/order.png") no-repeat;
            background-size: 40%;
            background-position: 345px 175px;
        }
        .qiz{
            margin:0;
            padding-left:3px;
        }
        .visit{
            padding-left:17px;
            width:92%;
            min-height: 30px;
        }
        .visit span{
            padding:5px 0;
            display: inline-block;
        }
        .visit span.visit-data{
            width:25%;
            float: left;
        }
        .visit span.visit-step{
            width:18%;
            text-align: center;
            float: left;
        }
        .visit span.visit-company{
            width:56%;
            line-height: 20px;
        }
        .visit-content{
            padding:0 30px 0 17px;
            height: 23px;
            line-height: 23px;
            overflow: hidden;
            position: relative;
        }
        .overscroll{
            height: 150px;
            overflow-y: auto;
            .step{
                padding-left: 50px;
            }
        }
        .fold{
            -webkit-line-clamp:1;
        }
        .btn {
            position: absolute;
            right: -3px;
            bottom: 1px;
            font-size: 14px;
            line-height: 19px;
            letter-spacing: 2px;
            color: #FFAD41;
            cursor: pointer;
            word-break: break-all;
        }
        .gsTag{
            padding-left: 17px;
        }
        .gsTag > span{
            display: inline-block;
            cursor: pointer;
            padding-left: 17px;
            padding: 5px 10px 0 0;
            color: #0099CC;
        }
        .gsList{
            height: 200px;
            overflow-y: scroll;
        }
        .gsgj{
            width:90%;
            height: 25px;
            display:inline-block;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            // -webkit-line-clamp: 1;
            padding-left: 17px;
            line-height: 25px;
            margin-right: 20px;
            // padding-bottom: 10px;
            border-bottom: 1px solid #ccc;
        }
        .show-all-content{
            height: auto;
        }
        .showHide{
            position: absolute;
            cursor: pointer;
            padding-top: 7px;
        }
        .dialog-footer{
            text-align: center;
            margin-left: -100px;
        }
        .cur{
            color: #333 !important;
        }
        .w300{
            width:300px;
        }
        .w360{
            width:360px;
        }
        .hide .show{
            display: block;
        }
        .box .hide .show .step{
            position: absolute;
            top:0;
            left:200px;
        }
       .companyList{
           width:40%;
           margin-left:0;
           margin-right:20px
       }
       .wrap{
		width:700px;
		display: flex;
		font-size:14px;
		justify-content: space-between;
		margin: 0 auto;
	}
	.fload{
		width:600px;
		height: auto;
		display: -webkit-box;
		word-break: break-all;
		-webkit-box-orient: vertical;
		/* 要显示多少行就改变line-clamp的数据,此处折叠起来显示一行*/
		-webkit-line-clamp: 1;
		overflow: hidden;
		text-overflow: ellipsis;
		margin-right: 40px;
		background-color:#F5F5F5;
	}
	.hide{
		display: -webkit-box;
	}
	.show{
		display: block;
	}
  }
</style>
