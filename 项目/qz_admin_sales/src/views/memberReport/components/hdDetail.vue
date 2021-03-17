<template>
    <div class="home-decoration-detail">
        <div class="main">
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>1、公司名称：</div></el-col>
                <el-col :span="18">{{ companyName }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>2、合作类型：</div></el-col>
                <el-col :span="18">{{ memberName }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>3、城市：</div></el-col>
                <el-col :span="18">{{ city }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、单倍/几倍：</div></el-col>
                <el-col :span="18">{{ memberType === 14 ? '标杆会员（保产值）' : ratio }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>5、新/老会员：</div></el-col>
                <el-col :span="18">{{ isNewMember }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>6、联系人：</div></el-col>
                <el-col :span="18">{{ contact }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">7、职务：</div></el-col>
                <el-col :span="18">{{ duties ? duties : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>8、电话：</div></el-col>
                <el-col :span="18">{{ phone }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">9、微信号：</div></el-col>
                <el-col :span="18">{{ wechat ? wechat : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>10、总合同时间：</div></el-col>
                <el-col :span="18">{{ contractTime }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">备注：</div></el-col>
                <el-col :span="18">{{ totalContractRemark }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>11、本次会员时间：</div></el-col>
                <el-col :span="6" :class="is_abnormal">{{ memberTime }}</el-col>
                <el-col :span="4" style="position:relative;"><p class="red keftips">客服注意，请记得按这个上传，销售勿删</p></el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">备注：</div></el-col>
                <el-col :span="18">{{ contractRemark }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>12、本次款项到账时间：</div></el-col>
                <el-col :span="18">{{ moneyInTime }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">13、下次付款时间：</div></el-col>
                <el-col :span="18">{{ nextpayTime ? nextpayTime : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>14、总合同金额：</div></el-col>
                <el-col :span="18">{{ totalMoney }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">平台使用费：</div></el-col>
                <el-col :span="2">{{ info.total_platform_money }}</el-col>
                <el-col v-if="info.total_platform_money" :span="4"><div class="lh-40">平台使用费有效期：</div></el-col>
                <el-col v-if="info.total_platform_money" :span="4">{{ info.total_platform_money_start_date }} - {{ info.total_platform_money_end_date }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">会员费：</div></el-col>
                <el-col :span="2">{{ info.total_contract_round_amount }}</el-col>
            </el-row>
            <el-row v-if="memberType === 14" class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">总合同保产值金额：</div></el-col>
                <el-col :span="2">{{ info.total_contract_amount_guaranteed }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>15、本次到账金额：</div></el-col>
                <el-col :span="6" :class="report_money_compart">{{ getMoney }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">平台使用费：</div></el-col>
                <el-col :span="2">{{ info.current_platform_money }}</el-col>
                <el-col v-if="info.current_platform_money" :span="4"><div class="lh-40">平台使用费有效期：</div></el-col>
                <el-col v-if="info.current_platform_money" :span="4">{{ info.current_platform_money_start_date }} - {{ info.current_platform_money_end_date }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">会员费：</div></el-col>
                <el-col :span="2">{{ info.current_contract_round_amount }}</el-col>
                <el-col v-if="showSmall" :span="4" style="padding-left:38px"><div class="lh-40">小报备：</div></el-col>
                <el-col v-if="showSmall" :span="4" style="color:green;">{{ paymentMoney }}（{{ paymentNumber }}）  次</el-col>
            </el-row>
            <el-row v-if="memberType === 14" class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">本次到账保产值金额：</div></el-col>
                <el-col :span="2">{{ info.current_contract_amount_guaranteed }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">16、余款多少：</div></el-col>
                <el-col :span="18">{{ String(lastMoney) === '-1' ? '----' : lastMoney }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6">
                    <div class="lh-40"><i class="red">*</i>17、订单承诺量：</div>
                </el-col>
                <el-col :span="18">
                    <div class="promiseorder">
                        <span class="inline-block one">总合同</span>
                        <span class="inline-block two">分单：<span class="c90f">{{ promisedOrdersFen }}</span></span>
                        <span class="inline-block three">赠单：<span class="c90f">{{ promisedOrdersZeng }}</span></span>
                        <span v-if="isShowtsl" class="inline-block w200 red">(系统折算订单量：{{ totalConvertOrderCount ? totalConvertOrderCount : '-' }})</span>
                        <span class="inline-block four" v-if="yearMonthOne"><i class="red">(过年月</i>{{ yearMonthOne }}<i class="red">不承诺订单量)</i></span>
                        <span style="margin-left: 20px" class="inline-block four" v-if="yearMonthTwo"><i class="red">(过年月</i>{{ yearMonthTwo }}<i class="red">不承诺订单量)</i></span>
                    </div>
                    <div class="promiseorder" style="margin-bottom: 0">
                        <span class="inline-block one">本次合同</span>
                        <span class="inline-block two">分单：<span class="c90f">{{ currentPromisedOrdersFen }}</span></span>
                        <span class="inline-block three">赠单：<span class="c90f">{{ currentPromisedOrdersZeng }}</span></span>
                        <span v-if="isShowtsl" class="inline-block w200 red">(系统折算订单量：{{ convertOrderCount ? convertOrderCount : '-' }})</span>
                        <span class="inline-block four" v-if="current_year_month_one"><i class="red">(过年月</i>{{ current_year_month_one }}<i class="red">不承诺订单量)</i></span>
                        <span style="margin-left: 20px" class="inline-block four" v-if="current_year_month_two"><i class="red">(过年月</i>{{ current_year_month_two }}<i class="red">不承诺订单量)</i></span>
                    </div>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">18、LOGO位置：</div></el-col>
                <el-col :span="18">{{ logoPos ? logoPos : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">19、通栏位置：</div></el-col>
                <el-col :span="18">{{ bannerPos | bannerPosFilter }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">20、总合同广告赠送：</div></el-col>
                <el-col :span="18">轮显{{ giftAdCircleDays }}天 通栏{{ giftAdBannerDays }}天</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">21、本次上广告情况：</div></el-col>
                <el-col :span="18">{{ adOnlineNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">22、接单金额及区域：</div></el-col>
                <el-col :span="18">{{ orderAreaNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>23、家装/公装是否都接：</div></el-col>
                <el-col :span="4">{{ isAllOrder }}</el-col>
                <el-col :span="14">{{isAllOrderOther}}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">24、网店代码：</div></el-col>
                <el-col :span="18">{{ onlineShopCode }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">25、备注：</div></el-col>
                <el-col :span="18">{{ otherNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">26、上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in img_list' :key="item">
                        <img :src="item" alt="" class="upimg" @click="preview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">27、客服上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in kfuploadImg' :key="item">
                        <img :src="item" alt="" class="upimg" @click="kfpreview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">28、开站讨论组备注：</div></el-col>
                <el-col :span="18">
                    <el-col :span="18">{{ openCityChatRemark }}</el-col>
                </el-col>
            </el-row>
            <!--图片预览-->
            <div id="app">
                <div class="imgMask" v-if="centerDialogVisible" @click.stop="centerDialogVisible=!centerDialogVisible">
                    <i class="iconfont el-icon-arrow-left prev" @click.stop="prev"></i>
                    <div class="showImg" >
                        <img class="bigImg" :src="url[num]">
                    </div>
                    <i class="iconfont el-icon-arrow-right next" @click.stop="next"></i>
                </div>
            </div>
        </div>
        <logs></logs>
    </div>
</template>

<script>
    import memberReportDetail from '@/mixins/memberReport'
    import { fortmatDate } from '@/utils/index'
    import logs from './logs'
    import QZ_CONFIG from '@/utils/config'
    export default {
        name: "hdDetail",
        mixins: [memberReportDetail],
        components: {
            logs
        },
        data() {
            return {
                companyName: '',
                memberName: '',
                memberType: '',
                city: '',
                ratio: '',
                isNewMember: '',
                contact: '',
                duties: '',
                phone: '',
                wechat: '',
                contractTime: '',
                memberTime: '',
                moneyInTime: '',
                nextpayTime: '',
                totalMoney: '',
                getMoney: '',
                lastMoney: '',
                logoPos: '',
                bannerPos: '',
                giftAdCircleDays: '',
                giftAdBannerDays: '',
                adOnlineNote: '',
                orderAreaNote: '',
                isAllOrder: '',
                monthPromiseOrder: '',
                promiseOrder: '',
                time_begin: '',
                time_end: '',
                onlineShopCode: '',
                otherNote: '',
                img_list: [],
                kfuploadImg: [],
                promisedOrdersFen: '',
                promisedOrdersZeng: '',
                currentPromisedOrdersFen: '',
                currentPromisedOrdersZeng: '',
                convertOrderCount: 0,
                totalConvertOrderCount: 0,
                yearMonthOne: '',
                yearMonthTwo: '',
                current_year_month_one: '',
                current_year_month_two: '',
                isAllOrderOther: '',
                contractRemark: '',
                totalContractRemark: '',
                is_abnormal: '',
                paymentMoney: '',
                paymentNumber: '',
                report_money_compart: '',
                showSmall: true,
                url: [],
                centerDialogVisible: false,
                num: 0,
                openCityChatRemark: '',
                isShowtsl: false,
                info: {}
            }
        },
        filters: {
            bannerPosFilter(val) {
                switch (val) {
                    case 1:
                        return 'A通栏'
                        break
                    case 2:
                        return 'B通栏'
                        break
                    case 3:
                        return 'A+B通栏'
                        break
                    default:
                        return '----'
                }
            }
        },
        created() {
            this.getMemberReportDetail()
            const type = Number(this.$route.query.type)
            if(type === 1 || type === 2 || type === 3) {
              this.isShowtsl = true
            }
        },
        methods: {
            prev() {
                const imgNum = this.url.length
                if (this.num === 0) {
                    this.num = imgNum
                }
                this.num--
            },
            next() {
                const imgNum = this.url.length
                if (this.num === imgNum - 1) {
                    this.num = -1
                }
                this.num++
            },
            getMemberReportDetail() {
                const queryObj = {
                    cooperation_type: this.$route.query.type,
                    id: this.$route.query.id,
                    city_quote: 1
                }
                this.memberReportDetail(queryObj, this.setData)
            },
            setData(data) {
                const ret = data.info
                for (var i = 0; i < ret.img_list.length; i++) {
                    this.img_list[i] = QZ_CONFIG.oss_img_host + ret.img_list[i]
                }
                this.img_list = this.img_list
                for(var i = 0; i < ret.kf_voucher_img.length; i++) {
                    this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
                }
                this.info = ret
                this.kfuploadImg = this.kfuploadImg

                this.companyName = ret.company_name
                this.memberType = ret.cooperation_type
                this.memberName = ret.cooperation_type_name
                this.city = ret.city_name
                this.ratio = ret.viptype
                this.isNewMember = ret.is_new_name
                this.contact = ret.company_contact
                this.duties = ret.company_contact_position
                this.phone = ret.company_contact_phone
                this.wechat = ret.company_contact_weixin
                this.contractTime = fortmatDate(ret.contract_start * 1000, '/') + ' - ' + fortmatDate(ret.contract_end * 1000, '/')
                this.memberTime = fortmatDate(ret.current_contract_start * 1000, '/') + ' - ' + fortmatDate(ret.current_contract_end * 1000, '/')
                this.moneyInTime = fortmatDate(ret.current_payment_time * 1000, '/')
                this.nextpayTime = fortmatDate(ret.next_payment_time * 1000, '/')
                this.totalMoney = ret.total_contract_amount
                this.getMoney = ret.current_contract_amount
                this.lastMoney = ret.lave_amount
                this.logoPos = ret.logo_address
                this.bannerPos = ret.passage_position
                this.giftAdCircleDays = ret.contract_handsel_banner
                this.giftAdBannerDays = ret.contract_handsel_passage
                this.adOnlineNote = ret.current_adver_content ? ret.current_adver_content : '----'
                this.orderAreaNote = ret.amount_and_area ? ret.amount_and_area : '----'
                this.isAllOrderOther = ret.lxs_remark
                this.contractRemark = ret.contract_remark
                this.totalContractRemark = ret.total_contract_remark
                this.openCityChatRemark = ret.open_city_chat_remark
                if (ret.is_abnormal === 1) {
                    this.is_abnormal = 'red'
                }
                if (ret.report_payment_number === 0) {
                    this.showSmall = false
                }
                if (ret.report_money_compart === 0 && this.showSmall) {
                    this.report_money_compart = 'red'
                }
                switch (ret.lxs) {
                    case 1:
                        this.isAllOrder = '家装'
                        break
                    case 2:
                        this.isAllOrder = '公装'
                        break
                    case 3:
                        this.isAllOrder = '公装/家装'
                        break
                    case 4:
                        this.isAllOrder = '其它'
                        break
                    default:
                        this.isAllOrder = '----'
                }

                this.promiseOrder = ret.promised_orders
                this.monthPromiseOrder = ret.month_promised_orders

                this.time_begin = fortmatDate(ret.deny_promised_start * 1000, '/')
                this.time_end = fortmatDate(ret.deny_promised_end * 1000, '/')
                this.onlineShopCode = ret.company_id ? ret.company_id : '----'
                this.otherNote = ret.remarke ? ret.remarke : '----'
                this.promisedOrdersFen = ret.promised_orders_fen
                this.promisedOrdersZeng = ret.promised_orders_zeng
                this.convertOrderCount = ret.convert_order_count ? ret.convert_order_count : 0
                this.totalConvertOrderCount = ret.total_convert_order_count ? ret.total_convert_order_count : 0
                this.currentPromisedOrdersFen = ret.current_promised_orders_fen
                this.currentPromisedOrdersZeng = ret.current_promised_orders_zeng
                this.yearMonthOne = parseInt(ret.year_month_one) === 0 ? '----' : ret.year_month_one
                this.yearMonthTwo = parseInt(ret.year_month_two) === 0 ? '----' : ret.year_month_two
                this.current_year_month_one = parseInt(ret.current_year_month_one) === 0 ? '----' : ret.current_year_month_one
                this.current_year_month_two = parseInt(ret.current_year_month_two) === 0 ? '----' : ret.current_year_month_two
                this.paymentMoney = ret.report_payment_money + ret.deposit_to_round_money
                this.paymentNumber = ret.report_payment_number
            },
            preview(index) {
                this.url = this.img_list
                this.num = index
                this.centerDialogVisible = true
            },
            kfpreview(index) {
                this.url = this.kfuploadImg
                this.num = index
                this.centerDialogVisible = true
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
.home-decoration-detail{
    .main{
        width: 800px;
        border-top:none !important;
    }
    .mb-20{
        margin-bottom: 20px;
    }
    .inline-block{
        display: inline-block;
    }
    .keftips{
        width: 500px;
        position: absolute;
        left: 30px;
        top: -17px;
    }
    .black{
        color: black;
    }
    .upimg {
        display: inline-block;
        width: 100px;
        height: 100px;
        margin-right: 10px;
    }
    .promiseorder{
        margin-bottom: 20px;
        margin-right: -160px;
        width: 1172px;
        i{
            font-style: normal;
        }
        .one{
            width: 80px;
        }
        .two{
            width: 150px;
        }
        .three{
            width: 150px;
        }
        .c90f{
          color: #9900ff;
        }
    }
    .imgMask{
        position: fixed;
        height: 100%;
        width:100%;
        top:0;
        left:0;
        bottom:0;
        right:0;
        z-index: 99999;
        background:rgba(0,0,0,.6);
    }
    .showImg{
        height:550px;
        width:800px;
        position: absolute;
        left:50%;
        top:50%;
        overflow:hidden;
        transform:translate(-50%,-50%);
    }
    .bigImg{
        height:100%;
        display: block;
        margin:0 auto;
    }
    .prev{
        position: absolute;
        top:50%;
        left:50px;
        font-size: 46px;
        cursor: pointer;
        color:#fff;
        transform:translate(10px,-50%);
    }
    .next{
        font-size: 46px;
        cursor: pointer;
        transform:translate(10px,-50%);
        position: absolute;
        top:50%;
        color:#fff;
        right:50px;
    }
    .amount{
        width: 1202px;
    }
}
</style>
