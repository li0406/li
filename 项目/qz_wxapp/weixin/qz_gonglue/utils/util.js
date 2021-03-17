const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

const navArray = [{
  "color": "",
  "img": "/img/home_icon.png",
  "img1": "/img/home_icon_1.png",
  "src": "/img/home_icon.png",
  "pagePath": "pages/index/index"
}, {
  "color": "",
  "img": "/img/jsq_icon.png",
  "img1": "/img/jsq_icon_1.png",
  "src": "/img/jsq_icon.png",
  "pagePath": "pages/jsq/jsq"
}, {
  "color": "",
  "img": "/img/admin_icon.png",
  "img1": "/img/admin_icon_1.png",
  "src": "/img/admin_icon.png",
  "pagePath": "pages/user/user"
}]

const activeNav = (url)=>{
  for (let i = 0; i < navArray.length;i++){
    if (url == navArray[i].pagePath){
      navArray[i].color="#ff5353";
      navArray[i].src = navArray[i].img1;
    }else{
      navArray[i].color = "";
      navArray[i].src = navArray[i].img;
    }
  }
  return navArray;
}

const collection=(e)=>{//收藏功能

  let id=e.currentTarget.dataset.id;//收藏id
  let filetype=e.currentTarget.dataset.type;//收藏类型
   
}
module.exports = {
  formatTime: formatTime,
  activeNav:activeNav,
  navArray:navArray,
  collection: collection
}
