//newwebsite
function FixedHead(Top){
	var Header = jQuery('#header')
	
	if(!Header.length) return false;
	
	if(Top >= 100){
		Header.addClass('on');
	}else{
		Header.removeClass('on');
	}
}

function FixedBanner(){
    var Banner = jQuery('#banner.FixedBanner'),
        WinHeight = jQuery(window).height();

    Banner.height(WinHeight);
}

function FixedBlank(){
	var h=jQuery('#header').height();
	jQuery('#header-blank').height(h);
}

function OpenMenu(){
	var MenuBtn = jQuery('#menu-btn'),
		Nav = jQuery('#nav');

	Nav.toggleClass('open-menu');
}

function msg(){
	$('.message').click(function(){
		$('#msg-box').before('<div id="msg-mask" class="fixed"></div>')	
		$('#msg-box').fadeIn('fast');
	})
	
	$('#msg-box .cls').click(function(){
		$('#msg-box').fadeOut('fast');
		$('#msg-mask').remove();
	})
}

function msgSubmit(){
	var para=$('#msgForm input[notnull]');
	var email=/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
	var mobile=/^1[34578]\d{9}$/;
	var status=true;
	
	$('.input-label').click(function(){
		$(this).toggleClass('checked')	
	})
	
	para.blur(function(){
		if($(this).val()==''){
			status=false;
			$(this).css('border-color', '#f00');
		}else{
			status=true;
			$(this).css('border-color', '#ddd');
		}
	});
	$('.msg-submit').click(function(){
		para.each(function(index, element) {
            if($(this).val()==''){
				$(this).css('border-color', '#F00');
			}
        });
		if(!email.test($('input[name=Email]').val())){
			status=false;
			$('#msgForm input[name=Email]').css('border-color', '#f00');
		}else{
			$('#msgForm input[name=Email]').css('border-color', '#ddd');	
		}
		if(!mobile.test($('input[name=Phone]').val())){
			status=false;
			$('#msgForm input[name=Phone]').css('border-color', '#f00');
		}else{
			status=true;
			$('#msgForm input[name=Phone]').css('border-color', '#ddd');
		}
		if(status==false){return;}
		$.post('/newwebsite/ajax/msg.php', $('#msgForm').serialize(), function(data){
			alert(data.msg);
			if(data.ret==1){
				window.location.href=$('input[name=return_url]').val();	
			}
		}, 'json');	
	})
}

function Development(){         //发展历程
	var Development = jQuery('.about-development'),
		Item = jQuery('.item',Development),
		Progress = jQuery('.about-progress .i',Development),
		Index = 0,
		Timer,Moving;

	Action(Index);
	//Auto(5000);

	Item.hover(function(){
		Index = jQuery(this).index();
		clearTimeout(Timer);
		clearTimeout(Moving);
		Action(Index);
        ActionProgress(Index,500);
	},function(){
        //Auto(5000);
    });

	function Action(i){
		Item.eq(i).addClass('on');
		/*Item.eq(i).children('.default').addClass('flipOutY').removeClass('flipInX');
		Item.eq(i).children('.bg').addClass('flipInX').removeClass('flipOutY');
		Item.eq(i).children('.development').addClass('flipInX').removeClass('flipOutY');*/

		Item.eq(i).siblings(Item).removeClass('on');
		/*Item.eq(i).siblings(Item).children('.default').removeClass('flipOutY');
		Item.eq(i).siblings(Item).children('.bg').removeClass('flipInX');
		Item.eq(i).siblings(Item).children('.development').removeClass('flipInX');*/
	}

	function ActionProgress(i,speed,callbacks){
		var Distance = Item.outerHeight(true);
		Progress.stop(false,true).animate({height:Distance*i-25},speed,'linear');
		Moving = setTimeout(function(){
			jQuery.isFunction(callbacks) && callbacks();
		}, speed);
	}

	function Auto(time){
		Timer = setTimeout(function(){
			Index >= Item.length-1 ? Index = 0 : Index++;
			ActionProgress(Index,5000,function(){
				Action(Index);
				Auto(!isNaN(time) ? time : 5000);
			});
		}, !isNaN(time) ? time : 5000);
	}
}

