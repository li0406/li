<template>
  <div class="baseinfo-city">
    <div class="main">
      <el-row :gutter="20">
        <el-col :span="3"><div class="grid-content bg-purple"><p><b>城市划分：</b></p></div></el-col>
        <el-col :span="3"><div class="grid-content bg-purple">
          <el-autocomplete
            v-model="selectVal"
            :fetch-suggestions="queryLSearch"
            placeholder="搜索"
            @select="handleLSelect"
            style="width: 100%"
          ></el-autocomplete>
          <div class="city-range" v-loading="selectLoading" @click="chooseLtrCity">
            <div v-for="(uCity, index) in allCitys" v-if="parseInt(uCity.is_relation) === 0" :data-cid="uCity.cid" :key="index" class="city-item">{{ uCity.value }}</div>
          </div>
        </div></el-col>
        <el-col :span="1">
          <div style="margin-top: 300px; text-align: center">
            <img src="@/assets/icon_images/switch.png" alt="arrow">
          </div>
        </el-col>
        <el-col :span="3"><div class="grid-content bg-purple">
          <el-autocomplete
            v-model="unSelectVal"
            :fetch-suggestions="queryRSearch"
            placeholder="搜索"
            @select="handleRSelect"
            style="width: 100%"
          ></el-autocomplete>
          <div class="city-range" v-loading="unSelectLoading" @click="chooseRtlCity">
            <div v-for="(city, index) in allCitys" v-if="parseInt(city.is_relation) === 1" :key="index" :data-cid="city.cid" class="city-item">{{ city.value }}</div>
          </div>
        </div></el-col>
      </el-row>
      <el-row style="margin-bottom: 20px;">
        <el-col :span="3">&nbsp;</el-col>
        <el-col :span="21" class="red">备注：选中的城市，其订单由远程人员操作分配，不会推送给稽核人员操作分配</el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="3"><div class="grid-content bg-purple">&nbsp;</div></el-col>
        <el-col :span="3"><div class="grid-content bg-purple">
          <el-button type="primary" icon="el-icon-search" @click="handleSave">保存</el-button>
        </div></el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
  import { fetchAuditCitySave } from '@/api/setting'
  import { fetchAllCitys, fetchSaveCitys, fetchSalerInfo, fetchAuditCityList } from '@/api/cityAccount'
  export default {
    name: "city",
    data() {
      return {
        selectVal: '',
        unSelectVal: '',
        unSelectCitys: [],
        selectCitys: [],
        allCitys: [],
        salerInfo: {},
        selectLoading: true,
        unSelectLoading: false,
        count: 0
      }
    },
    created() {
      this.getAllCityList()
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
        fetchAuditCityList().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.allCitys = res.data.data.city_all
            this.updataBothCity()
            this.selectLoading = false
            this.count++
          }
        })
      },
      updataBothCity() {
        this.unSelectCitys = []
        this.selectCitys = []
        this.allCitys.forEach(item => {
          if(item.value.indexOf('(') === -1) {
            item.value = item.value+'('+item.user_count+')'
          }
          if(!item.is_relation) {
            this.unSelectCitys.push(item)
          }else{
            this.selectCitys.push(item)
          }
        })
      },
      chooseLtrCity(evt) {
        const target = evt.target
        if(target.hasAttribute('data-cid')) {
          const cid = target.getAttribute('data-cid')
          this.handleLtrCitys(cid)
        }
      },
      handleLtrCitys(cid) {
        this.allCitys.forEach(item => {
          if(parseInt(item.cid) === parseInt(cid)) {
            item.is_relation = 1
          }
        })
        this.updataBothCity()
      },
      chooseRtlCity(evt) {
        const target = evt.target
        if(target.hasAttribute('data-cid')) {
          const cid = target.getAttribute('data-cid')
          this.handleRtlCitys(cid)
        }
      },
      handleRtlCitys(cid) {
        this.allCitys.forEach(item => {
          if(parseInt(item.cid) === parseInt(cid)) {
            item.is_relation = 0
          }
        })
        this.updataBothCity()
      },
      handleSave() {
        const citys = []
        this.allCitys.forEach((item, index) => {
          if(parseInt(item.is_relation) === 1) {
            citys.push(item.cid)
          }
        })
        fetchAuditCitySave({
          sale_citys: citys.join(',')
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '保存成功',
              type: 'success'
            });
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
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
        this.allCitys.forEach(jitem => {
          if(parseInt(jitem.cid) === parseInt(item.cid)) {
            item.is_relation = 1
          }
        })
        this.updataBothCity()
      },
      handleRSelect(item) {
        this.allCitys.forEach(jitem => {
          if(parseInt(jitem.cid) === parseInt(item.cid)) {
            item.is_relation = 0
          }
        })
        this.updataBothCity()
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .baseinfo-city{
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
