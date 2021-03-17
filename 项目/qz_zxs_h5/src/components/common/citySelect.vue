<template>
  <section id="mask-picker">
    <div style="width:100%; height:100%;" @click.stop="cancel"></div>
    <div :class="isHide?'m-picker-box':'m-picker-box open-picker'">
      <div class="m-picker-tool">
        <span class="m-picker-cancel" @click="cancel">取消</span>
        <span class="m-picker-ok" @click="okBtn">确定</span>
      </div>
      <div v-if="cityShow">
        <mt-picker  :slots="slots"  @change="onValuesChange" value-key="name"></mt-picker>
      </div>
      <m-tips ref="tips"/>
    </div>
  </section>
</template>
<script>
import { getCity, mapLocation } from "@/api/api";
import mTips from './mTips.vue' // 引入tips 提示框
// import BMap from 'BMap'
export default {
  name: "citySelect",
  props: ["csInfo"],
  components: {
    mTips
  },
  data() {
    return {
      slots: [
        {
          flex: 1,
          values: [],
          className: "slot1",
          textAlign: "right",
          defaultIndex: 0
        },
        {
          divider: true,
          content: "-",
          className: "slot2"
        },
        {
          flex: 1,
          values: [],
          className: "slot3",
          textAlign: "center",
          defaultIndex: 0
        },
        {
          divider: true,
          content: "-",
          className: "slot4"
        },
        {
          flex: 1,
          values: [],
          className: "slot5",
          textAlign: "left",
          defaultIndex: 0
        }
      ],
      oldPro: "",
      oldCity: "",
      oldArea: "",
      isHide: true,
      value: "",
      address: {
        province: "江苏省",
        city: "苏州市"
      },
      // ip定位城市数据
      ipInfo: [],
      // fallback 表单城市数据
      lastInfo:  [
        {id: 320000, name: "请继续提交，已为您识别城市！"},
        {id: "320500",name: ""},
        {id: 320508, name: ""},
      ],
      cityShow: false
    };
  },
  created() {
    let that = this;
    let parentCityId = this.csInfo || "0";
    this.getLocation(() => {
        if (sessionStorage.cityData) {
            this.initCityData(JSON.parse(sessionStorage.cityData), parentCityId);
        } else {
          getCity().then(res => {
            if (res.data.error_code === 0) {
              this.initCityData(res.data.data, parentCityId);
              sessionStorage.cityData = JSON.stringify(res.data.data);
            } else {
              that.$refs.tips.tipsFadeIn({
                text: '获取城市列表失败，可以继续提交,城市自动识别'
              })
            }
          }).catch(() => {
            const ipInfo = this.ipInfo
            that.$emit("getCityVlaue", ipInfo);
            sessionStorage.currentCity = JSON.stringify(ipInfo);
          });
        }
    });

  },
  watch: {
    isHide() {
      // let mybody = document.getElementsByTagName('body')[0]
      let maskPicker = document.getElementById("mask-picker");
      if (!this.isHide) {
        // mybody.style = 'overflow-y:hidden;position:fixed'
        let cssStyle =
          "position:fixed;left:0px;top:0px;z-index:999; width:10000px; height:10000px; background:rgba(0,0,0,0.6)";
        maskPicker.style = cssStyle;
        maskPicker.addEventListener(
          "touchmove",
          function(event) {
            event.preventDefault();
          },
          false
        );
      } else {
        // mybody.style = 'overflow-y:auto;position:initial'
        maskPicker.style = "";
        maskPicker.removeEventListener(
          "touchmove",
          function(event) {
            event.preventDefault();
          },
          false
        );
      }
    }
  },
  methods: {
    okBtn() {
      this.isHide = true;
      this.$emit("getCityVlaue", this.value);
    },
    cancel() {
      this.isHide = true;
      return false;
    },
    cityif() {
      this.isHide = true;
      this.cityShow = false;
      return false
    },
    openPicker() {
      this.isHide = false;
      this.cityShow = true;
    },
    getLocation(cb) {
      mapLocation().then(res => {
        if (
          parseInt(res.status) === 200 &&
          parseInt(res.data.error_code) === 0
        ) {
          const address = res.data.data
          this.ipInfo = [
            { id: address.province_id, name: address.provice_name},
            { id: address.city_id, name: address.city_name},
            { id: address.area_id, name: address.area_name},
          ]
          this.address.province = address.provice_name;
          this.address.city = address.city_name;
        }
        cb && cb.call()
      }).catch(() => {
          const lastInfo = this.lastInfo
          this.$emit("getCityVlaue", lastInfo);
          sessionStorage.currentCity = JSON.stringify(lastInfo);
      });
    },
    initCityData(data, csInfo) {
      let that = this;
      let initAddress = null;
      let BMap = null;
      if (csInfo !== "0") {
        // 手动定位
        that.setInitAddress(data, csInfo);
      } else {
        // 自动定位，由于百度地图每年都要挂掉几次，这里就废弃不用了
        if (BMap) {
          let geolocation = new BMap.Geolocation();
          geolocation.getCurrentPosition(function(cityRes) {
            initAddress =
              cityRes.address.province && cityRes.address.city
                ? cityRes
                : { address: that.address };
            if (sessionStorage.currentCity) {
              let currentCity = JSON.parse(sessionStorage.currentCity);
              if (cityRes.address.city.indexOf(currentCity[1].name) !== -1) {
                that.$emit("getCityVlaue", currentCity);
              }
              that.setInitAddress(data, initAddress);
            } else {
              that.setInitAddress(data, initAddress);
            }
          });
        } else {
          initAddress = { address: that.address };
          that.setInitAddress(data, initAddress);
        }
      }
    },
    setInitAddress(data, cityRes) {
      let that = this;
      let province = [];
      let lastInfo = [];
      let proIndex = 0;
      for (let i in data) {
        if (data[i].child.length === 0) {
          delete data[i];
        } else {
          province.push(data[i]); // 将对象转数组
          that.oldPro = cityRes.address.province;
          that.oldCity = cityRes.address.city;
          if (data[i].name === cityRes.address.province) {
            let provInfo = {
              id: data[i].id,
              name: data[i].name
            };
            proIndex = province.length - 1; // 初始化省默认序号
            lastInfo.push(provInfo);
            for (let k = 0; k < data[i].child.length; k++) {
              if (cityRes.address.city.indexOf(data[i].child[k].name) !== -1) {
                let cityInfo = {
                  name: data[i].child[k].name,
                  id: data[i].child[k].id
                };
                let areaInfo = {
                  id: data[i].child[k].child[0].id,
                  name: data[i].child[k].child[0].name
                };
                that.slots[2].values = data[i].child;
                that.slots[2].defaultIndex = k;
                that.slots[4].values = data[i].child[k].child;
                that.slots[4].defaultIndex = 0;
                that.oldArea = data[i].child[k].child[0].name;
                lastInfo.push(cityInfo);
                lastInfo.push(areaInfo);
                that.$emit("getCityVlaue", lastInfo);
                sessionStorage.currentCity = JSON.stringify(lastInfo);
              }
            }
          }
        }
      }
      that.slots[0].values = province;
      that.slots[0].defaultIndex = proIndex;
    },
    onValuesChange(picker, value) {
      if (!value[2]) {
        return false;
      }
      let that = this;
      let proName = value[0].name;
      let cityName = value[1].name;
      let areaName = value[2].name;
      if (proName !== that.oldPro) {
        // 省变
        picker.setSlotValues(1, value[0].child);
        that.oldPro = proName;
      }
      if (cityName !== that.oldCity) {
        // 市变
        picker.setSlotValues(2, value[1].child);
        that.oldCity = cityName;
      }
      if (areaName !== that.oldArea) {
        // 区变
        picker.setSlotValues(3, value[2].child);
        that.oldArea = areaName;
      }
      let lastInfo = [
        {
          id: value[0].id,
          name: value[0].name
        },
        {
          id: value[1].id,
          name: value[1].name
        },
        {
          id: value[2].id,
          name: value[2].name
        }
      ];
      that.value = lastInfo;
    }
  }
};
</script>
<style scoped>
.mask-picker {
  width: 100%;
  height: 100%;
  z-index: 999;
  background: red;
}
.m-picker-box {
  position: fixed;
  left: 0px;
  bottom: -250px;
  width: 100%;
  background: #fff;
  transition: all 0.5s;
  -webkit-transition: all 0.5s; /* Safari */
  z-index: 9999;
}
.m-picker-tool {
  padding: 10px 18px;
  border-bottom: 1px solid #dedede;
  overflow: hidden;
  color: #ff5353;
}
.m-picker-cancel {
  float: left;
}
.m-picker-ok {
  float: right;
}
.open-picker {
  bottom: 0px;
}
</style>
