$(function() {
    $('.comTit').html($('.nav').html());
    try {
        $('.swiper-container').height($(window).height());
        new Swiper('.swiper-container', {
            direction: 'vertical',
            nextButton: '.next'
        });
        //系统判断
        var M, channel = getQueryString('qd') || 'HUAJIANGFAPP_1',
            appKey = 'w8q4bu',
            iosOffLine = false;
        var an_url = "http://app.ercy.vip/direct/" + channel,
            ios_url = 'http://down.ercy.vip/wx.html';
        if (sysTemInfo() == 'ios') {
            appKey = 'vkvfsm';
            channel = 'ios' + channel;
            var OS = 'ios',appName = '宠爱之家';
            var titArry = ['立即下载' + appName + OS + '版app', appName + OS + '版官网下载', appName + OS + '版app官方下载', appName + '影视' + OS + '版app官方下载', appName + OS + '版下载'];
            $('.btn a').each(function(i) {
                $(this).attr({ class: OS, title: titArry[i] });
            })
            ios_url += '?qd=' + channel;
        }
        new OpenInstall({
            appKey: appKey,
            onready: function() {
                M = this;
                /*在app已安装的情况尝试拉起app*/
                M.schemeWakeup();
                $('.btn a').click(function() {
                    if (sysTemInfo() == 'ios') {
                        if (isWeChat()) {
                            // weChatRes('images/tips_weixin_ios.png');
                            M.install()
                        } else {
                            iosOffLine && installByQYZS();
                            M.install();
                            // window.location.href = ios_url;
                        }
                    } else {
                        if (isWeChat()) {
                            M.install();
                        } else {
                            window.location.href = an_url;
                        }
                    }
                })
            }
        }, {
            "channel": channel
        });
    } catch (e) {}

    $('.manual  ul li').eq(0).addClass('active').find('.con').show()
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

//微信系统内容处理
function weChatRes(n) {
    var html = '<div class="wechat"><img src="' + n + '" alt="点击右上角，然后选择浏览器打开！"/></div>';
    $('body').append(html);
    $(".wechat").css("height", $(window).height()).show();
}

//企业证书安装
/*function installByQYZS() {
    $("#js_box2").show();
    $(".now-download").show();
    $(".change").hide();
    loading = true;
    $(".top-bar").css("width", "0.1%");
    timer = setTimeout(function() {
        $(".top-bar").animate({
            width: "100%"
        }, 30000, function() {
            $(".now-download").html('安装完成，请开始设置！');
            $('.alert-btn').hide();
            $(".change").show();
            loading = false;
        });
    }, 1000);
    $('#js_closeBtn2').click(function() {
        $("#js_box2").hide();
        $(".now-download").html('“宠爱之家”安装中...');
        $(".top-bar").css("width", "0.1%");
        $('.alert-btn').show();
        clearTimeout(timer);
        loading = false;
    });
}*/

//微信系统内容处理
function weChatRes(n) {
    var html = '<div class="wechat"><img src="' + n + '" alt="点击右上角，然后选择浏览器打开！"/></div>';
    $('body').append(html);
    $(".wechat").css("height", $(window).height()).show();
}

function sysTemInfo() {
    var us = navigator.userAgent.toLowerCase();
    if ((us.indexOf('android') > -1 || us.indexOf('linux') > -1) || navigator.platform.toLowerCase().indexOf('linux') != -1) {
        return 'android';
    } else if (us.indexOf('iphone') > -1 || us.indexOf('ipad') > -1) {
        return 'ios';
    }
}

function getQueryString(name) {
    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}
