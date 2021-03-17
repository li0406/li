<template>
  <div class="city-offer-list">
    <div class="search">
      <div class="clearfix">
        <div class="fl mr20">
          城市：<br>
          <el-input v-model="citySelectStr" placeholder="请输入" />
        </div>
        <div class="fl mr20">
          所属地级市：<br>
          <el-input v-model="prefectureLevelCities" placeholder="请输入" />
        </div>
        <div class="fl mr20">
          城市等级：<br>
          <el-select v-model="cityLevelVal" filterable placeholder="请选择">
            <el-option
              v-for="item in cityLevelOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl">
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
          <el-upload
            class="upload-demo"
            :action="importUrl"
            :name="name"
            :headers="uploadContentType"
            :before-upload="beforeUpload"
            :on-error="uploadFail"
            :on-success="uploadSuccess"
            :file-list="fileList"
            :show-file-list="false"
            :limit="1"
          >
            <el-button v-loading="importLoading" type="primary">导入</el-button>
          </el-upload>
          <el-button type="primary" @click="handleTplDownload">模板下载</el-button>
        </div>
      </div>
      <div><br><el-button type="success" @click="handleAdd">添加</el-button></div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
      >
        <el-table-column
          align="center"
          prop="city_name"
          label="城市"
        />
        <el-table-column
          align="center"
          prop="parent_city_name"
          label="所属地级市"
        />
        <el-table-column
          align="center"
          label="主做模式"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.city_mode)==0?'-----':(parseInt(scope.row.city_mode)==1)?'1.0模式':'2.0模式' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="是否标杆"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_standard)==0?'-----':(parseInt(scope.row.is_standard)==1)?'是':'否' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="报价对应城市等级"
        >
          <template slot-scope="scope">
            {{ scope.row.little | littleFilter }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="轮单费单价"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.round_order_money) ? scope.row.round_order_money : '-----' }}
          </template>
        </el-table-column>
        <el-table-column align="center" label="局改价格">
          <template slot-scope="scope">
            {{ parseInt(scope.row.part_reform_price) ? scope.row.part_reform_price : '-----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="返点比例"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.rebates_ratio) ? scope.row.rebates_ratio : '-----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="quarter_quote"
          label="季度报价"
        />
        <el-table-column
          align="center"
          prop="half_year_quote"
          label="半年报价"
        />
        <el-table-column
          align="center"
          prop="year_quote"
          label="年度报价"
        />
        <el-table-column
          align="center"
          label="月承诺订单"
        >
          <template slot-scope="scope">
            {{ scope.row.month_promise_order ? scope.row.month_promise_order : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="1.0订单上限"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.user_order_limit ? scope.row.user_order_limit : '----' }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="不同档报价及承诺单量"
          width="120"
        >
          <template slot-scope="scope">
            <div>A:{{ scope.row.grade_a_price ? scope.row.grade_a_price : '----' }},单量：{{ scope.row.grade_a_order ? scope.row.grade_a_order : '----' }}</div>
            <div>B:{{ scope.row.grade_b_price ? scope.row.grade_b_price : '----' }},单量：{{ scope.row.grade_b_order ? scope.row.grade_b_order : '----' }}</div>
            <div>C:{{ scope.row.grade_c_price ? scope.row.grade_c_price : '----' }},单量：{{ scope.row.grade_c_order ? scope.row.grade_c_order : '----' }}</div>
            <div>D:{{ scope.row.grade_d_price ? scope.row.grade_d_price : '----' }},单量：{{ scope.row.grade_d_order ? scope.row.grade_d_order : '----' }}</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="年度会员时间"
        >
          <template slot-scope="scope">
            {{ scope.row.year_member_time ? scope.row.year_member_time : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="包干报价"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_consist) === 2 ? '不包干' : scope.row.consist_price }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="consist_fen"
          label="包干最低承诺分单数"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_consist) === 2 ? '----' : scope.row.consist_fen }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="包干赠单承诺"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_consist) === 2 ? '----' : scope.row.consist_zeng }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="独家报价"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_exclusive) === 2 ? '不独家' : scope.row.exclusive_price }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="独家最低分单数"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_exclusive) === 2 ? '----' : scope.row.exclusive_fen_min }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="独家最高分单数"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_exclusive) === 2 ? '----' : scope.row.exclusive_fen_max }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="独家赠单承诺"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_exclusive) === 2 ? '----' : scope.row.exclusive_zeng }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
          width="120"
          fixed="right"
        >
          <template slot-scope="scope">
            <span class="edit-btn" @click="handleEdit(scope.row)">编辑</span>
            <span class="check-btn" @click="handleCheckRecord(scope.row)">记录</span>
            <span class="del-btn" @click="handleDel(scope.row)">删除</span>
          </template>
        </el-table-column>
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
import { fetchCityOfferList, fetchCityOfferDel } from '@/api/memberReport'
import { fetchCityList } from '@/api/common'
import { fortmatDate } from '@/utils/index'
export default {
  name: 'CityOfferList',
  filters: {
    littleFilter(val) {
      switch (val) {
        case 0:
          return 'A类'
          break
        case 1:
          return 'B类'
          break
        case 2:
          return 'C类'
          break
        case 3:
          return 'D类'
          break
        case 4:
          return 'S1类'
          break
        case 5:
          return 'S2类'
          break
        default:
          return ''
      }
    }
  },
  data() {
    return {
      citySelectStr: '',
      cityBlurFlag: null,
      prefectureLevelCities: '',
      cityLevelVal: '',
      cityLevelOptions: [{
        value: '',
        label: '请选择'
      }, {
        value: '1',
        label: 'A类'
      }, {
        value: '2',
        label: 'B类'
      }, {
        value: '3',
        label: 'C类'
      }, {
        value: '4',
        label: 'D类'
      }, {
        value: '5',
        label: 'S1类'
      }, {
        value: '6',
        label: 'S2类'
      }],
      citys: [],
      tableData: [],
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      loading: false,
      importUrl: this.$qzconfig.base_api + '/v1/quote/read_city_quote',
      importHeaders: {
        enctype: 'multipart/form-data'
      },
      fileList: [],
      withCredentials: true,
      importFlag: 1,
      dialogImportVisible: false,
      errorResults: [],
      name: 'file',
      uploadContentType: {
        'token': localStorage.getItem('token')
      },
      importLoading: false
    }
  },
  created() {
    this.getCityOfferList()
  },
  methods: {
    beforeUpload(file) {
      // 上传前配置
      const excelfileExtend = '.xls,.xlsx'// 设置文件格式
      const fileExtend = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
      if (excelfileExtend.indexOf(fileExtend) <= -1) {
        this.$message.error('文件格式错误')
        return false
      }
      this.importLoading = true
    },
    uploadFail(err, file, fileList) {
      this.importLoading = false
      this.$message.error(err)
    },
    uploadSuccess(res, file, fileList) {
      if (parseInt(res.error_code) === 0) {
        this.$message({
          message: res.error_msg,
          type: 'success'
        })
        // location.reload()
      } else {
        this.$message.error(res.error_msg)
      }
      this.fileList = []
      this.importLoading = false
    },
    handleArguments(val) {
      const queryObj = {
        cname: this.citySelectStr, // 城市
        parent_cname: this.prefectureLevelCities, // 所属地级市
        level: this.cityLevelVal, // 城市等级
        p: this.currentPage, // 分页 页码  默认1
        psize: 20
      }
      return queryObj
    },
    getCityOfferList() {
      const queryObj = this.handleArguments()
      this.loading = true
      fetchCityOfferList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.getCityOfferList()
          } else {
            this.tableData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              this.tableData.push(item)
            })
            this.loading = false
          }
        } else {
          // this.tableData = []
          this.totals = 0
          this.loading = false
        }
      })
    },
    handleSearch() {
      this.currentPage = 1
      this.getCityOfferList()
    },
    handleTplDownload() {
      const urls = this.$qzconfig.oss_img_host + 'custom/20210305/Fk6XhDHylpx_SfT4ZxYmn8IbC9CF.xlsx'
      window.location.href = urls
    },
    handleDel(obj) {
      this.$confirm('确认删除该城市报价?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.handleDelAjax(obj)
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    handleDelAjax(obj) {
      fetchCityOfferDel({
        id: obj.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '删除成功'
          })
          this.getCityOfferList()
        } else {
          this.$message.error(res.data.error_code)
        }
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试')
      })
    },
    handleCheck(obj) {
      const { href } = this.$router.resolve({
        name: 'reportDetail',
        path: '/memberReport/detail',
        query: {
          id: 1,
          type: 1
        }
      })
      window.open(href, '_blank')
    },
    handleWithdraw() {},
    handleAdd() {
      const { href } = this.$router.resolve({
        name: 'cityOfferAdd',
        path: '/offerManager/cityOfferAdd'
      })
      window.open(href, '_blank')
    },
    handleEdit(obj) {
      const { href } = this.$router.resolve({
        name: 'cityOfferAdd',
        path: '/offerManager/cityOfferAdd',
        query: {
          id: obj.id
        }
      })
      window.open(href, '_blank')
    },
    handleCheckRecord(obj) {
      const { href } = this.$router.resolve({
        name: 'cityOfferRecordList',
        path: '/offerManager/cityOfferRecordList',
        query: {
          id: obj.id
        }
      })
      window.open(href, '_blank')
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.getCityOfferList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getCityOfferList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
    .city-offer-list {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .search {
            background: #fff;
            padding: 0px;
            box-sizing: border-box;
            line-height: 36px;
            border-top:0;
        }
        .qian-main {
            margin: 20px -20px 0;
            padding: 0px;
            box-sizing: border-box;
            background-color: #fff;
            border-top:0;
        }
        .upload-demo{
            display: inline-block;
        }
        .el-pagination{
            text-align: center;
            margin-top: 20px;
        }
        .fl {
            float: left;
        }
        .mr20 {
            margin-right: 20px;
        }
        .el-select{
            margin-top: 0;
        }
        .edit-btn{
            color: #e6a23c;
            cursor: pointer;
        }
        .del-btn{
            color: #f56c6c;
            cursor: pointer;
        }
        .check-btn{
            color: #409eff;
            cursor: pointer;
        }
        .widthdraw-btn{
            color: #67c23a;
            cursor: pointer;
        }
    }
</style>
