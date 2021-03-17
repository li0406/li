<template>
  <div class="order-content">
    <div class="search-box">
      <h2>列表查询</h2>
      <div class='content-form clearfix'>
        <div class="mt20 clearfix">
          <div class="fl mr20">
            <span>所在城市</span><br>
            <el-select
              v-model="formInline.cs"
              placeholder="请选择城市"
              filterable
              @change="getCityHandle(formInline.cs)"
            >
              <el-option value="0" label="请选择"/>
              <el-option
                v-for="item in citys"
                :key="item.cid"
                :value="item.cid"
                :label="item.cname"
              />
            </el-select>
          </div>
          <div class="fl mr20">
            <br>
            <el-select
              v-model="formInline.qx"
              placeholder="请选择区域"
              filterable
            >
              <el-option value="0" label="请选择"/>
              <el-option
                v-for="item in qx"
                :key="item.area_id"
                :value="item.area_id"
                :label="item.value"
              />
            </el-select>
          </div>
          <div class="fl mr20">
            <span>装修公司</span><br>
            <el-input v-model="formInline.company" placeholder="名称/id"></el-input>
          </div>
          <div class="fl mr20">
            <span>业主</span><br>
            <el-input v-model="formInline.proprietor" placeholder="姓名/小区"></el-input>
          </div>
          <div class="fl mr20">
            <span>订单类型</span><br>
            <el-select
              v-model="formInline.type"
              placeholder="请选择"
            >
              <el-option value="0" label="请选择"/>
              <el-option
                v-for="item in leixing"
                :key="item.id"
                :value="item.id"
                :label="item.name"
              />
            </el-select>
          </div>
          <div class="fl mr20">
            <span>签约时间</span><br>
            <el-date-picker
              v-model="formInline.start_time"
              value-format="yyyy-MM-dd"
              type="date"
              format="yyyy-MM-dd "
              placeholder="开始时间"
            />
          </div>
          <div class="fl mr20">
            <br>
            <el-date-picker
              v-model="formInline.end_time"
              value-format="yyyy-MM-dd"
              type="date"
              format="yyyy-MM-dd "
              placeholder="结束时间"
            />
          </div>
        </div>
        <div class="mt20 mb20 clearfix">
          <div class="fl mr20">
            <span>现场</span><br>
            <el-input v-model="formInline.code" placeholder="订单号/施工编号"></el-input>
          </div>
          <div class="fl mr20">
            <span>已施工阶段</span><br>
            <el-select
              v-model="formInline.step"
              placeholder="请选择"
            >
              <el-option value="0" label="请选择"/>
              <el-option
                v-for="item in stepList"
                :key="item.id"
                :value="item.id"
                :label="item.name"
              />
            </el-select>
          </div>
          <div class="fl mr20">
            <br>
              <el-button type="primary" @click="searchHandle">搜索</el-button>
              <el-button type="default" @click="resetHandle('formInline')">重置</el-button>
          </div>
        </div>

      </div>
    </div>
    <div class="content-box">
      <h2>现场列表</h2>
      <div class="content-table" v-loading="visibleTable">
        <el-table
          :data="tableData"
          border
          empty-text="暂无数据"
          style="width: 100%;">
          <el-table-column
            show-overflow-tooltip
            align="center"
            type="index"
            label="序号"
            width="50">
            <template scope="scope">
              <span>{{(page_current - 1) * page_size + scope.$index + 1}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="company_jc"
            align="center"
            label="装修公司">
            <template scope="scope">
              <span>{{scope.row.company_jc}}</span><br>
              <span>({{scope.row.company_id}})</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="yz_name"
            label="业主"
            align="center">
          </el-table-column>
          <el-table-column
            prop="area_name"
            label="城市"
            align="center">
            <template scope="scope">
              <span>{{scope.row.city_name}} </span><span>{{scope.row.area_name}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="xiaoqu"
            align="center"
            label="小区">
            <template scope="scope">
              <span>{{scope.row.xiaoqu || '--'}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="qiandan_date"
            align="center"
            label="签约时间">
          </el-table-column>
          <el-table-column
            prop="step_name"
            align="center"
            label="最新阶段">
            <template scope="scope">
              <span>{{scope.row.step_name || '--'}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="type_name"
            align="center"
            label="订单类型">
          </el-table-column>
          <el-table-column
            prop="order_id"
            align="center"
            label="订单号">
            <template scope="scope">
              <span>{{scope.row.order_type ===1 ? scope.row.order_id : '--'}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="code"
            align="center"
            label="施工编号"
            class="img-qrcode">

            <template slot-scope="scope">
              <el-popover trigger="hover" placement="bottom" @show="getInfoListQrcode(scope.row.code)">
                <div class="text-c">
                  <i class="el-icon-loading" v-if="!qrcode"></i>
                  <img v-else :src="prefixBase64+qrcode" alt="" width="110" height="110">
                </div>
                <div slot="reference" class="name-wrapper">
                  <el-tag size="medium">{{ scope.row.code }}</el-tag>
                </div>
              </el-popover>
            </template>

          </el-table-column>
          <el-table-column
            prop="operate"
            align="center"
            label="操作">
            <template slot-scope="scope">
              <el-button type="text" size="small" @click="recordHandle(scope.row.id)">详情</el-button>
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
  </div>

</template>

<script type="text/ecmascript-6">
import {
  fetchGetFormCitys,
  fetchGetArea
} from '@/api/orderManage'

import { fetchGetList, fetchGetInfoListqrcode, fetchGetStepList } from '@/api/decorate'

export default {
  name: 'Decorate',
  data() {
    return {
      formInline: {
        start_time: '', // 开始时间
        end_time: '', // 结束时间
        code: '', // 工地
        cs: '', // 所在城市
        qx: '', // 区县
        company: '', // 装修公司
        proprietor: '', // 业主
        type: '', // 订单类型
        step: '', // 施工类型
        id: ''
      },
      tableData: [],
      leixing: [{ id: '1', name: '分单' }, { id: '2', name: '赠单' }, { id: '3', name: '分单+赠单' }, { id: '4', name: '主动咨询单' }],
      stepList: [],
      citys: [],
      qx: [],
      // 分页
      page_current: 1, // 当前页
      page_size: 20, // 每页条数
      total_number: 0, // 总条数
      // 弹窗
      dialogTableVisible: false,
      dialoading: false,
      orderInfo: {},
      exportLoading: false,
      visibleTable: false, // 数据判断
      export: 0,
      exportData: [],
      qiandan_status: '',
      qrcode: '', // 二维码
      prefixBase64: 'data:image/png;base64,' // base64前缀
    }
  },
  created() {
    this.getFormOptions()
    this.getOrderList()
    this.getStepLIst()
  },
  methods: {
    // form表单
    getFormOptions() {
      fetchGetFormCitys().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.citys = res.data.data[0]
        }
      })
    },
    // 获取施工类型
    getStepLIst() {
      fetchGetStepList().then(res => {
        console.log(res)
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.stepList = res.data.data.info
        }
      })
    },
    // 获取订单数据
    getOrderList() {
      let query = this.formInline
      query = Object.assign({}, query, {
        page: this.page_current,
        limit: this.page_size
      })
      this.visibleTable = true
      // console.log(query)
      fetchGetList(query).then(res => {
        // console.log('list：', res)
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (this.tableData.length <= 0 && this.page_current > 1) {
            this.page_current--
            this.getOrderList()
            this.visibleTable = false
          } else {
            this.tableData = []
            this.tableData = res.data.data.list
            this.page_current = res.data.data.page.page_current
            this.page_size = res.data.data.page.page_size
            this.total_number = res.data.data.page.total_number
            this.visibleTable = false
          }
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
    // 搜索
    searchHandle() {
      const beginTime = this.formInline.start_time
      const endTime = this.formInline.end_time

      if (beginTime !== '' && endTime !== '' && beginTime > endTime) {
        this.$message.warning('结束时间必须大于开始时间')
        return false
      }
      this.page_current = 1
      this.getOrderList()
    },

    // 重置
    resetHandle(formName) {
      this.$refs[formName].resetFields()
      this.page_current = 1
      this.getOrderList()
    },
    // 选择城市
    getCityHandle(val) {
      this.formInline.qx = ''
      const query = { cid: val }
      fetchGetArea(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.qx = res.data.data[0]
        } else {
          this.qx = []
          this.$message.warning('未查询到符合要求的数据')
        }
      })
    },
    // 详情
    recordHandle(id) {
      this.$router.push({
        path: 'decorateDetails/' + id
      })
    },
    // 获取二维码
    getInfoListQrcode(id) {
      this.qrcode = ''
      fetchGetInfoListqrcode({
        live_code: id
      }).then(res => {
        // console.log('二维码', res)
        this.qrcode = res.data.data
      })
    }
  }
}

</script>

<style rel="stylesheet/scss" lang="scss">
  .text-c{
    text-align: center;
  }
  .order-content {
    padding: 10px 15px;
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

    .content-form {
      span{
        /*font-size: 14px;*/
        /*line-height: 1.5;*/
      }
      .fl{
        float: left;
      }
      .mr20{
        margin-right: 20px;
      }
      .mt20{
        margin-top: 20px;
      }
      .mb20{
        margin-bottom: 20px;
      }
    }

    .content-form .el-select {
      width: 200px;
    }

  }
</style>
