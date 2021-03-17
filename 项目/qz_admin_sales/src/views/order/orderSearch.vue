<template>
  <div class="order-search">
    <div class="search-box">
      <h2>订单查询</h2>
      <div class='content-form' style="overflow: hidden;">
        <div>
          <el-form :inline="true" ref="formInline" label-width="85px" :model="formInline">
            <div style="width:60%;float:left;">
              <div class="mr20 mt18">
                查询城市：
                <el-input
                  type="textarea"
                  :rows="4"
                  placeholder="请填写/复制城市"
                  v-model="formInline.cityValue">
                </el-input>
              </div>
              <div class="city-explain">批量查询城市用英文","隔开</div>
            </div>
            <div style="width:40%;float:left;">
              <div class="fl mr20 mt18">
                时间：<br/>
                <el-date-picker
                  v-model="formInline.date_begin"
                  value-format="yyyy-MM-dd"
                  type="date"
                  format="yyyy-MM-dd "
                  placeholder="请选择开始时间"/>
              </div>
              <div class="fl mr20 mt18">
                <br/>
                <el-date-picker
                  v-model="formInline.date_end"
                  value-format="yyyy-MM-dd"
                  type="date"
                  format="yyyy-MM-dd "
                  placeholder="请选择结束时间"/>
              </div>
              <el-form-item style="margin-top:22px;">
                <el-button type="primary" @click="searchHandle">查询</el-button>
                <el-button @click="resetHandle">重置</el-button>
                <el-button type="success" @click="exportHandle" v-loading="exportLoading">导出</el-button>
              </el-form-item>
            </div>
            <div>
            </div>
          </el-form>
        </div>
      </div>
    </div>
    <div class="content-box">
      <div class="order-tongji">
        <div class="content-table" v-loading="visibleTable">
          <h2>城市订单统计</h2>
          <el-table
            :data="tableData"
            max-height="480"
            v-loading="loading"
            border
            style="width: 100%;">
            <el-table-column
              prop="cname"
              align="center"
              label="城市"
              >
            </el-table-column>
            <el-table-column
              prop="count"
              label="发单数"
              align="center"
              >
            </el-table-column>
            <el-table-column
              prop="fen_count"
              align="center"
              label="有效单">
            </el-table-column>
            <el-table-column
              v-if="yuSuanValue"
              prop="yusuan_count"
              align="center"
              :label="`预算（${newYuSuan}）`"
            >
            </el-table-column>
            <el-table-column
              prop="lx_count"
              align="center"
              v-if="leiXingValue"
              :label="`装修类型（${newLeiXing}）`"
            >
            </el-table-column>
            <el-table-column
              prop="fangshi_count"
              align="center"
              v-if="fangShiValue"
              :label="`装修方式（${newFangShi}）`"
            >
            </el-table-column>
          </el-table>
        </div>
      </div>
      <div class="add-tongji" style="width:29%;float:right;">
        <h2>增加统计列</h2>
        <div>
          <div class="mr20 mt18">
            预算：<br/>
            <el-select
              v-model="formInline.visit_step"
              placeholder="请选择预算"
              class="typefw"
            >
              <el-option value="0" label="请选择"/>
              <el-option v-for="item in visit_step"
               :key="item.id"
               :label="item.name"
               :value="item.id"/>
            </el-select>
          </div>
          <div class="mr20 mt18">
            装修类型：<br/>
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
          </div>
          <div class="mr20 mt18">
            装修方式：<br/>
            <el-select
              v-model="formInline.deco_style"
              placeholder="请选择"
            >
              <el-option value="0" label="请选择"/>
              <el-option
                v-for="item in deco_style"
                :key="item.id"
                :value="item.id"
                :label="item.name"
              ></el-option>
            </el-select>
            <div class="add-btn">
              <el-button type="success" @click="addHandle">增加</el-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getYuSuan, getFangShi, getCityInfo } from '@/api/orderList'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'orderSearch',
  created() {
    var date = new Date()
    this.formInline.date_end = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate()
    this.formInline.date_begin = this.formatTime(date.getTime() - 90 * 24 * 3600 * 1000)
    this.getYuSuan()
    this.getFangShi()
  },
  data() {
    return {
      formInline: {
        visit_step: '',   //  预算
        visit_push: '',   //  装修类型（1：家装；2：公装）
        deco_style: '',   // 装修方式（1：半包；2：全包；3：清包；4：面议）
        cityValue: '',    // 城市
        date_begin: '',   // 开始时间
        date_end: ''      // 结束时间
      },
      yuSuanValue: false,
      leiXingValue: false,
      fangShiValue: false,
      newYuSuan: '',
      newLeiXing: '',
      newFangShi: '',
      tableData: [],
      visit_step: [],
      visit_push: [
        { 'id': '1', 'name': '家装' },
        { 'id': '2', 'name': '公装' }
      ],
      deco_style: [],
      exportLoading: false,
      visibleTable: false,
      export: 0,
      exportData: [],
      loading: false
    }
  },
  methods: {
    getVisitOrderList() {
      const beginTime = new Date(this.formInline.date_begin).getTime()
      const endTime = new Date(this.formInline.date_end).getTime()
      const time_diff = (endTime - beginTime) / 1000 / 60 / 60 / 24
      if (this.formInline.cityValue === '') {
        this.$message.warning('请填写城市，批量查询城市用英文","隔开')
        return false
      }
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
      if (beginTime !== '' && endTime !== '' && time_diff > 365) {
        this.$message.warning('查询时间不能超出一年')
        return false
      }
      this.getCityInfo()
    },
    // 查询
    searchHandle() {
      this.getVisitOrderList()
    },
    getCityInfo() {
      this.loading = true
      const obj = {
        city: this.formInline.cityValue,
        begin: this.formInline.date_begin,
        end: this.formInline.date_end,
        yusuan: this.formInline.visit_step,
        lx: this.formInline.visit_push,
        fangshi: this.formInline.deco_style
      }
      getCityInfo(obj).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.tableData = []
          this.loading = false
          for (var i in res.data.data) {
            this.tableData.push(res.data.data[i])
          }
        } else {
          this.loading = false
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 获取预算项
    getYuSuan() {
      getYuSuan().then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.visit_step = res.data.data[0]
        }
      })
    },
    // 获取装修方式项
    getFangShi() {
      getFangShi().then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.deco_style = res.data.data[0]
        }
      })
    },
    // 重置
    resetHandle() {
      this.formInline.date_begin = ''
      this.formInline.date_end = ''
      this.formInline.cityValue = ''
    },
    // 增加
    addHandle() {
      this.isShow()
      this.getCityInfo()
    },
    isShow() {
      // 预算
      if (this.formInline.visit_step && parseInt(this.formInline.visit_step) !== 0) {
        const _this = this
        _this.visit_step.forEach(function(index, item) {
          if (_this.formInline.visit_step === index.id) {
            _this.newYuSuan = index.name
          }
        })
        _this.yuSuanValue = true
      } else {
        this.newYuSuan = ''
        this.yuSuanValue = false
      }
      // 装修类型
      if (this.formInline.visit_push && parseInt(this.formInline.visit_push) !== 0) {
        const _this = this
        _this.visit_push.forEach(function(index, item) {
          if (_this.formInline.visit_push === index.id) {
            _this.newLeiXing = index.name
          }
        })
        _this.leiXingValue = true
      } else {
        this.newLeiXing = ''
        this.leiXingValue = false
      }
      // 装修方式
      if (this.formInline.deco_style && parseInt(this.formInline.deco_style) !== 0) {
        const _this = this
        _this.deco_style.forEach(function(index, item) {
          if (_this.formInline.deco_style === index.id) {
            _this.newFangShi = index.name
          }
        })
        _this.fangShiValue = true
      } else {
        this.newFangShi = ''
        this.fangShiValue = false
      }
    },
    // 导出
    exportHandle() {
      this.export = 1
      this.exportLoading = true
      const tHeader = [
        '城市',
        '发单数',
        '分单数',
        '预算',
        '装修类型',
        '装修方式'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'cname',
        'count',
        'fen_count',
        'yusuan_count',
        'lx_count',
        'fangshi_count'
      ]
      this.getVisitExport('export', () => {
        // 上面的index、phone_Num、school_Name是tableData里对象的属性
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '订单查询（大客户）')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    getVisitExport(val, cb) {
      let query = {
        city: this.formInline.cityValue,
        begin: this.formInline.date_begin,
        end: this.formInline.date_end,
        yusuan: this.formInline.visit_step,
        lx: this.formInline.visit_push,
        fangshi: this.formInline.deco_style
      }
      query = Object.assign({}, query, { export: 1 })
      getCityInfo(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (Object.keys(res.data.data).length > 0) {
            for (var i in res.data.data) {
              this.exportData.push(res.data.data[i])
            }
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
    // 时间戳转化时间日期
    formatTime(timeDate) {
      var time = new Date(timeDate)
      var y = time.getFullYear()
      var m = time.getMonth() + 1
      var d = time.getDate()
      return y + '-' + m + '-' + d
    }
  }
}

</script>

<style rel="stylesheet/scss" scoped lang="scss">
  .mr20 {
    margin-right: 20px;
  }
  .mt18{
    margin-top:18px;
  }
  .add-tongji{
    width: 29%;
    height: 550px;
    float: right;
    background: #ffff;
    border-top: 3px solid #d2d6de;
    padding-left: 30px;
  }
  .city-explain{
    font-family: Microsoft YaHei;
    margin:10px 0;
  }
  .order-tongji{
    width:70%;
    float: left;
  }

  .order-search {
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
    .content-table{
      height: 550px;
    }

    .content-form > form, .content-table {
      padding-top: 10px;
    }

    .content-box {
      margin-top: 10px;
    }

    .content-form label {
      text-align: left;
    }

    .content-form .el-select {
      width: 170px;
    }

    .el-select {
      margin-top: 0;
    }

    .add-btn{
      margin:20px 0 50px 0;
    }

  }
</style>
