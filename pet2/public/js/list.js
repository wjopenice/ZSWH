$(function () {
    var index = $(".sub_li.active").index();
    $(".list_ul").eq(index).addClass('active').siblings().removeClass('active');

    $(".sub_li").click(function(){
        var index = $(this).index();
        $(this).addClass("active").siblings().removeClass("active");
        $(".list_ul").eq(index).addClass('active').siblings().removeClass('active');
    })
});