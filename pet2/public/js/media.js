$(function () {
    //andriod ios 鼠标移上去显示隐藏
    /*$(".andriod-down").hover(function () {
        $(".andriod-down .down-mask").show(300);
    }, function () {
        $(".andriod-down .down-mask").hide(300);
    });

    $(".ios-down").hover(function () {
        $(".ios-down .down-mask").show(300);
    }, function () {
        $(".ios-down .down-mask").hide(300);
    });*/

    //显示导航栏
    $(window).scroll(function() {
        $(this).scrollTop() > 90 ?  $('.nav-bar').addClass('fixed'): $('.nav-bar').removeClass('fixed');
    });

    //点击向上滑动
    var $go = $('.go-top');
    $(window).scroll(function() {
        $(this).scrollTop() > 50 ? $go.show() : $go.hide();
    });
    $go.click(function() {
        $("html,body").animate({ scrollTop: 0 }, 500);
    });
});

//百度分享
window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"16"},"slide":{"type":"slide","bdImg":"7","bdPos":"right","bdTop":"250"},"selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];

