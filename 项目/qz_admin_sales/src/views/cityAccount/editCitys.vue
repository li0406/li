<template>
  <div class="edit-citys">
    <div class="main">
      <el-row :gutter="20">
        <el-col :span="3"><div class="grid-content bg-purple"><b>账号：</b> {{ salerInfo.saler_name }}</div></el-col>
        <el-col :span="3"><div class="grid-content bg-purple"><b>角色：</b>{{ salerInfo.roler }}</div></el-col>
        <el-col :span="3"><div class="grid-content bg-purple"><b>部门：</b> {{ salerInfo.department }}</div></el-col>
        <el-col :span="6"><div class="grid-content bg-purple">
          <b>在职情况：</b> {{ parseInt(salerInfo.state) === 0 ? "离职" : "在职" }}
        </div></el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="3"><div class="grid-content bg-purple"><p><b>管辖城市：</b></p></div></el-col>
        <el-col :span="3"><div class="grid-content bg-purple">
          <p>未选取城市</p>
          <el-autocomplete
            v-model="selectVal"
            :fetch-suggestions="queryLSearch"
            placeholder="搜索"
            @select="handleLSelect"
            style="width: 100%"
          ></el-autocomplete>
          <div class="city-range" v-loading="selectLoading" @click="chooseLtrCity">
            <div v-for="(uCity, index) in unSelectCitys" :data-cid="uCity.cid" :key="index" class="city-item">{{ uCity.city_name }}</div>
          </div>
        </div></el-col>
        <el-col :span="1">
          <div style="margin-top: 300px; text-align: center">
            <p>---></p>
            <p><---</p>
          </div>
        </el-col>
        <el-col :span="3"><div class="grid-content bg-purple">
          <p>已选取城市</p>
          <el-autocomplete
            v-model="unSelectVal"
            :fetch-suggestions="queryRSearch"
            placeholder="搜索"
            @select="handleRSelect"
            style="width: 100%"
          ></el-autocomplete>
          <div class="city-range" v-loading="unSelectLoading" @click="chooseRtlCity">
            <div v-for="(city, index) in selectCitys" :key="index" :data-cid="city.cid" class="city-item">{{ city.city_name }}</div>
          </div>
        </div></el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="3"><div class="grid-content bg-purple">&nbsp;</div></el-col>
        <el-col :span="3"><div class="grid-content bg-purple">
          <el-button type="primary" icon="el-icon-search" @click="handleSave">保存</el-button>
          <el-button plain @click="handleCancel">取消</el-button>
        </div></el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
  import { fetchAllCitys, fetchSaveCitys, fetchSalerInfo } from '@/api/cityAccount'
  export default {
    name: "editCitys",
    data() {
      return {
        selectVal: '',
        unSelectVal: '',
        unSelectCitys: [],
        selectCitys: [],
        salerInfo: {},
        selectLoading: true,
        unSelectLoading: true,
        count: 0
      }
    },
    created() {
      this.getAllCityList()
      this.getSalerInfo()
    },
    watch: {
      count(value){
        if(2 === value) {
          const empty = [], tempCity = []
          this.selectCitys.forEach((item, index) => {
            this.unSelectCitys.forEach((jitem, jindex) => {
              if(jitem.cid && item.cid === jitem.cid) {
                empty.push(jindex)
              }
            })
          })
          this.unSelectCitys.forEach((item, index) => {
            if(empty.indexOf(index) === -1) {
              tempCity.push(item)
            }
          })
          this.unSelectCitys = tempCity
          this.count = 0
        }
      }
    },
    methods: {
      getAllCityList() {
        fetchAllCitys().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.unSelectCitys = res.data.data[0]
            this.selectLoading = false
            this.count++
          }
        })
      },
      getSalerInfo() {
        fetchSalerInfo({
          id: this.$route.params.id
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data
            this.salerInfo = data
            if (data.citys.length > 0) {
              this.selectCitys = data.citys
            }
            this.unSelectLoading = false
            this.count++
          }
        })
      },
      chooseLtrCity(evt) {
        const target = evt.target
        const cid = target.getAttribute('data-cid')
        this.handleLtrCitys(cid)
      },
      handleLtrCitys(cid) {
        // 实现两个框城市切换
        let currentIndex = 999999999999999
        this.unSelectCitys.forEach((item, index) => {
          if(parseInt(item.cid) === parseInt(cid)) {
            currentIndex = index
          }
        })
        this.selectCitys.push(this.unSelectCitys[currentIndex])
        this.unSelectCitys.splice(currentIndex, 1)
      },
      chooseRtlCity(evt) {
        const target = evt.target
        const cid = target.getAttribute('data-cid')
        this.handleRtlCitys(cid)
      },
      handleRtlCitys(cid) {
        // 实现两个框城市切换
        let currentIndex = 999999999999999
        this.selectCitys.forEach((item, index) => {
          if(parseInt(item.cid) === parseInt(cid)) {
            currentIndex = index
          }
        })
        this.unSelectCitys.push(this.selectCitys[currentIndex])
        this.selectCitys.splice(currentIndex, 1)
      },
      handleSave() {
        const citys = []
        this.selectCitys.forEach((item, index) => {
          citys.push(item.cid)
        })
        fetchSaveCitys({
          id: this.$route.params.id,
          citys: citys
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '修改成功',
              type: 'success'
            });
            setTimeout(() => {
              this.$router.push({
                path: '/cityAccount/citys'
              })
            }, 1500)
          }
        })
      },
      handleCancel() {
        this.$router.push({
          path: '/cityAccount/citys'
        })
      },
      queryLSearch(city, cb) {
        const allCity = this.unSelectCitys
        const results = city ? allCity.filter(this.createFilter(city)) : allCity
        // 调用 callback 返回建议列表的数据
        cb(results)
      },
      queryRSearch(city, cb) {
        const allCity = this.selectCitys
        const results = city ? allCity.filter(this.createFilter(city)) : allCity
        // 调用 callback 返回建议列表的数据
        cb(results)
      },
      createFilter(queryString) {
        return (city) => {
          return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1)
        }
      },
      handleLSelect(item) {
        let currentIndex = 999999999999999
        this.unSelectCitys.forEach((jitem, index) => {
          if(parseInt(jitem.cid) === parseInt(item.cid)) {
            currentIndex = index
          }
        })
        this.selectCitys.push(item)
        this.unSelectCitys.splice(currentIndex, 1)
      },
      handleRSelect(item) {
        let currentIndex = 999999999999999
        this.selectCitys.forEach((jitem, index) => {
          if(parseInt(jitem.cid) === parseInt(item.cid)) {
            currentIndex = index
          }
        })
        this.unSelectCitys.push(item)
        this.selectCitys.splice(currentIndex, 1)
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .edit-citys{
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .main {
      padding: 20px;
      margin-bottom: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
    }
    .city-range{
      border:1px solid #CCC;
      height: 600px;
      margin: 20px 0;
      overflow-y: scroll;
      .city-item{
        line-height: 30px;
        padding-left: 20px;
        border-bottom: 1px #eee solid;
        cursor: pointer;
        color: #555;
        user-select: none;
      }
    }
  }
</style>
