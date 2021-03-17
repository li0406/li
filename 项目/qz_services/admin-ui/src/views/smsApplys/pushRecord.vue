<template>
  <div class="push-record">
    <div class="search clearfix">
       <div class="fl mr20">
        消息内容：<br>
        <el-input v-model="keyword" placeholder="请输入消息内容"></el-input>
      </div>
      <div class="yixiang fl mr20">
        项目应用名称：<br>
        <el-select v-model="projectApplyName" placeholder="请选择">
          <el-option
            v-for="item in projectApplyNameOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="fl mr20">
        推送方式：<br>
        <el-select v-model="send_type" placeholder="请选择">
          <el-option
            v-for="item in send_typeOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="fl mr20">
        推送时间：<br>
         <el-date-picker
          v-model="push_time"
          value-format="yyyy-MM-dd"
          type="date"
          placeholder="选择日期">
        </el-date-picker>
      </div>
     
      <div class="fr">
        <br>
        <el-button type="primary"  @click="handleSearch">查询</el-button>
        <!--<el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>-->
      </div>
    </div>
    <div class="qian-main">
        <el-table
            v-loading="loading"
            :data="newArr"
            border
            class="cell"
        >
            <el-table-column
                align="center"
                prop="id"
                label="消息ID"
            />
            <el-table-column
                align="center"
                prop="content"
                label="消息内容"
            />
            <el-table-column
                align="center"
                prop="type_name"
                label="消息类型"
            />
            <el-table-column
                align="center"
                prop="send_position"
                label="发送位置"
            />
            <el-table-column
                align="center"
                prop="platform_name"
                label="推送平台"
            />
            
            <!-- <el-table-column
                v-for="(item,index) in tableHead" 
                :key="index"
                align="center"
                :prop="item.prop"
                :label="item.label"
            /> -->
            <el-table-column
                align="center"
                prop="receive_app"
                label="接收平台"
            >
            <template slot-scope="scope">
                <span class="cells">
                    <span v-for="(item,index) in scope.row.receive_app" :key='index' class="gateway_item">{{item.name}}</span>
                </span>
            </template>
            </el-table-column>
            <el-table-column
                align="center"
                prop="send_type"
                label="推送方式"
            />
            <el-table-column
                align="center"
                prop="created_at"
                label="提交时间"
            />
            <el-table-column
                align="center"
                prop="operator"
                label="操作人"
            />

        
      </el-table>
        <el-pagination
          :current-page="currentPage"
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
import { getLogList ,fetchApplyList} from '@/api/projectApply'
import { isEmptyObject } from '@/utils/index'
import moment from 'moment'
export default {
  name: 'MemberOrder',
    data() {
        return {
            projectApplyName: '', //项目应用名称
            projectApplyNameOptions: [{
                value: '',
                label: '请选择'
            }],
            // ,{
            //     value: '1',
            //     label: '齐装网'
            // },{
            //     value: '2',
            //     label: '装修说'
            // }
            //推送方式
            send_type: '', 
            send_typeOptions: [{
                value: '',
                label: '请选择'
            },{
                value: '1',
                label: '单发'
            },{
                value: '2',
                label: '群发'
            }],
            //推送时间
            push_time:'',
        
            keyword: '', //搜索关键字
            tableHead:[
                {'prop':'id','label':'消息ID'},
                {'prop':'content','label':'消息内容'},
                {'prop':'type_name','label':'消息类型'},
                {'prop':'send_position','label':'发送位置'},
                {'prop':'platform_name','label':'推送平台'},
                {'prop':'receive_app','label':'接收平台'},
                {'prop':'send_type','label':'推送方式'},
                // {'prop':'user','label':'接收人'},
                {'prop':'created_at','label':'提交时间'},
                {'prop':'operator','label':'操作人'}
            ],
            tableData: [],
            currentPage: 1, // 当前页
            totals: 0, //总条数
            pageSize: 20, //每页20条
            page:'', //页码
            loading: false,
            exportLoading: false,
            exportData: [],
            receive_app:[]
        }
    },
    created() {
        this.getLog();
        this.getApplication();
    },
    computed:{
        newArr(){
            var _newArr = this.tableData;
            if(this.projectApplyNameOptions !== ''){
                var  _newArr = this.tableData.filter(el => el.projectApplyNameOptions == this.projectApplyNameOptionss)
            }
            return  _newArr
        }
    },
   
    methods: {
        moment,
        // 消息记录列表
        getLog(){
            this.loading = true
            this.tableData = []
            const queryObj = this.handleArgs()
            getLogList(queryObj).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code)===0){
                    if(!res.data.data.list && this.currentPage > 1) {
                        this.currentPage--
                        this.getLog()
                    }else{
                        if(res.data.data.list) {
                            this.tableData = res.data.data.list
                            this.tableData.forEach((item, index) => {
                                item.send_type = parseInt(item.send_type) === 1 ? '单发' : '群发' //推送方式
                                item.created_at = moment(new Date(item.created_at)*1000).format('YYYY-MM-DD HH:mm:ss') //提交时间
                                if(item.send_type == '群发'){
                                    item.user = '所有人'
                                }
                                //接收平台
                                if(item.receive_app && item.receive_app instanceof Array){
                                    this.receive_app = item.receive_app.map(items => {
                                        this.receive_app.push(items.name)
                                    })
                                }
                            })
                        }
                        this.totals = res.data.data.page.total_number
                    }
                }else{
                    this.$message.error(res.data.error_msg)
                }
                this.loading = false
            }).catch(res => {
                this.$message.error('网络异常，请稍后重试')
                this.loading = false
            })
        },
        // 请求参数
        handleArgs() {
            const queryObj = {
                push_time:this.push_time, //推送时间
                keyword:  this.keyword, //消息内容查询
                from_app: this.projectApplyName, //项目应用id 需要调取 项目应用接口 获取全部项目应用
                send_type : parseInt(this.send_type) === 0 ? '' :this.send_type, //发送类型（1：单发；2：群发）
                page : this.currentPage 
            }
            return queryObj
        },
        //应用
         getApplication() {
            fetchApplyList().then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    if(res.data.data && res.data.data.list && res.data.data.list.length > 0) {
                        const data = res.data.data.list
                        data.forEach(item => {
                            this.projectApplyNameOptions.push({
                                value: item.id,
                                label: item.name
                            })
                        })
                    }
                }else{
                    this.$message.error(res.data.error_code)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试 ')
            })
        },
        //查询 
        handleSearch() {
            // 调用分页数据
            this.currentPage = 1
            this.getLog()
        },
        //改当前页数
        handleSizeChange(pagesize) {
            console.log(pagesize)
            this.pageSize = pagesize
            this.currentPage = 1
            this.getLog()
        },
        //当前页
        handleCurrentChange(val) {
            this.currentPage = val
            this.getLog()
        }
    }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .push-record {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .category{
      margin-bottom: 20px;
    }
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
    .search {
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
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
    .cells span::after{
        content: '，';
    }
    .cells span:last-child:after{
        display: none;
    }
  }
</style>