function jobToggle(){
	var JobList = jQuery('.angle');
	JobList.click(function(){
		var index = JobList.index(this);
		jQuery('.angle').eq(index).toggleClass('on');
		jQuery('.description-wrap').eq(index).slideToggle();
	})
}

function ClientMove(){          //客户logo效果
	var Client = jQuery('.index-client'),
		List = jQuery('.list',Client),
		Item = jQuery('.item',Client),
		Move = jQuery('.move',Client);

	Item.mouseover(function(){
		var Left = jQuery(this).position().left,
			Top = jQuery(this).position().top,
			Width = jQuery(this).outerWidth(true),
			Height = jQuery(this).outerHeight(true);

		Move.css({left:Left,top:Top,width:Width,height:Height,opacity:1});
	});

	List.mouseleave(function(){
		Move.css('opacity',0);
	});
}

function SurroundingsBox(){     //环境                    优化处理中………………
	if(!jQuery('.team-surroundings').length) return false;

    var Box = jQuery('.team-surroundings'),
        Bd = jQuery('.bd',Box),
        Desc = jQuery('.desc',Box),
        Rig = jQuery('.rig',Box),
        Category = jQuery('.bar .category',Box),
        AllList = jQuery('.list',Category),
        ListBox = [],
        CateImg = jQuery('.category-img',Category),
        Imgs = [],
        orientation = 'r',
        cuboidsRandom = false,
        cuboidsCount = 5,
        disperseFactor = 30,
        Index = 0,
        ImgMove,_X,DownTime,MouseInterval,
        CategoryBox = Category.slicebox({
            onReady : function(){
                Bd.each(function(i,e){
                    jQuery(e).children().first().addClass('on');
                });
                Desc.each(function(i,e){
                    jQuery(e).children().first().addClass('on');
                });
                AllList.show();
                CateImg.hide();
            },
            onBeforeChange : function(i){
                Bd.each(function(ii,ee){
                    jQuery(ee).children().eq(i).addClass('on').siblings().removeClass('on');
                });
                Desc.each(function(ii,ee){
                    jQuery(ee).children().eq(i).addClass('on').siblings().removeClass('on');
                });
                AllList.show();
                CateImg.hide();
            },
            orientation : orientation,
            cuboidsRandom : cuboidsRandom,
            cuboidsCount : cuboidsCount,
            disperseFactor : disperseFactor
        });

    Desc.each(function(i,e){
        var txt = jQuery(e).children('.txt');
        txt.first().addClass('on');
        txt.each(function(ii,ee){
            var item = jQuery(ee).children();
            item.first().addClass('rollIn on');
        });
    });

    AllList.each(function(listi,list){
        var Cate = jQuery(list).parents(Category),
            Hd = jQuery(list).next('.hd'),
            Item = jQuery(list).children(),
            Img = CateImg.eq(listi),
            Html = '';

        Item.each(function(i,e){
            Html += '<a class="trans" rel="nofollow"></a>';
            Imgs[listi] = Item.eq(i).children('img').attr('src');
        });

        if(Item.length > 1){
            Hd.html(Html);
        }
        ListBox[listi] = jQuery(list).slicebox({
            onReady : function(){
                Hd.children().first().addClass('on');
            },
            onBeforeChange : function(c){
                Img.attr('src',Imgs[listi]);
                Hd.children().eq(c).addClass('on').siblings().removeClass('on');
                Desc.each(function(i,e){
                    var txt = jQuery(e).children('.txt'),
                        item = jQuery('.item',txt.eq(listi));
                    txt.eq(listi).addClass('on').siblings(txt).removeClass('on');
                    item.eq(c).addClass('rollIn animate on').siblings().addClass('rollOut animate');
                    setTimeout(function(){
                        item.removeClass('rollIn rollOut animate');
                        item.not(item.eq(c)).removeClass('on');
                    },1000);
                });
            },
            orientation : orientation,
            cuboidsRandom : cuboidsRandom,
            cuboidsCount : cuboidsCount,
            disperseFactor : disperseFactor
        });

        Hd.children('a').click(function(){
            var i = jQuery(this).index();
            Rig.height(Rig.height());
            Imgs[listi] = Item.eq(i).children('img').attr('src');
            ListBox[listi].jump(i+1);
            setTimeout(function(){
                Rig.height('auto');
            },1500);
        });
    });

    //touch是在移动端使用的
    /*Rig.on('touchstart',function(e){
        _X = e.originalEvent.targetTouches[0].pageX;	//记录鼠标点下时的位置	!!这条是手机上使用的!!
        ImgMove = true;
        DownTime = 0;       //记录鼠标点下的到松开的时间
        MouseInterval = setInterval(function(){DownTime++;},1);
    });
    jQuery('body').on('touchend',function(e){
        if(ImgMove){
            clearInterval(MouseInterval);

            if(DownTime < 100){      //鼠标快速滑过
                if(e.originalEvent.changedTouches[0].pageX > _X){	//向左滑
                    ListBox[Index].previous();
                }else if(e.originalEvent.changedTouches[0].pageX < _X){	//向右滑
                    ListBox[Index].next();
                }
            }
        }
        ImgMove = false;
    });*/

    Bd.children().click(function(){
        var i = jQuery(this).index();
        if(Index != i){
            AllList.hide();
            CateImg.show();
            Index = i;
            Rig.height(Rig.height());
            setTimeout(function(){
                Rig.height('auto');
            },1500);
        }
        CategoryBox.jump(i+1);		//参数是从1开始计算的。
    });
}

