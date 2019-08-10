var p = 0;
var n = 1;

//上传游戏头像
function getFilename(){
    var filename=document.getElementById("file").value;
    if(filename==undefined||filename==""){
        document.getElementById("filename").innerHTML="点击此处上传文件";
    } else{
        var fn=filename.substring(filename.lastIndexOf("\\")+1);
        document.getElementById("filename").innerHTML=fn; //将截取后的文件名填充到span中
    }
}
//上传游戏截图
function getFilename2(){
    var filename=document.getElementById("file2").value;
    if(filename==undefined||filename==""){
        document.getElementById("filename2").innerHTML="点击此处上传文件";
    } else{
        var fn=filename.substring(filename.lastIndexOf("\\")+1);
        document.getElementById("filename2").innerHTML=fn; //将截取后的文件名填充到span中
    }
}
//双向游戏title
function myFunction() {
    var x =document.getElementById("text_input") .value;
    document.getElementById("pre_title").innerHTML = x;
}
//上传游戏发布金额
function myFunction1() {
    var x = document.getElementById("money_input") .value;
    var y = document.getElementById("input_num") .value;
    p = x;
    n = y;
    var num = (p*n);
    document.getElementById("pre_price").innerHTML = x;
    document.getElementById("pre_rece").innerHTML = num;
}
//上传游戏数量
function myFunction2() {
    var x = document.getElementById("money_input") .value;
    var y = document.getElementById("input_num") .value;
    p = x;
    n = y;
    var num = (p*n);
    document.getElementById("pre_num").innerHTML = y;
    document.getElementById("pre_rece").innerHTML = num;
}
//上传游戏类型
function myFunction3() {
    var selectNode =  document.getElementById("type_select");
    var index = selectNode.selectedIndex;
    var x = selectNode.options[index].innerText;
    document.getElementById("pre_type").innerHTML = x;
}
//上传游戏区服
function myFunction4() {
    var selectNode =  document.getElementById("name_select");
    var index = selectNode.selectedIndex;
    var x = selectNode.options[index].innerText;
    document.getElementById("pre_area1").innerHTML = x;
    document.getElementById("pre_area2").innerHTML = "";
}
function myFunction5() {
    var selectNode =  document.getElementById("area_select");
    var index = selectNode.selectedIndex;
    var y = selectNode.options[index].innerText;
    document.getElementById("pre_area2").innerHTML = "/" + y;
}

//预览图片,参数说明：第一个参数：要预览的file对象，第二个参数：预览的img标签的id名称
function yl(obj,id) {
    //FileReader
    if(window.FileReader){//验证当前的浏览器是否支持图片预览
        var reader=new FileReader();
        var file=obj.files[0];
        var regexImage=/^image\//;//js正则表达式，匹配是否拥有image
        if(regexImage.test(file.type)){
            //设置读取结束的回调函数
            reader.onload=function(data){
                var img=document.getElementById(id);
                img.src=data.target.result;//将结果数据显示到img标签上

            };
            //开始读取上传的文件内容
            reader.readAsDataURL(file);
        }else{
            alert("亲，看清楚是图片预览");
            return;
        }
    }else{
        alert("亲，浏览器该升级了");
        return;
    }
}
