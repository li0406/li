export function isExternal(path) {
  return /(https?:|mailto:|tel:)/.test(path);
}

/* 小写字母 */
export function validLowerCase(str) {
  const reg = /^[a-z]+$/;
  return reg.test(str);
}

/* 大写字母 */
export function validUpperCase(str) {
  const reg = /^[A-Z]+$/;
  return reg.test(str);
}

/* 大小写字母 */
export function validAlphabets(str) {
  const reg = /^[A-Za-z]+$/;
  return reg.test(str);
}

/* 手机号验证 */
export function validPhone(str) {
  const reg = /^(13|14|15|16|17|18|19)[0-9]{9}$/;
  return reg.test(str);
}

/* 区号 */
export function validArea(str) {
  const reg = /^0\d{2,3}$/;
  return reg.test(str);
}

/* 电话号7-9 */
export function validCall(str) {
  const reg = /^\d{7,9}$/;
  return reg.test(str);
}

/* 电话号7-10 */
export function validCal(str) {
  const reg = /^\d{7,10}$/;
  return reg.test(str);
}

/* qq */
export function validQQ(str) {
  const reg = /^[1-9][0-9]{4,10}$/;
  return reg.test(str);
}

/* 数字 */
export function validNum(str) {
  const reg = /^\d{1,}$/;
  return reg.test(str);
}

export function createUniqueString() {
  const timestamp = `${+new Date()}`;
  // eslint-disable-next-line radix
  const randomNum = `${parseInt((1 + Math.random()) * 65536)}`;
  return (+(randomNum + timestamp)).toString(32);
}