function IndexCaseList(){           //首页案例(旧版)
    var Case = jQuery('.index-case'),
        Category = jQuery('.case-category a',Case),
        Button = jQuery('.button',Case),
        Bd = jQuery('.bd',Case),
        Bar = jQuery('.bar',Case),
        List = jQuery('.case-list',Case),
        AllItem = jQuery('.item',List),
        Item = jQuery('.item[status="show"]',List),
        ItemWidth = Item.outerWidth(true),
        ItemHeight = Item.outerHeight(true),
        Row = 3,
        Col = 3,
        Scroll = 4,
        Index = 0,
        Max = 0,
        Para = '',
        MoveTimer = '',
        InitPara = {
            0 : {
                Width : 1920,
                Row : 3,
                Col : 3,
                Scroll : 1
            },
            1 : {
                Width : 992,
                Row : 2,
                Col : 2,
                Scroll : 1
            },
            2 : {
                Width : 640,
                Row : 8,
                Col : 1,
                Scroll : 1
            }
        };

    Category.click(function(){
        var Url = jQuery(this).attr('href');
        switch(Url){
            case '#recommend':
                Para = {Classic:1};
                break;

            default:
                Para = {CateId:parseInt(Url.replace('#Cate',''))};
                break;
        }

        jQuery(this).addClass('on').siblings(Category).removeClass('on');

        AllItem.each(function(i,e){
            var Data = eval('(' +jQuery(e).attr('data')+ ')'),
                Is = false;

            if(Para.Classic){
                Data.Classic != Para.Classic && jQuery(e).attr('status','hide').children('.move').addClass('zoomOut animated').removeClass('zoomIn');
                Data.Classic == Para.Classic && jQuery(e).attr('status','show').children('.move').addClass('zoomIn animated').removeClass('zoomOut');
            }else if(Para.CateId){
                jQuery.each(Data.CateId, function(k,v){
                    if(Para.CateId == v) Is = true;
                });
                
                Is ? jQuery(e).attr('status','show').children('.move').addClass('zoomIn animated').removeClass('zoomOut') : jQuery(e).attr('status','hide').children('.move').addClass('zoomOut animated').removeClass('zoomIn');
            }
        });

        clearTimeout(MoveTimer);
        Init();
        ItemMove();

        return false;
    });

    Button.click(function(){
        jQuery(this).hasClass('prev') ? (Index > 0 ? Index-- : '') : (Index < Max ? Index++ : '');
        Slide(Index);
    });

    function Slide(i){
        Bar.css({top:-(Item.outerHeight(true)*Scroll*i)});
    }

    function ItemMove(){
        var Left = 0,
            Top = 0,
            R = 0;

        Item.each(function(i,e){
            jQuery(e).css({left:Left,top:Top});
            if((i+1)%Col==0){
                Left = 0;
                R++;
                Top = R*ItemHeight;
            }else{
                Left += ItemWidth;
            }
        });
    }

    function Init(){
        var HideItem = jQuery('.item[status="hide"]',List),
            WinWidth = jQuery(window).width(),
            OuterWidth = Item.outerWidth(true)*Scroll,
            OuterHeight = Item.outerHeight(true)*Row;

        jQuery.each(InitPara,function(key){
            if(WinWidth <= this.Width){
                Row = this.Row;
                Col = this.Col;
                Scroll = this.Scroll;
            }
        });

        Item = jQuery('.item[status="show"]',List);
        Max = Math.ceil((Item.length-(Row*Col))/Col)/Scroll;
        Item.each(function(i,e){
            jQuery(e).outerWidth(true) > 0 ? ItemWidth = jQuery(e).outerWidth(true) : '';
            jQuery(e).outerHeight(true) > 0 ? ItemHeight = jQuery(e).outerHeight(true) : '';
        });

        OuterWidth = ItemWidth*Scroll;
        OuterHeight = ItemHeight*Row;

        Index = 0;
        Slide(Index);

        Bd.height(OuterHeight);
    }

    jQuery(window).resize(function(){
        Init();
        ItemMove();
    });

    Item.imagesLoaded(function(){
        Init();
        ItemMove();
    });

    return;
}

