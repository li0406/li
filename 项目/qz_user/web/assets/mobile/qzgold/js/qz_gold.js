/**
 * qz_gold.js
 * 貔貅 pixiu web sdk
 */

/**
 * 插入广告函数，广告将插在选择器放
 * @param posSeleter 位置选择器，就是广告要插入的地方，可以是选择器字符串也可以是ele元素
 * @param pid 广告位id，后台广告位管理中相对应。 后台先配置，前端后使用
 * @param cs 城市id
 */
function qzInsertGold(posSeleter, pid, cs) {
    var containerIdPrefix = "pixiu-pid-";

    // 校验部分
    if(!posSeleter || !pid) {
        // throw Error('缺少参数')
        console.log('qzInsertGold: posSeleter pid','缺少参数');
        return;
    }
    if($(posSeleter).length <= 0) {
        // throw Error('找不到插入位置')
        console.log('qzInsertGold:', posSeleter, pid, '找不到插入位置');
        return;
    }

    // 样式处理
    // 把广告 style 加入到document对象中
    var csslink = document.createElement('link');
    csslink.rel = 'stylesheet';
    csslink.href = '/assets/mobile/qzgold/css/qz_ad.css';
    document.head.appendChild(csslink);

    // 动态创建广告对象
    // 一个pid对应一个对象
    // 给一个固定前缀"pixiu-pid-" 后端输出动态js需要对应
    var containerId = containerIdPrefix + pid;
    // 先创建一个id=containerId的广告位容器，并添加到页面指定位置，所有广告位的内容都在这个容器内
    var container = document.createElement('div');
    container.id = containerId;
    $(container).insertAfter(posSeleter);

    // 接口拉取的广告动态植入广告对象中
    // 创建脚本，引入脚本接口，并把脚本插入页面中
    var spt = document.createElement('script');
    spt.src = '//m.qizuang.com/pixiushow?position_id=' + pid + '&cs=' + cs;
    document.body.appendChild(spt);
}
