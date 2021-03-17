const app = getApp(),
      apiUrl = app.getApiUrl(),
      oImgUrl = app.getImgUrl();
    
var WxParse =require("../../wxParse/wxParse.js");

Page({
  data: {
    imgUrl: oImgUrl,
    details:'',
	serviceArea: '',
    caseList:[],
    count:9,
    anlicount:'',
    id:'',
	star: '',
    loadBool:true
  },
  onLoad : function(options){
    let that = this;
	 let userid = ''
		if(app.globalData.userInfo && app.globalData.userInfo.userId) {
			userid = app.globalData.userInfo.userId
			this.setData({
				userId: userid
			})
		}
    my.httpRequest({
        url: apiUrl+'/company/detail',
        data: { 
			id: options.id,
			bm: options.bm
		},
        header: {
            'content-type': 'application/json'
        },
        success:function(res){
			var details = ''
			if(res.data && res.data.detail && res.data.detail.jianjie && res.data.detail.jianjie.length > 0) {
				res.data.detail.jianjie.forEach(function(item, index) {
					details+=item
				})
			}
      WxParse.wxParse('article_content','html',details,that,5)
            that.setData({ 
                details: details, 
				serviceArea: res.data.detail.area,
                caseList: res.data.cases, 
                id: options.id,
				bm: options.bm,
                anlicount: options.anlicount,
				star: options.star
            });
            my.setNavigationBar({
                title: res.data.detail.qc
            });
        }
    });

  },
    /**
     * 上拉加载更多数据
     */
    loadMore(){
        let that = this,
            count = that.data.count;
        if (that.data.loadBool){
            my.showToast({
                content: '数据加载中...',
                icon: 'loading'
            });
            let len = that.data.caseList.length;
            count += 5;
            my.httpRequest({
                url: apiUrl + '/company/detail',
                data: { 
					id: that.data.id, 
					count: count,
					bm: that.data.bm
				},
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    
                    if (len == res.data.cases.length){
                        that.setData({loadBool:false})
                    }else{
                        that.setData({ caseList: res.data.cases, count: count, loadBool: true })
                    }
                }
            });
        }else{
            my.showToast({
                content: '没有更多了',
                icon: 'success'
            });
        }
    },



});