function indexCaseSwitch(){
	var category=$('.case-category .category-item');
	var drop=$('.case-menu li');	
	var list=$('.case-list');
	
	if(pg=='case'){return false;}
	category.click(function(){
		var index=category.index(this);

		category.removeClass('on').eq(index).addClass('on');
		list.hide(500).eq(index).show(300);
	});
	
	drop.click(function(){
		var index=drop.index(this);
		
		$('#dropdownMenu1').html($(this).find('a').text()+' <span class="caret"></span>');
		list.hide(500).eq(index).show(300);	
	});
}

function indexNewsSwitch(){
	var category=$('.news-category .category-item');
	var drop=$('.news-menu li');
	var list=$('.news-list');
	
	category.click(function(){
		var index=category.index(this);
		
		category.removeClass('on').eq(index).addClass('on');
		list.hide(500).eq(index).show(300);	
	});
	
	drop.click(function(){
		var index=drop.index(this);
		
		$('#dropdownMenu2').html($(this).find('a').text()+' <span class="caret"></span>');
		list.hide(500).eq(index).show(300);	
	});
}

function SolutionControl(Top){
	var control=$('#solution-control');
	if(!control.length) return false;
	
	if(Top >=600){
		control.addClass('to-up');
	}else{
		control.removeClass('to-up');
	}
}

function popService(data,obj){
    var num = parseInt(Math.random()*data.length),
        service = data[num];

    obj.click(function(){
        window.open('http://wpa.qq.com/msgrd?v=3&uin='+service.QQNumber+'&site=qq&menu=yes');
    });
}

