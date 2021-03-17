<template>
  <div class="order-visit">
    <div class="search-box">
      <h2>订单查询</h2>
      <div class='content-form'>
        <el-form :inline="true" ref="formInline" label-width="85px" :model="formInline">
          <div>
            <el-form-item label="选择城市" prop="cid">
              <el-select
                v-model="formInline.cid"
                placeholder="选城市"
                filterable
              >
                <el-option value="0" label="请选择"/>
                <el-option
                  v-for="item in citys"
                  :key="item.cid"
                  :value="item.cid"
                  :label="item.cname"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="回访类型" prop="visit_type">
              <el-select
                v-model="formInline.visit_type"
                placeholder="请选择"
              >
                <el-option
                  v-for="item in visit_type"
                  :key="item.id"
                  :value="item.id"
                  :label="item.name"
                />
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="订单状态" prop="type_fw" class="yusuan">
              <el-select
                v-model="formInline.type_fw"
                placeholder="请选择"
                class="yusuan"
              >
                <el-option value="0" label="请选择"/>
                <el-option v-for="item in type_fw"
                           :label="item.name"
                           :value="item.id"
                           :key="item.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="开始时间" prop="date_begin">
              <el-date-picker
                v-model="formInline.date_begin"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="发布开始时间"/>
            </el-form-item>
            <el-form-item label="结束时间" prop="date_end">
              <el-date-picker
                v-model="formInline.date_end"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="发布结束时间"/>
            </el-form-item>
            <el-form-item label="回访阶段" prop="visit_step">
              <el-select
                v-model="formInline.visit_step"
                placeholder="请选择"
                class="typefw"
                @change="visitStep"
              >
                <el-option value="0" label="请选择"/>
                <el-option v-for="item in visit_step"
                           :key="item.id"
                           :label="item.name"
                           :value="item.id"/>
              </el-select>
            </el-form-item>
            <el-form-item label="订单编号" prop="order_id">
              <el-input v-model="formInline.order_id" placeholder="请输入订单号"></el-input>
            </el-form-item>
            <el-form-item label="回访状态" prop="valid_status">
              <el-select
                v-model="formInline.valid_status"
                placeholder="请选择"
              >
                <el-option value="" label="请选择"/>
                <el-option
                  v-for="item in valid_status"
                  :key="item.id"
                  :value="item.id"
                  :label="item.name"
                ></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="是否已推送" prop="visit_push">
              <el-select
                v-model="formInline.visit_push"
                placeholder="请选择"
              >
                <el-option value="0" label="请选择"/>
                <el-option
                  v-for="item in visit_push"
                  :key="item.id"
                  :value="item.id"
                  :label="item.name"
                ></el-option>
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="searchHandle">查询</el-button>
              <el-button type="success" @click="exportHandle" v-loading="exportLoading">导出</el-button>
            </el-form-item>
          </div>
          <div>
          </div>
        </el-form>
      </div>
    </div>
    <div class="content-box">
      <h2>回访订单列表</h2>
      <div class="content-table" v-loading="visibleTable">
        <el-table
          :data="tableData"
          border
          style="width: 100%;"
          :row-class-name="tableRowClassName"
        >
          <el-table-column
            prop="date_real"
            align="center"
            label="发布日期">
          </el-table-column>

          <el-table-column
            prop="remark_type_name"
            align="center"
            label="回访备注">
          </el-table-column>

          <el-table-column
            prop="xiaoqu"
            label="小区名称"
            align="center">
          </el-table-column>
          <el-table-column
            prop="city_name"
            align="center"
            label="城市区县">
          </el-table-column>
          <el-table-column
            prop="yz_name"
            align="center"
            label="业主姓名">
          </el-table-column>
          <el-table-column
            prop="leixing_name"
            align="center"
            label="公装/家装">
          </el-table-column>
          <el-table-column
            prop="yusuan_name"
            align="center"
            label="预算">
          </el-table-column>
          <el-table-column
            prop="mianji"
            align="center"
            label="面积（㎡）">
          </el-table-column>
          <el-table-column
            prop="type_fw_name"
            align="center"
            label="订单状态">
          </el-table-column>
          <el-table-column
            prop="order_lf_name"
            align="center"
            label="量房状态">

          </el-table-column>
          <el-table-column
            prop="visit_type_name"
            align="center"
            label="回访类型">
          </el-table-column>
          <el-table-column
            prop="visit_step_name"
            align="center"
            label="回访阶段">
          </el-table-column>
          <el-table-column
            prop="visit_date"
            align="center"
            label="回访时间">
          </el-table-column>
          <el-table-column
            align="center"
            label="回访状态">
            <template slot-scope="scope">
              <span>{{scope.row.valid_status == 1 ? '已回访' : '未回访'}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="visit_push"
            align="center"
            label="是否已推送">
            <template slot-scope="scope">
              {{scope.row.visit_push === 1 ? '未推送' : '已推送' }}
            </template>
          </el-table-column>
          <el-table-column
            prop="created_date"
            align="center"
            width="100"
            label="创建时间">
          </el-table-column>
          <el-table-column
            prop="operate"
            align="center"
            width="100"
            label="操作">
            <template slot-scope="scope">
              <el-button type="text" size="small" @click="editHandle(scope.row)">编辑</el-button>
              <el-button type="text" size="small" @click="deleteHandle(scope.$index,tableData)">
                {{scope.row.visit_status === 1 ? '撤回' : '' }}
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
            @current-change="handleCurrentChange"/>
        </el-row>
      </div>
    </div>
    <qz-dialog :dia-title="'订单编号：' + orderInfo.id" :dialog-visible="dialogTableVisible" :dia-width="'650px'"
               @diaClose="closeDialog">
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
            <textarea
              style="width: 80%; height: 150px; vertical-align: top; border-color: #d7d7d7; background: transparent;"
              v-html="orderInfo.text"/>
          </td>
        </tr>
        <tr>
          <td width="80">选择公司：</td>
          <td colspan="3">{{orderInfo.company_name}}</td>
        </tr>
      </table>
    </qz-dialog>
    <!-- 编辑弹框 -->
    <dialogVisit :dialog='dialog' :orderinfos='orderinfos' :details='details'/>
  </div>

</template>

<script>
import {
  fetchGetFormCitys
} from '@/api/orderManage'
import {
  getVisitList,
  getVisitOptions,
  fetchVisitPush,
  getVisitDetails,
  fetchVisitDelete,
  getVisitExport
} from '@/api/orderList'

import QzDialog from '@/components/QzDialog'
import {export_json_to_excel} from '@/excel/Export2Excel'
import dialogVisit from './components/dialogVisit'

export default {
  name: 'index',
  components: {
    dialogVisit,
    QzDialog
  },
  created() {
    this.formInline.order_id = this.$route.query.order_id
    this.getVisitOrderList()
    this.visitStep()
    this.getCityList()
  },
  data() {
    return {
      formInline: {
        cid: '',
        order_id: '',    // 订单号
        type_fw: '',     // 订单状态（分单/赠单）
        visit_step: '',  // 回访阶段
        visit_type: '2',  // 回访类型 （1：被动；2：主动）
        valid_status: '1', // 回访状态（1：有效；2：无效） yapi上显示为有效字段、1125需求变更
        visit_push: '',   // 是否已推送（1：未推送；2：已推送）
        date_begin: '',
        date_end: ''
      },
      tableData: [],
      type_fw: [
        {"id": '1', 'name': '分单'},
        {"id": '2', 'name': '赠单'}
      ], // 分/赠单
      visit_step: [],
      visit_type: [
        {"id": '1', 'name': '被动'},
        {"id": '2', 'name': '主动'}
      ],
      valid_status: [
        { 'id': '1', 'name': '已回访' },
        { 'id': '2', 'name': '未回访' }
      ],
      visit_push: [
        {"id": '1', 'name': '未推送'},
        {"id": '2', 'name': '已推送'}
      ],
      // 分页
      page: '', // 当前页
      page_current: 1,
      page_size: 0,
      total_number: 0,
      order_id: '', // url中订单id
      // 弹窗
      dialog: {
        show: false
      }, // 模态框
      dialogTableVisible: false,
      dialoading: false,
      orderInfo: {},
      exportLoading: false,
      visibleTable: false,
      export: 0,
      exportData: [],
      qiandan_status: '',
      details: {
        visit_companys: [], // 要求回访的装修公司
      },
      orderinfos: {},
      citys: []
    }
  },
  methods: {
    getVisitOrderList() {
      const beginTime = this.formInline.date_begin
      const endTime = this.formInline.date_end
      if (!beginTime && endTime) {
        this.$message.warning('请选择开始时间')
        return false
      }
      if (beginTime && !endTime) {
        this.$message.warning('请选择结束时间')
        return false
      }
      if (beginTime !== '' && endTime !== '' && beginTime > endTime) {
        this.$message.warning('结束时间必须大于开始时间')
        return false
      }
      let query = {
        cs: this.formInline.cid,
        order_id: this.formInline.order_id,
        type_fw: this.formInline.type_fw, // 订单状态
        visit_step: this.formInline.visit_step,// 回访阶段
        visit_type: this.formInline.visit_type, // 回访类型
        valid_status: this.formInline.valid_status,// 回访状态
        visit_push: this.formInline.visit_push, // 是否已推送
        date_begin: this.formInline.date_begin, // 开始时间
        date_end: this.formInline.date_end, // 结束时间
        page: this.page_current // 当前页
      }
      getVisitList(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (this.tableData.length <= 0 && this.page_current > 1) {
            this.page_current--
            this.getVisitOrderList()
          } else {
            this.tableData = []
            this.tableData = res.data.data.list
            if (res.data.data.list.length > 0 && res.data.data.list[0].visit_type) {
              this.formInline.visit_type = String(res.data.data.list[0].visit_type)
            } else {
              this.formInline.visit_type = '2'
            }
            this.page_current = res.data.data.page.page_current
            this.page_size = res.data.data.page.page_size
            this.total_number = res.data.data.page.total_number
          }
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 回访阶段
    visitStep() {
      getVisitOptions().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.visit_step = res.data.data.visit_step_list
        } else {
          this.$message.warning('未查询到符合要求内容')
        }
      })
    },
    getCityList() {
      fetchGetFormCitys().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.citys = res.data.data[0];
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
      this.getVisitOrderList()
    },
    //
    // checkHandle(val) {
    //     this.dialogTableVisible = true
    //     let query = {id: val}
    //     fetchOrderCheck(query).then(res => {
    //         if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
    //             this.orderInfo = res.data.data.detail
    //             this.qiandan_status =res.data.data.detail.qiandan_status
    //         } else {
    //             this.$message.warning('未查询到符合要求内容')
    //             this.orderInfo = []
    //         }
    //     })
    // },
    // 编辑
    editHandle(val) {
      val.read_mark = 2
      getVisitDetails({ id: val.id, order_type: val.order_type, order_id: val.order_id }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          // let obj={}
          // Object.keys(res.data.data.detail).forEach(function(key){
          //     if(res.data.data.detail[key] == '' || res.data.data.detail[key] == null){
          //         obj[key] = '----'
          //     }else{
          //         obj[key] = res.data.data.detail[key]
          //     }
          // });
          this.details = res.data.data.detail
          // if(obj.visit_companys && obj.visit_companys instanceof Array) {
          //     this.details.visit_companys = obj.visit_companys.map(el => el.company_jc).join(',')
          // }

          // let _obj = {}
          // Object.keys(res.data.data.orderinfo).forEach(function(key){
          //     if(res.data.data.orderinfo[key] == ''  || res.data.data.orderinfo[key] == null){
          //         _obj[key] = '----'
          //     }else{
          //         _obj[key] = res.data.data.orderinfo[key]
          //     }
          // });
          this.orderinfos = res.data.data.orderinfo
          this.dialog.show = true
          // if(obj.lf_companys && obj.lf_companys instanceof Array) {
          //     this.orderinfos.lf_companys = _obj.lf_companys.map(item => item.company_qc).join(',')
          // }
          // this.orderinfos.companys = _obj.companys

          // if(_obj.companys && _obj.companys instanceof Array){
          //     this.orderinfos.companys = _obj.companys.map(item => item.company_jc).join(',')
          // }
          // console.log(this.orderinfos)
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 撤回
    deleteHandle(index, rows) {
      let id = rows[index].id
      this.$confirm('确定撤回该条数据吗?撤回后不可恢复', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        fetchVisitDelete({ id: id }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            rows.splice(index, 1)
            this.$message({
              type: 'success',
              message: '撤回成功!'
            })
          } else {
            this.$message({
              type: 'fail',
              message: res.data.error_msg
            })
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消撤回'
        });
      });
    },
    // 关闭弹窗
    closeDialog() {
      this.dialogTableVisible = false
    },
    searchHandle() {
      if (this.formInline.order_id) {
        this.formInline.valid_status = ''
      }
      this.page_current = 1
      this.getVisitOrderList()
    },
    // 表格颜色
    tableRowClassName({ row, rowIndex }) {
      if (row.read_mark === 1 && row.valid_status === 1) {
        return 'warning-row'
      } else {
        return ''
      }
    },

    // 回访导出
    exportHandle() {
      this.export = 1
      this.exportLoading = true
      const tHeader = [
        '发布日期',
        '小区名称',
        '城市区县',
        '业主姓名',
        '公装/家装',
        '预算',
        '面积（㎡）',
        '订单状态',
        '量房状态',
        '回访类型',
        '回访阶段',
        '回访时间',
        '回访状态',
        '是否已推送',
        '创建时间',
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'date_real',
        'xiaoqu',
        'city_name',
        'yz_name',
        'leixing_name',
        'yusuan_name',
        'mianji',
        'type_fw_name',
        'order_lf_name',
        'visit_type_name',
        'visit_step_name',
        'visit_date',
        'valid_status_name',
        'visit_push_name',
        'created_date',
      ]
      this.getVisitExport('export', () => {
        // 上面的index、phone_Num、school_Name是tableData里对象的属性
        this.exportData.forEach(item => {
          if (item.visit_push == 1) {
            item.visit_push_name = '未推送'
          } else if (item.visit_push == 2) {
            item.visit_push_name = '已推送'
          } else {
            item.visit_push_name = '----'
          }
        })
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '回访订单')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    getVisitExport(val, cb) {
      let query = this.formInline;
      query = Object.assign({}, query, {export: 1})
      getVisitList(query).then(res => {
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
    // 选择城市
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
    // 呼叫记录
    recordHandle(id) {
      this.$router.push({
        path: 'voipRecord',
        query: {
          orderid: id
        }
      })
    }
  }
}

</script>

<style rel="stylesheet/scss" lang="scss">
  .order-visit {
    padding: 10px 15px;

    .search-box h2, .content-box h2 {
      font-size: 16px;
      font-weight: normal;
      color: #666;
    }
    .warning-row{
      background: #fff0c2;
    }
    .content-form, .content-table {
      border-top: 3px solid #d2d6de;
      border-radius: 3px;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
      padding: 5px 15px;
      background: #fff;

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

    .isqiandan {
      background: url("~@/assets/dialog_images/order.png") no-repeat;
      background-size: 40%;
      background-position: 345px 175px;
    }

    .el-select {
      margin-top: 0;
    }

    .el-form-item__content {
      width: 200px;
    }
  }
</style>
