// 项目全局配置文件

const ws_protocol = (function(window) {
  const protocol = window.location.protocol
  if (protocol === 'https:') {
    return 'wss://'
  } else {
    return 'ws://'
  }
}(window))

const QZ_CONFIG = {
  base_api: process.env.BASE_API,
  oss_img_host: '//staticqn.qizuang.com/', // 图片服务器
  oss_video_host: '', // 视频服务器
  oss_audio_host: '//salesapi.qizuang.com', // 音频服务器
  oss_video_domain: '',
  oss_video_host_upload: '', // 视频上传，直传服务器
  ws_host: 'wsapi.qizuang.com/msg/system', // websocket服务器
  ws_protocol: ws_protocol // websocket服务器
}

export default QZ_CONFIG