function AjaxLoadList(){
    var PageData = jQuery('.page-data');
    PageData.each(function(i,e){
        var Name = jQuery(e).attr('name'),
            Page = jQuery(e).attr('page'),
            Max = jQuery(e).attr('max'),
            Where = jQuery(e).attr('where'),
            List = jQuery('#'+Name),
            Switch = true,
            Path = jQuery(e).attr('path');

        ScrollShow(jQuery(e),true,function(){
            if(Page && Path && Switch && Max && Where && List.length && Max>Page){
                Switch = false;
                jQuery(e).addClass('loading');
                setTimeout(function(){
                    Page++;
                    jQuery.get(Path,{page:Page,where:Where},function(Data){
                        if(Data){
                            List.append(Data);
                            Switch = true;
                        }
                        jQuery(e).removeClass('loading');
                    });
                },500);
            }
        });
    });

    return false;
}

function PlayVideo(Path,Type){      //播放视频      Type:{0:第三方视频,1:本地视频}
    if(Path && !jQuery('#PlayVideo').length){
        var Width = jQuery(window).width()*0.8,
            Height = jQuery(window).height()*0.8;
        Html = '<div class="Body-Mask hide"></div><div id="PlayVideo" class="hide"><div class="play-box relative">{Play}<a class="close-btn" href="javascript:;" rel="nofollow"><img class="max-w100 blodk" src="/newwebsite/images/images/close.jpg" alt=""/></a></div></div>',
        Play = '';

        switch(Type){
            case 0:
                Play = '<embed src="'+Path+'" allowfullscreen="true" quality="high" allowscriptaccess="always" type="application/x-shockwave-flash" width="'+Width+'" height="'+Height+'" align="middle" wmode="transparent">';
                break;
            case 1:
                Play = '<video class="video-js vjs-default-skin" controls preload="auto" width="'+Width+'" height="'+Height+'" poster="" data-setup="{}"><source src="'+Path+'" type="video/mp4" /><source src="'+Path+'" type="video/webm"><source src="'+Path+'" type="video/ogg"><p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p></video>';
                break;
        }

        Html += (Type == 1 ? '<script src="/js/video.min.js"></script>' : '' )+(Html.replace('{Play}',Play));   //需要一起加载在才会有播放器样式   /(ㄒoㄒ)/~~

        jQuery('body').append(Html);

        jQuery('body > .Body-Mask , body > #PlayVideo').fadeIn(300,function(){jQuery(this).removeClass('hide')});
        jQuery('body > .Body-Mask , body > #PlayVideo .close-btn').click(function(){
            jQuery('body > .Body-Mask , body > #PlayVideo').fadeOut(300,function(){jQuery(this).remove();});
        });
    }

    return false;
}

function ShowCountUp(Obj){          //元素进入浏览器可视区域后运行数值变化
    var Demos = SetCountUp(Obj);
    ScrollShow(Obj,false,function(){
        for(i = 0; i < Demos.length; i++){
            Demos[i].start();
        }
    });
}

function SetCountUp(Obj){      //设置数值变化     Obj:对象需要拥有ID
    var Demo = [],
        DefauitOptions = {
            useEasing : false,
            useGrouping : true,
            separator : '',
            decimal : '.',
            prefix : '',
            suffix : ''
        };

    Obj.each(function(i,e){
        if(jQuery(e).attr('id') != undefined){
            var Id = jQuery(e).attr('id'),
                Start = jQuery(e).attr('data-start'),
                End = jQuery(e).attr('data-end'),
                Decimals = jQuery(e).attr('data-decimals'),
                Duration = jQuery(e).attr('data-duration'),
                useEasing = jQuery(e).attr('data-useEasing'),
                useGrouping = jQuery(e).attr('data-useGrouping'),
                separator = jQuery(e).attr('data-separator'),
                decimal = jQuery(e).attr('data-decimal'),
                prefix = jQuery(e).attr('data-prefix'),
                suffix = jQuery(e).attr('data-suffix'),
                Options = {
                    useEasing : useEasing != undefined ? useEasing : DefauitOptions.useEasing,
                    useGrouping : useGrouping != undefined ? useGrouping : DefauitOptions.useGrouping,
                    separator : separator != undefined ? separator : DefauitOptions.separator,
                    decimal : decimal != undefined ? decimal : DefauitOptions.decimal,
                    prefix : prefix != undefined ? prefix : DefauitOptions.prefix,
                    suffix : suffix != undefined ? suffix : DefauitOptions.suffix
                };

            Demo[i] = new CountUp(Id, Start, End, Decimals, Duration, Options);
        }
    });

    return Demo;
}

