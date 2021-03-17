import Cookies from 'js-cookie'

const TokenKey = 'token'
const AccountKey = 'account'

export function getToken() {
  // return Cookies.get(TokenKey)
  return localStorage.getItem(TokenKey)
}

export function setToken(token) {
  // return Cookies.set(TokenKey, token)
  return localStorage.setItem(TokenKey, token)
}

export function removeToken() {
  return localStorage.removeItem(TokenKey)
}

export function getAccount() {
  // return Cookies.get(AccountKey)
  return localStorage.getItem(AccountKey)
}

export function setAccount(role) {
  // return Cookies.set(AccountKey, role)
  return localStorage.setItem(AccountKey, role)
}

export function removeAccount() {
  return localStorage.removeItem(AccountKey)
}
