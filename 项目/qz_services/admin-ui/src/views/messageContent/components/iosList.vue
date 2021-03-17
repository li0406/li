<template>
  <div class="ios-list">
    <div class="ios clearfix">
        <div class="fl mr20">
            发送时间：<br>
           <el-form>
				<el-form-item prop="search_data">
                    <el-date-picker
                    v-model="search_data.startTime"
                    type="date"
                    placeholder="开始时间">
                    </el-date-picker>
					
					<el-date-picker
					v-model="search_data.endTime"
					type="date"
					placeholder="结束时间">
					</el-date-picker>
				</el-form-item> 
			</el-form>
           
        </div>
        <div class="city fl mr20">
            推送app: <br>
            <el-select v-model="typeVal" placeholder="请选择">
                <el-option
                    v-for="item in typeOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                />
            </el-select>
        </div>
        <div class="city fl mr20">
            推送方式: <br>
            <el-select v-model="pushMode" placeholder="请选择">
                <el-option
                    v-for="item in pushModeOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                />
            </el-select>
        </div>
        <div class="fr">
            <br>
            <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
            <el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>
        </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        class="cell"
      >
        <el-table-column
            v-for="(item,index) in tableHead"
            :key="index"
            align="center"
            :prop="item.prop"
            :label="item.label"
        />
       <!--<el-table-column
          align="center"
          prop="createTime"
          label="消息创建时间"
        />
         <el-table-column
          align="center"
          prop="msgDescribe"
          label="消息描述"
        />
        <el-table-column
          align="center"
          prop="msgContent"
          label="消息内容"
        />
        <el-table-column
          align="center"
          prop="target"
          label="目标人群"
        />
        <el-table-column
          align="center"
          prop="sendTime"
          label="发送时间"
        />
        <el-table-column
          align="center"
          prop="planSend"
          label="计划发送"
        />
        <el-table-column
          align="center"
          prop="actualSend"
          label="实际发送"
        />
        <el-table-column
          align="center"
          prop="arrivNum"
          label="到达数"
        />
        <el-table-column
          align="center"
          prop="arrivRate"
          label="到达率"
        />
        <el-table-column
          align="center"
          prop="openNum"
          label="打开数"
        />
        <el-table-column
          align="center"
          prop="openRate"
          label="打开率"
        /> -->
        <el-table-column
          align="center"
          prop='push_status'
          label="状态"
        >
          <template slot-scope="scope">
            {{ scope.row.push_status === 1 ? '未推送' : '已推送' }}
          </template>
        </el-table-column>
       <el-table-column
          align="center"
          label="操作"
        >
        <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleEdit(scope.row)">编辑</span>
            <span class="link-blue pointer" @click="handleDelete(scope.$index, tableData)">删除</span>
            <span class="link-blue pointer" @click="handleCopy(scope.row)">复用</span>
        </template>
        </el-table-column>
      </el-table>
      <el-pagination
        v-if="paginationShow"
        :current-page.sync="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>

  </div>
</template>