function ScrollShow(Obj,Repeat,Callbacks){          //对象滚动到浏览器可视区域时执行回调函数
    if(Obj.length){
        Obj.each(function(i,e){
            var Site = jQuery(e).offset(),
                Height = jQuery(e).outerHeight(true),
                WinTop = jQuery(this).scrollTop(),         //滚动条位置
                WinHeight = jQuery(this).height(),      //窗口高度
                WinArea = WinTop+WinHeight,        //浏览器可视区域
                IsRepeat = Repeat === true ? true : false,      //重复执行
                Switch = true;                                  //开关

            jQuery(window).scroll(function(){
                Site = jQuery(e).offset();
                Height = jQuery(e).outerHeight(true);
                WinTop = jQuery(this).scrollTop();
                WinHeight = jQuery(this).height();
                WinArea = WinTop+WinHeight;
                //document.title = parseInt(Site.top)+' '+WinArea+' | '+parseInt(Height+Site.top)+' '+WinTop;
                if(Site.top <= WinArea && (Height+Site.top) >= WinTop && Switch){
                    jQuery.isFunction(Callbacks) && Callbacks();
                    Switch = false;
                }else{
                    if(IsRepeat === true && Switch === false){
                        Switch = true
                    }
                }
            });
        });
    }
    return;
}

function Grayscale(){           //图片变灰色
    var Gray = jQuery('.grayscale-img').parent();

    Gray.BlackAndWhite({
        hoverEffect : true,
        responsive:true,
        speed : 300
    });
}

function FixedTopBtn(){         //回到顶部按钮的显示
    var Btn = jQuery('.totop');

    jQuery(window).scroll(function(){
        var Top = jQuery(this).scrollTop();

        Top > 200 ? Btn.addClass('show') : Btn.removeClass('show');
    });
}

function toTop(){               //回到顶部
    jQuery('html,body').animate({scrollTop:0},400);
}

function OptHot(A){
    var Hotline = jQuery('#hotline');

    if(A){
        Hotline.parents('.item').addClass('open');
    }else{
        Hotline.parents('.item').removeClass('open');
    }
}

function OptOnline(A){          //开关在线咨询
    var Online = jQuery('#online');

    if(A){
        Online.parents('.item').addClass('open');
    }else{
        Online.parents('.item').removeClass('open');
    }
}

function OptQrcode(A){
    var Qrcode = jQuery('#wechat-qrcode');
	
    switch(A){
        case 0:
            Qrcode.stop(true,false).slideUp(300);
            break;
        case 1:
            Qrcode.stop(true,false).slideDown(300);
            break;
        default :
            Qrcode.stop(true,false).slideToggle(300);
            break;
    }
}

function globalFeedback(){
	var btn=$('.global-form-line .get-code');
	
	btn.click(function(){
		var number=$('.global-form-line input[name=Phone]').val();
		var mobile=/^1[3456789]\d{9}$/;
		if(btn.hasClass('not-allowed')){return false;}
		if(mobile.test(number)){
			$.post('/newwebsite/ajax/getcode.php', {Mobile:number}, function(data){
				if(data.ret==1){
					var count=60;
					var time=setInterval(function(){
								if(count>0){
									btn.addClass('not-allowed');
									btn.html(count+'s');
									count--;
								}else{
									btn.removeClass('not-allowed');
									btn.html('获取验证码');
									clearInterval(time);
								}
							}, 1000);	
				}else{
					alert(data.msg);	
				}
			}, 'json');
		}
	});
	
	$('form[name=global-form] input[name=Submit]').click(function(){
		$('form[name=global-form]').submit(function(){return false;});
		$.post('/newwebsite/ajax/feedback.php', $('form[name=global-form]').serialize(), function(data){
			if(data.ret==1){
				alert('感谢您的留言，我们将尽快给您联系！');
				location.reload();
			}else{
				alert(data.msg);
			}
		}, 'json');
	})
}

