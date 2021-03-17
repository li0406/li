<template>
  <div class="member-company">
    <div class="search">
      <div class="yixiang fl mr20">
        会员名称：<br>
        <el-input v-model="memberKey" placeholder="会员ID/会员简称"></el-input>
      </div>
      <div class="fl mr20">
        城市：<br>
        <el-select v-model="cityVal" @change="getAreaList" filterable placeholder="请选择">
          <el-option
              v-for="item in cityOptions"
              :key="item.value"
              :label="item.value"
              :value="item.cid"
          >
          </el-option>
        </el-select>
      </div>
      <div class="fl mr20">
        区域: <br>
        <div class="block">
          <el-select v-model="areaVal" placeholder="请选择">
            <el-option
                v-for="item in areaOptions"
                :key="item.area_id"
                :label="item.value"
                :value="item.area_id">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        经纬度: <br>
        <div class="block">
          <el-select v-model="litAndLatVal" placeholder="请选择">
            <el-option
                v-for="item in litAndLatOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        半包/全包: <br>
        <div class="block">
          <el-select v-model="decortVal" placeholder="请选择">
            <el-option
                v-for="item in decortOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        公装/家装: <br>
        <div class="block">
          <el-select v-model="decortTypeVal" placeholder="请选择">
            <el-option
                v-for="item in decortTypeOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        是否接受赠单: <br>
        <div class="block">
          <el-select v-model="accessGrantVal" placeholder="请选择">
            <el-option
                v-for="item in accessGrantOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        会员状态: <br>
        <div class="block">
          <el-select v-model="user_on" placeholder="请选择">
            <el-option
                v-for="item in userOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        合作模式: <br>
        <div class="block">
          <el-select v-model="cooperate_mode" placeholder="请选择">
            <el-option
                v-for="item in hzOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        虚拟号开通状态: <br>
        <div class="block">
          <el-select v-model="pnp_on" placeholder="请选择">
            <el-option
                v-for="item in xnhOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        装企绑定状态: <br>
        <div class="block">
          <el-select v-model="bind_status" placeholder="请选择">
            <el-option
                v-for="item in zqOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="fl mr20">
        <br>
        <div class="block">
          <el-button class="search-button" type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
          <el-button class="search-button" type="primary" @click="handleOpen">批量开通虚拟号</el-button>
        </div>
      </div>
    </div>
    <div class="qian-main">
      <el-table
          v-loading="loading"
          :data="tableData"
          border
      >
        <el-table-column
            align="center"
            label="会员ID"
            width="70px"
        >
          <template slot-scope="scope">
            {{ scope.row.id }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            prop="jc"
            label="会员简称"
        />
        <el-table-column
            align="center"
            label="会员状态"
        >
          <template slot-scope="scope">
            {{ !scope.row.user_on_name ? '----' : scope.row.user_on_name }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="合作模式"
        >
          <template slot-scope="scope">
            {{ !scope.row.cooperate_mode_name ? '----' : scope.row.cooperate_mode_name }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="所属城市"
        >
          <template slot-scope="scope">
            {{ scope.row.city_name }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            prop="area_name"
            label="所在区域"
        />
        <el-table-column
            align="center"
            label="详细地址"
        >
          <template slot-scope="scope">
            {{ !scope.row.dz ? '----' : scope.row.dz }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            prop="latlng"
            label="坐标"
        />
        <el-table-column
            align="center"
            prop="lxs_name"
            label="新房/旧房"
        />
        <el-table-column
            align="center"
            label="半包/全包"
        >
          <template slot-scope="scope">
            {{ scope.row.contract_name }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="公装/家装"
        >
          <template slot-scope="scope">
            {{ scope.row.leixing_name }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="接单区域"
        >
          <template slot-scope="scope">
            {{ scope.row.deliver_area_count }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="对立公司ID"
        >
          <template slot-scope="scope">
            {{ scope.row.other_id ? scope.row.other_id : '----' }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="分单面积"
        >
          <template slot-scope="scope">
            {{ scope.row.fen_mianji }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="特殊需求"
        >
          <template slot-scope="scope">
            {{ scope.row.fen_special_needs ? scope.row.fen_special_needs : '----' }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            width="120"
            label="是否接受赠单"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.get_gift_order) === 1 ? '是': '否' }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="赠单面积"
        >
          <template slot-scope="scope">
            {{ scope.row.mianji }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="开通状态"
        >
          <template slot-scope="scope">
            {{ scope.row.pnp_on_name ? scope.row.pnp_on_name : '----' }}
          </template>
        </el-table-column>
        <el-table-column
            align="center"
            label="授权状态"
        >
          <template slot-scope="scope">
            {{ scope.row.bind_num > 0 ? '已绑定' + (scope.row.bind_num) : '未绑定' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
          fixed="right"
          width="100"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleEdit(scope.row)">编辑</span>
            <span class="link-blue pointer" @click="handleTaping(scope.row)">录音</span>
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
    <memberCompanyEdit :show-edit-dia="editDia" :current-member-id="tempId" :city-option="cityOptions" @closeDia="handleCloseEditDia"></memberCompanyEdit>
    <el-dialog
        title="请选择批量开通虚拟号的城市"
        :visible.sync="openDialog"
        width="30%"
        :before-close="handleClose">
      <p>选择城市</p>
      <el-select v-model="openCity" class="opencity" filterable placeholder="请选择">
        <el-option
            v-for="item in cityOptions"
            :key="item.value"
            :label="item.value"
            :value="item.cid"
        >
        </el-option>
      </el-select>
      <el-card class="box-card">
        <span>注意</span> <br>
        1、若开通，则该城市装企所分配的订单的业主手机号将会被隐私保护，请第一时间通知装企为子账号的手机号授权，否则装企的子账号在订单有效期内，无法拨打成功。主账号默认可虚拟号拨打。<br>
        2、装企/装企子账号使用的本机号不是装企绑定的手机号，则无法拨打成功
      </el-card>
      <span slot="footer" class="dialog-footer">
      <el-button @click="openDialog = false">取 消</el-button>
      <el-button type="primary" @click="openPhone">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
  import { fetchMemberCompany, fetchPnp } from '@/api/setting'
  import { fetchCityList, fetchAreaList } from '@/api/common'
  import memberCompanyEdit from './components/memberCompanyEdit'
  export default {
    name: 'memberCompany',
    components: {
      memberCompanyEdit
    },
    data() {
      return {
        tempId: '',
        memberKey: '',
        cityVal: '0',
        cityOptions: [{
          cid: '0', cname: "请选择", value: "请选择", true_cname: "请选择", city_name: "请选择"
        }],
        areaVal: '0',
        areaOptions: [{
          area_id: '0', area: "请选择", value: "请选择"
        }],
        litAndLatVal: '0',
        litAndLatOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '有'
        }, {
          value: '2',
          label: '无'
        }],
        decortVal: '0',
        decortOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '半包'
        }, {
          value: '2',
          label: '全包'
        }, {
          value: '3',
          label: '半包/全包'
        }, {
          value: '99',
          label: '未填写'
        }],
        decortTypeVal: '0',
        userOptions: [
          {
            value: '',
            label: '请选择'
          }, {
            value: '2',
            label: '有效会员'
          }, {
            value: '-1',
            label: '过期会员'
          }, {
            value: '-4',
            label: '暂停会员'
          }
        ],
        user_on: '',
        decortTypeOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '家装'
        }, {
          value: '2',
          label: '公装'
        }, {
          value: '3',
          label: '公装/家装'
        }, {
          value: '99',
          label: '未填写'
        }],
        accessGrantVal: '0',
        accessGrantOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '是'
        }, {
          value: '2',
          label: '否'
        }],
        cooperate_mode: '',
        pnp_on: '',
        bind_status: '',
        hzOptions: [
          {
            value: '',
            label: '请选择'
          }, {
            value: '1',
            label: '常规会员'
          }, {
            value: '2',
            label: '新签返会员'
          }, {
            value: '3',
            label: '常规标杆会员'
          }, {
            value: '4',
            label: '新签返标杆会员'
          }
        ],
        xnhOptions: [
          {
            value: '',
            label: '请选择'
          }, {
            value: '1',
            label: '已开通'
          }, {
            value: '2',
            label: '未开通'
          }
        ],
        zqOptions: [
          {
            value: '',
            label: '请选择'
          }, {
            value: '2',
            label: '未绑定'
          }, {
            value: '1',
            label: '已绑定'
          }
        ],
        loading: false,
        tableData: [{
          company_name: '111',
          cname: '120'
        }],
        currentPage: 1,
        totals: 0,
        editDia: false,
        openDialog: false,
        openCity: ''
      }
    },
    created() {
      this.getCityList()
      this.getMemberCompanys()
    },
    methods: {
      getCityList() {
        fetchCityList().then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.cityOptions = this.cityOptions.concat(res.data.data[0])
          }
        })
      },
      getAreaList() {
        fetchAreaList({
          cid: this.cityVal
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.areaOptions = [{
              area_id: 0, area: "请选择", value: "请选择"
            }]
            this.areaVal = '0'
            this.areaOptions = this.areaOptions.concat(res.data.data[0])
          }
        })
      },
      getMemberCompanys() {
        this.loading = true
        fetchMemberCompany({
          user_keyword: this.memberKey,
          cs: this.cityVal,
          qx: this.areaVal,
          latlng: this.litAndLatVal,
          contract: this.decortVal,
          leixing: this.decortTypeVal,
          user_on: this.user_on,
          cooperate_mode: this.cooperate_mode,
          pnp_on: this.pnp_on,
          bind_status: this.bind_status,
          gift_order: this.accessGrantVal,
          page: this.currentPage
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tableData = res.data.data.list
            this.totals = res.data.data.page.total_number
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      handleSearch() {
        this.currentPage = 1
        this.getMemberCompanys()
      },
      // 分页处理
      handleSizeChange() {
        this.currentPage = 1
        this.getMemberCompanys()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.getMemberCompanys()
      },
      handleEdit(obj) {
        this.editDia = true
        this.tempId = String(obj.id)
      },
      handleCloseEditDia(tag) {
        this.editDia = false
        this.tempId = ''
        if(tag === 'updata') {
          this.getMemberCompanys()
        }
      },
      handleTaping(obj) {
        window.open('http://168new.qizuang.com/pnp/axborderlist?company_id=' + obj.id, '_blank')
      },
      // 批量开通虚拟号
      handleOpen() {
        this.openCity = ''
        this.openDialog = true
      },
      handleClose() {
        this.openDialog = false
      },
      openPhone() {
        if(!this.openCity) {
          this.$message.error('请选择城市进行虚拟号开通')
          return false
        }
        this.$confirm('是否确定开通该城市虚拟号，若开通则无法批量撤回请谨慎操作', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          fetchPnp({
            city_id: this.openCity
          }).then(res => {
            if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                message: '开通成功',
                type: 'success'
              })
              this.getMemberCompanys()
              setTimeout(() => {
                this.openDialog = false
              }, 2000)
            }
          })
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '已取消开通'
          })
        })
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-company {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
    .has-gutter th:nth-child(12), .has-gutter th:nth-child(13){
      color: green;
    }
    .has-gutter th:nth-child(14), .has-gutter th:nth-child(15){
      color: rgb(0, 153, 204);
    }
    .search {
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      overflow: hidden;
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
    .search{
      line-height: 36px;
    }
    .search-button{
      margin-top: 4px;
    }
    .opencity{
      width: 100%;
    }
    .box-card{
      margin-top: 40px;
      line-height: 24px;
      background: #FFE5E5;
      color: #FF4444;
      font-size: 12px;
      span{
        font-size: 14px;
      }
    }
  }
</style>
