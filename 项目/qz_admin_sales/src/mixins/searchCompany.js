/**
 * +----------------------------------------------------------------------
 * | 检索装修公司（根据装修公司ID/简称/全称 获取装修公司）
 * +----------------------------------------------------------------------
 * | Author: zwl
 * +----------------------------------------------------------------------
 */
import { fetchFindUser } from '@/api/common'

export default {
    data() {
        return {
            companyBlurFlag: null,
            companySearchTimeout: null,
            resultCompanys: [],
            companyId: '',
            companyCodeOrName: ''
        }
    },
    methods: {
        searchCompanyByIdOrName(queryString, cb) {
            this.companyBlurFlag = null
            this.searchUserByIdOrName(queryString, () => {
                clearTimeout(this.companySearchTimeout)
                this.companySearchTimeout = setTimeout(() => {
                    cb(this.resultCompanys)
                }, 1000)
            })
        },
        searchUserByIdOrName(query, cb) {
            fetchFindUser({ uid: query }).then(res => {
                const data = res.data.data[0]
                this.resultCompanys = data
                cb && cb.call()
            })
        },
        handleCompanySelect(item) {
            this.companyBlurFlag = item
            this.companyId = item.id
        },
        handleCompanyBlur() {
            if(!this.companyBlurFlag) {
                this.companyId = ''
                this.companyCodeOrName = ''
            }
        },
    }
}
