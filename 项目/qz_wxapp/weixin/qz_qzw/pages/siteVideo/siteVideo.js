Page({
  data: {
    url:'',
    thumb: ''
  },
  play(e) {
    var videoContext = wx.createVideoContext('myvideo', this);
    videoContext.requestFullScreen();
    this.setData({
      fullScreen: true
    })
  },
  closeVideo() {
    var videoContext = wx.createVideoContext('myvideo', this);
    videoContext.exitFullScreen();
  },
  fullScreen(e) {
    var isFull = e.detail.fullScreen;
    this.setData({
      fullScreen: isFull
    })
  },
  onLoad: function(options) {
    let url= options.url ? options.url : ''
    this.setData({
      url : url
    })
  },
})