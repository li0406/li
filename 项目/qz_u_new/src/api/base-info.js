import request from '@/utils/request';
import requestTwo from '@/utils/requestTwo';

function get(query) {
  return request({
    url: `/cp/v1/company/get`,
    method: 'get',
    params: query,
  });
}

function getareainfobycityid(query) {
  return requestTwo({
    url: `/v1/compant/getareainfobycityid`,
    method: 'get',
    params: query,
  });
}

function edit(data) {
  return request({
    url: `/cp/v1/company/edit`,
    method: 'post',
    data,
  });
}

// 服务类型/保障
function leixing(query) {
  return request({
    url: `/cp/v1/service/leixing`,
    method: 'get',
    params: query,
  });
}

// 装修风格
function fengge(query) {
  return request({
    url: `/cp/v1/service/fengge`,
    method: 'get',
    params: query,
  });
}

function editDetail(data) {
  return request({
    url: `/cp/v1/company/edit_detail`,
    method: 'post',
    data,
  });
}

function tags(query) {
  return request({
    url: `/cp/v1/company/tags`,
    method: 'get',
    params: query,
  });
}

function editTags(data) {
  return request({
    url: `/cp/v1/company/edit_tags`,
    method: 'post',
    data,
  });
}

function comImage(query) {
  return request({
    url: `/cp/v1//company/images`,
    method: 'get',
    params: query,
  });
}

function editImages(data) {
  return request({
    url: `/cp/v1//company/edit_images`,
    method: 'post',
    data,
  });
}

function enterImg(data) {
  return request({
    url: `/cp/v1/company/enter_img`,
    method: 'post',
    data,
  });
}

function licence(query) {
  return request({
    url: `/cp/v1//company/licence`,
    method: 'get',
    params: query,
  });
}

function saveLicence(data) {
  return request({
    url: `/cp/v1//company/save_licence`,
    method: 'post',
    data,
  });
}

function delLicence(data) {
  return request({
    url: `/cp/v1/company/del_licence`,
    method: 'post',
    data,
  });
}

function taglist(query) {
  return request({
    url: `/cp/v1/company/taglist`,
    method: 'get',
    params: query,
  });
}

function banner(query) {
  return request({
    url: `/cp/v1/company/banner`,
    method: 'get',
    params: query,
  });
}

function editBanner(data) {
  return request({
    url: `/cp/v1/company/banner`,
    method: 'post',
    data,
  });
}

export default {
  get,
  edit,
  getareainfobycityid,
  leixing,
  fengge,
  editDetail,
  tags,
  editTags,
  comImage,
  editImages,
  enterImg,
  licence,
  saveLicence,
  delLicence,
  taglist,
  banner,
  editBanner
};
