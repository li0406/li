<template>
  <div class="bd-map">
    <div class="top">
      城市：
      <el-row>
        <el-col :span="3">
          <el-select v-model="cs" filterable placeholder="请选择" @change="cityChange">
            <el-option
              v-for="item in citys"
              :key="item.cid"
              :label="item.cname"
              :value="item"
            />
          </el-select>
        </el-col>
        <el-col :span="17">
          <p>此地图仅供客服和销售标注装修公司位置，如果要查看订单位置，请直接百度地图</p>
        </el-col>
        <el-col :span="4">
          <bm-control>
            <el-button type="primary" icon="el-icon-edit" @click="openDistanceTool">测距</el-button>
            <el-button type="primary" icon="el-icon-location" @click="sign">标记</el-button>
          </bm-control>
        </el-col>

      </el-row>
    </div>
    <el-row>
      <el-col :span="19">
        <baidu-map
          ref="map"
          :center="center"
          :zoom="zoom"
          ak="q23cg2dVNxDL71KLzs64mujph1fE6zX4"
          class="bm-view"
          :map-click="false"
          :scroll-wheel-zoom="true"
          @ready="handler"
          @mousemove="syncMarker"
          @click="newMarker"
          @rightclick="paintMarker"
        >
          <!--比例尺控件-->
          <bm-scale />
          <!--缩放控件-->
          <bm-navigation anchor="BMAP_ANCHOR_TOP_RIGHT" />
          <bm-marker v-for="(item, index) in company" :key="index" :position="{lng: item.lng, lat: item.lat}" :dragging="false" :title="item.address" @click="infoWindowOpen(item)">
            <bm-label v-if="item.companies" :content="item.companies" :label-style="{color: 'red', fontSize : '12px'}" :offset="{width: 30, height: 0}" />
            <bm-info-window :show="item.showFlag" :width="300" :auto-pan="true" @close="infoWindowClose(item)">
              <el-form ref="form" :model="item" label-width="60px" style="margin-top:20px">
                <el-form-item label="描述：">
                  <el-input v-model="markCompanies" type="textarea" placeholder="标注描述" />
                </el-form-item>
                <el-form-item label="地址：">
                  {{ item.address }}
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" size="mini" @click="saveBtn(item)">确定</el-button>
                  <el-button size="mini" @click="delBtn(item,index)">删除</el-button>
                </el-form-item>
              </el-form>
            </bm-info-window>
          </bm-marker>
          <bm-marker v-if="isclick" :position="{lng: addMarker.lng, lat: addMarker.lat}" :dragging="true" :title="addMarker.address" @click="infoWindowOpen(item)">
            <bm-label content="点击地图添加标注" :label-style="{color: 'red', fontSize : '12px'}" :offset="{width: 30, height: 0}" />
          </bm-marker>
        </baidu-map>
      </el-col>
      <el-col :span="5">
        <div class="p20">
          <template>
            <el-table
              v-loading="loading"
              :data="company"
              max-height="650"
              @row-click="infoWindowOpen"
            >
              <el-table-column
                fixed
                label="公司列表"
              >
                <template v-if="scope.row.last_modified_by" slot-scope="scope">
                  {{ scope.row.companies }} - {{ scope.row.last_modified_by }}
                </template>
              </el-table-column>
            </el-table>
          </template>
        </div>

      </el-col>
    </el-row>

  </div>
</template>

