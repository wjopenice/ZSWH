$(function(){
    $(".buy_title span").click(function(){
        var index = $(this).index();
        $(this).addClass("active").siblings().removeClass("active");
        $(".buy_ul").eq(index).addClass('active').siblings().removeClass('active');
    });
});