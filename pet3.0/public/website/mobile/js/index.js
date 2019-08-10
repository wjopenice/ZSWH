$(function () {
    $('.swiper-container').height($(window).height());
    new Swiper('.swiper-container', {
        direction: 'vertical',
        nextButton: '.next'
    });

    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    $('.down_btn').click(function() {
        if (isAndroid) {
            if(isWeChat()){
                weChatRes('/website/mobile/images/tips_weixin_android.png');
            }else{
                window.location.href = 'https://www.pgyer.com/OwP5';
            }
        }
        if (isIOS) {
            if(isWeChat()){
                weChatRes('/website/mobile/images/tips_weixin_ios.png');
            }else {
                window.location.href = 'https://www.pgyer.com/Cmb4';
            }
        }
    });

    $(".more_icon").click(function () {
        $(".mask").show();
    })

});

function getOSType() {
    if (/(Android)/i.test(navigator.userAgent)) {
        return true;
    }
}

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
    var html = '<div class="wechat" onclick="wechat()"><img src="' + n + '" alt="点击右上角，然后选择浏览器打开！"/></div>';
    $('body').append(html);
    $(".wechat").css("height", $(window).height()).show();
}

function wechat() {
    $(".wechat").remove();
}

function mask() {
    $(".mask").hide();
}
