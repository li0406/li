<template>
  <div class="member-identify">
    <div class="search">
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
            区域：<br>
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
            标识：<br>
            <div class="block">
            <el-select v-model="identifyVal" placeholder="请选择">
                <el-option
                v-for="item in identifyOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
                </el-option>
            </el-select>
            </div>
        </div>
        <div class="fl mr20">
            会员ID： <br>
            <el-input v-model="memberKey" placeholder="请输入会员ID"></el-input>
        </div>
        <div class="fl mr20">
            会员简称： <br>
            <el-input v-model="memberName" placeholder="请输入会员简称"></el-input>
        </div>
        <div>
            <br>
            <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
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
          width="250px"
        />
        <el-table-column
          align="center"
          label="所属城市"
          width="120px"
        >
          <template slot-scope="scope">
            {{ scope.row.city_name }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="area_name"
          label="所在区域"
          width="170px"
        />
        <el-table-column
          align="center"
          prop="lxs_name"
          label="新房/旧房"
          width="130px"
        />
        <el-table-column
          align="center"
          label="半包/全包"
          width="130px"
        >
          <template slot-scope="scope">
            {{ scope.row.contract_name }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="公装/家装"
          width="130px"
        >
          <template slot-scope="scope">
            {{ scope.row.leixing_name }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="tag_name"
          label="标识"
        />
        <el-table-column
          align="center"
          label="操作"
          width="120px"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleEdit(scope.row)">选择标识</span>
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
      <el-dialog title="请选择标识（最多选择10个）" :visible.sync="editDia" :close-on-click-modal="false" @close="handleClose">
        <el-form ref="memberForm" :model="memberForm">
            <el-form-item label="家装类：">
              <div>
                <el-tabs>
                    <el-checkbox v-for="item in memberForm.jz" :key="item" :label="item" v-model="checkArr" @change="check(item)">{{item}}</el-checkbox>
                </el-tabs>
              </div>
            </el-form-item>
            <el-form-item label="公装类：">
              <div>
                <el-tabs >
                    <el-checkbox v-for="item in memberForm.gz"  max='10' :key="item" v-model="checkArr" :label="item" @change="check(item)">{{item}}</el-checkbox>
                </el-tabs>
              </div>
            </el-form-item>
            <el-form-item label="施工类：">
              <div>
                <el-tabs >
                    <el-checkbox v-for="item in memberForm.sg" :key="item" :label="item" v-model="checkArr" @change="check(item)">{{item}}</el-checkbox>
                </el-tabs>
              </div>
            </el-form-item>
            <el-form-item class="center">
              <el-button type="primary" @click="onSubmit()">保 存</el-button>
              <el-button @click="handleClose">取 消</el-button>
            </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
  import { fetchMemberIdentify,fetchMemberIdentifyEdit,fetchMemberIdentifySave } from '@/api/setting'
  import { fetchCityList, fetchAreaList } from '@/api/common'

  export default {
    name: 'memberIdentify',
    data() {
      return {
        tempId: '',
        memberKey: '',
        memberName: '',
        cityName: '',
        cs:'',
        cityVal: '0',
        cityOptions: [{
          cid: '0', cname: "请选择", value: "请选择", true_cname: "请选择", city_name: "请选择"
        }],
        areaVal: '0',
        areaOptions: [{
          area_id: '0', area: "请选择", value: "请选择"
        }],
        identifyVal: '0',
        identifyOptions: [{
          value: '0',
          label: '请选择'
        },{
          value: '1',
          label: '有'
        },{
          value: '2',
          label: '没有'
        }
        ],
        loading: false,
        tableData: [{
          company_name: '111',
          cname: '120'
        }],
        currentPage: 1,
        totals: 1,
        editDia: false,
        memberForm:{
          jz: [],
          gz: [],
          sg: []
        },
        checkArr: []
      }
    },
    created() {
      this.getCityList()
      this.getMemberIdentify()
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
      getMemberIdentify() {
        this.loading = true
        fetchMemberIdentify({
          cs: this.cityVal,
          qx: this.areaVal,
          jc: this.memberName,
          tag: this.identifyVal,
          company_id: this.memberKey,
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
        this.getMemberIdentify()
      },
      // 分页处理
      handleSizeChange() {
        this.currentPage = 1
        this.getMemberIdentify()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.getMemberIdentify()
      },
      getCheckList() {
        fetchMemberIdentifyEdit({
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            var data = res.data.data.list
            this.memberForm.jz = data.jz
            this.memberForm.gz = data.gz
            this.memberForm.sg = data.sg
          }
        })
      },
      handleEdit(obj) {
        this.checkArr = []
        this.editDia = true
        this.tempId = String(obj.id)
        this.cityName = obj.city_name
        this.cs = obj.cs
        this.getCheckList()
      },
      check(obj) {
        if(this.checkArr.length > 10){
          this.$message({
            message: '最多选择10个标识',
            type: 'error',
          })
          return false
        }
      },
      handleClose() {
        this.editDia = false
      },
      emitCloseDiaFn(tag) {
        this.editDia = false
      },
      onSubmit() {
        if (this.checkArr.length == 0) {
          this.$message({
            message: '请选择至少1个标识',
            type: 'error',
          })
          return false
        } else if (this.checkArr.length > 10) {
          this.$message({
            message: '最多选择10个标识',
            type: 'error',
          })
          return false
        } else {
          fetchMemberIdentifySave({
            company_id: this.tempId,
            city_name:this.cityName,
            cs:this.cs,
            tag: this.checkArr.join(',')
          }).then(res => {
            if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                message: '编辑成功',
                type: 'success'
              })
              this.emitCloseDiaFn()
              this.getMemberIdentify()
            }else{
              this.$message.error(res.data.error_msg)
            }
          })
        }
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-identify {
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
      height: 120px;
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
    .el-tabs__header {
      display: none;
    }
    .el-checkbox{
      margin-left: 0;
      margin-right: 30px;
      width: 100px;
    }
    .center {
      text-align: center;
    }
  }
</style>
