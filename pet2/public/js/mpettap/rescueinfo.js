$(function(){
    //点击向上滑动
    var $go = $('.go-top');
    $(window).scroll(function() {
        $(this).scrollTop() > 50 ? $go.show() : $go.hide();
    });
    $go.click(function() {
        $("html,body").animate({ scrollTop: 0 }, 500);
    });
});
