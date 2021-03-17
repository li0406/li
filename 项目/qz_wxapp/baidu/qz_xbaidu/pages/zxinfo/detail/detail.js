import { getWalkDetail, getgs, getArticalList } from '../../../utils/api.js'
var bdParse = require('../../../templates/bdParse/bdParse.js')
const app = getApp()
Page({
    data: {
        id: '',
        title: '',
        addtime: '',
        conrent: '',
        article: '',
        nodata: "no-data",
        commentParam: {},
        source: '20082407',
        city_id: '',
        company_list: [],
        case_list: [],
        tu_list: [],
        detailPath: '',
        toolbarConfig: {
            // 若 moduleList 中配置有 share 模块，默认是有，则该属性为必填，title 必传
            share: {
                title: ''
            },
            placeholder:''
        },
        tabs: [{
            index: 1,
            label: '装修公司'
        }, {
            index: 2,
            label: '效果图'
        }, {
            index: 3,
            label: '装修案例'
        }],
        currentIndex: 1
    },
    changeTab(e) {
        let index = e.currentTarget.dataset.index
        this.setData({
            currentIndex: index
        })
    },
    getComList(cs) {
        let that = this
        getgs(
            '/bd/v1/company',
            {
                cs: cs,
                qx: '',
                order: 'star',
                page:1,
                limit: 3
            }
        ).then(data => {
            if (data.error_code == 0) {
                that.setData({
                    company_list: data.data.list
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
    getArticalList: function(cid) {
        getArticalList(
            '/bd/v1/articledetail/tab',
            {
                cid: cid
            }
        ).then(data => {
            if(data.error_code === 0){
                this.setData({
                    case_list: data.data.case_list,
                    tu_list: data.data.tu_list
                })
            } else {
                swan.showToast({
                    title: datas.err_msg,
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
    toXgtDetail: function(e){
        let id = e.currentTarget.dataset.id
        swan.navigateTo({
            url: '../../tu/detail/detail?id=' + id
        });
    },
    toCaseDetail: function(e) {
        let cs = e.currentTarget.dataset.cs
        let id = e.currentTarget.dataset.id
        swan.navigateTo({
            url: '../../caseinfo/details/details?cs=' + cs + '&id=' + id
        });
    },
    toDetail: function(e){
        let company_id = e.currentTarget.dataset.company_id
        let cs = e.currentTarget.dataset.cs
        swan.navigateTo({
            url: '../../company/details/details?cs='+ cs +'&id=' + company_id
        })
    },
    getDetail: function (id) {
        swan.showLoading({
            title: '加载中',
        })
        getWalkDetail(
            '/bd/v1/zixun/getarticledetail',
            {
                id: id,
                cs: this.data.cs
            }
        ).then(data => {
            if (data.error_code == 0) {
                this.setData({
                    commentParam: {
                        snid: data.data.detail.id,
                        path: `/pages/common/commentdetails?snid=${data.data.detail.id}`,
                        title: data.data.detail.title,
                        content: data.data.detail.content
                    },
                    detailPath:`/pages/common/commentdetails?nid=${data.data.detail.id}`,
                    toolbarConfig: {
                        // 若 moduleList 中配置有 share 模块，默认是有，则该属性为必填，title 必传
                        share: {
                            title: data.data.detail.title
                        },
                        placeholder:'我来说两句'
                    }
                });
                swan.hideLoading()
                let pageInfo = data.data.detail;
                let contents = data.data.detail.content;
                let imgs = []
                contents.replace(/<img [^>]*src=['"]([^'"]+)[^>]*>/gi, function (match, capture) {
                    imgs.push(capture)
                })
                var that = this;
                bdParse.bdParse('article', 'html', contents, that, 5);
                that.setData({
                    title: pageInfo.title,
                    addtime: pageInfo.addtime,
                    views: pageInfo.pv,
                    likes: pageInfo.likes,
                    nodata: ""
                })
                that.getComList(data.data.city.cid)
                that.getArticalList(data.data.city.cid)
                swan.setPageInfo({
                    title: pageInfo.seo_title,
                    keywords: pageInfo.seo_keywords,
                    description: pageInfo.seo_description,
                    releaseDate: pageInfo.addtime,
                    articleTitle: pageInfo.title,
                    image: imgs.slice(0, 3)
                })
            } else {
                this.getComList(data.data.city.cid)
                this.getArticalList(data.data.city.cid)
                this.xDetail()
            }
        }).catch(error => {
            this.getComList(error.data.city.cid)
            this.getArticalList(error.data.city.cid)
            this.xDetail()
        })
    },
    xDetail() {
        let contents = '<p class="text" style="TEXT-INDENT:2em"><strong>荣誉加冕，实至名归</strong></p><p class="text" style="TEXT-INDENT:2em">2019年是齐装网全面爆发的一年，在这一年中，齐装网相继获得“中国消费者可信赖品牌”、“中国家居行业最具影响力品牌”、“中国百佳改革创新示范企业”、“中国百强优秀企业”、“全国互联网家居行业十大品牌”等荣誉奖项。这一年，齐装的优秀有目共睹，荣誉加冕，实至名归!</p><p class="text" style="TEXT-INDENT:2em"></p><p align="center"><img src="//inews.gtimg.com/newsapp_bt/0/10745384215/641" style="display:block;"></p><p></p><p class="text" style="TEXT-INDENT:2em">齐装网成立于2012年，总部位于毗邻全国第一大城市上海的苏州，在苏州、广州、天津、重庆、郑州、青岛、西安、南京、合肥、济南、杭州、厦门、武汉、南宁、无锡、福州、徐州、温州、泉州等全国大中城市500余个建立了直营分站。</p><p class="text" style="TEXT-INDENT:2em">在短短七年的发展，齐装网就从名不见经传的小企业，一跃成为“全国互联网家居行业十大品牌”，主要得益于时代的机遇以及齐装网上下全体员工的不懈努力。</p><p class="text" style="TEXT-INDENT:2em">这七年，齐装网服务装饰企业超60000家，服务用户超1500万，累计发布金额超过10000亿;这七年，齐装网从用户需求出发，以优质的内容、产品与服务，成功打造一个兼具体验与效率的平台;这七年，齐装网始终专注于每一位合作伙伴的需求，认真跟进落实每一笔订单，一步一个脚印，最终成长为“全国互联网家居行业十大品牌”。</p><p class="text" style="TEXT-INDENT:2em">金秋十月，齐装网品牌系列产品在全国互联网家居行业品牌质量监督活动中，符合《全国互联网家居行业十大品牌》入选标准，被荣选为“全国互联网家居行业十大品牌”。荣誉证书由中国产品质量检验监督中心以及中国品牌认证发展工作委员会共同颁发。</p><p class="text" style="TEXT-INDENT:2em"><strong>韬光养晦，厚积薄发</strong></p><p class="text" style="TEXT-INDENT:2em">互联网+时代的到来，是机遇，也是挑战。在这个瞬息万变的时代中，只有抓住稍纵即逝的机会，才可能从千军万马中脱颖而出，而齐装网无疑就是那匹黑马。天道酬勤，机会总是留给那些有准备的人，齐装网从成立之初就一直在准备着，韬光养晦，把握时代机遇，终于在2019年厚积薄发。</p><p class="text" style="TEXT-INDENT:2em"></p><p align="center"><img src="//inews.gtimg.com/newsapp_bt/0/10745384216/641" style="display:block;"></p><p></p><p class="text" style="TEXT-INDENT:2em">齐装网一直以“客户第一”作为企业发展的核心精神，坚持用户利益至上，不断倾听并深刻理解用户需求，从用户的角度提供品质的家装体验和服务。对于客户的每一份订单，齐装网都认真跟进落实;对于客户的每一项反馈，齐装网认真倾听记录，有则改之，无则加勉，不断的带给用户更好更优质的体验。</p><p class="text" style="TEXT-INDENT:2em">齐装网在互联网家装行业的暴风雪时期，没有被淹没，反而在不断攀升，凭借的是消费者的信赖。在寒潮笼罩之下，客户的每一笔订单，都代表着对其齐装网的一份信任，都是支撑齐装网走下去的一份动力以及发展的力量。最终，齐装网不负期待。</p><p class="text" style="TEXT-INDENT:2em"><strong>运筹帷幄，领航未来</strong></p><p class="text" style="TEXT-INDENT:2em">2019年8月16日，齐装网召开了2019-2021未来3年的战略规划会议，创始人陈世超在会议中明确提出，齐装网将战略升级为综合性大型互联网平台，携手合作伙伴以及所有家装行业的服务者，共创赋能型泛家装平台，帮助企业重构与消费者之间的联系。</p><p class="text" style="TEXT-INDENT:2em"></p><p align="center"><img src="//inews.gtimg.com/newsapp_bt/0/10745384218/641" style="display:block;"></p><p></p><p class="text" style="TEXT-INDENT:2em">未来三年，齐装网将会从产业链、流量、政策三个方面开展新的契机，并借助互联网+、大数据、云计算等科技的持续发展，以用户保障为前提，全面赋能装企，着力提升其品质交付能力，更聚焦平台生态体系发展。</p><p class="text" style="TEXT-INDENT:2em">在齐装网的发展蓝图中，这次的升级改革战略只是一小步，未来，齐装网将会始终坚持“让家装简单透明一体化，打造品质生活，提升幸福体验”的伟大使命，并以实际行动践行使命，最终成为互联网家装行业领跑者，为中国消费者带来智能化、便捷化、系统化的品质装修!</p>'
        bdParse.bdParse('article', 'html', contents, this, 5);
        this.setData({
            title: '快讯！齐装网入选全国互联网家居行业十大品牌',
            addtime: '2019/05/12 07:05',
            nodata: ""
        })
        swan.setPageInfo({
            title: '快讯！齐装网入选全国全国互联网家居行业十大品牌',
            keywords: '互联网家居行业,齐装网',
            description: '2019年是齐装网全面爆发的一年，在这一年中，齐装网相继获得“中国消费者可信赖品牌”、“中国家居行业最具影响力品牌”、“中国百佳改革创新示范企业”、“中国百强优秀企业”',
            image: []
        })
    },
    updata() {
        swan.showFavoriteGuide({
            type: 'tip',
            content: '关注小程序,下次使用更便捷',
            success: res => {
                console.log('关注成功：', res);
            },
            fail: err => {
                console.log('关注失败：', err);
            }
        })
        if (swan.canIUse("getUpdateManager")) {
            const updateManager = swan.getUpdateManager();
            updateManager.onCheckForUpdate(function (res) {
                if (res.hasUpdate) {
                    updateManager.onUpdateReady(function () {
                        swan.showModal({
                            title: "更新提示",
                            content: "新版本已经准备好，是否重启小程序？",
                            success(res) {
                                if (res.confirm) {
                                    updateManager.applyUpdate();
                                }
                            }
                        });
                    });
                    updateManager.onUpdateFailed(function () {
                        swan.showModal({
                            title: "已经有新版本了哟~",
                            content: "新版本已经上线啦~，请您删除当前小程序，重新搜索打开哟~"
                        });
                    });
                }
            });
        } else {
            swan.showModal({
                title: "提示",
                content:
                    "当前版本过低，无法使用该功能，请升级到最新百度版本后重试。"
            });
        }
    },
    onLoad: function (e) {
        let id = e.id ? e.id : '';
        this.setData({
            id: e.id,
            cs: e.cs,
            isPhonex: app.globalData.isIphoneX ? "isPhonex" : ''
        })
        if (id) {
            this.getDetail(id)
        } else {
            this.xDetail()
        }

       const isLoginResult = swan.isLoginSync();

       // 当前用户已登录的条件下，利用登录 code 获取 openid
       // 当前用户未登录的条件下，不强制其登录
       if (isLoginResult && isLoginResult.isLogin) {
           this.triggerLogin();
       }
    },
    onShow: function (e) {
        let that = this
        that.updata()
    },
    bdParseTagATap: function (e) {
        let href = e.currentTarget.dataset.src
        let navgaitUrl = ''
        if (href == 'http://www.qizuang.com/zhaobiao/') {
            navgaitUrl = '/pages/gonglue/zhuangxiusj/zhuangxiusj'
            swan.navigateTo({
                url: navgaitUrl
            });
        }
        if (href.indexOf('http://www.qizuang.com/gonglue') != -1) {
            this.urlParseFn(href)
        }
    },
    urlParseFn: function (url) {
        let urlData = url.split('/')
        let type = urlData[4]
        let id = urlData[5].split('.')[0]
        swan.navigateTo({
            url: '/pages/gonglue/detail/detail?id=' + id + '&type=' + type
        });
    },

    onReady() {
        requireDynamicLib('myDynamicLib').listenEvent();
    },

    clickComment(e) {
    },

    triggerLogin(e) {
        swan.login({
            success: res => {
                swan.request({
                    url: 'https://spapi.baidu.com/oauth/jscode2sessionkey',
                    method: 'POST',
                    header: {
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    data: {
                        code: res.code,
                        'client_id': 'qc2qssgyRvGsRqR6yErfXcxkQjG1vQkq', // AppKey
                        sk: 'sAnZmVPrqH8aHFbwRTz1qQj57oGMDW5N' // AppSecret
                    },
                    success: res => {
                        if (res.statusCode === 200) {
                            const commentParam = this.data.commentParam;
                            this.setData({
                                commentParam: {
                                    ...commentParam,
                                    openid: res.data.openid
                                }
                            }, () => {
                                // 我们建议将参数设为全局变量，方便取用
                                getApp().globalData.commentParam = this.data.commentParam;
                            });
                        }
                    }
                });
            }
        });
    }
});
