import defaultSettings from '@/settings'

const title = defaultSettings.title || '分销商城系统·商家版'

export default function getPageTitle(pageTitle) {
  if (pageTitle) {
    return `${pageTitle} - ${title}`
  }
  return `${title}`
}
