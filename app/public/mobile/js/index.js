$(function () {
    $('.swiper-container').height($(window).height());
    new Swiper('.swiper-container', {
        direction: 'vertical',
        nextButton: '.next'
    });
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    if (isAndroid) {
        $("#btn a").attr("class","android");
    }
    if (isIOS) {
        $("#btn a").attr("class","ios");
    }
    $('.comTit').html($('.nav').html());
    $('.btn a').click(function() {
        var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
        var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if (isAndroid) {
            $("#btn a").attr("class","android");
            if(isWeChat()){
                weChatRes('/public/mobile/images/tips_weixin_android.png');
            }else{
                window.location.href = 'http://app.zhishengwh.com/public/package/1561712019/20190628165339-186855.apk';
            }
        }
        if (isIOS) {
            $("#btn a").attr("class","ios");
            if(isWeChat()){
                weChatRes('/public/mobile/images/tips_weixin_android.png');
            }else {
                alert('暂时不支持Ios端下载');
            }
        }
    });

    $('.manual  ul li').eq(0).addClass('active').find('.con').show();
    $('.manual  ul li a').click(function() {
        $(this).parent().addClass('active').siblings().removeClass('active').find('.con').stop().slideUp();
        $(this).next().stop().slideDown()
    })

});

//判断是wx
function isWeChat() {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == "micromessenger") {
        return true;
    } else {
        return false;
    }
}

function sysTemInfo() {
    var us = navigator.userAgent.toLowerCase();
    if ((us.indexOf('android') > -1 || us.indexOf('linux') > -1) || navigator.platform.toLowerCase().indexOf('linux') != -1) {
        return 'android';
    } else if (us.indexOf('iphone') > -1 || us.indexOf('ipad') > -1) {
        return 'ios';
    }
}

//微信系统内容处理
function weChatRes(n) {
    var html = '<div class="wechat"><img src="' + n + '" alt="点击右上角，然后选择浏览器打开！"/></div>';
    $('body').append(html);
    $(".wechat").css("height", $(window).height()).show();
}

