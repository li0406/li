var hollyglobal = {
    hangupEvent: function (peer) {
//        console.dir(peer);
        alert('aaaaa');
    },
    ringEvent: function (peer) {
//        console.dir(peer);
    },
    talkingEvent: function (peer) {
//        console.dir(peer);
    },
    loginSuccessCallback: null,

    loginFailureCallback: function (peer) {
//        console.log(peer);
    },
    pbxs: [
        {
            PBX: '2.3.1.101',
            pbxNick: '101',
            NickName: '101',
            proxyUrl: "http://10.8.15.222"
        }
    ],
    accountId: holly_account,
    sipConfigId: '2.3.1.101',
    monitorPassword: '7pu3refwds98172e',
    monitorUserID: '2387rufhinjvcx73rfws',
    loginBusyType: "0",
    // loginExtenType: "Local",
    // loginPassword: "000001",
    // loginUser: "8001@maocg",
    // loginProxyUrl: "http://211.151.35.103",
    loginUser: holly_user,//软电话登录的登录名
    loginPassword: holly_pwd,//软电话登录的密码
    loginExtenType: "sip",//软电话登录的登录方式
    loginProxyUrl: holly_proxy,///软电话条需要请求的服务器IP（PBX IP）
    isDisplayInvestigate: true,//转调查
    isDisplayConsult: false,//咨询
    isHiddenNumber: false,//号码隐藏
    isMonitorPage: true,//
    isDisplayTransfer: false,//转接
    imgDir:'/assets/home/holly/softbar'
};