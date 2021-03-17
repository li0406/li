const app = getApp()
import { getgs,getfdFn } from '../../../utils/api.js'
import { formatTime } from '../../../utils/utils.js'
Page({
    data: {
        swiperCurrent: 0,
        source: '20082219',
        cityName: '',
        activeIndex:0,
        userList:[],
        bjtel: '',
        checkValue: true,
        orderShow: false,
        beforeOrder: true,
        orderSuccess: false,
        orderUnsuccess: false,
        activity_info: '',
        quanShow:true,
        index:'',
        quandata:[],
        filmon1:'',
        filmon2:''
    },
    gettel(e) {
        this.setData({
            bjtel: e.detail.value,
        })
    },
    checkboxChange: function (e) {
        let that = this;
        if (e.detail.value == '') {
            that.setData({
                checkValue: false
            })
        } else {
            that.setData({
                checkValue: true
            })
        }
    },
    getQuan: function(e){
        let that = this
        let id=e.currentTarget.dataset.id
        let phone = that.data.bjtel
        let checked = that.data.checkValue
        let indexone=that.data.index
        //联系方式
        if (phone.length < 1) {
            swan.showToast({
                title: "请输入您的手机号",
                icon: "none"
            })
            return;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(phone)) {
                swan.showToast({
                    title: "请输入正确的手机号码",
                    icon: "none"
                })
                return false;
            }
        }
        if (checked == false) {
            swan.showToast({
                title: "请勾选我已阅读齐装网的【免责声明】",
                icon: 'none'
            });
            return false;
        }
        //TODO 发单请求
        this.fdFn(id,indexone,that.data.cid)
    },
    fdFn(id,indexone,cid) {
        let that = this
        let phone = that.data.bjtel
        getfdFn(
            '/bd/v1/company/usergetcomcard',
            {
                tel: phone,
                record_id:id,
                cs:cid
            }
        ).then(data => {
            var set="quandata[" + indexone + "].linshow"
            if (data.error_code == 0) {
                that.setData({
                    beforeOrder: false,
                    orderSuccess: true,
                    bjtel: '',
                    [set]:false
                })
            } else {
                that.setData({
                    beforeOrder: false,
                    orderUnsuccess: true,
                    bjtel: '',
                    [set]:true
                })
            }

        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    // 立即领取打开发单弹窗
    openOrder: function(e){
        let elinshow=e.currentTarget.dataset.item.linshow
        if(elinshow==false){
            this.setData({
                orderShow: false,
            })
            return false
        }else{
            this.setData({
                orderShow: true,
                openItem:e.currentTarget.dataset.item,
                index:e.currentTarget.dataset.index
            })
        }
    },
    // 关闭优惠券发单弹窗
    closeOrder: function() {
        this.setData({
            orderShow: false,
            beforeOrder: true,
            orderSuccess: false,
            orderUnsuccess: false,
            bjtel: ''
        })
    },
    // 点击切换真实案例模块
    toogle(e){
        let that = this
        let index =e.currentTarget.dataset.index
        that.setData({
            activeIndex:index,
        })
        if(index === '2') {
            setTimeout(function(){
                that.setCollapse()
            }, 20);
        }

    },
    // 控制展开还是收起
    toogleShow(e){
        let index = e.currentTarget.dataset.index
        var set = "comment[" + index + "].collapse";
        this.setData({
            [set]: !this.data.comment[index].collapse
        })

    },
    setCollapse: function() {
        var query = swan.createSelectorQuery();
        var that = this;
        query.selectAll('.new').boundingClientRect(function (rect) {
            rect.forEach((v, i) => {
                if (v.height > 45) {  //判断高度,根据各种高度取折中
                    var set = "comment[" + i + "].collapse";
                    var set1 = "comment[" + i + "].showCollapse";
                    that.setData({
                        [set]: true,
                        [set1]: true,
                    })
                }
            })
        }).exec();
    },
    // 优惠劵的展开收起
    toogleQuanshow(){
        this.setData({
            quanShow:!this.data.quanShow
        })
    },
    // 关闭顶部活动提示框
    huiShow(){
       this.setData({
        [`userList.activity_info.title`]:''
       })
    },
    toogleHui(e){
        let id=e.currentTarget.dataset.id
        let bm=this.data.bm
        swan.navigateTo({
            url: `../../company/hddetail/hddetail?cs=${bm}&id=${id}`
        });
    },
    // 点击icon公司荣誉/标签
    iconTow(){
        swan.navigateTo({
            url: `../../company/about/about?cs=${this.data.userList.bm}&id=${this.data.userList.id}`
        });
    },
    // 设计师底部跳转到设计师列表页
    jump(){
        swan.navigateTo({
            url: `../../design/list/list?cs=${this.data.userList.bm}&id=${this.data.userList.id}`
        });
    },
    // 装修现场底部跳转到装修现场列表页
    golike(){
        swan.navigateTo({
            url: `../../worksite/list/list?cs=${this.data.userList.bm}&id=${this.data.userList.id}`
        });
    },
    // 点击真实案例底部跳转真实案例列表页
    gozsreal(){
        swan.navigateTo({
            url: `../../company/xgt/xgt?cs=${this.data.userList.bm}&id=${this.data.userList.id}`
        });
    },
    // 业主评价里的底部跳转
    golookp(){
        swan.navigateTo({
            url:`../../company/comment/comment?cs=${this.data.userList.bm}&id=${this.data.userList.id}`
        })
    },
    // 真实案例设计师封面图跳到真实案例详情页
    goimg(e){
        let alid = e.currentTarget.dataset.alid
        swan.navigateTo({
            url: `../../caseinfo/details/details?cs=${this.data.userList.bm}&id=${alid}`
        });

    },
    //跳到设计师详情页
    jumpdetail(e){
        let userid=e.currentTarget.dataset.userid
        let bm=e.currentTarget.dataset.bm
        let type =e.currentTarget.dataset.type
        if(type == 2){
            swan.navigateTo({
                url: `../../design/detail_new/detail_new?cs=${bm}&id=${userid}`
            });
        } else if(type == 1) {
            swan.navigateTo({
                url: `../../design/detail/detail?cs=${bm}&id=${userid}`
            });
        }
    },
    swiperChange(e) {
        this.setData({
            swiperCurrent: e.detail.current
        })
    },
    Detail(id) {
        let that = this
        getgs(
            '/bd/v1/company/detail',
            { company_id: id }
        ).then(data => {
            let datas = data;
            if (datas.error_code == 0) {
                let comment = datas.data.comment_list;
                comment.forEach((item)=>{
                    item.times= formatTime(item.time,'Y-M-D')
                    item.rptxt_times= formatTime(item.rptxt_time,'Y-M-D')
                })
                let quandata=datas.data.user.card_list
                if(quandata){
                    quandata.forEach((item)=>{
                        item.linshow = true
                        if(item.active_type=="1"){
                            item.filmon1=item.money2,
                            item.filmon2=item.money1,
                            item.texts="可用"
                        }else if(item.active_type=="2"){
                            item.filmon1=item.gift,
                            item.filmon2=item.money3,
                            item.texts="可领"
                        }
                    })
                }
                if(datas.data.case_list.length == 0 && datas.data.designer_list.length > 0){
                    that.setData({
                        activeIndex: 1
                    })
                }
                if(datas.data.case_list.length == 0 && datas.data.designer_list.length == 0 && datas.data.comment_list.length > 0){
                    that.setData({
                        activeIndex: 2
                    })
                }
                that.setData({
                    banners: datas.data.banners,
                    comment: comment,
                    comment_count: datas.data.comment_count,
                    company_tag: datas.data.company_tag,
                    user: datas.data.user,
                    cityName: datas.data.user.city_name ? datas.data.user.city_name : '苏州',
                    caseList: datas.data.case_list,
                    deList: datas.data.designer_list,
                    userList:datas.data.user,
                    quandata:quandata,
                    cid:datas.data.user.cs
                })
                swan.setPageInfo({
                    title: datas.data.tdk.title,
                    keywords: datas.data.tdk.keywords,
                    description: datas.data.tdk.description,
                    articleTitle: 'xxx',
                    image: [],
                    success: res => {
                        console.log('setPageInfo success', res);
                    },
                    fail: err => {
                        console.log('setPageInfo fail', err);
                    }
                })
            } else {
                swan.showToast({
                    title: datas.error_msg,
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    getBigImage: function(e) {
        let src = e.currentTarget.dataset.src
        let urls = e.currentTarget.dataset.urls
        swan.previewImage({
            current: src,
            urls: urls
        });
    },
    noImage: function() {
        // do nothing 阻止banner点击放大
    },
    worksite(id) {
        let that = this
        getgs(
            '/bd/v1/worksite',
            {
                company_id: id,
                page:1,
                limit: 4
            }
        ).then(data => {
            let datas = data;
            if (datas.error_code == 0) {
                that.setData({
                   gdList: datas.data.info,
                   gdnum:datas.data
                })
            } else {
                swan.showToast({
                    title: datas.error_msg,
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    callPhone() {
        swan.makePhoneCall({
            phoneNumber: '400 696 9336',
            fail: err => {
                swan.showModal({
                    title: '拨打失败',
                    content: '请检查是否输入了正确的电话号码',
                    showCancel: false
                });
            }
        });
    },
    onLoad: function (options) {
        let id = options.id
        this.setData({
            id: id,
            bm:options.cs
        })
        this.Detail(id)
        this.worksite(id)
    },
    onShow: function () {

    },
    onTap(e) {
        let myComponent = this.selectComponent('#mySever'); // 页面获取自定义组件实例
        myComponent.openOrderWin(e.currentTarget.dataset.id);
    }
});