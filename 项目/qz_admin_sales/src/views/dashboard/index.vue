<template>
  <div class="content">
    <div class="search-box">
      <h2>已分配订单</h2><span>（显示近7天内由稽核部操作&lt;=4家会员城市的已分配订单）</span>
    </div>
    <div class="content-form">
       <el-table
        :data="tableData"
        stripe
        style="width: 100%">
        <el-table-column
          prop="date_real"
          label="发布日期"
          width="180">
        </el-table-column>
        <el-table-column
          prop="xiaoqu"
          label="小区名称"
          width="180">
        </el-table-column>
        <el-table-column
          prop="qy_name"
          label="城市区县">
        </el-table-column>
        <el-table-column
          prop="name"
          width="100"
          label="业主姓名">
        </el-table-column>
        <el-table-column
          prop="leixing_name"
          width="90"
          label="公装/家装">
        </el-table-column>
        <el-table-column
          prop="yusuan_name"
          width="90"
          label="预算">
        </el-table-column>
        <el-table-column
          prop="mianji"
          width="90"
          label="面积㎡">
        </el-table-column>
        <el-table-column
          prop="lftime"
          label="量房时间">
        </el-table-column>
        <el-table-column
          prop="type_fw_name"
          width="90"
          label="订单状态">
        </el-table-column>
        <el-table-column
          prop="allot_num"
          label="分配上限"
           width="90"
          >
        </el-table-column>
        <el-table-column
          prop="already_allot_num"
          label="已分配会员数">
        </el-table-column>
        <el-table-column
          prop="date"
          label="订单分配时间">
        </el-table-column>
        <el-table-column
          label="操作"
           width="90"
        >
           <template slot-scope="scope">
            <el-button @click="handleClick(scope.row)" type="text" size="small">查看</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-row type="flex" justify="end" style="padding: 20px 0;">
        <el-pagination
          :current-page="page.page"
          :page-sizes="[10, 20, 30, 40]"
          :page-size="page.page_size"
          :total="page.total_number"
          layout="total, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"/>
      </el-row>
    </div>
   <qz-dialog 
    :dialog-visible="dialogInfo.visiable"
    :dia-title="dialogInfo.title+orderInfo.id"
    @diaClose="closeDialog"
    :dia-width="'650px'"
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
          <td width="80">选择公司：</td>
          <td colspan="3">{{choseCompany}}</td>
        </tr>
      </table>
   </qz-dialog>
  </div>
</template>
<script>
import {fetcheAuditorder} from '@/api/home'
import {fetchOrderCheck} from '@/api/orderManage'
import QzDialog from '@/components/QzDialog'
export default {
  name: 'Dashboard',
  components: {
    QzDialog
  },
  data() {
    return {
      tableData: [],
      page:{
        total_number:1,
        page_total_number:10,
        page_size:10,
        page:1
      },
      dialoading: false,
      qiandan_status:'',
      dialogInfo:{
        visiable:false,
        title:"订单编号："
      },
      orderInfo:{},
      choseCompany:""
    }
  },
  created() {
    this.getOrders()
  },
  methods: {
    handleClick (data) {
      fetchOrderCheck({
        id:data.id
      }).then(res=>{
        if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0){
          this.dialogInfo.visiable = true
          this.orderInfo = res.data.data.detail
          let newStr = []
          for(let i in res.data.data.detail.companys){
            newStr.push(res.data.data.detail.companys[i].jc)
          }
          this.choseCompany = newStr.join("，")
        }
      })
    },
    getOrders(){
      fetcheAuditorder(this.page).then(res=>{
        if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0){
          if(this.tableData.length <= 0 && this.page_current > 1){
            this.page_current--;
            this.getOrders();
          }else{
            this.tableData = res.data.data.list
            this.page.page = res.data.data.page.page_current
            this.page.total_number = res.data.data.page.total_number
            this.page.page_total_number = res.data.data.page.page_total_number
            this.page.page_size = res.data.data.page.page_size
          }
        }
      })
    },
    closeDialog(){
      this.dialogInfo.visiable = false
    },
    handleSizeChange(val){
      this.prmas.page_size = val
      this.page.page = 1
      this.getOrders()
    },
    handleCurrentChange(val){
      this.page.page = val
      this.getOrders()
    }
  },
 
}
</script>
<style scoped>
   .content {
    padding: 10px 15px;
  }
  .search-box{
      color: #666;
      margin:10px 0px;
  }
  .search-box h2{
    font-size: 16px;
    display: inline;
  }

  .content-form, .content-table {
    border-top: 3px solid #d2d6de;
    border-radius: 3px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    padding: 5px 15px;
    background: #fff;

  }

</style>