<script>
import { fetchSmsTplExport } from '@/api/smsTpl'
import { getMsglist,fetchMsgEdit,getMsgDel, getExport } from '@/api/projectApply'
import { isEmptyObject } from '@/utils/index'
import { export_json_to_excel } from '@/excel/Export2Excel' //导出
import moment from "moment"
export default {
    name: 'iosList',
    props: {
        systemType: {
            type: String,
            default: '1'
        }
    },
    data() {
        return {
            // 推送APP
            typeVal: '',
            typeOptions: [{
                value: '',
                label: '全部'
            },{
                value:'1',
                label:'齐装APP'
            },{
                value:'2',
                label:'装修说APP'
            }],
            // 推送方式
            pushMode:'',
            pushModeOptions:[{
                value: '',
                label: '请选择'
            },{
                value:'1',
                label:'立即推送'
            },{
                value:'2',
                label:'定时推送'
            }],
            // 系统
            system:"0",
            systemOption:[{
                value: '0',
                label: '请选择'
            },{
                value:'1',
                label:'Ios'
            },{
                value:'2',
                label:'Android '
            }],
            startTime: '', //开始时间
            endTime:'', //结束时间
            msg: '',
            push_status:'', //推送状态 
            page:'',
            loading: false, //加载
            tableData: [],
            currentPage: 1, //当前页
            exportLoading: false, //导出
            totals: 0,//总页数
            pageSize: 20, // 每页显示条数
            paginationShow:true,
            exportData: [],
            tableHead:[
                {'prop':'id','label':'消息ID'},
                {'prop':'created_at','label':'消息创建时间'},
                {'prop':'name','label':'消息描述'},
                {'prop':'body','label':'消息内容'},
                {'prop':'target_groups_name','label':'目标人群'},
                {'prop':'push_time','label':'发送时间'},
                {'prop':'push_num','label':'计划发送'},
                // {'prop':'push_success_num','label':'实际发送'},
                {'prop':'arrive_num','label':'到达数'},
                {'prop':'arrive_lv','label':'到达率'},
                {'prop':'read_num','label':'打开数'},
                {'prop':'read_lv','label':'打开率'},
                // {'prop':'push_status','label':'状态'}
            ],
            list:[],
            // 搜索
            search_data:{
                startTime:'',
                endTime:''
            },
        }
    },
    watch: {
        systemType(val) {
            this.currentPage = 1;
            this.getAppMsglist()
        },
    },
    created() {
        this.id = this.$route.query.id;
        this.getAppMsglist();
    },
    methods: {
        moment,
        getAppMsglist() {
            let startTime = '', endTime = ''
            if(this.search_data.startTime) {
               startTime = moment(this.search_data.startTime).format('YYYY-MM-DD') 
            }

            if(this.search_data.endTime) {
                endTime = moment(this.search_data.endTime).format('YYYY-MM-DD')
            }
           
            this.tableData = [];

            let query = {
                "start_time":startTime,
                "end_time": endTime,
                "system":this.systemType,
                "location":this.typeVal,  
                "type":parseInt(this.pushMode) === 0 ? '' :this.pushMode, 
                'page' : this.currentPage 
            }
            // 消息管理列表
            getMsglist(query).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code)===0){
                    console.log('res',res)
                    if(parseInt(isEmptyObject(res.data.data.list)) > 0){
                        this.tableData = res.data.data.list
                        this.tableData.map((item, index) => {
                            item.push_status = Number(item.push_status) //推送状态
                            let created_at = moment(new Date(item.created_at)*1000).format('YYYY-MM-DD HH:mm:ss'); //创建时间
                            item.created_at = created_at;
                            let push_time =  moment(new Date(item.push_time)*1000).format('YYYY-MM-DD HH:mm:ss'); //发送时间
                            item.push_time = push_time
                            item.arrive_lv = item.arrive_lv + '%' //到达率
                            item.read_lv = item.read_lv + '%'   //打开率
                        })
                        this.totals = res.data.data.page.total_number;
                    }else{
                        if(this.currentPage > 1){
                            this.currentPage--
                            this.getAppMsglist()
                        }
                        this.totals = 0;
                    }
                    this.loading = false
                }else{
                    this.tableData = [];
                    this.totals = 0;
                    this.loading = false;
                } 
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试 ')
                this.loading = false
            })
        },
        // 跳转编辑页
        handleEdit(obj,index) {
            this.$router.push({
                path: 'createMsg',
                query: {
                    id: obj.id
                }
            })
        },
        //复用页面跳转
        handleCopy(obj){
            this.$router.push({
                path: 'createMsg',
                query: {
                    id: obj.id,
                    fy:'fy'
                }
            })
        },
        //删除
        handleDelete(index,rows){
            let id = rows[index].id
            this.$confirm('确定删除该条数据吗?删除后不可恢复', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                getMsgDel({id:id}).then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0){
                        rows.splice(index, 1);
                        this.$message({
                            type: 'success',
                            message: '删除成功!'
                        });
                    }  
                })
            }).catch(() => {
                this.$message({
                type: 'info',
                message: '已取消删除'
                });          
            });
        },
       
       
        //查询
        handleSearch() {
            if(this.search_data.startTime) {
                this.search_data.startTime = moment(this.search_data.startTime).format('YYYY-MM-DD')
            }
            if(this.search_data.endTime) {
               this.search_data.endTime  = moment(this.search_data.endTime).format('YYYY-MM-DD')
            }
            if( (this.search_data.startTime != '' && this.search_data.startTime != null) && (this.search_data.endTime == '' || this.search_data.endTime == null) ){
                alert("请选择结束时间")
                return false
            }
            if( (this.search_data.startTime == '' || this.search_data.startTime == null) && (this.search_data.endTime != '' && this.search_data.endTime != null)){
                alert("请选择开始时间")
                return false
            }
            if( this.search_data.startTime > this.search_data.endTime && this.search_data.endTime < this.search_data.startTime){
                alert("开始时间不能晚于结束时间")
                return false
            }
            this.tableData = [];
            // 调用分页数据
            this.currentPage = 1
            this.getAppMsglist()
        },
        handleArgs() {
            const queryObj = {
                start_time : this.search_data.startTime || '', 
                end_time : this.search_data.endTime || '',
                system : this.system,
                location :this.typeVal, 
                type : parseInt(this.pushMode) === 0 ? '' :this.pushMode 
            }
            return queryObj
        },
        // 导出
        handleExport() {
            const queryObj = this.handleArgs()
            getExport(queryObj).then(res => {
                const blob = new Blob([res.data], {
                    type: 'application/vnd.ms-excel',
                    crossOrigin: 'Anonymous'
                })
                const objectUrl = URL.createObjectURL(blob)
                    window.location.href = objectUrl
                    }).catch(err => {
                    console.log(err)
            })
        },
        handleSizeChange() {
            this.currentPage = 1
            this.getAppMsglist()
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.getAppMsglist()
        }
    }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .ios-list {
    width: 100%;
    // padding: 20px;
    box-sizing: border-box;
    .category{
      margin-bottom: 20px;
    }
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
    .ios {
      background: #fff;
      padding: 20px;
    //   border-top: 3px solid #999;
      box-sizing: border-box;
    }
    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
    }
    .el-pagination{
      text-align: center;
      margin-top: 20px;
    }
    .el-dialog__header{
      border-bottom: 1px dashed #CCC;
    }
    .dia_table{
      width: 100%;
    }
    .dia_table td{
      line-height: 2.5;
    }
    .fix_letter_spance{
      letter-spacing: 3px;
    }
    .fl {
      float: left;
    }
    .mr20 {
      margin-right: 20px;
    }
    .cell{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
  }
</style>
