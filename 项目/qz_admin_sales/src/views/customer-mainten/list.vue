<template>
  <div class="customer-mainten">
    <div>
      <div class="search">
        <div class="fl mr20">
          城市：<br>
          <el-select v-model="citySelectStr" filterable placeholder="城市" clearable>
            <el-option
              v-for="item in citySelect"
              :key="item.value"
              :label="item.cname"
              :value="item.cid"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          装修公司：<br>
          <el-autocomplete
            clearable
            v-model="zxComvalText"
            :fetch-suggestions="querySearchUser"
            :trigger-on-focus="false"
            class="inline-input"
            @select="handleSelectUser"
            @blur="handleComBlur"
          ></el-autocomplete>
        </div>
        <div class="fl mr20">
          意向：<br>
          <el-select v-model="intentionValue" style="width: 100px" placeholder="请选择">
            <el-option
              v-for="item in intention"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          报备人：<br>
          <el-autocomplete
            v-model="personValue"
            class="inline-input zx"
            :fetch-suggestions="querySea"
            :trigger-on-focus="false"
            placeholder=""
            @input="findCom"
            style="width: 120px"
            @select="handleSel"
          />
        </div>
        <div class="fl mr20">
          签约状态：<br>
          <el-select v-model="qianyueValue" style="width: 100px" placeholder="请选择">
            <el-option
              v-for="item in qianyue"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          日期：<br>
          <el-select v-model="dataSelectVal" style="width: 140px" placeholder="请选择">
            <el-option
              v-for="item in Data"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          <br>
          <div class="block">
            <el-date-picker
              v-model="queryTimeRange"
              style="width: 400px"
              type="datetimerange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期">
            </el-date-picker>
          </div>
        </div>
        <div>
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch">搜索</el-button>
        </div>
        <br>
        <el-button type="primary" @click="addLog">添加日志</el-button>
        <el-button @click="handleExport">导出</el-button>
        <el-button @click="Muban">模板</el-button>
        <template>
            <vue-xlsx-table @on-select-file="handleSelectedFile">批量导入</vue-xlsx-table>
        </template>
      </div>
      <div class="qian-main">
        <div class="qd-input">
          <el-checkbox-group v-model="checkList">
            <el-checkbox label="总合同开始时间" @change="AllStarttime = !AllStarttime">总合同开始时间</el-checkbox>
            <el-checkbox label="总合同结束时间" @change="AllEndtime = !AllEndtime">总合同结束时间</el-checkbox>
            <el-checkbox label="本次合同开始时间" @change="TheStarttime = !TheStarttime">本次合同开始时间</el-checkbox>
            <el-checkbox label="本次合同结束时间" @change="TheEndtime = !TheEndtime">本次合同结束时间</el-checkbox>
            <el-checkbox label="尾款时间" @change="Paymenttime = !Paymenttime">尾款时间</el-checkbox>
          </el-checkbox-group>
        </div>
        <div>
          <el-table
            :data="pageData"
            border
            style="width: 100%"
          >
            <el-table-column
              align="center"
              prop="company_name"
              label="公司名称"
            />

            <el-table-column
              align="center"
              label="城市/区县"
            >
              <template slot-scope="scope">
                {{ scope.row.cname }}/{{ scope.row.area }}
              </template>
            </el-table-column>
            <el-table-column
              align="center"
              prop="name"
              label="联系人"
              width="80"
            />

            <el-table-column
              align="center"
              prop="addr"
              label="地址"
            />

            <el-table-column
              align="center"
              prop="intention_level"
              label="意向"
              width="60"
            />

            <el-table-column
              align="center"
              prop="is_new"
              label="客户类型"
              width="80"
            />

            <el-table-column
              align="center"
              prop="sign_status"
              label="签约状态"
              width="80"
            />

            <el-table-column
              v-if="AllStarttime"
              align="center"
              prop="allstart"
              label="总合同开始时间"
              width="100"
            />

            <el-table-column
              v-if="AllEndtime"
              align="center"
              prop="allend"
              label="总合同结束时间"
              width="100"
            />

            <el-table-column
              v-if="TheStarttime"
              align="center"
              prop="start"
              label="本次合同开始时间"
              width="100"
            />

            <el-table-column
              v-if="TheEndtime"
              align="center"
              prop="end"
              label="本次合同结束时间"
              width="100"
            />

            <el-table-column
              v-if="Paymenttime"
              align="center"
              prop="retainage_time"
              label="尾款时间"
              width="100"
            />

            <el-table-column
              align="center"
              prop="contact_num"
              label="联系次数"
              width="60"
            />

            <el-table-column
              align="center"
              prop="visit_start_time"
              label="拜访时间"
              width="160"
            >
              <template slot-scope="scope">
                <p>{{scope.row.visit_start_time}}</p>
                <p>{{scope.row.visit_end_time}}</p>
              </template>
            </el-table-column>
            <el-table-column
              align="center"
              prop="next_time"
              label="下次联系时间"
              width="160"
            />

            <el-table-column
              align="center"
              label="录音"
              width="80"
            >
              <template slot-scope="scope">
              <div v-for="(rcd, index) in scope.row.recording" :key="index" @click="handleSoundRecord(rcd)">
                <span class="zhu">录音{{index+1}}</span>
              </div>
              </template>
            </el-table-column>

            <el-table-column
              align="center"
              prop="admin_name"
              label="报备人"
              width="80"
            />

            <el-table-column
              align="center"
              label="操作"
              width="200"
            >
              <template slot-scope="scope">
                <el-button
                  size="mini"
                  type="primary"
                  @click="handleBaobei(scope.row.id)"
                >再次报备</el-button>
                <el-button
                  size="mini"
                  type="primary"
                  @click="handleRizhi(scope.row.id)"
                >日志记录</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="pag">
          <el-pagination
            :current-page="currentPage"
            :page-size="pageSize"
            layout="total, prev, pager, next, jumper"
            :total="totals"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>
    </div>
    <el-dialog
      title="录音播放"
      :visible="recordPlay"
      width="30%"
      @close="handleCloseRecord"
      >
        <audio class="player" :src="recordUrl" autoplay preload="auto" controls="" data-on="0" ref="record">
        </audio>
    </el-dialog>
    <el-dialog :dia-title="'id:'+ orderNO + '的电话呼叫记录'" :visible.sync="dialogTableVisible" :dia-width="'950px'" @diaClose="closeDialog">
      <el-table :data="gridData" v-loading="dialoading" border>
        <el-table-column align="center" property="time_add" label="产生时间" width="150" />
        <el-table-column align="center" property="action_name" label="呼叫动作" />
        <el-table-column align="center" property="byetype_name" label="挂机类型(代码)" width="120" />
        <el-table-column
          align="center"
          label="通话录音"
        >
          <template slot-scope="scope">
            <div v-if="parseInt(scope.row.duration) > 0">
              <span @click="handleRecordDetail(scope.row)" class="pointer link-blue">
                <i class="fa fa-play-circle-o" aria-hidden="true"></i>
              </span>
              <span>{{ scope.row.duration }}秒</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column align="center" property="callSid" label="callSid(callID)" width="190" />
        <el-table-column align="center" property="caller" label="主叫号码" width="120" />
        <el-table-column align="center" property="called" label="被叫号码" width="120" />
      </el-table>
    </el-dialog>
    <!-- <el-dialog title="批量导入excel表格" :visible.sync="dialogleadall">
      <el-upload>
      </el-upload>
    </el-dialog> -->
  </div>