<script>
import { fetchPrivcitys, fetchCompanyList, fetchAddCompanyInfo, fetchDelentCompanyInfo } from '@/api/map'
import { BaiduMap, BmScale, BmNavigation, BmMarker, BmInfoWindow, BmControl, BmLabel } from 'vue-baidu-map'
import DistanceTool from 'bmaplib.distancetool'
export default {
  name: 'Map',
  components: {
    BaiduMap,
    BmScale,
    BmNavigation,
    BmMarker,
    BmInfoWindow,
    BmLabel,
    BmControl
  },
  unmount() {
    distanceTool && distanceTool.close()
  },
  data() {
    return {
      BMap: '',
      map: '',
      BMapLib: '',
      center: '', // 地图位置
      zoom: 12, // 地图缩放
      citys: [], // 城市列表
      citysId: '', // 城市id
      company: [], // 装修公司标记列表
      cs: '', // 城市列表选中值
      loading: false,
      markCompanies: '', // 标记描述
      bmInfo: {}, // 当前标记数据
      isclick: false,
      addMarker: {
        lng: '',
        lat: '',
        address: '',
        markCompanies: '',
        showFlag: false
      }
    }
  },
  mounted() {
    this.loadAllCity()
  },
  created() {
    const id = this.$route.query.city
    const name = this.$route.query.name
    if (id) {
      this.citysId = id
      this.center = name
      this.getCompanyList(this.citysId)
    }
    if (name) {
      this.center = name
    }
  },
  methods: {
    // 构建地图
    handler({ BMap, map }) {
      const that = this
      that.BMap = BMap
      that.map = map
      // that.center = '苏州'
      var geolocation = new BMap.Geolocation()
      geolocation.getCurrentPosition((r) => { // 定位
        // console.log(r)
        if (that.center === '') {
          that.center = { lng: r.longitude, lat: r.latitude }		// 设置center属性值
        }
      }, { enableHighAccuracy: true })
      this.distanceTool = new DistanceTool(map, { lineStroke: 2 })
    },
    // 测距
    openDistanceTool(e) {
      const { distanceTool } = this
      distanceTool && distanceTool.open()
    },
    // 标记
    sign(e) {
      this.isclick = !this.isclick
    },
    syncMarker(e) {
      const that = this
      if (that.isclick) {
        that.addMarker.lng = e.point.lng
        that.addMarker.lat = e.point.lat
      }
    },
    newMarker(e) {
      const that = this
      if (that.isclick) {
        that.markCompanies = ''
        that.isclick = false
        const obj = {}
        obj.lng = e.point.lng
        obj.lat = e.point.lat
        var point = new BMap.Point(e.point.lng, e.point.lat)
        var gc = new BMap.Geocoder()
        gc.getLocation(point, function(rs) {
          var address = rs.address
          obj.address = address
          obj.showFlag = true
          that.bmInfo = obj
          that.company.push(obj)
        })
      }
    },
    paintMarker() {
      this.isclick = false
    },
    // 获取城市
    loadAllCity() {
      fetchPrivcitys().then(res => {
        const that = this
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const data = res.data.data.list
          data.forEach((item, index) => {
            this.citys.push(item)
            if (that.citysId === item.cid) {
              that.cs = item
            }
          })
        } else {
          this.citys = []
        }
      })
    },
    // 获取公司列表
    getCompanyList(id) {
      const that = this
      that.loading = true
      fetchCompanyList({ cityid: id }).then(res => {
        that.loading = false
        that.company = []
        res.data.data.marks.forEach(t => {
          t.showFlag = false
          that.company.push(t)
        })
      })
    },
    // 城市切换
    cityChange(val) {
      this.$router.push({
        name: 'map',
        query: {
          city: val.cid,
          name: val.true_cname
        }
      })
    },
    // 关闭
    infoWindowClose(item) {
      item.showFlag = false
    },
    // 显示标记
    infoWindowOpen(item) {
      this.bmInfo = JSON.parse(JSON.stringify(item))
      this.markCompanies = this.bmInfo.companies
      item.showFlag = true
    },
    // 保存标注
    saveBtn(item) {
      const that = this
      const obj = that.getData()
      fetchAddCompanyInfo(obj).then(res => {
        if (res.data.error_code === 0) {
          that.$message.success('保存成功')
          item.companies = obj.com
          item.showFlag = false
          that.company = []
          that.getCompanyList(that.citysId)
        } else {
          that.$message.success('服务器错误')
        }
      })
    },
    // 删除标注
    delBtn(item, index) {
      const that = this
      const obj = {}
      obj.id = item.id
      if (obj.id) {
        this.$confirm('确定删除?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          fetchDelentCompanyInfo(obj).then(res => {
            // console.log(res)
            if (res.data.error_code === 0) {
              that.$message.success('删除成功')
              that.company.splice(index, 1)
              item.showFlag = false
              that.company = []
              that.getCompanyList(that.citysId)
            } else {
              that.$message.success('服务器错误')
            }
          })
        })
      } else {
        that.company.splice(index, 1)
      }
    },
    // 保存参数
    getData() {
      const that = this
      const obj = {}
      if (that.bmInfo.id) {
        obj.id = that.bmInfo.id
      }
      if (!that.markCompanies) {
        this.$message.warning('请输入标注')
        return false
      }
      obj.lng = that.bmInfo.lng
      obj.lat = that.bmInfo.lat
      obj.com = that.markCompanies
      obj.addr = that.bmInfo.address
      obj.cityid = that.citysId
      return obj
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.bd-map{
  margin: 20px;
  .top{
    h4{
      margin-left: 20px;
    }
  }
  .p20{
    padding:0 20px;
  }
  .el-form-item{
    margin-bottom: 5px;
  }
}
.bm-view {
  width: 100%;
  height: 650px;
}
</style>
