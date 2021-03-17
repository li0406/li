function formatNumber(n) {
    n = n.toString()
    return n[1] ? n : '0' + n
}

function formatTime(number, format) {
    var formateArr = ['Y', 'M', 'D', 'h', 'm', 's'];
    var returnArr = [];
    var date = new Date(number * 1000);
    returnArr.push(date.getFullYear());
    returnArr.push(formatNumber(date.getMonth() + 1));
    returnArr.push(formatNumber(date.getDate()));
    returnArr.push(formatNumber(date.getHours()));
    returnArr.push(formatNumber(date.getMinutes()));
    returnArr.push(formatNumber(date.getSeconds()));
    for (var i in returnArr) {
        format = format.replace(formateArr[i], returnArr[i]);
    }
    return format;
}

function million(value) {
    let num;
    if (value > 9999) {
        num = (Math.floor(value / 1000) / 10) + 'W';
    } else if (value < 9999 && value > -9999) {
        num = value
    } else if (value < -9999) {//小于-9999显示-x.xx万
        num = -(Math.floor(Math.abs(value) / 1000) / 10) + 'W'
    }
    return num;
}

module.exports = {
    formatTime: formatTime,
    million:million
}