</template>

<script>
import { fetchList, fetchvoip, fetchCompanyImport, fetchFindUser, fetchCompanySale } from '@/api/custumor-list'
import { fetchAreaList } from '@/api/addLog'
import { fetchCityList } from '@/api/common'
import { fetchSoundRecordList, fetchSoundRecordDetail } from '@/api/report-again'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'CustomerMainten',
  data() {
    return {
      AllStarttime: false,
      AllEndtime: false,
      TheStarttime: false,
      TheEndtime: false,
      Paymenttime: false,
      dialogTableVisible: false,
      dialogleadall: false,
      dialoading: false,
      recordUrl: '',
      recordPlay: false,
      comBlurFlag: null,
      orderNO: '',
      zxComval: '',
      zxComs: [],
      citys: [],
      citySelect: [],
      // 导入的表格数据
      convertedData: [],
      // 分页器
      pageData: [],
      currentPage: 1,
      pageSize: 20,
      totals: 0,
      citySelectVal: '',
      // 城市搜索
      citySelectStr: '',
      // 装修公司数据
      zxComvalText: '',
      // 意向
      intentionValue: '',
      // 报备人
      personValue: '',
      // 签约状态
      qianyueValue: '',
      // 签约状态
      DataValue: '',
      // 日期区间筛选
      dataSelectVal: '',
      queryTimeRange: '',
      exportData: [],
      baobeiid: '',
      // 复选框
      checkList: [],
      // 下拉数据
      // 城市search数据
      cityoptions: [
        {
          value: '1',
          label: '扬州'
        }, {
          value: '2',
          label: '南通'
        }
      ],
      // 意向
      intention: [
        {
          value: '',
          label: '全部'
        },
        {
          value: '1',
          label: 'A'
        },
        {
          value: '2',
          label: 'B'
        },
        {
          value: '3',
          label: 'C'
        }
      ],
      // 签约
      qianyue: [
        {
          value: '',
          label: '全部'
        },
        {
          value: '1',
          label: '未签约'
        },
        {
          value: '2',
          label: '已签约'
        },
        {
          value: '3',
          label: '本周内'
        },
        {
          value: '4',
          label: '半月内'
        },
        {
          value: '5',
          label: '1个月内'
        },
        {
          value: '6',
          label: '3个月内'
        },
        {
          value: '7',
          label: '暂无意向'
        },
      ],
      // 日期
      Data: [
        {
          value: '1',
          label: '拜访时间'
        },
        {
          value: '2',
          label: '下次联系时间'
        }
      ],
      // 录音数据
      gridData: [],
      // 匹配人员
      restaurants: []
    }
  },
  watch: {
    dataSelectVal() {
      this.queryTimeRange = ''
    },
    zxComvalText(value) {
      if(!value) {
        this.zxComval = ''
        this.comBlurFlag = null
      }
    },
  },
  mounted() {
    this.loadAllCity()
  },
  created() {
    this.initList()
  },
  methods: {
    // download() {
    //   var filepath="../../assets/muban-anli";//文件路径
    //   var fileName="lead-muban.xlsx";
    //   window.location.href=ctx+"/downLoadController/downLoad?filepath="+filepath+"&fileName="+fileName;
    // },
    Muban() {
      var urls = '//staticqn.qizuang.com/custom/20190601/Fkf6bHWROiHEyinqS0Bx4yAWaDMs.xlsx'
      window.location.href = urls
    },
    loadAllCity() {
      fetchCityList().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.citySelect = res.data.data[0]
        } else {
          this.citySelect = []
        }
      })
    },
    querySearchUser(queryString, cb) {
      this.searchUser(queryString, () => {
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          cb(this.zxComs)
        }, 1000)
      })
    },
    searchUser(query, cb) {
      fetchFindUser({
          company: this.zxComvalText
        }).then(res => {
        let data = res.data.data
        this.zxComs = data
        this.zxComs.forEach((item, index) => {
          this.zxComs[index]['value'] = item.company_name
        })
        console.log(this.zxComs);
        cb && cb.call()
      })
    },
    handleSelectUser(item) {
      this.zxComval = item.company_id
      this.zxComvalText = item.company_name
    },
    handleComBlur() {
      if(!this.comBlurFlag) {
        this.zxComval = ''
        this.zxComvalText = ''
      }
    },
    // 添加日志
    addLog() {
      this.$router.push({
        path: 'add-log'
      })
    },
    // 再次报备
    handleBaobei(id) {
      this.$router.push({
        path: 'report-again/' + id
      })
    },
    // 日志记录
    handleRizhi(id) {
      this.$router.push({
        path: 'log-list/' + id
      })
    },
    // 搜索按钮
    handleSearch() {
      this.pageData = []
      this.initList()
      if (this.pageData = []) {
        this.totals = 0
      }
      console.log(this.totals);

    },
    // 批量导入
    handleSelectedFile (convertedData) {
      this.convertedData = convertedData.body
      const importData = []
      this.convertedData.forEach((item, index) => {
        const baseImportObj = {
          company_name: '',
          user_id: '',
          cs: '',
          qx: '',
          user_name: '',
          user_job: '',
          user_tel: '',
          user_qq: '',
          user_wx: '',
          address: '',
          intention: '',
          is_new: '',
          customer_source: '',
          qianyue_status: '',
          retainage_time: '',
          visit_start_time: '',
          visit_end_time: '',
          visit_next_time: '',
          visit_style: '',
          pre_money: '',
          desc: '',
        }
        baseImportObj.company_name = item['公司名称']
        baseImportObj.user_id = item['会员ID']
        baseImportObj.cs = item['城市']
        baseImportObj.qx = item['区县']
        baseImportObj.user_name = item['联系人']
        baseImportObj.user_job = item['联系人职务']
        baseImportObj.user_tel = item['手机']
        baseImportObj.user_qq = item['QQ']
        baseImportObj.user_wx = item['微信']
        baseImportObj.address = item['地址']
        baseImportObj.intention = item['意向']
        baseImportObj.is_new = item['客户类型']
        baseImportObj.customer_source = item['客户来源']
        baseImportObj.qianyue_status = item['签约状态']
        baseImportObj.retainage_time = item['尾款时间']
        baseImportObj.visit_start_time = item['拜访开始时间']
        baseImportObj.visit_end_time = item['拜访结束时间']
        baseImportObj.visit_next_time = item['下次联系时间']
        baseImportObj.visit_style = item['谈判方式']
        baseImportObj.pre_money = item['预计收款']
        baseImportObj.desc = item['谈话内容']
        importData.push(baseImportObj)
      })
      console.log(importData);
      fetchCompanyImport({
        data: importData
      }).then(res => {
        if(parseInt(res.status) === 200) {
          if (parseInt(res.data.error_code) == 0) {
            this.initList()
            this.$message({
              type: 'success',
              message: res.data.error_msg
            })
          } else {
            this.$message({
              type: 'error',
              message: res.data.error_msg
            })
          }
        }
      })
    },
    // 录音
    handleSoundRecord(obj) {
      this.dialogTableVisible = true
      this.orderNO = obj.id
      this.gridData = []
      fetchSoundRecordList({
        call_sid: obj.call_sid
      }).then(res => {
        if(parseInt(res.status) === 200 && res.data.data.length > 0) {
          this.gridData = res.data.data
          this.gridData.forEach((item, index) => {
            if(item.byetype_name === '-') {
              item.bye = '-'
            }else{
              item.bye = item.byetype_name + "(" + item.byetype + ")"
            }
          })

        }
      })
    },
    handleRecordDetail(obj) {
      this.recordUrl = ''
      fetchSoundRecordDetail({
        call_sid: obj.callSid
      }).then(res => {
        if(parseInt(res.status) === 200 && parseInt(res.data.error_code) == 0) {
          this.recordUrl = this.$qzconfig.oss_audio_host + res.data.data.url
          this.recordPlay = true
        }
      })
    },
    handleCloseRecord() {
      this.recordPlay = false
    },
    closeDialog() {
      this.orderNO = ''
      this.dialogTableVisible = false
    },
    handleSizeChange() {
      this.currentPage = 1
      this.initList()
    },

    handleCurrentChange(val) {
      this.currentPage = val
      this.initList()
    },
    initList() {
      const queryObj = this.handleArguments()
      fetchList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.initList()
          } else {
            this.pageData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              switch (item.is_new) {
                case 1:
                  item.is_new = '新客户'
                  break
                case 2:
                  item.is_new = '老客户'
                  break
                default:
                  break
              }
              this.pageData.push(item)
            })
          }
        }
      })
    },
    handleArguments() {
      let time_begin = ''
      let time_end = ''
      const queryObj = {
        visit_start: '', // 拜访查询开始时间
        visit_end: '', // 拜访查询结束时间
        next_start: '', // 下次联系查询开始时间
        next_end: '', // 下次联系查询结束时间
        cs: this.citySelectStr, // 查询城市ID
        company: this.zxComval, // 装修公司名称/ID
        level: this.intentionValue, // 意向：0或者空->请选择  等级 A  B  C
        op_uid: this.baobeiid, // 报备人ID  0或者空->请选择
        is_sign: this.qianyueValue, // 是否签约：0或者空->请选择  1->已签约  2->未签约
        p: this.currentPage, // 分页 页码  默认1
        psize: this.pageSize, // 分页  每页数量  默认 10
        down: '' // 是否导出 非0或非空->导出
      }
      if (this.queryTimeRange && this.queryTimeRange.length > 0) {
        if (!this.dataSelectVal) {
          this.$message.error('请选择日期类型')
          return
        }
        time_begin = new Date(this.queryTimeRange[0]).getFullYear() + '-' + (new Date(this.queryTimeRange[0]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[0]).getDate()
        time_end = new Date(this.queryTimeRange[1]).getFullYear() + '-' + (new Date(this.queryTimeRange[1]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[1]).getDate()
        switch (this.dataSelectVal) {
          case '1':
            queryObj.visit_start = time_begin
            queryObj.visit_end = time_end
            break
          case '2':
            queryObj.next_start = time_begin
            queryObj.next_end = time_end
            break
          default:
            break
        }
      }
      return queryObj
    },
    queryCity(queryString, cb) {
      const citys = this.citys
      const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
      cb(results)
    },
    createFilter(queryString) {
      return (city) => {
        return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0)
      }
    },
    handleSelect(item) {
      this.citySelectVal = item.cid
    },
    fetchExportlist(val, cb) {
      const queryObj = this.handleArguments(val)
      queryObj.psize = 999
      fetchList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length > 0) {
            res.data.data.list.forEach((item, index) => {
              this.exportData.push(item)
            })
            cb && cb.call()
          }else{
            this.$message.error('未查询到符合要求的数据')
            this.exportLoading = false
          }
        } else {
          this.$message.error(res.data.error_msg)
          this.exportData = []
          this.exportLoading = false
        }
      })
    },
    handleExport() {
      const tHeader = [
        '装修公司名称',
        '城市名称',
        '地区名称',
        '联系人',
        '公司地址',
        '意向等级',
        '客户类型',
        '签约状态',
        '联系次数',
        '拜访开始时间',
        '拜访结束时间',
        '下次联系时间',
        '销售ID（报备人ID）',
        '销售名称（报备人名称）'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'company_name',
        'cname',
        'area',
        'name',
        'addr',
        'intention_level',
        'is_new',
        'sign_status',
        'contact_num',
        'visit_start_time',
        'visit_end_time',
        'next_time',
        'admin_id',
        'admin_name'
      ]
      this.fetchExportlist('export', () => {
        const list = this.exportData
        this.exportData = []
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '客户维护统计')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    // 模糊匹配人员
    handleSels(com) {
      fetchCompanySale(
        { user_name: com }
      ).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.restaurants = res.data.data
        } else {
          this.restaurants = []
        }
      })
      // fetchCompanyList(
      //   { company_name: com }
      // ).then(res => {
      //   if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
      //     this.restaurants = res.data.data
      //   } else {
      //     console.log('fetchCompanyList')
      //     this.restaurants = []
      //   }
      // })
    },
    querySea(queryString, cb) {
      var numhui = []

      for (let i = 0; i < this.restaurants.length; i++) {
        numhui.push({ 'value': this.restaurants[i].user_name })
      }
      var restaurants = numhui
      var results = queryString ? restaurants.filter(this.createFil(queryString)) : restaurants
      cb(results)
    },
    createFil(queryString) {
      return (restaurant) => {
        return (restaurant.value)
      }
    },
    handleSel(item) {
      console.log(item.value)
      fetchCompanySale(
        { user_name: item.value }
      ).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.baobeiid = res.data.data[0].user_id
          console.log(this.baobeiid);

        } else {
          this.restaurants = []
        }
      })
    },
    findCom() {
      const com = this.personValue
      this.handleSels(com)
    }
  }
}
</script>

<style scoped>
.yincang {
  display: none;
}
.fl {
  float: left;
}
.mr20 {
  margin-right: 20px;
}
.customer-mainten {
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
}
.search {
  width: 100%;
  background: #fff;
  padding: 20px;
  border-top: 3px solid rgb(197, 196, 196);
  box-sizing: border-box;
}
.zx {
  width: 150px;
}
.qd-input {
  margin-bottom: 10px;
}
.qian-main {
  margin-top: 20px;
  padding: 20px;
  border-top: 3px solid rgb(197, 196, 196);
  box-sizing: border-box;
  background-color: #fff;
}
.pag {
  margin-top: 20px;
  text-align: center;
}
.zhu{
  color: #0099FF;
  cursor: pointer;
}
</style>
