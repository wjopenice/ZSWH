$(function(){
    //tab切换
    $(".classify_box a").click(function(){
        var index = $(this).index();
        $(this).addClass("active").siblings().removeClass("active");
        $(".classify_content .classify_ul").eq(index).addClass('active').siblings().removeClass('active');
    });

    //金额格式
    /*var money = $(".money_val").text();
    console.log(money)
    var num = parseFloat(money).toFixed(2);
    $(".money_val").text(num);*/
});