jQuery(window).scroll(function(){
	//FixedHead(jQuery(window).scrollTop());
	SolutionControl(jQuery(window).scrollTop())
	//document.title=jQuery(window).scrollTop();
});

jQuery(window).resize(function(){
    //FixedBanner();
	FixedBlank();
});

jQuery(window).ready(function(){
	$(window).scrollTop(0);
	//document.title=jQuery(window).scrollTop();
    new WOW().init({mobile:false});
	new Swiper('#solution-swiper', {
		pagination: {el:'.solution-pagination', 'clickable':true},
		slidesPerView:4, 
		slidesPerGroup: 4,
		spaceBetween:80,
		navigation:{nextEl:'.solution-right', prevEl:'.solution-left'},
		breakpoints:{
			1440:{
				spaceBetween:50,
			},
			1280:{
				spaceBetween:30,
			},
			980:{
				slidesPerView:3,
				spaceBetween:10
			},
			768:{
				slidesPerView:2,
				spaceBetween:20
			},
			640:{
				slidesPerView:1
			}
		}, 
	});
	new Swiper('#review-swiper', {slidesPerView:4, breakpoints:{980:{slidesPerView:2, 640:{slidesPerView:1}}}, spaceBetween:20, navigation:{nextEl:'.review-right', prevEl:'.review-left'}});
	new Swiper('#client-swiper', {
        pagination: {el:'.client-pagination', 'clickable':true},
        slidesPerView: 5,
        slidesPerColumn: 3,
		slidesPerGroup: 5,
        paginationClickable: true,
        spaceBetween: 0,
		breakpoints: { 
			//当宽度小于等于640
			992: {
				slidesPerView: 3,
				slidesPerGroup: 3,
			}
		}
    });



    //首页-服务
    /*jQuery('.index-server .img-box > div').hover(function(){
        jQuery(this).children('.ico').removeAttr('style').removeClass('wow fadeInLeft fadeInRight').addClass('animated wobble');
    },function(){
        jQuery(this).children('.ico').removeClass('animated wobble');
    });*/

    //关于-视频
    jQuery('.about-video .item').hover(function(){
        jQuery(this).children('.brief').addClass('animated zoomIn');
        jQuery(this).children('.mask').addClass('animated zoomIn');
    },function(){
        jQuery(this).children('.brief').removeClass('animated zoomIn');
        jQuery(this).children('.mask').removeClass('animated zoomIn');
    });

    //联系-图标
    /*jQuery('#contact .item , #contact .contact-address').hover(function(){
        jQuery(this).children('.ico').addClass('animated wobble');
    },function(){
        jQuery(this).children('.ico').removeClass('animated wobble');
    });*/
	
	$('#global-contact .contact-icon').on('click mouseover mouseout', function(){
		$(this).find('div').toggle();
	});
	

	//FixedHead(jQuery(window).scrollTop());
    //FixedBanner();
	FixedBlank();
	ClientMove();
	Development();
    //SurroundingsBox();
    //IndexCaseList();
	indexNewsSwitch();
	//indexCaseSwitch();
    AjaxLoadList();
    ShowCountUp(jQuery('.itcavant'));
    FixedTopBtn();
	globalFeedback();
	jobToggle();
    //popService(service,jQuery('.index-contact .button.pc'));       //service变量在footer文件中定义 首页"立即咨询"
    //popService(service2,jQuery('.service-category-1 .item .price'));    //service2变量在footer文件中定义 服务页面的价格
    //Grayscale();
	msg();
	msgSubmit();
	
	$('#nav').height($(window).height()-$('#header').height());//下拉高度控制
	$('.ddsolution-detail').height($('.solution-detail').height()+50);//案例详细页左侧
});