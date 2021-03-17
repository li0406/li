/**
 * +----------------------------------------------------------------------
 * | 城市控件，几乎每个页面都会用到，抽出来
 * +----------------------------------------------------------------------
 * | Author: zwl
 * +----------------------------------------------------------------------
 */
import { fetchCityList } from '@/api/common'

export default {
    data() {
        return {
            citySelectStr: '', // 存放城市文本
            citySelectVal: '', // 存放城市对应id
            citysArr: [], // 城市数组
            cityBlurFlag: null // 是否通过点击城市进行选择标志
        }
    },
    watch: {
        // 如果城市文本被清空，则清空保存的城市id及标志
        citySelectStr(value) {
            if(!value) {
                this.citySelectVal = ''
                this.cityBlurFlag = null
            }
        }
    },
    created() {
        this.loadAllCity()
    },
    methods: {
        // 加载城市数据
        loadAllCity() {
            fetchCityList().then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    const data = res.data.data[0]
                    data.forEach((item, index) => {
                        this.citysArr.push(item)
                    })
                    this.initCityInput()
                } else {
                    this.citysArr = []
                }
            })
        },
        // 用于编辑页面回显城市
        initCityInput() {
            if(this.citySelectVal) {
                this.citysArr.forEach((item, index) => {
                    if(this.citySelectVal == item.cid) {
                        this.citySelectStr = item.value
                    }
                })
            }
        },
        handleQueryCity(queryString, cb) {
            const citys = this.citysArr
            const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
            this.cityBlurFlag = null
            cb(results)
        },
        createFilter(queryString) {
            return (city) => {
                return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1)
            }
        },
        handleCitySelect(item) {
            this.cityBlurFlag = item
            this.citySelectVal = item.cid
            this.citySelectStr = item.value
        },
        handleCityBlur() {
            if (!this.cityBlurFlag) {
                this.citySelectVal = ''
                this.citySelectStr = ''
                this.cityBlurFlag = null
            }
        }
    }
}
