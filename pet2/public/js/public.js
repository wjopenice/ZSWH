$(function(){

    //用于copy
    $('.copy').each(function(i, item) {
        //定义该值是为了省略后面程序的的字符数，并且取copy的第一个
        var that = $('.copy').eq(0);
        //获得当前copy标签的data-num的值，即要复制的次数
        var num = that.attr("data-num");
        //获得包括当前节点的html代码
        var obj = that.clone().prop("outerHTML");
        //将获得到的html代码中的copy字符串去除，以免js出现死循环或错误循环，并存为变量
        var newObj = obj.replace('copy', '');

        for (i = 1; i < num; i++) {
            //在当前节点后插入html代码
            that.after(newObj);
        }
        //移除当前节点的copy的class,避免对页面第二个copy标签的复制影响
        that.removeClass('copy');
    });


});


//封装弹窗插件
function modal(pWidth,content){
    $("#msg").remove();
    var html ='<div id="msg" style="position:fixed;top:50%;width:400px;left: 50%;margin-left: -220px;height:30px;line-height:30px;margin-top:-35px;"><p style="background:#000;opacity:0.6;width:'+ pWidth +'px;color:#fff;text-align:center;padding:20px;margin:0 auto;font-size:16px;border-radius:4px;">'+content+'</p></div>';
    $("body").append(html);
    var t = setTimeout(next,3000);
    function next(){
        $("#msg").remove();
    }
}

//封装对姓名 电话 身份证部分隐藏
//三个参数的含义：str：字符串，frontLen：前面保留位数，endLen：后面保留位数
function plusStar (str,frontLen,endLen) {
    var len = str.length-frontLen-endLen;
    var star = '';
    for(var i=0;i<len;i++) {
        star+='*';
        }
    return str.substring(0,frontLen)+star+str.substring(str.length-endLen);
}
