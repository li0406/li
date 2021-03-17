/*
* @Author: qz_xsc
* @Date:   2018-07-02 15:57:00
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-07-02 15:57:17
*/
var today = new Date();
var current_hour = today.getHours();
var surplus = 100;
if(current_hour<=12){
    surplus = 100 - Math.ceil(Math.random()*50);
}else{
    surplus = Math.ceil(Math.random()*50);
}
var surplus_num = document.getElementById("surplus_num");
surplus_num.innerHTML=surplus;
