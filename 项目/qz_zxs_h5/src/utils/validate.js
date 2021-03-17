/**
 * Created by jiachenpan on 16/11/18.
 */

// 手机号验证
export function isvalidPhone (str) {
  const reg = new RegExp('^(13|14|15|16|17|18|19)[0-9]{9}$')
  return reg.test(str)
}

export function million (value) {
  let num
  if (value > 9999) {
    num = (Math.floor(value / 1000) / 10) + 'W'
  } else if (value < 9999 && value > -9999) {
    num = value
  } else if (value < -9999) {
    num = -(Math.floor(Math.abs(value) / 1000) / 10) + 'W'
  }
  return num
}
