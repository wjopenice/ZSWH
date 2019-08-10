(function ($) {

    // 滑动切换
    $.fn.hbySlide = function (opitons) {
        var defaults = {
            slideLi: ".slide-content li",   //滑动元素
            dotLi: ".slide-triggers li",    //小圆点
            slidePrev: ".slide-btns .prev", //向左箭头
            slideNext: ".slide-btns .next", //向右箭头
            isHoverBtns: true,              //hover才显示滑动按钮
            isAuto: true,                   //是否自动切换
            duration: 4000,                 //间隔时间
            mode: "hide"                    //滑动模式，hide : 显示或隐藏， left : 左右滑动 ， top : 上下滑动
        };
        var settings = $.extend({}, defaults, opitons); //利用extend方法把 defaults对象的方法属性全部整合到 options里
        this.each(function () {
            var $this = $(this),
                currentIndex = -1,
                interval = null,
                slideItems = $(settings.slideLi, $this),
                dotItems = $(settings.dotLi, $this),
                sItem = slideItems.eq(0),
                prev = $(settings.slidePrev, $this),
                next = $(settings.slideNext, $this),
                isHoverBtns = settings.isHoverBtns,
                isAuto = settings.isAuto,
                mode = settings.mode,
                dutation = settings.duration,
                showCount = mode == "top" ? parseInt($this.outerHeight(false) / slideItems.eq(0).outerHeight(false)) : parseInt($this.outerWidth(true) / sItem.outerWidth(false)),
                sildeCount = mode == "hide" ? slideItems.length : slideItems.length - showCount + 1,
                len = slideItems.length,
                isPrev = false,
                play = function () {
                    if (isPrev) {
                        currentIndex--;
                        currentIndex < 0 && (currentIndex = sildeCount - 1);
                    } else {
                        currentIndex++;
                        currentIndex >= sildeCount && (currentIndex = 0);
                    }
                    change(currentIndex);
                },
                change = function (index) {

                    if (mode == "left") {
                        sItem.parent().animate({ "margin-left": -index * sItem.outerWidth(true) }, 300);
                    }
                    else if (mode == "top") {

                        sItem.parent().animate({ "margin-top": -index * sItem.outerHeight(true) }, 300);
                    }
                    else {
                        slideItems.hide();
                        var startIndex = index - showCount, endIndex = index + 1;
                        if (isPrev) {
                            startIndex = index - 1;
                            endIndex = index + showCount;
                        }
                        slideItems.each(function (eachIndex) {
                            eachIndex > startIndex && eachIndex < endIndex && $(this).show();
                        });
                    }
                    dotItems.eq(index).addClass("cur").siblings().removeClass("cur");
                },
                timer = function () {
                    clearTimer();
                    if (isAuto) {
                        !interval && (interval = setInterval(play, dutation));
                    }
                },
                clearTimer = function () {
                    interval && clearInterval(interval);
                    interval = null;
                },
                showBtns = function () {
                    if (len > showCount) {
                        prev.show();
                        next.show();
                    }
                },
                hideBtns = function () {
                    if (len > showCount) {
                        prev.hide();
                        next.hide();
                    }
                };

            play();

            if (len <= showCount) {
                prev.hide();
                next.hide();
                dotItems.hide();
            } else {
                timer();

                $this.mouseover(function () {
                    isAuto && clearTimer();
                    isHoverBtns && showBtns();
                }).mouseout(function () {
                    isAuto && timer();
                    isHoverBtns && hideBtns();
                });

                dotItems.mouseover(function () {
                    clearTimer();
                    var index = $(this).index();
                    currentIndex != index && change(currentIndex = index);
                }).mouseout(function () {
                    timer();
                });

                next.mousedown(function () {
                    clearTimer();
                    play();
                });

                prev.mousedown(function () {
                    clearTimer();
                    isPrev = true;
                    play();
                }).mouseup(function () {
                    isPrev = false;
                });

            }
        });
        return this;
    };

    // 下拉菜单
    $.fn.hbyDropdown = function (opitons) {

        var defaults = {
            subItems: ["> ul > li", "> ul > li"],
            show: function () { },
            hide: function () { }
        };
        var hoverObj = {};  // 悬停的对象链
        var settings = $.extend({}, defaults, opitons);

        // 递归绑定所有层级
        var bind = function (item, index) {
            var subId = item.attr("sub-id");
            var sub = subId ? $(subId) : null;
            if (sub && sub[0]) {
                // 菜单项
                item.mouseover(function () {
                    var $this = $(this);
                    if (index == 0) {
                        sub.css({
                            "position": "absolute",
                            "z-index": 100,
                            "left": $this.offset().left,
                            "top": $this.offset().top + $this.height()
                        }).show();
                    } else {
                        // 父级可能还没展示，故延迟 1 毫秒做展示，这样不会错位
                        setTimeout(function () {
                            sub.css({
                                "position": "absolute",
                                "z-index": 100,
                                "left": $this.position().left + $this.width(),
                                "top": $this.position().top
                            }).show();
                        }, 1);
                    }
                    settings.show && settings.show();
                }).mouseout(function () {
                    sub.hide();
                    settings.hide && settings.hide();
                });

                // 不允许，出现指向同一个层级，否则 sub 会重复绑定事件
                sub.mouseover(function () {
                    if (index == 0) {
                        hoverObj = {};
                    }
                    hoverObj[index] = $(this);
                    for (var i = 0; i <= index; i++) {
                        hoverObj[i] && hoverObj[i].show();
                    }
                    settings.show && settings.show();
                }).mouseout(function () {
                    for (var key in hoverObj) {
                        hoverObj[key].hide();
                    }
                    settings.hide && settings.hide();
                });

                if (settings.subItems.length > index) {
                    sub.find(settings.subItems[index]).each(function () {
                        (function (pItem, pIndex) {
                            bind(pItem, pIndex + 1);
                        })($(this), index);
                    });
                }
            }
        }

        this.each(function () {
            var $this = $(this);
            (function (item) { bind(item, 0); })($this);
        });

        return this;
    };

    // 弹出层
    $.fn.hbyPopShow = function (opitons) {
        var defaults = {
            url: "",
            title: "",
            width: "",
            height: "",
            isWeb: false,
            isDel: false,
            isHideTitle: false
        };
        var settings = $.extend({}, defaults, opitons);
        this.each(function () {
            var $this = $(this),
                url = settings.url,
                title = settings.title,
                width = settings.width,
                height = settings.height,
                isWeb = settings.isWeb,
                isDel = settings.isDel,
                isHideTitle = settings.isHideTitle;

            var tag = $('<div class="sys-pop-wrap' + (isWeb ? " sys-pop-web-wrap" : "") + '"><div class="sys-pop-mask" ><iframe scrolling="no"src="about:blank"></iframe></div></div>').show().appendTo('body');
            var main = $('<div class="sys-pop-main"></div>').appendTo(tag).css({ "width": width, "height": height});
            var cont = $('<div class="pop-cont"></div>').appendTo(main);

            if (isHideTitle) {
                cont.css({ height: height });
            } else {
                var tit = $('<div class="pop-tit"><a class="close fr"></a><span>' + (title ? title : '') + '</span></div>');
                tit.prependTo(main).find(".close").click(function () {
                    $this.hbyPopHide(isDel);
                });
                cont.css({ height: height - tit.outerHeight(true) });
            }
            if (url) {
                var loading = $('<table class="pop-loading"><tr><td>加载中...</td></tr></table>').appendTo(cont);
                $this.find("iframe").load(function () {
                    loading.remove();
                }).prop("src", url);
            }
            $this.show().appendTo(cont);
            return this;
        });

        return this;
    };

    // 隐藏弹出层
    $.fn.hbyPopHide = function (isDel) {
        var $this = $(this);
        var pop = $this.closest('.sys-pop-wrap');
        $this.hide().appendTo('body');
        pop.remove();
        isDel && $this.remove();
        return this;
    };

    // 弹出层提示
    $.hbyPopTip = function (val) {
        $("#__sys_pop_tip_wrap").remove();
        var tag = $('<div id="__sys_pop_tip_wrap" class="sys-pop-wrap sys-pop-tip-wrap"><div class="sys-pop-mask" ><iframe scrolling="no"src="about:blank"></iframe></div></div>').show().appendTo('body');
        var main = $('<div class="sys-pop-main"></div>').appendTo(tag);
        var tit = $('<div class="pop-tit"><a class="close fr"></a><span>系统提示</span></div>').appendTo(main);
        $('<div class="pop-cont">' + val + '</div>').appendTo(main);
        var ft = $('<div class="pop-ft"><a href="javascript:void(0)" class="btn">确认</a></div>').appendTo(main);
        tit.find(".close").click(function () {
            tag.remove();
        });
        ft.find(".btn").click(function () {
            tag.remove();
        }).focus();
    };

    // 提示，success, wran, error
    $.hbySystemTip = function (tip, msg) {
        $("#__sys_tip").remove();
        var systemTip = $("<div id='__sys_tip' class='sys-tip'></div>");
        systemTip.addClass("tip-" + tip);
        systemTip.text(msg);
        systemTip.show().appendTo("body");
        systemTip.css({
            "margin-left": -(systemTip.width() / 2) + "px"
        });
        setTimeout(function () {
            systemTip.remove();
        }, 4000);
    };

    // 加载中提示
    $.hbyLoadingTip = function() {
        var tag = $('<div class="sys-pop-wrap sys-pop-loading-wrap"><div class="sys-pop-mask" ><iframe scrolling="no"src="about:blank"></iframe></div></div>').show().appendTo('body');
        var main = $('<div class="sys-pop-main"></div>').appendTo(tag);
        $('<div class="pop-cont"><table class="pop-loading"><tr><td>加载中...</td></tr></table></div>').appendTo(main);
        return tag;
    };

    // 无缝隙上下滚动插件
    (function() {
        $.fn.hbyMarquee = function(option) {
            $(this).each(function() {
                new marquee($(this), option);
            });
        };
        var marquee = function ($ele, option) {
            var defaults = {
                delay: 2500,
                speed: 40,
                scrollH: null,
                scrollNum: 1
            };
            this.$ele = $ele;
            this.$inner = this.$ele.find("ul");
            this.option = $.extend({}, defaults, option);
            if (!this.option.scrollH) {
                this.option.scrollH = this.$inner.find("li").height();
            }
            this.init();
        };
        marquee.prototype.init = function () {
            var me = this, $inner = this.$inner, option = this.option, scrollH = option.scrollH * option.scrollNum, liNum = $inner.children().length, innerH = $inner.height();
            var $copy = $($inner[0].outerHTML);
            this.$ele.append($copy);
            if (option.delay) {
                var interTime = option.scrollH / option.speed * 1000;
                var loop = function() {
                    $inner.stop(true, false);
                    $inner.animate({ "margin-top": "-=" + scrollH }, interTime, "linear", function() {
                        if (Math.abs(parseInt($inner.css("margin-top"))) >= innerH)
                            $inner.css("margin-top", 0);
                    });
                };
                this.inter = setInterval(loop, option.delay);
                $inner.on("mouseover", function () {
                    clearInterval(me.inter);
                });
                $inner.on("mouseout", function () {
                    me.inter = setInterval(loop, option.delay);
                });
            } else {
                var interTime = option.scrollH / option.speed * 1000 * liNum;
                this.$ele.on("mouseover", function () {
                    $inner.stop(true, false);
                });
                this.$ele.on("mouseout", function () {
                    var curMTop = Math.abs($inner.css("margin-top").slice(0, -2));
                    var remainTime = ($inner.height() - curMTop) / $inner.height() * interTime;
                    (function () {
                        var fn = arguments.callee;
                        $inner.animate({ "margin-top": -$inner.height() }, remainTime, "linear", function () {
                            $inner.css("margin-top", 0);
                            remainTime = option.scrollH / option.speed * 1000 * liNum;
                            fn();
                        });
                    })();
                });
                this.$ele.trigger("mouseout");
            }
        };
    })();


})(jQuery);





