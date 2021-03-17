import { getWorkSiteDetail, getWorkSiteDetails, getBaiKeList } from '../../../utils/api.js'
import { formatTime } from '../../../utils/utils.js'
Page({
    data: {
        classify: [{
            name: '开工大吉',
            step: 2
          }, {
            name: '主体拆改',
            step: 3
          }, {
            name: '水电整改',
            step: 4
          }, {
            name: '防水施工',
            step: 5
          }, {
            name: '泥瓦工程',
            step: 6
          }, {
            name: '木工进场',
            step: 7
          }, {
            name: '厨卫吊顶',
            step: 8
          }, {
            name: '油漆粉刷',
            step: 9
          }, {
            name: '铺贴壁纸',
            step: 10
          }, {
            name: '成品安装',
            step: 11
          }, {
            name: '保洁收尾',
            step: 12
          }, {
            name: '家具进场',
            step: 13
          }, {
            name: '家电进场',
            step: 14
          }, {
            name: '家居配饰',
            step: 15
          }, {
            name: '交付工程',
            step: 16
          }, {
            name: '竣工',
            step: 17
          }],
          step: 3,
          clickStep: 3,
          live_id: '',
          company_id: '',
          detailList: [],
          info: '',
          recommend: [],
          bkList: [],
          noData: false,
          bottomIcon: true
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        this.setData({
            live_id: options.id,
            step: options.step,
            clickStep: options.step
        })
        this.workSiteDetail()
        this.baikeList()
        this.workSiteDetails(this.data.step)
    },
    workSiteDetails:function(step) {
        getWorkSiteDetails(
            '/bd/v1/worksite/info',
            {
                live_id: this.data.live_id,
                step: step
            }
        ).then(data => {
            if(data.error_code === 0){
                let datas = data.data
                datas.forEach((item) => {
                    if(item.media_list && item.media_list.length >= 3) {
                        item.urlImages = item.media_list
                        item.media_list = item.media_list.slice(0,3)
                    } else {
                        item.urlImages = item.media_list
                    }
                })
                this.setData({
                    detailList: datas
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
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
    workSiteDetail:function() {
        getWorkSiteDetail(
            '/bd/v1/worksite/detail',
            {
                live_id: this.data.live_id
            }
        ).then(data => {
            if(data.error_code === 0){
                let recommends = data.data.recommend
                recommends.forEach((item)=>{
                    item.created_date= formatTime(item.datetime,'Y-M-D')
                    if(item.media_list && item.media_list.length >= 3) {
                        item.urlImages = item.media_list
                        item.media_list = item.media_list.slice(0,3)
                    } else {
                        item.urlImages = item.media_list
                    }
                })
                this.setData({
                    info: data.data.live_info,
                    recommend: recommends,
                    company_id: data.data.live_info.company_id
                })
                swan.setPageInfo({
                    title: this.data.info.name + this.data.info.sex + (this.data.info.mianji ? this.data.info.mianji : 96) + '平米新家的装修现场图_施工工地直播-'+ this.data.info.city_name +'齐装网',
                    keywords: this.data.info.name + this.data.info.sex + (this.data.info.mianji ? this.data.info.mianji : 96) + '平米装修现场图,' + this.data.info.name + this.data.info.sex + '新家施工工地直播',
                    description: '齐装网为您提供'+ this.data.info.name + this.data.info.sex + (this.data.info.mianji ? this.data.info.mianji : 96) + '平米新家装修施工现场直播,包含水电、泥木、油漆等施工工地图文详情，带您提前了解装修前、中、后期会遇到的问题.',
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
                    title: data.error_msg,
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
    baikeList: function() {
        getBaiKeList(
            '/bd/v1/baike/category/list',
            {}
        ).then(data => {
            if(data.error_code === 0){
                this.setData({
                    bkList: data.data
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
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
    toCompany: function(){
        swan.navigateTo({
            url: '../../company/details/details?id=' + this.data.company_id
        });
    },
    toDetail: function(e){
        let id = e.currentTarget.dataset.id
        let step = e.currentTarget.dataset.step
        swan.navigateTo({
            url: '../detail/detail?id=' + id + '&step=' + step
        });
    },
    toBaiKe: function(e){
        let id = e.currentTarget.dataset.id
        swan.navigateTo({
            url: '../../baike/bkdetail/bkdetail?id=' + id
        });
    },
    onTap: function(e) {
        let media_list = e.currentTarget.dataset.media_list
        let url_arry = media_list.map((item) => {
            return item.url_full
        })
        swan.previewImage({
            current:e.currentTarget.dataset.src,
            urls: url_arry
        });
    },
    noImage: function() {
        // do nothing  点击视频  阻止页面跳转
    },
    getProcess: function(e) {
        var process_step = e.currentTarget.dataset.step
        this.setData({
            clickStep: process_step
        })
        if (this.data.clickStep > this.data.step) {
            this.setData({
                noData: true
            })
        } else {
            this.setData({
                noData: false,
            })
            this.workSiteDetails(process_step)
        }
    }
});